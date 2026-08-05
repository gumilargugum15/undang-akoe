import { createFileRoute, Link } from "@tanstack/react-router";
import { ArrowLeft } from "lucide-react";
import { InvitationThemeProvider, useInvitationTheme } from "@/components/invitation/theme-provider";
import { ThemePreviewCard } from "@/components/invitation/theme-switcher";
import { themeList } from "@/lib/themes";
import { Divider } from "@/components/invitation/ornaments";

const title = "Pilih Tema Undangan — Alya & Raka";
const description =
  "Pratinjau dan pilih tema visual undangan digital: Elegant Classic, Modern Minimalist, Rustic Garden, atau Dark Luxury.";

export const Route = createFileRoute("/tema")({
  head: () => ({
    meta: [
      { title },
      { name: "description", content: description },
      { property: "og:title", content: title },
      { property: "og:description", content: description },
      { property: "og:type", content: "website" },
      { name: "twitter:card", content: "summary_large_image" },
    ],
  }),
  component: () => (
    <InvitationThemeProvider>
      <ThemePage />
    </InvitationThemeProvider>
  ),
});

function ThemePage() {
  const { setThemeId, theme } = useInvitationTheme();

  return (
    <main
      className="min-h-screen px-6 py-16 font-body text-inv-text"
      style={{ backgroundColor: "var(--inv-bg)", backgroundImage: "var(--inv-texture)" }}
    >
      <div className="mx-auto w-full max-w-3xl">
        <Link
          to="/"
          className="inline-flex items-center gap-2 font-body text-xs uppercase tracking-[0.2em] text-inv-muted hover:text-inv-primary"
        >
          <ArrowLeft className="size-4" /> Kembali ke undangan
        </Link>

        <header className="mt-8 text-center">
          <p className="inv-eyebrow">Personalisasi</p>
          <h1 className="inv-heading mt-2 text-4xl">Pilih Tema</h1>
          <Divider className="mt-4" />
          <p className="mx-auto mt-4 max-w-lg text-sm text-inv-muted">
            Setiap tema mengubah palet warna, pasangan font, ornamen dekoratif, gaya kartu &amp;
            tombol, serta animasi saat scroll. Tema aktif: <strong>{theme.name}</strong>.
          </p>
        </header>

        <div className="mt-10 grid gap-5 sm:grid-cols-2">
          {themeList.map((t) => (
            <ThemePreviewCard key={t.id} id={t.id} onSelect={setThemeId} />
          ))}
        </div>

        <div className="mt-10 text-center">
          <Link to="/" className="inv-btn">
            Lihat Undangan
          </Link>
        </div>
      </div>
    </main>
  );
}
