import { createRoot } from 'react-dom/client';
import AdminWithdrawAlert from './components/admin/AdminWithdrawAlert';

function init() {
    const rootEl = document.getElementById('admin-withdraw-root');
    if (rootEl) {
        createRoot(rootEl).render(<AdminWithdrawAlert />);
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
