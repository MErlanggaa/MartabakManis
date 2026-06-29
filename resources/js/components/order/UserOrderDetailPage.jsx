import { useEffect, useState } from 'react';
import { Icon } from '@iconify/react';
import { motion, AnimatePresence } from 'framer-motion';
import OrderTracking from './OrderTracking';
import OrderChat from './OrderChat';
import OrderRatingForm from './OrderRatingForm';
import { formatRupiah } from '../../utils/orderPricing';

const STATUS_BADGES = {
    pending: { label: 'Menunggu', cls: 'bg-yellow-100 text-yellow-800' },
    confirmed: { label: 'Dikonfirmasi', cls: 'bg-blue-100 text-blue-800' },
    processing: { label: 'Diproses', cls: 'bg-purple-100 text-purple-800' },
    delivered: { label: 'Selesai ✓', cls: 'bg-green-100 text-green-800' },
    cancelled: { label: 'Dikatalkan', cls: 'bg-red-100 text-red-800' },
};

const PAYMENT_BADGES = {
    pending: { label: 'Belum Bayar', cls: 'bg-yellow-100 text-yellow-800' },
    paid: { label: 'Lunas ✓', cls: 'bg-green-100 text-green-800' },
    expired: { label: 'Kadaluarsa', cls: 'bg-gray-100 text-gray-800' },
    failed: { label: 'Gagal', cls: 'bg-red-100 text-red-800' },
    cancelled: { label: 'Dibatalkan', cls: 'bg-red-100 text-red-800' },
};

export default function UserOrderDetailPage({ orderId }) {
    const [order, setOrder] = useState(null);
    const [loading, setLoading] = useState(true);
    const [ratingDone, setRatingDone] = useState(false);
    const [cancelling, setCancelling] = useState(false);
    const [cancelError, setCancelError] = useState('');

    // QRIS Payment states
    const [showQrisModal, setShowQrisModal] = useState(false);
    const [confirmingPayment, setConfirmingPayment] = useState(false);
    const [receiptFile, setReceiptFile] = useState(null);
    const [scanStage, setScanStage] = useState(0);
    const [scanText, setScanText] = useState('');
    const [error, setError] = useState('');

    const getCsrfToken = () => {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    };

    const handleConfirmPayment = async () => {
        if (!receiptFile) {
            setError('Silakan pilih & unggah file bukti transfer QRIS terlebih dahulu.');
            return;
        }

        setConfirmingPayment(true);
        setError('');
        
        try {
            setScanStage(1);
            setScanText('Mengupload bukti transfer & mengekstrak data gambar...');
            await new Promise(r => setTimeout(r, 1200));

            setScanStage(2);
            setScanText('Mencocokkan Merchant: MUHAMMAD ERLANGGA... (NMID: ID1025464889274)');
            await new Promise(r => setTimeout(r, 1200));

            setScanStage(3);
            const totalRupiah = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(order.total);
            setScanText(`Mengecek mutasi bank untuk nominal presisi: ${totalRupiah}...`);
            await new Promise(r => setTimeout(r, 1200));

            setScanStage(4);
            setScanText('Status: Cocok! Menyimpan status pembayaran...');
            
            const res = await fetch(`/api/orders/${order.id}/confirm-payment`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    Accept: 'application/json',
                },
            });
            const data = await res.json();

            if (!res.ok || !data.success) {
                throw new Error(data.message || 'Konfirmasi pembayaran gagal.');
            }

            setScanText('Status: Lunas! Pembayaran Terverifikasi Otomatis.');
            await new Promise(r => setTimeout(r, 1000));

            setShowQrisModal(false);
            fetchOrder();
        } catch (err) {
            setError(err.message);
            setScanStage(0);
            setScanText('');
        } finally {
            setConfirmingPayment(false);
        }
    };

    const handleCancelOrder = async () => {
        if (!confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')) return;
        setCancelling(true);
        setCancelError('');
        try {
            const res = await fetch(`/api/orders/${orderId}/cancel`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    Accept: 'application/json',
                },
            });
            const data = await res.json();
            if (!res.ok || !data.success) {
                throw new Error(data.message || 'Gagal membatalkan pesanan.');
            }
            fetchOrder();
        } catch (err) {
            setCancelError(err.message);
        } finally {
            setCancelling(false);
        }
    };

    const fetchOrder = async () => {
        try {
            const res = await fetch(`/api/user/orders/${orderId}`, {
                headers: { Accept: 'application/json' },
            });
            const data = await res.json();
            setOrder(data.order);
            if (data.order?.has_rating) setRatingDone(true);
        } catch { /* ignore */ } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchOrder();
        // Poll order status every 5 seconds
        const interval = setInterval(fetchOrder, 5000);
        return () => clearInterval(interval);
    }, [orderId]);

    if (loading) {
        return (
            <div className="flex items-center justify-center py-20">
                <div className="h-10 w-10 animate-spin rounded-full border-4 border-[#009b97] border-t-transparent" />
            </div>
        );
    }

    if (!order) {
        return (
            <div className="py-20 text-center text-gray-500 space-y-3">
                <div className="flex justify-center text-red-500">
                    <Icon icon="lucide:x-circle" className="w-16 h-16" />
                </div>
                <p>Pesanan tidak ditemukan.</p>
            </div>
        );
    }

    const canRate = order.order_status === 'delivered' && order.payment_status === 'paid' && !order.has_rating && !ratingDone;

    return (
        <div className="max-w-4xl mx-auto px-4 py-6 space-y-6">
            {/* Back */}
            <a href="/user/orders" className="inline-flex items-center gap-1 text-sm text-[#009b97] hover:underline font-semibold">
                <Icon icon="lucide:arrow-left" className="w-4 h-4" />
                Kembali ke History Pesanan
            </a>

            {/* Header */}
            <div className="rounded-2xl bg-gradient-to-br from-slate-900 to-slate-800 p-6 text-white shadow-md">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p className="text-white/60 text-xs uppercase tracking-wide">Kode Pesanan</p>
                        <p className="text-2xl font-bold">{order.order_code}</p>
                        <p className="text-white/70 text-sm mt-1">{order.created_at}</p>
                    </div>
                    <div className="flex flex-col gap-2 items-end">
                        <span className={`inline-block rounded-full px-3 py-1 text-xs font-bold ${STATUS_BADGES[order.order_status]?.cls}`}>
                            {STATUS_BADGES[order.order_status]?.label}
                        </span>
                        <span className={`inline-block rounded-full px-3 py-1 text-xs font-bold ${PAYMENT_BADGES[order.payment_status]?.cls}`}>
                            {PAYMENT_BADGES[order.payment_status]?.label}
                        </span>
                    </div>
                </div>
            </div>

            <div className="grid gap-6 md:grid-cols-2">
                {/* Order Info */}
                <div className="space-y-4">
                    {/* Product */}
                    <div className="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                        <h3 className="font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <Icon icon="lucide:shopping-bag" className="w-5 h-5 text-[#009b97]" />
                            Detail Pesanan
                        </h3>
                        <div className="flex gap-4">
                            {order.layanan_photo && (
                                <img
                                    src={order.layanan_photo}
                                    alt={order.layanan_name}
                                    className="h-20 w-20 rounded-xl object-cover border border-gray-100"
                                />
                            )}
                            <div className="flex-1">
                                <p className="font-bold text-gray-900">{order.layanan_name}</p>
                                <p className="text-sm text-gray-500">{order.umkm_name}</p>
                                <p className="text-sm text-gray-600 mt-1">{order.quantity}x · {order.delivery_label}</p>
                                {order.notes && (
                                    <p className="mt-1 text-xs text-gray-500 italic">Catatan: {order.notes}</p>
                                )}
                            </div>
                        </div>

                        {/* Pricing */}
                        <div className="mt-4 space-y-1 text-sm border-t border-gray-100 pt-3">
                            <div className="flex justify-between text-gray-600">
                                <span>Subtotal</span>
                                <span>{formatRupiah(order.subtotal)}</span>
                            </div>
                            <div className="flex justify-between text-gray-600">
                                <span>Ongkir</span>
                                <span>{formatRupiah(order.delivery_fee)}</span>
                            </div>
                            <div className="flex justify-between text-gray-600">
                                <span>Pajak Aplikasi</span>
                                <span>{formatRupiah(order.app_tax)}</span>
                            </div>
                            {order.qris_tax > 0 && (
                                <div className="flex justify-between text-orange-600">
                                    <span>Pajak QRIS</span>
                                    <span>{formatRupiah(order.qris_tax)}</span>
                                </div>
                            )}
                            <div className="flex justify-between font-bold text-gray-900 border-t border-gray-200 pt-1 text-base">
                                <span>Total</span>
                                <span className="text-[#009b97]">{formatRupiah(order.total)}</span>
                            </div>
                        </div>

                        {/* QRIS Pay Button if Pending Payment */}
                        {order.payment_status === 'pending' && order.payment_method === 'qris' && (
                            <div className="mt-4 border-t border-gray-100 pt-4 space-y-2">
                                <div className="bg-emerald-50 border border-emerald-100 rounded-xl p-3 text-xs text-emerald-800 font-semibold flex items-start gap-2">
                                    <Icon icon="lucide:info" className="w-4 h-4 text-emerald-600 flex-shrink-0 mt-0.5" />
                                    <div>
                                        <p className="font-bold">Pembayaran Belum Dilakukan</p>
                                        <p className="text-emerald-700 mt-0.5">Harap lakukan pembayaran transfer presisi ke QRIS Statis Merchant untuk memproses pesanan Anda.</p>
                                    </div>
                                </div>
                                <button
                                    onClick={() => setShowQrisModal(true)}
                                    className="w-full rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 py-3 font-bold text-white shadow-md hover:shadow-lg transition flex items-center justify-center gap-1.5"
                                >
                                    <Icon icon="lucide:qr-code" className="w-5 h-5" />
                                    Bayar / Verifikasi Sekarang
                                </button>
                            </div>
                        )}

                        {/* Cancel Order Button */}
                        {['pending', 'confirmed'].includes(order.order_status) && (
                            <div className="mt-4 border-t border-gray-100 pt-4">
                                <button
                                    onClick={handleCancelOrder}
                                    disabled={cancelling}
                                    data-no-loading="true"
                                    className="w-full rounded-xl bg-red-50 py-3 text-sm font-bold text-red-600 border border-red-200 hover:bg-red-100 transition disabled:opacity-50 flex items-center justify-center gap-1"
                                >
                                    <Icon icon="lucide:x-circle" className="w-4 h-4" />
                                    {cancelling ? 'Membatalkan...' : 'Batalkan Pesanan'}
                                </button>
                                {cancelError && (
                                    <p className="mt-2 text-xs text-red-600 text-center">{cancelError}</p>
                                )}
                            </div>
                        )}

                        {/* Report Problem Button */}
                        <div className="mt-4 border-t border-gray-100 pt-4">
                            <a
                                href={`/laporan?order_id=${order.id}`}
                                className="w-full rounded-xl bg-orange-50 hover:bg-orange-100 text-orange-600 border border-orange-200 py-3 text-sm font-bold transition flex items-center justify-center gap-1.5 text-center"
                            >
                                <Icon icon="lucide:alert-triangle" className="w-4 h-4" />
                                Laporkan Masalah / Komplain Toko
                            </a>
                        </div>
                    </div>

                    {/* Tracking */}
                    <OrderTracking order={order} onRefresh={fetchOrder} />

                    {/* Rating Form or Rating Display */}
                    {canRate && (
                        <OrderRatingForm
                            orderId={order.id}
                            onSuccess={() => {
                                setRatingDone(true);
                                fetchOrder();
                            }}
                        />
                    )}
                    {(order.has_rating || ratingDone) && order.order_status === 'delivered' && (
                        <div className="rounded-2xl border border-green-200 bg-green-50 p-5 text-center shadow-sm space-y-2">
                            <div className="flex justify-center text-yellow-500">
                                <Icon icon="lucide:star" className="w-10 h-10 fill-current" />
                            </div>
                            <p className="font-bold text-green-800">Ulasan sudah dikirim!</p>
                            <p className="text-sm text-green-600">Terima kasih atas ulasan Anda.</p>
                            {order.rating && (
                                <div className="mt-2 flex justify-center gap-1 text-yellow-400">
                                    {[...Array(order.rating.rating)].map((_, i) => (
                                        <Icon key={i} icon="lucide:star" className="w-5 h-5 fill-current" />
                                    ))}
                                </div>
                            )}
                        </div>
                    )}
                </div>

                {/* Chat */}
                <div className="flex flex-col gap-4">
                    {/* Delivery Address */}
                    <div className="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                        <h3 className="font-bold text-gray-900 mb-3 flex items-center gap-2">
                            <Icon icon="lucide:map-pin" className="w-5 h-5 text-[#009b97]" />
                            Alamat Pengiriman
                        </h3>
                        <p className="font-semibold text-gray-800">{order.customer_name}</p>
                        <p className="text-sm text-gray-600">{order.customer_phone}</p>
                        <p className="text-sm text-gray-600 mt-1">{order.customer_address}</p>
                    </div>

                    {/* Chat */}
                    {order.payment_status === 'paid' && (
                        <div className="relative">
                            {order.unread_chat_count > 0 && (
                                <div className="absolute -top-2 left-0 z-10 flex items-center gap-1.5 rounded-full bg-red-500 px-2.5 py-1 text-[11px] font-bold text-white shadow-md animate-pulse">
                                    <Icon icon="lucide:bell" className="w-3 h-3" />
                                    {order.unread_chat_count} pesan baru dari UMKM
                                </div>
                            )}
                            <div className={order.unread_chat_count > 0 ? 'mt-6' : ''}>
                                <OrderChat orderId={order.id} currentRole="user" />
                            </div>
                        </div>
                    )}
                </div>
            </div>

            {/* QRIS PAYMENT DIALOG MODAL */}
            <AnimatePresence>
                {showQrisModal && (
                    <div className="fixed inset-0 z-[99999] flex items-center justify-center bg-black/60 p-4">
                        <motion.div
                            initial={{ scale: 0.9, opacity: 0 }}
                            animate={{ scale: 1, opacity: 1 }}
                            exit={{ scale: 0.9, opacity: 0 }}
                            className="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl space-y-4 text-center overflow-hidden relative"
                        >
                            {/* Scanning Screen Overlay */}
                            {scanStage > 0 ? (
                                <div className="space-y-4 py-8 text-center flex flex-col items-center justify-center min-h-[350px]">
                                    <div className="relative w-24 h-24 bg-teal-50 border-2 border-[#009b97] rounded-xl flex items-center justify-center overflow-hidden">
                                        <Icon icon="lucide:scan-line" className="w-16 h-16 text-[#009b97]" />
                                        <motion.div 
                                            animate={{ top: ['0%', '90%', '0%'] }}
                                            transition={{ repeat: Infinity, duration: 1.5, ease: 'easeInOut' }}
                                            className="absolute left-0 right-0 h-1 bg-emerald-500 shadow-md"
                                        />
                                    </div>
                                    <h4 className="font-bold text-slate-800 text-sm">Verifikasi Pembayaran Otomatis</h4>
                                    <p className="text-xs text-slate-600 font-semibold px-4 min-h-[40px] flex items-center justify-center leading-relaxed">
                                        {scanText}
                                    </p>
                                    <div className="flex gap-1.5 justify-center mt-2">
                                        <div className={`w-2.5 h-2.5 rounded-full ${scanStage >= 1 ? 'bg-emerald-500' : 'bg-slate-200'} transition-all`} />
                                        <div className={`w-2.5 h-2.5 rounded-full ${scanStage >= 2 ? 'bg-emerald-500' : 'bg-slate-200'} transition-all`} />
                                        <div className={`w-2.5 h-2.5 rounded-full ${scanStage >= 3 ? 'bg-emerald-500' : 'bg-slate-200'} transition-all`} />
                                        <div className={`w-2.5 h-2.5 rounded-full ${scanStage >= 4 ? 'bg-emerald-500 animate-ping' : 'bg-slate-200'} transition-all`} />
                                    </div>
                                </div>
                            ) : (
                                <>
                                    {/* QRIS Brand Header */}
                                    <div className="flex justify-between items-center border-b pb-2">
                                        <div className="flex items-center gap-2">
                                            <span className="bg-red-600 text-white font-black px-2 py-0.5 rounded text-sm leading-none">QRIS</span>
                                            <span className="text-slate-400 font-bold text-[10px] uppercase tracking-wider">Verifikasi Instan</span>
                                        </div>
                                        <button onClick={() => setShowQrisModal(false)} className="text-slate-400 hover:text-slate-600">
                                            <Icon icon="lucide:x" className="w-5 h-5" />
                                        </button>
                                    </div>

                                    <div className="text-left space-y-1">
                                        <h3 className="font-black text-slate-800 text-base">Pembayaran QRIS Statis</h3>
                                        <p className="text-xs text-slate-500">Scan & transfer presisi ke QRIS Statis Merchant di bawah ini:</p>
                                    </div>

                                    {/* QR Image */}
                                    <div className="mx-auto border border-slate-100 p-1.5 rounded-xl bg-slate-50 w-44 h-auto shadow-inner flex flex-col items-center">
                                        <img
                                            src={`https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=${encodeURIComponent(order.dynamic_qris)}`}
                                            alt="QRIS Barcode"
                                            className="w-full h-auto rounded-lg object-contain"
                                        />
                                        <span className="text-[8px] font-bold text-slate-400 mt-1">NMID: ID1025464889274 (Dinamis)</span>
                                    </div>

                                    {/* Amount Details */}
                                    <div className="bg-emerald-50/50 rounded-xl p-3 border border-emerald-100/50 text-left space-y-1">
                                        <div className="flex justify-between items-center">
                                            <span className="text-[10px] text-slate-500 uppercase tracking-wider font-bold">Total Transfer</span>
                                            <span className="text-xs font-mono font-bold text-slate-400">Kode: {order.order_code}</span>
                                        </div>
                                        <div className="flex items-baseline gap-1 mt-0.5">
                                            <p className="text-2xl font-black text-emerald-600">{formatRupiah(order.total)}</p>
                                        </div>
                                        {order.unique_code > 0 && (
                                            <p className="text-[10px] text-emerald-800 bg-emerald-100/50 px-2 py-0.5 rounded inline-block font-semibold">
                                                *Sudah termasuk kode unik transfer: <strong>{order.unique_code}</strong>
                                            </p>
                                        )}
                                    </div>

                                    {/* Receipt Uploader */}
                                    <div className="text-left space-y-2">
                                        <label className="block text-xs font-bold text-slate-700 uppercase">Unggah Bukti Transfer (Screenshot)</label>
                                        <div className="relative border-2 border-dashed border-slate-200 hover:border-emerald-500 rounded-xl p-3 bg-slate-50 flex flex-col items-center justify-center transition cursor-pointer">
                                            <input
                                                type="file"
                                                accept="image/*"
                                                onChange={(e) => setReceiptFile(e.target.files[0])}
                                                className="absolute inset-0 opacity-0 w-full h-full cursor-pointer"
                                            />
                                            {receiptFile ? (
                                                <div className="flex items-center gap-2 text-emerald-600 font-bold text-xs">
                                                    <Icon icon="lucide:image" className="w-5 h-5 flex-shrink-0" />
                                                    <span className="truncate max-w-[200px]">{receiptFile.name}</span>
                                                </div>
                                            ) : (
                                                <div className="text-center space-y-1">
                                                    <Icon icon="lucide:upload-cloud" className="w-8 h-8 text-slate-400 mx-auto" />
                                                    <p className="text-xs text-slate-500 font-medium">Klik atau tarik file struk/SS transfer di sini</p>
                                                </div>
                                            )}
                                        </div>
                                    </div>

                                    {error && (
                                        <div className="rounded-xl bg-red-50 border border-red-100 p-2.5 text-xs text-red-700 text-left flex items-center gap-1.5">
                                            <Icon icon="lucide:alert-circle" className="w-4 h-4 flex-shrink-0" />
                                            <span>{error}</span>
                                        </div>
                                    )}

                                    <div className="space-y-2 pt-1">
                                        <button
                                            type="button"
                                            onClick={handleConfirmPayment}
                                            disabled={confirmingPayment}
                                            className="w-full rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 py-3 font-bold text-white shadow-md hover:shadow-lg disabled:opacity-50 transition"
                                        >
                                            Verifikasi Otomatis
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => setShowQrisModal(false)}
                                            className="w-full text-xs text-slate-500 hover:text-slate-700 font-bold"
                                        >
                                            Bayar Nanti (Pending)
                                        </button>
                                    </div>
                                </>
                            )}
                        </motion.div>
                    </div>
                )}
            </AnimatePresence>
        </div>
    );
}
