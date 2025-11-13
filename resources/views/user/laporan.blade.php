@extends('layouts.app')

@section('title', 'Laporan Bug & Feedback')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    @guest
        <!-- Info untuk user yang belum login -->
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 mb-8 rounded-lg">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-400 text-2xl"></i>
                </div>
                <div class="ml-4 flex-1">
                    <h3 class="text-lg font-semibold text-yellow-800 mb-2">Anda Belum Login</h3>
                    <p class="text-yellow-700 mb-4">
                        Untuk membuat laporan bug atau memberikan feedback, Anda harus login terlebih dahulu.
                    </p>
                    <div class="flex gap-3">
                        <a href="{{ route('login') }}" class="bg-[#009b97] hover:bg-[#007a77] text-white px-6 py-2 rounded-lg font-semibold transition-colors inline-flex items-center gap-2">
                            <i class="fas fa-sign-in-alt"></i> Login Sekarang
                        </a>
                        <a href="{{ route('register') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-2 rounded-lg font-semibold transition-colors inline-flex items-center gap-2">
                            <i class="fas fa-user-plus"></i> Daftar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endguest

    @auth
    <!-- Header -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-[#009b97] to-[#039b00] rounded-full mb-4">
            <i class="fas fa-bug text-white text-2xl"></i>
        </div>
        <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">Laporan Bug & Feedback</h1>
        <p class="text-gray-600">Bantu kami meningkatkan aplikasi dengan melaporkan bug atau memberikan saran</p>
    </div>

    <!-- Form Laporan -->
    <div class="bg-white rounded-xl shadow-lg p-6 md:p-8">
        <form id="laporanForm" onsubmit="submitLaporan(event)">
            @csrf
            
            <!-- Nama -->
            <div class="mb-6">
                <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">
                    Nama <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="nama" 
                       name="nama" 
                       required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#009b97] focus:border-[#009b97] transition-all"
                       placeholder="Masukkan nama Anda">
            </div>

            <!-- Email -->
            <div class="mb-6">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    Email <span class="text-red-500">*</span>
                </label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       value="{{ Auth::check() ? Auth::user()->email : '' }}"
                       readonly
                       required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed"
                       placeholder="Email otomatis dari akun Anda">
                <p class="text-xs text-gray-500 mt-1">
                    <i class="fas fa-info-circle"></i> Email otomatis terisi dari akun Anda dan tidak dapat diubah
                </p>
            </div>

            <!-- Kategori -->
            <div class="mb-6">
                <label for="kategori" class="block text-sm font-medium text-gray-700 mb-2">
                    Kategori <span class="text-red-500">*</span>
                </label>
                <select id="kategori" 
                        name="kategori" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#009b97] focus:border-[#009b97] transition-all">
                    <option value="">Pilih Kategori</option>
                    <option value="bug">Bug / Error</option>
                    <option value="fitur">Saran Fitur Baru</option>
                    <option value="pertanyaan">Pertanyaan</option>
                    <option value="lainnya">Lainnya</option>
                </select>
            </div>

            <!-- Judul -->
            <div class="mb-6">
                <label for="judul" class="block text-sm font-medium text-gray-700 mb-2">
                    Judul Laporan <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="judul" 
                       name="judul" 
                       required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#009b97] focus:border-[#009b97] transition-all"
                       placeholder="Ringkasan singkat masalah atau saran Anda">
            </div>

            <!-- Deskripsi -->
            <div class="mb-6">
                <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-2">
                    Deskripsi Detail <span class="text-red-500">*</span>
                </label>
                <textarea id="deskripsi" 
                          name="deskripsi" 
                          rows="6" 
                          required
                          minlength="10"
                          maxlength="2000"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#009b97] focus:border-[#009b97] transition-all resize-none"
                          placeholder="Jelaskan secara detail masalah yang Anda temukan atau saran yang ingin Anda berikan..."></textarea>
                <p class="text-xs text-gray-500 mt-2">
                    Minimal 10 karakter, maksimal 2000 karakter. 
                    <span id="charCount" class="font-medium">0/2000</span>
                </p>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end gap-4">
                <a href="{{ route('public.katalog') }}" 
                   class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-semibold">
                    Batal
                </a>
                <button type="submit" 
                        id="submitBtn"
                        class="px-8 py-3 bg-[#009b97] hover:bg-[#007a77] text-white rounded-lg transition-colors font-semibold shadow-md hover:shadow-lg flex items-center gap-2">
                    <i class="fas fa-paper-plane"></i>
                    <span>Kirim Laporan</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Info Box -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-start gap-3">
            <i class="fas fa-info-circle text-blue-600 mt-0.5"></i>
            <div class="text-sm text-blue-800">
                <p class="font-semibold mb-1">Tips untuk laporan yang efektif:</p>
                <ul class="list-disc list-inside space-y-1 text-blue-700">
                    <li>Jelaskan langkah-langkah untuk mereproduksi bug (jika melaporkan bug)</li>
                    <li>Sertakan screenshot jika memungkinkan</li>
                    <li>Berikan detail tentang perangkat dan browser yang digunakan</li>
                    <li>Untuk saran fitur, jelaskan manfaat dan kegunaannya</li>
                </ul>
            </div>
        </div>
    </div>
    @endauth
</div>
@endsection

@section('scripts')
@auth
<script>
    // Character counter
    const deskripsiTextarea = document.getElementById('deskripsi');
    const charCount = document.getElementById('charCount');
    
    deskripsiTextarea.addEventListener('input', function() {
        const length = this.value.length;
        charCount.textContent = `${length}/2000`;
        
        if (length > 2000) {
            charCount.classList.add('text-red-500');
            charCount.classList.remove('text-gray-500');
        } else {
            charCount.classList.remove('text-red-500');
            charCount.classList.add('text-gray-500');
        }
    });

    // Submit form
    function submitLaporan(event) {
        event.preventDefault();
        
        const form = document.getElementById('laporanForm');
        const formData = new FormData(form);
        const submitBtn = document.getElementById('submitBtn');
        const originalBtnText = submitBtn.innerHTML;
        
        // Disable button and show loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';
        
        // Validate
        const deskripsi = formData.get('deskripsi');
        if (deskripsi.length < 10) {
            Swal.fire({
                icon: 'error',
                title: 'Deskripsi terlalu pendek',
                text: 'Deskripsi harus minimal 10 karakter.',
                confirmButtonColor: '#009b97'
            });
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
            return;
        }
        
        if (deskripsi.length > 2000) {
            Swal.fire({
                icon: 'error',
                title: 'Deskripsi terlalu panjang',
                text: 'Deskripsi maksimal 2000 karakter.',
                confirmButtonColor: '#009b97'
            });
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
            return;
        }
        
        // Submit
        fetch('{{ route("public.laporan.submit") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                nama: formData.get('nama'),
                // Email tidak perlu dikirim karena otomatis dari backend berdasarkan user yang login
                kategori: formData.get('kategori'),
                judul: formData.get('judul'),
                deskripsi: formData.get('deskripsi')
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: data.message,
                    confirmButtonColor: '#009b97',
                    confirmButtonText: 'OK'
                }).then(() => {
                    // Redirect ke history laporan
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        // Fallback: redirect berdasarkan role
                        const userRole = '{{ Auth::check() ? Auth::user()->role : "" }}';
                        if (userRole === 'umkm') {
                            window.location.href = '{{ route("umkm.history.laporan") }}';
                        } else {
                            window.location.href = '{{ route("user.history.laporan") }}';
                        }
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: data.message || 'Gagal mengirim laporan. Silakan coba lagi.',
                    confirmButtonColor: '#009b97'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Terjadi kesalahan saat mengirim laporan. Silakan coba lagi.',
                confirmButtonColor: '#009b97'
            });
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        });
    }
</script>
@endauth
@endsection

