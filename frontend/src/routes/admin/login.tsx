import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { useEffect, useState, type FormEvent } from "react";
import { toast } from "sonner";
import { Toaster } from "@/components/ui/sonner";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { adminApi, AdminApiError } from "@/lib/admin-api";
import {
  getAdminSession,
  isAdminSession,
  setAdminSession,
  type AdminSession,
} from "@/lib/admin-auth";

export const Route = createFileRoute("/admin/login")({
  component: AdminLoginPage,
});

interface LoginResponse {
  message: string;
  data: AdminSession & { expires_at: string };
}

function AdminLoginPage() {
  const navigate = useNavigate();
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (isAdminSession(getAdminSession())) {
      navigate({ to: "/admin" });
    }
  }, [navigate]);

  async function handleSubmit(e: FormEvent) {
    e.preventDefault();
    setLoading(true);
    try {
      const res = await adminApi.post<LoginResponse>("/auth/login", { email, password });
      if (res.data.user.role !== "admin") {
        toast.error("Akun ini bukan akun admin.");
        return;
      }
      setAdminSession({ token: res.data.token, user: res.data.user });
      toast.success("Login berhasil.");
      navigate({ to: "/admin" });
    } catch (err) {
      toast.error(err instanceof AdminApiError ? err.message : "Login gagal, coba lagi.");
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="flex min-h-screen items-center justify-center bg-muted/30 px-4">
      <Toaster />
      <Card className="w-full max-w-sm">
        <CardHeader>
          <CardTitle>Admin Login</CardTitle>
          <CardDescription>Undang Akoe — Panel Admin</CardDescription>
        </CardHeader>
        <CardContent>
          <form onSubmit={handleSubmit} className="space-y-4">
            <div className="space-y-2">
              <Label htmlFor="email">Email</Label>
              <Input
                id="email"
                type="email"
                required
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                placeholder="admin@undangakoe.test"
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="password">Kata Sandi</Label>
              <Input
                id="password"
                type="password"
                required
                value={password}
                onChange={(e) => setPassword(e.target.value)}
              />
            </div>
            <Button type="submit" className="w-full" disabled={loading}>
              {loading ? "Memproses..." : "Masuk"}
            </Button>
          </form>
        </CardContent>
      </Card>
    </div>
  );
}
