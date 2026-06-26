import { motion } from 'framer-motion';

export default function SlideUp({ children, className = '', delay = 0, duration = 0.5 }) {
    return (
        <motion.div
            className={className}
            initial={{ opacity: 0, y: 32 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration, delay, ease: [0.22, 1, 0.36, 1] }}
        >
            {children}
        </motion.div>
    );
}
