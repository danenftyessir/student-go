# PHASE 02 — Brand Identity & Design System

> Source of truth for status is `student-go-docs/design-execution/PHASE_TRACKER.md`. This file is the work order for exactly one phase. Read `CLAUDE.md` and `student-go-docs/design-execution/EXECUTION_ROADMAP.md` first, execute only this phase, update the tracker, report, then stop.
> Evidence labels: VERIFIED / OBSERVED / INFERRED / UNKNOWN / BLOCKED (definitions in `DISCOVERY_REPORT.md` section 1).

| Field | Value |
|---|---|
| Phase | 2 — Brand Identity & Design System |
| Group | Foundation |
| Hard dependencies (must be COMPLETE) | Phase 0 (Readiness & Baseline) |
| Soft dependencies (recommended first) | Phase 1 (Critical Fixes & Data Integrity) |
| Decisions that gate this phase | D-01 Product name, wordmark and assistant name, D-16 Missing reference documents B and C, D-17 Approve the asset package as the visual foundation, D-18 Brand mark, D-19 Documentary photography, D-20 Delivery of JS/CSS libraries, D-29 Where the asset package CSS lives and how it is wired, D-32 Certificate assets (templates, Pinyon Script, Poppins), D-35 Embedded C2PA metadata in the package's SVG and PNG files |
| Effort estimate (planning guess, not a commitment) | L |
| Roles touched | All |
| Audit references | Audit C, H, I, J15, J16, J17, J20, J23, J24; asset package docs/DESIGN_SYSTEM.md, INTEGRATION.md, ASSET_SOURCES.md |
| Tracker status at planning time | NOT_STARTED |

## A. Phase overview

**Goal.** Integrate the approved visual foundation into the app without restyling any page yet: tokens, self-hosted fonts, icon sprite, brand SVGs, `.sg-*` component classes and the photo pipeline. After this phase the design system exists and compiles; pages still look like they did.

**In scope**

- Copy the custom asset package into the project exactly as mapped in `ASSET_INVENTORY.md` section 10 (`resources/css/student-go/`, `public/assets/{brand,fonts,icons,illustrations}/`, `scripts/`, package docs under `student-go-docs/design-system/`), after D-17, D-29 and D-35 are answered. Additive only: never overwrite an existing file; the existing `public/` assets are not touched.
- Wire the CSS into `resources/css/app.css` after `@import "tailwindcss"` and confirm Tailwind v4 compiles (`@theme` tokens, `@source`).
- Reconcile the package with documents B and C if the user supplies them (D-16), or record the approved substitution.
- Photography: no download in this phase unless the user chose it in D-33. If they did, run `--dry-run` and then `--only=homepage` as a comparison set, and review it (D-19).
- Write the project's design-system reference (`docs/DESIGN_SYSTEM.md` exists in the package) and the review checklist derived from audit section H.

**Out of scope**

- Restyling any Blade page or removing the CDN libraries (Phase 3 removes them with the shell).
- New npm/composer dependencies. Fonts and icons are local files, not packages.
- Changing any route, controller or data.

**Evidence basis.** The audit (OBSERVED, 2026-10-28, port 8001) plus a read-only look at the running app on 2026-10-30. No repository source was read when this file was written; every statement about templates, controllers, models, middleware and tests is UNKNOWN until Stage One (Phase 0) fills `DISCOVERY_REPORT.md` section 12.

## B. Pages and routes

No route is owned by this phase; it works on shared files and every route (see section A).

## C. Existing implementation

- [VERIFIED live] Tailwind default theme; the page loads Google Fonts twice (Inter, Outfit, Lora, Merriweather families loaded: Inter 400/500/600/700/900, Outfit 600/700, Lora 400, Merriweather 700), plus CDN CSS/JS (Font Awesome 6.5.1, Leaflet 1.9.4, AOS 2.3.1, Boxicons 2.1.4, Alpine 3.13.3).
- [OBSERVED] No design system: Tailwind 4.1.16 default theme, 4 font families in 3 stacks, 6 competing accents, `bg-primary-*` classes that resolve to nothing, 12 text sizes, 7 radius values, 5 shadow levels, gradients on controls (audit C).
- [OBSERVED] Logo `student-go-logo.png` is 1430x566 px and 272 KB rendered at 40 px; favicon.ico is 0 KB.
- [VERIFIED live] `/` has no `<link rel=icon>`.
- [DONE earlier, not yet in the app] `student-go-assets` package: tokens and components CSS (compiles in isolation), 96 SVGs, 71 Lucide icons + sprite, fonts with OFL licences, 60 license-checked photo records, downloader v1.2, style guide, manifest with hashes.

**Repository facts for this phase: UNKNOWN until Stage One.** Locate and record in `DISCOVERY_REPORT.md` section 12 before editing: resources/css/app.css and its `@source`/`@theme` lines; vite.config.js inputs (the missing `resources/css/auth-institution.css` entry, VERIFIED broken on `/forgot-password`); any existing `public/assets`, `public/images`; whether `public/build` is committed; `.gitignore` for `/storage/app/asset-originals/`.

## D. Problems found (audit findings)

| Backlog ID | Finding | Evidence |
|---|---|---|
| J15 (P2) | Typography split across 4 families and 3 stacks. | OBSERVED; fonts VERIFIED |
| J16 (P2) | Six competing accents; undefined `primary` class; difficulty uses status colours. | OBSERVED |
| J17 (P2) | Duplicated components: 3 header kinds, 4 placeholders, 3 uploads, 3 paginators, 4 card anatomies, 3 registration layouts. | OBSERVED |
| J20 (P2) | Naming leftovers (title 'Laravel', logo 'Student Go', default favicon, privacy link '#'). | OBSERVED; titles and '#' VERIFIED |
| J23 (P2) | Decorative motion without reduced-motion support. | OBSERVED |
| J24 (P2) | Unused/heavy assets: Boxicons, Font Awesome, empty JS bundle, 272 KB PNG logo. | OBSERVED; CDN tags VERIFIED |
| J10 / J11 (P1) | Contrast failures (footer 3.67:1, 'Hubungkan' 4.12:1, green numbers 3.08:1) and no `:focus-visible` rules; tokens must make these impossible. | OBSERVED |

## E. Approved design direction

**Approved by the user (binding)**

- Hybrid direction (AD-04): Ruang Kerja workbench foundation; Catatan Lapangan editorial layer only where reading and storytelling matter. This phase uses none of it unless stated below.
- Principles (AD-05) and functional preservation (AD-06). Tokens, fonts and components below come from the asset package and are PROPOSED until D-17 is approved; existing assets are reconciled under AD-09 (section H); D-16 records that reference documents B and C were not found.

**Proposed for this phase** (audit sections I and L, adapted; status PROPOSED until the user approves the gating decisions)

- One brand colour `#405080` for primary actions, links, selected nav and focus ring; one re-tuned neutral ramp with named roles; semantic success/warning/danger/info pairs; one status vocabulary; difficulty as a neutral 1-3 step meter; SDG colours only as small swatches; role colour only on the role badge. (PROPOSED, D-17.)
- Plus Jakarta Sans for UI and headings with tabular figures in tables and stats; Source Serif 4 only for long reading (project brief, repository abstract, student story). 9-step type scale, weights 400/500/600 (700 only in marketing), radii 6/8 px, shadow only on overlays, 150 ms motion off under `prefers-reduced-motion`. (PROPOSED.)
- Lucide icons at 1.5 px stroke via one SVG sprite; one meaning, one icon. Original SVG only for identity: wordmark, Indonesia map, SDG mark, five empty-state spots, workflow diagrams.
- Review checklist: the ten guardrails from audit H become pull-request rules (see `EXECUTION_ROADMAP.md`).

**Guardrails that bind this phase** (audit section H, numbered as in `EXECUTION_ROADMAP.md`): 1, 2, 3, 4, 5, 6, 7, 8, 9, 10.

## F. Implementation tasks (do in order)

2.1. Gate check: D-01 (name), D-16 (documents B/C), D-17 (approve foundation), D-18 (brand mark), D-20 (libraries). If D-16 is still OPEN, state in the report that the package was used under the audit-only substitution and ask for approval; do not proceed on the E sections silently.

2.2. Re-verify the package: run the manifest hash check described in its README and confirm all SVGs parse. Compare `docs/DESIGN_SYSTEM.md` with documents B/C if supplied; list differences and ask which wins.

2.3. Create a branch per D-27. Before copying, run `ls` on `public/assets`, `resources/css`, `scripts` and the package-docs target and stop on any name that already exists. Copy `public/assets/{brand,fonts,icons,illustrations}`, `resources/css/student-go`, `scripts/fetch-photos.*`, the package docs (to `student-go-docs/design-system/`, not an existing `docs/`) and `assets-manifest.json` following INTEGRATION.md section 2 as corrected in `ASSET_INVENTORY.md` section 13 (no logo or photo removal). Apply D-35 on the copy only (strip the embedded C2PA block; record both SHA-256 values); keep the package originals untouched. Add `/storage/app/asset-originals/` to `.gitignore` only if originals will be stored there.

2.4. Wire the CSS: keep the existing `@import "tailwindcss"` first, then import `student-go/fonts.css`, `tokens.css`, `components.css`; keep existing `@source` lines. Run the project's build and confirm it succeeds and that no existing page changes appearance (compare against baseline screenshots).

2.5. Fonts: serve the WOFF2 files from `public/assets/fonts`; confirm `font-display: swap`, correct MIME type and cache headers on the local server; do not remove the old Google Fonts links yet (Phase 3).

2.6. Icons: expose the sprite and `icons.json` as documented; add one tiny helper or Blade include for `<svg><use>` following the Blade conventions found in Stage One.

2.7. Brand: place the wordmark/symbol/favicon files; do not swap them into layouts yet (Phase 3 owns the shell). Verify `favicon.svg`, `favicon.ico` and `apple-touch-icon.png` open correctly.

2.8. Photos: only if D-33 chose (b) or (c): run `php scripts/fetch-photos.php --dry-run`, then the download on a machine with normal TLS (INTEGRATION.md has the Windows certificate fix). Verify each file is a valid WebP and record sizes. Otherwise record 'catalogue photographs deferred (D-33)' in the report.

2.9. Human review of any photographs downloaded (D-19): view every photo that shows people; flag minors and anyone not clearly appropriate; record decisions in the catalogue `status` and `credits.json`; hold back flagged photos. Separately record the user's rights and consent answers for the existing `public/` photographs in `ASSET_INVENTORY.md` section 1 (D-30). Do not place any photo in a page in this phase.

2.10. Contrast and token check: re-run `contrast.py` or equivalent for every text/background pair in `tokens.css`; confirm text >= 4.5:1, large text/UI >= 3:1, input borders >= 3:1.

2.11. Open `docs/styleguide.html` over a local server and compare with the package screenshots at 1440, 1366, 1024, 768, 390, 360 px.

2.12. Update the tracker and report: files added, build result, photo count/size, review decisions, open points.

## G. Components

| Component | Source | Notes |
|---|---|---|
| `.sg-btn` (+ primary, secondary, tertiary, danger, danger-solid, lg) | asset package components.css | Foundation only; adopted per page in later phases. |
| `.sg-input`, `.sg-field`, `.sg-label`, `.sg-help`, `.sg-error` | package | Form controls and messages. |
| `.sg-page-header` (+ back, desc, actions), `.sg-container`, `.sg-panel`, `.sg-divider-route` | package | Layout primitives. |
| `.sg-table` / `.sg-table-wrap` / `.sg-table__primary` / `.sg-table__meta`, `.sg-list` | package | Record patterns. |
| `.sg-status`, `.sg-chip` (+ swatch), `.sg-meter` (+ bars), `.sg-summary` | package | Status vocabulary, SDG swatch, difficulty meter, inline summary. |
| `.sg-stepper`, `.sg-dropzone`, `.sg-file-chip`, `.sg-empty` | package | One stepper, one upload component, empty state. |
| `.sg-reading`, `.sg-prose`, `.sg-figure`, `.sg-coord`, `.sg-marker`, `.sg-survey-marks`, `.sg-media`, `.sg-contour` | package | Catatan Lapangan layer. |
| `.sg-skip`, `.sg-overlay`, `.sg-icon` (+ sm, lg) | package | Accessibility and overlays. |

## H. Assets (Source A existing `public/`, Source B custom package, Source C usage)

> Policy AD-09. Three sources, none disposable: **A** existing assets in `public/`, **B** the custom package `student-go-assets/`, **C** `resources/` (shows what is really used). Every asset has one decision: Retain, Reuse, Adapt, Replace or Deprecate. Originals are never deleted, overwritten, moved or renamed by this phase; copying anything into a production directory happens only if a task above says so. Full inventory, rights and integration map: `student-go-docs/design-execution/ASSET_INVENTORY.md`. Evidence labels as in `DISCOVERY_REPORT.md` section 1.

### H.1 Source A: existing application assets that matter in this phase

| Asset | Decision | What happens in this phase | Evidence |
|---|---|---|---|
| Google Fonts (Inter, Outfit, Merriweather, Lora), Font Awesome, Boxicons, AOS (CDN libraries) | Deprecate | Not files in `public/`. Stop loading them only in Phase 3 when the layouts are rebuilt (D-20); this phase only prepares the replacement. | VERIFIED on `/` |
| Certificate fonts Pinyon Script and Poppins | Retain | Not part of `fonts.css`. If they are served from `public/fonts` or `storage`, they stay; if a certificate view loads them from Google Fonts, that link stays when the site-wide links are removed. | UNKNOWN / BLOCKED |
| `student-go-logo.png`, `favicon.ico` and all photographs | Retain | Untouched in this phase. | VERIFIED |

### H.2 Source B: custom package assets available for this phase

**Original SVG, brand, fonts, icons and CSS (made for this project; in the package; licences in `ASSET_INVENTORY.md` section 2)**

- `public/assets/brand/`: student-go-logo.svg, student-go-logo-on-dark.svg, student-go-symbol.svg, student-go-symbol-on-dark.svg, favicon.svg, favicon.ico, apple-touch-icon.png.
- `public/assets/illustrations/empty-states/`: no-applications, no-documents, no-notifications, no-saved-projects, no-search-results (.svg).
- `public/assets/illustrations/workflows/`: student-journey, institution-journey, company-journey, project-status (horizontal and vertical).
- `public/assets/illustrations/geographic/`: indonesia-regions.svg (7 island groups), indonesia-outline.svg (Natural Earth, public domain).
- `public/assets/illustrations/motifs/`: contour-field, survey-marks, map-pin, map-pin-selected.
- `public/assets/icons/ui/`: 71 Lucide icons (ISC) + sprite.svg + icons.json + LICENSE-lucide.txt.
- `public/assets/fonts/`: Plus Jakarta Sans (latin, latin-ext, variable weight) and Source Serif 4 (latin) WOFF2 with OFL texts.

**Notes for this phase**

- Copy proposal (not authorised until this phase starts and D-17, D-29 are answered): `public/assets/{brand,fonts,icons,illustrations}/` and `resources/css/student-go/` exactly as in ASSET_INVENTORY.md section 10. The collision check found no existing `/assets/*` files at the proposed paths.
- Strip or keep the embedded C2PA `<metadata>` block in the 96 SVGs and 7 PNGs (about 7.7 KB per SVG): decide with the user (D-35); do it on the copy, never on the package originals.

**Catalogue photographs (Source B: licence-checked, NOT downloaded; deferred by D-33; used only when no Source A photograph fills the slot)**

- 60 photographs, 6 homepage, 4 about, 50 library (pertanian 13, pesisir 12, pasar 9, pendidikan 8, desa 7, komunitas 1), Unsplash License / Pexels License. Catalogue notes 5 photos with minors and 13 with unknown people status: review before any use (D-19). Not downloaded; deferred by D-33.

### H.3 Reconciliation for this phase

The package supplies tokens, fonts, icons, brand, illustrations. Source A supplies nothing here. Typography: Plus Jakarta Sans/Source Serif 4 for the website; certificate fonts untouched.

Overlap comparisons that touch this phase are in `ASSET_INVENTORY.md` section 11; where a Source A asset and a Source B asset serve the same purpose, that table gives the starting recommendation and this phase re-checks it against the real page.

### H.4 Native UI (no asset; plain HTML/CSS)

- No raster UI chrome; plain CSS.

### H.5 Do not touch in this phase

- Certificate templates and the Pinyon Script and Poppins fonts (and whatever generates certificates): UNKNOWN location (D-32). Do not remove, rename, re-encode or restyle them, and do not remove a Google Fonts link that a certificate view depends on.
- Every existing `public/` file: the package is added beside them, not over them (no `public/assets/` file may overwrite anything).
- `public/build/` (generated).

## I. Dependencies

- **Phases:** hard — Phase 0 (Readiness & Baseline); soft — Phase 1 (Critical Fixes & Data Integrity).
- **Decisions:** D-01 (Product name, wordmark and assistant name), D-16 (Missing reference documents B and C), D-17 (Approve the asset package as the visual foundation), D-18 (Brand mark), D-19 (Documentary photography), D-20 (Delivery of JS/CSS libraries), D-29 (Where the asset package CSS lives and how it is wired), D-32 (Certificate assets (templates, Pinyon Script, Poppins)), D-35 (Embedded C2PA metadata in the package's SVG and PNG files). A gating decision that is still OPEN in `DECISIONS_AND_OPEN_QUESTIONS.md` means the dependent tasks stop and the agent asks the user; it does not guess.
- **Packages:** None (fonts and icons are static files). PHP CLI with curl or system curl is needed only if D-33 chooses to run the downloader.
- **Data / environment:** None; static assets only.

## J. Functionality preservation

The global rules in `CLAUDE.md` section 7 apply in full. For this phase specifically:

- No Blade file changes in this phase beyond optional include helpers; no page may look different after the build.
- Keep old font links, CDN tags and CSS in place until Phase 3 removes them.
- Do not rename existing files in `public/` that pages reference.

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

- Build passes; generated CSS size reported before/after.
- Baseline-vs-after screenshots of five pages (G01, G04, S01, I01, C01) are pixel-similar within anti-aliasing noise.
- Each font file loads (HTTP 200, `font/woff2`) and renders the Indonesian test strings (`Kabupaten Penajam Paser Utara`, `Dinas Sosial Pemberdayaan Perempuan Dan Perlindungan Anak Kabupaten Sidoarjo`).
- Manifest hash verification passes; SVG well-formedness check passes.
- Contrast table has no failing pair.

*Test commands are not listed here because none could be verified without the repository. `CLAUDE.md` section 4 lists candidate commands that Stage One must confirm.*

## L. Acceptance criteria

The phase is COMPLETE only if every box can be checked with evidence in the phase report. Otherwise the status is PARTIAL (say which boxes failed) or BLOCKED (say what unblocks it).

- [ ] The project builds with the three Student Go CSS files imported and no existing page changes appearance (screenshot comparison on the five named pages).
- [ ] All assets listed in `assets-manifest.json` exist at their paths with matching SHA-256, and every SVG is well-formed.
- [ ] Fonts and icons are served locally; the style guide at `docs/styleguide.html` renders correctly at 1440 and 390 px.
- [ ] Every token text/background pair meets the stated ratio (>= 4.5:1 text, >= 3:1 large text/UI) with a recorded table.
- [ ] Either the downloader was deliberately not run (D-33 recorded), or it produced the expected WebP files (or the report lists which failed and why) and every photo showing people has a recorded review decision. No existing `public/` file was deleted, overwritten, moved or renamed (`git diff --name-status` shows only additions); the copied package files match the manifest hashes (or the recorded post-strip hashes).
- [ ] No new composer or npm dependency was added; no route, controller or view logic changed.
- [ ] D-16 and D-17 are recorded as answered (or the report states the phase ran under the audit-only substitution awaiting approval).

## M. Risks and blockers

| Risk or blocker | Mitigation / what unblocks it | Blocks the phase? |
|---|---|---|
| Reference documents B and C may conflict with the package | Ask the user (D-16) before locking tokens. | Yes for token lock |
| Tailwind v4 `@theme` conflicts with existing config | Build and diff; keep tokens in one file; fall back to scoped variables if needed. | No |
| Photo downloads fail on the user's network (TLS) | Use downloader v1.2 fallbacks and the php.ini fix in INTEGRATION.md. | No |
| Photos with minors or unclear consent | Hold back; D-19 review. | No |
| Fonts look different from the style guide in the real build | Compare screenshots; check `font-display` and MIME. | No |

## N. Expected deliverables

- Added: `resources/css/student-go/*`, `public/assets/**` (brand, fonts, icons, illustrations), `scripts/fetch-photos.*`, and the package docs and manifest at the locations in `ASSET_INVENTORY.md` section 10 (`student-go-docs/design-system/`: `DESIGN_SYSTEM.md`, `ASSET_SOURCES.md`, `INTEGRATION.md`, `photo-catalog.json`, `styleguide.*`; `assets-manifest.json`).
- Modified: `resources/css/app.css` (imports only), `.gitignore`.
- Source A rights table filled in `ASSET_INVENTORY.md` (D-30), and, only if photographs were downloaded, the review log and `credits.json`.
- `student-go-docs/design-execution/PHASE_TRACKER.md`: set Phase 2 to COMPLETE, PARTIAL or BLOCKED with date, branch/commit (if the user asked for one) and a one-line note.
- A phase report in the chat: what changed (files), what was verified (with the evidence), what failed or was skipped and why, decisions needed from the user, and the exact next phase the roadmap allows. Then stop and wait; do not start another phase.
