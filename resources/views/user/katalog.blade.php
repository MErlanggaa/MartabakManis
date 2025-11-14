@extends('layouts.app')

@section('title', 'Katalog UMKM')

@section('styles')
<style>
    /* Pagination Styling dengan warna harmonis */
    .pagination {
        display: flex !important;
        gap: 1rem !important;
        align-items: center;
        justify-content: center;
        flex-wrap: wrap;
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    @media (min-width: 768px) {
        .pagination {
            gap: 1.5rem !important;
        }
    }
    
    @media (min-width: 1024px) {
        .pagination {
            gap: 2rem !important;
        }
    }
    
    .pagination .page-item {
        margin: 0 !important;
        padding: 0 !important;
    }
    
    .pagination .page-link {
        color: #009b97 !important;
        border-color: #e6f5f4 !important;
        background-color: white !important;
        transition: all 0.3s ease;
        padding: 0.5rem 0.75rem !important;
        margin: 0 !important;
        min-width: 2.5rem;
        text-align: center;
        display: inline-block;
        border-radius: 0.5rem;
        border-width: 1px;
    }
    
    @media (min-width: 768px) {
        .pagination .page-link {
            padding: 0.625rem 1rem !important;
            min-width: 3rem;
            margin: 0 !important;
        }
    }
    
    .pagination .page-link:hover {
        color: #007a77 !important;
        background-color: #e6f5f4 !important;
        border-color: #009b97 !important;
        transform: translateY(-2px);
    }
    
    .pagination .page-item.active .page-link {
        background-color: #009b97 !important;
        border-color: #009b97 !important;
        color: white !important;
    }
    
    .pagination .page-item.disabled .page-link {
        color: #9ca3af !important;
        background-color: #f9fafb !important;
        border-color: #e5e7eb !important;
        cursor: not-allowed;
        opacity: 0.6;
    }
    
    /* Override Bootstrap default spacing - remove all margins */
    .pagination > li + li {
        margin-left: 0 !important;
    }
    
    .pagination li {
        margin: 0 !important;
        padding: 0 !important;
    }
    
    /* Ensure gap works properly */
    .pagination .page-item:not(:last-child) {
        margin-right: 0 !important;
    }
</style>
@endsection

@section('content')
<!-- Hero Carousel - Full Width dengan Foto (Mentok ke Navbar) -->
<div class="w-full relative">
    <!-- Hero Carousel Container -->
    <div class="relative w-full h-[250px] md:h-[300px] lg:h-[800px] overflow-hidden group">
        <!-- Carousel Slides -->
        <div class="relative w-full h-full" id="heroCarousel">
            <!-- Slide 1 -->
            <div class="carousel-slide absolute inset-0 w-full h-full transition-opacity duration-700 ease-in-out opacity-100" data-slide="0">
                <img src="{{ asset('gambar/1.jpg') }}" 
                     alt="Hero Image 1" 
                     class="w-full h-full object-cover"
                     loading="eager">
                <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent"></div>
            </div>
            
            <!-- Slide 2 -->
            <div class="carousel-slide absolute inset-0 w-full h-full transition-opacity duration-700 ease-in-out opacity-0" data-slide="1">
                <img src="{{ asset('gambar/2.jpg') }}" 
                     alt="Hero Image 2" 
                     class="w-full h-full object-cover"
                     loading="lazy">
                <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent"></div>
            </div>
            
            <!-- Slide 3 -->
            <div class="carousel-slide absolute inset-0 w-full h-full transition-opacity duration-700 ease-in-out opacity-0" data-slide="2">
                <img src="{{ asset('gambar/3.jpg') }}" 
                     alt="Hero Image 3" 
                     class="w-full h-full object-cover"
                     loading="lazy">
                <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent"></div>
            </div>
        </div>
        
        <!-- Navigation Buttons -->
        <button onclick="changeSlide(-1)" 
                class="absolute left-4 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white text-gray-800 p-3 rounded-full shadow-lg transition-all duration-300 hover:scale-110 opacity-0 group-hover:opacity-100 z-10"
                id="prevBtn"
                aria-label="Previous slide">
            <i class="fas fa-chevron-left text-xl"></i>
        </button>
        
        <button onclick="changeSlide(1)" 
                class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white text-gray-800 p-3 rounded-full shadow-lg transition-all duration-300 hover:scale-110 opacity-0 group-hover:opacity-100 z-10"
                id="nextBtn"
                aria-label="Next slide">
            <i class="fas fa-chevron-right text-xl"></i>
        </button>
        
        <!-- Dots Indicator -->
        <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2 z-10">
            <button onclick="goToSlide(0)" 
                    class="w-3 h-3 rounded-full bg-white/80 hover:bg-white transition-all duration-300 carousel-dot active" 
                    data-dot="0"
                    aria-label="Go to slide 1"></button>
            <button onclick="goToSlide(1)" 
                    class="w-3 h-3 rounded-full bg-white/80 hover:bg-white transition-all duration-300 carousel-dot" 
                    data-dot="1"
                    aria-label="Go to slide 2"></button>
            <button onclick="goToSlide(2)" 
                    class="w-3 h-3 rounded-full bg-white/80 hover:bg-white transition-all duration-300 carousel-dot" 
                    data-dot="2"
                    aria-label="Go to slide 3"></button>
        </div>
    </div>
</div>

<!-- Search Bar dan Filters - Card di atas dengan posisi naik (overlapping hero) -->
<div class="max-w-7xl mx-auto px-4 -mt-12 md:-mt-16 lg:-mt-20 relative z-20 mb-8">
    <!-- Search Bar dan Filters - Dalam satu card -->
    <div class="bg-white rounded-xl shadow-xl p-4 md:p-6">
        <form method="GET" action="{{ route('public.katalog') }}" id="filterForm" class="space-y-4">
            <!-- Search Bar -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-800 mb-3 flex items-center gap-2">
                    <i class="fas fa-search text-[#009b97]"></i> 
                    <span>Pencarian Produk & Layanan</span>
                </label>
                <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1 relative">
                        <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 z-10"></i>
                    <input type="text" 
                           name="search" 
                               id="search_input"
                           value="{{ request('search') }}" 
                               placeholder="Cari produk, layanan, atau UMKM..." 
                               class="w-full pl-12 pr-4 py-3.5 rounded-xl border-2 border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#009b97] focus:border-[#009b97] text-gray-700 transition-all shadow-sm hover:shadow-md">
                </div>
                    <button type="submit" class="bg-[#218689] hover:from-[#007a77] hover:to-[#005d5a] text-white px-8 py-3.5 rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl flex items-center justify-center gap-2 whitespace-nowrap transform hover:scale-105">
                        <span>Telusuri</span>
                        <i class="fas fa-arrow-right"></i>
                </button>
        </div>
    </div>

            <div class="mb-4 pb-4 border-b border-gray-200">
                <p class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                    <span class="text-xl">🔥</span>
                    <span>Fitur Rekomendasi untuk Anda</span>
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @auth
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-heart text-red-500 mr-1"></i> Filter
                    </label>
                    <select name="favorite" id="favorite_select" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#009b97]">
                        <option value="">Semua UMKM</option>
                        <option value="1" {{ request('favorite') == '1' ? 'selected' : '' }}>Favorit Saya</option>
                    </select>
                </div>
                @endauth
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-map-marker-alt text-[#039b00] mr-1"></i> Jarak
                    </label>
                    <div class="flex gap-2">
                        <select name="distance" id="distance_select" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#009b97]">
                            <option value="">Semua Jarak</option>
                            <option value="5" {{ request('distance') == '5' ? 'selected' : '' }}>5 km</option>
                            <option value="10" {{ request('distance') == '10' ? 'selected' : '' }}>10 km</option>
                            <option value="25" {{ request('distance') == '25' ? 'selected' : '' }}>25 km</option>
                            <option value="50" {{ request('distance') == '50' ? 'selected' : '' }}>50 km</option>
                        </select>
                        <input type="hidden" id="user_lat" name="user_lat" value="{{ request('user_lat') }}">
                        <input type="hidden" id="user_lng" name="user_lng" value="{{ request('user_lng') }}">
                   
                    </div>
                </div>
                
                @auth
                <div class="flex items-end">
                    <a href="{{ route('user.ai.chat') }}" 
                       class="w-full bg-[#218689] hover:bg-[#007a77] text-white px-6 py-3 rounded-xl transition-all duration-300 inline-flex items-center justify-center gap-2 font-semibold shadow-md hover:shadow-lg">
                        <i class="fas fa-robot text-lg"></i>
                        <span class="text-base md:text-lg">Rekomendasi dari AI</span>
                    </a>
                </div>
                @endauth
            </div>
        </form>
    </div>
    </div>

<!-- Content Container -->
<div class="max-w-7xl mx-auto px-4 pb-6">
    <!-- Notification for Recommendations -->
    @if(request('recommendation') && request('user_lat') && request('user_lng'))
        <div class="bg-[#e6f5f4] border-l-4 border-[#009b97] p-4 mb-6 rounded-lg">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <i class="fas fa-robot text-[#009b97] text-xl"></i>
                    <div>
                        <h3 class="font-semibold text-gray-800">Rekomendasi Berdasarkan Jarak</h3>
                        <p class="text-gray-600 text-sm">Menampilkan produk dalam radius {{ request('distance', 10) }} km dari lokasi Anda</p>
                    </div>
                </div>
                <a href="{{ route('public.katalog') }}" class="text-[#009b97] hover:text-[#007a77] text-sm font-medium transition-colors">
                    Hapus Filter
                </a>
            </div>
        </div>
    @endif

    <!-- Categories Section -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-2xl font-bold text-gray-900">Kategori UMKM</h2>
            <p class="text-sm text-gray-600 hidden sm:block">Geser untuk melihat lebih banyak</p>
        </div>
        <div class="relative">
            <div class="overflow-x-auto pb-4 [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]" id="categoriesScroll">
                <div class="flex gap-4 w-max">
                    @foreach($categories as $category)
                        <a href="{{ route('public.katalog', ['search' => $category['name']]) }}" 
                           class="flex-shrink-0 w-52 bg-white rounded-2xl shadow-md hover:shadow-2xl transition-all duration-300 p-6 border border-gray-100 hover:border-[#009b97]/30 group transform hover:-translate-y-1">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <h3 class="font-bold text-gray-900 text-lg mb-2 group-hover:text-[#009b97] transition-colors leading-tight">
                                        {{ $category['name'] }}
                                    </h3>
                                    <p class="text-gray-500 text-sm font-medium">
                                        <span class="text-[#009b97] font-bold">{{ number_format($category['count'], 0, ',', '.') }}</span> Produk
                                    </p>
                                </div>
                                <div class="ml-3">
                                    @if($category['name'] == 'Makanan & Minuman')
                                        <div class="w-14 h-14 bg-gradient-to-br from-[#039b00] to-[#009b97] rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                            <i class="fas fa-utensils text-white text-xl"></i>
                                        </div>
                                    @elseif($category['name'] == 'Fashion')
                                        <div class="w-14 h-14 bg-gradient-to-br from-[#009b97] to-[#039b00] rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                            <i class="fas fa-tshirt text-white text-xl"></i>
                                        </div>
                                    @elseif($category['name'] == 'Kerajinan')
                                        <div class="w-14 h-14 bg-gradient-to-br from-[#039b00] to-[#009b97] rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                            <i class="fas fa-palette text-white text-xl"></i>
                                        </div>
                                    @elseif($category['name'] == 'Jasa')
                                        <div class="w-14 h-14 bg-gradient-to-br from-[#009b97] to-[#039b00] rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                            <i class="fas fa-handshake text-white text-xl"></i>
                                        </div>
                                    @elseif($category['name'] == 'Pertanian')
                                        <div class="w-14 h-14 bg-gradient-to-br from-[#039b00] to-[#009b97] rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                            <i class="fas fa-seedling text-white text-xl"></i>
                                        </div>
                                    @elseif($category['name'] == 'Kesehatan & Kecantikan' || str_contains($category['name'], 'Kesehatan'))
                                        <div class="w-14 h-14 bg-gradient-to-br from-[#009b97] to-[#039b00] rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                            <i class="fas fa-heartbeat text-white text-xl"></i>
                                        </div>
                                    @else
                                        <div class="w-14 h-14 bg-gradient-to-br from-[#039b00] to-[#009b97] rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                            <i class="fas fa-store text-white text-xl"></i>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-2 text-[#009b97] text-sm font-semibold group-hover:translate-x-1 transition-transform">
                                <span>Lihat semua</span>
                                <i class="fas fa-arrow-right"></i>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
            
            <!-- Scroll Buttons -->
                    <button onclick="scrollCategories('left')" 
                    class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-4 bg-white rounded-full shadow-xl p-4 hover:bg-[#e6f5f4] hover:scale-110 transition-all hidden border border-gray-100" 
                    id="scrollLeftBtn">
                <i class="fas fa-chevron-left text-gray-700"></i>
            </button>
            <button onclick="scrollCategories('right')" 
                    class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-4 bg-white rounded-full shadow-xl p-4 hover:bg-[#e6f5f4] hover:scale-110 transition-all border border-gray-100"
                    id="scrollRightBtn">
                <i class="fas fa-chevron-right text-gray-700"></i>
            </button>
        </div>
    </div>

    <!-- Results Count & Header -->
    @if($layanan->count() > 0)
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-200">
        <div>
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-1">Daftar Produk & Layanan</h2>
            <p class="text-gray-600 text-sm md:text-base">
                Menampilkan <span class="font-bold text-[#009b97]">{{ $layanan->count() }}</span> 
                dari <span class="font-bold text-gray-900">{{ $layanan->total() }}</span> hasil
            </p>
        </div>
        @if(request('search') || request('favorite') || request('distance'))
        <a href="{{ route('public.katalog') }}" 
           class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-[#009b97] transition-colors px-4 py-2 rounded-lg hover:bg-[#e6f5f4] border border-gray-200 hover:border-[#009b97]/30">
            <i class="fas fa-times"></i>
            <span>Hapus Filter</span>
        </a>
        @endif
    </div>
    @endif

    <!-- Products Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 md:gap-4 lg:gap-6">
        @forelse($layanan as $item)
            @php
                $umkm = $item->umkm->first();
                $userFavorites = auth()->check() ? (auth()->user()->favorites ?? []) : [];
                $isFavorite = $umkm && auth()->check() && in_array($umkm->id, $userFavorites);
            @endphp
            <div class="bg-white rounded-lg md:rounded-2xl shadow-md md:shadow-lg hover:shadow-xl md:hover:shadow-2xl transition-all duration-300 border border-gray-100 group cursor-pointer overflow-hidden transform hover:-translate-y-1" 
                 onclick="window.location.href='{{ route('public.layanan.show', $item->id) }}'">
                <!-- Product Image -->
                <div class="relative w-full h-40 md:h-48 lg:h-56 bg-gradient-to-br from-gray-100 to-gray-200 overflow-hidden">
                    @if($item->photo_path)
                        <img src="{{ asset('storage/' . $item->photo_path) }}" 
                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" 
                             alt="{{ $item->nama }}">
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-[#e6f5f4] to-gray-50">
                            <i class="fas fa-image text-3xl md:text-6xl text-gray-300"></i>
                        </div>
                    @endif
                    
                    <!-- Gradient Overlay on Hover -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    
                    <!-- Category Badge (Top Left) - Hidden on mobile, shown on md+ -->
                    @if($umkm)
                        <div class="absolute top-2 left-2 md:top-4 md:left-4 z-10 hidden md:block">
                            <span class="bg-white/95 backdrop-blur-sm text-gray-800 text-xs font-bold px-2 md:px-3 py-1 md:py-1.5 rounded-full shadow-lg border border-gray-200">
                                <i class="fas fa-tag mr-1 text-[#009b97]"></i>
                                <span class="hidden lg:inline">{{ $umkm->jenis_umkm }}</span>
                            </span>
                        </div>
                    @endif
                    
                    <!-- Favorite Button (Top Right) -->
                    @auth
                    @if($umkm)
                    <button onclick="event.stopPropagation(); toggleFavoriteWithAlert({{ $umkm->id }}, this, '{{ addslashes($umkm->nama) }}');" 
                            class="absolute top-2 right-2 md:top-4 md:right-4 z-10 favorite-btn p-2 md:p-2.5 rounded-full bg-white/95 backdrop-blur-sm shadow-lg hover:bg-white transition-all duration-300 hover:scale-110 {{ $isFavorite ? 'text-red-600' : 'text-gray-400' }}"
                            data-umkm-id="{{ $umkm->id }}"
                            data-umkm-nama="{{ $umkm->nama }}">
                        <i class="{{ $isFavorite ? 'fas' : 'far' }} fa-heart text-sm md:text-lg"></i>
                    </button>
                    @endif
                    @endauth
                </div>
                
                <!-- Content -->
                <div class="p-3 md:p-4 lg:p-5">
                    <!-- Product Name -->
                    <h3 class="text-sm md:text-base lg:text-lg font-bold text-gray-900 mb-1 line-clamp-2 group-hover:text-[#009b97] transition-colors leading-tight">
                        {{ Str::words($item->nama, 7, '...') }}
                    </h3>
                    
                    <!-- Description - Hidden on mobile, shown on md+ -->
                    @if($item->description)
                        <p class="hidden md:block text-gray-600 text-xs md:text-sm mb-2 md:mb-4 line-clamp-2 leading-relaxed">
                            {{ Str::limit($item->description, 80) }}
                        </p>
                    @endif
                    
                    <!-- Price -->
                    <div class="mb-2 md:mb-4 pb-2 md:pb-4 border-b border-gray-100">
                        <div class="flex items-baseline gap-2">
                            <span class="text-base md:text-xl lg:text-2xl font-bold text-black">
                            Rp {{ number_format($item->price, 0, ',', '.') }}
                        </span>
                        </div>
                    </div>
                    
                    <!-- UMKM Name & Location - Simplified on mobile -->
                    @if($umkm)
                        <div class="mb-2 md:mb-4 space-y-1 md:space-y-2">
                            <!-- UMKM Name -->
                            <div class="flex items-center gap-1.5 md:gap-2 text-xs md:text-sm">
                                <div class="w-5 h-5 md:w-6 md:h-6 rounded-full bg-[#218689]  flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-store text-white text-[10px] md:text-xs"></i>
                                </div>
                                <span class="font-semibold text-gray-800 truncate text-xs md:text-sm">{{ $umkm->nama }}</span>
                        </div>
                        
                            <!-- Location dengan Jarak - Simplified on mobile -->
                            <div class="space-y-1 md:space-y-2">
                                <!-- Alamat - Hidden on mobile, shown on md+ -->
                                @if($umkm->latitude && $umkm->longitude)
                                    <div class="address-container  md:flex items-start gap-2 text-xs" 
                                         data-lat="{{ $umkm->latitude }}" 
                                         data-lng="{{ $umkm->longitude }}"
                                         data-umkm-id="{{ $umkm->id }}">
                                        <i class="fas fa-map-marker-alt text-[#039b00] mt-0.5 flex-shrink-0"></i>
                                        <span class="text-gray-600 leading-relaxed address-text flex-1">
                                            <i class="fas fa-spinner fa-spin text-[#009b97]"></i> Memuat alamat...
                                    </span>
                                    </div>
                            @else
                                    <div class=" md:flex items-start gap-2 text-xs text-gray-400">
                                        <i class="fas fa-map-marker-alt mt-0.5 flex-shrink-0"></i>
                                        <span>Lokasi tidak tersedia</span>
                                    </div>
                            @endif
                            
                                <!-- Jarak - Always visible, smaller on mobile -->
                                @if($umkm->latitude && $umkm->longitude)
                                    <div class="flex items-center gap-1.5 md:gap-2 text-xs text-black font-medium distance-container" 
                                   data-umkm-lat="{{ $umkm->latitude }}" 
                                   data-umkm-lng="{{ $umkm->longitude }}"
                                   data-umkm-id="{{ $umkm->id }}">
                                        <i class="fas fa-route text-xs text-red-600"></i>
                                        <span class="distance-text">Menghitung...</span>
                                    </div>
                            @endif
                            </div>
                        </div>
                    @endif
                    
                    <!-- Action Button -->
                    <button onclick="event.stopPropagation(); window.location.href='{{ route('public.layanan.show', $item->id) }}'" 
                            class="w-full group/btn bg-[#32a752] hover:bg-[#027a00] text-white text-center py-2 md:py-2.5 lg:py-3 rounded-lg md:rounded-xl text-xs md:text-sm lg:text-base font-semibold transition-all duration-300 shadow-md hover:shadow-lg transform hover:scale-105 flex items-center justify-center gap-1.5 md:gap-2">
                        <span>Detail Layanan    </span>
                        <i class="fas fa-arrow-right text-xs md:text-sm group-hover/btn:translate-x-1 transition-transform"></i>
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="bg-gradient-to-br from-gray-50 to-[#e6f5f4] rounded-2xl shadow-lg p-12 md:p-16 text-center border border-gray-100">
                    <div class="max-w-md mx-auto">
                        <div class="w-32 h-32 bg-gradient-to-br from-[#009b97]/20 to-[#009b97]/10 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner">
                            <i class="fas fa-search text-5xl text-[#009b97]"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-3">Tidak ada produk ditemukan</h3>
                        <p class="text-gray-600 mb-8 leading-relaxed">Coba ubah kata kunci pencarian atau filter Anda untuk menemukan produk yang Anda cari</p>
                        <a href="{{ route('public.katalog') }}" 
                           class="inline-flex items-center gap-2 bg-[#009b97] hover:bg-[#007a77] text-white px-6 py-3 rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105">
                            <i class="fas fa-redo"></i>
                            <span>Reset Filter</span>
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($layanan->hasPages())
        <div class="mt-16 md:mt-20 lg:mt-24 mb-8 md:mb-12 lg:mb-16 flex justify-center">
            <div class="bg-white rounded-xl shadow-md p-4 md:p-6 lg:p-8 border border-gray-100 w-full max-w-4xl">
                <div class="flex justify-center">
                    {{ $layanan->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {
        const filterForm = document.getElementById('filterForm');
        const favoriteSelect = document.getElementById('favorite_select');
        const distanceSelect = document.getElementById('distance_select');
        const userLatInput = document.getElementById('user_lat');
        const userLngInput = document.getElementById('user_lng');
        
        // Auto submit when favorite filter changes
        if (favoriteSelect) {
            favoriteSelect.addEventListener('change', function() {
                const favoriteValue = this.value;
                console.log('Favorite filter changed:', favoriteValue);
                
                // Jika "Semua UMKM" dipilih (value=""), hapus parameter favorite dan redirect ke katalog
                if (!favoriteValue || favoriteValue === '') {
                    // Ambil semua parameter URL saat ini kecuali favorite
                    const currentUrl = new URL(window.location.href);
                    currentUrl.searchParams.delete('favorite');
                    
                    // Ambil semua parameter yang ada
                    const searchParams = new URLSearchParams(currentUrl.search);
                    
                    // Buat URL baru dengan semua parameter kecuali favorite
                    let newUrl = '{{ route("public.katalog") }}';
                    const paramsString = searchParams.toString();
                    if (paramsString) {
                        newUrl += '?' + paramsString;
                    }
                    
                    // Redirect ke katalog tanpa filter favorit
                    console.log('Redirecting to katalog without favorite filter:', newUrl);
                    window.location.href = newUrl;
                } else {
                    // Jika "Favorit Saya" dipilih, submit form seperti biasa
                    submitFilterForm();
                }
            });
        }
        
        // Auto submit when distance filter changes
        if (distanceSelect) {
            distanceSelect.addEventListener('change', function() {
                const distance = this.value;
                const userLat = userLatInput ? userLatInput.value : '';
                const userLng = userLngInput ? userLngInput.value : '';
                
                console.log('Distance filter changed:', distance, 'User location:', userLat, userLng);
                
                // Jika jarak dipilih tapi lokasi belum ada, ambil lokasi dulu
                if (distance && (!userLat || !userLng)) {
                    getUserLocationForDistanceFilter(distance);
                } else {
                    // Jika lokasi sudah ada atau jarak di-reset
                    if (!distance) {
                        // Jika jarak di-reset, tetap tampilkan jarak jika lokasi tersedia
                        // Jangan reset location, hanya submit form
                        submitFilterForm();
                    } else {
                        // Jika jarak dipilih dan lokasi sudah ada, submit form untuk filter
                        submitFilterForm();
                    }
                }
            });
        }
    });
    
    // Submit filter form
    function submitFilterForm() {
        const form = document.getElementById('filterForm');
        if (form) {
            console.log('Submitting filter form...');
            form.submit();
        } else {
            console.error('Filter form not found!');
        }
    }
    
    // Get user location untuk filter jarak
    function getUserLocationForDistanceFilter(distance) {
        if (navigator.geolocation) {
            // Show loading indicator
            const distanceSelect = document.getElementById('distance_select');
            const userLatInput = document.getElementById('user_lat');
            const userLngInput = document.getElementById('user_lng');
            
            if (!distanceSelect) return;
            
            const originalValue = distanceSelect.value;
            distanceSelect.disabled = true;
            
            console.log('Requesting user location for distance filter...');
            
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    console.log('User location obtained:', lat, lng);
                    
                    if (userLatInput) userLatInput.value = lat;
                    if (userLngInput) userLngInput.value = lng;
                    
                    distanceSelect.disabled = false;
                    
                    // Submit form dengan lokasi dan jarak
                    submitFilterForm();
                },
                function(error) {
                    console.error('Geolocation error:', error);
                    showNotification('Gagal mendapatkan lokasi. Silakan izinkan akses lokasi atau klik tombol lokasi.', 'error');
                    distanceSelect.disabled = false;
                    distanceSelect.value = originalValue;
                },
                {
                    timeout: 10000,
                    enableHighAccuracy: true
                }
            );
        } else {
            showNotification('Browser tidak mendukung geolocation', 'error');
        }
    }
    
    // Get recommendations based on distance (dipanggil dari tombol hijau)
    function getRecommendationsByDistance() {
        const userLat = document.getElementById('user_lat').value;
        const userLng = document.getElementById('user_lng').value;
        const distance = document.getElementById('distance_select').value || '10';
        
        // Jika lokasi belum diisi, ambil lokasi dulu
        if (!userLat || !userLng) {
            getUserLocationForRecommendation(distance);
        } else {
            // Jika lokasi sudah ada, langsung redirect dengan filter jarak
            redirectToRecommendations(userLat, userLng, distance);
        }
    }
    
    // Get user location khusus untuk rekomendasi
    function getUserLocationForRecommendation(distance) {
        if (navigator.geolocation) {
            const btn = event.target.closest('button');
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    document.getElementById('user_lat').value = lat;
                    document.getElementById('user_lng').value = lng;
                    
                    showNotification('Lokasi berhasil didapatkan!', 'success');
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                    
                    // Redirect dengan parameter jarak
                    redirectToRecommendations(lat, lng, distance);
                },
                function(error) {
                    showNotification('Gagal mendapatkan lokasi. Silakan izinkan akses lokasi.', 'error');
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
            );
        } else {
            showNotification('Browser tidak mendukung geolocation', 'error');
        }
    }
    
    function redirectToRecommendations(lat, lng, distance) {
        // Redirect ke katalog dengan parameter jarak dan lokasi
        const url = new URL('{{ route("public.katalog") }}', window.location.origin);
        url.searchParams.set('user_lat', lat);
        url.searchParams.set('user_lng', lng);
        url.searchParams.set('distance', distance);
        url.searchParams.set('recommendation', '1'); // Flag untuk menunjukkan ini adalah rekomendasi
        
        window.location.href = url.toString();
    }
    
    // Calculate distance between two coordinates (Haversine formula)
    function calculateDistance(lat1, lng1, lat2, lng2) {
        const R = 6371; // Radius of the Earth in km
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLng = (lng2 - lng1) * Math.PI / 180;
        const a = 
            Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
            Math.sin(dLng / 2) * Math.sin(dLng / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        const distance = R * c;
        return distance;
    }
    
    // Update distances untuk semua card
    function updateDistances(userLat, userLng) {
        if (!userLat || !userLng) {
            // Hide distance elements jika lokasi tidak tersedia
            document.querySelectorAll('.distance-container').forEach(el => {
                el.style.display = 'none';
            });
            return;
        }
        
        const distanceElements = document.querySelectorAll('.distance-container[data-umkm-lat][data-umkm-lng]');
        
        distanceElements.forEach(element => {
            const umkmLat = parseFloat(element.getAttribute('data-umkm-lat'));
            const umkmLng = parseFloat(element.getAttribute('data-umkm-lng'));
            
            if (umkmLat && umkmLng) {
                const distance = calculateDistance(userLat, userLng, umkmLat, umkmLng);
                const distanceText = element.querySelector('.distance-text');
                
                if (distanceText) {
                    element.style.display = 'flex';
                    const isMobile = window.innerWidth < 768; // md breakpoint
                    if (distance < 1) {
                        distanceText.textContent = isMobile ? `${Math.round(distance * 1000)} m` : `${Math.round(distance * 1000)} m dari lokasi Anda`;
                    } else {
                        distanceText.textContent = isMobile ? `${distance.toFixed(1)} km` : `${distance.toFixed(1)} km dari lokasi Anda`;
                    }
                }
            }
        });
    }
    
    // Get user location automatically on page load
    function getUserLocationAutomatically() {
        // Cek dulu apakah sudah ada di localStorage
        const savedLocation = localStorage.getItem('userLocation');
        if (savedLocation) {
            try {
                const location = JSON.parse(savedLocation);
                const now = new Date().getTime();
                // Gunakan lokasi yang tersimpan jika kurang dari 1 jam
                if (location.timestamp && (now - location.timestamp) < 3600000) {
                    console.log('Using saved location:', location);
                    updateDistances(location.lat, location.lng);
                    // Update hidden inputs
                    const userLatInput = document.getElementById('user_lat');
                    const userLngInput = document.getElementById('user_lng');
                    if (userLatInput) userLatInput.value = location.lat;
                    if (userLngInput) userLngInput.value = location.lng;
                    return;
                }
            } catch (e) {
                console.error('Error parsing saved location:', e);
            }
        }
        
        // Cek apakah lokasi sudah ada di URL parameter
        const urlParams = new URLSearchParams(window.location.search);
        const urlLat = urlParams.get('user_lat');
        const urlLng = urlParams.get('user_lng');
        
        if (urlLat && urlLng) {
            console.log('Using URL location:', urlLat, urlLng);
            updateDistances(parseFloat(urlLat), parseFloat(urlLng));
            // Simpan ke localStorage
            localStorage.setItem('userLocation', JSON.stringify({
                lat: parseFloat(urlLat),
                lng: parseFloat(urlLng),
                timestamp: new Date().getTime()
            }));
            return;
        }
        
        // Jika belum ada, request dari browser
        if (navigator.geolocation) {
            console.log('Requesting user location...');
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    console.log('User location obtained:', lat, lng);
                    
                    // Update distances
                    updateDistances(lat, lng);
                    
                    // Update hidden inputs
                    const userLatInput = document.getElementById('user_lat');
                    const userLngInput = document.getElementById('user_lng');
                    if (userLatInput) userLatInput.value = lat;
                    if (userLngInput) userLngInput.value = lng;
                    
                    // Simpan ke localStorage
                    localStorage.setItem('userLocation', JSON.stringify({
                        lat: lat,
                        lng: lng,
                        timestamp: new Date().getTime()
                    }));
                },
                function(error) {
                    console.log('Geolocation error:', error);
                    // Hide distance elements jika lokasi tidak bisa didapatkan
                    document.querySelectorAll('.distance-container').forEach(el => {
                        el.style.display = 'none';
                    });
                },
                {
                    timeout: 10000,
                    enableHighAccuracy: false,
                    maximumAge: 3600000 // Cache untuk 1 jam
                }
            );
        } else {
            console.log('Geolocation not supported');
            // Hide distance elements jika browser tidak mendukung geolocation
            document.querySelectorAll('.distance-container').forEach(el => {
                el.style.display = 'none';
            });
        }
    }
    
    // Load distances ketika halaman dimuat
    document.addEventListener('DOMContentLoaded', function() {
        // Cek dulu apakah ada lokasi di URL parameter
        const urlParams = new URLSearchParams(window.location.search);
        const urlLat = urlParams.get('user_lat');
        const urlLng = urlParams.get('user_lng');
        
        if (urlLat && urlLng) {
            updateDistances(parseFloat(urlLat), parseFloat(urlLng));
        } else {
            // Jika tidak ada, coba dapatkan lokasi secara otomatis
            getUserLocationAutomatically();
        }
    });
    
    // Get user location (untuk tombol lokasi di form filter) - Update jarak tanpa submit
    function getUserLocation(event) {
        if (navigator.geolocation) {
            const btn = event ? event.target.closest('button') : null;
            const originalHtml = btn ? btn.innerHTML : '';
            if (btn) {
            btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Meminta lokasi...';
            }
            
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    console.log('User location obtained manually:', lat, lng);
                    
                    // Update hidden inputs
                    const userLatInput = document.getElementById('user_lat');
                    const userLngInput = document.getElementById('user_lng');
                    if (userLatInput) userLatInput.value = lat;
                    if (userLngInput) userLngInput.value = lng;
                    
                    // Update distances secara langsung tanpa submit form
                    updateDistances(lat, lng);
                    
                    // Simpan ke localStorage
                    localStorage.setItem('userLocation', JSON.stringify({
                        lat: lat,
                        lng: lng,
                        timestamp: new Date().getTime()
                    }));
                    
                    if (btn) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'Lokasi berhasil didapatkan! Jarak sekarang ditampilkan.',
                            timer: 2000,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end'
                        });
                        btn.disabled = false;
                        btn.innerHTML = originalHtml;
                    }
                },
                function(error) {
                    console.error('Geolocation error:', error);
                    let errorMessage = 'Gagal mendapatkan lokasi.';
                    if (error.code === error.PERMISSION_DENIED) {
                        errorMessage = 'Akses lokasi ditolak. Silakan izinkan akses lokasi di pengaturan browser.';
                    } else if (error.code === error.POSITION_UNAVAILABLE) {
                        errorMessage = 'Informasi lokasi tidak tersedia.';
                    } else if (error.code === error.TIMEOUT) {
                        errorMessage = 'Waktu permintaan lokasi habis.';
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: errorMessage,
                        confirmButtonColor: '#ef4444'
                    });
                    
                    if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                    }
                },
                {
                    timeout: 10000,
                    enableHighAccuracy: false,
                    maximumAge: 3600000
                }
            );
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Tidak Didukung',
                text: 'Browser Anda tidak mendukung geolocation.',
                confirmButtonColor: '#ef4444'
            });
        }
    }

    // Favorite functionality - PASTIKAN INI ADA
    document.querySelectorAll('.favorite-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const umkmId = this.dataset.umkmId;
            toggleFavorite(umkmId, this);
        });
    });

    async function toggleFavoriteWithAlert(umkmId, btn, umkmNama) {
        const icon = btn.querySelector('i');
        const isFav = icon.classList.contains('fas');
        
        // Show confirmation/info alert before toggling
        if (!isFav) {
            // Show info alert when adding to favorites
            const result = await Swal.fire({
                title: 'Like UMKM',
                html: `<div class="text-left">
                    <p class="mb-3">Anda akan menyukai UMKM <strong>${umkmNama}</strong></p>
                    <p class="text-sm text-gray-600">💡 <strong>Informasi:</strong> Menyukai layanan ini akan menyukai semua layanan dari UMKM yang sama.</p>
                </div>`,
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '<i class="fas fa-heart mr-2"></i> Ya, Suka',
                cancelButtonText: 'Batal',
                reverseButtons: true
            });
            
            if (!result.isConfirmed) {
                return;
            }
        }
        
        // Optimistic UI update
        if (isFav) {
            icon.classList.remove('fas');
            icon.classList.add('far');
            btn.classList.remove('text-red-600');
            btn.classList.add('text-gray-400');
        } else {
            icon.classList.remove('far');
            icon.classList.add('fas');
            btn.classList.remove('text-gray-400');
            btn.classList.add('text-red-600');
        }
        
        fetch(`/user/favorite/${umkmId}/toggle`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message with SweetAlert
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: data.is_favorited ? 'UMKM telah ditambahkan ke favorit Anda' : 'UMKM telah dihapus dari favorit Anda',
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            } else {
                // Revert on error
                if (isFav) {
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                    btn.classList.remove('text-gray-400');
                    btn.classList.add('text-red-600');
                } else {
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                    btn.classList.remove('text-red-600');
                    btn.classList.add('text-gray-400');
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: data.message || 'Gagal memperbarui favorit',
                    confirmButtonColor: '#ef4444'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Revert on error
            if (isFav) {
                icon.classList.remove('far');
                icon.classList.add('fas');
                btn.classList.remove('text-gray-400');
                btn.classList.add('text-red-600');
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                btn.classList.remove('text-red-600');
                btn.classList.add('text-gray-400');
            }
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Terjadi kesalahan saat memproses permintaan.',
                confirmButtonColor: '#ef4444'
            });
        });
    }
    
    // Keep old function for backward compatibility (if needed)
    function toggleFavorite(umkmId, btn) {
        const umkmNama = btn.getAttribute('data-umkm-nama') || btn.closest('.bg-white').querySelector('h3')?.textContent?.trim() || 'UMKM ini';
        toggleFavoriteWithAlert(umkmId, btn, umkmNama);
    }

    function showNotification(message, type) {
        const toast = document.createElement('div');
        const bgColor = type === 'error' ? 'bg-red-500' : 'bg-[#039b00]';
        const icon = type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle';
        
        toast.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-4 rounded-lg shadow-xl z-50 flex items-center gap-3 min-w-[300px] max-w-md transform transition-all duration-300 translate-x-full`;
        
        toast.innerHTML = `
            <i class="fas ${icon} text-xl"></i>
            <p class="flex-1">${message}</p>
            <button onclick="this.parentElement.remove()" class="text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.remove('translate-x-full');
        }, 100);
        
        setTimeout(() => {
            toast.classList.add('translate-x-full');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Categories scroll functionality
    const categoriesContainer = document.getElementById('categoriesScroll');
    const scrollLeftBtn = document.getElementById('scrollLeftBtn');
    const scrollRightBtn = document.getElementById('scrollRightBtn');
    
    function updateScrollButtons() {
        if (categoriesContainer) {
            const { scrollLeft, scrollWidth, clientWidth } = categoriesContainer;
            
            // Show/hide left button using Tailwind classes
            if (scrollLeft > 0) {
                scrollLeftBtn.classList.remove('hidden');
                scrollLeftBtn.classList.add('block');
            } else {
                scrollLeftBtn.classList.remove('block');
                scrollLeftBtn.classList.add('hidden');
            }
            
            // Show/hide right button using Tailwind classes
            if (scrollLeft < scrollWidth - clientWidth - 10) {
                scrollRightBtn.classList.remove('hidden');
                scrollRightBtn.classList.add('block');
            } else {
                scrollRightBtn.classList.remove('block');
                scrollRightBtn.classList.add('hidden');
            }
        }
    }
    
    function scrollCategories(direction) {
        if (categoriesContainer) {
            const scrollAmount = 300;
            const currentScroll = categoriesContainer.scrollLeft;
            const newScroll = direction === 'left' 
                ? currentScroll - scrollAmount 
                : currentScroll + scrollAmount;
            
            categoriesContainer.scrollTo({
                left: newScroll,
                behavior: 'smooth'
            });
        }
    }
    
    // Update buttons on scroll
    if (categoriesContainer) {
        categoriesContainer.addEventListener('scroll', updateScrollButtons);
        updateScrollButtons();
        
        // Show buttons on hover
        const categoriesSection = categoriesContainer.closest('.relative');
        if (categoriesSection) {
            categoriesSection.addEventListener('mouseenter', function() {
                updateScrollButtons();
            });
        }
    }
    
    // Reverse Geocoding - Load addresses from coordinates
    async function loadAddressFromCoordinates() {
        const addressContainers = document.querySelectorAll('.address-container');
        
        // Process addresses in batches to avoid overwhelming the API
        const batchSize = 5;
        for (let i = 0; i < addressContainers.length; i += batchSize) {
            const batch = Array.from(addressContainers).slice(i, i + batchSize);
            
            // Load addresses in parallel for this batch
            await Promise.all(batch.map(container => fetchAddressForContainer(container)));
            
            // Small delay between batches to respect API rate limits
            if (i + batchSize < addressContainers.length) {
                await new Promise(resolve => setTimeout(resolve, 200));
            }
        }
    }
    
    async function fetchAddressForContainer(container) {
        const lat = container.getAttribute('data-lat');
        const lng = container.getAttribute('data-lng');
        const addressText = container.querySelector('.address-text');
        
        if (!lat || !lng || !addressText) {
            return;
        }
        
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
                
                // Update address text
                addressText.innerHTML = address;
                addressText.classList.remove('text-gray-400');
                addressText.classList.add('text-gray-800');
            } else {
                throw new Error('No address found');
            }
        } catch (error) {
            console.error('Error fetching address:', error);
            // Show error message
            addressText.innerHTML = 'Alamat tidak tersedia';
            addressText.classList.remove('text-[#009b97]');
            addressText.classList.add('text-gray-400');
        }
    }
    
    // Load addresses when page is ready
    document.addEventListener('DOMContentLoaded', function() {
        // Load addresses after a short delay to ensure page is fully rendered
        setTimeout(loadAddressFromCoordinates, 100);
        
        // Initialize hero carousel
        initHeroCarousel();
    });
    
    // Hero Carousel Functionality
    let currentSlide = 0;
    const totalSlides = 3;
    let carouselInterval;
    
    function initHeroCarousel() {
        // Auto-play carousel (change slide every 5 seconds)
        carouselInterval = setInterval(() => {
            changeSlide(1);
        }, 5000);
        
        // Pause on hover
        const carouselContainer = document.getElementById('heroCarousel')?.closest('.group');
        if (carouselContainer) {
            carouselContainer.addEventListener('mouseenter', () => {
                clearInterval(carouselInterval);
            });
            
            carouselContainer.addEventListener('mouseleave', () => {
                carouselInterval = setInterval(() => {
                    changeSlide(1);
                }, 5000);
            });
        }
    }
    
    function changeSlide(direction) {
        const slides = document.querySelectorAll('.carousel-slide');
        const dots = document.querySelectorAll('.carousel-dot');
        
        // Remove active class from current slide and dot
        slides[currentSlide].classList.remove('opacity-100');
        slides[currentSlide].classList.add('opacity-0');
        dots[currentSlide].classList.remove('active', 'bg-white');
        dots[currentSlide].classList.add('bg-white/80');
        
        // Calculate new slide index
        currentSlide += direction;
        if (currentSlide >= totalSlides) {
            currentSlide = 0;
        } else if (currentSlide < 0) {
            currentSlide = totalSlides - 1;
        }
        
        // Add active class to new slide and dot
        slides[currentSlide].classList.remove('opacity-0');
        slides[currentSlide].classList.add('opacity-100');
        dots[currentSlide].classList.remove('bg-white/80');
        dots[currentSlide].classList.add('active', 'bg-white');
        
        // Reset auto-play timer
        clearInterval(carouselInterval);
        carouselInterval = setInterval(() => {
            changeSlide(1);
        }, 5000);
    }
    
    function goToSlide(slideIndex) {
        const slides = document.querySelectorAll('.carousel-slide');
        const dots = document.querySelectorAll('.carousel-dot');
        
        // Remove active class from current slide and dot
        slides[currentSlide].classList.remove('opacity-100');
        slides[currentSlide].classList.add('opacity-0');
        dots[currentSlide].classList.remove('active', 'bg-white');
        dots[currentSlide].classList.add('bg-white/80');
        
        // Set new slide
        currentSlide = slideIndex;
        
        // Add active class to new slide and dot
        slides[currentSlide].classList.remove('opacity-0');
        slides[currentSlide].classList.add('opacity-100');
        dots[currentSlide].classList.remove('bg-white/80');
        dots[currentSlide].classList.add('active', 'bg-white');
        
        // Reset auto-play timer
        clearInterval(carouselInterval);
        carouselInterval = setInterval(() => {
            changeSlide(1);
        }, 5000);
    }
</script>
@endsection
