import { motion, type TargetAndTransition, type Transition } from "motion/react";
import { Mail } from "lucide-react";
import { useInvitationData } from "./invitation-data-provider";
import { CornerOrnament, Divider, RoseGarland, WeddingFlowers } from "./ornaments";
import { useInvitationTheme } from "./theme-provider";
import type { RevealKind } from "@/lib/themes";
import galleryHero from "@/assets/gallery-4.jpg";

// How the cover exits when a guest taps "Buka Undangan" — keyed to the same per-theme
// `reveal` used for in-page scroll reveals (see reveal.tsx), so the moment that actually
// matters most (opening the invitation) also varies by theme instead of always the same
// blur+scale regardless of which theme is active.
const EXIT_VARIANTS: Record<RevealKind, TargetAndTransition> = {
  fade: { opacity: 0, y: -24 },
  slide: { opacity: 0, x: 90 },
  zoom: { opacity: 0, scale: 1.1, rotate: 1.5 },
  blur: { opacity: 0, scale: 1.04, filter: "blur(10px)" },
  flip: { opacity: 0, rotateX: 55, y: -16 },
  // Transform-only, matching reveal.tsx's curtain — see that file for why clip-path is
  // avoided here (a real rendering bug, not just a style preference).
  curtain: { opacity: 0, x: -110, skewX: 8 },
  bounce: { opacity: 0, scale: 0.86, y: 36 },
};

const EXIT_TRANSITION: Partial<Record<RevealKind, Transition>> = {
  curtain: { duration: 0.6, ease: [0.65, 0, 0.35, 1] },
  flip: { duration: 0.6 },
  bounce: { duration: 0.5, ease: "easeIn" },
};

const COVER_EYEBROW: Record<string, string> = {
  wedding: "The Wedding Of",
  anniversary: "Hari Jadi Pernikahan",
  birthday: "Selamat Ulang Tahun",
  khitan: "Selamat Merayakan Khitanan",
  aqiqah: "Selamat Menyambut Kelahiran",
  graduation: "Selamat Atas Kelulusan",
  corporate: "Mengundang Anda Ke Acara",
};

const COUPLE_CATEGORIES = new Set(["wedding", "anniversary"]);

export function Cover({ onOpen, guest }: { onOpen: () => void; guest: string }) {
  const { theme } = useInvitationTheme();
  const invitation = useInvitationData();
  const eyebrow = COVER_EYEBROW[invitation.eventCategory] ?? COVER_EYEBROW.wedding;
  // Wedding/anniversary show the couple's names — every other category (birthday, khitan,
  // aqiqah, graduation, corporate, ...) has no bride/groom pair, so it falls back to
  // whichever honoree name(s) the invitation actually has instead of a bare "&".
  const heroName = COUPLE_CATEGORIES.has(invitation.eventCategory)
    ? null
    : (invitation.honorees ?? []).map((h) => h.nickname).join(" & ");
  // The stock photo is a wedding couple — only fitting for the categories that are
  // actually about a couple. Every other category relies on its own theme's texture/token
  // background instead (each one is already designed per category, e.g. birthday's confetti
  // gradient, khitan's emerald/gold glow, corporate's flat navy) rather than a mismatched photo.
  const showCouplePhoto = COUPLE_CATEGORIES.has(invitation.eventCategory);
  // A customer-uploaded cover photo (any event category) takes priority over the built-in
  // wedding stock photo — shown near-full-strength rather than the stock photo's faint wash,
  // fading into the theme's solid background lower down so the detail card stays readable.
  const customCoverPhoto = invitation.coverPhoto;

  return (
    <motion.section
      className={
        customCoverPhoto
          ? "relative flex min-h-[100dvh] flex-col items-center overflow-hidden text-center"
          : "relative flex min-h-[100dvh] flex-col items-center justify-center overflow-hidden px-6 py-16 text-center"
      }
      style={{ backgroundColor: "var(--inv-bg)", backgroundImage: "var(--inv-texture)" }}
      exit={EXIT_VARIANTS[theme.reveal]}
      transition={EXIT_TRANSITION[theme.reveal] ?? { duration: 0.7 }}
    >
      {customCoverPhoto ? (
        // A contained block at the top, not a full-bleed background behind the text — a
        // customer's own photo can carry its own graphics/text (party banners, quote cards,
        // ...) that would otherwise collide with the name rendered on top of it. Keeping its
        // bottom edge above the content guarantees they never overlap, whatever the photo is.
        <div className="relative h-[38vh] w-full shrink-0 overflow-hidden sm:h-[44vh]">
          <img
            src={customCoverPhoto}
            alt="Foto sampul undangan"
            className="absolute inset-0 size-full object-cover object-[50%_30%]"
          />
          <div
            className="absolute inset-x-0 bottom-0 h-24"
            style={{ background: "linear-gradient(180deg, transparent, var(--inv-bg))" }}
          />
        </div>
      ) : (
        showCouplePhoto && (
          <>
            <img
              src={galleryHero}
              alt="Pasangan pengantin berpelukan di bawah gerbang bunga saat matahari terbenam"
              width={900}
              height={1200}
              className="absolute inset-0 size-full object-cover opacity-20"
            />
            <div
              className="absolute inset-0"
              style={{
                background:
                  theme.id === "luxury"
                    ? "linear-gradient(180deg, rgba(8,11,19,.85), rgba(8,11,19,.95))"
                    : "linear-gradient(180deg, color-mix(in srgb, var(--inv-bg) 82%, transparent), color-mix(in srgb, var(--inv-bg) 94%, transparent))",
              }}
            />
          </>
        )
      )}
      {theme.ornament === "floral" ? (
        <>
          <RoseGarland />
          <RoseGarland flip />
        </>
      ) : (
        <>
          {showCouplePhoto && <WeddingFlowers />}
          <CornerOrnament className="left-0 top-0" />
          <CornerOrnament className="bottom-0 right-0 rotate-180" />
        </>
      )}

      <div
        className={
          customCoverPhoto
            ? "relative flex flex-1 flex-col items-center justify-center gap-6 px-6 py-10"
            : "relative flex flex-col items-center gap-6"
        }
      >
        <motion.p
          className="inv-eyebrow"
          initial={{ opacity: 0, y: 12 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.15 }}
        >
          {eyebrow}
        </motion.p>

        <motion.h1
          className="inv-heading inv-glow text-5xl leading-[1.05] sm:text-7xl"
          initial={{ opacity: 0, y: 18 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.3, duration: 0.8 }}
        >
          {heroName !== null ? (
            heroName
          ) : (
            <>
              {invitation.brideShort}
              <span className="mx-3 text-inv-secondary">&</span>
              {invitation.groomShort}
            </>
          )}
        </motion.h1>

        <Divider />

        <motion.p
          className="font-body text-base text-inv-muted"
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          transition={{ delay: 0.5 }}
        >
          {invitation.dateLabel}
        </motion.p>

        <motion.div
          className="mt-6 flex flex-col items-center gap-3"
          initial={{ opacity: 0, y: 16 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.65 }}
        >
          <p className="font-body text-xs uppercase tracking-[0.25em] text-inv-muted">
            Kepada Yth.
          </p>
          <p className="font-head text-xl text-inv-text">{guest}</p>
          <button className="inv-btn mt-3" onClick={onOpen}>
            <Mail className="size-4" />
            Buka Undangan
          </button>
        </motion.div>
      </div>
    </motion.section>
  );
}
