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
                <p class="text-gray-600 mt-2">Lihat semua laporan yang telah Anda kirim dan statusnya</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('public.laporan') }}" class="bg-[#009b97] hover:bg-[#007a77] text-white px-4 py-2 rounded-lg transition-colors inline-flex items-center gap-2">
                    <i class="fas fa-plus"></i> Buat Laporan Baru
                </a>
                <a href="{{ route('user.katalog') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition-colors inline-flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Laporan List -->
    @if($reports->count() > 0)
        <div class="space-y-4">
            @foreach($reports as $report)
                <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                        <div class="flex-1">
                            <!-- Header -->
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

                            <!-- Deskripsi -->
                            <div class="mb-4">
                                <p class="text-gray-700 leading-relaxed">{{ Str::limit($report->deskripsi, 200) }}</p>
                            </div>

                            <!-- Respon Admin (jika ada) -->
                            @if($report->respon_admin)
                                <div class="bg-blue-50 border-l-4 border-[#009b97] p-4 rounded-lg">
                                    <div class="flex items-start gap-3">
                                        <i class="fas fa-comment-dots text-[#009b97] mt-1"></i>
                                        <div class="flex-1">
                                            <h4 class="font-semibold text-gray-900 mb-1">Respon Admin</h4>
                                            <p class="text-gray-700 whitespace-pre-wrap">{{ $report->respon_admin }}</p>
                                            @if($report->admin)
                                                <p class="text-xs text-gray-500 mt-2">
                                                    Oleh: {{ $report->admin->name }} • 
                                                    {{ $report->updated_at->format('d M Y, H:i') }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="bg-gray-50 border-l-4 border-gray-300 p-4 rounded-lg">
                                    <div class="flex items-center gap-2 text-gray-600">
                                        <i class="fas fa-hourglass-half"></i>
                                        <span class="text-sm">Belum ada respon dari admin</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $reports->links() }}
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm p-12 text-center">
            <i class="fas fa-inbox text-gray-300 text-6xl mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Belum Ada Laporan</h3>
            <p class="text-gray-600 mb-6">Anda belum pernah mengirim laporan. Mulai kirim laporan pertama Anda!</p>
            <a href="{{ route('public.laporan') }}" class="inline-flex items-center gap-2 bg-[#009b97] hover:bg-[#007a77] text-white px-6 py-3 rounded-lg font-semibold transition-colors">
                <i class="fas fa-plus"></i> Buat Laporan Baru
            </a>
        </div>
    @endif
</div>
@endsection


