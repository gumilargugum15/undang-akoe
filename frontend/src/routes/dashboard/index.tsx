import { createFileRoute, Link } from "@tanstack/react-router";
import { useEffect, useMemo, useState } from "react";
import { toast } from "sonner";
import { Eye, Mail, MessageCircle, Send } from "lucide-react";
import { DashboardShell } from "@/components/dashboard/dashboard-shell";
import { QrCodeDialog } from "@/components/dashboard/qr-code-dialog";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alert";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { GreetingCard, StatCard } from "@/components/ui/stat-card";
import { BarStatChart } from "@/components/ui/bar-stat-chart";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { customerApi, CustomerApiError } from "@/lib/customer-api";
import { useRequireCustomerAuth } from "@/lib/customer-auth";

export const Route = createFileRoute("/dashboard/")({
  component: CustomerDashboardPage,
});

interface CustomerInvitation {
  id: number;
  title: string;
  slug: string;
  public_url: string;
  event_category: string;
  status: "draft" | "published" | "expired" | "suspended";
  theme: { name: string; thumbnail: string | null } | null;
  view_count: number;
}

interface PaginatedInvitations {
  data: CustomerInvitation[];
  meta: { current_page: number; last_page: number; per_page: number; total: number };
}

const STATUS_LABEL: Record<CustomerInvitation["status"], string> = {
  draft: "Draft",
  published: "Terbit",
  expired: "Kedaluwarsa",
  suspended: "Ditangguhkan",
};

function toWhatsAppNumber(phone: string): string {
  const digits = phone.replace(/\D/g, "");
  if (digits.startsWith("0")) return `62${digits.slice(1)}`;
  if (digits.startsWith("62")) return digits;
  return `62${digits}`;
}

function adminVerificationWhatsappUrl(): string {
  const adminPhone = import.meta.env.VITE_ADMIN_WHATSAPP ?? "";
  const message = "Halo Admin, saya ingin meminta verifikasi email untuk akun Undangan Digital saya agar bisa membuat undangan.";
  return `https://wa.me/${toWhatsAppNumber(adminPhone)}?text=${encodeURIComponent(message)}`;
}

function CustomerDashboardPage() {
  const { session, ready } = useRequireCustomerAuth();
  const [invitations, setInvitations] = useState<CustomerInvitation[]>([]);
  const [loading, setLoading] = useState(false);
  const [qrInvitation, setQrInvitation] = useState<CustomerInvitation | null>(null);

  async function loadInvitations() {
    setLoading(true);
    try {
      const res = await customerApi.get<PaginatedInvitations>("/invitations");
      setInvitations(res.data);
    } catch (err) {
      toast.error(err instanceof CustomerApiError ? err.message : "Gagal memuat daftar undangan.");
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    if (ready) loadInvitations();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [ready]);

  const publishedCount = useMemo(
    () => invitations.filter((inv) => inv.status === "published").length,
    [invitations],
  );
  const totalViews = useMemo(
    () => invitations.reduce((sum, inv) => sum + inv.view_count, 0),
    [invitations],
  );
  const viewsPerInvitation = useMemo(
    () => invitations.map((inv) => ({ name: inv.title, views: inv.view_count })),
    [invitations],
  );

  if (!ready || !session) return null;

  async function handlePublishToggle(invitation: CustomerInvitation) {
    try {
      if (invitation.status === "published") {
        await customerApi.patch(`/invitations/${invitation.id}/unpublish`);
        toast.success("Undangan dikembalikan ke draft.");
      } else {
        await customerApi.patch(`/invitations/${invitation.id}/publish`);
        toast.success("Undangan berhasil diterbitkan.");
      }
      await loadInvitations();
    } catch (err) {
      toast.error(err instanceof CustomerApiError ? err.message : "Gagal mengubah status undangan.");
    }
  }

  function shareToWhatsapp(invitation: CustomerInvitation) {
    const message = `Assalamualaikum, dengan hormat kami mengundang Bapak/Ibu/Saudara/i untuk menghadiri acara *${invitation.title}*. Info lengkap & RSVP dapat dilihat di:\n${invitation.public_url}`;
    window.open(`https://wa.me/?text=${encodeURIComponent(message)}`, "_blank", "noreferrer");
  }

  async function handleDelete(invitation: CustomerInvitation) {
    if (!confirm(`Hapus undangan "${invitation.title}"?`)) return;
    try {
      await customerApi.delete(`/invitations/${invitation.id}`);
      toast.success("Undangan berhasil dihapus.");
      await loadInvitations();
    } catch (err) {
      toast.error(err instanceof CustomerApiError ? err.message : "Gagal menghapus undangan.");
    }
  }

  return (
    <DashboardShell user={session.user}>
      <div className="space-y-6">
        {!session.user.email_verified_at && (
          <Alert variant="destructive">
            <AlertTitle>Email Anda belum terverifikasi</AlertTitle>
            <AlertDescription className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
              <span>Hanya akun yang sudah terverifikasi yang bisa membuat undangan.</span>
              <a href={adminVerificationWhatsappUrl()} target="_blank" rel="noreferrer">
                <Button variant="outline" size="sm" className="gap-2">
                  <MessageCircle className="size-4" />
                  Hubungi Admin via WhatsApp
                </Button>
              </a>
            </AlertDescription>
          </Alert>
        )}

        <GreetingCard
          title={`Halo, ${session.user.name.split(" ")[0]} \u{1F44B}`}
          subtitle="Ini ringkasan undangan digital yang sudah Anda buat."
        />

        <div className="grid gap-4 sm:grid-cols-3">
          <StatCard label="Total Undangan" value={invitations.length} icon={Mail} tone="violet" />
          <StatCard
            label="Sudah Terbit"
            value={publishedCount}
            icon={Send}
            tone="emerald"
            hint={`${invitations.length - publishedCount} draft`}
          />
          <StatCard label="Total Kunjungan" value={totalViews} icon={Eye} tone="sky" />
        </div>

        {invitations.length > 0 && (
          <BarStatChart
            title="Kunjungan per Undangan"
            data={viewsPerInvitation}
            dataKey="views"
            labelKey="name"
          />
        )}

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0">
            <CardTitle>Undangan Saya</CardTitle>
            <Link to="/dashboard/invitations/new">
              <Button>+ Buat Undangan</Button>
            </Link>
          </CardHeader>
          <CardContent>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Judul</TableHead>
                  <TableHead>Tema</TableHead>
                  <TableHead>Kategori</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead>Pengunjung</TableHead>
                  <TableHead className="text-right">Aksi</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {invitations.map((inv) => (
                  <TableRow key={inv.id}>
                    <TableCell className="font-medium">{inv.title}</TableCell>
                    <TableCell>{inv.theme?.name ?? "—"}</TableCell>
                    <TableCell>{inv.event_category}</TableCell>
                    <TableCell>
                      <Badge variant={inv.status === "published" ? "default" : "outline"}>
                        {STATUS_LABEL[inv.status]}
                      </Badge>
                    </TableCell>
                    <TableCell>{inv.view_count}</TableCell>
                    <TableCell className="space-x-1 text-right">
                      <Link to="/dashboard/invitations/$invitationId" params={{ invitationId: String(inv.id) }}>
                        <Button variant="outline" size="sm">
                          Edit
                        </Button>
                      </Link>
                      {inv.status === "published" && (
                        <>
                          <a href={inv.public_url} target="_blank" rel="noreferrer">
                            <Button variant="outline" size="sm">
                              Lihat
                            </Button>
                          </a>
                          <Button variant="outline" size="sm" onClick={() => shareToWhatsapp(inv)}>
                            Kirim WA
                          </Button>
                          <Button variant="outline" size="sm" onClick={() => setQrInvitation(inv)}>
                            QR Code
                          </Button>
                        </>
                      )}
                      <Button variant="outline" size="sm" onClick={() => handlePublishToggle(inv)}>
                        {inv.status === "published" ? "Batalkan Terbit" : "Terbitkan"}
                      </Button>
                      <Button variant="destructive" size="sm" onClick={() => handleDelete(inv)}>
                        Hapus
                      </Button>
                    </TableCell>
                  </TableRow>
                ))}
                {invitations.length === 0 && !loading && (
                  <TableRow>
                    <TableCell colSpan={6} className="text-center text-muted-foreground">
                      Anda belum punya undangan.{" "}
                      <Link to="/dashboard/invitations/new" className="underline">
                        Buat sekarang
                      </Link>
                      .
                    </TableCell>
                  </TableRow>
                )}
              </TableBody>
            </Table>
          </CardContent>
        </Card>

        {qrInvitation && (
          <QrCodeDialog
            open={!!qrInvitation}
            onOpenChange={(open) => !open && setQrInvitation(null)}
            title={qrInvitation.title}
            url={qrInvitation.public_url}
          />
        )}
      </div>
    </DashboardShell>
  );
}
