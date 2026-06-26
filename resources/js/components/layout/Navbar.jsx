import { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';

export default function Navbar({ user, links, logoUrl, katalogUrl, loginUrl, logoutUrl, csrfToken }) {
    const [scrolled, setScrolled] = useState(false);
    const [mobileOpen, setMobileOpen] = useState(false);
    const [userMenuOpen, setUserMenuOpen] = useState(false);

    useEffect(() => {
        const onScroll = () => setScrolled(window.scrollY > 12);
        window.addEventListener('scroll', onScroll, { passive: true });
        return () => window.removeEventListener('scroll', onScroll);
    }, []);

    useEffect(() => {
        document.body.style.overflow = mobileOpen ? 'hidden' : '';
        return () => { document.body.style.overflow = ''; };
    }, [mobileOpen]);

    // Close user menu when clicking outside
    useEffect(() => {
        if (!userMenuOpen) return;
        const handleClick = (e) => {
            if (!e.target.closest('[data-user-menu]')) setUserMenuOpen(false);
        };
        document.addEventListener('click', handleClick);
        return () => document.removeEventListener('click', handleClick);
    }, [userMenuOpen]);

    const currentPath = window.location.pathname;

    return (
        <header
            className={`sticky top-0 z-50 transition-all duration-300 ${
                scrolled
                    ? 'bg-white/80 shadow-nav backdrop-blur-xl border-b border-slate-100/80'
                    : 'bg-white/60 backdrop-blur-md border-b border-transparent'
            }`}
        >
            <div className="container mx-auto px-4">
                <div className="flex h-16 items-center justify-between md:h-[4.5rem]">
                    <a href={katalogUrl} className="group flex items-center gap-3">
                        <motion.img
                            src={logoUrl}
                            alt="UMKM.go"
                            className="h-9 w-auto object-contain md:h-11"
                            whileHover={{ scale: 1.03 }}
                            transition={{ type: 'spring', stiffness: 400 }}
                        />
                        <span className="hidden font-bold text-slate-900 sm:block">
                            UMKM<span className="text-brand-500">.go</span>
                        </span>
                    </a>

                    {/* Desktop Nav */}
                    <nav className="hidden items-center gap-2 md:flex">
                        {links.map((link) => {
                            const isActive = currentPath === new URL(link.href, window.location.origin).pathname;
                            return (
                                <a
                                    key={link.href}
                                    href={link.href}
                                    className={`relative px-4 py-2 text-sm font-semibold transition-colors duration-200 rounded-xl ${
                                        isActive ? 'text-brand-700' : 'text-slate-500 hover:text-slate-800'
                                    }`}
                                >
                                    {isActive && (
                                        <motion.div
                                            layoutId="navbar-active"
                                            className="absolute inset-0 rounded-xl bg-brand-50 shadow-sm"
                                            initial={false}
                                            transition={{ type: "spring", stiffness: 400, damping: 30 }}
                                        />
                                    )}
                                    <span className="relative z-10 flex items-center">
                                        {link.icon && <span className="mr-1.5">{link.icon}</span>}
                                        {link.label}
                                    </span>
                                </a>
                            );
                        })}

                        {user ? (
                            <div className="relative ml-2" data-user-menu>
                                <motion.button
                                    onClick={() => setUserMenuOpen(!userMenuOpen)}
                                    className="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-slate-700 shadow-soft transition hover:border-brand-200 hover:shadow-card"
                                    whileHover={{ y: -1 }}
                                >
                                    <span className="flex h-7 w-7 items-center justify-center rounded-full bg-brand-500 text-xs font-bold text-white">
                                        {user.name?.charAt(0)?.toUpperCase()}
                                    </span>
                                    <span className="max-w-[120px] truncate">{user.name}</span>
                                    <svg className={`h-4 w-4 transition-transform ${userMenuOpen ? 'rotate-180' : ''}`} fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                                    </svg>
                                </motion.button>

                                <AnimatePresence>
                                    {userMenuOpen && (
                                        <motion.div
                                            initial={{ opacity: 0, y: 8, scale: 0.96 }}
                                            animate={{ opacity: 1, y: 0, scale: 1 }}
                                            exit={{ opacity: 0, y: 8, scale: 0.96 }}
                                            transition={{ duration: 0.15 }}
                                            className="absolute right-0 mt-2 w-52 overflow-hidden rounded-xl border border-slate-100 bg-white shadow-card-hover"
                                        >
                                            {user.menu?.map((item) => (
                                                <a
                                                    key={item.href}
                                                    href={item.href}
                                                    className="flex items-center gap-2 px-4 py-3 text-sm text-slate-600 transition hover:bg-brand-50 hover:text-brand-700"
                                                >
                                                    {item.label}
                                                </a>
                                            ))}
                                            <form action={logoutUrl} method="POST" className="border-t border-slate-100">
                                                <input type="hidden" name="_token" value={csrfToken} />
                                                <button type="submit" className="flex w-full items-center gap-2 px-4 py-3 text-left text-sm text-red-600 transition hover:bg-red-50">
                                                    Keluar
                                                </button>
                                            </form>
                                        </motion.div>
                                    )}
                                </AnimatePresence>
                            </div>
                        ) : (
                            <motion.a
                                href={loginUrl}
                                className="btn-primary ml-2 !py-2.5 !text-sm"
                                whileHover={{ scale: 1.02 }}
                                whileTap={{ scale: 0.98 }}
                            >
                                Masuk / Daftar
                            </motion.a>
                        )}
                    </nav>

                    {/* Mobile toggle */}
                    <div className="flex items-center gap-2 md:hidden">
                        {!user && (
                            <a href={loginUrl} className="btn-primary !px-4 !py-2 !text-sm">Masuk</a>
                        )}
                        <motion.button
                            onClick={() => setMobileOpen(!mobileOpen)}
                            className="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700"
                            whileTap={{ scale: 0.95 }}
                            aria-label="Menu"
                        >
                            {mobileOpen ? (
                                <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            ) : (
                                <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            )}
                        </motion.button>
                    </div>
                </div>
            </div>

            {/* Mobile Menu */}
            <AnimatePresence>
                {mobileOpen && (
                    <motion.div
                        initial={{ opacity: 0, height: 0 }}
                        animate={{ opacity: 1, height: 'auto' }}
                        exit={{ opacity: 0, height: 0 }}
                        transition={{ duration: 0.25 }}
                        className="overflow-hidden border-t border-slate-100 bg-white/95 backdrop-blur-xl md:hidden"
                    >
                        <nav className="container mx-auto flex flex-col gap-1 px-4 py-4">
                            {links.map((link, i) => (
                                <motion.a
                                    key={link.href}
                                    href={link.href}
                                    initial={{ opacity: 0, x: -16 }}
                                    animate={{ opacity: 1, x: 0 }}
                                    transition={{ delay: i * 0.05 }}
                                    className="flex items-center gap-3 rounded-xl px-4 py-3 text-slate-700 transition hover:bg-brand-50 hover:text-brand-700"
                                    onClick={() => setMobileOpen(false)}
                                >
                                    <span>{link.icon}</span>
                                    <span className="font-medium">{link.label}</span>
                                </motion.a>
                            ))}
                            {user && (
                                <>
                                    <div className="my-2 border-t border-slate-100" />
                                    <p className="px-4 text-xs font-semibold uppercase tracking-wider text-slate-400">Akun</p>
                                    {user.menu?.map((item) => (
                                        <a key={item.href} href={item.href} className="block rounded-xl px-4 py-3 text-slate-600 hover:bg-slate-50 transition">
                                            {item.label}
                                        </a>
                                    ))}
                                    <form action={logoutUrl} method="POST">
                                        <input type="hidden" name="_token" value={csrfToken} />
                                        <button type="submit" className="w-full rounded-xl px-4 py-3 text-left text-red-600 hover:bg-red-50 transition">
                                            Keluar
                                        </button>
                                    </form>
                                </>
                            )}
                        </nav>
                    </motion.div>
                )}
            </AnimatePresence>
        </header>
    );
}
