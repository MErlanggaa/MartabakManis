<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nama',
        'email',
        'kategori',
        'judul',
        'deskripsi',
        'status',
        'respon_admin',
        'admin_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
        ];
    }

    // Relasi dengan User yang membuat laporan
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi dengan Admin (User dengan role admin)
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    // Scope untuk filter berdasarkan user_id (untuk history user)
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Scope untuk filter berdasarkan email (untuk history user - backward compatibility)
    public function scopeByEmail($query, $email)
    {
        return $query->where('email', $email);
    }

    // Helper untuk label kategori
    public function getKategoriLabelAttribute()
    {
        $labels = [
            'bug' => 'Bug / Error',
            'fitur' => 'Saran Fitur Baru',
            'pertanyaan' => 'Pertanyaan',
            'lainnya' => 'Lainnya'
        ];
        
        return $labels[$this->kategori] ?? $this->kategori;
    }

    // Helper untuk label status
    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'Menunggu',
            'diproses' => 'Diproses',
            'selesai' => 'Selesai'
        ];
        
        return $labels[$this->status] ?? $this->status;
    }

    // Helper untuk warna status
    public function getStatusColorAttribute()
    {
        $colors = [
            'pending' => 'yellow',
            'diproses' => 'blue',
            'selesai' => 'green'
        ];
        
        return $colors[$this->status] ?? 'gray';
    }
}
