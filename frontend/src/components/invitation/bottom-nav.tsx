import { motion } from "motion/react";
import type { LucideIcon } from "lucide-react";

export type NavItem = {
  id: string;
  label: string;
  icon: LucideIcon;
};

export function BottomNav({
  items,
  activeId,
  onSelect,
}: {
  items: NavItem[];
  activeId: string;
  onSelect: (id: string) => void;
}) {
  return (
    <nav
      className="fixed inset-x-0 bottom-0 z-40 border-t backdrop-blur"
      style={{
        backgroundColor: "color-mix(in srgb, var(--inv-surface) 92%, transparent)",
        borderColor: "var(--inv-border)",
        paddingBottom: "max(0.5rem, env(safe-area-inset-bottom))",
      }}
    >
      <div className="mx-auto flex max-w-3xl items-stretch overflow-x-auto px-1 [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">
        {items.map((item) => {
          const active = item.id === activeId;
          const Icon = item.icon;
          return (
            <button
              key={item.id}
              type="button"
              onClick={() => onSelect(item.id)}
              aria-current={active ? "page" : undefined}
              className="relative flex min-w-16 flex-1 shrink-0 flex-col items-center gap-1 px-1 pb-1.5 pt-2.5 transition-colors"
              style={{ color: active ? "var(--inv-primary)" : "var(--inv-muted)" }}
            >
              {active && (
                <motion.span
                  layoutId="bottomNavPill"
                  className="absolute inset-x-1.5 top-1 h-8 rounded-full"
                  style={{ backgroundColor: "var(--inv-bg-alt)" }}
                  transition={{ type: "spring", stiffness: 380, damping: 32 }}
                />
              )}
              <Icon className="relative size-5 shrink-0" strokeWidth={active ? 2.4 : 2} />
              <span className="relative truncate font-body text-[10px] uppercase tracking-wide">
                {item.label}
              </span>
            </button>
          );
        })}
      </div>
    </nav>
  );
}
