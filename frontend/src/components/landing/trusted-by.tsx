import { useEffect, useState } from "react";
import { motion } from "motion/react";
import { api } from "@/lib/api";

interface Stats {
  total_customers: number;
  total_invitations: number;
  total_visitors: number;
}

function formatCount(n: number): string {
  if (n >= 1000) return `${(n / 1000).toFixed(n % 1000 === 0 ? 0 : 1)}rb+`;
  return `${n}+`;
}

export function LandingTrustedBy() {
  const [stats, setStats] = useState<Stats | null>(null);

  useEffect(() => {
    api
      .get<{ data: Stats }>("/public/stats")
      .then((res) => setStats(res.data))
      .catch(() => setStats(null));
  }, []);

  const items = [
    { label: "Customer Terdaftar", value: stats ? formatCount(stats.total_customers) : "—" },
    { label: "Undangan Diterbitkan", value: stats ? formatCount(stats.total_invitations) : "—" },
    { label: "Kunjungan Tamu", value: stats ? formatCount(stats.total_visitors) : "—" },
    { label: "Tema Pilihan", value: "4+" },
  ];

  return (
    <section className="border-y bg-muted/30 py-12">
      <div className="mx-auto max-w-6xl px-4 sm:px-6">
        <div className="grid grid-cols-2 gap-8 text-center sm:grid-cols-4">
          {items.map((item, i) => (
            <motion.div
              key={item.label}
              initial={{ opacity: 0, y: 12 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true }}
              transition={{ duration: 0.4, delay: i * 0.08 }}
            >
              <p className="font-landing-display text-3xl font-bold text-brand sm:text-4xl">{item.value}</p>
              <p className="mt-1 font-landing-sans text-xs text-muted-foreground sm:text-sm">{item.label}</p>
            </motion.div>
          ))}
        </div>
      </div>
    </section>
  );
}
