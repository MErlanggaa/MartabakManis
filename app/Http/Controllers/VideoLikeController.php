<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\VideoLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VideoLikeController extends Controller
{
    public function toggle(Request $request, Video $video)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Please login first'], 401);
        }

        $userId = Auth::id();
        $like = VideoLike::where('video_id', $video->id)
            ->where('user_id', $userId)
            ->first();

        if ($like) {
            $like->delete();
            $liked = false;
        } else {
            VideoLike::create([
                'video_id' => $video->id,
                'user_id' => $userId,
            ]);
            $liked = true;
        }

        return response()->json([
            'success' => true,
            'liked' => $liked,
            'likes_count' => $video->likes()->count()
        ]);
    }
}
