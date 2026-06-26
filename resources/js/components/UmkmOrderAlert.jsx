import { useEffect, useRef, useState } from 'react';
import { formatRupiah, getCsrfToken } from '../utils/orderPricing';

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
                    setAlertOrder(order);
                    setShowAlert(true);
                    playNotificationSound();
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
            setAllOrders(data.orders || []);
        } catch {
            /* ignore */
        }
    };

    const playNotificationSound = () => {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.frequency.value = 880;
            gain.gain.value = 0.1;
            osc.start();
            osc.stop(ctx.currentTime + 0.3);
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

    const dismissAlert = () => {
        if (alertOrder) {
            markSeen([alertOrder.id]);
        }
        setShowAlert(false);
        setAlertOrder(null);
    };

    useEffect(() => {
        fetchAll();
        fetchPending();
        const interval = setInterval(fetchPending, 10000);
        return () => clearInterval(interval);
    }, []);

    return (
        <>
            {showAlert && alertOrder && (
                <div className="fixed inset-0 z-[99999] flex items-center justify-center bg-black/50 p-4">
                    <div className="w-full max-w-md animate-bounce-once rounded-2xl bg-white p-6 shadow-2xl">
                        <div className="mb-4 flex items-center gap-3">
                            <div className="flex h-14 w-14 items-center justify-center rounded-full bg-[#009b97] text-2xl text-white">
                                🔔
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
                                className="flex-1 rounded-xl bg-[#009b97] py-3 font-bold text-white hover:bg-[#007a77]"
                            >
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
                        <span className="flex h-10 w-10 items-center justify-center rounded-full bg-amber-400 text-white">
                            {pendingOrders.length}
                        </span>
                        <div className="flex-1">
                            <p className="font-bold text-amber-900">Ada pesanan baru belum dilihat!</p>
                            <p className="text-sm text-amber-700">Klik untuk melihat detail pesanan.</p>
                        </div>
                        <button
                            onClick={() => { setAlertOrder(pendingOrders[0]); setShowAlert(true); }}
                            className="rounded-lg bg-amber-500 px-4 py-2 text-sm font-bold text-white hover:bg-amber-600"
                        >
                            Lihat
                        </button>
                    </div>
                </div>
            )}

            <div className="rounded-xl border border-gray-100 bg-white shadow-sm">
                <div className="border-b border-gray-100 px-6 py-4">
                    <h2 className="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <span>📋</span> Daftar Pesanan
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
                                    </div>
                                    <div className="text-right">
                                        <p className="font-bold text-[#009b97]">{formatRupiah(order.total)}</p>
                                        <span className={`mt-1 inline-block rounded-full px-2 py-0.5 text-xs font-medium ${
                                            order.payment_status === 'paid'
                                                ? 'bg-green-100 text-green-800'
                                                : 'bg-yellow-100 text-yellow-800'
                                        }`}>
                                            {order.payment_status === 'paid' ? 'Lunas' : order.payment_status}
                                        </span>
                                    </div>
                                </div>
                                {order.payment_status === 'paid' && order.order_status !== 'delivered' && order.order_status !== 'cancelled' && (
                                    <div className="mt-3 flex flex-wrap gap-2">
                                        {order.order_status === 'confirmed' && (
                                            <button
                                                onClick={() => updateStatus(order.id, 'processing')}
                                                className="rounded-lg bg-blue-500 px-3 py-1.5 text-xs font-bold text-white hover:bg-blue-600"
                                            >
                                                Mulai Proses
                                            </button>
                                        )}
                                        {order.order_status === 'processing' && (
                                            <button
                                                onClick={() => updateStatus(order.id, 'delivered')}
                                                className="rounded-lg bg-green-500 px-3 py-1.5 text-xs font-bold text-white hover:bg-green-600"
                                            >
                                                Tandai Selesai
                                            </button>
                                        )}
                                        <span className="rounded-lg bg-gray-100 px-3 py-1.5 text-xs text-gray-600">
                                            Status: {STATUS_LABELS[order.order_status] || order.order_status}
                                        </span>
                                    </div>
                                )}
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}
