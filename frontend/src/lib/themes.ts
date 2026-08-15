export type OrnamentKind = "floral" | "line" | "leaf" | "shimmer" | "bouquet" | "crescent";
export type RevealKind = "fade" | "slide" | "zoom" | "blur" | "flip" | "curtain" | "bounce";

export type ThemeTokens = {
  bg: string;
  bgAlt: string;
  surface: string;
  primary: string;
  primaryFg: string;
  secondary: string;
  accent: string;
  text: string;
  muted: string;
  border: string;
};

export type InvitationTheme = {
  id: string;
  name: string;
  tagline: string;
  ornament: OrnamentKind;
  reveal: RevealKind;
  radius: string;
  cardRadius: string;
  shadow: string;
  buttonShadow: string;
  letterSpacing: string;
  headWeight: string;
  fonts: { head: string; body: string; script: string };
  tokens: ThemeTokens;
  /** small palette used by the theme picker preview */
  swatch: string[];
  texture: string;
  /** "circle" renders PersonCard photos as round avatars instead of the default
   * rectangular card — undefined behaves exactly like "rect" for every existing theme. */
  personCardStyle?: "rect" | "circle";
};

export const themes: Record<string, InvitationTheme> = {
  elegant: {
    id: "elegant",
    name: "Elegant Classic",
    tagline: "Gold, krem & maroon dengan ornamen floral klasik",
    ornament: "floral",
    reveal: "fade",
    radius: "0.5rem",
    cardRadius: "1.75rem 1.75rem 1.75rem 1.75rem",
    shadow: "0 24px 60px -35px rgba(88, 34, 44, 0.45)",
    buttonShadow: "0 14px 30px -14px rgba(123, 45, 59, 0.65)",
    letterSpacing: "0.06em",
    headWeight: "500",
    fonts: {
      head: '"Playfair Display", serif',
      body: '"Cormorant Garamond", serif',
      script: '"Playfair Display", serif',
    },
    tokens: {
      bg: "#fbf6ec",
      bgAlt: "#f5ead7",
      surface: "#fffdf8",
      primary: "#7b2d3b",
      primaryFg: "#fff8ee",
      secondary: "#b08d57",
      accent: "#c9a227",
      text: "#3a2429",
      muted: "#8a6f6a",
      border: "#e2cfae",
    },
    swatch: ["#fbf6ec", "#e2cfae", "#b08d57", "#7b2d3b"],
    texture:
      "radial-gradient(circle at 15% 10%, rgba(201,162,39,0.10), transparent 45%), radial-gradient(circle at 85% 80%, rgba(123,45,59,0.08), transparent 50%)",
  },
  minimalist: {
    id: "minimalist",
    name: "Modern Minimalist",
    tagline: "Monokrom pastel, garis bersih, banyak ruang kosong",
    ornament: "line",
    reveal: "slide",
    radius: "0.125rem",
    cardRadius: "0.125rem",
    shadow: "0 1px 0 0 rgba(20,20,20,0.08)",
    buttonShadow: "none",
    letterSpacing: "0.18em",
    headWeight: "500",
    fonts: {
      head: '"Plus Jakarta Sans", sans-serif',
      body: '"Plus Jakarta Sans", sans-serif',
      script: '"Plus Jakarta Sans", sans-serif',
    },
    tokens: {
      bg: "#eaf3fb",
      bgAlt: "#dfedf9",
      surface: "#ffffff",
      primary: "#1c1c1c",
      primaryFg: "#ffffff",
      secondary: "#8fa6b8",
      accent: "#bcd6ea",
      text: "#1c1c1c",
      muted: "#72838f",
      border: "#d7e6f2",
    },
    swatch: ["#eaf3fb", "#d7e6f2", "#bcd6ea", "#1c1c1c"],
    texture:
      "linear-gradient(180deg, rgba(200,226,247,0.85) 0%, rgba(234,243,251,0.6) 45%, rgba(255,255,255,0.9) 100%), radial-gradient(ellipse 80% 45% at 50% 100%, rgba(255,255,255,0.95), transparent 70%), radial-gradient(circle at 18% 88%, rgba(255,255,255,0.8), transparent 42%), radial-gradient(circle at 82% 92%, rgba(255,255,255,0.7), transparent 45%), radial-gradient(circle at 90% 8%, rgba(255,255,255,0.35), transparent 40%)",
  },
  rustic: {
    id: "rustic",
    name: "Rustic Garden",
    tagline: "Sage & terracotta, judul tulisan tangan, dedaunan liar",
    ornament: "leaf",
    reveal: "zoom",
    radius: "1.5rem",
    cardRadius: "2.5rem 0.75rem 2.5rem 0.75rem",
    shadow: "0 26px 50px -30px rgba(60,80,52,0.5)",
    buttonShadow: "0 12px 26px -12px rgba(192,113,75,0.6)",
    letterSpacing: "0.02em",
    headWeight: "400",
    fonts: {
      head: '"Caveat", cursive',
      body: '"Inter", sans-serif',
      script: '"Caveat", cursive',
    },
    tokens: {
      bg: "#f6f3e9",
      bgAlt: "#e9e6d5",
      surface: "#fffdf6",
      primary: "#4b6043",
      primaryFg: "#f8f6ec",
      secondary: "#c0714b",
      accent: "#8fa37f",
      text: "#33402f",
      muted: "#77836d",
      border: "#d7d5bf",
    },
    swatch: ["#f6f3e9", "#8fa37f", "#c0714b", "#4b6043"],
    texture:
      "radial-gradient(circle at 80% 12%, rgba(143,163,127,0.20), transparent 42%), radial-gradient(circle at 8% 85%, rgba(192,113,75,0.14), transparent 45%)",
  },
  luxury: {
    id: "luxury",
    name: "Dark Luxury",
    tagline: "Hitam, navy & emas dengan kilau shimmer halus",
    ornament: "shimmer",
    reveal: "blur",
    radius: "0.25rem",
    cardRadius: "0.25rem",
    shadow: "0 30px 70px -40px rgba(212,175,55,0.55)",
    buttonShadow: "0 0 28px -6px rgba(212,175,55,0.55)",
    letterSpacing: "0.24em",
    headWeight: "600",
    fonts: {
      head: '"Marcellus", serif',
      body: '"Inter", sans-serif',
      script: '"Marcellus", serif',
    },
    tokens: {
      bg: "#080b13",
      bgAlt: "#0f1626",
      surface: "#111a2b",
      primary: "#d4af37",
      primaryFg: "#0a0d16",
      secondary: "#1e2c4a",
      accent: "#e7d08a",
      text: "#f0ece2",
      muted: "#9aa2b4",
      border: "#2a3450",
    },
    swatch: ["#080b13", "#1e2c4a", "#d4af37", "#e7d08a"],
    texture:
      "radial-gradient(circle at 50% -10%, rgba(212,175,55,0.16), transparent 55%), radial-gradient(circle at 10% 90%, rgba(30,44,74,0.6), transparent 55%)",
  },
  blueBotanical: {
    id: "blue-botanical",
    name: "Blue Botanical",
    tagline: "Biru pastel lembut dengan ornamen floral & foto pasangan bulat",
    ornament: "floral",
    reveal: "fade",
    radius: "1.25rem",
    cardRadius: "2rem",
    shadow: "0 24px 55px -32px rgba(47, 93, 138, 0.35)",
    buttonShadow: "0 14px 28px -14px rgba(47, 93, 138, 0.45)",
    letterSpacing: "0.04em",
    headWeight: "600",
    fonts: {
      head: '"Playfair Display", serif',
      body: '"Plus Jakarta Sans", sans-serif',
      script: '"Playfair Display", serif',
    },
    tokens: {
      bg: "#eef5fb",
      bgAlt: "#e3eef8",
      surface: "#ffffff",
      primary: "#2f5d8a",
      primaryFg: "#ffffff",
      secondary: "#7ea3c4",
      accent: "#a9c4dd",
      text: "#2b3a4a",
      muted: "#6b8098",
      border: "#d7e5f0",
    },
    swatch: ["#eef5fb", "#d7e5f0", "#7ea3c4", "#2f5d8a"],
    texture:
      "radial-gradient(circle at 12% 8%, rgba(126,163,196,0.18), transparent 45%), radial-gradient(circle at 88% 85%, rgba(169,196,221,0.22), transparent 50%)",
    personCardStyle: "circle",
  },
  lavenderBouquet: {
    id: "lavender-bouquet",
    name: "Lavender Bouquet",
    tagline: "Ungu lavender lembut dengan buket mawar rimbun di setiap sudut",
    ornament: "bouquet",
    reveal: "fade",
    radius: "1rem",
    cardRadius: "1.75rem",
    shadow: "0 24px 55px -32px rgba(91, 58, 126, 0.45)",
    buttonShadow: "0 14px 30px -14px rgba(91, 58, 126, 0.55)",
    letterSpacing: "0.05em",
    headWeight: "500",
    fonts: {
      head: '"Playfair Display", serif',
      body: '"Cormorant Garamond", serif',
      script: '"Playfair Display", serif',
    },
    tokens: {
      bg: "#f6f3f9",
      bgAlt: "#ece4f3",
      surface: "#ffffff",
      primary: "#5b3a7e",
      primaryFg: "#ffffff",
      secondary: "#8a6bb0",
      accent: "#c9b8e0",
      text: "#3a2e46",
      muted: "#8a7f97",
      border: "#ddd3ea",
    },
    swatch: ["#f6f3f9", "#ddd3ea", "#8a6bb0", "#5b3a7e"],
    texture:
      "radial-gradient(circle at 15% 12%, rgba(91,58,126,0.10), transparent 45%), radial-gradient(circle at 85% 88%, rgba(138,107,176,0.14), transparent 50%), radial-gradient(circle at 50% 50%, rgba(180,170,190,0.06), transparent 60%)",
  },
  walimatulKhitan: {
    id: "walimatul-khitan",
    name: "Walimatul Khitan",
    tagline: "Zamrud & emas islami dengan bulan sabit, bintang, dan lengkung mihrab",
    ornament: "crescent",
    reveal: "fade",
    radius: "0.75rem",
    // Rounded top, flat bottom — an arch/mihrab silhouette on every card, not just
    // a generic rounded rectangle (see ArchWatermark in ornaments.tsx for the same idea).
    cardRadius: "3rem 3rem 1rem 1rem",
    shadow: "0 24px 55px -32px rgba(15, 81, 50, 0.4)",
    buttonShadow: "0 14px 30px -14px rgba(201, 162, 39, 0.5)",
    letterSpacing: "0.06em",
    headWeight: "600",
    fonts: {
      head: '"Marcellus", serif',
      body: '"Cormorant Garamond", serif',
      script: '"Playfair Display", serif',
    },
    tokens: {
      bg: "#faf5e6",
      bgAlt: "#f2ead2",
      surface: "#ffffff",
      primary: "#0f5132",
      primaryFg: "#fdf6e3",
      secondary: "#b08d57",
      accent: "#d4af37",
      text: "#1c2b22",
      muted: "#6b7d70",
      border: "#e6d9b0",
    },
    swatch: ["#faf5e6", "#e6d9b0", "#b08d57", "#0f5132"],
    texture:
      "radial-gradient(circle at 15% 10%, rgba(212,175,55,0.14), transparent 45%), radial-gradient(circle at 85% 85%, rgba(15,81,50,0.10), transparent 48%)",
  },
  walimatulUrsy: {
    id: "walimatul-ursy",
    name: "Walimatul Ursy",
    tagline:
      "Zamrud malam & emas islami dengan lengkung mihrab, bulan sabit, dan lentera bercahaya",
    ornament: "crescent",
    reveal: "blur",
    radius: "0.5rem",
    // Same arch/mihrab silhouette as Walimatul Khitan's cards — both dark and light
    // Islamic themes share this shape so it reads as one family, not two unrelated skins.
    cardRadius: "3rem 3rem 1rem 1rem",
    shadow: "0 30px 70px -40px rgba(212, 175, 55, 0.4)",
    buttonShadow: "0 0 28px -6px rgba(212, 175, 55, 0.55)",
    letterSpacing: "0.08em",
    headWeight: "600",
    fonts: {
      head: '"Marcellus", serif',
      body: '"Cormorant Garamond", serif',
      script: '"Playfair Display", serif',
    },
    tokens: {
      bg: "#0a1f16",
      bgAlt: "#0f2a1e",
      surface: "#12332a",
      primary: "#d4af37",
      primaryFg: "#0a1f16",
      secondary: "#1f4a38",
      accent: "#e7d08a",
      text: "#f0ece2",
      muted: "#8fa89a",
      border: "#24473a",
    },
    swatch: ["#0a1f16", "#1f4a38", "#d4af37", "#e7d08a"],
    texture:
      "radial-gradient(circle at 50% -10%, rgba(212,175,55,0.15), transparent 55%), radial-gradient(circle at 10% 90%, rgba(15,81,50,0.5), transparent 55%)",
  },
};

export const themeList = Object.values(themes);
export const defaultThemeId = "elegant";

export function themeStyle(theme: InvitationTheme): React.CSSProperties {
  const t = theme.tokens;
  return {
    "--inv-bg": t.bg,
    "--inv-bg-alt": t.bgAlt,
    "--inv-surface": t.surface,
    "--inv-primary": t.primary,
    "--inv-primary-fg": t.primaryFg,
    "--inv-secondary": t.secondary,
    "--inv-accent": t.accent,
    "--inv-text": t.text,
    "--inv-muted": t.muted,
    "--inv-border": t.border,
    "--inv-radius": theme.radius,
    "--inv-card-radius": theme.cardRadius,
    "--inv-shadow": theme.shadow,
    "--inv-btn-shadow": theme.buttonShadow,
    "--inv-tracking": theme.letterSpacing,
    "--inv-head-weight": theme.headWeight,
    "--inv-font-head": theme.fonts.head,
    "--inv-font-body": theme.fonts.body,
    "--inv-font-script": theme.fonts.script,
    "--inv-texture": theme.texture,
  } as React.CSSProperties;
}
