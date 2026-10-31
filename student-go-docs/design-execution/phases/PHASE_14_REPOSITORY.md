# PHASE 14 — Repository

> Source of truth for status is `student-go-docs/design-execution/PHASE_TRACKER.md`. This file is the work order for exactly one phase. Read `CLAUDE.md` and `student-go-docs/design-execution/EXECUTION_ROADMAP.md` first, execute only this phase, update the tracker, report, then stop.
> Evidence labels: VERIFIED / OBSERVED / INFERRED / UNKNOWN / BLOCKED (definitions in `DISCOVERY_REPORT.md` section 1).

| Field | Value |
|---|---|
| Phase | 14 — Repository |
| Group | Student role |
| Hard dependencies (must be COMPLETE) | Phase 1 (Critical Fixes & Data Integrity), Phase 3 (Shared Shell & Components) |
| Soft dependencies (recommended first) | Phase 2 (Brand Identity & Design System) |
| Decisions that gate this phase | D-02 Language policy and copy deck, D-05 Stable ordering and count definitions, D-26 Features the audit proposes that do not exist today |
| Effort estimate (planning guess, not a commitment) | M |
| Roles touched | Student |
| Audit references | S10, S11; J2, J4, J6, J8, J18, J27, J36; audit D6 |
| Tracker status at planning time | NOT_STARTED |

## A. Phase overview

**Goal.** Turn the knowledge repository into a library table with prominent search and readable metadata (file type chip, SDG names), and each document into a catalogue-style detail page. This is the largest real-content area (216 documents).

**In scope**

- `/student/repository` and `/student/repository/{id}`.

**Out of scope**

- Ordering/count backend (Phase 1); PDF preview (new feature, D-26).

**Evidence basis.** The audit (OBSERVED, 2026-10-28, port 8001) plus a read-only look at the running app on 2026-10-30. No repository source was read when this file was written; every statement about templates, controllers, models, middleware and tests is UNKNOWN until Stage One (Phase 0) fills `DISCOVERY_REPORT.md` section 12.

## B. Pages and routes

| Route pattern | Role | Audit status | Evidence | Notes |
|---|---|---|---|---|
| /student/repository | Student | Inspected | OBSERVED | ?page=1-19, ?search=. |
| /student/repository/{id} | Student | 1-216 fetched; 160 inspected | OBSERVED | /abc returns HTTP 500 (J13). |
| /student/repository/abc | Student | Probed by guess | OBSERVED | Non-numeric ID returns HTTP 500 (J13). Fix in Phase 1; error page in Phase 26. |

**Audit screenshot IDs for this phase** (audit section L; OBSERVED). Page data below is quoted or summarised from the audit; the screenshots themselves are in the PDF, not in this repository.

| ID | Page | URL | Keep (audit) | Design direction (audit) | Backlog |
|---|---|---|---|---|---|
| S10 | Repositori Pengetahuan (daftar) | /student/repository | Filter SDG/provinsi/tahun/urutkan relevan; metadata penulis, provinsi, tahun, dilihat, diunduh sudah tersedia. | Halaman perpustakaan berbasis tabel dengan search menonjol dan filter di atas tabel; detail dibuka di halaman sendiri dengan pratinjau PDF. | J2, J4, J6, J8, J18, J27 |
| S11 | Detail dokumen repositori (#160) | /student/repository/160 | Grid metadata (instansi, provinsi, kabupaten, universitas, tipe, ukuran). | Detail dokumen bergaya halaman katalog perpustakaan: judul, metadata, abstrak, pratinjau, unduh, terkait. | J27, J8, J36 |

## C. Existing implementation

- [OBSERVED] 216 real titles (34-65 characters); header says 'Total Dokumen 221'; paging hides 7 documents (19 pages give 209 unique; 43, 131, 192, 197, 214, 215, 216 never shown); SDG pills show a bare number ('4'); file type 'APPLICATION/PDF'; mixed labels ('Showing', 'Download', 'Detail'); a featured row repeats three items from the list.
- [OBSERVED] Detail: well-structured metadata grid (institution, province, regency, university, file type, size); duplicate related documents (J36).
- [OBSERVED] `/student/repository/abc` returns HTTP 500 (J13; fixed in Phase 1).

**Repository facts for this phase: UNKNOWN until Stage One.** Locate and record in `DISCOVERY_REPORT.md` section 12 before editing: repository controller/view, document model fields, how file type and size are stored, download endpoint, SDG relation, related-documents query.

## D. Problems found (audit findings)

| Backlog ID | Finding | Evidence |
|---|---|---|
| J2 / J4 (P0) | Hidden documents and wrong total. | OBSERVED |
| J27 (P3) | Raw labels: 'APPLICATION/PDF', bare SDG number. | OBSERVED |
| J18 (P2) | Card list for a library. | OBSERVED |
| J8 (P1) | Mixed language. | OBSERVED |
| J36 (P3) | Duplicate related documents. | OBSERVED |
| J6 (P1) | Photo hero. | OBSERVED |
| J13 (P1) | Non-numeric ID 500. | OBSERVED |

## E. Approved design direction

**Approved by the user (binding)**

- Hybrid direction (AD-04): Ruang Kerja workbench foundation; Catatan Lapangan editorial layer only where the repository detail is a reading page (abstract).
- Principles (AD-05) and functional preservation (AD-06). Tokens, fonts and components below come from the asset package and are PROPOSED until D-17 is approved; existing assets are reconciled under AD-09 (section H); D-16 records that reference documents B and C were not found.

**Proposed for this phase** (audit sections I and L, adapted; status PROPOSED until the user approves the gating decisions)

- Library table: Judul · Instansi · Wilayah · Tahun · Dilihat · Diunduh; prominent search; filters (SDG, provinsi, tahun, urutan) above the table; file chip 'PDF · 1,4 MB'; SDG as swatch + name; no featured row duplicating the list. Detail as a library catalogue page: title, metadata, abstract (serif reading column), download, related (de-duplicated). A PDF preview is only a layout slot, not built without D-26.

**Guardrails that bind this phase** (audit section H, numbered as in `EXECUTION_ROADMAP.md`): 1, 2, 3, 4, 5, 7, 9, 10.

## F. Implementation tasks (do in order)

14.1. Gate check: Phase 1 COMPLETE (216/216 reachable; header count equals records).

14.2. Table with the six columns, sortable only by parameters the backend supports; stable pager; search field prominent.

14.3. Human labels: file type map (`application/pdf` to 'PDF'), size formatting, SDG number to name with swatch (names from the same Indonesian SDG list used in Phase 22).

14.4. Remove the featured row or make it a real, distinct list (do not duplicate list items).

14.5. Detail page: metadata definition list, abstract as reading column, download button (existing endpoint), related list de-duplicated.

14.6. Verify with the longest title (65 chars), a document with no abstract, and `/abc` (404 after Phase 1).

14.7. Update the tracker and report with S10/S11 before/after.

## G. Components

| Component | Source | Notes |
|---|---|---|
| Library table + file chip + SDG chip | Phase 3 + package | — |
| Search + filters | Phase 3 | — |
| Definition list | Phase 3 | — |
| Reading column | package `.sg-reading` | — |

## H. Assets (Source A existing `public/`, Source B custom package, Source C usage)

> Policy AD-09. Three sources, none disposable: **A** existing assets in `public/`, **B** the custom package `student-go-assets/`, **C** `resources/` (shows what is really used). Every asset has one decision: Retain, Reuse, Adapt, Replace or Deprecate. Originals are never deleted, overwritten, moved or renamed by this phase; copying anything into a production directory happens only if a task above says so. Full inventory, rights and integration map: `student-go-docs/design-execution/ASSET_INVENTORY.md`. Evidence labels as in `DISCOVERY_REPORT.md` section 1.

### H.1 Source A: existing application assets that matter in this phase

| Asset | Decision | What happens in this phase | Evidence |
|---|---|---|---|
| `repo-student.jpeg` | Deprecate (usage) / Reuse (elsewhere) | Probable page hero on `/student/repository`; replaced by the standard page header. | INFERRED |
| Repository documents (uploads, PDFs) | Retain | Functional assets: never altered. Preview only if a feature exists (D-26). | UNKNOWN / BLOCKED |

### H.2 Source B: custom package assets available for this phase

**Original SVG, brand, fonts, icons and CSS (made for this project; in the package; licences in `ASSET_INVENTORY.md` section 2)**

- `empty-states/no-documents.svg`; Lucide icons (file-text, download, eye).

### H.3 Reconciliation for this phase

Nothing decorative; a photo only if it truly belongs to a document.

Overlap comparisons that touch this phase are in `ASSET_INVENTORY.md` section 11; where a Source A asset and a Source B asset serve the same purpose, that table gives the starting recommendation and this phase re-checks it against the real page.

### H.4 Native UI (no asset; plain HTML/CSS)

- Table and text.

### H.5 Do not touch in this phase

- All uploaded documents.
- Student profile photographs and uploads, institution/company logos, verification documents: never moved or re-encoded; never shown in a new public place (D-04, D-31).

## I. Dependencies

- **Phases:** hard — Phase 1 (Critical Fixes & Data Integrity), Phase 3 (Shared Shell & Components); soft — Phase 2 (Brand Identity & Design System).
- **Decisions:** D-02 (Language policy and copy deck), D-05 (Stable ordering and count definitions), D-26 (Features the audit proposes that do not exist today). A gating decision that is still OPEN in `DECISIONS_AND_OPEN_QUESTIONS.md` means the dependent tasks stop and the agent asks the user; it does not guess.
- **Packages:** No new composer or npm package. Anything new needs explicit approval first.
- **Data / environment:** 216 documents with long titles.

## J. Functionality preservation

The global rules in `CLAUDE.md` section 7 apply in full. For this phase specifically:

- Download endpoint, view counter behaviour and file storage unchanged.
- Filter and search parameters unchanged.
- Document detail URLs unchanged.

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

- All 216 reachable by paging.
- Longest title at 360 px.
- Download still works.
- Non-numeric ID returns 404.

*Test commands are not listed here because none could be verified without the repository. `CLAUDE.md` section 4 lists candidate commands that Stage One must confirm.*

## L. Acceptance criteria

The phase is COMPLETE only if every box can be checked with evidence in the phase report. Otherwise the status is PARTIAL (say which boxes failed) or BLOCKED (say what unblocks it).

- [ ] The header total equals the number of reachable documents (216 on the audit dataset).
- [ ] No raw MIME type or bare SDG number is visible anywhere.
- [ ] The list is a table with the six columns and stacks below 640 px.
- [ ] No duplicate featured row.
- [ ] Related documents contain no duplicates.
- [ ] All labels are Indonesian.

## M. Risks and blockers

| Risk or blocker | Mitigation / what unblocks it | Blocks the phase? |
|---|---|---|
| PDF preview requested | New feature (D-26). | No |
| Abstract field may not exist | Stage One; layout slot optional. | No |

## N. Expected deliverables

- Rewritten repository list/detail views.
- Screenshots S10/S11.
- `student-go-docs/design-execution/PHASE_TRACKER.md`: set Phase 14 to COMPLETE, PARTIAL or BLOCKED with date, branch/commit (if the user asked for one) and a one-line note.
- A phase report in the chat: what changed (files), what was verified (with the evidence), what failed or was skipped and why, decisions needed from the user, and the exact next phase the roadmap allows. Then stop and wait; do not start another phase.
