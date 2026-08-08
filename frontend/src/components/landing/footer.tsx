import { Facebook, Instagram, Mail } from "lucide-react";

const FOOTER_NAV = [
  { href: "#hero", label: "Home" },
  { href: "#template", label: "Template" },
  { href: "#fitur", label: "Fitur" },
  { href: "#harga", label: "Harga" },
  { href: "#faq", label: "FAQ" },
];

export function LandingFooter() {
  const year = new Date().getFullYear();

  return (
    <footer id="footer" className="border-t bg-background py-14">
      <div className="mx-auto max-w-6xl px-4 sm:px-6">
        <div className="grid gap-10 sm:grid-cols-3">
          <div>
            <p className="font-landing-display text-lg font-bold tracking-tight">
              Undang<span className="text-brand">Akoe</span>
            </p>
            <p className="mt-3 max-w-xs font-landing-sans text-sm leading-relaxed text-muted-foreground">
              Platform pembuatan undangan digital untuk pernikahan dan berbagai acara — mudah
              dibuat, indah dilihat, praktis dibagikan.
            </p>
            <div className="mt-4 flex gap-3 text-muted-foreground">
              <span
                aria-label="Instagram (segera hadir)"
                title="Segera hadir"
                className="cursor-default opacity-50"
              >
                <Instagram className="size-5" />
              </span>
              <span
                aria-label="Facebook (segera hadir)"
                title="Segera hadir"
                className="cursor-default opacity-50"
              >
                <Facebook className="size-5" />
              </span>
              <a
                href="mailto:halo@undangakoe.test"
                aria-label="Email"
                className="hover:text-foreground"
              >
                <Mail className="size-5" />
              </a>
            </div>
          </div>

          <div>
            <p className="font-landing-sans text-sm font-semibold">Navigasi</p>
            <ul className="mt-3 space-y-2">
              {FOOTER_NAV.map((link) => (
                <li key={link.href}>
                  <a
                    href={link.href}
                    className="font-landing-sans text-sm text-muted-foreground hover:text-foreground"
                  >
                    {link.label}
                  </a>
                </li>
              ))}
            </ul>
          </div>

          <div>
            <p className="font-landing-sans text-sm font-semibold">Kontak</p>
            <ul className="mt-3 space-y-2 font-landing-sans text-sm text-muted-foreground">
              <li>undangakoe@gmail.com</li>
              <li>Senin – Minggu, 08.00 – 17.00 WIB</li>
            </ul>
          </div>
        </div>

        <div className="mt-10 flex flex-col items-center justify-between gap-4 border-t pt-6 font-landing-sans text-xs text-muted-foreground sm:flex-row">
          <p>© {year} Undang Akoe. Seluruh hak cipta dilindungi.</p>
          <div className="flex gap-4">
            <span title="Segera hadir" className="cursor-default opacity-60">
              Kebijakan Privasi
            </span>
            <span title="Segera hadir" className="cursor-default opacity-60">
              Syarat &amp; Ketentuan
            </span>
          </div>
        </div>
      </div>
    </footer>
  );
}
