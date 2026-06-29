<?php

namespace App\Http\Controllers;

use App\Models\Layanan;
use App\Models\Order;
use App\Models\UMKM;
use App\Models\Keuntungan;
use App\Models\SaldoUmkm;
use App\Models\SaldoMutation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Services\MediaCompressionService;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'layanan_id' => 'required|exists:layanan,id',
            'umkm_id' => 'required|exists:umkm,id',
            'quantity' => 'required|integer|min:1|max:99',
            'delivery_method' => 'required|in:gojek,grab,umkm_go',
            'payment_method' => 'nullable|in:midtrans,qris',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_address' => 'required|string|max:1000',
            'notes' => 'nullable|string|max:500',
            'user_lat' => 'nullable|numeric',
            'user_lng' => 'nullable|numeric',
        ]);

        $layanan = Layanan::findOrFail($validated['layanan_id']);
        $umkm = UMKM::findOrFail($validated['umkm_id']);

        if (!$layanan->umkm()->where('umkm_id', $umkm->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'Layanan tidak tersedia di UMKM ini'], 422);
        }

        $paymentMethod = $validated['payment_method'] ?? 'qris'; // default to qris now

        // Calculate distance
        $distance = 5.5; // fallback
        if (isset($validated['user_lat']) && isset($validated['user_lng']) && $umkm->latitude && $umkm->longitude) {
            $distance = $this->calculateDistance(
                (float) $validated['user_lat'],
                (float) $validated['user_lng'],
                (float) $umkm->latitude,
                (float) $umkm->longitude
            );
        }

        // Validate Gojek <= 10 km
        if ($validated['delivery_method'] === 'gojek' && $distance > 10) {
            return response()->json([
                'success' => false,
                'message' => 'Layanan Gojek hanya tersedia untuk jarak terdekat di bawah 10 km. Jarak Anda: ' . round($distance, 2) . ' km.'
            ], 422);
        }

        $pricing = Order::calculatePricing(
            (float) $layanan->price,
            $validated['quantity'],
            $validated['delivery_method'],
            $paymentMethod,
            $distance,
            (float) ($layanan->weight ?? 1.0),
            (float) ($layanan->height ?? 10.0)
        );

        $orderCode = 'ORD-' . strtoupper(Str::random(8));

        $uniqueCode = 0;
        $total = $pricing['total'];
        if ($paymentMethod === 'qris') {
            $uniqueCode = rand(11, 999);
            $total += $uniqueCode;
        }

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
            'qris_tax' => $pricing['qrisTax'],
            'unique_code' => $uniqueCode,
            'total' => $total,
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'customer_address' => $validated['customer_address'],
            'notes' => $validated['notes'] ?? null,
            'payment_status' => 'pending',
            'payment_method' => $paymentMethod,
            'order_status' => 'pending',
            'midtrans_order_id' => $orderCode,
            'is_seen_by_umkm' => false,
        ]);

        return response()->json([
            'success' => true,
            'order' => $this->formatOrderForUser($order->fresh(['layanan', 'umkm'])),
            'message' => 'Pesanan berhasil dibuat, silakan lakukan pembayaran QRIS.'
        ]);
    }

    public function confirmPayment($id)
    {
        $order = Order::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $order->update([
            'payment_status' => 'paid',
            'order_status' => 'confirmed',
        ]);

        // ======================================================
        // Integrasi: otomatis catat pendapatan ke tabel keuntungan
        // Gross = subtotal + app_tax + qris_tax (yang user bayarkan ke UMKM, diluar ongkir)
        // Pengeluaran = app_tax + qris_tax (biaya platform)
        // Bersih = subtotal (harga produk)
        // ======================================================
        try {
            $bulan = now()->format('F Y');

            $pendapatanKotor = (float) $order->subtotal + (float) $order->app_tax + (float) $order->qris_tax;
            $biayaPlatform = (float) $order->app_tax + (float) $order->qris_tax;
            $pendapatanBersih = (float) $order->subtotal;

            $keuntungan = Keuntungan::where('umkm_id', $order->umkm_id)
                ->where('bulan', $bulan)
                ->first();

            if ($keuntungan) {
                $newPendapatan = $keuntungan->pendapatan + $pendapatanKotor;
                $newPengeluaran = $keuntungan->pengeluaran + $biayaPlatform;
                $keuntungan->update([
                    'pendapatan' => $newPendapatan,
                    'pengeluaran' => $newPengeluaran,
                    'keuntungan_bersih' => $newPendapatan - $newPengeluaran,
                    'jumlah_transaksi' => $keuntungan->jumlah_transaksi + 1,
                ]);
            } else {
                Keuntungan::create([
                    'umkm_id' => $order->umkm_id,
                    'bulan' => $bulan,
                    'pendapatan' => $pendapatanKotor,
                    'pengeluaran' => $biayaPlatform,
                    'keuntungan_bersih' => $pendapatanBersih,
                    'jumlah_transaksi' => 1,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Gagal update keuntungan dari order: ' . $e->getMessage());
        }

        // ======================================================
        // Integrasi: catat mutasi saldo (riwayat keuangan UMKM)
        // ======================================================
        try {
            SaldoMutation::recordOrderIncome($order->umkm_id, $order);
        } catch (\Exception $e) {
            Log::error('Gagal catat mutasi saldo UMKM dari order: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran QRIS berhasil dikonfirmasi!',
            'order' => $this->formatOrder($order->fresh(['layanan']))
        ]);
    }

    public function userCancel($id)
    {
        $order = Order::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // User can only cancel if status is 'pending' or 'confirmed'
        if (!in_array($order->order_status, ['pending', 'confirmed'])) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak dapat dibatalkan karena sudah dalam proses penyiapan/pengantaran.'
            ], 422);
        }

        $order->update([
            'order_status' => 'cancelled',
            'payment_status' => $order->payment_status === 'paid' ? 'cancelled' : $order->payment_status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil dibatalkan.',
            'order' => $this->formatOrderForUser($order->fresh(['layanan', 'umkm']))
        ]);
    }

    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
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

        // Jika transaksi berhasil dibayar (lunas), catat ke keuntungan & mutasi saldo
        if ($order->payment_status === 'paid') {
            try {
                $bulan = now()->format('F Y');

                $pendapatanKotor = (float) $order->subtotal + (float) $order->app_tax + (float) $order->qris_tax;
                $biayaPlatform = (float) $order->app_tax + (float) $order->qris_tax;
                $pendapatanBersih = (float) $order->subtotal;

                $keuntungan = Keuntungan::where('umkm_id', $order->umkm_id)
                    ->where('bulan', $bulan)
                    ->first();

                if ($keuntungan) {
                    $newPendapatan = $keuntungan->pendapatan + $pendapatanKotor;
                    $newPengeluaran = $keuntungan->pengeluaran + $biayaPlatform;
                    $keuntungan->update([
                        'pendapatan' => $newPendapatan,
                        'pengeluaran' => $newPengeluaran,
                        'keuntungan_bersih' => $newPendapatan - $newPengeluaran,
                        'jumlah_transaksi' => $keuntungan->jumlah_transaksi + 1,
                    ]);
                } else {
                    Keuntungan::create([
                        'umkm_id' => $order->umkm_id,
                        'bulan' => $bulan,
                        'pendapatan' => $pendapatanKotor,
                        'pengeluaran' => $biayaPlatform,
                        'keuntungan_bersih' => $pendapatanBersih,
                        'jumlah_transaksi' => 1,
                    ]);
                }

                // Catat mutasi saldo
                SaldoMutation::recordOrderIncome($order->umkm_id, $order);

            } catch (\Exception $e) {
                Log::error('Gagal update keuntungan via midtrans callback: ' . $e->getMessage());
            }
        }

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

    /**
     * User: List history pesanan milik user yang sedang login.
     */
    public function userHistory()
    {
        $orders = Order::with(['layanan', 'umkm', 'rating'])
            ->where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($o) => $this->formatOrderForUser($o));

        return response()->json(['orders' => $orders]);
    }

    /**
     * User: Detail 1 pesanan (termasuk chat count unread).
     */
    public function userOrderDetail($id)
    {
        $order = Order::with(['layanan', 'umkm', 'rating'])
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $unreadCount = $order->chats()
            ->where('sender_type', 'umkm')
            ->where('is_read', false)
            ->count();

        $formatted = $this->formatOrderForUser($order);
        $formatted['unread_chat_count'] = $unreadCount;

        return response()->json(['order' => $formatted]);
    }

    /**
     * User: Halaman history pesanan (blade view).
     */
    public function userOrdersPage()
    {
        return view('user.orders');
    }

    /**
     * User: Halaman detail pesanan (blade view).
     */
    public function userOrderDetailPage($id)
    {
        $order = Order::with(['layanan', 'umkm', 'rating'])
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('user.order-detail', ['order' => $order]);
    }

    /**
     * UMKM: Isi informasi driver (nama, telepon, kode).
     */
    public function updateDriverInfo(Request $request, $id)
    {
        $umkm = Auth::user()->umkm;
        $order = Order::where('id', $id)->where('umkm_id', $umkm?->id)->firstOrFail();

        $validated = $request->validate([
            'driver_name' => 'required|string|max:255',
            'driver_phone' => 'nullable|string|max:20',
            'driver_code' => 'nullable|string|max:100',
            'driver_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('driver_photo')) {
            if ($order->driver_photo && \Storage::disk('public')->exists($order->driver_photo)) {
                \Storage::disk('public')->delete($order->driver_photo);
            }
            $validated['driver_photo'] = MediaCompressionService::compressAndStoreImage($request->file('driver_photo'), 'driver-photos');
        }

        $order->update($validated);

        return response()->json(['success' => true, 'order' => $this->formatOrder($order->fresh(['layanan', 'user']))]);
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
            ->map(fn($o) => $this->formatOrder($o));

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
            ->map(fn($o) => $this->formatOrder($o));

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
        $order = Order::where('id', $id)
            ->where('umkm_id', $umkm?->id)
            ->firstOrFail();

        $validated = $request->validate([
            'order_status' => 'required|in:confirmed,processing,delivered,cancelled',
        ]);

        // WAJIB isi driver sebelum pesanan selesai
        if (
            $validated['order_status'] === 'delivered' &&
            (
                empty($order->driver_name) ||
                empty($order->driver_phone)
            )
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan isi data driver terlebih dahulu sebelum menyelesaikan pesanan.'
            ], 422);
        }

        $order->update([
            'order_status' => $validated['order_status'],
            'is_seen_by_umkm' => true
        ]);

        return response()->json([
            'success' => true,
            'order' => $this->formatOrder($order->fresh(['layanan', 'user']))
        ]);
    }

    public function pricingConfig()
    {
        return response()->json([
            'app_tax_rate' => Order::APP_TAX_RATE,
            'qris_tax' => Order::QRIS_TAX,
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

        $itemDetails = [
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
        ];

        if ($order->qris_tax > 0) {
            $itemDetails[] = [
                'id' => 'qris_tax',
                'price' => (int) round($order->qris_tax),
                'quantity' => 1,
                'name' => 'Pajak QRIS',
            ];
        }

        $payload = [
            'transaction_details' => [
                'order_id' => $order->order_code,
                'gross_amount' => (int) round($order->total),
            ],
            'item_details' => $itemDetails,
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

        // If QRIS only, restrict to QRIS payment method
        if ($order->payment_method === 'qris') {
            $payload['enabled_payments'] = ['qris'];
        }

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
            'qris_tax' => (float) $order->qris_tax,
            'unique_code' => (int) $order->unique_code,
            'dynamic_qris' => $order->payment_method === 'qris' ? \App\Services\QrisService::generateDynamic((float) $order->total, $order->order_code) : null,
            'total' => (float) $order->total,
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'customer_address' => $order->customer_address,
            'notes' => $order->notes,
            'payment_status' => $order->payment_status,
            'payment_method' => $order->payment_method,
            'order_status' => $order->order_status,
            'is_seen_by_umkm' => $order->is_seen_by_umkm,
            'driver_name' => $order->driver_name,
            'driver_phone' => $order->driver_phone,
            'driver_code' => $order->driver_code,
            'driver_photo' => $order->driver_photo ? asset('storage/' . $order->driver_photo) : null,
            'created_at' => $order->created_at?->format('d M Y H:i'),
            'user_name' => $order->user?->name,
            'unread_chat_count' => $order->chats()->where('sender_type', 'user')->where('is_read', false)->count(),
        ];
    }

    private function formatOrderForUser(Order $order): array
    {
        $formatted = $this->formatOrder($order);
        $formatted['umkm_name'] = $order->umkm?->nama;
        $formatted['layanan_photo'] = $order->layanan?->photo_path
            ? asset('storage/' . $order->layanan->photo_path)
            : null;
        $formatted['has_rating'] = $order->rating !== null;
        $formatted['rating'] = $order->rating ? [
            'rating' => $order->rating->rating,
            'review' => $order->rating->review,
        ] : null;
        $formatted['unread_chat_count'] = $order->chats()->where('sender_type', 'umkm')->where('is_read', false)->count();
        return $formatted;
    }
}
