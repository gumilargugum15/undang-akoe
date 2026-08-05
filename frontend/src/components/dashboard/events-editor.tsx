import { useEffect, useState, type FormEvent } from "react";
import { toast } from "sonner";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
  Dialog,
  DialogContent,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { customerApi, CustomerApiError } from "@/lib/customer-api";

interface EventItem {
  id: number;
  name: string;
  event_date: string;
  start_time: string | null;
  end_time: string | null;
  location_name: string | null;
  address: string | null;
  gmaps_url: string | null;
  notes: string | null;
  sort_order: number;
}

interface FormValues {
  name: string;
  event_date: string;
  start_time: string;
  end_time: string;
  location_name: string;
  address: string;
  gmaps_url: string;
  notes: string;
  sort_order: string;
}

const EMPTY_FORM: FormValues = {
  name: "",
  event_date: "",
  start_time: "",
  end_time: "",
  location_name: "",
  address: "",
  gmaps_url: "",
  notes: "",
  sort_order: "0",
};

function toFormValues(e: EventItem): FormValues {
  return {
    name: e.name,
    event_date: e.event_date,
    start_time: e.start_time ?? "",
    end_time: e.end_time ?? "",
    location_name: e.location_name ?? "",
    address: e.address ?? "",
    gmaps_url: e.gmaps_url ?? "",
    notes: e.notes ?? "",
    sort_order: String(e.sort_order),
  };
}

export function EventsEditor({ invitationId }: { invitationId: number }) {
  const [events, setEvents] = useState<EventItem[]>([]);
  const [dialogOpen, setDialogOpen] = useState(false);
  const [editing, setEditing] = useState<EventItem | null>(null);
  const [values, setValues] = useState<FormValues>(EMPTY_FORM);
  const [saving, setSaving] = useState(false);

  async function load() {
    try {
      const res = await customerApi.get<{ data: EventItem[] }>(`/invitations/${invitationId}/events`);
      setEvents(res.data);
    } catch (err) {
      toast.error(err instanceof CustomerApiError ? err.message : "Gagal memuat acara.");
    }
  }

  useEffect(() => {
    load();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [invitationId]);

  function openCreate() {
    setEditing(null);
    setValues(EMPTY_FORM);
    setDialogOpen(true);
  }

  function openEdit(item: EventItem) {
    setEditing(item);
    setValues(toFormValues(item));
    setDialogOpen(true);
  }

  function update<K extends keyof FormValues>(key: K, value: FormValues[K]) {
    setValues((v) => ({ ...v, [key]: value }));
  }

  async function handleSubmit(e: FormEvent) {
    e.preventDefault();
    setSaving(true);

    const payload = {
      name: values.name,
      event_date: values.event_date,
      start_time: values.start_time || null,
      end_time: values.end_time || null,
      location_name: values.location_name || null,
      address: values.address || null,
      gmaps_url: values.gmaps_url || null,
      notes: values.notes || null,
      sort_order: Number(values.sort_order || 0),
    };

    try {
      if (editing) {
        await customerApi.put(`/invitations/${invitationId}/events/${editing.id}`, payload);
        toast.success("Acara berhasil diperbarui.");
      } else {
        await customerApi.post(`/invitations/${invitationId}/events`, payload);
        toast.success("Acara berhasil ditambahkan.");
      }
      setDialogOpen(false);
      await load();
    } catch (err) {
      if (err instanceof CustomerApiError && err.errors) {
        Object.values(err.errors).flat().forEach((msg) => toast.error(msg));
      } else {
        toast.error(err instanceof CustomerApiError ? err.message : "Gagal menyimpan acara.");
      }
    } finally {
      setSaving(false);
    }
  }

  async function handleDelete(item: EventItem) {
    if (!confirm(`Hapus acara "${item.name}"?`)) return;
    try {
      await customerApi.delete(`/invitations/${invitationId}/events/${item.id}`);
      toast.success("Acara berhasil dihapus.");
      await load();
    } catch (err) {
      toast.error(err instanceof CustomerApiError ? err.message : "Gagal menghapus acara.");
    }
  }

  return (
    <Card>
      <CardHeader className="flex flex-row items-center justify-between space-y-0">
        <CardTitle>Acara</CardTitle>
        <Button onClick={openCreate}>+ Tambah Acara</Button>
      </CardHeader>
      <CardContent>
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Nama</TableHead>
              <TableHead>Tanggal</TableHead>
              <TableHead>Jam</TableHead>
              <TableHead>Lokasi</TableHead>
              <TableHead className="text-right">Aksi</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {events.map((item) => (
              <TableRow key={item.id}>
                <TableCell className="font-medium">{item.name}</TableCell>
                <TableCell>{item.event_date}</TableCell>
                <TableCell>
                  {item.start_time ?? "—"}
                  {item.end_time ? ` – ${item.end_time}` : ""}
                </TableCell>
                <TableCell>{item.location_name ?? "—"}</TableCell>
                <TableCell className="space-x-1 text-right">
                  <Button variant="outline" size="sm" onClick={() => openEdit(item)}>
                    Edit
                  </Button>
                  <Button variant="destructive" size="sm" onClick={() => handleDelete(item)}>
                    Hapus
                  </Button>
                </TableCell>
              </TableRow>
            ))}
            {events.length === 0 && (
              <TableRow>
                <TableCell colSpan={5} className="text-center text-muted-foreground">
                  Belum ada acara.
                </TableCell>
              </TableRow>
            )}
          </TableBody>
        </Table>
      </CardContent>

      <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
        <DialogContent className="max-h-[90vh] overflow-y-auto">
          <DialogHeader>
            <DialogTitle>{editing ? "Edit Acara" : "Tambah Acara"}</DialogTitle>
          </DialogHeader>
          <form onSubmit={handleSubmit} className="space-y-4">
            <div className="space-y-2">
              <Label>Nama Acara</Label>
              <Input
                required
                placeholder="Akad Nikah, Resepsi, dst."
                value={values.name}
                onChange={(e) => update("name", e.target.value)}
              />
            </div>
            <div className="grid grid-cols-3 gap-4">
              <div className="space-y-2">
                <Label>Tanggal</Label>
                <Input
                  type="date"
                  required
                  value={values.event_date}
                  onChange={(e) => update("event_date", e.target.value)}
                />
              </div>
              <div className="space-y-2">
                <Label>Jam Mulai</Label>
                <Input type="time" value={values.start_time} onChange={(e) => update("start_time", e.target.value)} />
              </div>
              <div className="space-y-2">
                <Label>Jam Selesai</Label>
                <Input type="time" value={values.end_time} onChange={(e) => update("end_time", e.target.value)} />
              </div>
            </div>
            <div className="space-y-2">
              <Label>Nama Lokasi</Label>
              <Input value={values.location_name} onChange={(e) => update("location_name", e.target.value)} />
            </div>
            <div className="space-y-2">
              <Label>Alamat</Label>
              <Textarea rows={2} value={values.address} onChange={(e) => update("address", e.target.value)} />
            </div>
            <div className="space-y-2">
              <Label>Tautan Google Maps (opsional)</Label>
              <Input
                type="url"
                placeholder="https://www.google.com/maps?q=...&output=embed"
                value={values.gmaps_url}
                onChange={(e) => update("gmaps_url", e.target.value)}
              />
            </div>
            <div className="space-y-2">
              <Label>Catatan (opsional)</Label>
              <Textarea rows={2} value={values.notes} onChange={(e) => update("notes", e.target.value)} />
            </div>
            <DialogFooter>
              <Button type="submit" disabled={saving}>
                {saving ? "Menyimpan..." : "Simpan"}
              </Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>
    </Card>
  );
}
