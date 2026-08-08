import { createFileRoute } from "@tanstack/react-router";
import { useEffect, useState, type ChangeEvent, type FormEvent } from "react";
import { toast } from "sonner";
import { AdminShell } from "@/components/admin/admin-shell";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { adminApi, AdminApiError } from "@/lib/admin-api";
import { useRequireAdmin } from "@/lib/admin-auth";

export const Route = createFileRoute("/admin/payment-settings")({
  component: AdminPaymentSettingsPage,
});

interface Bank {
  bank: string;
  account_number: string;
  account_name: string;
}

interface EwalletSetting {
  number: string | null;
  account_name: string | null;
}

interface PaymentSettings {
  banks: Bank[];
  dana: EwalletSetting;
  gopay: EwalletSetting;
  qris: { image_url: string | null; merchant_name: string | null };
}

const EMPTY_BANK: Bank = { bank: "", account_number: "", account_name: "" };

function AdminPaymentSettingsPage() {
  const { session, ready } = useRequireAdmin();
  const [settings, setSettings] = useState<PaymentSettings | null>(null);
  const [banks, setBanks] = useState<Bank[]>([]);
  const [dana, setDana] = useState<EwalletSetting>({ number: "", account_name: "" });
  const [gopay, setGopay] = useState<EwalletSetting>({ number: "", account_name: "" });
  const [qrisMerchantName, setQrisMerchantName] = useState("");
  const [qrisFile, setQrisFile] = useState<File | null>(null);
  const [saving, setSaving] = useState(false);
  const [uploadingQris, setUploadingQris] = useState(false);

  async function load() {
    try {
      const res = await adminApi.get<{ data: PaymentSettings }>("/payment-settings");
      setSettings(res.data);
      setBanks(res.data.banks.length > 0 ? res.data.banks : [EMPTY_BANK]);
      setDana({
        number: res.data.dana.number ?? "",
        account_name: res.data.dana.account_name ?? "",
      });
      setGopay({
        number: res.data.gopay.number ?? "",
        account_name: res.data.gopay.account_name ?? "",
      });
      setQrisMerchantName(res.data.qris.merchant_name ?? "");
    } catch (err) {
      toast.error(
        err instanceof AdminApiError ? err.message : "Gagal memuat pengaturan pembayaran.",
      );
    }
  }

  useEffect(() => {
    if (ready) load();
  }, [ready]);

  if (!ready || !session) return null;

  function updateBank(index: number, field: keyof Bank, value: string) {
    setBanks((prev) => prev.map((b, i) => (i === index ? { ...b, [field]: value } : b)));
  }

  function addBank() {
    setBanks((prev) => [...prev, { ...EMPTY_BANK }]);
  }

  function removeBank(index: number) {
    setBanks((prev) => prev.filter((_, i) => i !== index));
  }

  async function handleSubmit(e: FormEvent) {
    e.preventDefault();
    setSaving(true);
    try {
      await adminApi.put("/payment-settings", {
        banks: banks.filter((b) => b.bank && b.account_number && b.account_name),
        dana,
        gopay,
        qris_merchant_name: qrisMerchantName || null,
      });
      toast.success("Pengaturan pembayaran berhasil disimpan.");
      await load();
    } catch (err) {
      if (err instanceof AdminApiError && err.errors) {
        Object.values(err.errors)
          .flat()
          .forEach((msg) => toast.error(msg));
      } else {
        toast.error(err instanceof AdminApiError ? err.message : "Gagal menyimpan pengaturan.");
      }
    } finally {
      setSaving(false);
    }
  }

  function handleQrisFileChange(e: ChangeEvent<HTMLInputElement>) {
    setQrisFile(e.target.files?.[0] ?? null);
  }

  async function handleUploadQris() {
    if (!qrisFile) return;
    setUploadingQris(true);
    const form = new FormData();
    form.append("qris", qrisFile);
    try {
      await adminApi.post("/payment-settings/qris", form);
      toast.success("Gambar QRIS berhasil diunggah.");
      setQrisFile(null);
      await load();
    } catch (err) {
      if (err instanceof AdminApiError && err.errors) {
        Object.values(err.errors)
          .flat()
          .forEach((msg) => toast.error(msg));
      } else {
        toast.error(err instanceof AdminApiError ? err.message : "Gagal mengunggah QRIS.");
      }
    } finally {
      setUploadingQris(false);
    }
  }

  async function handleRemoveQris() {
    if (!confirm("Hapus gambar QRIS ini?")) return;
    try {
      await adminApi.delete("/payment-settings/qris");
      toast.success("Gambar QRIS berhasil dihapus.");
      await load();
    } catch (err) {
      toast.error(err instanceof AdminApiError ? err.message : "Gagal menghapus QRIS.");
    }
  }

  return (
    <AdminShell user={session.user}>
      <div className="space-y-6">
        <form onSubmit={handleSubmit} className="space-y-6">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0">
              <CardTitle>Rekening Transfer Bank</CardTitle>
              <Button type="button" variant="outline" size="sm" onClick={addBank}>
                + Tambah Rekening
              </Button>
            </CardHeader>
            <CardContent className="space-y-4">
              {banks.map((bank, i) => (
                <div key={i} className="grid gap-3 rounded-md border p-3 sm:grid-cols-4">
                  <div className="space-y-1">
                    <Label className="text-xs">Bank</Label>
                    <Input
                      value={bank.bank}
                      onChange={(e) => updateBank(i, "bank", e.target.value)}
                      placeholder="BCA"
                    />
                  </div>
                  <div className="space-y-1">
                    <Label className="text-xs">No. Rekening</Label>
                    <Input
                      value={bank.account_number}
                      onChange={(e) => updateBank(i, "account_number", e.target.value)}
                    />
                  </div>
                  <div className="space-y-1">
                    <Label className="text-xs">Atas Nama</Label>
                    <Input
                      value={bank.account_name}
                      onChange={(e) => updateBank(i, "account_name", e.target.value)}
                    />
                  </div>
                  <div className="flex items-end">
                    <Button
                      type="button"
                      variant="destructive"
                      size="sm"
                      onClick={() => removeBank(i)}
                      disabled={banks.length <= 1}
                    >
                      Hapus
                    </Button>
                  </div>
                </div>
              ))}
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>E-Wallet</CardTitle>
            </CardHeader>
            <CardContent className="grid gap-4 sm:grid-cols-2">
              <div className="space-y-3 rounded-md border p-3">
                <p className="text-sm font-medium">DANA</p>
                <div className="space-y-1">
                  <Label className="text-xs">Nomor</Label>
                  <Input
                    value={dana.number ?? ""}
                    onChange={(e) => setDana((v) => ({ ...v, number: e.target.value }))}
                    placeholder="08123456789"
                  />
                </div>
                <div className="space-y-1">
                  <Label className="text-xs">Atas Nama</Label>
                  <Input
                    value={dana.account_name ?? ""}
                    onChange={(e) => setDana((v) => ({ ...v, account_name: e.target.value }))}
                  />
                </div>
              </div>
              <div className="space-y-3 rounded-md border p-3">
                <p className="text-sm font-medium">GoPay</p>
                <div className="space-y-1">
                  <Label className="text-xs">Nomor</Label>
                  <Input
                    value={gopay.number ?? ""}
                    onChange={(e) => setGopay((v) => ({ ...v, number: e.target.value }))}
                    placeholder="08123456789"
                  />
                </div>
                <div className="space-y-1">
                  <Label className="text-xs">Atas Nama</Label>
                  <Input
                    value={gopay.account_name ?? ""}
                    onChange={(e) => setGopay((v) => ({ ...v, account_name: e.target.value }))}
                  />
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>QRIS</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="space-y-1">
                <Label className="text-xs">Nama Merchant (tampil di bawah kode QRIS)</Label>
                <Input
                  value={qrisMerchantName}
                  onChange={(e) => setQrisMerchantName(e.target.value)}
                />
              </div>
            </CardContent>
          </Card>

          <Button type="submit" disabled={saving}>
            {saving ? "Menyimpan..." : "Simpan Pengaturan"}
          </Button>
        </form>

        <Card>
          <CardHeader>
            <CardTitle>Gambar QRIS</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <p className="text-sm text-muted-foreground">
              Ditampilkan ke pelanggan saat memilih metode pembayaran QRIS.
            </p>
            {settings?.qris.image_url ? (
              <img
                src={settings.qris.image_url}
                alt="Kode QRIS"
                className="h-56 w-56 rounded-md border object-contain"
              />
            ) : (
              <div className="flex h-40 w-40 items-center justify-center rounded-md border border-dashed text-center text-sm text-muted-foreground">
                Belum ada gambar QRIS.
              </div>
            )}
            <Input type="file" accept="image/*" onChange={handleQrisFileChange} />
            <div className="flex gap-2">
              <Button
                type="button"
                disabled={!qrisFile || uploadingQris}
                onClick={handleUploadQris}
              >
                {uploadingQris ? "Mengunggah..." : "Unggah QRIS"}
              </Button>
              {settings?.qris.image_url && (
                <Button type="button" variant="destructive" onClick={handleRemoveQris}>
                  Hapus QRIS
                </Button>
              )}
            </div>
          </CardContent>
        </Card>
      </div>
    </AdminShell>
  );
}
