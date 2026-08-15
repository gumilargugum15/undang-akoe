import { createFileRoute, notFound } from "@tanstack/react-router";
import { AnimatePresence, motion } from "motion/react";
import { useEffect, useMemo, useState, type ReactNode } from "react";
import {
  BookHeart,
  CalendarDays,
  Gift as GiftIcon,
  Heart,
  Home as HomeIcon,
  Images,
  MapPin,
  MessageCircleHeart,
} from "lucide-react";
import { Toaster } from "@/components/ui/sonner";
import { InvitationThemeProvider } from "@/components/invitation/theme-provider";
import { InvitationDataProvider } from "@/components/invitation/invitation-data-provider";
import { Cover } from "@/components/invitation/cover";
import {
  EventSection,
  GallerySection,
  LoveStorySection,
  MapsSection,
} from "@/components/invitation/sections";
import { GiftSection, RsvpAndWishes } from "@/components/invitation/interactive";
import { MusicPlayer } from "@/components/invitation/music-player";
import { BottomNav, type NavItem } from "@/components/invitation/bottom-nav";
import { api, ApiError, getOrCreateSessionId } from "@/lib/api";
import {
  toGifts,
  toLegacyInvitation,
  type ApiEnvelope,
  type ApiPublicInvitation,
  type ApiWish,
} from "@/lib/invitation-adapter";
import { heroTabLabel, resolveHeroComponents, storyTabLabel } from "@/lib/invitation-templates";

type Page = NavItem & { content: ReactNode };

const INVITATION_NOUN: Record<string, string> = {
  wedding: "Undangan Pernikahan Digital",
  anniversary: "Undangan Hari Jadi Pernikahan Digital",
  birthday: "Undangan Ulang Tahun Digital",
  khitan: "Undangan Khitanan Digital",
  aqiqah: "Undangan Aqiqah Digital",
  graduation: "Undangan Wisuda Digital",
  corporate: "Undangan Acara Digital",
};

export const Route = createFileRoute("/$slug")({
  // `?to=<guest slug_token>` — a personalized link generated in the dashboard for a specific
  // invitee (e.g. shared via WhatsApp), resolved server-side to that guest's name so the cover
  // greets them instead of the generic "Bapak/Ibu/Saudara/i".
  validateSearch: (search: Record<string, unknown>) => ({
    to: typeof search.to === "string" ? search.to : undefined,
  }),
  loaderDeps: ({ search }) => ({ to: search.to }),
  loader: async ({ params, deps }) => {
    try {
      const invitationPath = deps.to
        ? `/public/invitations/${params.slug}?to=${encodeURIComponent(deps.to)}`
        : `/public/invitations/${params.slug}`;
      const [invitationRes, wallRes, envelopesRes] = await Promise.all([
        api.get<{ data: ApiPublicInvitation }>(invitationPath),
        api.get<{ data: ApiWish[] }>(`/public/invitations/${params.slug}/guestbook`),
        api.get<{ data: ApiEnvelope[] }>(`/public/invitations/${params.slug}/envelopes`),
      ]);

      return {
        invitation: invitationRes.data,
        wishes: wallRes.data,
        envelopes: envelopesRes.data,
      };
    } catch (err) {
      if (err instanceof ApiError && err.status === 404) {
        throw notFound();
      }
      throw err;
    }
  },
  head: ({ loaderData }) => {
    if (!loaderData) return {};

    const { invitation } = loaderData;
    const noun = INVITATION_NOUN[invitation.event_category] ?? INVITATION_NOUN.wedding;
    const title = invitation.seo.meta_title ?? `${invitation.title} — ${noun}`;
    const description = invitation.seo.meta_description ?? `${noun} dari ${invitation.title}.`;

    return {
      meta: [
        { title },
        { name: "description", content: description },
        { property: "og:title", content: title },
        { property: "og:description", content: description },
        ...(invitation.seo.og_image
          ? [{ property: "og:image", content: invitation.seo.og_image }]
          : []),
        { property: "og:type", content: "website" },
        { name: "twitter:card", content: "summary_large_image" },
      ],
      links: invitation.seo.favicon ? [{ rel: "icon", href: invitation.seo.favicon }] : [],
    };
  },
  component: PublicInvitationPage,
});

function PublicInvitationPage() {
  const { invitation, wishes, envelopes } = Route.useLoaderData();
  const { slug } = Route.useParams();
  const [opened, setOpened] = useState(false);

  const legacyInvitation = {
    ...toLegacyInvitation(invitation),
    gifts: toGifts(envelopes),
  };

  const { Home, People, Footer } = resolveHeroComponents(invitation.event_category);

  // Only the pages with real content get a tab — Cerita/Galeri/Amplop are skipped
  // entirely when the couple never added love stories/gallery photos/gift envelopes,
  // instead of leaving a guest to tap into a blank page.
  const pages: Page[] = useMemo(() => {
    const list: Page[] = [
      { id: "home", label: "Home", icon: HomeIcon, content: <Home /> },
      {
        id: "mempelai",
        label: heroTabLabel(invitation.event_category),
        icon: Heart,
        content: <People />,
      },
    ];
    if (legacyInvitation.loveStories.length > 0) {
      list.push({
        id: "cerita",
        label: storyTabLabel(invitation.event_category),
        icon: BookHeart,
        content: <LoveStorySection />,
      });
    }
    list.push({ id: "acara", label: "Acara", icon: CalendarDays, content: <EventSection /> });
    if (legacyInvitation.mapsEmbed) {
      list.push({ id: "maps", label: "Maps", icon: MapPin, content: <MapsSection /> });
    }
    if (legacyInvitation.gallery.length > 0) {
      list.push({ id: "galeri", label: "Galeri", icon: Images, content: <GallerySection /> });
    }
    list.push({
      id: "rsvp",
      label: "RSVP",
      icon: MessageCircleHeart,
      content: <RsvpAndWishes slug={slug} initialWishes={wishes} />,
    });
    if (legacyInvitation.gifts.length > 0) {
      list.push({ id: "amplop", label: "Amplop", icon: GiftIcon, content: <GiftSection /> });
    }
    // The thank-you footer always closes out the journey, whichever tab ends up last.
    const last = list[list.length - 1];
    list[list.length - 1] = {
      ...last,
      content: (
        <>
          {last.content}
          <Footer />
        </>
      ),
    };
    return list;
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [invitation.event_category, slug]);

  const [activeId, setActiveId] = useState(pages[0].id);

  function scrollToSection(id: string) {
    document.getElementById(id)?.scrollIntoView({ behavior: "smooth", block: "start" });
  }

  // Scrollspy: the bottom nav highlights whichever section currently sits in the
  // middle band of the viewport, instead of only reacting to a nav tap — every
  // section is always rendered and scrollable, the nav just tracks position.
  useEffect(() => {
    if (!opened) return;

    let observer: IntersectionObserver | null = null;
    let rafId: number;

    // The content only mounts once Cover's own exit animation finishes (AnimatePresence
    // mode="wait"), so the section elements may not exist in the DOM the instant `opened`
    // flips true — poll a frame at a time until they do, rather than observing nothing.
    function trySetup() {
      const elements = pages
        .map((p) => document.getElementById(p.id))
        .filter((el): el is HTMLElement => el !== null);

      if (elements.length < pages.length) {
        rafId = requestAnimationFrame(trySetup);
        return;
      }

      observer = new IntersectionObserver(
        (entries) => {
          const visible = entries.filter((e) => e.isIntersecting);
          if (visible.length === 0) return;
          const topMost = visible.reduce((a, b) =>
            a.boundingClientRect.top < b.boundingClientRect.top ? a : b,
          );
          setActiveId(topMost.target.id);
        },
        { rootMargin: "-45% 0px -45% 0px", threshold: 0 },
      );
      elements.forEach((el) => observer!.observe(el));
    }

    trySetup();

    return () => {
      cancelAnimationFrame(rafId);
      observer?.disconnect();
    };
  }, [opened, pages]);

  // Fire-and-forget pageview beacon — decoupled from the SSR loader above so crawlers/link
  // previews that only trigger the loader don't inflate the visitor count (Phase 11).
  useEffect(() => {
    void api
      .post(`/public/invitations/${slug}/visit`, {
        session_id: getOrCreateSessionId(),
        referrer: document.referrer || undefined,
      })
      .catch(() => {
        // Analytics beacon — never block or surface errors to the guest for this.
      });
  }, [slug]);

  return (
    <InvitationThemeProvider fixedTheme={invitation.theme}>
      <InvitationDataProvider data={legacyInvitation}>
        <main className="font-body text-inv-text">
          <AnimatePresence mode="wait">
            {!opened ? (
              <Cover
                key="cover"
                guest={invitation.guest_name ?? "Bapak/Ibu/Saudara/i"}
                onOpen={() => setOpened(true)}
              />
            ) : (
              <motion.div
                key="content"
                initial={{ opacity: 0 }}
                animate={{ opacity: 1 }}
                transition={{ duration: 0.6 }}
                className="pb-24"
              >
                {pages.map((p) => (
                  <div key={p.id}>{p.content}</div>
                ))}

                <BottomNav items={pages} activeId={activeId} onSelect={scrollToSection} />
              </motion.div>
            )}
          </AnimatePresence>

          <MusicPlayer opened={opened} />
          <Toaster position="top-center" />
        </main>
      </InvitationDataProvider>
    </InvitationThemeProvider>
  );
}
