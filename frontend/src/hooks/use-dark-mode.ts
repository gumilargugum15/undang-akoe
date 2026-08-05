import { useEffect, useState } from "react";

const STORAGE_KEY = "undangan-color-scheme";

type ColorScheme = "light" | "dark";

function applyScheme(scheme: ColorScheme) {
  document.documentElement.classList.toggle("dark", scheme === "dark");
}

function resolveInitialScheme(): ColorScheme {
  const stored = window.localStorage.getItem(STORAGE_KEY);
  if (stored === "light" || stored === "dark") return stored;
  return window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
}

/** Scoped to the admin/dashboard chrome only — the public invitation pages have their own
 * per-invitation `--inv-*` theme system and are never touched by this toggle. */
export function useDarkMode() {
  const [scheme, setScheme] = useState<ColorScheme>("light");

  useEffect(() => {
    const initial = resolveInitialScheme();
    setScheme(initial);
    applyScheme(initial);
  }, []);

  function toggle() {
    setScheme((prev) => {
      const next: ColorScheme = prev === "dark" ? "light" : "dark";
      window.localStorage.setItem(STORAGE_KEY, next);
      applyScheme(next);
      return next;
    });
  }

  return { scheme, toggle };
}
