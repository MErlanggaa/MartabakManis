import { motion, useInView } from 'framer-motion';
import { useRef } from 'react';

export default function ScrollReveal({
    children,
    className = '',
    delay = 0,
    direction = 'up',
    once = true,
}) {
    const ref = useRef(null);
    const isInView = useInView(ref, { once, margin: '-60px' });

    const offsets = {
        up: { y: 40, x: 0 },
        down: { y: -40, x: 0 },
        left: { x: 40, y: 0 },
        right: { x: -40, y: 0 },
    };

    const offset = offsets[direction] || offsets.up;

    return (
        <motion.div
            ref={ref}
            className={className}
            initial={{ opacity: 0, ...offset }}
            animate={isInView ? { opacity: 1, x: 0, y: 0 } : { opacity: 0, ...offset }}
            transition={{ duration: 0.6, delay, ease: [0.22, 1, 0.36, 1] }}
        >
            {children}
        </motion.div>
    );
}
