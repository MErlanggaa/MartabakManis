import { createRoot } from 'react-dom/client';
import HeroSection from './components/landing/HeroSection';
import StatsSection from './components/landing/StatsSection';
import FeaturesSection from './components/landing/FeaturesSection';

function mountComponent(id, Component, getProps) {
    const el = document.getElementById(id);
    if (!el) return;
    try {
        const props = getProps ? getProps(el) : {};
        createRoot(el).render(<Component {...props} />);
    } catch (err) {
        console.error(`[UMKM] Error mounting #${id}:`, err);
    }
}

function init() {
    mountComponent('landing-hero-root', HeroSection, (el) => ({
        katalogUrl: el.dataset.katalogUrl || '/katalog',
        loginUrl: el.dataset.loginUrl || '/login',
    }));

    mountComponent('landing-stats-root', StatsSection, (el) => ({
        stats: JSON.parse(el.dataset.stats || '[]'),
    }));

    mountComponent('landing-features-root', FeaturesSection, () => ({}));
}

// ES module scripts are deferred — DOM is always ready here.
// But guard with readyState just in case.
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
