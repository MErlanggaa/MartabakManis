import { motion } from 'framer-motion';

const fadeIn = {
    hidden: { opacity: 0 },
    visible: { opacity: 1, transition: { duration: 0.5, ease: 'easeOut' } },
};

export default function FadeIn({ children, className = '', delay = 0, as = 'div' }) {
    const Component = motion[as] || motion.div;
    return (
        <Component
            className={className}
            initial="hidden"
            animate="visible"
            variants={{
                hidden: fadeIn.hidden,
                visible: { ...fadeIn.visible, transition: { ...fadeIn.visible.transition, delay } },
            }}
        >
            {children}
        </Component>
    );
}
