<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'UMKM Katalog')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @yield('styles')
    <style>
        /* Premium Loading Screen Styles */
        #loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background: #ffffff;
            z-index: 99999;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            opacity: 1;
            transition: opacity 0.6s cubic-bezier(0.16, 1, 0.3, 1), backdrop-filter 0.6s ease;
            overflow: hidden;
            backdrop-filter: blur(10px);
        }
        
        #loading-screen.fade-out {
            opacity: 0;
            pointer-events: none;
            backdrop-filter: blur(0px);
        }
        
        #loading-screen.hidden {
            display: none;
        }
        
        .loading-logo-container {
            position: relative;
            margin-bottom: 2.5rem;
        }

        .loading-logo {
            width: 120px;
            height: 120px;
            object-fit: contain;
            position: relative;
            z-index: 2;
            animation: premiumFloat 4s ease-in-out infinite;
        }

        .loading-glow {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 140px;
            height: 140px;
            background: radial-gradient(circle, rgba(0, 150, 136, 0.2) 0%, rgba(0, 150, 136, 0) 70%);
            z-index: 1;
            border-radius: 50%;
            animation: glowPulse 3s ease-in-out infinite;
        }
        
        @keyframes premiumFloat {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-12px); }
        }

        @keyframes glowPulse {
            0%, 100% { transform: translate(-50%, -50%) scale(1); opacity: 0.6; }
            50% { transform: translate(-50%, -50%) scale(1.3); opacity: 1; }
        }
        
        .loading-bar-container {
            width: 200px;
            height: 4px;
            background: #F1F5F9;
            border-radius: 4px;
            overflow: hidden;
            position: relative;
        }
        
        .loading-bar-progress {
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, #009688, transparent);
            animation: swipeProgress 1.5s cubic-bezier(0.4, 0, 0.2, 1) infinite;
        }
        
        @keyframes swipeProgress {
            0% { left: -100%; }
            100% { left: 100%; }
        }
        
        .loading-text {
            margin-top: 1.5rem;
            font-size: 0.95rem;
            font-weight: 500;
            letter-spacing: 0.05em;
            color: #64748B;
            text-transform: uppercase;
        }

        /* Ambient floating particles */
        .particle {
            position: absolute;
            background: rgba(0, 150, 136, 0.1);
            border-radius: 50%;
            pointer-events: none;
            animation: floatUp 8s linear infinite;
        }

        @keyframes floatUp {
            0% { transform: translateY(100vh) scale(0); opacity: 0; }
            20% { opacity: 1; }
            80% { opacity: 1; }
            100% { transform: translateY(-20vh) scale(1); opacity: 0; }
        }
    </style>
</head>
<body class="bg-surface min-h-screen flex flex-col font-sans antialiased text-slate-800">
    <!-- Premium Loading Screen -->
    <div id="loading-screen">
        <!-- Ambient Particles generated via JS -->
        <div id="particles-container"></div>
        
        <div class="loading-logo-container">
            <div class="loading-glow"></div>
            <img src="{{ asset('gambar/logo.jpeg') }}" alt="Logo UMKM" class="loading-logo">
        </div>
        
        <div class="loading-bar-container">
            <div class="loading-bar-progress"></div>
        </div>
        
        <div class="loading-text">Memuat UMKM.go</div>
    </div>
    @php 
        $isAuthPage = in_array(request()->route()->getName(), ['login','register']);
        $hasSidebar = auth()->check() && auth()->user()->role === 'umkm' && (
            request()->routeIs('umkm.*') || 
            request()->segment(1) === 'umkm'
        );
    @endphp

    @if($hasSidebar)
        <!-- Dashboard Layout with Sidebar -->
        <div class="flex min-h-screen bg-slate-50">
            <!-- Sidebar Root -->
            <div id="umkm-sidebar-root" data-umkm-name="{{ auth()->user()->umkm?->nama ?? 'Dashboard UMKM' }}"></div>
            @vite(['resources/js/umkm-dashboard-ui.jsx'])

            <!-- Right Column -->
            <div class="flex flex-col flex-1 min-h-screen min-w-0 transition-all duration-300 pl-0 lg:pl-64" id="main-content-layout">
                <!-- Navbar -->
                @if(!$isAuthPage)
                @php
                    $navLinks = [
                        ['href' => route('public.katalog'), 'label' => 'Katalog', 'icon' => '🏪'],
                    ];
                    $navUserMenu = [
                        ['href' => route('umkm.edit.account'), 'label' => '✏️ Edit Akun'],
                        ['href' => route('umkm.history.laporan'), 'label' => '📋 History Laporan'],
                    ];
                    $navUser = ['name' => auth()->user()->name, 'menu' => $navUserMenu];
                @endphp
                <div id="navbar-root"
                     data-user='@json($navUser)'
                     data-links='@json($navLinks)'
                     data-logo-url="{{ asset('gambar/logo.jpeg') }}"
                     data-katalog-url="{{ route('public.katalog') }}"
                     data-login-url="{{ route('login') }}"
                     data-logout-url="{{ route('logout') }}"
                     data-csrf-token="{{ csrf_token() }}">
                </div>
                @vite(['resources/js/navbar.jsx'])
                @endif

                <!-- Main Content Panel -->
                <main class="flex-grow flex flex-col min-w-0">
                    @if(session('success'))
                        <div class="container mx-auto px-4 py-4">
                            <div class="alert-success" role="alert">
                                <i class="fas fa-check-circle text-brand-600 flex-shrink-0"></i>
                                <span class="flex-1">{{ session('success') }}</span>
                                <button onclick="this.closest('[role=alert]').style.opacity='0';setTimeout(()=>this.closest('[role=alert]').parentElement.remove(),200)" class="text-brand-600 hover:text-brand-800 transition-colors flex-shrink-0">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="container mx-auto px-4 py-4">
                            <div class="alert-error" role="alert">
                                <i class="fas fa-exclamation-circle text-red-600 flex-shrink-0"></i>
                                <span class="flex-1">{{ session('error') }}</span>
                                <button onclick="this.closest('[role=alert]').style.opacity='0';setTimeout(()=>this.closest('[role=alert]').parentElement.remove(),200)" class="text-red-600 hover:text-red-800 transition-colors flex-shrink-0">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    @endif

                    @yield('content')
                </main>

                <!-- Footer (Keeps at bottom) -->
                @if(!$isAuthPage)
                <footer class="mt-auto {{ auth()->check() && auth()->user()->role === 'umkm' ? 'pb-[calc(64px+env(safe-area-inset-bottom))] md:pb-0' : '' }}">
                    <!-- Footer Bagian Putih -->
                    <div class="bg-white text-slate-700 py-12 border-t border-slate-100">
                        <div class="container mx-auto px-4">
                            <div class="flex flex-col md:flex-row justify-between gap-8">
                                <div class="flex-1">
                                    <div class="mb-4 flex items-center gap-2">
                                        <img src="{{ asset('gambar/logo.jpeg') }}" alt="Logo UMKM" class="h-10 w-auto object-contain">
                                        <span class="font-bold text-slate-900">UMKM<span class="text-brand-500">.go</span></span>
                                    </div>
                                    <p class="text-slate-500 text-sm leading-relaxed max-w-md">
                                        Platform digital yang menghubungkan pelaku UMKM Indonesia dengan masyarakat — dari discovery, pemesanan, hingga pembayaran aman.
                                    </p>
                                </div>
                                <div class="md:text-left">
                                    <h4 class="font-bold text-brand-600 mb-4">Bantuan</h4>
                                    <ul class="space-y-2">
                                        <li><a href="#" class="text-slate-500 hover:text-brand-600 text-sm transition-colors">Privacy Policy</a></li>
                                        <li><a href="#" class="text-slate-500 hover:text-brand-600 text-sm transition-colors">Terms and Condition</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Footer Bottom Bar -->
                    <div class="bg-brand-800 text-white px-4 py-4">
                        <div class="container mx-auto">
                            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                <p class="text-white text-sm text-center md:text-right whitespace-nowrap">
                                    © 2025 Martabak Manis | All Rights Reserved.
                                </p>
                            </div>
                        </div>
                    </div>
                </footer>
                @endif
            </div>
        </div>
    @else
        <!-- Standard Layout (Full Width, No Sidebar) -->
        @if(!$isAuthPage)
        @php
            $navLinks = [
                ['href' => route('public.katalog'), 'label' => 'Katalog', 'icon' => '🏪'],
            ];
            $navUserMenu = [];
            if (auth()->check()) {
                $role = auth()->user()->role;
                if ($role === 'admin') {
                    $navLinks[] = ['href' => route('admin.dashboard'), 'label' => 'Dashboard', 'icon' => '📊'];
                } elseif ($role === 'umkm') {
                    $navLinks[] = ['href' => route('umkm.dashboard'), 'label' => 'Dashboard', 'icon' => '📊'];
                } elseif ($role === 'user') {
                    $navLinks[] = ['href' => route('videos.index'), 'label' => 'Video', 'icon' => '🎬'];
                    $navLinks[] = ['href' => route('user.ai.chat'), 'label' => 'AI Chat', 'icon' => '🤖'];
                }
                if ($role === 'user') {
                    $navUserMenu[] = ['href' => route('user.account'), 'label' => '👤 Detail Akun'];
                    $navUserMenu[] = ['href' => route('user.history.laporan'), 'label' => '📋 History Laporan'];
                } elseif ($role === 'umkm') {
                    $navUserMenu[] = ['href' => route('umkm.edit.account'), 'label' => '✏️ Edit Akun'];
                    $navUserMenu[] = ['href' => route('umkm.history.laporan'), 'label' => '📋 History Laporan'];
                }
            }
            $navUser = auth()->check() ? ['name' => auth()->user()->name, 'menu' => $navUserMenu] : null;
        @endphp
        <div id="navbar-root"
             data-user='@json($navUser)'
             data-links='@json($navLinks)'
             data-logo-url="{{ asset('gambar/logo.jpeg') }}"
             data-katalog-url="{{ route('public.katalog') }}"
             data-login-url="{{ route('login') }}"
             data-logout-url="{{ route('logout') }}"
             data-csrf-token="{{ csrf_token() }}">
        </div>
        @vite(['resources/js/navbar.jsx'])
        @endif

        <main class="flex-grow">
            @if(session('success'))
                <div class="container mx-auto px-4 py-4">
                    <div class="alert-success" role="alert">
                        <i class="fas fa-check-circle text-brand-600 flex-shrink-0"></i>
                        <span class="flex-1">{{ session('success') }}</span>
                        <button onclick="this.closest('[role=alert]').style.opacity='0';setTimeout(()=>this.closest('[role=alert]').parentElement.remove(),200)" class="text-brand-600 hover:text-brand-800 transition-colors flex-shrink-0">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="container mx-auto px-4 py-4">
                    <div class="alert-error" role="alert">
                        <i class="fas fa-exclamation-circle text-red-600 flex-shrink-0"></i>
                        <span class="flex-1">{{ session('error') }}</span>
                        <button onclick="this.closest('[role=alert]').style.opacity='0';setTimeout(()=>this.closest('[role=alert]').parentElement.remove(),200)" class="text-red-600 hover:text-red-800 transition-colors flex-shrink-0">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- Footer -->
        @if(!$isAuthPage)
        <footer class="mt-auto {{ auth()->check() && in_array(auth()->user()->role, ['user','umkm']) ? 'pb-[calc(64px+env(safe-area-inset-bottom))] md:pb-0' : '' }}">
            <!-- Footer Bagian Putih -->
            <div class="bg-white text-slate-700 py-12 border-t border-slate-100">
                <div class="container mx-auto px-4">
                    <div class="flex flex-col md:flex-row justify-between gap-8">
                        <div class="flex-1">
                            <div class="mb-4 flex items-center gap-2">
                                <img src="{{ asset('gambar/logo.jpeg') }}" alt="Logo UMKM" class="h-10 w-auto object-contain">
                                <span class="font-bold text-slate-900">UMKM<span class="text-brand-500">.go</span></span>
                            </div>
                            <p class="text-slate-500 text-sm leading-relaxed mb-6 max-w-md">
                                Platform digital yang menghubungkan pelaku UMKM Indonesia dengan masyarakat — dari discovery, pemesanan, hingga pembayaran aman.
                            </p>
                            <div class="space-y-3">
                                <div class="flex items-center gap-3 text-gray-700">
                                    <i class="fas fa-phone text-brand-600"></i>
                                    <span class="text-sm">umkm.go</span>
                                </div>
                                <div class="flex items-center gap-3 text-gray-700">
                                    <i class="fas fa-envelope text-brand-600"></i>
                                    <span class="text-sm">umkm.go</span>
                                </div>
                            </div>
                        </div>

                        <!-- Bantuan - Posisi Kanan -->
                        <div class="md:text-left">
                            <h4 class="font-bold text-brand-600 mb-4">Bantuan</h4>
                            <ul class="space-y-2">
                                @if(auth()->check() && auth()->user()->role !== 'admin')
                                    <li><a href="{{ route('public.laporan') }}" class="text-slate-500 hover:text-brand-600 text-sm transition-colors">Laporan</a></li>
                                @elseif(!auth()->check())
                                    <li><a href="{{ route('public.laporan') }}" class="text-slate-500 hover:text-brand-600 text-sm transition-colors">Laporan</a></li>
                                @endif
                                <li><a href="#" class="text-slate-500 hover:text-brand-600 text-sm transition-colors">Privacy Policy</a></li>
                                <li><a href="#" class="text-slate-500 hover:text-brand-600 text-sm transition-colors">Terms and Condition</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Bottom Bar -->
            <div class="bg-brand-800 text-white px-4 py-4">
                <div class="container mx-auto">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div class="flex flex-wrap items-center justify-center md:justify-start gap-4">
                            <a href="#" data-no-loading class="text-white hover:text-white/80 transition-colors transform hover:scale-110" aria-label="YouTube"><i class="fab fa-youtube text-2xl"></i></a>
                            <a href="#" data-no-loading class="text-white hover:text-white/80 transition-colors transform hover:scale-110" aria-label="WhatsApp"><i class="fab fa-whatsapp text-2xl"></i></a>
                            <a href="#" data-no-loading class="text-white hover:text-white/80 transition-colors transform hover:scale-110" aria-label="Facebook"><i class="fab fa-facebook text-2xl"></i></a>
                            <a href="#" data-no-loading class="text-white hover:text-white/80 transition-colors transform hover:scale-110" aria-label="LinkedIn"><i class="fab fa-linkedin text-2xl"></i></a>
                            <a href="#" data-no-loading class="text-white hover:text-white/80 transition-colors transform hover:scale-110" aria-label="Instagram"><i class="fab fa-instagram text-2xl"></i></a>
                            <a href="#" data-no-loading class="text-white hover:text-white/80 transition-colors transform hover:scale-110" aria-label="Twitter"><i class="fab fa-twitter text-2xl"></i></a>
                        </div>

                        <p class="text-white text-sm text-center md:text-right whitespace-nowrap">
                            © 2025 Martabak Manis | All Rights Reserved.
                        </p>
                    </div>
                </div>
            </div>
        </footer>
        @endif
    @endif
    @if(auth()->check() && in_array(auth()->user()->role, ['user','umkm']))
    <div class="h-16 md:hidden"></div>
@endif

  
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Loading Screen Script -->
    <script>
        (function() {
            const loadingScreen = document.getElementById('loading-screen');
            const particlesContainer = document.getElementById('particles-container');
            
            // Create ambient particles
            function createParticles() {
                if (!particlesContainer) return;
                particlesContainer.innerHTML = '';
                const count = window.innerWidth < 768 ? 10 : 20;
                for (let i = 0; i < count; i++) {
                    const particle = document.createElement('div');
                    particle.className = 'particle';
                    
                    const size = Math.random() * 60 + 20;
                    particle.style.width = `${size}px`;
                    particle.style.height = `${size}px`;
                    
                    particle.style.left = `${Math.random() * 100}%`;
                    particle.style.animationDelay = `${Math.random() * 8}s`;
                    particle.style.animationDuration = `${Math.random() * 4 + 6}s`;
                    
                    particlesContainer.appendChild(particle);
                }
            }
            createParticles();
            
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
                    !link.closest('.leaflet-control') && // Ignore Leaflet map controls
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
    
    <!-- Mobile Bottom Navigation (User & UMKM) -->
    @if(auth()->check() && (auth()->user()->role === 'user' || auth()->user()->role === 'umkm'))
    <div style="position: fixed; bottom: 0; left: 0; right: 0; width: 100%; z-index: 9999; box-shadow: 0 -2px 10px rgba(0,0,0,0.1);" class="bg-white border-t border-gray-200 md:hidden">
        @if(auth()->user()->role === 'user')
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
        @elseif(auth()->user()->role === 'umkm')
        <div class="grid grid-cols-3 h-16">
            <a href="{{ route('umkm.dashboard') }}" class="flex flex-col items-center justify-center h-full transition-colors {{ request()->routeIs('umkm.dashboard') ? 'text-[#009b97]' : 'text-gray-400 hover:text-gray-600' }}">
                <i class="fas fa-chart-line text-xl mb-1"></i>
                <span class="text-xs font-medium">Dashboard</span>
            </a>
            <a href="{{ route('public.katalog') }}" class="flex flex-col items-center justify-center h-full transition-colors {{ request()->routeIs('public.katalog') ? 'text-[#009b97]' : 'text-gray-400 hover:text-gray-600' }}">
                <i class="fas fa-store text-xl mb-1"></i>
                <span class="text-xs font-medium">Katalog</span>
            </a>
            <a href="{{ route('umkm.edit.account') }}" class="flex flex-col items-center justify-center h-full transition-colors {{ request()->routeIs('umkm.edit.account', 'umkm.history.laporan') ? 'text-[#009b97]' : 'text-gray-400 hover:text-gray-600' }}">
                <i class="fas fa-user-circle text-xl mb-1"></i>
                <span class="text-xs font-medium">Profil</span>
            </a>
        </div>
        @endif
    </div>
    @endif

    @yield('scripts')
</body>
</html>
