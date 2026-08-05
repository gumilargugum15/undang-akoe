import { createFileRoute, notFound } from "@tanstack/react-router";
import { AnimatePresence, motion } from "motion/react";
import { useEffect, useState } from "react";
import { Toaster } from "@/components/ui/sonner";
import { InvitationThemeProvider } from "@/components/invitation/theme-provider";
import { InvitationDataProvider } from "@/components/invitation/invitation-data-provider";
import { Cover } from "@/components/invitation/cover";
import { EventSection, GallerySection, LoveStorySection } from "@/components/invitation/sections";
import { GiftSection, RsvpAndWishes } from "@/components/invitation/interactive";
import { MusicPlayer } from "@/components/invitation/music-player";
import { api, ApiError, getOrCreateSessionId } from "@/lib/api";
import {
  toGifts,
  toLegacyInvitation,
  type ApiEnvelope,
  type ApiPublicInvitation,
  type ApiWish,
} from "@/lib/invitation-adapter";
import { heroTabLabel, resolveHeroComponents } from "@/lib/invitation-templates";

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
  const nav = [
    { id: "home", label: "Home" },
    { id: "mempelai", label: heroTabLabel(invitation.event_category) },
    { id: "cerita", label: "Cerita" },
    { id: "acara", label: "Acara" },
    { id: "galeri", label: "Galeri" },
    { id: "rsvp", label: "RSVP" },
    { id: "amplop", label: "Amplop" },
  ];

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
              >
                <nav className="sticky top-0 z-40 border-b border-inv-border/70 bg-inv-bg/85 backdrop-blur">
                  <div className="mx-auto flex max-w-3xl gap-5 overflow-x-auto px-6 py-3 [scrollbar-width:none]">
                    {nav.map((n) => (
                      <a
                        key={n.id}
                        href={`#${n.id}`}
                        className="shrink-0 font-body text-[11px] uppercase tracking-[0.22em] text-inv-muted transition-colors hover:text-inv-primary"
                      >
                        {n.label}
                      </a>
                    ))}
                  </div>
                </nav>

                <Home />
                <People />
                <LoveStorySection />
                <EventSection />
                <GallerySection />
                <RsvpAndWishes slug={slug} initialWishes={wishes} />
                <GiftSection />
                <Footer />
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
