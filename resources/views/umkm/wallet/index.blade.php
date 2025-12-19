@extends('layouts.app')

@section('title', 'Dompet UMKM')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-[#218689] to-[#009b97] rounded-xl shadow-lg p-6 text-white mb-6">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <p class="text-white/80 text-sm mb-1">Saldo Saat Ini</p>
                <h1 class="text-4xl font-bold">Rp {{ number_format($umkm->saldo, 0, ',', '.') }}</h1>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('umkm.wallet.topup') }}" class="bg-white text-[#218689] px-6 py-2 rounded-lg font-bold hover:bg-gray-100 transition-colors shadow-md flex items-center gap-2">
                    <i class="fas fa-plus-circle"></i> Isi Saldo
                </a>
                <a href="{{ route('umkm.wallet.withdraw') }}" class="bg-white/20 text-white px-6 py-2 rounded-lg font-bold hover:bg-white/30 transition-colors flex items-center gap-2">
                    <i class="fas fa-arrow-circle-down"></i> Tarik Dana
                </a>
            </div>
        </div>
    </div>

    <!-- History -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-history text-gray-500"></i> Riwayat Transaksi
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 font-semibold text-gray-600 text-sm">Tanggal</th>
                        <th class="px-6 py-4 font-semibold text-gray-600 text-sm">Kode TRX</th>
                        <th class="px-6 py-4 font-semibold text-gray-600 text-sm">Tipe</th>
                        <th class="px-6 py-4 font-semibold text-gray-600 text-sm">Jumlah</th>
                        <th class="px-6 py-4 font-semibold text-gray-600 text-sm">Status</th>
                        <th class="px-6 py-4 font-semibold text-gray-600 text-sm">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($transactions as $trx)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $trx->created_at->format('d M Y H:i') }}</td>
                        <td class="px-6 py-4 text-sm font-mono font-bold text-gray-800">{{ $trx->transaction_code ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm">
                            @if($trx->type == 'topup')
                                <span class="bg-green-100 text-green-700 px-2 py-1 rounded-md text-xs font-semibold uppercase">Top Up</span>
                            @else
                                <span class="bg-red-100 text-red-700 px-2 py-1 rounded-md text-xs font-semibold uppercase">Penarikan</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm font-bold {{ $trx->type == 'topup' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $trx->type == 'topup' ? '+' : '-' }} Rp {{ number_format($trx->amount, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @if($trx->status == 'approved')
                                <span class="text-green-600 font-semibold"><i class="fas fa-check-circle"></i> Berhasil</span>
                            @elseif($trx->status == 'pending')
                                <span class="text-yellow-600 font-semibold"><i class="fas fa-clock"></i> Memproses</span>
                            @else
                                <span class="text-red-600 font-semibold"><i class="fas fa-times-circle"></i> Ditolak</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            @if($trx->admin_note)
                                <span class="text-red-500 text-xs italic">{{ $trx->admin_note }}</span>
                            @elseif($trx->type == 'withdrawal')
                                @php $bank = json_decode($trx->bank_info) @endphp
                                <span class="text-xs text-gray-400">Ke: {{ $bank->bank_name ?? '-' }}</span>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                            <i class="fas fa-receipt text-4xl mb-3 block opacity-20"></i>
                            Belum ada transaksi
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
