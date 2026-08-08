import { Link } from "@tanstack/react-router";
import { Menu, X } from "lucide-react";
import { useEffect, useState } from "react";
import { Button } from "@/components/ui/button";

const NAV_LINKS = [
  { href: "#hero", label: "Home" },
  { href: "#template", label: "Template" },
  { href: "#fitur", label: "Fitur" },
  { href: "#harga", label: "Harga" },
  { href: "#cara-kerja", label: "Cara Kerja" },
  { href: "#testimoni", label: "Testimoni" },
  { href: "#faq", label: "FAQ" },
  { href: "#blog", label: "Blog" },
  { href: "#footer", label: "Kontak" },
];

export function LandingNavbar() {
  const [scrolled, setScrolled] = useState(false);
  const [mobileOpen, setMobileOpen] = useState(false);

  useEffect(() => {
    function onScroll() {
      setScrolled(window.scrollY > 8);
    }
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });
    return () => window.removeEventListener("scroll", onScroll);
  }, []);

  return (
    <header
      className={`fixed inset-x-0 top-0 z-50 transition-all duration-300 ${
        scrolled ? "bg-background/80 shadow-sm backdrop-blur-md" : "bg-transparent"
      }`}
    >
      <div className="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6">
        <a href="#hero" className="font-landing-display text-lg font-bold tracking-tight">
          Undang<span className="text-brand">Akoe</span>
        </a>

        <nav className="hidden items-center gap-6 lg:flex">
          {NAV_LINKS.map((link) => (
            <a
              key={link.href}
              href={link.href}
              className="font-landing-sans text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
              {link.label}
            </a>
          ))}
        </nav>

        <div className="hidden items-center gap-2 lg:flex">
          <Link to="/dashboard/login">
            <Button variant="ghost" size="sm" className="font-landing-sans">
              Masuk
            </Button>
          </Link>
          <Link to="/dashboard/register">
            <Button
              size="sm"
              className="font-landing-sans bg-brand text-brand-foreground hover:bg-brand/90"
            >
              Daftar Gratis
            </Button>
          </Link>
        </div>

        <button
          className="grid size-9 place-items-center rounded-md lg:hidden"
          onClick={() => setMobileOpen((o) => !o)}
          aria-label={mobileOpen ? "Tutup menu" : "Buka menu"}
        >
          {mobileOpen ? <X className="size-5" /> : <Menu className="size-5" />}
        </button>
      </div>

      {mobileOpen && (
        <nav className="border-t bg-background px-4 py-4 lg:hidden">
          <div className="flex flex-col gap-3">
            {NAV_LINKS.map((link) => (
              <a
                key={link.href}
                href={link.href}
                className="font-landing-sans text-sm text-muted-foreground"
                onClick={() => setMobileOpen(false)}
              >
                {link.label}
              </a>
            ))}
            <div className="mt-2 flex flex-col gap-2 border-t pt-3">
              <Link to="/dashboard/login" onClick={() => setMobileOpen(false)}>
                <Button variant="outline" size="sm" className="w-full font-landing-sans">
                  Masuk
                </Button>
              </Link>
              <Link to="/dashboard/register" onClick={() => setMobileOpen(false)}>
                <Button
                  size="sm"
                  className="w-full font-landing-sans bg-brand text-brand-foreground hover:bg-brand/90"
                >
                  Daftar Gratis
                </Button>
              </Link>
            </div>
          </div>
        </nav>
      )}
    </header>
  );
}
