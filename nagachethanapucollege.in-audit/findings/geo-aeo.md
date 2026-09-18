# GEO + AEO Audit — nagachethanapucollege.in

Audited: local mirror `C:\Users\Lenovo\Desktop\All websites\nagachethan-pu-college` (confirmed identical structurally to live site via live curl checks). Date: 2026-09-18.

## Scores

**GEO Readiness Score: 74/100**
**AEO Readiness Score: 68/100**

### GEO dimension breakdown
| Dimension | Weight | Score | Notes |
|---|---|---|---|
| Citability | 25% | 70/100 | Good FAQ schema and direct facts, but answer passages are shorter (19-40 words) than the optimal 134-167 word LLM-citation window; no fee numbers published |
| Structural Readability | 20% | 85/100 | Clean H1/H2/H3 hierarchy, TOC anchors on blog posts, lists/tables for comparisons |
| Multi-Modal Content | 15% | 45/100 | Homepage (`index.html`) and `about.html` have **zero `<img>` tags** despite 15+ campus/lab photos existing in the repo; YouTube channel linked but activity unverified |
| Authority & Brand Signals | 20% | 75/100 | Strong schema (CollegeOrUniversity, Person, Review, AggregateRating), sameAs links to FB/Instagram/YouTube/Google, real GBP place_id embedded; off-site presence largely unverified (search engines blocked automated fetch) |
| Technical Accessibility | 20% | 95/100 | Fully static HTML, all target AI crawlers return 200, robots.txt explicitly allows GPTBot/ClaudeBot/PerplexityBot/etc., llms.txt present and accurate |

### AEO dimension notes
- Featured-snippet/PAA readiness is helped by short `<details>` FAQ answers (good for PAA-style snippets, 19-40 words) but hurt by absence of numeric fee data and by several declarative (non-question) H2s.
- FAQPage schema present on index, about, academics, admissions, contact, and long-tail blog posts — strong PAA eligibility.
- Voice-answer readiness: direct Q→A pairs like "PCMB full form" are excellent (single-sentence, schema-marked); "PU college fees in Yelahanka" has no answerable value anywhere on site.

---

## AI Crawler Access (live curl, User-Agent tested against https://nagachethanapucollege.in/)

| Crawler | robots.txt rule | Live HTTP status |
|---|---|---|
| GPTBot | Allow: / | 200 |
| ChatGPT-User (OAI-SearchBot proxy) | Allow: / | 200 (tested as OAI-SearchBot UA) |
| ClaudeBot | Allow: / | 200 |
| PerplexityBot | Allow: / | 200 |
| anthropic-ai | Allow: / | 200 |
| Google-Extended | Allow: / | (not directly UA-tested; robots.txt allows) |
| Bingbot | Allow: / | (not directly UA-tested; robots.txt allows) |
| CCBot (training-only, optional block per spec) | not listed — falls under `User-agent: * / Allow: /` | 200 (not blocked; spec recommends optional block, currently allowed) |

No server-side blocks (WAF/.htaccess) detected for AI crawler UAs. `.htaccess` was reviewed for UA-based blocks — none found targeting AI bots. robots.txt live matches local mirror exactly (verified diff).

## llms.txt Status: **Present, well-formed, and accurate**

`llms.txt` (live, 200 OK) contains: college code (AN0928), founding year/founder, address, phone, email, hours, pass rate, streams, page index, and 6 FAQ pairs. Cross-checked against page content — all facts (98% pass rate, 2015 founding, AN0928 code, 4.8/5 rating) match what's published on `index.html` and `about.html`. No RSL 1.0 licensing block found (not present — optional per spec, low priority).

## Entity Name Consistency
- "Nagachethana" (693 occurrences) and lowercase "nagachethana" (313) dominate; no instances of "Naga Chethana" (space-separated) or "Nagachethan" (truncated) found on-site — **on-site entity naming is consistent**, single canonical form used throughout.
- Off-site variant usage (directories, GBP listing name) **could not be verified** — automated search fetches (Google, Bing, DuckDuckGo) returned CAPTCHA/blocked/irrelevant results in this session. This should be manually verified by checking Justdial, Shiksha, CollegeDunia, and the Google Business Profile listing for exact name-match against "Nagachethana PU College."

## Brand / Off-Site Signal Check

| Platform | Status |
|---|---|
| Google Business Profile | Place ID embedded in site (`0x3bae1955d0b76287:0x8d0419a80c2d89ea`) and Maps embed present on contact.html — GBP profile almost certainly exists and is linked correctly from the site. Review count (105) and rating (4.8) cited in schema/llms.txt but not independently re-verified this session (**unverified**). |
| Facebook | Page URL present in schema `sameAs` (`facebook.com/p/Nagachethana-PU-College-100069749096197/`), returns HTTP 200 |
| Instagram | `instagram.com/nagachethanapucollege/`, returns HTTP 200, linked in schema |
| YouTube | Channel linked (`youtube.com/@nagachethanapucollege4324`) in schema `sameAs`; **could not confirm video count or upload activity** (fetch did not return a parseable video count — flag as unverified, manual check recommended). YouTube mention/presence is the single strongest AI-citation correlator (~0.737), so this is a high-priority item to verify and grow. |
| Wikipedia / Wikidata | **No Wikipedia article found** (`en.wikipedia.org/wiki/Nagachethana_PU_College` → 404). No entity page exists — this is a real gap, though unsurprising for a single small private college (Wikipedia notability bar is high; a Wikidata item without a full article may still be achievable and would help entity recognition). |
| Justdial / Shiksha / CollegeDunia / Reddit | **Unverified** — automated search engine queries in this session were blocked by CAPTCHA (DuckDuckGo) or returned irrelevant/cached results (Google, Bing via WebFetch). Manual verification recommended; these directories strongly influence local-education AI citations and were flagged as a research gap, not a confirmed absence. |
| "best PU college in Yelahanka" SERP/AI citation check | **Not completed** — could not get usable live search results through available fetch tools this session (returned browser-download results, CAPTCHA pages, or empty rewrites). Recommend a manual check of ChatGPT/Perplexity/Google AIO for this query as a follow-up. |

---

## Citability Analysis (passage-level, from extracted HTML text)

- Homepage FAQ answers: 6 Q&A pairs, word counts 19, 33, 33, 35, 38, 40 — **all well under the 134-167 word optimal citation length**. They're excellent for short PAA/voice snippets but likely too thin for an LLM to prefer as a citation source over a longer, more self-contained competitor passage (e.g., a Shiksha or CollegeDunia entry with fuller context).
- Blog posts (e.g., `blog-pcmb-vs-pcmc.html`, `blog-top-pu-colleges-yelahanka.html`) have well-structured opening paragraphs (40-70 words) with named entities and a linked authoritative source (`pue.karnataka.gov.in`) — good, but body paragraphs under H2s were not individually word-counted at scale within session time; spot checks suggest most are close to or slightly under the ideal range.
- Admissions "Fee Overview" section: **no fee figures published anywhere in the crawled HTML** — text reads "Fee structure varies by stream. Contact our admission office for detailed fee information." This is a critical citability gap: AI engines answering "PU college fees in Yelahanka / Nagachethana PU College fees" have literally nothing extractable to cite and will default to third-party listings (Shiksha/CollegeDunia/Justdial) instead, if those have numbers.
- Dates/freshness: `dateModified` schema present and current (2026-09-08 for most core pages, 2026-03-06/07/13 for blog posts), sitemap `<lastmod>` values match. "2026-27" admission-cycle year used consistently (11 occurrences) — good freshness signal.

## Structural Readability
- Question-style headings present mainly in FAQ sections (schema-marked `Question`/`Answer`) and in some blog H2s ("What Are PCMB and PCMC?"). Most page section H2s are declarative ("Fee Overview," "Streams We Offer," "How to Get Admitted") rather than question-form — fine for on-page UX but a missed opportunity for direct PAA/snippet matching on queries like "PU college fees in Yelahanka" or "How much are PU college fees in Yelahanka."
- Tables/lists used well in 8 pages for comparisons (blog-top-pu-colleges-yelahanka.html has a full side-by-side comparison table; blog-pcmb-vs-pcmc.html has a full comparison table).
- FAQPage schema deployed on 8+ pages (index, about, academics, admissions, contact, and long-tail blogs) — strong structured-data coverage.

## Technical Accessibility
- Site is 100% static HTML (no React/Vue/Angular/Next.js SPA shell detected in any crawled page — one false-positive grep hit on the word "reactions" in chemistry-tips copy, not a JS framework). No CSR risk — content is fully present in raw HTML for any crawler that doesn't execute JS, which covers GPTBot, ClaudeBot, PerplexityBot today.
- robots.txt is explicit and correct; llms.txt is accurate; sitemap.xml exists with lastmod dates.

## Multi-Modal Content (biggest surprise finding)
- `index.html` (homepage) and `about.html` contain **zero `<img>` tags** — no logo, campus, classroom, or lab photos are actually embedded in the HTML markup, despite `logo.jpg`, `og-banner.jpg`, and 10+ campus/facility photos existing in the project root and being used correctly (with descriptive alt text) on `academics.html` (6 images, good alt text) and `gallery.html` (18 images, weaker generic alt text like "Classroom Setup," "Learning Space").
- This matters for GEO because image presence/alt-text is part of multi-modal citability and also reinforces authority signals (faculty photos, real campus imagery) that LLMs and users use to judge legitimacy — the two highest-traffic, highest-authority pages currently offer none of that.

---

## Top 10 Issues (prioritized, one line each)

1. **[HIGH]** Homepage and About page have zero `<img>` tags — no visible campus, lab, or leadership photos despite assets existing on disk; hurts multi-modal citability and trust signals.
2. **[HIGH]** No fee figures published anywhere on the site ("Contact us" placeholder) — kills citability for the highly-searched query "PU college fees in Yelahanka" and cedes that answer to third-party directories.
3. **[MEDIUM]** FAQ answer passages (19-40 words) are well below the 134-167 word optimal LLM-citation length — good for PAA snippets, but likely too thin to win citation over longer competitor passages.
4. **[MEDIUM]** No Wikipedia/Wikidata entity page exists for the college — a real gap in entity-graph presence that affects LLM knowledge-grounding, though realistically hard to achieve for a single small institution.
5. **[MEDIUM]** Off-site directory presence (Justdial, Shiksha, CollegeDunia, Reddit) could not be verified this session (search tools blocked/CAPTCHA'd) — unverified risk, needs manual confirmation given local-education queries lean heavily on these sources.
6. **[MEDIUM]** YouTube channel is linked but upload activity/video count is unverified — YouTube presence is the single strongest AI-citation correlator (0.737) and deserves confirmation + active investment.
7. **[LOW]** Most section H2s are declarative rather than question-form (e.g., "Fee Overview" vs. "How much are PU college fees in Yelahanka?") — missed alignment with real voice/PAA query phrasing.
8. **[LOW]** Gallery.html image alt text is generic ("Classroom Setup," "Learning Space") vs. academics.html's descriptive, entity-rich alt text ("Physics Laboratory at Nagachethana PU College Yelahanka") — inconsistent multi-modal SEO quality across pages.
9. **[LOW]** No RSL 1.0 licensing declaration present — optional per current GEO best practice but increasingly expected as AI licensing signals mature.
10. **[LOW]** CCBot (training-only crawler) is not explicitly addressed in robots.txt and falls under the open wildcard `Allow: /` — currently allowed for training scraping; consider an explicit optional block if the college wants to permit AI-search indexing (GPTBot etc.) while opting out of pure model-training scraping (CCBot, anthropic-ai used for training).

## What Works Well
- All target AI search crawlers (GPTBot, ClaudeBot, PerplexityBot, anthropic-ai) return live HTTP 200 with explicit `Allow: /` rules in robots.txt — no technical blocking.
- llms.txt is present, accurate, and well-structured with key facts, page index, and FAQ pairs matching live content.
- Fully static HTML site — no SPA/CSR risk for crawlers.
- Rich, correctly-implemented structured data: FAQPage, CollegeOrUniversity, Person, Review/AggregateRating, BreadcrumbList, Course, GeoCoordinates/Place, OpeningHoursSpecification across most core pages.
- Consistent single-form entity naming ("Nagachethana PU College") throughout the entire site — no damaging name variant fragmentation found on-site.
- `sameAs` schema links to Facebook, Instagram, YouTube, and a Google Maps/Business place ID are correctly implemented and all return HTTP 200.
- Strong internal content architecture for "best PU college in Yelahanka" / comparison-style queries: dedicated blog posts with comparison tables, cited external authority (pue.karnataka.gov.in), and 13 hyperlocal area pages (Bagalur, Hebbal, Hennur, etc.) feeding a cross-linked mesh.
- Freshness signals are current and consistent: `dateModified` schema and sitemap `lastmod` values are recent (Sept 2026), "2026-27" admission cycle referenced consistently across the site.

## Unverified Items (flagged, not scored as pass/fail)
- Off-site NAP consistency across Justdial, Shiksha, CollegeDunia, Google Business Profile listing name/rating.
- Actual AI citation behavior for "best PU college in Yelahanka" in ChatGPT/Perplexity/Google AIO/Bing Copilot.
- YouTube channel activity/video count.
- Reddit presence/mentions.
