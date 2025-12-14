<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Video Feed - UMKM</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .text-shadow { text-shadow: 0 1px 2px rgba(0,0,0,0.5); }
        .video-section { position: relative; }
        .video-container { 
            position: relative; 
            width: 100%; 
            height: 100vh;
        }
        .video-container video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .play-pause-icon {
            position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
            font-size: 4rem; color: white; opacity: 0; transition: opacity 0.3s;
            pointer-events: none; z-index: 5;
        }
        .play-pause-icon.show { opacity: 0.8; }
        .modal { display: none; position: fixed; z-index: 999; left: 0; top: 0; width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.7); }
        .modal.active { display: flex; align-items: flex-end; justify-content: center; }
        .modal-content { background-color: white; width: 100%; max-width: 500px; border-radius: 20px 20px 0 0;
            max-height: 80vh; overflow-y: auto; animation: slideUp 0.3s ease-out; }
        @keyframes slideUp { from { transform: translateY(100%); } to { transform: translateY(0); } }
    </style>
</head>
<body class="bg-black">
    <header class="fixed top-0 left-0 right-0 z-50 bg-transparent">
        <div class="flex items-center justify-between px-4 py-4">
            <a href="{{ route('public.katalog') }}" class="text-white hover:text-gray-300 bg-black/30 p-2 rounded-full backdrop-blur-sm">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <h1 onclick="location.reload()" class="text-white font-bold text-lg bg-black/30 px-4 py-2 rounded-full backdrop-blur-sm cursor-pointer hover:bg-black/40 transition">Video</h1>
            <div class="w-10"></div>
        </div>
    </header>

    <div class="bg-black min-h-screen text-white">
        <div class="max-w-md mx-auto relative h-screen overflow-y-scroll snap-y snap-mandatory scroll-smooth no-scrollbar">
            @foreach($videos as $video)
                <div class="relative w-full h-screen snap-start flex items-center justify-center bg-gray-900 video-section" data-video-id="{{ $video->id }}">
                    <div class="video-container">
                        <video 
                            src="{{ Storage::url($video->video_path) }}" 
                            class="w-full h-full object-cover video-player" 
                            loop 
                            playsinline
                        ></video>
                        <div class="play-pause-icon"><i class="fas fa-play"></i></div>
                     
                        
                        <!-- Mute/Unmute Button -->
                        <button class="hidden absolute top-4 right-4 z-10 bg-black/50 text-white p-3 rounded-full hover:bg-black/70 transition mute-btn">
                            <i class="fas fa-volume-mute text-xl"></i>
                        </button>
                    </div>

                    <!-- Overlay Info -->
                    <div class="absolute bottom-4 left-4 right-4 z-10 space-y-3 pointer-events-none">
                        <!-- Product Cards (Top - near like button) -->
                        @foreach($video->products as $product)
                            <a href="{{ route('public.layanan.show', $product->id) }}" class="flex items-center bg-white/95 hover:bg-white text-gray-900 p-3 rounded-xl max-w-[75%] backdrop-blur-sm transition pointer-events-auto shadow-lg">
                                <div class="w-16 h-16 bg-gray-200 rounded-lg overflow-hidden mr-3 flex-shrink-0">
                                    @if($product->photo_path)
                                        <img src="{{ Storage::url($product->photo_path) }}" alt="{{ $product->nama }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <i class="fas fa-image text-gray-400 text-xl"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 overflow-hidden">
                                    <p class="text-sm font-bold truncate text-gray-900">{{ $product->nama }}</p>
                                    <p class="text-lg font-bold text-red-600">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                                </div>
                                <div class="ml-2 flex-shrink-0">
                                    <div class="bg-red-500 text-white p-2 rounded-lg">
                                        <i class="fas fa-shopping-cart"></i>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                        
                        <!-- Username & Caption (Below products) -->
                        <div class="space-y-2">
                            <div class="flex items-center space-x-3">
                                <a href="{{ route('videos.umkm.profile', $video->umkm->id) }}">
                                 
                                </a>
                                <div class="flex-1">
                                    <a href="{{ route('videos.umkm.profile', $video->umkm->id) }}" class="font-bold text-shadow truncate block pointer-events-auto hover:underline text-left">
                                        @ {{ $video->umkm->nama }}
                                    </a>
                                </div>
                            </div>

                            <p class="text-sm text-white line-clamp-2 text-shadow">{{ $video->caption }}</p>
                        </div>
                    </div>
                    
                    <!-- Right Action Bar -->
                    <div class="absolute bottom-20 right-4 flex flex-col items-center space-y-6 z-20">
                        <!-- UMKM Profile with Follow Button -->
                        <div class="relative pointer-events-auto">
                            <!-- Profile Picture Link -->
                            <a href="{{ route('videos.umkm.profile', $video->umkm->id) }}" class="block w-12 h-12 rounded-full bg-gray-700 overflow-hidden border-2 border-white hover:scale-110 transition-transform">
                                @if($video->umkm->photo_path)
                                    <img src="{{ Storage::url($video->umkm->photo_path) }}" alt="{{ $video->umkm->nama }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-xs font-bold bg-gradient-to-br from-purple-500 to-blue-500">
                                        {{ substr($video->umkm->nama, 0, 2) }}
                                    </div>
                                @endif
                            </a>
                            
                            <!-- Follow + Button (TikTok style) - Positioned relative to profile pic -->
                            @auth
                                @if(Auth::user()->role === 'user' && !Auth::user()->following->contains($video->umkm->id))
                                    <form action="{{ route('user.follow.toggle', $video->umkm->id) }}" method="POST" class="absolute -bottom-3 left-1/2 transform -translate-x-1/2">
                                        @csrf
                                        <button type="submit" class="w-6 h-6 bg-red-500 rounded-full flex items-center justify-center border-2 border-white hover:bg-red-600 transition shadow-lg">
                                            <i class="fas fa-plus text-white text-sm"></i>
                                        </button>
                                    </form>
                                @endif
                            @endauth
                        </div>
                        
                        <!-- Like -->
                        <div class="flex flex-col items-center pointer-events-auto">
                            <button onclick="toggleLike({{ $video->id }})" class="like-btn p-2 bg-black/40 rounded-full hover:bg-black/60 transition" data-video-id="{{ $video->id }}" data-liked="{{ Auth::check() ? ($video->isLikedBy(Auth::id()) ? 'true' : 'false') : 'false' }}">
                                <i class="fas fa-heart text-2xl {{ Auth::check() && $video->isLikedBy(Auth::id()) ? 'text-red-500' : 'text-white' }}"></i>
                            </button>
                            <span class="text-xs font-bold mt-1 text-shadow like-count">{{ number_format($video->likes_count) }}</span>
                        </div>
                        
                        <!-- Comment -->
                        <div class="flex flex-col items-center pointer-events-auto">
                            <button onclick="openCommentModal({{ $video->id }})" class="p-2 bg-black/40 rounded-full hover:bg-black/60 transition">
                                <i class="fas fa-comment text-white text-2xl"></i>
                            </button>
                            <span class="text-xs font-bold mt-1 text-shadow comment-count">{{ number_format($video->comments_count) }}</span>
                        </div>
                        
                        <!-- Share -->
                        <div class="flex flex-col items-center pointer-events-auto">
                            <button onclick="shareVideo({{ $video->id }})" class="p-2 bg-black/40 rounded-full hover:bg-black/60 transition">
                                <i class="fas fa-share text-white text-2xl"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Hidden data for modals -->
                    <div class="hidden" data-umkm-id="{{ $video->umkm->id }}" data-umkm-data='@json($video->umkm)'></div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Mobile Bottom Navigation (User Only) -->
 

   

    <!-- Comment Modal -->
    <div id="commentModal" class="modal">
        <div class="modal-content">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-900">Komentar</h2>
                    <button onclick="closeCommentModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>
                
                <div id="commentsList" class="space-y-4 mb-4 max-h-96 overflow-y-auto">
                    <!-- Comments will be loaded here -->
                </div>
                
                @auth
                <form id="commentForm" class="flex gap-2">
                    <input type="hidden" id="parentCommentId" value="">
                    <input type="text" id="commentInput" placeholder="Tulis komentar..." class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-bold transition">
                        Kirim
                    </button>
                </form>
                <div id="replyingTo" class="hidden mt-2 text-sm text-gray-600">
                    Membalas <span id="replyingToName" class="font-bold"></span>
                    <button onclick="cancelReply()" class="ml-2 text-red-500 hover:text-red-700">Batal</button>
                </div>
                @else
                <p class="text-center text-gray-600">Login untuk berkomentar</p>
                @endauth
            </div>
        </div>
    </div>

    <script>
        let currentVideoId = null;
        let currentUmkmId = null;
        
        document.addEventListener('DOMContentLoaded', function() {
            const videoSections = document.querySelectorAll('.video-section');
            let currentVideo = null;
            
            videoSections.forEach((section, index) => {
                const video = section.querySelector('.video-player');
                const playPauseIcon = section.querySelector('.play-pause-icon i');
                const playPauseContainer = section.querySelector('.play-pause-icon');
                const muteBtn = section.querySelector('.mute-btn');
                const muteIcon = muteBtn.querySelector('i');
                
                // Mute/Unmute
                muteBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    video.muted = !video.muted;
                    muteIcon.classList.toggle('fa-volume-mute');
                    muteIcon.classList.toggle('fa-volume-up');
                });
                
                // Tap to play/pause
                video.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    if (video.paused) {
                        video.play();
                        playPauseIcon.classList.remove('fa-play');
                        playPauseIcon.classList.add('fa-pause');
                    } else {
                        video.pause();
                        playPauseIcon.classList.remove('fa-pause');
                        playPauseIcon.classList.add('fa-play');
                    }
                    
                    playPauseContainer.classList.add('show');
                    setTimeout(() => playPauseContainer.classList.remove('show'), 500);
                });
                
                // Auto-play first video
                if (index === 0) {
                    currentVideo = video;
                    video.play().catch(err => console.log('Autoplay prevented:', err));
                }
            });
            
            // Intersection Observer
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    const video = entry.target.querySelector('.video-player');
                    const videoId = entry.target.dataset.videoId;
                    
                    if (entry.isIntersecting) {
                        if (currentVideo && currentVideo !== video) {
                            currentVideo.pause();
                            currentVideo.currentTime = 0;
                        }
                        currentVideo = video;
                        video.play().catch(err => console.log('Play prevented:', err));
                        
                        // Increment view count (every time video is viewed)
                        fetch(`/videos/${videoId}/view`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                    }
                });
            }, { threshold: 0.5 });
            
            videoSections.forEach(section => observer.observe(section));
        });

        function openProfileModal(umkmId) {
            currentUmkmId = umkmId;
            const umkmData = document.querySelector(`[data-umkm-id="${umkmId}"]`).dataset.umkmData;
            const umkm = JSON.parse(umkmData);
            
            document.getElementById('profileName').textContent = umkm.nama;
            document.getElementById('profileDesc').textContent = umkm.description || 'Tidak ada deskripsi';
            document.getElementById('visitStoreBtn').href = `/umkm/${umkm.id}`;
            
            const photoDiv = document.getElementById('profilePhoto');
            if (umkm.photo_path) {
                photoDiv.innerHTML = `<img src="/storage/${umkm.photo_path}" class="w-full h-full object-cover">`;
            } else {
                photoDiv.innerHTML = `<div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-purple-500 to-blue-500 text-white text-2xl font-bold">${umkm.nama.substring(0, 2)}</div>`;
            }
            
            // TODO: Load actual stats from backend
            document.getElementById('profileVideos').textContent = '0';
            document.getElementById('profileFollowers').textContent = '0';
            document.getElementById('profileLikes').textContent = '0';
            
            document.getElementById('profileModal').classList.add('active');
        }

        function closeProfileModal() {
            document.getElementById('profileModal').classList.remove('active');
        }

        function toggleLike(videoId) {
            @guest
                alert('Silakan login terlebih dahulu');
                return;
            @endguest

            fetch(`/videos/${videoId}/like`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const btn = document.querySelector(`.like-btn[data-video-id="${videoId}"]`);
                    const icon = btn.querySelector('i');
                    const count = btn.parentElement.querySelector('.like-count');
                    
                    if (data.liked) {
                        icon.classList.remove('text-white');
                        icon.classList.add('text-red-500', 'fas');
                        icon.classList.remove('far'); // Just in case
                    } else {
                        icon.classList.remove('text-red-500', 'fas');
                        icon.classList.add('text-white', 'fas'); // Keep fas/solid for visibility or far
                        // Check if we want outline when unlike? Usually heart is solid just changing color
                        // Assuming solid white when not liked based on TikTok style
                    }
                    
                    count.textContent = new Intl.NumberFormat('id-ID').format(data.likes_count);
                }
            });
        }

        function shareVideo(videoId) {
            const videoUrl = `${window.location.origin}/videos/${videoId}`;
            
            // Check if Web Share API is available (mobile)
            if (navigator.share) {
                navigator.share({
                    title: 'Video UMKM',
                    text: 'Lihat video ini!',
                    url: videoUrl
                }).catch(err => console.log('Share cancelled'));
            } else {
                // Fallback: copy to clipboard
                navigator.clipboard.writeText(videoUrl).then(() => {
                    alert('Link video berhasil disalin!');
                }).catch(() => {
                    // Manual fallback
                    prompt('Copy link ini:', videoUrl);
                });
            }
        }

        function openCommentModal(videoId) {
            currentVideoId = videoId;
            loadComments(videoId);
            document.getElementById('commentModal').classList.add('active');
        }

        function closeCommentModal() {
            document.getElementById('commentModal').classList.remove('active');
        }

        function loadComments(videoId) {
            fetch(`/videos/${videoId}/comments`)
                .then(res => res.json())
                .then(data => {
                    const container = document.getElementById('commentsList');
                    if (data.comments.length === 0) {
                        container.innerHTML = '<p class="text-center text-gray-500">Belum ada komentar</p>';
                    } else {
                        container.innerHTML = data.comments.map(comment => `
                            <div class="space-y-2">
                                <div class="flex gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gray-300 flex-shrink-0 flex items-center justify-center text-gray-700 font-bold">
                                        ${comment.user.name.substring(0, 2).toUpperCase()}
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-bold text-gray-900">${comment.user.name}</p>
                                        <p class="text-gray-700">${comment.comment}</p>
                                        <div class="flex items-center gap-4 mt-1">
                                            <p class="text-xs text-gray-500">${new Date(comment.created_at).toLocaleDateString('id-ID')}</p>
                                            @auth
                                            <button onclick="replyToComment(${comment.id}, '${comment.user.name}')" class="text-xs text-blue-600 hover:text-blue-800 font-semibold">
                                                Balas
                                            </button>
                                            @endauth
                                        </div>
                                        ${comment.replies && comment.replies.length > 0 ? `
                                            <div class="mt-3 space-y-2 pl-4 border-l-2 border-gray-200">
                                                ${comment.replies.map(reply => `
                                                    <div class="flex gap-2">
                                                        <div class="w-8 h-8 rounded-full bg-gray-300 flex-shrink-0 flex items-center justify-center text-gray-700 font-bold text-xs">
                                                            ${reply.user.name.substring(0, 2).toUpperCase()}
                                                        </div>
                                                        <div class="flex-1">
                                                            <p class="font-bold text-gray-900 text-sm">${reply.user.name}</p>
                                                            <p class="text-gray-700 text-sm">${reply.comment}</p>
                                                            <p class="text-xs text-gray-500 mt-1">${new Date(reply.created_at).toLocaleDateString('id-ID')}</p>
                                                        </div>
                                                    </div>
                                                `).join('')}
                                            </div>
                                        ` : ''}
                                    </div>
                                </div>
                            </div>
                        `).join('');
                    }
                });
        }

        function replyToComment(commentId, userName) {
            document.getElementById('parentCommentId').value = commentId;
            document.getElementById('replyingToName').textContent = userName;
            document.getElementById('replyingTo').classList.remove('hidden');
            document.getElementById('commentInput').focus();
            document.getElementById('commentInput').placeholder = `Balas ${userName}...`;
        }

        function cancelReply() {
            document.getElementById('parentCommentId').value = '';
            document.getElementById('replyingTo').classList.add('hidden');
            document.getElementById('commentInput').placeholder = 'Tulis komentar...';
        }

        @auth
        document.getElementById('commentForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const input = document.getElementById('commentInput');
            const comment = input.value.trim();
            const parentId = document.getElementById('parentCommentId').value;
            
            if (!comment) return;
            
            const requestBody = { comment };
            if (parentId) {
                requestBody.parent_id = parentId;
            }
            
            fetch(`/videos/${currentVideoId}/comment`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(requestBody)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    input.value = '';
                    cancelReply();
                    loadComments(currentVideoId);
                    
                    // Update comment count
                    const section = document.querySelector(`[data-video-id="${currentVideoId}"]`);
                    const countEl = section.querySelector('.comment-count');
                    countEl.textContent = new Intl.NumberFormat('id-ID').format(data.comments_count);
                }
            });
        });
        @endauth

        // Close modals on background click
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>
