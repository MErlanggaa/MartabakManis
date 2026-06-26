import { createRoot } from 'react-dom/client';
import DashboardSidebar from './components/dashboard/DashboardSidebar';
import DashboardStatCards from './components/dashboard/DashboardStatCards';

function init() {
    const sidebarEl = document.getElementById('umkm-sidebar-root');
    if (sidebarEl) {
        createRoot(sidebarEl).render(
            <DashboardSidebar
                umkmName={sidebarEl.dataset.umkmName}
                currentPath={window.location.pathname}
            />
        );
    }

    const statsEl = document.getElementById('umkm-stats-cards-root');
    if (statsEl) {
        createRoot(statsEl).render(
            <DashboardStatCards stats={JSON.parse(statsEl.dataset.stats || '[]')} />
        );
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
