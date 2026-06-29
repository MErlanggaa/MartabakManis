@extends('layouts.app')

@section('title', 'Mediasi & Diskusi Laporan')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    <!-- Breadcrumb -->
    <nav class="mb-5 flex text-sm text-gray-500 gap-2 items-center">
        <a href="/" class="hover:text-gray-700">Home</a>
        <span>/</span>
        @if(auth()->user()->role === 'admin')
            <a href="{{ route('admin.laporan') }}" class="hover:text-gray-700">Manajemen Laporan</a>
        @else
            <a href="{{ route(auth()->user()->role . '.history.laporan') }}" class="hover:text-gray-700">History Laporan</a>
        @endif
        <span>/</span>
        <span class="text-gray-800 font-semibold">Diskusi Laporan #{{ $report->id }}</span>
    </nav>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- ═══════════════════════════════════════════════ -->
        <!-- Left Column: Report Info + Admin Action Panel -->
        <!-- ═══════════════════════════════════════════════ -->
        <div class="space-y-5 lg:col-span-1">

            <!-- Informasi Komplain Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <h2 class="font-bold text-gray-900 text-base">Informasi Komplain</h2>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold
                        @if($report->status == 'pending') bg-yellow-100 text-yellow-800
                        @elseif($report->status == 'diproses') bg-blue-100 text-blue-800
                        @else bg-green-100 text-green-800
                        @endif">
                        {{ $report->status_label }}
                    </span>
                </div>

                <div class="space-y-1">
                    <span class="text-xs text-gray-400 font-bold uppercase tracking-wider">Judul Aduan</span>
                    <p class="font-semibold text-gray-800">{{ $report->judul }}</p>
                </div>

                <div class="space-y-1">
                    <span class="text-xs text-gray-400 font-bold uppercase tracking-wider">Kategori</span><br>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                        {{ $report->kategori_label }}
                    </span>
                </div>

                <div class="space-y-1">
                    <span class="text-xs text-gray-400 font-bold uppercase tracking-wider">Pelapor</span>
                    <p class="text-sm font-medium text-gray-800">{{ $report->nama }}</p>
                    <p class="text-xs text-gray-500">{{ $report->email }}</p>
                </div>

                <div class="space-y-1">
                    <span class="text-xs text-gray-400 font-bold uppercase tracking-wider">Deskripsi</span>
                    <div class="text-sm text-gray-600 bg-gray-50/70 rounded-xl p-3 border border-gray-100 leading-relaxed whitespace-pre-wrap">{{ $report->deskripsi }}</div>
                </div>

                <div class="text-xs text-gray-400 pt-1 flex items-center justify-between">
                    <span>{{ $report->created_at->format('d M Y H:i') }}</span>
                    <span>ID: #{{ $report->id }}</span>
                </div>
            </div>

            <!-- Order / Transaction Info -->
            @if($report->order_id && $report->order)
                <div class="bg-amber-50/60 rounded-2xl shadow-sm border border-amber-200/60 p-5 space-y-3">
                    <h3 class="font-bold text-amber-900 text-sm flex items-center gap-2">
                        <i class="fas fa-shopping-bag text-amber-700"></i> Transaksi Terkait
                    </h3>
                    <div class="text-sm space-y-2 text-gray-700">
                        <div class="flex justify-between">
                            <span class="text-amber-800 font-semibold">Kode:</span>
                            <span class="font-mono font-bold text-xs">{{ $report->order->order_code }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Toko:</span>
                            <span class="font-semibold text-gray-800">{{ $report->order->umkm->nama }}</span>
                        </div>
                        <div class="flex justify-between pt-1 border-t border-amber-200/40">
                            <span class="font-semibold text-gray-800">Total:</span>
                            <span class="font-extrabold text-amber-800">Rp {{ number_format($report->order->total, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Admin Action Panel -->
            @if(auth()->user()->role === 'admin' && $report->status !== 'selesai')
                <div class="bg-purple-50/60 border border-purple-200/70 rounded-2xl shadow-sm p-5 space-y-4">
                    <h3 class="font-bold text-purple-900 text-sm flex items-center gap-2">
                        <i class="fas fa-gavel text-purple-700"></i> Panel Mediasi Admin
                    </h3>
                    <p class="text-xs text-purple-700 leading-relaxed">
                        Sebagai mediator, Anda dapat mengakhiri mediasi dengan Refund atau menandai selesai tanpa denda.
                    </p>

                    @if($report->order_id && $report->order)
                        <form id="adminRefundForm" onsubmit="processAdminRefund(event)" class="space-y-3 pt-1">
                            @csrf
                            <div>
                                <label class="block text-xs font-bold text-purple-800 uppercase mb-1">Opsi Penyelesaian</label>
                                <select name="refund_type" id="refundTypeSelect" onchange="toggleCustomAmount()" class="w-full text-sm px-3 py-2 border border-purple-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500">
                                    <option value="full">Refund Penuh 100% (Rp {{ number_format($report->order->subtotal, 0, ',', '.') }})</option>
                                    <option value="half">Refund 50% (Rp {{ number_format($report->order->subtotal * 0.5, 0, ',', '.') }})</option>
                                    <option value="custom">Refund Custom (Input Nominal)</option>
                                </select>
                            </div>
                            <div id="customAmountGroup" class="hidden">
                                <label class="block text-xs font-bold text-purple-800 uppercase mb-1">Nominal Custom (Rp)</label>
                                <input type="number" name="custom_amount" id="customAmountInput" placeholder="Contoh: 10000" class="w-full text-sm px-3 py-2 border border-purple-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500" />
                            </div>
                            <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold text-sm py-2.5 rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
                                <i class="fas fa-hand-holding-usd"></i> Proses Keputusan & Selesaikan
                            </button>
                        </form>
                    @else
                        <form onsubmit="processCloseWithoutRefund(event)" class="pt-1">
                            @csrf
                            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold text-sm py-2.5 rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
                                <i class="fas fa-check-circle"></i> Tandai Selesai
                            </button>
                        </form>
                    @endif
                </div>
            @endif

        </div>

        <!-- ═══════════════════════════════════════════════ -->
        <!-- Right Column: TWO SEPARATE MEDIATION ROOMS    -->
        <!-- ═══════════════════════════════════════════════ -->
        <div class="lg:col-span-2 flex flex-col">

            <!-- ── Tab Navigation ──────────────────────── -->
            @php
                // Tentukan tab default: pembeli lihat ruangnya, UMKM lihat ruangnya, admin lihat ruang user dulu
                $defaultTab = $isCreator ? 'user' : ($isUmkm ? 'umkm' : 'user');
            @endphp

            <div class="flex rounded-t-2xl overflow-hidden border border-b-0 border-gray-200 shadow-sm">
                {{-- Tab: Ruang Pembeli --}}
                @if($isAdmin || $isCreator)
                <button
                    id="tab-user"
                    onclick="switchRoom('user')"
                    class="room-tab flex-1 flex items-center justify-center gap-2 py-3.5 text-sm font-bold transition-all border-r border-gray-200
                        {{ $defaultTab === 'user' ? 'bg-[#009b97] text-white' : 'bg-gray-50 text-gray-500 hover:bg-gray-100' }}">
                    <i class="fas fa-user text-xs"></i>
                    <span>Ruang Pembeli</span>
                    @if($isAdmin && $userRoomMessages->count() > 0)
                        <span class="bg-white/30 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full leading-none">
                            {{ $userRoomMessages->count() }}
                        </span>
                    @endif
                </button>
                @endif

                {{-- Tab: Ruang UMKM --}}
                @if($isAdmin || $isUmkm)
                <button
                    id="tab-umkm"
                    onclick="switchRoom('umkm')"
                    class="room-tab flex-1 flex items-center justify-center gap-2 py-3.5 text-sm font-bold transition-all
                        {{ $defaultTab === 'umkm' ? 'bg-emerald-600 text-white' : 'bg-gray-50 text-gray-500 hover:bg-gray-100' }}">
                    <i class="fas fa-store text-xs"></i>
                    <span>Ruang UMKM</span>
                    @if($isAdmin && $umkmRoomMessages->count() > 0)
                        <span class="bg-white/30 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full leading-none">
                            {{ $umkmRoomMessages->count() }}
                        </span>
                    @endif
                </button>
                @endif
            </div>

            <!-- ── ROOM: Pembeli (user ↔ admin) ───────── -->
            @if($isAdmin || $isCreator)
            <div id="room-user" class="chat-room flex flex-col bg-white border border-gray-200 border-t-0 rounded-b-2xl shadow-sm overflow-hidden {{ $defaultTab === 'user' ? '' : 'hidden' }}" style="height: 70vh;">

                <!-- Room Header -->
                <div class="bg-[#009b97]/8 border-b border-gray-100 px-5 py-3 flex items-center gap-3">
                    <div class="w-8 h-8 bg-[#009b97]/15 text-[#009b97] rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-user text-xs"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800 text-sm">Ruang Mediasi Pembeli</h3>
                        <p class="text-xs text-gray-500">Percakapan antara Pembeli & Admin</p>
                    </div>
                    <span class="ml-auto text-xs text-gray-400 bg-gray-100 px-2 py-1 rounded-full">{{ $userRoomMessages->count() }} pesan</span>
                </div>

                <!-- Messages -->
                <div id="chatThread-user" class="flex-1 p-5 overflow-y-auto space-y-4 bg-gray-50/30">
                    @forelse($userRoomMessages as $msg)
                        @php
                            $isMe = $msg->sender_id === $user->id;
                            $role = $msg->sender->role;
                            $bubbleBg = 'bg-white border border-gray-100';
                            $roleBadge = '';
                            if ($role === 'admin') {
                                $bubbleBg = 'bg-purple-50 border border-purple-100';
                                $roleBadge = '<span class="px-1.5 py-0.5 rounded bg-purple-200 text-purple-800 text-[9px] font-bold">ADMIN</span>';
                            }
                        @endphp
                        <div class="flex flex-col {{ $isMe ? 'items-end' : 'items-start' }} max-w-[85%] {{ $isMe ? 'ml-auto' : 'mr-auto' }}">
                            <div class="flex items-center gap-1.5 mb-1 text-xs text-gray-400 font-bold {{ $isMe ? 'flex-row-reverse' : '' }}">
                                <span>{{ $msg->sender->name }}</span>
                                {!! $roleBadge !!}
                            </div>
                            <div class="rounded-2xl px-4 py-3 shadow-sm {{ $bubbleBg }}">
                                <p class="text-sm text-gray-800 whitespace-pre-wrap leading-relaxed">{{ $msg->message }}</p>
                                @if($msg->image_path)
                                    <div class="mt-3 overflow-hidden rounded-xl border border-gray-100/60 max-w-xs cursor-pointer hover:opacity-90 transition shadow-sm" onclick="viewImageFullscreen('{{ asset('storage/' . $msg->image_path) }}')">
                                        <img src="{{ asset('storage/' . $msg->image_path) }}" alt="Bukti" class="w-full h-auto object-contain max-h-48" />
                                    </div>
                                @endif
                            </div>
                            <span class="text-[10px] text-gray-400 mt-1">{{ $msg->created_at->format('H:i • d M') }}</span>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center h-full py-12 text-center">
                            <div class="w-14 h-14 bg-[#009b97]/10 text-[#009b97] rounded-full flex items-center justify-center mb-3">
                                <i class="fas fa-comments text-xl"></i>
                            </div>
                            <p class="text-sm font-semibold text-gray-600">Belum ada pesan</p>
                            <p class="text-xs text-gray-400 mt-1">Mulai diskusi dengan admin di ruang ini</p>
                        </div>
                    @endforelse
                </div>

                <!-- Input Form -->
                @if($report->status !== 'selesai')
                    <form id="chatForm-user" onsubmit="submitChatMessage(event, 'user')" action="{{ route('laporan.discussion.send', $report->id) }}" method="POST" enctype="multipart/form-data" class="border-t border-gray-100 bg-white p-4">
                        @csrf
                        <input type="hidden" name="room" value="user">
                        <div class="flex flex-col gap-2.5">
                            <textarea name="message" required rows="2" placeholder="Tulis pesan untuk admin di ruang pembeli..." class="w-full text-sm px-4 py-3 border border-gray-200 rounded-xl focus:border-[#009b97] focus:outline-none resize-none transition"></textarea>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <label for="proof_image_user" class="cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 border border-gray-200 hover:bg-gray-50 text-gray-600 rounded-lg text-xs font-bold transition">
                                        <i class="fas fa-camera text-gray-400"></i> Unggah Bukti
                                    </label>
                                    <input type="file" name="proof_image" id="proof_image_user" accept="image/*" class="hidden" onchange="previewUploadName(this, 'uploadPreviewName-user')" />
                                    <span id="uploadPreviewName-user" class="text-xs text-gray-400 max-w-[160px] truncate"></span>
                                </div>
                                <button type="submit" id="submitBtn-user" class="bg-[#009b97] hover:bg-[#007a77] text-white text-sm font-bold px-5 py-2 rounded-xl shadow transition flex items-center gap-1.5">
                                    <i class="fas fa-paper-plane text-xs"></i> Kirim
                                </button>
                            </div>
                        </div>
                    </form>
                @else
                    <div class="bg-gray-50 border-t border-gray-100 p-3 text-center text-xs font-bold text-gray-400 uppercase tracking-wider">
                        Diskusi Telah Ditutup
                    </div>
                @endif
            </div>
            @endif

            <!-- ── ROOM: UMKM (umkm ↔ admin) ─────────── -->
            @if($isAdmin || $isUmkm)
            <div id="room-umkm" class="chat-room flex flex-col bg-white border border-gray-200 border-t-0 rounded-b-2xl shadow-sm overflow-hidden {{ $defaultTab === 'umkm' ? '' : 'hidden' }}" style="height: 70vh;">

                <!-- Room Header -->
                <div class="bg-emerald-50/50 border-b border-gray-100 px-5 py-3 flex items-center gap-3">
                    <div class="w-8 h-8 bg-emerald-100 text-emerald-700 rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-store text-xs"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800 text-sm">Ruang Mediasi UMKM</h3>
                        <p class="text-xs text-gray-500">Percakapan antara UMKM & Admin</p>
                    </div>
                    <span class="ml-auto text-xs text-gray-400 bg-gray-100 px-2 py-1 rounded-full">{{ $umkmRoomMessages->count() }} pesan</span>
                </div>

                <!-- Messages -->
                <div id="chatThread-umkm" class="flex-1 p-5 overflow-y-auto space-y-4 bg-gray-50/30">
                    @forelse($umkmRoomMessages as $msg)
                        @php
                            $isMe = $msg->sender_id === $user->id;
                            $role = $msg->sender->role;
                            $bubbleBg = 'bg-emerald-50/50 border border-emerald-100';
                            $roleBadge = '<span class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[9px] font-bold">MERCHANT</span>';
                            if ($role === 'admin') {
                                $bubbleBg = 'bg-purple-50 border border-purple-100';
                                $roleBadge = '<span class="px-1.5 py-0.5 rounded bg-purple-200 text-purple-800 text-[9px] font-bold">ADMIN</span>';
                            }
                        @endphp
                        <div class="flex flex-col {{ $isMe ? 'items-end' : 'items-start' }} max-w-[85%] {{ $isMe ? 'ml-auto' : 'mr-auto' }}">
                            <div class="flex items-center gap-1.5 mb-1 text-xs text-gray-400 font-bold {{ $isMe ? 'flex-row-reverse' : '' }}">
                                <span>{{ $msg->sender->name }}</span>
                                {!! $roleBadge !!}
                            </div>
                            <div class="rounded-2xl px-4 py-3 shadow-sm {{ $bubbleBg }}">
                                <p class="text-sm text-gray-800 whitespace-pre-wrap leading-relaxed">{{ $msg->message }}</p>
                                @if($msg->image_path)
                                    <div class="mt-3 overflow-hidden rounded-xl border border-gray-100/60 max-w-xs cursor-pointer hover:opacity-90 transition shadow-sm" onclick="viewImageFullscreen('{{ asset('storage/' . $msg->image_path) }}')">
                                        <img src="{{ asset('storage/' . $msg->image_path) }}" alt="Bukti" class="w-full h-auto object-contain max-h-48" />
                                    </div>
                                @endif
                            </div>
                            <span class="text-[10px] text-gray-400 mt-1">{{ $msg->created_at->format('H:i • d M') }}</span>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center h-full py-12 text-center">
                            <div class="w-14 h-14 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mb-3">
                                <i class="fas fa-store text-xl"></i>
                            </div>
                            <p class="text-sm font-semibold text-gray-600">Belum ada pesan</p>
                            <p class="text-xs text-gray-400 mt-1">Mulai diskusi dengan admin di ruang UMKM</p>
                        </div>
                    @endforelse
                </div>

                <!-- Input Form -->
                @if($report->status !== 'selesai')
                    <form id="chatForm-umkm" onsubmit="submitChatMessage(event, 'umkm')" action="{{ route('laporan.discussion.send', $report->id) }}" method="POST" enctype="multipart/form-data" class="border-t border-gray-100 bg-white p-4">
                        @csrf
                        <input type="hidden" name="room" value="umkm">
                        <div class="flex flex-col gap-2.5">
                            <textarea name="message" required rows="2" placeholder="Tulis pesan untuk admin di ruang UMKM..." class="w-full text-sm px-4 py-3 border border-emerald-200 rounded-xl focus:border-emerald-500 focus:outline-none resize-none transition"></textarea>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <label for="proof_image_umkm" class="cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 border border-gray-200 hover:bg-gray-50 text-gray-600 rounded-lg text-xs font-bold transition">
                                        <i class="fas fa-camera text-gray-400"></i> Unggah Bukti
                                    </label>
                                    <input type="file" name="proof_image" id="proof_image_umkm" accept="image/*" class="hidden" onchange="previewUploadName(this, 'uploadPreviewName-umkm')" />
                                    <span id="uploadPreviewName-umkm" class="text-xs text-gray-400 max-w-[160px] truncate"></span>
                                </div>
                                <button type="submit" id="submitBtn-umkm" class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold px-5 py-2 rounded-xl shadow transition flex items-center gap-1.5">
                                    <i class="fas fa-paper-plane text-xs"></i> Kirim
                                </button>
                            </div>
                        </div>
                    </form>
                @else
                    <div class="bg-gray-50 border-t border-gray-100 p-3 text-center text-xs font-bold text-gray-400 uppercase tracking-wider">
                        Diskusi Telah Ditutup
                    </div>
                @endif
            </div>
            @endif

        </div><!-- /right col -->
    </div><!-- /main grid -->
</div>

<!-- Image Lightbox Modal -->
<div id="imageLightbox" class="fixed inset-0 z-[999999] hidden flex flex-col items-center justify-center bg-black/95 p-4">
    <button onclick="closeImageLightbox()" class="absolute top-5 right-5 text-white text-3xl font-bold h-12 w-12 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 transition">
        &times;
    </button>
    <div class="max-w-4xl max-h-[85vh] overflow-hidden rounded-lg shadow-2xl">
        <img id="lightboxImage" src="" alt="Proof Preview" class="w-full h-full max-h-[85vh] object-contain" />
    </div>
    <div class="mt-4 flex gap-3">
        <a id="lightboxDownloadBtn" href="" download="bukti_komplain.png" class="rounded-xl bg-[#009b97] px-6 py-3 font-bold text-white shadow-md hover:bg-[#007a77] transition flex items-center gap-2">
            <i class="fas fa-download"></i> Download
        </a>
        <button onclick="closeImageLightbox()" class="rounded-xl bg-slate-700 px-6 py-3 font-bold text-white hover:bg-slate-600 transition">Tutup</button>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // ─────────────────────────────────────────────────
    // ROOM TAB SWITCHING
    // ─────────────────────────────────────────────────
    let activeRoom = '{{ $isCreator ? "user" : ($isUmkm ? "umkm" : "user") }}';

    function switchRoom(room) {
        activeRoom = room;

        // Hide all rooms
        document.querySelectorAll('.chat-room').forEach(el => el.classList.add('hidden'));

        // Show target room
        const targetRoom = document.getElementById('room-' + room);
        if (targetRoom) {
            targetRoom.classList.remove('hidden');
            // Scroll to bottom
            const thread = document.getElementById('chatThread-' + room);
            if (thread) thread.scrollTop = thread.scrollHeight;
        }

        // Update tab styles
        const tabUser = document.getElementById('tab-user');
        const tabUmkm = document.getElementById('tab-umkm');

        if (tabUser) {
            if (room === 'user') {
                tabUser.classList.remove('bg-gray-50', 'text-gray-500', 'hover:bg-gray-100');
                tabUser.classList.add('bg-[#009b97]', 'text-white');
            } else {
                tabUser.classList.add('bg-gray-50', 'text-gray-500', 'hover:bg-gray-100');
                tabUser.classList.remove('bg-[#009b97]', 'text-white');
            }
        }
        if (tabUmkm) {
            if (room === 'umkm') {
                tabUmkm.classList.remove('bg-gray-50', 'text-gray-500', 'hover:bg-gray-100');
                tabUmkm.classList.add('bg-emerald-600', 'text-white');
            } else {
                tabUmkm.classList.add('bg-gray-50', 'text-gray-500', 'hover:bg-gray-100');
                tabUmkm.classList.remove('bg-emerald-600', 'text-white');
            }
        }
    }

    // Scroll both chat threads to bottom on load
    ['user', 'umkm'].forEach(r => {
        const t = document.getElementById('chatThread-' + r);
        if (t) t.scrollTop = t.scrollHeight;
    });

    // ─────────────────────────────────────────────────
    // AUDIO CHIME
    // ─────────────────────────────────────────────────
    let sharedAudioCtx = null;
    const initAudioContext = () => {
        if (!sharedAudioCtx) sharedAudioCtx = new (window.AudioContext || window.webkitAudioContext)();
        if (sharedAudioCtx.state === 'suspended') sharedAudioCtx.resume();
    };
    window.addEventListener('click', initAudioContext, { once: true });
    window.addEventListener('touchstart', initAudioContext, { once: true });

    function playChime() {
        try {
            initAudioContext();
            const ctx = sharedAudioCtx;
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, ctx.currentTime);
            osc.frequency.exponentialRampToValueAtTime(660, ctx.currentTime + 0.15);
            gain.gain.setValueAtTime(0.15, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.35);
            osc.start();
            osc.stop(ctx.currentTime + 0.4);
        } catch(e) {}
    }

    // ─────────────────────────────────────────────────
    // UTILS
    // ─────────────────────────────────────────────────
    function previewUploadName(input, spanId) {
        const span = document.getElementById(spanId);
        if (span) span.textContent = input.files && input.files[0] ? input.files[0].name : '';
    }

    function viewImageFullscreen(src) {
        document.getElementById('lightboxImage').src = src;
        document.getElementById('lightboxDownloadBtn').href = src;
        document.getElementById('imageLightbox').classList.remove('hidden');
    }
    function closeImageLightbox() {
        document.getElementById('imageLightbox').classList.add('hidden');
    }

    function toggleCustomAmount() {
        const type = document.getElementById('refundTypeSelect').value;
        const group = document.getElementById('customAmountGroup');
        const input = document.getElementById('customAmountInput');
        if (type === 'custom') {
            group.classList.remove('hidden');
            input.required = true;
        } else {
            group.classList.add('hidden');
            input.required = false;
        }
    }

    // ─────────────────────────────────────────────────
    // ADMIN REFUND ACTION
    // ─────────────────────────────────────────────────
    function processAdminRefund(e) {
        e.preventDefault();
        const type = document.getElementById('refundTypeSelect').value;
        const customVal = document.getElementById('customAmountInput')?.value || 0;
        let amountText = type === 'full' ? 'penuh 100%' : type === 'half' ? 'setengah 50%' : 'sebesar Rp ' + new Intl.NumberFormat('id-ID').format(customVal);

        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: `Proses refund saldo ke pelanggan ${amountText}. Saldo UMKM akan terpotong otomatis.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#7c3aed',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Proses!',
            cancelButtonText: 'Batal'
        }).then(result => {
            if (result.isConfirmed) {
                Swal.showLoading();
                fetch('{{ route("admin.laporan.refund", $report->id) }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ refund_type: type, custom_amount: customVal })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({ title: 'Berhasil!', text: data.message, icon: 'success', confirmButtonColor: '#7c3aed' }).then(() => location.reload());
                    } else {
                        Swal.fire({ title: 'Gagal', text: data.message || 'Terjadi kesalahan.', icon: 'error', confirmButtonColor: '#7c3aed' });
                    }
                })
                .catch(() => Swal.fire({ title: 'Error', text: 'Kegagalan jaringan.', icon: 'error', confirmButtonColor: '#7c3aed' }));
            }
        });
    }

    // ─────────────────────────────────────────────────
    // POLLING — per-room message count tracking
    // ─────────────────────────────────────────────────
    let lastMsgCount = {
        user: {{ $userRoomMessages->count() }},
        umkm: {{ $umkmRoomMessages->count() }}
    };

    function pollMessages() {
        if ('{{ $report->status }}' === 'selesai') return;

        fetch(window.location.href)
        .then(r => r.text())
        .then(html => {
            const doc = new DOMParser().parseFromString(html, 'text/html');

            ['user', 'umkm'].forEach(room => {
                const newThread = doc.getElementById('chatThread-' + room);
                const curThread = document.getElementById('chatThread-' + room);
                if (!newThread || !curThread) return;

                const newCount = newThread.querySelectorAll('.flex-col').length;
                if (newCount > lastMsgCount[room]) {
                    lastMsgCount[room] = newCount;
                    curThread.innerHTML = newThread.innerHTML;
                    if (activeRoom === room) {
                        curThread.scrollTop = curThread.scrollHeight;
                    }
                    playChime();
                }
            });
        })
        .catch(() => {});
    }

    setInterval(pollMessages, 5000);

    // ─────────────────────────────────────────────────
    // AJAX CHAT SUBMIT — room-aware
    // ─────────────────────────────────────────────────
    function submitChatMessage(e, room) {
        e.preventDefault();

        const form = document.getElementById('chatForm-' + room);
        const submitBtn = document.getElementById('submitBtn-' + room);
        const textarea = form.querySelector('textarea[name="message"]');
        const fileInput = form.querySelector('input[type="file"]');

        if (!textarea.value.trim() && (!fileInput || !fileInput.value)) return;

        const originalBtnHTML = submitBtn.innerHTML;
        

        const formData = new FormData(form);

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin text-xs"></i> Mengirim...';
        textarea.disabled = true;
console.log("Form:", form.id);
console.log("Textarea:", textarea.value);

for (const [key, value] of formData.entries()) {
    console.log(key, value);
}

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'  // Pastikan Laravel selalu kirim JSON, bukan HTML redirect
            }
        })
        .then(async res => {
            if (!res.ok) {
                // Parse JSON error dari Laravel (422 validation / 403 auth)
                let errMsg = `Error ${res.status}`;
                try {
                    const data = await res.json();
                    // Laravel validation: data.message atau data.errors
                    if (data.message) errMsg = data.message;
                    else if (data.errors) {
                        const firstField = Object.keys(data.errors)[0];
                        errMsg = data.errors[firstField][0];
                    }
                } catch (_) {}
                throw new Error(errMsg);
            }
            return res; // success
        })
        .then(() => {
            // Reload room thread setelah POST berhasil
            return fetch(window.location.href, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.text())
            .then(html => {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const newThread = doc.getElementById('chatThread-' + room);
                const curThread = document.getElementById('chatThread-' + room);
                if (newThread && curThread) {
                    curThread.innerHTML = newThread.innerHTML;
                    curThread.scrollTop = curThread.scrollHeight;
                    lastMsgCount[room] = newThread.querySelectorAll('.flex-col').length;
                }
            });
        })
        .then(() => {
            textarea.value = '';
            if (fileInput) fileInput.value = '';
            const previewSpan = document.getElementById('uploadPreviewName-' + room);
            if (previewSpan) previewSpan.textContent = '';
        })
        .catch(err => {
            Swal.fire({
                icon: 'error',
                title: 'Gagal mengirim',
                text: err.message || 'Terjadi kesalahan saat mengirim pesan.',
                confirmButtonColor: '#009b97'
            });
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnHTML;
            textarea.disabled = false;
            textarea.focus();
        });
    }
</script>
@endsection
