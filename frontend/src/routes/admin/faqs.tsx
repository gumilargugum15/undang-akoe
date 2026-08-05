import { createFileRoute } from "@tanstack/react-router";
import { useEffect, useState, type FormEvent } from "react";
import { toast } from "sonner";
import { AdminShell } from "@/components/admin/admin-shell";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
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
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { adminApi, AdminApiError } from "@/lib/admin-api";
import { useRequireAdmin } from "@/lib/admin-auth";

export const Route = createFileRoute("/admin/faqs")({
  component: AdminFaqsPage,
});

interface Faq {
  id: number;
  question: string;
  answer: string;
  category: string | null;
  sort_order: number;
  is_active: boolean;
}

interface FormValues {
  question: string;
  answer: string;
  category: string;
  sort_order: string;
  is_active: boolean;
}

const EMPTY_FORM: FormValues = {
  question: "",
  answer: "",
  category: "",
  sort_order: "0",
  is_active: true,
};

function toFormValues(faq: Faq): FormValues {
  return {
    question: faq.question,
    answer: faq.answer,
    category: faq.category ?? "",
    sort_order: String(faq.sort_order),
    is_active: faq.is_active,
  };
}

function AdminFaqsPage() {
  const { session, ready } = useRequireAdmin();
  const [faqs, setFaqs] = useState<Faq[]>([]);
  const [loading, setLoading] = useState(false);
  const [dialogOpen, setDialogOpen] = useState(false);
  const [editing, setEditing] = useState<Faq | null>(null);
  const [values, setValues] = useState<FormValues>(EMPTY_FORM);
  const [saving, setSaving] = useState(false);

  async function loadFaqs() {
    setLoading(true);
    try {
      const res = await adminApi.get<{ data: Faq[] }>("/faqs");
      setFaqs(res.data);
    } catch (err) {
      toast.error(err instanceof AdminApiError ? err.message : "Gagal memuat daftar FAQ.");
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    if (ready) loadFaqs();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [ready]);

  if (!ready || !session) return null;

  function openCreate() {
    setEditing(null);
    setValues(EMPTY_FORM);
    setDialogOpen(true);
  }

  function openEdit(faq: Faq) {
    setEditing(faq);
    setValues(toFormValues(faq));
    setDialogOpen(true);
  }

  function update<K extends keyof FormValues>(key: K, value: FormValues[K]) {
    setValues((v) => ({ ...v, [key]: value }));
  }

  async function handleSubmit(e: FormEvent) {
    e.preventDefault();
    setSaving(true);

    const payload = {
      question: values.question,
      answer: values.answer,
      category: values.category || null,
      sort_order: Number(values.sort_order || 0),
      is_active: values.is_active,
    };

    try {
      if (editing) {
        await adminApi.put(`/faqs/${editing.id}`, payload);
        toast.success("FAQ berhasil diperbarui.");
      } else {
        await adminApi.post("/faqs", payload);
        toast.success("FAQ berhasil ditambahkan.");
      }
      setDialogOpen(false);
      await loadFaqs();
    } catch (err) {
      if (err instanceof AdminApiError && err.errors) {
        Object.values(err.errors)
          .flat()
          .forEach((msg) => toast.error(msg));
      } else {
        toast.error(err instanceof AdminApiError ? err.message : "Gagal menyimpan FAQ.");
      }
    } finally {
      setSaving(false);
    }
  }

  async function handleDelete(faq: Faq) {
    if (!confirm("Hapus FAQ ini?")) return;
    try {
      await adminApi.delete(`/faqs/${faq.id}`);
      toast.success("FAQ berhasil dihapus.");
      await loadFaqs();
    } catch (err) {
      toast.error(err instanceof AdminApiError ? err.message : "Gagal menghapus FAQ.");
    }
  }

  return (
    <AdminShell user={session.user}>
      <Card>
        <CardHeader className="flex flex-row items-center justify-between space-y-0">
          <CardTitle>FAQ</CardTitle>
          <Button onClick={openCreate}>+ Tambah FAQ</Button>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Pertanyaan</TableHead>
                <TableHead>Kategori</TableHead>
                <TableHead>Aktif</TableHead>
                <TableHead className="text-right">Aksi</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {faqs.map((faq) => (
                <TableRow key={faq.id}>
                  <TableCell className="max-w-md font-medium">{faq.question}</TableCell>
                  <TableCell>
                    {faq.category && <Badge variant="secondary">{faq.category}</Badge>}
                  </TableCell>
                  <TableCell>
                    <Badge variant={faq.is_active ? "default" : "outline"}>
                      {faq.is_active ? "Ya" : "Tidak"}
                    </Badge>
                  </TableCell>
                  <TableCell className="space-x-1 text-right">
                    <Button variant="outline" size="sm" onClick={() => openEdit(faq)}>
                      Edit
                    </Button>
                    <Button variant="destructive" size="sm" onClick={() => handleDelete(faq)}>
                      Hapus
                    </Button>
                  </TableCell>
                </TableRow>
              ))}
              {faqs.length === 0 && !loading && (
                <TableRow>
                  <TableCell colSpan={4} className="text-center text-muted-foreground">
                    Belum ada FAQ.
                  </TableCell>
                </TableRow>
              )}
            </TableBody>
          </Table>
        </CardContent>
      </Card>

      <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
        <DialogContent className="max-h-[90vh] overflow-y-auto">
          <DialogHeader>
            <DialogTitle>{editing ? "Edit FAQ" : "Tambah FAQ"}</DialogTitle>
          </DialogHeader>
          <form onSubmit={handleSubmit} className="space-y-4">
            <div className="space-y-2">
              <Label>Pertanyaan</Label>
              <Input required value={values.question} onChange={(e) => update("question", e.target.value)} />
            </div>
            <div className="space-y-2">
              <Label>Jawaban</Label>
              <Textarea
                required
                rows={4}
                value={values.answer}
                onChange={(e) => update("answer", e.target.value)}
              />
            </div>
            <div className="grid grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label>Kategori (opsional)</Label>
                <Input
                  placeholder="umum, pembayaran, tema..."
                  value={values.category}
                  onChange={(e) => update("category", e.target.value)}
                />
              </div>
              <div className="space-y-2">
                <Label>Urutan Tampil</Label>
                <Input
                  type="number"
                  min={0}
                  value={values.sort_order}
                  onChange={(e) => update("sort_order", e.target.value)}
                />
              </div>
            </div>
            <div className="flex items-center justify-between rounded-md border p-3">
              <Label htmlFor="faq_active">Aktif</Label>
              <Switch
                id="faq_active"
                checked={values.is_active}
                onCheckedChange={(v) => update("is_active", v)}
              />
            </div>
            <DialogFooter>
              <Button type="submit" disabled={saving}>
                {saving ? "Menyimpan..." : "Simpan"}
              </Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>
    </AdminShell>
  );
}
