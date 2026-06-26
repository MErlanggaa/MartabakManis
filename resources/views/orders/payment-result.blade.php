@extends('layouts.app')

@section('title', 'Hasil Pembayaran')

@section('content')
<div class="max-w-lg mx-auto px-4 py-12">
    <div class="rounded-2xl bg-white p-8 text-center shadow-lg">
        @if($status === 'success')
            <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-green-100 text-4xl">✓</div>
            <h1 class="text-2xl font-bold text-gray-900">Pembayaran Berhasil</h1>
            <p class="mt-2 text-gray-600">Terima kasih! Pesanan Anda sedang diproses penjual.</p>
        @elseif($status === 'pending')
            <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-yellow-100 text-4xl">⏳</div>
            <h1 class="text-2xl font-bold text-gray-900">Menunggu Pembayaran</h1>
            <p class="mt-2 text-gray-600">Selesaikan pembayaran Anda sesuai instruksi.</p>
        @else
            <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-red-100 text-4xl">✕</div>
            <h1 class="text-2xl font-bold text-gray-900">Pembayaran Gagal</h1>
            <p class="mt-2 text-gray-600">Pembayaran tidak berhasil. Silakan coba lagi.</p>
        @endif

        @if($orderCode)
            <p class="mt-4 text-sm text-gray-500">Kode pesanan: <strong>{{ $orderCode }}</strong></p>
        @endif

        <a href="{{ route('public.katalog') }}" class="mt-6 inline-block rounded-xl bg-[#009b97] px-6 py-3 font-bold text-white hover:bg-[#007a77]">
            Kembali ke Katalog
        </a>
    </div>
</div>
@endsection
