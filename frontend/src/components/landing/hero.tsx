import { Link } from "@tanstack/react-router";
import { motion } from "motion/react";
import { ArrowRight, Play, Sparkles } from "lucide-react";
import { Button } from "@/components/ui/button";
import demoDesktop from "@/assets/demo-preview-desktop.png";
import demoMobile from "@/assets/demo-preview-mobile.png";

export function LandingHero() {
  return (
    <section id="hero" className="relative overflow-hidden pb-16 pt-32 sm:pb-24 sm:pt-40">
      <div
        className="pointer-events-none absolute inset-x-0 top-0 -z-10 h-[600px] bg-gradient-to-b from-brand/10 via-transparent to-transparent"
        aria-hidden
      />
      <div className="mx-auto max-w-6xl px-4 sm:px-6">
        <div className="grid items-center gap-12 lg:grid-cols-2">
          <motion.div
            initial={{ opacity: 0, y: 16 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5 }}
          >
            <span className="inline-flex items-center gap-1.5 rounded-full border bg-brand/10 px-3 py-1 font-landing-sans text-xs font-medium text-brand-foreground">
              <Sparkles className="size-3.5" />
              Undangan digital untuk momen spesial Anda
            </span>
            <h1 className="mt-5 font-landing-display text-4xl font-bold leading-[1.1] tracking-tight sm:text-5xl lg:text-[3.25rem]">
              Buat Undangan Digital yang Berkesan, dalam Hitungan Menit
            </h1>
            <p className="mt-5 max-w-lg font-landing-sans text-base leading-relaxed text-muted-foreground sm:text-lg">
              Pilih tema, isi data acara, dan bagikan satu tautan ke semua tamu. Lengkap dengan
              RSVP, buku tamu, musik, galeri, dan amplop digital — tanpa perlu kemampuan desain.
            </p>
            <div className="mt-8 flex flex-col gap-3 sm:flex-row">
              <Link to="/dashboard/register">
                <Button size="lg" className="w-full gap-2 bg-brand text-brand-foreground hover:bg-brand/90 sm:w-auto">
                  Buat Undangan Gratis
                  <ArrowRight className="size-4" />
                </Button>
              </Link>
              <a href="/alya-raka" target="_blank" rel="noreferrer">
                <Button size="lg" variant="outline" className="w-full gap-2 sm:w-auto">
                  <Play className="size-4" />
                  Lihat Demo
                </Button>
              </a>
              <a href="#template">
                <Button size="lg" variant="ghost" className="w-full sm:w-auto">
                  Lihat Template
                </Button>
              </a>
            </div>
          </motion.div>

          <motion.div
            initial={{ opacity: 0, y: 24 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6, delay: 0.15 }}
            className="relative mx-auto w-full max-w-md lg:max-w-none"
          >
            <div className="overflow-hidden rounded-xl border bg-card shadow-2xl">
              <div className="flex items-center gap-1.5 border-b bg-muted/50 px-3 py-2">
                <span className="size-2.5 rounded-full bg-red-400" />
                <span className="size-2.5 rounded-full bg-yellow-400" />
                <span className="size-2.5 rounded-full bg-green-400" />
              </div>
              <img
                src={demoDesktop}
                alt="Contoh tampilan undangan digital di desktop"
                width={1280}
                height={800}
                className="w-full"
                fetchPriority="high"
              />
            </div>

            <div className="absolute -bottom-8 -right-4 w-28 overflow-hidden rounded-2xl border-4 border-background bg-card shadow-2xl sm:-right-8 sm:w-36">
              <img
                src={demoMobile}
                alt="Contoh tampilan undangan digital di mobile"
                width={390}
                height={844}
                className="w-full"
                loading="lazy"
              />
            </div>
          </motion.div>
        </div>
      </div>
    </section>
  );
}
