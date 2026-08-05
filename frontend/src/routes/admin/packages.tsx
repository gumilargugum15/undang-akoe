import { createFileRoute } from "@tanstack/react-router";
import { useEffect, useState, type FormEvent } from "react";
import { toast } from "sonner";
import { AdminShell } from "@/components/admin/admin-shell";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Switch } from "@/components/ui/switch";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
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

export const Route = createFileRoute("/admin/packages")({
  component: AdminPackagesPage,
});

interface Package {
  id: number;
  name: string;
  description: string | null;
  price: number;
  duration_days: number | null;
  max_photos: number | null;
  max_guests: number | null;
  features: string[];
  is_active: boolean;
  sort_order: number;
}

interface FormValues {
  name: string;
  description: string;
  price: string;
  duration_days: string;
  max_photos: string;
  max_guests: string;
  features: string;
  is_active: boolean;
  sort_order: string;
}

const EMPTY_FORM: FormValues = {
  name: "",
  description: "",
  price: "0",
  duration_days: "",
  max_photos: "",
  max_guests: "",
  features: "",
  is_active: true,
  sort_order: "0",
};

function toFormValues(pkg: Package): FormValues {
  return {
    name: pkg.name,
    description: pkg.description ?? "",
    price: String(pkg.price),
    duration_days: pkg.duration_days !== null ? String(pkg.duration_days) : "",
    max_photos: pkg.max_photos !== null ? String(pkg.max_photos) : "",
    max_guests: pkg.max_guests !== null ? String(pkg.max_guests) : "",
    features: pkg.features.join(", "),
    is_active: pkg.is_active,
    sort_order: String(pkg.sort_order),
  };
}

function formatRupiah(value: number): string {
  return value === 0 ? "Gratis" : `Rp${value.toLocaleString("id-ID")}`;
}

function AdminPackagesPage() {
  const { session, ready } = useRequireAdmin();
  const [packages, setPackages] = useState<Package[]>([]);
  const [loading, setLoading] = useState(false);
  const [dialogOpen, setDialogOpen] = useState(false);
  const [editing, setEditing] = useState<Package | null>(null);
  const [values, setValues] = useState<FormValues>(EMPTY_FORM);
  const [saving, setSaving] = useState(false);

  async function loadPackages() {
    setLoading(true);
    try {
      const res = await adminApi.get<{ data: Package[] }>("/packages");
      setPackages(res.data);
    } catch (err) {
      toast.error(err instanceof AdminApiError ? err.message : "Gagal memuat daftar paket.");
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    if (ready) loadPackages();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [ready]);

  if (!ready || !session) return null;

  function openCreate() {
    setEditing(null);
    setValues(EMPTY_FORM);
    setDialogOpen(true);
  }

  function openEdit(pkg: Package) {
    setEditing(pkg);
    setValues(toFormValues(pkg));
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
      description: values.description || null,
      price: Number(values.price || 0),
      duration_days: values.duration_days ? Number(values.duration_days) : null,
      max_photos: values.max_photos ? Number(values.max_photos) : null,
      max_guests: values.max_guests ? Number(values.max_guests) : null,
      features: values.features
        .split(",")
        .map((f) => f.trim())
        .filter(Boolean),
      is_active: values.is_active,
      sort_order: Number(values.sort_order || 0),
    };

    try {
      if (editing) {
        await adminApi.put(`/packages/${editing.id}`, payload);
        toast.success("Paket berhasil diperbarui.");
      } else {
        await adminApi.post("/packages", payload);
        toast.success("Paket berhasil ditambahkan.");
      }
      setDialogOpen(false);
      await loadPackages();
    } catch (err) {
      if (err instanceof AdminApiError && err.errors) {
        Object.values(err.errors)
          .flat()
          .forEach((msg) => toast.error(msg));
      } else {
        toast.error(err instanceof AdminApiError ? err.message : "Gagal menyimpan paket.");
      }
    } finally {
      setSaving(false);
    }
  }

  async function handleDelete(pkg: Package) {
    if (!confirm(`Hapus paket "${pkg.name}"?`)) return;
    try {
      await adminApi.delete(`/packages/${pkg.id}`);
      toast.success("Paket berhasil dihapus.");
      await loadPackages();
    } catch (err) {
      toast.error(err instanceof AdminApiError ? err.message : "Gagal menghapus paket.");
    }
  }

  return (
    <AdminShell user={session.user}>
      <Card>
        <CardHeader className="flex flex-row items-center justify-between space-y-0">
          <CardTitle>Paket</CardTitle>
          <Button onClick={openCreate}>+ Tambah Paket</Button>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Nama</TableHead>
                <TableHead>Harga</TableHead>
                <TableHead>Durasi</TableHead>
                <TableHead>Batas</TableHead>
                <TableHead>Fitur</TableHead>
                <TableHead>Aktif</TableHead>
                <TableHead className="text-right">Aksi</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {packages.map((pkg) => (
                <TableRow key={pkg.id}>
                  <TableCell className="font-medium">{pkg.name}</TableCell>
                  <TableCell>{formatRupiah(pkg.price)}</TableCell>
                  <TableCell>{pkg.duration_days ? `${pkg.duration_days} hari` : "Seumur hidup"}</TableCell>
                  <TableCell className="text-xs text-muted-foreground">
                    {pkg.max_photos ?? "∞"} foto · {pkg.max_guests ?? "∞"} tamu
                  </TableCell>
                  <TableCell className="max-w-48">
                    <div className="flex flex-wrap gap-1">
                      {pkg.features.map((f) => (
                        <Badge key={f} variant="secondary" className="text-[10px]">
                          {f}
                        </Badge>
                      ))}
                    </div>
                  </TableCell>
                  <TableCell>{pkg.is_active ? "Ya" : "Tidak"}</TableCell>
                  <TableCell className="space-x-1 text-right">
                    <Button variant="outline" size="sm" onClick={() => openEdit(pkg)}>
                      Edit
                    </Button>
                    <Button variant="destructive" size="sm" onClick={() => handleDelete(pkg)}>
                      Hapus
                    </Button>
                  </TableCell>
                </TableRow>
              ))}
              {packages.length === 0 && !loading && (
                <TableRow>
                  <TableCell colSpan={7} className="text-center text-muted-foreground">
                    Belum ada paket.
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
            <DialogTitle>{editing ? "Edit Paket" : "Tambah Paket"}</DialogTitle>
          </DialogHeader>
          <form onSubmit={handleSubmit} className="space-y-4">
            <div className="space-y-2">
              <Label>Nama Paket</Label>
              <Input required value={values.name} onChange={(e) => update("name", e.target.value)} />
            </div>
            <div className="space-y-2">
              <Label>Deskripsi</Label>
              <Textarea
                rows={2}
                value={values.description}
                onChange={(e) => update("description", e.target.value)}
              />
            </div>
            <div className="grid grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label>Harga (Rp)</Label>
                <Input
                  type="number"
                  min={0}
                  required
                  value={values.price}
                  onChange={(e) => update("price", e.target.value)}
                />
              </div>
              <div className="space-y-2">
                <Label>Durasi (hari, kosong = seumur hidup)</Label>
                <Input
                  type="number"
                  min={1}
                  value={values.duration_days}
                  onChange={(e) => update("duration_days", e.target.value)}
                />
              </div>
              <div className="space-y-2">
                <Label>Maks Foto (kosong = tanpa batas)</Label>
                <Input
                  type="number"
                  min={1}
                  value={values.max_photos}
                  onChange={(e) => update("max_photos", e.target.value)}
                />
              </div>
              <div className="space-y-2">
                <Label>Maks Tamu (kosong = tanpa batas)</Label>
                <Input
                  type="number"
                  min={1}
                  value={values.max_guests}
                  onChange={(e) => update("max_guests", e.target.value)}
                />
              </div>
            </div>
            <div className="space-y-2">
              <Label>Fitur (pisahkan dengan koma)</Label>
              <Input
                placeholder="rsvp, buku_tamu, amplop_digital"
                value={values.features}
                onChange={(e) => update("features", e.target.value)}
              />
            </div>
            <div className="flex items-center justify-between rounded-md border p-3">
              <Label htmlFor="pkg_active">Aktif</Label>
              <Switch
                id="pkg_active"
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
