import { createRoot } from 'react-dom/client';
import UmkmOrderAlert from './components/UmkmOrderAlert';

function init() {
    const el = document.getElementById('umkm-orders-root');
    if (el) {
        createRoot(el).render(<UmkmOrderAlert />);
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
