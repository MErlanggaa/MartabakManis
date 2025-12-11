@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 pb-24">
    <!-- User Profile Header -->
    <div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
        <div class="flex flex-col items-center text-center">
            <div class="w-24 h-24 bg-gradient-to-br from-[#009b97] to-[#007a77] rounded-full flex items-center justify-center text-white text-3xl font-bold mb-4 shadow-lg">
                {{ substr($user->name, 0, 1) }}
            </div>
            <h1 class="text-2xl font-bold text-gray-800">{{ $user->name }}</h1>
            <p class="text-gray-500">{{ $user->email }}</p>
            
            <div class="flex gap-8 mt-6">
                <div class="text-center">
                    <span class="block text-xl font-bold text-gray-800">{{ $following->count() }}</span>
                    <span class="text-sm text-gray-500">Mengikuti</span>
                </div>
                <div class="text-center">
                    <span class="block text-xl font-bold text-gray-800">{{ $likedVideos->count() }}</span>
                    <span class="text-sm text-gray-500">Video Disukai</span>
                </div>
            </div>
            
            <div class="mt-6 flex flex-wrap justify-center gap-3">
                <a href="{{ route('user.edit.profile') }}" class="px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-full font-medium transition-colors">
                    Edit Profil
                </a>
                <a href="{{ route('user.history.laporan') }}" class="px-6 py-2 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-full font-medium transition-colors">
                    History Laporan
                </a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-6 py-2 bg-red-50 hover:bg-red-100 text-red-600 rounded-full font-medium transition-colors">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Stats/Tabs -->
    <div x-data="{ activeTab: 'following' }" class="bg-white rounded-2xl shadow-sm overflow-hidden min-h-[400px]">
        <!-- Tabs Header -->
        <div class="flex border-b border-gray-100">
            <button @click="activeTab = 'following'" 
                    class="flex-1 py-4 text-center font-medium transition-colors relative"
                    :class="activeTab === 'following' ? 'text-[#009b97]' : 'text-gray-500 hover:text-gray-700'">
                Mengikuti
                <div x-show="activeTab === 'following'" class="absolute bottom-0 left-0 w-full h-0.5 bg-[#009b97]"></div>
            </button>
            <button @click="activeTab = 'liked'" 
                    class="flex-1 py-4 text-center font-medium transition-colors relative"
                    :class="activeTab === 'liked' ? 'text-[#009b97]' : 'text-gray-500 hover:text-gray-700'">
                Video Disukai
                <div x-show="activeTab === 'liked'" class="absolute bottom-0 left-0 w-full h-0.5 bg-[#009b97]"></div>
            </button>
        </div>

        <!-- Tab Contents -->
        <div class="p-4">
            <!-- Following Tab -->
            <div x-show="activeTab === 'following'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                @if($following->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($following as $umkm)
                            <div class="flex items-center p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
                                <a href="{{ route('videos.umkm.profile', $umkm->id) }}" class="flex-shrink-0">
                                    @if($umkm->photo_path)
                                        <img src="{{ Storage::url($umkm->photo_path) }}" alt="{{ $umkm->nama }}" class="w-12 h-12 rounded-full object-cover border border-gray-200">
                                    @else
                                        <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center text-gray-500 font-bold border border-gray-300">
                                            {{ substr($umkm->nama, 0, 2) }}
                                        </div>
                                    @endif
                                </a>
                                <div class="ml-4 flex-1 min-w-0">
                                    <a href="{{ route('videos.umkm.profile', $umkm->id) }}" class="block text-gray-900 font-bold truncate hover:text-[#009b97]">
                                        {{ $umkm->nama }}
                                    </a>
                                    <p class="text-sm text-gray-500 truncate">{{ $umkm->jenis_umkm }}</p>
                                </div>
                                <form action="{{ route('user.follow.toggle', $umkm->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 bg-gray-200 text-gray-700 text-sm rounded-lg hover:bg-gray-300 transition-colors">
                                        Following
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-user-friends text-gray-400 text-2xl"></i>
                        </div>
                        <p class="text-gray-500 font-medium">Belum mengikuti UMKM siapapun</p>
                        <a href="{{ route('videos.index') }}" class="inline-block mt-4 text-[#009b97] font-medium hover:underline">
                            Cari di Video
                        </a>
                    </div>
                @endif
            </div>

            <!-- Liked Videos Tab -->
            <div x-show="activeTab === 'liked'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                @if($likedVideos->count() > 0)
                    <div class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-1">
                        @foreach($likedVideos as $video)
                            <a href="{{ route('videos.show', $video->id) }}?source=liked" class="block relative aspect-[9/16] bg-gray-200 rounded-xl overflow-hidden group">
                                <img src="{{ $video->thumbnail_path ? Storage::url($video->thumbnail_path) : 'https://via.placeholder.com/300x533?text=Video' }}" alt="Video Thumbnail" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                
                                <div class="absolute inset-0 bg-black/10 group-hover:bg-black/20 transition-colors"></div>
                                
                                <div class="absolute top-2 left-2 flex items-center gap-1 text-white text-xs font-bold drop-shadow-md">
                                    <i class="fas fa-play"></i> {{ number_format($video->views) }}
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-heart text-gray-400 text-2xl"></i>
                        </div>
                        <p class="text-gray-500 font-medium">Belum ada video yang disukai</p>
                        <a href="{{ route('videos.index') }}" class="inline-block mt-4 text-[#009b97] font-medium hover:underline">
                            Tonton Video
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Alpine.js is assumed to be loaded in layout or via CDN in head -->
<script src="//unpkg.com/alpinejs" defer></script>
@endsection
