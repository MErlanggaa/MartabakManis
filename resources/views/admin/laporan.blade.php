@extends('layouts.app')

@section('title', 'Laporan & Feedback - Admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-[#009b97] to-[#039b00] rounded-xl flex items-center justify-center text-white">
                        <i class="fas fa-bug text-xl"></i>
                    </div>
                    Laporan & Feedback
                </h1>
                <p class="text-gray-600 mt-2">Kelola semua laporan bug, saran fitur, dan feedback dari user</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.dashboard') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition-colors inline-flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm mb-1">Total Laporan</p>
                    <h3 class="text-3xl font-bold">{{ $totalReports }}</h3>
                </div>
                <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-alt text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-yellow-100 text-sm mb-1">Menunggu</p>
                    <h3 class="text-3xl font-bold">{{ $pendingReports }}</h3>
                </div>
                <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm mb-1">Diproses</p>
                    <h3 class="text-3xl font-bold">{{ $diprosesReports }}</h3>
                </div>
                <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-cog fa-spin text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm mb-1">Selesai</p>
                    <h3 class="text-3xl font-bold">{{ $selesaiReports }}</h3>
                </div>
                <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Laporan List -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                <i class="fas fa-list text-[#009b97]"></i> Daftar Laporan
            </h2>
        </div>
        
        @if($reports->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengirim</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($reports as $report)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $report->created_at->format('d M Y') }}<br>
                                    <span class="text-xs">{{ $report->created_at->format('H:i') }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $report->nama }}</div>
                                    <div class="text-sm text-gray-500">{{ $report->email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($report->kategori == 'bug') bg-red-100 text-red-800
                                        @elseif($report->kategori == 'fitur') bg-blue-100 text-blue-800
                                        @elseif($report->kategori == 'pertanyaan') bg-purple-100 text-purple-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ $report->kategori_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ Str::limit($report->judul, 50) }}</div>
                                    <div class="text-sm text-gray-500 mt-1">{{ Str::limit($report->deskripsi, 80) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($report->status == 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($report->status == 'diproses') bg-blue-100 text-blue-800
                                        @else bg-green-100 text-green-800
                                        @endif">
                                        {{ $report->status_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="showDetailModal({{ $report->id }})" 
                                            class="text-[#009b97] hover:text-[#007a77] mr-3">
                                        <i class="fas fa-eye"></i> Detail
                                    </button>
                                    <button onclick="deleteLaporan({{ $report->id }})" 
                                            class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $reports->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
                <p class="text-gray-500 text-lg">Belum ada laporan</p>
            </div>
        @endif
    </div>
</div>

<!-- Detail Modal -->
<div id="detailModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center p-6 border-b">
            <h3 class="text-xl font-semibold flex items-center gap-2">
                <i class="fas fa-bug text-[#009b97]"></i> Detail Laporan
            </h3>
            <button onclick="closeDetailModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div id="modalContent" class="p-6">
            <!-- Content will be loaded via JavaScript -->
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Show detail modal
    function showDetailModal(reportId) {
        // Fetch report data
        fetch(`/admin/laporan/${reportId}/detail`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const report = data.report;
                    const modalContent = document.getElementById('modalContent');
                    
                    modalContent.innerHTML = `
                        <div class="space-y-6">
                            <!-- Info Pengirim -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-500 mb-2">Pengirim</h4>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <p class="font-semibold text-gray-900">${report.nama}</p>
                                    <p class="text-sm text-gray-600">${report.email}</p>
                                </div>
                            </div>

                            <!-- Kategori & Status -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 mb-2">Kategori</h4>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                        ${report.kategori_label}
                                    </span>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 mb-2">Status</h4>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                        ${report.status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                          report.status === 'diproses' ? 'bg-blue-100 text-blue-800' : 
                                          'bg-green-100 text-green-800'}">
                                        ${report.status_label}
                                    </span>
                                </div>
                            </div>

                            <!-- Judul -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-500 mb-2">Judul</h4>
                                <p class="text-gray-900 font-semibold">${report.judul}</p>
                            </div>

                            <!-- Deskripsi -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-500 mb-2">Deskripsi</h4>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <p class="text-gray-700 whitespace-pre-wrap">${report.deskripsi}</p>
                                </div>
                            </div>

                            <!-- Respon Admin (jika ada) -->
                            ${report.respon_admin ? `
                            <div>
                                <h4 class="text-sm font-medium text-gray-500 mb-2">Respon Admin</h4>
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <p class="text-gray-700 whitespace-pre-wrap">${report.respon_admin}</p>
                                    ${report.admin ? `<p class="text-xs text-gray-500 mt-2">Oleh: ${report.admin.name}</p>` : ''}
                                </div>
                            </div>
                            ` : ''}

                            <!-- Update Status -->
                            <div class="border-t pt-6">
                                <h4 class="text-sm font-medium text-gray-500 mb-4">Update Status</h4>
                                <form id="updateStatusForm" onsubmit="updateStatus(event, ${report.id})">
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                        <select name="status" id="statusSelect" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#009b97]">
                                            <option value="pending" ${report.status === 'pending' ? 'selected' : ''}>Menunggu</option>
                                            <option value="diproses" ${report.status === 'diproses' ? 'selected' : ''}>Diproses</option>
                                            <option value="selesai" ${report.status === 'selesai' ? 'selected' : ''}>Selesai</option>
                                        </select>
                                    </div>
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Respon Admin (Opsional)</label>
                                        <textarea name="respon_admin" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#009b97] resize-none" placeholder="Tambahkan catatan atau respon untuk laporan ini...">${report.respon_admin || ''}</textarea>
                                    </div>
                                    <div class="flex justify-end gap-3">
                                        <button type="button" onclick="closeDetailModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                                            Batal
                                        </button>
                                        <button type="submit" class="px-6 py-2 bg-[#009b97] hover:bg-[#007a77] text-white rounded-lg transition-colors">
                                            <i class="fas fa-save mr-2"></i> Simpan
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    `;
                    
                    document.getElementById('detailModal').classList.remove('hidden');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Gagal memuat detail laporan.',
                    confirmButtonColor: '#009b97'
                });
            });
    }

    // Close detail modal
    function closeDetailModal() {
        document.getElementById('detailModal').classList.add('hidden');
    }

    // Update status
    function updateStatus(event, reportId) {
        event.preventDefault();
        
        const form = document.getElementById('updateStatusForm');
        const formData = new FormData(form);
        
        fetch(`/admin/laporan/${reportId}/status`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                status: formData.get('status'),
                respon_admin: formData.get('respon_admin')
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
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: data.message || 'Gagal memperbarui status.',
                    confirmButtonColor: '#009b97'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Terjadi kesalahan saat memperbarui status.',
                confirmButtonColor: '#009b97'
            });
        });
    }

    // Delete laporan
    function deleteLaporan(reportId) {
        Swal.fire({
            icon: 'warning',
            title: 'Hapus Laporan?',
            text: 'Apakah Anda yakin ingin menghapus laporan ini?',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Create form for DELETE request
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/laporan/${reportId}`;
                
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                form.appendChild(csrfToken);
                
                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';
                form.appendChild(methodField);
                
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
</script>
@endsection

