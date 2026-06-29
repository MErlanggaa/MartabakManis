import ScrollReveal from '../motion/ScrollReveal';
import { motion } from 'framer-motion';
import { Icon } from '@iconify/react';

const defaultFeatures = [
    {
        icon: 'lucide:search',
        title: 'Pencarian Cerdas',
        description: 'Temukan produk UMKM berdasarkan nama, kategori, jarak, dan preferensi favorit Anda.',
    },
    {
        icon: 'lucide:credit-card',
        title: 'Pembayaran Aman',
        description: 'Bayar dengan Midtrans — aman, cepat, dan mendukung berbagai metode pembayaran.',
    },
    {
        icon: 'lucide:bike',
        title: 'Pengantaran Fleksibel',
        description: 'Pilih Gojek, Grab, atau UMKM.go untuk pengiriman langsung ke alamat Anda.',
    },
    {
        icon: 'lucide:bot',
        title: 'AI Konsultasi Bisnis',
        description: 'Pelaku UMKM mendapat saran bisnis, pemasaran, dan analisis dari AI.',
    },
    {
        icon: 'lucide:bar-chart-2',
        title: 'Dashboard Analytics',
        description: 'Kelola keuntungan, pesanan, dan performa UMKM dalam satu dashboard modern.',
    },
    {
        icon: 'lucide:video',
        title: 'Video Profil UMKM',
        description: 'Tampilkan produk lewat video pendek dan bangun engagement dengan pembeli.',
    },
];

export default function FeaturesSection({ features = defaultFeatures }) {
    return (
        <section className="container mx-auto px-4 py-16 md:py-24">
            <ScrollReveal className="mx-auto max-w-2xl text-center">
                <h2 className="section-title">Mengapa UMKM.go?</h2>
                <p className="section-subtitle">
                    Platform lengkap yang menghubungkan pelaku UMKM dengan masyarakat — dari discovery hingga transaksi.
                </p>
            </ScrollReveal>

            <div className="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                {features.map((feature, i) => (
                    <ScrollReveal key={feature.title} delay={i * 0.08}>
                        <motion.div
                            className="group relative h-full overflow-hidden rounded-2xl border border-slate-100 bg-white p-8 shadow-card transition-all duration-300 hover:-translate-y-1 hover:shadow-card-hover"
                        >
                            <div className="absolute -right-8 -top-8 h-32 w-32 rounded-full bg-brand-50/50 blur-2xl transition-all duration-500 group-hover:bg-brand-50" />
                            <div className="relative z-10 mb-6 flex h-14 w-14 items-center justify-center rounded-xl bg-brand-50 text-brand-600 shadow-soft transition-transform duration-300 group-hover:scale-110">
                                <Icon icon={feature.icon} className="w-7 h-7" />
                            </div>
                            <h3 className="relative z-10 text-xl font-bold text-slate-900">{feature.title}</h3>
                            <p className="relative z-10 mt-3 text-sm leading-relaxed text-slate-500">{feature.description}</p>
                        </motion.div>
                    </ScrollReveal>
                ))}
            </div>
        </section>
    );
}
