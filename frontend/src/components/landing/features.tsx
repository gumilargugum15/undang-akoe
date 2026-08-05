import { motion } from "motion/react";
import {
  BarChart3,
  BookHeart,
  Clock,
  Images,
  Layers,
  MessageSquareHeart,
  Music2,
  QrCode,
  Search,
  Smartphone,
  Video,
  Wallet,
  type LucideIcon,
} from "lucide-react";

const FEATURES: { icon: LucideIcon; title: string; description: string }[] = [
  { icon: Layers, title: "Multi Tema", description: "Ganti tema kapan saja tanpa kehilangan data yang sudah diisi." },
  { icon: Smartphone, title: "Responsive", description: "Tampilan optimal di desktop, tablet, maupun smartphone." },
  { icon: QrCode, title: "QR Code", description: "Unduh QR code undangan untuk dicetak di kartu fisik." },
  { icon: MessageSquareHeart, title: "RSVP Online", description: "Tamu konfirmasi kehadiran langsung dari halaman undangan." },
  { icon: BookHeart, title: "Buku Tamu Digital", description: "Kumpulkan ucapan dan doa dari para tamu secara online." },
  { icon: Clock, title: "Countdown", description: "Hitung mundur otomatis menuju hari bahagia Anda." },
  { icon: Images, title: "Galeri Foto", description: "Tampilkan momen berharga lewat galeri foto yang elegan." },
  { icon: Video, title: "Galeri Video", description: "Sematkan video YouTube atau unggah video singkat." },
  { icon: Music2, title: "Musik Latar", description: "Putar lagu favorit sebagai latar undangan digital Anda." },
  { icon: Wallet, title: "Amplop Digital", description: "Terima hadiah lewat rekening bank, e-wallet, atau QRIS." },
  { icon: Search, title: "SEO Friendly", description: "Judul dan deskripsi undangan otomatis siap dibagikan di media sosial." },
  { icon: BarChart3, title: "Statistik Pengunjung", description: "Pantau jumlah kunjungan dan respons tamu secara real-time." },
];

export function LandingFeatures() {
  return (
    <section id="fitur" className="bg-muted/30 py-20 sm:py-28">
      <div className="mx-auto max-w-6xl px-4 sm:px-6">
        <div className="mx-auto max-w-xl text-center">
          <p className="font-landing-sans text-sm font-medium text-brand">Fitur Unggulan</p>
          <h2 className="mt-2 font-landing-display text-3xl font-bold tracking-tight sm:text-4xl">
            Semua yang Anda Butuhkan, dalam Satu Undangan
          </h2>
        </div>

        <div className="mt-14 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
          {FEATURES.map((feature, i) => (
            <motion.div
              key={feature.title}
              initial={{ opacity: 0, y: 12 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true, margin: "-40px" }}
              transition={{ duration: 0.35, delay: (i % 6) * 0.05 }}
              className="rounded-xl border bg-card p-5 transition-shadow hover:shadow-md"
            >
              <div className="grid size-10 place-items-center rounded-lg bg-brand/10 text-brand">
                <feature.icon className="size-5" />
              </div>
              <h3 className="mt-4 font-landing-display text-base font-semibold">{feature.title}</h3>
              <p className="mt-1.5 font-landing-sans text-sm leading-relaxed text-muted-foreground">
                {feature.description}
              </p>
            </motion.div>
          ))}
        </div>
      </div>
    </section>
  );
}
