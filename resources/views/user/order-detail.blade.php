@extends('layouts.app')

@section('title', 'Detail Pesanan #' . $order->order_code)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div id="user-order-detail-root" data-order-id="{{ $order->id }}">
        <!-- Rendered by React -->
        <div class="flex items-center justify-center py-20">
            <div class="h-10 w-10 animate-spin rounded-full border-4 border-[#009b97] border-t-transparent"></div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@vite(['resources/js/order-detail.jsx'])
@endsection
