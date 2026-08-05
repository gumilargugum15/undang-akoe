import { useState } from "react";
import { AnimatePresence, motion } from "motion/react";
import { Check, Palette, X } from "lucide-react";
import { themeList } from "@/lib/themes";
import { useInvitationTheme } from "./theme-provider";

export function ThemePreviewCard({
  id,
  compact = false,
  onSelect,
}: {
  id: string;
  compact?: boolean;
  onSelect: (id: string) => void;
}) {
  const { themeId } = useInvitationTheme();
  const t = themeList.find((x) => x.id === id)!;
  const active = themeId === id;

  return (
    <button
      onClick={() => onSelect(id)}
      className="group w-full overflow-hidden rounded-xl border text-left transition-transform hover:-translate-y-1"
      style={{
        borderColor: active ? t.tokens.primary : t.tokens.border,
        backgroundColor: t.tokens.surface,
        boxShadow: active ? `0 0 0 2px ${t.tokens.primary}` : "none",
      }}
    >
      <div
        className="relative flex h-24 flex-col items-center justify-center gap-2"
        style={{ backgroundColor: t.tokens.bg, backgroundImage: t.texture }}
      >
        <span
          style={{
            fontFamily: t.fonts.head,
            color: t.tokens.text,
            letterSpacing: t.letterSpacing,
          }}
          className="text-lg"
        >
          A &amp; R
        </span>
        <span className="h-px w-10" style={{ backgroundColor: t.tokens.secondary }} />
        <span
          className="rounded px-3 py-1 text-[9px] uppercase tracking-widest"
          style={{
            backgroundColor: t.tokens.primary,
            color: t.tokens.primaryFg,
            borderRadius: t.radius,
          }}
        >
          Buka
        </span>
        {active && (
          <span
            className="absolute right-2 top-2 grid size-5 place-items-center rounded-full"
            style={{ backgroundColor: t.tokens.primary, color: t.tokens.primaryFg }}
          >
            <Check className="size-3" />
          </span>
        )}
      </div>
      <div className="space-y-1 px-3 py-3">
        <p className="text-sm font-semibold" style={{ color: t.tokens.text, fontFamily: t.fonts.body }}>
          {t.name}
        </p>
        {!compact && (
          <p className="text-xs leading-snug" style={{ color: t.tokens.muted, fontFamily: t.fonts.body }}>
            {t.tagline}
          </p>
        )}
        <div className="flex gap-1 pt-1">
          {t.swatch.map((c) => (
            <span key={c} className="size-3 rounded-full" style={{ backgroundColor: c }} />
          ))}
        </div>
      </div>
    </button>
  );
}

export function ThemeSwitcher() {
  const { setThemeId } = useInvitationTheme();
  const [open, setOpen] = useState(false);

  return (
    <>
      <button
        onClick={() => setOpen(true)}
        aria-label="Pilih tema undangan"
        className="fixed bottom-5 right-5 z-50 grid size-12 place-items-center rounded-full border border-inv-border bg-inv-surface text-inv-primary transition-transform hover:scale-105"
        style={{ boxShadow: "var(--inv-shadow)" }}
      >
        <Palette className="size-5" />
      </button>

      <AnimatePresence>
        {open && (
          <motion.div
            className="fixed inset-0 z-50 flex items-end justify-center bg-black/50 p-0 sm:items-center sm:p-6"
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            onClick={() => setOpen(false)}
          >
            <motion.div
              className="max-h-[85dvh] w-full max-w-lg overflow-y-auto rounded-t-3xl border border-inv-border bg-inv-bg p-6 sm:rounded-2xl"
              initial={{ y: 60, opacity: 0 }}
              animate={{ y: 0, opacity: 1 }}
              exit={{ y: 60, opacity: 0 }}
              onClick={(e) => e.stopPropagation()}
            >
              <div className="mb-5 flex items-start justify-between gap-4">
                <div className="min-w-0">
                  <h2 className="inv-heading text-xl">Pilih Tema</h2>
                  <p className="font-body text-sm text-inv-muted">
                    Seluruh warna, font, ornamen & animasi ikut berubah.
                  </p>
                </div>
                <button
                  onClick={() => setOpen(false)}
                  aria-label="Tutup"
                  className="shrink-0 text-inv-muted hover:text-inv-text"
                >
                  <X className="size-5" />
                </button>
              </div>
              <div className="grid grid-cols-2 gap-3">
                {themeList.map((t) => (
                  <ThemePreviewCard key={t.id} id={t.id} onSelect={setThemeId} />
                ))}
              </div>
            </motion.div>
          </motion.div>
        )}
      </AnimatePresence>
    </>
  );
}
