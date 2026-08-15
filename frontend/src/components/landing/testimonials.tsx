import { Star } from "lucide-react";
import {
  Carousel,
  CarouselContent,
  CarouselItem,
  CarouselNext,
  CarouselPrevious,
} from "@/components/ui/carousel";

// Sample content — no real customer-review backend exists yet (planned for a later phase).
const TESTIMONIALS = [
  {
    initials: "AR",
    name: "Alya & Raka",
    role: "Pernikahan, Bandung",
    rating: 5,
    comment:
      "Prosesnya cepat banget, dalam sejam undangan sudah jadi dan bisa langsung dibagikan ke keluarga. Tamu juga jadi lebih mudah RSVP.",
  },
];

export function LandingTestimonials() {
  return (
    <section id="testimoni" className="py-20 sm:py-28">
      <div className="mx-auto max-w-4xl px-4 sm:px-6">
        <div className="mx-auto max-w-xl text-center">
          <p className="font-landing-sans text-sm font-medium text-brand">Testimoni</p>
          <h2 className="mt-2 font-landing-display text-3xl font-bold tracking-tight sm:text-4xl">
            Kata Mereka yang Sudah Mencoba
          </h2>
        </div>

        <Carousel opts={{ loop: true, align: "center" }} className="mt-12">
          <CarouselContent>
            {TESTIMONIALS.map((t) => (
              <CarouselItem key={t.name} className="sm:basis-1/2">
                <div className="mx-2 flex h-full flex-col rounded-2xl border bg-card p-6 shadow-sm">
                  <div className="flex gap-0.5">
                    {Array.from({ length: 5 }).map((_, i) => (
                      <Star
                        key={i}
                        className={`size-4 ${i < t.rating ? "fill-brand text-brand" : "text-muted"}`}
                      />
                    ))}
                  </div>
                  <p className="mt-4 flex-1 font-landing-sans text-sm leading-relaxed text-foreground">
                    “{t.comment}”
                  </p>
                  <div className="mt-5 flex items-center gap-3">
                    <span className="grid size-10 place-items-center rounded-full bg-brand/15 font-landing-display text-sm font-semibold text-brand-foreground">
                      {t.initials}
                    </span>
                    <div>
                      <p className="font-landing-sans text-sm font-semibold">{t.name}</p>
                      <p className="font-landing-sans text-xs text-muted-foreground">{t.role}</p>
                    </div>
                  </div>
                </div>
              </CarouselItem>
            ))}
          </CarouselContent>
          <div className="mt-6 flex justify-center gap-2">
            <CarouselPrevious className="static translate-y-0" />
            <CarouselNext className="static translate-y-0" />
          </div>
        </Carousel>
      </div>
    </section>
  );
}
