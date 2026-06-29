@extends('layouts.app')

@section('title', 'History Laporan Saya')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-[#009b97] to-[#039b00] rounded-xl flex items-center justify-center text-white">
                        <i class="fas fa-history text-xl"></i>
                    </div>
                    History Laporan Saya
                </h1>
                <p class="text-gray-600 mt-2">Lihat semua laporan yang telah Anda kirim atau mediasi komplain dari pelanggan</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('public.laporan') }}" class="bg-[#009b97] hover:bg-[#007a77] text-white px-4 py-2 rounded-lg transition-colors inline-flex items-center gap-2">
                    <i class="fas fa-plus"></i> Buat Laporan Baru
                </a>
                <a href="{{ route('umkm.dashboard') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition-colors inline-flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Tabs Menu -->
    <div class="flex border-b border-gray-200 mb-6 bg-white rounded-xl shadow-sm overflow-hidden p-1.5 gap-2">
        <button onclick="switchTab('my-reports')" id="tabBtn-my-reports" class="flex-1 py-3 text-sm font-bold rounded-lg transition-all text-center bg-[#009b97] text-white shadow-sm">
            <i class="fas fa-paper-plane mr-1.5"></i> Laporan Saya
        </button>
        <button onclick="switchTab('complaints')" id="tabBtn-complaints" class="flex-1 py-3 text-sm font-bold rounded-lg transition-all text-center text-gray-500 hover:bg-gray-50 hover:text-gray-700 relative">
            <i class="fas fa-bullhorn mr-1.5 text-amber-500"></i> Komplain Pelanggan
            @php
                $totalUnreadComplaints = $customerComplaints ? $customerComplaints->sum('unread_by_umkm') : 0;
            @endphp
            @if($totalUnreadComplaints > 0)
                <span class="absolute top-2 right-4 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-[10px] font-black text-white shadow-md animate-pulse">
                    {{ $totalUnreadComplaints }}
                </span>
            @endif
        </button>
    </div>

    <!-- Tab 1: Laporan Saya -->
    <div id="tab-my-reports" class="space-y-4">
        @if($reports->count() > 0)
            @foreach($reports as $report)
                <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $report->judul }}</h3>
                                    <div class="flex flex-wrap items-center gap-3 text-sm text-gray-600">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($report->kategori == 'bug') bg-red-100 text-red-800
                                            @elseif($report->kategori == 'fitur') bg-blue-100 text-blue-800
                                            @elseif($report->kategori == 'pertanyaan') bg-purple-100 text-purple-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ $report->kategori_label }}
                                        </span>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($report->status == 'pending') bg-yellow-100 text-yellow-800
                                            @elseif($report->status == 'diproses') bg-blue-100 text-blue-800
                                            @else bg-green-100 text-green-800
                                            @endif">
                                            <i class="fas fa-circle text-xs mr-1"></i>
                                            {{ $report->status_label }}
                                        </span>
                                        <span class="text-gray-500">
                                            <i class="far fa-clock mr-1"></i>
                                            {{ $report->created_at->format('d M Y, H:i') }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <p class="text-gray-700 leading-relaxed">{{ Str::limit($report->deskripsi, 200) }}</p>
                            </div>

                            <div class="flex gap-2 items-center mb-4">
                                <a href="{{ route('laporan.discussion', $report->id) }}" class="relative bg-[#009b97]/10 hover:bg-[#009b97]/20 text-[#009b97] border border-[#009b97]/20 px-4 py-2 rounded-xl text-xs font-bold transition-all inline-flex items-center gap-1.5 shadow-sm">
                                    <i class="fas fa-comments"></i> Detail & Diskusi Mediasi
                                    @if($report->unread_by_user > 0)
                                        <span class="absolute -top-1.5 -right-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-black text-white shadow-md">
                                            {{ $report->unread_by_user }}
                                        </span>
                                    @endif
                                </a>
                            </div>

                            @if($report->respon_admin)
                                <div class="bg-blue-50 border-l-4 border-[#009b97] p-4 rounded-lg">
                                    <div class="flex items-start gap-3">
                                        <i class="fas fa-comment-dots text-[#009b97] mt-1"></i>
                                        <div class="flex-1">
                                            <h4 class="font-semibold text-gray-900 mb-1">Respon Admin</h4>
                                            <p class="text-gray-700 whitespace-pre-wrap">{{ $report->respon_admin }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
            <div class="mt-6">{{ $reports->links() }}</div>
        @else
            <div class="bg-white rounded-xl shadow-sm p-12 text-center">
                <i class="fas fa-inbox text-gray-300 text-6xl mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Belum Ada Laporan</h3>
                <p class="text-gray-600">Anda belum pernah mengirim laporan sebagai pengguna.</p>
            </div>
        @endif
    </div>

    <!-- Tab 2: Komplain Pelanggan -->
    <div id="tab-complaints" class="hidden space-y-4">
        @if($customerComplaints && $customerComplaints->count() > 0)
            @foreach($customerComplaints as $report)
                <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow border-l-4 border-amber-400">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <span class="text-xs font-black text-amber-700 bg-amber-50 border border-amber-200/60 px-2.5 py-1 rounded-lg">KOMPLAIN PELANGGAN</span>
                                    <h3 class="text-lg font-semibold text-gray-900 mt-2 mb-2">{{ $report->judul }}</h3>
                                    
                                    <div class="flex flex-wrap items-center gap-3 text-sm text-gray-600">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Barang Rusak / Toko Curang
                                        </span>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($report->status == 'pending') bg-yellow-100 text-yellow-800
                                            @elseif($report->status == 'diproses') bg-blue-100 text-blue-800
                                            @else bg-green-100 text-green-800
                                            @endif">
                                            <i class="fas fa-circle text-xs mr-1"></i>
                                            {{ $report->status_label }}
                                        </span>
                                        <span class="text-gray-500">
                                            <i class="far fa-clock mr-1"></i>
                                            {{ $report->created_at->format('d M Y, H:i') }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Order Link & Detail -->
                            @if($report->order)
                                <div class="bg-slate-50 border border-gray-100 rounded-xl p-4 mb-4 text-sm">
                                    <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                                        <span class="font-bold text-gray-700">Order: #{{ $report->order->order_code }}</span>
                                        <span class="font-bold text-[#009b97]">Subtotal: Rp {{ number_format($report->order->subtotal, 0, ',', '.') }}</span>
                                    </div>
                                    <p class="text-gray-500 text-xs">Pelapor/Konsumen: <strong>{{ $report->nama }}</strong></p>
                                </div>
                            @endif

                            <div class="mb-4">
                                <span class="text-xs text-gray-400 font-bold uppercase tracking-wider block mb-1">Aduan Pelanggan</span>
                                <p class="text-gray-700 leading-relaxed bg-gray-50/50 p-3 rounded-lg border border-gray-100">{{ $report->deskripsi }}</p>
                            </div>

                            <div class="flex gap-2 items-center mb-4">
                                <a href="{{ route('laporan.discussion', $report->id) }}" class="relative bg-amber-500 hover:bg-amber-600 text-white px-5 py-2.5 rounded-xl text-xs font-bold transition-all inline-flex items-center gap-1.5 shadow-md">
                                    <i class="fas fa-reply-all"></i> Tanggapi & Aju Banding (Mediasi)
                                    @if($report->unread_by_umkm > 0)
                                        <span class="absolute -top-1.5 -right-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-red-600 text-[10px] font-black text-white shadow-md animate-bounce">
                                            {{ $report->unread_by_umkm }}
                                        </span>
                                    @endif
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
            <div class="mt-6">{{ $customerComplaints->links() }}</div>
        @else
            <div class="bg-white rounded-xl shadow-sm p-12 text-center border-l-4 border-amber-300">
                <i class="fas fa-check-circle text-green-500 text-6xl mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Toko Anda Bersih!</h3>
                <p class="text-gray-600">Belum ada komplain atau aduan kecurangan dari pelanggan terhadap toko Anda.</p>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    function switchTab(tabId) {
        const myReportsTab = document.getElementById('tab-my-reports');
        const complaintsTab = document.getElementById('tab-complaints');
        const btnMyReports = document.getElementById('tabBtn-my-reports');
        const btnComplaints = document.getElementById('tabBtn-complaints');

        if (tabId === 'my-reports') {
            myReportsTab.classList.remove('hidden');
            complaintsTab.classList.add('hidden');
            
            btnMyReports.className = "flex-1 py-3 text-sm font-bold rounded-lg transition-all text-center bg-[#009b97] text-white shadow-sm";
            btnComplaints.className = "flex-1 py-3 text-sm font-bold rounded-lg transition-all text-center text-gray-500 hover:bg-gray-50 hover:text-gray-700 relative";
        } else {
            myReportsTab.classList.add('hidden');
            complaintsTab.classList.remove('hidden');
            
            btnMyReports.className = "flex-1 py-3 text-sm font-bold rounded-lg transition-all text-center text-gray-500 hover:bg-gray-50 hover:text-gray-700";
            btnComplaints.className = "flex-1 py-3 text-sm font-bold rounded-lg transition-all text-center bg-[#009b97] text-white shadow-sm relative";
        }
    }

    // Auto switch to complaints if query params contain complaints_page
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('complaints_page')) {
        switchTab('complaints');
    }
</script>
@endsection
