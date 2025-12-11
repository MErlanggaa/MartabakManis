<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'UMKM Katalog')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @yield('styles')
    <style>
        /* Loading Screen Styles */
        #loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background: linear-gradient(135deg, #ffffff 0%, #e0f7fa 100%);
            z-index: 99999;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            opacity: 1;
            transition: opacity 0.3s ease-out;
            overflow: hidden;
        }
        
        #loading-screen.fade-out {
            opacity: 0;
            pointer-events: none;
        }
        
        #loading-screen.hidden {
            display: none;
        }
        
        .loading-logo {
            width: 150px;
            height: 150px;
            animation: logoFloat 3s ease-in-out infinite;
            filter: drop-shadow(0 10px 20px rgba(0, 155, 151, 0.3));
        }
        
        @keyframes logoFloat {
            0%, 100% {
                transform: translateY(0px) scale(1);
            }
            50% {
                transform: translateY(-20px) scale(1.05);
            }
        }
        
        .loading-spinner {
            width: 60px;
            height: 60px;
            margin-top: 30px;
            position: relative;
        }
        
        .loading-spinner::before,
        .loading-spinner::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            border: 4px solid transparent;
        }
        
        .loading-spinner::before {
            width: 60px;
            height: 60px;
            border-top-color: #009b97;
            border-right-color: #009b97;
            animation: spin 1s linear infinite;
        }
        
        .loading-spinner::after {
            width: 40px;
            height: 40px;
            top: 10px;
            left: 10px;
            border-bottom-color: #007a77;
            border-left-color: #007a77;
            animation: spinReverse 0.8s linear infinite;
        }
        
        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }
        
        @keyframes spinReverse {
            0% {
                transform: rotate(360deg);
            }
            100% {
                transform: rotate(0deg);
            }
        }
        
        .loading-text {
            margin-top: 20px;
            font-size: 18px;
            font-weight: 600;
            color: #009b97;
            animation: pulse 1.5s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.6;
            }
        }
        
        .loading-dots {
            display: inline-flex;
            gap: 8px;
            margin-top: 10px;
        }
        
        .loading-dot {
            width: 10px;
            height: 10px;
            background-color: #009b97;
            border-radius: 50%;
            animation: dotBounce 1.4s ease-in-out infinite;
        }
        
        .loading-dot:nth-child(1) {
            animation-delay: 0s;
        }
        
        .loading-dot:nth-child(2) {
            animation-delay: 0.2s;
        }
        
        .loading-dot:nth-child(3) {
            animation-delay: 0.4s;
        }
        
        @keyframes dotBounce {
            0%, 80%, 100% {
                transform: translateY(0) scale(1);
                opacity: 0.7;
            }
            40% {
                transform: translateY(-15px) scale(1.2);
                opacity: 1;
            }
        }
        
        /* Wave Animation Background - Removed to fix green color issue */
    </style>
</head>
<body class="bg-white min-h-screen flex flex-col">
    <!-- Loading Screen -->
    <div id="loading-screen">
        <img src="{{ asset('gambar/logo.jpeg') }}" alt="Logo UMKM" class="loading-logo object-contain">
        <div class="loading-spinner"></div>
        <div class="loading-text">Memuat UMKM.go</div>
        <div class="loading-dots">
            <div class="loading-dot"></div>
            <div class="loading-dot"></div>
            <div class="loading-dot"></div>
        </div>
    </div>
    @php 
     $isAuthPage = in_array(request()->route()->getName(), ['login','register']);
    @endphp

    @if(!$isAuthPage)
    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <a href="{{ route('public.katalog') }}" class="flex items-center gap-3 hover:opacity-80 transition-opacity">
                    <img src="{{ asset('gambar/logo.jpeg') }}" 
                         alt="Logo UMKM" 
                         class="h-10 w-auto md:h-12 object-contain">
                        </a>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center gap-4">
                    @auth
                        @if(auth()->user()->role === 'admin')
                            <a href="{{ route('public.katalog') }}" class="text-gray-700 hover:text-gray-900 transition-colors">
                                <i class="fas fa-th-large"></i> Katalog
                            </a>
                            <a href="{{ route('admin.dashboard') }}" class="text-gray-700 hover:text-gray-900 transition-colors">
                                <i class="fas fa-home"></i> Beranda
                                </a>
                        @elseif(auth()->user()->role === 'umkm')
                            <a href="{{ route('public.katalog') }}" class="text-gray-700 hover:text-gray-900 transition-colors">
                                <i class="fas fa-th-large"></i> Katalog
                            </a>
                            <a href="{{ route('umkm.dashboard') }}" class="text-gray-700 hover:text-gray-900 transition-colors">
                                <i class="fas fa-home"></i> Beranda
                            </a>
                        @elseif(auth()->user()->role === 'user')
                            <a href="{{ route('public.katalog') }}" class="text-gray-700 hover:text-gray-900 transition-colors">
                                <i class="fas fa-th-large"></i> Katalog
                            </a>
                               <a href="{{ route('videos.index') }}" class="text-gray-700 hover:text-gray-900 transition-colors">
                <i class="fas fa-play-circle text-xl mb-1"></i>
                Video
            </a>
                          
                        @else
                            <a href="{{ route('public.katalog') }}" class="text-gray-700 hover:text-gray-900 transition-colors">
                                <i class="fas fa-home"></i> Beranda
                                </a>
                            
                        @endif
                        
                        <div class="relative group">
                            <button class="text-gray-700 hover:text-gray-900 transition-colors flex items-center gap-2">
                                <i class="fas fa-user"></i> {{ auth()->user()->name }}
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50">
                                @if(auth()->user()->role === 'user')
                                    <a href="{{ route('user.edit.profile') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors">
                                        <i class="fas fa-user-edit"></i> Edit Profil
                                    </a>
                                    <a href="{{ route('user.history.laporan') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors">
                                        <i class="fas fa-history"></i> History Laporan
                                    </a>
                                @elseif(auth()->user()->role === 'umkm')
                                    <a href="{{ route('umkm.edit.account') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors">
                                        <i class="fas fa-user-edit"></i> Edit Akun
                                    </a>
                                    <a href="{{ route('umkm.history.laporan') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors">
                                        <i class="fas fa-history"></i> History Laporan
                                    </a>
                                @endif
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="bg-[#009b97] hover:bg-[#007a77] text-white px-6 py-2 rounded-lg transition-colors font-medium shadow-md hover:shadow-lg">
                            Masuk/Daftar
                        </a>
                    @endauth
                </div>

                <!-- Mobile Menu Button -->
                <div class="md:hidden flex items-center">
                    @auth
                    @else
                        <a href="{{ route('login') }}" class="bg-[#009b97] hover:bg-[#007a77] text-white px-4 py-2 rounded-lg transition-colors font-medium text-sm mr-3 shadow-md hover:shadow-lg">
                            Masuk
                        </a>
                    @endauth
                    <button id="mobile-menu-button" class="text-gray-700 hover:text-gray-900 focus:outline-none focus:text-gray-900 transition-colors p-2">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu Dropdown -->
            <div id="mobile-menu" class="hidden md:hidden pb-4 border-t border-gray-200 mt-4">
                <div class="flex flex-col gap-4 pt-4">
                    @auth
                        @if(auth()->user()->role === 'admin')
                            <a href="{{ route('public.katalog') }}" class="text-gray-700 hover:text-gray-900 transition-colors flex items-center gap-2 py-2">
                                <i class="fas fa-th-large"></i> Katalog
                            </a>
                            <a href="{{ route('admin.dashboard') }}" class="text-gray-700 hover:text-gray-900 transition-colors flex items-center gap-2 py-2">
                                <i class="fas fa-home"></i> Beranda
                            </a>
                        @elseif(auth()->user()->role === 'umkm')
                            <a href="{{ route('public.katalog') }}" class="text-gray-700 hover:text-gray-900 transition-colors flex items-center gap-2 py-2">
                                <i class="fas fa-th-large"></i> Katalog
                            </a>
                            <a href="{{ route('umkm.dashboard') }}" class="text-gray-700 hover:text-gray-900 transition-colors flex items-center gap-2 py-2">
                                <i class="fas fa-home"></i> Beranda
                            </a>
                        @else
                            <!-- <a href="{{ route('public.katalog') }}" class="text-gray-700 hover:text-gray-900 transition-colors flex items-center gap-2 py-2">
                                <i class="fas fa-home"></i> Beranda
                            </a> -->
                        @endif
                        
                        @if(auth()->user()->role === 'user')
                            <!-- <a href="{{ route('user.edit.profile') }}" class="text-gray-700 hover:text-gray-900 transition-colors flex items-center gap-2 py-2">
                                <i class="fas fa-user-edit"></i> Edit Profil
                            </a> -->
                            <a href="{{ route('user.history.laporan') }}" class="text-gray-700 hover:text-gray-900 transition-colors flex items-center gap-2 py-2">
                                <i class="fas fa-history"></i> History Laporan
                            </a>
                        @elseif(auth()->user()->role === 'umkm')
                            <a href="{{ route('umkm.edit.account') }}" class="text-gray-700 hover:text-gray-900 transition-colors flex items-center gap-2 py-2">
                                <i class="fas fa-user-edit"></i> Edit Akun
                            </a>
                            <a href="{{ route('umkm.history.laporan') }}" class="text-gray-700 hover:text-gray-900 transition-colors flex items-center gap-2 py-2">
                                <i class="fas fa-history"></i> History Laporan
                            </a>
                        @endif
                        
                        <div class="flex items-center gap-2 py-2 text-gray-700">
                            <i class="fas fa-user"></i> {{ auth()->user()->name }}
                        </div>
                        
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full text-left text-gray-700 hover:text-gray-900 transition-colors flex items-center gap-2 py-2">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="bg-[#009b97] hover:bg-[#007a77] text-white px-6 py-2 rounded-lg transition-colors font-medium text-center shadow-md hover:shadow-lg">
                            Masuk/Daftar
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </header>
    @endif
    <main class="flex-grow">
        @if(session('success'))
            <div class="container mx-auto px-4 py-4">
                <div class="bg-[#e6f5f4] border-l-4 border-[#009b97] text-gray-800 px-4 py-3 rounded mb-4" role="alert">
                <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-check-circle text-[#009b97]"></i>
                    <span>{{ session('success') }}</span>
                        </div>
                        <button onclick="this.parentElement.parentElement.remove()" class="text-gray-600 hover:text-gray-800 transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="container mx-auto px-4 py-4">
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                <div class="flex items-center justify-between">
                    <span>{{ session('error') }}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="text-red-700 hover:text-red-900">
                        <i class="fas fa-times"></i>
                    </button>
                    </div>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    @if(!$isAuthPage)
    <footer class="mt-auto">
        <!-- Footer Bagian Putih -->
        <div class="bg-white text-gray-800 py-12 border-t-2 border-gray-200 shadow-lg">
            <div class="container mx-auto px-4">
                <div class="flex flex-col md:flex-row justify-between gap-8">
                    <!-- Logo & Deskripsi -->
                    <div class="flex-1">
                        <div class="mb-4">
                            <img src="{{ asset('gambar/logo.jpeg') }}" 
                                 alt="Logo UMKM" 
                                 class="h-10 w-auto md:h-12 object-contain">
                        </div>
                        <p class="text-gray-600 text-sm leading-relaxed mb-6">
                            Kami Mendukung Penuh Kepada Pelaku Usaha Mikro, Kecil, dan Menengah Indonesia.<br> Kami hadir untuk membantu pengguna untuk menemukan UMKM terdekat                
                        </p>
                        <div class="space-y-3">
                            <div class="flex items-center gap-3 text-gray-700">
                                <i class="fas fa-phone text-[#009b97]"></i>
                                <span class="text-sm">umkm.go</span>
                            </div>
                            <div class="flex items-center gap-3 text-gray-700">
                                <i class="fas fa-envelope text-[#009b97]"></i>
                                <span class="text-sm">umkm.go</span>
                            </div>
                        </div>
                    </div>

                    <!-- Bantuan - Posisi Kanan -->
                    <div class="md:text-left">
                        <h4 class="font-bold text-[#009b97] mb-4">Bantuan</h4>
                        <ul class="space-y-2">
                            @if(auth()->check() && auth()->user()->role !== 'admin')
                                <li><a href="{{ route('public.laporan') }}" class="text-gray-600 hover:text-[#009b97] text-sm transition-colors">Laporan</a></li>
                            @elseif(!auth()->check())
                                <li><a href="{{ route('public.laporan') }}" class="text-gray-600 hover:text-[#009b97] text-sm transition-colors">Laporan</a></li>
                            @endif
                            <li><a href="#" class="text-gray-600 hover:text-[#009b97] text-sm transition-colors">Privacy Policy</a></li>
                            <li><a href="#" class="text-gray-600 hover:text-[#009b97] text-sm transition-colors">Terms and Condition</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Bagian Hijau -->
        <div class="bg-[#218689] text-white py-6">
        <div class="container mx-auto px-4">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    <!-- Social Media Icons -->
                    <div class="flex items-center gap-4">
                        <a href="#" data-no-loading class="text-white hover:text-white/80 transition-colors transform hover:scale-110" aria-label="YouTube">
                            <i class="fab fa-youtube text-2xl"></i>
                        </a>
                        <a href="#" data-no-loading class="text-white hover:text-white/80 transition-colors transform hover:scale-110" aria-label="WhatsApp">
                            <i class="fab fa-whatsapp text-2xl"></i>
                </a>
                        <a href="#" data-no-loading class="text-white hover:text-white/80 transition-colors transform hover:scale-110" aria-label="Facebook">
                    <i class="fab fa-facebook text-2xl"></i>
                </a>
                        <a href="#" data-no-loading class="text-white hover:text-white/80 transition-colors transform hover:scale-110" aria-label="LinkedIn">
                    <i class="fab fa-linkedin text-2xl"></i>
                </a>
                        <a href="#" data-no-loading class="text-white hover:text-white/80 transition-colors transform hover:scale-110" aria-label="Instagram">
                            <i class="fab fa-instagram text-2xl"></i>
                </a>
                        <a href="#" data-no-loading class="text-white hover:text-white/80 transition-colors transform hover:scale-110" aria-label="Twitter">
                    <i class="fab fa-twitter text-2xl"></i>
                </a>
            </div>
                    <!-- Copyright -->
                    <p class="text-white text-sm text-center md:text-right">
                        © 2025 Martabak Manis    | All Rights Reserved.
            </p>
                </div>
            </div>
        </div>
    </footer>
    @endif
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Loading Screen Script - Hanya untuk transisi perpindahan halaman -->
    <script>
        (function() {
            const loadingScreen = document.getElementById('loading-screen');
            
            // Function to show loading screen
            function showLoading() {
                if (loadingScreen) {
                    loadingScreen.classList.remove('hidden', 'fade-out');
                    loadingScreen.style.display = 'flex';
                    loadingScreen.style.opacity = '1';
                }
            }
            
            // Function to hide loading screen
            function hideLoading() {
                if (loadingScreen) {
                    loadingScreen.classList.add('fade-out');
                    // Reduce timeout for faster hide
                    setTimeout(() => {
                        loadingScreen.classList.add('hidden');
                        loadingScreen.style.display = 'none';
                    }, 150);
                }
            }
            
            // Make functions globally available
            window.showLoading = showLoading;
            window.hideLoading = hideLoading;
            
            // Hide loading screen on initial page load
            // Loading screen tidak muncul saat initial load, langsung hide
            function hideOnPageLoad() {
                hideLoading();
            }
            
            if (document.readyState === 'complete') {
                hideOnPageLoad();
            } else if (document.readyState === 'interactive') {
                hideOnPageLoad();
            } else {
                document.addEventListener('DOMContentLoaded', hideOnPageLoad);
                window.addEventListener('load', hideOnPageLoad);
            }
            
            // Intercept all link clicks for same-domain navigation
            document.addEventListener('click', function(e) {
                const link = e.target.closest('a');
                if (link && link.href && 
                    !link.href.startsWith('javascript:') && 
                    !link.href.startsWith('#') && 
                    link.href !== '#' &&
                    !link.hasAttribute('data-no-loading') &&
                    link.hostname === window.location.hostname &&
                    link.href !== window.location.href) {
                    
                    // Show loading immediately when link is clicked (transisi)
                    showLoading();
                }
            }, true);
            
            // Handle form submissions (GET only) - untuk transisi
            document.addEventListener('submit', function(e) {
                const form = e.target;
                if (form.tagName === 'FORM' && 
                    form.method.toLowerCase() === 'get' && 
                    !form.hasAttribute('data-no-loading')) {
                    showLoading();
                }
            });
            
            // Handle POST form submissions with loading indicator
            document.addEventListener('submit', function(e) {
                const form = e.target;
                if (form.tagName === 'FORM' && 
                    form.method.toLowerCase() === 'post' && 
                    !form.hasAttribute('data-no-loading') &&
                    !form.hasAttribute('data-no-full-loading')) {
                    // Show a subtle loading overlay for POST requests
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn && !submitBtn.disabled) {
                        // Button loading is handled in individual forms
                        // This is just for page-level loading if needed
                    }
                }
            });
            
            // Handle browser back/forward buttons - transisi
            window.addEventListener('popstate', function() {
                showLoading();
                // Hide setelah halaman baru dimuat
                setTimeout(function() {
                    if (document.readyState === 'complete') {
                        hideLoading();
                    } else {
                        window.addEventListener('load', hideLoading);
                        document.addEventListener('DOMContentLoaded', hideLoading);
                    }
                }, 100);
            });
            
            // Show loading before page unload (transisi)
            // Disable beforeunload loading for better bfcache support especially on mobile gestures
            // window.addEventListener('beforeunload', function(e) { showLoading(); });

            // Clean up loading screen explicitly when page is shown
            // This handles back/forward button and gesture navigation
            window.addEventListener('pageshow', function(event) {
                // Always Force hide loading on page show, regardless of whether it was persisted
                // setTimeout ensures it runs after any browser rendering quirks
                setTimeout(function() {
                    hideLoading();
                    if (loadingScreen) {
                       loadingScreen.style.display = 'none'; // Hard hide
                       loadingScreen.classList.add('hidden');
                    }
                }, 10);
            });
        })();
    </script>
    
    <!-- Mobile Menu Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            const menuIcon = mobileMenuButton?.querySelector('i');
            
            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    // Toggle menu visibility
                    mobileMenu.classList.toggle('hidden');
                    
                    // Toggle icon between bars and times
                    if (menuIcon) {
                        if (mobileMenu.classList.contains('hidden')) {
                            menuIcon.classList.remove('fa-times');
                            menuIcon.classList.add('fa-bars');
                        } else {
                            menuIcon.classList.remove('fa-bars');
                            menuIcon.classList.add('fa-times');
                        }
                    }
                });
                
                // Close menu when clicking outside
                document.addEventListener('click', function(event) {
                    if (!mobileMenuButton.contains(event.target) && !mobileMenu.contains(event.target)) {
                        if (!mobileMenu.classList.contains('hidden')) {
                            mobileMenu.classList.add('hidden');
                            if (menuIcon) {
                                menuIcon.classList.remove('fa-times');
                                menuIcon.classList.add('fa-bars');
                            }
                        }
                    }
                });
            }
        });
    </script>
    
    <!-- Mobile Bottom Navigation (User Only) -->
    @if(auth()->check() && auth()->user()->role === 'user')
    <div style="position: fixed; bottom: 0; left: 0; right: 0; width: 100%; z-index: 9999; box-shadow: 0 -2px 10px rgba(0,0,0,0.1);" class="bg-white border-t border-gray-200 md:hidden">
        <div class="grid grid-cols-3 h-16">
            <a href="{{ route('public.katalog') }}" class="flex flex-col items-center justify-center h-full transition-colors {{ request()->routeIs('public.katalog', 'user.katalog') ? 'text-[#009b97]' : 'text-gray-400 hover:text-gray-600' }}">
                <i class="fas fa-home text-xl mb-1"></i>
                <span class="text-xs font-medium">Beranda</span>
            </a>
            <a href="{{ route('videos.index') }}" class="flex flex-col items-center justify-center h-full transition-colors {{ request()->routeIs('videos.*') ? 'text-[#009b97]' : 'text-gray-400 hover:text-gray-600' }}">
                <i class="fas fa-play-circle text-xl mb-1"></i>
                <span class="text-xs font-medium">Video</span>
            </a>
            <a href="{{ route('user.account') }}" class="flex flex-col items-center justify-center h-full transition-colors {{ request()->routeIs('user.account') ? 'text-[#009b97]' : 'text-gray-400 hover:text-gray-600' }}">
                <i class="fas fa-user text-xl mb-1"></i>
                <span class="text-xs font-medium">Akun</span>
            </a>
        </div>
    </div>
    @endif

    @yield('scripts')
</body>
</html>
