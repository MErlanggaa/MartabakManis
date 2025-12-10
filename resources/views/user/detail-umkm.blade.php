@extends('layouts.app')

@section('title', $umkm->nama . ' - Detail UMKM')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    <!-- Breadcrumbs -->
    <nav class="mb-6 text-sm text-gray-600">
        <ol class="flex items-center space-x-2">
            <li><a href="{{ route('public.katalog') }}" class="hover:text-blue-600">Home</a></li>
            <li><i class="fas fa-chevron-right text-xs"></i></li>
            <li><a href="{{ route('public.katalog', ['kategori' => $umkm->jenis_umkm]) }}" class="hover:text-blue-600">Kategori {{ $umkm->jenis_umkm }}</a></li>
            <li><i class="fas fa-chevron-right text-xs"></i></li>
            <li><span class="text-gray-900 font-medium">Detail UMKM {{ $umkm->nama }}</span></li>
        </ol>
    </nav>

    <!-- Header + Actions -->
    <div class="bg-white rounded-xl shadow-sm p-4 md:p-6 mb-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-purple-500 to-blue-500 flex items-center justify-center text-white">
                    <i class="fas fa-store text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900">{{ $umkm->nama }}</h1>
                    <div class="mt-1 flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-2 bg-blue-50 text-blue-700 text-xs font-medium px-3 py-1 rounded-full">
                            <i class="fas fa-tag"></i> {{ $umkm->jenis_umkm }}
                        </span>
                        <span class="inline-flex items-center gap-2 bg-green-50 text-green-700 text-xs font-medium px-3 py-1 rounded-full">
                            <i class="fas fa-heart"></i> {{ $umkm->favorit_count }} Favorit
                        </span>
                        <span class="inline-flex items-center gap-2 bg-[#009b97]/10 text-[#009b97] text-xs font-medium px-3 py-1 rounded-full">
                            <i class="fas fa-eye"></i> {{ number_format($umkm->views ?? 0, 0, ',', '.') }} Dilihat
                        </span>
                        <span class="inline-flex items-center gap-2 bg-gray-100 text-gray-700 text-xs font-medium px-3 py-1 rounded-full">
                            <i class="fas fa-calendar"></i> Bergabung {{ $umkm->created_at->format('d M Y') }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                @php
                    $isFav = auth()->check() && in_array($umkm->id, auth()->user()->favorites ?? []);
                @endphp

                @auth
                <!-- Follow Button -->
                @php
                    $isFollowing = auth()->user()->following->contains($umkm->id);
                @endphp
                <form action="{{ route('user.follow.toggle', $umkm->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" 
                            class="inline-flex items-center gap-2 border {{ $isFollowing ? 'border-gray-300 bg-gray-50 text-gray-600' : 'border-blue-500 bg-blue-500 text-white' }} hover:{{ $isFollowing ? 'bg-gray-100' : 'bg-blue-600' }} px-4 py-2 rounded-lg transition-colors">
                        <i class="{{ $isFollowing ? 'fas fa-check' : 'fas fa-plus' }}"></i>
                        <span>{{ $isFollowing ? 'Mengikuti' : 'Ikuti' }}</span>
                    </button>
                </form>

                <button class="favorite-btn inline-flex items-center gap-2 border {{ $isFav ? 'border-red-300 bg-red-50 text-red-600' : 'border-red-300 text-red-600' }} hover:bg-red-50 px-4 py-2 rounded-lg transition-colors"
                        data-umkm-id="{{ $umkm->id }}">
                    <i class="{{ $isFav ? 'fas' : 'far' }} fa-heart"></i>
                    <span>Favorit</span>
                </button>
                @endauth
                <a href="{{ route('public.katalog') }}" class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Top Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Left: Image + Description + Services + Map -->
        <div class="lg:col-span-2 space-y-4">
            <!-- Cover / Photo -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="w-full h-64 md:h-80 bg-gray-200">
                    @if($umkm->photo_path)
                        <img src="{{ Storage::url($umkm->photo_path) }}" alt="{{ $umkm->nama }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-400">
                            <i class="fas fa-image text-4xl"></i>
                        </div>
                    @endif
                </div>
                <div class="p-4 md:p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-2">Tentang UMKM</h2>
                    <p class="text-gray-700 leading-relaxed">{{ $umkm->description }}</p>
                </div>
            </div>

            <!-- Services -->
            @if($umkm->layanan->count() > 0)
                <div class="bg-white rounded-xl shadow-sm p-4 md:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-list text-blue-600"></i> Layanan yang Ditawarkan
                        </h2>
                        <span class="text-sm text-gray-500">{{ $umkm->layanan->count() }} layanan</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($umkm->layanan as $layanan)
                            <div class="border border-gray-200 rounded-xl overflow-hidden hover:shadow-md transition-shadow bg-white">
                                <div class="w-full h-40 bg-gray-100">
                                    @if($layanan->photo_path)
                                        <img src="{{ Storage::url($layanan->photo_path) }}" alt="{{ $layanan->nama }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-400">
                                            <i class="fas fa-image text-2xl"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="p-4">
                                    <h3 class="font-semibold text-gray-900 line-clamp-2">{{ Str::words($layanan->nama, 14, '...') }}</h3>
                                    @if($layanan->description)
                                        <p class="text-sm text-gray-600 mt-1 line-clamp-3">{{ $layanan->description }}</p>
                                    @endif
                                    <div class="mt-3 flex items-center justify-between">
                                        <span class="text-green-600 font-semibold">
                                            Rp {{ number_format($layanan->price, 0, ',', '.') }}
                                        </span>
                                      
                                    </div>
                                    <button onclick="event.stopPropagation(); window.location.href='{{ route('public.layanan.show', $layanan->id) }}'" 
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white text-center py-2 rounded-lg font-medium transition-colors text-sm">
                        Lihat Detail
                    </button>
                                </div>
                            </div>
                         
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Map -->
            <div class="bg-white rounded-xl shadow-sm p-4 md:p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="fas fa-map-marker-alt text-red-500"></i> Lokasi
                </h2>
                @if($umkm->latitude && $umkm->longitude)
                <div id="map" class="w-full h-72 rounded-lg border border-gray-200"></div>
                <div class="mt-2 text-sm text-gray-600">
                    <i class="fas fa-crosshairs"></i>
                    Koordinat: {{ number_format($umkm->latitude, 6) }}, {{ number_format($umkm->longitude, 6) }}
                </div>
                @else
                    <div class="mb-3 p-4 bg-red-50 rounded-lg border border-red-200">
                        <p class="text-sm text-red-800">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            Lokasi tidak tersedia. Koordinat latitude dan longitude belum diatur.
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right: Sidebar -->
        <div class="space-y-4">
            <!-- Contact -->
            <div class="bg-white rounded-xl shadow-sm p-4 md:p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="fas fa-phone text-green-600"></i> Informasi Kontak
                </h2>
                <div class="space-y-2 text-gray-700">
                    <p><span class="text-gray-500">Pemilik:</span> <span class="font-medium">{{ $umkm->user->name }}</span></p>
                    <p><span class="text-gray-500">Email:</span> <span class="font-medium">{{ $umkm->user->email }}</span></p>
                    <p>
                        <span class="text-gray-500">Nomor Telepon:</span> 
                        @if($umkm->no_wa)
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $umkm->no_wa) }}" 
                               target="_blank" 
                               class="font-medium text-green-600 hover:text-green-700 inline-flex items-center gap-1">
                                <i class="fab fa-whatsapp"></i> {{ $umkm->no_wa }}
                            </a>
                        @else
                            <span class="font-medium text-gray-400">-</span>
                        @endif
                    </p>
                    <p><span class="text-gray-500">Jenis:</span> <span class="font-medium">{{ $umkm->jenis_umkm }}</span></p>
                </div>
            </div>

            <!-- Social Media & E-commerce -->
            @if($umkm->instagram_url || $umkm->shopee_url || $umkm->tokopedia_url)
            <div class="bg-white rounded-xl shadow-sm p-4 md:p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="fas fa-share-alt text-blue-600"></i> Media Sosial & Toko Online
                </h2>
                <div class="space-y-3">
                    @if($umkm->instagram_url)
                        <a href="{{ $umkm->instagram_url }}" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           class="flex items-center gap-3 p-3 bg-gradient-to-r from-pink-50 to-purple-50 hover:from-pink-100 hover:to-purple-100 rounded-lg transition-all border border-pink-200 hover:border-pink-300 group">
                            <div class="w-10 h-10 bg-gradient-to-br from-pink-500 to-purple-600 rounded-lg flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                                <i class="fab fa-instagram text-xl"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-gray-900">Instagram</p>
                                <p class="text-xs text-gray-600 truncate">{{ $umkm->instagram_url }}</p>
                            </div>
                            <i class="fas fa-external-link-alt text-gray-400 group-hover:text-pink-600 transition-colors"></i>
                        </a>
                    @endif
                    
                    @if($umkm->shopee_url)
                        <a href="{{ $umkm->shopee_url }}" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           class="flex items-center gap-3 p-3 bg-gradient-to-r from-orange-50 to-red-50 hover:from-orange-100 hover:to-red-100 rounded-lg transition-all border border-orange-200 hover:border-orange-300 group">
                            <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-red-600 rounded-lg flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                                <i class="fas fa-shopping-bag text-xl"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-gray-900">Shopee</p>
                                <p class="text-xs text-gray-600 truncate">{{ $umkm->shopee_url }}</p>
                            </div>
                            <i class="fas fa-external-link-alt text-gray-400 group-hover:text-orange-600 transition-colors"></i>
                        </a>
                    @endif
                    
                    @if($umkm->tokopedia_url)
                        <a href="{{ $umkm->tokopedia_url }}" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           class="flex items-center gap-3 p-3 bg-gradient-to-r from-green-50 to-emerald-50 hover:from-green-100 hover:to-emerald-100 rounded-lg transition-all border border-green-200 hover:border-green-300 group">
                            <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                                <i class="fas fa-store text-xl"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-gray-900">Tokopedia</p>
                                <p class="text-xs text-gray-600 truncate">{{ $umkm->tokopedia_url }}</p>
                            </div>
                            <i class="fas fa-external-link-alt text-gray-400 group-hover:text-green-600 transition-colors"></i>
                        </a>
                    @endif
                </div>
            </div>
            @endif

            <!-- Alamat Lengkap -->
            <div class="bg-white rounded-xl shadow-sm p-4 md:p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="fas fa-map-marker-alt text-red-500"></i> Alamat Lengkap
                </h2>
                @if($umkm->latitude && $umkm->longitude)
                    <div class="space-y-2 text-gray-700">
                        <p class="text-sm text-gray-800 leading-relaxed address-full-text" 
                           data-lat="{{ $umkm->latitude }}" 
                           data-lng="{{ $umkm->longitude }}">
                            <i class="fas fa-spinner fa-spin text-blue-500"></i> Memuat alamat...
                        </p>
                        <a href="https://www.google.com/maps?q={{ $umkm->latitude }},{{ $umkm->longitude }}" 
                           target="_blank"
                           class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 text-sm font-medium mt-2">
                            <i class="fas fa-external-link-alt"></i> Buka di Google Maps
                        </a>
                    </div>
                @else
                    <div class="text-sm text-gray-500">
                        <p class="flex items-center gap-2">
                            <i class="fas fa-exclamation-circle text-gray-400"></i>
                            Koordinat lokasi belum diatur
                        </p>
                    </div>
                @endif
            </div>

            <!-- Stats -->
            <div class="bg-white rounded-xl shadow-sm p-4 md:p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="fas fa-chart-bar text-blue-600"></i> Statistik
                </h2>
                <div class="grid grid-cols-3 gap-3 text-center">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="text-2xl font-bold text-blue-700">{{ $umkm->favorit_count }}</div>
                        <div class="text-xs text-blue-700/80">Favorit</div>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4">
                        <div class="text-2xl font-bold text-green-700">{{ $umkm->layanan->count() }}</div>
                        <div class="text-xs text-green-700/80">Layanan</div>
                    </div>
                    <div class="bg-[#009b97]/10 rounded-lg p-4">
                        <div class="text-2xl font-bold text-[#009b97]">{{ number_format($umkm->views ?? 0, 0, ',', '.') }}</div>
                        <div class="text-xs text-[#009b97]/80">Dilihat</div>
                    </div>
                </div>
            </div>

            <!-- Distance Calculator -->
            @auth
                <div class="bg-white rounded-xl shadow-sm p-4 md:p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center gap-2">
                        <i class="fas fa-route text-purple-600"></i> Jarak dari Lokasi Anda
                    </h2>
                    <button class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors"
                            onclick="calculateDistance()">
                        <i class="fas fa-location-arrow"></i> Hitung Jarak
                    </button>
                    <div id="distance-result" class="hidden mt-3">
                        <div class="bg-blue-50 border border-blue-200 text-blue-800 text-sm px-4 py-3 rounded-lg">
                            <i class="fas fa-info-circle"></i>
                            <span id="distance-text" class="ml-2"></span>
                        </div>
                    </div>
                </div>
            @endauth

            <!-- Related -->
            <div class="bg-white rounded-xl shadow-sm p-4 md:p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="fas fa-store text-emerald-600"></i> UMKM Serupa
                    <span class="text-sm font-normal text-gray-500">({{ $umkm->jenis_umkm }})</span>
                </h2>
                
                @if($similarUmkm->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($similarUmkm as $similar)
                            <a href="{{ route('public.umkm.show', $similar->id) }}" 
                               class="block border border-gray-200 rounded-lg overflow-hidden hover:shadow-md transition-shadow bg-white group">
                                <div class="w-full h-32 bg-gray-100 overflow-hidden">
                                    @if($similar->photo_path)
                                        <img src="{{ Storage::url($similar->photo_path) }}" 
                                             alt="{{ $similar->nama }}" 
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-400">
                                            <i class="fas fa-store text-2xl"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="p-3">
                                    <h3 class="font-semibold text-gray-900 text-sm mb-1 line-clamp-1 group-hover:text-blue-600 transition-colors">
                                        {{ $similar->nama }}
                                    </h3>
                                    <p class="text-xs text-gray-600 line-clamp-2 mb-2">
                                        {{ Str::limit($similar->description, 60) }}
                                    </p>
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded">
                                            {{ $similar->jenis_umkm }}
                                        </span>
                                        @if($similar->layanan->count() > 0)
                                            <span class="text-xs text-gray-500">
                                                <i class="fas fa-list"></i> {{ $similar->layanan->count() }} layanan
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6">
                        <i class="fas fa-store text-gray-300 text-4xl mb-3"></i>
                        <p class="text-gray-500 text-sm">Belum ada UMKM lain dengan kategori yang sama</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Comments Section -->
    <div class="mt-8 bg-white rounded-xl shadow-sm p-4 md:p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-comments text-[#009b97]"></i> Komentar & Ulasan
                </h2>
                <div class="mt-2 flex items-center gap-4 text-sm text-gray-600">
                    <div class="flex items-center gap-2">
                        <div class="flex items-center">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star {{ $i <= round($averageRating) ? 'text-yellow-400' : 'text-gray-300' }} text-sm"></i>
                            @endfor
                        </div>
                        <span class="font-semibold text-gray-900">{{ number_format($averageRating, 1) }}</span>
                    </div>
                    <span class="text-gray-500">({{ $totalComments }} ulasan)</span>
                </div>
            </div>
        </div>

        <!-- Comment Form (only for authenticated users) -->
        @auth
            <div class="mb-8 border-b border-gray-200 pb-6">
                @if($userComment)
                    <!-- Edit Comment Form -->
                    <div id="edit-comment-form" class="bg-gray-50 rounded-lg p-4 md:p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <i class="fas fa-edit text-[#009b97]"></i> Edit Komentar Anda
                        </h3>
                        <form id="updateCommentForm" onsubmit="updateComment(event, {{ $userComment->id }})">
                            @csrf
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
                                <div class="flex items-center gap-2" id="rating-stars-edit">
                                    @for($i = 5; $i >= 1; $i--)
                                        <button type="button" onclick="setRating({{ $i }}, 'edit')" class="rating-star-edit text-2xl focus:outline-none">
                                            <i class="far fa-star text-gray-300 hover:text-yellow-400"></i>
                                        </button>
                                    @endfor
                                </div>
                                <input type="hidden" name="rating" id="rating-input-edit" value="{{ $userComment->rating }}" required>
                            </div>
                            <div class="mb-4">
                                <label for="comment-edit" class="block text-sm font-medium text-gray-700 mb-2">Komentar</label>
                                <textarea id="comment-edit" name="comment" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#009b97] focus:border-[#009b97] resize-none" required>{{ $userComment->comment }}</textarea>
                                <p class="text-xs text-gray-500 mt-1">Minimal 10 karakter, maksimal 1000 karakter</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <button type="submit" class="bg-[#009b97] hover:bg-[#007a77] text-white px-6 py-2 rounded-lg font-semibold transition-colors">
                                    <i class="fas fa-save mr-2"></i> Simpan Perubahan
                                </button>
                                <button type="button" onclick="cancelEdit()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg font-semibold transition-colors">
                                    Batal
                                </button>
                                <button type="button" onclick="deleteComment({{ $userComment->id }})" class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-lg font-semibold transition-colors">
                                    <i class="fas fa-trash mr-2"></i> Hapus
                                </button>
                            </div>
                        </form>
                    </div>
                    <div id="view-comment" class="hidden">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="font-semibold text-gray-900">{{ Auth::user()->name }}</span>
                                        <div class="flex items-center">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star {{ $i <= $userComment->rating ? 'text-yellow-400' : 'text-gray-300' }} text-xs"></i>
                                            @endfor
                                        </div>
                                        <span class="text-xs text-gray-500">{{ $userComment->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-gray-700">{{ $userComment->comment }}</p>
                                </div>
                                <button onclick="showEditForm()" class="text-[#009b97] hover:text-[#007a77] ml-4">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- New Comment Form -->
                    <div class="bg-gray-50 rounded-lg p-4 md:p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <i class="fas fa-comment-dots text-[#009b97]"></i> Tulis Komentar
                        </h3>
                        <form id="commentForm" onsubmit="submitComment(event, {{ $umkm->id }})">
                            @csrf
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
                                <div class="flex items-center gap-2" id="rating-stars">
                                    @for($i = 5; $i >= 1; $i--)
                                        <button type="button" onclick="setRating({{ $i }}, 'new')" class="rating-star text-2xl focus:outline-none">
                                            <i class="far fa-star text-gray-300 hover:text-yellow-400"></i>
                                        </button>
                                    @endfor
                                </div>
                                <input type="hidden" name="rating" id="rating-input" value="5" required>
                            </div>
                            <div class="mb-4">
                                <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">Komentar</label>
                                <textarea id="comment" name="comment" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#009b97] focus:border-[#009b97] resize-none" placeholder="Bagikan pengalaman Anda tentang UMKM ini..." required></textarea>
                                <p class="text-xs text-gray-500 mt-1">Minimal 10 karakter, maksimal 1000 karakter</p>
                            </div>
                            <button type="submit" class="bg-[#009b97] hover:bg-[#007a77] text-white px-6 py-2 rounded-lg font-semibold transition-colors">
                                <i class="fas fa-paper-plane mr-2"></i> Kirim Komentar
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        @else
            <div class="mb-8 bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
                <p class="text-gray-700">
                    <a href="{{ route('login') }}" class="text-[#009b97] hover:text-[#007a77] font-semibold">Login</a> untuk menulis komentar dan memberikan ulasan
                </p>
            </div>
        @endauth

        <!-- Comments List -->
        <div id="comments-list">
            @if($comments->count() > 0)
                <div class="space-y-4">
                    @foreach($comments as $comment)
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow comment-item" data-comment-id="{{ $comment->id }}">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#009b97] to-[#039b00] flex items-center justify-center text-white font-semibold">
                                            {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <span class="font-semibold text-gray-900">{{ $comment->user->name }}</span>
                                            <div class="flex items-center gap-1">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="fas fa-star {{ $i <= $comment->rating ? 'text-yellow-400' : 'text-gray-300' }} text-xs"></i>
                                                @endfor
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-gray-700 mb-2">{{ $comment->comment }}</p>
                                    <p class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $comments->links() }}
                </div>
            @else
                <div class="text-center py-8">
                    <i class="fas fa-comments text-gray-300 text-4xl mb-3"></i>
                    <p class="text-gray-500">Belum ada komentar untuk UMKM ini</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let map;
    let userLocation = null;

    // Initialize map
    function initMap() {
        map = L.map('map').setView([{{ $umkm->latitude }}, {{ $umkm->longitude }}], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        const umkmMarker = L.marker([{{ $umkm->latitude }}, {{ $umkm->longitude }}]).addTo(map);
        
        // Build popup content with buttons
        const popupContent = `
            <div style="min-width: 200px; padding: 8px;">
                <strong style="font-size: 14px; color: #1f2937; display: block; margin-bottom: 6px;">Nama UMKM :{{ $umkm->nama }}</strong>
                <span style="font-size: 12px; color: #6b7280; display: block; margin-bottom: 4px;">Kategori UMKM : {{ $umkm->jenis_umkm }}</span>
                <small style="font-size: 11px; color: #9ca3af; display: block; margin-bottom: 10px;">Deskripsi UMKM : {{ Str::limit($umkm->description, 80) }}</small>
                <div style="display: flex; flex-direction: column; gap: 6px; margin-top: 10px;">
               
                    <a href="https://www.google.com/maps?q={{ $umkm->latitude }},{{ $umkm->longitude }}" 
                       target="_blank"
                       style="display: block; text-align: center; padding: 6px 12px; background-color: #10b981; color: white; text-decoration: none; border-radius: 6px; font-size: 12px; font-weight: 500; transition: background-color 0.2s;"
                       onmouseover="this.style.backgroundColor='#059669'" 
                       onmouseout="this.style.backgroundColor='#10b981'">
                        <i class="fas fa-map-marked-alt"></i> Buka di Google Maps
                    </a>
                </div>
            </div>
        `;
        
        umkmMarker.bindPopup(popupContent);
        
        // Open popup automatically when map loads
        umkmMarker.openPopup();

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                userLocation = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };

                const userMarker = L.marker([userLocation.lat, userLocation.lng], {
                    icon: L.divIcon({
                        className: 'bg-transparent',
                        html: '<i class="fas fa-user-circle fa-2x text-purple-600"></i>',
                        iconSize: [30, 30]
                    })
                }).addTo(map);

                userMarker.bindPopup('Lokasi Anda');

                const group = new L.featureGroup([umkmMarker, userMarker]);
                map.fitBounds(group.getBounds().pad(0.15));
                
                // Reopen popup after fitBounds
                setTimeout(() => {
                    umkmMarker.openPopup();
                }, 500);
            });
        }
    }

    // Favorite
    document.querySelectorAll('.favorite-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const umkmId = this.dataset.umkmId;
            toggleFavorite(umkmId, this);
        });
    });

    function toggleFavorite(umkmId, btn) {
        fetch(`/user/favorite/${umkmId}/toggle`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const icon = btn.querySelector('i');
                if (icon.classList.contains('far')) {
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                    btn.classList.add('bg-red-50','border-red-300');
                } else {
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                    btn.classList.remove('bg-red-50','border-red-300');
                }
                showToast(data.message, 'success');
            }
        });
    }

    // Distance
    function calculateDistance() {
        if (!userLocation) {
            // Try to get user location first
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    userLocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    // Retry after getting location
                    fetchDistance();
                }, function(error) {
                    showToast('Lokasi Anda tidak tersedia. Pastikan GPS aktif dan izinkan akses lokasi.', 'warning');
                });
            } else {
                showToast('Lokasi Anda tidak tersedia. Pastikan GPS aktif.', 'warning');
            }
            return;
        }
        fetchDistance();
    }
    
    function fetchDistance() {
        if (!userLocation) {
            showToast('Lokasi Anda tidak tersedia.', 'warning');
            return;
        }
        
        const distanceBtn = document.querySelector('button[onclick="calculateDistance()"]');
        const originalBtnText = distanceBtn ? distanceBtn.innerHTML : '';
        
        if (distanceBtn) {
            distanceBtn.disabled = true;
            distanceBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menghitung...';
        }
        
        fetch(`/user/distance/{{ $umkm->id }}/${userLocation.lat}/${userLocation.lng}`)
            .then(response => response.json())
            .then(data => {
                if (distanceBtn) {
                    distanceBtn.disabled = false;
                    distanceBtn.innerHTML = originalBtnText;
                }
                
                if (data.distance !== undefined) {
                    document.getElementById('distance-text').textContent = `Jarak: ${data.distance} km`;
                    document.getElementById('distance-result').classList.remove('hidden');
                } else {
                    showToast('Gagal menghitung jarak', 'error');
                }
            })
            .catch((error) => {
                if (distanceBtn) {
                    distanceBtn.disabled = false;
                    distanceBtn.innerHTML = originalBtnText;
                }
                console.error('Error:', error);
                showToast('Gagal menghitung jarak', 'error');
            });
    }

    // Toast with Tailwind
    function showToast(message, type = 'success') {
        const color = type === 'error' ? 'red' : type === 'warning' ? 'yellow' : 'green';
        const el = document.createElement('div');
        el.className = `fixed top-4 right-4 z-[1000] bg-${color}-100 text-${color}-800 border border-${color}-200 px-4 py-3 rounded-lg shadow-lg`;
        el.textContent = message;
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 3000);
    }

    document.addEventListener('DOMContentLoaded', initMap);
    
    // Reverse Geocoding - Load addresses from coordinates (same function as katalog)
    async function fetchAddressFromCoordinates(lat, lng) {
        try {
            // Use OpenStreetMap Nominatim API (free, no API key needed)
            const response = await fetch(
                `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1&accept-language=id`,
                {
                    headers: {
                        'User-Agent': 'UMKM-App/1.0' // Required by Nominatim
                    }
                }
            );
            
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            
            const data = await response.json();
            
            if (data && data.display_name) {
                // Format address to be more readable
                let address = data.display_name;
                
                // Try to extract and format Indonesian address components
                if (data.address) {
                    const addr = data.address;
                    const addressParts = [];
                    
                    // House number and road
                    if (addr.house_number) addressParts.push('No. ' + addr.house_number);
                    if (addr.road) addressParts.push(addr.road);
                    
                    // RT/RW if available in display_name
                    const rtMatch = address.match(/RT\s*\.?\s*\d+/i);
                    const rwMatch = address.match(/RW\s*\.?\s*\d+/i);
                    if (rtMatch) addressParts.push(rtMatch[0]);
                    if (rwMatch) addressParts.push(rwMatch[0]);
                    
                    // Kelurahan/Suburb
                    if (addr.suburb || addr.neighbourhood || addr.village) {
                        addressParts.push('Kel. ' + (addr.suburb || addr.neighbourhood || addr.village));
                    }
                    
                    // Kecamatan/City District
                    if (addr.city_district && addr.city_district !== (addr.suburb || addr.neighbourhood)) {
                        addressParts.push('Kec. ' + addr.city_district);
                    }
                    
                    // City
                    if (addr.city || addr.town || addr.municipality) {
                        addressParts.push(addr.city || addr.town || addr.municipality);
                    }
                    
                    // Province/State
                    if (addr.state) {
                        // Handle Jakarta
                        if (addr.state.includes('Jakarta') || addr.state.includes('DKI')) {
                            addressParts.push('DKI Jakarta');
                        } else {
                            addressParts.push(addr.state);
                        }
                    }
                    
                    // Postal code
                    if (addr.postcode) {
                        addressParts.push(addr.postcode);
                    }
                    
                    // Use formatted address if we have parts, otherwise use display_name
                    if (addressParts.length > 0) {
                        address = addressParts.join(', ');
                    }
                }
                
                return address;
            } else {
                throw new Error('No address found');
            }
        } catch (error) {
            console.error('Error fetching address:', error);
            return null;
        }
    }
    
    // Load address for detail-umkm page
    async function loadAddressFromCoordinates() {
        const addressElement = document.querySelector('.address-full-text');
        if (!addressElement) return;
        
        const lat = addressElement.getAttribute('data-lat');
        const lng = addressElement.getAttribute('data-lng');
        
        if (!lat || !lng) {
            addressElement.innerHTML = 'Lokasi tidak tersedia';
            addressElement.classList.remove('text-blue-500');
            addressElement.classList.add('text-gray-400');
            return;
        }
        
        const address = await fetchAddressFromCoordinates(lat, lng);
        
        if (address) {
            addressElement.innerHTML = address;
            addressElement.classList.remove('text-blue-500', 'text-gray-600');
            addressElement.classList.add('text-gray-800');
        } else {
            addressElement.innerHTML = 'Alamat tidak tersedia';
            addressElement.classList.remove('text-blue-500');
            addressElement.classList.add('text-gray-400');
        }
    }
    
    // Load address when page is ready
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(loadAddressFromCoordinates, 100);
        
        // Initialize rating stars
        @if(auth()->check() && !$userComment)
            setRating(5, 'new');
        @elseif(auth()->check() && $userComment)
            setRating({{ $userComment->rating }}, 'edit');
        @endif
    });

    // Rating Stars Functions
    function setRating(rating, type) {
        const stars = document.querySelectorAll(`.rating-star${type === 'edit' ? '-edit' : ''}`);
        const input = document.getElementById(`rating-input${type === 'edit' ? '-edit' : ''}`);
        
        stars.forEach((star, index) => {
            const starIndex = stars.length - index;
            const icon = star.querySelector('i');
            
            if (starIndex <= rating) {
                icon.classList.remove('far');
                icon.classList.add('fas');
                icon.classList.remove('text-gray-300');
                icon.classList.add('text-yellow-400');
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                icon.classList.remove('text-yellow-400');
                icon.classList.add('text-gray-300');
            }
        });
        
        if (input) {
            input.value = rating;
        }
    }

    // Submit Comment
    function submitComment(event, umkmId) {
        event.preventDefault();
        
        const form = document.getElementById('commentForm');
        const formData = new FormData(form);
        const comment = formData.get('comment');
        const rating = formData.get('rating');
        const submitBtn = form.querySelector('button[type="submit"]');
        
        // Validate
        if (!comment || comment.trim().length < 10) {
            Swal.fire({
                icon: 'error',
                title: 'Komentar terlalu pendek',
                text: 'Komentar harus minimal 10 karakter.',
                confirmButtonColor: '#009b97'
            });
            return;
        }
        
        if (comment.trim().length > 1000) {
            Swal.fire({
                icon: 'error',
                title: 'Komentar terlalu panjang',
                text: 'Komentar maksimal 1000 karakter.',
                confirmButtonColor: '#009b97'
            });
            return;
        }
        
        // Show loading animation
        const originalBtnText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Mengirim...';
        
        // Show loading screen overlay
        if (window.showLoading) {
            window.showLoading();
        }
        
        // Submit
        fetch(`/umkm/${umkmId}/comment`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                comment: comment.trim(),
                rating: parseInt(rating)
            })
        })
        .then(response => response.json())
        .then(data => {
            // Hide loading screen
            if (window.hideLoading) {
                window.hideLoading();
            }
            
            // Restore button
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
            
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
                    text: data.message || 'Gagal mengirim komentar.',
                    confirmButtonColor: '#009b97'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            
            // Hide loading screen
            if (window.hideLoading) {
                window.hideLoading();
            }
            
            // Restore button
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
            
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Terjadi kesalahan saat mengirim komentar.',
                confirmButtonColor: '#009b97'
            });
        });
    }

    // Update Comment
    function updateComment(event, commentId) {
        event.preventDefault();
        
        const form = document.getElementById('updateCommentForm');
        const formData = new FormData(form);
        const comment = formData.get('comment');
        const rating = formData.get('rating');
        const submitBtn = form.querySelector('button[type="submit"]');
        
        // Validate
        if (!comment || comment.trim().length < 10) {
            Swal.fire({
                icon: 'error',
                title: 'Komentar terlalu pendek',
                text: 'Komentar harus minimal 10 karakter.',
                confirmButtonColor: '#009b97'
            });
            return;
        }
        
        if (comment.trim().length > 1000) {
            Swal.fire({
                icon: 'error',
                title: 'Komentar terlalu panjang',
                text: 'Komentar maksimal 1000 karakter.',
                confirmButtonColor: '#009b97'
            });
            return;
        }
        
        // Show loading animation
        const originalBtnText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Menyimpan...';
        
        // Show loading screen overlay
        if (window.showLoading) {
            window.showLoading();
        }
        
        // Submit
        fetch(`/comment/${commentId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                comment: comment.trim(),
                rating: parseInt(rating)
            })
        })
        .then(response => response.json())
        .then(data => {
            // Hide loading screen
            if (window.hideLoading) {
                window.hideLoading();
            }
            
            // Restore button
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
            
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
                    text: data.message || 'Gagal memperbarui komentar.',
                    confirmButtonColor: '#009b97'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            
            // Hide loading screen
            if (window.hideLoading) {
                window.hideLoading();
            }
            
            // Restore button
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
            
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Terjadi kesalahan saat memperbarui komentar.',
                confirmButtonColor: '#009b97'
            });
        });
    }

    // Delete Comment
    function deleteComment(commentId) {
        Swal.fire({
            icon: 'warning',
            title: 'Hapus Komentar?',
            text: 'Apakah Anda yakin ingin menghapus komentar ini?',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/comment/${commentId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
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
                            text: data.message || 'Gagal menghapus komentar.',
                            confirmButtonColor: '#009b97'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan saat menghapus komentar.',
                        confirmButtonColor: '#009b97'
                    });
                });
            }
        });
    }

    // Show Edit Form
    function showEditForm() {
        document.getElementById('view-comment').classList.add('hidden');
        document.getElementById('edit-comment-form').classList.remove('hidden');
    }

    // Cancel Edit
    function cancelEdit() {
        document.getElementById('edit-comment-form').classList.add('hidden');
        document.getElementById('view-comment').classList.remove('hidden');
    }
</script>
@endsection 