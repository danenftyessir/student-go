# PHASE 21 — Company Dashboard

> Source of truth for status is `student-go-docs/design-execution/PHASE_TRACKER.md`. This file is the work order for exactly one phase. Read `CLAUDE.md` and `student-go-docs/design-execution/EXECUTION_ROADMAP.md` first, execute only this phase, update the tracker, report, then stop.
> Evidence labels: VERIFIED / OBSERVED / INFERRED / UNKNOWN / BLOCKED (definitions in `DISCOVERY_REPORT.md` section 1).

| Field | Value |
|---|---|
| Phase | 21 — Company Dashboard |
| Group | Company role |
| Hard dependencies (must be COMPLETE) | Phase 3 (Shared Shell & Components) |
| Soft dependencies (recommended first) | Phase 1 (Critical Fixes & Data Integrity) |
| Decisions that gate this phase | D-02 Language policy and copy deck, D-10 Which statistics help each role decide?, D-13 Seed data for blocked screens, D-21 Charts on dashboards |
| Effort estimate (planning guess, not a commitment) | S |
| Roles touched | Company |
| Audit references | C01; J1, J6, J8, J9, J16; audit D11 |
| Tracker status at planning time | NOT_STARTED |

## A. Phase overview

**Goal.** Replace the hero-and-empty-charts company dashboard with an ATS-style queue: active jobs table, new applicants by stage, saved talents; Indonesian navigation and labels.

**In scope**

- `/company/dashboard`.

**Out of scope**

- Jobs, applicants and talents pages (Phases 22-24).

**Evidence basis.** The audit (OBSERVED, 2026-10-28, port 8001) plus a read-only look at the running app on 2026-10-30. No repository source was read when this file was written; every statement about templates, controllers, models, middleware and tests is UNKNOWN until Stage One (Phase 0) fills `DISCOVERY_REPORT.md` section 12.

## B. Pages and routes

| Route pattern | Role | Audit status | Evidence | Notes |
|---|---|---|---|---|
| /company/dashboard | Company | Inspected | OBSERVED | — |

**Audit screenshot IDs for this phase** (audit section L; OBSERVED). Page data below is quoted or summarised from the audit; the screenshots themselves are in the PDF, not in this repository.

| ID | Page | URL | Keep (audit) | Design direction (audit) | Backlog |
|---|---|---|---|---|---|
| C01 | Dashboard perusahaan | /company/dashboard | Empty state “Belum Ada Lamaran” dengan CTA. | Dashboard ATS: lowongan aktif (tabel), pelamar baru per tahap, talenta tersimpan; aksen oranye hanya pada badge peran. | J1, J6, J8, J9, J16 |

## C. Existing implementation

- [OBSERVED] Hero photo (stock office) on every company page; dashboard draws two empty charts, one with a decimal 0-1.0 axis for counts; green '↗ 0% bulan lalu' trend chip; 12 talent suggestions; empty state 'Belum Ada Lamaran' with a CTA (kept).
- [OBSERVED] Entire company navigation is English (Talents, Jobs, Applications, Saved Talents); accent colours amber/orange avatar and purple filters.

**Repository facts for this phase: UNKNOWN until Stage One.** Locate and record in `DISCOVERY_REPORT.md` section 12 before editing: company dashboard controller/view, the chart code and library, what suggestions are.

## D. Problems found (audit findings)

| Backlog ID | Finding | Evidence |
|---|---|---|
| J6 (P1) | Hero photo. | OBSERVED |
| J9 (P1) | Empty charts, trend chip on zero data. | OBSERVED |
| J8 (P1) | English UI. | OBSERVED |
| J16 (P2) | Accent colours. | OBSERVED |
| J1 (P0) | Talent names mismatch (Phase 1). | OBSERVED |

## E. Approved design direction

**Approved by the user (binding)**

- Hybrid direction (AD-04): Ruang Kerja workbench foundation; Catatan Lapangan editorial layer only where reading and storytelling matter. This phase uses none of it unless stated below.
- Principles (AD-05) and functional preservation (AD-06). Tokens, fonts and components below come from the asset package and are PROPOSED until D-17 is approved; existing assets are reconciled under AD-09 (section H); D-16 records that reference documents B and C were not found.

**Proposed for this phase** (audit sections I and L, adapted; status PROPOSED until the user approves the gating decisions)

- ATS dashboard: active jobs (table), new applicants by stage, saved talents, one primary action ('Pasang lowongan'). No charts until five or more data points (D-21). Orange is allowed only on the role badge.

**Guardrails that bind this phase** (audit section H, numbered as in `EXECUTION_ROADMAP.md`): 1, 2, 3, 4, 5, 6, 9, 10.

## F. Implementation tasks (do in order)

21.1. Gate check: D-10, D-21, D-02.

21.2. Rebuild with the Phase 3 header and list/table components; keep the empty state 'Belum Ada Lamaran' pattern with a specific CTA.

21.3. Remove charts and the trend chip; use a summary line.

21.4. Talent suggestions: only if the existing feature exists, as list rows with the name from the single source (Phase 1).

21.5. Verify with the empty account and (D-13) a populated one.

21.6. Update the tracker and report with C01 before/after.

## G. Components

| Component | Source | Notes |
|---|---|---|
| Jobs table | Phase 3 | — |
| Applicants-by-stage list | Phase 3 list + status | — |
| Summary line | package | — |
| Empty states | Phase 3 | — |

## H. Assets (Source A existing `public/`, Source B custom package, Source C usage)

> Policy AD-09. Three sources, none disposable: **A** existing assets in `public/`, **B** the custom package `student-go-assets/`, **C** `resources/` (shows what is really used). Every asset has one decision: Retain, Reuse, Adapt, Replace or Deprecate. Originals are never deleted, overwritten, moved or renamed by this phase; copying anything into a production directory happens only if a task above says so. Full inventory, rights and integration map: `student-go-docs/design-execution/ASSET_INVENTORY.md`. Evidence labels as in `DISCOVERY_REPORT.md` section 1.

### H.1 Source A: existing application assets that matter in this phase

| Asset | Decision | What happens in this phase | Evidence |
|---|---|---|---|
| `company1-5.jpg`, `company-profile.jpg` (stock office photo on every company page) | Deprecate (usage) / Retain (files) | The company dashboard starts with the standard page header; no photo. Files stay until Phases 21-25 are verified and the user authorises archiving. | OBSERVED (audit); mapping INFERRED |

### H.2 Source B: custom package assets available for this phase

**Original SVG, brand, fonts, icons and CSS (made for this project; in the package; licences in `ASSET_INVENTORY.md` section 2)**

- Lucide icons; `workflows/company-journey.svg` for the first-run state.

### H.3 Reconciliation for this phase

Overlap 7: no photo.

Overlap comparisons that touch this phase are in `ASSET_INVENTORY.md` section 11; where a Source A asset and a Source B asset serve the same purpose, that table gives the starting recommendation and this phase re-checks it against the real page.

### H.4 Native UI (no asset; plain HTML/CSS)

- Table.

### H.5 Do not touch in this phase

- Student profile photographs and uploads, institution/company logos, verification documents: never moved or re-encoded; never shown in a new public place (D-04, D-31).

## I. Dependencies

- **Phases:** hard — Phase 3 (Shared Shell & Components); soft — Phase 1 (Critical Fixes & Data Integrity).
- **Decisions:** D-02 (Language policy and copy deck), D-10 (Which statistics help each role decide?), D-13 (Seed data for blocked screens), D-21 (Charts on dashboards). A gating decision that is still OPEN in `DECISIONS_AND_OPEN_QUESTIONS.md` means the dependent tasks stop and the agent asks the user; it does not guess.
- **Packages:** No new composer or npm package. Anything new needs explicit approval first.
- **Data / environment:** Company with jobs and applicants (D-13).

## J. Functionality preservation

The global rules in `CLAUDE.md` section 7 apply in full. For this phase specifically:

- Links to talents, jobs, applications and saved talents keep targets.
- Suggestion logic unchanged.

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

- Empty and populated dashboards; navigation labels in Indonesian.

*Test commands are not listed here because none could be verified without the repository. `CLAUDE.md` section 4 lists candidate commands that Stage One must confirm.*

## L. Acceptance criteria

The phase is COMPLETE only if every box can be checked with evidence in the phase report. Otherwise the status is PARTIAL (say which boxes failed) or BLOCKED (say what unblocks it).

- [ ] No hero photo, no empty chart, no trend chip.
- [ ] All visible labels are Indonesian.
- [ ] One primary action.
- [ ] Talent names are consistent with the profile pages.
- [ ] Empty state has a specific next step.

## M. Risks and blockers

| Risk or blocker | Mitigation / what unblocks it | Blocks the phase? |
|---|---|---|
| Populated state BLOCKED | D-13. | Partially |

## N. Expected deliverables

- Rewritten company dashboard view.
- Screenshot C01.
- `student-go-docs/design-execution/PHASE_TRACKER.md`: set Phase 21 to COMPLETE, PARTIAL or BLOCKED with date, branch/commit (if the user asked for one) and a one-line note.
- A phase report in the chat: what changed (files), what was verified (with the evidence), what failed or was skipped and why, decisions needed from the user, and the exact next phase the roadmap allows. Then stop and wait; do not start another phase.
