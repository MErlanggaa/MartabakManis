<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class AIConsultationController extends Controller
{
    private $geminiApiKey;

    public function __construct()
    {
        // Gemini API Key
        $this->geminiApiKey = 'AIzaSyCuvo30wJaTFgwVWvY88_xEOWrkeK5aQz4';
    }

    public function index()
    {
        return view('umkm.ai-consultation');
    }

    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $user = Auth::user();
        $umkm = $user->umkm;
        
        if (!$umkm) {
            return response()->json([
                'success' => false,
                'message' => 'Profil UMKM belum lengkap'
            ], 400);
        }

        // Prepare context for AI
        $context = $this->buildContext($umkm, $request->message);
        
        // Get AI response from Gemini
        $response = $this->getAIResponse($context, $request->message);
        
        if ($response['success']) {
            return response()->json([
                'success' => true,
                'response' => $response['message'],
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Maaf, layanan AI sedang tidak tersedia. Silakan coba lagi nanti.'
            ], 500);
        }
    }

    private function buildContext($umkm, $userMessage)
    {
        // Build comprehensive context for professional consultant
        $context = "Anda adalah seorang Konsultan Bisnis Profesional yang berpengalaman dalam membantu UMKM (Usaha Mikro, Kecil, dan Menengah) di Indonesia. ";
        $context .= "Anda memiliki keahlian dalam strategi bisnis, pemasaran digital, manajemen keuangan, operasional, dan pengembangan bisnis. ";
        $context .= "Gaya komunikasi Anda jelas, praktis, mudah dipahami, dan memberikan solusi yang dapat langsung diterapkan.\n\n";
        
        $context .= "INFORMASI UMKM:\n";
        $context .= "- Nama UMKM: {$umkm->nama}\n";
        $context .= "- Jenis Bisnis: {$umkm->jenis_umkm}\n";
        $context .= "- Deskripsi: " . ($umkm->description ?? 'Tidak ada deskripsi') . "\n";
        
        // Add financial context if available
        $keuntungan = $umkm->keuntungan()->latest()->first();
        if ($keuntungan) {
            $context .= "\nDATA KEUANGAN TERBARU:\n";
            $context .= "- Pendapatan: Rp " . number_format($keuntungan->pendapatan, 0, ',', '.') . "\n";
            $context .= "- Pengeluaran: Rp " . number_format($keuntungan->pengeluaran, 0, ',', '.') . "\n";
            $context .= "- Keuntungan Bersih: Rp " . number_format($keuntungan->keuntungan_bersih, 0, ',', '.') . "\n";
            $context .= "- Jumlah Transaksi: {$keuntungan->jumlah_transaksi}\n";
        }
        
        $context .= "\nPERTANYAAN DARI PEMILIK UMKM:\n";
        $context .= "{$userMessage}\n\n";
        
        $context .= "INSTRUKSI UNTUK ANDA:\n";
        $context .= "1. Berikan jawaban yang profesional, praktis, dan mudah dipahami oleh pemilik UMKM\n";
        $context .= "2. Fokus pada solusi yang dapat langsung diterapkan dengan sumber daya terbatas\n";
        $context .= "3. Berikan saran yang spesifik dan relevan dengan jenis bisnis dan kondisi UMKM ini\n";
        $context .= "4. Jika memungkinkan, berikan langkah-langkah konkret yang dapat diikuti\n";
        $context .= "5. Gunakan bahasa Indonesia yang jelas dan ramah\n";
        $context .= "6. Jika pertanyaan tentang keuangan, gunakan data keuangan yang tersedia untuk analisis\n";
        $context .= "7. Berikan motivasi dan dukungan positif untuk pemilik UMKM\n";
        $context .= "8. Jika perlu, ajukan pertanyaan klarifikasi untuk memberikan saran yang lebih tepat\n\n";
        
        $context .= "Jawablah dengan format yang rapi, gunakan poin-poin jika perlu, dan pastikan jawaban Anda membantu pemilik UMKM memahami dan menerapkan saran Anda.";
        
        return $context;
    }

    private function getAIResponse($context, $userMessage)
    {
        // Use Gemini API
        $response = $this->tryGemini($context, $userMessage);
        if ($response['success']) {
            return $response;
        }

        // Fallback to simple rule-based response
        return $this->getFallbackResponse($userMessage);
    }

    private function tryGemini($context, $userMessage)
    {
        try {
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=' . $this->geminiApiKey;
            
            $response = Http::timeout(60)->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => $context
                            ]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 2048,
                ],
                'safetySettings' => [
                    [
                        'category' => 'HARM_CATEGORY_HARASSMENT',
                        'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                    ],
                    [
                        'category' => 'HARM_CATEGORY_HATE_SPEECH',
                        'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                    ],
                    [
                        'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                        'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                    ],
                    [
                        'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                        'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                    ]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Extract response from Gemini API
                if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                    $message = $data['candidates'][0]['content']['parts'][0]['text'];
                    
                    // Clean up the response
                    $message = trim($message);
                    
                    return [
                        'success' => true,
                        'message' => $message
                    ];
                } else {
                    \Log::error('Gemini API - Unexpected response structure: ' . json_encode($data));
                }
            } else {
                \Log::error('Gemini API Error: ' . $response->body());
            }
        } catch (\Exception $e) {
            \Log::error('Gemini API Exception: ' . $e->getMessage());
        }

        return ['success' => false];
    }

    private function getFallbackResponse($userMessage)
    {
        $keywords = [
            'penjualan' => 'Untuk meningkatkan penjualan, coba fokus pada digital marketing, promosi di media sosial, dan meningkatkan kualitas produk.',
            'keuntungan' => 'Untuk meningkatkan keuntungan, analisis biaya operasional, optimasi proses produksi, dan pertimbangkan harga yang kompetitif.',
            'pemasaran' => 'Strategi pemasaran yang efektif: gunakan media sosial, buat konten menarik, dan jalin relasi dengan pelanggan.',
            'digital' => 'Transformasi digital penting untuk UMKM. Mulai dengan website sederhana, media sosial, dan sistem pembayaran online.',
            'modal' => 'Untuk mendapatkan modal, pertimbangkan pinjaman bank, investor, atau crowdfunding. Siapkan proposal bisnis yang solid.',
            'karyawan' => 'Kelola karyawan dengan baik: berikan pelatihan, insentif yang adil, dan ciptakan lingkungan kerja yang positif.',
            'lokasi' => 'Lokasi strategis penting untuk UMKM. Pertimbangkan area dengan lalu lintas tinggi atau dekat target pasar.',
            'produk' => 'Fokus pada kualitas produk, inovasi, dan diferensiasi dari kompetitor. Dengarkan feedback pelanggan.',
        ];

        $message = strtolower($userMessage);
        foreach ($keywords as $keyword => $response) {
            if (strpos($message, $keyword) !== false) {
                return [
                    'success' => true,
                    'message' => $response
                ];
            }
        }

        return [
            'success' => true,
            'message' => 'Terima kasih atas pertanyaannya. Sebagai konsultan bisnis, saya sarankan untuk fokus pada kualitas produk, pelayanan pelanggan, dan strategi pemasaran yang tepat. Jika ada pertanyaan spesifik, silakan tanyakan lebih detail.'
        ];
    }

    public function getBusinessTips()
    {
        $tips = [
            "Fokus pada kualitas produk dan pelayanan pelanggan yang excellent",
            "Manfaatkan media sosial untuk promosi dan engagement dengan pelanggan",
            "Analisis kompetitor dan temukan keunggulan unik bisnis Anda",
            "Kelola keuangan dengan baik, pisahkan modal dan keuntungan",
            "Bangun relasi dengan supplier dan partner bisnis yang terpercaya",
            "Investasi dalam teknologi yang dapat meningkatkan efisiensi",
            "Dengarkan feedback pelanggan dan terus berinovasi",
            "Buat sistem operasional yang terstruktur dan dapat diukur",
            "Manfaatkan data untuk pengambilan keputusan bisnis",
            "Jangan takut untuk mencoba strategi pemasaran baru"
        ];

        return response()->json([
            'success' => true,
            'tips' => $tips
        ]);
    }
}