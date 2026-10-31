# PHASE 23 — Company Job Management

> Source of truth for status is `student-go-docs/design-execution/PHASE_TRACKER.md`. This file is the work order for exactly one phase. Read `CLAUDE.md` and `student-go-docs/design-execution/EXECUTION_ROADMAP.md` first, execute only this phase, update the tracker, report, then stop.
> Evidence labels: VERIFIED / OBSERVED / INFERRED / UNKNOWN / BLOCKED (definitions in `DISCOVERY_REPORT.md` section 1).

| Field | Value |
|---|---|
| Phase | 23 — Company Job Management |
| Group | Company role |
| Hard dependencies (must be COMPLETE) | Phase 3 (Shared Shell & Components) |
| Soft dependencies (recommended first) | Phase 1 (Critical Fixes & Data Integrity), Phase 22 (Company Talent Discovery) |
| Decisions that gate this phase | D-02 Language policy and copy deck, D-07 Product scope: KKN-only or jobs/internships as a main flow, D-13 Seed data for blocked screens, D-26 Features the audit proposes that do not exist today |
| Effort estimate (planning guess, not a commitment) | M |
| Roles touched | Company |
| Audit references | C04, C05; J6, J7, J8, J17, J28; audit D11 |
| Tracker status at planning time | NOT_STARTED |

## A. Phase overview

**Goal.** Keep the job table (the best table in the app), fix the 5-step wizard overflow, give it local placeholders and a shared stepper, and design the job detail page once data exists.

**In scope**

- `/company/jobs`, `/company/jobs/create`, and (INFERRED, BLOCKED) `/company/jobs/{id}`.

**Out of scope**

- Applicant pipeline (Phase 24).

**Evidence basis.** The audit (OBSERVED, 2026-10-28, port 8001) plus a read-only look at the running app on 2026-10-30. No repository source was read when this file was written; every statement about templates, controllers, models, middleware and tests is UNKNOWN until Stage One (Phase 0) fills `DISCOVERY_REPORT.md` section 12.

## B. Pages and routes

| Route pattern | Role | Audit status | Evidence | Notes |
|---|---|---|---|---|
| /company/jobs | Company | Inspected (empty) | OBSERVED | Good table pattern. |
| /company/jobs/create | Company | Inspected, not submitted | OBSERVED | 5-step wizard; overflows at 390 px. |
| /company/jobs/{id} | Company | BLOCKED (0 records) | INFERRED | — |

**Audit screenshot IDs for this phase** (audit section L; OBSERVED). Page data below is quoted or summarised from the audit; the screenshots themselves are in the PDF, not in this repository.

| ID | Page | URL | Keep (audit) | Design direction (audit) | Backlog |
|---|---|---|---|---|---|
| C04 | Kelola Lowongan Pekerjaan | /company/jobs | Tabel dengan empty state di dalamnya, filter berlabel. | Halaman tabel standar (header + tab status + tabel). | J6, J17 |
| C05 | Buat Lowongan Baru (wizard) | /company/jobs/create | Pemecahan menjadi langkah dengan langkah khusus “SDG Alignment” dan “Kriteria Dampak” yang khas produk. | Wizard bersama dengan rail pratinjau lowongan seperti yang akan dilihat mahasiswa. | J7, J8, J28 |

## C. Existing implementation

- [OBSERVED] Jobs table: real table with the right columns (judul, kategori, status, pelamar, dilihat, tanggal, aksi), horizontal scroll on mobile, labelled filters, empty state inside the table (kept).
- [OBSERVED] Create wizard (5 steps) with distinctive steps 'SDG Alignment' and 'Kriteria Dampak'; the stepper overflows at 390 px (page 483 px wide); placeholders 'Senior Software Engineer' and 'Remote, Berlin, Germany' are foreign.
- [BLOCKED] `/company/jobs/{id}`: no records, every probe 404 (INFERRED route).

**Repository facts for this phase: UNKNOWN until Stage One.** Locate and record in `DISCOVERY_REPORT.md` section 12 before editing: company jobs controller/views, wizard JS, job model fields, status values, SDG/impact fields.

## D. Problems found (audit findings)

| Backlog ID | Finding | Evidence |
|---|---|---|
| J7 (P1) | Stepper overflows at 390 px. | OBSERVED |
| J28 (P3) | Foreign placeholders. | OBSERVED |
| J8 (P1) | English labels. | OBSERVED |
| J6 (P1) | Hero photo. | OBSERVED |
| J17 (P2) | Divergent stepper. | OBSERVED |

## E. Approved design direction

**Approved by the user (binding)**

- Hybrid direction (AD-04): Ruang Kerja workbench foundation; Catatan Lapangan editorial layer only where reading and storytelling matter. This phase uses none of it unless stated below.
- Principles (AD-05) and functional preservation (AD-06). Tokens, fonts and components below come from the asset package and are PROPOSED until D-17 is approved; existing assets are reconciled under AD-09 (section H); D-16 records that reference documents B and C were not found.

**Proposed for this phase** (audit sections I and L, adapted; status PROPOSED until the user approves the gating decisions)

- Table page: header + status tabs + table (kept as the model). Wizard: shared stepper (horizontal from 640 px with 'Langkah 2 dari 5'), a preview rail of the posting as students will see it (front-end only, D-26), local examples (Koperasi, desa, kabupaten).

**Guardrails that bind this phase** (audit section H, numbered as in `EXECUTION_ROADMAP.md`): 1, 2, 3, 4, 5, 6, 8, 9, 10.

## F. Implementation tasks (do in order)

23.1. Gate check: D-02, D-07, D-26.

23.2. Jobs table: restyle with Phase 3 components; keep columns, filters and the horizontal-scroll container (one per table).

23.3. Wizard: shared stepper; all five steps with the same field names; local placeholders; validation after blur; preview rail optional (D-26).

23.4. Job detail: if the route exists and data is seeded, design as a record page (header, content, facts rail, applicants link); else BLOCKED.

23.5. Verify at 360 and 390 px that nothing overflows; verify create on the local DB.

23.6. Update the tracker and report with C04/C05 before/after.

## G. Components

| Component | Source | Notes |
|---|---|---|
| Table | Phase 3 | Model pattern. |
| Stepper | package | — |
| Form fields | package | — |
| Preview rail | to build (optional) | — |

## H. Assets (Source A existing `public/`, Source B custom package, Source C usage)

> Policy AD-09. Three sources, none disposable: **A** existing assets in `public/`, **B** the custom package `student-go-assets/`, **C** `resources/` (shows what is really used). Every asset has one decision: Retain, Reuse, Adapt, Replace or Deprecate. Originals are never deleted, overwritten, moved or renamed by this phase; copying anything into a production directory happens only if a task above says so. Full inventory, rights and integration map: `student-go-docs/design-execution/ASSET_INVENTORY.md`. Evidence labels as in `DISCOVERY_REPORT.md` section 1.

### H.1 Source A: existing application assets that matter in this phase

| Asset | Decision | What happens in this phase | Evidence |
|---|---|---|---|
| Company stock photo on job pages | Deprecate (usage) | Removed. | OBSERVED |

### H.2 Source B: custom package assets available for this phase

**Original SVG, brand, fonts, icons and CSS (made for this project; in the package; licences in `ASSET_INVENTORY.md` section 2)**

- `empty-states/no-search-results.svg`, Lucide icons.

### H.3 Reconciliation for this phase

No photo.

Overlap comparisons that touch this phase are in `ASSET_INVENTORY.md` section 11; where a Source A asset and a Source B asset serve the same purpose, that table gives the starting recommendation and this phase re-checks it against the real page.

### H.4 Native UI (no asset; plain HTML/CSS)

- Table, forms.

### H.5 Do not touch in this phase

- Student profile photographs and uploads, institution/company logos, verification documents: never moved or re-encoded; never shown in a new public place (D-04, D-31).

## I. Dependencies

- **Phases:** hard — Phase 3 (Shared Shell & Components); soft — Phase 1 (Critical Fixes & Data Integrity), Phase 22 (Company Talent Discovery).
- **Decisions:** D-02 (Language policy and copy deck), D-07 (Product scope: KKN-only or jobs/internships as a main flow), D-13 (Seed data for blocked screens), D-26 (Features the audit proposes that do not exist today). A gating decision that is still OPEN in `DECISIONS_AND_OPEN_QUESTIONS.md` means the dependent tasks stop and the agent asks the user; it does not guess.
- **Packages:** No new composer or npm package. Anything new needs explicit approval first.
- **Data / environment:** Jobs in different statuses (D-13).

## J. Functionality preservation

The global rules in `CLAUDE.md` section 7 apply in full. For this phase specifically:

- Wizard field names, step order and 'SDG Alignment' / 'Kriteria Dampak' steps unchanged.
- Filters and table actions unchanged.

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

- Create a job on the local DB; compare field names with the baseline.
- 390 px wizard.
- Table horizontal scroll container count = 1.

*Test commands are not listed here because none could be verified without the repository. `CLAUDE.md` section 4 lists candidate commands that Stage One must confirm.*

## L. Acceptance criteria

The phase is COMPLETE only if every box can be checked with evidence in the phase report. Otherwise the status is PARTIAL (say which boxes failed) or BLOCKED (say what unblocks it).

- [ ] The wizard has no overflow at 360 and 390 px.
- [ ] Placeholders are local and Indonesian.
- [ ] Field names and steps match the baseline.
- [ ] The table keeps all seven columns.
- [ ] Job detail designed with data or listed BLOCKED.

## M. Risks and blockers

| Risk or blocker | Mitigation / what unblocks it | Blocks the phase? |
|---|---|---|
| No job data | D-13. | Partially |
| Preview rail is new behaviour | D-26. | No |

## N. Expected deliverables

- Rewritten jobs list and create views.
- Screenshots C04/C05.
- `student-go-docs/design-execution/PHASE_TRACKER.md`: set Phase 23 to COMPLETE, PARTIAL or BLOCKED with date, branch/commit (if the user asked for one) and a one-line note.
- A phase report in the chat: what changed (files), what was verified (with the evidence), what failed or was skipped and why, decisions needed from the user, and the exact next phase the roadmap allows. Then stop and wait; do not start another phase.
