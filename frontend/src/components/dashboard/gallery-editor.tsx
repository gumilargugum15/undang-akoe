import { useEffect, useState, type FormEvent } from "react";
import { toast } from "sonner";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
  Dialog,
  DialogContent,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { customerApi, CustomerApiError } from "@/lib/customer-api";

type GalleryType = "photo" | "video_youtube" | "video_mp4";

interface GalleryItem {
  id: number;
  type: GalleryType;
  url: string | null;
  thumbnail: string | null;
  caption: string | null;
  category: string | null;
  sort_order: number;
}

export function GalleryEditor({ invitationId }: { invitationId: number }) {
  const [items, setItems] = useState<GalleryItem[]>([]);
  const [dialogOpen, setDialogOpen] = useState(false);
  const [type, setType] = useState<GalleryType>("photo");
  const [file, setFile] = useState<File | null>(null);
  const [bulkFiles, setBulkFiles] = useState<FileList | null>(null);
  const [externalUrl, setExternalUrl] = useState("");
  const [caption, setCaption] = useState("");
  const [category, setCategory] = useState("");
  const [saving, setSaving] = useState(false);
  const [bulkSaving, setBulkSaving] = useState(false);

  async function load() {
    try {
      const res = await customerApi.get<{ data: GalleryItem[] }>(
        `/invitations/${invitationId}/gallery`,
      );
      setItems(res.data);
    } catch (err) {
      toast.error(err instanceof CustomerApiError ? err.message : "Gagal memuat galeri.");
    }
  }

  useEffect(() => {
    load();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [invitationId]);

  function openCreate() {
    setType("photo");
    setFile(null);
    setExternalUrl("");
    setCaption("");
    setCategory("");
    setDialogOpen(true);
  }

  async function handleSubmit(e: FormEvent) {
    e.preventDefault();
    setSaving(true);

    const form = new FormData();
    form.append("type", type);
    if (type === "video_youtube") {
      form.append("external_url", externalUrl);
    } else if (file) {
      form.append("file", file);
    }
    if (caption) form.append("caption", caption);
    if (category) form.append("category", category);

    try {
      await customerApi.post(`/invitations/${invitationId}/gallery`, form);
      toast.success("Item galeri berhasil ditambahkan.");
      setDialogOpen(false);
      await load();
    } catch (err) {
      if (err instanceof CustomerApiError && err.errors) {
        Object.values(err.errors)
          .flat()
          .forEach((msg) => toast.error(msg));
      } else {
        toast.error(err instanceof CustomerApiError ? err.message : "Gagal menambah item galeri.");
      }
    } finally {
      setSaving(false);
    }
  }

  async function handleBulkUpload() {
    if (!bulkFiles || bulkFiles.length === 0) return;
    setBulkSaving(true);

    const form = new FormData();
    Array.from(bulkFiles).forEach((f) => form.append("photos[]", f));

    try {
      await customerApi.post(`/invitations/${invitationId}/gallery/bulk`, form);
      toast.success(`${bulkFiles.length} foto berhasil diunggah.`);
      setBulkFiles(null);
      await load();
    } catch (err) {
      if (err instanceof CustomerApiError && err.errors) {
        Object.values(err.errors)
          .flat()
          .forEach((msg) => toast.error(msg));
      } else {
        toast.error(err instanceof CustomerApiError ? err.message : "Gagal mengunggah foto.");
      }
    } finally {
      setBulkSaving(false);
    }
  }

  async function handleDelete(item: GalleryItem) {
    if (!confirm("Hapus item galeri ini?")) return;
    try {
      await customerApi.delete(`/invitations/${invitationId}/gallery/${item.id}`);
      toast.success("Item galeri berhasil dihapus.");
      await load();
    } catch (err) {
      toast.error(err instanceof CustomerApiError ? err.message : "Gagal menghapus item galeri.");
    }
  }

  return (
    <Card>
      <CardHeader className="flex flex-row items-center justify-between space-y-0">
        <CardTitle>Galeri</CardTitle>
        <div className="flex items-center gap-2">
          <Input
            type="file"
            accept="image/*"
            multiple
            className="max-w-52"
            onChange={(e) => setBulkFiles(e.target.files)}
          />
          <Button variant="outline" onClick={handleBulkUpload} disabled={!bulkFiles || bulkSaving}>
            {bulkSaving ? "Mengunggah..." : "Unggah Banyak Foto"}
          </Button>
          <Button onClick={openCreate}>+ Tambah Item</Button>
        </div>
      </CardHeader>
      <CardContent>
        <div className="grid grid-cols-2 gap-3 sm:grid-cols-4">
          {items.map((item) => (
            <div key={item.id} className="space-y-1">
              <div className="relative aspect-square overflow-hidden rounded-md bg-muted">
                {item.type === "photo" && item.url && (
                  <img src={item.url} alt={item.caption ?? ""} className="size-full object-cover" />
                )}
                {item.type === "video_mp4" && item.url && (
                  <video src={item.url} className="size-full object-cover" muted />
                )}
                {item.type === "video_youtube" && (
                  <div className="flex size-full items-center justify-center bg-black/80 text-xs text-white">
                    YouTube
                  </div>
                )}
              </div>
              <p className="truncate text-xs text-muted-foreground">{item.caption || "—"}</p>
              <Button
                variant="destructive"
                size="sm"
                className="w-full"
                onClick={() => handleDelete(item)}
              >
                Hapus
              </Button>
            </div>
          ))}
          {items.length === 0 && (
            <p className="col-span-4 text-center text-sm text-muted-foreground">
              Belum ada item galeri.
            </p>
          )}
        </div>
      </CardContent>

      <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
        <DialogContent className="max-h-[90vh] overflow-y-auto">
          <DialogHeader>
            <DialogTitle>Tambah Item Galeri</DialogTitle>
          </DialogHeader>
          <form onSubmit={handleSubmit} className="space-y-4">
            <div className="space-y-2">
              <Label>Tipe</Label>
              <Select value={type} onValueChange={(v) => setType(v as GalleryType)}>
                <SelectTrigger>
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="photo">Foto</SelectItem>
                  <SelectItem value="video_mp4">Video (unggah file)</SelectItem>
                  <SelectItem value="video_youtube">Video YouTube (tautan)</SelectItem>
                </SelectContent>
              </Select>
            </div>
            {type === "video_youtube" ? (
              <div className="space-y-2">
                <Label>Tautan YouTube</Label>
                <Input
                  type="url"
                  required
                  placeholder="https://www.youtube.com/watch?v=..."
                  value={externalUrl}
                  onChange={(e) => setExternalUrl(e.target.value)}
                />
              </div>
            ) : (
              <div className="space-y-2">
                <Label>File</Label>
                <Input
                  type="file"
                  required
                  accept={type === "photo" ? "image/*" : "video/mp4,video/quicktime"}
                  onChange={(e) => setFile(e.target.files?.[0] ?? null)}
                />
              </div>
            )}
            <div className="space-y-2">
              <Label>Keterangan (opsional)</Label>
              <Input value={caption} onChange={(e) => setCaption(e.target.value)} />
            </div>
            <div className="space-y-2">
              <Label>Kategori (opsional)</Label>
              <Input
                placeholder="prewedding, venue, dst."
                value={category}
                onChange={(e) => setCategory(e.target.value)}
              />
            </div>
            <DialogFooter>
              <Button type="submit" disabled={saving}>
                {saving ? "Mengunggah..." : "Simpan"}
              </Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>
    </Card>
  );
}
