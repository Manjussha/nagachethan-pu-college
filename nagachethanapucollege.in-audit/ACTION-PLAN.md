# nagachethanapucollege.in — SEO / AEO / GEO Action Plan

Audit date: 2026-09-18 · Pages: 30 (sitemap) · Business type: Local education (PU college, Yelahanka, Bengaluru)

## SEO Health Score: 64 / 100

| Category | Weight | Score |
|---|---|---|
| Technical SEO | 22% | 58 |
| Content Quality (E-E-A-T) | 23% | 62 |
| On-Page SEO | 20% | 68 |
| Schema / Structured Data | 10% | 58 |
| Performance (estimated, PSI quota hit) | 10% | 78 |
| AI Search Readiness (GEO 74 / AEO 68) | 10% | 71 |
| Images | 5% | 47 |
| Local SEO (not in weighted score) | — | 58 |

Detailed evidence: `findings/technical.md`, `content.md`, `schema.md`, `geo-aeo.md`, `local.md`, `performance.md`.

---

## Phase 1 — Critical (this week)

1. **Remove exposed WordPress install** — `/wp-login.php` (200, real WP 6.9.1 login), `/wp-admin/`, `/xmlrpc.php`, `/license.txt`, `/default.php`, `blog-api.php` all live. Attack surface; a hacked site gets de-ranked. Fix: back up DB export, then delete WP core files + `wp-content` (check nothing uses it — blog now reads `posts.json`), drop the WP database, remove LSCACHE WP block from `.htaccess`.
2. **Delete self-serving review markup** — `aggregateRating` 4.8★/105 + 3 inline `Review` objects in `index.html` JSON-LD, and unsupported `aggregateRating` in `admissions.html`. Google policy violation risk. Keep reviews on Google Business Profile only.
3. **Render `/blog` post list in static HTML** — currently built by JS from `posts.json`; raw HTML has 0 post links. Put the 7 cards in the HTML (JS can still enhance).
4. **Put header nav + footer in raw HTML** — injected by `components.js` via XHR on every page; crawlers/link bots without JS see no nav, footer, privacy/terms links. Inline them at build time (simple script to copy `header.html`/`footer.html` into every page).

## Phase 2 — High impact (weeks 2–3)

5. **Fix founding-date contradiction** — "established 2015" vs "first batch 2016" (e.g. `pu-college-in-bangalore.html:109-110`). Pick one wording site-wide ("Founded 2015, first batch 2016").
6. **Wrong email on privacy/terms** — `info@nagachethanapucollege.com` (wrong domain) at `privacy.html:331`, `terms.html:336`. Use `npucan928@gmail.com`.
7. **Unify Organization schema** — same `@id` (`/#organization`) on contact/admissions; one canonical NAP block (phone, email, sameAs, geo, openingHours) everywhere; area pages reference the `@id` instead of repeating the full block.
8. **E-E-A-T** — add principal/faculty qualifications + real photos on About; named author (Person) with bio on each blog post; real campus/lab photos on Home and About (currently zero `<img>`).
9. **Publish fee ranges** (PCMB, PCMC, CEBA, HEBA) — "PU college fees in Yelahanka" is a top query; without numbers, directories win the answer and AI citations.
10. **Area pages: cut duplication** (64–69% shared text across 13 pages) — rewrite shared boilerplate per area, add more unique local facts (schools nearby, commute times, student count from that area).
11. **Blog cannibalization** — `index`, `blog-best-pu-college-yelahanka`, `blog-top-pu-colleges-yelahanka` target the same query. Differentiate: home = brand, "best" = why us, "top" = neutral comparison list; cross-link with distinct anchors.

## Phase 3 — Content & authority (month 2)

12. **Images** — convert all JPG/PNG to WebP (only 2 have WebP now), rename files with spaces (`GROUND IMAGE.jpg`, `pg-1 EXAM -1 RESULT 2025.JPG`, `IMG_4071.PNG.jpg`), descriptive gallery alt text. New uploads via /admin already do this automatically.
13. **Add `Vary: Accept`** to `.htaccess` for WebP-negotiated images (cache correctness).
14. **Gallery LCP** — remove `loading="lazy"` from first 2–4 gallery images.
15. **Thin core pages** — academics (328 words), achievements (317), admissions (287): add stream subjects, results by year, admission steps/documents/dates.
16. **AEO** — convert key H2s to question form; expand FAQ answers for AI citation (lead with a 40–60 word direct answer, then detail).
17. **Meta descriptions** on 13 area pages are 175–186 chars → trim to ~155.
18. **Course schema** — add `hasCourseInstance` + `offers`; blog `BlogPosting` add `mainEntityOfPage`, per-post image, real `dateModified`.
19. **Off-site citations** — claim/align NAP on Justdial, Sulekha, Shiksha, CollegeDunia, Careers360, Bing Places, Apple Business Connect; add them to `sameAs`. Grow YouTube (campus tour, topper interviews).

## Phase 4 — Monitoring (ongoing)

20. Re-run PageSpeed with an API key (quota hit today — no lab CWV numbers captured).
21. Implement IndexNow (Bing/Yandex instant indexing).
22. Monthly: GBP review replies, check GBP rating vs site claims, Search Console coverage.
23. Manually verify GBP category/name + directory NAP (automated checks were CAPTCHA-blocked).

## What already works
All 30 URLs 200 · clean single-hop redirects · correct self-canonicals · real 404 · full security headers · unique titles/descriptions · AI crawlers allowed + accurate `llms.txt` · static HTML (no SPA) · valid JSON-LD (0 parse errors) · consistent NAP on-site · Brotli + long cache · GBP listing exists (Maps place ID) · area pages have genuine local route/bus/landmark data.
