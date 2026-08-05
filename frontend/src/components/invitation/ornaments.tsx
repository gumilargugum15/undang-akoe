import { motion } from "motion/react";
import { useInvitationTheme } from "./theme-provider";

/** Decorative divider that changes shape per theme. */
export function Divider({ className = "" }: { className?: string }) {
  const { theme } = useInvitationTheme();
  const k = theme.ornament;

  return (
    <div className={`flex justify-center ${className}`} aria-hidden="true">
      {k === "floral" && (
        <svg
          width="220"
          height="34"
          viewBox="0 0 220 34"
          fill="none"
          className="text-inv-secondary"
        >
          <path d="M8 17h72" stroke="currentColor" strokeWidth="1" />
          <path d="M140 17h72" stroke="currentColor" strokeWidth="1" />
          <path
            d="M110 4c7 6 11 9 11 13s-4 7-11 13c-7-6-11-9-11-13s4-7 11-13Z"
            stroke="currentColor"
            strokeWidth="1"
          />
          <path d="M92 17c6-5 12-5 18 0-6 5-12 5-18 0Z" stroke="currentColor" strokeWidth="1" />
          <path d="M110 17c6-5 12-5 18 0-6 5-12 5-18 0Z" stroke="currentColor" strokeWidth="1" />
          <circle cx="110" cy="17" r="2" fill="currentColor" />
        </svg>
      )}
      {k === "line" && <span className="block h-px w-24 bg-inv-border" />}
      {k === "leaf" && (
        <svg width="200" height="30" viewBox="0 0 200 30" fill="none" className="text-inv-accent">
          <path
            d="M10 15h60M130 15h60"
            stroke="currentColor"
            strokeWidth="1.2"
            strokeLinecap="round"
          />
          <path
            d="M100 3c-9 5-13 10-13 15s5 9 13 9 13-4 13-9-4-10-13-15Z"
            stroke="currentColor"
            strokeWidth="1.2"
            fill="none"
          />
          <path d="M100 6v18" stroke="currentColor" strokeWidth="1" />
          <path d="M78 12c5-3 10-2 13 3-5 3-10 2-13-3Z" fill="currentColor" opacity=".5" />
          <path d="M122 12c-5-3-10-2-13 3 5 3 10 2 13-3Z" fill="currentColor" opacity=".5" />
        </svg>
      )}
      {k === "shimmer" && (
        <div className="flex items-center gap-3">
          <span className="block h-px w-20 bg-gradient-to-r from-transparent to-inv-primary" />
          <span className="inv-shimmer-dot block size-1.5 rotate-45 bg-inv-primary" />
          <span className="block h-px w-20 bg-gradient-to-l from-transparent to-inv-primary" />
        </div>
      )}
    </div>
  );
}

/** Large corner ornament used on hero-ish blocks. */
export function CornerOrnament({ className = "" }: { className?: string }) {
  const { theme } = useInvitationTheme();
  const k = theme.ornament;
  if (k === "line") return null;

  return (
    <div className={`pointer-events-none absolute opacity-45 ${className}`} aria-hidden="true">
      {k === "floral" && (
        <svg
          width="170"
          height="170"
          viewBox="0 0 170 170"
          fill="none"
          className="text-inv-secondary"
        >
          <path d="M4 166C4 96 42 34 118 8" stroke="currentColor" strokeWidth="1" />
          <path d="M40 120c-14 4-22 14-24 28 16 1 27-8 31-22" fill="currentColor" opacity=".45" />
          <path d="M66 82c-15 1-25 9-30 23 16 4 28-3 34-16" fill="currentColor" opacity=".35" />
          <path d="M96 48c-15-1-26 6-32 19 15 6 28 1 35-11" fill="currentColor" opacity=".3" />
          <circle cx="122" cy="20" r="9" stroke="currentColor" strokeWidth="1" />
          <circle cx="122" cy="20" r="3" fill="currentColor" />
        </svg>
      )}
      {k === "leaf" && (
        <svg width="180" height="180" viewBox="0 0 180 180" fill="none" className="text-inv-accent">
          <path d="M6 174C20 100 70 40 168 12" stroke="currentColor" strokeWidth="1.4" />
          <g fill="currentColor" opacity=".5">
            <ellipse cx="38" cy="130" rx="20" ry="8" transform="rotate(-40 38 130)" />
            <ellipse cx="70" cy="96" rx="22" ry="8" transform="rotate(-30 70 96)" />
            <ellipse cx="108" cy="62" rx="22" ry="8" transform="rotate(-22 108 62)" />
            <ellipse cx="146" cy="34" rx="18" ry="7" transform="rotate(-14 146 34)" />
          </g>
        </svg>
      )}
      {k === "shimmer" && (
        <div className="size-56 rounded-full bg-[radial-gradient(circle,var(--inv-primary)_0%,transparent_65%)] opacity-25 blur-2xl" />
      )}
    </div>
  );
}

function Blossom({ width, height }: { width: number; height: number }) {
  return (
    <svg width={width} height={height} viewBox="0 0 40 40" fill="none" aria-hidden="true">
      <g stroke="currentColor" strokeWidth="1">
        <ellipse cx="20" cy="10" rx="6" ry="9" />
        <ellipse cx="20" cy="30" rx="6" ry="9" />
        <ellipse cx="10" cy="20" rx="9" ry="6" />
        <ellipse cx="30" cy="20" rx="9" ry="6" />
      </g>
      <circle cx="20" cy="20" r="4" fill="currentColor" />
    </svg>
  );
}

const FLOWER_SPOTS = [
  { className: "left-3 top-12 text-inv-secondary/70", x: -60, delay: 0.15, size: 36 },
  { className: "right-4 top-28 text-inv-accent/70", x: 60, delay: 0.3, size: 26 },
  { className: "left-5 bottom-28 text-inv-accent/60", x: -60, delay: 0.45, size: 30 },
  { className: "right-3 bottom-14 text-inv-secondary/60", x: 60, delay: 0.6, size: 42 },
];

/** Blossoms that slide in from the left/right edges — used only on wedding/anniversary covers. */
export function WeddingFlowers() {
  return (
    <div className="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
      {FLOWER_SPOTS.map((spot, i) => (
        <motion.div
          key={i}
          className={`absolute ${spot.className}`}
          initial={{ opacity: 0, x: spot.x }}
          animate={{ opacity: 1, x: 0 }}
          transition={{ duration: 0.9, delay: spot.delay, ease: [0.22, 0.61, 0.36, 1] }}
        >
          <Blossom width={spot.size} height={spot.size} />
        </motion.div>
      ))}
    </div>
  );
}

function RoseBloom({ cx, cy, scale = 1 }: { cx: number; cy: number; scale?: number }) {
  return (
    <g transform={`translate(${cx} ${cy}) scale(${scale})`}>
      <g
        stroke="currentColor"
        strokeWidth={1 / scale}
        className="text-inv-secondary"
        fill="var(--inv-surface)"
      >
        <ellipse cx="0" cy="-10" rx="6" ry="9" />
        <ellipse cx="0" cy="10" rx="6" ry="9" />
        <ellipse cx="-10" cy="0" rx="9" ry="6" />
        <ellipse cx="10" cy="0" rx="9" ry="6" />
        <ellipse cx="-7" cy="-7" rx="7" ry="5" transform="rotate(45)" />
        <ellipse cx="7" cy="-7" rx="7" ry="5" transform="rotate(-45)" />
        <ellipse cx="-7" cy="7" rx="7" ry="5" transform="rotate(-45)" />
        <ellipse cx="7" cy="7" rx="7" ry="5" transform="rotate(45)" />
      </g>
      <circle r="3.5" className="text-inv-accent" fill="currentColor" />
    </g>
  );
}

const GARLAND_BLOOMS = [
  { cx: 55, cy: 58, scale: 1.2 },
  { cx: 140, cy: 32, scale: 1.6 },
  { cx: 235, cy: 46, scale: 1.35 },
  { cx: 330, cy: 32, scale: 1.6 },
  { cx: 415, cy: 58, scale: 1.2 },
];

/**
 * Full-width rose-and-leaf cluster for wedding covers using the "floral" ornament —
 * richer than CornerOrnament's single corner swirl. `flip` mirrors it vertically for
 * the bottom edge so both ends read as a matching pair, like a garland framing the hero.
 */
export function RoseGarland({ flip = false }: { flip?: boolean }) {
  return (
    <div
      className={`pointer-events-none absolute inset-x-0 overflow-hidden ${flip ? "bottom-0" : "top-0"}`}
      aria-hidden="true"
    >
      <svg
        viewBox="0 0 470 100"
        preserveAspectRatio="xMidYMin meet"
        className={`h-24 w-full sm:h-28 ${flip ? "-scale-y-100" : ""}`}
      >
        <g className="text-inv-accent" fill="currentColor" opacity=".5">
          <ellipse cx="30" cy="75" rx="26" ry="10" transform="rotate(-30 30 75)" />
          <ellipse cx="105" cy="65" rx="30" ry="11" transform="rotate(-18 105 65)" />
          <ellipse cx="195" cy="60" rx="26" ry="10" transform="rotate(-6 195 60)" />
          <ellipse cx="275" cy="60" rx="26" ry="10" transform="rotate(6 275 60)" />
          <ellipse cx="365" cy="65" rx="30" ry="11" transform="rotate(18 365 65)" />
          <ellipse cx="440" cy="75" rx="26" ry="10" transform="rotate(30 440 75)" />
        </g>
        <g
          className="text-inv-accent"
          stroke="currentColor"
          strokeWidth="1"
          opacity=".45"
          fill="none"
        >
          <path d="M20 90C90 45 150 25 235 27" />
          <path d="M450 90C380 45 320 25 235 27" />
        </g>
        {GARLAND_BLOOMS.map((b, i) => (
          <RoseBloom key={i} cx={b.cx} cy={b.cy} scale={b.scale} />
        ))}
      </svg>
    </div>
  );
}

export function Monogram({ initials }: { initials: string }) {
  const { theme } = useInvitationTheme();
  const sharp = theme.ornament === "line" || theme.ornament === "shimmer";
  return (
    <div
      className={`mx-auto grid size-20 place-items-center border border-inv-primary/40 text-inv-primary ${
        sharp ? "" : "rounded-full"
      }`}
      style={{ boxShadow: "var(--inv-shadow)" }}
    >
      <span className="whitespace-nowrap font-head text-lg tracking-[var(--inv-tracking)]">
        {initials}
      </span>
    </div>
  );
}
