import { createFileRoute, Link } from "@tanstack/react-router";
import { useEffect, useState } from "react";
import { toast } from "sonner";
import { DashboardShell } from "@/components/dashboard/dashboard-shell";
import { CoupleEditor } from "@/components/dashboard/couple-editor";
import { HonoreeEditor } from "@/components/dashboard/honoree-editor";
import { EventsEditor } from "@/components/dashboard/events-editor";
import { LoveStoryEditor } from "@/components/dashboard/love-story-editor";
import { GalleryEditor } from "@/components/dashboard/gallery-editor";
import { MusicEditor } from "@/components/dashboard/music-editor";
import { EnvelopeEditor } from "@/components/dashboard/envelope-editor";
import { RsvpManager } from "@/components/dashboard/rsvp-manager";
import { ThemeEditor } from "@/components/dashboard/theme-editor";
import { CoverPhotoEditor } from "@/components/dashboard/cover-photo-editor";
import { GuestListEditor } from "@/components/dashboard/guest-list-editor";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { customerApi, CustomerApiError } from "@/lib/customer-api";
import { useRequireCustomerAuth } from "@/lib/customer-auth";
import { heroTabLabel, isHonoreeCategory } from "@/lib/invitation-templates";

export const Route = createFileRoute("/dashboard/invitations/$invitationId")({
  component: EditInvitationPage,
});

interface InvitationDetail {
  id: number;
  title: string;
  slug: string;
  public_url: string;
  status: string;
  event_category: string;
  theme: { id: number; name: string } | null;
  cover_photo: string | null;
}

function EditInvitationPage() {
  const { invitationId } = Route.useParams();
  const { session, ready } = useRequireCustomerAuth();
  const [invitation, setInvitation] = useState<InvitationDetail | null>(null);
  const [loadError, setLoadError] = useState(false);

  function loadInvitation() {
    return customerApi
      .get<{ data: InvitationDetail }>(`/invitations/${invitationId}`)
      .then((res) => setInvitation(res.data))
      .catch((err) => {
        toast.error(err instanceof CustomerApiError ? err.message : "Gagal memuat undangan.");
        setLoadError(true);
      });
  }

  useEffect(() => {
    if (!ready) return;
    loadInvitation();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [ready, invitationId]);

  if (!ready || !session) return null;

  return (
    <DashboardShell user={session.user}>
      <div className="mb-4 flex items-center justify-between">
        <div>
          <div className="flex items-center gap-2">
            <h1 className="text-xl font-semibold">{invitation?.title ?? "Memuat..."}</h1>
            {invitation && (
              <Badge variant={invitation.status === "published" ? "default" : "outline"}>
                {invitation.status}
              </Badge>
            )}
          </div>
          {invitation && <p className="text-sm text-muted-foreground">{invitation.theme?.name}</p>}
        </div>
        <Link to="/dashboard">
          <Button variant="outline" size="sm">
            Kembali
          </Button>
        </Link>
      </div>

      {loadError && <p className="text-sm text-destructive">Undangan tidak ditemukan.</p>}

      {invitation && (
        <Tabs defaultValue="mempelai">
          <TabsList className="flex-wrap">
            <TabsTrigger value="mempelai">{heroTabLabel(invitation.event_category)}</TabsTrigger>
            <TabsTrigger value="tema">Tema</TabsTrigger>
            <TabsTrigger value="sampul">Sampul</TabsTrigger>
            <TabsTrigger value="acara">Acara</TabsTrigger>
            <TabsTrigger value="cerita">Cerita</TabsTrigger>
            <TabsTrigger value="galeri">Galeri</TabsTrigger>
            <TabsTrigger value="musik">Musik</TabsTrigger>
            <TabsTrigger value="amplop">Amplop</TabsTrigger>
            <TabsTrigger value="tamu">Tamu</TabsTrigger>
            <TabsTrigger value="rsvp">RSVP</TabsTrigger>
          </TabsList>

          <TabsContent value="mempelai" className="mt-4">
            {isHonoreeCategory(invitation.event_category) ? (
              <HonoreeEditor
                invitationId={invitation.id}
                eventCategory={invitation.event_category}
              />
            ) : (
              <CoupleEditor invitationId={invitation.id} />
            )}
          </TabsContent>
          <TabsContent value="tema" className="mt-4">
            <ThemeEditor
              invitationId={invitation.id}
              currentThemeId={invitation.theme?.id ?? null}
              locked={invitation.status !== "draft"}
              onChanged={loadInvitation}
            />
          </TabsContent>
          <TabsContent value="sampul" className="mt-4">
            <CoverPhotoEditor
              invitationId={invitation.id}
              currentPhoto={invitation.cover_photo}
              onChanged={loadInvitation}
            />
          </TabsContent>
          <TabsContent value="acara" className="mt-4">
            <EventsEditor invitationId={invitation.id} />
          </TabsContent>
          <TabsContent value="cerita" className="mt-4">
            <LoveStoryEditor invitationId={invitation.id} />
          </TabsContent>
          <TabsContent value="galeri" className="mt-4">
            <GalleryEditor invitationId={invitation.id} />
          </TabsContent>
          <TabsContent value="musik" className="mt-4">
            <MusicEditor invitationId={invitation.id} />
          </TabsContent>
          <TabsContent value="amplop" className="mt-4">
            <EnvelopeEditor invitationId={invitation.id} />
          </TabsContent>
          <TabsContent value="tamu" className="mt-4">
            <GuestListEditor invitationId={invitation.id} publicUrl={invitation.public_url} />
          </TabsContent>
          <TabsContent value="rsvp" className="mt-4">
            <RsvpManager invitationId={invitation.id} />
          </TabsContent>
        </Tabs>
      )}
    </DashboardShell>
  );
}
