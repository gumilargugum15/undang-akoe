# Migration Report — TanStack Start/Nitro → Vite React SPA

`frontend/` is now a standard client-side React SPA built with Vite. There is
no server runtime, no SSR, and no Nitro build step. Production builds output
to `frontend/dist/` (a set of static files), not `.output/`.

Stack preserved exactly as requested: React 19, TypeScript, TanStack Router
(SPA mode), TanStack React Query, TailwindCSS v4, Radix UI (via shadcn/ui),
all pages/layouts/components/styles/API calls/business logic.

See `MIGRATION_ANALYSIS.md` for the pre-migration audit this was based on.

---

## 1. Files modified

### Created
| File | Purpose |
| --- | --- |
| `frontend/index.html` | Standard Vite SPA HTML entry (`<div id="root">` + `<script src="/src/main.tsx">`), carries the static default `<title>`/meta/font-preconnect/favicon tags |
| `frontend/src/main.tsx` | Client entry point — `createRoot(...).render(<RouterProvider router={router} />)`, imports `styles.css` |
| `docs/MIGRATION_ANALYSIS.md` | Pre-migration audit and plan |
| `docs/MIGRATION_REPORT.md` | This file |

### Rewritten
| File | Change |
| --- | --- |
| `frontend/vite.config.ts` | Replaced the `@lovable.dev/vite-tanstack-config` wrapper (Start + Nitro + Cloudflare + devtools) with a plain `defineConfig` listing exactly: `@tanstack/router-plugin/vite` (route codegen, SPA target, `autoCodeSplitting: true`), `@vitejs/plugin-react`, `@tailwindcss/vite`, `vite-tsconfig-paths`. `build.outDir: "dist"`, `css.transformer: "lightningcss"` (was already in the dependency tree via `vite` itself). Dev server kept on `host: "::", port: 8080` to match prior behavior. |
| `frontend/src/router.tsx` | Was a `getRouter()` factory (SSR needs a fresh router per request). Now exports a singleton `router` + `queryClient`, plus the `declare module "@tanstack/react-router" { interface Register { router: typeof router } }` augmentation (previously declared against `@tanstack/react-start`). |
| `frontend/src/routes/__root.tsx` | Removed `shellComponent`/`RootShell` (the `<html>/<head>/<body>` renderer) and `<Scripts />`. `RootComponent` now renders `<QueryClientProvider><HeadContent /><Outlet /></QueryClientProvider>` directly. Dropped the `appCss` `?url` import + stylesheet `<link>` (styles are now imported directly in `main.tsx`). All route `head()` configs, the 404/error components, and all business logic in this file are untouched. |
| `frontend/.gitignore` | Removed `.output`, `.vinxi`, `.nitro`, `.wrangler/`, `.dev.vars`, `dist-ssr` entries. Kept `dist`. |
| `frontend/eslint.config.js` | Removed `.output`/`.vinxi` from `ignores`, removed the `no-restricted-imports` rule that referenced `@tanstack/react-start/server-only` (nothing left to enforce). |
| `frontend/package.json` | See dependency tables below; also renamed `"name"` from `tanstack_start_ts` to `undangan-akoe-frontend`, added an explicit `"engines"` field (see §4 — this matters). |
| `frontend/src/routes/README.md` | Reworded the file-based-routing note from "TanStack Start" to "TanStack Router ... SPA mode — no server, no SSR". |

### Deleted
| File/dir | Why |
| --- | --- |
| `frontend/src/start.ts` | Start server instance + CSRF middleware for server functions — none exist in this app |
| `frontend/src/server.ts` | Nitro/Cloudflare Worker `fetch` entrypoint |
| `frontend/src/lib/error-page.ts` | Server-side 500 fallback HTML, only used by `server.ts` |
| `frontend/src/lib/error-capture.ts` | Server-side error capture for `server.ts`/`start.ts` |
| `frontend/src/routeTree.gen.ts` | Stale, Start-flavored generated file — regenerated fresh by the plain router-plugin on first `dev`/`build` |
| `frontend/.output/` | Nitro build output directory |
| `frontend/.wrangler/` | Cloudflare Workers local-dev cache |
| `frontend/bun.lock` | Project now standardizes on `npm` per the migration brief; `bunfig.toml` was left in place (harmless, not referenced by anything) in case you still use it elsewhere — delete it too if not needed |

### Untouched (confirmed framework-agnostic — verified via full-repo grep for `createServerFn`/`@tanstack/react-start`/`useServerFn`/`createMiddleware`/`createIsomorphicFn`, only 3 hits, all in the deleted files above)
All 18 files in `src/routes/**`, everything in `src/components/**`, all of
`src/lib/*-api.ts` / `*-auth.ts` / `invitation-adapter.ts` / `themes.ts` /
`invitation-templates.ts` / `lovable-error-reporting.ts` / `utils.ts`,
`src/hooks/**`, `src/styles.css`, `tsconfig.json`, `components.json`,
`public/**`.

---

## 2. Dependencies removed

| Package | Was | Reason |
| --- | --- | --- |
| `@tanstack/react-start` | `dependencies` | SSR framework — no longer used |
| `nitro` | `devDependencies` | Server build/deploy target — replaced by plain `vite build` |
| `@lovable.dev/vite-tanstack-config` | `devDependencies` | Wrapper that wired Start + Nitro + Cloudflare + devtools into Vite |

`npm install` after these removals dropped **68 packages** from the tree
(Nitro, h3, Cloudflare Worker types, `@tanstack/start-*`, etc.) with zero
vulnerabilities reported.

## 3. Dependencies added

**None.** `@tanstack/router-plugin`, `@vitejs/plugin-react`, `@tailwindcss/
vite`, and `vite-tsconfig-paths` were already explicit `dependencies` in
`package.json` (the removed wrapper package used to import and configure them
internally); `vite.config.ts` now wires them directly instead.

**Flagged, not added:** the migration brief said to "keep Axios," but the
codebase has no Axios usage anywhere — `src/lib/api.ts`, `admin-api.ts`, and
`customer-api.ts` all call the native `fetch`. Adding an unused dependency
would contradict "keep all API integrations / business logic unchanged" for
no functional benefit, so it was left out. If you do want Axios going
forward, it's a separate, deliberate refactor of those three files — not a
side effect of this migration.

---

## 4. Build result

```
npm install   # ✅ 387 packages, 0 vulnerabilities
npm run build # ✅ vite build → frontend/dist/
tsc --noEmit  # ✅ 0 errors
```

`dist/` output (production build):

```
dist/index.html
dist/assets/index-*.css      (~97 kB, ~16 kB gzip — Tailwind output)
dist/assets/index-*.js       (~273 kB, ~85 kB gzip — app entry)
dist/assets/<route>-*.js     (one chunk per route, via autoCodeSplitting)
dist/assets/*.{jpg,png}      (images from src/assets, hashed)
```

`dist/index.html` correctly references the hashed JS entry + CSS + all
`modulepreload` chunks — this is a plain static bundle, deployable to any
static host or behind Nginx (see §5).

**Verification performed:**
- `npm run dev` boots cleanly on Vite 8 (auto-fell back to port 8081 since
  8080 was occupied in this sandbox; unaffected in a normal environment).
- `curl` against `/`, `/tema`, `/admin/login` in dev all return `200` with
  the SPA fallback serving `index.html` (client-side routing works).
- Headless Chrome (`google-chrome --headless --dump-dom`) rendered the built
  dev server output and confirmed: the landing page's actual content
  (`LandingNavbar`, `LandingHero`, etc.) mounted into `#root`, and the
  route's dynamic `<title>` ("Undangan Digital — Buat Undangan Pernikahan
  Online...") correctly took precedence over `index.html`'s static default
  title in the live DOM (React 19's head-hoisting places it first in
  document order). No console errors were captured.

**Environment note (action may be required on your side):** this sandbox's
default `node` resolves to **v14.21.3** via `nvm`, which is below what Vite 8
and `@tanstack/router-plugin` require (`node ^20.19.0 || >=22.12.0`). All
verification above was run after `nvm use 22.16.0`. An explicit `"engines"`
field was added to `package.json` to surface this instead of failing
silently on an incompatible Node version. Make sure your dev machines and CI
use Node ≥20.19 (or ≥22.12).

**Pre-existing, unrelated lint debt:** `npm run lint` reports ~150
Prettier-formatting mismatches and one missing-rule error (`react/no-danger`,
referenced by an inline disable-comment in `routes/index.tsx` but never
registered in `eslint.config.js`). These exist in files this migration never
touched and predate it — they are not caused by, and out of scope for, the
SSR→SPA migration. Flagging so they aren't mistaken for a migration
regression; happy to clean them up as a separate pass if wanted.

---

## 5. Deployment instructions — Nginx + Laravel API

Two independently deployed pieces: the SPA (static files) and the Laravel API
(PHP-FPM). They can live on the same domain (different paths) or different
subdomains — pick one and keep it consistent with `VITE_API_URL` /
`CORS_ALLOWED_ORIGINS` below.

### 5.1 Build the frontend

```sh
cd frontend
npm ci
VITE_API_URL="https://api.your-domain.com/api" npm run build
# -> frontend/dist/
```

`VITE_API_URL` is read at **build time** (`src/lib/api.ts`, `admin-api.ts`,
`customer-api.ts` all do `import.meta.env.VITE_API_URL ?? "http://localhost:8000/api"`),
so it must point at the real API URL before building for production —
there's no runtime env injection in a static SPA.

Ship `frontend/dist/` to the server, e.g. under
`/var/www/undangan/current/` (see the rollback strategy in §6 for why
`current/` is a symlink rather than a fixed path).

### 5.2 Laravel API (unchanged by this migration)

Standard Laravel + PHP-FPM deploy: `composer install --no-dev --optimize-autoloader`,
`php artisan migrate --force`, `php artisan config:cache route:cache view:cache`,
point Nginx's PHP location block at `backend/public/`. Set in `backend/.env`:

```
APP_URL=https://api.your-domain.com
FRONTEND_URL=https://your-domain.com
CORS_ALLOWED_ORIGINS=https://your-domain.com
```

(`backend/config/cors.php` reads `CORS_ALLOWED_ORIGINS`, comma-separated for
multiple origins; auth is Bearer-token based — `supports_credentials: false`
— so this is purely an XHR-origin allowlist, not a cookie/CSRF boundary.)

### 5.3 Nginx — SPA + API on one domain (recommended)

```nginx
server {
    listen 443 ssl http2;
    server_name your-domain.com;

    # --- SPA (static, served directly by Nginx) ---
    root /var/www/undangan/current/dist;
    index index.html;

    location / {
        try_files $uri $uri/ /index.html;  # client-side routing fallback
    }

    location /assets/ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # --- Laravel API, reverse-proxied under /api ---
    location /api/ {
        proxy_pass http://127.0.0.1:9000;  # or wherever the Laravel app's own
                                            # Nginx/PHP-FPM vhost listens
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    listen 80;
    # redirect :80 to :443, or terminate TLS upstream — your call
}
```

If instead the Laravel app is deployed on its own domain
(`api.your-domain.com`) with its own standard Laravel/Nginx vhost (`root
backend/public; try_files $uri /index.php?$query_string;` + a `location ~
\.php$` PHP-FPM block), drop the `location /api/` proxy above entirely and
just build the frontend with `VITE_API_URL=https://api.your-domain.com/api`.
This is simpler operationally (two vhosts, no reverse proxy) — the "same
domain" version above only matters if you specifically need to avoid CORS or
share a domain for cookie/SEO reasons, neither of which applies here.

### 5.4 Cache headers

`vite build` content-hashes every file under `dist/assets/`, so those are
safe to cache aggressively (`immutable`, 1 year). `dist/index.html` must
**not** be cached (or very briefly) — it's the only file that references the
current hashed bundle, so a stale cached copy pins users to an old build.

---

## 6. Rollback steps if the migration fails

**This project has no git repository** (confirmed: no `.git` at the repo
root). That means none of the usual `git revert`/`git checkout` safety nets
apply — plan rollback around file/artifact backups instead. Two concrete
recommendations, independent of whether a rollback is ever needed:

1. **Before deploying this migration**, archive the current working
   `frontend/` directory as it exists after this migration (e.g. `tar czf
   frontend-spa-migration-$(date +%Y%m%d).tar.gz frontend/`) as a known-good
   snapshot, *and* separately archive whatever was previously deployed in
   production (the last working `.output/` build or equivalent), if you
   still have it.
2. **Initialize git now** (`git init && git add -A && git commit -m
   "pre-deploy snapshot"`) so future changes — including a real rollback if
   this one has issues — are a `git revert` instead of a manual file
   restore. Strongly recommended regardless of this migration.

### If the new SPA build is broken in production

- **Fastest rollback (static hosting):** if you deployed via a
  `current -> releases/<timestamp>` symlink pattern (recommended — see
  below), just repoint the symlink to the previous release and `nginx -s
  reload`. No rebuild needed.
  ```sh
  ln -sfn /var/www/undangan/releases/<previous-timestamp> /var/www/undangan/current
  systemctl reload nginx
  ```
- **No previous release kept:** re-run the pre-migration build. The deleted
  Start/Nitro files are recoverable from whatever source control or backup
  held the pre-migration state (see the "archive before deploying" note
  above) — regenerate `.output/` with the old `npm run build` (which invoked
  Nitro) and redeploy that.
- **Backend is unaffected either way** — this migration touched only
  `frontend/`; the Laravel API in `backend/` needs no rollback action, and
  the frontend/backend contract (`VITE_API_URL` → `backend/routes/api.php`)
  didn't change.

### If `npm install`/`npm run build` fails in CI/on a teammate's machine

- Check Node version first — `package.json` now declares `"engines": {
  "node": "^20.19.0 || >=22.12.0" }`. The single most likely failure mode is
  an old Node (this sandbox's default was v14.21.3, which fails silently on
  `npm install` with only warnings, then fails hard on `vite build`).
- `rm -rf node_modules package-lock.json && npm install` to rule out a
  stale lockfile from before the dependency removals.

### Structural rollback (undoing the migration itself, not just a bad deploy)

If the SPA approach needs to be abandoned entirely and TanStack Start/Nitro
restored, there's no automated path — the fastest route is restoring the
pre-migration `frontend/` from whatever backup/archive you made per
recommendation 1 above (or wherever this project's history lived before —
check the Lovable editor's project history at the URL in `frontend/
README.md`, since `frontend/AGENTS.md` notes this repo syncs with a Lovable
project, which likely retains earlier revisions independent of local git).
