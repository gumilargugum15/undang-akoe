import { createFileRoute, Link } from "@tanstack/react-router";
import { useEffect, useState } from "react";
import { toast } from "sonner";
import { AdminShell } from "@/components/admin/admin-shell";
import { ThemeForm, type ThemeCategoryOption, type ThemeFormValues } from "@/components/admin/theme-form";
import { Button } from "@/components/ui/button";
import { adminApi, AdminApiError } from "@/lib/admin-api";
import { useRequireAdmin } from "@/lib/admin-auth";

export const Route = createFileRoute("/admin/themes/$themeId")({
  component: EditThemePage,
});

interface AdminThemeDetail {
  id: number;
  theme_category_id: number;
  name: string;
  description: string | null;
  thumbnail: string | null;
  type: "free" | "premium";
  price: number;
  supports_dark_mode: boolean;
  is_active: boolean;
  sort_order: number;
  config: Record<string, unknown>;
}

function EditThemePage() {
  const { themeId } = Route.useParams();
  const { session, ready } = useRequireAdmin();
  const [categories, setCategories] = useState<ThemeCategoryOption[]>([]);
  const [values, setValues] = useState<ThemeFormValues | null>(null);
  const [loadError, setLoadError] = useState(false);

  useEffect(() => {
    if (!ready) return;

    Promise.all([
      adminApi.get<{ data: ThemeCategoryOption[] }>("/theme-categories"),
      adminApi.get<{ data: AdminThemeDetail }>(`/themes/${themeId}`),
    ])
      .then(([categoriesRes, themeRes]) => {
        setCategories(categoriesRes.data);
        const theme = themeRes.data;
        setValues({
          theme_category_id: theme.theme_category_id,
          name: theme.name,
          description: theme.description ?? "",
          type: theme.type,
          price: theme.price ? String(theme.price) : "",
          supports_dark_mode: theme.supports_dark_mode,
          is_active: theme.is_active,
          sort_order: String(theme.sort_order),
          config: JSON.stringify(theme.config, null, 2),
          thumbnailUrl: theme.thumbnail,
        });
      })
      .catch((err) => {
        toast.error(err instanceof AdminApiError ? err.message : "Gagal memuat tema.");
        setLoadError(true);
      });
  }, [ready, themeId]);

  if (!ready || !session) return null;

  return (
    <AdminShell user={session.user}>
      <div className="mb-4 flex items-center justify-between">
        <h1 className="text-xl font-semibold">Edit Tema</h1>
        <Link to="/admin">
          <Button variant="outline" size="sm">
            Kembali
          </Button>
        </Link>
      </div>
      {loadError && <p className="text-sm text-destructive">Tema tidak ditemukan.</p>}
      {values && (
        <ThemeForm
          mode="edit"
          themeId={Number(themeId)}
          categories={categories}
          initialValues={values}
        />
      )}
    </AdminShell>
  );
}
