import { motion } from "motion/react";
import { useInvitationTheme } from "./theme-provider";

/** Decorative divider that changes shape per theme. */
export function Divider({ className = "" }: { className?: string }) {
  const { theme } = useInvitationTheme();
  const k = theme.ornament;

  return (
    <div className={`flex justify-center ${className}`} aria-hidden="true">
      {(k === "floral" || k === "bouquet") && (
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
      {k === "crescent" && (
        <svg
          width="220"
          height="34"
          viewBox="0 0 220 34"
          fill="none"
          className="text-inv-secondary"
        >
          <path d="M8 17h68" stroke="currentColor" strokeWidth="1" />
          <path d="M150 17h62" stroke="currentColor" strokeWidth="1" />
          <path
            d="M104 6a11 11 0 1 0 0 22 9 9 0 0 1 0-22Z"
            fill="currentColor"
            className="text-inv-accent"
          />
          <path
            d="M126 11.5l1.4 3.9 3.9 1.4-3.9 1.4-1.4 3.9-1.4-3.9-3.9-1.4 3.9-1.4Z"
            fill="currentColor"
          />
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

  // The bouquet cluster reads as a filled floral illustration, not a faint line
  // sketch — it needs to stay more vivid than the other kinds' 45%, but full
  // opacity fights the text it sits behind (e.g. FooterSection's closing line).
  const wrapperOpacity = k === "bouquet" ? "opacity-70" : "opacity-45";

  return (
    <div
      className={`pointer-events-none absolute ${wrapperOpacity} ${className}`}
      aria-hidden="true"
    >
      {k === "bouquet" && (
        <svg width="200" height="200" viewBox="0 0 200 200" fill="none">
          <g
            stroke="currentColor"
            strokeWidth="1"
            opacity=".5"
            className="text-inv-accent"
            fill="none"
          >
            <path d="M50 105C82 75 115 48 192 22" />
            <path d="M65 122C92 95 122 70 178 50" />
          </g>
          <g className="text-inv-accent" fill="currentColor" opacity=".55">
            <ellipse cx="62" cy="100" rx="17" ry="7" transform="rotate(-38 62 100)" />
            <ellipse cx="88" cy="80" rx="19" ry="7" transform="rotate(-30 88 80)" />
            <ellipse cx="118" cy="58" rx="18" ry="7" transform="rotate(-16 118 58)" />
            <ellipse cx="152" cy="70" rx="15" ry="6" transform="rotate(24 152 70)" />
            <ellipse cx="178" cy="40" rx="14" ry="6" transform="rotate(10 178 40)" />
          </g>
          {/* Layered blooms, largest/darkest nearest the corner, smaller and lighter
              trailing outward — reads as a gathered bouquet rather than one flat rosette. */}
          <RoseBloom cx={82} cy={44} scale={1.15} rotate={12} tone="surface" />
          <RoseBloom cx={118} cy={70} scale={1.35} rotate={-8} tone="secondary" />
          <RoseBloom cx={155} cy={50} scale={1.55} rotate={18} tone="secondary" />
          <RoseBloom cx={178} cy={78} scale={1.1} rotate={-15} tone="accent" />
          <RoseBloom cx={186} cy={20} scale={1.05} rotate={30} tone="accent" />
          <RoseBloom cx={152} cy={30} scale={2.3} rotate={-10} tone="primary" />
        </svg>
      )}
      {k === "crescent" && (
        // Same diagonal sweep (bottom-left mass tapering to a top-right accent) as
        // the "floral" case below, so it lines up correctly at every existing
        // CornerOrnament call site without needing per-site placement fixes.
        <svg
          width="170"
          height="170"
          viewBox="0 0 170 170"
          fill="none"
          className="text-inv-secondary"
        >
          <path d="M4 166C4 96 42 34 118 8" stroke="currentColor" strokeWidth="1" />
          <path d="M62 134a16 16 0 1 0 0-32 13 13 0 0 1 0 32Z" fill="currentColor" opacity=".4" />
          <path
            d="M78 84l1.6 4.4 4.4 1.6-4.4 1.6L78 96l-1.6-4.4L72 90l4.4-1.6Z"
            fill="currentColor"
            opacity=".35"
          />
          <path
            d="M100 54l1.3 3.6 3.7 1.4-3.7 1.4L100 64l-1.3-3.6L95 59l3.7-1.4Z"
            fill="currentColor"
            opacity=".3"
          />
          <circle cx="122" cy="20" r="9" stroke="currentColor" strokeWidth="1" />
          <path
            d="M122 14l1.4 4.6 4.6 1.4-4.6 1.4-1.4 4.6-1.4-4.6-4.6-1.4 4.6-1.4Z"
            fill="currentColor"
          />
        </svg>
      )}
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

function RoseBloom({
  cx,
  cy,
  scale = 1,
  rotate = 0,
  tone = "surface",
}: {
  cx: number;
  cy: number;
  scale?: number;
  rotate?: number;
  /** "surface" keeps RoseGarland's original pale outlined look; the other tones
   * fill solid with a theme color, for a denser illustration like CornerOrnament's
   * "bouquet" cluster. */
  tone?: "surface" | "primary" | "secondary" | "accent";
}) {
  const toneClass =
    tone === "primary"
      ? "text-inv-primary"
      : tone === "accent"
        ? "text-inv-accent"
        : "text-inv-secondary";
  return (
    <g transform={`translate(${cx} ${cy}) rotate(${rotate}) scale(${scale})`}>
      <g
        stroke="currentColor"
        strokeWidth={1 / scale}
        className={toneClass}
        fill={tone === "surface" ? "var(--inv-surface)" : "currentColor"}
        fillOpacity={tone === "surface" ? 1 : 0.85}
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
  // A mihrab-like arch (rounded top, flat bottom) instead of a plain circle —
  // reads as unmistakably Islamic rather than a generic wedding monogram frame.
  const arch = theme.ornament === "crescent";
  return (
    <div
      className={`mx-auto grid size-20 place-items-center border border-inv-primary/40 text-inv-primary ${
        arch ? "rounded-t-full" : sharp ? "" : "rounded-full"
      }`}
      style={{ boxShadow: "var(--inv-shadow)" }}
    >
      <span className="whitespace-nowrap font-head text-lg tracking-[var(--inv-tracking)]">
        {initials}
      </span>
    </div>
  );
}

/**
 * Large pointed-arch outline behind the hero content — the mihrab silhouette that
 * recurs through the Islamic reference designs (docs/UI/khitanan_islamic). Purely
 * decorative background, sized loosely so it reads as a watermark, not a frame.
 */
export function ArchWatermark({ className = "" }: { className?: string }) {
  return (
    <div
      className={`pointer-events-none absolute inset-x-0 bottom-0 flex justify-center overflow-hidden ${className}`}
      aria-hidden="true"
    >
      <div
        className="h-40 w-56 rounded-t-full border-2 opacity-25 sm:h-52 sm:w-72"
        style={{ borderColor: "var(--inv-accent)" }}
      />
    </div>
  );
}
