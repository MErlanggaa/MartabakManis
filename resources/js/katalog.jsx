import { createRoot } from 'react-dom/client';
import HeroSection from './components/landing/HeroSection';
import StatsSection from './components/landing/StatsSection';
import FeaturesSection from './components/landing/FeaturesSection';
import ProductGrid from './components/catalog/ProductGrid';

function init() {
    const heroEl = document.getElementById('katalog-hero-root');
    if (heroEl) {
        createRoot(heroEl).render(
            <HeroSection
                katalogUrl={heroEl.dataset.katalogUrl}
                loginUrl={heroEl.dataset.loginUrl}
            />
        );
    }

    const statsEl = document.getElementById('katalog-stats-root');
    if (statsEl) {
        createRoot(statsEl).render(
            <StatsSection stats={JSON.parse(statsEl.dataset.stats || '[]')} />
        );
    }

    const featuresEl = document.getElementById('katalog-features-root');
    if (featuresEl) {
        createRoot(featuresEl).render(<FeaturesSection />);
    }

    const productsEl = document.getElementById('katalog-products-root');
    if (productsEl) {
        createRoot(productsEl).render(
            <ProductGrid
                products={JSON.parse(productsEl.dataset.products || '[]')}
                total={parseInt(productsEl.dataset.total || '0', 10)}
                showing={parseInt(productsEl.dataset.showing || '0', 10)}
            />
        );
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
