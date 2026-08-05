import { useEffect, useState, type FormEvent } from "react";
import { toast } from "sonner";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Switch } from "@/components/ui/switch";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { customerApi, CustomerApiError } from "@/lib/customer-api";

type Source = "upload" | "spotify" | "youtube_music";

interface MusicData {
  source: Source;
  title: string | null;
  artist: string | null;
  url: string | null;
  autoplay: boolean;
  is_loop: boolean;
  is_active: boolean;
}

export function MusicEditor({ invitationId }: { invitationId: number }) {
  const [music, setMusic] = useState<MusicData | null>(null);
  const [loaded, setLoaded] = useState(false);
  const [source, setSource] = useState<Source>("upload");
  const [title, setTitle] = useState("");
  const [artist, setArtist] = useState("");
  const [externalUrl, setExternalUrl] = useState("");
  const [file, setFile] = useState<File | null>(null);
  const [autoplay, setAutoplay] = useState(true);
  const [isLoop, setIsLoop] = useState(true);
  const [isActive, setIsActive] = useState(true);
  const [saving, setSaving] = useState(false);

  async function load() {
    try {
      const res = await customerApi.get<{ data: MusicData | null }>(`/invitations/${invitationId}/music`);
      setMusic(res.data);
      if (res.data) {
        setSource(res.data.source);
        setTitle(res.data.title ?? "");
        setArtist(res.data.artist ?? "");
        if (res.data.source !== "upload") setExternalUrl(res.data.url ?? "");
        setAutoplay(res.data.autoplay);
        setIsLoop(res.data.is_loop);
        setIsActive(res.data.is_active);
      }
    } catch (err) {
      toast.error(err instanceof CustomerApiError ? err.message : "Gagal memuat musik.");
    } finally {
      setLoaded(true);
    }
  }

  useEffect(() => {
    load();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [invitationId]);

  async function handleSubmit(e: FormEvent) {
    e.preventDefault();
    setSaving(true);

    const form = new FormData();
    form.append("source", source);
    if (title) form.append("title", title);
    if (artist) form.append("artist", artist);
    if (source === "upload") {
      if (file) form.append("file", file);
    } else {
      form.append("external_url", externalUrl);
    }
    form.append("autoplay", autoplay ? "1" : "0");
    form.append("is_loop", isLoop ? "1" : "0");
    form.append("is_active", isActive ? "1" : "0");
    form.append("_method", "PUT");

    try {
      await customerApi.post(`/invitations/${invitationId}/music`, form);
      toast.success("Musik berhasil disimpan.");
      await load();
    } catch (err) {
      if (err instanceof CustomerApiError && err.errors) {
        Object.values(err.errors).flat().forEach((msg) => toast.error(msg));
      } else {
        toast.error(err instanceof CustomerApiError ? err.message : "Gagal menyimpan musik.");
      }
    } finally {
      setSaving(false);
    }
  }

  async function handleDelete() {
    if (!confirm("Hapus musik latar undangan ini?")) return;
    try {
      await customerApi.delete(`/invitations/${invitationId}/music`);
      toast.success("Musik berhasil dihapus.");
      setMusic(null);
      setTitle("");
      setArtist("");
      setExternalUrl("");
      setFile(null);
    } catch (err) {
      toast.error(err instanceof CustomerApiError ? err.message : "Gagal menghapus musik.");
    }
  }

  if (!loaded) return null;

  return (
    <Card>
      <CardHeader>
        <CardTitle>Musik Latar</CardTitle>
      </CardHeader>
      <CardContent>
        <form onSubmit={handleSubmit} className="space-y-4">
          {music?.source === "upload" && music.url && (
            <audio controls src={music.url} className="w-full" />
          )}
          <div className="space-y-2">
            <Label>Sumber</Label>
            <Select value={source} onValueChange={(v) => setSource(v as Source)}>
              <SelectTrigger>
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="upload">Unggah File</SelectItem>
                <SelectItem value="spotify">Spotify</SelectItem>
                <SelectItem value="youtube_music">YouTube Music</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div className="grid gap-4 sm:grid-cols-2">
            <div className="space-y-2">
              <Label>Judul Lagu (opsional)</Label>
              <Input value={title} onChange={(e) => setTitle(e.target.value)} />
            </div>
            <div className="space-y-2">
              <Label>Penyanyi (opsional)</Label>
              <Input value={artist} onChange={(e) => setArtist(e.target.value)} />
            </div>
          </div>
          {source === "upload" ? (
            <div className="space-y-2">
              <Label>File Musik {music?.source === "upload" && "(kosongkan jika tidak diganti)"}</Label>
              <Input type="file" accept=".mp3,.wav,.ogg,.m4a" onChange={(e) => setFile(e.target.files?.[0] ?? null)} />
            </div>
          ) : (
            <div className="space-y-2">
              <Label>Tautan {source === "spotify" ? "Spotify" : "YouTube Music"}</Label>
              <Input
                type="url"
                required
                value={externalUrl}
                onChange={(e) => setExternalUrl(e.target.value)}
              />
            </div>
          )}
          <div className="grid gap-3 sm:grid-cols-3">
            <div className="flex items-center justify-between rounded-md border p-3">
              <Label htmlFor="autoplay">Putar Otomatis</Label>
              <Switch id="autoplay" checked={autoplay} onCheckedChange={setAutoplay} />
            </div>
            <div className="flex items-center justify-between rounded-md border p-3">
              <Label htmlFor="loop">Ulangi</Label>
              <Switch id="loop" checked={isLoop} onCheckedChange={setIsLoop} />
            </div>
            <div className="flex items-center justify-between rounded-md border p-3">
              <Label htmlFor="active">Aktif</Label>
              <Switch id="active" checked={isActive} onCheckedChange={setIsActive} />
            </div>
          </div>
          <div className="flex gap-2">
            <Button type="submit" disabled={saving}>
              {saving ? "Menyimpan..." : "Simpan"}
            </Button>
            {music && (
              <Button type="button" variant="destructive" onClick={handleDelete}>
                Hapus Musik
              </Button>
            )}
          </div>
        </form>
      </CardContent>
    </Card>
  );
}
