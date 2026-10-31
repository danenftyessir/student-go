# PHASE 25 — Company Profile

> Source of truth for status is `student-go-docs/design-execution/PHASE_TRACKER.md`. This file is the work order for exactly one phase. Read `CLAUDE.md` and `student-go-docs/design-execution/EXECUTION_ROADMAP.md` first, execute only this phase, update the tracker, report, then stop.
> Evidence labels: VERIFIED / OBSERVED / INFERRED / UNKNOWN / BLOCKED (definitions in `DISCOVERY_REPORT.md` section 1).

| Field | Value |
|---|---|
| Phase | 25 — Company Profile |
| Group | Company role |
| Hard dependencies (must be COMPLETE) | Phase 3 (Shared Shell & Components) |
| Soft dependencies (recommended first) | Phase 1 (Critical Fixes & Data Integrity), Phase 20 (Institution Profile & Verification) |
| Decisions that gate this phase | D-06 Enum alignment for institution type and company size |
| Effort estimate (planning guess, not a commitment) | S |
| Roles touched | Company |
| Audit references | C07, C08; J5, J6, J16, J17; audit D11 |
| Tracker status at planning time | NOT_STARTED |

## A. Phase overview

**Goal.** Make the company profile the same organisation page as the institution profile and its edit form complete and prefilled, with the same fields as registration.

**In scope**

- `/company/profile`, `/company/profile/edit`.

**Out of scope**

- Registration (Phase 6).

**Evidence basis.** The audit (OBSERVED, 2026-10-28, port 8001) plus a read-only look at the running app on 2026-10-30. No repository source was read when this file was written; every statement about templates, controllers, models, middleware and tests is UNKNOWN until Stage One (Phase 0) fills `DISCOVERY_REPORT.md` section 12.

## B. Pages and routes

| Route pattern | Role | Audit status | Evidence | Notes |
|---|---|---|---|---|
| /company/profile | Company | Inspected | OBSERVED | — |
| /company/profile/edit | Company | Inspected | OBSERVED | Email empty and company size unselected (J5). |

**Audit screenshot IDs for this phase** (audit section L; OBSERVED). Page data below is quoted or summarised from the audit; the screenshots themselves are in the PDF, not in this repository.

| ID | Page | URL | Keep (audit) | Design direction (audit) | Backlog |
|---|---|---|---|---|---|
| C07 | Profil Perusahaan | /company/profile | Tata letak dua kolom yang rapi, grid kontak 2 kolom, info akun. | Profil organisasi standar yang sama dengan profil instansi. | J6, J16 |
| C08 | Edit Profil Perusahaan | /company/profile/edit | Pengelompokan Informasi Dasar, Kontak, Media Sosial. | Form bersection bersama. | J5, J17 |

## C. Existing implementation

- [OBSERVED] Profile: tidy two-column layout, two-column contact grid, account info (kept); hero photo.
- [OBSERVED] Edit: groups 'Informasi Dasar', 'Kontak', 'Media Sosial'; the email field loads empty and company size is unselected (J5).

**Repository facts for this phase: UNKNOWN until Stage One.** Locate and record in `DISCOVERY_REPORT.md` section 12 before editing: company profile controller/views, company-size enum values, registration vs edit field parity.

## D. Problems found (audit findings)

| Backlog ID | Finding | Evidence |
|---|---|---|
| J5 (P1) | Email and size not loaded. | OBSERVED |
| J6 (P1) | Hero photo. | OBSERVED |
| J16 (P2) | Accent colour. | OBSERVED |
| J17 (P2) | Different form component. | OBSERVED |

## E. Approved design direction

**Approved by the user (binding)**

- Hybrid direction (AD-04): Ruang Kerja workbench foundation; Catatan Lapangan editorial layer only where reading and storytelling matter. This phase uses none of it unless stated below.
- Principles (AD-05) and functional preservation (AD-06). Tokens, fonts and components below come from the asset package and are PROPOSED until D-17 is approved; existing assets are reconciled under AD-09 (section H); D-16 records that reference documents B and C were not found.

**Proposed for this phase** (audit sections I and L, adapted; status PROPOSED until the user approves the gating decisions)

- Same organisation profile as institutions; edit form shares fields and components with registration.

**Guardrails that bind this phase** (audit section H, numbered as in `EXECUTION_ROADMAP.md`): 1, 2, 3, 4, 6, 9, 10.

## F. Implementation tasks (do in order)

25.1. Gate check: D-06 resolved in Phase 1 (company size values).

25.2. Profile: standard organisation profile, no hero.

25.3. Edit: sectioned shared form; every stored value prefilled; field names unchanged.

25.4. Verify email and size are populated and that saving does not blank them.

25.5. Update the tracker and report with C07/C08 before/after.

## G. Components

| Component | Source | Notes |
|---|---|---|
| Organisation profile | Phase 3 | Same as Phase 20. |
| Sectioned form | Phase 3 | — |
| Upload (logo) | package | — |

## H. Assets (Source A existing `public/`, Source B custom package, Source C usage)

> Policy AD-09. Three sources, none disposable: **A** existing assets in `public/`, **B** the custom package `student-go-assets/`, **C** `resources/` (shows what is really used). Every asset has one decision: Retain, Reuse, Adapt, Replace or Deprecate. Originals are never deleted, overwritten, moved or renamed by this phase; copying anything into a production directory happens only if a task above says so. Full inventory, rights and integration map: `student-go-docs/design-execution/ASSET_INVENTORY.md`. Evidence labels as in `DISCOVERY_REPORT.md` section 1.

### H.1 Source A: existing application assets that matter in this phase

| Asset | Decision | What happens in this phase | Evidence |
|---|---|---|---|
| `company-profile.jpg` | Deprecate (usage) | Standard page header instead. | INFERRED |
| Company logo (upload) | Retain | Shown if present; initials otherwise. | UNKNOWN / BLOCKED |

### H.2 Source B: custom package assets available for this phase

**Original SVG, brand, fonts, icons and CSS (made for this project; in the package; licences in `ASSET_INVENTORY.md` section 2)**

- Lucide icons.

### H.3 Reconciliation for this phase

No overlap.

Overlap comparisons that touch this phase are in `ASSET_INVENTORY.md` section 11; where a Source A asset and a Source B asset serve the same purpose, that table gives the starting recommendation and this phase re-checks it against the real page.

### H.4 Native UI (no asset; plain HTML/CSS)

- Forms.

### H.5 Do not touch in this phase

- Student profile photographs and uploads, institution/company logos, verification documents: never moved or re-encoded; never shown in a new public place (D-04, D-31).

## I. Dependencies

- **Phases:** hard — Phase 3 (Shared Shell & Components); soft — Phase 1 (Critical Fixes & Data Integrity), Phase 20 (Institution Profile & Verification).
- **Decisions:** D-06 (Enum alignment for institution type and company size). A gating decision that is still OPEN in `DECISIONS_AND_OPEN_QUESTIONS.md` means the dependent tasks stop and the agent asks the user; it does not guess.
- **Packages:** No new composer or npm package. Anything new needs explicit approval first.
- **Data / environment:** Company with all fields filled.

## J. Functionality preservation

The global rules in `CLAUDE.md` section 7 apply in full. For this phase specifically:

- Field names and validation unchanged; logo upload unchanged.

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

- Edit and save with all fields; compare stored values.

*Test commands are not listed here because none could be verified without the repository. `CLAUDE.md` section 4 lists candidate commands that Stage One must confirm.*

## L. Acceptance criteria

The phase is COMPLETE only if every box can be checked with evidence in the phase report. Otherwise the status is PARTIAL (say which boxes failed) or BLOCKED (say what unblocks it).

- [ ] Email and company size are prefilled.
- [ ] No hero photo.
- [ ] Fields equal the registration fields (parity).
- [ ] Field names match the baseline.

## M. Risks and blockers

| Risk or blocker | Mitigation / what unblocks it | Blocks the phase? |
|---|---|---|
| Enum mismatch persists | Phase 1 / D-06. | Partially |

## N. Expected deliverables

- Rewritten company profile and edit views.
- Screenshots C07/C08.
- `student-go-docs/design-execution/PHASE_TRACKER.md`: set Phase 25 to COMPLETE, PARTIAL or BLOCKED with date, branch/commit (if the user asked for one) and a one-line note.
- A phase report in the chat: what changed (files), what was verified (with the evidence), what failed or was skipped and why, decisions needed from the user, and the exact next phase the roadmap allows. Then stop and wait; do not start another phase.
