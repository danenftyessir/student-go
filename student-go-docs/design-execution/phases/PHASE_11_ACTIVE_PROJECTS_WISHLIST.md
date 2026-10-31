# PHASE 11 — Active Projects & Wishlist

> Source of truth for status is `student-go-docs/design-execution/PHASE_TRACKER.md`. This file is the work order for exactly one phase. Read `CLAUDE.md` and `student-go-docs/design-execution/EXECUTION_ROADMAP.md` first, execute only this phase, update the tracker, report, then stop.
> Evidence labels: VERIFIED / OBSERVED / INFERRED / UNKNOWN / BLOCKED (definitions in `DISCOVERY_REPORT.md` section 1).

| Field | Value |
|---|---|
| Phase | 11 — Active Projects & Wishlist |
| Group | Student role |
| Hard dependencies (must be COMPLETE) | Phase 3 (Shared Shell & Components) |
| Soft dependencies (recommended first) | Phase 10 (Applications), Phase 8 (Student Project Discovery) |
| Decisions that gate this phase | D-13 Seed data for blocked screens, D-21 Charts on dashboards |
| Effort estimate (planning guess, not a commitment) | S |
| Roles touched | Student |
| Audit references | S09, S17; J6, J17; audit D9 |
| Tracker status at planning time | NOT_STARTED |

## A. Phase overview

**Goal.** Give `/student/projects` and `/student/wishlist` the standard header and row patterns; show progress rows for active projects when data exists and a useful empty state when it does not.

**In scope**

- `/student/projects` (active projects) and `/student/wishlist`.
- Empty states (the audit's best pattern) with a specific next step; row layouts for when data exists.

**Out of scope**

- Project reports or milestones screens (unknown routes; see section C).

**Evidence basis.** The audit (OBSERVED, 2026-10-28, port 8001) plus a read-only look at the running app on 2026-10-30. No repository source was read when this file was written; every statement about templates, controllers, models, middleware and tests is UNKNOWN until Stage One (Phase 0) fills `DISCOVERY_REPORT.md` section 12.

## B. Pages and routes

| Route pattern | Role | Audit status | Evidence | Notes |
|---|---|---|---|---|
| /student/projects | Student | Inspected (empty) | OBSERVED | Active-project data BLOCKED (0 records). |
| /student/wishlist | Student | Inspected + measured (empty) | OBSERVED | — |

**Audit screenshot IDs for this phase** (audit section L; OBSERVED). Page data below is quoted or summarised from the audit; the screenshots themselves are in the PDF, not in this repository.

| ID | Page | URL | Keep (audit) | Design direction (audit) | Backlog |
|---|---|---|---|---|---|
| S09 | Proyek Saya | /student/projects | Empty state dengan CTA yang mengarah ke langkah berikutnya. | Saat ada data: daftar proyek berbentuk baris dengan progres (tahap, tenggat laporan berikutnya), bukan kartu. | J6 |
| S17 | Wishlist Saya | /student/wishlist | Header polos dengan aksi di kanan. | Daftar baris proyek tersimpan dengan tenggat dan tombol “Lamar”. | J17 |

## C. Existing implementation

- [OBSERVED] `/student/projects`: filter bar above an empty state; 0 active projects for the test account (BLOCKED for populated state).
- [OBSERVED] `/student/wishlist`: plain header with right-aligned action and an empty state; the empty-state pattern (outline icon, title, one line, CTA) is the best in the app.
- [OBSERVED] Seeded notifications mention milestones, weekly reports and project start for an account with no projects (J38), suggesting project-progress screens exist (milestones, weekly reports) that the audit could not reach.
- [UNKNOWN] Whether a student project detail or report-submission route exists (not in the 59-pattern inventory).

**Repository facts for this phase: UNKNOWN until Stage One.** Locate and record in `DISCOVERY_REPORT.md` section 12 before editing: projects controller/view, milestone and weekly-report models/routes, wishlist controller and toggle endpoint, how a wishlist item links to the problem.

## D. Problems found (audit findings)

| Backlog ID | Finding | Evidence |
|---|---|---|
| J6 (P1) | Photo-hero header on projects. | OBSERVED |
| J17 (P2) | Filter bar placed above an empty state; component divergence. | OBSERVED |
| J38 (P2) | Seed notifications do not match state; progress screens unseen. | OBSERVED |
| UNKNOWN | Populated active-project layout is BLOCKED (0 records). | BLOCKED |

## E. Approved design direction

**Approved by the user (binding)**

- Hybrid direction (AD-04): Ruang Kerja workbench foundation; Catatan Lapangan editorial layer only where reading and storytelling matter. This phase uses none of it unless stated below.
- Principles (AD-05) and functional preservation (AD-06). Tokens, fonts and components below come from the asset package and are PROPOSED until D-17 is approved; existing assets are reconciled under AD-09 (section H); D-16 records that reference documents B and C were not found.

**Proposed for this phase** (audit sections I and L, adapted; status PROPOSED until the user approves the gating decisions)

- Projects: when data exists, rows with Proyek · Instansi · Tahap · Laporan berikutnya · Progres (stage meter); the filter bar appears only when there is something to filter. Wishlist: rows with deadline and a 'Lamar' action; remove-from-wishlist as tertiary action. Empty states use the package SVGs and a role-specific next step.

**Guardrails that bind this phase** (audit section H, numbered as in `EXECUTION_ROADMAP.md`): 1, 2, 3, 6, 7, 9, 10.

## F. Implementation tasks (do in order)

11.1. Stage One result needed: which routes exist for project detail, milestones and reports. If routes exist that the audit missed, list them in the report and add them to this phase only with the user's approval.

11.2. Rebuild both pages with the Phase 3 page header and list/table components; hide the filter bar when the list is empty.

11.3. Projects rows: stage meter from the project status values that exist; never invent stages.

11.4. Wishlist rows: problem title, institution, deadline, 'Lamar' (existing target) and 'Hapus' (existing endpoint and verb).

11.5. Empty states with `no-saved-projects.svg` / `no-applications.svg` and a specific CTA ('Telusuri masalah KKN').

11.6. With seeded active projects (D-13) verify the populated layout; otherwise record BLOCKED for the populated state.

11.7. Update the tracker and report with S09/S17 before/after.

## G. Components

| Component | Source | Notes |
|---|---|---|
| Page header | Phase 3 | — |
| Row list with progress meter | Phase 3 list + `.sg-meter` | — |
| Empty state | Phase 3 | Keep the pattern. |
| Tertiary remove action | `.sg-btn--tertiary` | — |

## H. Assets (Source A existing `public/`, Source B custom package, Source C usage)

> Policy AD-09. Three sources, none disposable: **A** existing assets in `public/`, **B** the custom package `student-go-assets/`, **C** `resources/` (shows what is really used). Every asset has one decision: Retain, Reuse, Adapt, Replace or Deprecate. Originals are never deleted, overwritten, moved or renamed by this phase; copying anything into a production directory happens only if a task above says so. Full inventory, rights and integration map: `student-go-docs/design-execution/ASSET_INVENTORY.md`. Evidence labels as in `DISCOVERY_REPORT.md` section 1.

### H.1 Source A: existing application assets that matter in this phase

| Asset | Decision | What happens in this phase | Evidence |
|---|---|---|---|
| `projects-student.jpeg` | Deprecate (usage) / Reuse (elsewhere) | Probable page hero on `/student/projects`; replaced by the standard page header. Kept for documentary reuse. | INFERRED |

### H.2 Source B: custom package assets available for this phase

**Original SVG, brand, fonts, icons and CSS (made for this project; in the package; licences in `ASSET_INVENTORY.md` section 2)**

- `empty-states/no-saved-projects.svg`, `no-applications.svg`; Lucide icons (bookmark, calendar).

### H.3 Reconciliation for this phase

A real field photograph may accompany a completed project's story only if it belongs to that project.

Overlap comparisons that touch this phase are in `ASSET_INVENTORY.md` section 11; where a Source A asset and a Source B asset serve the same purpose, that table gives the starting recommendation and this phase re-checks it against the real page.

### H.4 Native UI (no asset; plain HTML/CSS)

- Rows.

### H.5 Do not touch in this phase

- Student profile photographs and uploads, institution/company logos, verification documents: never moved or re-encoded; never shown in a new public place (D-04, D-31).
- Certificate templates and the Pinyon Script and Poppins fonts (and whatever generates certificates): UNKNOWN location (D-32). Do not remove, rename, re-encode or restyle them, and do not remove a Google Fonts link that a certificate view depends on.

## I. Dependencies

- **Phases:** hard — Phase 3 (Shared Shell & Components); soft — Phase 10 (Applications), Phase 8 (Student Project Discovery).
- **Decisions:** D-13 (Seed data for blocked screens), D-21 (Charts on dashboards). A gating decision that is still OPEN in `DECISIONS_AND_OPEN_QUESTIONS.md` means the dependent tasks stop and the agent asks the user; it does not guess.
- **Packages:** No new composer or npm package. Anything new needs explicit approval first.
- **Data / environment:** Active projects (D-13); wishlist items.

## J. Functionality preservation

The global rules in `CLAUDE.md` section 7 apply in full. For this phase specifically:

- Wishlist add/remove endpoints and verbs unchanged.
- Links to problem detail unchanged.
- Project status values unchanged.

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

- Empty and populated states for both pages.
- Remove an item from the wishlist on the local DB and confirm the same result as before.
- Keyboard operation of remove/apply.

*Test commands are not listed here because none could be verified without the repository. `CLAUDE.md` section 4 lists candidate commands that Stage One must confirm.*

## L. Acceptance criteria

The phase is COMPLETE only if every box can be checked with evidence in the phase report. Otherwise the status is PARTIAL (say which boxes failed) or BLOCKED (say what unblocks it).

- [ ] Both pages use the standard page header and row patterns; no photo hero.
- [ ] Empty states show the SVG, one line and one specific next step.
- [ ] The filter bar does not render above an empty list.
- [ ] Wishlist actions keep their endpoints.
- [ ] Populated project layout verified with seeded data or recorded BLOCKED.

## M. Risks and blockers

| Risk or blocker | Mitigation / what unblocks it | Blocks the phase? |
|---|---|---|
| No active-project data | Request seeds (D-13); mark populated state BLOCKED. | Partially |
| Hidden project/report routes | Stage One inventory. | No |

## N. Expected deliverables

- Rewritten projects and wishlist views.
- Screenshots S09/S17.
- `student-go-docs/design-execution/PHASE_TRACKER.md`: set Phase 11 to COMPLETE, PARTIAL or BLOCKED with date, branch/commit (if the user asked for one) and a one-line note.
- A phase report in the chat: what changed (files), what was verified (with the evidence), what failed or was skipped and why, decisions needed from the user, and the exact next phase the roadmap allows. Then stop and wait; do not start another phase.
