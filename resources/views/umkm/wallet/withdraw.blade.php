@extends('layouts.app')

@section('title', 'Tarik Dana')

@section('content')
<div class="max-w-xl mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('umkm.wallet.index') }}" class="text-gray-500 hover:text-[#218689] flex items-center gap-2 transition-colors">
            <i class="fas fa-arrow-left"></i> Kembali ke Dompet
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="bg-gradient-to-r from-orange-500 to-red-500 p-6 text-center text-white">
            <h1 class="text-2xl font-bold mb-2">Tarik Dana</h1>
            <p class="opacity-90 text-sm">Transfer saldo ke rekening bank Anda</p>
        </div>

        <div class="p-8">
            <div class="bg-gray-50 p-4 rounded-lg mb-6 flex justify-between items-center border border-gray-200">
                <span class="text-gray-600 text-sm">Saldo Tersedia:</span>
                <span class="font-bold text-gray-900 text-lg">Rp {{ number_format(auth()->user()->umkm->saldo, 0, ',', '.') }}</span>
            </div>

            <form action="{{ route('umkm.wallet.storeWithdraw') }}" method="POST" class="space-y-5">
                @csrf
                
                <!-- Bank Info -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Nama Bank</label>
                        <select name="bank_name" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <option value="">Pilih Bank</option>
                            <option value="BCA">BCA</option>
                            <option value="BRI">BRI</option>
                            <option value="BNI">BNI</option>
                            <option value="Mandiri">Mandiri</option>
                            <option value="BSI">BSI</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Nomor Rekening</label>
                        <input type="number" name="account_number" required placeholder="Contoh: 1234567890" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Atas Nama</label>
                        <input type="text" name="account_name" required placeholder="Nama Pemilik Rekening" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                </div>

                <hr class="border-gray-100">

                <!-- Amount -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Jumlah Penarikan</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span class="text-gray-500 font-bold">Rp</span>
                        </div>
                        <input type="number" id="withdrawAmount" name="amount" min="10000" max="{{ auth()->user()->umkm->saldo }}" step="1" required 
                               class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 font-bold text-lg text-gray-800" 
                               placeholder="0" oninput="checkBalance()">
                    </div>
                    <div class="flex justify-between items-center mt-2">
                        <p class="text-xs text-gray-400">Minimal Rp 10.000</p>
                        <p class="text-xs font-semibold text-gray-600" id="balanceHelper">
                            Saldo Tersedia: <span class="text-green-600">Rp {{ number_format(auth()->user()->umkm->saldo, 0, ',', '.') }}</span>
                        </p>
                    </div>
                    <p id="errorMsg" class="text-xs text-red-500 mt-1 hidden font-bold"><i class="fas fa-times-circle"></i> Saldo tidak mencukupi!</p>
                </div>

                <div class="pt-4">
                    <button type="submit" id="submitBtn" class="w-full bg-orange-600 hover:bg-orange-700 text-white font-bold py-3 rounded-lg shadow-md transition-transform transform hover:scale-[1.02]">
                        <i class="fas fa-money-bill-wave mr-2"></i> Ajukan Penarikan
                    </button>
                    <p class="text-center text-xs text-gray-500 mt-4">
                        Proses transfer membutukan waktu 1-3 hari kerja setelah disetujui Admin.
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function checkBalance() {
        const input = document.getElementById('withdrawAmount');
        const submitBtn = document.getElementById('submitBtn');
        const errorMsg = document.getElementById('errorMsg');
        const balanceHelper = document.getElementById('balanceHelper');
        
        const currentBalance = {{ auth()->user()->umkm->saldo }};
        const amount = parseFloat(input.value) || 0;

        if (amount > currentBalance) {
            input.classList.add('border-red-500', 'focus:ring-red-500');
            input.classList.remove('border-gray-300', 'focus:ring-orange-500');
            errorMsg.classList.remove('hidden');
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            balanceHelper.querySelector('span').classList.remove('text-green-600');
            balanceHelper.querySelector('span').classList.add('text-red-500');
        } else {
            input.classList.remove('border-red-500', 'focus:ring-red-500');
            input.classList.add('border-gray-300', 'focus:ring-orange-500');
            errorMsg.classList.add('hidden');
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            balanceHelper.querySelector('span').classList.add('text-green-600');
            balanceHelper.querySelector('span').classList.remove('text-red-500');
        }
    }
</script>
@endsection
