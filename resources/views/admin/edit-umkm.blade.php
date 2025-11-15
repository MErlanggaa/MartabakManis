@extends('layouts.app')

@section('title', 'Edit UMKM - Admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-500 rounded-xl flex items-center justify-center text-white">
                        <i class="fas fa-edit text-xl"></i>
                    </div>
                    Edit UMKM
                </h1>
                <p class="text-gray-600 mt-2">Ubah informasi UMKM: {{ $umkm->nama }}</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition-colors inline-flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.umkm.update', $umkm->id) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- User Information Card -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="fas fa-user text-blue-500"></i> Informasi User
                </h2>
                
                <div class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $umkm->user->name) }}" 
                               required
                               class="w-full px-4 py-2 border @error('name') border-red-300 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="{{ old('email', $umkm->user->email) }}" 
                               required
                               class="w-full px-4 py-2 border @error('email') border-red-300 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- UMKM Information Card -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="fas fa-store text-purple-500"></i> Informasi UMKM
                </h2>
                
                <div class="space-y-4">
                    <div>
                        <label for="nama_umkm" class="block text-sm font-medium text-gray-700 mb-2">
                            Nama UMKM <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="nama_umkm" 
                               name="nama_umkm" 
                               value="{{ old('nama_umkm', $umkm->nama) }}" 
                               required
                               class="w-full px-4 py-2 border @error('nama_umkm') border-red-300 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        @error('nama_umkm')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            Deskripsi <span class="text-red-500">*</span>
                        </label>
                        <textarea id="description" 
                                  name="description" 
                                  rows="4" 
                                  required
                                  class="w-full px-4 py-2 border @error('description') border-red-300 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">{{ old('description', $umkm->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="jenis_umkm" class="block text-sm font-medium text-gray-700 mb-2">
                            Jenis UMKM <span class="text-red-500">*</span>
                        </label>
                        <select id="jenis_umkm" 
                                name="jenis_umkm" 
                                required
                                class="w-full px-4 py-2 border @error('jenis_umkm') border-red-300 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="">Pilih Jenis UMKM</option>
                            <option value="Makanan & Minuman" {{ old('jenis_umkm', $umkm->jenis_umkm) == 'Makanan & Minuman' ? 'selected' : '' }}>Makanan & Minuman</option>
                            <option value="Fashion" {{ old('jenis_umkm', $umkm->jenis_umkm) == 'Fashion' ? 'selected' : '' }}>Fashion</option>
                            <option value="Kerajinan" {{ old('jenis_umkm', $umkm->jenis_umkm) == 'Kerajinan' ? 'selected' : '' }}>Kerajinan</option>
                            <option value="Jasa" {{ old('jenis_umkm', $umkm->jenis_umkm) == 'Jasa' ? 'selected' : '' }}>Jasa</option>
                            <option value="Pertanian" {{ old('jenis_umkm', $umkm->jenis_umkm) == 'Pertanian' ? 'selected' : '' }}>Pertanian</option>
                            <option value="Lainnya" {{ old('jenis_umkm', $umkm->jenis_umkm) == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                        @error('jenis_umkm')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="no_wa" class="block text-sm font-medium text-gray-700 mb-2">
                            Nomor WhatsApp
                        </label>
                        <input type="text" 
                               id="no_wa" 
                               name="no_wa" 
                               value="{{ old('no_wa', $umkm->no_wa) }}" 
                               placeholder="Contoh: 081234567890 atau +6281234567890"
                               class="w-full px-4 py-2 border @error('no_wa') border-red-300 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        @error('no_wa')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Format: nomor telepon (dapat menggunakan +62 atau 0)</p>
                    </div>

                    <div>
                        <label for="instagram_url" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fab fa-instagram text-pink-500"></i> Instagram URL
                        </label>
                        <input type="url" 
                               id="instagram_url" 
                               name="instagram_url" 
                               value="{{ old('instagram_url', $umkm->instagram_url) }}" 
                               placeholder="https://instagram.com/username"
                               class="w-full px-4 py-2 border @error('instagram_url') border-red-300 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        @error('instagram_url')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Link profil Instagram (opsional)</p>
                    </div>

                    <div>
                        <label for="shopee_url" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-shopping-bag text-orange-500"></i> Shopee URL
                        </label>
                        <input type="url" 
                               id="shopee_url" 
                               name="shopee_url" 
                               value="{{ old('shopee_url', $umkm->shopee_url) }}" 
                               placeholder="https://shopee.co.id/username"
                               class="w-full px-4 py-2 border @error('shopee_url') border-red-300 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        @error('shopee_url')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Link toko Shopee (opsional)</p>
                    </div>

                    <div>
                        <label for="tokopedia_url" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-store text-green-500"></i> Tokopedia URL
                        </label>
                        <input type="url" 
                               id="tokopedia_url" 
                               name="tokopedia_url" 
                               value="{{ old('tokopedia_url', $umkm->tokopedia_url) }}" 
                               placeholder="https://tokopedia.com/username"
                               class="w-full px-4 py-2 border @error('tokopedia_url') border-red-300 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        @error('tokopedia_url')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Link toko Tokopedia (opsional)</p>
                    </div>

                    <div>
                        <label for="photo" class="block text-sm font-medium text-gray-700 mb-2">
                            Foto UMKM
                        </label>
                        
                        <!-- Current Photo Preview -->
                        @if($umkm->photo_path)
                            <div class="mb-3">
                                <p class="text-sm text-gray-600 mb-2">Foto Saat Ini:</p>
                                <img src="{{ asset('storage/' . $umkm->photo_path) }}" 
                                     alt="{{ $umkm->nama }}" 
                                     class="w-32 h-32 object-cover rounded-lg border border-gray-300">
                            </div>
                        @endif
                        
                        <label for="photo" class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <i class="fas fa-cloud-upload-alt text-gray-400 text-3xl mb-2"></i>
                                <p class="text-sm text-gray-500">Klik untuk upload foto baru</p>
                                <p class="text-xs text-gray-400 mt-1">PNG, JPG, GIF (Max 2MB)</p>
                            </div>
                            <input type="file" 
                                   id="photo" 
                                   name="photo" 
                                   accept="image/*" 
                                   class="hidden"
                                   onchange="previewImage(this)">
                        </label>
                        <div id="image-preview" class="mt-3 hidden">
                            <p class="text-sm text-gray-600 mb-2">Preview Foto Baru:</p>
                            <img id="preview-img" src="" alt="Preview" class="w-32 h-32 object-cover rounded-lg border border-gray-300">
                        </div>
                        @error('photo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Location Section -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <i class="fas fa-map-marker-alt text-red-500"></i> Lokasi UMKM
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div class="md:col-span-2">
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                        Alamat
                    </label>
                    <div class="flex gap-2">
                        <input type="text" 
                               id="address" 
                               placeholder="Masukkan alamat lengkap..."
                               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                        <button type="button" 
                                onclick="geocodeAddress()"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-search"></i> Cari
                        </button>
                    </div>
                </div>
                
                <div>
                    <label for="latitude" class="block text-sm font-medium text-gray-700 mb-2">
                        Latitude <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           step="any" 
                           id="latitude" 
                           name="latitude" 
                           value="{{ old('latitude', $umkm->latitude) }}" 
                           required 
                           readonly
                           class="w-full px-4 py-2 border @error('latitude') border-red-300 @else border-gray-300 @enderror rounded-lg bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500">
                    @error('latitude')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="longitude" class="block text-sm font-medium text-gray-700 mb-2">
                        Longitude <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           step="any" 
                           id="longitude" 
                           name="longitude" 
                           value="{{ old('longitude', $umkm->longitude) }}" 
                           required 
                           readonly
                           class="w-full px-4 py-2 border @error('longitude') border-red-300 @else border-gray-300 @enderror rounded-lg bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500">
                    @error('longitude')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <!-- Map Container -->
            <div>
                <div id="map" class="w-full h-96 rounded-lg border border-gray-300"></div>
                <p class="text-xs text-gray-500 mt-2">
                    <i class="fas fa-info-circle"></i> Klik pada peta untuk memilih lokasi atau gunakan pencarian alamat
                </p>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.dashboard') }}" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Batal
            </a>
            <button type="submit" class="px-6 py-2 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white rounded-lg transition-all shadow-lg hover:shadow-xl inline-flex items-center gap-2">
                <i class="fas fa-save"></i> Update UMKM
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script>
    let map;
    let marker;

    function previewImage(input) {
        const preview = document.getElementById('image-preview');
        const previewImg = document.getElementById('preview-img');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                preview.classList.remove('hidden');
            }
            
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.classList.add('hidden');
        }
    }

    // Initialize map
    function initMap() {
        const lat = parseFloat(document.getElementById('latitude').value) || -6.200000;
        const lng = parseFloat(document.getElementById('longitude').value) || 106.816666;
        
        map = L.map('map').setView([lat, lng], 13);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Add existing marker
        marker = L.marker([lat, lng]).addTo(map);
        marker.bindPopup('{{ $umkm->nama }}').openPopup();

        // Add click event to map
        map.on('click', function(e) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;
            
            document.getElementById('latitude').value = lat.toFixed(8);
            document.getElementById('longitude').value = lng.toFixed(8);
            
            if (marker) {
                map.removeLayer(marker);
            }
            marker = L.marker([lat, lng]).addTo(map);
        });
    }

    // Geocode address to coordinates
    function geocodeAddress() {
        const address = document.getElementById('address').value;
        
        if (!address) {
            showToast('Masukkan alamat terlebih dahulu', 'error');
            return;
        }

        // Show loading
        const searchBtn = event.target;
        const originalText = searchBtn.innerHTML;
        searchBtn.disabled = true;
        searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mencari...';

        fetch('/api/geocode', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ address: address })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const lat = parseFloat(data.latitude);
                const lng = parseFloat(data.longitude);
                
                document.getElementById('latitude').value = lat.toFixed(8);
                document.getElementById('longitude').value = lng.toFixed(8);
                
                map.setView([lat, lng], 15);
                
                if (marker) {
                    map.removeLayer(marker);
                }
                marker = L.marker([lat, lng]).addTo(map);
                
                showToast('Lokasi berhasil ditemukan!', 'success');
            } else {
                showToast(data.message || 'Alamat tidak ditemukan', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Terjadi kesalahan saat mencari alamat', 'error');
        })
        .finally(() => {
            searchBtn.disabled = false;
            searchBtn.innerHTML = '<i class="fas fa-search"></i> Cari';
        });
    }

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

    // Initialize map when page loads
    document.addEventListener('DOMContentLoaded', function() {
        initMap();
    });
</script>
@endsection
