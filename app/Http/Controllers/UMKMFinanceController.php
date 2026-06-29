<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\SaldoMutation;
use App\Models\WithdrawRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UMKMFinanceController extends Controller
{
    /**
     * Summary: saldo, pemasukan kotor/bersih, pengeluaran, total WD.
     */
    public function financeSummary()
    {
        $umkm = Auth::user()->umkm;
        if (!$umkm) {
            return response()->json(['success' => false, 'message' => 'UMKM tidak ditemukan'], 404);
        }

        // Pemasukan kotor = subtotal + app_tax + qris_tax
        $pemasukanKotor = (float) Order::where('umkm_id', $umkm->id)
            ->where('payment_status', 'paid')
            ->selectRaw('SUM(subtotal + app_tax + qris_tax) as total')
            ->value('total') ?? 0;

        // Biaya platform
        $biayaPlatform = (float) Order::where('umkm_id', $umkm->id)
            ->where('payment_status', 'paid')
            ->selectRaw('SUM(app_tax + qris_tax) as total')
            ->value('total') ?? 0;

        // Pemasukan bersih = subtotal saja
        $pemasukanBersih = (float) Order::where('umkm_id', $umkm->id)
            ->where('payment_status', 'paid')
            ->sum('subtotal');

        // Total WD approved
        $totalWdApproved = (float) WithdrawRequest::where('umkm_id', $umkm->id)
            ->where('status', 'approved')
            ->sum('jumlah');

        // Total potongan admin
        $totalDeductions = (float) SaldoMutation::where('umkm_id', $umkm->id)
            ->where('category', 'admin_deduction')
            ->sum('amount');

        // Total refunds
        $totalRefunds = (float) SaldoMutation::where('umkm_id', $umkm->id)
            ->where('category', 'refund')
            ->sum('amount');

        // Saldo tersedia (net - wd - deductions - refunds)
        $saldoTersedia = $pemasukanBersih - $totalWdApproved - $totalDeductions - $totalRefunds;

        // Pemasukan hari ini (kotor)
        $pemasukanHariIni = (float) Order::where('umkm_id', $umkm->id)
            ->where('payment_status', 'paid')
            ->whereDate('updated_at', today())
            ->selectRaw('SUM(subtotal + app_tax + qris_tax) as total')
            ->value('total') ?? 0;

        // Total transaksi
        $totalTransaksi = Order::where('umkm_id', $umkm->id)
            ->where('payment_status', 'paid')
            ->count();

        return response()->json([
            'success' => true,
            'summary' => [
                'pemasukan_kotor'    => $pemasukanKotor,
                'biaya_platform'     => $biayaPlatform,
                'pemasukan_bersih'   => $pemasukanBersih,
                'total_wd_approved'  => $totalWdApproved,
                'total_deductions'   => $totalDeductions,
                'saldo_tersedia'     => $saldoTersedia,
                'pemasukan_hari_ini' => $pemasukanHariIni,
                'total_transaksi'    => $totalTransaksi,
            ],
        ]);
    }

    /**
     * History all saldo mutations for this UMKM.
     */
    public function financeHistory(Request $request)
    {
        $umkm = Auth::user()->umkm;
        if (!$umkm) {
            return response()->json(['success' => false, 'message' => 'UMKM tidak ditemukan'], 404);
        }

        $query = SaldoMutation::where('umkm_id', $umkm->id)
            ->with(['order', 'withdraw', 'report'])
            ->orderByDesc('created_at');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $mutations = $query->paginate(20);

        return response()->json([
            'success'   => true,
            'mutations' => $mutations->map(fn($m) => $this->formatMutation($m)),
            'total'     => $mutations->total(),
            'per_page'  => $mutations->perPage(),
            'page'      => $mutations->currentPage(),
            'last_page' => $mutations->lastPage(),
        ]);
    }

    /**
     * Full history of paid orders for this UMKM (riwayat transaksi online).
     */
    public function orderHistory(Request $request)
    {
        $umkm = Auth::user()->umkm;
        if (!$umkm) {
            return response()->json(['success' => false, 'message' => 'UMKM tidak ditemukan'], 404);
        }

        $orders = Order::where('umkm_id', $umkm->id)
            ->where('payment_status', 'paid')
            ->with(['user', 'layanan'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'orders'  => $orders->map(fn($o) => [
                'id'            => $o->id,
                'order_code'    => $o->order_code,
                'customer'      => $o->customer_name,
                'layanan_name'  => $o->layanan?->nama,
                'quantity'      => $o->quantity,
                'subtotal'      => (float) $o->subtotal,
                'app_tax'       => (float) $o->app_tax,
                'qris_tax'      => (float) $o->qris_tax,
                'delivery_fee'  => (float) $o->delivery_fee,
                'total'         => (float) $o->total,
                'pemasukan_kotor'  => (float) $o->subtotal + (float) $o->app_tax + (float) $o->qris_tax,
                'biaya_platform'   => (float) $o->app_tax + (float) $o->qris_tax,
                'pemasukan_bersih' => (float) $o->subtotal,
                'payment_method'=> $o->payment_method,
                'order_status'  => $o->order_status,
                'date'          => $o->created_at->format('d M Y H:i'),
            ]),
        ]);
    }

    private function formatMutation(SaldoMutation $m): array
    {
        return [
            'id'             => $m->id,
            'type'           => $m->type,
            'category'       => $m->category,
            'category_label' => $m->category_label,
            'amount'         => (float) $m->amount,
            'description'    => $m->description,
            'balance_after'  => (float) $m->balance_after,
            'order_code'     => $m->order?->order_code,
            'date'           => $m->created_at->format('d M Y H:i'),
        ];
    }
}
