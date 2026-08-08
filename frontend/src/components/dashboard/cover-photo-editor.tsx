import { useState, type ChangeEvent } from "react";
import { toast } from "sonner";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { customerApi, CustomerApiError } from "@/lib/customer-api";

export function CoverPhotoEditor({
  invitationId,
  currentPhoto,
  onChanged,
  endpoint = "cover-photo",
  title = "Foto Sampul",
  description = "Unggah foto yang akan tampil penuh di layar pembuka undangan, menggantikan background bawaan tema.",
  emptyHint = "Belum ada foto sampul — memakai tampilan bawaan tema.",
  uploadLabel = "Unggah Foto Sampul",
  removeLabel = "Hapus Foto Sampul",
  removeConfirm = "Hapus foto sampul ini? Undangan akan kembali memakai tampilan bawaan tema.",
}: {
  invitationId: number;
  currentPhoto: string | null;
  onChanged?: () => void;
  endpoint?: string;
  title?: string;
  description?: string;
  emptyHint?: string;
  uploadLabel?: string;
  removeLabel?: string;
  removeConfirm?: string;
}) {
  const [file, setFile] = useState<File | null>(null);
  const [preview, setPreview] = useState<string | null>(null);
  const [saving, setSaving] = useState(false);

  function handleFileChange(e: ChangeEvent<HTMLInputElement>) {
    const selected = e.target.files?.[0] ?? null;
    setFile(selected);
    setPreview(selected ? URL.createObjectURL(selected) : null);
  }

  async function handleUpload() {
    if (!file) return;
    setSaving(true);
    const form = new FormData();
    form.append("photo", file);

    try {
      await customerApi.post(`/invitations/${invitationId}/${endpoint}`, form);
      toast.success("Foto berhasil diunggah.");
      setFile(null);
      setPreview(null);
      onChanged?.();
    } catch (err) {
      if (err instanceof CustomerApiError && err.errors) {
        Object.values(err.errors)
          .flat()
          .forEach((msg) => toast.error(msg));
      } else {
        toast.error(err instanceof CustomerApiError ? err.message : "Gagal mengunggah foto.");
      }
    } finally {
      setSaving(false);
    }
  }

  async function handleRemove() {
    if (!confirm(removeConfirm)) return;
    try {
      await customerApi.delete(`/invitations/${invitationId}/${endpoint}`);
      toast.success("Foto berhasil dihapus.");
      onChanged?.();
    } catch (err) {
      toast.error(err instanceof CustomerApiError ? err.message : "Gagal menghapus foto.");
    }
  }

  const displayImage = preview ?? currentPhoto;

  return (
    <Card>
      <CardHeader>
        <CardTitle>{title}</CardTitle>
      </CardHeader>
      <CardContent className="space-y-4">
        <p className="text-sm text-muted-foreground">{description}</p>
        {displayImage ? (
          <img
            src={displayImage}
            alt={`Pratinjau ${title.toLowerCase()}`}
            className="max-h-72 w-full rounded-md object-cover"
          />
        ) : (
          <div className="flex h-40 items-center justify-center rounded-md border border-dashed text-sm text-muted-foreground">
            {emptyHint}
          </div>
        )}
        <Input type="file" accept="image/*" onChange={handleFileChange} />
        <div className="flex gap-2">
          <Button type="button" disabled={!file || saving} onClick={handleUpload}>
            {saving ? "Mengunggah..." : uploadLabel}
          </Button>
          {currentPhoto && (
            <Button type="button" variant="destructive" onClick={handleRemove}>
              {removeLabel}
            </Button>
          )}
        </div>
      </CardContent>
    </Card>
  );
}
