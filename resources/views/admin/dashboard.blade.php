@extends('layouts.app')

@section('title', 'Dashboard Admin - UMKM Katalog')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-500 rounded-xl flex items-center justify-center text-white">
                        <i class="fas fa-tachometer-alt text-xl"></i>
                    </div>
                    Dashboard Admin
                </h1>
                <p class="text-gray-600 mt-2">Kelola data UMKM dan upload data AI user</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.users') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors inline-flex items-center gap-2">
                    <i class="fas fa-users"></i> Manajemen Akun
                </a>
                <a href="{{ route('admin.wallet.index') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition-colors inline-flex items-center gap-2">
                    <i class="fas fa-wallet"></i> Manajemen Dompet
                </a>
                <a href="{{ route('admin.laporan') }}" class="bg-[#009b97] hover:bg-[#007a77] text-white px-4 py-2 rounded-lg transition-colors inline-flex items-center gap-2">
                    <i class="fas fa-bug"></i> Laporan
                </a>
                <button onclick="showUploadModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors inline-flex items-center gap-2">
                    <i class="fas fa-file-pdf"></i> Upload PDF AI
                </button>
                <a href="{{ route('admin.umkm.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors inline-flex items-center gap-2">
                    <i class="fas fa-plus"></i> Tambah UMKM
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <!-- Pending Wallet Card -->
        <a href="{{ route('admin.wallet.index') }}" class="block transform transition-transform hover:scale-105">
            <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl p-6 text-white shadow-lg h-full">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-indigo-100 text-sm mb-1">Transaksi Pending</p>
                        <h3 class="text-3xl font-bold">{{ $pendingWalletCount }}</h3>
                    </div>
                    <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-wallet text-2xl"></i>
                    </div>
                </div>
            </div>
        </a>
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm mb-1">Total UMKM</p>
                    <h3 class="text-3xl font-bold">{{ $umkm->count() }}</h3>
                </div>
                <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-store text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm mb-1">UMKM Baru (30 hari)</p>
                    <h3 class="text-3xl font-bold">{{ $umkm->where('created_at', '>=', now()->subMonth())->count() }}</h3>
                </div>
                <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-line text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm mb-1">Total Favorit</p>
                    <h3 class="text-3xl font-bold">{{ $umkm->sum('favorit_count') }}</h3>
                </div>
                <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-heart text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm mb-1">Jenis UMKM</p>
                    <h3 class="text-3xl font-bold">{{ $umkm->groupBy('jenis_umkm')->count() }}</h3>
                </div>
                <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-tags text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- UMKM List -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                <i class="fas fa-list text-blue-500"></i> Daftar UMKM
            </h2>
        </div>
        <div class="overflow-x-auto">
            @if($umkm->count() > 0)
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Foto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama UMKM</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pemilik</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Favorit</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Dibuat</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($umkm as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($item->photo_path)
                                        <img src="{{ asset('storage/' . $item->photo_path) }}" 
                                             class="w-12 h-12 rounded-lg object-cover" 
                                             alt="{{ $item->nama }}">
                                    @else
                                        <div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-store text-gray-400"></i>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-semibold text-gray-900">{{ $item->nama }}</div>
                                    <div class="text-sm text-gray-500">{{ Str::limit($item->description, 50) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->user->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $item->jenis_umkm }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                    {{ number_format($item->latitude, 4) }}, {{ number_format($item->longitude, 4) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-heart mr-1"></i> {{ $item->favorit_count }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->created_at->format('d M Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.umkm.edit', $item->id) }}" 
                                           class="text-blue-600 hover:text-blue-900">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick="deleteUmkm({{ $item->id }})" 
                                                class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-store text-gray-300 text-5xl mb-4"></i>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Belum ada UMKM terdaftar</h3>
                    <p class="text-gray-600 mb-4">Klik tombol "Tambah UMKM" untuk menambahkan UMKM pertama.</p>
                    <a href="{{ route('admin.umkm.create') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-plus"></i> Tambah UMKM
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Upload PDF Modal -->
<div id="uploadModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center p-6 border-b">
            <h3 class="text-xl font-semibold flex items-center gap-2">
                <i class="fas fa-file-pdf text-red-500"></i> Upload PDF Data AI User
            </h3>
            <button onclick="closeUploadModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="uploadPdfForm" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf
            <div>
                <label for="pdf_file" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-file-pdf text-red-500 mr-2"></i>Pilih File PDF
                </label>
                <input type="file" 
                       id="pdf_file" 
                       name="pdf_file" 
                       accept=".pdf" 
                       required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                <p class="text-xs text-gray-500 mt-2">Format yang didukung: PDF (Maksimal 10MB)</p>
            </div>
            
            <div id="upload-progress" class="hidden">
                <div class="bg-gray-200 rounded-full h-2 mb-2">
                    <div id="progress-bar" class="bg-green-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
                <p class="text-sm text-gray-600 text-center" id="progress-text">Mengupload...</p>
            </div>

            <div id="upload-result" class="hidden"></div>

            <div class="flex justify-end gap-3 pt-4 border-t">
                <button type="button" onclick="closeUploadModal()" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Batal
                </button>
                <button type="submit" id="upload-btn" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors inline-flex items-center gap-2">
                    <i class="fas fa-upload"></i> Upload PDF
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl max-w-md w-full">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Konfirmasi Hapus</h3>
            </div>
            <p class="text-gray-600 mb-6">Apakah Anda yakin ingin menghapus UMKM ini? Tindakan ini tidak dapat dibatalkan.</p>
            <div class="flex justify-end gap-3">
                <button onclick="closeDeleteModal()" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Batal
                </button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg">
                        Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function showUploadModal() {
        document.getElementById('uploadModal').classList.remove('hidden');
    }

    function closeUploadModal() {
        document.getElementById('uploadModal').classList.add('hidden');
        document.getElementById('uploadPdfForm').reset();
        document.getElementById('upload-progress').classList.add('hidden');
        document.getElementById('upload-result').classList.add('hidden');
        document.getElementById('progress-bar').style.width = '0%';
    }

    function deleteUmkm(umkmId) {
        const deleteForm = document.getElementById('deleteForm');
        deleteForm.action = `/admin/umkm/${umkmId}/delete`;
        document.getElementById('deleteModal').classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }

    // Toast Notification Function
    function showToast(message, type = 'success') {
        const toastId = 'toast-' + Date.now();
        const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        
        const toast = document.createElement('div');
        toast.id = toastId;
        toast.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-4 rounded-lg shadow-xl z-50 flex items-center gap-3 min-w-[300px] max-w-md transform transition-all duration-300 translate-x-full`;
        
        toast.innerHTML = `
            <div class="flex items-center gap-3 flex-1">
                <i class="fas ${icon} text-xl"></i>
                <p class="flex-1">${message}</p>
                <button onclick="closeToast('${toastId}')" class="text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        // Animate in
        setTimeout(() => {
            toast.classList.remove('translate-x-full');
        }, 100);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            closeToast(toastId);
        }, 5000);
    }

    function closeToast(toastId) {
        const toast = document.getElementById(toastId);
        if (toast) {
            toast.classList.add('translate-x-full');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }
    }

    // Upload PDF Form
    document.getElementById('uploadPdfForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const uploadBtn = document.getElementById('upload-btn');
        const progressDiv = document.getElementById('upload-progress');
        const progressBar = document.getElementById('progress-bar');
        const progressText = document.getElementById('progress-text');
        const resultDiv = document.getElementById('upload-result');
        
        // Show progress
        progressDiv.classList.remove('hidden');
        resultDiv.classList.add('hidden');
        uploadBtn.disabled = true;
        uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengupload...';
        
        // Simulate progress
        let progress = 0;
        const progressInterval = setInterval(() => {
            progress += 10;
            if (progress <= 90) {
                progressBar.style.width = progress + '%';
            }
        }, 200);
        
        fetch('{{ route("admin.upload.pdf") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            clearInterval(progressInterval);
            progressBar.style.width = '100%';
            
            if (data.success) {
                progressText.textContent = 'Upload berhasil!';
                resultDiv.className = 'bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg';
                resultDiv.innerHTML = `
                    <div class="flex items-center gap-2">
                        <i class="fas fa-check-circle"></i>
                        <span>${data.message || 'PDF berhasil diupload!'}</span>
                    </div>
                `;
                resultDiv.classList.remove('hidden');
                
                // Show success toast
                showToast(data.message || 'PDF berhasil diupload ke sistem AI!', 'success');
                
                // Reset form after 2 seconds
                setTimeout(() => {
                    closeUploadModal();
                }, 2000);
            } else {
                progressText.textContent = 'Upload gagal!';
                resultDiv.className = 'bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg';
                resultDiv.innerHTML = `
                    <div class="flex items-center gap-2">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>${data.message || 'Gagal mengupload PDF'}</span>
                    </div>
                `;
                resultDiv.classList.remove('hidden');
                
                // Show error toast
                showToast(data.message || 'Gagal mengupload PDF ke sistem AI!', 'error');
            }
        })
        .catch(error => {
            clearInterval(progressInterval);
            progressText.textContent = 'Upload gagal!';
            resultDiv.className = 'bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg';
            resultDiv.innerHTML = `
                <div class="flex items-center gap-2">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>Terjadi kesalahan: ${error.message}</span>
                </div>
            `;
            resultDiv.classList.remove('hidden');
            
            // Show error toast
            showToast('Terjadi kesalahan saat mengupload PDF. Silakan coba lagi.', 'error');
        })
        .finally(() => {
            uploadBtn.disabled = false;
            uploadBtn.innerHTML = '<i class="fas fa-upload"></i> Upload PDF';
        });
    });

    // Handle delete form submission
    document.getElementById('deleteForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const form = this;
        const formData = new FormData(form);
        
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (response.ok) {
                showToast('UMKM berhasil dihapus!', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showToast('Gagal menghapus UMKM!', 'error');
            }
        })
        .catch(error => {
            showToast('Terjadi kesalahan saat menghapus UMKM!', 'error');
        });
    });

    // Close modals when clicking outside
    document.getElementById('uploadModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeUploadModal();
        }
    });

    document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeDeleteModal();
        }
    });

    // Show success/error message from session (if any)
    @if(session('success'))
        showToast('{{ session('success') }}', 'success');
    @endif

    @if(session('error'))
        showToast('{{ session('error') }}', 'error');
    @endif
</script>
@endsection
