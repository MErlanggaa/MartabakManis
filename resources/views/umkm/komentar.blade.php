@extends('layouts.app')

@section('title', 'Komentar - UMKM')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-[#009b97] to-[#039b00] rounded-xl flex items-center justify-center text-white">
                        <i class="fas fa-comments text-xl"></i>
                    </div>
                    Komentar & Ulasan
                </h1>
                <p class="text-gray-600 mt-2">Lihat semua komentar dan ulasan untuk UMKM dan layanan Anda</p>
            </div>
            <a href="{{ route('umkm.dashboard') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition-colors inline-flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>

    <!-- UMKM Comments Section -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-store text-[#009b97]"></i> Komentar untuk UMKM
                </h2>
                <div class="mt-2 flex items-center gap-4 text-sm text-gray-600">
                    <div class="flex items-center gap-2">
                        <div class="flex items-center">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star {{ $i <= round($averageRatingUmkm) ? 'text-yellow-400' : 'text-gray-300' }} text-sm"></i>
                            @endfor
                        </div>
                        <span class="font-semibold text-gray-900">{{ number_format($averageRatingUmkm, 1) }}</span>
                    </div>
                    <span class="text-gray-500">({{ $totalCommentsUmkm }} ulasan)</span>
                </div>
            </div>
        </div>

        <!-- Comments List -->
        <div id="comments-umkm-list">
            @if($commentsUmkm->count() > 0)
                <div class="space-y-4">
                    @foreach($commentsUmkm as $comment)
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-3">
                                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-[#009b97] to-[#039b00] flex items-center justify-center text-white font-semibold text-lg">
                                            {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="font-semibold text-gray-900">{{ $comment->user->name }}</span>
                                                <div class="flex items-center gap-1">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <i class="fas fa-star {{ $i <= $comment->rating ? 'text-yellow-400' : 'text-gray-300' }} text-xs"></i>
                                                    @endfor
                                                </div>
                                            </div>
                                            <p class="text-xs text-gray-500">{{ $comment->created_at->format('d M Y, H:i') }}</p>
                                        </div>
                                    </div>
                                    <p class="text-gray-700 leading-relaxed ml-16">{{ $comment->comment }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-comments text-gray-300 text-5xl mb-4"></i>
                    <p class="text-gray-500 text-lg">Belum ada komentar untuk UMKM Anda</p>
                    <p class="text-gray-400 text-sm mt-2">Komentar dari pelanggan akan muncul di sini</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Layanan Comments Section -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-list text-[#039b00]"></i> Komentar untuk Layanan/Menu
                </h2>
                <p class="text-sm text-gray-600 mt-1">Komentar untuk setiap layanan/menu yang Anda tawarkan</p>
            </div>
        </div>

        @if($layananList->count() > 0)
            <div class="space-y-6">
                @foreach($layananList as $layanan)
                    @php
                        $layananComments = $commentsByLayanan->get($layanan->id, collect());
                        $avgRating = $averageRatingsLayanan[$layanan->id] ?? 0;
                    @endphp
                    
                    <div class="border border-gray-200 rounded-lg p-6">
                        <!-- Layanan Header -->
                        <div class="flex items-start gap-4 mb-4 pb-4 border-b border-gray-200">
                            <div class="w-20 h-20 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                @if($layanan->photo_path)
                                    <img src="{{ asset('storage/' . $layanan->photo_path) }}" 
                                         alt="{{ $layanan->nama }}" 
                                         class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                                        <i class="fas fa-image text-2xl"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ $layanan->nama }}</h3>
                                <p class="text-sm text-gray-600 mb-2 line-clamp-2">{{ $layanan->description }}</p>
                                <div class="flex items-center gap-4 text-sm">
                                    <div class="flex items-center gap-2">
                                        <div class="flex items-center">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star {{ $i <= round($avgRating) ? 'text-yellow-400' : 'text-gray-300' }} text-xs"></i>
                                            @endfor
                                        </div>
                                        <span class="font-semibold text-gray-900 ml-1">{{ number_format($avgRating, 1) }}</span>
                                    </div>
                                    <span class="text-gray-500">({{ $layananComments->count() }} ulasan)</span>
                                    <span class="text-[#009b97] font-semibold">Rp {{ number_format($layanan->price, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Comments for this Layanan -->
                        <div id="comments-layanan-{{ $layanan->id }}">
                            @if($layananComments->count() > 0)
                                <div class="space-y-4">
                                    @foreach($layananComments as $comment)
                                        <div class="border-l-4 border-[#039b00] bg-gray-50 rounded-r-lg p-4">
                                            <div class="flex items-start gap-3">
                                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#039b00] to-[#009b97] flex items-center justify-center text-white font-semibold text-sm flex-shrink-0">
                                                    {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                                                </div>
                                                <div class="flex-1">
                                                    <div class="flex items-center gap-2 mb-2">
                                                        <span class="font-semibold text-gray-900 text-sm">{{ $comment->user->name }}</span>
                                                        <div class="flex items-center gap-1">
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <i class="fas fa-star {{ $i <= $comment->rating ? 'text-yellow-400' : 'text-gray-300' }} text-xs"></i>
                                                            @endfor
                                                        </div>
                                                        <span class="text-xs text-gray-500 ml-2">{{ $comment->created_at->format('d M Y, H:i') }}</span>
                                                    </div>
                                                    <p class="text-gray-700 text-sm leading-relaxed">{{ $comment->comment }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8 bg-gray-50 rounded-lg">
                                    <i class="fas fa-comment-slash text-gray-300 text-3xl mb-2"></i>
                                    <p class="text-gray-500 text-sm">Belum ada komentar untuk layanan ini</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <i class="fas fa-list text-gray-300 text-5xl mb-4"></i>
                <p class="text-gray-500 text-lg">Belum ada layanan yang ditambahkan</p>
                <p class="text-gray-400 text-sm mt-2">Tambahkan layanan terlebih dahulu untuk menerima komentar</p>
                <a href="{{ route('umkm.dashboard') }}" class="inline-block mt-4 bg-[#009b97] hover:bg-[#007a77] text-white px-6 py-2 rounded-lg transition-colors">
                    <i class="fas fa-plus mr-2"></i> Tambah Layanan
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Smooth scroll to comments
    document.addEventListener('DOMContentLoaded', function() {
        // Add any additional JavaScript if needed
    });
</script>
@endsection


