import { createRoot } from 'react-dom/client';
import UserOrderHistory from './components/order/UserOrderHistory';

function init() {
    const el = document.getElementById('user-orders-root');
    if (el) {
        createRoot(el).render(<UserOrderHistory />);
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
