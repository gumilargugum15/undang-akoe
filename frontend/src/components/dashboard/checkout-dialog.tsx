import { useEffect, useState, type ChangeEvent } from "react";
import { toast } from "sonner";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";
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
import { customerApi, CustomerApiError } from "@/lib/customer-api";

interface PaidPackageOption {
  id: number;
  name: string;
  price: number;
  requires_payment: boolean;
}

export interface TransactionInfo {
  id: number;
  invoice_number: string;
  package_name_snapshot: string;
  amount: number;
  payment_method: "bank_transfer" | "qris" | "dana" | "gopay";
  payment_channel: string | null;
  status: "pending" | "paid" | "failed" | "expired" | "refunded";
  proof_image: string | null;
  proof_uploaded_at: string | null;
  instructions: {
    banks?: { bank: string; account_number: string; account_name: string }[];
    image_url?: string | null;
    merchant_name?: string;
    provider?: string;
    number?: string | null;
    account_name?: string | null;
  };
}

function formatRupiah(value: number): string {
  return value > 0 ? `Rp${value.toLocaleString("id-ID")}` : "Gratis";
}

/**
 * Manual-payment checkout: pick a paid package + method, see the instructions the same
 * `Transaction::paymentInstructionsFor()` call always returns, upload proof, or cancel. No
 * payment gateway is involved — an admin verifies the proof separately.
 */
export function CheckoutDialog({
  invitationId,
  currentPackageId,
  currentTransaction,
  open,
  onOpenChange,
  onChanged,
}: {
  invitationId: number;
  currentPackageId: number | null;
  currentTransaction: TransactionInfo | null;
  open: boolean;
  onOpenChange: (open: boolean) => void;
  onChanged: () => void;
}) {
  const [packages, setPackages] = useState<PaidPackageOption[]>([]);
  const [packageId, setPackageId] = useState<string>("");
  const [paymentMethod, setPaymentMethod] =
    useState<TransactionInfo["payment_method"]>("bank_transfer");
  const [transaction, setTransaction] = useState<TransactionInfo | null>(currentTransaction);
  const [file, setFile] = useState<File | null>(null);
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    setTransaction(currentTransaction);
  }, [currentTransaction]);

  useEffect(() => {
    if (!open || transaction) return;
    customerApi
      .get<{ data: PaidPackageOption[] }>("/packages")
      .then((res) => {
        const paid = res.data.filter((p) => p.requires_payment);
        setPackages(paid);
        setPackageId(String(currentPackageId ?? paid[0]?.id ?? ""));
      })
      .catch(() => toast.error("Gagal memuat daftar paket."));
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [open, transaction]);

  async function handleCheckout() {
    if (!packageId) {
      toast.error("Pilih paket terlebih dahulu.");
      return;
    }
    setSubmitting(true);
    try {
      const res = await customerApi.post<{ data: TransactionInfo }>(
        `/invitations/${invitationId}/checkout`,
        { package_id: Number(packageId), payment_method: paymentMethod },
      );
      setTransaction(res.data);
      toast.success("Checkout berhasil dibuat. Selesaikan pembayaran sesuai instruksi.");
      onChanged();
    } catch (err) {
      if (err instanceof CustomerApiError && err.errors) {
        Object.values(err.errors)
          .flat()
          .forEach((msg) => toast.error(msg));
      } else {
        toast.error(err instanceof CustomerApiError ? err.message : "Gagal membuat checkout.");
      }
    } finally {
      setSubmitting(false);
    }
  }

  function handleFileChange(e: ChangeEvent<HTMLInputElement>) {
    setFile(e.target.files?.[0] ?? null);
  }

  async function handleUploadProof() {
    if (!file || !transaction) return;
    setSubmitting(true);
    const form = new FormData();
    form.append("proof", file);
    try {
      const res = await customerApi.post<{ data: TransactionInfo }>(
        `/transactions/${transaction.id}/proof`,
        form,
      );
      setTransaction(res.data);
      setFile(null);
      toast.success("Bukti pembayaran berhasil diunggah. Menunggu verifikasi admin.");
      onChanged();
    } catch (err) {
      toast.error(
        err instanceof CustomerApiError ? err.message : "Gagal mengunggah bukti pembayaran.",
      );
    } finally {
      setSubmitting(false);
    }
  }

  async function handleCancel() {
    if (!transaction) return;
    if (!confirm("Batalkan pembayaran ini? Undangan akan kembali ke status draft.")) return;
    setSubmitting(true);
    try {
      await customerApi.patch(`/transactions/${transaction.id}/cancel`);
      toast.success("Pembayaran dibatalkan. Undangan dikembalikan ke draft.");
      setTransaction(null);
      onChanged();
      onOpenChange(false);
    } catch (err) {
      toast.error(err instanceof CustomerApiError ? err.message : "Gagal membatalkan pembayaran.");
    } finally {
      setSubmitting(false);
    }
  }

  const awaitingVerification = transaction?.status === "pending" && !!transaction.proof_uploaded_at;

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Checkout Pembayaran</DialogTitle>
          <DialogDescription>
            Paket berbayar wajib lunas terlebih dahulu sebelum undangan bisa dipublikasikan.
          </DialogDescription>
        </DialogHeader>

        {!transaction && (
          <div className="space-y-4">
            <div className="space-y-2">
              <Label>Paket</Label>
              <Select value={packageId} onValueChange={setPackageId}>
                <SelectTrigger>
                  <SelectValue placeholder="Pilih paket" />
                </SelectTrigger>
                <SelectContent>
                  {packages.map((p) => (
                    <SelectItem key={p.id} value={String(p.id)}>
                      {p.name} — {formatRupiah(p.price)}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>
            <div className="space-y-2">
              <Label>Metode Pembayaran</Label>
              <RadioGroup
                value={paymentMethod}
                onValueChange={(v) => setPaymentMethod(v as TransactionInfo["payment_method"])}
              >
                <div className="flex items-center gap-2">
                  <RadioGroupItem value="bank_transfer" id="pm-bank" />
                  <Label htmlFor="pm-bank" className="cursor-pointer font-normal">
                    Transfer Bank
                  </Label>
                </div>
                <div className="flex items-center gap-2">
                  <RadioGroupItem value="qris" id="pm-qris" />
                  <Label htmlFor="pm-qris" className="cursor-pointer font-normal">
                    QRIS
                  </Label>
                </div>
                <div className="flex items-center gap-2">
                  <RadioGroupItem value="dana" id="pm-dana" />
                  <Label htmlFor="pm-dana" className="cursor-pointer font-normal">
                    DANA
                  </Label>
                </div>
                <div className="flex items-center gap-2">
                  <RadioGroupItem value="gopay" id="pm-gopay" />
                  <Label htmlFor="pm-gopay" className="cursor-pointer font-normal">
                    GoPay
                  </Label>
                </div>
              </RadioGroup>
            </div>
            <DialogFooter>
              <Button onClick={handleCheckout} disabled={submitting || !packageId}>
                {submitting ? "Memproses..." : "Checkout"}
              </Button>
            </DialogFooter>
          </div>
        )}

        {transaction && (
          <div className="space-y-4">
            <div className="rounded-md border p-3 text-sm">
              <p className="font-medium">{transaction.package_name_snapshot}</p>
              <p className="text-muted-foreground">No. Invoice: {transaction.invoice_number}</p>
              <p className="text-muted-foreground">Total: {formatRupiah(transaction.amount)}</p>
            </div>

            {transaction.payment_method === "bank_transfer" && transaction.instructions.banks && (
              <div className="space-y-2 text-sm">
                <Label>Transfer ke salah satu rekening berikut</Label>
                {transaction.instructions.banks.map((b) => (
                  <div key={b.bank} className="rounded-md border p-2">
                    <p className="font-medium">{b.bank}</p>
                    <p>
                      {b.account_number} a.n. {b.account_name}
                    </p>
                  </div>
                ))}
              </div>
            )}

            {transaction.payment_method === "qris" && (
              <div className="space-y-2 text-sm">
                <Label>Scan QRIS berikut</Label>
                {transaction.instructions.image_url ? (
                  <img
                    src={transaction.instructions.image_url}
                    alt="QRIS"
                    className="mx-auto h-56 w-56 object-contain"
                  />
                ) : (
                  <p className="text-muted-foreground">Hubungi admin untuk kode QRIS.</p>
                )}
                {transaction.instructions.merchant_name && (
                  <p className="text-center text-muted-foreground">
                    {transaction.instructions.merchant_name}
                  </p>
                )}
              </div>
            )}

            {(transaction.payment_method === "dana" || transaction.payment_method === "gopay") && (
              <div className="space-y-2 text-sm">
                <Label>Kirim ke {transaction.instructions.provider}</Label>
                {transaction.instructions.number ? (
                  <div className="rounded-md border p-2">
                    <p className="font-medium">{transaction.instructions.number}</p>
                    <p className="text-muted-foreground">
                      a.n. {transaction.instructions.account_name}
                    </p>
                  </div>
                ) : (
                  <p className="text-muted-foreground">
                    Hubungi admin untuk nomor {transaction.instructions.provider}.
                  </p>
                )}
              </div>
            )}

            {awaitingVerification ? (
              <div className="rounded-md border border-amber-500/50 bg-amber-500/10 p-3 text-sm">
                Bukti pembayaran sudah diunggah — menunggu verifikasi admin.
              </div>
            ) : (
              <div className="space-y-2">
                <Label>Unggah Bukti Pembayaran</Label>
                <Input type="file" accept="image/*" onChange={handleFileChange} />
                <Button type="button" disabled={!file || submitting} onClick={handleUploadProof}>
                  {submitting ? "Mengunggah..." : "Unggah Bukti Pembayaran"}
                </Button>
              </div>
            )}

            <DialogFooter>
              <Button
                type="button"
                variant="destructive"
                onClick={handleCancel}
                disabled={submitting}
              >
                Batalkan Pembayaran
              </Button>
            </DialogFooter>
          </div>
        )}
      </DialogContent>
    </Dialog>
  );
}
