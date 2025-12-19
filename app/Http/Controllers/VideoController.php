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
        // Increment views
        $video->increment('views');
        
        // Handle "Liked Videos" feed context
        if (request('source') === 'liked' && Auth::check()) {
            // Get all liked videos ordered by latest like
            $likedVideos = Auth::user()->likedVideos()
                ->with(['umkm', 'products', 'likes', 'comments'])
                ->withCount(['likes', 'comments'])
                ->orderByPivot('created_at', 'desc')
                ->get();

            // Reorder collection: Put the current video first, then the rest
            // This ensures the user lands on the clicked video but can scroll to others
            $videos = $likedVideos->reject(function ($v) use ($video) {
                return $v->id === $video->id; // Remove current video from list
            })->prepend($video); // Add it back to the beginning
            
            // Ensure the relationships are loaded on the prepended video instance too
            $video->load(['umkm', 'products', 'likes', 'comments']);
            $video->loadCount(['likes', 'comments']);
            
        } else {
            // Default behavior: Show only this video
            $videos = collect([$video]);
            $video->load(['umkm', 'products', 'likes', 'comments']);
            $video->loadCount(['likes', 'comments']);
        }
        
        return view('videos.index', compact('videos'));
    }

    public function incrementView(Request $request, Video $video)
    {
        // Count every view, not just once per session
        $video->increment('views');
        return response()->json(['success' => true, 'views' => $video->views]);
    }
    public function edit(Video $video)
    {
        // Check ownership
        if ($video->umkm_id !== Auth::user()->umkm->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        // Load products relationship to check selected product
        $video->load('products');
        return response()->json([
            'id' => $video->id,
            'caption' => $video->caption,
            'product_id' => $video->products->first() ? $video->products->first()->id : null
        ]);
    }

    public function update(Request $request, Video $video)
    {
        // Check ownership
        if ($video->umkm_id !== Auth::user()->umkm->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'caption' => 'nullable|string|max:500',
            'product_id' => 'nullable|exists:layanan,id',
        ]);

        $video->update([
            'caption' => $request->caption,
        ]);

        if ($request->has('product_id')) {
            $video->products()->sync($request->product_id ? [$request->product_id] : []);
        }

        return response()->json(['success' => true, 'message' => 'Video berhasil diperbarui!']);
    }

    public function destroy(Video $video)
    {
        // Check ownership
        if ($video->umkm_id !== Auth::user()->umkm->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Delete file
        if ($video->video_path && Storage::disk('public')->exists($video->video_path)) {
            Storage::disk('public')->delete($video->video_path);
        }
        // If you had Playback ID or similar (e.g. Mux), delete it here too.

        $video->delete();

        return response()->json(['success' => true, 'message' => 'Video berhasil dihapus!']);
    }
}