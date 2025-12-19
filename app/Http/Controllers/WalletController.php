<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class WalletController extends Controller
{
    public function index()
    {
        $umkm = Auth::user()->umkm;
        if(!$umkm) return redirect()->route('umkm.create');

        $transactions = $umkm->walletTransactions()->orderBy('created_at', 'desc')->get();
        return view('umkm.wallet.index', compact('umkm', 'transactions'));
    }

    public function topup()
    {
        return view('umkm.wallet.topup');
    }

    public function storeTopup(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10000',
            'proof' => 'required|image|max:2048', // 2MB
        ]);

        $umkm = Auth::user()->umkm;
        $path = $request->file('proof')->store('payment_proofs', 'public');

        WalletTransaction::create([
            'transaction_code' => 'TRX-' . time() . rand(100, 999), 
            'umkm_id' => $umkm->id,
            'type' => 'topup',
            'amount' => $request->amount,
            'status' => 'pending',
            'proof_path' => $path,
        ]);

        return redirect()->route('umkm.wallet.index')->with('success', 'Permintaan Top-up berhasil dikirim. Menunggu verifikasi admin.');
    }

    public function withdraw()
    {
        return view('umkm.wallet.withdraw');
    }

    public function storeWithdraw(Request $request)
    {
        $umkm = Auth::user()->umkm;
        
        $request->validate([
            'amount' => 'required|numeric|min:10000|max:' . $umkm->saldo,
            'bank_name' => 'required|string',
            'account_number' => 'required|string',
            'account_name' => 'required|string',
        ]);

        $bankInfo = json_encode([
            'bank_name' => $request->bank_name,
            'account_number' => $request->account_number,
            'account_name' => $request->account_name,
        ]);

        WalletTransaction::create([
            'transaction_code' => 'TRX-' . time() . rand(100, 999),
            'umkm_id' => $umkm->id,
            'type' => 'withdrawal',
            'amount' => $request->amount,
            'status' => 'pending',
            'bank_info' => $bankInfo,
        ]);

        // Optional: Deduct balance immediately or hold it. 
        // Logic: Usually we deduct immediately to prevent double spending, and refund if rejected.
        $umkm->decrement('saldo', $request->amount);

        return redirect()->route('umkm.wallet.index')->with('success', 'Permintaan Penarikan berhasil dikirim.');
    }
}
