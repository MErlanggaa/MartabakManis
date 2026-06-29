<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderChat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderChatController extends Controller
{
    public function index($orderId)
    {
        $user = Auth::user();
        $order = $this->getAuthorizedOrder($orderId, $user);

        if (!$order) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $chats = $order->chats()->get()->map(fn($chat) => [
            'id' => $chat->id,
            'sender_type' => $chat->sender_type,
            'message' => $chat->message,
            'is_read' => $chat->is_read,
            'created_at' => $chat->created_at?->format('H:i'),
            'created_at_full' => $chat->created_at?->format('d M Y H:i'),
        ]);

        return response()->json(['chats' => $chats]);
    }

    public function store(Request $request, $orderId)
    {
        $user = Auth::user();
        $order = $this->getAuthorizedOrder($orderId, $user);

        if (!$order) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $senderType = ($user->role === 'umkm') ? 'umkm' : 'user';

        $chat = OrderChat::create([
            'order_id' => $order->id,
            'sender_type' => $senderType,
            'sender_id' => $user->id,
            'message' => $validated['message'],
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'chat' => [
                'id' => $chat->id,
                'sender_type' => $chat->sender_type,
                'message' => $chat->message,
                'is_read' => $chat->is_read,
                'created_at' => $chat->created_at?->format('H:i'),
                'created_at_full' => $chat->created_at?->format('d M Y H:i'),
            ],
        ]);
    }

    public function markRead(Request $request, $orderId)
    {
        $user = Auth::user();
        $order = $this->getAuthorizedOrder($orderId, $user);

        if (!$order) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $receiverType = ($user->role === 'umkm') ? 'umkm' : 'user';

        // Mark all messages NOT sent by current role as read
        OrderChat::where('order_id', $order->id)
            ->where('sender_type', '!=', $receiverType)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * Get order if user is authorized to access it (owner or UMKM that owns the order).
     */
    private function getAuthorizedOrder($orderId, $user): ?Order
    {
        $order = Order::find($orderId);
        if (!$order) return null;

        if ($user->role === 'user' && $order->user_id === $user->id) {
            return $order;
        }

        if ($user->role === 'umkm' && $user->umkm?->id === $order->umkm_id) {
            return $order;
        }

        return null;
    }
}
