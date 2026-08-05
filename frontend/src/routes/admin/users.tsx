import { createFileRoute } from "@tanstack/react-router";
import { useEffect, useState } from "react";
import { toast } from "sonner";
import { AdminShell } from "@/components/admin/admin-shell";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
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

export const Route = createFileRoute("/admin/users")({
  component: AdminUsersPage,
});

interface AdminUser {
  id: number;
  uuid: string;
  name: string;
  email: string;
  phone: string | null;
  role: "admin" | "customer";
  is_active: boolean;
  email_verified_at: string | null;
  created_at: string;
}

interface PaginatedUsers {
  data: AdminUser[];
  meta: { current_page: number; last_page: number; per_page: number; total: number };
}

function AdminUsersPage() {
  const { session, ready } = useRequireAdmin();
  const [users, setUsers] = useState<AdminUser[]>([]);
  const [meta, setMeta] = useState<PaginatedUsers["meta"] | null>(null);
  const [search, setSearch] = useState("");
  const [roleFilter, setRoleFilter] = useState("all");
  const [statusFilter, setStatusFilter] = useState("all");
  const [page, setPage] = useState(1);
  const [loading, setLoading] = useState(false);

  async function loadUsers() {
    setLoading(true);
    try {
      const params = new URLSearchParams({ page: String(page) });
      if (roleFilter !== "all") params.set("role", roleFilter);
      if (statusFilter !== "all") params.set("is_active", statusFilter === "active" ? "1" : "0");
      if (search.trim()) params.set("search", search.trim());
      const res = await adminApi.get<PaginatedUsers>(`/users?${params.toString()}`);
      setUsers(res.data);
      setMeta(res.meta);
    } catch (err) {
      toast.error(err instanceof AdminApiError ? err.message : "Gagal memuat daftar pengguna.");
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    if (ready) loadUsers();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [ready, page, roleFilter, statusFilter]);

  if (!ready || !session) return null;

  async function handleToggleActive(user: AdminUser) {
    try {
      if (user.is_active) {
        await adminApi.patch(`/users/${user.id}/suspend`);
        toast.success("Pengguna berhasil dinonaktifkan.");
      } else {
        await adminApi.patch(`/users/${user.id}/activate`);
        toast.success("Pengguna berhasil diaktifkan kembali.");
      }
      await loadUsers();
    } catch (err) {
      toast.error(err instanceof AdminApiError ? err.message : "Gagal mengubah status pengguna.");
    }
  }

  async function handleRoleChange(user: AdminUser, role: string) {
    if (role === user.role) return;
    try {
      await adminApi.put(`/users/${user.id}/role`, { role });
      toast.success("Peran pengguna berhasil diperbarui.");
      await loadUsers();
    } catch (err) {
      toast.error(err instanceof AdminApiError ? err.message : "Gagal mengubah peran pengguna.");
    }
  }

  async function handleDelete(user: AdminUser) {
    if (!confirm(`Hapus pengguna "${user.name}"?`)) return;
    try {
      await adminApi.delete(`/users/${user.id}`);
      toast.success("Pengguna berhasil dihapus.");
      await loadUsers();
    } catch (err) {
      toast.error(err instanceof AdminApiError ? err.message : "Gagal menghapus pengguna.");
    }
  }

  return (
    <AdminShell user={session.user}>
      <Card>
        <CardHeader>
          <CardTitle>Pengguna</CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="flex flex-wrap gap-2">
            <Input
              placeholder="Cari nama atau email..."
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              onKeyDown={(e) => {
                if (e.key === "Enter") {
                  setPage(1);
                  loadUsers();
                }
              }}
              className="max-w-xs"
            />
            <Select
              value={roleFilter}
              onValueChange={(v) => {
                setRoleFilter(v);
                setPage(1);
              }}
            >
              <SelectTrigger className="w-40">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">Semua peran</SelectItem>
                <SelectItem value="admin">Admin</SelectItem>
                <SelectItem value="customer">Customer</SelectItem>
              </SelectContent>
            </Select>
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
                <SelectItem value="active">Aktif</SelectItem>
                <SelectItem value="inactive">Nonaktif</SelectItem>
              </SelectContent>
            </Select>
            <Button variant="outline" onClick={() => loadUsers()} disabled={loading}>
              Cari
            </Button>
          </div>

          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Nama</TableHead>
                <TableHead>Email</TableHead>
                <TableHead>Peran</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Verifikasi Email</TableHead>
                <TableHead className="text-right">Aksi</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {users.map((user) => (
                <TableRow key={user.id}>
                  <TableCell className="font-medium">{user.name}</TableCell>
                  <TableCell>{user.email}</TableCell>
                  <TableCell>
                    <Select value={user.role} onValueChange={(v) => handleRoleChange(user, v)}>
                      <SelectTrigger className="h-8 w-32">
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="admin">Admin</SelectItem>
                        <SelectItem value="customer">Customer</SelectItem>
                      </SelectContent>
                    </Select>
                  </TableCell>
                  <TableCell>
                    <Badge variant={user.is_active ? "default" : "outline"}>
                      {user.is_active ? "Aktif" : "Nonaktif"}
                    </Badge>
                  </TableCell>
                  <TableCell>
                    <Badge variant={user.email_verified_at ? "secondary" : "outline"}>
                      {user.email_verified_at ? "Terverifikasi" : "Belum"}
                    </Badge>
                  </TableCell>
                  <TableCell className="space-x-1 text-right">
                    <Button variant="outline" size="sm" onClick={() => handleToggleActive(user)}>
                      {user.is_active ? "Nonaktifkan" : "Aktifkan"}
                    </Button>
                    <Button variant="destructive" size="sm" onClick={() => handleDelete(user)}>
                      Hapus
                    </Button>
                  </TableCell>
                </TableRow>
              ))}
              {users.length === 0 && !loading && (
                <TableRow>
                  <TableCell colSpan={6} className="text-center text-muted-foreground">
                    Tidak ada pengguna ditemukan.
                  </TableCell>
                </TableRow>
              )}
            </TableBody>
          </Table>

          {meta && meta.last_page > 1 && (
            <div className="flex items-center justify-between text-sm">
              <span className="text-muted-foreground">
                Halaman {meta.current_page} dari {meta.last_page} ({meta.total} pengguna)
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
    </AdminShell>
  );
}
