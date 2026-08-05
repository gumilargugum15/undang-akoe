import { createFileRoute, Link, useNavigate } from "@tanstack/react-router";
import { useEffect, useState, type FormEvent } from "react";
import { toast } from "sonner";
import { DashboardShell } from "@/components/dashboard/dashboard-shell";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { customerApi, CustomerApiError } from "@/lib/customer-api";
import { useRequireCustomerAuth } from "@/lib/customer-auth";

export const Route = createFileRoute("/dashboard/invitations/new")({
  component: NewInvitationPage,
});

const EVENT_CATEGORIES: { value: string; label: string }[] = [
  { value: "wedding", label: "Pernikahan" },
  { value: "birthday", label: "Ulang Tahun" },
  { value: "khitan", label: "Khitanan" },
  { value: "aqiqah", label: "Aqiqah" },
  { value: "anniversary", label: "Anniversary" },
  { value: "corporate", label: "Acara Perusahaan" },
  { value: "graduation", label: "Wisuda" },
  { value: "custom", label: "Lainnya" },
];

interface ThemeOption {
  id: number;
  name: string;
  thumbnail: string | null;
  type: "free" | "premium";
  price: number;
}

interface PackageOption {
  id: number;
  name: string;
  price: number;
}

function NewInvitationPage() {
  const { session, ready } = useRequireCustomerAuth();
  const navigate = useNavigate();

  const [themes, setThemes] = useState<ThemeOption[]>([]);
  const [packages, setPackages] = useState<PackageOption[]>([]);
  const [title, setTitle] = useState("");
  const [eventCategory, setEventCategory] = useState("wedding");
  const [themeId, setThemeId] = useState<number | null>(null);
  const [packageId, setPackageId] = useState<string>("");
  const [saving, setSaving] = useState(false);
  const [errors, setErrors] = useState<string[]>([]);

  useEffect(() => {
    if (!ready) return;
    Promise.all([
      customerApi.get<{ data: ThemeOption[] }>("/themes"),
      customerApi.get<{ data: PackageOption[] }>("/packages"),
    ])
      .then(([themesRes, packagesRes]) => {
        setThemes(themesRes.data);
        setPackages(packagesRes.data);
      })
      .catch(() => toast.error("Gagal memuat pilihan tema/paket."));
  }, [ready]);

  if (!ready || !session) return null;

  async function handleSubmit(e: FormEvent) {
    e.preventDefault();
    setErrors([]);

    if (!themeId) {
      setErrors(["Silakan pilih tema terlebih dahulu."]);
      return;
    }

    setSaving(true);
    try {
      await customerApi.post("/invitations", {
        title,
        event_category: eventCategory,
        theme_id: themeId,
        package_id: packageId ? Number(packageId) : undefined,
      });
      toast.success("Undangan berhasil dibuat.");
      navigate({ to: "/dashboard" });
    } catch (err) {
      if (err instanceof CustomerApiError && err.errors) {
        setErrors(Object.values(err.errors).flat());
      } else {
        toast.error(err instanceof CustomerApiError ? err.message : "Gagal membuat undangan.");
      }
    } finally {
      setSaving(false);
    }
  }

  return (
    <DashboardShell user={session.user}>
      <div className="mb-4 flex items-center justify-between">
        <h1 className="text-xl font-semibold">Buat Undangan Baru</h1>
        <Link to="/dashboard">
          <Button variant="outline" size="sm">
            Kembali
          </Button>
        </Link>
      </div>

      <form onSubmit={handleSubmit} className="space-y-6">
        {errors.length > 0 && (
          <div className="rounded-md border border-destructive/50 bg-destructive/10 p-3 text-sm text-destructive">
            <ul className="list-inside list-disc space-y-1">
              {errors.map((msg, i) => (
                <li key={i}>{msg}</li>
              ))}
            </ul>
          </div>
        )}

        <Card>
          <CardHeader>
            <CardTitle>Informasi Dasar</CardTitle>
          </CardHeader>
          <CardContent className="grid gap-4 sm:grid-cols-2">
            <div className="space-y-2 sm:col-span-2">
              <Label>Judul Undangan</Label>
              <Input
                required
                placeholder="Contoh: Pernikahan Alya & Raka"
                value={title}
                onChange={(e) => setTitle(e.target.value)}
              />
            </div>
            <div className="space-y-2">
              <Label>Kategori Acara</Label>
              <Select value={eventCategory} onValueChange={setEventCategory}>
                <SelectTrigger>
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  {EVENT_CATEGORIES.map((c) => (
                    <SelectItem key={c.value} value={c.value}>
                      {c.label}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>
            {packages.length > 0 && (
              <div className="space-y-2">
                <Label>Paket (opsional)</Label>
                <Select value={packageId} onValueChange={setPackageId}>
                  <SelectTrigger>
                    <SelectValue placeholder="Tanpa paket" />
                  </SelectTrigger>
                  <SelectContent>
                    {packages.map((p) => (
                      <SelectItem key={p.id} value={String(p.id)}>
                        {p.name} {p.price > 0 ? `— Rp${p.price.toLocaleString("id-ID")}` : "— Gratis"}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
            )}
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Pilih Tema</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid gap-4 sm:grid-cols-3">
              {themes.map((theme) => (
                <button
                  type="button"
                  key={theme.id}
                  onClick={() => setThemeId(theme.id)}
                  className={`overflow-hidden rounded-lg border-2 text-left transition-colors ${
                    themeId === theme.id ? "border-primary" : "border-transparent hover:border-muted-foreground/30"
                  }`}
                >
                  {theme.thumbnail && (
                    <img src={theme.thumbnail} alt={theme.name} className="h-32 w-full object-cover" />
                  )}
                  <div className="flex items-center justify-between p-2">
                    <span className="text-sm font-medium">{theme.name}</span>
                    <Badge variant={theme.type === "premium" ? "default" : "secondary"} className="text-[10px]">
                      {theme.type === "premium" ? `Rp${theme.price.toLocaleString("id-ID")}` : "Gratis"}
                    </Badge>
                  </div>
                </button>
              ))}
              {themes.length === 0 && (
                <p className="col-span-3 text-sm text-muted-foreground">Memuat tema...</p>
              )}
            </div>
          </CardContent>
        </Card>

        <Button type="submit" disabled={saving}>
          {saving ? "Menyimpan..." : "Buat Undangan"}
        </Button>
      </form>
    </DashboardShell>
  );
}
