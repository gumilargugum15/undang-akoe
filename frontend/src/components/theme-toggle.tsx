import { Moon, Sun } from "lucide-react";
import { Button } from "@/components/ui/button";
import { useDarkMode } from "@/hooks/use-dark-mode";

export function ThemeToggle() {
  const { scheme, toggle } = useDarkMode();

  return (
    <Button variant="ghost" size="icon" onClick={toggle} aria-label="Ganti tema terang/gelap">
      {scheme === "dark" ? <Sun className="size-4" /> : <Moon className="size-4" />}
    </Button>
  );
}
