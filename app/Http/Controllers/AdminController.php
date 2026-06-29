<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UMKM;
use App\Models\Report;
use App\Models\Order;
use App\Models\SaldoMutation;
use App\Models\WithdrawRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use App\Services\MediaCompressionService;

class AdminController extends Controller
{
    public function index()
    {
        $umkm = UMKM::with('user')->get();
        $totalLaporan = Report::count();
        $pendingLaporan = Report::where('status', 'pending')->count();
        return view('admin.dashboard', compact('umkm', 'totalLaporan', 'pendingLaporan'));
    }

    public function createUmkm()
    {
        return view('admin.create-umkm');
    }

    public function storeUmkm(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'nama_umkm' => 'required|string|max:255',
            'description' => 'required|string',
            'jenis_umkm' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'no_wa' => 'nullable|string|max:20',
            'instagram_url' => 'nullable|url|max:255',
            'shopee_url' => 'nullable|url|max:255',
            'tokopedia_url' => 'nullable|url|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Create user with UMKM role
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'umkm',
        ]);

        // Handle photo upload
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = MediaCompressionService::compressAndStoreImage($request->file('photo'), 'umkm-photos');
        }

        // Create UMKM
        UMKM::create([
            'user_id' => $user->id,
            'nama' => $request->nama_umkm,
            'description' => $request->description,
            'jenis_umkm' => $request->jenis_umkm,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'no_wa' => $request->no_wa,
            'instagram_url' => $request->instagram_url,
            'shopee_url' => $request->shopee_url,
            'tokopedia_url' => $request->tokopedia_url,
            'photo_path' => $photoPath,
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Akun UMKM berhasil dibuat!');
    }

    public function editUmkm($id)
    {
        $umkm = UMKM::with('user')->findOrFail($id);
        return view('admin.edit-umkm', compact('umkm'));
    }

    public function updateUmkm(Request $request, $id)
    {
        $umkm = UMKM::with('user')->findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $umkm->user_id,
            'nama_umkm' => 'required|string|max:255',
            'description' => 'required|string',
            'jenis_umkm' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'no_wa' => 'nullable|string|max:20',
            'instagram_url' => 'nullable|url|max:255',
            'shopee_url' => 'nullable|url|max:255',
            'tokopedia_url' => 'nullable|url|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Update user
        $umkm->user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo
            if ($umkm->photo_path) {
                Storage::disk('public')->delete($umkm->photo_path);
            }
            $photoPath = MediaCompressionService::compressAndStoreImage($request->file('photo'), 'umkm-photos');
        } else {
            $photoPath = $umkm->photo_path;
        }

        // Update UMKM
        $umkm->update([
            'nama' => $request->nama_umkm,
            'description' => $request->description,
            'jenis_umkm' => $request->jenis_umkm,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'no_wa' => $request->no_wa,
            'instagram_url' => $request->instagram_url,
            'shopee_url' => $request->shopee_url,
            'tokopedia_url' => $request->tokopedia_url,
            'photo_path' => $photoPath,
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Data UMKM berhasil diperbarui!');
    }

    public function deleteUmkm($id)
    {
        $umkm = UMKM::findOrFail($id);
        
        // Delete photo
        if ($umkm->photo_path) {
            Storage::disk('public')->delete($umkm->photo_path);
        }
        
        // Delete user and UMKM (cascade will handle related data)
        $umkm->user->delete();
        
        return redirect()->route('admin.dashboard')->with('success', 'UMKM berhasil dihapus!');
    }

    public function uploadPdf(Request $request)
    {
        try {
            $request->validate([
                'pdf_file' => 'required|file|mimes:pdf|max:10240', // Max 10MB
            ]);

            if (!$request->hasFile('pdf_file')) {
                return response()->json([
                    'success' => false,
                    'message' => 'File PDF tidak ditemukan.'
                ], 400);
            }

            $file = $request->file('pdf_file');
            
            // Send to external API
            $response = Http::timeout(60)
                ->attach('file', file_get_contents($file->getPathname()), $file->getClientOriginalName())
                ->post('https://ai-umkmm-go.sgp.dom.my.id/admin/upload');

            if ($response->successful()) {
                $data = $response->json();
                
                return response()->json([
                    'success' => true,
                    'message' => $data['message'] ?? 'PDF berhasil diupload ke sistem AI!',
                    'data' => $data
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $response->json()['message'] ?? 'Gagal mengupload PDF ke sistem AI.',
                    'error' => $response->body()
                ], $response->status());
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat terhubung ke server AI. Silakan coba lagi nanti.'
            ], 503);
        } catch (\Exception $e) {
            \Log::error('PDF upload error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show all reports/laporan
     */
    public function laporan()
    {
        $reports = Report::with(['admin', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        // Statistics
        $totalReports = Report::count();
        $pendingReports = Report::where('status', 'pending')->count();
        $diprosesReports = Report::where('status', 'diproses')->count();
        $selesaiReports = Report::where('status', 'selesai')->count();
        
        return view('admin.laporan', compact('reports', 'totalReports', 'pendingReports', 'diprosesReports', 'selesaiReports'));
    }

    /**
     * Get single report detail (for modal)
     */
    public function getLaporan($id)
    {
        $report = Report::with('admin')->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'report' => [
                'id' => $report->id,
                'nama' => $report->nama,
                'email' => $report->email,
                'kategori' => $report->kategori,
                'kategori_label' => $report->kategori_label,
                'judul' => $report->judul,
                'deskripsi' => $report->deskripsi,
                'status' => $report->status,
                'status_label' => $report->status_label,
                'respon_admin' => $report->respon_admin,
                'admin' => $report->admin ? [
                    'name' => $report->admin->name
                ] : null,
                'created_at' => $report->created_at->format('d M Y H:i'),
                'order_id' => $report->order_id,
                'order' => $report->order ? [
                    'order_code' => $report->order->order_code,
                    'total' => (float)$report->order->total,
                    'subtotal' => (float)$report->order->subtotal,
                    'umkm_name' => $report->order->umkm?->nama,
                    'umkm_id' => $report->order->umkm_id,
                ] : null,
            ]
        ]);
    }

    /**
     * Potong saldo UMKM karena laporan user
     */
    public function deductUmkm(Request $request, $id)
    {
        $report = Report::with('order.umkm')->findOrFail($id);

        if (!$report->order_id || !$report->order) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan ini tidak terhubung dengan transaksi order manapun.'
            ], 422);
        }

        $order = $report->order;
        $umkm = $order->umkm;

        if (!$umkm) {
            return response()->json([
                'success' => false,
                'message' => 'UMKM tidak ditemukan untuk transaksi ini.'
            ], 404);
        }

        $deductAmount = (float) $order->subtotal; // potong sebesar subtotal (harga produk)

        // Cek saldo saat ini secara dinamis
        $currentBalance = SaldoMutation::computeCurrentBalance($umkm->id);
        if ($currentBalance < $deductAmount) {
            return response()->json([
                'success' => false,
                'message' => 'Saldo UMKM tidak mencukupi untuk melakukan pemotongan. Saldo tersedia: Rp ' . number_format($currentBalance, 0, ',', '.')
            ], 422);
        }

        // Catat mutasi debit (deduction)
        $reason = "Pemotongan oleh admin berdasarkan laporan #" . $report->id . " (" . $report->judul . ")";
        SaldoMutation::recordAdminDeduction($umkm->id, $deductAmount, $reason, $report->id);

        // Update status laporan menjadi selesai secara otomatis dengan respon admin
        $report->update([
            'status' => 'selesai',
            'respon_admin' => ($report->respon_admin ? $report->respon_admin . "\n\n" : "") . "[SISTEM ADMIN] Saldo UMKM telah dipotong sebesar Rp " . number_format($deductAmount, 0, ',', '.') . " sebagai tindakan disiplin atas laporan ini.",
            'admin_id' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Saldo UMKM berhasil dipotong sebesar Rp ' . number_format($deductAmount, 0, ',', '.') . '.',
            'report' => $report
        ]);
    }

    /**
     * Proses refund/denda fleksibel dari laporan user (Full / Half / Custom)
     */
    public function processRefundUmkm(Request $request, $id)
    {
        $report = Report::with('order.umkm')->findOrFail($id);

        if (!$report->order_id || !$report->order) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan ini tidak terhubung dengan transaksi order manapun.'
            ], 422);
        }

        $request->validate([
            'refund_type' => 'required|in:full,half,custom',
            'custom_amount' => 'required_if:refund_type,custom|nullable|numeric|min:0',
        ]);

        $order = $report->order;
        $umkm = $order->umkm;

        if (!$umkm) {
            return response()->json([
                'success' => false,
                'message' => 'UMKM tidak ditemukan untuk transaksi ini.'
            ], 404);
        }

        $type = $request->refund_type;
        $deductAmount = 0.0;

        if ($type === 'full') {
            $deductAmount = (float) $order->subtotal;
            // Batalkan order
            $order->update(['order_status' => 'cancelled']);
        } elseif ($type === 'half') {
            $deductAmount = (float) ($order->subtotal * 0.5);
        } elseif ($type === 'custom') {
            $deductAmount = (float) $request->custom_amount;
        }

        // Catat mutasi debit (deduction / refund) - saldo diperbolehkan bernilai minus (negatif)
        $reason = "Refund/Denda oleh admin (" . ucfirst($type) . ") berdasarkan laporan #" . $report->id . " (" . $report->judul . ")";
        
        SaldoMutation::recordAdminDeduction($umkm->id, $deductAmount, $reason, $report->id);

        // Update status laporan menjadi selesai secara otomatis dengan respon admin
        $report->update([
            'status' => 'selesai',
            'respon_admin' => ($report->respon_admin ? $report->respon_admin . "\n\n" : "") . "[SISTEM ADMIN] Refund (" . ucfirst($type) . ") diproses. Saldo UMKM dikurangi sebesar Rp " . number_format($deductAmount, 0, ',', '.') . " sebagai penyelesaian komplain.",
            'admin_id' => auth()->id(),
        ]);

        // Post pesan otomatis di diskusi chat sebagai pengumuman
        \App\Models\ReportMessage::create([
            'report_id' => $report->id,
            'sender_id' => auth()->id(),
            'message' => "[KEPUTUSAN ADMIN] Laporan telah diselesaikan oleh Admin dengan keputusan REFUND " . strtoupper($type) . " sebesar Rp " . number_format($deductAmount, 0, ',', '.') . ".",
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Refund/Denda berhasil diproses sebesar Rp ' . number_format($deductAmount, 0, ',', '.') . '.',
            'report' => $report
        ]);
     }

     /**
      * Get current count of pending reports for admin alerts
      */
     public function pendingLaporanCount()
     {
         $count = Report::where('status', 'pending')->count();
         return response()->json([
             'success' => true,
             'count' => $count
         ]);
     }

    /**
     * Tampilkan halaman tracking semua transaksi antara user dengan umkm
     */
    public function allTransactions(Request $request)
    {
        $query = Order::with(['user', 'umkm', 'layanan'])->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhereHas('umkm', function($qu) use ($search) {
                      $qu->where('nama', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('order_status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $orders = $query->paginate(20);

        // Statistics
        $totalRevenue = Order::where('payment_status', 'paid')->sum('total');
        $totalOrdersCount = Order::count();
        $paidOrdersCount = Order::where('payment_status', 'paid')->count();

        return view('admin.transactions', compact('orders', 'totalRevenue', 'totalOrdersCount', 'paidOrdersCount'));
    }

    /**
     * Update status laporan
     */
    public function updateStatusLaporan(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,diproses,selesai',
            'respon_admin' => 'nullable|string|max:1000',
        ]);

        $report = Report::findOrFail($id);
        
        $report->update([
            'status' => $request->status,
            'respon_admin' => $request->respon_admin,
            'admin_id' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status laporan berhasil diperbarui.',
            'report' => $report
        ]);
    }

    /**
     * Delete laporan
     */
    public function deleteLaporan($id)
    {
        $report = Report::findOrFail($id);
        $report->delete();


        return redirect()->route('admin.laporan')->with('success', 'Laporan berhasil dihapus!');
    }

    /**
     * Show all users
     */
    public function users()
    {
        $users = User::with('umkm')->orderBy('created_at', 'desc')->paginate(15);
        
        // Statistics
        $totalUsers = User::count();
        $totalUserRole = User::where('role', 'user')->count();
        $totalUmkmRole = User::where('role', 'umkm')->count();
        $totalAdminRole = User::where('role', 'admin')->count();
        
        return view('admin.users', compact('users', 'totalUsers', 'totalUserRole', 'totalUmkmRole', 'totalAdminRole'));
    }

    /**
     * Show edit user form
     */
    public function editUser($id)
    {
        $user = User::with('umkm')->findOrFail($id);
        return view('admin.edit-user', compact('user'));
    }

    /**
     * Update user (admin can edit any user)
     */
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:user,umkm,admin',
        ]);

        $updateData = [
            'name' => $request->name,
            'role' => $request->role,
        ];

        // Only update password if provided
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return redirect()->route('admin.users')->with('success', 'Data akun berhasil diperbarui!');
    }

    /**
     * List semua withdraw requests (sorted pending dulu).
     */
    public function withdrawRequests()
    {
        $withdraws = WithdrawRequest::with('umkm.user')
            ->orderByRaw("CASE status WHEN 'pending' THEN 1 WHEN 'approved' THEN 2 WHEN 'rejected' THEN 3 ELSE 4 END")
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($w) => $this->formatWithdraw($w));

        $pendingCount = WithdrawRequest::where('status', 'pending')->count();

        return response()->json([
            'success'       => true,
            'withdraws'     => $withdraws,
            'pending_count' => $pendingCount,
        ]);
    }

    /**
     * Jumlah WD pending yang belum dilihat admin.
     */
    public function withdrawPendingCount()
    {
        $count = WithdrawRequest::where('status', 'pending')
            ->where('is_seen_by_admin', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Admin memproses WD (approve/reject).
     */
    public function processWithdraw(Request $request, $id)
    {
        $wd = WithdrawRequest::findOrFail($id);

        if ($wd->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Permintaan ini sudah diproses sebelumnya.'], 422);
        }

        $validated = $request->validate([
            'action'     => 'required|in:approve,reject',
            'admin_note' => 'nullable|string|max:500',
            'bukti'      => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $status = $validated['action'] === 'approve' ? 'approved' : 'rejected';

        $buktiPath = $wd->bukti_transfer;
        if ($request->hasFile('bukti')) {
            $buktiPath = MediaCompressionService::compressAndStoreImage($request->file('bukti'), 'withdraw-bukti');
        }

        // Jika approve, validasi saldo masih cukup secara dinamis (kurangi juga total deductions)
        if ($status === 'approved') {
            $totalPemasukan = (float) \App\Models\Order::where('umkm_id', $wd->umkm_id)
                ->where('payment_status', 'paid')
                ->sum('subtotal');
            $totalWithdrawApproved = (float) WithdrawRequest::where('umkm_id', $wd->umkm_id)
                ->where('status', 'approved')
                ->sum('jumlah');
            $totalDeductions = (float) SaldoMutation::where('umkm_id', $wd->umkm_id)
                ->where('category', 'admin_deduction')
                ->sum('amount');
            $saldoTersedia = max(0, $totalPemasukan - $totalWithdrawApproved - $totalDeductions);

            if ($saldoTersedia < (float) $wd->jumlah) {
                return response()->json([
                    'success' => false,
                    'message' => 'Saldo UMKM tidak mencukupi. Saldo tersedia: Rp ' . number_format($saldoTersedia, 0, ',', '.')
                ], 422);
            }
        }

        $wd->update([
            'status'          => $status,
            'admin_note'      => $validated['admin_note'] ?? null,
            'bukti_transfer'  => $buktiPath,
            'processed_at'    => now(),
            'processed_by'    => Auth::id(),
            'is_seen_by_admin'=> true,
            'is_seen_by_umkm' => false, // UMKM belum lihat hasilnya
        ]);

        if ($status === 'approved') {
            try {
                SaldoMutation::recordWithdrawal($wd->umkm_id, $wd);
            } catch (\Exception $e) {
                \Log::error('Gagal mencatat mutasi WD: ' . $e->getMessage());
            }
        }

        return response()->json([
            'success'  => true,
            'message'  => 'Permintaan penarikan berhasil ' . ($status === 'approved' ? 'disetujui' : 'ditolak') . '.',
            'withdraw' => $this->formatWithdraw($wd->fresh()),
        ]);
    }

    /**
     * Admin mark WD as seen.
     */
    public function markWithdrawSeen()
    {
        WithdrawRequest::where('status', 'pending')
            ->where('is_seen_by_admin', false)
            ->update(['is_seen_by_admin' => true]);

        return response()->json(['success' => true]);
    }

    private function formatWithdraw(WithdrawRequest $wd): array
    {
        return [
            'id'             => $wd->id,
            'umkm_id'        => $wd->umkm_id,
            'umkm_name'      => $wd->umkm?->nama ?? '-',
            'umkm_user'      => $wd->umkm?->user?->name ?? '-',
            'jumlah'         => (float) $wd->jumlah,
            'rekening_bank'  => $wd->rekening_bank,
            'nomor_rekening' => $wd->nomor_rekening,
            'nama_pemilik'   => $wd->nama_pemilik,
            'status'         => $wd->status,
            'status_label'   => $wd->status_label,
            'admin_note'     => $wd->admin_note,
            'bukti_transfer' => $wd->bukti_transfer ? asset('storage/' . $wd->bukti_transfer) : null,
            'processed_at'   => $wd->processed_at?->format('d M Y H:i'),
            'created_at'     => $wd->created_at->format('d M Y H:i'),
            'is_seen_by_admin'=> $wd->is_seen_by_admin,
        ];
    }

    /**
     * Get transaction detail JSON for tracking modal.
     */
    public function getTransactionDetail($id)
    {
        $order = Order::with(['user', 'umkm.user', 'layanan'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'order'   => [
                'id'               => $order->id,
                'order_code'       => $order->order_code,
                'user_name'        => $order->user?->name ?? 'User',
                'user_email'       => $order->user?->email ?? '-',
                'umkm_name'        => $order->umkm?->nama ?? 'UMKM',
                'umkm_owner'       => $order->umkm?->user?->name ?? '-',
                'layanan_name'     => $order->layanan?->nama ?? 'Layanan',
                'layanan_price'    => (float) $order->layanan?->price ?? 0,
                'quantity'         => $order->quantity,
                'subtotal'         => (float) $order->subtotal,
                'delivery_method'  => $order->delivery_method,
                'delivery_fee'     => (float) $order->delivery_fee,
                'app_tax'          => (float) $order->app_tax,
                'qris_tax'         => (float) $order->qris_tax,
                'unique_code'      => (int) $order->unique_code,
                'total'            => (float) $order->total,
                'customer_name'    => $order->customer_name,
                'customer_phone'   => $order->customer_phone,
                'customer_address' => $order->customer_address,
                'notes'            => $order->notes,
                'payment_status'   => $order->payment_status,
                'payment_method'   => $order->payment_method,
                'order_status'     => $order->order_status,
                'created_at'       => $order->created_at->format('d M Y H:i'),
                'is_refunded'      => SaldoMutation::where('order_id', $order->id)->where('category', 'refund')->exists(),
            ]
        ]);
    }

    /**
     * Process refund for an order (subtracts subtotal from UMKM balance).
     */
    public function refundTransaction($id)
    {
        $order = Order::findOrFail($id);

        if ($order->payment_status !== 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi belum lunas, tidak dapat di-refund.'
            ], 400);
        }

        // Check if already refunded
        $isRefunded = SaldoMutation::where('order_id', $order->id)
            ->where('category', 'refund')
            ->exists();

        if ($isRefunded) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi ini sudah pernah di-refund sebelumnya.'
            ], 400);
        }

        // 1. Update order status
        $order->update([
            'order_status' => 'cancelled' // cancelled / refunded
        ]);

        // 2. Record mutation
        SaldoMutation::recordRefund($order->umkm_id, $order);

        return response()->json([
            'success' => true,
            'message' => 'Refund / Retur berhasil diproses. Saldo UMKM telah dikurangi!'
        ]);
    }
}