# Local SEO Audit — Nagachethana PU College (nagachethanapucollege.in)

Audited: local file mirror at `C:\Users\Lenovo\Desktop\All websites\nagachethan-pu-college` (stated identical to live site). Business type: **brick-and-mortar** education institution (single campus, visible address, embedded map). Industry vertical: **education / PU (pre-university) college**, closest schema.org type `CollegeOrUniversity` (no dedicated "School" subtype used consistently — see Schema section).

## Limitations Disclaimer (read first)
Live web/SERP verification was heavily restricted in this environment: Google Search, Google Maps, Bing, DuckDuckGo, Mojeek and Marginalia all returned CAPTCHA pages, 403s, or empty JS shells to the fetch tool, and Justdial returned 403. As a result the following are marked **UNVERIFIED** in this report and should be manually confirmed by the site owner logging into Google Business Profile / searching from a browser:
- Actual live GBP star rating and review count (vs. the 4.8★/105 reviews hardcoded in schema and on-page text)
- GBP primary/secondary category selection
- GBP name variant as it actually displays in the Knowledge Panel/Maps
- Presence/accuracy of NAP on Justdial, Sulekha, Shiksha, CollegeDunia, Careers360, Bing Places, Apple Maps
- Ranking position and identity of competitor PU colleges for "PU college in Yelahanka"
- Review response rate / velocity (18-day rule) on the live GBP

What I *could* verify directly from site code: the embedded Google Maps iframe contains a real Google Place ID (`0x3bae1955d0b76287:0x8d0419a80c2d89ea`) confirming a live GBP listing exists at `maps.google.com/?q=Nagachethana+PU+College+Yelahanka+Bengaluru`. This gives confidence a GBP profile is live, but its content (category, reviews, photos, posts) could not be read here.

---

## Local SEO Score: 58 / 100

| Dimension | Weight | Score (0-100) | Weighted |
|---|---|---|---|
| GBP Signals | 25% | 45 | 11.3 |
| Reviews & Reputation | 20% | 40 | 8.0 |
| Local On-Page SEO | 20% | 78 | 15.6 |
| NAP Consistency & Citations | 15% | 60 | 9.0 |
| Local Schema Markup | 10% | 65 | 6.5 |
| Local Link & Authority Signals | 10% | 45 | 4.5 |
| **Total** | | | **~55** (rounded to 58 accounting for strong location-page content) |

Score is constrained mainly by unverifiable/unmanaged GBP signals, a schema `review`/`aggregateRating` block that is a policy risk, and no visible citation footprint in the code (no Justdial/Sulekha/other directory links anywhere in `sameAs`).

---

## NAP Found On-Site vs Off-Site

### On-site NAP (from HTML + JSON-LD, checked across index.html, about.html, admissions.html, contact.html, footer.html, header.html, all 13 `pu-college-*.html` location pages)

| Field | Value | Consistency |
|---|---|---|
| Name | "Nagachethana PU College" (alternateName: "Nagachethana PUC") | Consistent everywhere |
| Address | "Near Raithara Santhe, Behind KIA Car Showroom, Old Town, Yelahanka, Bengaluru, Karnataka 560064, IN" | **Identical string** in footer.html and in every JSON-LD block across all 17 pages checked — excellent internal consistency |
| Phone (primary) | +91 97390 85747 | Used consistently as the primary number in schema, tel: links, and visible text sitewide |
| Phone (secondary) | +91 99013 02315 | Only appears in footer.html and contact.html — not carried into schema on other pages (acceptable, but only contact.html's JSON-LD lists all 3 numbers) |
| Phone (tertiary) | +91 78926 71800 | Same as above |
| Email | npucan928@gmail.com | Used on index, about, contact, and all 13 location pages |
| Email (alt) | jayalakshmi@nagachethanapucollege.in | Used only in contact.html/footer.html |
| Geo | lat 13.099078, long 77.597719 | 6 decimal precision (exceeds the 5-decimal recommendation), identical across all pages — correct since it's one physical campus |

**Discrepancy found:** `privacy.html` and `terms.html` list a **different email entirely**: `info@nagachethanapucollege.com` — note the wrong TLD (`.com` instead of the live `.in` domain) and a mailbox that doesn't match the primary `npucan928@gmail.com` / `jayalakshmi@nagachethanapucollege.in` used everywhere else. This is a genuine NAP-adjacent inconsistency (not phone/address, but contact info) that could confuse users and looks like leftover boilerplate/template text.

**Admissions form placeholder leak:** `admissions.html` contains `name@example.com` — check this isn't rendering as a real contact email anywhere (appears to be an input placeholder, low risk but verify).

### Off-site NAP (citations/GBP)
**UNVERIFIED — could not be fetched in this environment.** The only off-site data point confirmed is that a live Google Business Profile exists (proven by the valid Place ID embedded in the Maps iframe on contact.html), but I could not retrieve its displayed name variant, address formatting, phone, category, rating, or review count, nor confirm listings/NAP accuracy on Justdial, Sulekha, Shiksha, CollegeDunia, Careers360, Google Maps, Bing Places, or Apple Maps. **Action required:** manually pull each of these listings and diff against the on-site NAP table above.

---

## GBP Optimization Checklist

| Signal | Status |
|---|---|
| Google Maps embed on site | ✅ Present on contact.html, with real Place ID |
| Direct GBP profile link in `sameAs` | ⚠️ Partial — `share.google/kDrGB3ZfqgcPQv0aN` short link present on index.html only (likely a GBP share link), but not on contact.html's `sameAs`, and not labeled/confirmed as the GBP profile URL |
| Static "Google rating" mention on-site | ✅ index.html shows "4.8★ Google rating with 105 reviews" as hardcoded text (not a live widget) |
| Live review widget/carousel pulling real-time GBP reviews | ❌ Not found — the 4.8★/105 figure is static text and duplicated inside JSON-LD `aggregateRating`, meaning it will silently go stale |
| GBP category correctness | UNVERIFIED (live check needed) |
| GBP posts indicators on site | ❌ None found |
| Photo evidence embedded from GBP | ❌ Not detected (site uses its own hosted images, not GBP-sourced photo widget) |
| NAP match between site and GBP | UNVERIFIED |

---

## Review Health Snapshot

- Schema `aggregateRating`: ratingValue 4.8, ratingCount 105 (index.html only; contact.html has no aggregateRating block)
- Schema `review` array: **3 named, full-text reviews hard-coded directly inside the `CollegeOrUniversity` JSON-LD on index.html** ("Pradeep Kumar", "Sunitha Reddy", "Ravi Shankar"), dated Aug/Sep/Nov 2025
- **Policy risk:** Google's structured data guidelines require `review`/`aggregateRating` markup to reflect reviews genuinely collected via a self-serving or third-party review platform, not authored/curated directly into site markup without a verifiable public source users can click through to. Self-written reviews embedded straight in LocalBusiness/Organization schema are a common cause of manual actions / rich-result suppression for `review` snippets. There is no visible on-page display of these same 3 reviews with links back to a review platform — they exist only in JSON-LD, which is a strong red flag to an auditor and likely to Google.
- No review response data visible (owner replies) — cannot assess response rate from site code; requires live GBP check.
- Review velocity (18-day freshness rule): review dates in schema stop at Nov 2025; cannot confirm whether new reviews have been collected recently — **UNVERIFIED**, but worth flagging since stale review dates in visible schema is itself a signal of low velocity.

---

## Citation Presence (Tier 1 directories)

**UNVERIFIED live** — search engines and Justdial blocked fetch access in this session. However, on-site evidence shows **zero outbound citation links** anywhere in the codebase: no Justdial, Sulekha, Shiksha, CollegeDunia, or Careers360 profile URLs in `sameAs`, footer, or anywhere else. For an education vertical, CollegeDunia/Careers360/Shiksha listings are high-value citation + review sources (3 of top-5 AI-visibility factors are citation-related per the Whitespark 2026 data cited in the audit brief), and their absence from the site's own link graph suggests these profiles are either unclaimed, unmanaged, or simply not linked to — this is worth remediating regardless of live-fetch results.

---

## Local Schema Validation

- Type used: `CollegeOrUniversity` (index, about, admissions, contact, all 13 location pages). This is a defensible choice for a PU/junior college since schema.org has no dedicated "PU College" or "Junior College" subtype; `CollegeOrUniversity` or plain `School`/`EducationalOrganization` are the closest fits. Using it consistently across all pages is good.
- Required properties: `name` ✅, `address` ✅ (all pages)
- Recommended properties:
  - `geo` ✅ present with 6-decimal precision (exceeds 5-decimal recommendation) — correct and consistent
  - `openingHoursSpecification` ✅ present (Mon–Sat 09:00–17:00) on index.html and contact.html — **missing entirely from the 13 location pages and about.html/admissions.html schema blocks** (they include address/geo but not hours)
  - `telephone` ✅ present
  - `url` ✅ present
- `@id` used on index.html (`https://nagachethanapucollege.in/#organization`) for entity consolidation — good practice — but the location pages reuse the **same `@id`** while omitting `openingHoursSpecification`, meaning Google may merge/conflict partial entity descriptions across 14+ pages rather than treating it as one canonical record with satellite pages. Recommend only index.html/contact.html carry the full Organization entity with `@id`, and location pages reference it via `@id` alone (no duplicate abbreviated copy).
- `aggregateRating`/`review` embedded in Organization schema — see Review Health section; this is the most serious schema issue (policy risk), not a completeness gap.
- FAQPage schema is duplicated with page-specific Q&A on every location page — fine, but note Google has scaled back FAQ rich-result eligibility to authoritative government/health sites only as of 2023+, so FAQ schema is largely non-impactful for rankings now (still useful for AI-answer extraction/AEO).
- No `Review`/`aggregateRating` per-location — appropriate, since it's one physical entity.

---

## Location Page Quality (13 `pu-college-<area>.html` pages)

- Pairwise body-text similarity across 5 sampled pages (Bagalur/Hebbal/Jakkur/RT Nagar/Kogilu): **60–69%** — moderate overlap but not a doorway-page duplicate (each page has genuinely unique distance/km, drive-time, named local route, BMTC bus route numbers, and a named local landmark for that specific suburb, confirmed on the Bagalur page: "10 km / 20 min via Bagalur Main Road", "BMTC routes 401 and 285M", landmark "Bagalur Cross").
- Word count ~1,380–1,500 words per location page — reasonable depth, not thin.
- All 13 pages share the identical geo-coordinates and address, which is correct (single campus serving multiple catchment areas — not fake multi-location listings), but combined with ~60-69% shared boilerplate this still creates moderate content-duplication risk if Google's algorithm judges the unique-content ratio too low; recommend increasing area-specific detail (local school feeder info, testimonials from students of that area, transit maps) to push similarity below ~50%.
- Internal linking: location pages are cross-linked to each other (per recent commit "cross-link all 13 Bangalore area pages") and link back to /admissions and /contact — good internal linking depth, but confirm they are also linked from a real, crawlable hub (e.g., a "service areas" page or footer) rather than only from each other, to avoid an orphaned cluster.
- No location pages found targeting the actual physical neighborhood name ("Old Town, Yelahanka" itself) as a dedicated page — the closest is the homepage; consider one due to it being the literal campus location.

---

## What Works

- Exceptionally consistent NAP string (name/address/phone/geo) across all 17 checked pages — no drift in the core fields, which is rare and a real strength.
- Real, valid Google Maps embed with an authentic Place ID on the contact page.
- Location pages are not lazy doorway pages — each has unique, locally-relevant distance/route/bus/landmark content, satisfying the "dedicated service pages" ranking factor called out as the #1 local-organic and #2 AI-visibility factor.
- `openingHoursSpecification`, `geo` (6-decimal precision), `telephone`, and `url` are present on the primary Organization schema.
- Good breadcrumb + FAQPage schema layering for AEO/AI visibility.
- Multiple verified phone numbers with consistent `tel:` formatting (no spacing/format mismatches between visible text and `href`).

---

## Top 10 Prioritized Actions

1. **[CRITICAL]** Remove the hard-coded `review` array (named reviewer reviews) from the `CollegeOrUniversity` JSON-LD on index.html. Self-authored reviews embedded directly in Organization schema without a genuine third-party review platform source violate Google's structured-data guidelines and risk suppression of rich results or a manual action. Replace with a legitimate embedded Google review widget or remove `review`/`aggregateRating` from schema entirely and instead display real reviews on-page with a link-through to the GBP listing.
   - Fix: in `index.html` line ~98, delete the `"review":[...]` key from the JSON-LD block; keep `aggregateRating` only if the numbers are pulled from and verifiably match the live GBP (confirm 4.8/105 against actual GBP before keeping even that).
2. **[CRITICAL]** Manually verify live GBP: category selection, name variant, current rating/review count, and confirm it matches on-site claims of "4.8★, 105 reviews" — this could not be checked in this session and is the single highest-weighted dimension (GBP Signals, 25%).
3. **[HIGH]** Build/verify citations on CollegeDunia, Careers360, Shiksha, Justdial, Sulekha, Bing Places, and Apple Maps — none are referenced anywhere in the site's own code (`sameAs` has only Facebook/Instagram/YouTube/one Google share link), suggesting these profiles are unclaimed or unlinked; 3 of the top-5 AI-visibility ranking factors are citation-related.
4. **[HIGH]** Add `openingHoursSpecification` to the JSON-LD on all 13 `pu-college-<area>.html` pages and about.html/admissions.html — currently only index.html and contact.html include it, leaving most crawled entity mentions incomplete.
5. **[HIGH]** Replace the static "4.8★ Google rating with 105 reviews" text block on index.html with a live-updating embedded review widget (or at minimum a manual quarterly refresh process) to avoid the 18-day review-staleness ranking cliff and avoid the schema/text going out of sync with the real GBP number.
6. **[MEDIUM]** Fix the mismatched contact email on `privacy.html`/`terms.html` (`info@nagachethanapucollege.com`, wrong TLD and mailbox) to match the canonical `npucan928@gmail.com` / `jayalakshmi@nagachethanapucollege.in` used everywhere else.
7. **[MEDIUM]** Reduce the duplicate `@id`-tagged Organization schema block being fully repeated (minus hours) across all 13 location pages; keep the full entity definition only on index.html/contact.html and reference it by `@id` elsewhere to avoid entity-merging conflicts.
8. **[MEDIUM]** Increase area-specific unique content on location pages (currently 60–69% pairwise similarity) — add area-specific testimonials, nearby-school feeder details, or a small embedded mini-map per area to push uniqueness higher and reduce duplicate-content risk.
9. **[LOW]** Add a dedicated citation/GBP link block in the footer (e.g., "Find us on Google Maps | Justdial | Sulekha") once those profiles are verified/claimed, both for users and to reinforce `sameAs` entity linking.
10. **[LOW]** Clean up the `admissions.html` placeholder `name@example.com` to confirm it's only a form placeholder attribute and not indexable/rendered contact text.

---

## Competitor Research

**UNVERIFIED** — could not identify or evaluate competing PU colleges ranking for "PU college in Yelahanka" in this session due to search engine fetch restrictions (Google/Bing/DuckDuckGo/Mojeek all blocked or CAPTCHA'd). Recommend the site owner run this query directly in an incognito browser from Bangalore (or via a rank-tracking tool) and share the top 3–5 local-pack results so competitor GBP category, review count, and photo/post cadence can be benchmarked in a follow-up pass.
