<?php

namespace App\Http\Controllers;

use App\Models\UMKM;
use App\Models\Keuntungan;
use App\Models\Layanan;
use App\Models\LayananUMKM;
use App\Models\User;
use App\Models\Report;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
// use PhpOffice\PhpSpreadsheet\Spreadsheet;
// use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
// use PhpOffice\PhpSpreadsheet\IOFactory;
// use Maatwebsite\Excel\Facades\Excel;
// use App\Imports\KeuntunganImport;
// use App\Exports\KeuntunganTemplateExport;

class UMKMController extends Controller
{
    public function dashboard()
    {
        $umkm = Auth::user()->umkm;
        $keuntungan = $umkm ? Keuntungan::where('umkm_id', $umkm->id)->orderBy('bulan', 'desc')->get() : collect();
        $layanan = Layanan::where('user_id', Auth::id())->get();
        
        // Get users who favorited this UMKM
        $favoriteUsers = collect();
        if ($umkm && $umkm->favorite) {
            $favoriteUsers = User::whereIn('id', $umkm->favorite)->get();
        }
        
        return view('umkm.dashboard', compact('umkm', 'keuntungan', 'layanan', 'favoriteUsers'));
    }

    public function updateProfile(Request $request)
    {
        try {
            $request->validate([
                'nama' => 'required|string|max:255',
                'description' => 'required|string',
                'jenis_umkm' => 'required|string|max:255',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'no_wa' => 'nullable|string|max:20',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $umkm = Auth::user()->umkm;
            
            // Handle photo upload
            $photoPath = null;
            if ($request->hasFile('photo')) {
                // Delete old photo if exists
                if ($umkm && $umkm->photo_path) {
                    Storage::disk('public')->delete($umkm->photo_path);
                }
                $photoPath = $request->file('photo')->store('umkm-photos', 'public');
            } else {
                $photoPath = $umkm ? $umkm->photo_path : null;
            }

            if ($umkm) {
                // Update existing UMKM
                $umkm->update([
                    'nama' => $request->nama,
                    'description' => $request->description,
                    'jenis_umkm' => $request->jenis_umkm,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'no_wa' => $request->no_wa,
                    'photo_path' => $photoPath,
                ]);
            } else {
                // Create new UMKM
                UMKM::create([
                    'user_id' => Auth::id(),
                    'nama' => $request->nama,
                    'description' => $request->description,
                    'jenis_umkm' => $request->jenis_umkm,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'no_wa' => $request->no_wa,
                    'photo_path' => $photoPath,
                    'favorit_count' => 0,
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Profil UMKM berhasil diperbarui!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan: ' . $e->getMessage()]);
        }
    }

    public function storeKeuntungan(Request $request)
    {
        try {
            $umkm = Auth::user()->umkm;
            
            if (!$umkm) {
                return response()->json(['success' => false, 'message' => 'Profil UMKM belum lengkap. Silakan lengkapi profil terlebih dahulu.']);
            }
            
            $request->validate([
                'periode_type' => 'required|in:harian,mingguan,bulanan',
                'tanggal' => 'required_if:periode_type,harian|date',
                'minggu' => 'required_if:periode_type,mingguan|date_format:Y-\WW',
                'bulan' => 'required_if:periode_type,bulanan|date_format:Y-m',
                'pendapatan' => 'required|numeric|min:0',
                'pengeluaran' => 'required|numeric|min:0',
                'jumlah_transaksi' => 'required|integer|min:0',
            ]);

            $keuntunganBersih = $request->pendapatan - $request->pengeluaran;
            
            // Always group data by month regardless of input type
            $bulan = '';
            if ($request->periode_type === 'harian') {
                $bulan = date('F Y', strtotime($request->tanggal));
            } elseif ($request->periode_type === 'mingguan') {
                // Input format: 2025-W01, convert to proper date
                $mingguDate = date('Y-m-d', strtotime($request->minggu . '-1'));
                $bulan = date('F Y', strtotime($mingguDate));
            } elseif ($request->periode_type === 'bulanan') {
                // Input format: 2025-01, convert to proper date
                $bulanDate = $request->bulan . '-01';
                $bulan = date('F Y', strtotime($bulanDate));
            }

            // Check if data for this month already exists
            $existingKeuntungan = Keuntungan::where('umkm_id', $umkm->id)
                ->where('bulan', $bulan)
                ->first();
            
            if ($existingKeuntungan) {
                // Update existing monthly data by adding new values
                $existingKeuntungan->update([
                    'pendapatan' => $existingKeuntungan->pendapatan + $request->pendapatan,
                    'pengeluaran' => $existingKeuntungan->pengeluaran + $request->pengeluaran,
                    'keuntungan_bersih' => ($existingKeuntungan->pendapatan + $request->pendapatan) - ($existingKeuntungan->pengeluaran + $request->pengeluaran),
                    'jumlah_transaksi' => $existingKeuntungan->jumlah_transaksi + $request->jumlah_transaksi,
                ]);
            } else {
                // Create new monthly data
                Keuntungan::create([
                    'umkm_id' => $umkm->id,
                    'bulan' => $bulan,
                    'pendapatan' => $request->pendapatan,
                    'pengeluaran' => $request->pengeluaran,
                    'keuntungan_bersih' => $keuntunganBersih,
                    'jumlah_transaksi' => $request->jumlah_transaksi,
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Data keuntungan berhasil ditambahkan!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan: ' . $e->getMessage()]);
        }
    }
    
    public function deleteKeuntungan($id)
    {
        try {
            $umkm = Auth::user()->umkm;
            
            if (!$umkm) {
                return response()->json(['success' => false, 'message' => 'Profil UMKM belum lengkap.']);
            }
            
            $keuntungan = Keuntungan::where('id', $id)->where('umkm_id', $umkm->id)->first();
            
            if (!$keuntungan) {
                return response()->json(['success' => false, 'message' => 'Data keuntungan tidak ditemukan.']);
            }
            
            $keuntungan->delete();
            
            return response()->json(['success' => true, 'message' => 'Data keuntungan berhasil dihapus!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus: ' . $e->getMessage()]);
        }
    }

    public function uploadExcel(Request $request)
    {
        try {
            $umkm = Auth::user()->umkm;
            
            if (!$umkm) {
                return response()->json(['success' => false, 'message' => 'Profil UMKM belum lengkap. Silakan lengkapi profil terlebih dahulu.']);
            }
            
            $request->validate([
                'excel_file' => 'required|file|mimes:csv,txt|max:2048',
            ]);

            $file = $request->file('excel_file');
            
            if (!$file) {
                return response()->json(['success' => false, 'message' => 'File tidak ditemukan.']);
            }
            
            // Debug file info
            $fileInfo = [
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'extension' => $file->getClientOriginalExtension(),
                'size' => $file->getSize()
            ];
            
            \Log::info('File upload info:', $fileInfo);
            
            $fileContent = file($file->getPathname());
            if (!$fileContent) {
                return response()->json(['success' => false, 'message' => 'File kosong atau tidak dapat dibaca.']);
            }
            
            // Try different delimiters
            $delimiters = [';', ',', '\t'];
            $data = [];
            
            foreach ($delimiters as $delimiter) {
                $testData = array_map(function($line) use ($delimiter) {
                    return str_getcsv($line, $delimiter);
                }, $fileContent);
                
                // Check if we have multiple columns (more than 1)
                if (count($testData) > 0 && count($testData[0]) > 1) {
                    $data = $testData;
                    \Log::info("Using delimiter: '$delimiter'");
                    break;
                }
            }
            
            if (empty($data)) {
                return response()->json(['success' => false, 'message' => 'Tidak dapat memparse file CSV. Pastikan file memiliki data yang valid.']);
            }
            array_shift($data); // Skip header
            
            \Log::info('Parsed CSV data:', $data);
            
            // Debug: Show first few rows
            $debugInfo = [
                'total_rows' => count($data),
                'first_3_rows' => array_slice($data, 0, 3)
            ];
            \Log::info('CSV Debug Info:', $debugInfo);
            
            $imported = 0;
            foreach ($data as $index => $row) {
                \Log::info("Processing row " . ($index + 2) . ":", $row);
                
                // More lenient validation - check if we have at least 3 columns
                if (count($row) >= 3) {
                    $tanggal = trim($row[0]);
                    $pendapatanStr = trim($row[1]);
                    $pengeluaranStr = trim($row[2]);
                    $jumlahTransaksi = (int) (trim($row[4] ?? '0'));
                    
                    \Log::info("Row data - tanggal: '$tanggal', pendapatan: '$pendapatanStr', pengeluaran: '$pengeluaranStr'");
                    
                    // Skip if tanggal is empty or contains formula
                    if (empty($tanggal) || strpos($tanggal, '=') === 0) {
                        \Log::info("Skipping row " . ($index + 2) . " - empty tanggal or formula");
                        continue;
                    }
                    
                    // Convert to float, handle empty values
                    $pendapatan = !empty($pendapatanStr) ? (float) str_replace(',', '', $pendapatanStr) : 0;
                    $pengeluaran = !empty($pengeluaranStr) ? (float) str_replace(',', '', $pengeluaranStr) : 0;
                    
                    // Skip if both pendapatan and pengeluaran are 0 (empty row)
                    if ($pendapatan == 0 && $pengeluaran == 0) {
                        \Log::info("Skipping row " . ($index + 2) . " - both values are 0");
                        continue;
                    }
                    
                    // Validate date - try different formats
                    $dateFormats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y'];
                    $validDate = false;
                    $parsedDate = null;
                    
                    foreach ($dateFormats as $format) {
                        $parsedDate = \DateTime::createFromFormat($format, $tanggal);
                        if ($parsedDate && $parsedDate->format($format) === $tanggal) {
                            $validDate = true;
                            break;
                        }
                    }
                    
                    if (!$validDate) {
                        \Log::warning("Invalid date in row " . ($index + 2) . ": " . $tanggal);
                        continue;
                    }
                    
                    $keuntunganBersih = $pendapatan - $pengeluaran;
                    $bulan = $parsedDate->format('F Y');
                    $tahun = $parsedDate->format('Y');
                    
                    // Only process data for 2025
                    if ($tahun != '2025') {
                        \Log::info("Skipping row " . ($index + 2) . " - year is not 2025: " . $tahun);
                        continue;
                    }
                    
                    \Log::info("Creating keuntungan record - bulan: $bulan, pendapatan: $pendapatan, pengeluaran: $pengeluaran");
                    
                    // Check if data for this month already exists
                    $existingKeuntungan = Keuntungan::where('umkm_id', $umkm->id)
                        ->where('bulan', $bulan)
                        ->first();
                    
                    if ($existingKeuntungan) {
                        // Update existing monthly data by adding daily values
                        $existingKeuntungan->update([
                            'pendapatan' => $existingKeuntungan->pendapatan + $pendapatan,
                            'pengeluaran' => $existingKeuntungan->pengeluaran + $pengeluaran,
                            'keuntungan_bersih' => ($existingKeuntungan->pendapatan + $pendapatan) - ($existingKeuntungan->pengeluaran + $pengeluaran),
                            'jumlah_transaksi' => $existingKeuntungan->jumlah_transaksi + $jumlahTransaksi,
                        ]);
                        \Log::info("Updated existing monthly data for: $bulan");
                    } else {
                        // Create new monthly data
                        Keuntungan::create([
                            'umkm_id' => $umkm->id,
                            'bulan' => $bulan,
                            'pendapatan' => $pendapatan,
                            'pengeluaran' => $pengeluaran,
                            'keuntungan_bersih' => $keuntunganBersih,
                            'jumlah_transaksi' => $jumlahTransaksi,
                        ]);
                        \Log::info("Created new monthly data for: $bulan");
                    }
                    $imported++;
                    \Log::info("Successfully imported row " . ($index + 2));
                }
            }
            
            return response()->json([
                'success' => true, 
                'message' => "Data Excel berhasil diimpor! {$imported} data ditambahkan.",
                'debug' => [
                    'total_rows' => count($data),
                    'first_3_rows' => array_slice($data, 0, 3),
                    'imported_count' => $imported
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengimpor data: ' . $e->getMessage()]);
        }
    }

    public function downloadKeuntunganTemplate()
    {
        $filename = 'template_keuntungan.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');

            // Tambahkan BOM UTF-8 agar Excel membaca karakter dan formula dengan benar
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header CSV
            fputcsv($file, ['tanggal', 'pendapatan', 'pengeluaran', 'keuntungan_bersih', 'jumlah_transaksi'], ';');

            // Ambil tanggal hari ini
            $today = now();

            // Tulis 5 baris data kosong dengan tanggal valid dan rumus otomatis
            for ($i = 2; $i <= 6; $i++) {
                $date = $today->copy()->addDays($i - 2)->format('Y-m-d'); // format Excel-friendly
                fputcsv($file, [$date, '', '', "=B{$i}-C{$i}", ''], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    
    
    

    public function updateLayanan(Request $request)
    {
        try {
            $umkm = Auth::user()->umkm;
            
            if (!$umkm) {
                return response()->json(['success' => false, 'message' => 'Profil UMKM belum lengkap. Silakan lengkapi profil terlebih dahulu.']);
            }
            
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'nama' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'description' => 'nullable|string',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Handle photo upload
            $photoPath = null;
            if ($request->hasFile('photo')) {
                try {
                    $photoPath = $request->file('photo')->store('layanan-photos', 'public');
                    \Log::info('Photo uploaded successfully: ' . $photoPath);
                } catch (\Exception $e) {
                    \Log::error('Photo upload failed: ' . $e->getMessage());
                    return response()->json(['success' => false, 'message' => 'Gagal mengupload foto: ' . $e->getMessage()]);
                }
            } else {
                \Log::info('No photo file in request');
            }

            // Create new layanan
            $layanan = Layanan::create([
                'user_id' => $request->user_id,
                'nama' => $request->nama,
                'price' => $request->price,
                'description' => $request->description,
                'photo_path' => $photoPath,
            ]);

            // Attach to UMKM
            $umkm->layanan()->attach($layanan->id);

            return response()->json(['success' => true, 'message' => 'Layanan berhasil ditambahkan!']);
        } catch (\Exception $e) {
            \Log::error('Error in updateLayanan: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan: ' . $e->getMessage()]);
        }
    }
    
    public function removeLayanan($layananId)
    {
        $umkm = Auth::user()->umkm;
        
        if (!$umkm) {
            return response()->json(['success' => false, 'message' => 'Profil UMKM belum lengkap.']);
        }
        
        // Detach from UMKM
        $umkm->layanan()->detach($layananId);
        
        return response()->json(['success' => true, 'message' => 'Layanan berhasil dihapus!']);
    }

    public function getKeuntunganData()
    {
        $umkm = Auth::user()->umkm;
        
        if (!$umkm) {
            return response()->json([
                'labels' => [],
                'pendapatan' => [],
                'pengeluaran' => [],
                'keuntungan_bersih' => [],
            ]);
        }
        
        $keuntungan = $umkm->keuntungan()->orderBy('bulan')->get();
        
        $data = [
            'labels' => $keuntungan->pluck('bulan')->toArray(),
            'pendapatan' => $keuntungan->pluck('pendapatan')->toArray(),
            'pengeluaran' => $keuntungan->pluck('pengeluaran')->toArray(),
            'keuntungan_bersih' => $keuntungan->pluck('keuntungan_bersih')->toArray(),
        ];

        return response()->json($data);
    }

    public function getLayanan($id)
    {
        try {
            $umkm = Auth::user()->umkm;
            
            if (!$umkm) {
                return response()->json(['success' => false, 'message' => 'Profil UMKM belum lengkap.']);
            }
            
            $layanan = $umkm->layanan()->where('layanan.id', $id)->first();
            
            if (!$layanan) {
                return response()->json(['success' => false, 'message' => 'Layanan tidak ditemukan.']);
            }
            
            return response()->json(['success' => true, 'data' => $layanan]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengambil data: ' . $e->getMessage()]);
        }
    }

    public function updateLayananEdit(Request $request, $id)
    {
        try {
            $umkm = Auth::user()->umkm;
            
            if (!$umkm) {
                return response()->json(['success' => false, 'message' => 'Profil UMKM belum lengkap. Silakan lengkapi profil terlebih dahulu.']);
            }
            
            $layanan = $umkm->layanan()->where('layanan.id', $id)->first();
            
            if (!$layanan) {
                return response()->json(['success' => false, 'message' => 'Layanan tidak ditemukan.']);
            }
            
            // Log request data untuk debugging
            \Log::info('Update Layanan Request:', [
                'id' => $id,
                'nama' => $request->input('nama'),
                'price' => $request->input('price'),
                'description' => $request->input('description'),
                'has_photo' => $request->hasFile('photo'),
                'all_input' => $request->all()
            ]);
            
            $request->validate([
                'nama' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'description' => 'nullable|string',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Handle photo upload
            $photoPath = $layanan->photo_path; // Keep existing photo if not updated
            if ($request->hasFile('photo')) {
                try {
                    // Delete old photo if exists
                    if ($layanan->photo_path && \Storage::disk('public')->exists($layanan->photo_path)) {
                        \Storage::disk('public')->delete($layanan->photo_path);
                    }
                    
                    $photoPath = $request->file('photo')->store('layanan-photos', 'public');
                    \Log::info('Photo uploaded successfully: ' . $photoPath);
                } catch (\Exception $e) {
                    \Log::error('Photo upload failed: ' . $e->getMessage());
                    return response()->json(['success' => false, 'message' => 'Gagal mengupload foto: ' . $e->getMessage()]);
                }
            }

            // Update layanan
            $layanan->update([
                'nama' => $request->nama,
                'price' => $request->price,
                'description' => $request->description ?? null,
                'photo_path' => $photoPath,
            ]);

            \Log::info('Layanan updated successfully:', ['id' => $layanan->id]);

            return response()->json(['success' => true, 'message' => 'Layanan berhasil diperbarui!']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error:', $e->errors());
            return response()->json([
                'success' => false, 
                'message' => 'Validasi gagal: ' . implode(', ', array_map(function($errors) {
                    return implode(', ', $errors);
                }, $e->errors()))
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in updateLayananEdit: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui: ' . $e->getMessage()]);
        }
    }

    /**
     * Show history laporan UMKM
     */
    public function historyLaporan()
    {
        // Pastikan user sudah login
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Anda harus login terlebih dahulu untuk melihat history laporan.');
        }

        $user = Auth::user();
        
        // Get laporan berdasarkan user_id yang sedang login (pastikan tidak nabrak dengan akun lain)
        $reports = Report::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('umkm.history-laporan', compact('reports'));
    }

    /**
     * Show comments page for UMKM
     */
    public function komentar()
    {
        // Pastikan user sudah login
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Anda harus login terlebih dahulu.');
        }

        $user = Auth::user();
        $umkm = $user->umkm;

        if (!$umkm) {
            return redirect()->route('umkm.dashboard')->with('error', 'Profil UMKM belum lengkap.');
        }

        // Get comments for this UMKM (komentar untuk UMKM)
        $commentsUmkm = Comment::where('umkm_id', $umkm->id)
            ->whereNull('layanan_id') // Hanya komentar untuk UMKM, bukan layanan
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate average rating for UMKM
        $averageRatingUmkm = Comment::where('umkm_id', $umkm->id)
            ->whereNull('layanan_id')
            ->avg('rating') ?? 0;
        $totalCommentsUmkm = $commentsUmkm->count();

        // Get all layanan for this UMKM
        $layananList = $umkm->layanan;

        // Get comments for all layanan (komentar untuk layanan)
        $layananIds = $layananList->pluck('id');
        $commentsLayanan = Comment::whereIn('layanan_id', $layananIds)
            ->where('umkm_id', $umkm->id)
            ->with(['user', 'layanan'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Group comments by layanan
        $commentsByLayanan = $commentsLayanan->groupBy('layanan_id');

        // Calculate average rating for each layanan
        $averageRatingsLayanan = [];
        foreach ($layananList as $layanan) {
            $averageRatingsLayanan[$layanan->id] = Comment::where('layanan_id', $layanan->id)
                ->avg('rating') ?? 0;
        }

        return view('umkm.komentar', compact(
            'umkm',
            'commentsUmkm',
            'averageRatingUmkm',
            'totalCommentsUmkm',
            'layananList',
            'commentsByLayanan',
            'averageRatingsLayanan'
        ));
    }
}
