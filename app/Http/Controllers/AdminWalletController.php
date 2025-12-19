<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WalletTransaction;

class AdminWalletController extends Controller
{
    public function index(Request $request)
    {
        $query = WalletTransaction::with('umkm')->orderBy('created_at', 'desc');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('transaction_code', 'like', "%$search%")
                  ->orWhereHas('umkm', function($subQ) use ($search) {
                      $subQ->where('nama', 'like', "%$search%");
                  });
            });
        }

        $transactions = $query->get();
        return view('admin.wallet.index', compact('transactions'));
    }

    public function approve(Request $request, WalletTransaction $transaction)
    {
        if ($transaction->status !== 'pending') return back()->with('error', 'Transaksi sudah diproses.');

        if ($transaction->type === 'topup') {
            $transaction->umkm->increment('saldo', $transaction->amount);
        }
        // If type is withdrawal, balance was already deducted on request.

        $transaction->update(['status' => 'approved']);

        return back()->with('success', 'Transaksi berhasil disetujui.');
    }

    public function reject(Request $request, WalletTransaction $transaction)
    {
        if ($transaction->status !== 'pending') return back()->with('error', 'Transaksi sudah diproses.');
        
        $request->validate(['admin_note' => 'required|string']);

        if ($transaction->type === 'withdrawal') {
            // Refund balance
            $transaction->umkm->increment('saldo', $transaction->amount);
        }

        $transaction->update([
            'status' => 'rejected',
            'admin_note' => $request->admin_note
        ]);

        return back()->with('success', 'Transaksi ditolak.');
    }
}
