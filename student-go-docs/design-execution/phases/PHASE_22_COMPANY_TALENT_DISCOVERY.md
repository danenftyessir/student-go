# PHASE 22 — Company Talent Discovery

> Source of truth for status is `student-go-docs/design-execution/PHASE_TRACKER.md`. This file is the work order for exactly one phase. Read `CLAUDE.md` and `student-go-docs/design-execution/EXECUTION_ROADMAP.md` first, execute only this phase, update the tracker, report, then stop.
> Evidence labels: VERIFIED / OBSERVED / INFERRED / UNKNOWN / BLOCKED (definitions in `DISCOVERY_REPORT.md` section 1).

| Field | Value |
|---|---|
| Phase | 22 — Company Talent Discovery |
| Group | Company role |
| Hard dependencies (must be COMPLETE) | Phase 1 (Critical Fixes & Data Integrity), Phase 3 (Shared Shell & Components) |
| Soft dependencies (recommended first) | Phase 13 (Friends) |
| Decisions that gate this phase | D-02 Language policy and copy deck, D-04 Student contact visibility policy, D-05 Stable ordering and count definitions, D-08 Should companies see all students or only opted-in ones?, D-26 Features the audit proposes that do not exist today, D-31 Personal photographs, student photos and identifying file names |
| Effort estimate (planning guess, not a commitment) | L |
| Roles touched | Company |
| Audit references | C02, C03; J1, J4, J8, J17, J18, J22, J33; audit D11 |
| Tracker status at planning time | NOT_STARTED |

## A. Phase overview

**Goal.** Make talent search a directory with a left filter rail, one pager, real locations and Indonesian SDG names, and make saved talents a two-column folder view with notes; fix the invisible CTA in the empty state.

**In scope**

- `/company/talents`, `/company/talents/saved`, `/company/talents/export`, `/company/talents/compare`, and candidate profile viewing.

**Out of scope**

- Student public profile layout (Phase 12).

**Evidence basis.** The audit (OBSERVED, 2026-10-28, port 8001) plus a read-only look at the running app on 2026-10-30. No repository source was read when this file was written; every statement about templates, controllers, models, middleware and tests is UNKNOWN until Stage One (Phase 0) fills `DISCOVERY_REPORT.md` section 12.

## B. Pages and routes

| Route pattern | Role | Audit status | Evidence | Notes |
|---|---|---|---|---|
| /company/talents | Company | Inspected + clicked | OBSERVED | 12 of 699 shown; numbered pager and load-more together. |
| /company/talents/saved | Company | Inspected (empty) | OBSERVED | CTA invisible (J33). |
| /company/talents/export | Company | Fetched read-only | OBSERVED | CSV export. Preserve. |
| /company/talents/compare | Company | Fetched | OBSERVED | Redirects to /company/talents without selection. |

**Candidate profiles:** the audit shows a company clicking a talent opens a profile page (title 'Oki Nabila' for the clicked 'Uli Hidayat'). The route used for that page is not in the 59-pattern inventory: it is UNKNOWN whether it is `/profile/{username}`, `/student/friends/profile/{id}` (student-only?) or a company-specific route. Stage One must identify it before this phase starts.

**Audit screenshot IDs for this phase** (audit section L; OBSERVED). Page data below is quoted or summarised from the audit; the screenshots themselves are in the PDF, not in this repository.

| ID | Page | URL | Keep (audit) | Design direction (audit) | Backlog |
|---|---|---|---|---|---|
| C02 | Temukan Talenta | /company/talents | Toggle Grid/List, fitur simpan pencarian dan bandingkan. | Direktori talenta dengan filter kiri dan daftar kanan, mirip pencarian mahasiswa tetapi dengan kolom yang relevan untuk perekrut. | J1, J4, J8, J18, J22 |
| C03 | Talent Tersimpan | /company/talents/saved | Konsep folder untuk shortlist. | Dua kolom: folder di kiri, daftar talenta di kanan dengan catatan per talenta. | J33, J17 |

## C. Existing implementation

- [OBSERVED] 12 of 699 students shown ('12 Talenta Ditemukan'); numbered pagination and 'Muat Lebih Banyak' simultaneously; SDG labels switch to English ('No Poverty'); every location reads 'Indonesia'; Grid/List toggle, save search and compare features exist; filters in purple.
- [OBSERVED] Saved talents: folder concept; the CTA in the empty state is white on white and invisible (J33).
- [OBSERVED] `/company/talents/export` returns a CSV (fetched read-only); `/company/talents/compare` redirects to `/company/talents` without a selection.

**Repository facts for this phase: UNKNOWN until Stage One.** Locate and record in `DISCOVERY_REPORT.md` section 12 before editing: company talents controller, the filter parameters, why only 12 results (a visibility rule or a limit), saved-talent folders/notes models, compare and export logic, the route used for candidate profile.

## D. Problems found (audit findings)

| Backlog ID | Finding | Evidence |
|---|---|---|
| J1 (P0) | Name mismatch on click-through. | OBSERVED |
| J4 (P0) | '12 of 699' unexplained. | OBSERVED |
| J22 (P2) | Two pagination patterns; English SDG; 'Indonesia' as location. | OBSERVED |
| J33 (P1) | Invisible CTA in saved empty state. | OBSERVED |
| J18 / J8 / J17 | Grid, English, divergent components. | OBSERVED |

## E. Approved design direction

**Approved by the user (binding)**

- Hybrid direction (AD-04): Ruang Kerja workbench foundation; Catatan Lapangan editorial layer only where reading and storytelling matter. This phase uses none of it unless stated below.
- Principles (AD-05) and functional preservation (AD-06). Tokens, fonts and components below come from the asset package and are PROPOSED until D-17 is approved; existing assets are reconciled under AD-09 (section H); D-16 records that reference documents B and C were not found.

**Proposed for this phase** (audit sections I and L, adapted; status PROPOSED until the user approves the gating decisions)

- Talent directory: filters at left (SDG in Indonesian names, universitas, jurusan, provinsi, keterampilan), a dense list with a 'why matched' column only if data exists (new feature otherwise; D-26), one pager, honest pool size and rule (D-08). Saved talents: folders at left, list at right with a note per talent (existing feature). Compare and export keep their behaviour; 'Bandingkan' appears only when items are selected.

**Guardrails that bind this phase** (audit section H, numbered as in `EXECUTION_ROADMAP.md`): 1, 2, 3, 4, 5, 6, 7, 9, 10.

## F. Implementation tasks (do in order)

22.1. Gate check: Phase 1 COMPLETE; D-08 (pool rule), D-04 (what a recruiter may see), D-26 (match reason).

22.2. Identify the candidate-profile route in Stage One output; if it is not recorded, ask.

22.3. Directory: filter rail (drawer on mobile), result count stating the rule that narrows the pool, list rows with the single-source name, university, major, location, SDG names; remove 'Muat Lebih Banyak' or the numbered pager (one pager).

22.4. SDG names: Indonesian names from one list shared with Phase 15; swatch only.

22.5. Locations: show the real regency/province or nothing; never the literal 'Indonesia' if that is a placeholder.

22.6. Saved talents: two-column folders and notes; the empty-state CTA uses the shared button (contrast verified).

22.7. Compare and export: keep behaviour; style only.

22.8. Verify with 12 and with a larger pool if D-08 changes the rule; verify the Grid/List toggle either preserved or removed with the user's approval.

22.9. Update the tracker and report with C02/C03 before/after.

## G. Components

| Component | Source | Notes |
|---|---|---|
| Filter rail + drawer | Phase 3 | — |
| People list rows | Phase 3 list | Shared with Phase 13. |
| SDG checklist (names) | Phase 15 | — |
| Folders + notes | page-specific | — |
| Pager | Phase 3 | — |
| Button | package | Contrast-safe. |

## H. Assets (Source A existing `public/`, Source B custom package, Source C usage)

> Policy AD-09. Three sources, none disposable: **A** existing assets in `public/`, **B** the custom package `student-go-assets/`, **C** `resources/` (shows what is really used). Every asset has one decision: Retain, Reuse, Adapt, Replace or Deprecate. Originals are never deleted, overwritten, moved or renamed by this phase; copying anything into a production directory happens only if a task above says so. Full inventory, rights and integration map: `student-go-docs/design-execution/ASSET_INVENTORY.md`. Evidence labels as in `DISCOVERY_REPORT.md` section 1.

### H.1 Source A: existing application assets that matter in this phase

| Asset | Decision | What happens in this phase | Evidence |
|---|---|---|---|
| Company stock hero | Deprecate (usage) | Removed from the talent pages. | OBSERVED |
| Student profile photographs in the talent list | Retain | Shown as today; no larger or more prominent public exposure (D-04). Initials when absent. | UNKNOWN / BLOCKED |

### H.2 Source B: custom package assets available for this phase

**Original SVG, brand, fonts, icons and CSS (made for this project; in the package; licences in `ASSET_INVENTORY.md` section 2)**

- `empty-states/no-saved-projects.svg`, Lucide icons (users, bookmark, download).

### H.3 Reconciliation for this phase

The talent list is the most privacy-sensitive place photographs appear.

Overlap comparisons that touch this phase are in `ASSET_INVENTORY.md` section 11; where a Source A asset and a Source B asset serve the same purpose, that table gives the starting recommendation and this phase re-checks it against the real page.

### H.4 Native UI (no asset; plain HTML/CSS)

- Lists.

### H.5 Do not touch in this phase

- Student profile photographs and uploads, institution/company logos, verification documents: never moved or re-encoded; never shown in a new public place (D-04, D-31).

## I. Dependencies

- **Phases:** hard — Phase 1 (Critical Fixes & Data Integrity), Phase 3 (Shared Shell & Components); soft — Phase 13 (Friends).
- **Decisions:** D-02 (Language policy and copy deck), D-04 (Student contact visibility policy), D-05 (Stable ordering and count definitions), D-08 (Should companies see all students or only opted-in ones?), D-26 (Features the audit proposes that do not exist today), D-31 (Personal photographs, student photos and identifying file names). A gating decision that is still OPEN in `DECISIONS_AND_OPEN_QUESTIONS.md` means the dependent tasks stop and the agent asks the user; it does not guess.
- **Packages:** No new composer or npm package. Anything new needs explicit approval first.
- **Data / environment:** Large talent pool, saved folders with notes (D-13).

## J. Functionality preservation

The global rules in `CLAUDE.md` section 7 apply in full. For this phase specifically:

- Search, save-search, compare and export endpoints and parameters unchanged.
- Saved folders and notes unchanged.
- Students' contact data remains private per D-04.

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

- Search and filter; pager; save/unsave; compare two talents; export CSV and compare headers with baseline.
- Empty saved page CTA contrast.
- 390 px list.

*Test commands are not listed here because none could be verified without the repository. `CLAUDE.md` section 4 lists candidate commands that Stage One must confirm.*

## L. Acceptance criteria

The phase is COMPLETE only if every box can be checked with evidence in the phase report. Otherwise the status is PARTIAL (say which boxes failed) or BLOCKED (say what unblocks it).

- [ ] One pager only.
- [ ] SDG labels are Indonesian everywhere.
- [ ] The count states how many students match and why the pool is limited.
- [ ] No row shows the literal 'Indonesia' as a placeholder location.
- [ ] The empty saved page CTA is visible with contrast >= 4.5:1.
- [ ] Export CSV bytes equal the baseline for the same query.

## M. Risks and blockers

| Risk or blocker | Mitigation / what unblocks it | Blocks the phase? |
|---|---|---|
| Candidate-profile route unknown | Stage One must resolve it. | Yes |
| Match reason is a new feature | D-26. | No |
| Privacy: recruiters see student data | D-04, D-08. | Yes |

## N. Expected deliverables

- Rewritten talents, saved, compare/export-adjacent views.
- Screenshots C02/C03.
- `student-go-docs/design-execution/PHASE_TRACKER.md`: set Phase 22 to COMPLETE, PARTIAL or BLOCKED with date, branch/commit (if the user asked for one) and a one-line note.
- A phase report in the chat: what changed (files), what was verified (with the evidence), what failed or was skipped and why, decisions needed from the user, and the exact next phase the roadmap allows. Then stop and wait; do not start another phase.
