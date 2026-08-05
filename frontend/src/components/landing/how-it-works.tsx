import { motion } from "motion/react";
import { LayoutTemplate, Link2, PenLine, Send, UserPlus, type LucideIcon } from "lucide-react";

const STEPS: { icon: LucideIcon; title: string; description: string }[] = [
  { icon: UserPlus, title: "Daftar", description: "Buat akun gratis hanya dengan email, tanpa kartu kredit." },
  { icon: LayoutTemplate, title: "Pilih Template", description: "Jelajahi koleksi tema dan pilih yang paling sesuai acara Anda." },
  { icon: PenLine, title: "Isi Data", description: "Lengkapi data mempelai, acara, galeri, dan detail lainnya." },
  { icon: Send, title: "Publish", description: "Terbitkan undangan Anda hanya dengan satu klik." },
  { icon: Link2, title: "Bagikan Link", description: "Bagikan tautan undangan ke tamu lewat WhatsApp atau media lain." },
];

export function LandingHowItWorks() {
  return (
    <section id="cara-kerja" className="py-20 sm:py-28">
      <div className="mx-auto max-w-6xl px-4 sm:px-6">
        <div className="mx-auto max-w-xl text-center">
          <p className="font-landing-sans text-sm font-medium text-brand">Cara Kerja</p>
          <h2 className="mt-2 font-landing-display text-3xl font-bold tracking-tight sm:text-4xl">
            Lima Langkah Menuju Undangan Anda
          </h2>
        </div>

        <div className="mt-14 grid gap-8 sm:grid-cols-2 lg:grid-cols-5">
          {STEPS.map((step, i) => (
            <motion.div
              key={step.title}
              initial={{ opacity: 0, y: 16 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true, margin: "-40px" }}
              transition={{ duration: 0.4, delay: i * 0.08 }}
              className="relative text-center"
            >
              <div className="mx-auto grid size-14 place-items-center rounded-2xl bg-brand/10 text-brand">
                <step.icon className="size-6" />
              </div>
              <p className="mt-4 font-landing-sans text-xs font-semibold uppercase tracking-widest text-muted-foreground">
                Langkah {i + 1}
              </p>
              <h3 className="mt-1 font-landing-display text-lg font-semibold">{step.title}</h3>
              <p className="mt-2 font-landing-sans text-sm leading-relaxed text-muted-foreground">
                {step.description}
              </p>
            </motion.div>
          ))}
        </div>
      </div>
    </section>
  );
}
