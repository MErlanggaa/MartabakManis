@extends('layouts.app')

@section('title', 'Selamat Datang di UMKM.go')

@section('content')
@php
    $platformStats = [
        ['key' => 'umkm', 'label' => 'UMKM Terdaftar', 'value' => number_format(\App\Models\UMKM::count())],
        ['key' => 'products', 'label' => 'Produk & Layanan', 'value' => number_format(\App\Models\Layanan::count())],
        ['key' => 'users', 'label' => 'Pengguna Aktif', 'value' => number_format(\App\Models\User::where('role', 'user')->count())],
        ['key' => 'orders', 'label' => 'Pesanan Selesai', 'value' => number_format(\App\Models\Order::where('payment_status', 'paid')->count())],
    ];
@endphp

<!-- Hero Section -->
<div id="landing-hero-root"
     data-katalog-url="{{ route('public.katalog') }}"
     data-login-url="{{ route('login') }}"></div>

<!-- Stats Section -->
<div id="landing-stats-root" data-stats='@json($platformStats)'></div>

<!-- Features Section -->
<div id="landing-features-root" class="mt-8"></div>
@endsection

@section('scripts')
@vite(['resources/js/landing.jsx'])
@endsection
