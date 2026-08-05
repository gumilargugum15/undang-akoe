import { createFileRoute, Link } from "@tanstack/react-router";
import { useEffect, useState, type FormEvent } from "react";
import { toast } from "sonner";
import { LayoutGrid, Palette, Sparkles } from "lucide-react";
import { AdminShell } from "@/components/admin/admin-shell";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { GreetingCard, StatCard } from "@/components/ui/stat-card";
import { BarStatChart } from "@/components/ui/bar-stat-chart";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { adminApi, AdminApiError } from "@/lib/admin-api";
import { useRequireAdmin } from "@/lib/admin-auth";

export const Route = createFileRoute("/admin/")({
  component: AdminDashboardPage,
});

interface ThemeCategory {
  id: number;
  name: string;
  slug: string;
  icon: string | null;
  sort_order: number;
}

interface AdminTheme {
  id: number;
  uuid: string;
  name: string;
  slug: string;
  description: string | null;
  category: { name: string; slug: string } | null;
  thumbnail: string | null;
  type: "free" | "premium";
  status: "draft" | "published";
  price: number;
  is_active: boolean;
  sort_order: number;
}

interface PaginatedThemes {
  data: AdminTheme[];
  meta: { current_page: number; last_page: number; per_page: number; total: number };
}

interface ThemeStats {
  total: number;
  published: number;
  byCategory: Array<{ name: string; count: number }>;
}

function AdminDashboardPage() {
  const { session, ready } = useRequireAdmin();

  const [categories, setCategories] = useState<ThemeCategory[]>([]);
  const [newCategoryName, setNewCategoryName] = useState("");
  const [addingCategory, setAddingCategory] = useState(false);

  const [themes, setThemes] = useState<AdminTheme[]>([]);
  const [meta, setMeta] = useState<PaginatedThemes["meta"] | null>(null);
  const [statusFilter, setStatusFilter] = useState<string>("all");
  const [search, setSearch] = useState("");
  const [page, setPage] = useState(1);
  const [loading, setLoading] = useState(false);

  const [stats, setStats] = useState<ThemeStats | null>(null);

  async function loadCategories() {
    const res = await adminApi.get<{ data: ThemeCategory[] }>("/theme-categories");
    setCategories(res.data);
  }

  async function loadStats() {
    try {
      // One unfiltered pull (well above any realistic catalog size) so the summary
      // cards/chart reflect the whole catalog, not just the table's current filter/page.
      const res = await adminApi.get<PaginatedThemes>("/themes?per_page=200");
      const byCategoryMap = new Map<string, number>();
      for (const theme of res.data) {
        const name = theme.category?.name ?? "Tanpa kategori";
        byCategoryMap.set(name, (byCategoryMap.get(name) ?? 0) + 1);
      }
      setStats({
        total: res.meta.total,
        published: res.data.filter((t) => t.status === "published").length,
        byCategory: Array.from(byCategoryMap, ([name, count]) => ({ name, count })),
      });
    } catch {
      // Summary layer only — the category/theme management cards below don't depend on it.
    }
  }

  async function loadThemes() {
    setLoading(true);
    try {
      const params = new URLSearchParams({ page: String(page) });
      if (statusFilter !== "all") params.set("status", statusFilter);
      if (search.trim()) params.set("search", search.trim());
      const res = await adminApi.get<PaginatedThemes>(`/themes?${params.toString()}`);
      setThemes(res.data);
      setMeta(res.meta);
    } catch (err) {
      toast.error(err instanceof AdminApiError ? err.message : "Gagal memuat daftar tema.");
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    if (!ready) return;
    loadCategories().catch(() => toast.error("Gagal memuat kategori tema."));
    loadStats();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [ready]);

  useEffect(() => {
    if (!ready) return;
    loadThemes();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [ready, page, statusFilter]);

  if (!ready || !session) return null;

  async function handleAddCategory(e: FormEvent) {
    e.preventDefault();
    if (!newCategoryName.trim()) return;
    setAddingCategory(true);
    try {
      await adminApi.post("/theme-categories", { name: newCategoryName.trim() });
      setNewCategoryName("");
      toast.success("Kategori berhasil ditambahkan.");
      await loadCategories();
    } catch (err) {
      toast.error(err instanceof AdminApiError ? err.message : "Gagal menambah kategori.");
    } finally {
      setAddingCategory(false);
    }
  }

  async function handleDeleteCategory(category: ThemeCategory) {
    if (!confirm(`Hapus kategori "${category.name}"?`)) return;
    try {
      await adminApi.delete(`/theme-categories/${category.id}`);
      toast.success("Kategori berhasil dihapus.");
      await loadCategories();
    } catch (err) {
      toast.error(err instanceof AdminApiError ? err.message : "Gagal menghapus kategori.");
    }
  }

  async function handlePublishToggle(theme: AdminTheme) {
    try {
      if (theme.status === "published") {
        await adminApi.patch(`/themes/${theme.id}/unpublish`);
        toast.success("Tema dikembalikan ke draft.");
      } else {
        await adminApi.patch(`/themes/${theme.id}/publish`);
        toast.success("Tema berhasil dipublikasikan.");
      }
      await Promise.all([loadThemes(), loadStats()]);
    } catch (err) {
      toast.error(err instanceof AdminApiError ? err.message : "Gagal mengubah status tema.");
    }
  }

  async function handleDuplicate(theme: AdminTheme) {
    try {
      await adminApi.post(`/themes/${theme.id}/duplicate`);
      toast.success("Tema berhasil diduplikasi.");
      await Promise.all([loadThemes(), loadStats()]);
    } catch (err) {
      toast.error(err instanceof AdminApiError ? err.message : "Gagal menduplikasi tema.");
    }
  }

  async function handleDelete(theme: AdminTheme) {
    if (!confirm(`Hapus tema "${theme.name}"?`)) return;
    try {
      await adminApi.delete(`/themes/${theme.id}`);
      toast.success("Tema berhasil dihapus.");
      await Promise.all([loadThemes(), loadStats()]);
    } catch (err) {
      toast.error(err instanceof AdminApiError ? err.message : "Gagal menghapus tema.");
    }
  }

  return (
    <AdminShell user={session.user}>
      <div className="space-y-6">
        <GreetingCard
          title={`Halo, ${session.user.name.split(" ")[0]} \u{1F44B}`}
          subtitle="Kelola tema, kategori, dan katalog undangan digital dari sini."
        />

        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <StatCard
            label="Total Tema"
            value={stats?.total ?? "—"}
            icon={Palette}
            tone="violet"
          />
          <StatCard
            label="Tema Published"
            value={stats?.published ?? "—"}
            icon={Sparkles}
            tone="emerald"
            hint={stats ? `${stats.total - stats.published} draft` : undefined}
          />
          <StatCard label="Total Kategori" value={categories.length} icon={LayoutGrid} tone="sky" />
        </div>

        <BarStatChart
          title="Tema per Kategori"
          data={stats?.byCategory ?? []}
          dataKey="count"
          labelKey="name"
          emptyMessage="Belum ada tema untuk ditampilkan."
        />

        <Card>
          <CardHeader>
            <CardTitle>Kategori Tema</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <form onSubmit={handleAddCategory} className="flex gap-2">
              <Input
                placeholder="Nama kategori baru, mis. Rustic"
                value={newCategoryName}
                onChange={(e) => setNewCategoryName(e.target.value)}
              />
              <Button type="submit" disabled={addingCategory}>
                Tambah
              </Button>
            </form>
            <div className="flex flex-wrap gap-2">
              {categories.map((c) => (
                <Badge key={c.id} variant="secondary" className="gap-2 py-1.5">
                  {c.name}
                  <button
                    onClick={() => handleDeleteCategory(c)}
                    className="text-muted-foreground hover:text-destructive"
                    aria-label={`Hapus kategori ${c.name}`}
                  >
                    ×
                  </button>
                </Badge>
              ))}
              {categories.length === 0 && (
                <p className="text-sm text-muted-foreground">Belum ada kategori.</p>
              )}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0">
            <CardTitle>Tema</CardTitle>
            <Link to="/admin/themes/new">
              <Button>+ Tambah Tema</Button>
            </Link>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="flex flex-wrap gap-2">
              <Input
                placeholder="Cari nama tema..."
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                onKeyDown={(e) => {
                  if (e.key === "Enter") {
                    setPage(1);
                    loadThemes();
                  }
                }}
                className="max-w-xs"
              />
              <Select
                value={statusFilter}
                onValueChange={(v) => {
                  setStatusFilter(v);
                  setPage(1);
                }}
              >
                <SelectTrigger className="w-40">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">Semua status</SelectItem>
                  <SelectItem value="draft">Draft</SelectItem>
                  <SelectItem value="published">Published</SelectItem>
                </SelectContent>
              </Select>
              <Button variant="outline" onClick={() => loadThemes()} disabled={loading}>
                Cari
              </Button>
            </div>

            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Nama</TableHead>
                  <TableHead>Kategori</TableHead>
                  <TableHead>Tipe</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead>Aktif</TableHead>
                  <TableHead className="text-right">Aksi</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {themes.map((theme) => (
                  <TableRow key={theme.id}>
                    <TableCell className="font-medium">{theme.name}</TableCell>
                    <TableCell>{theme.category?.name ?? "—"}</TableCell>
                    <TableCell>
                      <Badge variant={theme.type === "premium" ? "default" : "secondary"}>
                        {theme.type === "premium" ? `Premium (Rp${theme.price.toLocaleString("id-ID")})` : "Free"}
                      </Badge>
                    </TableCell>
                    <TableCell>
                      <Badge variant={theme.status === "published" ? "default" : "outline"}>
                        {theme.status}
                      </Badge>
                    </TableCell>
                    <TableCell>{theme.is_active ? "Ya" : "Tidak"}</TableCell>
                    <TableCell className="space-x-1 text-right">
                      <Link to="/admin/themes/$themeId" params={{ themeId: String(theme.id) }}>
                        <Button variant="outline" size="sm">
                          Edit
                        </Button>
                      </Link>
                      <Button variant="outline" size="sm" onClick={() => handlePublishToggle(theme)}>
                        {theme.status === "published" ? "Unpublish" : "Publish"}
                      </Button>
                      <Button variant="outline" size="sm" onClick={() => handleDuplicate(theme)}>
                        Duplikasi
                      </Button>
                      <Button variant="destructive" size="sm" onClick={() => handleDelete(theme)}>
                        Hapus
                      </Button>
                    </TableCell>
                  </TableRow>
                ))}
                {themes.length === 0 && !loading && (
                  <TableRow>
                    <TableCell colSpan={6} className="text-center text-muted-foreground">
                      Belum ada tema.
                    </TableCell>
                  </TableRow>
                )}
              </TableBody>
            </Table>

            {meta && meta.last_page > 1 && (
              <div className="flex items-center justify-between text-sm">
                <span className="text-muted-foreground">
                  Halaman {meta.current_page} dari {meta.last_page} ({meta.total} tema)
                </span>
                <div className="flex gap-2">
                  <Button
                    variant="outline"
                    size="sm"
                    disabled={meta.current_page <= 1}
                    onClick={() => setPage((p) => p - 1)}
                  >
                    Sebelumnya
                  </Button>
                  <Button
                    variant="outline"
                    size="sm"
                    disabled={meta.current_page >= meta.last_page}
                    onClick={() => setPage((p) => p + 1)}
                  >
                    Berikutnya
                  </Button>
                </div>
              </div>
            )}
          </CardContent>
        </Card>
      </div>
    </AdminShell>
  );
}
