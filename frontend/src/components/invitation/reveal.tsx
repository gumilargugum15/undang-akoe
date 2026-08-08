import { motion, useInView, type TargetAndTransition, type Transition } from "motion/react";
import { useRef, type ReactNode } from "react";
import { useInvitationTheme } from "./theme-provider";
import type { RevealKind } from "@/lib/themes";

const DEFAULT_TRANSITION: Transition = { duration: 0.75, ease: [0.22, 0.61, 0.36, 1] };

const variants: Record<
  RevealKind,
  { hidden: TargetAndTransition; shown: TargetAndTransition; transition?: Transition }
> = {
  fade: { hidden: { opacity: 0, y: 24 }, shown: { opacity: 1, y: 0 } },
  slide: { hidden: { opacity: 0, x: -40 }, shown: { opacity: 1, x: 0 } },
  zoom: { hidden: { opacity: 0, scale: 0.92, rotate: -1.5 }, shown: { opacity: 1, scale: 1, rotate: 0 } },
  blur: { hidden: { opacity: 0, filter: "blur(14px)", y: 18 }, shown: { opacity: 1, filter: "blur(0px)", y: 0 } },
  // 3D-ish tip-forward, like a card being set down on a table.
  flip: {
    hidden: { opacity: 0, rotateX: -50, y: 20 },
    shown: { opacity: 1, rotateX: 0, y: 0 },
    transition: { duration: 0.8, ease: [0.22, 0.61, 0.36, 1] },
  },
  // A sweeping diagonal entrance — deliberately transform-only (no clip-path): animated
  // clip-path left several themes rendering fully transparent in testing despite computed
  // styles reporting opacity:1/clip-path:none, a real Chromium compositing bug with the
  // pattern used here (animated clip-path + Framer's layer promotion), not a false alarm.
  curtain: {
    hidden: { opacity: 0, x: 70, skewX: -8 },
    shown: { opacity: 1, x: 0, skewX: 0 },
    transition: { duration: 0.7, ease: [0.65, 0, 0.35, 1] },
  },
  // Springy overshoot instead of an eased curve — playful, energetic.
  bounce: {
    hidden: { opacity: 0, y: 46, scale: 0.88 },
    shown: { opacity: 1, y: 0, scale: 1 },
    transition: { type: "spring", stiffness: 260, damping: 16 },
  },
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
      transition={{ ...DEFAULT_TRANSITION, ...v.transition, delay }}
    >
      {children}
    </motion.div>
  );
}
