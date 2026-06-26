import { motion } from 'framer-motion';
import { formatRupiah } from '../../utils/orderPricing';

export default function ProductCard({ product, index = 0 }) {
    const handleClick = () => {
        window.location.href = product.url;
    };

    return (
        <motion.article
            className="group relative cursor-pointer overflow-hidden rounded-2xl border border-slate-100/80 bg-white shadow-card transition-all duration-300 hover:-translate-y-1 hover:shadow-card-hover"
            initial={{ opacity: 0, y: 24 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.4, delay: index * 0.05, ease: [0.22, 1, 0.36, 1] }}
            onClick={handleClick}
        >
            <div className="relative aspect-[4/3] overflow-hidden bg-slate-50">
                {product.photo_url ? (
                    <motion.img
                        src={product.photo_url}
                        alt={product.nama}
                        className="h-full w-full object-cover transition-transform duration-700 ease-out group-hover:scale-110"
                    />
                ) : (
                    <div className="flex h-full items-center justify-center bg-gradient-to-br from-brand-50 to-slate-100">
                        <span className="text-5xl text-slate-300 opacity-50">📦</span>
                    </div>
                )}

                <div className="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-slate-900/0 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100" />

                {product.category && (
                    <span className="absolute left-4 top-4 inline-flex items-center gap-1.5 rounded-full bg-white/95 px-3 py-1.5 text-xs font-bold text-slate-700 shadow-soft backdrop-blur-md">
                        <span className="h-1.5 w-1.5 rounded-full bg-brand-500"></span>
                        {product.category}
                    </span>
                )}

                {product.is_favorite && (
                    <span className="absolute right-4 top-4 flex h-9 w-9 items-center justify-center rounded-full bg-red-500/90 text-white shadow-soft backdrop-blur-md transition-transform group-hover:scale-110">
                        <svg className="h-4 w-4 fill-current" viewBox="0 0 24 24">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                        </svg>
                    </span>
                )}
            </div>

            <div className="p-5">
                {product.umkm_name && (
                    <p className="mb-2 truncate text-xs font-semibold uppercase tracking-wider text-brand-600">{product.umkm_name}</p>
                )}
                <h3 className="line-clamp-2 text-base font-bold leading-snug text-slate-900 md:text-lg">{product.nama}</h3>

                {product.rating > 0 && (
                    <div className="mt-2.5 flex items-center gap-1.5">
                        <svg className="h-4 w-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                        <span className="text-sm font-bold text-slate-700">{product.rating.toFixed(1)}</span>
                    </div>
                )}

                <div className="mt-4 flex items-center justify-between border-t border-slate-100 pt-4">
                    <p className="text-lg font-black text-brand-700 md:text-xl">
                        {formatRupiah(product.price)}
                    </p>
                    <motion.div
                        className="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600 transition-colors group-hover:bg-brand-500 group-hover:text-white"
                        whileHover={{ scale: 1.05 }}
                        whileTap={{ scale: 0.95 }}
                    >
                        <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </motion.div>
                </div>
            </div>
        </motion.article>
    );
}
