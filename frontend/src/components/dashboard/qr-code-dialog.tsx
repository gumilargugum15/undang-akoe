import { useEffect, useState } from "react";
import QRCode from "qrcode";
import { Button } from "@/components/ui/button";
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";

export function QrCodeDialog({
  open,
  onOpenChange,
  title,
  url,
}: {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  title: string;
  url: string;
}) {
  const [dataUrl, setDataUrl] = useState<string | null>(null);

  useEffect(() => {
    if (!open) return;
    setDataUrl(null);
    QRCode.toDataURL(url, { width: 480, margin: 2 })
      .then(setDataUrl)
      .catch(() => setDataUrl(null));
  }, [open, url]);

  const fileSlug = title.toLowerCase().replace(/[^a-z0-9]+/g, "-").replace(/(^-|-$)/g, "");

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="max-w-sm">
        <DialogHeader>
          <DialogTitle>QR Code Undangan</DialogTitle>
        </DialogHeader>
        <div className="flex flex-col items-center gap-4">
          {dataUrl ? (
            <img src={dataUrl} alt={`QR Code ${title}`} className="size-64 rounded-md border" />
          ) : (
            <div className="flex size-64 items-center justify-center rounded-md border text-sm text-muted-foreground">
              Membuat QR Code...
            </div>
          )}
          <p className="break-all text-center text-xs text-muted-foreground">{url}</p>
          {dataUrl && (
            <a href={dataUrl} download={`qr-undangan-${fileSlug}.png`}>
              <Button>Unduh QR Code</Button>
            </a>
          )}
        </div>
      </DialogContent>
    </Dialog>
  );
}
