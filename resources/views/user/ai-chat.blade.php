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
            <form id="chat-form" class="flex gap-3">
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

    function appendMessage(sender, text, isTyping = false) {
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
            messageDiv.innerHTML = `
                <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-blue-500 rounded-full flex items-center justify-center flex-shrink-0 ${typingClass}">
                    <i class="fas fa-robot text-white text-sm"></i>
                </div>
                <div class="flex-1">
                    <div class="bg-white rounded-2xl rounded-tl-none px-4 py-3 shadow-sm">
                        ${isTyping ? '<div class="flex gap-1"><div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div><div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div><div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.4s"></div></div>' : '<p class="text-gray-800 whitespace-pre-wrap">' + escapeHtml(text) + '</p>'}
                    </div>
                    ${!isTyping ? `<span class="text-xs text-gray-500 mt-1 block ml-2">${time}</span>` : ''}
                </div>
            `;
        }
        
        chatBox.appendChild(messageDiv);
        chatBox.scrollTop = chatBox.scrollHeight;
        
        return messageDiv;
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
        
        // Send to API
        fetch('{{ route('user.ai.chat.send') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ message: message })
        })
        .then(response => response.json())
        .then(data => {
            // Remove typing indicator
            typingIndicator.remove();
            
            if (data.success) {
                appendMessage('ai', data.reply);
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
</script>
@endsection


