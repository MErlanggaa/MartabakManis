@extends('layouts.app')

@section('title', 'Tracking Transaksi - Admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl flex items-center justify-center text-white">
                        <i class="fas fa-history text-xl"></i>
                    </div>
                    Tracking Transaksi Online
                </h1>
                <p class="text-gray-600 mt-2">Pantau dan awasi seluruh transaksi jual beli real-time antara User dan UMKM</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.dashboard') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition-colors inline-flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Stats summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-6 flex items-center justify-between border-l-4 border-amber-500">
            <div>
                <p class="text-gray-500 text-sm font-semibold uppercase">Total Transaksi</p>
                <h3 class="text-3xl font-black mt-1 text-gray-900">{{ number_format($totalOrdersCount) }}</h3>
            </div>
            <div class="w-12 h-12 bg-amber-50 text-amber-500 rounded-xl flex items-center justify-center text-xl">
                <i class="fas fa-shopping-cart"></i>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 flex items-center justify-between border-l-4 border-green-500">
            <div>
                <p class="text-gray-500 text-sm font-semibold uppercase">Transaksi Lunas</p>
                <h3 class="text-3xl font-black mt-1 text-gray-900">{{ number_format($paidOrdersCount) }}</h3>
            </div>
            <div class="w-12 h-12 bg-green-50 text-green-500 rounded-xl flex items-center justify-center text-xl">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 flex items-center justify-between border-l-4 border-blue-500">
            <div>
                <p class="text-gray-500 text-sm font-semibold uppercase">Total Perputaran Uang</p>
                <h3 class="text-3xl font-black mt-1 text-blue-600">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h3>
            </div>
            <div class="w-12 h-12 bg-blue-50 text-blue-500 rounded-xl flex items-center justify-center text-xl">
                <i class="fas fa-wallet"></i>
            </div>
        </div>
    </div>

    <!-- Filtering & Search -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <form method="GET" action="{{ route('admin.transactions') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Cari Kode / Customer / UMKM</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari..." class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:border-amber-500 focus:outline-none" />
                    <span class="absolute left-3 top-2.5 text-gray-400 text-sm">
                        <i class="fas fa-search"></i>
                    </span>
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Status Bayar</label>
                <select name="payment_status" class="w-full border border-gray-200 rounded-lg py-2 px-3 text-sm focus:border-amber-500 focus:outline-none">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Lunas</option>
                    <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>Gagal / Expired</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Status Order</label>
                <select name="status" class="w-full border border-gray-200 rounded-lg py-2 px-3 text-sm focus:border-amber-500 focus:outline-none">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu Konfirmasi</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Dikonfirmasi</option>
                    <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Diproses</option>
                    <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Selesai</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white font-bold px-6 py-2 rounded-lg text-sm transition-colors flex-1">
                    Filter
                </button>
                <a href="{{ route('admin.transactions') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table List -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            @if($orders->count() > 0)
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Kode / Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">UMKM / Menu</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Subtotal</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Pajak / Ongkir</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Pembayaran</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Status Order</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($orders as $o)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-mono text-sm font-bold text-gray-900">{{ $o->order_code }}</div>
                                    <div class="text-xs text-gray-500 mt-1">{{ $o->created_at->format('d M Y H:i') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900">{{ $o->customer_name }}</div>
                                    <div class="text-xs text-gray-500 mt-0.5">{{ $o->customer_phone }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-gray-900">{{ $o->umkm?->nama ?? 'Tidak Ada UMKM' }}</div>
                                    <div class="text-xs text-gray-600 mt-0.5">{{ $o->layanan?->nama }} (x{{ $o->quantity }})</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right font-semibold text-sm text-gray-900">
                                    Rp {{ number_format($o->subtotal, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-xs text-gray-500">
                                    <div>Platform: Rp {{ number_format($o->app_tax + $o->qris_tax, 0, ',', '.') }}</div>
                                    <div class="mt-0.5">Ongkir: Rp {{ number_format($o->delivery_fee, 0, ',', '.') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right font-black text-sm text-amber-600">
                                    Rp {{ number_format($o->total, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-black uppercase tracking-wider {{
                                        $o->payment_status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                                    }}">
                                        {{ $o->payment_status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold {{
                                        $o->order_status === 'delivered' ? 'bg-green-100 text-green-800' : (
                                            $o->order_status === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'
                                        )
                                    }}">
                                        {{ $o->order_status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                    <button type="button" onclick="showTransactionDetail({{ $o->id }})" class="bg-slate-100 hover:bg-[#009b97] hover:text-white text-slate-700 px-3 py-1.5 rounded-lg text-xs font-bold transition">
                                        <i class="fas fa-eye mr-1"></i> Detail
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="text-center py-12 bg-gray-50">
                    <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-receipt text-gray-400 text-2xl"></i>
                    </div>
                    <p class="text-gray-500 font-bold">Belum ada data transaksi yang sesuai filter</p>
                </div>
            @endif
        </div>
        @if($orders->hasPages())
            <div class="p-6 border-t border-gray-100">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Transaction Detail Modal -->
<div id="detailModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-black bg-opacity-60 transition-opacity" onclick="closeDetailModal()"></div>

        <!-- Modal Center Hook -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <div class="bg-gradient-to-r from-slate-900 to-slate-800 px-6 py-4 flex items-center justify-between text-white">
                <div>
                    <h3 class="text-lg font-bold" id="modal-title">Detail Transaksi</h3>
                    <p class="text-xs text-slate-300 font-mono mt-0.5" id="detailOrderCode">ORD-XXXXXXXX</p>
                </div>
                <button type="button" onclick="closeDetailModal()" class="text-slate-400 hover:text-white transition">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <div class="bg-white px-6 py-6 space-y-6">
                <!-- Grid Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Customer Card -->
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 space-y-1">
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Data Customer</span>
                        <h4 class="font-bold text-slate-800" id="detailCustomerName">-</h4>
                        <p class="text-xs text-slate-600" id="detailCustomerPhone">-</p>
                        <p class="text-xs text-slate-500 mt-1" id="detailCustomerAddress">-</p>
                        <p class="text-xs text-slate-400 mt-1 italic" id="detailCustomerEmail">Email: -</p>
                    </div>

                    <!-- UMKM & Menu Card -->
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 space-y-1">
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Data Merchant & Menu</span>
                        <h4 class="font-bold text-slate-800" id="detailUmkmName">-</h4>
                        <p class="text-xs text-slate-500" id="detailUmkmOwner">Pemilik: -</p>
                        <div class="border-t border-slate-200/60 my-2 pt-1">
                            <p class="text-xs font-semibold text-slate-700" id="detailMenuName">-</p>
                            <p class="text-xs text-slate-500 mt-0.5" id="detailMenuQty">-</p>
                        </div>
                    </div>
                </div>

                <!-- Financial Table -->
                <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 space-y-2">
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block border-b pb-1.5 mb-2">Rincian Finansial</span>
                    <div class="flex justify-between text-xs text-slate-600">
                        <span>Harga Produk (Subtotal)</span>
                        <span id="detailSubtotal">Rp 0</span>
                    </div>
                    <div class="flex justify-between text-xs text-slate-600">
                        <span>Pajak Aplikasi (2%)</span>
                        <span id="detailAppTax">Rp 0</span>
                    </div>
                    <div class="flex justify-between text-xs text-slate-600">
                        <span>Biaya Penanganan QRIS</span>
                        <span id="detailQrisTax">Rp 0</span>
                    </div>
                    <div class="flex justify-between text-xs text-slate-600">
                        <span>Biaya Pengiriman (Ongkir)</span>
                        <span id="detailDeliveryFee">Rp 0</span>
                    </div>
                    <div class="flex justify-between text-xs text-slate-600" id="uniqueCodeRow">
                        <span>Kode Unik QRIS</span>
                        <span id="detailUniqueCode" class="text-emerald-600 font-semibold">Rp 0</span>
                    </div>
                    <div class="flex justify-between font-bold text-sm text-slate-800 border-t pt-2 mt-1">
                        <span>Total Keseluruhan</span>
                        <span id="detailTotal" class="text-[#009b97]">Rp 0</span>
                    </div>
                </div>

                <!-- Tracking & Status -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Metode Pembayaran</span>
                            <span class="text-sm font-bold text-slate-700 capitalize mt-1 inline-block" id="detailPaymentMethod">-</span>
                        </div>
                        <span id="detailPaymentStatusBadge" class="px-2.5 py-0.5 rounded-full text-xs font-black uppercase">-</span>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Status Pesanan</span>
                            <span class="text-sm font-bold text-slate-700 capitalize mt-1 inline-block" id="detailOrderStatus">-</span>
                        </div>
                        <span id="detailOrderStatusBadge" class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase">-</span>
                    </div>
                </div>

                <!-- Refund Banner (If refunded) -->
                <div id="refundedBanner" class="bg-red-50 border border-red-200 rounded-xl p-4 text-left hidden">
                    <div class="flex items-start gap-3">
                        <div class="text-red-500 mt-0.5">
                            <i class="fas fa-undo-alt text-lg"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-red-800 text-sm">Transaksi Telah Direfund</h4>
                            <p class="text-xs text-red-600 mt-1">
                                Pembayaran transaksi ini telah ditarik kembali/dikurangi dari saldo UMKM. Status order diubah menjadi dibatalkan.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div id="detailNotesContainer" class="bg-slate-50 p-4 rounded-xl border border-slate-100 text-xs text-slate-500 italic hidden">
                    Catatan Order: <span id="detailNotes">-</span>
                </div>
            </div>

            <div class="bg-slate-50 px-6 py-4 flex flex-col md:flex-row md:justify-between items-center gap-3 border-t">
                <div>
                    <span class="text-xs text-slate-500 font-medium" id="detailDate">Tanggal: -</span>
                </div>
                <div class="flex gap-2 w-full md:w-auto">
                    <!-- Refund Button -->
                    <button type="button" id="refundBtn" onclick="processRefund()" class="bg-red-600 hover:bg-red-700 text-white font-bold px-4 py-2 rounded-lg text-xs transition inline-flex items-center gap-1.5 justify-center flex-1 md:flex-none">
                        <i class="fas fa-undo-alt"></i> Refund / Retur Dana UMKM
                    </button>
                    <button type="button" onclick="closeDetailModal()" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold px-4 py-2 rounded-lg text-xs transition justify-center flex-1 md:flex-none">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let currentDetailOrderId = null;

    function showTransactionDetail(orderId) {
        currentDetailOrderId = orderId;
        
        // Clear previous content
        document.getElementById('detailOrderCode').innerText = 'Loading...';
        document.getElementById('detailCustomerName').innerText = '-';
        document.getElementById('detailCustomerPhone').innerText = '-';
        document.getElementById('detailCustomerAddress').innerText = '-';
        document.getElementById('detailCustomerEmail').innerText = '-';
        document.getElementById('detailUmkmName').innerText = '-';
        document.getElementById('detailUmkmOwner').innerText = '-';
        document.getElementById('detailMenuName').innerText = '-';
        document.getElementById('detailMenuQty').innerText = '-';
        document.getElementById('detailSubtotal').innerText = 'Rp 0';
        document.getElementById('detailAppTax').innerText = 'Rp 0';
        document.getElementById('detailQrisTax').innerText = 'Rp 0';
        document.getElementById('detailDeliveryFee').innerText = 'Rp 0';
        document.getElementById('detailUniqueCode').innerText = 'Rp 0';
        document.getElementById('detailTotal').innerText = 'Rp 0';
        document.getElementById('detailPaymentMethod').innerText = '-';
        document.getElementById('detailOrderStatus').innerText = '-';
        document.getElementById('detailDate').innerText = 'Tanggal: -';
        
        // Hide containers
        document.getElementById('refundedBanner').classList.add('hidden');
        document.getElementById('detailNotesContainer').classList.add('hidden');
        document.getElementById('refundBtn').classList.add('hidden');

        // Open modal
        document.getElementById('detailModal').classList.remove('hidden');

        // Fetch details
        fetch(`/admin/transactions/${orderId}/detail`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const o = data.order;
                    document.getElementById('detailOrderCode').innerText = o.order_code;
                    document.getElementById('detailCustomerName').innerText = o.customer_name;
                    document.getElementById('detailCustomerPhone').innerText = o.customer_phone;
                    document.getElementById('detailCustomerAddress').innerText = o.customer_address;
                    document.getElementById('detailCustomerEmail').innerText = 'Email: ' + o.user_email;
                    document.getElementById('detailUmkmName').innerText = o.umkm_name;
                    document.getElementById('detailUmkmOwner').innerText = 'Pemilik: ' + o.umkm_owner;
                    document.getElementById('detailMenuName').innerText = o.layanan_name + ' (Rp ' + formatIDR(o.layanan_price) + ')';
                    document.getElementById('detailMenuQty').innerText = 'Jumlah: ' + o.quantity + ' Porsi';
                    
                    document.getElementById('detailSubtotal').innerText = 'Rp ' + formatIDR(o.subtotal);
                    document.getElementById('detailAppTax').innerText = 'Rp ' + formatIDR(o.app_tax);
                    document.getElementById('detailQrisTax').innerText = 'Rp ' + formatIDR(o.qris_tax);
                    document.getElementById('detailDeliveryFee').innerText = 'Rp ' + formatIDR(o.delivery_fee);
                    
                    if (o.unique_code > 0) {
                        document.getElementById('uniqueCodeRow').classList.remove('hidden');
                        document.getElementById('detailUniqueCode').innerText = 'Rp ' + formatIDR(o.unique_code);
                    } else {
                        document.getElementById('uniqueCodeRow').classList.add('hidden');
                    }
                    
                    document.getElementById('detailTotal').innerText = 'Rp ' + formatIDR(o.total);
                    document.getElementById('detailPaymentMethod').innerText = o.payment_method;
                    document.getElementById('detailOrderStatus').innerText = o.order_status;
                    document.getElementById('detailDate').innerText = 'Tanggal Pesanan: ' + o.created_at;

                    // Notes
                    if (o.notes) {
                        document.getElementById('detailNotes').innerText = o.notes;
                        document.getElementById('detailNotesContainer').classList.remove('hidden');
                    }

                    // Payment Badge
                    const pBadge = document.getElementById('detailPaymentStatusBadge');
                    pBadge.innerText = o.payment_status;
                    if (o.payment_status === 'paid') {
                        pBadge.className = 'px-2.5 py-0.5 rounded-full text-xs font-black uppercase bg-green-100 text-green-800';
                    } else {
                        pBadge.className = 'px-2.5 py-0.5 rounded-full text-xs font-black uppercase bg-yellow-100 text-yellow-800';
                    }

                    // Order Status Badge
                    const oBadge = document.getElementById('detailOrderStatusBadge');
                    oBadge.innerText = o.order_status;
                    if (o.order_status === 'delivered') {
                        oBadge.className = 'px-2.5 py-0.5 rounded-full text-xs font-bold uppercase bg-green-100 text-green-800';
                    } else if (o.order_status === 'cancelled') {
                        oBadge.className = 'px-2.5 py-0.5 rounded-full text-xs font-bold uppercase bg-red-100 text-red-800';
                    } else {
                        oBadge.className = 'px-2.5 py-0.5 rounded-full text-xs font-bold uppercase bg-blue-100 text-blue-800';
                    }

                    // Refund handling
                    if (o.is_refunded) {
                        document.getElementById('refundedBanner').classList.remove('hidden');
                    } else if (o.payment_status === 'paid' && o.order_status !== 'cancelled') {
                        // show refund button if lunas and not cancelled/refunded
                        document.getElementById('refundBtn').classList.remove('hidden');
                    }
                }
            })
            .catch(err => {
                alert('Gagal mengambil detail transaksi.');
                closeDetailModal();
            });
    }

    function closeDetailModal() {
        document.getElementById('detailModal').classList.add('hidden');
        currentDetailOrderId = null;
    }

    function processRefund() {
        if (!currentDetailOrderId) return;
        if (!confirm('Apakah Anda yakin ingin melakukan refund/retur dana untuk transaksi ini? Uang UMKM (sebesar harga subtotal produk) akan dipotong, saldo mereka bisa menjadi negatif, dan status order akan diubah menjadi dibatalkan.')) return;

        const btn = document.getElementById('refundBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...';

        fetch(`/admin/transactions/${currentDetailOrderId}/refund`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message || 'Gagal memproses refund.');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-undo-alt mr-1"></i> Refund / Retur Dana UMKM';
            }
        })
        .catch(err => {
            alert('Terjadi kesalahan saat memproses refund.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-undo-alt mr-1"></i> Refund / Retur Dana UMKM';
        });
    }

    function formatIDR(num) {
        return new Intl.NumberFormat('id-ID').format(num);
    }
</script>
@endsection
