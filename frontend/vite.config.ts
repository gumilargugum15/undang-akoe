import path from "node:path";
import { defineConfig } from "vite";
import react from "@vitejs/plugin-react";
import tailwindcss from "@tailwindcss/vite";
import tsConfigPaths from "vite-tsconfig-paths";
import { tanstackRouter } from "@tanstack/router-plugin/vite";

// Plain Vite SPA config — no server runtime, no SSR, no Nitro.
// `vite build` outputs a static `dist/` that can be served by any static host.
export default defineConfig({
  plugins: [
    tanstackRouter({ target: "react", autoCodeSplitting: true }),
    react(),
    tailwindcss(),
    tsConfigPaths({ projects: ["./tsconfig.json"] }),
  ],
  resolve: {
    alias: {
      "@": path.resolve(process.cwd(), "./src"),
    },
  },
  css: {
    transformer: "lightningcss",
  },
  server: {
    host: "::",
    port: 8080,
    // Fail fast instead of silently drifting to 8081/8082 — the backend's
    // CORS_ALLOWED_ORIGINS (backend/.env) only whitelists :5173 and :8080,
    // so a silent port change here manifests downstream as a confusing
    // "blocked by CORS policy" error on every API call instead.
    strictPort: true,
  },
  build: {
    outDir: "dist",
  },
});
