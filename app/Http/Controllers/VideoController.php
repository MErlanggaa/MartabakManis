<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\Layanan;
use App\Models\UMKM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class VideoController extends Controller
{
        public function index()
        {
            $videos = Video::with(['umkm', 'products'])
                ->withCount(['likes', 'comments'])
                ->inRandomOrder()
                ->get();
            return view('videos.index', compact('videos'));
        }

    public function create()
    {   
        // Ensure user has UMKM
        $umkm = Auth::user()->umkm;
        if (!$umkm) {
            return redirect()->route('umkm.create')->with('error', 'Buat toko terlebih dahulu.');
        }
        
        $products = $umkm->layanan; // Get umkm products
        return view('umkm.upload-video', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'video' => 'required|mimes:mp4,mov,ogg,qt|max:20000', // 20MB max
            'caption' => 'nullable|string|max:500',
            'product_id' => 'nullable|exists:layanan,id',
        ]);

        $umkm = Auth::user()->umkm;
        
        // Upload Video
        $videoPath = $request->file('video')->store('videos', 'public');
        
        // Generate Thumbnail (Optional - simplistic approach used here, better to use FFMpeg in real prod)
        // For now, we will just use a placeholder or no thumbnail logic if complex setup not requested.
        // We leave thumbnail_path null for now.

        $video = Video::create([
            'umkm_id' => $umkm->id,
            'caption' => $request->caption,
            'video_path' => $videoPath,
            // 'thumbnail_path' => $thumbnailPath,
        ]);

        if ($request->product_id) {
            $video->products()->attach($request->product_id);
        }

        return redirect()->route('umkm.dashboard')->with('success', 'Video berhasil diupload!');
    }

    public function show(Video $video)
    {
        // Increment views only once per session
        $sessionKey = 'video_viewed_' . $video->id;
        if (!session()->has($sessionKey)) {
            $video->increment('views');
            session()->put($sessionKey, true);
        }
        
        return view('videos.show', compact('video'));
    }

    public function incrementView(Request $request, Video $video)
    {
        // Count every view, not just once per session
        $video->increment('views');
        return response()->json(['success' => true, 'views' => $video->views]);
    }
}
