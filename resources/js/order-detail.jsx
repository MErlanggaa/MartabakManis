import { createRoot } from 'react-dom/client';
import UserOrderDetailPage from './components/order/UserOrderDetailPage';

function init() {
    const el = document.getElementById('user-order-detail-root');
    if (el) {
        const orderId = el.dataset.orderId;
        createRoot(el).render(<UserOrderDetailPage orderId={orderId} />);
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
