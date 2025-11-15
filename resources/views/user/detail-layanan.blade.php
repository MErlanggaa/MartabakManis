@extends('layouts.app')

@section('title', $layanan->nama . ' - Detail Layanan')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    <!-- Breadcrumbs -->
    <nav class="mb-6 text-sm text-gray-600">
        <ol class="flex items-center space-x-2">
            <li><a href="{{ route('public.katalog') }}" class="hover:text-[#009b97]">Home</a></li>
            <li><i class="fas fa-chevron-right text-xs"></i></li>
            <li><span class="text-gray-900 font-medium">Kategori {{ $umkm->jenis_umkm ?? 'Layanan' }}</span></li>
        </ol>
    </nav>

    <!-- Main Product Display -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 p-6 lg:p-8">
            <!-- Product Image Section -->
            <div class="relative w-full">
                <div class="relative w-full h-96 lg:h-[500px] bg-gray-100 rounded-lg overflow-hidden shadow-inner">
                    @if($layanan->photo_path)
                        <img src="{{ asset('storage/' . $layanan->photo_path) }}" 
                             alt="{{ $layanan->nama }}" 
                             class="w-full h-full object-cover object-center">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-400 bg-gray-200">
                            <i class="fas fa-image text-6xl"></i>
                        </div>
                    @endif
                    
                    <!-- Category Badge (Top Left) -->
                    @if($umkm && $umkm->jenis_umkm)
                        <span class="absolute top-4 left-4 bg-white text-black text-sm font-semibold px-4 py-2 rounded-full shadow-lg">
                            {{ $umkm->jenis_umkm }}
                        </span>
                    @endif

                    <!-- Favorite Button (Top Right) -->
                    @auth
                        @php
                            $umkmId = $umkm->id ?? null;
                            $userFavorites = auth()->user()->favorites ?? [];
                            $isFav = $umkmId && in_array($umkmId, $userFavorites);
                        @endphp
                        @if($umkmId)
                            <button type="button" 
                                    class="favorite-btn absolute top-4 right-4 z-10 p-3 rounded-full bg-white shadow-lg hover:bg-red-50 transition-all duration-200 {{ $isFav ? 'text-red-600' : 'text-gray-400' }} hover:scale-110"
                                    data-umkm-id="{{ $umkmId }}"
                                    data-umkm-nama="{{ $umkm->nama ?? '' }}"
                                    onclick="toggleFavoriteWithAlert({{ $umkmId }}, this, '{{ $umkm->nama ?? '' }}')"
                                    title="{{ $isFav ? 'Hapus dari favorit' : 'Tambah ke favorit' }}">
                                <i class="{{ $isFav ? 'fas' : 'far' }} fa-heart text-xl"></i>
                            </button>
                        @endif
                    @endauth
                </div>
            </div>

            <!-- Product Details Section -->
            <div class="flex flex-col justify-between">
                <div>
                    <!-- Product Name -->
                    <h1 class="text-3xl lg:text-4xl font-extrabold text-gray-900 mb-2">
                        {{ $layanan->nama }}
                    </h1>
                    
                    <!-- Description -->
                    @if($layanan->description)
                    <div class="mb-4">
                        <p class="text-gray-700 leading-relaxed text-base">
                            {{ $layanan->description }}
                        </p>
                    </div>
                    @endif
                    
                    <!-- Price -->
                    <div class="mb-4">
                        <p class="text-3xl lg:text-4xl font-bold text-black mb-2">
                            Rp {{ number_format($layanan->price, 0, ',', '.') }}
                        </p>
                        <div class="flex items-center gap-4 text-sm text-gray-600">
                            <span class="inline-flex items-center gap-1.5 text-[#009b97]">
                                <i class="fas fa-eye"></i>
                                <span class="font-medium">{{ number_format($layanan->views ?? 0, 0, ',', '.') }} Dilihat</span>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Location -->
                    @if($umkm && !is_null($umkm->latitude) && !is_null($umkm->longitude) && $umkm->latitude != 0 && $umkm->longitude != 0)
                        <div class="flex items-center text-gray-700 mb-4">
                            <i class="fas fa-map-marker-alt mr-3 text-red-500 text-lg"></i>
                            <span id="location-name" 
                                  class="text-base location-name-text" 
                                  data-lat="{{ (float)$umkm->latitude }}" 
                                  data-lng="{{ (float)$umkm->longitude }}">
                                <i class="fas fa-spinner fa-spin mr-2"></i>Memuat lokasi...
                            </span>
                        </div>
                    @elseif($umkm)
                        <div class="flex items-center text-gray-700 mb-4">
                            <i class="fas fa-map-marker-alt mr-3 text-red-500 text-lg"></i>
                            <span class="text-base">
                                {{ $umkm->address ?? 'Lokasi tidak tersedia' }}
                            </span>
                        </div>
                    @endif
                </div>

                <!-- Action Buttons -->
                <div class="mt-6 space-y-4">
                    <!-- Primary Actions -->
                    <div class="flex flex-col sm:flex-row gap-4">
                        @if($umkm && $umkm->no_wa)
                            @php
                                $no_wa_clean = preg_replace('/[^0-9]/', '', $umkm->no_wa);
                                $message = urlencode("Halo, saya tertarik dengan layanan {$layanan->nama} Anda di UMKM {$umkm->nama}.");
                            @endphp
                            <a href="https://wa.me/{{ $no_wa_clean }}?text={{ $message }}" 
                               target="_blank" 
                               data-no-loading="true"
                               class="flex-1 bg-green-500 hover:bg-green-600 text-white font-bold py-4 px-6 rounded-lg transition-colors duration-300 flex items-center justify-center gap-3 shadow-md hover:shadow-lg">
                                <i class="fab fa-whatsapp text-2xl"></i>
                                <span>Hubungi Penjual</span>
                            </a>
                        @else
                            <button disabled class="flex-1 bg-gray-300 text-gray-500 font-bold py-4 px-6 rounded-lg cursor-not-allowed flex items-center justify-center gap-3" title="Nomor WhatsApp tidak tersedia">
                                <i class="fab fa-whatsapp text-2xl"></i>
                                <span>Hubungi Penjual</span>
                            </button>
                        @endif
                        
                        <button type="button" 
                                onclick="shareProduct()"
                                class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold py-4 px-6 rounded-lg transition-colors duration-300 flex items-center justify-center gap-3 shadow-md hover:shadow-lg">
                            <i class="fas fa-share-alt text-xl"></i>
                            <span>Bagikan</span>
                        </button>
                    </div>
                    
                    <!-- Social Media & E-commerce Buttons -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <!-- Instagram Button -->
                        @if($umkm && $umkm->instagram_url)
                            <a href="{{ $umkm->instagram_url }}" 
                               target="_blank" 
                               rel="noopener noreferrer"
                               data-no-loading="true"
                               class="bg-gradient-to-r from-pink-500 to-purple-600 hover:from-pink-600 hover:to-purple-700 text-white font-bold py-3 px-4 rounded-lg transition-all duration-300 flex items-center justify-center gap-2 shadow-md hover:shadow-lg transform hover:scale-105">
                                <i class="fab fa-instagram text-xl"></i>
                                <span class="text-sm">Instagram</span>
                            </a>
                        @else
                            <button disabled class="bg-gray-300 text-gray-500 font-bold py-3 px-4 rounded-lg cursor-not-allowed flex items-center justify-center gap-2" title="Instagram tidak tersedia">
                                <i class="fab fa-instagram text-xl"></i>
                                <span class="text-sm">Instagram</span>
                            </button>
                        @endif
                        
                        <!-- Shopee Button -->
                        @if($umkm && $umkm->shopee_url)
                            <a href="{{ $umkm->shopee_url }}" 
                               target="_blank" 
                               rel="noopener noreferrer"
                               data-no-loading="true"
                               class="bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white font-bold py-3 px-4 rounded-lg transition-all duration-300 flex items-center justify-center gap-2 shadow-md hover:shadow-lg transform hover:scale-105">
                                <i class="fas fa-shopping-bag text-xl"></i>
                                <span class="text-sm">Shopee</span>
                            </a>
                        @else
                            <button disabled class="bg-gray-300 text-gray-500 font-bold py-3 px-4 rounded-lg cursor-not-allowed flex items-center justify-center gap-2" title="Shopee tidak tersedia">
                                <i class="fas fa-shopping-bag text-xl"></i>
                                <span class="text-sm">Shopee</span>
                            </button>
                        @endif
                        
                        <!-- Tokopedia Button -->
                        @if($umkm && $umkm->tokopedia_url)
                            <a href="{{ $umkm->tokopedia_url }}" 
                               target="_blank" 
                               rel="noopener noreferrer"
                               data-no-loading="true"
                               class="bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-bold py-3 px-4 rounded-lg transition-all duration-300 flex items-center justify-center gap-2 shadow-md hover:shadow-lg transform hover:scale-105">
                                <i class="fas fa-store text-xl"></i>
                                <span class="text-sm">Tokopedia</span>
                            </a>
                        @else
                            <button disabled class="bg-gray-300 text-gray-500 font-bold py-3 px-4 rounded-lg cursor-not-allowed flex items-center justify-center gap-2" title="Tokopedia tidak tersedia">
                                <i class="fas fa-store text-xl"></i>
                                <span class="text-sm">Tokopedia</span>
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Seller Info -->
                @if($umkm)
                    <div class="mt-8 p-5 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-purple-500 to-blue-500 flex items-center justify-center text-white text-2xl flex-shrink-0 overflow-hidden">
                                @if($umkm->photo_path)
                                    <img src="{{ asset('storage/' . $umkm->photo_path) }}" 
                                         alt="{{ $umkm->nama }}" 
                                         class="w-full h-full object-cover">
                                @else
                                    <i class="fas fa-store"></i>
                                @endif
                            </div>
                            <div class="flex-1">
                                <h3 class="font-bold text-lg text-gray-900 mb-2">{{ $umkm->nama }}</h3>
                                @if($umkm && !is_null($umkm->latitude) && !is_null($umkm->longitude) && $umkm->latitude != 0 && $umkm->longitude != 0)
                                    <div class="mb-2">
                                        <p class="text-xs text-gray-500 mb-1 flex items-center">
                                            <i class="fas fa-map-marker-alt text-red-500 mr-1.5"></i>
                                            <span class="font-medium">Alamat Lengkap:</span>
                                        </p>
                                        <p class="text-sm text-gray-700 leading-relaxed ml-5 address-layanan-text" 
                                           data-lat="{{ (float)$umkm->latitude }}" 
                                           data-lng="{{ (float)$umkm->longitude }}">
                                            <i class="fas fa-spinner fa-spin text-[#009b97]"></i> Memuat alamat...
                                        </p>
                                    </div>
                                @else
                                <p class="text-sm text-gray-600 mb-2">
                                        <i class="fas fa-map-marker-alt text-gray-400 mr-1"></i>
                                        <span>Lokasi UMKM tidak tersedia</span>
                                </p>
                                @endif
                                <a href="{{ route('public.umkm.show', $umkm->id) }}" 
                                   class="text-[#009b97] hover:text-[#007a77] text-sm font-medium inline-flex items-center gap-1">
                                    Buka Profil <i class="fas fa-arrow-right text-xs"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Comments Section for Layanan -->
    <div class="mt-8 bg-white rounded-xl shadow-sm p-4 md:p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-comments text-[#009b97]"></i> Komentar & Ulasan Layanan
                </h2>
                <div class="mt-2 flex items-center gap-4 text-sm text-gray-600">
                    <div class="flex items-center gap-2">
                        <div class="flex items-center">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star {{ $i <= round($averageRatingLayanan) ? 'text-yellow-400' : 'text-gray-300' }} text-sm"></i>
                            @endfor
                        </div>
                        <span class="font-semibold text-gray-900">{{ number_format($averageRatingLayanan, 1) }}</span>
                    </div>
                    <span class="text-gray-500">({{ $totalCommentsLayanan }} ulasan)</span>
                </div>
            </div>
        </div>

        <!-- Comment Form (only for authenticated users) -->
        @auth
            <div class="mb-8 border-b border-gray-200 pb-6">
                @if($userCommentLayanan)
                    <!-- Edit Comment Form -->
                    <div id="edit-comment-layanan-form" class="bg-gray-50 rounded-lg p-4 md:p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <i class="fas fa-edit text-[#009b97]"></i> Edit Komentar Anda
                        </h3>
                        <form id="updateCommentLayananForm" onsubmit="updateCommentLayanan(event, {{ $userCommentLayanan->id }})">
                            @csrf
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
                                <div class="flex items-center gap-2" id="rating-stars-layanan-edit">
                                    @for($i = 5; $i >= 1; $i--)
                                        <button type="button" onclick="setRatingLayanan({{ $i }}, 'edit')" class="rating-star-layanan-edit text-2xl focus:outline-none">
                                            <i class="far fa-star text-gray-300 hover:text-yellow-400"></i>
                                        </button>
                                    @endfor
                                </div>
                                <input type="hidden" name="rating" id="rating-input-layanan-edit" value="{{ $userCommentLayanan->rating }}" required>
                            </div>
                            <div class="mb-4">
                                <label for="comment-layanan-edit" class="block text-sm font-medium text-gray-700 mb-2">Komentar</label>
                                <textarea id="comment-layanan-edit" name="comment" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#009b97] focus:border-[#009b97] resize-none" required>{{ $userCommentLayanan->comment }}</textarea>
                                <p class="text-xs text-gray-500 mt-1">Minimal 10 karakter, maksimal 1000 karakter</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <button type="submit" class="bg-[#009b97] hover:bg-[#007a77] text-white px-6 py-2 rounded-lg font-semibold transition-colors">
                                    <i class="fas fa-save mr-2"></i> Simpan Perubahan
                                </button>
                                <button type="button" onclick="cancelEditLayanan()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg font-semibold transition-colors">
                                    Batal
                                </button>
                                <button type="button" onclick="deleteCommentLayanan({{ $userCommentLayanan->id }})" class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-lg font-semibold transition-colors">
                                    <i class="fas fa-trash mr-2"></i> Hapus
                                </button>
                            </div>
                        </form>
                    </div>
                    <div id="view-comment-layanan" class="hidden">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="font-semibold text-gray-900">{{ Auth::user()->name }}</span>
                                        <div class="flex items-center">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star {{ $i <= $userCommentLayanan->rating ? 'text-yellow-400' : 'text-gray-300' }} text-xs"></i>
                                            @endfor
                                        </div>
                                        <span class="text-xs text-gray-500">{{ $userCommentLayanan->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-gray-700">{{ $userCommentLayanan->comment }}</p>
                                </div>
                                <button onclick="showEditFormLayanan()" class="text-[#009b97] hover:text-[#007a77] ml-4">
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
                        <form id="commentLayananForm" onsubmit="submitCommentLayanan(event, {{ $layanan->id }})">
                            @csrf
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
                                <div class="flex items-center gap-2" id="rating-stars-layanan">
                                    @for($i = 5; $i >= 1; $i--)
                                        <button type="button" onclick="setRatingLayanan({{ $i }}, 'new')" class="rating-star-layanan text-2xl focus:outline-none">
                                            <i class="far fa-star text-gray-300 hover:text-yellow-400"></i>
                                        </button>
                                    @endfor
                                </div>
                                <input type="hidden" name="rating" id="rating-input-layanan" value="5" required>
                            </div>
                            <div class="mb-4">
                                <label for="comment-layanan" class="block text-sm font-medium text-gray-700 mb-2">Komentar</label>
                                <textarea id="comment-layanan" name="comment" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#009b97] focus:border-[#009b97] resize-none" placeholder="Bagikan pengalaman Anda tentang layanan ini..." required></textarea>
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
        <div id="comments-layanan-list">
            @if($commentsLayanan->count() > 0)
                <div class="space-y-4">
                    @foreach($commentsLayanan as $comment)
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow comment-layanan-item" data-comment-id="{{ $comment->id }}">
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
            @else
                <div class="text-center py-8">
                    <i class="fas fa-comments text-gray-300 text-4xl mb-3"></i>
                    <p class="text-gray-500">Belum ada komentar untuk layanan ini</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Similar Products Section -->
    @if($similarLayanan->count() > 0)
        <div class="mt-12">
            <h2 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-6">Layanan Serupa</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($similarLayanan as $similarItem)
                    @php
                        $similarUmkm = $similarItem->umkm->first();
                    @endphp
                    <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden group cursor-pointer"
                         onclick="window.location.href='{{ route('public.layanan.show', $similarItem->id) }}'">
                        <!-- Product Image -->
                        <div class="relative w-full h-48 bg-gray-200 overflow-hidden">
                            @if($similarItem->photo_path)
                                <img src="{{ asset('storage/' . $similarItem->photo_path) }}" 
                                     alt="{{ $similarItem->nama }}" 
                                     class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400 bg-gray-200">
                                    <i class="fas fa-image text-4xl"></i>
                                </div>
                            @endif
                            
                            <!-- Category Badge -->
                            @if($similarUmkm && $similarUmkm->jenis_umkm)
                                <span class="absolute top-3 left-3 bg-white text-black text-xs font-semibold px-2 py-1 rounded-full shadow-sm">
                                    {{ $similarUmkm->jenis_umkm }}
                                </span>
                            @endif
                        </div>
                        
                        <!-- Product Info -->
                        <div class="p-4">
                            <h3 class="font-semibold text-lg text-gray-900 mb-2 line-clamp-1">
                                {{ $similarItem->nama }}
                            </h3>
                            <p class="text-sm text-gray-600 mb-3 line-clamp-2 min-h-[2.5rem]">
                                {{ $similarItem->description ?? 'Tidak ada deskripsi.' }}
                            </p>
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-[#009b97] text-lg">
                                    Rp {{ number_format($similarItem->price, 0, ',', '.') }}
                                </span>
                                @if($similarUmkm)
                                    <span class="text-xs text-gray-500 flex items-center">
                                        <i class="fas fa-store mr-1"></i>
                                        <span class="truncate max-w-[100px]">{{ $similarUmkm->nama }}</span>
                                    </span>
                                @endif
                            </div>
                            <button onclick="event.stopPropagation(); window.location.href='{{ route('public.layanan.show', $similarItem->id) }}'" 
                            class="w-full bg-[#32a752] hover:bg-[#218689] text-white text-center py-2 rounded-lg font-medium transition-colors text-sm">
                        Lihat Detail
                    </button>
                        </div>
                    </div>
               
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    async function toggleFavoriteWithAlert(umkmId, button, umkmNama) {
        const icon = button.querySelector('i');
        const isCurrentlyFavorite = icon.classList.contains('fas');
        
        // Show confirmation/info alert before toggling
        if (!isCurrentlyFavorite) {
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
        
        // Toggle favorite
        fetch(`/user/favorite/${umkmId}/toggle`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update UI
                if (data.is_favorited !== undefined ? data.is_favorited : !isCurrentlyFavorite) {
                    button.classList.remove('text-gray-400');
                    button.classList.add('text-red-600');
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                    
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'UMKM telah ditambahkan ke favorit Anda',
                        timer: 2000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                } else {
                    button.classList.remove('text-red-600');
                    button.classList.add('text-gray-400');
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                    
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'UMKM telah dihapus dari favorit Anda',
                        timer: 2000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                }
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: data.message || 'Gagal mengubah status favorit.',
                    confirmButtonColor: '#ef4444'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Terjadi kesalahan saat memproses permintaan.',
                confirmButtonColor: '#ef4444'
            });
        });
    }

    function shareProduct() {
        if (navigator.share) {
            navigator.share({
                title: '{{ $layanan->nama }}',
                text: 'Lihat layanan ini: {{ $layanan->nama }}',
                url: window.location.href
            }).catch(err => console.log('Error sharing:', err));
        } else {
            // Fallback: Copy to clipboard
            navigator.clipboard.writeText(window.location.href).then(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Link berhasil disalin ke clipboard!',
                    timer: 2000,
                    showConfirmButton: false,
                    confirmButtonColor: '#009b97'
                });
            }).catch(err => {
                console.error('Error copying to clipboard:', err);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Gagal menyalin link. Silakan salin manual dari address bar.',
                    confirmButtonColor: '#009b97'
                });
            });
        }
    }
    
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
    
    // Load address for a specific element - always use full address format (same as detail-umkm)
    async function loadAddressForElement(element) {
        if (!element) {
            console.warn('Element not found');
            return;
        }
        
        const lat = element.getAttribute('data-lat');
        const lng = element.getAttribute('data-lng');
        
        if (!lat || !lng || lat === 'null' || lng === 'null' || lat === '' || lng === '') {
            console.warn('Invalid coordinates:', lat, lng);
            element.innerHTML = 'Lokasi tidak tersedia';
            element.classList.remove('text-[#009b97]', 'text-gray-700');
            element.classList.add('text-gray-400');
            return;
        }
        
        try {
            // Use the formatted address from fetchAddressFromCoordinates (same as detail-umkm)
            const fullAddress = await fetchAddressFromCoordinates(lat, lng);
            
            if (fullAddress) {
                element.innerHTML = fullAddress;
                element.classList.remove('text-[#009b97]', 'text-gray-400');
                element.classList.add('text-gray-700');
                console.log('Address loaded successfully:', fullAddress);
            } else {
                // Fallback: try to get display_name directly
                const response = await fetch(
                    `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1&accept-language=id`,
                    {
                        headers: {
                            'User-Agent': 'UMKM-App/1.0'
                        }
                    }
                );
                
                if (response.ok) {
                    const data = await response.json();
                    if (data && data.display_name) {
                        element.innerHTML = data.display_name;
                        element.classList.remove('text-[#009b97]', 'text-gray-400');
                        element.classList.add('text-gray-700');
                    } else {
                        throw new Error('No address found');
                    }
                } else {
                    throw new Error('Network response was not ok');
                }
            }
        } catch (error) {
            console.error('Error loading address:', error);
            element.innerHTML = 'Lokasi tidak tersedia';
            element.classList.remove('text-[#009b97]', 'text-gray-700');
            element.classList.add('text-gray-400');
        }
    }
    
    // Load address for detail-layanan page
    async function loadAddressFromCoordinates() {
        // Load address for location-name (top section) - use full address
        const locationElement = document.getElementById('location-name');
        if (locationElement) {
            await loadAddressForElement(locationElement);
        }
        
        // Load address for address-layanan-text (seller info section) - use full address
        const addressElement = document.querySelector('.address-layanan-text');
        if (addressElement) {
            await loadAddressForElement(addressElement);
        }
    }
    
    // Load address when page is ready - ensure it runs after DOM is fully loaded
    function initAddressLoader() {
        // Wait a bit longer to ensure all elements are rendered
        setTimeout(function() {
            console.log('Initializing address loader...');
            loadAddressFromCoordinates();
        }, 300);
    }
    
    // Try multiple methods to ensure function runs
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAddressLoader);
    } else if (document.readyState === 'interactive' || document.readyState === 'complete') {
        // DOM is already loading or loaded
        initAddressLoader();
    } else {
        // Fallback
        window.addEventListener('load', initAddressLoader);
    }
    
    // Also try after a delay as fallback
    setTimeout(function() {
        const locationElement = document.getElementById('location-name');
        const addressElement = document.querySelector('.address-layanan-text');
        const needsRetry = (locationElement && locationElement.innerHTML.includes('Memuat lokasi')) ||
                          (addressElement && addressElement.innerHTML.includes('Memuat alamat'));
        
        if (needsRetry) {
            console.log('Retry loading address...');
            loadAddressFromCoordinates();
        }
    }, 1000);

    // ========== LAYANAN COMMENT FUNCTIONS ==========
    
    // Set rating for layanan comment
    function setRatingLayanan(rating, type) {
        const inputId = type === 'edit' ? 'rating-input-layanan-edit' : 'rating-input-layanan';
        const starsId = type === 'edit' ? 'rating-stars-layanan-edit' : 'rating-stars-layanan';
        const starClass = type === 'edit' ? 'rating-star-layanan-edit' : 'rating-star-layanan';
        
        document.getElementById(inputId).value = rating;
        const stars = document.querySelectorAll(`#${starsId} .${starClass}`);
        
        stars.forEach((star, index) => {
            const starIndex = 5 - index; // Reverse order
            const icon = star.querySelector('i');
            if (starIndex <= rating) {
                icon.classList.remove('far');
                icon.classList.add('fas', 'text-yellow-400');
            } else {
                icon.classList.remove('fas', 'text-yellow-400');
                icon.classList.add('far', 'text-gray-300');
            }
        });
    }

    // Submit comment for layanan
    function submitCommentLayanan(event, layananId) {
        event.preventDefault();
        
        const form = event.target;
        const formData = new FormData(form);
        const comment = formData.get('comment');
        const rating = formData.get('rating');
        
        if (!comment || comment.length < 10) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Komentar minimal 10 karakter'
            });
            return;
        }
        
        // Disable submit button
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Mengirim...';
        
        fetch(`/layanan/${layananId}/comment`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ comment, rating: parseInt(rating) })
        })
        .then(async response => {
            const data = await response.json();
            
            if (!response.ok) {
                // Handle validation errors
                if (data.errors) {
                    const errorMessages = Object.values(data.errors).flat().join(', ');
                    throw new Error(errorMessages);
                }
                throw new Error(data.message || 'Terjadi kesalahan saat mengirim komentar');
            }
            
            return data;
        })
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
                    title: 'Error',
                    text: data.message || 'Terjadi kesalahan saat mengirim komentar'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'Terjadi kesalahan saat mengirim komentar'
            });
        })
        .finally(() => {
            // Re-enable submit button
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        });
    }

    // Update comment for layanan
    function updateCommentLayanan(event, commentId) {
        event.preventDefault();
        
        const form = event.target;
        const formData = new FormData(form);
        const comment = formData.get('comment');
        const rating = formData.get('rating');
        
        if (!comment || comment.length < 10) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Komentar minimal 10 karakter'
            });
            return;
        }
        
        // Disable submit button
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Menyimpan...';
        
        fetch(`/layanan/comment/${commentId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ comment, rating: parseInt(rating) })
        })
        .then(async response => {
            const data = await response.json();
            
            if (!response.ok) {
                // Handle validation errors
                if (data.errors) {
                    const errorMessages = Object.values(data.errors).flat().join(', ');
                    throw new Error(errorMessages);
                }
                throw new Error(data.message || 'Terjadi kesalahan saat memperbarui komentar');
            }
            
            return data;
        })
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
                    title: 'Error',
                    text: data.message || 'Terjadi kesalahan saat memperbarui komentar'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'Terjadi kesalahan saat memperbarui komentar'
            });
        })
        .finally(() => {
            // Re-enable submit button
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        });
    }

    // Delete comment for layanan
    function deleteCommentLayanan(commentId) {
        Swal.fire({
            title: 'Hapus Komentar?',
            text: 'Apakah Anda yakin ingin menghapus komentar ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/layanan/comment/${commentId}`, {
                    method: 'DELETE',
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
                            title: 'Error',
                            text: data.message
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan saat menghapus komentar'
                    });
                });
            }
        });
    }

    // Show edit form for layanan comment
    function showEditFormLayanan() {
        document.getElementById('edit-comment-layanan-form').classList.remove('hidden');
        document.getElementById('view-comment-layanan').classList.add('hidden');
        
        // Initialize rating stars
        const rating = document.getElementById('rating-input-layanan-edit').value;
        setRatingLayanan(rating, 'edit');
    }

    // Cancel edit for layanan comment
    function cancelEditLayanan() {
        document.getElementById('edit-comment-layanan-form').classList.add('hidden');
        document.getElementById('view-comment-layanan').classList.remove('hidden');
    }

    // Initialize rating stars on page load for edit form
    @if($userCommentLayanan ?? false)
        document.addEventListener('DOMContentLoaded', function() {
            const rating = {{ $userCommentLayanan->rating ?? 5 }};
            setRatingLayanan(rating, 'edit');
        });
    @endif
</script>
@endsection
