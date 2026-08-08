import { useInvitationData } from "./invitation-data-provider";
import { Divider, CornerOrnament, Monogram } from "./ornaments";
import { Reveal } from "./reveal";
import { Section, SectionTitle, PersonCard, HomeCoverPhoto } from "./sections";

type HonoreeCopy = { eyebrow: string; title: string; closing: string };

/** Category-appropriate copy for the 3 sections below — driven by `eventCategory`
 * rather than string-matching a honoree's free-text `roleLabel`. */
const HONOREE_COPY: Record<string, HonoreeCopy> = {
  khitan: {
    eyebrow: "Khitanan",
    title: "Yang Dikhitan",
    closing:
      "Merupakan suatu kehormatan dan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir dan memberikan doa restu atas khitanan putra kami.",
  },
  birthday: {
    eyebrow: "Ulang Tahun",
    title: "Yang Berulang Tahun",
    closing:
      "Kehadiran dan doa restu Bapak/Ibu/Saudara/i akan menjadi kebahagiaan tersendiri bagi kami di hari istimewa ini.",
  },
  aqiqah: {
    eyebrow: "Aqiqah",
    title: "Yang Diaqiqah",
    closing:
      "Merupakan suatu kehormatan dan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir dan memberikan doa restu atas kelahiran putra/putri kami.",
  },
  graduation: {
    eyebrow: "Wisuda",
    title: "Yang Diwisuda",
    closing:
      "Kehadiran dan doa restu Bapak/Ibu/Saudara/i akan menjadi kebahagiaan dan semangat tersendiri bagi kami di hari kelulusan ini.",
  },
  corporate: {
    eyebrow: "Acara Perusahaan",
    title: "Tuan Rumah Acara",
    closing:
      "Kami mengucapkan terima kasih atas kehadiran Bapak/Ibu sekalian dalam acara perusahaan kami.",
  },
};

function copyFor(eventCategory: string): HonoreeCopy {
  return HONOREE_COPY[eventCategory] ?? HONOREE_COPY.birthday;
}

export function HonoreeHomeSection() {
  const invitation = useInvitationData();
  const honorees = invitation.honorees ?? [];
  const initials = honorees.map((h) => h.nickname.charAt(0)).join(" & ") || "?";

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

export function HonoreeSection() {
  const invitation = useInvitationData();
  const honorees = invitation.honorees ?? [];
  const { eyebrow, title } = copyFor(invitation.eventCategory);

  return (
    <Section id="mempelai" alt>
      <SectionTitle eyebrow={eyebrow} title={title} />
      <div
        className={`grid gap-6 ${honorees.length > 1 ? "sm:grid-cols-2" : "sm:grid-cols-1 sm:justify-items-center"}`}
      >
        {honorees.map((h, idx) => (
          <div key={h.nickname} className={honorees.length === 1 ? "sm:max-w-sm" : undefined}>
            <PersonCard
              photo={h.photo}
              name={h.fullName}
              bio={h.bio}
              handle={h.handle}
              delay={idx * 0.15}
            />
          </div>
        ))}
      </div>
    </Section>
  );
}

export function HonoreeFooterSection() {
  const invitation = useInvitationData();
  const honorees = invitation.honorees ?? [];
  const { closing } = copyFor(invitation.eventCategory);
  const names = honorees.map((h) => h.nickname).join(" & ");

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
        <p className="font-script text-3xl text-inv-primary inv-glow sm:text-4xl">{names}</p>
        <p className="mt-6 font-body text-[11px] uppercase tracking-[0.3em] text-inv-muted">
          Terima kasih
        </p>
      </Reveal>
    </footer>
  );
}
