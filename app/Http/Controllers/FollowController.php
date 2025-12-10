<?php

namespace App\Http\Controllers;

use App\Models\UMKM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowController extends Controller
{
    public function toggle(UMKM $umkm)
    {
        $user = Auth::user();
        
        if ($user->following()->where('umkm_id', $umkm->id)->exists()) {
            $user->following()->detach($umkm->id);
            return back()->with('success', 'Berhasil unfollow toko.');
        } else {
            $user->following()->attach($umkm->id);
            return back()->with('success', 'Berhasil follow toko.');
        }
    }
}
