<?php

namespace App\Http\Controllers;

use App\Models\SaldoUmkm;
use App\Models\WithdrawRequest;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class WithdrawController extends Controller
{
    /**
     * Ambil data saldo UMKM yang sedang login.
     */
    public function getSaldo()
    {
        $umkm = Auth::user()->umkm;
        if (!$umkm) {
            return response()->json(['success' => false, 'message' => 'UMKM tidak ditemukan'], 404);
        }

        // Hitung langsung dari data real (agar selalu akurat termasuk order lama)
        $totalPemasukan = (float) Order::where('umkm_id', $umkm->id)
            ->where('payment_status', 'paid')
            ->sum('subtotal');

        $totalWithdraw = (float) WithdrawRequest::where('umkm_id', $umkm->id)
            ->where('status', 'approved')
            ->sum('jumlah');

        $saldoTersedia = \App\Models\SaldoMutation::computeCurrentBalance($umkm->id);

        $pemasukanHariIni = (float) Order::where('umkm_id', $umkm->id)
            ->where('payment_status', 'paid')
            ->whereDate('updated_at', today())
            ->sum('subtotal');

        return response()->json([
            'success' => true,
            'saldo' => [
                'saldo_tersedia'      => $saldoTersedia,
                'total_pemasukan'     => $totalPemasukan,
                'total_withdraw'      => $totalWithdraw,
                'pemasukan_hari_ini'  => $pemasukanHariIni,
            ],
        ]);
    }

    /**
     * UMKM mengajukan request penarikan dana.
     */
    public function requestWithdraw(Request $request)
    {
        $umkm = Auth::user()->umkm;
        if (!$umkm) {
            return response()->json(['success' => false, 'message' => 'UMKM tidak ditemukan'], 404);
        }

        $validated = $request->validate([
            'jumlah'         => 'required|numeric|min:50000',
            'rekening_bank'  => 'required|string|max:100',
            'nomor_rekening' => 'required|string|max:50',
            'nama_pemilik'   => 'required|string|max:255',
        ], [
            'jumlah.min' => 'Minimum penarikan adalah Rp 50.000',
        ]);

        // Hitung saldo tersedia secara dinamis dari orders - approved withdrawals - admin deductions - refunds
        $saldoTersedia = \App\Models\SaldoMutation::computeCurrentBalance($umkm->id);

        if ($saldoTersedia < $validated['jumlah']) {
            return response()->json([
                'success' => false,
                'message' => 'Saldo tidak mencukupi. Saldo tersedia: Rp ' . number_format($saldoTersedia, 0, ',', '.')
            ], 422);
        }

        // Cek tidak ada WD pending
        $pendingExists = WithdrawRequest::where('umkm_id', $umkm->id)
            ->where('status', 'pending')
            ->exists();

        if ($pendingExists) {
            return response()->json([
                'success' => false,
                'message' => 'Anda masih memiliki permintaan penarikan yang sedang diproses. Tunggu sampai selesai.'
            ], 422);
        }

        $wd = WithdrawRequest::create([
            'umkm_id'         => $umkm->id,
            'jumlah'          => $validated['jumlah'],
            'rekening_bank'   => $validated['rekening_bank'],
            'nomor_rekening'  => $validated['nomor_rekening'],
            'nama_pemilik'    => $validated['nama_pemilik'],
            'status'          => 'pending',
            'is_seen_by_admin'=> false,
            'is_seen_by_umkm' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permintaan penarikan berhasil diajukan. Admin akan memproses segera.',
            'withdraw' => $this->formatWithdraw($wd),
        ]);
    }

    /**
     * List history WD milik UMKM yang sedang login.
     */
    public function myWithdraws()
    {
        $umkm = Auth::user()->umkm;
        if (!$umkm) {
            return response()->json(['success' => false, 'message' => 'UMKM tidak ditemukan'], 404);
        }

        $withdraws = WithdrawRequest::where('umkm_id', $umkm->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($w) => $this->formatWithdraw($w));

        // Mark as seen by umkm
        WithdrawRequest::where('umkm_id', $umkm->id)
            ->where('is_seen_by_umkm', false)
            ->update(['is_seen_by_umkm' => true]);

        return response()->json(['success' => true, 'withdraws' => $withdraws]);
    }

    /**
     * Jumlah WD yang belum dilihat UMKM (untuk notifikasi badge).
     */
    public function unreadCount()
    {
        $umkm = Auth::user()->umkm;
        if (!$umkm) {
            return response()->json(['count' => 0]);
        }

        $count = WithdrawRequest::where('umkm_id', $umkm->id)
            ->where('is_seen_by_umkm', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Format WD untuk response JSON.
     */
    private function formatWithdraw(WithdrawRequest $wd): array
    {
        return [
            'id'             => $wd->id,
            'jumlah'         => (float) $wd->jumlah,
            'rekening_bank'  => $wd->rekening_bank,
            'nomor_rekening' => $wd->nomor_rekening,
            'nama_pemilik'   => $wd->nama_pemilik,
            'status'         => $wd->status,
            'status_label'   => $wd->status_label,
            'admin_note'     => $wd->admin_note,
            'bukti_transfer' => $wd->bukti_transfer ? asset('storage/' . $wd->bukti_transfer) : null,
            'processed_at'   => $wd->processed_at?->format('d M Y H:i'),
            'created_at'     => $wd->created_at->format('d M Y H:i'),
        ];
    }
}
