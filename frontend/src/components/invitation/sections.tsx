import { useEffect, useState, type ReactNode } from "react";
import { CalendarDays, Clock, MapPin } from "lucide-react";
import { useInvitationData } from "./invitation-data-provider";
import { Divider, CornerOrnament, Monogram } from "./ornaments";
import { Reveal } from "./reveal";

export function Section({
  id,
  children,
  alt = false,
  className = "",
}: {
  id: string;
  children: ReactNode;
  alt?: boolean;
  className?: string;
}) {
  return (
    <section
      id={id}
      className={`relative overflow-hidden px-6 py-20 sm:py-24 ${className}`}
      style={{
        backgroundColor: alt ? "var(--inv-bg-alt)" : "var(--inv-bg)",
        backgroundImage: alt ? "none" : "var(--inv-texture)",
      }}
    >
      <div className="mx-auto w-full max-w-3xl">{children}</div>
    </section>
  );
}

export function SectionTitle({ eyebrow, title }: { eyebrow: string; title: string }) {
  return (
    <Reveal className="mb-10 text-center">
      <p className="inv-eyebrow">{eyebrow}</p>
      <h2 className="inv-heading mt-2 text-3xl sm:text-4xl">{title}</h2>
      <Divider className="mt-4" />
    </Reveal>
  );
}

/**
 * The same customer-uploaded cover photo shown on the opening Cover screen, repeated as a
 * full-bleed header for the Home section right after the invitation opens — negative margins
 * cancel the parent Section's own padding so it reaches the section's true edges, then a
 * bottom fade hands off into the section's background before the greeting text starts.
 */
export function HomeCoverPhoto({ src }: { src: string }) {
  return (
    <div className="relative -mx-6 -mt-20 mb-8 h-[32vh] overflow-hidden sm:-mt-24 sm:h-[38vh]">
      <img
        src={src}
        alt="Foto sampul undangan"
        className="absolute inset-0 size-full object-cover object-[50%_30%]"
      />
      <div
        className="absolute inset-x-0 bottom-0 h-16"
        style={{ background: "linear-gradient(180deg, transparent, var(--inv-bg))" }}
      />
    </div>
  );
}

export function HomeSection() {
  const invitation = useInvitationData();
  const initials = `${invitation.brideShort.charAt(0)} & ${invitation.groomShort.charAt(0)}`;

  return (
    <Section id="home">
      {invitation.homeCoverPhoto && <HomeCoverPhoto src={invitation.homeCoverPhoto} />}
      <CornerOrnament className="-left-6 top-0" />
      <Reveal className="flex flex-col items-center gap-6 text-center">
        <Monogram initials={initials} />
        <p className="font-body text-lg leading-relaxed text-inv-text">{invitation.greeting}</p>
        <Divider />
        <blockquote className="max-w-xl">
          <p className="font-script text-xl leading-relaxed text-inv-primary sm:text-2xl">
            “{invitation.quote}”
          </p>
          <cite className="mt-3 block font-body text-xs uppercase not-italic tracking-[0.3em] text-inv-muted">
            {invitation.quoteSource}
          </cite>
        </blockquote>
      </Reveal>
    </Section>
  );
}

export function PersonCard({
  photo,
  name,
  bio,
  handle,
  delay,
}: {
  photo: string | null;
  name: string;
  bio: string;
  handle: string;
  delay: number;
}) {
  return (
    <Reveal delay={delay} className="inv-surface overflow-hidden p-6 text-center">
      {photo ? (
        <img
          src={photo}
          alt={`Foto ${name}`}
          width={800}
          height={1000}
          loading="lazy"
          className="mx-auto aspect-[4/5] w-full max-w-56 object-cover"
          style={{ borderRadius: "var(--inv-card-radius)" }}
        />
      ) : (
        // No photo uploaded yet — a CSS initial avatar instead of a mismatched stock
        // photo, since a wedding-couple placeholder doesn't fit every event category.
        <div
          className="mx-auto flex aspect-[4/5] w-full max-w-56 items-center justify-center"
          style={{ borderRadius: "var(--inv-card-radius)", backgroundColor: "var(--inv-bg-alt)" }}
        >
          <span className="inv-heading text-6xl text-inv-secondary">{name.charAt(0)}</span>
        </div>
      )}
      <h3 className="inv-heading mt-5 text-2xl">{name}</h3>
      <p className="mt-2 font-body text-sm leading-relaxed text-inv-muted">{bio}</p>
      <p className="mt-3 font-body text-xs tracking-widest text-inv-secondary">{handle}</p>
    </Reveal>
  );
}

// wedding vs anniversary is the only branch here — every other category renders
// through HonoreeSection/HonoreeFooterSection instead (see invitation-templates.ts).
function coupleCopyFor(eventCategory: string) {
  if (eventCategory === "anniversary") {
    return {
      eyebrow: "Hari Jadi",
      title: "Pasangan Kami",
      closing:
        "Terima kasih telah menjadi bagian dari perjalanan cinta kami. Doa restu Bapak/Ibu/Saudara/i akan menjadi kebahagiaan tersendiri di hari istimewa ini.",
    };
  }

  return {
    eyebrow: "Mempelai",
    title: "Kedua Pengantin",
    closing:
      "Merupakan suatu kehormatan dan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir dan memberikan doa restu.",
  };
}

export function CoupleSection() {
  const invitation = useInvitationData();
  const { eyebrow, title } = coupleCopyFor(invitation.eventCategory);

  return (
    <Section id="mempelai" alt>
      <SectionTitle eyebrow={eyebrow} title={title} />
      <div className="grid gap-6 sm:grid-cols-2">
        <PersonCard
          photo={invitation.bridePhoto}
          name={invitation.brideFull}
          bio={invitation.brideBio}
          handle={invitation.brideHandle}
          delay={0}
        />
        <PersonCard
          photo={invitation.groomPhoto}
          name={invitation.groomFull}
          bio={invitation.groomBio}
          handle={invitation.groomHandle}
          delay={0.15}
        />
      </div>
    </Section>
  );
}

function useCountdown(target: string) {
  const [left, setLeft] = useState<number>(0);
  useEffect(() => {
    const t = new Date(target).getTime();
    const tick = () => setLeft(Math.max(0, t - Date.now()));
    tick();
    const i = window.setInterval(tick, 1000);
    return () => window.clearInterval(i);
  }, [target]);

  const s = Math.floor(left / 1000);
  return {
    days: Math.floor(s / 86400),
    hours: Math.floor((s % 86400) / 3600),
    minutes: Math.floor((s % 3600) / 60),
    seconds: s % 60,
  };
}

function Countdown() {
  const invitation = useInvitationData();
  const c = useCountdown(invitation.dateISO);
  const items = [
    { label: "Hari", value: c.days },
    { label: "Jam", value: c.hours },
    { label: "Menit", value: c.minutes },
    { label: "Detik", value: c.seconds },
  ];
  return (
    <div className="grid grid-cols-4 gap-2 sm:gap-4">
      {items.map((i) => (
        <div key={i.label} className="inv-surface px-2 py-4 text-center">
          <p className="inv-heading inv-glow text-2xl tabular-nums sm:text-4xl">
            {String(i.value).padStart(2, "0")}
          </p>
          <p className="mt-1 font-body text-[10px] uppercase tracking-[0.2em] text-inv-muted">
            {i.label}
          </p>
        </div>
      ))}
    </div>
  );
}

export function EventSection() {
  const invitation = useInvitationData();

  return (
    <Section id="acara">
      <SectionTitle eyebrow="Save the Date" title="Rangkaian Acara" />
      <Reveal className="mb-10">
        <Countdown />
      </Reveal>

      <div className="grid gap-5 sm:grid-cols-2">
        {invitation.events.map((e, idx) => (
          <Reveal key={e.title} delay={idx * 0.12} className="inv-surface p-6">
            <h3 className="inv-heading text-2xl text-inv-primary">{e.title}</h3>
            <ul className="mt-4 space-y-3 font-body text-sm text-inv-text">
              <li className="flex items-start gap-3">
                <CalendarDays className="mt-0.5 size-4 shrink-0 text-inv-secondary" />
                <span>{e.date}</span>
              </li>
              <li className="flex items-start gap-3">
                <Clock className="mt-0.5 size-4 shrink-0 text-inv-secondary" />
                <span>{e.time}</span>
              </li>
              <li className="flex items-start gap-3">
                <MapPin className="mt-0.5 size-4 shrink-0 text-inv-secondary" />
                <span>
                  <strong className="font-semibold">{e.place}</strong>
                  <br />
                  <span className="text-inv-muted">{e.address}</span>
                </span>
              </li>
            </ul>
          </Reveal>
        ))}
      </div>

      <Reveal delay={0.2} className="mt-8">
        <div className="inv-surface overflow-hidden p-2">
          <iframe
            title="Lokasi acara pernikahan"
            src={invitation.mapsEmbed}
            className="h-64 w-full border-0 sm:h-80"
            style={{ borderRadius: "calc(var(--inv-radius))" }}
            loading="lazy"
            referrerPolicy="no-referrer-when-downgrade"
          />
        </div>
        <div className="mt-5 text-center">
          <a
            className="inv-btn inv-btn-outline"
            href={invitation.mapsLink}
            target="_blank"
            rel="noreferrer"
          >
            <MapPin className="size-4" /> Buka di Google Maps
          </a>
        </div>
      </Reveal>
    </Section>
  );
}

export function LoveStorySection() {
  const invitation = useInvitationData();

  if (invitation.loveStories.length === 0) return null;

  return (
    <Section id="cerita" alt>
      <SectionTitle eyebrow="Perjalanan Kami" title="Love Story" />
      <div className="space-y-6">
        {invitation.loveStories.map((s, idx) => (
          <Reveal key={s.title} delay={idx * 0.12} className="inv-surface flex gap-5 p-6">
            {s.photo && (
              <img
                src={s.photo}
                alt={s.title}
                loading="lazy"
                className="size-20 shrink-0 object-cover sm:size-28"
                style={{ borderRadius: "var(--inv-card-radius)" }}
              />
            )}
            <div>
              <h3 className="inv-heading text-xl text-inv-primary sm:text-2xl">{s.title}</h3>
              {s.date && (
                <p className="mt-1 font-body text-xs uppercase tracking-[0.2em] text-inv-secondary">
                  {s.date}
                </p>
              )}
              {s.description && (
                <p className="mt-3 font-body text-sm leading-relaxed text-inv-text">
                  {s.description}
                </p>
              )}
            </div>
          </Reveal>
        ))}
      </div>
    </Section>
  );
}

// Repeats the original 4-photo mosaic's row-span rhythm (tall, short, short, tall) for however
// many photos the couple actually uploaded, instead of a fixed 4-item layout.
function gallerySpan(index: number): string {
  const position = index % 4;
  return position === 0 || position === 3 ? "row-span-2" : "";
}

export function GallerySection() {
  const invitation = useInvitationData();

  return (
    <Section id="galeri" alt>
      <SectionTitle eyebrow="Momen" title="Galeri Kami" />
      <div className="grid auto-rows-[130px] grid-cols-2 gap-3 sm:auto-rows-[180px]">
        {invitation.gallery.map((g, i) => (
          <Reveal key={g.src} delay={i * 0.08} className={gallerySpan(i)}>
            <div
              className="size-full overflow-hidden"
              style={{ borderRadius: "var(--inv-card-radius)" }}
            >
              <img
                src={g.src}
                alt={g.alt}
                loading="lazy"
                className="size-full object-cover transition-transform duration-700 hover:scale-110"
              />
            </div>
          </Reveal>
        ))}
      </div>
    </Section>
  );
}

export function FooterSection() {
  const invitation = useInvitationData();
  const { closing } = coupleCopyFor(invitation.eventCategory);

  return (
    <footer
      className="relative overflow-hidden px-6 py-20 text-center"
      style={{ backgroundColor: "var(--inv-bg)", backgroundImage: "var(--inv-texture)" }}
    >
      <CornerOrnament className="bottom-0 left-0" />
      <CornerOrnament className="right-0 top-0 rotate-180" />
      <Reveal className="relative mx-auto max-w-xl">
        <p className="font-body text-sm leading-relaxed text-inv-muted">{closing}</p>
        <Divider className="my-6" />
        <p className="font-script text-3xl text-inv-primary inv-glow sm:text-4xl">
          {invitation.brideShort} &amp; {invitation.groomShort}
        </p>
        <p className="mt-6 font-body text-[11px] uppercase tracking-[0.3em] text-inv-muted">
          Terima kasih
        </p>
      </Reveal>
    </footer>
  );
}
