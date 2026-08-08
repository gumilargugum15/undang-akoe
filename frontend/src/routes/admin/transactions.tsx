import { createFileRoute } from "@tanstack/react-router";
import { useEffect, useState } from "react";
import { toast } from "sonner";
import { AdminShell } from "@/components/admin/admin-shell";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Textarea } from "@/components/ui/textarea";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { adminApi, AdminApiError } from "@/lib/admin-api";
import { useRequireAdmin } from "@/lib/admin-auth";

export const Route = createFileRoute("/admin/transactions")({
  component: AdminTransactionsPage,
});

type TransactionStatus = "pending" | "paid" | "failed" | "expired" | "refunded";

interface AdminTransaction {
  id: number;
  uuid: string;
  invoice_number: string;
  package_name_snapshot: string;
  invitation: { id: number; title: string; slug: string } | null;
  owner: { name: string; email: string } | null;
  amount: number;
  payment_method: string;
  payment_channel: string | null;
  status: TransactionStatus;
  proof_image: string | null;
  proof_uploaded_at: string | null;
  verified_at: string | null;
  notes: string | null;
  created_at: string | null;
}

interface PaginatedTransactions {
  data: AdminTransaction[];
  meta: { current_page: number; last_page: number; per_page: number; total: number };
}

const STATUS_LABEL: Record<TransactionStatus, string> = {
  pending: "Pending",
  paid: "Lunas",
  failed: "Gagal",
  expired: "Kedaluwarsa",
  refunded: "Refund",
};

const STATUS_BADGE_VARIANT: Record<
  TransactionStatus,
  "default" | "outline" | "destructive" | "secondary"
> = {
  pending: "outline",
  paid: "default",
  failed: "destructive",
  expired: "destructive",
  refunded: "secondary",
};

const FILTERS = [
  { value: "awaiting_verification", label: "Menunggu Verifikasi" },
  { value: "pending", label: "Semua Pending" },
  { value: "paid", label: "Lunas" },
  { value: "failed", label: "Gagal" },
  { value: "expired", label: "Kedaluwarsa" },
  { value: "refunded", label: "Refund" },
  { value: "all", label: "Semua Status" },
] as const;

function formatRupiah(value: number): string {
  return `Rp${value.toLocaleString("id-ID")}`;
}

function formatDate(value: string | null): string {
  if (!value) return "—";
  return new Date(value).toLocaleString("id-ID", { dateStyle: "medium", timeStyle: "short" });
}

function AdminTransactionsPage() {
  const { session, ready } = useRequireAdmin();
  const [transactions, setTransactions] = useState<AdminTransaction[]>([]);
  const [meta, setMeta] = useState<PaginatedTransactions["meta"] | null>(null);
  const [filter, setFilter] = useState<(typeof FILTERS)[number]["value"]>("awaiting_verification");
  const [page, setPage] = useState(1);
  const [loading, setLoading] = useState(false);
  const [reviewing, setReviewing] = useState<AdminTransaction | null>(null);
  const [reason, setReason] = useState("");
  const [submitting, setSubmitting] = useState(false);

  async function loadTransactions() {
    setLoading(true);
    try {
      const params = new URLSearchParams({ page: String(page) });
      if (filter === "awaiting_verification") {
        params.set("awaiting_verification", "1");
      } else if (filter !== "all") {
        params.set("status", filter);
      }
      const res = await adminApi.get<PaginatedTransactions>(`/transactions?${params.toString()}`);
      setTransactions(res.data);
      setMeta(res.meta);
    } catch (err) {
      toast.error(err instanceof AdminApiError ? err.message : "Gagal memuat daftar transaksi.");
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    if (ready) loadTransactions();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [ready, page, filter]);

  if (!ready || !session) return null;

  function openReview(tx: AdminTransaction) {
    setReviewing(tx);
    setReason("");
  }

  async function handleApprove() {
    if (!reviewing) return;
    setSubmitting(true);
    try {
      await adminApi.patch(`/transactions/${reviewing.id}/approve`);
      toast.success("Pembayaran disetujui — undangan berhasil dipublikasikan.");
      setReviewing(null);
      await loadTransactions();
    } catch (err) {
      toast.error(err instanceof AdminApiError ? err.message : "Gagal menyetujui pembayaran.");
    } finally {
      setSubmitting(false);
    }
  }

  async function handleReject() {
    if (!reviewing) return;
    setSubmitting(true);
    try {
      await adminApi.patch(`/transactions/${reviewing.id}/reject`, { reason: reason || null });
      toast.success("Pembayaran ditolak.");
      setReviewing(null);
      await loadTransactions();
    } catch (err) {
      toast.error(err instanceof AdminApiError ? err.message : "Gagal menolak pembayaran.");
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <AdminShell user={session.user}>
      <Card>
        <CardHeader className="flex flex-row items-center justify-between space-y-0">
          <CardTitle>Verifikasi Pembayaran</CardTitle>
          <Select
            value={filter}
            onValueChange={(v) => {
              setFilter(v as typeof filter);
              setPage(1);
            }}
          >
            <SelectTrigger className="w-56">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              {FILTERS.map((f) => (
                <SelectItem key={f.value} value={f.value}>
                  {f.label}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        </CardHeader>
        <CardContent className="space-y-4">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Invoice</TableHead>
                <TableHead>Pelanggan</TableHead>
                <TableHead>Paket / Undangan</TableHead>
                <TableHead>Jumlah</TableHead>
                <TableHead>Metode</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Diunggah</TableHead>
                <TableHead className="text-right">Aksi</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {transactions.map((tx) => (
                <TableRow key={tx.id}>
                  <TableCell className="font-medium">{tx.invoice_number}</TableCell>
                  <TableCell>
                    <div className="text-sm">{tx.owner?.name ?? "—"}</div>
                    <div className="text-xs text-muted-foreground">{tx.owner?.email}</div>
                  </TableCell>
                  <TableCell>
                    <div className="text-sm">{tx.package_name_snapshot}</div>
                    <div className="text-xs text-muted-foreground">
                      {tx.invitation?.title ?? "—"}
                    </div>
                  </TableCell>
                  <TableCell>{formatRupiah(tx.amount)}</TableCell>
                  <TableCell className="text-xs text-muted-foreground">
                    {tx.payment_method === "qris" ? "QRIS" : "Transfer Bank"}
                    {tx.payment_channel ? ` (${tx.payment_channel})` : ""}
                  </TableCell>
                  <TableCell>
                    <Badge variant={STATUS_BADGE_VARIANT[tx.status]}>
                      {STATUS_LABEL[tx.status]}
                    </Badge>
                  </TableCell>
                  <TableCell className="text-xs text-muted-foreground">
                    {formatDate(tx.proof_uploaded_at)}
                  </TableCell>
                  <TableCell className="text-right">
                    <Button variant="outline" size="sm" onClick={() => openReview(tx)}>
                      {tx.status === "pending" ? "Tinjau" : "Lihat"}
                    </Button>
                  </TableCell>
                </TableRow>
              ))}
              {transactions.length === 0 && !loading && (
                <TableRow>
                  <TableCell colSpan={8} className="text-center text-muted-foreground">
                    Tidak ada transaksi.
                  </TableCell>
                </TableRow>
              )}
            </TableBody>
          </Table>

          {meta && meta.last_page > 1 && (
            <div className="flex items-center justify-between text-sm">
              <span className="text-muted-foreground">
                Halaman {meta.current_page} dari {meta.last_page} ({meta.total} transaksi)
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

      <Dialog open={!!reviewing} onOpenChange={(open) => !open && setReviewing(null)}>
        <DialogContent className="max-h-[90vh] overflow-y-auto">
          <DialogHeader>
            <DialogTitle>{reviewing?.invoice_number}</DialogTitle>
            <DialogDescription>
              {reviewing?.owner?.name} — {reviewing?.package_name_snapshot} —{" "}
              {reviewing && formatRupiah(reviewing.amount)}
            </DialogDescription>
          </DialogHeader>

          {reviewing && (
            <div className="space-y-4">
              <div className="grid grid-cols-2 gap-2 text-sm">
                <div>
                  <p className="text-xs text-muted-foreground">Undangan</p>
                  <p>{reviewing.invitation?.title ?? "—"}</p>
                </div>
                <div>
                  <p className="text-xs text-muted-foreground">Metode</p>
                  <p>
                    {reviewing.payment_method === "qris" ? "QRIS" : "Transfer Bank"}
                    {reviewing.payment_channel ? ` (${reviewing.payment_channel})` : ""}
                  </p>
                </div>
                <div>
                  <p className="text-xs text-muted-foreground">Diunggah</p>
                  <p>{formatDate(reviewing.proof_uploaded_at)}</p>
                </div>
                <div>
                  <p className="text-xs text-muted-foreground">Status</p>
                  <Badge variant={STATUS_BADGE_VARIANT[reviewing.status]}>
                    {STATUS_LABEL[reviewing.status]}
                  </Badge>
                </div>
              </div>

              <div className="space-y-2">
                <p className="text-xs text-muted-foreground">Bukti Pembayaran</p>
                {reviewing.proof_image ? (
                  <a href={reviewing.proof_image} target="_blank" rel="noreferrer">
                    <img
                      src={reviewing.proof_image}
                      alt="Bukti pembayaran"
                      className="max-h-96 w-full rounded-md border object-contain"
                    />
                  </a>
                ) : (
                  <div className="flex h-32 items-center justify-center rounded-md border border-dashed text-sm text-muted-foreground">
                    Bukti pembayaran belum diunggah.
                  </div>
                )}
              </div>

              {reviewing.notes && (
                <div className="rounded-md border p-3 text-sm">
                  <p className="text-xs text-muted-foreground">Catatan</p>
                  <p>{reviewing.notes}</p>
                </div>
              )}

              {reviewing.status === "pending" && (
                <div className="space-y-2">
                  <p className="text-xs text-muted-foreground">
                    Alasan penolakan (opsional, hanya dipakai jika ditolak)
                  </p>
                  <Textarea
                    rows={2}
                    placeholder="Contoh: nominal tidak sesuai, bukti tidak terbaca..."
                    value={reason}
                    onChange={(e) => setReason(e.target.value)}
                  />
                </div>
              )}
            </div>
          )}

          {reviewing?.status === "pending" && (
            <DialogFooter>
              <Button
                type="button"
                variant="destructive"
                onClick={handleReject}
                disabled={submitting}
              >
                Tolak
              </Button>
              <Button type="button" onClick={handleApprove} disabled={submitting}>
                {submitting ? "Memproses..." : "Setujui"}
              </Button>
            </DialogFooter>
          )}
        </DialogContent>
      </Dialog>
    </AdminShell>
  );
}
