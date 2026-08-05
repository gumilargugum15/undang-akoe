import { useState, type FormEvent } from "react";
import { AnimatePresence, motion } from "motion/react";
import { Check, Copy, Gift, Heart, Send } from "lucide-react";
import { toast } from "sonner";
import { api, ApiError } from "@/lib/api";
import type { ApiWish } from "@/lib/invitation-adapter";
import { useInvitationData } from "./invitation-data-provider";
import { Reveal } from "./reveal";
import { Section, SectionTitle } from "./sections";

const ATTENDANCE_OPTIONS = [
  { value: "hadir", label: "Hadir" },
  { value: "tidak_hadir", label: "Tidak Hadir" },
] as const;

const ATTENDANCE_LABEL: Record<ApiWish["attendance"], string> = {
  hadir: "Hadir",
  tidak_hadir: "Tidak Hadir",
  ragu: "Ragu",
};

// Only used when no `slug` is passed — the original template demo (`/`) has no real invitation
// behind it, so it keeps behaving exactly as it did before this integration: local-only, seeded.
const DEMO_SEED_WISHES: ApiWish[] = [
  {
    uuid: "demo-1",
    guest_name: "Dinda & Keluarga",
    attendance: "hadir",
    guest_count: 3,
    message: "Selamat menempuh hidup baru, semoga menjadi keluarga yang sakinah mawaddah warahmah.",
    is_approved: true,
    created_at: new Date(Date.now() - 1000 * 60 * 60 * 26).toISOString(),
  },
  {
    uuid: "demo-2",
    guest_name: "Bagas Prayoga",
    attendance: "tidak_hadir",
    guest_count: 1,
    message: "Maaf belum bisa hadir, doa terbaik selalu menyertai kalian berdua!",
    is_approved: true,
    created_at: new Date(Date.now() - 1000 * 60 * 90).toISOString(),
  },
];

function timeAgo(iso: string) {
  const m = Math.floor((Date.now() - new Date(iso).getTime()) / 60000);
  if (m < 1) return "baru saja";
  if (m < 60) return `${m} menit lalu`;
  const h = Math.floor(m / 60);
  if (h < 24) return `${h} jam lalu`;
  return `${Math.floor(h / 24)} hari lalu`;
}

export function RsvpAndWishes({
  slug,
  initialWishes = DEMO_SEED_WISHES,
}: {
  slug?: string;
  initialWishes?: ApiWish[];
}) {
  const [wishes, setWishes] = useState<ApiWish[]>(initialWishes);
  const [name, setName] = useState("");
  const [attendance, setAttendance] = useState<(typeof ATTENDANCE_OPTIONS)[number]["value"]>("hadir");
  const [guests, setGuests] = useState(1);
  const [message, setMessage] = useState("");
  const [submitting, setSubmitting] = useState(false);

  const submit = async (e: FormEvent) => {
    e.preventDefault();
    if (!name.trim() || !message.trim()) {
      toast.error("Nama dan ucapan wajib diisi.");
      return;
    }

    setSubmitting(true);
    try {
      if (slug) {
        const res = await api.post<{ message: string; data: ApiWish }>(
          `/public/invitations/${slug}/rsvp`,
          {
            guest_name: name.trim(),
            attendance,
            guest_count: attendance === "hadir" ? guests : undefined,
            message: message.trim(),
          },
        );
        setWishes((w) => [res.data, ...w]);
        toast.success(res.message);
      } else {
        // No real invitation behind this page (the `/` template demo) — local-only, as before.
        setWishes((w) => [
          {
            uuid: `local-${Date.now()}`,
            guest_name: name.trim(),
            attendance,
            guest_count: attendance === "hadir" ? guests : 1,
            message: message.trim(),
            is_approved: true,
            created_at: new Date().toISOString(),
          },
          ...w,
        ]);
        toast.success(
          attendance === "hadir"
            ? `Terima kasih ${name}, sampai jumpa bersama ${guests} tamu!`
            : `Terima kasih ${name} atas doanya.`,
        );
      }
      setName("");
      setMessage("");
    } catch (err) {
      toast.error(err instanceof ApiError ? err.message : "Gagal mengirim ucapan, coba lagi.");
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <Section id="rsvp">
      <SectionTitle eyebrow="RSVP" title="Konfirmasi Kehadiran" />

      <Reveal className="inv-surface p-6">
        <form onSubmit={submit} className="space-y-4">
          <div>
            <label className="mb-1.5 block font-body text-xs uppercase tracking-widest text-inv-muted">
              Nama
            </label>
            <input
              className="inv-field"
              value={name}
              onChange={(e) => setName(e.target.value)}
              placeholder="Nama Anda"
            />
          </div>

          <div className="grid gap-4 sm:grid-cols-2">
            <div>
              <label className="mb-1.5 block font-body text-xs uppercase tracking-widest text-inv-muted">
                Kehadiran
              </label>
              <div className="flex gap-2">
                {ATTENDANCE_OPTIONS.map((opt) => (
                  <button
                    key={opt.value}
                    type="button"
                    onClick={() => setAttendance(opt.value)}
                    className={`inv-btn flex-1 ${attendance === opt.value ? "" : "inv-btn-outline"}`}
                  >
                    {opt.label}
                  </button>
                ))}
              </div>
            </div>
            <div>
              <label className="mb-1.5 block font-body text-xs uppercase tracking-widest text-inv-muted">
                Jumlah tamu
              </label>
              <select
                className="inv-field"
                value={guests}
                disabled={attendance !== "hadir"}
                onChange={(e) => setGuests(Number(e.target.value))}
              >
                {[1, 2, 3, 4, 5].map((n) => (
                  <option key={n} value={n}>
                    {n} orang
                  </option>
                ))}
              </select>
            </div>
          </div>

          <div>
            <label className="mb-1.5 block font-body text-xs uppercase tracking-widest text-inv-muted">
              Ucapan & Doa
            </label>
            <textarea
              className="inv-field min-h-28 resize-none"
              value={message}
              onChange={(e) => setMessage(e.target.value)}
              placeholder="Tulis ucapan dan doa terbaik Anda..."
            />
          </div>

          <button type="submit" className="inv-btn w-full" disabled={submitting}>
            <Send className="size-4" /> {submitting ? "Mengirim..." : "Kirim Ucapan"}
          </button>
        </form>
      </Reveal>

      <div className="mt-12">
        <SectionTitle eyebrow="Wall of Love" title="Ucapan & Doa" />
        <div className="space-y-3">
          <AnimatePresence initial={false}>
            {wishes.map((w) => (
              <motion.div
                key={w.uuid}
                layout
                initial={{ opacity: 0, y: -12 }}
                animate={{ opacity: 1, y: 0 }}
                exit={{ opacity: 0 }}
                className="inv-surface p-5"
              >
                <div className="grid grid-cols-[minmax(0,1fr)_auto] items-start gap-3">
                  <div className="flex min-w-0 items-center gap-2">
                    <Heart className="size-4 shrink-0 text-inv-secondary" />
                    <p className="truncate font-head text-lg text-inv-text">{w.guest_name}</p>
                  </div>
                  <span className="shrink-0 font-body text-[10px] uppercase tracking-widest text-inv-muted">
                    {timeAgo(w.created_at)}
                  </span>
                </div>
                <p className="mt-2 font-body text-sm leading-relaxed text-inv-muted">{w.message}</p>
                <span
                  className="mt-3 inline-block border border-inv-border px-2 py-0.5 font-body text-[10px] uppercase tracking-widest text-inv-secondary"
                  style={{ borderRadius: "var(--inv-radius)" }}
                >
                  {ATTENDANCE_LABEL[w.attendance]}
                </span>
              </motion.div>
            ))}
          </AnimatePresence>
        </div>
      </div>
    </Section>
  );
}

export function GiftSection() {
  const invitation = useInvitationData();
  const [copied, setCopied] = useState<string | null>(null);

  const copy = async (value: string) => {
    await navigator.clipboard.writeText(value);
    setCopied(value);
    toast.success("Nomor berhasil disalin");
    window.setTimeout(() => setCopied(null), 2000);
  };

  if (invitation.gifts.length === 0) return null;

  return (
    <Section id="amplop" alt>
      <SectionTitle eyebrow="Amplop Digital" title="Tanda Kasih" />
      <Reveal className="mb-6 text-center">
        <p className="font-body text-sm leading-relaxed text-inv-muted">
          Doa restu Anda adalah hadiah terindah. Namun jika berkenan memberi tanda kasih, dapat
          melalui:
        </p>
      </Reveal>

      <div className="grid gap-4 sm:grid-cols-3">
        {invitation.gifts.map((g, i) => (
          <Reveal key={g.number} delay={i * 0.1} className="inv-surface p-5 text-center">
            <Gift className="mx-auto size-5 text-inv-secondary" />
            <p className="mt-3 font-head text-xl text-inv-primary">{g.label}</p>
            <p className="mt-1 font-body text-sm tabular-nums text-inv-text">{g.number}</p>
            <p className="font-body text-xs text-inv-muted">a.n. {g.holder}</p>
            <button className="inv-btn inv-btn-outline mt-4 w-full" onClick={() => copy(g.number)}>
              {copied === g.number ? <Check className="size-4" /> : <Copy className="size-4" />}
              {copied === g.number ? "Tersalin" : "Salin"}
            </button>
          </Reveal>
        ))}
      </div>

      {invitation.address && (
        <Reveal delay={0.2} className="mt-6 text-center">
          <p className="font-body text-xs text-inv-muted">{invitation.address}</p>
        </Reveal>
      )}
    </Section>
  );
}
