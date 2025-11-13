<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $email = $request->email;
        $password = $request->password;

        // Check if user exists
        $user = User::where('email', $email)->first();

        if (!$user) {
            // Email tidak terdaftar
            return back()->withErrors(['email' => 'Email tidak terdaftar'])->withInput($request->only('email'));
        }

        // Check password
        if (!Hash::check($password, $user->password)) {
            // Password salah
            return back()->withErrors(['password' => 'Password salah'])->withInput($request->only('email'));
        }

        // Attempt login
        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();
            
            // Redirect based on role
            switch ($user->role) {
                case 'admin':
                    return redirect()->route('admin.dashboard');
                case 'umkm':
                    return redirect()->route('umkm.dashboard');
                case 'user':
                    return redirect()->route('user.katalog');
                default:
                    return redirect()->route('public.katalog');
            }
        }

        return back()->withErrors(['email' => 'Kredensial tidak valid.'])->withInput($request->only('email'));
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:user,umkm',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        Auth::login($user);

        // Redirect based on role
        switch ($user->role) {
            case 'umkm':
                return redirect()->route('umkm.dashboard');
            case 'user':
                return redirect()->route('user.katalog');
            default:
                return redirect()->route('public.katalog');
        }
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }
}