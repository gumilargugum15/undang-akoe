import { motion, useInView, type TargetAndTransition } from "motion/react";
import { useRef, type ReactNode } from "react";
import { useInvitationTheme } from "./theme-provider";
import type { RevealKind } from "@/lib/themes";

const variants: Record<RevealKind, { hidden: TargetAndTransition; shown: TargetAndTransition }> = {
  fade: { hidden: { opacity: 0, y: 24 }, shown: { opacity: 1, y: 0 } },
  slide: { hidden: { opacity: 0, x: -40 }, shown: { opacity: 1, x: 0 } },
  zoom: { hidden: { opacity: 0, scale: 0.92, rotate: -1.5 }, shown: { opacity: 1, scale: 1, rotate: 0 } },
  blur: { hidden: { opacity: 0, filter: "blur(14px)", y: 18 }, shown: { opacity: 1, filter: "blur(0px)", y: 0 } },
};

export function Reveal({
  children,
  delay = 0,
  className,
}: {
  children: ReactNode;
  delay?: number;
  className?: string;
}) {
  const { theme } = useInvitationTheme();
  const ref = useRef<HTMLDivElement>(null);
  const inView = useInView(ref, { once: true, margin: "-60px" });
  const v = variants[theme.reveal];

  return (
    <motion.div
      ref={ref}
      className={className}
      initial={v.hidden}
      animate={inView ? v.shown : v.hidden}
      transition={{ duration: 0.75, delay, ease: [0.22, 0.61, 0.36, 1] }}
    >
      {children}
    </motion.div>
  );
}
