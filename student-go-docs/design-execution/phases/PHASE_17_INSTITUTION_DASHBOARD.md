# PHASE 17 — Institution Dashboard

> Source of truth for status is `student-go-docs/design-execution/PHASE_TRACKER.md`. This file is the work order for exactly one phase. Read `CLAUDE.md` and `student-go-docs/design-execution/EXECUTION_ROADMAP.md` first, execute only this phase, update the tracker, report, then stop.
> Evidence labels: VERIFIED / OBSERVED / INFERRED / UNKNOWN / BLOCKED (definitions in `DISCOVERY_REPORT.md` section 1).

| Field | Value |
|---|---|
| Phase | 17 — Institution Dashboard |
| Group | Institution role |
| Hard dependencies (must be COMPLETE) | Phase 3 (Shared Shell & Components) |
| Soft dependencies (recommended first) | Phase 1 (Critical Fixes & Data Integrity) |
| Decisions that gate this phase | D-09 Verification process for institutions and companies, D-10 Which statistics help each role decide?, D-13 Seed data for blocked screens, D-21 Charts on dashboards |
| Effort estimate (planning guess, not a commitment) | S |
| Roles touched | Institution |
| Audit references | I01; J8, J9, J16; audit D10 |
| Tracker status at planning time | NOT_STARTED |

## A. Phase overview

**Goal.** Make the institution dashboard a work queue (verification status, applicants awaiting a decision, running projects, draft problems), without invented trends and with one primary action.

**In scope**

- `/institution/dashboard`.

**Out of scope**

- Review screens (Phase 19), problem management (Phase 18), verification page (Phase 20).

**Evidence basis.** The audit (OBSERVED, 2026-10-28, port 8001) plus a read-only look at the running app on 2026-10-30. No repository source was read when this file was written; every statement about templates, controllers, models, middleware and tests is UNKNOWN until Stage One (Phase 0) fills `DISCOVERY_REPORT.md` section 12.

## B. Pages and routes

| Route pattern | Role | Audit status | Evidence | Notes |
|---|---|---|---|---|
| /institution/dashboard | Institution | Inspected | OBSERVED | — |

**Audit screenshot IDs for this phase** (audit section L; OBSERVED). Page data below is quoted or summarised from the audit; the screenshots themselves are in the PDF, not in this repository.

| ID | Page | URL | Keep (audit) | Design direction (audit) | Backlog |
|---|---|---|---|---|---|
| I01 | Dashboard institusi | /institution/dashboard | Header tanpa foto, banner verifikasi, tautan “Lihat Semua”. | Dashboard instansi sebagai antrean: (1) status verifikasi, (2) pelamar yang menunggu keputusan, (3) proyek berjalan dengan laporan yang jatuh tempo, (4) masalah draft. | J9, J8, J16 |

## C. Existing implementation

- [OBSERVED] The most consistent role: no hero, plain header, verification banner at the right priority, 'Lihat Semua' links; red '↓ 0.0% dari bulan lalu' on a new account; three quick actions in solid blue, dark yellow and green; all-zero stat tiles; English statuses.
- [OBSERVED] Test account: 0 problems, applications, projects, reviews; verification 'Menunggu Verifikasi'.

**Repository facts for this phase: UNKNOWN until Stage One.** Locate and record in `DISCOVERY_REPORT.md` section 12 before editing: institution dashboard controller/view and what the tiles and trend compute.

## D. Problems found (audit findings)

| Backlog ID | Finding | Evidence |
|---|---|---|
| J9 (P1) | Trend indicator on zero data; all-zero tiles. | OBSERVED |
| J16 (P2) | Three competing button colours. | OBSERVED |
| J8 (P1) | English status words. | OBSERVED |

## E. Approved design direction

**Approved by the user (binding)**

- Hybrid direction (AD-04): Ruang Kerja workbench foundation; Catatan Lapangan editorial layer only where reading and storytelling matter. This phase uses none of it unless stated below.
- Principles (AD-05) and functional preservation (AD-06). Tokens, fonts and components below come from the asset package and are PROPOSED until D-17 is approved; existing assets are reconciled under AD-09 (section H); D-16 records that reference documents B and C were not found.

**Proposed for this phase** (audit sections I and L, adapted; status PROPOSED until the user approves the gating decisions)

- Queue: (1) verification status banner, (2) applicants awaiting a decision, (3) running projects with the next report due, (4) draft problems. One primary action ('Buat masalah baru'); other quick actions as links. Trend hidden until two periods of data exist. This page is the template for the other roles' dashboards (audit I).

**Guardrails that bind this phase** (audit section H, numbered as in `EXECUTION_ROADMAP.md`): 1, 2, 3, 4, 5, 6, 9, 10.

## F. Implementation tasks (do in order)

17.1. Gate check: D-10 (stats per role), D-13 (populated states), D-09 (verification copy).

17.2. Keep the verification banner; restyle with status vocabulary and honest copy (no promise beyond the real process).

17.3. Build the queue sections using Phase 3 list components; hide a section with nothing actionable but keep the empty-state next step.

17.4. Replace the three coloured quick actions with one primary and two tertiary links.

17.5. Remove trend chips and zero tiles; replace with `.sg-summary` line.

17.6. Verify with the empty account and (D-13) a populated one.

17.7. Update the tracker and report with I01 before/after.

## G. Components

| Component | Source | Notes |
|---|---|---|
| Verification banner | Phase 3 + status | — |
| Queue lists | Phase 3 | — |
| Summary line | package | — |
| Primary/tertiary buttons | package | — |

## H. Assets (Source A existing `public/`, Source B custom package, Source C usage)

> Policy AD-09. Three sources, none disposable: **A** existing assets in `public/`, **B** the custom package `student-go-assets/`, **C** `resources/` (shows what is really used). Every asset has one decision: Retain, Reuse, Adapt, Replace or Deprecate. Originals are never deleted, overwritten, moved or renamed by this phase; copying anything into a production directory happens only if a task above says so. Full inventory, rights and integration map: `student-go-docs/design-execution/ASSET_INVENTORY.md`. Evidence labels as in `DISCOVERY_REPORT.md` section 1.

### H.1 Source A: existing application assets that matter in this phase

| Asset | Decision | What happens in this phase | Evidence |
|---|---|---|---|
| Institution logo (upload) | Retain | Shown if present; initials otherwise. | UNKNOWN / BLOCKED |
| `Photographs` | n/a | The audit says the institution dashboard has no hero; nothing to deprecate. | OBSERVED |

### H.2 Source B: custom package assets available for this phase

**Original SVG, brand, fonts, icons and CSS (made for this project; in the package; licences in `ASSET_INVENTORY.md` section 2)**

- Lucide icons; `workflows/institution-journey.svg` can illustrate the first-run empty dashboard.

### H.3 Reconciliation for this phase

The institution dashboard is the template for the calm workbench; no Source A asset is involved.

Overlap comparisons that touch this phase are in `ASSET_INVENTORY.md` section 11; where a Source A asset and a Source B asset serve the same purpose, that table gives the starting recommendation and this phase re-checks it against the real page.

### H.4 Native UI (no asset; plain HTML/CSS)

- Lists.

### H.5 Do not touch in this phase

- Student profile photographs and uploads, institution/company logos, verification documents: never moved or re-encoded; never shown in a new public place (D-04, D-31).

## I. Dependencies

- **Phases:** hard — Phase 3 (Shared Shell & Components); soft — Phase 1 (Critical Fixes & Data Integrity).
- **Decisions:** D-09 (Verification process for institutions and companies), D-10 (Which statistics help each role decide?), D-13 (Seed data for blocked screens), D-21 (Charts on dashboards). A gating decision that is still OPEN in `DECISIONS_AND_OPEN_QUESTIONS.md` means the dependent tasks stop and the agent asks the user; it does not guess.
- **Packages:** No new composer or npm package. Anything new needs explicit approval first.
- **Data / environment:** Institution accounts with and without data.

## J. Functionality preservation

The global rules in `CLAUDE.md` section 7 apply in full. For this phase specifically:

- Links to problems, applications, projects, reviews and verification keep their targets.
- Verification banner logic unchanged.

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

- Empty and populated dashboards; verification pending and verified variants.

*Test commands are not listed here because none could be verified without the repository. `CLAUDE.md` section 4 lists candidate commands that Stage One must confirm.*

## L. Acceptance criteria

The phase is COMPLETE only if every box can be checked with evidence in the phase report. Otherwise the status is PARTIAL (say which boxes failed) or BLOCKED (say what unblocks it).

- [ ] No trend chip appears without two periods of data.
- [ ] No all-zero tile grid.
- [ ] Exactly one primary button.
- [ ] Statuses use the shared vocabulary.
- [ ] The verification state is the first block when unverified.

## M. Risks and blockers

| Risk or blocker | Mitigation / what unblocks it | Blocks the phase? |
|---|---|---|
| Populated state BLOCKED (0 records) | D-13. | Partially |

## N. Expected deliverables

- Rewritten institution dashboard view.
- Screenshot I01.
- `student-go-docs/design-execution/PHASE_TRACKER.md`: set Phase 17 to COMPLETE, PARTIAL or BLOCKED with date, branch/commit (if the user asked for one) and a one-line note.
- A phase report in the chat: what changed (files), what was verified (with the evidence), what failed or was skipped and why, decisions needed from the user, and the exact next phase the roadmap allows. Then stop and wait; do not start another phase.
