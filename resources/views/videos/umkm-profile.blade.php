<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $umkm->nama }} - Profile</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-white">
    <!-- Header -->
    <header class="sticky top-0 z-50 bg-white border-b border-gray-200">
        <div class="max-w-2xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ route('videos.index') }}" class="text-gray-700 hover:text-gray-900">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <h1 class="text-lg font-bold text-gray-900">{{ $umkm->nama }}</h1>
            <button class="text-gray-700 hover:text-gray-900">
                <i class="fas fa-ellipsis-h text-xl"></i>
            </button>
        </div>
    </header>

    <div class="max-w-2xl mx-auto px-4 py-6">
        <!-- Profile Header -->
        <div class="text-center mb-6">
            <!-- Profile Photo -->
            <div class="w-24 h-24 mx-auto mb-4 rounded-full overflow-hidden bg-gray-200">
                @if($umkm->photo_path)
                    <img src="{{ Storage::url($umkm->photo_path) }}" alt="{{ $umkm->nama }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-purple-500 to-blue-500 text-white text-2xl font-bold">
                        {{ substr($umkm->nama, 0, 2) }}
                    </div>
                @endif
            </div>

            <!-- Name & Username -->
            <h2 class="text-2xl font-bold text-gray-900 mb-1">{{ $umkm->nama }}</h2>
            <!-- <p class="text-gray-600 text-sm mb-4">@{{ strtolower(str_replace(' ', '', $umkm->nama)) }}</p> -->

            <!-- Stats -->
            <div class="flex justify-center gap-8 mb-6">
                <div class="text-center">
                    <p class="text-2xl font-bold text-gray-900">{{ $totalVideos }}</p>
                    <p class="text-sm text-gray-600">Total Video</p>
                </div>
             
                <div class="text-center">
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($totalFollowers) }}</p>
                    <p class="text-sm text-gray-600">Pengikut</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($totalLikes) }}</p>
                    <p class="text-sm text-gray-600">Suka</p>
                </div>
            </div>

            <!-- Bio -->
            @if($umkm->description)
                <p class="text-gray-700 text-sm mb-4 max-w-md mx-auto">{{ $umkm->description }}</p>
            @endif

            <!-- Action Buttons -->
            <div class="flex gap-3 justify-center mb-6">
                @auth
                    @if(Auth::user()->role === 'user')
                        @if(Auth::user()->following->contains($umkm->id))
                            <form action="{{ route('user.follow.toggle', $umkm->id) }}" method="POST" class="flex-1 max-w-xs">
                                @csrf
                                <button type="submit" class="w-full bg-gray-200 hover:bg-gray-300 text-gray-900 px-6 py-2 rounded-lg font-bold transition">
                                    <i class="fas fa-check mr-1"></i> Mengikuti
                                </button>
                            </form>
                        @else
                            <form action="{{ route('user.follow.toggle', $umkm->id) }}" method="POST" class="flex-1 max-w-xs">
                                @csrf
                                <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-lg font-bold transition">
                                    Ikuti
                                </button>
                            </form>
                        @endif
                    @endif
                @endauth
                
                <a href="{{ route('public.umkm.show', $umkm->id) }}" class="flex-1 max-w-xs bg-white border-2 border-gray-300 hover:bg-gray-50 text-gray-900 px-6 py-2 rounded-lg font-bold transition text-center">
                    <i class="fas fa-store mr-2"></i>Kunjungi Toko
                </a>
            </div>

            <!-- Contact Info -->
            @if($umkm->no_wa || $umkm->instagram_url || $umkm->shopee_url || $umkm->tokopedia_url)
                <div class="flex justify-center gap-4 mb-6">
                    @if($umkm->no_wa)
                        <a href="https://wa.me/{{ $umkm->no_wa }}" target="_blank" class="text-green-600 hover:text-green-700">
                            <i class="fab fa-whatsapp text-2xl"></i>
                        </a>
                    @endif
                    @if($umkm->instagram_url)
                        <a href="{{ $umkm->instagram_url }}" target="_blank" class="text-pink-600 hover:text-pink-700">
                            <i class="fab fa-instagram text-2xl"></i>
                        </a>
                    @endif
                    @if($umkm->shopee_url)
                        <a href="{{ $umkm->shopee_url }}" target="_blank" class="text-orange-600 hover:text-orange-700">
                            <i class="fas fa-shopping-bag text-2xl"></i>
                        </a>
                    @endif
                    @if($umkm->tokopedia_url)
                        <a href="{{ $umkm->tokopedia_url }}" target="_blank" class="text-green-600 hover:text-green-700">
                            <i class="fas fa-shopping-cart text-2xl"></i>
                        </a>
                    @endif
                </div>
            @endif
        </div>

        <!-- Tabs -->
        <div class="border-b border-gray-200 mb-4">
            <div class="flex">
                <button class="tab-btn flex-1 py-3 text-center font-semibold border-b-2 border-gray-900 text-gray-900" data-tab="posting">
                    <i class="fas fa-th mr-2"></i>Posting
                </button>
                <button class="tab-btn flex-1 py-3 text-center font-semibold border-b-2 border-transparent text-gray-500 hover:text-gray-900" data-tab="produk">
                    <i class="fas fa-shopping-bag mr-2"></i>Produk
                </button>
            </div>
        </div>

        <!-- Video Grid -->
        <div id="posting-tab" class="tab-content">
            @if($umkm->videos->count() > 0)
                <div class="grid grid-cols-3 gap-1">
                    @foreach($umkm->videos as $video)
                        <a href="{{ route('videos.show', $video->id) }}" class="relative aspect-[9/16] bg-gray-200 overflow-hidden group">
                            <video src="{{ Storage::url($video->video_path) }}" class="w-full h-full object-cover"></video>
                            
                            <!-- Overlay -->
                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors flex items-center justify-center">
                                <i class="fas fa-play text-white text-2xl opacity-0 group-hover:opacity-100 transition-opacity"></i>
                            </div>
                            
                            <!-- Stats (Top Left - TikTok Style) -->
                            <div class="absolute top-2 left-2 flex items-center gap-1 text-white text-xs font-bold text-shadow">
                                <i class="fas fa-play"></i>
                                <span>{{ number_format($video->views) }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <div class="w-20 h-20 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-video-slash text-gray-400 text-3xl"></i>
                    </div>
                    <p class="text-gray-500">Belum ada video</p>
                </div>
            @endif
        </div>

        <!-- Product Grid -->
        <div id="produk-tab" class="tab-content hidden">
            @if($umkm->layanan->count() > 0)
                <div class="grid grid-cols-2 gap-4">
                    @foreach($umkm->layanan as $product)
                        <a href="{{ route('public.layanan.show', $product->id) }}" class="bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition">
                            <div class="aspect-square bg-gray-200">
                                @if($product->photo_path)
                                    <img src="{{ Storage::url($product->photo_path) }}" alt="{{ $product->nama }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <i class="fas fa-image text-gray-400 text-3xl"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="p-3">
                                <p class="font-semibold text-gray-900 text-sm truncate">{{ $product->nama }}</p>
                                <p class="text-red-600 font-bold">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <div class="w-20 h-20 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-box-open text-gray-400 text-3xl"></i>
                    </div>
                    <p class="text-gray-500">Belum ada produk</p>
                </div>
            @endif
        </div>
    </div>

    <script>
        // Tab switching
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const tab = this.dataset.tab;
                
                // Update buttons
                document.querySelectorAll('.tab-btn').forEach(b => {
                    b.classList.remove('border-gray-900', 'text-gray-900');
                    b.classList.add('border-transparent', 'text-gray-500');
                });
                this.classList.remove('border-transparent', 'text-gray-500');
                this.classList.add('border-gray-900', 'text-gray-900');
                
                // Update content
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.add('hidden');
                });
                document.getElementById(tab + '-tab').classList.remove('hidden');
            });
        });
    </script>
</body>
</html>
