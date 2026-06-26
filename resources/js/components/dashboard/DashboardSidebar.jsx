import { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { 
    LayoutDashboard, 
    ShoppingCart, 
    Bot, 
    MessageSquare, 
    Video, 
    ClipboardList, 
    Settings, 
    Store,
    ChevronLeft,
    ChevronRight,
    Menu,
    X
} from 'lucide-react';

const menuItems = [
    { id: 'dashboard', label: 'Dashboard', icon: LayoutDashboard, href: '/umkm/dashboard' },
    { id: 'orders', label: 'Pesanan', icon: ShoppingCart, href: '/umkm/dashboard#orders' },
    { id: 'ai', label: 'AI Konsultasi', icon: Bot, href: '/umkm/ai-consultation' },
    { id: 'comments', label: 'Komentar', icon: MessageSquare, href: '/umkm/komentar' },
    { id: 'videos', label: 'Upload Video', icon: Video, href: '/umkm/videos/create' },
    { id: 'laporan', label: 'History Laporan', icon: ClipboardList, href: '/umkm/history-laporan' },
    { id: 'account', label: 'Edit Akun', icon: Settings, href: '/umkm/account/edit' },
    { id: 'katalog', label: 'Lihat Katalog', icon: Store, href: '/katalog' },
];

export default function DashboardSidebar({ umkmName, currentPath }) {
    const [mobileOpen, setMobileOpen] = useState(false);
    const [collapsed, setCollapsed] = useState(false);

    // Sync layout margin dynamically when collapsed state changes
    useEffect(() => {
        const mainContent = document.getElementById('main-content-layout');
        if (mainContent) {
            if (collapsed) {
                mainContent.classList.remove('lg:pl-64');
                mainContent.classList.add('lg:pl-20');
            } else {
                mainContent.classList.remove('lg:pl-20');
                mainContent.classList.add('lg:pl-64');
            }
        }
    }, [collapsed]);

    const SidebarContent = ({ onNavigate }) => (
        <div className="flex h-full flex-col bg-white">
            {/* Sidebar Brand Header */}
            <div className="flex h-16 items-center justify-between px-4 border-b border-slate-50">
                <div className="flex items-center gap-3 overflow-hidden">
                    <div className="flex h-10 w-10 min-w-[2.5rem] items-center justify-center rounded-xl bg-brand-600 text-sm text-white font-black shadow-md shadow-brand-500/10">
                        {umkmName?.charAt(0)?.toUpperCase() || 'U'}
                    </div>
                    {!collapsed && (
                        <motion.div 
                            initial={{ opacity: 0, x: -10 }}
                            animate={{ opacity: 1, x: 0 }}
                            className="min-w-0"
                        >
                            <p className="truncate text-sm font-bold text-slate-800 tracking-tight">{umkmName || 'Dashboard UMKM'}</p>
                            <p className="text-xs font-semibold text-slate-400">Panel Pengelola</p>
                        </motion.div>
                    )}
                </div>
            </div>

            {/* Navigation Menu */}
            <nav className="flex-1 space-y-1 px-3 py-4 overflow-y-auto">
                {menuItems.map((item) => {
                    const Icon = item.icon;
                    // Strict active route check
                    const active = currentPath === item.href || (item.id === 'orders' && currentPath.includes('dashboard') && window.location.hash === '#orders');
                    
                    return (
                        <motion.a
                            key={item.id}
                            href={item.href}
                            onClick={onNavigate}
                            className={`relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition-all group ${
                                active
                                    ? 'text-brand-700 bg-brand-50/50'
                                    : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50/50'
                            }`}
                            whileHover={{ x: active ? 0 : 3 }}
                            whileTap={{ scale: 0.98 }}
                        >
                            {/* Active Indicator Slide Bar */}
                            {active && (
                                <motion.div
                                    layoutId="sidebar-active-indicator"
                                    className="absolute left-0 top-2 bottom-2 w-1 rounded-r bg-brand-600"
                                    initial={false}
                                    transition={{ type: "spring", stiffness: 350, damping: 25 }}
                                />
                            )}
                            
                            <Icon className={`h-5 w-5 transition-colors ${active ? 'text-brand-600' : 'text-slate-400 group-hover:text-slate-600'}`} />
                            
                            {!collapsed && (
                                <motion.span 
                                    initial={{ opacity: 0 }}
                                    animate={{ opacity: 1 }}
                                    className="truncate"
                                >
                                    {item.label}
                                </motion.span>
                            )}
                        </motion.a>
                    );
                })}
            </nav>

            {/* Sidebar Collapse Trigger Footer */}
            <div className="border-t border-slate-50 p-3">
                <button
                    onClick={() => setCollapsed(!collapsed)}
                    className="hidden w-full items-center justify-center gap-2 rounded-xl py-2.5 text-xs font-bold text-slate-400 hover:bg-slate-50 hover:text-slate-600 transition lg:flex"
                >
                    {collapsed ? (
                        <ChevronRight className="h-4 w-4" />
                    ) : (
                        <>
                            <ChevronLeft className="h-4 w-4" />
                            <span>Kecilkan Sidebar</span>
                        </>
                    )}
                </button>
            </div>
        </div>
    );

    return (
        <>
            {/* Mobile floating toggle button */}
            <button
                onClick={() => setMobileOpen(true)}
                className="fixed bottom-6 right-6 z-40 flex h-12 w-12 items-center justify-center rounded-full bg-brand-600 text-white shadow-lg shadow-brand-500/20 hover:scale-105 active:scale-95 transition lg:hidden"
                aria-label="Open Sidebar"
            >
                <Menu className="h-6 w-6" />
            </button>

            {/* Desktop fixed sidebar */}
            <aside
                className={`hidden lg:fixed lg:inset-y-0 lg:left-0 lg:z-30 lg:flex lg:flex-col border-r border-slate-100 bg-white shadow-soft transition-all duration-300 ${
                    collapsed ? 'lg:w-20' : 'lg:w-64'
                }`}
            >
                <SidebarContent />
            </aside>

            {/* Mobile drawer sidebar */}
            <AnimatePresence>
                {mobileOpen && (
                    <>
                        {/* Overlay backdrop */}
                        <motion.div
                            initial={{ opacity: 0 }}
                            animate={{ opacity: 1 }}
                            exit={{ opacity: 0 }}
                            className="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm lg:hidden"
                            onClick={() => setMobileOpen(false)}
                        />
                        {/* Mobile sidebar container */}
                        <motion.aside
                            initial={{ x: '-100%' }}
                            animate={{ x: 0 }}
                            exit={{ x: '-100%' }}
                            transition={{ type: 'spring', damping: 25, stiffness: 260 }}
                            className="fixed inset-y-0 left-0 z-50 w-72 bg-white shadow-2xl lg:hidden flex flex-col"
                        >
                            {/* Close Button Inside Mobile Sidebar */}
                            <div className="flex h-16 items-center justify-end px-4 border-b border-slate-50">
                                <button
                                    onClick={() => setMobileOpen(false)}
                                    className="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-50 rounded-xl"
                                >
                                    <X className="h-5 w-5" />
                                </button>
                            </div>
                            <div className="flex-1 overflow-y-auto">
                                <SidebarContent onNavigate={() => setMobileOpen(false)} />
                            </div>
                        </motion.aside>
                    </>
                )}
            </AnimatePresence>
        </>
    );
}
