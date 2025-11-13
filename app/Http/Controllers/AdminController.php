<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UMKM;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class AdminController extends Controller
{
    public function index()
    {
        $umkm = UMKM::with('user')->get();
        return view('admin.dashboard', compact('umkm'));
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
            $photoPath = $request->file('photo')->store('umkm-photos', 'public');
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
            $photoPath = $request->file('photo')->store('umkm-photos', 'public');
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
                ->post('https://ai-martabakmanis-production.up.railway.app/admin/upload');

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
            ]
        ]);
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
}