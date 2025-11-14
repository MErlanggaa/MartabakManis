@extends('layouts.app')

@section('title', 'Dashboard UMKM')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-blue-500 rounded-xl flex items-center justify-center text-white">
                        <i class="fas fa-store text-xl"></i>
                    </div>
                    Dashboard UMKM
                </h1>
                <p class="text-gray-600 mt-2">Kelola profil, data keuntungan, dan layanan UMKM Anda</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('umkm.ai-consultation') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors inline-flex items-center gap-2">
                    <i class="fas fa-robot"></i> AI Konsultasi
                </a>
                <a href="{{ route('umkm.komentar') }}" class="bg-[#009b97] hover:bg-[#007a77] text-white px-4 py-2 rounded-lg transition-colors inline-flex items-center gap-2">
                    <i class="fas fa-comments"></i> Komentar
                </a>
                <button onclick="showProfileModal()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors inline-flex items-center gap-2">
                    <i class="fas fa-edit"></i> Edit Profil
                </button>
                <button onclick="showKeuntunganModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors inline-flex items-center gap-2">
                    <i class="fas fa-plus"></i> Tambah Keuntungan
                </button>
                <button onclick="showLayananModal()" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors inline-flex items-center gap-2">
                    <i class="fas fa-cogs"></i> Kelola Layanan
                </button>
            </div>
        </div>
    </div>

    @if(!$umkm)
        <!-- Warning: No Profile -->
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
                    <div>
                        <h3 class="font-semibold text-yellow-900">Profil UMKM belum lengkap!</h3>
                        <p class="text-yellow-700 text-sm">Silakan lengkapi profil UMKM Anda terlebih dahulu.</p>
                    </div>
                </div>
                <button onclick="showProfileModal()" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg transition-colors">
                    Lengkapi Profil
                </button>
            </div>
        </div>
    @else
        <!-- Profile Card -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="md:col-span-1">
                    <div class="w-full h-48 bg-gray-200 rounded-lg overflow-hidden">
                        @if($umkm->photo_path)
                            <img src="{{ asset('storage/' . $umkm->photo_path) }}" alt="{{ $umkm->nama }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-400">
                                <i class="fas fa-store text-4xl"></i>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="md:col-span-3">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $umkm->nama }}</h2>
                    <p class="text-gray-600 mb-4">{{ $umkm->description }}</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Jenis UMKM</p>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                {{ $umkm->jenis_umkm }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Lokasi</p>
                            <p class="text-gray-900">{{ number_format($umkm->latitude, 4) }}, {{ number_format($umkm->longitude, 4) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Favorit</p>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                <i class="fas fa-heart mr-1"></i> {{ $umkm->favorit_count }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Total Dilihat</p>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-[#009b97]/10 text-[#009b97]">
                                <i class="fas fa-eye mr-1"></i> {{ number_format($umkm->views ?? 0, 0, ',', '.') }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Bergabung</p>
                            <p class="text-gray-900">{{ $umkm->created_at->format('d M Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm mb-1">Data Keuntungan</p>
                        <h3 class="text-3xl font-bold">{{ $keuntungan->count() }}</h3>
                    </div>
                    <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chart-bar text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-6 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm mb-1">Total Keuntungan</p>
                        <h3 class="text-2xl font-bold">Rp {{ number_format($keuntungan->sum('keuntungan_bersih'), 0, ',', '.') }}</h3>
                    </div>
                    <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-money-bill-wave text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-6 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-sm mb-1">Total Transaksi</p>
                        <h3 class="text-3xl font-bold">{{ $keuntungan->sum('jumlah_transaksi') }}</h3>
                    </div>
                    <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-shopping-cart text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-pink-500 to-pink-600 rounded-xl p-6 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-pink-100 text-sm mb-1">User Favorit</p>
                        <h3 class="text-3xl font-bold">{{ $favoriteUsers->count() }}</h3>
                    </div>
                    <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-heart text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-[#009b97] to-[#007a77] rounded-xl p-6 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/80 text-sm mb-1">Total Dilihat</p>
                        <h3 class="text-3xl font-bold">{{ number_format($umkm->views ?? 0, 0, ',', '.') }}</h3>
                    </div>
                    <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-eye text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Favorite Users -->
        @if($favoriteUsers->count() > 0)
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="fas fa-heart text-pink-500"></i> User yang Memfavoritkan UMKM Anda
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($favoriteUsers as $user)
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="text-center">
                                <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-blue-500 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <i class="fas fa-user text-white text-2xl"></i>
                                </div>
                                <h4 class="font-semibold text-gray-900">{{ $user->name }}</h4>
                                <p class="text-sm text-gray-600 mb-2">{{ $user->email }}</p>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-pink-100 text-pink-800">
                                    <i class="fas fa-heart mr-1"></i> Favorit
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Charts and Upload -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Chart -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="fas fa-chart-line text-blue-500"></i> Grafik Keuntungan
                </h3>
                <canvas id="keuntunganChart" class="w-full"></canvas>
            </div>

            <!-- Upload Excel -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="fas fa-upload text-green-500"></i> Upload Excel
                </h3>
                <div class="space-y-4">
                    <a href="{{ route('umkm.excel.template') }}" class="block w-full bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition-colors text-center">
                        <i class="fas fa-download mr-2"></i> Download Template Excel
                    </a>
                    <form id="excelForm" enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        <div>
                            <label for="excel_file" class="block text-sm font-medium text-gray-700 mb-2">File Excel</label>
                            <input type="file" id="excel_file" name="excel_file" accept=".csv,.txt" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-upload mr-2"></i> Upload Excel
                        </button>
                    </form>
                    <div class="text-xs text-gray-500 bg-gray-50 p-3 rounded-lg">
                        <strong>Cara Menggunakan:</strong><br>
                        1. Download template CSV<br>
                        2. Buka dengan Excel<br>
                        3. Isi data pendapatan & pengeluaran<br>
                        4. Upload file yang sudah diisi
                    </div>
                </div>
            </div>
        </div>

        <!-- Keuntungan Table -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-table text-purple-500"></i> Data Keuntungan
                </h3>
                <button onclick="showKeuntunganModal()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors inline-flex items-center gap-2">
                    <i class="fas fa-plus"></i> Tambah Data
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bulan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pendapatan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengeluaran</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keuntungan Bersih</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Transaksi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($keuntungan as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->bulan }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rp {{ number_format($item->pendapatan, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rp {{ number_format($item->pengeluaran, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold {{ $item->keuntungan_bersih >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    Rp {{ number_format($item->keuntungan_bersih, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->jumlah_transaksi }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <button onclick="editKeuntungan({{ $item->id }})" class="text-yellow-600 hover:text-yellow-900 mr-3">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deleteKeuntungan({{ $item->id }})" class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">Belum ada data keuntungan</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

<!-- Modals akan tetap sama seperti sebelumnya, hanya perlu update class Bootstrap ke Tailwind -->
<!-- Profile Modal -->
<div id="profileModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center p-6 border-b">
            <h3 class="text-xl font-semibold">Edit Profil UMKM</h3>
            <button onclick="closeModal('profileModal')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="profileForm" enctype="multipart/form-data" class="p-6 space-y-4" data-no-loading>
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">Nama UMKM</label>
                    <input type="text" id="nama" name="nama" value="{{ $umkm->nama ?? '' }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label for="jenis_umkm" class="block text-sm font-medium text-gray-700 mb-2">Jenis UMKM</label>
                    <select id="jenis_umkm" name="jenis_umkm" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">Pilih Jenis UMKM</option>
                        <option value="Makanan & Minuman" {{ ($umkm->jenis_umkm ?? '') == 'Makanan & Minuman' ? 'selected' : '' }}>Makanan & Minuman</option>
                        <option value="Fashion" {{ ($umkm->jenis_umkm ?? '') == 'Fashion' ? 'selected' : '' }}>Fashion</option>
                        <option value="Kerajinan" {{ ($umkm->jenis_umkm ?? '') == 'Kerajinan' ? 'selected' : '' }}>Kerajinan</option>
                        <option value="Jasa" {{ ($umkm->jenis_umkm ?? '') == 'Jasa' ? 'selected' : '' }}>Jasa</option>
                        <option value="Pertanian" {{ ($umkm->jenis_umkm ?? '') == 'Pertanian' ? 'selected' : '' }}>Pertanian</option>
                        <option value="Lainnya" {{ ($umkm->jenis_umkm ?? '') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                    <textarea id="description" name="description" rows="3" required
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">{{ $umkm->description ?? '' }}</textarea>
                </div>
                <div>
                    <label for="no_wa" class="block text-sm font-medium text-gray-700 mb-2">Nomor WhatsApp</label>
                    <input type="text" id="no_wa" name="no_wa" value="{{ $umkm->no_wa ?? '' }}" 
                           placeholder="Contoh: 081234567890 atau +6281234567890"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <p class="mt-1 text-xs text-gray-500">Format: nomor telepon (dapat menggunakan +62 atau 0)</p>
                </div>
                <div>
                    <label for="photo" class="block text-sm font-medium text-gray-700 mb-2">Foto UMKM</label>
                    <input type="file" id="photo" name="photo" accept="image/*"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
                    <div class="flex gap-2">
                        <input type="text" id="address" name="address" placeholder="Masukkan alamat lengkap"
                               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <button type="button" onclick="geocodeAddress()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-map-marker-alt"></i> Cari
                        </button>
                    </div>
                </div>
                <div>
                    <label for="latitude" class="block text-sm font-medium text-gray-700 mb-2">Latitude</label>
                    <input type="number" step="any" id="latitude" name="latitude" value="{{ $umkm->latitude ?? '' }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label for="longitude" class="block text-sm font-medium text-gray-700 mb-2">Longitude</label>
                    <input type="number" step="any" id="longitude" name="longitude" value="{{ $umkm->longitude ?? '' }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Peta Lokasi</label>
                    <div id="map" class="w-full h-64 border border-gray-300 rounded-lg"></div>
                    <p class="text-xs text-gray-500 mt-2">Klik pada peta untuk memilih lokasi</p>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t">
                <button type="button" onclick="closeModal('profileModal')" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Keuntungan Modal -->
<div id="keuntunganModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center p-6 border-b">
            <h3 class="text-xl font-semibold" id="keuntunganModalTitle">Tambah Data Keuntungan</h3>
            <button onclick="closeModal('keuntunganModal')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="keuntunganForm" class="p-6 space-y-4">
            @csrf
            <input type="hidden" id="keuntungan_id" name="keuntungan_id" value="">
            <div>
                <label for="periode_type" class="block text-sm font-medium text-gray-700 mb-2">Periode</label>
                <select id="periode_type" name="periode_type" onchange="togglePeriodeInput()" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="">Pilih Periode</option>
                    <option value="harian">Harian</option>
                    <option value="mingguan">Mingguan</option>
                    <option value="bulanan">Bulanan</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">Catatan: Edit hanya mengubah nilai, tidak mengubah periode</p>
            </div>
            <div id="tanggal_input" class="hidden">
                <label for="tanggal" class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                <input type="date" id="tanggal" name="tanggal"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <div id="minggu_input" class="hidden">
                <label for="minggu" class="block text-sm font-medium text-gray-700 mb-2">Minggu</label>
                <input type="week" id="minggu" name="minggu"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <div id="bulan_input" class="hidden">
                <label for="bulan" class="block text-sm font-medium text-gray-700 mb-2">Bulan</label>
                <input type="month" id="bulan" name="bulan"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <div>
                <label for="pendapatan" class="block text-sm font-medium text-gray-700 mb-2">Pendapatan (Rp)</label>
                <input type="number" id="pendapatan" name="pendapatan" min="0" step="0.01" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <div>
                <label for="pengeluaran" class="block text-sm font-medium text-gray-700 mb-2">Pengeluaran (Rp)</label>
                <input type="number" id="pengeluaran" name="pengeluaran" min="0" step="0.01" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <div>
                <label for="jumlah_transaksi" class="block text-sm font-medium text-gray-700 mb-2">Jumlah Transaksi</label>
                <input type="number" id="jumlah_transaksi" name="jumlah_transaksi" min="0" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t">
                <button type="button" onclick="closeModal('keuntunganModal')" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">
                    <span id="keuntunganSubmitText">Simpan</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Layanan Modal -->
<div id="layananModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center p-6 border-b">
            <h3 class="text-xl font-semibold">Kelola Layanan</h3>
            <button onclick="closeModal('layananModal')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6 space-y-6">
            <!-- Add/Edit Service Form -->
            <div class="border border-gray-200 rounded-lg p-4">
                <h4 class="font-semibold text-gray-900 mb-4" id="layananFormTitle">Tambah Layanan Baru</h4>
                <form id="layananForm" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ Auth::id() }}">
                    <input type="hidden" id="layanan_id" name="layanan_id" value="">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="layanan_nama" class="block text-sm font-medium text-gray-700 mb-2">Nama Layanan</label>
                            <input type="text" id="layanan_nama" name="nama" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                        </div>
                        <div>
                            <label for="layanan_price" class="block text-sm font-medium text-gray-700 mb-2">Harga (Rp)</label>
                            <input type="number" id="layanan_price" name="price" min="0" step="0.01" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                        </div>
                        <div>
                            <label for="layanan_photo" class="block text-sm font-medium text-gray-700 mb-2">Foto Layanan</label>
                            <input type="file" id="layanan_photo" name="photo" accept="image/*" onchange="previewLayananPhoto(this)"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <div id="layanan_photo_preview" class="mt-2 hidden">
                                <img id="layanan_photo_preview_img" src="" alt="Preview" class="w-32 h-32 object-cover rounded-lg border border-gray-300">
                            </div>
                        </div>
                        <div>
                            <label for="layanan_description" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                            <textarea id="layanan_description" name="description" rows="3"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"></textarea>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-plus mr-2" id="layananSubmitIcon"></i> 
                            <span id="layananSubmitText">Tambah Layanan</span>
                        </button>
                        <button type="button" id="layananCancelBtn" onclick="resetLayananForm()" class="hidden px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                            Batal
                        </button>
                    </div>
                </form>
            </div>

            <!-- Current Services -->
            <div class="border border-gray-200 rounded-lg p-4">
                <h4 class="font-semibold text-gray-900 mb-4">Layanan Saat Ini</h4>
                <div id="layanan-list" class="space-y-3">
                    @if($umkm && $umkm->layanan->count() > 0)
                        @foreach($umkm->layanan as $layanan)
                            <div id="layanan-{{ $layanan->id }}" class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-20 h-20 bg-gray-200 rounded-lg overflow-hidden flex-shrink-0">
                                        @if($layanan->photo_path)
                                            <img src="{{ asset('storage/' . $layanan->photo_path) }}" alt="{{ $layanan->nama }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-gray-400">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <h5 class="font-semibold text-gray-900">{{ $layanan->nama }}</h5>
                                        <p class="text-sm text-gray-600 mb-1">{{ $layanan->description ?? 'Tidak ada deskripsi' }}</p>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                            Rp {{ number_format($layanan->price, 0, ',', '.') }}
                                        </span>
                                    </div>
                                    <div class="flex gap-2">
                                        <button onclick="editLayanan({{ $layanan->id }})" class="text-blue-600 hover:text-blue-800" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="removeLayanan({{ $layanan->id }})" class="text-red-600 hover:text-red-800" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-center text-gray-500 py-4">Belum ada layanan</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let keuntunganChart;
    let map;
    let marker;

    function showProfileModal() {
        try {
            const modal = document.getElementById('profileModal');
            if (modal) {
                modal.classList.remove('hidden');
                setTimeout(() => initMap(), 500);
            } else {
                console.error('Profile modal not found');
            }
        } catch (error) {
            console.error('Error showing profile modal:', error);
        }
    }

    function showKeuntunganModal() {
        try {
            const modal = document.getElementById('keuntunganModal');
            if (modal) {
                modal.classList.remove('hidden');
                // Reset form jika perlu
                document.getElementById('keuntunganForm')?.reset();
                document.getElementById('keuntungan_id').value = '';
                document.getElementById('keuntunganModalTitle').textContent = 'Tambah Data Keuntungan';
                togglePeriodeInput();
            } else {
                console.error('Keuntungan modal not found');
            }
        } catch (error) {
            console.error('Error showing keuntungan modal:', error);
        }
    }

    function showLayananModal() {
        try {
            const modal = document.getElementById('layananModal');
            if (modal) {
                modal.classList.remove('hidden');
                // Reset form jika perlu
                document.getElementById('layananForm')?.reset();
                document.getElementById('layanan_id').value = '';
                const formTitle = document.getElementById('layananFormTitle');
                if (formTitle) formTitle.textContent = 'Tambah Layanan';
            } else {
                console.error('Layanan modal not found');
            }
        } catch (error) {
            console.error('Error showing layanan modal:', error);
        }
    }

    function closeModal(modalId) {
        try {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('hidden');
            }
        } catch (error) {
            console.error('Error closing modal:', error);
        }
    }

    function initMap() {
        const lat = parseFloat(document.getElementById('latitude').value) || -6.200000;
        const lng = parseFloat(document.getElementById('longitude').value) || 106.816666;
        
        if (map) map.remove();
        
        map = L.map('map').setView([lat, lng], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        
        marker = L.marker([lat, lng]).addTo(map);
        
        map.on('click', function(e) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;
            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;
            if (marker) map.removeLayer(marker);
            marker = L.marker([lat, lng]).addTo(map);
        });
    }

    function geocodeAddress() {
        const address = document.getElementById('address').value;
        if (!address) {
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan',
                text: 'Masukkan alamat terlebih dahulu',
                confirmButtonColor: '#009b97'
            });
            return;
        }
        
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1`)
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    const lat = parseFloat(data[0].lat);
                    const lng = parseFloat(data[0].lon);
                    document.getElementById('latitude').value = lat;
                    document.getElementById('longitude').value = lng;
                    if (map) {
                        map.setView([lat, lng], 15);
                        if (marker) map.removeLayer(marker);
                        marker = L.marker([lat, lng]).addTo(map);
                    }
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Alamat ditemukan',
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Tidak Ditemukan',
                        text: 'Alamat tidak ditemukan',
                        confirmButtonColor: '#009b97'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Gagal mencari alamat',
                    confirmButtonColor: '#009b97'
                });
            });
    }

    function togglePeriodeInput() {
        const periodeType = document.getElementById('periode_type').value;
        document.getElementById('tanggal_input').classList.add('hidden');
        document.getElementById('minggu_input').classList.add('hidden');
        document.getElementById('bulan_input').classList.add('hidden');
        
        if (periodeType === 'harian') {
            document.getElementById('tanggal_input').classList.remove('hidden');
        } else if (periodeType === 'mingguan') {
            document.getElementById('minggu_input').classList.remove('hidden');
        } else if (periodeType === 'bulanan') {
            document.getElementById('bulan_input').classList.remove('hidden');
        }
    }

    function removeLayanan(layananId) {
        Swal.fire({
            title: 'Hapus Layanan?',
            text: 'Apakah Anda yakin ingin menghapus layanan ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
            fetch(`/umkm/layanan/${layananId}/remove`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message || 'Layanan berhasil dihapus!', 'success');
                    document.getElementById(`layanan-${layananId}`).remove();
                } else {
                    showToast('Gagal menghapus layanan: ' + (data.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Terjadi kesalahan saat menghapus layanan', 'error');
            });
            }
        });
    }

    function editLayanan(id) {
        // Fetch data layanan
        fetch(`/umkm/layanan/${id}`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const layanan = data.data;
                
                // Set form title
                const formTitle = document.getElementById('layananFormTitle');
                if (formTitle) {
                    formTitle.textContent = 'Edit Layanan';
                }
                
                const submitText = document.getElementById('layananSubmitText');
                const submitIcon = document.getElementById('layananSubmitIcon');
                const cancelBtn = document.getElementById('layananCancelBtn');
                
                if (submitText) submitText.textContent = 'Update Layanan';
                if (submitIcon) {
                    submitIcon.classList.remove('fa-plus');
                    submitIcon.classList.add('fa-save');
                }
                if (cancelBtn) cancelBtn.classList.remove('hidden');
                
                // Set hidden ID
                document.getElementById('layanan_id').value = layanan.id;
                
                // Set form values
                document.getElementById('layanan_nama').value = layanan.nama;
                document.getElementById('layanan_price').value = layanan.price;
                document.getElementById('layanan_description').value = layanan.description || '';
                
                // Set photo preview if exists
                const photoPreview = document.getElementById('layanan_photo_preview');
                const photoPreviewImg = document.getElementById('layanan_photo_preview_img');
                if (layanan.photo_path) {
                    if (photoPreviewImg) {
                        photoPreviewImg.src = `/storage/${layanan.photo_path}`;
                    }
                    if (photoPreview) {
                        photoPreview.classList.remove('hidden');
                    }
                } else {
                    if (photoPreview) {
                        photoPreview.classList.add('hidden');
                    }
                }
                
                // Scroll to form
                document.getElementById('layananForm').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } else {
                showToast('Gagal memuat data layanan: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Terjadi kesalahan saat memuat data layanan', 'error');
        });
    }

    function resetLayananForm() {
        // Reset form
        document.getElementById('layananForm').reset();
        document.getElementById('layanan_id').value = '';
        
        const formTitle = document.getElementById('layananFormTitle');
        if (formTitle) {
            formTitle.textContent = 'Tambah Layanan Baru';
        }
        
        const submitText = document.getElementById('layananSubmitText');
        const submitIcon = document.getElementById('layananSubmitIcon');
        const cancelBtn = document.getElementById('layananCancelBtn');
        
        if (submitText) submitText.textContent = 'Tambah Layanan';
        if (submitIcon) {
            submitIcon.classList.remove('fa-save');
            submitIcon.classList.add('fa-plus');
        }
        if (cancelBtn) cancelBtn.classList.add('hidden');
        
        const photoPreview = document.getElementById('layanan_photo_preview');
        if (photoPreview) {
            photoPreview.classList.add('hidden');
        }
    }

    function previewLayananPhoto(input) {
        const preview = document.getElementById('layanan_photo_preview');
        const previewImg = document.getElementById('layanan_photo_preview_img');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                if (previewImg) {
                    previewImg.src = e.target.result;
                }
                if (preview) {
                    preview.classList.remove('hidden');
                }
            }
            
            reader.readAsDataURL(input.files[0]);
        } else {
            if (preview) {
                preview.classList.add('hidden');
            }
        }
    }

    function editKeuntungan(id) {
        // Fetch data keuntungan
        fetch(`/umkm/keuntungan/${id}`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const keuntungan = data.data;
                
                // Set modal title
                document.getElementById('keuntunganModalTitle').textContent = 'Edit Data Keuntungan';
                document.getElementById('keuntunganSubmitText').textContent = 'Update';
                
                // Set hidden ID
                document.getElementById('keuntungan_id').value = keuntungan.id;
                
                // Parse bulan (format: "January 2025" -> "2025-01")
                const bulanParts = keuntungan.bulan.split(' ');
                const bulanMap = {
                    'January': '01', 'February': '02', 'March': '03', 'April': '04',
                    'May': '05', 'June': '06', 'July': '07', 'August': '08',
                    'September': '09', 'October': '10', 'November': '11', 'December': '12'
                };
                const bulanValue = `${bulanParts[1]}-${bulanMap[bulanParts[0]]}`;
                
                // Set form values
                document.getElementById('periode_type').value = 'bulanan';
                document.getElementById('bulan').value = bulanValue;
                document.getElementById('pendapatan').value = keuntungan.pendapatan;
                document.getElementById('pengeluaran').value = keuntungan.pengeluaran;
                document.getElementById('jumlah_transaksi').value = keuntungan.jumlah_transaksi;
                
                // Show bulan input
                togglePeriodeInput();
                
                // Show modal
                document.getElementById('keuntunganModal').classList.remove('hidden');
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Gagal memuat data keuntungan: ' + (data.message || 'Unknown error'),
                    confirmButtonColor: '#009b97'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Terjadi kesalahan saat memuat data keuntungan',
                confirmButtonColor: '#009b97'
            });
        });
    }

    function deleteKeuntungan(id) {
        Swal.fire({
            title: 'Hapus Data Keuntungan?',
            text: 'Apakah Anda yakin ingin menghapus data keuntungan ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
            fetch(`/umkm/keuntungan/${id}/delete`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    showToast(data.message || 'Gagal menghapus data keuntungan', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Terjadi kesalahan saat menghapus data', 'error');
            });
            }
        });
    }

    // Form submissions (tetap sama seperti sebelumnya)
    document.addEventListener('DOMContentLoaded', function() {
        // Profile form
        const profileForm = document.getElementById('profileForm');
        if (profileForm) {
            profileForm.addEventListener('submit', function(e) {
                e.preventDefault();
                // Jangan ubah button menjadi loading, biarkan seperti semula agar SweetAlert bisa muncul
                const formData = new FormData(this);
                
                fetch('{{ route("umkm.profile.update") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message,
                            confirmButtonColor: '#009b97'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Gagal menyimpan profil: ' + (data.message || 'Unknown error'),
                            confirmButtonColor: '#009b97'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Gagal menyimpan profil',
                        confirmButtonColor: '#009b97'
                    });
                });
            });
        }

        // Keuntungan form
        const keuntunganForm = document.getElementById('keuntunganForm');
        if (keuntunganForm) {
            keuntunganForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const keuntunganId = document.getElementById('keuntungan_id').value;
                const formData = new FormData(this);
                
                // Jika ada ID, berarti edit mode
                if (keuntunganId) {
                    // Update existing
                    const updateData = {
                        pendapatan: formData.get('pendapatan'),
                        pengeluaran: formData.get('pengeluaran'),
                        jumlah_transaksi: formData.get('jumlah_transaksi')
                    };
                    
                    fetch(`/umkm/keuntungan/${keuntunganId}/update`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(updateData)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast(data.message, 'success');
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        } else {
                            showToast(data.message || 'Gagal memperbarui data keuntungan', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('Terjadi kesalahan saat memperbarui data', 'error');
                    });
                } else {
                    // Create new (kode yang sudah ada)
                    const periodeType = formData.get('periode_type');
                    if (periodeType === 'harian') {
                        formData.delete('minggu');
                        formData.delete('bulan');
                    } else if (periodeType === 'mingguan') {
                        formData.delete('tanggal');
                        formData.delete('bulan');
                    } else if (periodeType === 'bulanan') {
                        formData.delete('tanggal');
                        formData.delete('minggu');
                    }
                    
                    fetch('{{ route("umkm.keuntungan.store") }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast(data.message, 'success');
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        } else {
                            showToast(data.message || 'Gagal menyimpan data keuntungan', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('Terjadi kesalahan saat menyimpan data', 'error');
                    });
                }
            });
        }

        // Excel form
        const excelForm = document.getElementById('excelForm');
        if (excelForm) {
            excelForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                fetch('{{ route("umkm.excel.upload") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message,
                            confirmButtonColor: '#009b97'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Gagal mengupload file Excel: ' + (data.message || 'Unknown error'),
                            confirmButtonColor: '#009b97'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Gagal mengupload file Excel',
                        confirmButtonColor: '#009b97'
                    });
                });
            });
        }

        // Layanan form
        const layananForm = document.getElementById('layananForm');
        if (layananForm) {
            layananForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const layananId = document.getElementById('layanan_id').value;
                const formData = new FormData(this);
                const submitBtn = this.querySelector('button[type="submit"]');
                const submitIcon = document.getElementById('layananSubmitIcon');
                const submitText = document.getElementById('layananSubmitText');
                const originalIconClass = submitIcon ? submitIcon.className : '';
                const originalText = submitText ? submitText.textContent : '';
                
                // Show loading on button
                if (submitBtn) {
                    submitBtn.disabled = true;
                    if (submitIcon) {
                        submitIcon.className = 'fas fa-spinner fa-spin mr-2';
                    }
                    if (submitText) {
                        submitText.textContent = 'Mengupload...';
                    } else {
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Mengupload...';
                    }
                }
                
                // Jika ada ID, berarti edit mode
                if (layananId) {
                    // Buat FormData manual untuk memastikan semua field terkirim
                    const formData = new FormData();
                    formData.append('nama', document.getElementById('layanan_nama').value);
                    formData.append('price', document.getElementById('layanan_price').value);
                    formData.append('description', document.getElementById('layanan_description').value);
                    
                    // Hanya tambahkan photo jika ada file baru
                    const photoInput = document.getElementById('layanan_photo');
                    if (photoInput.files && photoInput.files[0]) {
                        formData.append('photo', photoInput.files[0]);
                    }
                    
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                    
                    // Update existing
                    fetch(`/umkm/layanan/${layananId}/update`, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Restore button
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            if (submitIcon) {
                                submitIcon.className = originalIconClass;
                            }
                            if (submitText) {
                                submitText.textContent = originalText;
                            } else {
                                submitBtn.innerHTML = '<i class="fas fa-plus mr-2"></i> Tambah Layanan';
                            }
                        }
                        
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: data.message || 'Layanan berhasil diperbarui!',
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
                                text: 'Gagal memperbarui layanan: ' + (data.message || 'Unknown error'),
                                confirmButtonColor: '#009b97'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        // Restore button
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            if (submitIcon) {
                                submitIcon.className = originalIconClass;
                            }
                            if (submitText) {
                                submitText.textContent = originalText;
                            } else {
                                submitBtn.innerHTML = '<i class="fas fa-plus mr-2"></i> Tambah Layanan';
                            }
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Terjadi kesalahan saat memperbarui layanan',
                            confirmButtonColor: '#009b97'
                        });
                    });
                } else {
                    // Create new (kode yang sudah ada)
                    fetch('{{ route("umkm.layanan.update") }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Restore button
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            if (submitIcon) {
                                submitIcon.className = originalIconClass;
                            }
                            if (submitText) {
                                submitText.textContent = originalText;
                            } else {
                                submitBtn.innerHTML = '<i class="fas fa-plus mr-2"></i> Tambah Layanan';
                            }
                        }
                        
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Upload Berhasil!',
                                text: data.message || 'Layanan berhasil ditambahkan!',
                                confirmButtonColor: '#009b97',
                                timer: 2500,
                                showConfirmButton: true,
                                confirmButtonText: 'OK'
                            }).then(() => {
                                // Reset form
                                layananForm.reset();
                                document.getElementById('layanan_id').value = '';
                                document.getElementById('layananFormTitle').textContent = 'Tambah Layanan Baru';
                                
                                // Reload to show new layanan
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: 'Gagal menambahkan layanan: ' + (data.message || 'Unknown error'),
                                confirmButtonColor: '#009b97'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        // Restore button
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            if (submitIcon) {
                                submitIcon.className = originalIconClass;
                            }
                            if (submitText) {
                                submitText.textContent = originalText;
                            } else {
                                submitBtn.innerHTML = '<i class="fas fa-plus mr-2"></i> Tambah Layanan';
                            }
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Terjadi kesalahan saat menambahkan layanan',
                            confirmButtonColor: '#009b97'
                        });
                    });
                }
            });
        }

        // Initialize chart
        fetch('{{ route("umkm.keuntungan.data") }}')
            .then(response => response.json())
            .then(data => {
                const ctx = document.getElementById('keuntunganChart').getContext('2d');
                keuntunganChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: 'Pendapatan',
                            data: data.pendapatan,
                            borderColor: 'rgb(75, 192, 192)',
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            tension: 0.1
                        }, {
                            label: 'Pengeluaran',
                            data: data.pengeluaran,
                            borderColor: 'rgb(255, 99, 132)',
                            backgroundColor: 'rgba(255, 99, 132, 0.2)',
                            tension: 0.1
                        }, {
                            label: 'Keuntungan Bersih',
                            data: data.keuntungan_bersih,
                            borderColor: 'rgb(54, 162, 235)',
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            tension: 0.1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            });
    });

    // Close modal when clicking outside
    document.querySelectorAll('[id$="Modal"]').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
            }
        });
    });

    // Tambahkan toast notification function jika belum ada
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
        
        setTimeout(() => {
            toast.classList.remove('translate-x-full');
        }, 100);
        
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
</script>
@endsection
