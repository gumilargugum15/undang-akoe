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
}: {
  invitationId: number;
  currentPhoto: string | null;
  onChanged?: () => void;
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
      await customerApi.post(`/invitations/${invitationId}/cover-photo`, form);
      toast.success("Foto sampul berhasil diunggah.");
      setFile(null);
      setPreview(null);
      onChanged?.();
    } catch (err) {
      if (err instanceof CustomerApiError && err.errors) {
        Object.values(err.errors)
          .flat()
          .forEach((msg) => toast.error(msg));
      } else {
        toast.error(
          err instanceof CustomerApiError ? err.message : "Gagal mengunggah foto sampul.",
        );
      }
    } finally {
      setSaving(false);
    }
  }

  async function handleRemove() {
    if (!confirm("Hapus foto sampul ini? Undangan akan kembali memakai tampilan bawaan tema."))
      return;
    try {
      await customerApi.delete(`/invitations/${invitationId}/cover-photo`);
      toast.success("Foto sampul berhasil dihapus.");
      onChanged?.();
    } catch (err) {
      toast.error(err instanceof CustomerApiError ? err.message : "Gagal menghapus foto sampul.");
    }
  }

  const displayImage = preview ?? currentPhoto;

  return (
    <Card>
      <CardHeader>
        <CardTitle>Foto Sampul</CardTitle>
      </CardHeader>
      <CardContent className="space-y-4">
        <p className="text-sm text-muted-foreground">
          Unggah foto yang akan tampil penuh di layar pembuka undangan, menggantikan background
          bawaan tema.
        </p>
        {displayImage ? (
          <img
            src={displayImage}
            alt="Pratinjau foto sampul"
            className="max-h-72 w-full rounded-md object-cover"
          />
        ) : (
          <div className="flex h-40 items-center justify-center rounded-md border border-dashed text-sm text-muted-foreground">
            Belum ada foto sampul — memakai tampilan bawaan tema.
          </div>
        )}
        <Input type="file" accept="image/*" onChange={handleFileChange} />
        <div className="flex gap-2">
          <Button type="button" disabled={!file || saving} onClick={handleUpload}>
            {saving ? "Mengunggah..." : "Unggah Foto Sampul"}
          </Button>
          {currentPhoto && (
            <Button type="button" variant="destructive" onClick={handleRemove}>
              Hapus Foto Sampul
            </Button>
          )}
        </div>
      </CardContent>
    </Card>
  );
}
