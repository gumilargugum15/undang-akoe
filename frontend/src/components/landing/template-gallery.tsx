import { Link } from "@tanstack/react-router";
import { useEffect, useState } from "react";
import { motion } from "motion/react";
import { Eye } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
} from "@/components/ui/dialog";
import { api } from "@/lib/api";

interface PublicTheme {
  uuid: string;
  name: string;
  slug: string;
  description: string | null;
  category: { name: string; slug: string } | null;
  thumbnail: string | null;
  type: "free" | "premium";
  price: number;
  config: { tokens: Record<string, string>; fonts: { head: string } };
}

export function LandingTemplateGallery() {
  const [themes, setThemes] = useState<PublicTheme[]>([]);
  const [activeCategory, setActiveCategory] = useState("semua");
  const [previewTheme, setPreviewTheme] = useState<PublicTheme | null>(null);

  useEffect(() => {
    api
      .get<{ data: PublicTheme[] }>("/public/themes")
      .then((res) => setThemes(res.data))
      .catch(() => setThemes([]));
  }, []);

  const categories = [
    "semua",
    ...Array.from(new Set(themes.map((t) => t.category?.slug).filter((c): c is string => !!c))),
  ];

  const filtered =
    activeCategory === "semua" ? themes : themes.filter((t) => t.category?.slug === activeCategory);

  return (
    <section id="template" className="py-20 sm:py-28">
      <div className="mx-auto max-w-6xl px-4 sm:px-6">
        <div className="mx-auto max-w-xl text-center">
          <p className="font-landing-sans text-sm font-medium text-brand">Template</p>
          <h2 className="mt-2 font-landing-display text-3xl font-bold tracking-tight sm:text-4xl">
            Koleksi Tema Siap Pakai
          </h2>
          <p className="mt-3 font-landing-sans text-muted-foreground">
            Setiap tema sudah dirancang lengkap — tinggal isi data acara Anda.
          </p>
        </div>

        {categories.length > 1 && (
          <div className="mt-8 flex flex-wrap justify-center gap-2">
            {categories.map((cat) => (
              <button
                key={cat}
                onClick={() => setActiveCategory(cat)}
                className={`rounded-full px-4 py-1.5 font-landing-sans text-sm capitalize transition-colors ${
                  activeCategory === cat
                    ? "bg-brand text-brand-foreground"
                    : "bg-muted text-muted-foreground hover:bg-muted/70"
                }`}
              >
                {cat}
              </button>
            ))}
          </div>
        )}

        <div className="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
          {filtered.map((theme, i) => (
            <motion.div
              key={theme.uuid}
              initial={{ opacity: 0, y: 16 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true, margin: "-40px" }}
              transition={{ duration: 0.4, delay: (i % 4) * 0.06 }}
              className="group overflow-hidden rounded-xl border bg-card shadow-sm transition-shadow hover:shadow-md"
            >
              <div className="relative aspect-[4/5] overflow-hidden bg-muted">
                {theme.thumbnail ? (
                  <img
                    src={theme.thumbnail}
                    alt={theme.name}
                    loading="lazy"
                    className="size-full object-cover transition-transform duration-500 group-hover:scale-105"
                  />
                ) : (
                  <div
                    className="flex size-full items-center justify-center px-4 text-center font-landing-display text-lg"
                    style={{
                      backgroundColor: theme.config?.tokens?.bg ?? "var(--muted)",
                      color: theme.config?.tokens?.text ?? "var(--foreground)",
                    }}
                  >
                    {theme.name}
                  </div>
                )}
                <Badge className="absolute left-2 top-2" variant={theme.type === "premium" ? "default" : "secondary"}>
                  {theme.type === "premium" ? "Premium" : "Free"}
                </Badge>
              </div>
              <div className="p-4">
                <p className="font-landing-display text-sm font-semibold">{theme.name}</p>
                <p className="mt-0.5 font-landing-sans text-xs capitalize text-muted-foreground">
                  {theme.category?.name ?? "Umum"}
                </p>
                <div className="mt-3 flex gap-2">
                  <Button variant="outline" size="sm" className="flex-1 gap-1" onClick={() => setPreviewTheme(theme)}>
                    <Eye className="size-3.5" />
                    Preview
                  </Button>
                  <Link to="/dashboard/register" className="flex-1">
                    <Button size="sm" className="w-full bg-brand text-brand-foreground hover:bg-brand/90">
                      Gunakan
                    </Button>
                  </Link>
                </div>
              </div>
            </motion.div>
          ))}
          {filtered.length === 0 && (
            <p className="col-span-4 text-center font-landing-sans text-muted-foreground">Memuat tema...</p>
          )}
        </div>
      </div>

      <Dialog open={!!previewTheme} onOpenChange={(open) => !open && setPreviewTheme(null)}>
        <DialogContent>
          {previewTheme && (
            <>
              <DialogHeader>
                <DialogTitle className="font-landing-display">{previewTheme.name}</DialogTitle>
                <DialogDescription className="font-landing-sans">
                  {previewTheme.description ?? "Tema siap pakai untuk undangan digital Anda."}
                </DialogDescription>
              </DialogHeader>
              {previewTheme.thumbnail && (
                <img
                  src={previewTheme.thumbnail}
                  alt={previewTheme.name}
                  className="w-full rounded-lg object-cover"
                />
              )}
              <div className="flex items-center gap-2">
                {Object.values(previewTheme.config?.tokens ?? {})
                  .slice(0, 6)
                  .map((color, i) => (
                    <span
                      key={i}
                      className="size-7 rounded-full border"
                      style={{ backgroundColor: color }}
                      title={color}
                    />
                  ))}
              </div>
              <Link to="/dashboard/register">
                <Button className="w-full bg-brand text-brand-foreground hover:bg-brand/90">
                  Gunakan Tema Ini
                </Button>
              </Link>
            </>
          )}
        </DialogContent>
      </Dialog>
    </section>
  );
}
