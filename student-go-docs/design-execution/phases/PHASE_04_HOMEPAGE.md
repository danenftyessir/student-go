# PHASE 04 — Homepage

> Source of truth for status is `student-go-docs/design-execution/PHASE_TRACKER.md`. This file is the work order for exactly one phase. Read `CLAUDE.md` and `student-go-docs/design-execution/EXECUTION_ROADMAP.md` first, execute only this phase, update the tracker, report, then stop.
> Evidence labels: VERIFIED / OBSERVED / INFERRED / UNKNOWN / BLOCKED (definitions in `DISCOVERY_REPORT.md` section 1).

| Field | Value |
|---|---|
| Phase | 4 — Homepage |
| Group | Public and authentication |
| Hard dependencies (must be COMPLETE) | Phase 3 (Shared Shell & Components) |
| Soft dependencies (recommended first) | Phase 1 (Critical Fixes & Data Integrity) |
| Decisions that gate this phase | D-01 Product name, wordmark and assistant name, D-19 Documentary photography, D-20 Delivery of JS/CSS libraries, D-23 Content ownership for new or rewritten copy, D-30 Provenance and consent for existing photographs in `public/`, D-33 Whether to download the 60 catalogue photographs at all, D-34 Photograph derivatives: format, sizes and location |
| Effort estimate (planning guess, not a commitment) | L |
| Roles touched | Public (guest) |
| Audit references | G01; J9, J15, J20, J23, J7; audit D1, H, L1 |
| Tracker status at planning time | NOT_STARTED |

## A. Phase overview

**Goal.** Replace the template-looking marketing page with a short, honest, three-screen page: a typographic hero with a clear call to action, live proof drawn from real data, and a role-by-role explanation, keeping the interactive Leaflet coverage map as the one distinctive visual.

**In scope**

- Hero without photo background, mesh gradient, Merriweather/Lora, or glass pills.
- Real numbers only (from the database) or none; remove the hard-coded 34 / 514 / 85+ / 120+ block (VERIFIED hard-coded on the live page).
- Proof section: 3-5 latest open KKN problems as list rows plus the interactive coverage map.
- Audience sections ('Wujudkan Dampak Nyata Lewat KKN' and 'Rekrut Talenta Terbaik untuk Proyek Anda' today) rewritten as two text columns with an original workflow SVG or, later, real screenshots.
- Removal of 'Kenapa Memilih Student Go?' three-feature row and the motivational quote block.
- Fix horizontal scroll at 390 and 768 px (the AOS `fade-left` elements); remove AOS from this page.
- Editorial layer: selected documentary photographs framed between paragraphs, never as backgrounds (D-19).

**Out of scope**

- Registration and login pages (Phase 6) and the About/Contact pages (Phase 5).
- Writing marketing claims. Every sentence must be supportable by product facts (D-23).
- Replacing Leaflet or OSM tiles. The interactive map stays (AD-08).

**Evidence basis.** The audit (OBSERVED, 2026-10-28, port 8001) plus a read-only look at the running app on 2026-10-30. No repository source was read when this file was written; every statement about templates, controllers, models, middleware and tests is UNKNOWN until Stage One (Phase 0) fills `DISCOVERY_REPORT.md` section 12.

## B. Pages and routes

| Route pattern | Role | Audit status | Evidence | Notes |
|---|---|---|---|---|
| / | Public | Inspected | OBSERVED; page VERIFIED live | Title 'Laravel - ...' (VERIFIED). Hard-coded stats 34/514/85+/120+ (VERIFIED). Hero Merriweather/Lora, 7-layer mesh, Leaflet map, AOS (OBSERVED; fonts and libs VERIFIED). |

**Audit screenshot IDs for this phase** (audit section L; OBSERVED). Page data below is quoted or summarised from the audit; the screenshots themselves are in the PDF, not in this repository.

| ID | Page | URL | Keep (audit) | Design direction (audit) | Backlog |
|---|---|---|---|---|---|
| G01 | Beranda publik | / | Peta cakupan, pemisahan audiens mahasiswa vs instansi, footer gelap yang sederhana. | Beranda menjadi halaman pendek tiga layar: (1) hero tipografis + CTA daftar, (2) | J9, J15, J20, J23, J7 (luapan 390 px) |

## C. Existing implementation

- [VERIFIED live] `<title>` 'Laravel - Platform Digital untuk Kuliah Kerja Nyata Berkelanjutan'; `<html lang="en">`; one h1 'Platform Digital untuk Kuliah Kerja Nyata'; h2s: 'Wujudkan Dampak Nyata Lewat KKN', 'Rekrut Talenta Terbaik untuk Proyek Anda', 'Kenapa Memilih Student Go?', 'Jangkauan di Seluruh Indonesia'.
- [VERIFIED live] Stat block 34 Provinsi, 514 Kabupaten/Kota, 85+ Instansi Mitra, 120+ Proyek Selesai renders even while the database is unreachable, so it is hard-coded text. 14 elements carry `data-aos`. Primary CTAs 'Login Sekarang' and 'Daftar Sekarang'. Public nav: About Us, Contact, Login, Register.
- [VERIFIED live] Fonts loaded on `/`: Inter 400/500/600/700/900, Outfit 600/700, Lora 400, Merriweather 700. Libraries: Alpine, Leaflet, AOS on the window object; two inline `<style>` blocks; 17 images, none broken at 1525 px.
- [OBSERVED] Hero stacks a photo, seven radial-gradient blobs, a 60/75 px Merriweather h1 and Lora paragraph; the page scrolls sideways at 390 px (455 px wide) and at 768 px; page height about 4,800 px; generic SVG mock-UI and flat-building illustrations; Leaflet pins cluster in Sumatra-Java and are not clickable.
- [OBSERVED] Real data: 77 problems, 79 institutions, 700 students (audit); the page's figures contradict them.

**Repository facts for this phase: UNKNOWN until Stage One.** Locate and record in `DISCOVERY_REPORT.md` section 12 before editing: the home view and controller (does it query the DB? it rendered while the DB was down, so probably not), the Leaflet script and its marker data source, AOS initialisation, inline `<style>` blocks, image paths.

## D. Problems found (audit findings)

| Backlog ID | Finding | Evidence |
|---|---|---|
| J9 (P1) | Marketing metrics are fabricated or unsourced (34/514/85+/120+ hard-coded; 'Proyek Selesai' contradicts data). | VERIFIED hard-coded; OBSERVED contradiction |
| J15 (P2) | Merriweather/Lora hero fonts appear nowhere else; four families in three stacks. | VERIFIED fonts |
| J20 (P2) | Tab title 'Laravel - ...', section 'Kenapa Memilih Student Go?', footer privacy link '#'. | VERIFIED |
| J23 (P2) | AOS fade effects without reduced-motion support. | OBSERVED |
| J7 (P1) | Horizontal scroll at 390 px (455) and 768 px caused by AOS `fade-left` elements. | OBSERVED (iframe measurement) |
| H | Template patterns: mesh gradient, glass pills, three-feature row, generic SVG mock-UI, big quote with no information. | OBSERVED |
| L (G01) | Leaflet map is the only distinctive visual but pins are not clickable. | OBSERVED |

## E. Approved design direction

**Approved by the user (binding)**

- Hybrid direction (AD-04): Ruang Kerja workbench foundation; Catatan Lapangan editorial layer only where the homepage has a proof band and a story section.
- Principles (AD-05) and functional preservation (AD-06). Tokens, fonts and components below come from the asset package and are PROPOSED until D-17 is approved; existing assets are reconciled under AD-09 (section H); D-16 records that reference documents B and C were not found.

**Proposed for this phase** (audit sections I and L, adapted; status PROPOSED until the user approves the gating decisions)

- Three screens (audit L1): (1) typographic hero with one primary and one secondary CTA, (2) live proof: 3-5 latest open problems as list rows and the coverage map, (3) per-role explanation in two text columns with product visuals. One type family; title 'Student Go'.
- Catatan Lapangan layer (approved selective): one or two framed documentary photographs (existing field photographs first, after D-30; catalogue `homepage/` only for a slot nothing existing fills) with caption and coordinate label, a contour or survey-mark motif in the hero margin, serif only for pull sentences. Photography is never a background.
- Map: keep Leaflet. Optionally make pins link to the filtered problem list for a province (feature needs a route that exists today: `/student/browse-problems` with a filter, which requires login, so guests would hit login; decide in D-26 before wiring).
- Numbers: only live counts with honest labels ('77 masalah dipublikasikan', '79 instansi'); if the query is not available to guests or the DB is down, omit the block instead of showing zero.

**Guardrails that bind this phase** (audit section H, numbered as in `EXECUTION_ROADMAP.md`): 1, 2, 3, 5, 6, 7, 8, 9, 10.

## F. Implementation tasks (do in order)

4.1. Gate check: D-01 (name), D-19 (photo review done and approved for homepage use), D-20 (AOS removal), D-23 (who writes copy). If copy is not supplied or approved, draft Indonesian copy only from verifiable product facts, mark it DRAFT and ask for approval.

4.2. Read the home view/controller; record where the stats, the map data and the CTA targets come from. Decide with the user whether guests may see live counts (privacy and load considerations).

4.3. Build the public layout page: hero (h1, one-sentence description, 'Daftar' primary, 'Masuk' secondary), keeping the h1's meaning ('Platform Digital untuk Kuliah Kerja Nyata').

4.4. Proof section: query the latest open problems (existing model/query; no new endpoint if avoidable) and render list rows (title, institution, location, deadline) using the Phase 3 list component; link each row as today.

4.5. Coverage map: keep Leaflet 1.9.4 and OSM tiles; restyle the container and pins using `map-pin.svg` / `map-pin-selected.svg`; keep existing marker data; ensure keyboard-focusable controls and visible attribution; do not break zoom controls.

4.6. Audience sections: write two text columns (Mahasiswa / Instansi and, if the company role is promoted today, Perusahaan) with the matching workflow SVG (`student-journey.svg`, `institution-journey.svg`, `company-journey.svg`). Real product screenshots can replace them in Phase 28 once designed pages exist; do not fake a UI.

4.7. Remove: AOS script/CSS and `data-aos` attributes, seven-layer gradient, glass pills, 'Kenapa Memilih Student Go?', quote block, hard-coded stat block, Merriweather/Lora, unused illustrations.

4.8. Editorial layer: place up to two photographs with caption, location (for example a `sg-coord` label) and Indonesian alt text: first from the existing `public/` field photographs (WebP derivatives per D-34, originals untouched, rights confirmed per D-30), and only if a slot stays empty from the catalogue (D-33; alt text and credit from `credits.json`). Add a contour/survey-mark motif sparingly. Reject any photo whose rights or consent status is unclear. Stop referencing `hero-background.jpg` from the homepage template (usage Deprecated; the file stays).

4.9. Measure at 360/390/768/1024/1366/1440 px: no horizontal scroll (scrollWidth <= innerWidth), hero h1 within 160 px of the top of the first screen on mobile, LCP element is text or a single small image.

4.10. Verify keyboard order: skip link, nav, hero CTAs, list rows, map controls, footer. Verify `prefers-reduced-motion` has no motion at all on this page.

4.11. Update the tracker and report with before/after screenshots (G01) and page weight.

## G. Components

| Component | Source | Notes |
|---|---|---|
| Public layout, nav, footer | Phase 3 | Reused. |
| Typographic hero | page-specific using `.sg-container`, `.sg-btn` | No photo background. |
| Record list rows | Phase 3 list component | 3-5 latest open problems. |
| Coverage map container | page-specific + package pins | Leaflet kept. |
| Framed figure with caption and coordinate label | package `.sg-figure`, `.sg-coord` | Catatan Lapangan layer. |
| Workflow diagram figure | package workflow SVGs | Alt text describes the steps. |

## H. Assets (Source A existing `public/`, Source B custom package, Source C usage)

> Policy AD-09. Three sources, none disposable: **A** existing assets in `public/`, **B** the custom package `student-go-assets/`, **C** `resources/` (shows what is really used). Every asset has one decision: Retain, Reuse, Adapt, Replace or Deprecate. Originals are never deleted, overwritten, moved or renamed by this phase; copying anything into a production directory happens only if a task above says so. Full inventory, rights and integration map: `student-go-docs/design-execution/ASSET_INVENTORY.md`. Evidence labels as in `DISCOVERY_REPORT.md` section 1.

### H.1 Source A: existing application assets that matter in this phase

| Asset | Decision | What happens in this phase | Evidence |
|---|---|---|---|
| `hero-background.jpg` | Deprecate (usage) / Reuse (optional) | Leaves the hero. May return as one framed figure if rights are confirmed (D-30). | VERIFIED |
| `about.jpg` | Adapt / Reuse (optional) | Candidate framed landscape for the homepage band; needs a derivative (original is 3.6 MB). | VERIFIED |
| `mahasiswa-about.jpeg`, `handprints-about.jpeg`, `jaket-about.jpeg`, `dashboard-student2.jpeg`, `network-student.JPG`, `projects-student.jpeg`, `repo-student.jpeg` | Reuse (candidates) | At most two framed, captioned documentary figures on the homepage, chosen after rights and consent are confirmed (D-30). | VERIFIED (about photos) / INFERRED (others) |
| `student-go-logo.png` | Retain | Navigation and footer logo as decided in Phase 3. | VERIFIED |

### H.2 Source B: custom package assets available for this phase

**Original SVG, brand, fonts, icons and CSS (made for this project; in the package; licences in `ASSET_INVENTORY.md` section 2)**

- `workflows/student-journey.svg`, `institution-journey.svg`, `company-journey.svg` (and vertical variants for mobile).
- `motifs/contour-field.svg`, `motifs/survey-marks.svg`, `motifs/map-pin*.svg`.
- `brand/student-go-logo*.svg`.
- Optional: `geographic/indonesia-regions.svg` as a static fallback/legend beside the Leaflet map.

**Catalogue photographs (Source B: licence-checked, NOT downloaded; deferred by D-33; used only when no Source A photograph fills the slot)**

- `public/assets/photography/homepage/*` (6 candidates): fallback only for a slot the existing field photographs cannot fill (D-33). At most two photographs in total on the page, framed with caption and credit; none as background. Subject to D-19 review (minors and consent).

### H.3 Reconciliation for this phase

Overlap 3 (homepage imagery) and 12 (generic illustrations). Existing real field photographs come first; the old inline SVG mock UI is retired by template change.

Overlap comparisons that touch this phase are in `ASSET_INVENTORY.md` section 11; where a Source A asset and a Source B asset serve the same purpose, that table gives the starting recommendation and this phase re-checks it against the real page.

### H.4 Native UI (no asset; plain HTML/CSS)

- Leaflet map, text, list rows, borders and dividers.

### H.5 Do not touch in this phase

- Student profile photographs and uploads, institution/company logos, verification documents: never moved or re-encoded; never shown in a new public place (D-04, D-31).
- All originals in `public/` stay on disk, including the hero background that leaves the page.

## I. Dependencies

- **Phases:** hard — Phase 3 (Shared Shell & Components); soft — Phase 1 (Critical Fixes & Data Integrity).
- **Decisions:** D-01 (Product name, wordmark and assistant name), D-19 (Documentary photography), D-20 (Delivery of JS/CSS libraries), D-23 (Content ownership for new or rewritten copy), D-30 (Provenance and consent for existing photographs in `public/`), D-33 (Whether to download the 60 catalogue photographs at all), D-34 (Photograph derivatives: format, sizes and location). A gating decision that is still OPEN in `DECISIONS_AND_OPEN_QUESTIONS.md` means the dependent tasks stop and the agent asks the user; it does not guess.
- **Packages:** Leaflet stays. AOS removed. No new package.
- **Data / environment:** Optional live counts and latest problems (needs a reachable database or the block is omitted).

## J. Functionality preservation

The global rules in `CLAUDE.md` section 7 apply in full. For this phase specifically:

- Public URLs and CTA destinations (`/login`, `/register`) unchanged.
- The Leaflet map still renders interactive tiles, zoom and markers from the same data source.
- Guest access: page works without authentication and without the database if the counts are omitted gracefully.
- No claim or number is introduced that is not backed by data or by a user-approved source.

## K. Testing plan

**Standard checks (every phase)**

- Viewport matrix: 360, 390, 768, 1024, 1366 and 1440 px wide. At each width `document.documentElement.scrollWidth <= window.innerWidth` (no page-level horizontal scroll). At most one horizontally scrolling container per table.
- State matrix for every changed page: default, empty, long content (76-character institution name, 65-character document title, 51-character email), missing media, validation error, and the role/permission variants that exist for that page.
- Keyboard: Tab through the whole page; focus is always visible; no keyboard trap; Esc closes overlays; exactly one <h1>; `<html lang="id">`.
- Contrast: text pairs >= 4.5:1, large text and UI parts >= 3:1 (use the measured table in docs/DESIGN_SYSTEM.md; re-measure any new pair). Check with `prefers-reduced-motion: reduce` emulated.
- Functionality: compare against the Phase 0 baseline (routes, form field names, query parameters, redirects). Submit forms only against the local seeded test database with test accounts found in the project's seeders; never against production.
- Console and network: no JavaScript errors; no request to a CDN that this phase removed; no 404 for a new asset.
- Automated: run the project's own test suite (command confirmed in Stage One; see CLAUDE.md section 4) and report pass/fail counts before and after.
- Visual: take screenshots of every changed page at the matrix widths and store them in the phase report; compare against the Phase 0 baseline and the audit's screenshot IDs.
- Assets: `git status` / `git diff --name-status` shows no deleted, renamed or overwritten file under `public/`; every asset the changed pages reference returns 200; nothing is copied into a production asset directory unless this phase's section H authorises it; certificate output (if the phase touches fonts, layout or the asset pipeline) is byte-comparable or visually identical to the Phase 0 baseline.

**Phase-specific checks**

- Guest view at all six widths; scrollWidth <= innerWidth at each.
- Network panel: no AOS, Boxicons, Font Awesome, Merriweather/Lora requests.
- Map: pan, zoom, marker popup (if any) still work; attribution visible; keyboard operable.
- With the database unreachable, the page still renders without errors or zero-filled counters.
- Tab order and screen-reader landmark check.
- Reduced-motion emulation shows no animation.

*Test commands are not listed here because none could be verified without the repository. `CLAUDE.md` section 4 lists candidate commands that Stage One must confirm.*

## L. Acceptance criteria

The phase is COMPLETE only if every box can be checked with evidence in the phase report. Otherwise the status is PARTIAL (say which boxes failed) or BLOCKED (say what unblocks it).

- [ ] Page `<title>` is 'Student Go' based and `<html lang="id">`.
- [ ] No hard-coded marketing metric remains; every number on the page comes from a query, or no number is shown.
- [ ] No horizontal page scroll at 360, 390, 768, 1024, 1366, 1440 px.
- [ ] The hero has no photo background, gradient blobs, glass pills, serif hero font, or AOS; the h1 and primary CTA are visible within the first 160 px at 390 px.
- [ ] Leaflet interactive map is present and functional; pins use the new marker; OSM attribution is visible.
- [ ] 'Kenapa Memilih Student Go?' feature row and the quote block are gone; audience sections describe real product flows.
- [ ] At most two photographs are used, each framed with caption and credit, each with confirmed rights (D-30) and approved placement (D-19); no photo is a background; `hero-background.jpg` and every other original is still on disk.
- [ ] No request to AOS, Boxicons, Font Awesome or the old Google Fonts families on this page.
- [ ] Page weight is lower than the baseline (report numbers).

## M. Risks and blockers

| Risk or blocker | Mitigation / what unblocks it | Blocks the phase? |
|---|---|---|
| Live proof needs data guests cannot or should not see | Omit the block; ask which counts may be public. | No |
| Copy not supplied (D-23) | Draft from product facts, mark DRAFT, ask approval; never invent testimonials or metrics. | Partially |
| Workflow SVG vs real screens | Use SVG now; real screenshots are a Phase 28 task. | No |
| Pins to filtered lists need login | Do not wire; decide in D-26. | No |

## N. Expected deliverables

- Rewritten home view and its small partials; removal of AOS usage and unused assets from this page.
- Before/after screenshots at six widths and a page-weight comparison.
- `student-go-docs/design-execution/PHASE_TRACKER.md`: set Phase 4 to COMPLETE, PARTIAL or BLOCKED with date, branch/commit (if the user asked for one) and a one-line note.
- A phase report in the chat: what changed (files), what was verified (with the evidence), what failed or was skipped and why, decisions needed from the user, and the exact next phase the roadmap allows. Then stop and wait; do not start another phase.
