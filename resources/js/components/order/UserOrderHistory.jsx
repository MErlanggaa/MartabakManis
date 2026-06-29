import { useEffect, useState } from 'react';
import { Icon } from '@iconify/react';
import { formatRupiah } from '../../utils/orderPricing';

const STATUS_BADGES = {
    pending: { label: 'Menunggu', cls: 'bg-yellow-100 text-yellow-800' },
    confirmed: { label: 'Dikonfirmasi', cls: 'bg-blue-100 text-blue-800' },
    processing: { label: 'Diproses', cls: 'bg-purple-100 text-purple-800' },
    delivered: { label: 'Selesai ✓', cls: 'bg-green-100 text-green-800' },
    cancelled: { label: 'Dibatalkan', cls: 'bg-red-100 text-red-800' },
};

export default function UserOrderHistory() {
    const [orders, setOrders] = useState([]);
    const [filter, setFilter] = useState('all'); // all, active, completed, cancelled
    const [loading, setLoading] = useState(true);

    const fetchOrders = async () => {
        try {
            const res = await fetch('/api/user/orders', {
                headers: { Accept: 'application/json' },
            });
            const data = await res.json();
            setOrders(data.orders || []);
        } catch {
            /* ignore */
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchOrders();
        // Poll every 7 seconds to update unread chat count
        const interval = setInterval(fetchOrders, 7000);
        return () => clearInterval(interval);
    }, []);

    const getFilteredOrders = () => {
        return orders.filter((order) => {
            if (filter === 'all') return true;
            if (filter === 'active') {
                return ['pending', 'confirmed', 'processing'].includes(order.order_status);
            }
            if (filter === 'completed') {
                return order.order_status === 'delivered';
            }
            if (filter === 'cancelled') {
                return order.order_status === 'cancelled';
            }
            return true;
        });
    };

    const filtered = getFilteredOrders();

    if (loading) {
        return (
            <div className="flex items-center justify-center py-20">
                <div className="h-10 w-10 animate-spin rounded-full border-4 border-[#009b97] border-t-transparent" />
            </div>
        );
    }

    return (
        <div className="max-w-4xl mx-auto px-4 py-8">
            <div className="mb-8 flex items-center justify-between">
                <div>
                    <h1 className="text-3xl font-bold text-gray-900 flex items-center gap-3">
                        <div className="w-12 h-12 bg-gradient-to-br from-[#009b97] to-[#039b00] rounded-xl flex items-center justify-center text-white">
                            <Icon icon="lucide:clipboard-list" className="h-6 w-6" />
                        </div>
                        Pesanan Saya
                    </h1>
                    <p className="text-gray-600 mt-2">Pantau status pengiriman makanan & histori belanja Anda</p>
                </div>
            </div>

            {/* Filters */}
            <div className="mb-6 flex gap-2 border-b border-gray-200 pb-px overflow-x-auto">
                {[
                    { id: 'all', label: 'Semua Pesanan' },
                    { id: 'active', label: 'Sedang Berjalan' },
                    { id: 'completed', label: 'Selesai' },
                    { id: 'cancelled', label: 'Dibatalkan' },
                ].map((tab) => (
                    <button
                        key={tab.id}
                        onClick={() => setFilter(tab.id)}
                        className={`whitespace-nowrap pb-4 px-4 font-bold text-sm border-b-2 transition-all ${
                            filter === tab.id
                                ? 'border-[#009b97] text-[#009b97]'
                                : 'border-transparent text-gray-500 hover:text-gray-800'
                        }`}
                    >
                        {tab.label}
                    </button>
                ))}
            </div>

            {/* List */}
            {filtered.length === 0 ? (
                <div className="rounded-2xl border border-gray-100 bg-white p-12 text-center text-gray-500 shadow-sm space-y-4">
                    <div className="flex justify-center text-[#009b97]">
                        <Icon icon="lucide:shopping-cart" className="w-16 h-16" />
                    </div>
                    <p className="font-bold text-gray-800 text-lg">Belum ada pesanan</p>
                    <p className="text-sm text-gray-500">
                        Cari kuliner favoritmu di katalog dan lakukan pemesanan sekarang!
                    </p>
                    <a
                        href="/katalog"
                        className="mt-6 inline-block rounded-xl bg-[#009b97] px-6 py-3 font-bold text-white hover:bg-[#007a77] transition-all"
                    >
                        Mulai Belanja
                    </a>
                </div>
            ) : (
                <div className="space-y-4">
                    {filtered.map((order) => (
                        <div
                            key={order.id}
                            className="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm hover:shadow-md transition-all flex flex-col md:flex-row md:items-center justify-between p-5 gap-4"
                        >
                            <div className="flex gap-4">
                                {order.layanan_photo ? (
                                    <img
                                        src={order.layanan_photo}
                                        alt={order.layanan_name}
                                        className="h-20 w-20 rounded-xl object-cover border border-gray-100"
                                    />
                                ) : (
                                    <div className="h-20 w-20 rounded-xl bg-gray-100 flex items-center justify-center text-gray-400">
                                        <Icon icon="lucide:utensils-crossed" className="w-10 h-10" />
                                    </div>
                                )}
                                <div className="flex-1">
                                    <div className="flex items-center gap-2 flex-wrap">
                                        <span className="font-mono text-xs text-gray-400 font-bold">
                                            {order.order_code}
                                        </span>
                                        <span className="text-xs text-gray-400">•</span>
                                        <span className="text-xs text-gray-500">{order.created_at}</span>
                                    </div>
                                    <h3 className="font-bold text-gray-900 text-lg mt-1">{order.layanan_name}</h3>
                                    <p className="text-sm text-gray-500">{order.umkm_name}</p>
                                    <p className="text-sm text-gray-600 mt-1">
                                        {order.quantity}x · {order.delivery_label}
                                    </p>
                                </div>
                            </div>

                            <div className="flex md:flex-col items-center justify-between md:items-end gap-2 border-t md:border-t-0 pt-3 md:pt-0 border-gray-100">
                                <div className="text-left md:text-right">
                                    <p className="text-xs text-gray-500 uppercase tracking-wide">Total Pembayaran</p>
                                    <p className="font-bold text-[#009b97] text-lg">{formatRupiah(order.total)}</p>
                                </div>
                                    <div className="flex items-center gap-3 relative">
                                        <span className={`rounded-full px-3 py-1 text-xs font-bold ${STATUS_BADGES[order.order_status]?.cls}`}>
                                            {STATUS_BADGES[order.order_status]?.label}
                                        </span>
                                        <div className="relative">
                                            <a
                                                href={`/user/orders/${order.id}`}
                                                className="rounded-xl border border-[#009b97] text-[#009b97] hover:bg-[#009b97] hover:text-white px-4 py-2 text-sm font-bold transition-all"
                                            >
                                                Detail
                                            </a>
                                            {order.unread_chat_count > 0 && (
                                                <span className="absolute -top-2 -right-2 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-[10px] font-black text-white shadow-md animate-bounce">
                                                    {order.unread_chat_count > 9 ? '9+' : order.unread_chat_count}
                                                </span>
                                            )}
                                        </div>
                                    </div>
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
}
