<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class AIConsultationController extends Controller
{
    private $huggingFaceApiKey;
    private $openaiApiKey;

    public function __construct()
    {
        $this->huggingFaceApiKey = config('services.huggingface.api_key');
        $this->openaiApiKey = config('services.openai.api_key');
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
        
        // Try different AI services
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
        $context = "Anda adalah konsultan bisnis AI yang membantu UMKM. ";
        $context .= "UMKM ini bernama '{$umkm->nama}', jenis bisnis: {$umkm->jenis_umkm}. ";
        $context .= "Deskripsi: {$umkm->description}. ";
        
        // Add financial context if available
        $keuntungan = $umkm->keuntungan()->latest()->first();
        if ($keuntungan) {
            $context .= "Data keuangan terbaru: Pendapatan Rp " . number_format($keuntungan->pendapatan) . 
                       ", Pengeluaran Rp " . number_format($keuntungan->pengeluaran) . 
                       ", Keuntungan bersih Rp " . number_format($keuntungan->keuntungan_bersih) . 
                       ", Jumlah transaksi: {$keuntungan->jumlah_transaksi}. ";
        }
        
        $context .= "Pertanyaan user: {$userMessage}. ";
        $context .= "Berikan saran yang praktis dan dapat diterapkan untuk UMKM ini. ";
        $context .= "Fokus pada peningkatan penjualan, efisiensi operasional, dan strategi digital marketing.";
        
        return $context;
    }

    private function getAIResponse($context, $userMessage)
    {
        // Try OpenAI first
        if ($this->openaiApiKey) {
            $response = $this->tryOpenAI($context, $userMessage);
            if ($response['success']) {
                return $response;
            }
        }

        // Fallback to Hugging Face
        if ($this->huggingFaceApiKey) {
            $response = $this->tryHuggingFace($context, $userMessage);
            if ($response['success']) {
                return $response;
            }
        }

        // Fallback to simple rule-based response
        return $this->getFallbackResponse($userMessage);
    }

    private function tryOpenAI($context, $userMessage)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->openaiApiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $context
                    ],
                    [
                        'role' => 'user',
                        'content' => $userMessage
                    ]
                ],
                'max_tokens' => 500,
                'temperature' => 0.7
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'message' => $data['choices'][0]['message']['content']
                ];
            }
        } catch (\Exception $e) {
            \Log::error('OpenAI API Error: ' . $e->getMessage());
        }

        return ['success' => false];
    }

    private function tryHuggingFace($context, $userMessage)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->huggingFaceApiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post('https://api-inference.huggingface.co/models/microsoft/DialoGPT-medium', [
                'inputs' => $context . ' ' . $userMessage,
                'parameters' => [
                    'max_length' => 200,
                    'temperature' => 0.7
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data[0]['generated_text'])) {
                    return [
                        'success' => true,
                        'message' => $data[0]['generated_text']
                    ];
                }
            }
        } catch (\Exception $e) {
            \Log::error('Hugging Face API Error: ' . $e->getMessage());
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