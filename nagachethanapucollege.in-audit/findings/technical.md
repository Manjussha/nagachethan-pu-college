# Technical SEO Audit — nagachethanapucollege.in
Date: 2026-09-18
Method: Local mirror inspection (`C:\Users\Lenovo\Desktop\All websites\nagachethan-pu-college`) cross-verified against live site via curl. No files modified.

**Score: 58 / 100**

---

## Top Issues (severity-ordered)

| # | Severity | Issue |
|---|----------|-------|
| 1 | **Critical** | A live, functional WordPress core install is exposed on the production domain — `wp-login.php` (real login form, WP 6.9.1), `wp-admin/` (302 → wp-login.php, `X-Redirect-By: WordPress`), `xmlrpc.php` (405, endpoint live), `license.txt` (200, WP core file), `readme.html`→`/readme` (200, real WP readme). Serious security/brand-hijack risk and confuses crawlers about site identity/tech stack. |
| 2 | **High** | Site-wide primary navigation (`header.html`) and footer (`footer.html`, incl. all Quick Links, Privacy/Terms links, Areas We Serve cross-links, contact/social info) are injected client-side via `XMLHttpRequest` in `components.js`/`components.min.js`. They are **absent from raw server HTML on every page** (confirmed via curl on `/`, `/pu-college-hebbal`, etc.) with no `<noscript>` fallback. |
| 3 | **High** | robots.txt does not disallow the exposed WordPress attack-surface paths (`/wp-admin/`, `/wp-login.php`, `/xmlrpc.php`, `/wp-content/`), compounding issue #1 by leaving them crawlable/indexable. |
| 4 | **High** | Blog hub page (`/blog`) renders its entire post list client-side via `fetch('posts.json')` with no static/SSR fallback — raw HTML has zero post links/snippets for non-JS crawlers or link-preview bots. |
| 5 | **Medium** | `#footer-placeholder` has no reserved height in CSS (unlike `#header-placeholder`, which reserves `calc(36px + var(--header-height))`) — async footer injection is a potential CLS contributor on short/mobile pages. |
| 6 | **Medium** | IndexNow protocol is not implemented — no key file at `/<key>.txt`, no evidence of ping calls to Bing/Yandex/Naver endpoints on publish. |
| 7 | **Low** | `admin/.htaccess` contains an unenforced `Deny from all` directive (legacy Apache 2.2 syntax) with a comment claiming the folder is blocked from direct access — live test shows `admin/` returns `200` with the real login form, i.e. the deny rule is not actually restricting access (protection currently relies solely on the PHP login gate, which is otherwise reasonably implemented with CSRF + lockout). |
| 8 | **Low** | `googlec9407f7e62e77f27.html` (Google site-verification file) and `model_comparison.html` (internal dev tool) both correctly 301-redirect via the `.html`-stripping rule to `/googlec...` and `/model_comparison` — verification files should generally be excluded from that rewrite (works today because Google fetches the exact `.html` URL and gets a 301+200 chain, which Search Console tolerates, but it's not best practice; the internal-only `model_comparison.html` tool being reachable at all, even with `noindex`, is unnecessary public surface). |

---

## What Works Well

- **Redirects**: clean, single-hop 301s verified live for HTTP→HTTPS, `www`→non-`www`, `.html`→clean URL, trailing-slash→clean URL, and `/index.html`/`/index`→`/`. No redirect chains detected.
- **Canonicals**: all 30 sitemap URLs (plus `404.html`) have correct, self-referencing canonical tags pointing to the clean (non-`.html`) URL. No mismatches found.
- **Sitemap**: `sitemap.xml` lists exactly 30 URLs, all return live `200`, all match real pages, `lastmod` dates present and plausible. No orphaned indexable pages found; component/admin/dev files (`header.html`, `footer.html`, `admin/`, `model_comparison.html`, `googlec...html`, `404.html`) are correctly excluded from the sitemap.
- **404 handling**: unmatched URLs correctly return HTTP `404` with the custom `404.html` page (verified live) and carry `<meta name="robots" content="noindex">`.
- **Security headers**: CSP (`upgrade-insecure-requests`), `X-Content-Type-Options`, `X-Frame-Options`, `X-XSS-Protection`, `Referrer-Policy`, `Strict-Transport-Security` (HSTS, 1yr, includeSubDomains), `Permissions-Policy` all present site-wide via `.htaccess`.
- **Mixed content**: none found on homepage raw HTML (no hardcoded `http://` asset/link references).
- **Mobile**: correct `<meta name="viewport" content="width=device-width, initial-scale=1.0">` on all pages checked; responsive class naming (BEM) and a hamburger menu pattern present.
- **Structured data**: rich JSON-LD present on homepage — `CollegeOrUniversity`, `FAQPage`/`Question`/`Answer`, `Course`, `Review`/`AggregateRating`/`Rating`, `GeoCoordinates`/`PostalAddress`, `OpeningHoursSpecification`, `Offer`/`OfferCatalog`, `WebSite`. No obvious type errors from a static read.
- **Titles/meta descriptions**: all 30 indexable pages have unique, keyword-appropriate titles and descriptions — no duplicates found.
- **AI crawler management**: robots.txt explicitly `Allow`s GPTBot, ChatGPT-User, PerplexityBot, ClaudeBot, anthropic-ai, Google-Extended, Bingbot, plus a well-structured `llms.txt` with key facts/FAQ — good AI-search/citation posture.
- **hreflang**: correctly absent (single-language, single-region site — no action needed).
- **`/admin` noindex**: confirmed live — `X-Robots-Tag: noindex, nofollow` served on both `/admin/` and `/admin/index.php`; not present in sitemap or any internal links.
- **`posts.json`, `header.html`, `footer.html`, `model_comparison.html`**: all correctly carry `X-Robots-Tag: noindex, nofollow` per `.htaccess` `<FilesMatch>` rules (verified live).

---

## Evidence & Fixes

### 1. Exposed live WordPress install — CRITICAL
**Evidence (live curl, 2026-09-18):**
```
GET /wp-login.php     → 200, full WP 6.9.1 login form, "Powered by WordPress"
GET /wp-admin/        → 302 → /wp-login.php?redirect_to=...&reauth=1  (X-Redirect-By: WordPress)
GET /xmlrpc.php       → 405 (endpoint alive, rejects GET but accepts POST — classic brute-force/DDoS vector)
GET /license.txt      → 200, "WordPress - Web publishing software"
GET /readme.html      → 301 → /readme → 200, real WP readme content (7425 bytes)
```
This is a **second, live application** sitting on the same domain as the static site (likely a legacy install from before the site was rebuilt as static HTML, never decommissioned). This is out of scope for a pure "SEO" fix but has direct SEO consequences: if compromised (a very real risk — exposed `xmlrpc.php` + `wp-login.php` are the top two WordPress attack vectors), Google will flag the site as hacked and can deindex or apply a manual action to the *entire* domain, destroying all the legitimate static-site rankings.

**Fix:**
- Have the hosting/dev team confirm whether this WP install is still needed. If not, **delete the entire WordPress codebase** from the server (wp-admin/, wp-includes/, wp-content/, wp-login.php, xmlrpc.php, wp-config.php, license.txt, readme.html, wp-*.php in root).
- If it must stay (e.g., used for something internal), immediately: rename/remove `xmlrpc.php`, IP-allowlist `/wp-admin/` and `/wp-login.php` at the server/`.htaccess` level, delete `license.txt` and `readme.html`, and ensure WP core/plugins are patched to latest.
- Either way, do **not** rely on robots.txt alone — it does not prevent access, only crawling.

### 2. Header/footer are 100% client-side rendered — HIGH
**Evidence:**
- `components.js` (and `components.min.js`) use `XMLHttpRequest` to `GET header.html` / `GET footer.html` and inject via `placeholder.outerHTML = xhr.responseText`.
- Raw curl of `https://nagachethanapucollege.in/` and `/pu-college-hebbal` shows only `<div id="header-placeholder"></div>` and `<div id="footer-placeholder"></div>` in server-delivered HTML — the entire primary nav menu (Home/About/Academics/Admissions/Gallery/Achievements/Contact/Blog), the "Apply Now" CTA, and the entire footer (Quick Links, Privacy/Terms, Areas We Serve list, contact details, social links) are missing until JS executes.
- Confirmed no `<noscript>` fallback for either placeholder.
- Mitigating factor found: the 13 area pages *do* additionally hardcode their own inline cross-links to sibling area pages in the page body (independent of the footer), so that specific internal-link mesh survives without JS. But sitewide primary navigation and Privacy/Terms links are **not** duplicated anywhere else in raw HTML (spot-checked `index.html`, `academics.html`, `admissions.html`, `gallery.html`, `achievements.html`, `blog.html`, `pu-college-hebbal.html` — none contain `/privacy`, `/terms`, `/gallery`, or `/achievements` links outside the JS-injected footer, except `contact.html` and `about.html` which happen to reference `/achievements`/`/terms`/`/privacy` inline).

**Why it matters:** Googlebot generally renders JS but on a delay (second wave of indexing) and can fail to execute XHR-injected content reliably at scale; Bingbot's JS rendering is more limited; most third-party SEO crawlers (Screaming Frog default config, many rank trackers) and all link-preview/social bots do not execute JS at all — for these, the site effectively has **no navigation and no footer**, which undermines PageRank flow, discovery of Privacy/Terms, and any future footer-based internal linking.

**Fix:** Inline the header and footer HTML directly into every page at build/deploy time (simple templating — even a basic Node/PHP include or a pre-commit script that stitches `header.html`/`footer.html` into each page would work, since this is already a static-HTML site with no server-side templating). Keep `components.js` only for the progressive-enhancement bits it still needs (active-nav-link highlighting), not for injecting the markup itself.

### 3. `/blog` hub is fully client-rendered from `posts.json` — HIGH
**Evidence:** `blog.html` line 369: `fetch('posts.json')`; raw curl of `/blog` shows only the placeholder divs and no post titles/links/excerpts in server HTML (individual blog post pages themselves are static and fine — this issue is specific to the `/blog` listing/hub page).

**Fix:** Render the post list statically at build time (same posts.json data, but pre-rendered into `blog.html` at deploy, with `fetch()` used only to enhance/filter client-side if needed).

### 4. robots.txt doesn't block exposed WP paths — HIGH (pairs with #1)
**Fix:** Add, as a defense-in-depth measure only (not a substitute for deleting/locking down the WP install):
```
Disallow: /wp-admin/
Disallow: /wp-login.php
Disallow: /xmlrpc.php
Disallow: /wp-content/
Disallow: /wp-includes/
Disallow: /readme.html
Disallow: /license.txt
```

### 5. Footer CLS risk — MEDIUM
**Evidence:** `index.html` line 53: `#header-placeholder{min-height:calc(36px + var(--header-height));contain:layout}` — no equivalent rule for `#footer-placeholder` anywhere in `styles.css` or inline critical CSS.
**Fix:** Add a reasonable `min-height` (measured from real rendered footer height, e.g. ~600–700px desktop / ~900px+ mobile given the 4-column grid) to `#footer-placeholder`, or better, resolve via fix #2 (inlining removes the async-injection CLS risk entirely).

### 6. IndexNow not implemented — MEDIUM
**Evidence:** No key file found at any tested path (`/indexnow.txt` → 404); no reference to IndexNow in `.htaccess`, `script.js`, or deploy scripts.
**Fix:** Generate an IndexNow key, publish `/​<key>.txt` at the root, and add a simple POST call (from the deploy script or a small serverless function) to `https://api.indexnow.org/indexnow` whenever a page is published/updated — covers Bing, Yandex, Naver, Seznam in one call.

### 7. `admin/.htaccess` "Deny from all" is not effective — LOW
**Evidence:** `admin/.htaccess` contains `Deny from all` (Apache 2.2 syntax) with a comment claiming the folder is never directly reachable; live curl to `/admin/` and `/admin/index.php` both return `200` with the actual login form rendered, meaning the "Deny from all" line has no effect (Hostinger's Apache/LiteSpeed config likely runs in `mod_authz_core`-only mode without `mod_access_compat`, which makes this old-style directive silently no-op rather than error). Access is currently protected only by the PHP session login (which does look reasonably solid — CSRF check, lockout on repeated failures). Correctly `noindex`ed either way.
**Fix:** Replace with 2.4-native syntax if IP restriction is actually wanted (`Require ip x.x.x.x`), or remove the misleading comment/directive if login-only protection is the intended design.

### 8. Verification/dev files pulled through the `.html`-stripping rewrite — LOW
**Evidence:** `.htaccess` `.html`-strip rule applies to `googlec9407f7e62e77f27.html` (Google Search Console verification) and `model_comparison.html` (internal AI-model comparison tool), 301-redirecting them to extensionless URLs. Functionally harmless (Google Search Console still validates fine after a redirect), but `model_comparison.html` being publicly reachable at all (even `noindex`ed) is unnecessary attack/crawl surface for an internal tool.
**Fix:** Exclude verification files from the `.html`-strip `RewriteCond` (`RewriteCond %{REQUEST_URI} !^/googlec9407f7e62e77f27\.html$`), and consider removing or IP-gating `model_comparison.html` if it's not meant to be public.

---

## Full URL Status Check (30/30 sitemap URLs — all 200 live)
Homepage, about, academics, admissions, gallery, achievements, contact, blog, 7 blog posts, pu-college-in-bangalore + 11 area pages, privacy, terms — all verified `200 OK` via live curl, all canonical-consistent with sitemap `<loc>`.
