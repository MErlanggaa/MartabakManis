@extends('layouts.app')

@section('title', 'Register - UMKM Katalog')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Logo & Header -->
        <div class="text-center">
        <div class="mx-auto h-20 w-auto flex items-center justify-center mb-4">
                <img src="{{ asset('gambar/logo.jpeg') }}" 
                     alt="Logo UMKM" 
                     class="h-full w-auto object-contain">
            </div>
            <h2 class="text-3xl font-extrabold text-gray-900">
                Buat Akun Baru
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Daftar sekarang untuk mulai menjelajahi UMKM di UMKM.go
            </p>
        </div>

        <!-- Register Card -->
        <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf
                
                <!-- Name Input -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user text-gray-400 mr-2"></i>Nama Lengkap
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name') }}" 
                           required
                           autocomplete="name"
                           autofocus
                           class="appearance-none relative block w-full px-4 py-3 border @error('name') border-red-300 @else border-gray-300 @enderror placeholder-gray-400 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                           placeholder="Masukkan nama lengkap">
                    @error('name')
                        <p class="mt-2 text-sm text-red-600 flex items-center">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope text-gray-400 mr-2"></i>Email
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="{{ old('email') }}" 
                           required
                           autocomplete="email"
                           class="appearance-none relative block w-full px-4 py-3 border @error('email') border-red-300 @else border-gray-300 @enderror placeholder-gray-400 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                           placeholder="nama@email.com">
                    @error('email')
                        <p class="mt-2 text-sm text-red-600 flex items-center">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Password Input -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock text-gray-400 mr-2"></i>Password
                    </label>
                    <div class="relative">
                        <input type="password" 
                               id="password" 
                               name="password" 
                               required
                               autocomplete="new-password"
                               class="appearance-none relative block w-full px-4 py-3 border @error('password') border-red-300 @else border-gray-300 @enderror placeholder-gray-400 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all pr-12"
                               placeholder="Minimal 8 karakter">
                        <button type="button" 
                                onclick="togglePassword('password')"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                            <i class="fas fa-eye" id="password-toggle-icon"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-2 text-sm text-red-600 flex items-center">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            {{ $message }}
                        </p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Password minimal 8 karakter</p>
                </div>

                <!-- Password Confirmation Input -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock text-gray-400 mr-2"></i>Konfirmasi Password
                    </label>
                    <div class="relative">
                        <input type="password" 
                               id="password_confirmation" 
                               name="password_confirmation" 
                               required
                               autocomplete="new-password"
                               class="appearance-none relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-400 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all pr-12"
                               placeholder="Ulangi password">
                        <button type="button" 
                                onclick="togglePassword('password_confirmation')"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                            <i class="fas fa-eye" id="password_confirmation-toggle-icon"></i>
                        </button>
                    </div>
                </div>

                <!-- Role Selection -->
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user-tag text-gray-400 mr-2"></i>Daftar Sebagai
                    </label>
                    <select id="role" 
                            name="role" 
                            required
                            class="appearance-none relative block w-full px-4 py-3 border @error('role') border-red-300 @else border-gray-300 @enderror text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all bg-white">
                        <option value="">Pilih Role</option>
                        <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>User Biasa</option>
                        <option value="umkm" {{ old('role') == 'umkm' ? 'selected' : '' }}>Pemilik UMKM</option>
                    </select>
                    @error('role')
                        <p class="mt-2 text-sm text-red-600 flex items-center">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            {{ $message }}
                        </p>
                    @enderror
                    <div class="mt-2 space-y-1">
                        <p class="text-xs text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            <strong>User Biasa:</strong> Untuk menjelajahi dan mencari UMKM
                        </p>
                        <p class="text-xs text-gray-500">
                            <i class="fas fa-store mr-1"></i>
                            <strong>Pemilik UMKM:</strong> Untuk mengelola profil dan data UMKM Anda
                        </p>
                    </div>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit" 
                            class="group relative w-full flex justify-center items-center py-3 px-4 border border-transparent text-base font-medium rounded-lg text-white bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <i class="fas fa-user-plus text-green-200"></i>
                        </span>
                        Daftar
                    </button>
                </div>
            </form>

            <!-- Divider -->
            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500">Atau</span>
                    </div>
                </div>
            </div>

            <!-- Login Link -->
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Sudah punya akun?
                    <a href="{{ route('login') }}" class="font-medium text-green-600 hover:text-green-500 transition-colors">
                        Login di sini
                    </a>
                </p>
            </div>
        </div>

        <!-- Back to Home -->
        <div class="text-center">
            <a href="{{ route('public.katalog') }}" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali ke beranda
            </a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const icon = document.getElementById(fieldId + '-toggle-icon');
        
        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            field.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    // Password match validation
    document.getElementById('password_confirmation').addEventListener('input', function() {
        const password = document.getElementById('password').value;
        const passwordConfirmation = this.value;
        
        if (passwordConfirmation && password !== passwordConfirmation) {
            this.setCustomValidity('Password tidak cocok');
            this.classList.add('border-red-300');
        } else {
            this.setCustomValidity('');
            this.classList.remove('border-red-300');
        }
    });

    document.getElementById('password').addEventListener('input', function() {
        const passwordConfirmation = document.getElementById('password_confirmation');
        if (passwordConfirmation.value) {
            passwordConfirmation.dispatchEvent(new Event('input'));
        }
    });
</script>
@endsection
