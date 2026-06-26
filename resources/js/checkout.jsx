import { createRoot } from 'react-dom/client';
import Checkout from './components/Checkout';

function init() {
    const el = document.getElementById('checkout-root');
    if (el) {
        const layanan = JSON.parse(el.dataset.layanan || '{}');
        const umkm = JSON.parse(el.dataset.umkm || '{}');
        const user = JSON.parse(el.dataset.user || 'null');
        const isAuthenticated = el.dataset.authenticated === 'true';

        createRoot(el).render(
            <Checkout
                layanan={layanan}
                umkm={umkm}
                user={user}
                isAuthenticated={isAuthenticated}
            />
        );
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
