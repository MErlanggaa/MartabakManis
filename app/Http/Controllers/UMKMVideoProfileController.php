<?php

namespace App\Http\Controllers;

use App\Models\UMKM;
use App\Models\Video;
use Illuminate\Http\Request;

class UMKMVideoProfileController extends Controller
{
    public function show($id)
    {
        $umkm = UMKM::with(['videos' => function($query) {
            $query->withCount(['likes', 'comments'])->latest();
        }])->findOrFail($id);
        
        // Calculate stats
        $totalVideos = $umkm->videos->count();
        $totalFollowers = $umkm->followers()->count();
        $totalLikes = $umkm->videos->sum(function($video) {
            return $video->likes()->count();
        });
        $totalViews = $umkm->videos->sum('views');
        
        return view('videos.umkm-profile', compact('umkm', 'totalVideos', 'totalFollowers', 'totalLikes', 'totalViews'));
    }
}
