# PHASE 15 — Jobs

> Source of truth for status is `student-go-docs/design-execution/PHASE_TRACKER.md`. This file is the work order for exactly one phase. Read `CLAUDE.md` and `student-go-docs/design-execution/EXECUTION_ROADMAP.md` first, execute only this phase, update the tracker, report, then stop.
> Evidence labels: VERIFIED / OBSERVED / INFERRED / UNKNOWN / BLOCKED (definitions in `DISCOVERY_REPORT.md` section 1).

| Field | Value |
|---|---|
| Phase | 15 — Jobs |
| Group | Student role |
| Hard dependencies (must be COMPLETE) | Phase 3 (Shared Shell & Components) |
| Soft dependencies (recommended first) | Phase 8 (Student Project Discovery) |
| Decisions that gate this phase | D-07 Product scope: KKN-only or jobs/internships as a main flow, D-13 Seed data for blocked screens, D-02 Language policy and copy deck |
| Effort estimate (planning guess, not a commitment) | S |
| Roles touched | Student |
| Audit references | S14, S15, S16; J6, J8, J17, J18, J24; audit D9 |
| Tracker status at planning time | NOT_STARTED |

## A. Phase overview

**Goal.** Give student jobs, alerts and saved jobs the same list-plus-filter pattern as project discovery, so students learn one pattern; keep the screens honest while no job data exists.

**In scope**

- `/student/jobs`, `/student/jobs/alerts`, `/student/jobs/saved`.

**Out of scope**

- Company-side job management (Phase 23).

**Evidence basis.** The audit (OBSERVED, 2026-10-28, port 8001) plus a read-only look at the running app on 2026-10-30. No repository source was read when this file was written; every statement about templates, controllers, models, middleware and tests is UNKNOWN until Stage One (Phase 0) fills `DISCOVERY_REPORT.md` section 12.

## B. Pages and routes

| Route pattern | Role | Audit status | Evidence | Notes |
|---|---|---|---|---|
| /student/jobs | Student | Inspected (empty) | OBSERVED | — |
| /student/jobs/alerts | Student | Inspected + measured | OBSERVED | — |
| /student/jobs/saved | Student | Inspected + measured | OBSERVED | Only page using fa- icons (2). |

**Audit screenshot IDs for this phase** (audit section L; OBSERVED). Page data below is quoted or summarised from the audit; the screenshots themselves are in the PDF, not in this repository.

| ID | Page | URL | Keep (audit) | Design direction (audit) | Backlog |
|---|---|---|---|---|---|
| S14 | Lowongan Kerja & Magang | /student/jobs | Struktur filter + hasil + urutkan. | Sama dengan Jelajahi Proyek (daftar baris + filter), agar mahasiswa tidak perlu mempelajari dua pola. | J6, J8, J18 |
| S15 | Job Alerts | /student/jobs/alerts | Header polos tanpa foto. | Halaman pengaturan sederhana: daftar alert (kata kunci, lokasi, SDG, frekuensi) dengan toggle aktif. | J8, J17 |
| S16 | Lowongan Tersimpan | /student/jobs/saved | Teks empty state dan CTA “Cari Lowongan”. | Daftar baris yang sama dengan halaman Lowongan, dengan aksi “Hapus dari tersimpan”. | J17, J24 |

## C. Existing implementation

- [OBSERVED] Jobs: stock office photo header, '0 Lowongan Aktif / 0 Perusahaan', 17 SDG checkboxes labelled only 'SDG 1...SDG 17' (targets below 24 px). All data empty, so list layout cannot be judged (BLOCKED).
- [OBSERVED] Alerts: plain header (kept), empty state. Saved: empty state with 'Cari Lowongan'; the only page using Font Awesome (2 icons).

**Repository facts for this phase: UNKNOWN until Stage One.** Locate and record in `DISCOVERY_REPORT.md` section 12 before editing: jobs controllers/views, alert model and fields, saved-job toggle, SDG list source and names.

## D. Problems found (audit findings)

| Backlog ID | Finding | Evidence |
|---|---|---|
| J6 (P1) | Stock photo header. | OBSERVED |
| J8 (P1) | Mixed language and 'Jobs' in nav. | OBSERVED |
| J18 (P2) | Different pattern from discovery. | OBSERVED |
| J24 (P2) | Font Awesome used only here. | OBSERVED |
| a11y (D9) | 17 SDG checkboxes labelled by number only, below 24 px target. | OBSERVED |
| BLOCKED | No job records. | BLOCKED |

## E. Approved design direction

**Approved by the user (binding)**

- Hybrid direction (AD-04): Ruang Kerja workbench foundation; Catatan Lapangan editorial layer only where reading and storytelling matter. This phase uses none of it unless stated below.
- Principles (AD-05) and functional preservation (AD-06). Tokens, fonts and components below come from the asset package and are PROPOSED until D-17 is approved; existing assets are reconciled under AD-09 (section H); D-16 records that reference documents B and C were not found.

**Proposed for this phase** (audit sections I and L, adapted; status PROPOSED until the user approves the gating decisions)

- Same list + filter pattern as Phase 8 (rows, filters, one search); alerts as a simple settings list (kata kunci, lokasi, SDG, frekuensi) with an active toggle; saved jobs as the same rows with 'Hapus dari tersimpan'. SDG filter shows names with swatches and 44 px touch targets (24 px minimum per WCAG 2.2).

**Guardrails that bind this phase** (audit section H, numbered as in `EXECUTION_ROADMAP.md`): 1, 2, 3, 6, 7, 9, 10.

## F. Implementation tasks (do in order)

15.1. Gate check: D-07 (jobs priority), D-13 (job seeds for visual verification).

15.2. Reuse the Phase 8 list/filter components; map job fields to the row (Posisi · Perusahaan · Lokasi · SDG · Tenggat).

15.3. SDG filter: list of 17 names (Indonesian) with visible labels linked to inputs, target size >= 24 px (44 px on touch).

15.4. Alerts page: list with toggle and edit/delete using the existing endpoints; empty state text specific.

15.5. Saved page: same rows, remove action; replace the two Font Awesome icons with sprite icons.

15.6. Verify empty states now and populated states when seeds exist (D-13), else record BLOCKED.

15.7. Update the tracker and report with S14-S16 before/after.

## G. Components

| Component | Source | Notes |
|---|---|---|
| List + filter (Phase 8 patterns) | Phase 3 | — |
| SDG checklist with names | to build | Shared with Phase 22. |
| Toggle | Phase 3 | — |
| Empty state | Phase 3 | — |

## H. Assets (Source A existing `public/`, Source B custom package, Source C usage)

> Policy AD-09. Three sources, none disposable: **A** existing assets in `public/`, **B** the custom package `student-go-assets/`, **C** `resources/` (shows what is really used). Every asset has one decision: Retain, Reuse, Adapt, Replace or Deprecate. Originals are never deleted, overwritten, moved or renamed by this phase; copying anything into a production directory happens only if a task above says so. Full inventory, rights and integration map: `student-go-docs/design-execution/ASSET_INVENTORY.md`. Evidence labels as in `DISCOVERY_REPORT.md` section 1.

### H.1 Source A: existing application assets that matter in this phase

| Asset | Decision | What happens in this phase | Evidence |
|---|---|---|---|
| One of `company1-5.jpg` / `company-profile.jpg` (stock office photo on Lowongan) | Deprecate (usage) | The stock hero leaves the jobs pages (audit: unrelated stock). Files stay. | OBSERVED (audit); file mapping INFERRED |
| Font Awesome (used 2 times, jobs pages) | Deprecate | Replace the two icons with Lucide, then drop the CDN (D-20). | OBSERVED |

### H.2 Source B: custom package assets available for this phase

**Original SVG, brand, fonts, icons and CSS (made for this project; in the package; licences in `ASSET_INVENTORY.md` section 2)**

- `empty-states/no-saved-projects.svg`, `no-search-results.svg`; Lucide icons (briefcase, bell, bookmark).

### H.3 Reconciliation for this phase

No photo on task pages.

Overlap comparisons that touch this phase are in `ASSET_INVENTORY.md` section 11; where a Source A asset and a Source B asset serve the same purpose, that table gives the starting recommendation and this phase re-checks it against the real page.

### H.4 Native UI (no asset; plain HTML/CSS)

- Lists.

### H.5 Do not touch in this phase

- Student profile photographs and uploads, institution/company logos, verification documents: never moved or re-encoded; never shown in a new public place (D-04, D-31).

## I. Dependencies

- **Phases:** hard — Phase 3 (Shared Shell & Components); soft — Phase 8 (Student Project Discovery).
- **Decisions:** D-07 (Product scope: KKN-only or jobs/internships as a main flow), D-13 (Seed data for blocked screens), D-02 (Language policy and copy deck). A gating decision that is still OPEN in `DECISIONS_AND_OPEN_QUESTIONS.md` means the dependent tasks stop and the agent asks the user; it does not guess.
- **Packages:** No new composer or npm package. Anything new needs explicit approval first.
- **Data / environment:** Job records (D-13); none exist today.

## J. Functionality preservation

The global rules in `CLAUDE.md` section 7 apply in full. For this phase specifically:

- Alert create/update/delete and save/unsave endpoints unchanged.
- Filter parameter names unchanged.

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

- Empty states at six widths; keyboard operation of the SDG list; screen-reader labels for each SDG checkbox.

*Test commands are not listed here because none could be verified without the repository. `CLAUDE.md` section 4 lists candidate commands that Stage One must confirm.*

## L. Acceptance criteria

The phase is COMPLETE only if every box can be checked with evidence in the phase report. Otherwise the status is PARTIAL (say which boxes failed) or BLOCKED (say what unblocks it).

- [ ] No stock photo; headers are the standard page header.
- [ ] SDG checkboxes show full Indonesian names and meet the target size.
- [ ] Font Awesome is no longer needed anywhere.
- [ ] The list and filter layout is the same as discovery.
- [ ] Populated state verified or recorded BLOCKED.

## M. Risks and blockers

| Risk or blocker | Mitigation / what unblocks it | Blocks the phase? |
|---|---|---|
| No job data | BLOCKED for populated verification. | Partially |
| Scope decision D-07 may deprioritise | Phase can be deferred. | No |

## N. Expected deliverables

- Rewritten jobs, alerts and saved views.
- Screenshots S14-S16.
- `student-go-docs/design-execution/PHASE_TRACKER.md`: set Phase 15 to COMPLETE, PARTIAL or BLOCKED with date, branch/commit (if the user asked for one) and a one-line note.
- A phase report in the chat: what changed (files), what was verified (with the evidence), what failed or was skipped and why, decisions needed from the user, and the exact next phase the roadmap allows. Then stop and wait; do not start another phase.
