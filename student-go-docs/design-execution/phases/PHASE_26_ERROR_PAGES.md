# PHASE 26 — Error Pages

> Source of truth for status is `student-go-docs/design-execution/PHASE_TRACKER.md`. This file is the work order for exactly one phase. Read `CLAUDE.md` and `student-go-docs/design-execution/EXECUTION_ROADMAP.md` first, execute only this phase, update the tracker, report, then stop.
> Evidence labels: VERIFIED / OBSERVED / INFERRED / UNKNOWN / BLOCKED (definitions in `DISCOVERY_REPORT.md` section 1).

| Field | Value |
|---|---|
| Phase | 26 — Error Pages |
| Group | Cross-cutting |
| Hard dependencies (must be COMPLETE) | Phase 1 (Critical Fixes & Data Integrity), Phase 2 (Brand Identity & Design System), Phase 3 (Shared Shell & Components) |
| Soft dependencies (recommended first) | none |
| Decisions that gate this phase | D-01 Product name, wordmark and assistant name, D-11 Admin area scope, D-25 Defect fixes that change HTTP behaviour |
| Effort estimate (planning guess, not a commitment) | S |
| Roles touched | All |
| Audit references | E01, E02; J13; audit D12, G |
| Tracker status at planning time | NOT_STARTED |

## A. Phase overview

**Goal.** Replace the unbranded default 403/404/500 pages with branded error pages inside the app shell, each with a way back, and make sure production-like configuration never shows stack traces.

**In scope**

- Error views for 403, 404, 419, 500, 503 (and 429 if used); the 'Unauthorized. Hanya admin yang dapat mengakses' message on `/admin/dashboard`.

**Out of scope**

- Admin UI (D-11), exception handling logic beyond views and configuration.

**Evidence basis.** The audit (OBSERVED, 2026-10-28, port 8001) plus a read-only look at the running app on 2026-10-30. No repository source was read when this file was written; every statement about templates, controllers, models, middleware and tests is UNKNOWN until Stage One (Phase 0) fills `DISCOVERY_REPORT.md` section 12.

## B. Pages and routes

| Route pattern | Role | Audit status | Evidence | Notes |
|---|---|---|---|---|
| 404 / 403 / 500 fallbacks | Anyone | Inspected | OBSERVED; 404 and 500 VERIFIED | 404 is a bare default 'Not Found' page with no navigation (VERIFIED). 500 is a full debug page with stack trace (VERIFIED; APP_DEBUG is on). |
| /admin/dashboard | Admin | BLOCKED (no credentials) | OBSERVED (403 for non-admin) | Not designed (D-11). 403 page only. 22 other /admin/* guesses were 404. |

**Audit screenshot IDs for this phase** (audit section L; OBSERVED). Page data below is quoted or summarised from the audit; the screenshots themselves are in the PDF, not in this repository.

| ID | Page | URL | Keep (audit) | Design direction (audit) | Backlog |
|---|---|---|---|---|---|
| E01 | Error 403 (admin) | /admin/dashboard (403) | — | Set halaman 403/404/419/500/503 bermerek. | J13 |
| E02 | Error 404 | /student/browse-problems/999 (404) | — | Sama dengan E01. | J13 |

## C. Existing implementation

- [VERIFIED live 2026-10-30] A guessed URL (`/zz-not-a-route`) renders a bare Laravel '404 | Not Found' page with no navigation and no branding; `lang="en"`; title 'Not Found'.
- [VERIFIED live 2026-10-30] Server errors render the full Laravel debug page (exception class, SQL, file paths, stack trace, connection details), meaning APP_DEBUG is on in this environment.
- [OBSERVED] 403: the audit saw 'Unauthorized. Hanya admin yang dapat mengakses' for a student on `/admin/dashboard`; `/student/repository/abc` gave HTTP 500; cross-role access silently redirects (kept).

**Repository facts for this phase: UNKNOWN until Stage One.** Locate and record in `DISCOVERY_REPORT.md` section 12 before editing: resources/views/errors/*, exception handler or bootstrap/app.php, config of APP_DEBUG/APP_ENV, middleware that aborts 403 with that message.

## D. Problems found (audit findings)

| Backlog ID | Finding | Evidence |
|---|---|---|
| J13 (P1) | Default pages, debug page exposure. | OBSERVED; VERIFIED live |
| D12 | No branding, no way back. | OBSERVED; VERIFIED live |
| Security | Debug output reveals class names, SQL, host details; never copy them anywhere. | VERIFIED |

## E. Approved design direction

**Approved by the user (binding)**

- Hybrid direction (AD-04): Ruang Kerja workbench foundation; Catatan Lapangan editorial layer only where reading and storytelling matter. This phase uses none of it unless stated below.
- Principles (AD-05) and functional preservation (AD-06). Tokens, fonts and components below come from the asset package and are PROPOSED until D-17 is approved; existing assets are reconciled under AD-09 (section H); D-16 records that reference documents B and C were not found.

**Proposed for this phase** (audit sections I and L, adapted; status PROPOSED until the user approves the gating decisions)

- Branded error set in the shell (or minimal auth-less shell for 500/503): status code, one clear sentence in Indonesian, 'Kembali' and 'Ke beranda' actions, contact link; no technical detail. Same component for all codes.

**Guardrails that bind this phase** (audit section H, numbered as in `EXECUTION_ROADMAP.md`): 1, 4, 5, 7, 9.

## F. Implementation tasks (do in order)

26.1. Gate check: D-25 (debug/cross-role decisions), D-01.

26.2. Create the error view set using the shared shell (public shell for guests; app shell for logged-in users where possible; a self-contained minimal template for 500/503 that cannot fail if the database is down).

26.3. Ensure the views work when the DB is unreachable (no queries, no user-dependent partials in 500/503).

26.4. Document the environment settings that must be off in non-local environments (APP_DEBUG=false); do not edit `.env`.

26.5. Verify each code by forcing it on the local app (404 by URL; 403 by non-admin on `/admin/dashboard`; 419 by an expired token on a throwaway form; 500 only in a local test route removed afterwards, or via a test; do not leave test routes).

26.6. Update the tracker and report with E01/E02 before/after.

## G. Components

| Component | Source | Notes |
|---|---|---|
| Error layout | Phase 3 | — |
| Buttons | package | — |

## H. Assets (Source A existing `public/`, Source B custom package, Source C usage)

> Policy AD-09. Three sources, none disposable: **A** existing assets in `public/`, **B** the custom package `student-go-assets/`, **C** `resources/` (shows what is really used). Every asset has one decision: Retain, Reuse, Adapt, Replace or Deprecate. Originals are never deleted, overwritten, moved or renamed by this phase; copying anything into a production directory happens only if a task above says so. Full inventory, rights and integration map: `student-go-docs/design-execution/ASSET_INVENTORY.md`. Evidence labels as in `DISCOVERY_REPORT.md` section 1.

### H.1 Source A: existing application assets that matter in this phase

| Asset | Decision | What happens in this phase | Evidence |
|---|---|---|---|
| `student-go-logo.png` | Retain / Replace (conditional) | Follows Phase 3. | VERIFIED |

### H.2 Source B: custom package assets available for this phase

**Original SVG, brand, fonts, icons and CSS (made for this project; in the package; licences in `ASSET_INVENTORY.md` section 2)**

- Wordmark SVG; `motifs/contour-field.svg` or `survey-marks.svg` as a quiet motif; Lucide icons.

### H.3 Reconciliation for this phase

Debug pages must never expose host, user or credential values (global safety rule).

Overlap comparisons that touch this phase are in `ASSET_INVENTORY.md` section 11; where a Source A asset and a Source B asset serve the same purpose, that table gives the starting recommendation and this phase re-checks it against the real page.

### H.4 Native UI (no asset; plain HTML/CSS)

- Text.

### H.5 Do not touch in this phase

- Student profile photographs and uploads, institution/company logos, verification documents: never moved or re-encoded; never shown in a new public place (D-04, D-31).

## I. Dependencies

- **Phases:** hard — Phase 1 (Critical Fixes & Data Integrity), Phase 2 (Brand Identity & Design System), Phase 3 (Shared Shell & Components); soft — none.
- **Decisions:** D-01 (Product name, wordmark and assistant name), D-11 (Admin area scope), D-25 (Defect fixes that change HTTP behaviour). A gating decision that is still OPEN in `DECISIONS_AND_OPEN_QUESTIONS.md` means the dependent tasks stop and the agent asks the user; it does not guess.
- **Packages:** No new composer or npm package. Anything new needs explicit approval first.
- **Data / environment:** None.

## J. Functionality preservation

The global rules in `CLAUDE.md` section 7 apply in full. For this phase specifically:

- HTTP status codes unchanged.
- Silent cross-role redirects unchanged.
- Admin 403 message may be reworded but the status stays 403.

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

- Each code renders branded with the way back.
- With the DB down, 500 still renders.
- With debug off, no trace is shown (local test environment only).

*Test commands are not listed here because none could be verified without the repository. `CLAUDE.md` section 4 lists candidate commands that Stage One must confirm.*

## L. Acceptance criteria

The phase is COMPLETE only if every box can be checked with evidence in the phase report. Otherwise the status is PARTIAL (say which boxes failed) or BLOCKED (say what unblocks it).

- [ ] 404, 403, 419, 500 and 503 pages are branded, in Indonesian, and offer a way back.
- [ ] No error page displays a stack trace, SQL, path or connection detail when debug is off.
- [ ] Error pages render with the database unreachable.
- [ ] Status codes are unchanged.

## M. Risks and blockers

| Risk or blocker | Mitigation / what unblocks it | Blocks the phase? |
|---|---|---|
| Debug flag lives in `.env` | Document it; the user changes it. | No |
| A shell that queries the DB breaks error pages | Use a self-contained template. | No |

## N. Expected deliverables

- Error views, minimal CSS if needed.
- Configuration notes in the phase report.
- `student-go-docs/design-execution/PHASE_TRACKER.md`: set Phase 26 to COMPLETE, PARTIAL or BLOCKED with date, branch/commit (if the user asked for one) and a one-line note.
- A phase report in the chat: what changed (files), what was verified (with the evidence), what failed or was skipped and why, decisions needed from the user, and the exact next phase the roadmap allows. Then stop and wait; do not start another phase.
