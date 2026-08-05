import { useEffect, useState } from "react";
import { toast } from "sonner";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { customerApi, CustomerApiError } from "@/lib/customer-api";

interface ThemeOption {
  id: number;
  name: string;
  thumbnail: string | null;
  type: "free" | "premium";
  price: number;
  config: { swatch?: string[] };
}

export function ThemeEditor({
  invitationId,
  currentThemeId,
  locked,
  onChanged,
}: {
  invitationId: number;
  currentThemeId: number | null;
  locked: boolean;
  onChanged?: (themeId: number) => void;
}) {
  const [themes, setThemes] = useState<ThemeOption[]>([]);
  const [loaded, setLoaded] = useState(false);
  const [savingId, setSavingId] = useState<number | null>(null);

  useEffect(() => {
    customerApi
      .get<{ data: ThemeOption[] }>("/themes")
      .then((res) => setThemes(res.data))
      .catch((err) =>
        toast.error(err instanceof CustomerApiError ? err.message : "Gagal memuat daftar tema."),
      )
      .finally(() => setLoaded(true));
  }, []);

  async function handleSelect(themeId: number) {
    if (locked || themeId === currentThemeId) return;
    setSavingId(themeId);
    try {
      await customerApi.patch(`/invitations/${invitationId}/change-theme`, { theme_id: themeId });
      toast.success("Tema undangan berhasil diganti.");
      onChanged?.(themeId);
    } catch (err) {
      if (err instanceof CustomerApiError && err.errors) {
        Object.values(err.errors)
          .flat()
          .forEach((msg) => toast.error(msg));
      } else {
        toast.error(err instanceof CustomerApiError ? err.message : "Gagal mengganti tema.");
      }
    } finally {
      setSavingId(null);
    }
  }

  if (!loaded) return null;

  return (
    <Card>
      <CardHeader>
        <CardTitle>Tema Undangan</CardTitle>
      </CardHeader>
      <CardContent className="space-y-4">
        {locked && (
          <p className="rounded-md border border-amber-300 bg-amber-50 p-3 text-sm text-amber-800">
            Tema tidak bisa diganti karena undangan ini sudah dipublikasikan. Batalkan publikasi
            terlebih dahulu untuk mengganti tema.
          </p>
        )}
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          {themes.map((t) => {
            const active = t.id === currentThemeId;
            const swatch = t.config?.swatch ?? [];
            return (
              <button
                key={t.id}
                type="button"
                disabled={locked || savingId !== null}
                onClick={() => handleSelect(t.id)}
                className={`group overflow-hidden rounded-lg border text-left transition-opacity disabled:cursor-not-allowed ${
                  active ? "border-primary ring-2 ring-primary" : "border-border"
                } ${locked && !active ? "opacity-60" : ""}`}
              >
                {t.thumbnail ? (
                  <img src={t.thumbnail} alt={t.name} className="h-28 w-full object-cover" />
                ) : (
                  <div className="flex h-28 w-full">
                    {swatch.map((color, i) => (
                      <span key={i} className="flex-1" style={{ backgroundColor: color }} />
                    ))}
                  </div>
                )}
                <div className="flex items-center justify-between gap-2 p-3">
                  <span className="text-sm font-medium">{t.name}</span>
                  <div className="flex items-center gap-2">
                    {t.type === "premium" && <Badge variant="secondary">Premium</Badge>}
                    {active && <Badge>Aktif</Badge>}
                  </div>
                </div>
              </button>
            );
          })}
        </div>
        {!locked && (
          <p className="text-xs text-muted-foreground">
            Klik salah satu tema untuk langsung menerapkannya.
          </p>
        )}
      </CardContent>
    </Card>
  );
}
