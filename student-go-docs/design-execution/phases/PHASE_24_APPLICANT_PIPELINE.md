# PHASE 24 — Applicant Pipeline

> Source of truth for status is `student-go-docs/design-execution/PHASE_TRACKER.md`. This file is the work order for exactly one phase. Read `CLAUDE.md` and `student-go-docs/design-execution/EXECUTION_ROADMAP.md` first, execute only this phase, update the tracker, report, then stop.
> Evidence labels: VERIFIED / OBSERVED / INFERRED / UNKNOWN / BLOCKED (definitions in `DISCOVERY_REPORT.md` section 1).

| Field | Value |
|---|---|
| Phase | 24 — Applicant Pipeline |
| Group | Company role |
| Hard dependencies (must be COMPLETE) | Phase 3 (Shared Shell & Components), Phase 23 (Company Job Management) |
| Soft dependencies (recommended first) | Phase 1 (Critical Fixes & Data Integrity) |
| Decisions that gate this phase | D-02 Language policy and copy deck, D-13 Seed data for blocked screens, D-26 Features the audit proposes that do not exist today |
| Effort estimate (planning guess, not a commitment) | M |
| Roles touched | Company |
| Audit references | C06; J7, J17; audit D11 |
| Tracker status at planning time | NOT_STARTED |

## A. Phase overview

**Goal.** Turn `/company/applications` into a stage pipeline (tabs + table, optional board later), inside the standard container, with an empty state and a responsive table that never crops columns.

**In scope**

- `/company/applications` and (INFERRED, BLOCKED) `/company/applications/{id}`.

**Out of scope**

- Student-side job applications tab (Phase 10).

**Evidence basis.** The audit (OBSERVED, 2026-10-28, port 8001) plus a read-only look at the running app on 2026-10-30. No repository source was read when this file was written; every statement about templates, controllers, models, middleware and tests is UNKNOWN until Stage One (Phase 0) fills `DISCOVERY_REPORT.md` section 12.

## B. Pages and routes

| Route pattern | Role | Audit status | Evidence | Notes |
|---|---|---|---|---|
| /company/applications | Company | Inspected (empty) | OBSERVED | Full-bleed; 3 of 6 columns at 390 px; no empty state. |
| /company/applications/{id} | Company | BLOCKED (0 records) | INFERRED | — |

**Audit screenshot IDs for this phase** (audit section L; OBSERVED). Page data below is quoted or summarised from the audit; the screenshots themselves are in the PDF, not in this repository.

| ID | Page | URL | Keep (audit) | Design direction (audit) | Backlog |
|---|---|---|---|---|---|
| C06 | Manajemen Lamaran | /company/applications | Kolom tabel yang relevan, checkbox untuk aksi massal. | Pipeline per tahap (Baru · Ditinjau · Wawancara · Diterima · Ditolak) sebagai tab + tabel, atau papan kanban opsional. | J7, J17 |

## C. Existing implementation

- [OBSERVED] Full-bleed layout (only page like it); table with relevant columns and a checkbox for bulk actions; empty table with no empty-state message; at 390 px only 3 of 6 columns are visible.
- [BLOCKED] Detail route and every stage state: no applicants.

**Repository facts for this phase: UNKNOWN until Stage One.** Locate and record in `DISCOVERY_REPORT.md` section 12 before editing: company applications controller/views, applicant stage/status values, bulk-action endpoints.

## D. Problems found (audit findings)

| Backlog ID | Finding | Evidence |
|---|---|---|
| J7 (P1) | Columns cropped at 390 px; full-bleed layout. | OBSERVED |
| J17 (P2) | Inconsistent container and component use. | OBSERVED |
| No empty state | Table is empty with no message. | OBSERVED |

## E. Approved design direction

**Approved by the user (binding)**

- Hybrid direction (AD-04): Ruang Kerja workbench foundation; Catatan Lapangan editorial layer only where reading and storytelling matter. This phase uses none of it unless stated below.
- Principles (AD-05) and functional preservation (AD-06). Tokens, fonts and components below come from the asset package and are PROPOSED until D-17 is approved; existing assets are reconciled under AD-09 (section H); D-16 records that reference documents B and C were not found.

**Proposed for this phase** (audit sections I and L, adapted; status PROPOSED until the user approves the gating decisions)

- Stage tabs (Baru · Ditinjau · Wawancara · Diterima · Ditolak, mapped to the real stored stages; never invented) with counts, then a table; below 640 px stacked rows; bulk actions in a bar that appears on selection; a board view only if D-26 approves.

**Guardrails that bind this phase** (audit section H, numbered as in `EXECUTION_ROADMAP.md`): 1, 2, 3, 4, 6, 9, 10.

## F. Implementation tasks (do in order)

24.1. Gate check: D-13 seeds; Stage One list of real stages.

24.2. Standard container and page header; stage tabs with counts from real queries.

24.3. Table with the six columns; stacked rows below 640 px; sticky header; bulk-action bar keeps the same endpoints.

24.4. Empty state per stage ('Belum ada pelamar'), CTA to jobs.

24.5. Detail page if route exists and data is seeded; else BLOCKED.

24.6. Verify with applicants in every stage when seeded.

24.7. Update the tracker and report with C06 before/after.

## G. Components

| Component | Source | Notes |
|---|---|---|
| Tabs with counts | Phase 3 | — |
| Table with bulk bar | Phase 3 | — |
| Status badge | Phase 3 | — |
| Empty state | Phase 3 | — |

## H. Assets (Source A existing `public/`, Source B custom package, Source C usage)

> Policy AD-09. Three sources, none disposable: **A** existing assets in `public/`, **B** the custom package `student-go-assets/`, **C** `resources/` (shows what is really used). Every asset has one decision: Retain, Reuse, Adapt, Replace or Deprecate. Originals are never deleted, overwritten, moved or renamed by this phase; copying anything into a production directory happens only if a task above says so. Full inventory, rights and integration map: `student-go-docs/design-execution/ASSET_INVENTORY.md`. Evidence labels as in `DISCOVERY_REPORT.md` section 1.

### H.1 Source A: existing application assets that matter in this phase

| Asset | Decision | What happens in this phase | Evidence |
|---|---|---|---|
| Applicant photographs and documents | Retain | Shown as today. | UNKNOWN / BLOCKED |

### H.2 Source B: custom package assets available for this phase

**Original SVG, brand, fonts, icons and CSS (made for this project; in the package; licences in `ASSET_INVENTORY.md` section 2)**

- `empty-states/no-applications.svg`, Lucide icons.

### H.3 Reconciliation for this phase

No overlap.

Overlap comparisons that touch this phase are in `ASSET_INVENTORY.md` section 11; where a Source A asset and a Source B asset serve the same purpose, that table gives the starting recommendation and this phase re-checks it against the real page.

### H.4 Native UI (no asset; plain HTML/CSS)

- Table.

### H.5 Do not touch in this phase

- Student profile photographs and uploads, institution/company logos, verification documents: never moved or re-encoded; never shown in a new public place (D-04, D-31).
- Certificate templates and the Pinyon Script and Poppins fonts (and whatever generates certificates): UNKNOWN location (D-32). Do not remove, rename, re-encode or restyle them, and do not remove a Google Fonts link that a certificate view depends on.

## I. Dependencies

- **Phases:** hard — Phase 3 (Shared Shell & Components), Phase 23 (Company Job Management); soft — Phase 1 (Critical Fixes & Data Integrity).
- **Decisions:** D-02 (Language policy and copy deck), D-13 (Seed data for blocked screens), D-26 (Features the audit proposes that do not exist today). A gating decision that is still OPEN in `DECISIONS_AND_OPEN_QUESTIONS.md` means the dependent tasks stop and the agent asks the user; it does not guess.
- **Packages:** No new composer or npm package. Anything new needs explicit approval first.
- **Data / environment:** Applicants in every stage (D-13).

## J. Functionality preservation

The global rules in `CLAUDE.md` section 7 apply in full. For this phase specifically:

- Bulk-action endpoints and checkbox names unchanged.
- Stage values unchanged.

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

- 390 px: no cropped columns; stacked rows.
- Bulk action on throwaway data.

*Test commands are not listed here because none could be verified without the repository. `CLAUDE.md` section 4 lists candidate commands that Stage One must confirm.*

## L. Acceptance criteria

The phase is COMPLETE only if every box can be checked with evidence in the phase report. Otherwise the status is PARTIAL (say which boxes failed) or BLOCKED (say what unblocks it).

- [ ] The page uses the standard container.
- [ ] At 390 px every column's information is visible (stacked rows) with no cropped table.
- [ ] An empty stage shows a message.
- [ ] Tab labels equal real stored stages (or the approved mapping).
- [ ] Bulk actions keep their endpoints.

## M. Risks and blockers

| Risk or blocker | Mitigation / what unblocks it | Blocks the phase? |
|---|---|---|
| Stages unseen | BLOCKED for non-empty states. | Partially |

## N. Expected deliverables

- Rewritten applications view.
- Screenshot C06.
- `student-go-docs/design-execution/PHASE_TRACKER.md`: set Phase 24 to COMPLETE, PARTIAL or BLOCKED with date, branch/commit (if the user asked for one) and a one-line note.
- A phase report in the chat: what changed (files), what was verified (with the evidence), what failed or was skipped and why, decisions needed from the user, and the exact next phase the roadmap allows. Then stop and wait; do not start another phase.
