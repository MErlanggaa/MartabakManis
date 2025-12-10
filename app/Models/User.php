<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'favorites',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'favorites' => 'array',
        ];
    }

    // Relasi dengan UMKM
    public function umkm()
    {
        return $this->hasOne(UMKM::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    // Relasi Following UMKM
    public function following()
    {
        return $this->belongsToMany(UMKM::class, 'followers', 'user_id', 'umkm_id')->withTimestamps();
    }

    // Relasi dengan Comments
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    // Relasi Video yang disukai
    public function likedVideos()
    {
        return $this->belongsToMany(Video::class, 'video_likes', 'user_id', 'video_id')->withTimestamps();
    }
}
