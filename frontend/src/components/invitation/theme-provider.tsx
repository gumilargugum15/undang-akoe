import { createContext, useContext, useEffect, useMemo, useState, type ReactNode } from "react";
import { defaultThemeId, themeStyle, themes, type InvitationTheme } from "@/lib/themes";

type Ctx = {
  theme: InvitationTheme;
  themeId: string;
  setThemeId: (id: string) => void;
};

const ThemeCtx = createContext<Ctx | null>(null);

const STORAGE_KEY = "undangan-theme";

/**
 * `fixedTheme` locks the provider to one already-resolved theme (the couple's
 * actual choice from the API, overrides already merged in) and disables
 * switching — used by the real public invitation page. Without it, behavior
 * is unchanged: free switching between the local demo themes, persisted to
 * localStorage, exactly as before (still used by `/` and `/tema`).
 */
export function InvitationThemeProvider({
  children,
  fixedTheme,
}: {
  children: ReactNode;
  fixedTheme?: InvitationTheme;
}) {
  const [themeId, setThemeId] = useState(defaultThemeId);

  useEffect(() => {
    if (fixedTheme) return;
    const saved = window.localStorage.getItem(STORAGE_KEY);
    if (saved && themes[saved]) setThemeId(saved);
  }, [fixedTheme]);

  useEffect(() => {
    if (fixedTheme) return;
    window.localStorage.setItem(STORAGE_KEY, themeId);
  }, [fixedTheme, themeId]);

  const theme = fixedTheme ?? themes[themeId] ?? themes[defaultThemeId];
  const value = useMemo(
    () => ({
      theme,
      themeId: theme.id,
      setThemeId: fixedTheme
        ? () => {
            throw new Error("Tema undangan yang dipublikasikan tidak dapat diganti dari halaman ini.");
          }
        : setThemeId,
    }),
    [theme, fixedTheme],
  );

  return (
    <ThemeCtx.Provider value={value}>
      <div
        style={themeStyle(theme)}
        className="min-h-screen"
        data-theme={theme.id}
      >
        {children}
      </div>
    </ThemeCtx.Provider>
  );
}

export function useInvitationTheme() {
  const ctx = useContext(ThemeCtx);
  if (!ctx) throw new Error("useInvitationTheme must be used inside InvitationThemeProvider");
  return ctx;
}
