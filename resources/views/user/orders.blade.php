@extends('layouts.app')

@section('title', 'Pesanan Saya')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div id="user-orders-root">
            <!-- Rendered by React -->
            <div class="flex items-center justify-center py-20">
                <div class="h-10 w-10 animate-spin rounded-full border-4 border-[#009b97] border-t-transparent"></div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @vite(['resources/js/orders.jsx'])
@endsection