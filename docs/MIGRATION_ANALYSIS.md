# Migration Analysis — TanStack Start/Nitro → Vite React SPA

This document records the **pre-migration audit** of `frontend/`: what the app
actually used TanStack Start for, what was safe to remove, and what the
migration plan was before any code changed. See `MIGRATION_REPORT.md` for the
executed result.

## 1. What the app was

- React 19 + TypeScript, TanStack Router (file-based routes in `src/routes/`),
  TanStack React Query, Tailwind CSS v4, Radix UI primitives (via shadcn/ui in
  `src/components/ui/`).
- Wrapped by `@tanstack/react-start` (SSR framework) + `nitro` (server build/
  deploy target), configured through a third-party helper package,
  `@lovable.dev/vite-tanstack-config`, which composed ~10 Vite plugins
  (TanStack Start, Nitro targeting `cloudflare-module`, Tailwind, tsconfig
  paths, React, devtools, and several Lovable-editor-only dev plugins).
- Backend is a separate Laravel app (`backend/`) exposing a JSON API consumed
  over plain `fetch` (see `src/lib/api.ts`, `admin-api.ts`, `customer-api.ts`).
  Auth sessions are stored in `localStorage` (Bearer tokens), not cookies —
  confirmed via `backend/config/cors.php` (`supports_credentials: false`).

## 2. Key finding: the app never used server rendering as a feature

A full-repo scan for `createServerFn`, `createMiddleware`, `useServerFn`,
`createIsomorphicFn`, and `@tanstack/react-start` imports turned up exactly
three files: `src/start.ts`, `src/server.ts`, and the generated
`src/routeTree.gen.ts`. No route, loader, or component called a server
function. TanStack Start was being used purely as:

1. An SSR **document renderer** (the root route's `shellComponent` rendered
   `<html>/<head>/<body>` and Start/Nitro served that per-request), and
2. A **deploy target** (Nitro built a Cloudflare Worker-shaped server bundle
   into `.output/`).

Every route's `loader`/`head` function only called `fetch` (via `src/lib/
api.ts`) and ran identically whether invoked on a server or in the browser.
This meant the app was, functionally, already an SPA wearing an SSR
harness — the migration was a matter of removing the harness, not rewriting
business logic.

## 3. Files identified as SSR/Nitro-only (safe to delete)

| File | Why it existed | Why it's removable |
| --- | --- | --- |
| `src/start.ts` | Defined the Start server instance + CSRF middleware for server functions | No server functions exist anywhere in the app |
| `src/server.ts` | Nitro/Cloudflare Worker `fetch` entrypoint, wrapping Start's server-entry with error-page fallback | No server runtime in a static SPA |
| `src/lib/error-page.ts` | Static HTML string returned by `server.ts` on a 500 | Only consumer was `server.ts` |
| `src/lib/error-capture.ts` | Captured errors server-side so `server.ts` could recover a stack from h3's swallowed 500s | Only consumers were `start.ts`/`server.ts` |
| `.output/` | Nitro build output (Cloudflare Worker + static assets) | Replaced by plain `dist/` from `vite build` |
| `.wrangler/` | Cloudflare Workers local-dev cache | No Cloudflare target anymore |

`src/lib/lovable-error-reporting.ts` looked similar but is **not**
SSR-related — it's a client-only React-error-boundary reporter (guarded by
`typeof window === "undefined"`) used by `__root.tsx`'s `ErrorComponent`. It
was kept as-is.

## 4. What had to change vs. what could stay untouched

**Untouched (confirmed SSR-agnostic):**
- All 18 route files in `src/routes/**` — their `loader`/`head`/`component`
  functions, Radix/shadcn UI components, business logic in `src/lib/*-api.ts`,
  `*-auth.ts`, `invitation-adapter.ts`, `themes.ts`, `invitation-templates.ts`.
- All components under `src/components/**` (landing, dashboard, admin,
  invitation, ui).
- `src/styles.css` (Tailwind v4 `@theme`/`@import` config) — no changes.
- `tsconfig.json`, `components.json` — already framework-agnostic.

**Had to change (SSR/Nitro plumbing only):**
- `src/router.tsx` — was a `getRouter()` factory (so Start could create a
  fresh router per SSR request); converted to a singleton `router` export,
  the standard pattern for a client-only SPA.
- `src/routes/__root.tsx` — dropped `shellComponent` (the `<html>/<head>/
  <body>` renderer) and `<Scripts />`; kept `<HeadContent />` and every
  route's `head()` config unchanged (see §5).
- `vite.config.ts` — replaced the `@lovable.dev/vite-tanstack-config` wrapper
  with an explicit plugin list (all of these packages were already direct
  dependencies, just previously wired implicitly).
- New `index.html` + `src/main.tsx` — the standard Vite SPA entry pair that
  Start had been generating/injecting automatically.
- `package.json`, `.gitignore`, `eslint.config.js` — remove Start/Nitro
  references.

## 5. Risk assessed: per-route `<title>`/meta tags without SSR

Routes `/`, `/tema`, and `/$slug` set dynamic `head()` (title, description,
OG tags; `/$slug` also overrides the favicon per invitation). Without SSR,
the concern was whether `<HeadContent />` — rendered inside `<div id="root">`
in the document `<body>`, not literally inside `<head>` — would still update
the real `document.title`/meta tags for a browser tab / share preview.

This turned out to be a non-issue: **React 19 natively hoists `<title>`,
`<meta>`, and `<link>` elements to `<head>` no matter where in the tree
they're rendered**, and de-conflicts with the static defaults already present
in `index.html`. This was verified empirically in Chrome headless (see
`MIGRATION_REPORT.md` §"Verification") — the React-rendered title from
`routes/index.tsx` correctly took precedence over `index.html`'s static
default.

## 6. Discrepancy flagged: "Keep Axios"

The migration brief listed Axios among the stack to preserve. The codebase
does not use Axios anywhere — `src/lib/api.ts`, `admin-api.ts`, and
`customer-api.ts` all wrap the native `fetch`. Introducing Axios with nothing
to call it from would violate "keep all API integrations / business logic
unchanged" for no benefit, so it was **not** added. Flagged here rather than
silently ignored; see `MIGRATION_REPORT.md` for the explicit call-out.

## 7. Plan executed

1. Strip the SSR/Nitro plugin wrapper from `vite.config.ts`, replace with a
   plain Vite config (React, Tailwind v4, tsconfig-paths, TanStack Router
   codegen plugin).
2. Add `index.html` + `src/main.tsx` (standard `createRoot` mount).
3. Convert `src/router.tsx` to a singleton router; update the `Register`
   module augmentation to target `@tanstack/react-router` instead of
   `@tanstack/react-start`.
4. Trim `src/routes/__root.tsx` down to a component-only root route.
5. Delete the 4 SSR-only source files + `.output/` + `.wrangler/`.
6. Remove `@tanstack/react-start`, `nitro`, `@lovable.dev/vite-tanstack-config`
   from `package.json`.
7. Regenerate `src/routeTree.gen.ts` via the plain router-plugin codegen.
8. Verify: `npm install`, `npm run dev`, `npm run build`, `tsc --noEmit`, and a
   headless-Chrome render of the built app.
