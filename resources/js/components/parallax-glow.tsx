import React from "react";
import { motion, useScroll, useTransform, useSpring } from "framer-motion";

export function ParallaxGlow() {
  // track scroll progress 0..1
  const { scrollYProgress } = useScroll();
  // smooth the progress for buttery motion
  const smooth = useSpring(scrollYProgress, { stiffness: 120, damping: 20, mass: 0.25 });

  // map progress to subtle translations / scale
  const topX = useTransform(smooth, [0, 1], [0, -60]);
  const topY = useTransform(smooth, [0, 1], [0, 40]);

  const bottomX = useTransform(smooth, [0, 1], [0, 60]);
  const bottomY = useTransform(smooth, [0, 1], [0, -40]);

  const sheen = useTransform(smooth, [0, 1], [0.06, 0.02]); // opacity drift

  return (
    <div className="pointer-events-none absolute inset-0">
      <motion.div
        style={{ x: topX, y: topY }}
        className="absolute -top-40 -left-32 h-[520px] w-[520px] rounded-full
                   bg-[radial-gradient(closest-side,rgba(120,119,198,0.45),rgba(120,119,198,0)_70%)]
                   blur-2xl"
      />
      <motion.div
        style={{ x: bottomX, y: bottomY }}
        className="absolute -bottom-32 -right-24 h-[520px] w-[520px] rounded-full
                   bg-[radial-gradient(closest-side,rgba(56,189,248,0.45),rgba(56,189,248,0)_70%)]
                   blur-2xl"
      />
      <motion.div
        style={{ opacity: sheen }}
        className="absolute inset-0 bg-[linear-gradient(120deg,rgba(255,255,255,0.08),rgba(255,255,255,0.02))]"
      />
    </div>
  );
}
