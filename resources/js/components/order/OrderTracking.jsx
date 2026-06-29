import { useState } from 'react';
import { Icon } from '@iconify/react';
import { motion, AnimatePresence } from 'framer-motion';

const STATUS_STEPS = [
    { key: 'pending', label: 'Pesanan Dibuat', icon: 'lucide:clipboard-list', desc: 'Menunggu konfirmasi pembayaran' },
    { key: 'confirmed', label: 'Dikonfirmasi', icon: 'lucide:check-circle', desc: 'Pesanan dikonfirmasi UMKM' },
    { key: 'processing', label: 'Diproses', icon: 'mdi:chef-hat', desc: 'Sedang dipersiapkan' },
    { key: 'on_the_way', label: 'Dalam Perjalanan', icon: 'material-symbols:moped-outline', desc: 'Driver sedang mengantar' },
    { key: 'delivered', label: 'Selesai', icon: 'lucide:party-popper', desc: 'Pesanan telah diterima' },
];

// Map order_status ke step index (processing = antar jalan)
const STATUS_MAP = {
    pending: 0,
    confirmed: 1,
    processing: 2,
    delivered: 4,
    cancelled: -1,
};

const DELIVERY_ICONS = {
    gojek: 'material-symbols:moped-outline',
    grab: 'lucide:car',
    umkm_go: 'lucide:package',
};

export default function OrderTracking({ order, onRefresh }) {
    const [lightboxPhoto, setLightboxPhoto] = useState(null);
    const currentStep = STATUS_MAP[order.order_status] ?? 0;
    const isCancelled = order.order_status === 'cancelled';
    const isProcessing = order.order_status === 'processing' && order.driver_name;

    // Show "on the way" step if processing with driver
    const displayStep = isProcessing ? 3 : currentStep;

    return (
        <div className="rounded-2xl border border-gray-200 bg-white overflow-hidden shadow-sm">
            {/* Header */}
            <div className="bg-gradient-to-r from-slate-800 to-slate-700 px-5 py-4">
                <div className="flex items-center justify-between">
                    <div>
                        <p className="text-white/70 text-xs font-medium uppercase tracking-wide">Kode Pesanan</p>
                        <p className="text-white font-bold text-lg">{order.order_code}</p>
                    </div>
                    <div className="text-right">
                        <p className="text-white/70 text-xs">{order.created_at}</p>
                        {!isCancelled && (
                            <button
                                onClick={onRefresh}
                                className="mt-1 text-xs text-[#34d399] hover:text-emerald-300 transition flex items-center gap-1 justify-end"
                            >
                                <Icon icon="lucide:refresh-cw" className="w-3 h-3" />
                                Refresh Status
                            </button>
                        )}
                    </div>
                </div>
            </div>

            {isCancelled ? (
                <div className="p-6 text-center space-y-2">
                    <div className="flex justify-center text-red-500">
                        <Icon icon="lucide:x-circle" className="w-12 h-12" />
                    </div>
                    <p className="font-bold text-red-600 text-lg">Pesanan Dibatalkan</p>
                    <p className="text-gray-500 text-sm">Pesanan ini telah dibatalkan.</p>
                </div>
            ) : (
                <div className="p-5">
                    {/* Progress Steps */}
                    <div className="relative mb-6">
                        {/* Progress Line */}
                        <div className="absolute top-5 left-5 right-5 h-1 bg-gray-200 rounded-full" style={{ zIndex: 0 }}>
                            <div
                                className="h-full rounded-full bg-gradient-to-r from-[#009b97] to-emerald-400 transition-all duration-700"
                                style={{ width: `${(displayStep / (STATUS_STEPS.length - 1)) * 100}%` }}
                            />
                        </div>

                        <div className="relative flex justify-between" style={{ zIndex: 1 }}>
                            {STATUS_STEPS.map((step, idx) => {
                                const done = idx <= displayStep;
                                const active = idx === displayStep;
                                return (
                                    <div key={step.key} className="flex flex-col items-center" style={{ width: `${100 / STATUS_STEPS.length}%` }}>
                                        <div className={`flex h-10 w-10 items-center justify-center rounded-full border-2 transition-all duration-500 ${
                                            done
                                                ? 'border-[#009b97] bg-[#009b97] text-white shadow-lg shadow-[#009b97]/30'
                                                : 'border-gray-200 bg-white text-gray-300'
                                        } ${active ? 'scale-110 ring-4 ring-[#009b97]/20' : ''}`}>
                                            {done ? (
                                                <Icon icon={step.icon} className="w-5 h-5" />
                                            ) : (
                                                <span className="text-sm font-semibold">{idx + 1}</span>
                                            )}
                                        </div>
                                        <p className={`mt-2 text-center text-[10px] font-bold leading-tight ${done ? 'text-[#009b97]' : 'text-gray-400'}`} style={{ maxWidth: 70 }}>
                                            {step.label}
                                        </p>
                                    </div>
                                );
                            })}
                        </div>
                    </div>

                    {/* Current Status Description */}
                    <div className="mb-4 rounded-xl bg-[#009b97]/5 border border-[#009b97]/20 p-4">
                        <div className="flex items-center gap-3">
                            <div className="text-[#009b97] flex-shrink-0">
                                <Icon icon={STATUS_STEPS[displayStep]?.icon} className="w-8 h-8" />
                            </div>
                            <div>
                                <p className="font-bold text-gray-900">{STATUS_STEPS[displayStep]?.label}</p>
                                <p className="text-sm text-gray-600">{STATUS_STEPS[displayStep]?.desc}</p>
                            </div>
                        </div>
                    </div>

                    {/* Driver Info */}
                    {order.driver_name && (
                        <div className="rounded-xl border border-amber-200 bg-amber-50 p-4">
                            <p className="text-xs font-bold text-amber-700 uppercase tracking-wide mb-2 flex items-center gap-1">
                                <Icon icon={DELIVERY_ICONS[order.delivery_method] ?? 'lucide:truck'} className="w-4 h-4" />
                                Info Driver / Kurir
                            </p>
                            <div className="flex items-center gap-3">
                                <div 
                                    onClick={() => order.driver_photo && setLightboxPhoto(order.driver_photo)}
                                    className={`flex h-16 w-16 items-center justify-center rounded-xl bg-amber-100 text-[#d97706] overflow-hidden flex-shrink-0 border border-amber-200 shadow-sm ${order.driver_photo ? 'cursor-pointer hover:opacity-90 transition' : ''}`}
                                >
                                    {order.driver_photo ? (
                                        <img src={order.driver_photo} alt={order.driver_name} className="w-full h-full object-cover" />
                                    ) : (
                                        <Icon icon={DELIVERY_ICONS[order.delivery_method] ?? 'lucide:truck'} className="w-8 h-8" />
                                    )}
                                </div>
                                <div>
                                    <p className="font-bold text-gray-900">{order.driver_name}</p>
                                    {order.driver_phone && (
                                        <a
                                            href={`tel:${order.driver_phone}`}
                                            className="text-sm text-[#009b97] hover:underline flex items-center gap-1 font-semibold"
                                        >
                                            <Icon icon="lucide:phone" className="w-3.5 h-3.5" />
                                            {order.driver_phone}
                                        </a>
                                    )}
                                    {order.driver_code && (
                                        <p className="text-xs text-gray-500 mt-1 flex items-center gap-1">
                                            <Icon icon="lucide:qr-code" className="w-3 h-3" />
                                            Kode: <span className="font-mono font-bold">{order.driver_code}</span>
                                        </p>
                                    )}
                                    {order.driver_photo && (
                                        <button
                                            type="button"
                                            onClick={() => setLightboxPhoto(order.driver_photo)}
                                            className="mt-2 flex items-center gap-1 text-xs font-bold text-[#009b97] hover:text-[#007a77] bg-[#009b97]/10 px-2.5 py-1 rounded-lg transition-all"
                                        >
                                            <Icon icon="lucide:eye" className="w-3.5 h-3.5" />
                                            Lihat Foto Driver
                                        </button>
                                    )}
                                </div>
                            </div>
                            <p className="mt-2 text-xs text-amber-600 flex items-center gap-1">
                                <Icon icon="lucide:info" className="w-3 h-3 flex-shrink-0" />
                                {order.delivery_method === 'gojek' ? 'Gunakan kode di atas untuk tracking di aplikasi Gojek' :
                                 order.delivery_method === 'grab' ? 'Gunakan kode di atas untuk tracking di aplikasi Grab' :
                                 'Hubungi nomor di atas untuk info pengiriman'}
                            </p>
                        </div>
                    )}

                    {/* Delivery method badge */}
                    {!order.driver_name && order.order_status === 'processing' && (
                        <div className="rounded-xl bg-blue-50 border border-blue-200 p-3 text-sm text-blue-700 flex items-center gap-2">
                            <Icon icon="lucide:loader" className="w-4 h-4 animate-spin" />
                            Menunggu driver dikonfirmasi oleh UMKM...
                        </div>
                    )}
                </div>
            )}

            {/* Lightbox Modal for driver photo */}
            <AnimatePresence>
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
                        
                        <div className="mt-4 flex gap-3">
                            <a 
                                href={lightboxPhoto} 
                                download={`driver_${order.driver_name || 'photo'}.png`}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="rounded-xl bg-[#009b97] px-6 py-3 font-bold text-white shadow-md hover:bg-[#007a77] transition-all flex items-center gap-2"
                            >
                                <Icon icon="lucide:download" className="w-5 h-5" />
                                Download Gambar
                            </a>
                            <button 
                                onClick={() => setLightboxPhoto(null)}
                                className="rounded-xl bg-slate-700 px-6 py-3 font-bold text-white hover:bg-slate-600 transition-all"
                            >
                                Tutup
                            </button>
                        </div>
                    </div>
                )}
            </AnimatePresence>
        </div>
    );
}
