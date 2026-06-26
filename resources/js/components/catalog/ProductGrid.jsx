import ProductCard from './ProductCard';
import ScrollReveal from '../motion/ScrollReveal';

export default function ProductGrid({ products, total, showing }) {
    if (!products?.length) {
        return (
            <ScrollReveal className="py-16 text-center">
                <div className="mx-auto max-w-md rounded-2xl border border-dashed border-slate-200 bg-white p-12">
                    <span className="text-5xl">🔍</span>
                    <h3 className="mt-4 text-xl font-bold text-slate-900">Tidak ada produk ditemukan</h3>
                    <p className="mt-2 text-slate-500">Coba ubah kata kunci pencarian atau filter Anda.</p>
                </div>
            </ScrollReveal>
        );
    }

    return (
        <div>
            <ScrollReveal className="mb-6 flex flex-col gap-2 border-b border-slate-100 pb-6 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 className="section-title">Produk & Layanan</h2>
                    <p className="section-subtitle !mt-1 !text-sm">
                        Menampilkan <strong className="text-brand-600">{showing}</strong> dari{' '}
                        <strong>{total}</strong> hasil
                    </p>
                </div>
            </ScrollReveal>

            <div className="grid grid-cols-2 gap-3 sm:gap-4 md:grid-cols-3 lg:grid-cols-4 lg:gap-6">
                {products.map((product, i) => (
                    <ProductCard key={product.id} product={product} index={i} />
                ))}
            </div>
        </div>
    );
}
