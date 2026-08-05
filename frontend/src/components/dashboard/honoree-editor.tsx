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
import { heroTabLabel } from "@/lib/invitation-templates";

interface Honoree {
  id: number;
  role_label: string;
  nickname: string;
  full_name: string;
  parent_name: string | null;
  instagram_handle: string | null;
  photo: string | null;
  description: string | null;
  meta: { age?: string; birth_date?: string } | null;
  sort_order: number;
}

interface FormValues {
  role_label: string;
  nickname: string;
  full_name: string;
  parent_name: string;
  instagram_handle: string;
  description: string;
  age: string;
  birth_date: string;
}

function emptyForm(defaultRoleLabel: string): FormValues {
  return {
    role_label: defaultRoleLabel,
    nickname: "",
    full_name: "",
    parent_name: "",
    instagram_handle: "",
    description: "",
    age: "",
    birth_date: "",
  };
}

function toFormValues(h: Honoree): FormValues {
  return {
    role_label: h.role_label,
    nickname: h.nickname,
    full_name: h.full_name,
    parent_name: h.parent_name ?? "",
    instagram_handle: h.instagram_handle ?? "",
    description: h.description ?? "",
    age: h.meta?.age ?? "",
    birth_date: h.meta?.birth_date ?? "",
  };
}

export function HonoreeEditor({ invitationId, eventCategory }: { invitationId: number; eventCategory: string }) {
  const defaultRoleLabel = heroTabLabel(eventCategory);
  // Birth date is meaningful for both a birthday (the honoree's own birthday) and an
  // aqiqah (a newborn's birth) — every other honoree category skips these extra fields.
  const showsBirthMeta = eventCategory === "birthday" || eventCategory === "aqiqah";

  const [honorees, setHonorees] = useState<Honoree[]>([]);
  const [dialogOpen, setDialogOpen] = useState(false);
  const [editing, setEditing] = useState<Honoree | null>(null);
  const [values, setValues] = useState<FormValues>(emptyForm(defaultRoleLabel));
  const [photo, setPhoto] = useState<File | null>(null);
  const [saving, setSaving] = useState(false);

  async function load() {
    try {
      const res = await customerApi.get<{ data: Honoree[] }>(`/invitations/${invitationId}/honorees`);
      setHonorees(res.data);
    } catch (err) {
      toast.error(err instanceof CustomerApiError ? err.message : "Gagal memuat data.");
    }
  }

  useEffect(() => {
    load();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [invitationId]);

  function openCreate() {
    setEditing(null);
    setValues(emptyForm(defaultRoleLabel));
    setPhoto(null);
    setDialogOpen(true);
  }

  function openEdit(item: Honoree) {
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
    form.append("role_label", values.role_label);
    form.append("nickname", values.nickname);
    form.append("full_name", values.full_name);
    if (values.parent_name) form.append("parent_name", values.parent_name);
    if (values.instagram_handle) form.append("instagram_handle", values.instagram_handle);
    if (values.description) form.append("description", values.description);
    // Bracket notation is multipart/form-data's native array encoding — Laravel parses
    // `meta[age]`/`meta[birth_date]` straight into `$request->meta` as a nested array,
    // unlike a JSON.stringify()'d value which would just arrive as an opaque string.
    if (showsBirthMeta && values.age) form.append("meta[age]", values.age);
    if (showsBirthMeta && values.birth_date) form.append("meta[birth_date]", values.birth_date);
    if (photo) form.append("photo", photo);

    try {
      if (editing) {
        form.append("_method", "PUT");
        await customerApi.post(`/invitations/${invitationId}/honorees/${editing.id}`, form);
        toast.success("Data berhasil diperbarui.");
      } else {
        await customerApi.post(`/invitations/${invitationId}/honorees`, form);
        toast.success("Data berhasil ditambahkan.");
      }
      setDialogOpen(false);
      await load();
    } catch (err) {
      if (err instanceof CustomerApiError && err.errors) {
        Object.values(err.errors).flat().forEach((msg) => toast.error(msg));
      } else {
        toast.error(err instanceof CustomerApiError ? err.message : "Gagal menyimpan data.");
      }
    } finally {
      setSaving(false);
    }
  }

  async function handleDelete(item: Honoree) {
    if (!confirm(`Hapus data "${item.nickname}"?`)) return;
    try {
      await customerApi.delete(`/invitations/${invitationId}/honorees/${item.id}`);
      toast.success("Data berhasil dihapus.");
      await load();
    } catch (err) {
      toast.error(err instanceof CustomerApiError ? err.message : "Gagal menghapus data.");
    }
  }

  return (
    <Card>
      <CardHeader className="flex flex-row items-center justify-between space-y-0">
        <CardTitle>{defaultRoleLabel}</CardTitle>
        <Button onClick={openCreate}>+ Tambah</Button>
      </CardHeader>
      <CardContent>
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Nama Panggilan</TableHead>
              <TableHead>Nama Lengkap</TableHead>
              <TableHead className="text-right">Aksi</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {honorees.map((item) => (
              <TableRow key={item.id}>
                <TableCell className="font-medium">{item.nickname}</TableCell>
                <TableCell>{item.full_name}</TableCell>
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
            {honorees.length === 0 && (
              <TableRow>
                <TableCell colSpan={3} className="text-center text-muted-foreground">
                  Belum ada data.
                </TableCell>
              </TableRow>
            )}
          </TableBody>
        </Table>
      </CardContent>

      <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
        <DialogContent className="max-h-[90vh] overflow-y-auto">
          <DialogHeader>
            <DialogTitle>{editing ? "Edit Data" : "Tambah Data"}</DialogTitle>
          </DialogHeader>
          <form onSubmit={handleSubmit} className="space-y-4">
            <div className="space-y-2">
              <Label>Nama Panggilan</Label>
              <Input required value={values.nickname} onChange={(e) => update("nickname", e.target.value)} />
            </div>
            <div className="space-y-2">
              <Label>Nama Lengkap</Label>
              <Input required value={values.full_name} onChange={(e) => update("full_name", e.target.value)} />
            </div>
            <div className="space-y-2">
              <Label>Nama Orang Tua (opsional)</Label>
              <Input value={values.parent_name} onChange={(e) => update("parent_name", e.target.value)} />
            </div>
            <div className="space-y-2">
              <Label>Instagram (opsional)</Label>
              <Input
                placeholder="@username"
                value={values.instagram_handle}
                onChange={(e) => update("instagram_handle", e.target.value)}
              />
            </div>
            {showsBirthMeta && (
              <div className="grid grid-cols-2 gap-3">
                <div className="space-y-2">
                  <Label>Usia (opsional)</Label>
                  <Input value={values.age} onChange={(e) => update("age", e.target.value)} />
                </div>
                <div className="space-y-2">
                  <Label>Tanggal Lahir (opsional)</Label>
                  <Input type="date" value={values.birth_date} onChange={(e) => update("birth_date", e.target.value)} />
                </div>
              </div>
            )}
            <div className="space-y-2">
              <Label>Deskripsi (opsional)</Label>
              <Textarea rows={3} value={values.description} onChange={(e) => update("description", e.target.value)} />
            </div>
            <div className="space-y-2">
              <Label>Foto (opsional)</Label>
              {editing?.photo && (
                <img src={editing.photo} alt={editing.nickname} className="h-20 w-20 rounded object-cover" />
              )}
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
