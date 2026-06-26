import { createRoot } from 'react-dom/client';
import Navbar from './components/layout/Navbar';

function init() {
    const el = document.getElementById('navbar-root');
    if (el) {
        createRoot(el).render(
            <Navbar
                user={JSON.parse(el.dataset.user || 'null')}
                links={JSON.parse(el.dataset.links || '[]')}
                logoUrl={el.dataset.logoUrl}
                katalogUrl={el.dataset.katalogUrl}
                loginUrl={el.dataset.loginUrl}
                logoutUrl={el.dataset.logoutUrl}
                csrfToken={el.dataset.csrfToken}
            />
        );
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
