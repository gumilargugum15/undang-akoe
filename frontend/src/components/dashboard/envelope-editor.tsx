import { useEffect, useState, type FormEvent } from "react";
import { toast } from "sonner";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Switch } from "@/components/ui/switch";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
  Dialog,
  DialogContent,
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
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { customerApi, CustomerApiError } from "@/lib/customer-api";

type EnvelopeType = "bank" | "ewallet" | "qris";

const EWALLET_PROVIDERS = ["Dana", "OVO", "GoPay", "ShopeePay"];

interface Envelope {
  id: number;
  type: EnvelopeType;
  provider_name: string;
  account_number: string | null;
  account_holder: string | null;
  qr_image: string | null;
  sort_order: number;
  is_active: boolean;
}

export function EnvelopeEditor({ invitationId }: { invitationId: number }) {
  const [envelopes, setEnvelopes] = useState<Envelope[]>([]);
  const [dialogOpen, setDialogOpen] = useState(false);
  const [editing, setEditing] = useState<Envelope | null>(null);
  const [type, setType] = useState<EnvelopeType>("bank");
  const [providerName, setProviderName] = useState("");
  const [accountNumber, setAccountNumber] = useState("");
  const [accountHolder, setAccountHolder] = useState("");
  const [qrImage, setQrImage] = useState<File | null>(null);
  const [isActive, setIsActive] = useState(true);
  const [saving, setSaving] = useState(false);

  async function load() {
    try {
      const res = await customerApi.get<{ data: Envelope[] }>(`/invitations/${invitationId}/envelopes`);
      setEnvelopes(res.data);
    } catch (err) {
      toast.error(err instanceof CustomerApiError ? err.message : "Gagal memuat amplop digital.");
    }
  }

  useEffect(() => {
    load();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [invitationId]);

  function openCreate() {
    setEditing(null);
    setType("bank");
    setProviderName("");
    setAccountNumber("");
    setAccountHolder("");
    setQrImage(null);
    setIsActive(true);
    setDialogOpen(true);
  }

  function openEdit(item: Envelope) {
    setEditing(item);
    setType(item.type);
    setProviderName(item.provider_name);
    setAccountNumber(item.account_number ?? "");
    setAccountHolder(item.account_holder ?? "");
    setQrImage(null);
    setIsActive(item.is_active);
    setDialogOpen(true);
  }

  async function handleSubmit(e: FormEvent) {
    e.preventDefault();
    setSaving(true);

    const form = new FormData();
    form.append("type", type);
    if (type !== "qris") {
      form.append("provider_name", providerName);
      form.append("account_number", accountNumber);
      form.append("account_holder", accountHolder);
    }
    if (qrImage) form.append("qr_image", qrImage);
    form.append("is_active", isActive ? "1" : "0");

    try {
      if (editing) {
        form.append("_method", "PUT");
        await customerApi.post(`/invitations/${invitationId}/envelopes/${editing.id}`, form);
        toast.success("Amplop digital berhasil diperbarui.");
      } else {
        await customerApi.post(`/invitations/${invitationId}/envelopes`, form);
        toast.success("Amplop digital berhasil ditambahkan.");
      }
      setDialogOpen(false);
      await load();
    } catch (err) {
      if (err instanceof CustomerApiError && err.errors) {
        Object.values(err.errors).flat().forEach((msg) => toast.error(msg));
      } else {
        toast.error(err instanceof CustomerApiError ? err.message : "Gagal menyimpan amplop digital.");
      }
    } finally {
      setSaving(false);
    }
  }

  async function handleDelete(item: Envelope) {
    if (!confirm(`Hapus amplop "${item.provider_name}"?`)) return;
    try {
      await customerApi.delete(`/invitations/${invitationId}/envelopes/${item.id}`);
      toast.success("Amplop digital berhasil dihapus.");
      await load();
    } catch (err) {
      toast.error(err instanceof CustomerApiError ? err.message : "Gagal menghapus amplop digital.");
    }
  }

  return (
    <Card>
      <CardHeader className="flex flex-row items-center justify-between space-y-0">
        <CardTitle>Amplop Digital</CardTitle>
        <Button onClick={openCreate}>+ Tambah Amplop</Button>
      </CardHeader>
      <CardContent>
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Tipe</TableHead>
              <TableHead>Penyedia</TableHead>
              <TableHead>No. Rekening</TableHead>
              <TableHead>Aktif</TableHead>
              <TableHead className="text-right">Aksi</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {envelopes.map((item) => (
              <TableRow key={item.id}>
                <TableCell>{item.type}</TableCell>
                <TableCell className="font-medium">{item.provider_name}</TableCell>
                <TableCell>{item.account_number ?? "—"}</TableCell>
                <TableCell>
                  <Badge variant={item.is_active ? "default" : "outline"}>{item.is_active ? "Ya" : "Tidak"}</Badge>
                </TableCell>
                <TableCell className="space-x-1 text-right">
                  <Button variant="outline" size="sm" onClick={() => openEdit(item)}>
                    Edit
                  </Button>
                  <Button variant="destructive" size="sm" onClick={() => handleDelete(item)}>
                    Hapus
                  </Button>
                </TableCell>
              </TableRow>
            ))}
            {envelopes.length === 0 && (
              <TableRow>
                <TableCell colSpan={5} className="text-center text-muted-foreground">
                  Belum ada amplop digital.
                </TableCell>
              </TableRow>
            )}
          </TableBody>
        </Table>
      </CardContent>

      <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
        <DialogContent className="max-h-[90vh] overflow-y-auto">
          <DialogHeader>
            <DialogTitle>{editing ? "Edit Amplop" : "Tambah Amplop"}</DialogTitle>
          </DialogHeader>
          <form onSubmit={handleSubmit} className="space-y-4">
            <div className="space-y-2">
              <Label>Tipe</Label>
              <Select value={type} onValueChange={(v) => setType(v as EnvelopeType)}>
                <SelectTrigger>
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="bank">Rekening Bank</SelectItem>
                  <SelectItem value="ewallet">E-Wallet</SelectItem>
                  <SelectItem value="qris">QRIS</SelectItem>
                </SelectContent>
              </Select>
            </div>

            {type === "bank" && (
              <div className="space-y-2">
                <Label>Nama Bank</Label>
                <Input
                  required
                  placeholder="BCA, Mandiri, BNI, dst."
                  value={providerName}
                  onChange={(e) => setProviderName(e.target.value)}
                />
              </div>
            )}
            {type === "ewallet" && (
              <div className="space-y-2">
                <Label>Penyedia E-Wallet</Label>
                <Select value={providerName || undefined} onValueChange={setProviderName}>
                  <SelectTrigger>
                    <SelectValue placeholder="Pilih penyedia" />
                  </SelectTrigger>
                  <SelectContent>
                    {EWALLET_PROVIDERS.map((p) => (
                      <SelectItem key={p} value={p}>
                        {p}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
            )}
            {type !== "qris" && (
              <div className="grid gap-4 sm:grid-cols-2">
                <div className="space-y-2">
                  <Label>No. Rekening/HP</Label>
                  <Input required value={accountNumber} onChange={(e) => setAccountNumber(e.target.value)} />
                </div>
                <div className="space-y-2">
                  <Label>Atas Nama</Label>
                  <Input required value={accountHolder} onChange={(e) => setAccountHolder(e.target.value)} />
                </div>
              </div>
            )}
            {type === "qris" && (
              <div className="space-y-2">
                <Label>Gambar QRIS {editing && "(kosongkan jika tidak diganti)"}</Label>
                {editing?.qr_image && (
                  <img src={editing.qr_image} alt="QRIS" className="h-32 w-32 object-contain" />
                )}
                <Input
                  type="file"
                  accept="image/*"
                  required={!editing}
                  onChange={(e) => setQrImage(e.target.files?.[0] ?? null)}
                />
              </div>
            )}
            <div className="flex items-center justify-between rounded-md border p-3">
              <Label htmlFor="envelope_active">Aktif</Label>
              <Switch id="envelope_active" checked={isActive} onCheckedChange={setIsActive} />
            </div>
            <DialogFooter>
              <Button type="submit" disabled={saving}>
                {saving ? "Menyimpan..." : "Simpan"}
              </Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>
    </Card>
  );
}
