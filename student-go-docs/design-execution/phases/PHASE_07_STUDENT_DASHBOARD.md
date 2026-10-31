# PHASE 07 — Student Dashboard

> Source of truth for status is `student-go-docs/design-execution/PHASE_TRACKER.md`. This file is the work order for exactly one phase. Read `CLAUDE.md` and `student-go-docs/design-execution/EXECUTION_ROADMAP.md` first, execute only this phase, update the tracker, report, then stop.
> Evidence labels: VERIFIED / OBSERVED / INFERRED / UNKNOWN / BLOCKED (definitions in `DISCOVERY_REPORT.md` section 1).

| Field | Value |
|---|---|
| Phase | 7 — Student Dashboard |
| Group | Student role |
| Hard dependencies (must be COMPLETE) | Phase 1 (Critical Fixes & Data Integrity), Phase 3 (Shared Shell & Components) |
| Soft dependencies (recommended first) | none |
| Decisions that gate this phase | D-03 Single source of truth for a student's display name, D-10 Which statistics help each role decide?, D-21 Charts on dashboards, D-12 A working database for baseline and QA |
| Effort estimate (planning guess, not a commitment) | M |
| Roles touched | Student |
| Audit references | S01 (+S26 menu state in Phase 3); J1, J6, J9, J10, J18; audit D3 |
| Tracker status at planning time | NOT_STARTED |

## A. Phase overview

**Goal.** Turn the dashboard from a hero-plus-tiles page into a work queue: compact header with the user's name, next steps (nearest deadline, pending applications), three recommended problems as list rows, a one-line network summary, and the recent applications list. No duplicated statistics.

**In scope**

- `/student/dashboard` only.
- Header, next-steps block, recommended problems, network summary, recent applications.

**Out of scope**

- Applications page (Phase 10), network (Phase 13), the avatar menu (Phase 3).
- Charts (D-21: none until data exists).

**Evidence basis.** The audit (OBSERVED, 2026-10-28, port 8001) plus a read-only look at the running app on 2026-10-30. No repository source was read when this file was written; every statement about templates, controllers, models, middleware and tests is UNKNOWN until Stage One (Phase 0) fills `DISCOVERY_REPORT.md` section 12.

## B. Pages and routes

| Route pattern | Role | Audit status | Evidence | Notes |
|---|---|---|---|---|
| /student/dashboard | Student | Inspected (desktop, 390, 768) | OBSERVED | — |

**Audit screenshot IDs for this phase** (audit section L; OBSERVED). Page data below is quoted or summarised from the audit; the screenshots themselves are in the PDF, not in this repository.

| ID | Page | URL | Keep (audit) | Design direction (audit) | Backlog |
|---|---|---|---|---|---|
| S01 | Dashboard mahasiswa | /student/dashboard | Urutan yang menempatkan aksi utama di atas dan daftar lamaran terbaru. | Dashboard sebagai antrean kerja: header ringkas, blok “Langkah berikutnya” (tenggat terdekat, lamaran menunggu), daftar 3 proyek rekomendasi, ringkasan jaringan satu baris. Tanpa foto, tanpa tile, tanpa metrik ganda. | J1, J6, J9, J10, J18 |

## C. Existing implementation

- [OBSERVED] Page height about 3,048 px; 370 px photo hero greets the user before any data; avatar 'DH' under the name 'Julia Suryanto' (J1); 'Jaringan Saya' with three pastel tiles (0/0/10); four recommendation cards each with a solid purple 'Hubungkan' button (white on purple-500 measured 4.12:1, below AA); 'Statistik Aktivitas' shows the same four numbers as tiles and as progress bars; statuses in English ('Rejected', 'Pending').
- [OBSERVED] Test account data: 2 applications, 3 notifications, 0 active projects, 0 connections.
- [OBSERVED] Good: the action order (search projects, view applications) is at the top and the recent applications list with status badges is useful.

**Repository facts for this phase: UNKNOWN until Stage One.** Locate and record in `DISCOVERY_REPORT.md` section 12 before editing: the student dashboard controller and view; how recommendations are chosen; what the network numbers count; where 'next deadline' could come from (applications, problems, wishlist).

## D. Problems found (audit findings)

| Backlog ID | Finding | Evidence |
|---|---|---|
| J1 (P0) | Name and initials mismatch (fixed in Phase 1; verify here). | OBSERVED |
| J6 (P1) | Photo hero pushes work below the fold; 85-423 KB; low contrast before the image loads. | OBSERVED |
| J9 (P1) | Duplicate statistics; pastel zero tiles. | OBSERVED |
| J10 (P1) | 'Hubungkan' button 4.12:1. | OBSERVED |
| J18 (P2) | Recommendation cards instead of rows. | OBSERVED |
| J8 (P1) | English status words. | OBSERVED |

## E. Approved design direction

**Approved by the user (binding)**

- Hybrid direction (AD-04): Ruang Kerja workbench foundation; Catatan Lapangan editorial layer only where reading and storytelling matter. This phase uses none of it unless stated below.
- Principles (AD-05) and functional preservation (AD-06). Tokens, fonts and components below come from the asset package and are PROPOSED until D-17 is approved; existing assets are reconciled under AD-09 (section H); D-16 records that reference documents B and C were not found.

**Proposed for this phase** (audit sections I and L, adapted; status PROPOSED until the user approves the gating decisions)

- Header with name and nearest deadline; 'Langkah berikutnya' queue; three recommended problems as rows; one-line network summary ('0 koneksi · 10 saran'); recent applications as a short table. No photo, no tiles, no duplicate metrics (audit D3/L2).

**Guardrails that bind this phase** (audit section H, numbered as in `EXECUTION_ROADMAP.md`): 1, 2, 3, 4, 5, 6, 7, 9, 10.

## F. Implementation tasks (do in order)

7.1. Gate check: D-03 settled and Phase 1 COMPLETE (names), D-10 (which numbers are shown).

7.2. Read the controller to see what data exists for 'next steps' (nearest deadline, pending applications). Do not add a query that needs new tables; use existing relations only.

7.3. Build the header and queue with the Phase 3 components; empty queue shows the role-specific next step ('Telusuri masalah KKN').

7.4. Recommended problems: 3 rows using the Phase 3 list component with a quiet secondary 'Lihat' action; remove the solid purple button; 'Hubungkan' belongs to the network page (Phase 13).

7.5. Network summary as a single `.sg-summary` line; recent applications as a compact table with status badges from the shared mapping.

7.6. Remove tiles, the duplicate statistics block and any chart; if a chart is kept it needs at least five data points (D-21).

7.7. Verify at six widths with the test account (2 applications) and with an empty account (no applications).

7.8. Update the tracker and report with S01 before/after.

## G. Components

| Component | Source | Notes |
|---|---|---|
| Page header with name | Phase 3 | Greeting is plain text. |
| Next-steps list | Phase 3 list | Deadlines and pending items. |
| Problem rows | Phase 3 list | Replaces cards. |
| Summary line | package `.sg-summary` | Replaces tiles. |
| Applications table + status badge | Phase 3 | Shared vocabulary. |

## H. Assets (Source A existing `public/`, Source B custom package, Source C usage)

> Policy AD-09. Three sources, none disposable: **A** existing assets in `public/`, **B** the custom package `student-go-assets/`, **C** `resources/` (shows what is really used). Every asset has one decision: Retain, Reuse, Adapt, Replace or Deprecate. Originals are never deleted, overwritten, moved or renamed by this phase; copying anything into a production directory happens only if a task above says so. Full inventory, rights and integration map: `student-go-docs/design-execution/ASSET_INVENTORY.md`. Evidence labels as in `DISCOVERY_REPORT.md` section 1.

### H.1 Source A: existing application assets that matter in this phase

| Asset | Decision | What happens in this phase | Evidence |
|---|---|---|---|
| `dashboard-student2.jpeg` | Deprecate (usage) / Reuse (elsewhere) | Stops being the dashboard hero; the file is kept for documentary reuse on About or in a story. | OBSERVED (audit) |
| `dashboard-student.jpg`, `dashboard-student3.jpg` | Retain | Possible dashboard variants. Stage One confirms whether anything references them; not deleted either way. | INFERRED |
| Student avatar (profile photo) | Retain | Header avatar shows the real photo if one exists, initials otherwise (J1 fix from Phase 1). | UNKNOWN / BLOCKED |

### H.2 Source B: custom package assets available for this phase

**Original SVG, brand, fonts, icons and CSS (made for this project; in the package; licences in `ASSET_INVENTORY.md` section 2)**

- Lucide icons (calendar, clock, map-pin, users); `empty-states/no-applications.svg` for the empty queue.

### H.3 Reconciliation for this phase

No photograph on a task page (guardrail 1). The deprecated hero is a usage decision, not a file removal.

Overlap comparisons that touch this phase are in `ASSET_INVENTORY.md` section 11; where a Source A asset and a Source B asset serve the same purpose, that table gives the starting recommendation and this phase re-checks it against the real page.

### H.4 Native UI (no asset; plain HTML/CSS)

- Text, rows, dividers.

### H.5 Do not touch in this phase

- Student profile photographs and uploads, institution/company logos, verification documents: never moved or re-encoded; never shown in a new public place (D-04, D-31).
- `dashboard-student*.jpg|jpeg` stay on disk.

## I. Dependencies

- **Phases:** hard — Phase 1 (Critical Fixes & Data Integrity), Phase 3 (Shared Shell & Components); soft — none.
- **Decisions:** D-03 (Single source of truth for a student's display name), D-10 (Which statistics help each role decide?), D-21 (Charts on dashboards), D-12 (A working database for baseline and QA). A gating decision that is still OPEN in `DECISIONS_AND_OPEN_QUESTIONS.md` means the dependent tasks stop and the agent asks the user; it does not guess.
- **Packages:** No new composer or npm package. Anything new needs explicit approval first.
- **Data / environment:** Student with applications and one with none (D-13).

## J. Functionality preservation

The global rules in `CLAUDE.md` section 7 apply in full. For this phase specifically:

- Every link from the dashboard (search projects, applications, individual problems, profile) keeps its target.
- Connect action keeps its endpoint (moves to Phase 13 UI only if the user wants it removed here; default: keep a secondary link).
- Recommendation logic unchanged.

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

- Student with 2 applications; student with 0; student with 0 notifications.
- No element shorter than 24 px touch target on mobile; keyboard order is header, queue, recommendations.
- Compare data shown with the underlying records (counts equal queries).

*Test commands are not listed here because none could be verified without the repository. `CLAUDE.md` section 4 lists candidate commands that Stage One must confirm.*

## L. Acceptance criteria

The phase is COMPLETE only if every box can be checked with evidence in the phase report. Otherwise the status is PARTIAL (say which boxes failed) or BLOCKED (say what unblocks it).

- [ ] The h1 and the first action are within the first 160 px; no photo hero.
- [ ] No number appears twice; no zero tile; no trend chip.
- [ ] Every status uses the shared vocabulary (no 'Rejected'/'Pending').
- [ ] No control has contrast below 4.5:1 (the 'Hubungkan' button no longer appears here).
- [ ] Name and initials equal the account's single display name.
- [ ] An empty account shows a clear next-step empty state.

## M. Risks and blockers

| Risk or blocker | Mitigation / what unblocks it | Blocks the phase? |
|---|---|---|
| Phase 1 not done, so names still mismatch | Hard dependency; do not start. | Yes |
| Next deadline data not available | Show only what exists; do not invent. | No |
| Zero-data account hides real layout issues | Test with the 2-application account too. | No |

## N. Expected deliverables

- Rewritten dashboard view.
- Screenshots before/after (S01).
- `student-go-docs/design-execution/PHASE_TRACKER.md`: set Phase 7 to COMPLETE, PARTIAL or BLOCKED with date, branch/commit (if the user asked for one) and a one-line note.
- A phase report in the chat: what changed (files), what was verified (with the evidence), what failed or was skipped and why, decisions needed from the user, and the exact next phase the roadmap allows. Then stop and wait; do not start another phase.
