<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\VideoComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VideoCommentController extends Controller
{
    public function store(Request $request, Video $video)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Please login first'], 401);
        }

        $request->validate([
            'comment' => 'required|string|max:500',
            'parent_id' => 'nullable|exists:video_comments,id'
        ]);

        $comment = VideoComment::create([
            'video_id' => $video->id,
            'user_id' => Auth::id(),
            'parent_id' => $request->parent_id,
            'comment' => $request->comment,
        ]);

        $comment->load('user', 'replies');

        return response()->json([
            'success' => true,
            'comment' => $comment,
            'comments_count' => $video->comments()->count()
        ]);
    }

    public function index(Video $video)
    {
        $comments = $video->comments()
            ->whereNull('parent_id')
            ->with(['user', 'replies.user'])
            ->latest()
            ->get();
        
        return response()->json([
            'success' => true,
            'comments' => $comments
        ]);
    }
}
