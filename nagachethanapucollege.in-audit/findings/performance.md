# Performance / Core Web Vitals Audit — nagachethanapucollege.in

Date: 2026-09-18
Pages audited: Home (/), /about, /academics, /gallery, /pu-college-hebbal, /blog-pcmb-vs-pcmc

## Data availability note

PageSpeed Insights API (no-key, shared quota) returned `429 Quota exceeded for quota
metric 'Queries' and limit 'Queries per day'` on every page/strategy combination tried
(including retries with backoff). `psi_check.js` in the repo root uses the same
unauthenticated endpoint and will hit the same daily cap. A local Lighthouse CLI run
(`npx lighthouse`) also failed on this Windows sandbox with a Chrome-launcher temp-dir
cleanup `EPERM` error, so no lab Lighthouse JSON could be captured either.

**No CrUX field data or fresh Lighthouse lab scores could be collected in this session.**
The findings below are therefore based on: (1) live HTTP header/response inspection via
curl against the production site, and (2) static analysis of the shipped HTML/CSS/JS
source for known LCP/INP/CLS risk patterns. Numeric Lighthouse/CrUX metrics are marked
`n/a` in the table and should be re-run once the daily PSI quota resets (resets ~24h
after first exhaustion — likely already used up by other tooling today) — use a proper
API key via `scripts/pagespeed_check.py` if credentials are available, which gets a much
higher per-key quota than the anonymous endpoint.

## Overall Performance Score: 78/100 (estimated, architecture-based — not lab-measured)

This is a qualitative estimate based on strong foundational practices (critical CSS
inlined, async font/CSS loading, HTTP/3, Brotli, immutable caching, no image-based hero)
offset by real defects found below (oversized/lazy-loaded gallery images, no WebP for
most photos, JS-injected header with no `Vary: Accept`). Treat as directional only until
a real Lighthouse/CrUX run is obtained.

## Metrics table (mobile) — all pages

| Page | Perf score | LCP | INP/TBT | CLS | FCP | TTFB (measured) | CrUX field data |
|---|---|---|---|---|---|---|---|
| / (home) | n/a (PSI quota exceeded) | n/a | n/a | n/a | n/a | ~189ms (time_starttransfer) | n/a |
| /about.html | n/a | n/a | n/a | n/a | n/a | ~455ms* | n/a |
| /academics.html | n/a | n/a | n/a | n/a | n/a | ~142ms | n/a |
| /gallery.html | n/a | n/a | n/a | n/a | n/a | ~141ms | n/a |
| /pu-college-hebbal.html | n/a | n/a | n/a | n/a | n/a | ~110ms | n/a |
| /blog-pcmb-vs-pcmc.html | n/a | n/a | n/a | n/a | n/a | ~119ms | n/a |

\* about.html TTFB sample was an outlier (single-run, likely cold cache/CPU burst on
LiteSpeed) — all TTFB values are well under the 200ms "good" guidance except that one
sample; re-test with multiple runs before treating 455ms as representative.

Server response headers confirmed for `/`: `HTTP/1.1 200`, but `alt-svc: h3=":443"` is
advertised, meaning the server supports HTTP/3/QUIC — however curl connected over
HTTP/1.1 in this sandbox (curl build lacks `--http2`/h3 support), so real-browser
negotiation to h2/h3 could not be directly confirmed from this environment. Recommend
confirming via `chrome://net-export` or webpagetest.org that browsers are actually
upgrading to h3.

## Evidence gathered

### Compression / caching (curl header checks)
- `/` (HTML): `Content-Encoding: br`, `Cache-Control: public, max-age=3600, must-revalidate`, `Vary: Accept-Encoding` — good.
- `styles.min.css`, `script.min.js`: `Content-Encoding: br`, `Cache-Control: public, max-age=31536000, immutable`, `Vary: Accept-Encoding` — good, 1-year immutable caching with versioned query strings (`?v=20260313`) for cache-busting.
- Images (`college-campus.jpg`, `logo.jpg`): `Cache-Control: public, max-age=31536000, immutable` — good.

### Image content negotiation (WebP)
- `college-campus.jpg` / `logo.jpg`: requesting with `Accept: image/webp,image/*,*/*;q=0.8` correctly returns `Content-Type: image/webp` (32,808 bytes vs 41,065 bytes for the JPEG fallback) — negotiation works for these two files.
- **Bug**: neither the plain nor the WebP-negotiated response for these images (nor any other image tested) sends a `Vary: Accept` header — only `/`, CSS and JS responses send `Vary: Accept-Encoding`. Without `Vary: Accept` on image responses, any HTTP cache/CDN/proxy sitting in front of the origin (browser disk cache across tab reloads, corporate proxies, future CDN if one is added) can serve the WebP payload to a client that didn't send `Accept: image/webp` (broken image) or serve the JPEG to a client that did (missed savings). This is a live correctness bug, not hypothetical — it will manifest the moment any shared cache is added in front of the origin.
- **Bigger gap**: `GROUND IMAGE.jpg` (used on /gallery, 168KB) does **not** get WebP-negotiated at all — requesting it with `Accept: image/webp` still returns `Content-Type: image/jpeg`, same 168,419-byte payload. Spot-checking suggests the WebP negotiation is only wired up for `college-campus.jpg`/`logo.jpg`, not for the bulk of the photo library (gallery photos, facility lab photos, `Ground-2.jpg`, `IMG_40xx.jpg` series, etc.).

### Gallery page image weight + lazy-loading
- `/gallery.html` has 17 `<img>` tags, **all** marked `loading="lazy"` — including the first images in the grid (`GROUND IMAGE.jpg`, `Ground-2.jpg`) which render above/near the fold on both desktop and most mobile viewports as the primary page content (this is a gallery page; the image grid IS the main content, not a below-fold extra).
- File sizes for the raw JPEGs served: `GROUND IMAGE.jpg` 168KB, `Ground-2.jpg` 178KB, `cls room.jpg` 128KB, `IMG_4068.jpg` 322KB, `IMG_4071.PNG.jpg` 170KB — these are un-thumbnailed camera-resolution photos displayed at `width="400" height="300"` (i.e., a 400×300 CSS box). None have WebP variants served.
- Net effect: on /gallery, the LCP candidate is very likely one of these lazy-loaded, oversized, non-WebP JPEGs — the single worst LCP risk found in the audit.

### Header/footer injected via client-side XHR (all pages)
- `components.js` (loaded as `<script defer>`) runs after HTML parsing, then issues a synchronous-style `XMLHttpRequest` to fetch `header.html`/`footer.html` and injects them via `placeholder.outerHTML = xhr.responseText`.
- This adds a full extra network round-trip (parse → execute defer script → XHR request → response → DOM injection) before the real navbar (including the `<img src="logo-small.jpg" fetchpriority="high">` inside `header.html`) exists in the DOM at all. Nav links, the "Apply Now" CTA, and the logo are invisible/non-interactive until this completes.
- Partial mitigation already in place: `index.html` separately preloads `logo-small.jpg` directly in `<head>` (`<link rel="preload" href="logo-small.jpg" as="image" fetchpriority="high">`), so the image bytes start downloading immediately regardless of the header injection delay — good catch by whoever built this. `#header-placeholder{min-height:calc(36px + var(--header-height));contain:layout}` in critical CSS also reserves vertical space, which should prevent a CLS shift when the header pops in.
- Remaining concerns: (1) real nav interactivity (menu clicks, hamburger menu) is unavailable until the XHR completes — an INP/usability risk on slow connections; (2) this pattern is duplicated on every page (about, academics, gallery, hebbal, blog, etc.) — none of them ship a server-rendered header, so first paint of the nav is always network-dependent; (3) `header.html`/`footer.html` are fetched with a bare relative URL and no cache-busting query string, unlike `styles.min.css?v=...`/`script.min.js?v=...`, so edits to header/footer navigation risk being stuck in browser cache without the versioning discipline used elsewhere.

### LCP element risk by page (static analysis)
- Home (`/`): hero has no `<img>` — background is a pure CSS `linear-gradient`; the H1 text is the LCP candidate. This is a good pattern (no image decode blocking LCP), contingent on the Poppins font loading fast (it's `preload`ed as `font/woff2` with `crossorigin`, plus a local `Poppins-Fallback` `@font-face` with `size-adjust` metrics override — well-engineered to avoid FOIT and minimize CLS from font swap).
- `/about.html`, `/pu-college-hebbal.html`, `/blog-pcmb-vs-pcmc.html`: no hero `<img>`/background-image found — text-based LCP, same good pattern as home.
- `/academics.html`: only images are 6 facility-lab photos (`facility-physics-lab.jpg` etc.), all correctly `loading="lazy"` since they're in a lower "Our Facilities" section, not the LCP candidate.
- `/gallery.html`: LCP candidate is almost certainly one of the lazy-loaded gallery grid photos — see above, this is the one page where the lazy-loading pattern is actively harmful.

### Render-blocking / third-party scripts
- Google Fonts and Font Awesome CSS are both loaded with the `<link rel="preload" as="style" onload="this.rel='stylesheet'">` async-swap pattern plus `<noscript>` fallback — correctly non-render-blocking.
- `gtag.js` (GA4) loads via `<script async>` — non-blocking, present on all 6 pages tested.
- No other third-party scripts (no chat widgets, no ad tags, no heavy embeds) found on the 6 pages — third-party script load is minimal, a genuine strength.
- DOM size is modest on every page tested (approx. tag counts: home 370, about 210, academics 278, gallery 225, hebbal 162, blog 226) — all comfortably under the 1,500-element "excessive DOM" threshold, low INP risk from DOM size.

## Top 6 issues (prioritized)

1. **[HIGH] Gallery page (/gallery) LCP images are lazy-loaded and unoptimized/no-WebP.** The first-row gallery photos (`GROUND IMAGE.jpg` 168KB, `Ground-2.jpg` 178KB) are the visual LCP candidates on this page yet carry `loading="lazy"`, forcing the browser to wait for an IntersectionObserver-style trigger instead of downloading immediately, and they're served as full-size, non-WebP JPEGs into a 400×300 box. **Fix**: remove `loading="lazy"` from the first 2–4 above-the-fold gallery images (or use `fetchpriority="high"` on just the very first one), and generate properly-sized WebP/AVIF derivatives (e.g. 400×300 or 2x=800×600 for retina) for every gallery/facility photo, serving via `<picture>` with JPEG fallback. Expected impact: gallery page LCP could drop from likely 3-5s+ (unoptimized lazy image) to under 2s.

2. **[HIGH] WebP content-negotiation is only wired up for 2 files (logo.jpg, college-campus.jpg); the rest of the photo library (gallery images, facility lab photos, IMG_40xx series) is served as plain JPEG even when the client sends `Accept: image/webp`.** Confirmed via curl: `GROUND IMAGE.jpg` returns `Content-Type: image/jpeg` regardless of the `Accept` header. **Fix**: extend whatever server-side rule/rewrite produces the WebP negotiation (likely a LiteSpeed/Hostinger image-optimization rule or `.htaccess` mod_rewrite) to cover all image paths, or pre-generate `.webp` siblings for every JPEG in the repo and reference them via `<picture>` markup so it isn't dependent on server config at all.

3. **[MEDIUM–HIGH] Missing `Vary: Accept` header on WebP-negotiated image responses.** Confirmed via curl that `college-campus.jpg`/`logo.jpg` responses vary their `Content-Type` (JPEG vs WebP) based on the `Accept` request header but send no `Vary: Accept` response header (only `Vary: Accept-Encoding` is present, on HTML/CSS/JS, not images). This is a caching-correctness bug: any shared/proxy cache (or CDN, if one is added later) can serve the wrong image format to a client, either breaking the image or losing the WebP savings. **Fix**: add `Vary: Accept` (in addition to the existing `Accept-Encoding`) to every image response whose body depends on `Accept`.

4. **[MEDIUM] Header/footer are injected client-side via `XMLHttpRequest` + `outerHTML` on every single page (components.js), adding a full extra network round-trip before the real nav, CTA, and logo exist in the DOM.** CLS is already mitigated with a reserved `min-height` placeholder and the LCP-critical `logo-small.jpg` is separately preloaded in `<head>`, so this is not currently a severe LCP/CLS problem — but it delays nav interactivity (menu/hamburger clicks are dead until the XHR completes) and is architecturally fragile (every page pays this network cost; no server-side includes/SSR). **Fix**: consider a build-time include (e.g., a simple SSG/templating step, or edge-side includes if the host supports them) so `header.html`/`footer.html` content is inlined into each page's static HTML at deploy time instead of fetched client-side at runtime. If that's too large a change short-term, at minimum add a cache-busting version query (`header.html?v=YYYYMMDD`) to match the discipline already used for `styles.min.css`/`script.min.js`.

5. **[MEDIUM] No fresh Lighthouse/PSI/CrUX numeric data could be captured this session** — the anonymous PageSpeed Insights endpoint used by `psi_check.js` returned `429 Quota exceeded ... Queries per day` on every attempt (shared/no-key quota, likely already exhausted by other tooling today), and a local `npx lighthouse` run failed with a Chrome-launcher `EPERM` cleaning up its temp directory on this Windows machine. **Fix**: re-run `psi_check.js` (or `scripts/pagespeed_check.py` with a real Google API key if available — a keyed request gets a much higher daily quota than the anonymous endpoint) at a later time/day to get real LCP/INP/CLS numbers per page and confirm/refute the estimates in this report; separately, fix the local Lighthouse CLI environment (run as admin, or point `--output-path`/Chrome's temp dir at a location without an EPERM lock) so lab runs are repeatable without hitting PSI's daily cap.

6. **[LOW] Unverified HTTP/2 vs HTTP/3 negotiation from this environment.** The origin correctly advertises `alt-svc: h3=":443"` and headers otherwise look modern (LiteSpeed + Hostinger), but the curl build available in this sandbox doesn't support `--http2`/HTTP/3, so real browser protocol negotiation couldn't be directly confirmed here. **Fix**: verify with `chrome://net-export`, `curl --http2 -I` from a curl build that supports it, or webpagetest.org that mobile Chrome sessions are actually upgrading to h2/h3 (not silently falling back to HTTP/1.1 as this curl sandbox did).

## What's already working well (do not regress)
- Critical CSS inlined in `<head>`, non-critical CSS loaded via preload+swap async pattern.
- Google Fonts preloaded with `crossorigin` + local `-Fallback` `@font-face` using `size-adjust`/`ascent-override`/`descent-override` metrics matching to prevent layout shift on font swap — excellent CLS mitigation for web fonts.
- Font Awesome CSS loaded async (preload+swap+noscript), not render-blocking.
- GA4 `gtag.js` loaded via `<script async>`, not render-blocking.
- Brotli compression on HTML/CSS/JS; 1-year immutable caching with versioned query strings on CSS/JS.
- No hero `<img>`/background-image on 5 of 6 pages audited — LCP element is text, avoiding image-decode LCP delay entirely on those pages.
- Facility/lab images below the fold on /academics correctly use `loading="lazy"`.
- DOM size is modest (162–370 elements) across all pages tested — low INP risk from DOM complexity.
- No heavy third-party scripts/widgets found beyond GA4 and Font Awesome/Google Fonts CDNs.
