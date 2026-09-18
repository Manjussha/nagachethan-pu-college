# Schema.org / JSON-LD Audit — nagachethanapucollege.in

Audited: local mirror `C:\Users\Lenovo\Desktop\All websites\nagachethan-pu-college\*.html` (confirmed identical to live). 30 content pages examined (404.html, header.html, footer.html, googlec9407f7e62e77f27.html, model_comparison.html excluded — no rendered JSON-LD / not indexable content pages).

**Overall Score: 58 / 100**

All JSON-LD parses without syntax errors and uses correct, non-deprecated types — a solid technical base. The score is pulled down primarily by one **Critical** content-policy risk (self-serving reviews on an Organization/CollegeOrUniversity node) plus **@id / NAP fragmentation** that splits the entity graph across pages.

---

## 1. Detection Results (what exists)

| Page | JSON-LD blocks | Types |
|---|---|---|
| index.html | 3 | CollegeOrUniversity, WebSite, FAQPage |
| about.html | 1 (array) | BreadcrumbList, AboutPage, FAQPage |
| academics.html | 1 (array) | BreadcrumbList, ItemList (Course×3), FAQPage |
| achievements.html | 2 | FAQPage, BreadcrumbList |
| admissions.html | 3 | FAQPage, BreadcrumbList, CollegeOrUniversity |
| contact.html | 3 | CollegeOrUniversity, BreadcrumbList, FAQPage |
| gallery.html | 2 | ImageGallery, BreadcrumbList |
| blog.html | 2 | Blog, BreadcrumbList |
| blog-*.html (×7 posts) | 1 (array) each | BreadcrumbList, BlogPosting, FAQPage |
| pu-college-*.html (×13 area pages) | 1 (array) each | BreadcrumbList, CollegeOrUniversity, FAQPage |
| privacy.html / terms.html | 1 (array) each | BreadcrumbList, WebPage |

No Microdata or RDFa found anywhere — JSON-LD only (correct, per best practice). No HowTo, SpecialAnnouncement, CourseInfo, EstimatedSalary, or LearningVideo found anywhere (good — nothing deprecated in use).

---

## 2. Validation Results (pass/fail)

| Check | Result |
|---|---|
| Valid JSON syntax (all 30 pages) | ✅ Pass — 0 parse errors across every `<script type="application/ld+json">` block |
| `@context` = `https://schema.org` | ✅ Pass everywhere (no `http://`) |
| No deprecated `@type`s | ✅ Pass |
| Absolute URLs in `url`, breadcrumb `item`, images | ✅ Pass |
| Dates ISO 8601 | ✅ Pass (`datePublished`/`dateModified` on blog posts; `foundingDate: "2015"` is a valid partial-ISO year) |
| Placeholder text (`[Business Name]`, "lorem ipsum", etc.) | ✅ Pass — none found |
| BreadcrumbList `position` + `name` + `item` present | ✅ Pass on every page checked |
| Breadcrumb URLs match real resolvable clean URLs | ✅ Pass — verified against `.htaccess` rewrite rules (`.html` → clean URL 301 + internal rewrite back); e.g. `/blog-pcmb-vs-pcmc` resolves |
| `@id` consistency for the Organization node | ❌ **Fail** — see Issue #2 |
| NAP (name/address/phone/email/sameAs) consistency | ❌ **Fail** — see Issue #3 |
| Self-serving Review/AggregateRating | ❌ **Fail (Critical)** — see Issue #1 |
| Course schema completeness | ⚠️ Partial — see Issue #5 |
| BlogPosting required properties | ✅ Pass (headline, datePublished, dateModified, author, image, publisher w/ logo present on all 7 posts) — ⚠️ minor gaps, see Issue #6 |

---

## 3. Top Issues (ranked by severity)

### Issue #1 — CRITICAL: Self-serving Review + AggregateRating markup on your own Organization
**Where:** `index.html` (CollegeOrUniversity block) and `admissions.html` (CollegeOrUniversity block).

`index.html` embeds an `aggregateRating` of `4.8` from `105` ratings, backed by only **3** inline `Review` objects written in first person testimonial style with named "customers" (Pradeep Kumar, Sunitha Reddy, Ravi Shankar). This is a textbook case of what Google's structured data guidelines call self-serving reviews: reviews of an organization, authored/curated by that same organization, marked up on its own pages. Google explicitly disallows review snippets in this pattern for Organization/LocalBusiness/CollegeOrUniversity types when the reviews aren't sourced from an independent third-party platform (Google/Facebook/Justdial reviews, etc.) — at minimum this markup is silently ignored by Google, and at worst it's a manual-action risk if reported/detected as manipulated content, since:
- The `ratingCount: 105` has no matching corpus — only 3 reviews are provided anywhere in the markup, an unverifiable/inflated count.
- `admissions.html` repeats the identical `aggregateRating` (4.8 / 105) with **zero** `review` items backing it at all — an aggregate rating floating with no source content is an even clearer violation.
- These review authors cannot be verified as real, consenting reviewers by any crawler; there's no link to an original review source (no `url` on the Review, no third-party platform reference).

**Fix:** Remove `aggregateRating` and `review` from both `index.html` and `admissions.html` entirely. If you want genuine review rich results, embed a **Google Business Profile review widget/link** instead (not schema), or only mark up reviews that live on an actual independent third-party review platform you don't control. Do not self-publish Review/AggregateRating JSON-LD about your own institution.

```json
// index.html — CollegeOrUniversity block: DELETE these two keys entirely
"aggregateRating": { ... },   // ← remove
"review": [ ... ]             // ← remove
```

---

### Issue #2 — HIGH: `@id` inconsistency fragments the Organization entity graph
**Where:** `index.html` + all 13 `pu-college-*.html` area pages use
`"@id": "https://nagachethanapucollege.in/#organization"` — correct, consistent, reusable node reference.

`contact.html` and `admissions.html` CollegeOrUniversity blocks **omit `@id` entirely**. Without a shared `@id`, Google/LLMs cannot reliably merge these blocks into a single canonical entity — worst case, it creates duplicate/competing Organization nodes in the Knowledge Graph, diluting entity authority signals across the domain.

**Fix — add to both `contact.html` and `admissions.html`:**
```json
{
  "@context": "https://schema.org",
  "@type": "CollegeOrUniversity",
  "@id": "https://nagachethanapucollege.in/#organization",
  "name": "Nagachethana PU College",
  ... (existing fields unchanged)
}
```

---

### Issue #3 — HIGH: NAP (Name/Address/Phone/sameAs) inconsistency across blocks
**Where:** Comparing `index.html`, `contact.html`, `admissions.html`, and the 13 area pages:

| Field | index.html | contact.html | admissions.html | area pages (×13) |
|---|---|---|---|---|
| `telephone` | single `"+919739085747"` | **array** of 3 numbers (`+919739085747`, `+919901302315`, `+917892671800`) | single `"+919739085747"` | single `"+919739085747"` |
| `email` | single `"npucan928@gmail.com"` | **array** of 2, different order/primary (`jayalakshmi@nagachethanapucollege.in` listed first) | *(absent)* | single `"npucan928@gmail.com"` |
| `sameAs` | 4 links (FB, IG, YouTube, `share.google`) | 3 links (missing `share.google`) | *(absent)* | *(absent on all 13)* |
| `geo` | present | *(absent)* | *(absent)* | present |

None of these are individually invalid (multiple phones/emails are legal per schema.org), but the **inconsistency itself** is the problem: search engines cross-reference NAP fields across pages/citations to establish local-business trust, and a fragmented/partial picture (some pages richer than others, `admissions.html` missing sameAs and geo entirely) weakens that signal. It also means the "primary" contact email flips depending on which page a crawler samples.

**Fix:** Standardize every CollegeOrUniversity block sitewide to carry the **same** `telephone` array, the **same** `email` array (with `npucan928@gmail.com` first to match the majority pattern), and the **same** `sameAs` array (including the `share.google` link), regardless of which page it's on. Recommended canonical block (also folds in the `@id` fix from Issue #2):

```json
{
  "@context": "https://schema.org",
  "@type": "CollegeOrUniversity",
  "@id": "https://nagachethanapucollege.in/#organization",
  "name": "Nagachethana PU College",
  "alternateName": "Nagachethana PUC",
  "url": "https://nagachethanapucollege.in/",
  "logo": "https://nagachethanapucollege.in/logo.jpg",
  "image": "https://nagachethanapucollege.in/logo.jpg",
  "description": "Nagachethana PU College, Yelahanka, Bengaluru – offering Science and Commerce PUC streams since 2015. College Code: AN0928.",
  "foundingDate": "2015",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "Near Raithara Santhe, Behind KIA Car Showroom, Old Town, Yelahanka",
    "addressLocality": "Bengaluru",
    "addressRegion": "Karnataka",
    "postalCode": "560064",
    "addressCountry": "IN"
  },
  "geo": {
    "@type": "GeoCoordinates",
    "latitude": "13.099078",
    "longitude": "77.597719"
  },
  "hasMap": "https://maps.google.com/?q=Nagachethana+PU+College+Yelahanka+Bengaluru",
  "telephone": ["+919739085747", "+919901302315", "+917892671800"],
  "email": ["npucan928@gmail.com", "jayalakshmi@nagachethanapucollege.in"],
  "sameAs": [
    "https://www.facebook.com/p/Nagachethana-PU-College-100069749096197/",
    "https://www.instagram.com/nagachethanapucollege/",
    "https://www.youtube.com/@nagachethanapucollege4324",
    "https://share.google/kDrGB3ZfqgcPQv0aN"
  ]
}
```
(Full pages can keep their page-specific extras — e.g. `contactPoint`, `areaServed`, `hasOfferCatalog`, `openingHoursSpecification` — layered on top of this canonical core; just don't let the core NAP fields drift.)

---

### Issue #4 — MEDIUM: `admissions.html` aggregateRating has zero supporting reviews
Already flagged as part of Issue #1's removal, called out separately because it's the more severe half: an `aggregateRating` node with `ratingCount: 105` and no `review` array anywhere on that page is unsubstantiated by definition — remove it along with the index.html copy (see Issue #1 fix).

---

### Issue #5 — MEDIUM: Course schema (academics.html) missing `hasCourseInstance`/`offers` and uses inline duplicate `provider` instead of `@id` reference
The `ItemList` of 3 `Course` items (PCMB, PCMC, Commerce) is a good addition, but:
- No `hasCourseInstance` (with `courseMode`, `courseSchedule`) or `offers` — Google's Course structured data guidelines require at least one of these for eligibility, and generative/AI engines benefit from structured schedule/mode data too.
- `provider` is a fresh inline `{"@type":"CollegeOrUniversity","name":...,"url":...}` object rather than referencing the canonical node via `{"@id": "https://nagachethanapucollege.in/#organization"}`, which (once Issue #2 is fixed) would properly link courses to the single Organization entity.

**Fix (per Course item):**
```json
{
  "@type": "Course",
  "name": "Science Stream – PCMB",
  "description": "Physics, Chemistry, Mathematics and Biology stream for students targeting NEET, CET and engineering entrances.",
  "provider": { "@id": "https://nagachethanapucollege.in/#organization" },
  "hasCourseInstance": {
    "@type": "CourseInstance",
    "courseMode": "Onsite",
    "courseWorkload": "PT30H",
    "location": {
      "@type": "Place",
      "name": "Nagachethana PU College, Yelahanka, Bengaluru"
    }
  }
}
```

---

### Issue #6 — MEDIUM: BlogPosting gaps — no Person byline, shared generic image, no `mainEntityOfPage`
All 7 blog posts (`blog-best-pu-college-yelahanka.html`, `blog-commerce-stream-yelahanka.html`, `blog-kcet-2026-preparation-tips.html`, `blog-pcmb-vs-pcmc.html`, `blog-pu-college-near-bangalore-airport.html`, `blog-puc-admissions-2026-27.html`, `blog-top-pu-colleges-yelahanka.html`) have complete required fields (headline, datePublished, dateModified, author, image, publisher+logo), but:
- `author` is always `{"@type":"Organization", ...}` — no named `Person` author anywhere, which is a missed E-E-A-T signal (a real staff/principal byline reads more authoritative to both Google and AI engines).
- `image` is the identical sitewide `og-banner.jpg` on every post — no article-specific image, weakening uniqueness for image rich results.
- `mainEntityOfPage` (recommended, not required) is absent on all 7.

**Fix (add per post):**
```json
{
  "@type": "BlogPosting",
  "mainEntityOfPage": { "@type": "WebPage", "@id": "https://nagachethanapucollege.in/blog-pcmb-vs-pcmc" },
  "author": {
    "@type": "Person",
    "name": "G. Jayalakshmi",
    "jobTitle": "Principal",
    "url": "https://nagachethanapucollege.in/about"
  },
  "image": "https://nagachethanapucollege.in/facility-biology-lab.jpg"
}
```
(Swap in a genuinely article-relevant image per post rather than the shared banner.)

---

### Issue #7 — LOW: FAQPage present on 25 of 30 pages — no Google SERP benefit, keep for AI/GEO only
`FAQPage` markup appears on: about, academics, achievements, admissions, contact, index, all 7 blog posts, and all 13 area pages. Per current policy, Google retired FAQ rich results for all sites (May 7, 2026), so none of this markup will produce a SERP rich result anymore. This is **not** a defect to fix — the existing FAQPage blocks still have real value for AI/LLM answer-engine citation and entity/QA extraction, so **do not remove them**. Flagging as Info: don't invest further effort chasing FAQ *rich-result* eligibility, and for any genuinely user-submitted Q&A content in the future, use `QAPage` instead of `FAQPage`.

---

### Issue #8 — LOW: Static, identical `dateModified` copied across 14 pages; WebSite node has no `@id`/`SearchAction`
- `dateModified: "2026-09-08"` is hard-coded identically across `index.html` and all 13 `pu-college-*.html` pages — the actual file mtimes differ (some Sep 10, some unrelated), so this date is not truly page-specific and reads as a static/stale value rather than a genuine last-modified signal. Recommend either removing `dateModified` from pages that aren't actually re-published, or wiring it to the real content update date per page.
- The `WebSite` entity on `index.html` has no `@id` and no `potentialAction` (`SearchAction`) — a low-cost addition if there's an on-site search page, enabling the Sitelinks Search Box feature:
```json
{
  "@context": "https://schema.org",
  "@type": "WebSite",
  "@id": "https://nagachethanapucollege.in/#website",
  "name": "Nagachethana PU College",
  "url": "https://nagachethanapucollege.in/",
  "publisher": { "@id": "https://nagachethanapucollege.in/#organization" }
}
```
(Omit `SearchAction` unless a real site-search endpoint exists — do not add a placeholder.)

---

## 4. What Works Well

- **Zero JSON parse errors** across all 30 pages — every block is syntactically valid.
- **Correct, current `@type` choices**: `CollegeOrUniversity` (appropriate subtype of EducationalOrganization, better than generic `LocalBusiness`) for the institution; `BlogPosting` for blog content; `ItemList`/`Course` for streams; `BreadcrumbList`, `WebSite`, `ImageGallery`, `WebPage` used appropriately elsewhere.
- **Nothing deprecated in use anywhere**: confirmed no `HowTo`, `SpecialAnnouncement`, `CourseInfo`, `EstimatedSalary`, or `LearningVideo`.
- **`@context` always `https://schema.org`** (correct HTTPS form, never `http://`).
- **BreadcrumbList is exemplary**: every instance has correct `position`, `name`, and absolute `item` URLs, and — verified against `.htaccess` — those clean URLs (`/blog-pcmb-vs-pcmc`, `/pu-college-hebbal`, etc.) genuinely resolve via the site's rewrite rules, not just cosmetic/broken links.
- **13 area (near-me) pages have genuinely localized FAQ content**, not templated/duplicated questions — good defense against doorway-page/duplicate-content concerns.
- **No placeholder text** (`[Business Name]`, "Lorem ipsum", `example.com`, etc.) found in any JSON-LD block.
- **Format discipline**: JSON-LD used exclusively — no competing/conflicting Microdata or RDFa anywhere on the site.

---

## 5. Priority Fix Order

1. **Critical** — Remove self-serving `review`/`aggregateRating` from `index.html` and `admissions.html` (Issue #1, #4).
2. **High** — Add matching `@id` to `contact.html` and `admissions.html` CollegeOrUniversity blocks (Issue #2).
3. **High** — Standardize `telephone`/`email`/`sameAs`/`geo` across all CollegeOrUniversity blocks sitewide (Issue #3).
4. **Medium** — Add `hasCourseInstance`/`offers` to Course items and switch `provider` to `@id` reference (Issue #5).
5. **Medium** — Add Person byline options and per-post images to BlogPosting (Issue #6).
6. **Low** — Leave FAQPage as-is for AI/GEO value (Issue #7); tidy `dateModified` and add `WebSite` `@id` (Issue #8).
