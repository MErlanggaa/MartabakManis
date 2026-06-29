<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderChat extends Model
{
    protected $fillable = [
        'order_id',
        'sender_type',
        'sender_id',
        'message',
        'is_read',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Scope for unread messages by a given receiver type.
     */
    public function scopeUnreadFor($query, string $receiverType)
    {
        return $query->where('sender_type', '!=', $receiverType)->where('is_read', false);
    }
}
