import { CalendarHeart, CheckSquare, Palette, Sparkles } from "lucide-react";

// Sample content — placeholder until a real Blog/CMS backend is built (planned for a later phase).
const ARTICLES = [
  {
    icon: CalendarHeart,
    category: "Tips Pernikahan",
    title: "10 Hal yang Sering Terlupa Saat Menyiapkan Pernikahan",
    excerpt: "Dari susunan acara hingga daftar tamu, ini checklist yang wajib dicek sebelum hari-H.",
  },
  {
    icon: Sparkles,
    category: "Inspirasi Undangan",
    title: "Inspirasi Kalimat Undangan yang Hangat dan Berkesan",
    excerpt: "Kumpulan contoh kata pembuka dan penutup undangan yang bisa langsung Anda pakai.",
  },
  {
    icon: Palette,
    category: "Dekorasi",
    title: "Memilih Palet Warna Dekorasi Sesuai Tema Undangan",
    excerpt: "Padukan warna dekorasi venue dengan tema undangan digital agar terasa konsisten.",
  },
  {
    icon: CheckSquare,
    category: "Checklist Acara",
    title: "Checklist Persiapan Acara: H-3 Bulan sampai H-1 Hari",
    excerpt: "Panduan lengkap timeline persiapan supaya tidak ada yang terlewat menjelang acara.",
  },
];

export function LandingBlog() {
  return (
    <section id="blog" className="py-20 sm:py-28">
      <div className="mx-auto max-w-6xl px-4 sm:px-6">
        <div className="mx-auto max-w-xl text-center">
          <p className="font-landing-sans text-sm font-medium text-brand">Blog</p>
          <h2 className="mt-2 font-landing-display text-3xl font-bold tracking-tight sm:text-4xl">
            Tips &amp; Inspirasi Seputar Acara Anda
          </h2>
        </div>

        <div className="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
          {ARTICLES.map((article) => (
            <article key={article.title} className="rounded-xl border bg-card p-5 shadow-sm">
              <div className="grid size-10 place-items-center rounded-lg bg-brand/10 text-brand">
                <article.icon className="size-5" />
              </div>
              <p className="mt-4 font-landing-sans text-xs font-semibold uppercase tracking-wide text-brand">
                {article.category}
              </p>
              <h3 className="mt-1.5 font-landing-display text-base font-semibold leading-snug">
                {article.title}
              </h3>
              <p className="mt-2 font-landing-sans text-sm leading-relaxed text-muted-foreground">
                {article.excerpt}
              </p>
            </article>
          ))}
        </div>
      </div>
    </section>
  );
}
