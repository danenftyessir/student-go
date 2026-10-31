# PHASE 19 — Institution Review & Monitoring

> Source of truth for status is `student-go-docs/design-execution/PHASE_TRACKER.md`. This file is the work order for exactly one phase. Read `CLAUDE.md` and `student-go-docs/design-execution/EXECUTION_ROADMAP.md` first, execute only this phase, update the tracker, report, then stop.
> Evidence labels: VERIFIED / OBSERVED / INFERRED / UNKNOWN / BLOCKED (definitions in `DISCOVERY_REPORT.md` section 1).

| Field | Value |
|---|---|
| Phase | 19 — Institution Review & Monitoring |
| Group | Institution role |
| Hard dependencies (must be COMPLETE) | Phase 3 (Shared Shell & Components) |
| Soft dependencies (recommended first) | Phase 1 (Critical Fixes & Data Integrity), Phase 18 (Institution Project Management) |
| Decisions that gate this phase | D-02 Language policy and copy deck, D-13 Seed data for blocked screens, D-25 Defect fixes that change HTTP behaviour, D-26 Features the audit proposes that do not exist today |
| Effort estimate (planning guess, not a commitment) | L |
| Roles touched | Institution |
| Audit references | I03, I04, I05; J8, J13, J17, J18, J9; audit D10, E |
| Tracker status at planning time | NOT_STARTED |

## A. Phase overview

**Goal.** Give institutions a split-view applicant review with a decision bar, a project monitoring table with progress, and a reviews list; make `/institution/reviews/create` either work or fail gracefully.

**In scope**

- `/institution/applications` (?status=pending), `/institution/projects` (?status=active), `/institution/reviews`, `/institution/reviews/create` (probed route that throws), and detail routes if they exist.

**Out of scope**

- Student-side applications (Phase 10).

**Evidence basis.** The audit (OBSERVED, 2026-10-28, port 8001) plus a read-only look at the running app on 2026-10-30. No repository source was read when this file was written; every statement about templates, controllers, models, middleware and tests is UNKNOWN until Stage One (Phase 0) fills `DISCOVERY_REPORT.md` section 12.

## B. Pages and routes

| Route pattern | Role | Audit status | Evidence | Notes |
|---|---|---|---|---|
| /institution/applications | Institution | Inspected (empty) | OBSERVED | ?status=pending. |
| /institution/projects | Institution | Inspected (empty) | OBSERVED | ?status=active. |
| /institution/reviews | Institution | Inspected (empty) | OBSERVED | — |
| /institution/reviews/create | Institution | Probed by guess | OBSERVED | Renders a full Illuminate\Database\QueryException debug page (J13). Also relevant to Phase 1. |

**Audit screenshot IDs for this phase** (audit section L; OBSERVED). Page data below is quoted or summarised from the audit; the screenshots themselves are in the PDF, not in this repository.

| ID | Page | URL | Keep (audit) | Design direction (audit) | Backlog |
|---|---|---|---|---|---|
| I03 | Review Aplikasi Mahasiswa | /institution/applications | Filter berdasarkan masalah dan status. | Split view: daftar pelamar di kiri (nama, kampus, jurusan, tanggal, status), detail lamaran di kanan (motivasi, proposal, profil), bar keputusan Terima / Tolak dengan alasan. | J8, J18 |
| I04 | Kelola Proyek | /institution/projects | Filter berdasarkan masalah dan status. | Tabel proyek dengan kolom progres (meter tahap) dan laporan terakhir; detail proyek berupa timeline. | J8, J17 |
| I05 | Review Mahasiswa (Ulasan) | /institution/reviews | Empty state yang menjelaskan. | Daftar ulasan ber-divider (mahasiswa, proyek, rating, komentar, tanggal). | J9, J17 |

## C. Existing implementation

- [OBSERVED] All three pages are empty states for the test institution (0 applications, projects, reviews); filter by problem and status exists on applications and projects.
- [OBSERVED] `/institution/reviews/create` (a guessed URL) renders a full QueryException debug page (J13).
- [BLOCKED] Review queue, decision flow, monitoring and review forms were never seen with data.

**Repository facts for this phase: UNKNOWN until Stage One.** Locate and record in `DISCOVERY_REPORT.md` section 12 before editing: applications, projects and reviews controllers; decision endpoints (accept/reject with reason); project progress and report models; the reviews create route and form.

## D. Problems found (audit findings)

| Backlog ID | Finding | Evidence |
|---|---|---|
| J13 (P1) | `/institution/reviews/create` exception page. | OBSERVED |
| J8 (P1) | English statuses. | OBSERVED |
| J17 (P2) | Inconsistent component use. | OBSERVED |
| J18 (P2) | No dense review layout. | OBSERVED |
| BLOCKED | Populated screens unseen. | BLOCKED |

## E. Approved design direction

**Approved by the user (binding)**

- Hybrid direction (AD-04): Ruang Kerja workbench foundation; Catatan Lapangan editorial layer only where reading and storytelling matter. This phase uses none of it unless stated below.
- Principles (AD-05) and functional preservation (AD-06). Tokens, fonts and components below come from the asset package and are PROPOSED until D-17 is approved; existing assets are reconciled under AD-09 (section H); D-16 records that reference documents B and C were not found.

**Proposed for this phase** (audit sections I and L, adapted; status PROPOSED until the user approves the gating decisions)

- Applicants: split view, queue at the left (name, campus, major, date, status), application at the right (motivation, proposal, profile), a decision bar 'Terima / Tolak' with required reason, keyboard navigation between applicants. Projects: table with stage meter and last report; detail as a timeline. Reviews: divided list (student, project, rating, comment, date); create form if the route is real.

**Guardrails that bind this phase** (audit section H, numbered as in `EXECUTION_ROADMAP.md`): 1, 2, 3, 4, 5, 6, 9, 10.

## F. Implementation tasks (do in order)

19.1. Gate check: D-13 (seeded applicants in each status), D-25 (reviews/create), D-26 (keyboard queue nav is front-end only).

19.2. Stage One facts: how accept/reject and reasons are posted; keep endpoints and field names.

19.3. Applicants: build the split view for 1024 px and up; below that, list then detail pages (no cropped split).

19.4. Decision bar: primary 'Terima', outline-danger 'Tolak' with a confirm dialog that requires a reason if the backend requires it.

19.5. Projects: table with stage meter and last report date; detail timeline if route exists.

19.6. Reviews: list and, if `/institution/reviews/create` is a real route after Phase 1, its form in the shared form layout; otherwise it 404s.

19.7. Verify with seeded data for every status; otherwise record BLOCKED per screen.

19.8. Update the tracker and report with I03-I05 before/after.

## G. Components

| Component | Source | Notes |
|---|---|---|
| Split view (list + detail + decision bar) | to build (Phase 3 primitives) | — |
| Table + stage meter | Phase 3 + `.sg-meter` | — |
| Divided list | Phase 3 | — |
| Confirm dialog | Phase 3 | — |
| Form | Phase 3 | — |

## H. Assets (Source A existing `public/`, Source B custom package, Source C usage)

> Policy AD-09. Three sources, none disposable: **A** existing assets in `public/`, **B** the custom package `student-go-assets/`, **C** `resources/` (shows what is really used). Every asset has one decision: Retain, Reuse, Adapt, Replace or Deprecate. Originals are never deleted, overwritten, moved or renamed by this phase; copying anything into a production directory happens only if a task above says so. Full inventory, rights and integration map: `student-go-docs/design-execution/ASSET_INVENTORY.md`. Evidence labels as in `DISCOVERY_REPORT.md` section 1.

### H.1 Source A: existing application assets that matter in this phase

| Asset | Decision | What happens in this phase | Evidence |
|---|---|---|---|
| None identified | n/a | No photographs known on these pages. | OBSERVED |

### H.2 Source B: custom package assets available for this phase

**Original SVG, brand, fonts, icons and CSS (made for this project; in the package; licences in `ASSET_INVENTORY.md` section 2)**

- `empty-states/no-applications.svg`, `workflows/project-status.svg` (explains stages), Lucide icons.

### H.3 Reconciliation for this phase

No overlap.

Overlap comparisons that touch this phase are in `ASSET_INVENTORY.md` section 11; where a Source A asset and a Source B asset serve the same purpose, that table gives the starting recommendation and this phase re-checks it against the real page.

### H.4 Native UI (no asset; plain HTML/CSS)

- Split pane.

### H.5 Do not touch in this phase

- Student profile photographs and uploads, institution/company logos, verification documents: never moved or re-encoded; never shown in a new public place (D-04, D-31).

## I. Dependencies

- **Phases:** hard — Phase 3 (Shared Shell & Components); soft — Phase 1 (Critical Fixes & Data Integrity), Phase 18 (Institution Project Management).
- **Decisions:** D-02 (Language policy and copy deck), D-13 (Seed data for blocked screens), D-25 (Defect fixes that change HTTP behaviour), D-26 (Features the audit proposes that do not exist today). A gating decision that is still OPEN in `DECISIONS_AND_OPEN_QUESTIONS.md` means the dependent tasks stop and the agent asks the user; it does not guess.
- **Packages:** No new composer or npm package. Anything new needs explicit approval first.
- **Data / environment:** Applicants, active projects with reports, reviews (D-13).

## J. Functionality preservation

The global rules in `CLAUDE.md` section 7 apply in full. For this phase specifically:

- Decision endpoints, verbs and required reasons unchanged.
- Filters `?status=` unchanged.
- Ownership rules unchanged.

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

- Accept and reject on the local DB with throwaway data; verify notifications are produced as before.
- Keyboard navigation of the queue.
- Narrow-screen behaviour (no cropped split).

*Test commands are not listed here because none could be verified without the repository. `CLAUDE.md` section 4 lists candidate commands that Stage One must confirm.*

## L. Acceptance criteria

The phase is COMPLETE only if every box can be checked with evidence in the phase report. Otherwise the status is PARTIAL (say which boxes failed) or BLOCKED (say what unblocks it).

- [ ] Applicants screen shows queue and detail together at >= 1024 px and sequentially below.
- [ ] The decision actions post to the same endpoints as the baseline.
- [ ] Projects table shows a stage meter and last report.
- [ ] `/institution/reviews/create` no longer renders a QueryException.
- [ ] Statuses use the shared vocabulary.
- [ ] Populated states verified or listed BLOCKED.

## M. Risks and blockers

| Risk or blocker | Mitigation / what unblocks it | Blocks the phase? |
|---|---|---|
| No seeded data | D-13; mark BLOCKED. | Partially |
| Decision flow has side effects (emails, notifications) | Test only on the local DB with mail log driver. | No |

## N. Expected deliverables

- Rewritten applications, projects, reviews views.
- Screenshots I03-I05.
- `student-go-docs/design-execution/PHASE_TRACKER.md`: set Phase 19 to COMPLETE, PARTIAL or BLOCKED with date, branch/commit (if the user asked for one) and a one-line note.
- A phase report in the chat: what changed (files), what was verified (with the evidence), what failed or was skipped and why, decisions needed from the user, and the exact next phase the roadmap allows. Then stop and wait; do not start another phase.
