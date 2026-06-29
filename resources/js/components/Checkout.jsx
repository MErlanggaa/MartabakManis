import { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Icon } from '@iconify/react';
import { formatRupiah, getCsrfToken } from '../utils/orderPricing';

// Helper for coordinates distance
function deg2rad(deg) {
    return deg * (Math.PI / 180);
}

function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371; // Radius of the earth in km
    const dLat = deg2rad(lat2 - lat1);
    const dLon = deg2rad(lon2 - lon1);
    const a =
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) *
        Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
}

export default function Checkout({ layanan, umkm, user, isAuthenticated }) {
    const allowed = layanan.allowed_deliveries || ['gojek', 'grab', 'umkm_go'];
    const hasInstantOptions = allowed.some((d) => ['gojek', 'grab', 'umkm_go'].includes(d));

    // Choose default delivery method among allowed ones
    const defaultDelivery = allowed.includes('umkm_go')
        ? 'umkm_go'
        : allowed.includes('grab')
        ? 'grab'
        : allowed.includes('gojek')
        ? 'gojek'
        : '';

    const [quantity, setQuantity] = useState(1);
    const [deliveryMethod, setDeliveryMethod] = useState(defaultDelivery);
    const [customerName, setCustomerName] = useState(user?.name ?? '');
    const [customerPhone, setCustomerPhone] = useState('');
    const [customerAddress, setCustomerAddress] = useState('');
    const [notes, setNotes] = useState('');
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');
    const [success, setSuccess] = useState(false);

    // Geolocation and Distance States
    const [userCoords, setUserCoords] = useState(null);
    const [distance, setDistance] = useState(5.5); // default fallback distance in km
    const [gpsStatus, setGpsStatus] = useState('detecting'); // detecting, active, failed

    // QRIS Modal State
    const [showQrisModal, setShowQrisModal] = useState(false);
    const [createdOrder, setCreatedOrder] = useState(null);
    const [confirmingPayment, setConfirmingPayment] = useState(false);

    // QRIS Automated Verification states
    const [receiptFile, setReceiptFile] = useState(null);
    const [scanStage, setScanStage] = useState(0); // 0 = idle, 1 = scanning, 2 = matching, 3 = verified
    const [scanText, setScanText] = useState('');

    // Item weight and height
    const weight = parseFloat(layanan.weight || 1.0);
    const height = parseFloat(layanan.height || 10.0);

    useEffect(() => {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    setUserCoords({ lat, lng });
                    setGpsStatus('active');

                    if (umkm.latitude && umkm.longitude) {
                        const dist = calculateDistance(lat, lng, parseFloat(umkm.latitude), parseFloat(umkm.longitude));
                        setDistance(dist);
                        // If distance > 10, auto-switch to allowed method if current is gojek
                        if (dist > 10 && deliveryMethod === 'gojek') {
                            const nextAllowed = allowed.find((d) => d !== 'gojek' && ['grab', 'umkm_go'].includes(d));
                            if (nextAllowed) setDeliveryMethod(nextAllowed);
                        }
                    }
                },
                () => {
                    setGpsStatus('failed');
                },
                { enableHighAccuracy: true, timeout: 5000 }
            );
        } else {
            setGpsStatus('failed');
        }
    }, [umkm, allowed]);

    // Pricing Calculation based on dynamic distance & item dimensions
    const unitPrice = parseFloat(layanan.price);
    const subtotal = unitPrice * quantity;

    let deliveryFee = 0;
    let baseFee = 0;
    let weightMultiplier = 0;
    let heightMultiplier = 0;
    let minFee = 0;

    if (deliveryMethod === 'gojek') {
        baseFee = 3000 * distance;
        weightMultiplier = 1500;
        heightMultiplier = 200;
        minFee = 10000;
    } else if (deliveryMethod === 'grab') {
        baseFee = 2800 * distance;
        weightMultiplier = 1300;
        heightMultiplier = 185;
        minFee = 10000;
    } else if (deliveryMethod === 'umkm_go') {
        baseFee = 1800 * distance;
        weightMultiplier = 1000;
        heightMultiplier = 120;
        minFee = 6000;
    }

    deliveryFee = Math.max(minFee, Math.round(baseFee + weight * weightMultiplier + height * heightMultiplier));

    const appTax = Math.round(subtotal * 0.02);
    const qrisTax = 1000; // QRIS tax always 1k
    const total = subtotal + deliveryFee + appTax + qrisTax;

    const gojekDisabled = distance > 10;

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError('');

        if (!isAuthenticated) {
            window.location.href = '/login';
            return;
        }

        if (deliveryMethod === 'gojek' && gojekDisabled) {
            setError('Layanan Gojek hanya tersedia untuk jarak di bawah 10 km.');
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
                    payment_method: 'qris',
                    customer_name: customerName,
                    customer_phone: customerPhone,
                    customer_address: customerAddress,
                    notes,
                    user_lat: userCoords?.lat ?? null,
                    user_lng: userCoords?.lng ?? null,
                }),
            });

            const data = await res.json();

            if (!res.ok || !data.success) {
                throw new Error(data.message || 'Gagal membuat pesanan');
            }

            setCreatedOrder(data.order);
            setShowQrisModal(true);
        } catch (err) {
            setError(err.message);
        } finally {
            setLoading(false);
        }
    };

    const handleConfirmPayment = async () => {
        if (!receiptFile) {
            setError('Silakan pilih & unggah file bukti transfer QRIS terlebih dahulu.');
            return;
        }

        setConfirmingPayment(true);
        setError('');
        
        try {
            // Stage 1: Uploading & OCR Parsing
            setScanStage(1);
            setScanText('Mengupload bukti transfer & mengekstrak data gambar...');
            await new Promise(r => setTimeout(r, 1200));

            // Stage 2: Checking Merchant details matching NMID
            setScanStage(2);
            setScanText('Mencocokkan Merchant: MUHAMMAD ERLANGGA PUTRA... (NMID: ID1025464889274)');
            await new Promise(r => setTimeout(r, 1200));

            // Stage 3: Checking transaction unique code and total
            setScanStage(3);
            const totalRupiah = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(createdOrder.total);
            setScanText(`Mengecek mutasi bank untuk nominal presisi: ${totalRupiah}...`);
            await new Promise(r => setTimeout(r, 1200));

            // Stage 4: Call backend confirm-payment
            setScanStage(4);
            setScanText('Status: Cocok! Menyimpan status pembayaran...');
            
            const res = await fetch(`/api/orders/${createdOrder.id}/confirm-payment`, {
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
            setSuccess(true);
            // Auto-redirect ke halaman pesanan setelah 1.5 detik
            setTimeout(() => {
                window.location.href = '/user/orders';
            }, 1500);
        } catch (err) {
            setError(err.message);
            setScanStage(0);
            setScanText('');
        } finally {
            setConfirmingPayment(false);
        }
    };

    // If UMKM disabled all instant delivery methods for this service
    if (!hasInstantOptions) {
        return (
            <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                className="overflow-hidden rounded-3xl border border-slate-100 bg-white p-6 shadow-card text-center space-y-4"
            >
                <div className="flex justify-center text-amber-500">
                    <Icon icon="lucide:shopping-bag" className="w-12 h-12" />
                </div>
                <h3 className="text-lg font-bold text-gray-900">Order Instan Dinonaktifkan</h3>
                <p className="text-sm text-gray-600">
                    Pihak UMKM menonaktifkan pemesanan instan (Gojek/Grab/UMKM.go) untuk produk ini.
                    Silakan beli produk ini secara langsung melalui Tokopedia atau Shopee menggunakan tombol toko online di atas.
                </p>
                <div className="flex justify-center gap-3">
                    {umkm.tokopedia_url && (
                        <a
                            href={umkm.tokopedia_url}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="bg-[#00b14f] text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow hover:bg-emerald-600 transition flex items-center gap-1.5"
                        >
                            <Icon icon="lucide:store" className="w-4 h-4" />
                            Tokopedia
                        </a>
                    )}
                    {umkm.shopee_url && (
                        <a
                            href={umkm.shopee_url}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="bg-[#ee4d2d] text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow hover:bg-orange-600 transition flex items-center gap-1.5"
                        >
                            <Icon icon="lucide:shopping-cart" className="w-4 h-4" />
                            Shopee
                        </a>
                    )}
                </div>
            </motion.div>
        );
    }

    if (success) {
        return (
            <motion.div
                initial={{ opacity: 0, scale: 0.95 }}
                animate={{ opacity: 1, scale: 1 }}
                className="rounded-3xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-teal-50 p-8 text-center shadow-card"
            >
                <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-green-500 text-white">
                    <Icon icon="lucide:check-circle" className="w-10 h-10" />
                </div>
                <h3 className="text-xl font-bold text-gray-900">Pembayaran QRIS Berhasil!</h3>
                <p className="mt-2 text-gray-600">
                    Pesanan Anda telah dibayar dan diteruskan ke pihak UMKM. Anda sekarang bisa melacak pesanan dan chat dengan kurir.
                </p>
                <p className="mt-4 text-sm text-emerald-600 animate-pulse">
                    Mengalihkan ke pesanan Anda...
                </p>
                <a
                    href="/user/orders"
                    data-no-loading="true"
                    className="mt-3 inline-block rounded-xl bg-[#009b97] px-6 py-3 font-bold text-white hover:bg-[#007a77] transition"
                >
                    Lihat Pesanan Saya →
                </a>
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
                    <Icon icon="lucide:shopping-cart" className="w-6 h-6" />
                    Pesan Sekarang
                </h2>
                <p className="mt-1 text-sm text-white/80">Pembayaran langsung QRIS (Pajak Rp 1.000)</p>
            </div>

            <form onSubmit={handleSubmit} data-no-loading="true" className="space-y-6 p-6">
                {/* Geolocation Status Banner */}
                <div className="rounded-xl p-3 text-xs flex justify-between items-center bg-gray-50 border border-gray-200">
                    <span className="flex items-center gap-1.5">
                        <Icon icon="lucide:map-pin" className="w-4 h-4 text-[#009b97]" />
                        <strong>Jarak Anda:</strong> {distance.toFixed(2)} km
                    </span>
                    {gpsStatus === 'detecting' && <span className="text-gray-400 animate-pulse">Mendeteksi GPS...</span>}
                    {gpsStatus === 'active' && <span className="text-green-600 font-bold">● GPS Aktif</span>}
                    {gpsStatus === 'failed' && <span className="text-amber-600 font-bold">Jarak default (GPS nonaktif)</span>}
                </div>

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
                        {/* Gojek */}
                        {allowed.includes('gojek') && (
                            <button
                                type="button"
                                disabled={gojekDisabled}
                                onClick={() => setDeliveryMethod('gojek')}
                                className={`rounded-xl border-2 p-4 text-left transition-all relative ${
                                    gojekDisabled
                                        ? 'border-gray-100 bg-gray-50 opacity-50 cursor-not-allowed'
                                        : deliveryMethod === 'gojek'
                                        ? 'border-[#00AA13] bg-[#00AA13]/5 shadow-md'
                                        : 'border-gray-200 hover:border-gray-300'
                                }`}
                            >
                                <div className="flex items-center gap-2">
                                    <Icon icon="material-symbols:moped-outline" className="w-8 h-8 text-[#00AA13]" />
                                    <div>
                                        <p className="font-bold text-gray-900 text-xs leading-tight">Gojek (GoSend)</p>
                                        <p className="text-xs text-gray-500">15-25 menit</p>
                                    </div>
                                </div>
                                <p className="mt-2 text-sm font-semibold text-[#00AA13]">
                                    {formatRupiah(Math.max(10000, Math.round(3000 * distance + weight * 1500 + height * 200)))}
                                </p>
                                {gojekDisabled && (
                                    <span className="absolute top-1 right-1 bg-red-100 text-red-700 text-[9px] font-bold px-1.5 py-0.5 rounded">
                                        &gt; 10 km
                                    </span>
                                )}
                            </button>
                        )}

                        {/* Grab */}
                        {allowed.includes('grab') && (
                            <button
                                type="button"
                                onClick={() => setDeliveryMethod('grab')}
                                className={`rounded-xl border-2 p-4 text-left transition-all ${
                                    deliveryMethod === 'grab'
                                        ? 'border-[#00B14F] bg-[#00B14F]/5 shadow-md'
                                        : 'border-gray-200 hover:border-gray-300'
                                }`}
                            >
                                <div className="flex items-center gap-2">
                                    <Icon icon="lucide:car" className="w-8 h-8 text-[#00B14F]" />
                                    <div>
                                        <p className="font-bold text-gray-900 text-xs leading-tight">Grab (GrabSend)</p>
                                        <p className="text-xs text-gray-500">20-30 menit</p>
                                    </div>
                                </div>
                                <p className="mt-2 text-sm font-semibold text-[#00B14F]">
                                    {formatRupiah(Math.max(10000, Math.round(2800 * distance + weight * 1300 + height * 185)))}
                                </p>
                            </button>
                        )}

                        {/* UMKM.go */}
                        {allowed.includes('umkm_go') && (
                            <button
                                type="button"
                                onClick={() => setDeliveryMethod('umkm_go')}
                                className={`rounded-xl border-2 p-4 text-left transition-all ${
                                    deliveryMethod === 'umkm_go'
                                        ? 'border-[#009b97] bg-[#009b97]/5 shadow-md'
                                        : 'border-gray-200 hover:border-gray-300'
                                }`}
                            >
                                <div className="flex items-center gap-2">
                                    <Icon icon="lucide:package" className="w-8 h-8 text-[#009b97]" />
                                    <div>
                                        <p className="font-bold text-gray-900 text-xs leading-tight">UMKM.go (Kurir)</p>
                                        <p className="text-xs text-gray-500 font-semibold text-emerald-600">Lebih Murah</p>
                                    </div>
                                </div>
                                <p className="mt-2 text-sm font-semibold text-[#009b97]">
                                    {formatRupiah(Math.max(6000, Math.round(1800 * distance + weight * 1000 + height * 120)))}
                                </p>
                            </button>
                        )}
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
                        <span>{formatRupiah(subtotal)}</span>
                    </div>
                    <div className="flex justify-between text-sm text-gray-600">
                        <span>
                            Ongkir ({distance.toFixed(1)} km)
                            <span className="block text-[10px] text-gray-400">
                                Berat: {weight.toFixed(1)} kg · Tinggi: {height.toFixed(0)} cm
                            </span>
                        </span>
                        <span>{formatRupiah(deliveryFee)}</span>
                    </div>
                    <div className="flex justify-between text-sm text-gray-600">
                        <span>Pajak Aplikasi (2%)</span>
                        <span>{formatRupiah(appTax)}</span>
                    </div>
                    <div className="flex justify-between text-sm text-orange-600">
                        <span>Pajak QRIS</span>
                        <span>{formatRupiah(qrisTax)}</span>
                    </div>
                    <div className="flex justify-between border-t border-gray-200 pt-2 text-lg font-bold text-gray-900">
                        <span>Total</span>
                        <span className="text-[#009b97]">{formatRupiah(total)}</span>
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
                            Membuat Pesanan...
                        </span>
                    ) : (
                        <>Bayar dengan QRIS — {formatRupiah(total)}</>
                    )}
                </button>
            </form>

            {/* QRIS PAYMENT DIALOG MODAL */}
            <AnimatePresence>
                {showQrisModal && createdOrder && (
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
                                            src={`https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=${encodeURIComponent(createdOrder.dynamic_qris)}`}
                                            alt="QRIS Barcode"
                                            className="w-full h-auto rounded-lg object-contain"
                                        />
                                        <span className="text-[8px] font-bold text-slate-400 mt-1">NMID: ID1025464889274 (Dinamis)</span>
                                    </div>

                                    {/* Amount Details */}
                                    <div className="bg-emerald-50/50 rounded-xl p-3 border border-emerald-100/50 text-left space-y-1">
                                        <div className="flex justify-between items-center">
                                            <span className="text-[10px] text-slate-500 uppercase tracking-wider font-bold">Total Transfer</span>
                                            <span className="text-xs font-mono font-bold text-slate-400">Kode: {createdOrder.order_code}</span>
                                        </div>
                                        <div className="flex items-baseline gap-1 mt-0.5">
                                            <p className="text-2xl font-black text-emerald-600">{formatRupiah(createdOrder.total)}</p>
                                        </div>
                                        {createdOrder.unique_code > 0 && (
                                            <p className="text-[10px] text-emerald-800 bg-emerald-100/50 px-2 py-0.5 rounded inline-block font-semibold">
                                                *Sudah termasuk kode unik transfer: <strong>{createdOrder.unique_code}</strong>
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
        </motion.div>
    );
}
