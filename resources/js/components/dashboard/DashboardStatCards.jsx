import ScrollReveal from '../motion/ScrollReveal';
import { motion } from 'framer-motion';

const gradients = [
    'from-blue-500 to-blue-600',
    'from-emerald-500 to-emerald-600',
    'from-violet-500 to-violet-600',
    'from-rose-500 to-rose-600',
    'from-brand-500 to-brand-700',
];

export default function DashboardStatCards({ stats }) {
    return (
        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
            {stats.map((stat, i) => (
                <ScrollReveal key={stat.label} delay={i * 0.08}>
                    <motion.div
                        className="group relative overflow-hidden rounded-xl border border-slate-100 bg-white p-6 shadow-card transition-all duration-300 hover:-translate-y-1 hover:shadow-card-hover"
                        whileHover={{ y: -4 }}
                        transition={{ type: 'spring', stiffness: 300 }}
                    >
                        <div className="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-slate-50 transition-transform duration-500 group-hover:scale-150" />
                        <div className="relative flex items-center justify-between">
                            <div>
                                <p className="text-sm font-medium text-slate-500">{stat.label}</p>
                                <p className="mt-2 text-2xl font-bold tracking-tight text-slate-900 md:text-3xl">{stat.value}</p>
                            </div>
                            <div className={`flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br ${gradients[i % gradients.length]} text-xl text-white shadow-soft transition-transform duration-300 group-hover:scale-110`}>
                                {stat.icon}
                            </div>
                        </div>
                    </motion.div>
                </ScrollReveal>
            ))}
        </div>
    );
}
