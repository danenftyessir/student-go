# PHASE 28 — Final QA

> Source of truth for status is `student-go-docs/design-execution/PHASE_TRACKER.md`. This file is the work order for exactly one phase. Read `CLAUDE.md` and `student-go-docs/design-execution/EXECUTION_ROADMAP.md` first, execute only this phase, update the tracker, report, then stop.
> Evidence labels: VERIFIED / OBSERVED / INFERRED / UNKNOWN / BLOCKED (definitions in `DISCOVERY_REPORT.md` section 1).

| Field | Value |
|---|---|
| Phase | 28 — Final QA |
| Group | Cross-cutting |
| Hard dependencies (must be COMPLETE) | Phase 27 (Responsiveness & Accessibility) |
| Soft dependencies (recommended first) | none |
| Decisions that gate this phase | D-12 A working database for baseline and QA, D-13 Seed data for blocked screens, D-22 Test approach, D-24 Real-device and screen-reader testing |
| Effort estimate (planning guess, not a commitment) | L |
| Roles touched | All |
| Audit references | Audit H (guardrails), K (checklist), I; all phases |
| Tracker status at planning time | NOT_STARTED |

## A. Phase overview

**Goal.** Prove the design is complete and safe: nothing functional regressed, every guardrail holds, every asset is licensed and used, leftovers are removed, docs are closed, and anything not verifiable is listed honestly.

**In scope**

- Regression against the Phase 0 baseline (routes, forms, query parameters, redirects, tests).
- Audit-guardrail review of every page; asset and licence audit; cleanup of the old layouts and unused code/CDN tags; final documentation updates.

**Out of scope**

- New design or features.

**Evidence basis.** The audit (OBSERVED, 2026-10-28, port 8001) plus a read-only look at the running app on 2026-10-30. No repository source was read when this file was written; every statement about templates, controllers, models, middleware and tests is UNKNOWN until Stage One (Phase 0) fills `DISCOVERY_REPORT.md` section 12.

## B. Pages and routes

No route is owned by this phase; it works on shared files and every route (see section A).

## C. Existing implementation

- [OBSERVED] Baseline and defects are in the audit and the Phase 0 baseline (to be created).
- [UNKNOWN] The final state of the app: depends on Phases 1-27.

**Repository facts for this phase: UNKNOWN until Stage One.** Locate and record in `DISCOVERY_REPORT.md` section 12 before editing: the Phase 0 baseline artefacts; the tracker; ASSET_SOURCES.md and credits.json; the route snapshot.

## D. Problems found (audit findings)

| Backlog ID | Finding | Evidence |
|---|---|---|
| Audit K | Readiness checklist items still open at the time of QA must be listed. | OBSERVED |
| Guardrails H1-H10 | Each must be verified across the app. | OBSERVED |
| Leftovers | Old layouts, unused CDN tags (AOS, Boxicons, Font Awesome), emoji, English labels, placeholders. | OBSERVED |

## E. Approved design direction

**Approved by the user (binding)**

- Hybrid direction (AD-04): Ruang Kerja workbench foundation; Catatan Lapangan editorial layer only where reading and storytelling matter. This phase uses none of it unless stated below.
- Principles (AD-05) and functional preservation (AD-06). Tokens, fonts and components below come from the asset package and are PROPOSED until D-17 is approved; existing assets are reconciled under AD-09 (section H); D-16 records that reference documents B and C were not found.

**Proposed for this phase** (audit sections I and L, adapted; status PROPOSED until the user approves the gating decisions)

- QA is evidence-based: every claim in the final report links to a command output, screenshot or file.

**Guardrails that bind this phase** (audit section H, numbered as in `EXECUTION_ROADMAP.md`): 1, 2, 3, 4, 5, 6, 7, 8, 9, 10.

## F. Implementation tasks (do in order)

28.1. Preconditions: tracker shows Phases 0-27 COMPLETE (or PARTIAL/BLOCKED with the user's acceptance). Pull a clean working tree.

28.2. Routes: `php artisan route:list --json` (command confirmed in Stage One) and diff with the Phase 0 snapshot; every difference must be an approved D-25 change.

28.3. Forms: re-extract the form inventory and diff with the baseline (action, method, enctype, input names): expect no differences.

28.4. Tests: run the project's suite and the new regression tests; compare with the baseline counts.

28.5. Data matrix: every list with 77 problems, 216 documents, 699/700 students, 79 institutions, the 76-character name, the 65-character title, the 51-character email, empty accounts for each role.

28.6. Role matrix: guest, student, institution, company (and admin 403): walk every route in the inventory; record HTTP status, `<title>`, h1 count, lang, console errors.

28.7. Guardrail audit: for each of the ten guardrails, search the views for violations (hero images, gradients on controls, pill buttons, shadows outside overlays, emoji, English words, placeholder tiles, marketing numbers) and report counts (target 0).

28.8. Assets: verify every file in `assets-manifest.json` is present, every photograph used has caption, credit and a license entry in `ASSET_SOURCES.md`; remove unused assets; confirm no CDN requests remain for removed libraries.

28.9. Performance: record page weight and LCP for key pages vs the baseline (the 272 KB logo, 423 KB hero image and 125 KB CSS bundle should be gone or reduced).

28.10. Cleanup: remove superseded layouts/partials and inline `<style>` blocks that are dead; list anything intentionally kept.

28.11. Documentation: update `CLAUDE.md`, `DISCOVERY_REPORT.md` and `DECISIONS_AND_OPEN_QUESTIONS.md` to the final state; mark the design closed in the tracker only if all phases are COMPLETE.

28.12. Final report to the user: what is verified, what is not (BLOCKED items), residual risks, and a go/no-go recommendation (a recommendation, not a decision).

## G. Components

| Component | Source | Notes |
|---|---|---|
| None new | n/a | QA only. |

## H. Assets (Source A existing `public/`, Source B custom package, Source C usage)

> Policy AD-09. Three sources, none disposable: **A** existing assets in `public/`, **B** the custom package `student-go-assets/`, **C** `resources/` (shows what is really used). Every asset has one decision: Retain, Reuse, Adapt, Replace or Deprecate. Originals are never deleted, overwritten, moved or renamed by this phase; copying anything into a production directory happens only if a task above says so. Full inventory, rights and integration map: `student-go-docs/design-execution/ASSET_INVENTORY.md`. Evidence labels as in `DISCOVERY_REPORT.md` section 1.

### H.1 Source A: existing application assets that matter in this phase

| Asset | Decision | What happens in this phase | Evidence |
|---|---|---|---|
| Every Source A asset in ASSET_INVENTORY.md | Retain / Reuse / Adapt / Replace / Deprecate | Final reconciliation: each recorded decision is either executed or consciously deferred; deprecated usages are gone from templates; nothing was deleted, renamed or overwritten; certificate output matches the baseline; archiving of deprecated files happens only if the user explicitly approves it here. | to be verified |

### H.2 Source B: custom package assets available for this phase

**Original SVG, brand, fonts, icons and CSS (made for this project; in the package; licences in `ASSET_INVENTORY.md` section 2)**

- Verify all package assets in use are listed in the manifest.

**Notes for this phase**

- Every Source B file copied into `public/` or `resources/` is listed in `assets-manifest.json` with a matching SHA-256; `ASSET_SOURCES.md` has a row for every asset used.

**Catalogue photographs (Source B: licence-checked, NOT downloaded; deferred by D-33; used only when no Source A photograph fills the slot)**

- Verify credits and captions.

### H.3 Reconciliation for this phase

Produce a final inventory diff: what changed against the discovery inventory.

Overlap comparisons that touch this phase are in `ASSET_INVENTORY.md` section 11; where a Source A asset and a Source B asset serve the same purpose, that table gives the starting recommendation and this phase re-checks it against the real page.

### H.4 Native UI (no asset; plain HTML/CSS)

- N/A.

### H.5 Do not touch in this phase

- Student profile photographs and uploads, institution/company logos, verification documents: never moved or re-encoded; never shown in a new public place (D-04, D-31).
- Certificate templates and the Pinyon Script and Poppins fonts (and whatever generates certificates): UNKNOWN location (D-32). Do not remove, rename, re-encode or restyle them, and do not remove a Google Fonts link that a certificate view depends on.

## I. Dependencies

- **Phases:** hard — Phase 27 (Responsiveness & Accessibility); soft — none.
- **Decisions:** D-12 (A working database for baseline and QA), D-13 (Seed data for blocked screens), D-22 (Test approach), D-24 (Real-device and screen-reader testing). A gating decision that is still OPEN in `DECISIONS_AND_OPEN_QUESTIONS.md` means the dependent tasks stop and the agent asks the user; it does not guess.
- **Packages:** None.
- **Data / environment:** Same dataset as the baseline.

## J. Functionality preservation

The global rules in `CLAUDE.md` section 7 apply in full. For this phase specifically:

- QA may fix small regressions but must not add scope; larger issues become new items in the report.

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

- All of section F, executed with evidence.
- Re-run the standard checks on every page.

*Test commands are not listed here because none could be verified without the repository. `CLAUDE.md` section 4 lists candidate commands that Stage One must confirm.*

## L. Acceptance criteria

The phase is COMPLETE only if every box can be checked with evidence in the phase report. Otherwise the status is PARTIAL (say which boxes failed) or BLOCKED (say what unblocks it).

- [ ] The route snapshot differs from the Phase 0 snapshot only by approved D-25 changes (list attached).
- [ ] The form inventory diff against the baseline is empty (or every difference is approved).
- [ ] The project's tests pass with counts no lower than baseline.
- [ ] Guardrail violation counts are 0 for hero images on task pages, gradients on controls, pill buttons, shadows outside overlays, emoji icons, English UI words (except proper names), media placeholder tiles and marketing counters.
- [ ] No request is made to AOS, Boxicons, Font Awesome or removed Google Font families; no page title contains 'Laravel' or 'Karsa' (unless D-01 says Karsa).
- [ ] Every photograph used has caption, credit, alt text and an entry in `ASSET_SOURCES.md`; every package asset in use matches the manifest hash.
- [ ] Phase 27 evidence is attached; real-device checks are done or recorded BLOCKED.
- [ ] The tracker, CLAUDE.md, DISCOVERY_REPORT.md and DECISIONS are consistent with the final state.

## M. Risks and blockers

| Risk or blocker | Mitigation / what unblocks it | Blocks the phase? |
|---|---|---|
| Incomplete earlier phases | QA reports them; does not paper over. | Yes |
| No real-device testing | Record BLOCKED. | No |
| Data states still missing (D-13) | List unverified states. | No |

## N. Expected deliverables

- Final QA report with evidence links.
- Updated docs and tracker.
- A list of residual risks and recommended follow-ups.
- `student-go-docs/design-execution/PHASE_TRACKER.md`: set Phase 28 to COMPLETE, PARTIAL or BLOCKED with date, branch/commit (if the user asked for one) and a one-line note.
- A phase report in the chat: what changed (files), what was verified (with the evidence), what failed or was skipped and why, decisions needed from the user, and the exact next phase the roadmap allows. Then stop and wait; do not start another phase.
