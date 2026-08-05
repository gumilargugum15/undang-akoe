import { useEffect, useState, type FormEvent } from "react";
import { toast } from "sonner";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { customerApi, CustomerApiError } from "@/lib/customer-api";

interface Couple {
  role: "groom" | "bride";
  nickname: string;
  full_name: string;
  parent_name: string | null;
  instagram_handle: string | null;
  photo: string | null;
  description: string | null;
}

interface FormValues {
  nickname: string;
  full_name: string;
  parent_name: string;
  instagram_handle: string;
  description: string;
}

const EMPTY_FORM: FormValues = {
  nickname: "",
  full_name: "",
  parent_name: "",
  instagram_handle: "",
  description: "",
};

function CoupleForm({
  invitationId,
  role,
  label,
  initial,
  onSaved,
}: {
  invitationId: number;
  role: "groom" | "bride";
  label: string;
  initial: Couple | null;
  onSaved: () => void;
}) {
  const [values, setValues] = useState<FormValues>(EMPTY_FORM);
  const [photo, setPhoto] = useState<File | null>(null);
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    if (initial) {
      setValues({
        nickname: initial.nickname,
        full_name: initial.full_name,
        parent_name: initial.parent_name ?? "",
        instagram_handle: initial.instagram_handle ?? "",
        description: initial.description ?? "",
      });
    }
  }, [initial]);

  function update<K extends keyof FormValues>(key: K, value: FormValues[K]) {
    setValues((v) => ({ ...v, [key]: value }));
  }

  async function handleSubmit(e: FormEvent) {
    e.preventDefault();
    setSaving(true);

    const form = new FormData();
    form.append("nickname", values.nickname);
    form.append("full_name", values.full_name);
    if (values.parent_name) form.append("parent_name", values.parent_name);
    if (values.instagram_handle) form.append("instagram_handle", values.instagram_handle);
    if (values.description) form.append("description", values.description);
    if (photo) form.append("photo", photo);
    form.append("_method", "PUT");

    try {
      await customerApi.post(`/invitations/${invitationId}/couples/${role}`, form);
      toast.success(`Data ${label.toLowerCase()} berhasil disimpan.`);
      onSaved();
    } catch (err) {
      toast.error(err instanceof CustomerApiError ? err.message : `Gagal menyimpan data ${label.toLowerCase()}.`);
    } finally {
      setSaving(false);
    }
  }

  return (
    <Card>
      <CardHeader>
        <CardTitle>{label}</CardTitle>
      </CardHeader>
      <CardContent>
        <form onSubmit={handleSubmit} className="space-y-4">
          {initial?.photo && (
            <img src={initial.photo} alt={label} className="size-20 rounded-full object-cover" />
          )}
          <div className="space-y-2">
            <Label>Foto</Label>
            <Input type="file" accept="image/*" onChange={(e) => setPhoto(e.target.files?.[0] ?? null)} />
          </div>
          <div className="grid gap-4 sm:grid-cols-2">
            <div className="space-y-2">
              <Label>Nama Panggilan</Label>
              <Input required value={values.nickname} onChange={(e) => update("nickname", e.target.value)} />
            </div>
            <div className="space-y-2">
              <Label>Nama Lengkap</Label>
              <Input required value={values.full_name} onChange={(e) => update("full_name", e.target.value)} />
            </div>
            <div className="space-y-2">
              <Label>Nama Orang Tua</Label>
              <Input value={values.parent_name} onChange={(e) => update("parent_name", e.target.value)} />
            </div>
            <div className="space-y-2">
              <Label>Instagram</Label>
              <Input
                placeholder="@username"
                value={values.instagram_handle}
                onChange={(e) => update("instagram_handle", e.target.value)}
              />
            </div>
          </div>
          <div className="space-y-2">
            <Label>Deskripsi Singkat</Label>
            <Textarea rows={2} value={values.description} onChange={(e) => update("description", e.target.value)} />
          </div>
          <Button type="submit" disabled={saving}>
            {saving ? "Menyimpan..." : "Simpan"}
          </Button>
        </form>
      </CardContent>
    </Card>
  );
}

export function CoupleEditor({ invitationId }: { invitationId: number }) {
  const [couples, setCouples] = useState<Couple[]>([]);
  const [loaded, setLoaded] = useState(false);

  async function load() {
    try {
      const res = await customerApi.get<{ data: Couple[] }>(`/invitations/${invitationId}/couples`);
      setCouples(res.data);
    } catch (err) {
      toast.error(err instanceof CustomerApiError ? err.message : "Gagal memuat data mempelai.");
    } finally {
      setLoaded(true);
    }
  }

  useEffect(() => {
    load();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [invitationId]);

  if (!loaded) return null;

  const bride = couples.find((c) => c.role === "bride") ?? null;
  const groom = couples.find((c) => c.role === "groom") ?? null;

  return (
    <div className="grid gap-4 sm:grid-cols-2">
      <CoupleForm invitationId={invitationId} role="bride" label="Mempelai Wanita" initial={bride} onSaved={load} />
      <CoupleForm invitationId={invitationId} role="groom" label="Mempelai Pria" initial={groom} onSaved={load} />
    </div>
  );
}
