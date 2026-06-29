<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\MediaCompressionService;

class OrderRatingController extends Controller
{
    public function show($orderId)
    {
        $order = Order::findOrFail($orderId);

        // Only user who owns the order or UMKM can see
        $user = Auth::user();
        if ($user->role === 'user' && $order->user_id !== $user->id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $rating = $order->rating;
        if (!$rating) {
            return response()->json(['rating' => null]);
        }

        return response()->json([
            'rating' => [
                'id' => $rating->id,
                'rating' => $rating->rating,
                'review' => $rating->review,
                'photos' => collect($rating->photos ?? [])->map(fn($p) => Storage::url($p))->values(),
                'created_at' => $rating->created_at?->format('d M Y H:i'),
                'user_name' => $rating->user?->name,
            ],
        ]);
    }

    public function store(Request $request, $orderId)
    {
        $user = Auth::user();
        $order = Order::where('id', $orderId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($order->order_status !== 'delivered') {
            return response()->json(['success' => false, 'message' => 'Pesanan belum selesai dikirim.'], 422);
        }

        if ($order->rating) {
            return response()->json(['success' => false, 'message' => 'Anda sudah memberikan rating untuk pesanan ini.'], 422);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
            'photos' => 'nullable|array|max:3',
            'photos.*' => 'file|image|max:5120', // 5MB each
        ]);

        $photoPaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $photoPaths[] = MediaCompressionService::compressAndStoreImage($photo, 'order-ratings');
            }
        }

        // 1. Simpan ke order_ratings (untuk detail pesanan)
        OrderRating::create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'layanan_id' => $order->layanan_id,
            'rating' => $validated['rating'],
            'review' => $validated['review'] ?? null,
            'photos' => $photoPaths,
        ]);

        // 2. Otomatis buat/update Comment untuk LAYANAN (menu)
        //    supaya rating muncul di halaman detail menu & katalog
        if ($order->layanan_id) {
            \App\Models\Comment::updateOrCreate(
                [
                    'user_id'    => $user->id,
                    'umkm_id'    => $order->umkm_id,
                    'layanan_id' => $order->layanan_id,
                ],
                [
                    'comment' => $validated['review'] ?? 'Ulasan dari pesanan #' . $order->order_code,
                    'rating'  => $validated['rating'],
                ]
            );
        }

        // 3. Otomatis buat/update Comment untuk UMKM (toko)
        //    supaya rating muncul di halaman detail toko
        $umkmComment = \App\Models\Comment::where('user_id', $user->id)
            ->where('umkm_id', $order->umkm_id)
            ->whereNull('layanan_id')
            ->first();

        if ($umkmComment) {
            $umkmComment->update([
                'comment' => $validated['review'] ?? 'Ulasan toko dari pesanan #' . $order->order_code,
                'rating'  => $validated['rating'],
            ]);
        } else {
            \App\Models\Comment::create([
                'user_id'    => $user->id,
                'umkm_id'    => $order->umkm_id,
                'layanan_id' => null,
                'comment'    => $validated['review'] ?? 'Ulasan toko dari pesanan #' . $order->order_code,
                'rating'     => $validated['rating'],
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Rating berhasil dikirim!']);
    }
}
