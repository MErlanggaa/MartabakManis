@extends('layouts.app')

@section('title', 'AI Chat')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-blue-500 rounded-full flex items-center justify-center">
                    <i class="fas fa-robot text-white text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">AI Assistant</h1>
                    <p class="text-sm text-gray-500">Tanyakan apapun tentang produk dan rekomendasi</p>
                </div>
            </div>
            <a href="{{ route('user.katalog') }}" 
               class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- Chat Container -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <!-- Chat Messages -->
        <div id="chat-box" class="h-[500px] overflow-y-auto p-6 space-y-4 bg-gray-50">
            <!-- Welcome Message -->
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-robot text-white text-sm"></i>
                </div>
                <div class="flex-1">
                    <div class="bg-white rounded-2xl rounded-tl-none px-4 py-3 shadow-sm">
                        <p class="text-gray-800">Halo! Saya adalah AI Assistant. Saya siap membantu Anda dengan pertanyaan tentang produk, rekomendasi menu, atau hal lainnya. Silakan tanyakan apa yang ingin Anda ketahui!</p>
                    </div>
                    <span class="text-xs text-gray-500 mt-1 block ml-2" id="welcome-time"></span>
                </div>
            </div>
        </div>

        <!-- Chat Input -->
        <div class="border-t bg-white p-4">
            <form id="chat-form" class="flex gap-3" data-no-loading>
                @csrf
                <div class="flex-1 relative">
                    <input type="text" 
                           id="message" 
                           name="message" 
                           placeholder="Tulis pertanyaan Anda di sini..." 
                           required
                           autocomplete="off"
                           class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    <button type="submit" 
                            id="send-btn"
                            class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
                <button type="button" 
                        onclick="clearChat()"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-3 rounded-lg transition-colors">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </form>
            
            <!-- Quick Actions -->
            <div class="mt-3 flex flex-wrap gap-2">
                <button onclick="sendQuickQuestion('Rekomendasi menu')" 
                        class="text-xs bg-purple-100 hover:bg-purple-200 text-purple-700 px-3 py-1 rounded-full transition-colors">
                    📋 Rekomendasi menu
                </button>
                <button onclick="sendQuickQuestion('Apa produk terlaris?')" 
                        class="text-xs bg-blue-100 hover:bg-blue-200 text-blue-700 px-3 py-1 rounded-full transition-colors">
                    🏆 Produk terlaris
                </button>
                <button onclick="sendQuickQuestion('Harga produk')" 
                        class="text-xs bg-green-100 hover:bg-green-200 text-green-700 px-3 py-1 rounded-full transition-colors">
                    💰 Harga produk
                </button>
                <button onclick="sendQuickQuestion('Cara pemesanan')" 
                        class="text-xs bg-orange-100 hover:bg-orange-200 text-orange-700 px-3 py-1 rounded-full transition-colors">
                    📦 Cara pemesanan
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const chatBox = document.getElementById('chat-box');
    const chatForm = document.getElementById('chat-form');
    const messageInput = document.getElementById('message');
    const sendBtn = document.getElementById('send-btn');
    
    // Set welcome message time
    document.getElementById('welcome-time').textContent = new Date().toLocaleTimeString('id-ID', { 
        hour: '2-digit', 
        minute: '2-digit' 
    });

    function appendMessage(sender, text, isTyping = false, isHtml = false) {
        const messageDiv = document.createElement('div');
        messageDiv.className = 'flex items-start gap-3 ' + (sender === 'user' ? 'flex-row-reverse' : '');
        
        const time = new Date().toLocaleTimeString('id-ID', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
        
        if (sender === 'user') {
            messageDiv.innerHTML = `
                <div class="flex-1 flex flex-col items-end">
                    <div class="bg-purple-600 text-white rounded-2xl rounded-tr-none px-4 py-3 shadow-sm max-w-[80%]">
                        <p class="whitespace-pre-wrap">${escapeHtml(text)}</p>
                    </div>
                    <span class="text-xs text-gray-500 mt-1 mr-2">${time}</span>
                </div>
            `;
        } else {
            const typingClass = isTyping ? 'animate-pulse' : '';
            const contentDiv = document.createElement('div');
            contentDiv.className = 'bg-white rounded-2xl rounded-tl-none px-4 py-3 shadow-sm';
            
            if (isTyping) {
                contentDiv.innerHTML = '<div class="flex gap-1"><div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div><div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div><div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.4s"></div></div>';
            } else if (isHtml) {
                contentDiv.innerHTML = text;
            } else {
                contentDiv.innerHTML = '<p class="text-gray-800 whitespace-pre-wrap">' + escapeHtml(text) + '</p>';
            }
            
            messageDiv.innerHTML = `
                <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-blue-500 rounded-full flex items-center justify-center flex-shrink-0 ${typingClass}">
                    <i class="fas fa-robot text-white text-sm"></i>
                </div>
                <div class="flex-1">
                    ${contentDiv.outerHTML}
                    ${!isTyping ? `<span class="text-xs text-gray-500 mt-1 block ml-2">${time}</span>` : ''}
                </div>
            `;
        }
        
        chatBox.appendChild(messageDiv);
        chatBox.scrollTop = chatBox.scrollHeight;
        
        // After appending, find and add data-no-loading to pesan links
        if (sender === 'ai' && !isTyping) {
            setTimeout(() => {
                const messageContent = messageDiv.querySelector('.bg-white.rounded-2xl');
                if (messageContent) {
                    // Find all links that might be pesan/WhatsApp links
                    const links = messageContent.querySelectorAll('a[href*="wa.me"], a[href*="whatsapp"]');
                    links.forEach(link => {
                        const linkText = link.textContent.toLowerCase();
                        if (linkText.includes('pesan') || link.href.includes('wa.me') || link.href.includes('whatsapp')) {
                            link.setAttribute('data-no-loading', 'true');
                        }
                    });
                    
                    // Also check for buttons
                    const buttons = messageContent.querySelectorAll('button');
                    buttons.forEach(btn => {
                        const btnText = btn.textContent.toLowerCase();
                        const onclick = btn.getAttribute('onclick') || '';
                        if (btnText.includes('pesan') || onclick.includes('wa.me') || onclick.includes('whatsapp') || onclick.includes('pesan')) {
                            btn.setAttribute('data-no-loading', 'true');
                        }
                    });
                }
            }, 100);
        }
        
        return messageDiv;
    }

    // Display UMKM cards as a new AI message
    function displayUmkmCards(umkmArray) {
        if (!umkmArray || umkmArray.length === 0) return;
        
        // Create a new AI message container for the cards
        const messageDiv = document.createElement('div');
        messageDiv.className = 'flex items-start gap-3';
        
        const time = new Date().toLocaleTimeString('id-ID', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
        
        // Create cards container
        const cardsContainer = document.createElement('div');
        cardsContainer.className = 'space-y-3 w-full';
        cardsContainer.id = 'umkm-cards-container';
        
        umkmArray.forEach(umkm => {
            const card = createUmkmCard(umkm);
            cardsContainer.appendChild(card);
        });
        
        // Build the message HTML
        const messageContent = document.createElement('div');
        messageContent.className = 'flex-1';
        
        const messageBubble = document.createElement('div');
        messageBubble.className = 'bg-white rounded-2xl rounded-tl-none px-4 py-3 shadow-sm';
        
        const titleText = document.createElement('p');
        titleText.className = 'text-gray-800 mb-3 font-medium';
        titleText.textContent = 'Berikut adalah UMKM yang sudah bergabung:';
        messageBubble.appendChild(titleText);
        messageBubble.appendChild(cardsContainer);
        
        messageContent.appendChild(messageBubble);
        
        const timeSpan = document.createElement('span');
        timeSpan.className = 'text-xs text-gray-500 mt-1 block ml-2';
        timeSpan.textContent = time;
        messageContent.appendChild(timeSpan);
        
        const robotIcon = document.createElement('div');
        robotIcon.className = 'w-8 h-8 bg-gradient-to-br from-purple-500 to-blue-500 rounded-full flex items-center justify-center flex-shrink-0';
        robotIcon.innerHTML = '<i class="fas fa-robot text-white text-sm"></i>';
        
        messageDiv.appendChild(robotIcon);
        messageDiv.appendChild(messageContent);
        
        // Append as new message to chat box
        chatBox.appendChild(messageDiv);
        
        // Scroll to bottom
        chatBox.scrollTop = chatBox.scrollHeight;
        
        // Add data-no-loading to all links in cards
        setTimeout(() => {
            const links = messageDiv.querySelectorAll('a[href]');
            links.forEach(link => {
                link.setAttribute('data-no-loading', 'true');
            });
        }, 100);
    }

    // Create UMKM card
    function createUmkmCard(umkm) {
        const card = document.createElement('div');
        card.className = 'bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow cursor-pointer';
        card.onclick = () => window.location.href = umkm.url;
        
        const imageUrl = umkm.photo_path || 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="200" height="150" viewBox="0 0 200 150"%3E%3Crect fill="%23e5e7eb" width="200" height="150"/%3E%3Ctext fill="%239ca3af" font-family="Arial" font-size="14" x="50%25" y="50%25" text-anchor="middle" dy=".3em"%3ENo Image%3C/text%3E%3C/svg%3E';
        
        card.innerHTML = `
            <div class="flex gap-3 p-3">
                <div class="w-20 h-20 md:w-24 md:h-24 bg-gray-200 rounded-lg overflow-hidden flex-shrink-0">
                    <img src="${imageUrl}" alt="${escapeHtml(umkm.nama)}" class="w-full h-full object-cover" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\\'http://www.w3.org/2000/svg\\' width=\\'100\\' height=\\'100\\' viewBox=\\'0 0 100 100\\'%3E%3Crect fill=\\'%23e5e7eb\\' width=\\'100\\' height=\\'100\\'/%3E%3Ctext fill=\\'%239ca3af\\' font-family=\\'Arial\\' font-size=\\'12\\' x=\\'50%25\\' y=\\'50%25\\' text-anchor=\\'middle\\' dy=\\'.3em\\'%3ENo Image%3C/text%3E%3C/svg%3E'">
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="font-bold text-gray-900 text-sm md:text-base mb-1 line-clamp-1">${escapeHtml(umkm.nama)}</h4>
                    <div class="flex items-center gap-3 mb-2">
                        ${umkm.rating_umkm > 0 ? `
                            <div class="flex items-center gap-1">
                                <div class="flex items-center">
                                    ${generateStars(umkm.rating_umkm)}
                                </div>
                                <span class="text-xs text-gray-600">${umkm.rating_umkm}</span>
                            </div>
                        ` : ''}
                        ${umkm.distance !== null ? `
                            <div class="flex items-center gap-1">
                                <i class="fas fa-map-marker-alt text-[#218689] text-xs"></i>
                                <span class="text-xs text-gray-600">${umkm.distance} km</span>
                            </div>
                        ` : ''}
                    </div>
                    ${umkm.latitude && umkm.longitude ? `
                        <div class="flex items-center gap-1 mb-2">
                            <i class="fas fa-location-dot text-gray-400 text-xs"></i>
                            <span class="text-xs text-gray-500 line-clamp-1" data-lat="${umkm.latitude}" data-lng="${umkm.longitude}">Memuat lokasi...</span>
                        </div>
                    ` : ''}
                    <a href="${umkm.url}" 
                       data-no-loading="true"
                       onclick="event.stopPropagation()"
                       class="bg-[#009b97] hover:bg-[#007a77] text-white text-xs px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1 inline-block">
                        Lihat Layanan <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                </div>
            </div>
        `;
        
        // Load address if coordinates are available
        if (umkm.latitude && umkm.longitude) {
            const locationElement = card.querySelector('[data-lat]');
            if (locationElement) {
                fetchAddressFromCoordinates(umkm.latitude, umkm.longitude)
                    .then(address => {
                        if (address) {
                            locationElement.textContent = address;
                        } else {
                            locationElement.textContent = 'Lokasi tidak tersedia';
                        }
                    })
                    .catch(() => {
                        if (locationElement) {
                            locationElement.textContent = 'Lokasi tidak tersedia';
                        }
                    });
            }
        }
        
        return card;
    }
    
    // Helper function to fetch address from coordinates
    async function fetchAddressFromCoordinates(lat, lng) {
        try {
            const response = await fetch(
                `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1&accept-language=id`,
                {
                    headers: {
                        'User-Agent': 'UMKM-App/1.0'
                    }
                }
            );
            
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            
            const data = await response.json();
            return data && data.display_name ? data.display_name : null;
        } catch (error) {
            console.error('Error fetching address:', error);
            return null;
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function sendMessage(message) {
        if (!message.trim()) return;
        
        // Append user message
        appendMessage('user', message);
        messageInput.value = '';
        
        // Disable input while processing
        messageInput.disabled = true;
        sendBtn.disabled = true;
        
        // Show typing indicator
        const typingIndicator = appendMessage('ai', '', true);
        
        // Get user location if available
        let requestBody = { message: message };
        
        // Try to get user location from localStorage or geolocation
        if (navigator.geolocation) {
            const savedLocation = localStorage.getItem('userLocation');
            if (savedLocation) {
                try {
                    const location = JSON.parse(savedLocation);
                    if (location.lat && location.lng) {
                        requestBody.user_lat = location.lat;
                        requestBody.user_lng = location.lng;
                    }
                } catch (e) {
                    console.error('Error parsing saved location:', e);
                }
            } else {
                // Try to get current location
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        requestBody.user_lat = position.coords.latitude;
                        requestBody.user_lng = position.coords.longitude;
                        // Save to localStorage
                        localStorage.setItem('userLocation', JSON.stringify({
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        }));
                    },
                    function(error) {
                        console.log('Geolocation error:', error);
                    },
                    { timeout: 5000 }
                );
            }
        }
        
        // Send to API
        fetch('{{ route('user.ai.chat.send') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(requestBody)
        })
        .then(response => response.json())
        .then(data => {
            // Remove typing indicator
            typingIndicator.remove();
            
            if (data.success) {
                // Check if reply contains HTML tags
                const replyText = data.reply;
                const hasHtml = /<[a-z][\s\S]*>/i.test(replyText);
                
                if (hasHtml) {
                    // Parse HTML and add data-no-loading to pesan links
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = replyText;
                    
                    // Find and mark pesan/WhatsApp links
                    const links = tempDiv.querySelectorAll('a');
                    links.forEach(link => {
                        const href = link.getAttribute('href') || '';
                        const linkText = link.textContent.toLowerCase();
                        if (href.includes('wa.me') || href.includes('whatsapp') || linkText.includes('pesan')) {
                            link.setAttribute('data-no-loading', 'true');
                        }
                    });
                    
                    // Find and mark pesan buttons
                    const buttons = tempDiv.querySelectorAll('button');
                    buttons.forEach(btn => {
                        const onclick = btn.getAttribute('onclick') || '';
                        const btnText = btn.textContent.toLowerCase();
                        if (onclick.includes('wa.me') || onclick.includes('whatsapp') || onclick.includes('pesan') || btnText.includes('pesan')) {
                            btn.setAttribute('data-no-loading', 'true');
                        }
                    });
                    
                    appendMessage('ai', tempDiv.innerHTML, false, true);
                } else {
                    appendMessage('ai', replyText);
                }
                
                // Display layanan cards if available - as a separate new message
                // Wait a bit to ensure the previous message is fully rendered
                setTimeout(() => {
                    if (data.layanan && data.layanan.length > 0) {
                        displayLayananCards(data.layanan);
                    }
                    // Display UMKM cards if available
                    if (data.umkm && data.umkm.length > 0) {
                        displayUmkmCards(data.umkm);
                    }
                }, 300);
            } else {
                appendMessage('ai', data.message || 'Maaf, terjadi kesalahan. Silakan coba lagi.');
            }
        })
        .catch(error => {
            typingIndicator.remove();
            appendMessage('ai', 'Maaf, tidak dapat terhubung ke server AI. Silakan coba lagi nanti.');
            console.error('Error:', error);
        })
        .finally(() => {
            // Re-enable input
            messageInput.disabled = false;
            sendBtn.disabled = false;
            messageInput.focus();
        });
    }

    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const message = messageInput.value.trim();
        if (message) {
            sendMessage(message);
        }
    });

    // Quick question function
    function sendQuickQuestion(question) {
        messageInput.value = question;
        sendMessage(question);
    }

    // Clear chat function
    function clearChat() {
        Swal.fire({
            title: 'Hapus Semua Pesan?',
            text: 'Apakah Anda yakin ingin menghapus semua pesan?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const welcomeMessage = chatBox.querySelector('.flex.items-start.gap-3');
                chatBox.innerHTML = '';
                if (welcomeMessage) {
                    chatBox.appendChild(welcomeMessage);
                }
                Swal.fire({
                    title: 'Berhasil!',
                    text: 'Semua pesan telah dihapus',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        });
    }

    // Focus input on load
    messageInput.focus();
    
    // Allow Enter to send (Shift+Enter for new line)
    messageInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            chatForm.dispatchEvent(new Event('submit'));
        }
    });

    // Display layanan cards as a new AI message
    function displayLayananCards(layananArray) {
        if (!layananArray || layananArray.length === 0) return;
        
        // Create a new AI message container for the cards
        const messageDiv = document.createElement('div');
        messageDiv.className = 'flex items-start gap-3';
        
        const time = new Date().toLocaleTimeString('id-ID', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
        
        // Create cards container
        const cardsContainer = document.createElement('div');
        cardsContainer.className = 'space-y-3 w-full';
        cardsContainer.id = 'layanan-cards-container';
        
        layananArray.forEach(layanan => {
            const card = createLayananCard(layanan);
            cardsContainer.appendChild(card);
        });
        
        // Build the message HTML - cards will be inserted after this
        const messageContent = document.createElement('div');
        messageContent.className = 'flex-1';
        
        const messageBubble = document.createElement('div');
        messageBubble.className = 'bg-white rounded-2xl rounded-tl-none px-4 py-3 shadow-sm';
        
        const titleText = document.createElement('p');
        titleText.className = 'text-gray-800 mb-3 font-medium';
        titleText.textContent = 'Berikut adalah rekomendasi menu yang tersedia:';
        messageBubble.appendChild(titleText);
        messageBubble.appendChild(cardsContainer);
        
        messageContent.appendChild(messageBubble);
        
        const timeSpan = document.createElement('span');
        timeSpan.className = 'text-xs text-gray-500 mt-1 block ml-2';
        timeSpan.textContent = time;
        messageContent.appendChild(timeSpan);
        
        const robotIcon = document.createElement('div');
        robotIcon.className = 'w-8 h-8 bg-gradient-to-br from-purple-500 to-blue-500 rounded-full flex items-center justify-center flex-shrink-0';
        robotIcon.innerHTML = '<i class="fas fa-robot text-white text-sm"></i>';
        
        messageDiv.appendChild(robotIcon);
        messageDiv.appendChild(messageContent);
        
        // Append as new message to chat box
        chatBox.appendChild(messageDiv);
        
        // Scroll to bottom
        chatBox.scrollTop = chatBox.scrollHeight;
        
        // Add data-no-loading to all links in cards
        setTimeout(() => {
            const links = messageDiv.querySelectorAll('a[href]');
            links.forEach(link => {
                link.setAttribute('data-no-loading', 'true');
            });
        }, 100);
    }

    // Create layanan card
    function createLayananCard(layanan) {
        const card = document.createElement('div');
        card.className = 'bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow cursor-pointer';
        card.onclick = () => window.location.href = layanan.url;
        
        const imageUrl = layanan.photo_path || 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="200" height="150" viewBox="0 0 200 150"%3E%3Crect fill="%23e5e7eb" width="200" height="150"/%3E%3Ctext fill="%239ca3af" font-family="Arial" font-size="14" x="50%25" y="50%25" text-anchor="middle" dy=".3em"%3ENo Image%3C/text%3E%3C/svg%3E';
        
        card.innerHTML = `
            <div class="flex gap-3 p-3">
                <div class="w-20 h-20 md:w-24 md:h-24 bg-gray-200 rounded-lg overflow-hidden flex-shrink-0">
                    <img src="${imageUrl}" alt="${escapeHtml(layanan.nama)}" class="w-full h-full object-cover" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\\'http://www.w3.org/2000/svg\\' width=\\'100\\' height=\\'100\\' viewBox=\\'0 0 100 100\\'%3E%3Crect fill=\\'%23e5e7eb\\' width=\\'100\\' height=\\'100\\'/%3E%3Ctext fill=\\'%239ca3af\\' font-family=\\'Arial\\' font-size=\\'12\\' x=\\'50%25\\' y=\\'50%25\\' text-anchor=\\'middle\\' dy=\\'.3em\\'%3ENo Image%3C/text%3E%3C/svg%3E'">
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="font-bold text-gray-900 text-sm md:text-base mb-1 line-clamp-1">${escapeHtml(layanan.nama)}</h4>
                    <p class="text-xs text-gray-600 mb-2 line-clamp-2">${escapeHtml(layanan.description || 'Tidak ada deskripsi')}</p>
                    <div class="flex items-center gap-2 mb-2">
                        <div class="flex items-center gap-1">
                            <i class="fas fa-store text-[#218689] text-xs"></i>
                            <span class="text-xs text-gray-700 font-medium truncate">${escapeHtml(layanan.umkm.nama)}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 mb-2">
                        ${layanan.rating_layanan > 0 ? `
                            <div class="flex items-center gap-1">
                                <div class="flex items-center">
                                    ${generateStars(layanan.rating_layanan)}
                                </div>
                                <span class="text-xs text-gray-600">Menu: ${layanan.rating_layanan}</span>
                            </div>
                        ` : ''}
                        ${layanan.rating_umkm > 0 ? `
                            <div class="flex items-center gap-1">
                                <div class="flex items-center">
                                    ${generateStars(layanan.rating_umkm)}
                                </div>
                                <span class="text-xs text-gray-600">Toko: ${layanan.rating_umkm}</span>
                            </div>
                        ` : ''}
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-bold text-[#009b97]">Rp ${formatNumber(layanan.price)}</span>
                        <a href="${layanan.url}" 
                           data-no-loading="true"
                           onclick="event.stopPropagation()"
                           class="bg-[#009b97] hover:bg-[#007a77] text-white text-xs px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1">
                            <span>Lihat Layanan</span>
                            <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>
            </div>
        `;
        
        return card;
    }

    // Generate star rating HTML
    function generateStars(rating) {
        let starsHtml = '';
        const fullStars = Math.floor(rating);
        const hasHalfStar = rating % 1 >= 0.5;
        
        for (let i = 1; i <= 5; i++) {
            if (i <= fullStars) {
                starsHtml += '<i class="fas fa-star text-yellow-400 text-xs"></i>';
            } else if (i === fullStars + 1 && hasHalfStar) {
                starsHtml += '<i class="fas fa-star-half-alt text-yellow-400 text-xs"></i>';
            } else {
                starsHtml += '<i class="far fa-star text-gray-300 text-xs"></i>';
            }
        }
        
        return starsHtml;
    }

    // Format number with thousand separator
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }
</script>
@endsection


