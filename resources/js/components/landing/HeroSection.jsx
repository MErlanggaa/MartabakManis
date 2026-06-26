import { motion } from 'framer-motion';
import SlideUp from '../motion/SlideUp';
import FadeIn from '../motion/FadeIn';

export default function HeroSection({ katalogUrl, loginUrl }) {
    return (
        <section className="relative overflow-hidden bg-brand-900 pb-20 pt-16 md:pb-32 md:pt-24 lg:pb-36 lg:pt-32">
            
            {/* Organic Soft Gradients Background */}
            <div className="pointer-events-none absolute inset-0 overflow-hidden">
                <motion.div 
                    className="absolute -right-32 -top-32 h-[600px] w-[600px] rounded-full bg-brand-600/30 blur-[120px]"
                    animate={{ scale: [1, 1.1, 1], opacity: [0.4, 0.7, 0.4] }}
                    transition={{ duration: 12, repeat: Infinity, ease: "easeInOut" }}
                />
                <motion.div 
                    className="absolute -bottom-32 -left-32 h-[500px] w-[500px] rounded-full bg-accent/20 blur-[100px]"
                    animate={{ scale: [1.1, 1, 1.1], opacity: [0.3, 0.6, 0.3] }}
                    transition={{ duration: 15, repeat: Infinity, ease: "easeInOut" }}
                />
            </div>

            <div className="container relative z-10 mx-auto px-4">
                <div className="mx-auto max-w-4xl text-center">
                    <FadeIn delay={0.1}>
                        <span className="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-1.5 text-sm font-medium text-white/90 backdrop-blur-md shadow-soft">
                            <span className="h-2 w-2 animate-pulse rounded-full bg-accent" />
                            Platform UMKM Indonesia #1
                        </span>
                    </FadeIn>

                    <SlideUp delay={0.2}>
                        <h1 className="mt-6 text-4xl font-extrabold leading-tight tracking-tight text-white md:text-5xl lg:text-6xl">
                            Temukan Produk UMKM
                            <span className="block bg-gradient-to-r from-accent-light to-white bg-clip-text text-transparent">
                                Terbaik di Sekitarmu
                            </span>
                        </h1>
                    </SlideUp>

                    <SlideUp delay={0.35}>
                        <p className="mx-auto mt-6 max-w-2xl text-lg text-brand-50 md:text-xl">
                            Jelajahi ribuan produk dan layanan dari pelaku UMKM lokal.
                            Pesan langsung, bayar aman, dan dukung ekonomi kerakyatan.
                        </p>
                    </SlideUp>

                    <SlideUp delay={0.5}>
                        <div className="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                            <motion.a
                                href={katalogUrl}
                                className="inline-flex items-center justify-center gap-2 rounded-lg bg-white px-6 py-3 font-semibold text-brand-700 shadow-card transition-all hover:bg-brand-50 hover:shadow-card-hover"
                                whileHover={{ scale: 1.02 }}
                                whileTap={{ scale: 0.98 }}
                            >
                                Jelajahi Katalog
                                <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                </svg>
                            </motion.a>
                            <motion.a
                                href={loginUrl}
                                className="inline-flex items-center justify-center gap-2 rounded-lg border border-white/30 bg-white/5 backdrop-blur-sm px-6 py-3 font-semibold text-white transition hover:bg-white/15"
                                whileHover={{ scale: 1.02 }}
                                whileTap={{ scale: 0.98 }}
                            >
                                Daftarkan UMKM Anda
                            </motion.a>
                        </div>
                    </SlideUp>
                </div>
            </div>

            {/* Clean structural divider */}
            <div className="absolute bottom-0 left-0 right-0">
                <svg viewBox="0 0 1440 60" fill="none" preserveAspectRatio="none" className="w-full h-[60px] text-surface">
                    <path d="M0 60L1440 60L1440 0C1440 0 1140 60 720 60C300 60 0 0 0 0L0 60Z" fill="currentColor" />
                </svg>
            </div>
        </section>
    );
}
