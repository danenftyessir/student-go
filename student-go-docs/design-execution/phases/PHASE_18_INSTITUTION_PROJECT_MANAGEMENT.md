# PHASE 18 — Institution Project Management

> Source of truth for status is `student-go-docs/design-execution/PHASE_TRACKER.md`. This file is the work order for exactly one phase. Read `CLAUDE.md` and `student-go-docs/design-execution/EXECUTION_ROADMAP.md` first, execute only this phase, update the tracker, report, then stop.
> Evidence labels: VERIFIED / OBSERVED / INFERRED / UNKNOWN / BLOCKED (definitions in `DISCOVERY_REPORT.md` section 1).

| Field | Value |
|---|---|
| Phase | 18 — Institution Project Management |
| Group | Institution role |
| Hard dependencies (must be COMPLETE) | Phase 3 (Shared Shell & Components) |
| Soft dependencies (recommended first) | Phase 1 (Critical Fixes & Data Integrity), Phase 17 (Institution Dashboard) |
| Decisions that gate this phase | D-02 Language policy and copy deck, D-15 Placeholder region data, D-26 Features the audit proposes that do not exist today, D-13 Seed data for blocked screens |
| Effort estimate (planning guess, not a commitment) | L |
| Roles touched | Institution |
| Audit references | I02, I06; J8, J16, J18, J21, J30; audit D10 |
| Tracker status at planning time | NOT_STARTED |

## A. Phase overview

**Goal.** Make `/institution/problems` a status table and turn the 37-input problem creation page into a sectioned brief editor with a progress rail, keeping every field and the dependent selects working.

**In scope**

- `/institution/problems`, `/institution/problems/create`, and (INFERRED, BLOCKED) `/institution/problems/{id}` and `/{id}/edit` once they can be seen.

**Out of scope**

- Applicant review and project monitoring (Phase 19).

**Evidence basis.** The audit (OBSERVED, 2026-10-28, port 8001) plus a read-only look at the running app on 2026-10-30. No repository source was read when this file was written; every statement about templates, controllers, models, middleware and tests is UNKNOWN until Stage One (Phase 0) fills `DISCOVERY_REPORT.md` section 12.

## B. Pages and routes

| Route pattern | Role | Audit status | Evidence | Notes |
|---|---|---|---|---|
| /institution/problems | Institution | Inspected (empty) | OBSERVED | Statuses in English: Draft/Open/In Progress/Completed. |
| /institution/problems/create | Institution | Inspected, not submitted | OBSERVED | 37 inputs; /api/regencies/{id} dependency. |
| /institution/problems/{id} | Institution | BLOCKED (0 records; every ID 404) | INFERRED | Route inferred; layout cannot be designed from evidence. |
| /institution/problems/{id}/edit | Institution | BLOCKED | INFERRED | Same. |

**Audit screenshot IDs for this phase** (audit section L; OBSERVED). Page data below is quoted or summarised from the audit; the screenshots themselves are in the PDF, not in this repository.

| ID | Page | URL | Keep (audit) | Design direction (audit) | Backlog |
|---|---|---|---|---|---|
| I02 | Kelola Masalah | /institution/problems | Header polos, filter, empty state yang menjelaskan tujuan. | Tabel masalah: Judul · Status · Pelamar · Kuota · Tenggat · Diperbarui · Aksi. | J8, J16, J18 |
| I06 | Buat Masalah Baru | /institution/problems/create | Contoh placeholder lokal (“Peningkatan Literasi Digital di Desa Sukamaju”), helper text pada status Draft/Open, tombol “+ Tambah …” untuk daftar dinamis. | Penyusunan masalah sebagai dokumen brief: kiri navigasi section, tengah form, kanan pratinjau kartu/daftar seperti yang akan dilihat mahasiswa. | J21, J30, J8 |

## C. Existing implementation

- [OBSERVED] List: plain header, filters, a purposeful empty state; statuses in English (Draft, Open, In Progress, Completed).
- [OBSERVED] Create: one long page of 37 inputs; good local placeholder ('Peningkatan Literasi Digital di Desa Sukamaju'), Draft/Open helper text, '+ Tambah ...' buttons for dynamic lists; province-to-regency via `/api/regencies/{id}` with the stale-response race (J30).
- [BLOCKED] Detail and edit routes: the test institution has 0 problems and every ID probed was 404; layouts for them cannot be derived from evidence.

**Repository facts for this phase: UNKNOWN until Stage One.** Locate and record in `DISCOVERY_REPORT.md` section 12 before editing: institution problems controller/views, create/edit form field names, dynamic list JS, gallery/upload fields, status values, the `{id}` and `edit` routes.

## D. Problems found (audit findings)

| Backlog ID | Finding | Evidence |
|---|---|---|
| J21 (P2) | 37 inputs on one page; risk of abandonment. | OBSERVED |
| J30 (P3) | Regency select race. | OBSERVED |
| J8 (P1) | English statuses. | OBSERVED |
| J16 (P2) | Colour inconsistency. | OBSERVED |
| J18 (P2) | Needs table, not cards. | OBSERVED |
| BLOCKED | Detail/edit screens unseen. | BLOCKED |

## E. Approved design direction

**Approved by the user (binding)**

- Hybrid direction (AD-04): Ruang Kerja workbench foundation; Catatan Lapangan editorial layer only where reading and storytelling matter. This phase uses none of it unless stated below.
- Principles (AD-05) and functional preservation (AD-06). Tokens, fonts and components below come from the asset package and are PROPOSED until D-17 is approved; existing assets are reconciled under AD-09 (section H); D-16 records that reference documents B and C were not found.

**Proposed for this phase** (audit sections I and L, adapted; status PROPOSED until the user approves the gating decisions)

- List: table Judul · Status · Pelamar · Kuota · Tenggat · Diperbarui · Aksi. Create: left section nav (Informasi dasar, Lokasi, Sasaran & SDG, Kebutuhan & keterampilan, Media, Status), centre form, right preview of the row/brief as students will see it (the preview is a front-end render of the typed values: no new backend; confirm in D-26). Draft saves with the existing mechanism only.

**Guardrails that bind this phase** (audit section H, numbered as in `EXECUTION_ROADMAP.md`): 1, 2, 3, 4, 5, 6, 9, 10.

## F. Implementation tasks (do in order)

18.1. Gate check: D-02 statuses (Draf, Dibuka, Berjalan, Selesai proposed), D-26 (preview), D-13.

18.2. Build the table with the shared status badge and the existing filters; keep the empty state.

18.3. Create form: regroup the 37 inputs into the six sections without renaming; shared stepper/section nav; dynamic lists keep their Alpine behaviour.

18.4. Regency select: abort previous requests, loading state, `aria-live`.

18.5. Optional preview rail only if D-26 allows (front-end only); otherwise skip.

18.6. If Stage One finds `{id}` and `edit` routes and seeded data exists (D-13), apply the same sectioned form for edit and a brief layout for detail; otherwise mark both BLOCKED.

18.7. Verify creating a draft problem on the local DB with a throwaway institution account and compare stored fields with the baseline form inventory.

18.8. Update the tracker and report with I02/I06 before/after.

## G. Components

| Component | Source | Notes |
|---|---|---|
| Table | Phase 3 | — |
| Sectioned form with progress rail | Phase 3 stepper/section nav | — |
| Dynamic list inputs | existing Alpine + `.sg-btn--tertiary` | — |
| Upload | package `.sg-dropzone` | — |
| Dependent selects | existing JS + abort/loading | — |

## H. Assets (Source A existing `public/`, Source B custom package, Source C usage)

> Policy AD-09. Three sources, none disposable: **A** existing assets in `public/`, **B** the custom package `student-go-assets/`, **C** `resources/` (shows what is really used). Every asset has one decision: Retain, Reuse, Adapt, Replace or Deprecate. Originals are never deleted, overwritten, moved or renamed by this phase; copying anything into a production directory happens only if a task above says so. Full inventory, rights and integration map: `student-go-docs/design-execution/ASSET_INVENTORY.md`. Evidence labels as in `DISCOVERY_REPORT.md` section 1.

### H.1 Source A: existing application assets that matter in this phase

| Asset | Decision | What happens in this phase | Evidence |
|---|---|---|---|
| Problem images uploaded by institutions | Retain | Upload behaviour, accepted types and storage paths are preserved. | UNKNOWN / BLOCKED |

### H.2 Source B: custom package assets available for this phase

**Original SVG, brand, fonts, icons and CSS (made for this project; in the package; licences in `ASSET_INVENTORY.md` section 2)**

- `empty-states/no-saved-projects.svg`; Lucide icons.

### H.3 Reconciliation for this phase

No institution-side empty state exists in the package (section 8); use text.

Overlap comparisons that touch this phase are in `ASSET_INVENTORY.md` section 11; where a Source A asset and a Source B asset serve the same purpose, that table gives the starting recommendation and this phase re-checks it against the real page.

### H.4 Native UI (no asset; plain HTML/CSS)

- Forms.

### H.5 Do not touch in this phase

- Upload field names and storage paths.
- Student profile photographs and uploads, institution/company logos, verification documents: never moved or re-encoded; never shown in a new public place (D-04, D-31).

## I. Dependencies

- **Phases:** hard — Phase 3 (Shared Shell & Components); soft — Phase 1 (Critical Fixes & Data Integrity), Phase 17 (Institution Dashboard).
- **Decisions:** D-02 (Language policy and copy deck), D-15 (Placeholder region data), D-26 (Features the audit proposes that do not exist today), D-13 (Seed data for blocked screens). A gating decision that is still OPEN in `DECISIONS_AND_OPEN_QUESTIONS.md` means the dependent tasks stop and the agent asks the user; it does not guess.
- **Packages:** No new composer or npm package. Anything new needs explicit approval first.
- **Data / environment:** Institution with problems in several statuses (D-13).

## J. Functionality preservation

The global rules in `CLAUDE.md` section 7 apply in full. For this phase specifically:

- All 37 input names, validation and the draft/open behaviour unchanged.
- `/api/regencies/{id}` consumer unchanged.
- Upload field names and storage unchanged.

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

- Create a draft with every section filled; compare stored values with input names.
- Validation errors appear inside the right section and the section nav indicates them.
- Rapid province change.
- 390 px layout of the long form.

*Test commands are not listed here because none could be verified without the repository. `CLAUDE.md` section 4 lists candidate commands that Stage One must confirm.*

## L. Acceptance criteria

The phase is COMPLETE only if every box can be checked with evidence in the phase report. Otherwise the status is PARTIAL (say which boxes failed) or BLOCKED (say what unblocks it).

- [ ] The list is a table with the seven columns and shared status words.
- [ ] Every one of the 37 inputs keeps its name and value flow (inventory diff empty).
- [ ] A user can navigate sections by keyboard and errors are announced.
- [ ] Regency options always match the selected province.
- [ ] No horizontal scroll at six widths.
- [ ] Detail/edit are either designed with data or listed BLOCKED.

## M. Risks and blockers

| Risk or blocker | Mitigation / what unblocks it | Blocks the phase? |
|---|---|---|
| Detail/edit unseen | BLOCKED until seeds exist. | Partially |
| Preview rail is new behaviour | D-26. | No |

## N. Expected deliverables

- Rewritten problems list and create (and edit/detail if verifiable) views.
- Screenshots I02/I06.
- `student-go-docs/design-execution/PHASE_TRACKER.md`: set Phase 18 to COMPLETE, PARTIAL or BLOCKED with date, branch/commit (if the user asked for one) and a one-line note.
- A phase report in the chat: what changed (files), what was verified (with the evidence), what failed or was skipped and why, decisions needed from the user, and the exact next phase the roadmap allows. Then stop and wait; do not start another phase.
