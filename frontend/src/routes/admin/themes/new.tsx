import { createFileRoute, Link } from "@tanstack/react-router";
import { useEffect, useState } from "react";
import { toast } from "sonner";
import { AdminShell } from "@/components/admin/admin-shell";
import { ThemeForm, type ThemeCategoryOption } from "@/components/admin/theme-form";
import { Button } from "@/components/ui/button";
import { adminApi } from "@/lib/admin-api";
import { useRequireAdmin } from "@/lib/admin-auth";

export const Route = createFileRoute("/admin/themes/new")({
  component: NewThemePage,
});

function NewThemePage() {
  const { session, ready } = useRequireAdmin();
  const [categories, setCategories] = useState<ThemeCategoryOption[]>([]);

  useEffect(() => {
    if (!ready) return;
    adminApi
      .get<{ data: ThemeCategoryOption[] }>("/theme-categories")
      .then((res) => setCategories(res.data))
      .catch(() => toast.error("Gagal memuat kategori tema."));
  }, [ready]);

  if (!ready || !session) return null;

  return (
    <AdminShell user={session.user}>
      <div className="mb-4 flex items-center justify-between">
        <h1 className="text-xl font-semibold">Tambah Tema Baru</h1>
        <Link to="/admin">
          <Button variant="outline" size="sm">
            Kembali
          </Button>
        </Link>
      </div>
      <ThemeForm mode="create" categories={categories} />
    </AdminShell>
  );
}
