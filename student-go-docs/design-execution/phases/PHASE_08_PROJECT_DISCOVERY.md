# PHASE 08 — Student Project Discovery

> Source of truth for status is `student-go-docs/design-execution/PHASE_TRACKER.md`. This file is the work order for exactly one phase. Read `CLAUDE.md` and `student-go-docs/design-execution/EXECUTION_ROADMAP.md` first, execute only this phase, update the tracker, report, then stop.
> Evidence labels: VERIFIED / OBSERVED / INFERRED / UNKNOWN / BLOCKED (definitions in `DISCOVERY_REPORT.md` section 1).

| Field | Value |
|---|---|
| Phase | 8 — Student Project Discovery |
| Group | Student role |
| Hard dependencies (must be COMPLETE) | Phase 1 (Critical Fixes & Data Integrity), Phase 3 (Shared Shell & Components) |
| Soft dependencies (recommended first) | Phase 2 (Brand Identity & Design System) |
| Decisions that gate this phase | D-02 Language policy and copy deck, D-05 Stable ordering and count definitions, D-14 Problem gallery images, D-12 A working database for baseline and QA |
| Effort estimate (planning guess, not a commitment) | L |
| Roles touched | Student |
| Audit references | S02, S25; J2, J4, J6, J12, J18, J19; audit D4, E |
| Tracker status at planning time | NOT_STARTED |

## A. Phase overview

**Goal.** Make `/student/browse-problems` a dense, sortable list by default (card gallery and the Leaflet map as optional views), with one search, filters that behave, honest counts and no repeated records.

**In scope**

- List view (default), gallery view (optional), map view (kept).
- Filters: province, SDG, difficulty, duration; single search; active-filter chips; empty states (no results, ?page=99).
- Row metadata: institution · location · SDG · quota · deadline.

**Out of scope**

- Project detail (Phase 9).
- Ordering/count backend (Phase 1).
- Wishlist page (Phase 11).

**Evidence basis.** The audit (OBSERVED, 2026-10-28, port 8001) plus a read-only look at the running app on 2026-10-30. No repository source was read when this file was written; every statement about templates, controllers, models, middleware and tests is UNKNOWN until Stage One (Phase 0) fills `DISCOVERY_REPORT.md` section 12.

## B. Pages and routes

| Route pattern | Role | Audit status | Evidence | Notes |
|---|---|---|---|---|
| /student/browse-problems | Student | Inspected | OBSERVED | ?page=1-7, ?search=, ?page=99. |

**Audit screenshot IDs for this phase** (audit section L; OBSERVED). Page data below is quoted or summarised from the audit; the screenshots themselves are in the PDF, not in this repository.

| ID | Page | URL | Keep (audit) | Design direction (audit) | Backlog |
|---|---|---|---|---|---|
| S02 | Jelajahi Proyek KKN (daftar) | /student/browse-problems | Filter yang sesuai domain (provinsi, SDG, tingkat kesulitan, durasi), toggle grid/list/peta, empty state. | Tata letak dua kolom: filter di kiri (ringkas, bisa diciutkan), daftar baris di kanan dengan header kolom yang bisa diurutkan, toggle Peta sebagai tampilan alternatif. Detail metadata terbaca tanpa membuka kartu. | J2, J4, J6, J12, J18, J19 |
| S25 | Jelajahi Proyek — hasil pencarian kosong | /student/browse-problems?search=zzzzqqq | Empty state dengan satu CTA yang jelas. | Empty state bersama dengan varian “tanpa hasil pencarian”, menampilkan filter aktif yang bisa dihapus. | J4, J17 |

## C. Existing implementation

- [OBSERVED] 77 problems; 7 pages of 12; grid/list/map toggle; two search fields (top bar and sidebar 'Cari Problem') plus a separate 'Terapkan Filter' button; every card starts with a 150 px blue icon tile (about 40% of card height) because no problem has a loaded cover image; institution names truncated at about 25 characters; SDG chip 'Pekerjaan Layak Dan Pertumbuhan' clipped; difficulty shown with status colours ('Advanced' in red).
- [OBSERVED] Counts contradict (sidebar 'Total Proyek 77', footer 'Menampilkan 73 dari 77', second round 'Menampilkan 1 dari 77 proyek'); only 62 unique records reachable; 73 gradient classes on this page alone.
- [OBSERVED] Search with no match and `?page=99` render a designed empty state (kept).
- [BLOCKED] Real gallery images (310 on remote Supabase storage) never loaded locally.

**Repository facts for this phase: UNKNOWN until Stage One.** Locate and record in `DISCOVERY_REPORT.md` section 12 before editing: the browse-problems controller/query, filter parameter names, view modes and their JS, the Leaflet map view data, the problem model's SDG and difficulty fields.

## D. Problems found (audit findings)

| Backlog ID | Finding | Evidence |
|---|---|---|
| J2 / J4 (P0) | Repeated cards and wrong counts (Phase 1 fixes; verify here). | OBSERVED |
| J18 (P2) | Card grid where users compare deadlines, location and quota. | OBSERVED |
| J12 (P1) | Media placeholder consumes space; no fallback. | OBSERVED |
| J19 (P2) | Truncated institution names and SDG chips. | OBSERVED |
| J6 (P1) | Photo hero. | OBSERVED |
| D4 | Duplicate search; separate Apply button; difficulty in status colours. | OBSERVED |

## E. Approved design direction

**Approved by the user (binding)**

- Hybrid direction (AD-04): Ruang Kerja workbench foundation; Catatan Lapangan editorial layer only where reading and storytelling matter. This phase uses none of it unless stated below.
- Principles (AD-05) and functional preservation (AD-06). Tokens, fonts and components below come from the asset package and are PROPOSED until D-17 is approved; existing assets are reconciled under AD-09 (section H); D-16 records that reference documents B and C were not found.

**Proposed for this phase** (audit sections I and L, adapted; status PROPOSED until the user approves the gating decisions)

- Two columns: compact collapsible filters at left (drawer on mobile with sticky 'Terapkan'), sortable list at right with column headers (Judul · Instansi · Lokasi · SDG · Kuota · Tenggat); Map as an alternative view; gallery optional. Difficulty as a neutral meter 'Pemula/Menengah/Lanjut'; SDG as swatch plus name; full institution name in the detail row. Single search. Metadata readable without opening the row.

**Guardrails that bind this phase** (audit section H, numbered as in `EXECUTION_ROADMAP.md`): 1, 2, 3, 4, 5, 6, 7, 9, 10.

## F. Implementation tasks (do in order)

8.1. Gate check: Phase 1 COMPLETE (ordering and counts), D-05 (sort options), D-14 (images), D-02 (copy).

8.2. Read the controller to list every accepted query parameter; keep them all.

8.3. Build the page header, a single search, the filter panel and the sortable list. Sorting only through parameters the backend already supports; additional sorts are a backend change and need approval (D-26).

8.4. List rows: title (linked), institution (full name, wraps), location (`Kota, Provinsi` with a note when the regency is a placeholder per D-15), SDG swatch + name, quota, deadline, difficulty meter, status badge if the student already applied.

8.5. Gallery view: card per problem without placeholder tile; show media only if present; fallback to text-only card.

8.6. Map view: keep Leaflet and the existing data; restyle with package pins; keep list/map toggle (segmented control) and its URL state if present.

8.7. Mobile: filters as drawer with 'Terapkan' sticky; table becomes stacked rows below 640 px; one pager.

8.8. Empty states: no results shows active filters as removable chips; `?page=99` shows the existing empty state in the new style.

8.9. Verify with the 77-problem dataset: all IDs reachable by paging; long names do not clip; layouts with 0 results.

8.10. Update the tracker and report with S02/S25 before/after.

## G. Components

| Component | Source | Notes |
|---|---|---|
| Page header + single search | Phase 3 | — |
| Filter bar and mobile drawer | Phase 3 | Sticky 'Terapkan'. |
| Sortable table / divided list | Phase 3 | Stacked rows below 640 px. |
| Segmented view toggle (List / Galeri / Peta) | Phase 3 tabs | — |
| Difficulty meter | package `.sg-meter` | Neutral. |
| SDG chip with swatch | package `.sg-chip` | — |
| Pagination | Phase 3 | Stable. |
| Empty state with filter chips | Phase 3 | — |

## H. Assets (Source A existing `public/`, Source B custom package, Source C usage)

> Policy AD-09. Three sources, none disposable: **A** existing assets in `public/`, **B** the custom package `student-go-assets/`, **C** `resources/` (shows what is really used). Every asset has one decision: Retain, Reuse, Adapt, Replace or Deprecate. Originals are never deleted, overwritten, moved or renamed by this phase; copying anything into a production directory happens only if a task above says so. Full inventory, rights and integration map: `student-go-docs/design-execution/ASSET_INVENTORY.md`. Evidence labels as in `DISCOVERY_REPORT.md` section 1.

### H.1 Source A: existing application assets that matter in this phase

| Asset | Decision | What happens in this phase | Evidence |
|---|---|---|---|
| Problem gallery images (remote Supabase bucket) | Retain | Shown only when they load; the media region collapses otherwise (D-14). | OBSERVED (audit J12) |
| `search-network.jpg`, `application-student.jpg` | Retain | Not used here; they may be page heroes elsewhere. | INFERRED |

### H.2 Source B: custom package assets available for this phase

**Original SVG, brand, fonts, icons and CSS (made for this project; in the package; licences in `ASSET_INVENTORY.md` section 2)**

- `empty-states/no-search-results.svg`, `map-pin*.svg`, Lucide icons.

### H.3 Reconciliation for this phase

Overlap 10 (map markers): swap the marker icon only; keep the map, tiles and handlers (AD-08).

Overlap comparisons that touch this phase are in `ASSET_INVENTORY.md` section 11; where a Source A asset and a Source B asset serve the same purpose, that table gives the starting recommendation and this phase re-checks it against the real page.

### H.4 Native UI (no asset; plain HTML/CSS)

- Filters, table.

### H.5 Do not touch in this phase

- Leaflet map behaviour.
- Student profile photographs and uploads, institution/company logos, verification documents: never moved or re-encoded; never shown in a new public place (D-04, D-31).

## I. Dependencies

- **Phases:** hard — Phase 1 (Critical Fixes & Data Integrity), Phase 3 (Shared Shell & Components); soft — Phase 2 (Brand Identity & Design System).
- **Decisions:** D-02 (Language policy and copy deck), D-05 (Stable ordering and count definitions), D-14 (Problem gallery images), D-12 (A working database for baseline and QA). A gating decision that is still OPEN in `DECISIONS_AND_OPEN_QUESTIONS.md` means the dependent tasks stop and the agent asks the user; it does not guess.
- **Packages:** No new composer or npm package. Anything new needs explicit approval first.
- **Data / environment:** 77 problems with varied difficulty, SDG, deadlines; real gallery images if available (D-14).

## J. Functionality preservation

The global rules in `CLAUDE.md` section 7 apply in full. For this phase specifically:

- All query parameters, including `page`, `search` and filter names, keep working.
- Links to problem detail keep their URLs.
- Map view data and toggle behaviour unchanged.
- Apply status per user (already applied/pending) still visible.

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

- Page through all pages; confirm each of 77 IDs appears exactly once.
- Search 'zzzzqqq' empty state; `?page=99` empty state.
- Filters combined (province + SDG + difficulty) return correct counts.
- Longest institution name and longest title in the list at 360 px.
- Keyboard: filters, sort, view toggle, pager.

*Test commands are not listed here because none could be verified without the repository. `CLAUDE.md` section 4 lists candidate commands that Stage One must confirm.*

## L. Acceptance criteria

The phase is COMPLETE only if every box can be checked with evidence in the phase report. Otherwise the status is PARTIAL (say which boxes failed) or BLOCKED (say what unblocks it).

- [ ] List is the default view and exposes institution, location, SDG, quota and deadline without opening a record.
- [ ] One search field; no separate 'Terapkan Filter' on desktop (auto-apply or a single apply bar); mobile drawer has sticky apply.
- [ ] No media placeholder tile appears anywhere.
- [ ] Difficulty is a neutral meter, never red/yellow/green.
- [ ] Counts shown match the query; filtered totals are labelled.
- [ ] Map view still works.
- [ ] No horizontal scroll at six widths; table stacks below 640 px without cropping.
- [ ] Reachability: all 77 problems are reachable and unique.

## M. Risks and blockers

| Risk or blocker | Mitigation / what unblocks it | Blocks the phase? |
|---|---|---|
| Sorting needs backend support | Limit to supported parameters; propose backend work via D-26. | No |
| Phase 1 incomplete leaves repeated cards | Hard dependency. | Yes |
| Real images change layout | Verify with D-14 images when available. | No |

## N. Expected deliverables

- Rewritten browse-problems view and partials; filter drawer; list rows.
- Before/after screenshots (S02, S25).
- `student-go-docs/design-execution/PHASE_TRACKER.md`: set Phase 8 to COMPLETE, PARTIAL or BLOCKED with date, branch/commit (if the user asked for one) and a one-line note.
- A phase report in the chat: what changed (files), what was verified (with the evidence), what failed or was skipped and why, decisions needed from the user, and the exact next phase the roadmap allows. Then stop and wait; do not start another phase.
