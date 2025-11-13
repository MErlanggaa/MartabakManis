@extends('layouts.app')

@section('title', '404 - Halaman Tidak Ditemukan')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-gray-50 to-[#e6f5f4]">
    <div class="max-w-2xl w-full text-center">
        <!-- Logo -->
        <div class="mb-8 flex justify-center">
            <div class="h-20 w-auto flex items-center justify-center">
                <img src="{{ asset('gambar/logo.jpeg') }}" 
                     alt="Logo UMKM" 
                     class="h-full w-auto object-contain">
            </div>
        </div>

        <!-- 404 Illustration -->
        <div class="mb-8">
            <div class="inline-flex items-center justify-center w-48 h-48 bg-gradient-to-br from-[#009b97]/10 to-[#039b00]/10 rounded-full mb-6">
                <div class="text-center">
                    <h1 class="text-8xl font-bold text-[#009b97] mb-2">404</h1>
                    <div class="w-24 h-1 bg-gradient-to-r from-[#009b97] to-[#039b00] mx-auto rounded-full"></div>
                </div>
            </div>
        </div>

        <!-- Error Message -->
        <div class="mb-8">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                Halaman Tidak Ditemukan
            </h2>
            <p class="text-lg text-gray-600 mb-2">
                Maaf, halaman yang Anda cari tidak dapat ditemukan.
            </p>
            <p class="text-sm text-gray-500">
                Halaman mungkin telah dipindahkan, dihapus, atau URL yang Anda masukkan salah.
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-8">
            <a href="{{ route('public.katalog') }}" 
               class="inline-flex items-center gap-2 bg-[#009b97] hover:bg-[#007a77] text-white px-6 py-3 rounded-lg font-semibold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105">
                <i class="fas fa-home"></i>
                <span>Kembali ke Beranda</span>
            </a>
            
            <button onclick="window.history.back()" 
                    class="inline-flex items-center gap-2 bg-white hover:bg-gray-50 text-gray-700 border-2 border-gray-300 hover:border-[#009b97] px-6 py-3 rounded-lg font-semibold transition-all duration-300 shadow-md hover:shadow-lg">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali</span>
            </button>
        </div>

        <!-- Helpful Links -->
        <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center justify-center gap-2">
                <i class="fas fa-lightbulb text-[#009b97]"></i>
                <span>Mungkin Anda Mencari:</span>
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('public.katalog') }}" 
                   class="flex flex-col items-center p-4 rounded-lg border border-gray-200 hover:border-[#009b97] hover:bg-[#e6f5f4] transition-all duration-300 group">
                    <i class="fas fa-th-large text-2xl text-[#009b97] mb-2 group-hover:scale-110 transition-transform"></i>
                    <span class="text-sm font-medium text-gray-700 group-hover:text-[#009b97]">Katalog UMKM</span>
                </a>
                
                @auth
                    @if(auth()->user()->role === 'user')
                        <a href="{{ route('user.history.laporan') }}" 
                           class="flex flex-col items-center p-4 rounded-lg border border-gray-200 hover:border-[#009b97] hover:bg-[#e6f5f4] transition-all duration-300 group">
                            <i class="fas fa-history text-2xl text-[#009b97] mb-2 group-hover:scale-110 transition-transform"></i>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-[#009b97]">History Laporan</span>
                        </a>
                    @elseif(auth()->user()->role === 'umkm')
                        <a href="{{ route('umkm.dashboard') }}" 
                           class="flex flex-col items-center p-4 rounded-lg border border-gray-200 hover:border-[#009b97] hover:bg-[#e6f5f4] transition-all duration-300 group">
                            <i class="fas fa-store text-2xl text-[#009b97] mb-2 group-hover:scale-110 transition-transform"></i>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-[#009b97]">Dashboard UMKM</span>
                        </a>
                    @elseif(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}" 
                           class="flex flex-col items-center p-4 rounded-lg border border-gray-200 hover:border-[#009b97] hover:bg-[#e6f5f4] transition-all duration-300 group">
                            <i class="fas fa-tachometer-alt text-2xl text-[#009b97] mb-2 group-hover:scale-110 transition-transform"></i>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-[#009b97]">Dashboard Admin</span>
                        </a>
                    @endif
                @else
                    <a href="{{ route('login') }}" 
                       class="flex flex-col items-center p-4 rounded-lg border border-gray-200 hover:border-[#009b97] hover:bg-[#e6f5f4] transition-all duration-300 group">
                        <i class="fas fa-sign-in-alt text-2xl text-[#009b97] mb-2 group-hover:scale-110 transition-transform"></i>
                        <span class="text-sm font-medium text-gray-700 group-hover:text-[#009b97]">Login</span>
                    </a>
                @endauth
                
                <a href="{{ route('public.laporan') }}" 
                   class="flex flex-col items-center p-4 rounded-lg border border-gray-200 hover:border-[#009b97] hover:bg-[#e6f5f4] transition-all duration-300 group">
                    <i class="fas fa-bug text-2xl text-[#009b97] mb-2 group-hover:scale-110 transition-transform"></i>
                    <span class="text-sm font-medium text-gray-700 group-hover:text-[#009b97]">Laporan</span>
                </a>
            </div>
        </div>

        <!-- Search Box -->
        <div class="mt-8">
            <p class="text-sm text-gray-600 mb-3">Atau cari sesuatu:</p>
            <form action="{{ route('public.katalog') }}" method="GET" class="max-w-md mx-auto">
                <div class="flex gap-2">
                    <input type="text" 
                           name="search" 
                           placeholder="Cari UMKM, produk, atau layanan..." 
                           class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#009b97] focus:border-[#009b97] transition-all">
                    <button type="submit" 
                            class="bg-[#009b97] hover:bg-[#007a77] text-white px-6 py-3 rounded-lg font-semibold transition-all duration-300 shadow-lg hover:shadow-xl">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection


