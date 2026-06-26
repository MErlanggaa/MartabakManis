import { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import {
    DELIVERY_OPTIONS,
    formatRupiah,
    calculatePricing,
    getCsrfToken,
} from '../utils/orderPricing';

export default function Checkout({ layanan, umkm, user, isAuthenticated }) {
    const [quantity, setQuantity] = useState(1);
    const [deliveryMethod, setDeliveryMethod] = useState('gojek');
    const [customerName, setCustomerName] = useState(user?.name ?? '');
    const [customerPhone, setCustomerPhone] = useState('');
    const [customerAddress, setCustomerAddress] = useState('');
    const [notes, setNotes] = useState('');
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');
    const [success, setSuccess] = useState(false);

    const unitPrice = parseFloat(layanan.price);
    const pricing = calculatePricing(unitPrice, quantity, deliveryMethod);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError('');

        if (!isAuthenticated) {
            window.location.href = '/login';
            return;
        }

        setLoading(true);

        try {
            const res = await fetch('/api/orders', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    Accept: 'application/json',
                },
                body: JSON.stringify({
                    layanan_id: layanan.id,
                    umkm_id: umkm.id,
                    quantity,
                    delivery_method: deliveryMethod,
                    customer_name: customerName,
                    customer_phone: customerPhone,
                    customer_address: customerAddress,
                    notes,
                }),
            });

            const data = await res.json();

            if (!res.ok || !data.success) {
                throw new Error(data.message || 'Gagal membuat pesanan');
            }

            if (data.snap_token && window.snap) {
                window.snap.pay(data.snap_token, {
                    onSuccess: () => setSuccess(true),
                    onPending: () => setSuccess(true),
                    onError: () => setError('Pembayaran gagal atau dibatalkan.'),
                    onClose: () => setLoading(false),
                });
            } else {
                setError('Midtrans belum dimuat. Muat ulang halaman.');
            }
        } catch (err) {
            setError(err.message);
        } finally {
            setLoading(false);
        }
    };

    if (success) {
        return (
            <motion.div
                initial={{ opacity: 0, scale: 0.95 }}
                animate={{ opacity: 1, scale: 1 }}
                className="rounded-3xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-teal-50 p-8 text-center shadow-card"
            >
                <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-green-500 text-3xl text-white">
                    ✓
                </div>
                <h3 className="text-xl font-bold text-gray-900">Pesanan Berhasil!</h3>
                <p className="mt-2 text-gray-600">
                    Pembayaran sedang diproses. Penjual akan segera menerima notifikasi pesanan Anda.
                </p>
            </motion.div>
        );
    }

    return (
        <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5 }}
            className="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-card"
        >
            <div className="bg-gradient-to-r from-[#009b97] to-[#007a77] px-6 py-5">
                <h2 className="text-xl font-bold text-white flex items-center gap-2">
                    <span>🛒</span> Pesan Sekarang
                </h2>
                <p className="mt-1 text-sm text-white/80">Checkout aman via Midtrans</p>
            </div>

            <form onSubmit={handleSubmit} className="space-y-6 p-6">
                {/* Quantity */}
                <div>
                    <label className="mb-2 block text-sm font-semibold text-gray-700">Jumlah</label>
                    <div className="flex items-center gap-3">
                        <button
                            type="button"
                            onClick={() => setQuantity(Math.max(1, quantity - 1))}
                            className="flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-gray-50 text-lg font-bold hover:bg-gray-100"
                        >
                            −
                        </button>
                        <span className="w-12 text-center text-xl font-bold">{quantity}</span>
                        <button
                            type="button"
                            onClick={() => setQuantity(Math.min(99, quantity + 1))}
                            className="flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-gray-50 text-lg font-bold hover:bg-gray-100"
                        >
                            +
                        </button>
                    </div>
                </div>

                {/* Delivery */}
                <div>
                    <label className="mb-3 block text-sm font-semibold text-gray-700">
                        Jasa Pengantaran
                    </label>
                    <div className="grid gap-3 sm:grid-cols-3">
                        {DELIVERY_OPTIONS.map((opt) => (
                            <button
                                key={opt.id}
                                type="button"
                                onClick={() => setDeliveryMethod(opt.id)}
                                className={`rounded-xl border-2 p-4 text-left transition-all ${
                                    deliveryMethod === opt.id
                                        ? 'border-[#009b97] bg-[#009b97]/5 shadow-md'
                                        : 'border-gray-200 hover:border-gray-300'
                                }`}
                            >
                                <div className="flex items-center gap-2">
                                    <span className="text-2xl">{opt.icon}</span>
                                    <div>
                                        <p className="font-bold text-gray-900">{opt.label}</p>
                                        <p className="text-xs text-gray-500">{opt.eta}</p>
                                    </div>
                                </div>
                                <p className="mt-2 text-sm font-semibold" style={{ color: opt.color }}>
                                    {formatRupiah(opt.fee)}
                                </p>
                            </button>
                        ))}
                    </div>
                </div>

                {/* Customer info */}
                <div className="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label className="mb-1 block text-sm font-medium text-gray-700">Nama</label>
                        <input
                            type="text"
                            required
                            value={customerName}
                            onChange={(e) => setCustomerName(e.target.value)}
                            className="w-full rounded-xl border border-gray-200 px-4 py-3 focus:border-[#009b97] focus:outline-none focus:ring-2 focus:ring-[#009b97]/20"
                            placeholder="Nama penerima"
                        />
                    </div>
                    <div>
                        <label className="mb-1 block text-sm font-medium text-gray-700">No. HP</label>
                        <input
                            type="tel"
                            required
                            value={customerPhone}
                            onChange={(e) => setCustomerPhone(e.target.value)}
                            className="w-full rounded-xl border border-gray-200 px-4 py-3 focus:border-[#009b97] focus:outline-none focus:ring-2 focus:ring-[#009b97]/20"
                            placeholder="08xxxxxxxxxx"
                        />
                    </div>
                </div>

                <div>
                    <label className="mb-1 block text-sm font-medium text-gray-700">Alamat Pengiriman</label>
                    <textarea
                        required
                        rows={3}
                        value={customerAddress}
                        onChange={(e) => setCustomerAddress(e.target.value)}
                        className="w-full rounded-xl border border-gray-200 px-4 py-3 focus:border-[#009b97] focus:outline-none focus:ring-2 focus:ring-[#009b97]/20"
                        placeholder="Alamat lengkap untuk pengantaran"
                    />
                </div>

                <div>
                    <label className="mb-1 block text-sm font-medium text-gray-700">Catatan (opsional)</label>
                    <input
                        type="text"
                        value={notes}
                        onChange={(e) => setNotes(e.target.value)}
                        className="w-full rounded-xl border border-gray-200 px-4 py-3 focus:border-[#009b97] focus:outline-none focus:ring-2 focus:ring-[#009b97]/20"
                        placeholder="Contoh: tanpa keju, pedas sedang"
                    />
                </div>

                {/* Price breakdown */}
                <div className="rounded-xl bg-gray-50 p-4 space-y-2">
                    <div className="flex justify-between text-sm text-gray-600">
                        <span>Subtotal ({quantity}x)</span>
                        <span>{formatRupiah(pricing.subtotal)}</span>
                    </div>
                    <div className="flex justify-between text-sm text-gray-600">
                        <span>Ongkir {DELIVERY_OPTIONS.find((d) => d.id === deliveryMethod)?.label}</span>
                        <span>{formatRupiah(pricing.deliveryFee)}</span>
                    </div>
                    <div className="flex justify-between text-sm text-gray-600">
                        <span>Pajak Aplikasi (2%)</span>
                        <span>{formatRupiah(pricing.appTax)}</span>
                    </div>
                    <div className="flex justify-between border-t border-gray-200 pt-2 text-lg font-bold text-gray-900">
                        <span>Total</span>
                        <span className="text-[#009b97]">{formatRupiah(pricing.total)}</span>
                    </div>
                </div>

                {error && (
                    <div className="rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                        {error}
                    </div>
                )}

                {!isAuthenticated && (
                    <div className="rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800">
                        Silakan <a href="/login" className="font-semibold underline">login</a> terlebih dahulu untuk memesan.
                    </div>
                )}

                <button
                    type="submit"
                    disabled={loading || !isAuthenticated}
                    className="w-full rounded-xl bg-gradient-to-r from-[#009b97] to-[#007a77] py-4 text-lg font-bold text-white shadow-lg transition hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {loading ? (
                        <span className="flex items-center justify-center gap-2">
                            <span className="h-5 w-5 animate-spin rounded-full border-2 border-white border-t-transparent" />
                            Memproses...
                        </span>
                    ) : (
                        <>Bayar dengan Midtrans — {formatRupiah(pricing.total)}</>
                    )}
                </button>
            </form>
        </motion.div>
    );
}
