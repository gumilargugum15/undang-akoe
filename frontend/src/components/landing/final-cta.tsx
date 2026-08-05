import { Link } from "@tanstack/react-router";
import { ArrowRight, Play } from "lucide-react";
import { Button } from "@/components/ui/button";

export function LandingFinalCta() {
  return (
    <section className="py-20 sm:py-28">
      <div className="mx-auto max-w-4xl px-4 sm:px-6">
        <div className="rounded-3xl bg-brand/10 px-6 py-14 text-center sm:px-14">
          <h2 className="font-landing-display text-3xl font-bold tracking-tight sm:text-4xl">
            Buat Undangan Digital Impian Anda Sekarang
          </h2>
          <p className="mx-auto mt-3 max-w-md font-landing-sans text-muted-foreground">
            Gratis untuk mulai, tanpa kartu kredit. Undangan Anda siap dibagikan hanya dalam
            hitungan menit.
          </p>
          <div className="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
            <Link to="/dashboard/register">
              <Button size="lg" className="w-full gap-2 bg-brand text-brand-foreground hover:bg-brand/90 sm:w-auto">
                Daftar Gratis
                <ArrowRight className="size-4" />
              </Button>
            </Link>
            <a href="/alya-raka" target="_blank" rel="noreferrer">
              <Button size="lg" variant="outline" className="w-full gap-2 sm:w-auto">
                <Play className="size-4" />
                Lihat Demo
              </Button>
            </a>
          </div>
        </div>
      </div>
    </section>
  );
}
