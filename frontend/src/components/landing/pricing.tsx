import { Link } from "@tanstack/react-router";
import { useEffect, useState } from "react";
import { motion } from "motion/react";
import { Check } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { api } from "@/lib/api";

interface PublicPackage {
  id: number;
  name: string;
  description: string | null;
  price: number;
  duration_days: number | null;
  max_photos: number | null;
  max_guests: number | null;
  features: string[];
}

function formatRupiah(value: number): string {
  return value === 0 ? "Gratis" : `Rp${value.toLocaleString("id-ID")}`;
}

export function LandingPricing() {
  const [packages, setPackages] = useState<PublicPackage[]>([]);

  useEffect(() => {
    api
      .get<{ data: PublicPackage[] }>("/public/packages")
      .then((res) => setPackages(res.data))
      .catch(() => setPackages([]));
  }, []);

  const mostPopularIndex = packages.length > 1 ? 1 : 0;

  return (
    <section id="harga" className="bg-muted/30 py-20 sm:py-28">
      <div className="mx-auto max-w-6xl px-4 sm:px-6">
        <div className="mx-auto max-w-xl text-center">
          <p className="font-landing-sans text-sm font-medium text-brand">Paket Harga</p>
          <h2 className="mt-2 font-landing-display text-3xl font-bold tracking-tight sm:text-4xl">
            Pilih Paket Sesuai Kebutuhan
          </h2>
        </div>

        <div className="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          {packages.map((pkg, i) => {
            const isPopular = i === mostPopularIndex && packages.length > 1;
            return (
              <motion.div
                key={pkg.id}
                initial={{ opacity: 0, y: 16 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true, margin: "-40px" }}
                transition={{ duration: 0.4, delay: i * 0.08 }}
                className={`relative rounded-2xl border bg-card p-6 ${
                  isPopular ? "border-brand shadow-lg" : "shadow-sm"
                }`}
              >
                {isPopular && (
                  <Badge className="absolute -top-3 left-1/2 -translate-x-1/2 bg-brand text-brand-foreground">
                    Paling Populer
                  </Badge>
                )}
                <h3 className="font-landing-display text-lg font-semibold">{pkg.name}</h3>
                <p className="mt-1 min-h-10 font-landing-sans text-sm text-muted-foreground">{pkg.description}</p>
                <p className="mt-4 font-landing-display text-3xl font-bold">
                  {formatRupiah(pkg.price)}
                  {pkg.price > 0 && (
                    <span className="font-landing-sans text-sm font-normal text-muted-foreground">
                      {" "}
                      / {pkg.duration_days ? `${pkg.duration_days} hari` : "seumur hidup"}
                    </span>
                  )}
                </p>
                <ul className="mt-6 space-y-2.5">
                  {pkg.features.map((f) => (
                    <li key={f} className="flex items-center gap-2 font-landing-sans text-sm">
                      <Check className="size-4 shrink-0 text-brand" />
                      <span className="capitalize">{f.replace(/_/g, " ")}</span>
                    </li>
                  ))}
                  <li className="flex items-center gap-2 font-landing-sans text-sm">
                    <Check className="size-4 shrink-0 text-brand" />
                    {pkg.max_photos ? `${pkg.max_photos} foto` : "Foto tanpa batas"}
                  </li>
                  <li className="flex items-center gap-2 font-landing-sans text-sm">
                    <Check className="size-4 shrink-0 text-brand" />
                    {pkg.max_guests ? `${pkg.max_guests} tamu` : "Tamu tanpa batas"}
                  </li>
                </ul>
                <Link to="/dashboard/register">
                  <Button
                    className={`mt-6 w-full ${isPopular ? "bg-brand text-brand-foreground hover:bg-brand/90" : ""}`}
                    variant={isPopular ? "default" : "outline"}
                  >
                    Pilih Paket
                  </Button>
                </Link>
              </motion.div>
            );
          })}
        </div>
      </div>
    </section>
  );
}
