import { useEffect, useRef, useState } from 'react';
import { Icon } from '@iconify/react';
import { formatRupiah, getCsrfToken } from '../utils/orderPricing';
import OrderChat from './order/OrderChat';

let sharedAudioCtx = null;
const initAudioContext = () => {
    if (typeof window === 'undefined') return;
    if (!sharedAudioCtx) {
        sharedAudioCtx = new (window.AudioContext || window.webkitAudioContext)();
    }
    if (sharedAudioCtx.state === 'suspended') {
        sharedAudioCtx.resume();
    }
};
if (typeof window !== 'undefined') {
    window.addEventListener('click', initAudioContext, { once: true });
    window.addEventListener('touchstart', initAudioContext, { once: true });
}

const STATUS_LABELS = {
    pending: 'Menunggu',
    confirmed: 'Dikonfirmasi',
    processing: 'Diproses',
    delivered: 'Selesai',
    cancelled: 'Dibatalkan',
};

export default function UmkmOrderAlert() {
    const [pendingOrders, setPendingOrders] = useState([]);
    const [allOrders, setAllOrders] = useState([]);
    const [showAlert, setShowAlert] = useState(false);
    const [alertOrder, setAlertOrder] = useState(null);
    const knownIds = useRef(new Set());
    const knownChatCounts = useRef({});
    const isFirstLoad = useRef(true);

    // Driver Modal State
    const [driverModalOrder, setDriverModalOrder] = useState(null);
    const [driverName, setDriverName] = useState('');
    const [driverPhone, setDriverPhone] = useState('');
    const [driverCode, setDriverCode] = useState('');
    const [driverLoading, setDriverLoading] = useState(false);
    const [driverError, setDriverError] = useState('');

    // Chat Modal State
    const [activeChatOrder, setActiveChatOrder] = useState(null);

    // Lightbox State for driver photo full screen
    const [lightboxPhoto, setLightboxPhoto] = useState(null);

    const fetchPending = async () => {
        try {
            const res = await fetch('/api/umkm/orders/pending', {
                headers: { Accept: 'application/json' },
            });
            const data = await res.json();
            const orders = data.orders || [];

            for (const order of orders) {
                if (!knownIds.current.has(order.id)) {
                    knownIds.current.add(order.id);
                    if (!isFirstLoad.current) {
                        setAlertOrder(order);
                        setShowAlert(true);
                        playNotificationSound();
                    }
                }
            }
            setPendingOrders(orders);
        } catch {
            /* polling error — ignore */
        }
    };

    const fetchAll = async () => {
        try {
            const res = await fetch('/api/umkm/orders', {
                headers: { Accept: 'application/json' },
            });
            const data = await res.json();
            const orders = data.orders || [];

            let hasNewChat = false;
            orders.forEach(order => {
                const prevCount = knownChatCounts.current[order.id] || 0;
                const currentCount = order.unread_chat_count || 0;
                if (currentCount > prevCount) {
                    if (!isFirstLoad.current) {
                        hasNewChat = true;
                    }
                }
                knownChatCounts.current[order.id] = currentCount;
            });

            if (hasNewChat) {
                playNotificationSound();
            }

            setAllOrders(orders);
        } catch {
            /* ignore */
        }
    };

    const playNotificationSound = () => {
        try {
            initAudioContext();
            const ctx = sharedAudioCtx || new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);

            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, ctx.currentTime);
            osc.frequency.exponentialRampToValueAtTime(660, ctx.currentTime + 0.15);

            gain.gain.setValueAtTime(0.15, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.35);

            osc.start();
            osc.stop(ctx.currentTime + 0.4);
        } catch {
            /* audio not available */
        }
    };

    const markSeen = async (orderIds) => {
        await fetch('/api/umkm/orders/mark-seen', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                Accept: 'application/json',
            },
            body: JSON.stringify({ order_ids: orderIds }),
        });
        fetchPending();
        fetchAll();
    };

    const updateStatus = async (orderId, status) => {
        await fetch(`/api/umkm/orders/${orderId}/status`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                Accept: 'application/json',
            },
            body: JSON.stringify({ order_status: status }),
        });
        fetchAll();
        fetchPending();
    };

    const handleOpenDriverModal = (order) => {
        setDriverModalOrder(order);
        setDriverName(order.driver_name || '');
        setDriverPhone(order.driver_phone || '');
        setDriverCode(order.driver_code || '');
        setDriverError('');
    };

    const handleSaveDriverInfo = async (e) => {
        e.preventDefault();
        if (!driverName.trim()) {
            setDriverError('Nama driver wajib diisi.');
            return;
        }
        setDriverLoading(true);
        setDriverError('');

        try {
            const formData = new FormData();
            formData.append('_method', 'PUT');
            formData.append('driver_name', driverName.trim());
            formData.append('driver_phone', driverPhone.trim());
            formData.append('driver_code', driverCode.trim());

            const photoInput = document.getElementById('driver_photo');
            if (photoInput && photoInput.files[0]) {
                formData.append('driver_photo', photoInput.files[0]);
            }

            const res = await fetch(`/api/umkm/orders/${driverModalOrder.id}/driver`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    Accept: 'application/json',
                },
                body: formData,
            });

            const data = await res.json();
            if (!res.ok || !data.success) {
                throw new Error(data.message || 'Gagal menyimpan info driver.');
            }

            setDriverModalOrder(null);
            fetchAll();
        } catch (err) {
            setDriverError(err.message);
        } finally {
            setDriverLoading(false);
        }
    };

    const dismissAlert = () => {
        if (alertOrder) {
            markSeen([alertOrder.id]);
        }
        setShowAlert(false);
        setAlertOrder(null);
    };

    useEffect(() => {
        const initData = async () => {
            await fetchAll();
            await fetchPending();
            isFirstLoad.current = false;
        };
        initData();
        const interval = setInterval(() => {
            fetchPending();
            fetchAll();
        }, 5000);
        return () => clearInterval(interval);
    }, []);

    return (
        <>
            {showAlert && alertOrder && (
                <div className="fixed inset-0 z-[99999] flex items-center justify-center bg-black/50 p-4">
                    <div className="w-full max-w-md animate-bounce-once rounded-2xl bg-white p-6 shadow-2xl">
                        <div className="mb-4 flex items-center gap-3">
                            <div className="flex h-14 w-14 items-center justify-center rounded-full bg-[#009b97] text-white">
                                <Icon icon="lucide:bell" className="w-7 h-7" />
                            </div>
                            <div>
                                <h3 className="text-xl font-bold text-gray-900">Pesanan Baru!</h3>
                                <p className="text-sm text-gray-500">{alertOrder.order_code}</p>
                            </div>
                        </div>
                        <div className="mb-4 space-y-2 rounded-xl bg-gray-50 p-4 text-sm">
                            <p><strong>Produk:</strong> {alertOrder.layanan_name}</p>
                            <p><strong>Jumlah:</strong> {alertOrder.quantity}x</p>
                            <p><strong>Total:</strong> {formatRupiah(alertOrder.total)}</p>
                            <p><strong>Pengantar:</strong> {alertOrder.delivery_label}</p>
                            <p><strong>Pembeli:</strong> {alertOrder.customer_name}</p>
                            <p><strong>HP:</strong> {alertOrder.customer_phone}</p>
                            <p><strong>Alamat:</strong> {alertOrder.customer_address}</p>
                        </div>
                        <div className="flex gap-3">
                            <button
                                onClick={() => { updateStatus(alertOrder.id, 'processing'); dismissAlert(); }}
                                className="flex-1 rounded-xl bg-[#009b97] py-3 font-bold text-white hover:bg-[#007a77] flex items-center justify-center gap-1"
                            >
                                <Icon icon="lucide:chef-hat" className="w-4 h-4" />
                                Proses Pesanan
                            </button>
                            <button
                                onClick={dismissAlert}
                                className="rounded-xl border border-gray-300 px-4 py-3 font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {pendingOrders.length > 0 && !showAlert && (
                <div className="mb-6 rounded-xl border border-amber-300 bg-amber-50 p-4">
                    <div className="flex items-center gap-3">
                        <span className="flex h-10 w-10 items-center justify-center rounded-full bg-amber-400 text-white font-bold">
                            {pendingOrders.length}
                        </span>
                        <div className="flex-1">
                            <p className="font-bold text-amber-900">Ada pesanan baru belum dilihat!</p>
                            <p className="text-sm text-amber-700">Klik untuk melihat detail pesanan.</p>
                        </div>
                        <button
                            onClick={() => { setAlertOrder(pendingOrders[0]); setShowAlert(true); }}
                            className="rounded-lg bg-amber-500 px-4 py-2 text-sm font-bold text-white hover:bg-amber-600 flex items-center gap-1"
                        >
                            <Icon icon="lucide:eye" className="w-4 h-4" />
                            Lihat
                        </button>
                    </div>
                </div>
            )}

            <div className="rounded-xl border border-gray-100 bg-white shadow-sm">
                <div className="border-b border-gray-100 px-6 py-4">
                    <h2 className="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <Icon icon="lucide:clipboard-list" className="w-5 h-5 text-[#009b97]" />
                        Daftar Pesanan
                    </h2>
                </div>
                {allOrders.length === 0 ? (
                    <div className="p-8 text-center text-gray-500">
                        Belum ada pesanan masuk.
                    </div>
                ) : (
                    <div className="divide-y divide-gray-100">
                        {allOrders.map((order) => (
                            <div key={order.id} className="p-4 hover:bg-gray-50">
                                <div className="flex flex-wrap items-start justify-between gap-2">
                                    <div>
                                        <p className="font-bold text-gray-900">{order.layanan_name}</p>
                                        <p className="text-sm text-gray-500">{order.order_code} · {order.created_at}</p>
                                        <p className="mt-1 text-sm text-gray-600">
                                            {order.customer_name} · {order.delivery_label}
                                        </p>
                                        {order.driver_name && (
                                            <div className="flex items-center gap-2 mt-2">
                                                <div
                                                    className="w-16 h-16 rounded-xl border border-amber-300 bg-amber-50 overflow-hidden flex items-center justify-center flex-shrink-0 cursor-pointer hover:opacity-80 transition-opacity"
                                                    title="Klik untuk memperbesar & download"
                                                    onClick={() => setLightboxPhoto(order.driver_photo)}
                                                >
                                                    {order.driver_photo ? (
                                                        <img src={order.driver_photo} alt={order.driver_name} className="w-full h-full object-cover" />
                                                    ) : (
                                                        <Icon icon="material-symbols:moped-outline" className="w-4 h-4 text-amber-600" />
                                                    )}
                                                </div>
                                                <span className="text-xs text-amber-700 font-semibold">
                                                    Kurir: {order.driver_name} ({order.driver_phone})
                                                </span>
                                            </div>
                                        )}
                                    </div>
                                    <div className="text-right">
                                        <p className="font-bold text-[#009b97]">{formatRupiah(order.total)}</p>
                                        <span className={`mt-1 inline-block rounded-full px-2 py-0.5 text-xs font-medium ${order.payment_status === 'paid'
                                            ? 'bg-green-100 text-green-800'
                                            : 'bg-yellow-100 text-yellow-800'
                                            }`}>
                                            {order.payment_status === 'paid' ? 'Lunas' : order.payment_status}
                                        </span>
                                    </div>
                                </div>
                                {order.payment_status === 'paid' && order.order_status !== 'delivered' && order.order_status !== 'cancelled' && (
                                    <div className="mt-3 flex flex-wrap gap-2 items-center">
                                        {order.order_status === 'confirmed' && (
                                            <button
                                                onClick={() => updateStatus(order.id, 'processing')}
                                                className="rounded-lg bg-blue-500 px-3 py-1.5 text-xs font-bold text-white hover:bg-blue-600 flex items-center gap-1"
                                            >
                                                <Icon icon="lucide:play" className="w-3 h-3" />
                                                Mulai Proses
                                            </button>
                                        )}
                                        {order.order_status === 'processing' && (
                                            <>
                                                <button
                                                    disabled={!order.driver_name || !order.driver_phone}
                                                    onClick={() => updateStatus(order.id, 'delivered')}
                                                    className={`rounded-lg px-3 py-1.5 text-xs font-bold flex items-center gap-1 ${order.driver_name && order.driver_phone
                                                            ? 'bg-green-500 text-white hover:bg-green-600'
                                                            : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                                                        }`}
                                                >
                                                    <Icon icon="lucide:check" className="w-3 h-3" />
                                                    Tandai Selesai
                                                </button>

                                                {(!order.driver_name || !order.driver_phone) && (
                                                    <span className="text-xs text-red-500">
                                                        Lengkapi data driver terlebih dahulu.
                                                    </span>
                                                )}
                                            </>
                                        )}
                                        {order.order_status === 'processing' && (
                                            <button
                                                onClick={() => handleOpenDriverModal(order)}
                                                className="rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-bold text-white hover:bg-amber-600 flex items-center gap-1"
                                            >
                                                <Icon icon="material-symbols:moped-outline" className="w-3.5 h-3.5" />
                                                {order.driver_name ? 'Ubah Driver' : 'Atur Driver/Kurir'}
                                            </button>
                                        )}
                                        <button
                                            onClick={() => setActiveChatOrder(order)}
                                            className="relative rounded-lg border border-[#009b97] text-[#009b97] px-3 py-1.5 text-xs font-bold hover:bg-[#009b97] hover:text-white transition flex items-center gap-1"
                                        >
                                            <Icon icon="lucide:message-square" className="w-3 h-3" />
                                            Chat Pelanggan
                                            {order.unread_chat_count > 0 && (
                                                <span className="absolute -top-2 -right-2 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-black text-white shadow-md">
                                                    {order.unread_chat_count > 9 ? '9+' : order.unread_chat_count}
                                                </span>
                                            )}
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => {
                                                if (confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')) {
                                                    updateStatus(order.id, 'cancelled');
                                                }
                                            }}
                                            className="rounded-lg bg-red-100 text-red-700 px-3 py-1.5 text-xs font-bold hover:bg-red-200 transition flex items-center gap-1"
                                        >
                                            <Icon icon="lucide:x" className="w-3 h-3" />
                                            Batalkan
                                        </button>
                                        <span className="rounded-lg bg-gray-100 px-3 py-1.5 text-xs text-gray-600 font-semibold">
                                            Status: {STATUS_LABELS[order.order_status] || order.order_status}
                                        </span>
                                    </div>
                                )}
                            </div>
                        ))}
                    </div>
                )}
            </div>

            {/* Driver Info Modal */}
            {driverModalOrder && (
                <div className="fixed inset-0 z-[99999] flex items-center justify-center bg-black/50 p-4">
                    <form onSubmit={handleSaveDriverInfo} data-no-loading="true" className="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl space-y-4">
                        <div>
                            <h3 className="text-xl font-bold text-gray-900">Atur Kurir / Driver</h3>
                            <p className="text-sm text-gray-500">Input info Gosend/Grab atau kurir internal UMKM</p>
                        </div>

                        {driverError && (
                            <div className="p-3 text-sm text-red-700 bg-red-50 rounded-xl border border-red-200">
                                {driverError}
                            </div>
                        )}

                        <div className="space-y-3">
                            <div>
                                <label className="block text-xs font-bold text-gray-700 uppercase mb-1">Nama Driver/Kurir *</label>
                                <input
                                    type="text"
                                    required
                                    value={driverName}
                                    onChange={(e) => setDriverName(e.target.value)}
                                    placeholder="Contoh: Pak Budi"
                                    className="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-[#009b97] focus:outline-none"
                                />
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-gray-700 uppercase mb-1">No. HP Driver</label>
                                <input
                                    type="tel"
                                    value={driverPhone}
                                    onChange={(e) => setDriverPhone(e.target.value)}
                                    placeholder="Contoh: 08123456789"
                                    className="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-[#009b97] focus:outline-none"
                                />
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-gray-700 uppercase mb-1">Kode Order Gosend / Grab (Optional)</label>
                                <input
                                    type="text"
                                    value={driverCode}
                                    onChange={(e) => setDriverCode(e.target.value)}
                                    placeholder="Contoh: GK-12345678"
                                    className="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-[#009b97] focus:outline-none"
                                />
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-gray-700 uppercase mb-1">Foto Driver / Kurir (Optional)</label>
                                <input
                                    type="file"
                                    id="driver_photo"
                                    accept="image/*"
                                    className="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-[#009b97] focus:outline-none"
                                />
                            </div>
                        </div>

                        <div className="flex gap-3 pt-2">
                            <button
                                type="submit"
                                disabled={driverLoading}
                                className="flex-1 rounded-xl bg-[#009b97] py-3 font-bold text-white hover:bg-[#007a77] disabled:opacity-50"
                            >
                                {driverLoading ? 'Menyimpan...' : 'Simpan Info Driver'}
                            </button>
                            <button
                                type="button"
                                onClick={() => setDriverModalOrder(null)}
                                className="rounded-xl border border-gray-300 px-4 py-3 font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            )}

            {/* Chat Modal */}
            {activeChatOrder && (
                <div className="fixed inset-0 z-[99999] flex items-center justify-center bg-black/50 p-4">
                    <div className="w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden relative">
                        <button
                            onClick={() => setActiveChatOrder(null)}
                            className="absolute top-3 right-3 text-white hover:text-white/80 z-20 text-xl font-bold bg-black/20 hover:bg-black/40 h-8 w-8 flex items-center justify-center rounded-full"
                        >
                            ×
                        </button>
                        <OrderChat orderId={activeChatOrder.id} currentRole="umkm" />
                    </div>
                </div>
            )}

            {/* Lightbox Modal */}
            {lightboxPhoto && (
                <div className="fixed inset-0 z-[999999] flex flex-col items-center justify-center bg-black/90 p-4">
                    <button
                        onClick={() => setLightboxPhoto(null)}
                        className="absolute top-5 right-5 text-white hover:text-white/80 text-3xl font-bold h-12 w-12 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 transition-colors"
                    >
                        ×
                    </button>

                    <div className="max-w-4xl max-h-[80vh] overflow-hidden rounded-lg shadow-2xl">
                        <img
                            src={lightboxPhoto}
                            alt="Driver Photo Fullscreen"
                            className="w-full h-full max-h-[80vh] object-contain"
                        />
                    </div>

                    <div className="mt-4 flex gap-4">
                        <a
                            href={lightboxPhoto}
                            download="foto-driver.jpg"
                            target="_blank"
                            rel="noopener noreferrer"
                            className="inline-flex items-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 px-6 py-3 font-bold text-white shadow transition-all duration-300"
                        >
                            <Icon icon="lucide:download" className="w-5 h-5" />
                            Download Gambar
                        </a>
                        <button
                            onClick={() => setLightboxPhoto(null)}
                            className="rounded-xl border border-white/20 text-white hover:bg-white/10 px-6 py-3 font-semibold transition"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            )}
        </>
    );
}
