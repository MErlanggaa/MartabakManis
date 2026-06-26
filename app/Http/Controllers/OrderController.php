<?php

namespace App\Http\Controllers;

use App\Models\Layanan;
use App\Models\Order;
use App\Models\UMKM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'layanan_id' => 'required|exists:layanan,id',
            'umkm_id' => 'required|exists:umkm,id',
            'quantity' => 'required|integer|min:1|max:99',
            'delivery_method' => 'required|in:gojek,grab,umkm_go',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_address' => 'required|string|max:1000',
            'notes' => 'nullable|string|max:500',
        ]);

        $layanan = Layanan::findOrFail($validated['layanan_id']);
        $umkm = UMKM::findOrFail($validated['umkm_id']);

        if (!$layanan->umkm()->where('umkm_id', $umkm->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'Layanan tidak tersedia di UMKM ini'], 422);
        }

        $pricing = Order::calculatePricing(
            (float) $layanan->price,
            $validated['quantity'],
            $validated['delivery_method']
        );

        $orderCode = 'ORD-' . strtoupper(Str::random(8));

        $order = Order::create([
            'order_code' => $orderCode,
            'user_id' => Auth::id(),
            'umkm_id' => $umkm->id,
            'layanan_id' => $layanan->id,
            'quantity' => $validated['quantity'],
            'subtotal' => $pricing['subtotal'],
            'delivery_method' => $validated['delivery_method'],
            'delivery_fee' => $pricing['deliveryFee'],
            'app_tax' => $pricing['appTax'],
            'total' => $pricing['total'],
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'customer_address' => $validated['customer_address'],
            'notes' => $validated['notes'] ?? null,
            'payment_status' => 'pending',
            'order_status' => 'pending',
            'midtrans_order_id' => $orderCode,
            'is_seen_by_umkm' => false,
        ]);

        $snapToken = $this->createSnapToken($order, $layanan);

        if (!$snapToken) {
            $order->update(['payment_status' => 'failed']);
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat pembayaran. Periksa konfigurasi Midtrans.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'order' => $order->load('layanan'),
            'snap_token' => $snapToken,
            'client_key' => config('services.midtrans.client_key'),
        ]);
    }

    public function notification(Request $request)
    {
        $serverKey = config('services.midtrans.server_key');

        if (!$serverKey) {
            return response()->json(['message' => 'Midtrans not configured'], 500);
        }

        $orderId = $request->input('order_id');
        $transactionStatus = $request->input('transaction_status');
        $fraudStatus = $request->input('fraud_status');

        $order = Order::where('order_code', $orderId)
            ->orWhere('midtrans_order_id', $orderId)
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $order->midtrans_transaction_id = $request->input('transaction_id');

        if ($transactionStatus === 'capture') {
            $order->payment_status = ($fraudStatus === 'accept') ? 'paid' : 'failed';
            if ($order->payment_status === 'paid') {
                $order->order_status = 'confirmed';
            }
        } elseif ($transactionStatus === 'settlement') {
            $order->payment_status = 'paid';
            $order->order_status = 'confirmed';
        } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
            $order->payment_status = $transactionStatus === 'expire' ? 'expired' : 'failed';
        } elseif ($transactionStatus === 'pending') {
            $order->payment_status = 'pending';
        }

        $order->save();

        return response()->json(['message' => 'OK']);
    }

    public function paymentFinish(Request $request)
    {
        return view('orders.payment-result', [
            'status' => 'success',
            'orderCode' => $request->query('order_id'),
        ]);
    }

    public function paymentError(Request $request)
    {
        return view('orders.payment-result', [
            'status' => 'error',
            'orderCode' => $request->query('order_id'),
        ]);
    }

    public function paymentPending(Request $request)
    {
        return view('orders.payment-result', [
            'status' => 'pending',
            'orderCode' => $request->query('order_id'),
        ]);
    }

    public function pendingForUmkm()
    {
        $umkm = Auth::user()->umkm;
        if (!$umkm) {
            return response()->json(['orders' => [], 'unseen_count' => 0]);
        }

        $orders = Order::with(['layanan', 'user'])
            ->where('umkm_id', $umkm->id)
            ->where('payment_status', 'paid')
            ->where('is_seen_by_umkm', false)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($o) => $this->formatOrder($o));

        return response()->json([
            'orders' => $orders,
            'unseen_count' => $orders->count(),
        ]);
    }

    public function indexForUmkm()
    {
        $umkm = Auth::user()->umkm;
        if (!$umkm) {
            return response()->json(['orders' => []]);
        }

        $orders = Order::with(['layanan', 'user'])
            ->where('umkm_id', $umkm->id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn ($o) => $this->formatOrder($o));

        return response()->json(['orders' => $orders]);
    }

    public function markSeen(Request $request)
    {
        $umkm = Auth::user()->umkm;
        if (!$umkm) {
            return response()->json(['success' => false], 403);
        }

        $ids = $request->input('order_ids', []);
        Order::where('umkm_id', $umkm->id)
            ->whereIn('id', $ids)
            ->update(['is_seen_by_umkm' => true]);

        return response()->json(['success' => true]);
    }

    public function updateStatus(Request $request, $id)
    {
        $umkm = Auth::user()->umkm;
        $order = Order::where('id', $id)->where('umkm_id', $umkm?->id)->firstOrFail();

        $validated = $request->validate([
            'order_status' => 'required|in:confirmed,processing,delivered,cancelled',
        ]);

        $order->update(['order_status' => $validated['order_status'], 'is_seen_by_umkm' => true]);

        return response()->json(['success' => true, 'order' => $this->formatOrder($order->fresh(['layanan', 'user']))]);
    }

    public function pricingConfig()
    {
        return response()->json([
            'app_tax_rate' => Order::APP_TAX_RATE,
            'delivery_fees' => Order::DELIVERY_FEES,
            'delivery_labels' => Order::DELIVERY_LABELS,
        ]);
    }

    private function createSnapToken(Order $order, Layanan $layanan): ?string
    {
        $serverKey = config('services.midtrans.server_key');
        $isProduction = config('services.midtrans.is_production', false);

        if (!$serverKey) {
            Log::warning('Midtrans server key not configured');
            return null;
        }

        $baseUrl = $isProduction
            ? 'https://app.midtrans.com'
            : 'https://app.sandbox.midtrans.com';

        $payload = [
            'transaction_details' => [
                'order_id' => $order->order_code,
                'gross_amount' => (int) round($order->total),
            ],
            'item_details' => [
                [
                    'id' => (string) $layanan->id,
                    'price' => (int) round($layanan->price),
                    'quantity' => $order->quantity,
                    'name' => Str::limit($layanan->nama, 50),
                ],
                [
                    'id' => 'delivery',
                    'price' => (int) round($order->delivery_fee),
                    'quantity' => 1,
                    'name' => 'Ongkir ' . (Order::DELIVERY_LABELS[$order->delivery_method] ?? $order->delivery_method),
                ],
                [
                    'id' => 'app_tax',
                    'price' => (int) round($order->app_tax),
                    'quantity' => 1,
                    'name' => 'Pajak Aplikasi (2%)',
                ],
            ],
            'customer_details' => [
                'first_name' => $order->customer_name,
                'phone' => $order->customer_phone,
                'shipping_address' => [
                    'address' => $order->customer_address,
                ],
            ],
            'callbacks' => [
                'finish' => route('orders.payment.finish'),
                'error' => route('orders.payment.error'),
                'pending' => route('orders.payment.pending'),
            ],
        ];

        $response = Http::withBasicAuth($serverKey, '')
            ->post("{$baseUrl}/snap/v1/transactions", $payload);

        if ($response->successful()) {
            return $response->json('token');
        }

        Log::error('Midtrans Snap error', ['body' => $response->body()]);
        return null;
    }

    private function formatOrder(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_code' => $order->order_code,
            'layanan_name' => $order->layanan?->nama,
            'quantity' => $order->quantity,
            'subtotal' => (float) $order->subtotal,
            'delivery_method' => $order->delivery_method,
            'delivery_label' => Order::DELIVERY_LABELS[$order->delivery_method] ?? $order->delivery_method,
            'delivery_fee' => (float) $order->delivery_fee,
            'app_tax' => (float) $order->app_tax,
            'total' => (float) $order->total,
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'customer_address' => $order->customer_address,
            'notes' => $order->notes,
            'payment_status' => $order->payment_status,
            'order_status' => $order->order_status,
            'is_seen_by_umkm' => $order->is_seen_by_umkm,
            'created_at' => $order->created_at?->format('d M Y H:i'),
            'user_name' => $order->user?->name,
        ];
    }
}
