@extends('layouts.app')

@section('title', 'AI Konsultasi - UMKM')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-500 rounded-xl flex items-center justify-center text-white">
                        <i class="fas fa-robot text-xl"></i>
                    </div>
                    AI Konsultasi Bisnis
                </h1>
                <p class="text-gray-600 mt-2">Dapatkan saran strategis untuk mengembangkan UMKM Anda</p>
            </div>
            <button onclick="loadBusinessTips()" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg transition-colors inline-flex items-center gap-2">
                <i class="fas fa-lightbulb"></i> Tips Bisnis
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Chat Interface -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <!-- Chat Header -->
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                            <i class="fas fa-comments"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold">Konsultasi dengan AI</h3>
                            <p class="text-sm text-blue-100">AI Konsultan siap membantu Anda</p>
                        </div>
                    </div>
                </div>

                <!-- Chat Messages -->
                <div id="chat-messages" class="h-[500px] overflow-y-auto p-6 bg-gray-50 space-y-4">
                    <!-- Welcome Message -->
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-robot text-white"></i>
                        </div>
                        <div class="flex-1">
                            <div class="bg-white rounded-2xl rounded-tl-none px-4 py-3 shadow-sm">
                                <p class="text-gray-800">
                                    <strong>AI Konsultan:</strong> Halo! Saya di sini untuk membantu mengembangkan bisnis UMKM Anda. Silakan ajukan pertanyaan tentang strategi bisnis, pemasaran, keuangan, atau aspek lainnya yang ingin Anda ketahui.
                                </p>
                            </div>
                            <span class="text-xs text-gray-500 mt-1 block ml-2">{{ now()->format('H:i') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Chat Input -->
                <div class="border-t bg-white p-4">
                    <form id="chat-form" class="flex gap-3">
                        @csrf
                        <div class="flex-1 relative">
                            <input type="text" 
                                   id="message-input" 
                                   placeholder="Tulis pertanyaan Anda di sini..." 
                                   maxlength="1000" 
                                   required
                                   autocomplete="off"
                                   class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <button type="submit" 
                                    id="send-btn"
                                    class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
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
                        <button onclick="sendQuickQuestion('Bagaimana cara meningkatkan penjualan?')" 
                                class="text-xs bg-blue-100 hover:bg-blue-200 text-blue-700 px-3 py-1 rounded-full transition-colors">
                            📈 Meningkatkan Penjualan
                        </button>
                        <button onclick="sendQuickQuestion('Strategi digital marketing apa yang efektif?')" 
                                class="text-xs bg-purple-100 hover:bg-purple-200 text-purple-700 px-3 py-1 rounded-full transition-colors">
                            💻 Digital Marketing
                        </button>
                        <button onclick="sendQuickQuestion('Bagaimana mengelola keuangan bisnis?')" 
                                class="text-xs bg-green-100 hover:bg-green-200 text-green-700 px-3 py-1 rounded-full transition-colors">
                            💰 Manajemen Keuangan
                        </button>
                        <button onclick="sendQuickQuestion('Cara mendapatkan modal untuk bisnis?')" 
                                class="text-xs bg-yellow-100 hover:bg-yellow-200 text-yellow-700 px-3 py-1 rounded-full transition-colors">
                            💵 Mendapatkan Modal
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Business Tips -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="bg-gradient-to-r from-green-500 to-emerald-600 text-white p-4">
                    <h3 class="font-semibold flex items-center gap-2">
                        <i class="fas fa-lightbulb"></i> Tips Bisnis
                    </h3>
                </div>
                <div id="business-tips" class="p-4">
                    <div class="text-center py-8">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
                        <p class="mt-2 text-gray-600 text-sm">Memuat tips bisnis...</p>
                    </div>
                </div>
            </div>

            <!-- Quick Questions -->
            {{-- <div class="bg-white rounded-xl shadow-sm p-4">
                <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="fas fa-question-circle text-blue-500"></i> Pertanyaan Cepat
                </h3>
                <div class="space-y-2">
                    <button onclick="sendQuickQuestion('Bagaimana cara meningkatkan penjualan?')" 
                            class="w-full text-left px-4 py-3 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg transition-colors flex items-center gap-2">
                        <i class="fas fa-chart-line"></i>
                        <span class="text-sm">Meningkatkan Penjualan</span>
                    </button>
                    <button onclick="sendQuickQuestion('Strategi digital marketing apa yang efektif?')" 
                            class="w-full text-left px-4 py-3 bg-purple-50 hover:bg-purple-100 text-purple-700 rounded-lg transition-colors flex items-center gap-2">
                        <i class="fas fa-digital-tachograph"></i>
                        <span class="text-sm">Digital Marketing</span>
                    </button>
                    <button onclick="sendQuickQuestion('Bagaimana mengelola keuangan bisnis?')" 
                            class="w-full text-left px-4 py-3 bg-green-50 hover:bg-green-100 text-green-700 rounded-lg transition-colors flex items-center gap-2">
                        <i class="fas fa-money-bill-wave"></i>
                        <span class="text-sm">Manajemen Keuangan</span>
                    </button>
                    <button onclick="sendQuickQuestion('Cara mendapatkan modal untuk bisnis?')" 
                            class="w-full text-left px-4 py-3 bg-yellow-50 hover:bg-yellow-100 text-yellow-700 rounded-lg transition-colors flex items-center gap-2">
                        <i class="fas fa-coins"></i>
                        <span class="text-sm">Mendapatkan Modal</span>
                    </button>
                </div>
            </div> --}}
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let isLoading = false;

    // Load business tips on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadBusinessTips();
    });

    // Chat form submission
    document.getElementById('chat-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (isLoading) return;
        
        const messageInput = document.getElementById('message-input');
        const message = messageInput.value.trim();
        
        if (!message) return;
        
        // Add user message to chat
        addMessage(message, 'user');
        messageInput.value = '';
        
        // Send to AI
        sendToAI(message);
    });

    function addMessage(content, sender) {
        const chatMessages = document.getElementById('chat-messages');
        const messageDiv = document.createElement('div');
        
        const now = new Date();
        const timeString = now.getHours().toString().padStart(2, '0') + ':' + 
                          now.getMinutes().toString().padStart(2, '0');
        
        if (sender === 'user') {
            messageDiv.className = 'flex items-start gap-3 flex-row-reverse';
            messageDiv.innerHTML = `
                <div class="flex-1 flex flex-col items-end">
                    <div class="bg-blue-600 text-white rounded-2xl rounded-tr-none px-4 py-3 shadow-sm max-w-[80%]">
                        <p class="whitespace-pre-wrap">${escapeHtml(content)}</p>
                    </div>
                    <span class="text-xs text-gray-500 mt-1 mr-2">${timeString}</span>
                </div>
            `;
        } else {
            messageDiv.className = 'flex items-start gap-3';
            messageDiv.innerHTML = `
                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-robot text-white text-sm"></i>
                </div>
                <div class="flex-1">
                    <div class="bg-white rounded-2xl rounded-tl-none px-4 py-3 shadow-sm">
                        <p class="text-gray-800 whitespace-pre-wrap">
                            <strong>AI Konsultan:</strong> ${escapeHtml(content)}
                        </p>
                    </div>
                    <span class="text-xs text-gray-500 mt-1 block ml-2">${timeString}</span>
                </div>
            `;
        }
        
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function sendToAI(message) {
        isLoading = true;
        const sendBtn = document.getElementById('send-btn');
        const messageInput = document.getElementById('message-input');
        
        sendBtn.disabled = true;
        sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        messageInput.disabled = true;
        
        // Show typing indicator
        const typingIndicator = document.createElement('div');
        typingIndicator.className = 'flex items-start gap-3';
        typingIndicator.id = 'typing-indicator';
        typingIndicator.innerHTML = `
            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center flex-shrink-0 animate-pulse">
                <i class="fas fa-robot text-white text-sm"></i>
            </div>
            <div class="flex-1">
                <div class="bg-white rounded-2xl rounded-tl-none px-4 py-3 shadow-sm">
                    <div class="flex gap-1">
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
                    </div>
                </div>
            </div>
        `;
        document.getElementById('chat-messages').appendChild(typingIndicator);
        document.getElementById('chat-messages').scrollTop = document.getElementById('chat-messages').scrollHeight;
        
        fetch('{{ route("umkm.ai-consultation.chat") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ message: message })
        })
        .then(response => {
            const status = response.status;
            return response.json().then(data => ({ status, data }));
        })
        .then(({ status, data }) => {
            // Remove typing indicator
            const typing = document.getElementById('typing-indicator');
            if (typing) typing.remove();
            
            if (data.success) {
                addMessage(data.response, 'ai');
            } else {
                // Check if it's a rate limit error (429)
                if (status === 429) {
                    addMessage('⚠️ ' + (data.message || 'Maaf, batas penggunaan API telah tercapai. Free tier memiliki limit 10 request per menit. Silakan tunggu sebentar dan coba lagi.'), 'ai');
                } else {
                    addMessage(data.message || 'Maaf, terjadi kesalahan. Silakan coba lagi.', 'ai');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const typing = document.getElementById('typing-indicator');
            if (typing) typing.remove();
            addMessage('Maaf, layanan AI sedang tidak tersedia. Silakan coba lagi nanti.', 'ai');
        })
        .finally(() => {
            isLoading = false;
            sendBtn.disabled = false;
            sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
            messageInput.disabled = false;
            messageInput.focus();
        });
    }

    function sendQuickQuestion(question) {
        const messageInput = document.getElementById('message-input');
        messageInput.value = question;
        document.getElementById('chat-form').dispatchEvent(new Event('submit'));
    }

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
                const chatMessages = document.getElementById('chat-messages');
                const welcomeMessage = chatMessages.querySelector('.flex.items-start.gap-3');
                chatMessages.innerHTML = '';
                if (welcomeMessage) {
                    chatMessages.appendChild(welcomeMessage);
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

    function loadBusinessTips() {
        const tipsContainer = document.getElementById('business-tips');
        tipsContainer.innerHTML = `
            <div class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
                <p class="mt-2 text-gray-600 text-sm">Memuat tips bisnis...</p>
            </div>
        `;
        
        fetch('{{ route("umkm.ai-consultation.tips") }}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayBusinessTips(data.tips);
                } else {
                    tipsContainer.innerHTML = '<p class="text-gray-500 text-sm text-center py-4">Gagal memuat tips bisnis.</p>';
                }
            })
            .catch(error => {
                console.error('Error loading tips:', error);
                tipsContainer.innerHTML = '<p class="text-gray-500 text-sm text-center py-4">Gagal memuat tips bisnis.</p>';
            });
    }

    function displayBusinessTips(tips) {
        const tipsContainer = document.getElementById('business-tips');
        let html = '<div class="space-y-3">';
        
        tips.forEach((tip, index) => {
            html += `
                <div class="flex items-start gap-3 p-3 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                    <div class="w-6 h-6 bg-green-600 text-white rounded-full flex items-center justify-center flex-shrink-0 text-xs font-semibold">
                        ${index + 1}
                    </div>
                    <p class="text-sm text-gray-700 flex-1">${escapeHtml(tip)}</p>
                </div>
            `;
        });
        
        html += '</div>';
        tipsContainer.innerHTML = html;
    }

    // Allow Enter to send (Shift+Enter for new line)
    document.getElementById('message-input').addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            document.getElementById('chat-form').dispatchEvent(new Event('submit'));
        }
    });
</script>
@endsection
