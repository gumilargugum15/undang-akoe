import type { InvitationTheme } from "@/lib/themes";
import bridePlaceholder from "@/assets/bride.jpg";
import groomPlaceholder from "@/assets/groom.jpg";

export type ApiCouple = {
  role: "groom" | "bride";
  nickname: string;
  full_name: string;
  parent_name: string | null;
  instagram_handle: string | null;
  photo: string | null;
  description: string | null;
  sort_order: number;
};

/** Generic, N-per-invitation counterpart to `ApiCouple` — used by every non-wedding
 * category (birthday, khitan, ...) instead of the fixed groom/bride pair. */
export type ApiHonoree = {
  role_label: string;
  nickname: string;
  full_name: string;
  parent_name: string | null;
  instagram_handle: string | null;
  photo: string | null;
  description: string | null;
  meta: Record<string, unknown> | null;
  sort_order: number;
};

export type ApiEvent = {
  id: number;
  name: string;
  event_date: string;
  start_time: string | null;
  end_time: string | null;
  location_name: string | null;
  address: string | null;
  gmaps_url: string | null;
  latitude: number | null;
  longitude: number | null;
  notes: string | null;
  sort_order: number;
};

export type ApiLoveStory = {
  id: number;
  title: string;
  story_date: string | null;
  description: string | null;
  photo: string | null;
  sort_order: number;
};

export type ApiMusic = {
  source: "upload" | "spotify" | "youtube_music";
  title: string | null;
  artist: string | null;
  url: string | null;
  autoplay: boolean;
  is_loop: boolean;
  is_active: boolean;
};

export type ApiGalleryItem = {
  id: number;
  type: "photo" | "video_youtube" | "video_mp4";
  url: string | null;
  thumbnail: string | null;
  caption: string | null;
  category: string | null;
  sort_order: number;
};

export type ApiEnvelope = {
  id: number;
  type: "bank" | "ewallet" | "qris";
  provider_name: string;
  account_number: string | null;
  account_holder: string | null;
  qr_image: string | null;
  sort_order: number;
  is_active: boolean;
};

export type ApiWish = {
  uuid: string;
  guest_name: string;
  attendance: "hadir" | "tidak_hadir" | "ragu";
  guest_count: number;
  message: string;
  is_approved: boolean;
  created_at: string;
};

export type ApiPublicInvitation = {
  title: string;
  slug: string;
  event_category: string;
  cover_photo: string | null;
  guest_name: string | null;
  theme: InvitationTheme;
  seo: {
    meta_title: string | null;
    meta_description: string | null;
    og_image: string | null;
    favicon: string | null;
  };
  couples: { groom: ApiCouple | null; bride: ApiCouple | null };
  honorees: ApiHonoree[];
  events: ApiEvent[];
  love_stories: ApiLoveStory[];
  gallery: ApiGalleryItem[];
  music: ApiMusic | null;
};

/** Legacy shape from the original src/lib/invitation-data.ts — kept identical so
 * sections.tsx / cover.tsx / interactive.tsx need zero JSX changes, only a data-source swap. */
export type LegacyInvitation = {
  eventCategory: string;
  coverPhoto: string | null;
  brideShort: string;
  groomShort: string;
  brideFull: string;
  groomFull: string;
  brideBio: string;
  groomBio: string;
  brideHandle: string;
  groomHandle: string;
  dateISO: string;
  dateLabel: string;
  quote: string;
  quoteSource: string;
  greeting: string;
  events: { title: string; time: string; date: string; place: string; address: string }[];
  loveStories: { title: string; date: string; description: string; photo: string | null }[];
  // Generic N-per-invitation counterpart to the bride/groom fields above — only populated
  // (and only rendered, via HonoreeSection/HonoreeHomeSection/HonoreeFooterSection) for
  // non-wedding categories. Optional so every existing wedding-page consumer is untouched.
  honorees?: { roleLabel: string; nickname: string; fullName: string; bio: string; handle: string; photo: string | null }[];
  music: { source: "upload" | "spotify" | "youtube_music"; url: string; autoplay: boolean; loop: boolean } | null;
  mapsEmbed: string;
  mapsLink: string;
  gifts: { label: string; holder: string; number: string }[];
  address: string;
  bridePhoto: string;
  groomPhoto: string;
  gallery: { src: string; alt: string }[];
};

// Not modeled on any backend table (never part of the Mempelai/Undangan schema) — kept as
// fixed template flavor text per event_category rather than inventing a new
// customer-editable column for it. Falls back to the wedding copy for any
// category without its own entry (matches resolveHeroComponents' same fallback).
const DEFAULT_COPY: Record<string, { greeting: string; quote: string; quoteSource: string }> = {
  wedding: {
    greeting:
      "Dengan memohon rahmat dan ridha Tuhan Yang Maha Esa, kami bermaksud menyelenggarakan acara pernikahan putra-putri kami.",
    quote:
      "Dan di antara tanda-tanda kekuasaan-Nya diciptakan-Nya untukmu pasangan hidup, supaya kamu mendapat ketenangan hati.",
    quoteSource: "QS. Ar-Rum: 21",
  },
  birthday: {
    greeting:
      "Dengan penuh syukur, kami mengundang Bapak/Ibu/Saudara/i untuk turut merayakan hari bahagia ini bersama kami.",
    quote: "Usia bertambah, semoga berkah dan kebahagiaan turut bertambah menyertai setiap langkahmu.",
    quoteSource: "Untaian Doa",
  },
  khitan: {
    greeting:
      "Dengan memohon rahmat dan ridha Allah SWT, kami bermaksud menyelenggarakan acara khitanan putra kami.",
    quote: "Sesungguhnya kebersihan itu sebagian dari iman.",
    quoteSource: "HR. Muslim",
  },
  aqiqah: {
    greeting:
      "Dengan memohon rahmat dan ridha Allah SWT, kami bermaksud menyelenggarakan acara aqiqah putra/putri kami.",
    quote: "Harta dan anak-anak adalah perhiasan kehidupan dunia.",
    quoteSource: "QS. Al-Kahfi: 46",
  },
  anniversary: {
    greeting:
      "Dengan penuh syukur atas perjalanan cinta yang telah kami lalui, kami mengundang Bapak/Ibu/Saudara/i untuk turut merayakan hari jadi pernikahan kami.",
    quote:
      "Dan di antara tanda-tanda kekuasaan-Nya diciptakan-Nya untukmu pasangan hidup, supaya kamu mendapat ketenangan hati.",
    quoteSource: "QS. Ar-Rum: 21",
  },
  corporate: {
    greeting: "Dengan bangga, kami mengundang Bapak/Ibu/Saudara/i untuk hadir dalam acara perusahaan kami.",
    quote: "Kesuksesan adalah hasil dari kerja keras, dedikasi, dan kebersamaan tim yang solid.",
    quoteSource: "Undang Akoe",
  },
  graduation: {
    greeting:
      "Dengan penuh rasa syukur atas pencapaian ini, kami mengundang Bapak/Ibu/Saudara/i untuk turut merayakan hari kelulusan kami.",
    quote: "Pendidikan adalah senjata paling ampuh untuk mengubah dunia.",
    quoteSource: "Nelson Mandela",
  },
};

function copyForCategory(eventCategory: string) {
  return DEFAULT_COPY[eventCategory] ?? DEFAULT_COPY.wedding;
}

function formatIndonesianDate(dateStr: string): string {
  const date = new Date(`${dateStr}T00:00:00`);
  return new Intl.DateTimeFormat("id-ID", {
    weekday: "long",
    day: "numeric",
    month: "long",
    year: "numeric",
  }).format(date);
}

function formatTimeRange(start: string | null, end: string | null): string {
  const fmt = (t: string) => t.replace(":", ".");
  if (start && end) return `${fmt(start)} – ${fmt(end)} WIB`;
  if (start) return `${fmt(start)} WIB`;
  return "";
}

function bioFor(couple: ApiCouple | null): string {
  if (!couple) return "";
  if (couple.description) return couple.description;
  if (couple.parent_name) return `Putra/Putri dari ${couple.parent_name}`;
  return "";
}

function bioForHonoree(honoree: ApiHonoree): string {
  if (honoree.description) return honoree.description;
  if (honoree.parent_name) return `Putra/Putri dari ${honoree.parent_name}`;
  return "";
}

export function toLegacyInvitation(data: ApiPublicInvitation): LegacyInvitation {
  const { groom, bride } = data.couples;
  const sortedEvents = [...data.events].sort((a, b) => a.sort_order - b.sort_order);
  const earliest = [...data.events].sort((a, b) => a.event_date.localeCompare(b.event_date))[0];

  const dateISO = earliest
    ? `${earliest.event_date}T${earliest.start_time ?? "00:00"}:00+07:00`
    : new Date().toISOString();

  const withMaps = data.events.find((e) => e.gmaps_url);
  const copy = copyForCategory(data.event_category);

  return {
    eventCategory: data.event_category,
    coverPhoto: data.cover_photo,
    brideShort: bride?.nickname ?? "",
    groomShort: groom?.nickname ?? "",
    brideFull: bride?.full_name ?? "",
    groomFull: groom?.full_name ?? "",
    brideBio: bioFor(bride),
    groomBio: bioFor(groom),
    brideHandle: bride?.instagram_handle ?? "",
    groomHandle: groom?.instagram_handle ?? "",
    dateISO,
    dateLabel: earliest ? formatIndonesianDate(earliest.event_date) : "",
    quote: copy.quote,
    quoteSource: copy.quoteSource,
    greeting: copy.greeting,
    events: sortedEvents.map((e) => ({
      title: e.name,
      time: formatTimeRange(e.start_time, e.end_time),
      date: formatIndonesianDate(e.event_date),
      place: e.location_name ?? "",
      address: e.address ?? "",
    })),
    loveStories: [...data.love_stories]
      .sort((a, b) => a.sort_order - b.sort_order)
      .map((s) => ({
        title: s.title,
        date: s.story_date ? formatIndonesianDate(s.story_date) : "",
        description: s.description ?? "",
        photo: s.photo,
      })),
    music:
      data.music && data.music.url
        ? {
            source: data.music.source,
            url: data.music.url,
            autoplay: data.music.autoplay,
            loop: data.music.is_loop,
          }
        : null,
    mapsEmbed: withMaps?.gmaps_url ?? "",
    mapsLink: withMaps?.gmaps_url ?? "",
    // QRIS envelopes are skipped here: the current GiftSection card has no image slot, and adding
    // one would mean extending the Lovable design rather than just wiring data into it.
    gifts: [],
    address: "",
    // Placeholder photos keep the layout looking finished for a couple who hasn't uploaded one yet,
    // instead of a broken-image icon.
    bridePhoto: bride?.photo ?? bridePlaceholder,
    groomPhoto: groom?.photo ?? groomPlaceholder,
    gallery: data.gallery
      .filter((g) => g.type === "photo" && g.url)
      .sort((a, b) => a.sort_order - b.sort_order)
      .map((g) => ({ src: g.url as string, alt: g.caption ?? "Foto galeri" })),
    honorees: [...data.honorees]
      .sort((a, b) => a.sort_order - b.sort_order)
      .map((h) => ({
        roleLabel: h.role_label,
        nickname: h.nickname,
        fullName: h.full_name,
        bio: bioForHonoree(h),
        handle: h.instagram_handle ?? "",
        photo: h.photo,
      })),
  };
}

export function toGifts(envelopes: ApiEnvelope[]): LegacyInvitation["gifts"] {
  return envelopes
    .filter((e) => e.type !== "qris" && e.is_active)
    .map((e) => ({
      label: e.provider_name,
      holder: e.account_holder ?? "",
      number: e.account_number ?? "",
    }));
}
