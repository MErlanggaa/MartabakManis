<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'sender_id',
        'message',
        'image_path',
        'room', // 'user' = ruang pembeli-admin, 'umkm' = ruang UMKM-admin
    ];

    /**
     * Relationship with the Report.
     */
    public function report()
    {
        return $this->belongsTo(Report::class, 'report_id');
    }

    /**
     * Relationship with the Sender (User).
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
