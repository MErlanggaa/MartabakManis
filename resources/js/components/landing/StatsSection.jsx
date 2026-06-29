import { motion } from 'framer-motion';
import { Icon } from '@iconify/react';
import ScrollReveal from '../motion/ScrollReveal';

const icons = {
    umkm: 'lucide:store',
    products: 'lucide:package',
    users: 'lucide:users',
    orders: 'lucide:shopping-cart',
};

function StatCard({ stat, index }) {
    return (
        <ScrollReveal delay={index * 0.1}>
            <motion.div
                className="group relative overflow-hidden rounded-xl border border-slate-100 bg-white p-6 text-center shadow-card transition-all duration-300 hover:-translate-y-1 hover:shadow-card-hover"
            >
                <div className="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-brand-50/50 blur-2xl transition-all group-hover:bg-brand-50" />
                <div className="relative z-10 mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-xl bg-brand-50 text-brand-600 transition-transform duration-300 group-hover:scale-110">
                    <Icon icon={icons[stat.key] || 'lucide:sparkles'} className="w-7 h-7" />
                </div>
                <p className="relative z-10 text-3xl font-extrabold text-slate-900 md:text-4xl">
                    {stat.value}
                </p>
                <p className="relative z-10 mt-1 text-sm font-semibold text-slate-500 uppercase tracking-wider">{stat.label}</p>
            </motion.div>
        </ScrollReveal>
    );
}

export default function StatsSection({ stats }) {
    return (
        <section className="container mx-auto px-4 -mt-12 md:-mt-16 relative z-10">
            <div className="grid grid-cols-2 gap-4 md:grid-cols-4 md:gap-6">
                {stats.map((stat, i) => (
                    <StatCard key={stat.key} stat={stat} index={i} />
                ))}
            </div>
        </section>
    );
}
