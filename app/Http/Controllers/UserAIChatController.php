<?php

namespace App\Http\Controllers;

use App\Models\Layanan;
use App\Models\UMKM;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserAIChatController extends Controller
{
    public function chatPage()
    {
        return view('user.ai-chat');
    }

    public function send(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $question = $request->input('message');

        try {
            // Get database data to help AI if needed
            $databaseContext = $this->getDatabaseContext($question);
            
            // Send question to AI API with database context
            $response = Http::timeout(30)
                ->post('https://ai-martabakmanis-production.up.railway.app/chat', [
                    'question' => $question,
                    'database_context' => $databaseContext // Send database data to help AI
                ]);

            if ($response->failed()) {
                Log::error('AI chat API error: ' . $response->body());
                return response()->json([
                    'success' => false, 
                    'message' => 'AI service error. Silakan coba lagi nanti.'
                ], 502);
            }

            $data = $response->json();
            
            // Handle response dari API
            $reply = $data['reply'] ?? $data['answer'] ?? $data['response'] ?? 'Maaf, saya tidak dapat menjawab saat ini.';

            // Extract menu/layanan names from AI response and validate with database
            $foundLayanan = $this->validateAndFindLayanan($reply, $question);
            
            return response()->json([
                'success' => true, 
                'reply' => $reply,
                'layanan' => $foundLayanan // Send found layanan data
            ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('AI chat connection error: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Tidak dapat terhubung ke server AI. Silakan coba lagi nanti.'
            ], 503);
        } catch (\Throwable $e) {
            Log::error('AI chat error: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Terjadi kesalahan. Silakan coba lagi.'
            ], 500);
        }
    }

    /**
     * Get database context to help AI
     */
    private function getDatabaseContext($question)
    {
        $context = [];
        
        // Check if question is about UMKM list
        if (preg_match('/\b(umkm|toko|daftar|list|terdaftar|yang ada)\b/i', $question)) {
            $umkmList = UMKM::with('layanan')
                ->select('id', 'nama', 'jenis_umkm', 'description')
                ->limit(50)
                ->get()
                ->map(function($umkm) {
                    return [
                        'nama' => $umkm->nama,
                        'jenis' => $umkm->jenis_umkm,
                        'layanan' => $umkm->layanan->pluck('nama')->toArray()
                    ];
                });
            
            $context['umkm_list'] = $umkmList;
        }
        
        // Get all layanan names for reference
        $layananNames = Layanan::select('id', 'nama', 'description')
            ->limit(100)
            ->get()
            ->map(function($layanan) {
                return [
                    'nama' => $layanan->nama,
                    'deskripsi' => Str::limit($layanan->description ?? '', 100)
                ];
            });
        
        $context['layanan_list'] = $layananNames;
        
        return $context;
    }

    /**
     * Validate AI response and find matching layanan in database
     */
    private function validateAndFindLayanan($aiReply, $question)
    {
        // Check if question is about menu recommendation
        $isMenuRecommendation = preg_match('/\b(rekomendasi|menu|produk|makanan|minuman|layanan)\b/i', $question);
        
        if (!$isMenuRecommendation) {
            return [];
        }

        // Extract potential menu names from AI reply
        $potentialNames = $this->extractMenuNames($aiReply);
        
        if (empty($potentialNames)) {
            return [];
        }

        // Search for matching layanan in database
        $foundLayanan = [];
        
        foreach ($potentialNames as $name) {
            // Search by name (fuzzy match)
            $layanan = Layanan::where(function($q) use ($name) {
                    $q->where('nama', 'like', '%' . $name . '%')
                      ->orWhere('description', 'like', '%' . $name . '%');
                })
                ->with(['umkm.user', 'comments'])
                ->first();
            
            if ($layanan) {
                $umkm = $layanan->umkm->first();
                
                if ($umkm) {
                    // Calculate ratings
                    $ratingLayanan = Comment::where('layanan_id', $layanan->id)
                        ->avg('rating') ?? 0;
                    
                    $ratingUmkm = Comment::where('umkm_id', $umkm->id)
                        ->whereNull('layanan_id')
                        ->avg('rating') ?? 0;
                    
                    $foundLayanan[] = [
                        'id' => $layanan->id,
                        'nama' => $layanan->nama,
                        'description' => Str::limit($layanan->description ?? '', 100),
                        'price' => $layanan->price,
                        'photo_path' => $layanan->photo_path ? asset('storage/' . $layanan->photo_path) : null,
                        'umkm' => [
                            'id' => $umkm->id,
                            'nama' => $umkm->nama,
                        ],
                        'rating_layanan' => round($ratingLayanan, 1),
                        'rating_umkm' => round($ratingUmkm, 1),
                        'url' => route('public.layanan.show', $layanan->id)
                    ];
                }
            }
        }

        // If no exact match, try to find similar layanan based on keywords
        if (empty($foundLayanan)) {
            $keywords = $this->extractKeywords($question);
            $foundLayanan = $this->searchLayananByKeywords($keywords);
        }

        return $foundLayanan;
    }

    /**
     * Extract menu names from AI reply
     */
    private function extractMenuNames($text)
    {
        $names = [];
        
        // Common patterns for menu names in Indonesian
        // Look for quoted text, capitalized words, or common food terms
        preg_match_all('/["\']([^"\']+)["\']/', $text, $quoted);
        if (!empty($quoted[1])) {
            $names = array_merge($names, $quoted[1]);
        }
        
        // Look for common food patterns
        preg_match_all('/\b([A-Z][a-z]+(?:\s+[A-Z][a-z]+)*)\b/', $text, $capitalized);
        if (!empty($capitalized[1])) {
            $names = array_merge($names, array_slice($capitalized[1], 0, 5));
        }
        
        // Remove duplicates and clean
        $names = array_unique(array_map('trim', $names));
        $names = array_filter($names, function($name) {
            return strlen($name) > 2 && strlen($name) < 50;
        });
        
        return array_slice($names, 0, 5); // Limit to 5 names
    }

    /**
     * Extract keywords from question
     */
    private function extractKeywords($question)
    {
        // Remove common stop words
        $stopWords = ['yang', 'ada', 'di', 'dan', 'atau', 'dari', 'untuk', 'dengan', 'adalah', 'ini', 'itu', 'saya', 'anda'];
        $words = explode(' ', strtolower($question));
        $keywords = array_filter($words, function($word) use ($stopWords) {
            return !in_array($word, $stopWords) && strlen($word) > 2;
        });
        
        return array_values($keywords);
    }

    /**
     * Search layanan by keywords
     */
    private function searchLayananByKeywords($keywords)
    {
        if (empty($keywords)) {
            return [];
        }

        $foundLayanan = [];
        
        $query = Layanan::query();
        foreach ($keywords as $keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('nama', 'like', '%' . $keyword . '%')
                  ->orWhere('description', 'like', '%' . $keyword . '%');
            });
        }
        
        $layanan = $query->with(['umkm.user', 'comments'])
            ->limit(5)
            ->get();
        
        foreach ($layanan as $item) {
            $umkm = $item->umkm->first();
            
            if ($umkm) {
                $ratingLayanan = Comment::where('layanan_id', $item->id)
                    ->avg('rating') ?? 0;
                
                $ratingUmkm = Comment::where('umkm_id', $umkm->id)
                    ->whereNull('layanan_id')
                    ->avg('rating') ?? 0;
                
                $foundLayanan[] = [
                    'id' => $item->id,
                    'nama' => $item->nama,
                    'description' => Str::limit($item->description ?? '', 100),
                    'price' => $item->price,
                    'photo_path' => $item->photo_path ? asset('storage/' . $item->photo_path) : null,
                    'umkm' => [
                        'id' => $umkm->id,
                        'nama' => $umkm->nama,
                    ],
                    'rating_layanan' => round($ratingLayanan, 1),
                    'rating_umkm' => round($ratingUmkm, 1),
                    'url' => route('public.layanan.show', $item->id)
                ];
            }
        }
        
        return $foundLayanan;
    }
}


