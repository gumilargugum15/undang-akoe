import { useState } from "react";
import { Monitor, Smartphone } from "lucide-react";
import { Button } from "@/components/ui/button";
import demoDesktop from "@/assets/demo-preview-desktop.png";
import demoMobile from "@/assets/demo-preview-mobile.png";

export function LandingInteractiveDemo() {
  const [device, setDevice] = useState<"desktop" | "mobile">("desktop");

  return (
    <section className="py-20 sm:py-28">
      <div className="mx-auto max-w-4xl px-4 text-center sm:px-6">
        <p className="font-landing-sans text-sm font-medium text-brand">Demo Interaktif</p>
        <h2 className="mt-2 font-landing-display text-3xl font-bold tracking-tight sm:text-4xl">
          Rasakan Pengalamannya Langsung
        </h2>
        <p className="mx-auto mt-3 max-w-lg font-landing-sans text-muted-foreground">
          Lihat bagaimana undangan tampil di layar tamu — lengkap dengan cover, mempelai, acara,
          galeri, RSVP, hingga amplop digital.
        </p>

        <div className="mt-6 inline-flex rounded-full border bg-muted p-1">
          <button
            onClick={() => setDevice("desktop")}
            className={`flex items-center gap-1.5 rounded-full px-4 py-1.5 font-landing-sans text-sm transition-colors ${
              device === "desktop" ? "bg-background shadow-sm" : "text-muted-foreground"
            }`}
          >
            <Monitor className="size-4" /> Desktop
          </button>
          <button
            onClick={() => setDevice("mobile")}
            className={`flex items-center gap-1.5 rounded-full px-4 py-1.5 font-landing-sans text-sm transition-colors ${
              device === "mobile" ? "bg-background shadow-sm" : "text-muted-foreground"
            }`}
          >
            <Smartphone className="size-4" /> Mobile
          </button>
        </div>

        <div className="mt-8 flex justify-center">
          {device === "desktop" ? (
            <div className="w-full overflow-hidden rounded-xl border bg-card shadow-xl">
              <div className="flex items-center gap-1.5 border-b bg-muted/50 px-3 py-2">
                <span className="size-2.5 rounded-full bg-red-400" />
                <span className="size-2.5 rounded-full bg-yellow-400" />
                <span className="size-2.5 rounded-full bg-green-400" />
              </div>
              <img src={demoDesktop} alt="Demo undangan tampilan desktop" loading="lazy" className="w-full" />
            </div>
          ) : (
            <div className="w-56 overflow-hidden rounded-[2rem] border-8 border-foreground/90 bg-card shadow-xl">
              <img src={demoMobile} alt="Demo undangan tampilan mobile" loading="lazy" className="w-full" />
            </div>
          )}
        </div>

        <a href="/alya-raka" target="_blank" rel="noreferrer">
          <Button size="lg" variant="outline" className="mt-8">
            Lihat Demo Live
          </Button>
        </a>
      </div>
    </section>
  );
}
