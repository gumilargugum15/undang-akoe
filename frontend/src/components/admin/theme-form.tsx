import { useNavigate } from "@tanstack/react-router";
import { useEffect, useState, type FormEvent } from "react";
import { toast } from "sonner";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Switch } from "@/components/ui/switch";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { adminApi, AdminApiError } from "@/lib/admin-api";

const DEFAULT_CONFIG = {
  ornament: "floral",
  reveal: "fade",
  radius: "0.5rem",
  cardRadius: "1.75rem 1.75rem 1.75rem 1.75rem",
  shadow: "0 24px 60px -35px rgba(88, 34, 44, 0.45)",
  buttonShadow: "0 14px 30px -14px rgba(123, 45, 59, 0.65)",
  letterSpacing: "0.06em",
  headWeight: "500",
  fonts: {
    head: '"Playfair Display", serif',
    body: '"Cormorant Garamond", serif',
    script: '"Playfair Display", serif',
  },
  tokens: {
    bg: "#fbf6ec",
    bgAlt: "#f5ead7",
    surface: "#fffdf8",
    primary: "#7b2d3b",
    primaryFg: "#fff8ee",
    secondary: "#b08d57",
    accent: "#c9a227",
    text: "#3a2429",
    muted: "#8a6f6a",
    border: "#e2cfae",
  },
  swatch: ["#fbf6ec", "#e2cfae", "#b08d57", "#7b2d3b"],
  texture: "none",
};

/**
 * Laravel's multipart parser only sees nested `array`/`config.*` validation
 * rules if the field names use PHP's bracket notation (`config[tokens][bg]`)
 * — a single field holding a JSON string is received as a plain string, not
 * an array, and fails validation.
 */
function appendNested(form: FormData, key: string, value: unknown): void {
  if (Array.isArray(value)) {
    value.forEach((item, i) => appendNested(form, `${key}[${i}]`, item));
  } else if (value !== null && typeof value === "object") {
    Object.entries(value as Record<string, unknown>).forEach(([k, v]) => appendNested(form, `${key}[${k}]`, v));
  } else {
    form.append(key, String(value));
  }
}

export interface ThemeCategoryOption {
  id: number;
  name: string;
}

export interface ThemeFormValues {
  theme_category_id: number | "";
  name: string;
  description: string;
  type: "free" | "premium";
  price: string;
  supports_dark_mode: boolean;
  is_active: boolean;
  sort_order: string;
  config: string;
  thumbnailUrl?: string | null;
}

const EMPTY_VALUES: ThemeFormValues = {
  theme_category_id: "",
  name: "",
  description: "",
  type: "free",
  price: "",
  supports_dark_mode: false,
  is_active: true,
  sort_order: "0",
  config: JSON.stringify(DEFAULT_CONFIG, null, 2),
};

interface ThemeFormProps {
  mode: "create" | "edit";
  themeId?: number;
  categories: ThemeCategoryOption[];
  initialValues?: ThemeFormValues;
}

export function ThemeForm({ mode, themeId, categories, initialValues }: ThemeFormProps) {
  const navigate = useNavigate();
  const [values, setValues] = useState<ThemeFormValues>(initialValues ?? EMPTY_VALUES);
  const [thumbnail, setThumbnail] = useState<File | null>(null);
  const [bannerPreview, setBannerPreview] = useState<File | null>(null);
  const [saving, setSaving] = useState(false);
  const [errors, setErrors] = useState<string[]>([]);

  useEffect(() => {
    if (initialValues) setValues(initialValues);
  }, [initialValues]);

  function update<K extends keyof ThemeFormValues>(key: K, value: ThemeFormValues[K]) {
    setValues((v) => ({ ...v, [key]: value }));
  }

  async function handleSubmit(e: FormEvent) {
    e.preventDefault();
    setErrors([]);

    let parsedConfig: unknown;
    try {
      parsedConfig = JSON.parse(values.config);
    } catch {
      setErrors(["Konfigurasi (config) bukan JSON yang valid."]);
      return;
    }

    const form = new FormData();
    form.append("theme_category_id", String(values.theme_category_id));
    form.append("name", values.name);
    form.append("description", values.description);
    form.append("type", values.type);
    if (values.type === "premium") form.append("price", values.price || "0");
    form.append("supports_dark_mode", values.supports_dark_mode ? "1" : "0");
    form.append("is_active", values.is_active ? "1" : "0");
    form.append("sort_order", values.sort_order || "0");
    appendNested(form, "config", parsedConfig);
    if (thumbnail) form.append("thumbnail", thumbnail);
    if (bannerPreview) form.append("banner_preview", bannerPreview);

    setSaving(true);
    try {
      if (mode === "create") {
        await adminApi.post("/themes", form);
        toast.success("Tema berhasil ditambahkan.");
      } else {
        await adminApi.postFormAsPut(`/themes/${themeId}`, form);
        toast.success("Tema berhasil diperbarui.");
      }
      navigate({ to: "/admin" });
    } catch (err) {
      if (err instanceof AdminApiError && err.errors) {
        setErrors(Object.values(err.errors).flat());
      } else {
        toast.error(err instanceof AdminApiError ? err.message : "Gagal menyimpan tema.");
      }
    } finally {
      setSaving(false);
    }
  }

  return (
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
          <div className="space-y-2">
            <Label>Kategori</Label>
            <Select
              value={values.theme_category_id ? String(values.theme_category_id) : undefined}
              onValueChange={(v) => update("theme_category_id", Number(v))}
            >
              <SelectTrigger>
                <SelectValue placeholder="Pilih kategori" />
              </SelectTrigger>
              <SelectContent>
                {categories.map((c) => (
                  <SelectItem key={c.id} value={String(c.id)}>
                    {c.name}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>
          <div className="space-y-2">
            <Label>Nama Tema</Label>
            <Input required value={values.name} onChange={(e) => update("name", e.target.value)} />
          </div>
          <div className="space-y-2 sm:col-span-2">
            <Label>Deskripsi</Label>
            <Textarea
              value={values.description}
              onChange={(e) => update("description", e.target.value)}
              rows={2}
            />
          </div>
          <div className="space-y-2">
            <Label>Tipe</Label>
            <Select value={values.type} onValueChange={(v) => update("type", v as "free" | "premium")}>
              <SelectTrigger>
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="free">Free</SelectItem>
                <SelectItem value="premium">Premium</SelectItem>
              </SelectContent>
            </Select>
          </div>
          {values.type === "premium" && (
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
          )}
          <div className="space-y-2">
            <Label>Urutan Tampil</Label>
            <Input
              type="number"
              min={0}
              value={values.sort_order}
              onChange={(e) => update("sort_order", e.target.value)}
            />
          </div>
          <div className="flex items-center justify-between rounded-md border p-3">
            <Label htmlFor="is_active">Aktif</Label>
            <Switch
              id="is_active"
              checked={values.is_active}
              onCheckedChange={(v) => update("is_active", v)}
            />
          </div>
          <div className="flex items-center justify-between rounded-md border p-3">
            <Label htmlFor="dark_mode">Dukung Dark Mode</Label>
            <Switch
              id="dark_mode"
              checked={values.supports_dark_mode}
              onCheckedChange={(v) => update("supports_dark_mode", v)}
            />
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Gambar</CardTitle>
        </CardHeader>
        <CardContent className="grid gap-4 sm:grid-cols-2">
          <div className="space-y-2">
            <Label>Thumbnail</Label>
            {values.thumbnailUrl && (
              <img src={values.thumbnailUrl} alt="Thumbnail saat ini" className="h-24 w-24 rounded-md object-cover" />
            )}
            <Input type="file" accept="image/*" onChange={(e) => setThumbnail(e.target.files?.[0] ?? null)} />
          </div>
          <div className="space-y-2">
            <Label>Banner Preview</Label>
            <Input
              type="file"
              accept="image/*"
              onChange={(e) => setBannerPreview(e.target.files?.[0] ?? null)}
            />
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Konfigurasi Tampilan (config)</CardTitle>
        </CardHeader>
        <CardContent>
          <Textarea
            value={values.config}
            onChange={(e) => update("config", e.target.value)}
            rows={20}
            className="font-mono text-xs"
          />
          <p className="mt-2 text-xs text-muted-foreground">
            JSON mentah sesuai struktur <code>InvitationTheme</code> di frontend (ornament, reveal,
            fonts, tokens warna, swatch, texture). Nilai contoh sudah diisi sebagai starting point.
          </p>
        </CardContent>
      </Card>

      <div className="flex gap-2">
        <Button type="submit" disabled={saving}>
          {saving ? "Menyimpan..." : "Simpan"}
        </Button>
      </div>
    </form>
  );
}
