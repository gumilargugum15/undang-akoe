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

interface Story {
  id: number;
  title: string;
  story_date: string | null;
  description: string | null;
  photo: string | null;
  sort_order: number;
}

interface FormValues {
  title: string;
  story_date: string;
  description: string;
  sort_order: string;
}

const EMPTY_FORM: FormValues = { title: "", story_date: "", description: "", sort_order: "0" };

function toFormValues(s: Story): FormValues {
  return {
    title: s.title,
    story_date: s.story_date ?? "",
    description: s.description ?? "",
    sort_order: String(s.sort_order),
  };
}

export function LoveStoryEditor({ invitationId }: { invitationId: number }) {
  const [stories, setStories] = useState<Story[]>([]);
  const [dialogOpen, setDialogOpen] = useState(false);
  const [editing, setEditing] = useState<Story | null>(null);
  const [values, setValues] = useState<FormValues>(EMPTY_FORM);
  const [photo, setPhoto] = useState<File | null>(null);
  const [saving, setSaving] = useState(false);

  async function load() {
    try {
      const res = await customerApi.get<{ data: Story[] }>(`/invitations/${invitationId}/love-stories`);
      setStories(res.data);
    } catch (err) {
      toast.error(err instanceof CustomerApiError ? err.message : "Gagal memuat cerita cinta.");
    }
  }

  useEffect(() => {
    load();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [invitationId]);

  function openCreate() {
    setEditing(null);
    setValues(EMPTY_FORM);
    setPhoto(null);
    setDialogOpen(true);
  }

  function openEdit(item: Story) {
    setEditing(item);
    setValues(toFormValues(item));
    setPhoto(null);
    setDialogOpen(true);
  }

  function update<K extends keyof FormValues>(key: K, value: FormValues[K]) {
    setValues((v) => ({ ...v, [key]: value }));
  }

  async function handleSubmit(e: FormEvent) {
    e.preventDefault();
    setSaving(true);

    const form = new FormData();
    form.append("title", values.title);
    if (values.story_date) form.append("story_date", values.story_date);
    if (values.description) form.append("description", values.description);
    form.append("sort_order", values.sort_order || "0");
    if (photo) form.append("photo", photo);

    try {
      if (editing) {
        form.append("_method", "PUT");
        await customerApi.post(`/invitations/${invitationId}/love-stories/${editing.id}`, form);
        toast.success("Cerita cinta berhasil diperbarui.");
      } else {
        await customerApi.post(`/invitations/${invitationId}/love-stories`, form);
        toast.success("Cerita cinta berhasil ditambahkan.");
      }
      setDialogOpen(false);
      await load();
    } catch (err) {
      if (err instanceof CustomerApiError && err.errors) {
        Object.values(err.errors).flat().forEach((msg) => toast.error(msg));
      } else {
        toast.error(err instanceof CustomerApiError ? err.message : "Gagal menyimpan cerita cinta.");
      }
    } finally {
      setSaving(false);
    }
  }

  async function handleDelete(item: Story) {
    if (!confirm(`Hapus cerita "${item.title}"?`)) return;
    try {
      await customerApi.delete(`/invitations/${invitationId}/love-stories/${item.id}`);
      toast.success("Cerita cinta berhasil dihapus.");
      await load();
    } catch (err) {
      toast.error(err instanceof CustomerApiError ? err.message : "Gagal menghapus cerita cinta.");
    }
  }

  return (
    <Card>
      <CardHeader className="flex flex-row items-center justify-between space-y-0">
        <CardTitle>Cerita Cinta</CardTitle>
        <Button onClick={openCreate}>+ Tambah Cerita</Button>
      </CardHeader>
      <CardContent>
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Judul</TableHead>
              <TableHead>Tanggal</TableHead>
              <TableHead className="text-right">Aksi</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {stories.map((item) => (
              <TableRow key={item.id}>
                <TableCell className="font-medium">{item.title}</TableCell>
                <TableCell>{item.story_date ?? "—"}</TableCell>
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
            {stories.length === 0 && (
              <TableRow>
                <TableCell colSpan={3} className="text-center text-muted-foreground">
                  Belum ada cerita cinta.
                </TableCell>
              </TableRow>
            )}
          </TableBody>
        </Table>
      </CardContent>

      <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
        <DialogContent className="max-h-[90vh] overflow-y-auto">
          <DialogHeader>
            <DialogTitle>{editing ? "Edit Cerita" : "Tambah Cerita"}</DialogTitle>
          </DialogHeader>
          <form onSubmit={handleSubmit} className="space-y-4">
            <div className="space-y-2">
              <Label>Judul</Label>
              <Input
                required
                placeholder="Pertama Bertemu, Lamaran, dst."
                value={values.title}
                onChange={(e) => update("title", e.target.value)}
              />
            </div>
            <div className="space-y-2">
              <Label>Tanggal (opsional)</Label>
              <Input type="date" value={values.story_date} onChange={(e) => update("story_date", e.target.value)} />
            </div>
            <div className="space-y-2">
              <Label>Cerita</Label>
              <Textarea rows={4} value={values.description} onChange={(e) => update("description", e.target.value)} />
            </div>
            <div className="space-y-2">
              <Label>Foto (opsional)</Label>
              {editing?.photo && <img src={editing.photo} alt={editing.title} className="h-20 w-20 rounded object-cover" />}
              <Input type="file" accept="image/*" onChange={(e) => setPhoto(e.target.files?.[0] ?? null)} />
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
