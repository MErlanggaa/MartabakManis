import { useEffect, useRef, useState } from 'react';
import { Icon } from '@iconify/react';
import { getCsrfToken } from '../../utils/orderPricing';

export default function OrderChat({ orderId, currentRole }) {
    const [chats, setChats] = useState([]);
    const [message, setMessage] = useState('');
    const [sending, setSending] = useState(false);
    const bottomRef = useRef(null);
    const lastCountRef = useRef(0);

    const fetchChats = async () => {
        try {
            const res = await fetch(`/api/orders/${orderId}/chat`, {
                headers: { Accept: 'application/json' },
            });
            const data = await res.json();
            const newChats = data.chats || [];
            if (newChats.length !== lastCountRef.current) {
                lastCountRef.current = newChats.length;
                setChats(newChats);
                // Mark as read
                fetch(`/api/orders/${orderId}/chat/read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        Accept: 'application/json',
                    },
                });
            }
        } catch { /* ignore */ }
    };

    const sendMessage = async (e) => {
        e.preventDefault();
        if (!message.trim() || sending) return;
        setSending(true);
        try {
            const res = await fetch(`/api/orders/${orderId}/chat`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    Accept: 'application/json',
                },
                body: JSON.stringify({ message: message.trim() }),
            });
            const data = await res.json();
            if (data.success) {
                setMessage('');
                await fetchChats();
            }
        } catch { /* ignore */ } finally {
            setSending(false);
        }
    };

    useEffect(() => {
        fetchChats();
        const interval = setInterval(fetchChats, 3000);
        return () => clearInterval(interval);
    }, [orderId]);

    useEffect(() => {
        bottomRef.current?.scrollIntoView({ behavior: 'smooth' });
    }, [chats]);

    const isMe = (chat) => chat.sender_type === currentRole;

    return (
        <div className="flex flex-col rounded-2xl border border-gray-200 bg-white overflow-hidden" style={{ height: 420 }}>
            {/* Header */}
            <div className="flex items-center gap-3 border-b border-gray-100 bg-gradient-to-r from-[#009b97] to-[#007a77] px-4 py-3 text-white">
                <Icon icon="lucide:message-square" className="w-5 h-5 text-white" />
                <div>
                    <p className="font-bold text-white text-sm">Chat dengan {currentRole === 'user' ? 'UMKM / Driver' : 'Pelanggan'}</p>
                    <p className="text-white/70 text-xs">Pesan update otomatis setiap 3 detik</p>
                </div>
            </div>

            {/* Messages */}
            <div className="flex-1 overflow-y-auto p-4 space-y-3 bg-slate-50">
                {chats.length === 0 && (
                    <div className="flex flex-col items-center justify-center h-full text-gray-400">
                        <Icon icon="lucide:message-square" className="w-10 h-10 mb-2 text-slate-300 animate-pulse" />
                        <p className="text-sm">Belum ada pesan. Mulai obrolan!</p>
                    </div>
                )}
                {chats.map((chat) => (
                    <div
                        key={chat.id}
                        className={`flex ${isMe(chat) ? 'justify-end' : 'justify-start'}`}
                    >
                        <div
                            className={`max-w-[75%] rounded-2xl px-4 py-2 text-sm shadow-sm ${
                                isMe(chat)
                                    ? 'rounded-br-sm bg-[#009b97] text-white'
                                    : 'rounded-bl-sm bg-white text-gray-800 border border-gray-100'
                            }`}
                        >
                            {!isMe(chat) && (
                                <p className={`text-xs font-bold mb-1 flex items-center gap-1 ${isMe(chat) ? 'text-white/70' : 'text-[#009b97]'}`}>
                                    {chat.sender_type === 'umkm'
                                        ? <><Icon icon="lucide:store" className="w-3 h-3" /> UMKM / Driver</>
                                        : <><Icon icon="lucide:user" className="w-3 h-3" /> Anda</>
                                    }
                                </p>
                            )}
                            <p className="leading-relaxed">{chat.message}</p>
                            <p className={`text-xs mt-1 text-right ${isMe(chat) ? 'text-white/60' : 'text-gray-400'}`}>
                                {chat.created_at}
                                {isMe(chat) && <span className="ml-1">{chat.is_read ? '✓✓' : '✓'}</span>}
                            </p>
                        </div>
                    </div>
                ))}
                <div ref={bottomRef} />
            </div>

            {/* Input */}
            <form onSubmit={sendMessage} data-no-loading="true" className="flex gap-2 border-t border-gray-100 bg-white p-3">
                <input
                    type="text"
                    value={message}
                    onChange={(e) => setMessage(e.target.value)}
                    placeholder="Ketik pesan..."
                    className="flex-1 rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-[#009b97] focus:outline-none focus:ring-2 focus:ring-[#009b97]/20"
                />
                <button
                    type="submit"
                    disabled={sending || !message.trim()}
                    className="flex items-center justify-center rounded-xl bg-[#009b97] px-4 py-2 text-white hover:bg-[#007a77] transition disabled:opacity-50"
                >
                    {sending ? (
                        <span className="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
                    ) : (
                        <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                    )}
                </button>
            </form>
        </div>
    );
}
