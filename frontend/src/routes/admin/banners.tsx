import { createFileRoute } from "@tanstack/react-router";
import { useEffect, useState, type FormEvent } from "react";
import { toast } from "sonner";
import { AdminShell } from "@/components/admin/admin-shell";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Switch } from "@/components/ui/switch";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import {
  Dialog,
  DialogContent,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { adminApi, AdminApiError } from "@/lib/admin-api";
import { useRequireAdmin } from "@/lib/admin-auth";

export const Route = createFileRoute("/admin/banners")({
  component: AdminBannersPage,
});

const POSITIONS = ["home_hero", "home_secondary", "sidebar"] as const;

interface Banner {
  id: number;
  title: string;
  image: string | null;
  link_url: string | null;
  position: string;
  sort_order: number;
  is_active: boolean;
  starts_at: string | null;
  ends_at: string | null;
}

interface FormValues {
  title: string;
  link_url: string;
  position: string;
  sort_order: string;
  is_active: boolean;
  starts_at: string;
  ends_at: string;
}

const EMPTY_FORM: FormValues = {
  title: "",
  link_url: "",
  position: "home_hero",
  sort_order: "0",
  is_active: true,
  starts_at: "",
  ends_at: "",
};

function toDateInput(iso: string | null): string {
  return iso ? iso.slice(0, 10) : "";
}

function toFormValues(banner: Banner): FormValues {
  return {
    title: banner.title,
    link_url: banner.link_url ?? "",
    position: banner.position,
    sort_order: String(banner.sort_order),
    is_active: banner.is_active,
    starts_at: toDateInput(banner.starts_at),
    ends_at: toDateInput(banner.ends_at),
  };
}

function AdminBannersPage() {
  const { session, ready } = useRequireAdmin();
  const [banners, setBanners] = useState<Banner[]>([]);
  const [loading, setLoading] = useState(false);
  const [dialogOpen, setDialogOpen] = useState(false);
  const [editing, setEditing] = useState<Banner | null>(null);
  const [values, setValues] = useState<FormValues>(EMPTY_FORM);
  const [image, setImage] = useState<File | null>(null);
  const [saving, setSaving] = useState(false);

  async function loadBanners() {
    setLoading(true);
    try {
      const res = await adminApi.get<{ data: Banner[] }>("/banners");
      setBanners(res.data);
    } catch (err) {
      toast.error(err instanceof AdminApiError ? err.message : "Gagal memuat daftar banner.");
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    if (ready) loadBanners();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [ready]);

  if (!ready || !session) return null;

  function openCreate() {
    setEditing(null);
    setValues(EMPTY_FORM);
    setImage(null);
    setDialogOpen(true);
  }

  function openEdit(banner: Banner) {
    setEditing(banner);
    setValues(toFormValues(banner));
    setImage(null);
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
    if (values.link_url) form.append("link_url", values.link_url);
    form.append("position", values.position);
    form.append("sort_order", values.sort_order || "0");
    form.append("is_active", values.is_active ? "1" : "0");
    if (values.starts_at) form.append("starts_at", values.starts_at);
    if (values.ends_at) form.append("ends_at", values.ends_at);
    if (image) form.append("image", image);

    try {
      if (editing) {
        await adminApi.postFormAsPut(`/banners/${editing.id}`, form);
        toast.success("Banner berhasil diperbarui.");
      } else {
        await adminApi.post("/banners", form);
        toast.success("Banner berhasil ditambahkan.");
      }
      setDialogOpen(false);
      await loadBanners();
    } catch (err) {
      if (err instanceof AdminApiError && err.errors) {
        Object.values(err.errors)
          .flat()
          .forEach((msg) => toast.error(msg));
      } else {
        toast.error(err instanceof AdminApiError ? err.message : "Gagal menyimpan banner.");
      }
    } finally {
      setSaving(false);
    }
  }

  async function handleDelete(banner: Banner) {
    if (!confirm(`Hapus banner "${banner.title}"?`)) return;
    try {
      await adminApi.delete(`/banners/${banner.id}`);
      toast.success("Banner berhasil dihapus.");
      await loadBanners();
    } catch (err) {
      toast.error(err instanceof AdminApiError ? err.message : "Gagal menghapus banner.");
    }
  }

  return (
    <AdminShell user={session.user}>
      <Card>
        <CardHeader className="flex flex-row items-center justify-between space-y-0">
          <CardTitle>Banner</CardTitle>
          <Button onClick={openCreate}>+ Tambah Banner</Button>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Gambar</TableHead>
                <TableHead>Judul</TableHead>
                <TableHead>Posisi</TableHead>
                <TableHead>Jadwal</TableHead>
                <TableHead>Aktif</TableHead>
                <TableHead className="text-right">Aksi</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {banners.map((banner) => (
                <TableRow key={banner.id}>
                  <TableCell>
                    {banner.image && (
                      <img src={banner.image} alt={banner.title} className="h-10 w-16 rounded object-cover" />
                    )}
                  </TableCell>
                  <TableCell className="font-medium">{banner.title}</TableCell>
                  <TableCell>{banner.position}</TableCell>
                  <TableCell className="text-xs text-muted-foreground">
                    {banner.starts_at || banner.ends_at
                      ? `${toDateInput(banner.starts_at) || "—"} s/d ${toDateInput(banner.ends_at) || "—"}`
                      : "Selalu tampil"}
                  </TableCell>
                  <TableCell>
                    <Badge variant={banner.is_active ? "default" : "outline"}>
                      {banner.is_active ? "Ya" : "Tidak"}
                    </Badge>
                  </TableCell>
                  <TableCell className="space-x-1 text-right">
                    <Button variant="outline" size="sm" onClick={() => openEdit(banner)}>
                      Edit
                    </Button>
                    <Button variant="destructive" size="sm" onClick={() => handleDelete(banner)}>
                      Hapus
                    </Button>
                  </TableCell>
                </TableRow>
              ))}
              {banners.length === 0 && !loading && (
                <TableRow>
                  <TableCell colSpan={6} className="text-center text-muted-foreground">
                    Belum ada banner.
                  </TableCell>
                </TableRow>
              )}
            </TableBody>
          </Table>
        </CardContent>
      </Card>

      <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
        <DialogContent className="max-h-[90vh] overflow-y-auto">
          <DialogHeader>
            <DialogTitle>{editing ? "Edit Banner" : "Tambah Banner"}</DialogTitle>
          </DialogHeader>
          <form onSubmit={handleSubmit} className="space-y-4">
            <div className="space-y-2">
              <Label>Judul</Label>
              <Input required value={values.title} onChange={(e) => update("title", e.target.value)} />
            </div>
            <div className="space-y-2">
              <Label>Gambar {editing && "(kosongkan jika tidak diganti)"}</Label>
              {editing?.image && (
                <img src={editing.image} alt={editing.title} className="h-16 w-28 rounded object-cover" />
              )}
              <Input
                type="file"
                accept="image/*"
                required={!editing}
                onChange={(e) => setImage(e.target.files?.[0] ?? null)}
              />
            </div>
            <div className="space-y-2">
              <Label>Tautan (opsional)</Label>
              <Input
                type="url"
                placeholder="https://..."
                value={values.link_url}
                onChange={(e) => update("link_url", e.target.value)}
              />
            </div>
            <div className="grid grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label>Posisi</Label>
                <Select value={values.position} onValueChange={(v) => update("position", v)}>
                  <SelectTrigger>
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    {POSITIONS.map((p) => (
                      <SelectItem key={p} value={p}>
                        {p}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
              <div className="space-y-2">
                <Label>Urutan Tampil</Label>
                <Input
                  type="number"
                  min={0}
                  value={values.sort_order}
                  onChange={(e) => update("sort_order", e.target.value)}
                />
              </div>
              <div className="space-y-2">
                <Label>Mulai Tampil (opsional)</Label>
                <Input type="date" value={values.starts_at} onChange={(e) => update("starts_at", e.target.value)} />
              </div>
              <div className="space-y-2">
                <Label>Berakhir (opsional)</Label>
                <Input type="date" value={values.ends_at} onChange={(e) => update("ends_at", e.target.value)} />
              </div>
            </div>
            <div className="flex items-center justify-between rounded-md border p-3">
              <Label htmlFor="banner_active">Aktif</Label>
              <Switch
                id="banner_active"
                checked={values.is_active}
                onCheckedChange={(v) => update("is_active", v)}
              />
            </div>
            <DialogFooter>
              <Button type="submit" disabled={saving}>
                {saving ? "Menyimpan..." : "Simpan"}
              </Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>
    </AdminShell>
  );
}
