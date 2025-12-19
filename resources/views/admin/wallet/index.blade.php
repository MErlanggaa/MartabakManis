@extends('layouts.app')

@section('title', 'Dompet UMKM')

@section('content')
<div class="container px-6 mx-auto grid">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-700">Manajemen Dompet UMKM</h2>
        <form action="{{ route('admin.wallet.index') }}" method="GET" class="flex gap-2">
            <input type="text" name="search" placeholder="Cari Kode Transaksi / UMKM..." value="{{ request('search') }}" 
                   class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>

    <!-- Table -->
    <div class="w-full overflow-hidden rounded-lg shadow-xs">
        <div class="w-full overflow-x-auto">
            <table class="w-full whitespace-no-wrap">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b bg-gray-50">
                        <th class="px-4 py-3">Kode Transaksi</th>
                        <th class="px-4 py-3">UMKM</th>
                        <th class="px-4 py-3">Tipe</th>
                        <th class="px-4 py-3">Jumlah</th>
                        <th class="px-4 py-3">Bukti / Info Bank</th>
                        <th class="px-4 py-3">Waktu</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y">
                    @forelse($transactions as $trx)
                    <tr class="text-gray-700">
                        <td class="px-4 py-3 text-sm font-mono font-bold">
                            {{ $trx->transaction_code ?? '-' }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center text-sm">
                                <div>
                                    <p class="font-semibold">{{ $trx->umkm->nama ?? 'Unknown' }}</p>
                                    <p class="text-xs text-gray-600">{{ $trx->umkm->user->email ?? '' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            @if($trx->type == 'topup')
                                <span class="px-2 py-1 font-semibold leading-tight text-green-700 bg-green-100 rounded-full">Top Up</span>
                            @else
                                <span class="px-2 py-1 font-semibold leading-tight text-red-700 bg-red-100 rounded-full">Penarikan</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm font-bold">
                            Rp {{ number_format($trx->amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm">
                            @if($trx->type == 'topup')
                                @if($trx->proof_path)
                                    <button onclick="showProof('{{ Storage::url($trx->proof_path) }}')" class="text-blue-600 hover:underline">Lihat Bukti</button>
                                @else
                                    <span class="text-red-500">Tidak ada bukti</span>
                                @endif
                            @else
                                @php $bank = json_decode($trx->bank_info) @endphp
                                <div class="text-xs">
                                    <p class="font-bold">{{ $bank->bank_name ?? '-' }}</p>
                                    <p>{{ $bank->account_number ?? '-' }}</p>
                                    <p>{{ $bank->account_name ?? '-' }}</p>
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm">
                            {{ $trx->created_at->format('d M Y H:i') }}
                        </td>
                        <td class="px-4 py-3 text-xs">
                             @if($trx->status == 'approved')
                                <span class="px-2 py-1 font-semibold leading-tight text-green-700 bg-green-100 rounded-full">Disetujui</span>
                            @elseif($trx->status == 'pending')
                                <span class="px-2 py-1 font-semibold leading-tight text-yellow-700 bg-yellow-100 rounded-full">Menunggu</span>
                            @else
                                <span class="px-2 py-1 font-semibold leading-tight text-red-700 bg-red-100 rounded-full">Ditolak</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center space-x-2 text-sm">
                                @if($trx->status == 'pending')
                                    <form action="{{ route('admin.wallet.approve', $trx->id) }}" method="POST" onsubmit="return confirm('Setujui transaksi ini?')">
                                        @csrf
                                        <button type="submit" class="flex items-center justify-center px-2 py-1 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-green-600 border border-transparent rounded-lg active:bg-green-600 hover:bg-green-700 focus:outline-none focus:shadow-outline-green" title="Setujui">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    <button onclick="openRejectModal({{ $trx->id }})" class="flex items-center justify-center px-2 py-1 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-red-600 border border-transparent rounded-lg active:bg-red-600 hover:bg-red-700 focus:outline-none focus:shadow-outline-red" title="Tolak">
                                        <i class="fas fa-times"></i>
                                    </button>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-3 text-center text-gray-500">Tidak ada data transaksi</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Proof Modal -->
<div id="proofModal" class="fixed inset-0 z-50 flex items-center justify-center overflow-auto bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg p-4 max-w-2xl w-full mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-lg">Bukti Transfer</h3>
            <button onclick="document.getElementById('proofModal').classList.add('hidden')" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times"></i></button>
        </div>
        <img id="proofImage" src="" class="w-full h-auto rounded-lg">
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="fixed inset-0 z-50 flex items-center justify-center overflow-auto bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="font-bold text-lg mb-4">Tolak Transaksi</h3>
        <form id="rejectForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-700 mb-2">Alasan Penolakan</label>
                <textarea name="admin_note" rows="3" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('rejectModal').classList.add('hidden')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg">Batal</button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Tolak</button>
            </div>
        </form>
    </div>
</div>

<script>
    function showProof(url) {
        document.getElementById('proofImage').src = url;
        document.getElementById('proofModal').classList.remove('hidden');
    }

    function openRejectModal(id) {
        let form = document.getElementById('rejectForm');
        form.action = '/admin/wallet/' + id + '/reject';
        document.getElementById('rejectModal').classList.remove('hidden');
    }
</script>
@endsection
