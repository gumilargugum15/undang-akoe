import { Link, useNavigate, useRouterState } from "@tanstack/react-router";
import { useEffect, useState, type ReactNode } from "react";
import {
  Bell,
  HelpCircle,
  Image,
  LogOut,
  Package,
  Palette,
  Receipt,
  Sparkles,
  Users,
  Wallet,
} from "lucide-react";
import { Toaster } from "@/components/ui/sonner";
import { Button } from "@/components/ui/button";
import { Separator } from "@/components/ui/separator";
import { Avatar, AvatarFallback } from "@/components/ui/avatar";
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarGroup,
  SidebarGroupContent,
  SidebarGroupLabel,
  SidebarHeader,
  SidebarInset,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  SidebarProvider,
  SidebarRail,
  SidebarTrigger,
} from "@/components/ui/sidebar";
import { ThemeToggle } from "@/components/theme-toggle";
import { adminApi } from "@/lib/admin-api";
import { clearAdminSession, type AdminUser } from "@/lib/admin-auth";
import { getInitials } from "@/lib/utils";

const NAV_ITEMS = [
  { to: "/admin", label: "Tema", icon: Palette, exact: true },
  { to: "/admin/packages", label: "Paket", icon: Package, exact: false },
  { to: "/admin/transactions", label: "Verifikasi Pembayaran", icon: Receipt, exact: false },
  { to: "/admin/payment-settings", label: "Pengaturan Pembayaran", icon: Wallet, exact: false },
  { to: "/admin/users", label: "Pengguna", icon: Users, exact: false },
  { to: "/admin/banners", label: "Banner", icon: Image, exact: false },
  { to: "/admin/faqs", label: "FAQ", icon: HelpCircle, exact: false },
] as const;

/** Header page title — more lenient than the sidebar's own exact/startsWith active-match,
 * since sub-pages like /admin/themes/new aren't in NAV_ITEMS at all. */
function pageTitleFor(pathname: string): string {
  if (pathname.startsWith("/admin/themes")) return "Tema";
  const item = NAV_ITEMS.find((i) => i.to !== "/admin" && pathname.startsWith(i.to));
  return item?.label ?? "Tema";
}

/** Polls the same "Menunggu Verifikasi" count shown as the default filter on
 * /admin/transactions, so the badge here and the queue a click lands on always agree. */
function PaymentVerificationBell() {
  const navigate = useNavigate();
  const [count, setCount] = useState(0);

  useEffect(() => {
    let active = true;

    async function loadCount() {
      try {
        const res = await adminApi.get<{ meta: { total: number } }>(
          "/transactions?awaiting_verification=1&per_page=1",
        );
        if (active) setCount(res.meta.total);
      } catch {
        // decorative indicator — a failed poll just leaves the last known count showing
      }
    }

    loadCount();
    const interval = setInterval(loadCount, 60_000);
    return () => {
      active = false;
      clearInterval(interval);
    };
  }, []);

  return (
    <Button
      variant="ghost"
      size="icon"
      className="relative"
      aria-label={
        count > 0
          ? `${count} pembayaran menunggu verifikasi`
          : "Tidak ada pembayaran menunggu verifikasi"
      }
      onClick={() => navigate({ to: "/admin/transactions" })}
    >
      <Bell className="size-4" />
      {count > 0 && (
        <span className="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-destructive px-1 text-[10px] font-semibold leading-none text-destructive-foreground">
          {count > 99 ? "99+" : count}
        </span>
      )}
    </Button>
  );
}

export function AdminShell({ user, children }: { user: AdminUser; children: ReactNode }) {
  const navigate = useNavigate();
  const pathname = useRouterState({ select: (state) => state.location.pathname });

  async function handleLogout() {
    try {
      await adminApi.post("/auth/logout");
    } catch {
      // token may already be invalid — clear locally regardless
    }
    clearAdminSession();
    navigate({ to: "/admin/login" });
  }

  return (
    <SidebarProvider>
      <Toaster />
      <Sidebar collapsible="icon">
        <SidebarHeader>
          <div className="flex items-center gap-2 px-2 py-1.5">
            <div className="flex size-9 shrink-0 items-center justify-center rounded-xl bg-sidebar-primary text-sidebar-primary-foreground shadow-sm">
              <Sparkles className="size-5" />
            </div>
            <div className="flex flex-col leading-none group-data-[collapsible=icon]:hidden">
              <span className="font-semibold">Undang Akoe</span>
              <span className="text-xs text-muted-foreground">Admin</span>
            </div>
          </div>
        </SidebarHeader>
        <SidebarContent>
          <SidebarGroup>
            <SidebarGroupLabel>Menu</SidebarGroupLabel>
            <SidebarGroupContent>
              <SidebarMenu>
                {NAV_ITEMS.map((item) => {
                  const active = item.exact ? pathname === item.to : pathname.startsWith(item.to);
                  return (
                    <SidebarMenuItem key={item.to}>
                      <SidebarMenuButton asChild isActive={active} tooltip={item.label}>
                        <Link to={item.to}>
                          <item.icon />
                          <span>{item.label}</span>
                        </Link>
                      </SidebarMenuButton>
                    </SidebarMenuItem>
                  );
                })}
              </SidebarMenu>
            </SidebarGroupContent>
          </SidebarGroup>
        </SidebarContent>
        <SidebarFooter>
          <div className="flex items-center justify-between gap-2 px-2 py-1.5">
            <div className="flex min-w-0 flex-col leading-tight group-data-[collapsible=icon]:hidden">
              <span className="truncate text-sm font-medium">{user.name}</span>
              <span className="text-xs text-muted-foreground">Administrator</span>
            </div>
            <Button
              variant="ghost"
              size="icon"
              className="shrink-0"
              onClick={handleLogout}
              aria-label="Keluar"
            >
              <LogOut className="size-4" />
            </Button>
          </div>
        </SidebarFooter>
        <SidebarRail />
      </Sidebar>
      <SidebarInset className="bg-muted/30">
        <header className="flex h-16 shrink-0 items-center gap-3 border-b bg-background px-4 md:px-6">
          <SidebarTrigger className="-ml-1" />
          <Separator orientation="vertical" className="h-5" />
          <div className="min-w-0">
            <p className="text-xs text-muted-foreground">Panel Admin</p>
            <p className="truncate text-sm font-semibold">{pageTitleFor(pathname)}</p>
          </div>
          <div className="ml-auto flex items-center gap-2">
            <PaymentVerificationBell />
            <ThemeToggle />
            <Avatar className="size-9">
              <AvatarFallback className="bg-sidebar-accent text-sm font-semibold text-sidebar-accent-foreground">
                {getInitials(user.name)}
              </AvatarFallback>
            </Avatar>
          </div>
        </header>
        <main className="flex-1 p-4 md:p-6">
          <div className="mx-auto w-full max-w-6xl">{children}</div>
        </main>
      </SidebarInset>
    </SidebarProvider>
  );
}
