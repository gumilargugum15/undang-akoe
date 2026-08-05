import { useEffect, useState, type FormEvent } from "react";
import { toast } from "sonner";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Checkbox } from "@/components/ui/checkbox";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { customerApi, CustomerApiError } from "@/lib/customer-api";

interface Guest {
  id: number;
  name: string;
  phone: string | null;
  category: string | null;
  slug_token: string;
  is_checked_in: boolean;
}

/** Normalizes a local Indonesian number ("08...") to the international digits-only
 * form wa.me requires ("62..."), passing through numbers already in that shape. */
function toWhatsAppNumber(phone: string): string {
  const digits = phone.replace(/\D/g, "");
  if (digits.startsWith("0")) return `62${digits.slice(1)}`;
  if (digits.startsWith("62")) return digits;
  return `62${digits}`;
}

export function GuestListEditor({
  invitationId,
  publicUrl,
}: {
  invitationId: number;
  publicUrl: string;
}) {
  const [guests, setGuests] = useState<Guest[]>([]);
  const [name, setName] = useState("");
  const [phone, setPhone] = useState("");
  const [category, setCategory] = useState("");
  const [saving, setSaving] = useState(false);
  const [selectedIds, setSelectedIds] = useState<Set<number>>(new Set());

  async function load() {
    try {
      const res = await customerApi.get<{ data: Guest[] }>(`/invitations/${invitationId}/guests`);
      setGuests(res.data);
    } catch (err) {
      toast.error(err instanceof CustomerApiError ? err.message : "Gagal memuat daftar tamu.");
    }
  }

  useEffect(() => {
    load();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [invitationId]);

  async function handleSubmit(e: FormEvent) {
    e.preventDefault();
    setSaving(true);
    try {
      await customerApi.post(`/invitations/${invitationId}/guests`, {
        name,
        phone: phone || undefined,
        category: category || undefined,
      });
      toast.success("Tamu berhasil ditambahkan.");
      setName("");
      setPhone("");
      setCategory("");
      await load();
    } catch (err) {
      if (err instanceof CustomerApiError && err.errors) {
        Object.values(err.errors)
          .flat()
          .forEach((msg) => toast.error(msg));
      } else {
        toast.error(err instanceof CustomerApiError ? err.message : "Gagal menambahkan tamu.");
      }
    } finally {
      setSaving(false);
    }
  }

  async function handleDelete(guest: Guest) {
    if (!confirm(`Hapus tamu "${guest.name}"?`)) return;
    try {
      await customerApi.delete(`/invitations/${invitationId}/guests/${guest.id}`);
      toast.success("Tamu berhasil dihapus.");
      setSelectedIds((prev) => {
        const next = new Set(prev);
        next.delete(guest.id);
        return next;
      });
      await load();
    } catch (err) {
      toast.error(err instanceof CustomerApiError ? err.message : "Gagal menghapus tamu.");
    }
  }

  function personalLink(guest: Guest): string {
    return `${publicUrl}?to=${guest.slug_token}`;
  }

  async function handleCopyLink(guest: Guest) {
    await navigator.clipboard.writeText(personalLink(guest));
    toast.success("Link personal disalin.");
  }

  function waHref(guest: Guest): string | null {
    if (!guest.phone) return null;
    const message = `Yth. ${guest.name}, kami mengundang Anda untuk hadir di acara kami. Silakan buka undangan berikut: ${personalLink(guest)}`;
    return `https://wa.me/${toWhatsAppNumber(guest.phone)}?text=${encodeURIComponent(message)}`;
  }

  const guestsWithPhone = guests.filter((g) => g.phone);
  const allSelectableSelected =
    guestsWithPhone.length > 0 && guestsWithPhone.every((g) => selectedIds.has(g.id));

  function toggleSelected(guestId: number, checked: boolean) {
    setSelectedIds((prev) => {
      const next = new Set(prev);
      if (checked) next.add(guestId);
      else next.delete(guestId);
      return next;
    });
  }

  function toggleSelectAll(checked: boolean) {
    setSelectedIds(checked ? new Set(guestsWithPhone.map((g) => g.id)) : new Set());
  }

  /** WhatsApp has no bulk-send link — this opens one wa.me tab per selected guest instead.
   * Each window.open() runs synchronously inside this click handler (same user gesture), so
   * browsers don't treat the batch as blocked popups the way they would for an async loop. */
  function handleBulkSend() {
    const targets = guests.filter((g) => selectedIds.has(g.id) && g.phone);
    if (targets.length === 0) return;

    targets.forEach((guest) => {
      const href = waHref(guest);
      if (href) window.open(href, "_blank", "noopener,noreferrer");
    });
    toast.success(`Membuka WhatsApp untuk ${targets.length} tamu.`);
  }

  return (
    <Card>
      <CardHeader>
        <CardTitle>Daftar Tamu</CardTitle>
      </CardHeader>
      <CardContent className="space-y-6">
        <p className="text-sm text-muted-foreground">
          Setiap tamu mendapat link undangan personal. Saat tamu membuka link tersebut, namanya akan
          tampil di halaman utama undangan.
        </p>

        <form onSubmit={handleSubmit} className="grid gap-4 sm:grid-cols-3">
          <div className="space-y-2">
            <Label>Nama Tamu</Label>
            <Input required value={name} onChange={(e) => setName(e.target.value)} />
          </div>
          <div className="space-y-2">
            <Label>No. WhatsApp (opsional)</Label>
            <Input
              value={phone}
              onChange={(e) => setPhone(e.target.value)}
              placeholder="08123456789"
            />
          </div>
          <div className="space-y-2">
            <Label>Kategori (opsional)</Label>
            <Input
              value={category}
              onChange={(e) => setCategory(e.target.value)}
              placeholder="VIP, Keluarga, ..."
            />
          </div>
          <Button type="submit" disabled={saving} className="sm:col-span-3 sm:w-fit">
            {saving ? "Menambahkan..." : "Tambah Tamu"}
          </Button>
        </form>

        {guests.length > 0 && (
          <>
            <div className="flex flex-wrap items-center justify-between gap-2 rounded-md border p-3">
              <p className="text-sm text-muted-foreground">
                {selectedIds.size > 0
                  ? `${selectedIds.size} tamu dipilih`
                  : "Pilih tamu (yang punya nomor WhatsApp) untuk kirim link sekaligus"}
              </p>
              <Button
                type="button"
                size="sm"
                disabled={selectedIds.size === 0}
                onClick={handleBulkSend}
              >
                Kirim WhatsApp ke Tamu Terpilih
              </Button>
            </div>

            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead className="w-10">
                    <Checkbox
                      checked={allSelectableSelected}
                      onCheckedChange={(checked) => toggleSelectAll(checked === true)}
                      disabled={guestsWithPhone.length === 0}
                      aria-label="Pilih semua tamu yang punya nomor WhatsApp"
                    />
                  </TableHead>
                  <TableHead>Nama</TableHead>
                  <TableHead>Kategori</TableHead>
                  <TableHead>Aksi</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {guests.map((guest) => (
                  <TableRow key={guest.id}>
                    <TableCell>
                      <Checkbox
                        checked={selectedIds.has(guest.id)}
                        onCheckedChange={(checked) => toggleSelected(guest.id, checked === true)}
                        disabled={!guest.phone}
                        aria-label={`Pilih ${guest.name}`}
                      />
                    </TableCell>
                    <TableCell className="font-medium">{guest.name}</TableCell>
                    <TableCell>
                      {guest.category ? <Badge variant="secondary">{guest.category}</Badge> : "-"}
                    </TableCell>
                    <TableCell className="flex flex-wrap gap-2">
                      <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        onClick={() => handleCopyLink(guest)}
                      >
                        Salin Link
                      </Button>
                      {waHref(guest) && (
                        <Button type="button" size="sm" asChild>
                          <a href={waHref(guest) ?? undefined} target="_blank" rel="noreferrer">
                            Kirim via WhatsApp
                          </a>
                        </Button>
                      )}
                      <Button
                        type="button"
                        variant="destructive"
                        size="sm"
                        onClick={() => handleDelete(guest)}
                      >
                        Hapus
                      </Button>
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </>
        )}
      </CardContent>
    </Card>
  );
}
