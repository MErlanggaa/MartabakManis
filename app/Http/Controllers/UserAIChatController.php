<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
            $response = Http::timeout(30)
                ->post('https://ai-martabakmanis-production.up.railway.app/chat', [
                    'question' => $question
                ]);

            if ($response->failed()) {
                Log::error('AI chat API error: ' . $response->body());
                return response()->json([
                    'success' => false, 
                    'message' => 'AI service error. Silakan coba lagi nanti.'
                ], 502);
            }

            $data = $response->json();
            
            // Handle response dari API Anda
            // Sesuaikan dengan format response yang dikembalikan API Anda
            $reply = $data['reply'] ?? $data['answer'] ?? $data['response'] ?? 'Maaf, saya tidak dapat menjawab saat ini.';

            return response()->json([
                'success' => true, 
                'reply' => $reply
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
}


