@extends('layouts.app')

@section('title', 'Isi Saldo')

@section('content')
<div class="max-w-xl mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('umkm.wallet.index') }}" class="text-gray-500 hover:text-[#218689] flex items-center gap-2 transition-colors">
            <i class="fas fa-arrow-left"></i> Kembali ke Dompet
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="bg-[#218689] p-6 text-center text-white">
            <h1 class="text-2xl font-bold mb-2">Isi Saldo Dompet</h1>
            <p class="opacity-90 text-sm">Scan QRIS di bawah ini untuk melakukan Top Up</p>
        </div>

        <div class="p-8">
            <form action="{{ route('umkm.wallet.storeTopup') }}" method="POST" enctype="multipart/form-data" class="space-y-6" id="topupForm">
                @csrf
                
                <!-- Step 1: Input Nominal -->
                <div id="step1">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Nominal Top Up</label>
                    <div class="relative mb-2">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span class="text-gray-500 font-bold">Rp</span>
                        </div>
                        <input type="number" id="baseAmount" min="10000" step="1" 
                               class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#218689] font-bold text-lg text-gray-800 placeholder-gray-300" 
                               placeholder="Min. 10.000">
                    </div>
                    <p class="text-xs text-gray-500 mb-6">Minimal pengisian Rp 10.000</p>
                    
                    <button type="button" onclick="goToStep2()" class="w-full bg-[#218689] hover:bg-[#1a6b6d] text-white font-bold py-3 rounded-lg shadow-md transition-colors">
                        Lanjut <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </div>

                <!-- Step 2: QRIS & Instruction -->
                <div id="step2" class="hidden animate-fade-in-up">
                    <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-circle text-yellow-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    QRIS di bawah ini sudah <strong>DINAMIS</strong> (Otomatis berisi nominal). Cukup scan dan bayar.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mb-6">
                        <p class="text-sm text-gray-500 mb-1">ID Transaksi</p>
                        <p class="text-lg font-mono font-bold text-gray-800 tracking-wider mb-4" id="trxIdDisplay">TRX-...</p>

                        <div class="bg-white p-4 rounded-xl inline-block border-2 border-dashed border-gray-300 shadow-sm relative group mb-4">
                            <!-- Helper Text if QRIS String is missing -->
                            <div id="qris-error" class="hidden mb-2 text-red-500 text-xs max-w-xs mx-auto">
                                String QRIS belum disetting! Silakan edit file ini dan masukkan string QRIS asli Anda di variabel 'STATIC_QR_DATA'.
                            </div>

                            <!-- QR Code Container -->
                            <div id="qrcode" class="flex items-center justify-center mx-auto"></div>
                        </div>

                        <p class="text-sm text-gray-600 mb-2">Total Pembayaran</p>
                        <div class="text-3xl font-bold text-[#218689] mb-1" id="displayTotal">Rp 0</div>
                        <p class="text-xs text-gray-400">(Termasuk kode unik 3 digit)</p>
                    </div>

                    <input type="hidden" name="amount" id="finalAmountInput">

                    <button type="button" onclick="goToStep3()" class="w-full bg-[#218689] hover:bg-[#1a6b6d] text-white font-bold py-3 rounded-lg shadow-md transition-colors mb-3">
                        Saya Sudah Bayar
                    </button>
                    <button type="button" onclick="resetStep()" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold py-3 rounded-lg transition-colors">
                        Batal / Ubah Nominal
                    </button>
                </div>

                <!-- Step 3: Upload Proof -->
                <div id="step3" class="hidden animate-fade-in-up">
                    <div class="text-center mb-6">
                        <h3 class="text-lg font-bold text-gray-800">Upload Bukti Transfer</h3>
                        <p class="text-sm text-gray-600">Pastikan nominal dan ID Transaksi terlihat</p>
                    </div>

                    <div class="mb-4">
                         <div class="flex justify-between items-center bg-gray-50 p-3 rounded-lg text-sm">
                             <span class="text-gray-500">Total Transfer</span>
                             <span class="font-bold text-gray-800" id="summaryTotal">Rp 0</span>
                         </div>
                    </div>

                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:bg-gray-50 transition-colors cursor-pointer relative mb-6">
                        <input type="file" name="proof" accept="image/*" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="previewImage(this)">
                        <div id="upload-placeholder">
                            <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                            <p class="text-sm text-gray-600">Klik untuk upload bukti bayar</p>
                            <p class="text-xs text-gray-400">(JPG, PNG - Max 2MB)</p>
                        </div>
                        <img id="preview" class="hidden max-h-48 mx-auto rounded-lg shadow-sm">
                    </div>

                    <div class="flex gap-2">
                        <button type="button" onclick="backToStep2()" class="w-1/3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-3 rounded-lg transition-colors">
                            Kembali
                        </button>
                        <button type="submit" class="w-2/3 bg-[#218689] hover:bg-[#1a6b6d] text-white font-bold py-3 rounded-lg shadow-md transition-transform transform hover:scale-[1.02]">
                            Kirim Bukti
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    // ==========================================
    // CONFIGURATION: PASTE YOUR QRIS STRING HERE
    // ==========================================
    // Cara dapat string: Scan QRIS.jpeg anda pakai aplikasi "Barcode Scanner" (bukan m-banking), copy text-nya ke sini.
    // Contoh: "00020101021126570014ID.CO.QRIS.WWW..."
    const STATIC_QR_DATA = "00020101021126610014COM.GO-JEK.WWW01189360091436952667940210G6952667940303UMI51440014ID.CO.QRIS.WWW0215ID10254648892740303UMI5204762953033605802ID5925MUHAMMAD ERLANGGA PUTRA W6013JAKARTA TIMUR61051382062070703A0163044E02"; 
    // ==========================================

    let uniqueCode = 0;
    let trxId = '';
    let qrcodeObj = null;

    function goToStep2() {
        const baseAmountInput = document.getElementById('baseAmount');
        const amount = parseInt(baseAmountInput.value);
        
        if (!amount || amount < 10000) {
            alert('Minimal top up Rp 10.000');
            return;
        }

        if(uniqueCode === 0) uniqueCode = Math.floor(Math.random() * 999) + 1;
        if(trxId === '') trxId = 'TRX-' + Date.now().toString().slice(-6) + Math.floor(Math.random() * 100);

        const total = amount + uniqueCode;

        // Update Displays
        document.getElementById('trxIdDisplay').innerText = trxId;
        document.getElementById('displayTotal').innerText = 'Rp ' + total.toLocaleString('id-ID');
        document.getElementById('summaryTotal').innerText = 'Rp ' + total.toLocaleString('id-ID');
        document.getElementById('finalAmountInput').value = total;
        
        // Toggle Steps
        document.getElementById('step1').classList.add('hidden');
        document.getElementById('step2').classList.remove('hidden');

        // Generate Dynamic QRIS
        generateDynamicQris(total);
    }

    function generateDynamicQris(amount) {
        const container = document.getElementById('qrcode');
        container.innerHTML = ''; // Clear previous

        if (!STATIC_QR_DATA) {
            document.getElementById('qris-error').classList.remove('hidden');
            return;
        }

        try {
            const dynamicQris = convertToDynamicQris(STATIC_QR_DATA, amount);
            
            new QRCode(container, {
                text: dynamicQris,
                width: 200,
                height: 200,
                colorDark : "#000000",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.H
            });
        } catch (e) {
            console.error(e);
            alert("Gagal membuat QRIS Dinamis. Cek string QRIS Anda.");
        }
    }

    // --- EMVCo QRIS Logic ---
    function convertToDynamicQris(qrisRaw, amount) {
        // 1. Validasi awal (000201...)
        if (!qrisRaw.startsWith('000201')) throw new Error("Invalid QRIS Format");

        let qris = qrisRaw;

        // 2. Ubah Point of Initiation Method (01) kalau Static (11) jadi Dynamic (12)
        // Cari tag 01
        // Usually 000201 010211 ...
        // Index 6 is tag 01. Length 02. Value 11.
        // We replace '010211' with '010212' if found.
        qris = qris.replace('010211', '010212');

        // 3. Hapus Tag 54 (Transaction Amount) lama jika ada, dan Tag 55 (Tip), 58 (Country code default ID is safe), 63 (CRC)
        // Kita akan rebuild ulang part sebelum CRC.
        // Cara gampang: Potong sebelum CRC (Tag 63). Biasa di akhir.
        
        // Find CRC Tag '6304' position
        const crcIndex = qris.lastIndexOf('6304');
        if (crcIndex > -1) {
            qris = qris.substring(0, crcIndex);
        }

        // Hapus Tag 54 lama kalau ada (agak tricky kalau string based, tapi asumsi qris statis biasanya gak ada 54)
        // Better: Just append 54 before calculating CRC.
        // Format Tag 54: '54' + Length (2 digit) + Amount (String)
        const amountStr = amount.toString();
        const amountLen = amountStr.length.toString().padStart(2, '0');
        const tag54 = '54' + amountLen + amountStr; // Misal 540510000

        // Masukkan Tag 54 sebelum CRC. 
        // Urutan tag EMVCo sebaiknya urut, tapi banyak reader toleransi un-ordered. 
        // 54 biasanya setelah 53 (Currency) atau 52 (MCC).
        // Kita append saja di akhir data sebelum CRC.
        qris += tag54;

        // 4. Hitung CRC Barus
        qris += '6304'; // Append CRC Tag and Length
        const crc = crc16ccitt(qris);
        qris += crc;

        return qris;
    }

    // CRC16 CCITT-FALSE (Polynomial 0x1021, Initial 0xFFFF)
    function crc16ccitt(str) {
        let crc = 0xFFFF;
        for (let i = 0; i < str.length; i++) {
            let c = str.charCodeAt(i);
            crc ^= (c << 8) & 0xFFFF;
            for (let j = 0; j < 8; j++) {
                if (crc & 0x8000) {
                    crc = ((crc << 1) ^ 0x1021) & 0xFFFF;
                } else {
                    crc = (crc << 1) & 0xFFFF;
                }
            }
        }
        return crc.toString(16).toUpperCase().padStart(4, '0');
    }

    function goToStep3() {
        document.getElementById('step2').classList.add('hidden');
        document.getElementById('step3').classList.remove('hidden');
    }

    function backToStep2() {
        document.getElementById('step3').classList.add('hidden');
        document.getElementById('step2').classList.remove('hidden');
    }

    function resetStep() {
        uniqueCode = 0; // Reset unique code
        trxId = '';
        document.getElementById('step1').classList.remove('hidden');
        document.getElementById('step2').classList.add('hidden');
        document.getElementById('step3').classList.add('hidden');
        document.getElementById('baseAmount').value = '';
        document.getElementById('qrcode').innerHTML = '';
    }

    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('upload-placeholder').classList.add('hidden');
                var img = document.getElementById('preview');
                img.src = e.target.result;
                img.classList.remove('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>


@endsection
