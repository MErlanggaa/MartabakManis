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
            
            // Handle response dari API - support multiple formats
            // AI API might return: {"intent":"qa","answer":"...","umkm_list":[],"recommendations":[]}
            // Or: {"reply":"..."} or {"answer":"..."}
            // Or: {"reply":"{\"intent\":\"qa\",\"answer\":\"...\"}"} (nested JSON string)
            
            $reply = null;
            
            // First, check if data directly contains 'answer' field (most common case)
            if (isset($data['answer']) && is_string($data['answer'])) {
                $reply = $data['answer'];
            }
            // Check if data contains 'reply' field
            elseif (isset($data['reply'])) {
                $rawReply = $data['reply'];
                
                // If reply is a JSON string, parse it
                if (is_string($rawReply)) {
                    // Check if it's a JSON string
                    if (strpos(trim($rawReply), '{') === 0) {
                        $decoded = json_decode($rawReply, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            // Response is JSON, extract answer
                            $reply = $decoded['answer'] ?? $decoded['reply'] ?? $rawReply;
                        } else {
                            // Not valid JSON, use as is
                            $reply = $rawReply;
                        }
                    } else {
                        // Not JSON, use as is
                        $reply = $rawReply;
                    }
                } else {
                    // Already an array or object
                    if (is_array($rawReply)) {
                        $reply = $rawReply['answer'] ?? $rawReply['reply'] ?? json_encode($rawReply);
                    } else {
                        $reply = $rawReply;
                    }
                }
            }
            // Check if data contains 'response' field
            elseif (isset($data['response'])) {
                $reply = is_string($data['response']) ? $data['response'] : json_encode($data['response']);
            }
            // If data itself is the answer (direct structure)
            elseif (isset($data['intent']) && isset($data['answer'])) {
                $reply = $data['answer'];
            }
            
            // Fallback: if still null, try to extract from any field
            if ($reply === null) {
                // Try to find any text field
                foreach (['answer', 'reply', 'response', 'message', 'text'] as $field) {
                    if (isset($data[$field]) && is_string($data[$field])) {
                        $reply = $data[$field];
                        break;
                    }
                }
            }
            
            // Final fallback
            if ($reply === null || empty($reply)) {
                $reply = 'Maaf, saya tidak dapat menjawab saat ini.';
                Log::warning('AI Chat - Could not extract reply from response', [
                    'data_keys' => array_keys($data),
                    'data_preview' => Str::limit(json_encode($data), 200)
                ]);
            }
            
            // Clean up reply - remove JSON formatting if it's still there
            if (is_string($reply) && (strpos(trim($reply), '{"intent"') === 0 || strpos(trim($reply), '{"answer"') === 0)) {
                $decoded = json_decode($reply, true);
                if (json_last_error() === JSON_ERROR_NONE && isset($decoded['answer'])) {
                    $reply = $decoded['answer'];
                }
            }
            
            // Remove markdown formatting (** for bold)
            if (is_string($reply)) {
                $reply = preg_replace('/\*\*(.*?)\*\*/', '$1', $reply); // Remove **bold**
                $reply = preg_replace('/\*(.*?)\*/', '$1', $reply); // Remove *italic*
            }
            
            // Check if question is about "cara pemesanan" (how to order)
            $isCaraPemesanan = preg_match('/\b(cara|bagaimana|how)\s+(pesan|pemesanan|order|memesan|memesan|beli|membeli)\b/i', $question);
            if ($isCaraPemesanan) {
                $reply = "Untuk melakukan pemesanan, silakan ikuti langkah-langkah berikut:\n\n1. Klik layanan yang Anda inginkan\n2. Setelah masuk ke halaman detail layanan, Anda dapat:\n   - Klik tombol \"Hubungi Penjual\" untuk menghubungi UMKM via WhatsApp\n   - Klik tombol social media (Instagram) jika UMKM memiliki akun Instagram\n   - Klik tombol e-commerce (Shopee/Tokopedia) jika UMKM memiliki toko online\n   - Klik tombol \"Lihat Lokasi\" untuk melihat lokasi UMKM di peta\n\nPilih metode yang paling nyaman untuk Anda!";
            }

            // Validate AI response with database - check if mentioned data exists
            $validationResult = $this->validateAIResponseWithDatabase($reply, $question);
            
            // If validation failed (data mentioned but not in database), request again with corrected context
            if (!$validationResult['valid'] && !empty($validationResult['missing_data'])) {
                Log::info('AI Chat - Invalid data detected, requesting correction', [
                    'missing_data' => $validationResult['missing_data'],
                    'original_reply' => Str::limit($reply, 200)
                ]);
                
                // Request AI again with corrected database context
                $correctedReply = $this->requestCorrectedResponse($question, $validationResult['missing_data'], $databaseContext);
                
                if ($correctedReply) {
                    // Clean corrected reply too
                    if (is_string($correctedReply) && (strpos($correctedReply, '{"intent"') === 0 || strpos($correctedReply, '{"answer"') === 0)) {
                        $decoded = json_decode($correctedReply, true);
                        if (json_last_error() === JSON_ERROR_NONE && isset($decoded['answer'])) {
                            $correctedReply = $decoded['answer'];
                        }
                    }
                    $reply = $correctedReply;
                    // Re-validate the corrected response
                    $validationResult = $this->validateAIResponseWithDatabase($reply, $question);
                }
            }
            
            // Check if question is about UMKM list
            $isUmkmList = preg_match('/\b(umkm|toko|daftar|list|terdaftar|yang ada|sudah bergabung|bergabung|join)\b/i', $question);
            
            // Extract menu/layanan names from validated AI response
            $foundLayanan = [];
            $foundUmkm = [];
            
            if ($isUmkmList) {
                // Get UMKM list from database
                $foundUmkm = $this->getUmkmList($request);
            } else {
                // Get layanan recommendations
                $foundLayanan = $this->validateAndFindLayanan($reply, $question, $request);
            }
            
            // Log for debugging
            Log::info('AI Chat - Final Response', [
                'question' => $question,
                'reply_length' => strlen($reply),
                'found_layanan_count' => count($foundLayanan),
                'found_umkm_count' => count($foundUmkm),
                'found_ids' => array_column($foundLayanan, 'id'),
                'was_corrected' => isset($correctedReply)
            ]);

            return response()->json([
                'success' => true, 
                'reply' => $reply,
                'layanan' => $foundLayanan, // Send found layanan data
                'umkm' => $foundUmkm // Send found UMKM data
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
     * Validate AI response with database - check if mentioned data exists
     */
    private function validateAIResponseWithDatabase($aiReply, $question)
    {
        $isMenuRecommendation = preg_match('/\b(rekomendasi|menu|produk|makanan|minuman|layanan|berikan|tampilkan|ada|tersedia|sedia|punya)\b/i', $question);
        
        if (!$isMenuRecommendation) {
            return ['valid' => true, 'missing_data' => []];
        }

        $missingData = [];
        
        // Extract potential menu names and UMKM names from AI reply
        $potentialNames = $this->extractMenuNames($aiReply);
        $umkmNames = $this->extractUmkmNames($aiReply);
        
        // Check if mentioned menu names exist in database
        foreach ($potentialNames as $name) {
            if (empty($name) || strlen($name) < 3) continue;
            
            $cleanName = trim($name);
            
            // Check if it exists in database
            $layanan = Layanan::where(function($q) use ($cleanName) {
                    $q->where('nama', 'like', '%' . $cleanName . '%')
                      ->orWhere('description', 'like', '%' . $cleanName . '%');
                })
                ->first();
            
            // If found, check similarity to ensure it's a real match
            if ($layanan) {
                $similarity = $this->calculateSimilarity(strtolower($cleanName), strtolower($layanan->nama));
                if ($similarity < 0.5) {
                    // Similarity too low, consider it missing
                    $missingData['menu'][] = $cleanName;
                }
            } else {
                // Not found at all
                $missingData['menu'][] = $cleanName;
            }
        }
        
        // Check if mentioned UMKM names exist in database
        foreach ($umkmNames as $umkmName) {
            if (empty($umkmName) || strlen($umkmName) < 3) continue;
            
            $cleanUmkmName = trim($umkmName);
            
            // Check if it exists in database
            $umkm = UMKM::where('nama', 'like', '%' . $cleanUmkmName . '%')
                ->first();
            
            // If found, check similarity to ensure it's a real match
            if ($umkm) {
                $similarity = $this->calculateSimilarity(strtolower($cleanUmkmName), strtolower($umkm->nama));
                if ($similarity < 0.5) {
                    // Similarity too low, consider it missing
                    $missingData['umkm'][] = $cleanUmkmName;
                }
            } else {
                // Not found at all
                $missingData['umkm'][] = $cleanUmkmName;
            }
        }
        
        // If there's missing data, validation failed
        $isValid = empty($missingData);
        
        return [
            'valid' => $isValid,
            'missing_data' => $missingData
        ];
    }
    
    /**
     * Request corrected response from AI with actual database data
     */
    private function requestCorrectedResponse($originalQuestion, $missingData, $databaseContext)
    {
        try {
            // Build correction message for AI
            $correctionMessage = $this->buildCorrectionMessage($originalQuestion, $missingData, $databaseContext);
            
            // Request AI again with correction
            $response = Http::timeout(30)
                ->post('https://ai-martabakmanis-production.up.railway.app/chat', [
                    'question' => $correctionMessage,
                    'database_context' => $databaseContext,
                    'correction_mode' => true // Flag to indicate this is a correction request
                ]);

            if ($response->failed()) {
                Log::error('AI correction request failed: ' . $response->body());
                return null;
            }

            $data = $response->json();
            $correctedReply = $data['reply'] ?? $data['answer'] ?? $data['response'] ?? null;
            
            if ($correctedReply) {
                Log::info('AI Chat - Correction successful', [
                    'original_question' => $originalQuestion,
                    'missing_data' => $missingData,
                    'corrected_reply_length' => strlen($correctedReply)
                ]);
            }
            
            return $correctedReply;
        } catch (\Throwable $e) {
            Log::error('AI correction request error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Build correction message for AI
     */
    private function buildCorrectionMessage($originalQuestion, $missingData, $databaseContext)
    {
        $correctionParts = [];
        
        // Build list of actual data from database
        $actualData = [];
        
        // Get actual UMKM names from database
        if (isset($missingData['umkm']) && !empty($missingData['umkm'])) {
            $actualUmkm = UMKM::with('layanan')
                ->select('id', 'nama', 'jenis_umkm')
                ->limit(20)
                ->get()
                ->map(function($umkm) {
                    return [
                        'nama' => $umkm->nama,
                        'jenis' => $umkm->jenis_umkm,
                        'layanan' => $umkm->layanan->pluck('nama')->toArray()
                    ];
                });
            
            $actualData['umkm'] = $actualUmkm;
        }
        
        // Get actual layanan names from database
        if (isset($missingData['menu']) && !empty($missingData['menu'])) {
            $actualLayanan = Layanan::with('umkm')
                ->select('id', 'nama', 'description')
                ->limit(30)
                ->get()
                ->map(function($layanan) {
                    $umkm = $layanan->umkm->first();
                    return [
                        'nama' => $layanan->nama,
                        'deskripsi' => Str::limit($layanan->description ?? '', 100),
                        'umkm' => $umkm ? $umkm->nama : null
                    ];
                });
            
            $actualData['layanan'] = $actualLayanan;
        }
        
        // Build correction message
        $correctionMessage = "Pertanyaan sebelumnya: " . $originalQuestion . "\n\n";
        
        if (isset($missingData['umkm']) && !empty($missingData['umkm'])) {
            $correctionMessage .= "PERHATIAN: Data berikut TIDAK ADA di database: " . implode(', ', $missingData['umkm']) . "\n";
            $correctionMessage .= "Berikut adalah data UMKM yang BENAR-BENAR ADA di database:\n";
            foreach ($actualData['umkm'] as $umkm) {
                $correctionMessage .= "- " . $umkm['nama'] . " (" . $umkm['jenis'] . ")";
                if (!empty($umkm['layanan'])) {
                    $correctionMessage .= " - Layanan: " . implode(', ', array_slice($umkm['layanan'], 0, 5));
                }
                $correctionMessage .= "\n";
            }
            $correctionMessage .= "\n";
        }
        
        if (isset($missingData['menu']) && !empty($missingData['menu'])) {
            $correctionMessage .= "PERHATIAN: Menu/layanan berikut TIDAK ADA di database: " . implode(', ', $missingData['menu']) . "\n";
            $correctionMessage .= "Berikut adalah layanan yang BENAR-BENAR ADA di database:\n";
            foreach ($actualData['layanan'] as $layanan) {
                $correctionMessage .= "- " . $layanan['nama'];
                if ($layanan['umkm']) {
                    $correctionMessage .= " (dari " . $layanan['umkm'] . ")";
                }
                if ($layanan['deskripsi']) {
                    $correctionMessage .= " - " . $layanan['deskripsi'];
                }
                $correctionMessage .= "\n";
            }
            $correctionMessage .= "\n";
        }
        
        $correctionMessage .= "Silakan jawab pertanyaan dengan HANYA menggunakan data yang BENAR-BENAR ADA di database di atas. JANGAN menyebutkan data yang tidak ada di database.";
        
        return $correctionMessage;
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
     * STRICT VALIDATION: Only return data that actually exists in database
     * For recommendations: Sort by views and rating
     */
    private function validateAndFindLayanan($aiReply, $question, $request = null)
    {
        // Check if question is about menu recommendation or asking for services
        $isMenuRecommendation = preg_match('/\b(rekomendasi|menu|produk|makanan|minuman|layanan|berikan|tampilkan|ada|tersedia|sedia|punya)\b/i', $question);
        
        if (!$isMenuRecommendation) {
            return [];
        }

        // Check if question is about nearby/distance-based recommendations
        $isNearbyRecommendation = preg_match('/\b(dekat|sekitar|jarak|tidak jauh|nearby|close|distance)\b/i', $question);
        
        // Get user location if available
        $userLat = $request ? $request->input('user_lat') : null;
        $userLng = $request ? $request->input('user_lng') : null;

        // Check if this is a general recommendation request (not specific menu/UMKM)
        $isGeneralRecommendation = preg_match('/\b(rekomendasi|rekomendasikan)\b/i', $question) && 
                                   !preg_match('/\b(dari|oleh|nama|umkm|toko)\b/i', $question);

        // If general recommendation, get top layanan by views and rating (with distance if available)
        if ($isGeneralRecommendation) {
            return $this->getRecommendedLayanan($userLat, $userLng, $isNearbyRecommendation);
        }

        // Extract potential menu names from AI reply
        $potentialNames = $this->extractMenuNames($aiReply);
        
        // Also extract UMKM names mentioned in AI reply
        $umkmNames = $this->extractUmkmNames($aiReply);
        
        // Search for matching layanan in database - STRICT VALIDATION
        $foundLayanan = [];
        $foundIds = []; // Track found IDs to avoid duplicates
        
        // First, try to find by menu names - MUST EXIST IN DATABASE
        foreach ($potentialNames as $name) {
            if (empty($name) || strlen($name) < 3) continue;
            
            // Clean the name
            $cleanName = trim($name);
            
            // Search by name (fuzzy match) - but validate similarity
            $layanan = Layanan::where(function($q) use ($cleanName) {
                    $q->where('nama', 'like', '%' . $cleanName . '%')
                      ->orWhere('description', 'like', '%' . $cleanName . '%');
                })
                ->with(['umkm.user', 'comments'])
                ->get();
            
            foreach ($layanan as $item) {
                if (in_array($item->id, $foundIds)) continue;
                
                // STRICT VALIDATION: Check if the name actually matches
                // Use similarity check to ensure it's a real match
                $similarity = $this->calculateSimilarity(strtolower($cleanName), strtolower($item->nama));
                
                // Only add if similarity is high enough (at least 60% match)
                if ($similarity >= 0.6) {
                    $umkm = $item->umkm->first();
                    if ($umkm) {
                        $foundIds[] = $item->id;
                        $foundLayanan[] = $this->formatLayananData($item, $umkm);
                    }
                }
            }
        }
        
        // Second, try to find by UMKM names mentioned - MUST EXIST IN DATABASE
        foreach ($umkmNames as $umkmName) {
            if (empty($umkmName) || strlen($umkmName) < 3) continue;
            
            $cleanUmkmName = trim($umkmName);
            
            // Search for UMKM in database
            $umkm = UMKM::where('nama', 'like', '%' . $cleanUmkmName . '%')
                ->first();
            
            // STRICT VALIDATION: Only proceed if UMKM exists in database
            if ($umkm) {
                // Validate similarity to ensure it's the right UMKM
                $similarity = $this->calculateSimilarity(strtolower($cleanUmkmName), strtolower($umkm->nama));
                
                if ($similarity >= 0.6) {
                    // Get all layanan from this UMKM, sorted by views and rating
                    $layanan = $umkm->layanan()
                        ->with(['comments'])
                        ->get()
                        ->map(function($item) use ($umkm) {
                            return $this->formatLayananData($item, $umkm);
                        })
                        ->sortByDesc(function($item) {
                            // Sort by rating first, then views
                            $rating = $item['rating_layanan'] ?? 0;
                            $views = $item['views'] ?? 0;
                            return ($rating * 1000) + ($views / 100); // Rating weighted more
                        })
                        ->values();
                    
                    foreach ($layanan as $item) {
                        if (in_array($item['id'], $foundIds)) continue;
                        
                        $foundIds[] = $item['id'];
                        $foundLayanan[] = $item;
                    }
                }
            }
        }

        // If no specific match found but it's a recommendation request, get top recommendations
        if (empty($foundLayanan) && $isMenuRecommendation) {
            return $this->getRecommendedLayanan($userLat, $userLng, $isNearbyRecommendation);
        }
        
        // Log what was found for debugging
        if (!empty($foundLayanan)) {
            Log::info('AI Chat - Validated Layanan Found', [
                'potential_names' => $potentialNames,
                'umkm_names' => $umkmNames,
                'found_count' => count($foundLayanan),
                'found_names' => array_column($foundLayanan, 'nama')
            ]);
        } else {
            Log::info('AI Chat - No Validated Layanan Found', [
                'potential_names' => $potentialNames,
                'umkm_names' => $umkmNames,
                'ai_reply_preview' => Str::limit($aiReply, 200)
            ]);
        }

        // Limit to 5 results
        return array_slice($foundLayanan, 0, 5);
    }
    
    /**
     * Get recommended layanan based on views and rating
     * Supports distance-based filtering if user location is provided
     */
    private function getRecommendedLayanan($userLat = null, $userLng = null, $isNearby = false)
    {
        // Get all layanan with their UMKM
        $query = Layanan::with(['umkm.user', 'comments'])
            ->whereHas('umkm'); // Only get layanan that have UMKM
        
        // If nearby recommendation and user location is available, filter by distance
        if ($isNearby && $userLat && $userLng) {
            $maxDistance = 15; // 15 km radius
            $query->whereHas('umkm', function($q) use ($userLat, $userLng, $maxDistance) {
                $q->whereRaw("
                    (6371 * acos(cos(radians(?)) * cos(radians(latitude)) *
                    cos(radians(longitude) - radians(?)) + sin(radians(?)) *
                    sin(radians(latitude)))) <= ?
                ", [$userLat, $userLng, $userLat, $maxDistance]);
            });
        }
        
        $allLayanan = $query->get();
        
        // Calculate score for each layanan (rating + views + distance if available)
        $layananWithScore = $allLayanan->map(function($item) use ($userLat, $userLng, $isNearby) {
            $umkm = $item->umkm->first();
            if (!$umkm) {
                return null;
            }
            
            // Calculate rating
            $ratingLayanan = Comment::where('layanan_id', $item->id)
                ->avg('rating') ?? 0;
            
            // Get views
            $views = $item->views ?? 0;
            
            // Calculate distance if user location is available
            $distance = null;
            if ($userLat && $userLng && $umkm->latitude && $umkm->longitude) {
                $distance = $this->calculateDistance($userLat, $userLng, $umkm->latitude, $umkm->longitude);
            }
            
            // Calculate score: Rating weighted 70%, Views weighted 30%
            // If nearby, add distance bonus (closer = better)
            $normalizedViews = min($views, 10000) / 10000;
            $score = ($ratingLayanan * 0.7) + ($normalizedViews * 0.3);
            
            // Add distance bonus for nearby recommendations (closer = higher score)
            if ($isNearby && $distance !== null) {
                $distanceBonus = max(0, (15 - $distance) / 15) * 0.2; // Max 20% bonus for very close
                $score += $distanceBonus;
            }
            
            $formattedData = $this->formatLayananData($item, $umkm, $distance);
            
            return [
                'data' => $formattedData,
                'rating' => round($ratingLayanan, 1),
                'views' => $views,
                'distance' => $distance,
                'score' => $score
            ];
        })
        ->filter() // Remove null values
        ->sortByDesc('score') // Sort by score (rating + views + distance)
        ->take(5) // Get top 5
        ->map(function($item) {
            return $item['data']; // Return only the formatted data
        })
        ->values()
        ->toArray();
        
        Log::info('AI Chat - Recommended Layanan', [
            'count' => count($layananWithScore),
            'is_nearby' => $isNearby,
            'has_user_location' => ($userLat && $userLng),
            'layanan_names' => array_column($layananWithScore, 'nama')
        ]);
        
        return $layananWithScore;
    }
    
    /**
     * Get UMKM list for display as cards
     */
    private function getUmkmList($request)
    {
        $userLat = $request->input('user_lat');
        $userLng = $request->input('user_lng');
        
        // Get all UMKM
        $query = UMKM::with(['layanan', 'user']);
        
        // If user location is available, calculate distance
        $umkmList = $query->get()->map(function($umkm) use ($userLat, $userLng) {
            // Calculate distance if user location is available
            $distance = null;
            if ($userLat && $userLng && $umkm->latitude && $umkm->longitude) {
                $distance = $this->calculateDistance($userLat, $userLng, $umkm->latitude, $umkm->longitude);
            }
            
            // Calculate UMKM rating (from comments not tied to specific layanan)
            $ratingUmkm = Comment::where('umkm_id', $umkm->id)
                ->whereNull('layanan_id')
                ->avg('rating') ?? 0;
            
            return [
                'id' => $umkm->id,
                'nama' => $umkm->nama,
                'photo_path' => $umkm->photo_path ? asset('storage/' . $umkm->photo_path) : null,
                'latitude' => $umkm->latitude,
                'longitude' => $umkm->longitude,
                'distance' => $distance ? round($distance, 2) : null,
                'rating_umkm' => round($ratingUmkm, 1),
                'url' => route('public.umkm.show', $umkm->id)
            ];
        })->toArray();
        
        Log::info('AI Chat - UMKM List', [
            'count' => count($umkmList),
            'has_user_location' => ($userLat && $userLng)
        ]);
        
        return $umkmList;
    }
    
    /**
     * Calculate distance between two coordinates (Haversine formula)
     */
    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371; // Earth's radius in kilometers
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;
        
        return $distance;
    }
    
    /**
     * Calculate similarity between two strings (0-1)
     * Uses Levenshtein distance
     */
    private function calculateSimilarity($str1, $str2)
    {
        $str1 = strtolower(trim($str1));
        $str2 = strtolower(trim($str2));
        
        if (empty($str1) || empty($str2)) {
            return 0;
        }
        
        // If exact match or one contains the other
        if ($str1 === $str2) {
            return 1.0;
        }
        
        if (strpos($str2, $str1) !== false || strpos($str1, $str2) !== false) {
            return 0.8;
        }
        
        // Calculate Levenshtein distance
        $maxLen = max(strlen($str1), strlen($str2));
        if ($maxLen == 0) {
            return 1.0;
        }
        
        $distance = levenshtein($str1, $str2);
        $similarity = 1 - ($distance / $maxLen);
        
        return max(0, $similarity);
    }
    
    /**
     * Format layanan data for response
     */
    private function formatLayananData($layanan, $umkm, $distance = null)
    {
        // Calculate ratings
        $ratingLayanan = Comment::where('layanan_id', $layanan->id)
            ->avg('rating') ?? 0;
        
        $ratingUmkm = Comment::where('umkm_id', $umkm->id)
            ->whereNull('layanan_id')
            ->avg('rating') ?? 0;
        
        return [
            'id' => $layanan->id,
            'nama' => $layanan->nama,
            'description' => Str::limit($layanan->description ?? '', 100),
            'price' => $layanan->price,
            'photo_path' => $layanan->photo_path ? asset('storage/' . $layanan->photo_path) : null,
            'views' => $layanan->views ?? 0,
            'umkm' => [
                'id' => $umkm->id,
                'nama' => $umkm->nama,
                'latitude' => $umkm->latitude,
                'longitude' => $umkm->longitude,
            ],
            'rating_layanan' => round($ratingLayanan, 1),
            'rating_umkm' => round($ratingUmkm, 1),
            'distance' => $distance ? round($distance, 2) : null,
            'url' => route('public.layanan.show', $layanan->id)
        ];
    }
    
    /**
     * Extract UMKM names from text
     */
    private function extractUmkmNames($text)
    {
        $names = [];
        
        // Look for patterns like "dari [UMKM Name]", "oleh [UMKM Name]", "[UMKM Name] yang"
        preg_match_all('/\b(dari|oleh|di|pada)\s+([A-Z][a-zA-Z\s]+?)(?:\s+(?:yang|menyediakan|menawarkan|menjual)|[.,]|$)/i', $text, $matches);
        if (!empty($matches[2])) {
            $names = array_merge($names, array_map('trim', $matches[2]));
        }
        
        // Look for quoted UMKM names
        preg_match_all('/["\']([^"\']+?)\s+(?:menyediakan|menawarkan|menjual)/i', $text, $quoted);
        if (!empty($quoted[1])) {
            $names = array_merge($names, array_map('trim', $quoted[1]));
        }
        
        // Look for patterns like "[UMKM Name] menawarkan", "[UMKM Name] menyediakan"
        preg_match_all('/\b([A-Z][a-zA-Z\s]{2,30}?)\s+(?:menawarkan|menyediakan|menjual|yang)/i', $text, $offerMatches);
        if (!empty($offerMatches[1])) {
            $names = array_merge($names, array_map('trim', $offerMatches[1]));
        }
        
        // Look for capitalized words that might be UMKM names (like "AA Snack", "Banagood")
        // Pattern: 2-3 words starting with capital letters
        preg_match_all('/\b([A-Z][A-Za-z]+\s+[A-Z][A-Za-z]+(?:\s+[A-Z][A-Za-z]+)?)\b/', $text, $capitalized);
        if (!empty($capitalized[1])) {
            // Filter out common non-UMKM words
            $excludeWords = ['Yang', 'Dengan', 'Harga', 'Rp', 'Per', 'Pcs', 'Kemasan', 'Tersedia', 'Juga', 'Selain', 'Itu', 'Berdasarkan', 'Informasi'];
            $filtered = array_filter($capitalized[1], function($name) use ($excludeWords) {
                $nameWords = explode(' ', $name);
                foreach ($nameWords as $word) {
                    if (in_array($word, $excludeWords)) {
                        return false;
                    }
                }
                return strlen($name) > 3 && strlen($name) < 40;
            });
            $names = array_merge($names, array_slice($filtered, 0, 5));
        }
        
        // Remove duplicates and clean
        $names = array_unique(array_map('trim', $names));
        $names = array_filter($names, function($name) {
            // Filter out common non-UMKM words
            $exclude = ['yang', 'dengan', 'harga', 'seharga', 'per', 'pcs', 'kemasan', 'tersedia', 'juga', 'selain', 'itu', 'berdasarkan', 'informasi', 'kami', 'memiliki'];
            $nameLower = strtolower($name);
            foreach ($exclude as $word) {
                if ($nameLower === $word || strpos($nameLower, $word . ' ') === 0) {
                    return false;
                }
            }
            return strlen($name) > 2 && strlen($name) < 50;
        });
        
        return array_slice($names, 0, 5);
    }
    
    /**
     * Extract common food terms from text
     */
    private function extractFoodTerms($text)
    {
        $commonFoodTerms = [
            'snack', 'camilan', 'makanan', 'minuman', 'bento', 'nasi', 'pisang', 
            'keripik', 'kue', 'roti', 'bakso', 'mie', 'ayam', 'ikan', 'daging',
            'sayur', 'buah', 'jus', 'kopi', 'teh', 'es', 'goreng', 'rebus', 'panggang'
        ];
        
        $foundTerms = [];
        $textLower = strtolower($text);
        
        foreach ($commonFoodTerms as $term) {
            if (stripos($textLower, $term) !== false) {
                $foundTerms[] = $term;
            }
        }
        
        return $foundTerms;
    }

    /**
     * Extract menu names from AI reply
     */
    private function extractMenuNames($text)
    {
        $names = [];
        
        // Common patterns for menu names in Indonesian
        // Look for quoted text
        preg_match_all('/["\']([^"\']+)["\']/', $text, $quoted);
        if (!empty($quoted[1])) {
            $names = array_merge($names, $quoted[1]);
        }
        
        // Look for patterns like "menu [Name]", "produk [Name]", "[Name] dengan harga"
        preg_match_all('/\b(menu|produk|camilan|makanan|minuman|layanan)\s+([A-Z][a-zA-Z\s]+?)(?:\s+(?:dengan|seharga|harga|yang)|[.,]|$)/i', $text, $menuMatches);
        if (!empty($menuMatches[2])) {
            $names = array_merge($names, array_map('trim', $menuMatches[2]));
        }
        
        // Look for capitalized words (potential menu names)
        preg_match_all('/\b([A-Z][a-z]+(?:\s+[A-Z][a-z]+){0,3})\b/', $text, $capitalized);
        if (!empty($capitalized[1])) {
            // Filter out common words that are not menu names
            $excludeWords = ['UMKM', 'Snack', 'Bento', 'Yang', 'Dengan', 'Harga', 'Rp', 'Per', 'Pcs', 'Kemasan'];
            $filtered = array_filter($capitalized[1], function($name) use ($excludeWords) {
                return !in_array($name, $excludeWords) && strlen($name) > 3;
            });
            $names = array_merge($names, array_slice($filtered, 0, 5));
        }
        
        // Look for patterns after "menawarkan", "menyediakan", "menjual"
        preg_match_all('/\b(menawarkan|menyediakan|menjual)\s+([a-zA-Z\s]+?)(?:\s+(?:dengan|seharga|harga|yang)|[.,]|$)/i', $text, $offerMatches);
        if (!empty($offerMatches[2])) {
            $names = array_merge($names, array_map('trim', $offerMatches[2]));
        }
        
        // Remove duplicates and clean
        $names = array_unique(array_map('trim', $names));
        $names = array_filter($names, function($name) {
            // Filter out common non-menu words
            $exclude = ['yang', 'dengan', 'harga', 'seharga', 'per', 'pcs', 'kemasan', 'tersedia', 'juga', 'selain', 'itu'];
            $nameLower = strtolower($name);
            foreach ($exclude as $word) {
                if ($nameLower === $word || strpos($nameLower, $word . ' ') === 0) {
                    return false;
                }
            }
            return strlen($name) > 2 && strlen($name) < 50;
        });
        
        return array_slice($names, 0, 10); // Increase limit to 10
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


