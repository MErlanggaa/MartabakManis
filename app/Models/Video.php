<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $table = 'videos';

    protected $fillable = [
        'umkm_id',
        'caption',
        'video_path',
        'thumbnail_path',
        'views',
    ];

    public function umkm()
    {
        return $this->belongsTo(UMKM::class, 'umkm_id');
    }

    public function products()
    {
        return $this->belongsToMany(Layanan::class, 'video_products', 'video_id', 'layanan_id');
    }

    public function likes()
    {
        return $this->hasMany(VideoLike::class);
    }

    public function comments()
    {
        return $this->hasMany(VideoComment::class)->latest();
    }

    public function isLikedBy($userId)
    {
        return $this->likes()->where('user_id', $userId)->exists();
    }
}
