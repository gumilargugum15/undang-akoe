import { useEffect, useState } from "react";
import { toast } from "sonner";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { customerApi, CustomerApiError } from "@/lib/customer-api";

interface RsvpEntry {
  id: number;
  guest_name: string;
  attendance: "hadir" | "tidak_hadir" | "ragu";
  guest_count: number;
  message: string;
  is_approved: boolean;
  created_at: string;
}

interface Summary {
  total_submissions: number;
  hadir: { submissions: number; guests: number };
  tidak_hadir: { submissions: number };
  ragu: { submissions: number };
}

const ATTENDANCE_LABEL: Record<RsvpEntry["attendance"], string> = {
  hadir: "Hadir",
  tidak_hadir: "Tidak Hadir",
  ragu: "Ragu-ragu",
};

export function RsvpManager({ invitationId }: { invitationId: number }) {
  const [entries, setEntries] = useState<RsvpEntry[]>([]);
  const [summary, setSummary] = useState<Summary | null>(null);

  async function load() {
    try {
      const [entriesRes, summaryRes] = await Promise.all([
        customerApi.get<{ data: RsvpEntry[] }>(`/invitations/${invitationId}/rsvp`),
        customerApi.get<{ data: Summary }>(`/invitations/${invitationId}/rsvp/summary`),
      ]);
      setEntries(entriesRes.data);
      setSummary(summaryRes.data);
    } catch (err) {
      toast.error(err instanceof CustomerApiError ? err.message : "Gagal memuat RSVP.");
    }
  }

  useEffect(() => {
    load();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [invitationId]);

  async function handleApprove(entry: RsvpEntry) {
    try {
      await customerApi.patch(`/invitations/${invitationId}/rsvp/${entry.id}/approve`);
      toast.success("Ucapan berhasil disetujui.");
      await load();
    } catch (err) {
      toast.error(err instanceof CustomerApiError ? err.message : "Gagal menyetujui ucapan.");
    }
  }

  async function handleReject(entry: RsvpEntry) {
    try {
      await customerApi.patch(`/invitations/${invitationId}/rsvp/${entry.id}/reject`);
      toast.success("Ucapan berhasil ditolak.");
      await load();
    } catch (err) {
      toast.error(err instanceof CustomerApiError ? err.message : "Gagal menolak ucapan.");
    }
  }

  async function handleDelete(entry: RsvpEntry) {
    if (!confirm(`Hapus RSVP dari "${entry.guest_name}"?`)) return;
    try {
      await customerApi.delete(`/invitations/${invitationId}/rsvp/${entry.id}`);
      toast.success("RSVP berhasil dihapus.");
      await load();
    } catch (err) {
      toast.error(err instanceof CustomerApiError ? err.message : "Gagal menghapus RSVP.");
    }
  }

  return (
    <div className="space-y-4">
      {summary && (
        <div className="grid grid-cols-2 gap-3 sm:grid-cols-4">
          <Card>
            <CardContent className="pt-6">
              <p className="text-2xl font-semibold">{summary.total_submissions}</p>
              <p className="text-xs text-muted-foreground">Total Respons</p>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="pt-6">
              <p className="text-2xl font-semibold">{summary.hadir.guests}</p>
              <p className="text-xs text-muted-foreground">Tamu Hadir ({summary.hadir.submissions} respons)</p>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="pt-6">
              <p className="text-2xl font-semibold">{summary.tidak_hadir.submissions}</p>
              <p className="text-xs text-muted-foreground">Tidak Hadir</p>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="pt-6">
              <p className="text-2xl font-semibold">{summary.ragu.submissions}</p>
              <p className="text-xs text-muted-foreground">Ragu-ragu</p>
            </CardContent>
          </Card>
        </div>
      )}

      <Card>
        <CardHeader>
          <CardTitle>Ucapan & RSVP</CardTitle>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Nama</TableHead>
                <TableHead>Kehadiran</TableHead>
                <TableHead>Jumlah Tamu</TableHead>
                <TableHead>Ucapan</TableHead>
                <TableHead>Status</TableHead>
                <TableHead className="text-right">Aksi</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {entries.map((entry) => (
                <TableRow key={entry.id}>
                  <TableCell className="font-medium">{entry.guest_name}</TableCell>
                  <TableCell>{ATTENDANCE_LABEL[entry.attendance]}</TableCell>
                  <TableCell>{entry.guest_count}</TableCell>
                  <TableCell className="max-w-64 truncate">{entry.message}</TableCell>
                  <TableCell>
                    <Badge variant={entry.is_approved ? "default" : "outline"}>
                      {entry.is_approved ? "Disetujui" : "Menunggu"}
                    </Badge>
                  </TableCell>
                  <TableCell className="space-x-1 text-right">
                    {!entry.is_approved && (
                      <Button variant="outline" size="sm" onClick={() => handleApprove(entry)}>
                        Setujui
                      </Button>
                    )}
                    {entry.is_approved && (
                      <Button variant="outline" size="sm" onClick={() => handleReject(entry)}>
                        Tolak
                      </Button>
                    )}
                    <Button variant="destructive" size="sm" onClick={() => handleDelete(entry)}>
                      Hapus
                    </Button>
                  </TableCell>
                </TableRow>
              ))}
              {entries.length === 0 && (
                <TableRow>
                  <TableCell colSpan={6} className="text-center text-muted-foreground">
                    Belum ada RSVP masuk.
                  </TableCell>
                </TableRow>
              )}
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>
  );
}
