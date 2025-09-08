import React from "react";
import { motion, useReducedMotion } from "framer-motion";

type Props = {
  children: React.ReactNode;
  /** "up" | "down" | "left" | "right" */
  direction?: "up" | "down" | "left" | "right";
  /** delay in seconds */
  delay?: number;
  className?: string;
};

const dirMap = {
  up: { y: 24, x: 0 },
  down: { y: -24, x: 0 },
  left: { x: 24, y: 0 },
  right: { x: -24, y: 0 },
};

export function AnimatedSection({
  children,
  direction = "up",
  delay = 0,
  className,
}: Props) {
  const prefersReduced = useReducedMotion();
  const offset = dirMap[direction];

  return (
    <motion.section
      className={className}
      initial={prefersReduced ? false : { opacity: 0, ...offset }}
      whileInView={prefersReduced ? {} : { opacity: 1, x: 0, y: 0 }}
      viewport={{ once: true, amount: 0.2 }}
      transition={{ duration: 0.6, ease: [0.22, 1, 0.36, 1], delay }}
    >
      {children}
    </motion.section>
  );
}
