# PHASE 27 — Responsiveness & Accessibility

> Source of truth for status is `student-go-docs/design-execution/PHASE_TRACKER.md`. This file is the work order for exactly one phase. Read `CLAUDE.md` and `student-go-docs/design-execution/EXECUTION_ROADMAP.md` first, execute only this phase, update the tracker, report, then stop.
> Evidence labels: VERIFIED / OBSERVED / INFERRED / UNKNOWN / BLOCKED (definitions in `DISCOVERY_REPORT.md` section 1).

| Field | Value |
|---|---|
| Phase | 27 — Responsiveness & Accessibility |
| Group | Cross-cutting |
| Hard dependencies (must be COMPLETE) | Phase 4 (Homepage), Phase 5 (About, Contact, Public Institution Profile), Phase 6 (Auth), Phase 7 (Student Dashboard), Phase 8 (Student Project Discovery), Phase 9 (Project Detail), Phase 10 (Applications), Phase 11 (Active Projects & Wishlist), Phase 12 (Profile & Portfolio), Phase 13 (Friends), Phase 14 (Repository), Phase 15 (Jobs), Phase 16 (KARA Chatbot & Notifications), Phase 17 (Institution Dashboard), Phase 18 (Institution Project Management), Phase 19 (Institution Review & Monitoring), Phase 20 (Institution Profile & Verification), Phase 21 (Company Dashboard), Phase 22 (Company Talent Discovery), Phase 23 (Company Job Management), Phase 24 (Applicant Pipeline), Phase 25 (Company Profile), Phase 26 (Error Pages) |
| Soft dependencies (recommended first) | none |
| Decisions that gate this phase | D-24 Real-device and screen-reader testing, D-22 Test approach, D-12 A working database for baseline and QA, D-13 Seed data for blocked screens |
| Effort estimate (planning guess, not a commitment) | L |
| Roles touched | All |
| Audit references | Audit I (baseline), J7, J10, J11, J23, J25; audit G (limits) |
| Tracker status at planning time | NOT_STARTED |

## A. Phase overview

**Goal.** Sweep every designed page for responsive and accessibility defects against WCAG 2.2 AA and the audit's baseline, fix what is left, and record evidence; real devices and a screen reader are a separate decision (D-24).

**In scope**

- All pages in the 59-pattern inventory that Phases 4-26 touched; global components.
- Responsive rules: max one horizontal scroll container per table, filter sidebars become drawers, mobile menu includes notifications.
- Accessibility: contrast, `:focus-visible`, skip link, one h1, `lang="id"`, labels, accessible names, `aria-expanded`, `aria-live`, status as text, dialog focus lock and Esc, reduced motion, target size.

**Out of scope**

- Design decisions; this phase fixes defects, it does not alter the design direction.

**Evidence basis.** The audit (OBSERVED, 2026-10-28, port 8001) plus a read-only look at the running app on 2026-10-30. No repository source was read when this file was written; every statement about templates, controllers, models, middleware and tests is UNKNOWN until Stage One (Phase 0) fills `DISCOVERY_REPORT.md` section 12.

## B. Pages and routes

No route is owned by this phase; it works on shared files and every route (see section A).

## C. Existing implementation

- [OBSERVED] Before the design: 59 `outline-none` and 0 `:focus-visible` rules; the bell announced as '0'; unlabelled filter selects; 17 SDG checkboxes at under 24 px; contrast failures (footer 3.67:1; 'Hubungkan' 4.12:1; green numbers 3.08:1; gray-400 text); no reduced-motion rule; horizontal scroll on `/` (455 px at 390) and on friend search at 768 px; `/company/applications` 3 of 6 columns at 390; stepper overflow on `/company/jobs/create` (483 px).
- [OBSERVED] The audit tested 390/768 px only in iframes; no real device, no screen reader (G).

**Repository facts for this phase: UNKNOWN until Stage One.** Locate and record in `DISCOVERY_REPORT.md` section 12 before editing: the list of pages touched by earlier phases (tracker), component library, any existing a11y tooling.

## D. Problems found (audit findings)

| Backlog ID | Finding | Evidence |
|---|---|---|
| J7 (P1) | Mobile layout breaks. | OBSERVED |
| J10 (P1) | Contrast failures. | OBSERVED |
| J11 (P1) | Focus and naming gaps. | OBSERVED |
| J23 (P2) | Motion without reduced-motion handling. | OBSERVED |
| J25 (P2) | Mobile menu gaps. | OBSERVED |
| G | No real-device/screen-reader testing yet. | BLOCKED until D-24 |

## E. Approved design direction

**Approved by the user (binding)**

- Hybrid direction (AD-04): Ruang Kerja workbench foundation; Catatan Lapangan editorial layer only where reading and storytelling matter. This phase uses none of it unless stated below.
- Principles (AD-05) and functional preservation (AD-06). Tokens, fonts and components below come from the asset package and are PROPOSED until D-17 is approved; existing assets are reconciled under AD-09 (section H); D-16 records that reference documents B and C were not found.

**Proposed for this phase** (audit sections I and L, adapted; status PROPOSED until the user approves the gating decisions)

- WCAG 2.2 AA is the bar: contrast 4.5:1 text, 3:1 large text and UI parts, visible focus, target size >= 24 px (44 px recommended on touch), no keyboard traps, dialogs trap focus and close on Esc, status never conveyed by colour alone, one h1 per page, `lang="id"`.

**Guardrails that bind this phase** (audit section H, numbered as in `EXECUTION_ROADMAP.md`): 1, 2, 8, 9, 10.

## F. Implementation tasks (do in order)

27.1. Build the page checklist from the tracker (every page touched by Phases 4-26).

27.2. Automated pass: run an accessibility scanner (axe or Lighthouse via Playwright `npx`, per D-22) on every page and role; record violations.

27.3. Contrast: recompute every token pair and spot-check rendered pages; fix tokens, not individual pages, where possible.

27.4. Keyboard: walk every page; verify focus ring, skip link, tab order, dialogs, menus, steppers, maps (Leaflet keyboard operation).

27.5. Screen-reader semantics: landmarks, one h1, labels, names for icon buttons, `aria-live` for regency loader and notification count; table headers.

27.6. Responsive: for each page at 360, 390, 768, 1024, 1366, 1440 px assert `scrollWidth <= innerWidth`; check tables (stacked rows below 640 px), filters (drawer), steppers, pagers.

27.7. Motion: emulate `prefers-reduced-motion: reduce`; nothing animates except instantaneous state changes.

27.8. Target size: verify the SDG checklists and icon buttons.

27.9. Fix defects found, in small commits if the user asked for commits; re-run the passes.

27.10. If D-24 is answered, produce a device and screen-reader checklist for the user or teammate; otherwise record the gap.

27.11. Update the tracker and report: defect counts before/after per page, remaining items, BLOCKED real-device checks.

## G. Components

| Component | Source | Notes |
|---|---|---|
| All shared components | Phase 3 | Fix at the component level. |

## H. Assets (Source A existing `public/`, Source B custom package, Source C usage)

> Policy AD-09. Three sources, none disposable: **A** existing assets in `public/`, **B** the custom package `student-go-assets/`, **C** `resources/` (shows what is really used). Every asset has one decision: Retain, Reuse, Adapt, Replace or Deprecate. Originals are never deleted, overwritten, moved or renamed by this phase; copying anything into a production directory happens only if a task above says so. Full inventory, rights and integration map: `student-go-docs/design-execution/ASSET_INVENTORY.md`. Evidence labels as in `DISCOVERY_REPORT.md` section 1.

### H.1 Source A: existing application assets that matter in this phase

| Asset | Decision | What happens in this phase | Evidence |
|---|---|---|---|
| Every Source A photograph still shown | Retain | Verify alt text exists and describes the scene, derivatives are used for large images, no image causes page-level horizontal scroll, and missing images degrade gracefully. | to be verified |

### H.2 Source B: custom package assets available for this phase

**Notes for this phase**

- Verify alt text and the courtesy credit for every Source B photograph placed (none placed unless D-33 chose to download).

**Catalogue photographs (Source B: licence-checked, NOT downloaded; deferred by D-33; used only when no Source A photograph fills the slot)**

- Verify alt text and credits for every photo placed.

### H.3 Reconciliation for this phase

Audit the two sources with the same checklist.

Overlap comparisons that touch this phase are in `ASSET_INVENTORY.md` section 11; where a Source A asset and a Source B asset serve the same purpose, that table gives the starting recommendation and this phase re-checks it against the real page.

### H.4 Native UI (no asset; plain HTML/CSS)

- N/A.

### H.5 Do not touch in this phase

- Student profile photographs and uploads, institution/company logos, verification documents: never moved or re-encoded; never shown in a new public place (D-04, D-31).
- Certificate templates and the Pinyon Script and Poppins fonts (and whatever generates certificates): UNKNOWN location (D-32). Do not remove, rename, re-encode or restyle them, and do not remove a Google Fonts link that a certificate view depends on.

## I. Dependencies

- **Phases:** hard — Phase 4 (Homepage), Phase 5 (About, Contact, Public Institution Profile), Phase 6 (Auth), Phase 7 (Student Dashboard), Phase 8 (Student Project Discovery), Phase 9 (Project Detail), Phase 10 (Applications), Phase 11 (Active Projects & Wishlist), Phase 12 (Profile & Portfolio), Phase 13 (Friends), Phase 14 (Repository), Phase 15 (Jobs), Phase 16 (KARA Chatbot & Notifications), Phase 17 (Institution Dashboard), Phase 18 (Institution Project Management), Phase 19 (Institution Review & Monitoring), Phase 20 (Institution Profile & Verification), Phase 21 (Company Dashboard), Phase 22 (Company Talent Discovery), Phase 23 (Company Job Management), Phase 24 (Applicant Pipeline), Phase 25 (Company Profile), Phase 26 (Error Pages); soft — none.
- **Decisions:** D-24 (Real-device and screen-reader testing), D-22 (Test approach), D-12 (A working database for baseline and QA), D-13 (Seed data for blocked screens). A gating decision that is still OPEN in `DECISIONS_AND_OPEN_QUESTIONS.md` means the dependent tasks stop and the agent asks the user; it does not guess.
- **Packages:** Accessibility scanners and Playwright via `npx` outside the project (D-22 option c); adding them as dev dependencies needs approval.
- **Data / environment:** Seeded DB for every role; empty accounts.

## J. Functionality preservation

The global rules in `CLAUDE.md` section 7 apply in full. For this phase specifically:

- Do not change behaviour to satisfy a scanner without checking the baseline; fix markup, names and tokens.

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

- Scanner reports 0 serious/critical issues on every page (or each remaining one is documented).
- Manual keyboard and reduced-motion checks.
- Viewport matrix on every page.
- Real-device and screen-reader checks only if D-24 is answered.

*Test commands are not listed here because none could be verified without the repository. `CLAUDE.md` section 4 lists candidate commands that Stage One must confirm.*

## L. Acceptance criteria

The phase is COMPLETE only if every box can be checked with evidence in the phase report. Otherwise the status is PARTIAL (say which boxes failed) or BLOCKED (say what unblocks it).

- [ ] No page has horizontal page scroll at 360, 390, 768, 1024, 1366, 1440 px.
- [ ] Every text pair >= 4.5:1 and every large-text/UI pair >= 3:1 on the rendered pages (report lists the measured worst case).
- [ ] Every interactive element has a visible focus indicator and an accessible name; icon buttons have labels; the bell announces its count.
- [ ] Each page has exactly one h1 and `lang="id"`.
- [ ] Dialogs trap focus and close with Esc; menus expose `aria-expanded`.
- [ ] Under `prefers-reduced-motion: reduce`, no transition or animation runs.
- [ ] SDG checkboxes and icon buttons are at least 24x24 CSS px.
- [ ] Scanner results are attached; zero serious/critical issues remain or each is explained.
- [ ] Real-device and screen-reader testing is either done (evidence) or recorded as BLOCKED per D-24.

## M. Risks and blockers

| Risk or blocker | Mitigation / what unblocks it | Blocks the phase? |
|---|---|---|
| Real devices unavailable | Record BLOCKED (D-24); provide checklist. | Partially |
| Scanner false positives | Judge manually; document. | No |
| Late fixes regress earlier pages | Re-run the matrix after fixes. | No |

## N. Expected deliverables

- Fixes across components/pages; an accessibility and responsive report (in chat or in the phase report) with per-page results.
- Device/screen-reader checklist for the user if requested.
- `student-go-docs/design-execution/PHASE_TRACKER.md`: set Phase 27 to COMPLETE, PARTIAL or BLOCKED with date, branch/commit (if the user asked for one) and a one-line note.
- A phase report in the chat: what changed (files), what was verified (with the evidence), what failed or was skipped and why, decisions needed from the user, and the exact next phase the roadmap allows. Then stop and wait; do not start another phase.
