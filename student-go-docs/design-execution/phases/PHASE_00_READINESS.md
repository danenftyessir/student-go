# PHASE 00 — Readiness & Baseline

> Source of truth for status is `student-go-docs/design-execution/PHASE_TRACKER.md`. This file is the work order for exactly one phase. Read `CLAUDE.md` and `student-go-docs/design-execution/EXECUTION_ROADMAP.md` first, execute only this phase, update the tracker, report, then stop.
> Evidence labels: VERIFIED / OBSERVED / INFERRED / UNKNOWN / BLOCKED (definitions in `DISCOVERY_REPORT.md` section 1).

| Field | Value |
|---|---|
| Phase | 0 — Readiness & Baseline |
| Group | Foundation |
| Hard dependencies (must be COMPLETE) | none |
| Soft dependencies (recommended first) | none |
| Decisions that gate this phase | D-12 A working database for baseline and QA, D-16 Missing reference documents B and C, D-22 Test approach, D-27 Git workflow for phase execution, D-28 Documentation root path, D-32 Certificate assets (templates, Pinyon Script, Poppins) |
| Effort estimate (planning guess, not a commitment) | M |
| Roles touched | All (read-only) |
| Audit references | Audit K (readiness checklist), G (limits); this discovery's Stage One |
| Tracker status at planning time | NOT_STARTED |

## A. Phase overview

**Goal.** Turn this provisional, audit-and-browser-only plan into a verified one. Stage One reads the repository (read-only), reconciles it with the audit, and records what is true. The rest of the phase captures the baseline (routes, forms, screenshots, tests) that every later phase is compared against. No application code changes.

**In scope**

- Stage One repository verification (read-only): versions, `route:list`, Blade/layout/partial map, form inventory, JS inventory, model/controller facts needed by Phase 1, tests, seeders, test-account locations.
- Getting the app to a state where data pages render (DB reachable or seeded locally) so baseline screenshots show real states (D-12).
- Baseline artefacts: route snapshot, form-field inventory, screenshots of every audit page ID, page-weight/CDN/font table, test-suite result.
- Correcting `DISCOVERY_REPORT.md` section 12, `CLAUDE.md` (PROVISIONAL markers, commands, conventions), and `DECISIONS_AND_OPEN_QUESTIONS.md` where the repository contradicts them.
- Asking the user to approve or answer the gating decisions listed above.

**Out of scope**

- Any change to application code, Blade, CSS, JS, routes, migrations, seeders or models.
- Copying the asset package into the app (Phase 2), running the photo downloader (only if D-33 allows it), installing new packages, and any delete, overwrite, move, rename, resize or re-encode of an existing asset.
- Submitting forms, opening `/student/chatbot/create`, creating accounts other than documented local test accounts, or touching a production database.

**Evidence basis.** The audit (OBSERVED, 2026-10-28, port 8001) plus a read-only look at the running app on 2026-10-30. No repository source was read when this file was written; every statement about templates, controllers, models, middleware and tests is UNKNOWN until Stage One (Phase 0) fills `DISCOVERY_REPORT.md` section 12.

## B. Pages and routes

| Route pattern | Role | Audit status | Evidence | Notes |
|---|---|---|---|---|
| Non-GET action routes (apply, cancel application, add connection, wishlist toggle, save job/talent, save search, notification mark-read/delete, chatbot send/archive/delete, profile update, verification upload) | Student/Institution/Company | Not enumerable from GET crawling | UNKNOWN | Stage One lists them from `route:list` and records form actions per page so designed forms keep posting to the same endpoints. |

## C. Existing implementation

- [VERIFIED 2026-10-30] The running app answers on http://127.0.0.1:8000. Public static pages render; the front-end build hashes are `app-CDP40zpX.css` and `app-34mOoJaZ.js`, identical to the audit's, so the front-end bundle has not been rebuilt since 28 Oct.
- [VERIFIED 2026-10-30] DB-backed pages fail locally with Laravel debug pages: `/institution/19` (QueryException, SQLSTATE 08006, Supabase pooler 'tenant/user not found'), `/register/student` (QueryException on a `universities` query, `app/Http/Controllers/Auth/RegisterController.php` line 67), `/forgot-password` (ViteException from `resources/views/layouts/auth.blade.php`: 'Unable to locate file in Vite manifest: resources/css/auth-institution.css').
- [VERIFIED 2026-10-30] Framework versions shown by the debug page: Laravel 13.7.0, PHP 8.3.24. Database driver is PostgreSQL (`pgsql`). APP_DEBUG is on.
- [OBSERVED] The audit ran on port 8001 with data: 700 students, 77 problems, 216 repository documents, 79 institutions, 0 jobs, test accounts for the three roles.
- [BLOCKED for this session] repository, `route:list`, Blade views, controllers, tests, seeders, `.env`.

**Repository facts for this phase: UNKNOWN until Stage One.** Locate and record in `DISCOVERY_REPORT.md` section 12 before editing: repository root and branch; composer.json/composer.lock; package.json/lock; vite.config.js; resources/css/app.css; routes/*.php; app/Http/Controllers/**; app/Models/**; database/migrations, seeders, factories; resources/views/** (layouts, partials, components, pages); resources/js/**; tests/** and phpunit.xml/pest config; bootstrap/app.php or exception handler; .gitignore (is `public/build` ignored?); README.

## D. Problems found (audit findings)

| Backlog ID | Finding | Evidence |
|---|---|---|
| G (audit) | No repository access during the audit: routing, Blade and Tailwind findings come from rendered HTML only. `route:list` and `resources/views` review are the first items in audit section K. | OBSERVED |
| D-12 (new) | The local database is unreachable on 2026-10-30, so data-driven baseline screenshots cannot be taken until it is fixed. | VERIFIED |
| J13 / G | Debug mode is on and leaks stack traces (class names, SQL, host details). Do not copy any host, user or credential seen on those pages into documents. | VERIFIED |
| K | Admin credentials, seeded institution/company records and `/forgot-password` inspection are still missing. | OBSERVED |

## E. Approved design direction

**Approved by the user (binding)**

- Hybrid direction (AD-04): Ruang Kerja workbench foundation; Catatan Lapangan editorial layer only where reading and storytelling matter. This phase uses none of it unless stated below.
- Principles (AD-05) and functional preservation (AD-06). Tokens, fonts and components below come from the asset package and are PROPOSED until D-17 is approved; existing assets are reconciled under AD-09 (section H); D-16 records that reference documents B and C were not found.

**Proposed for this phase** (audit sections I and L, adapted; status PROPOSED until the user approves the gating decisions)

- No design work. This phase only produces evidence. If the repository shows a different stack from the audit (for example no Tailwind v4 `@theme` support in the build, or a different view layer), stop and report; do not adapt the plan silently.
- Baseline screenshots use the same page IDs as the audit (G01-G08, S01-S26, I01-I11, C01-C09, E01-E02) so before/after comparison is mechanical.

**Guardrails that bind this phase** (audit section H, numbered as in `EXECUTION_ROADMAP.md`): 10.

## F. Implementation tasks (do in order)

0.1. Precondition: confirm the session sees the Laravel root (artisan, composer.json). If not, stop and report BLOCKED. Run `git status`, `git branch --show-current`, `git log -1 --oneline` (read-only). If the working tree has uncommitted changes, stop and ask; never overwrite or stash anyone's work.

0.2. Ask the user to answer D-12 (database), D-16 (reference documents B and C), D-22 (test approach) and D-27 (git workflow) if they are still OPEN. Do not guess.

0.3. Stage One A, versions and build: read composer.json/lock, package.json/lock, vite.config.js, resources/css/app.css, resources/js/app.js, .env.example (never `.env`; never print secrets), config/app.php (locale, name), config/database.php (default driver). Record exact versions and compare with the VERIFIED live values (Laravel 13.7.0, PHP 8.3.24) and the OBSERVED Tailwind 4.1.16, Alpine 3.13.3 (CDN), Leaflet 1.9.4, AOS 2.3.1, Boxicons 2.1.4, Font Awesome 6.5.1.

0.4. Stage One B, routes: run `php artisan route:list --json` (read-only; confirm it does not need the database). Save the output as the route snapshot. Reconcile against `DISCOVERY_REPORT.md` section 5 (59 patterns plus extras): list matched, new, and missing patterns. Resolve the four INFERRED record routes (`/institution/problems/{id}`, `/institution/problems/{id}/edit`, `/company/jobs/{id}`, `/company/applications/{id}`), the admin routes, password-reset and email-verification routes, logout, `/institution/reviews/create`, and every non-GET action route (apply, cancel, add connection, wishlist, saved job, saved talent, notification actions, chatbot send/archive/delete, profile update, verification upload). Record route names, controller actions and middleware.

0.5. Stage One C, views: map each route to its Blade view. List layouts (`layouts/auth.blade.php` is VERIFIED to exist), partials and Blade components, inline `<style>` and `@push`/`@stack` usage, CDN tags in layouts, and each page's header pattern. Build the form inventory: for every view with a `<form>`, record action, method (and `@method`), enctype and every input name/id.

0.6. Stage One D, JavaScript: list Alpine components, inline scripts, the `/api/regencies/{id}` fetch code, Leaflet initialisation (homepage and problem map view), AOS usage, the About slider, and `resources/js/app.js` (the compiled bundle was 0 bytes on 28 Oct).

0.7. Stage One E, backend facts that Phase 1 needs: how a student's name is stored and read (users, students/profiles, accessors, seeders) for D-03; the queries behind browse-problems, repository, friends search and company talents (ordering, pagination, counts) for D-05; the public-profile view and controller for D-04; the chatbot `create` route; exception handling and where APP_DEBUG is read; where institution type and company size options and stored values live for D-06.

0.8. Stage One F, tests and data: list tests/, the test framework and config, factories, seeders, and where test accounts for each role are defined (record the file and line, never the credentials). Check whether the seeders can reproduce the audit's dataset. Check `.gitignore` for `public/build`, `vendor`, `node_modules`.

0.9. Stage One G, assets (AD-09), read-only: run checklist S1-29 to S1-37 from `ASSET_INVENTORY.md` section 12. List `public/` recursively with size and SHA-256 into `student-go-docs/design-execution/baseline/asset-hashes.txt`; search `resources/`, `app/`, `routes/`, `config/`, `database/` for every file name in the inventory and for `asset(`, `url(`, `public_path(`, `storage_path(`; locate the certificate templates and the Pinyon Script and Poppins fonts, and the code that renders certificates; locate where avatars, logos, verification documents and problem images are stored; confirm the proposed targets under `public/assets/` and `resources/css/student-go/` do not collide with anything. Turn every INFERRED row of the inventory into a confirmed row and add any asset it missed. Ask the user for provenance and consent of each existing photograph (D-30). Change no asset.

0.10. Environment readiness, with approvals: ask before `composer install`, `npm ci` or `npm run build` (they write ignored directories but use the network). Resolve D-12 so DB-backed pages render. Fix nothing in the app; if the Vite error on `/forgot-password` persists after a build, record it for Phase 1/6.

0.11. Baseline screenshots: with the DB working and test accounts for the three roles, capture every audit page ID at 1440, 768 and 390 px (and 1024 and 360 for the pages named in Phase 27). Skip `/student/chatbot/create` and `/institution/reviews/create`. Submit no form. Store under `student-go-docs/design-execution/baseline/` (ask the user before adding many binary files; offer an out-of-repo folder instead). Record per page: weight of CSS/JS/images, fonts loaded, CDN hosts, `scrollWidth` vs `innerWidth`, console errors.

0.12. Baseline functionality and tests: save the route snapshot and form inventory under `student-go-docs/design-execution/baseline/`. Run the project's own test suite and record pass/fail counts and duration. Run the project's formatter/linter only in check mode, if one is configured.

0.13. Write the Verification Addendum: update `DISCOVERY_REPORT.md` section 12 so each UNKNOWN/BLOCKED item becomes VERIFIED (with file path and line) or stays BLOCKED with the reason. Update `CLAUDE.md`: remove PROVISIONAL markers that are now resolved, replace candidate commands with the ones that actually ran (with the date), and fix the repo-navigation and conventions sections. Update `DECISIONS_AND_OPEN_QUESTIONS.md` if the repository changes any option.

0.14. Update the tracker and report: list what Stage One contradicted in this plan, every phase doc that now needs a correction (do not edit other phase docs unless the user asks; list them), and what remains blocked. Stop.

## G. Components

| Component | Source | Notes |
|---|---|---|
| None built in this phase | n/a | Inventory only. |

## H. Assets (Source A existing `public/`, Source B custom package, Source C usage)

> Policy AD-09. Three sources, none disposable: **A** existing assets in `public/`, **B** the custom package `student-go-assets/`, **C** `resources/` (shows what is really used). Every asset has one decision: Retain, Reuse, Adapt, Replace or Deprecate. Originals are never deleted, overwritten, moved or renamed by this phase; copying anything into a production directory happens only if a task above says so. Full inventory, rights and integration map: `student-go-docs/design-execution/ASSET_INVENTORY.md`. Evidence labels as in `DISCOVERY_REPORT.md` section 1.

### H.1 Source A: existing application assets that matter in this phase

| Asset | Decision | What happens in this phase | Evidence |
|---|---|---|---|
| Every file under `public/` | Retain | Stage One lists `public/` recursively and records size and SHA-256 per file in `baseline/asset-hashes.txt`. Read-only. Confirms or corrects every INFERRED usage in `ASSET_INVENTORY.md` by searching `resources/views`, `resources/css`, `resources/js` and `app/` for each filename, `asset(`, `url(`, `public_path(` and `storage_path(`. | UNKNOWN until Stage One |
| Certificate templates and fonts | Retain | Locate them and record path, format and the code that uses them (D-32). Generate one sample certificate and keep it as the baseline if the feature can be run safely. | UNKNOWN / BLOCKED |
| Student profile photographs and uploads | Retain | Locate the storage disk and the symlink; record how Blade renders an avatar. Do not open or copy private files. | UNKNOWN / BLOCKED |

### H.2 Source B: custom package assets available for this phase

**Notes for this phase**

- Verify `assets-manifest.json` hashes against the package copy (111 entries; VERIFIED 0 mismatches this session) and record the package's own location in the repository, if it was copied in already.
- Confirm that `public/assets/` does not exist yet or list its content, so the proposed targets do not collide (VERIFIED 404 for `/assets/brand|fonts|icons|illustrations/*` on 2026-10-30; directory listing still needed).

### H.3 Reconciliation for this phase

Nothing is integrated in this phase. The output is a verified inventory: ASSET_INVENTORY.md sections 1-3 updated from VERIFIED/INFERRED to confirmed, and any asset the inventory missed added.

Overlap comparisons that touch this phase are in `ASSET_INVENTORY.md` section 11; where a Source A asset and a Source B asset serve the same purpose, that table gives the starting recommendation and this phase re-checks it against the real page.

### H.4 Native UI (no asset; plain HTML/CSS)

- Markdown tables and JSON snapshots.

### H.5 Do not touch in this phase

- Certificate templates and the Pinyon Script and Poppins fonts (and whatever generates certificates): UNKNOWN location (D-32). Do not remove, rename, re-encode or restyle them, and do not remove a Google Fonts link that a certificate view depends on.
- Student profile photographs and uploads, institution/company logos, verification documents: never moved or re-encoded; never shown in a new public place (D-04, D-31).
- Everything in `public/` (read only).

## I. Dependencies

- **Phases:** hard — none; soft — none.
- **Decisions:** D-12 (A working database for baseline and QA), D-16 (Missing reference documents B and C), D-22 (Test approach), D-27 (Git workflow for phase execution), D-28 (Documentation root path), D-32 (Certificate assets (templates, Pinyon Script, Poppins)). A gating decision that is still OPEN in `DECISIONS_AND_OPEN_QUESTIONS.md` means the dependent tasks stop and the agent asks the user; it does not guess.
- **Packages:** None to add. `composer install` / `npm ci` / `npm run build` only if already needed and approved.
- **Data / environment:** Reachable database with the audit-like dataset (D-12), test accounts for student, institution and company (found in seeders or provided by the user; never written into docs), optionally an admin account (D-13).

## J. Functionality preservation

The global rules in `CLAUDE.md` section 7 apply in full. For this phase specifically:

- Read-only phase: `git status` after the phase must show changes only under `student-go-docs/design-execution/` and `CLAUDE.md` (and the baseline folder).
- Never call a state-changing GET (`/student/chatbot/create`) and never POST a form while capturing the baseline.
- Never print `.env` values or the debug pages' connection details into any document or report.

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

- Re-run the route snapshot twice and diff: identical.
- Spot-check 10 routes from the snapshot against the live app's HTTP status, comparing with the audit table.
- Grep every file written in this phase for secrets patterns (`password`, `secret`, `token`, hostnames) and confirm none are present.
- Confirm the baseline screenshot count matches the table of audit page IDs, listing any missing ones with a reason.

*Test commands are not listed here because none could be verified without the repository. `CLAUDE.md` section 4 lists candidate commands that Stage One must confirm.*

## L. Acceptance criteria

The phase is COMPLETE only if every box can be checked with evidence in the phase report. Otherwise the status is PARTIAL (say which boxes failed) or BLOCKED (say what unblocks it).

- [ ] `git status` shows no modified application files; only `student-go-docs/design-execution/**`, `CLAUDE.md` and the baseline folder changed.
- [ ] `DISCOVERY_REPORT.md` section 12 resolves every item in the Stage One checklist as VERIFIED (with path and line) or BLOCKED (with reason); no item is left unlabelled.
- [ ] A route snapshot exists with a reconciliation table (matched / new / missing against the 59 patterns). The four INFERRED routes, admin routes, password reset, logout and non-GET action routes are each classified.
- [ ] A form inventory exists covering 100% of views that contain a `<form>`.
- [ ] `CLAUDE.md` lists only commands that were run successfully in this phase, each with the date; no PROVISIONAL marker remains unless explicitly listed as still unresolved.
- [ ] Baseline screenshots exist for every audit page ID at least at 1440, 768 and 390 px, or each missing one is listed with its reason (for example BLOCKED: no data).
- [ ] Baseline test-suite result is recorded (counts and duration) or the absence of tests is recorded.
- [ ] D-12, D-16, D-22 and D-27 each have an answer from the user or are explicitly carried as blockers in the report.
- [ ] `ASSET_INVENTORY.md` is updated from Stage One G: every INFERRED usage is confirmed or corrected, every file in `public/` appears in it (or in `baseline/asset-hashes.txt`), the certificate assets and upload locations are recorded (or BLOCKED with a reason), and no asset file was changed (hash list identical before and after).

## M. Risks and blockers

| Risk or blocker | Mitigation / what unblocks it | Blocks the phase? |
|---|---|---|
| Database stays unreachable (Supabase 'tenant/user not found') | User restores the remote project or approves a local PostgreSQL seeded from the project's seeders (D-12). Without it, data-driven baseline is BLOCKED. | Partially: static pages only |
| Repository differs from the audit's port-8001 instance | Compare first; report differences before any other phase starts. | No, but changes later phases |
| No test suite exists | Record that; propose D-22 option (c) for later phases. | No |
| Large binary baseline bloats the repo | Ask for an out-of-repo folder or LFS decision before committing screenshots. | No |
| Secrets appear in logs/debug pages | Never copy them; redact in reports. | No |

## N. Expected deliverables

- `student-go-docs/design-execution/DISCOVERY_REPORT.md` section 12 (Verification Addendum) completed.
- `CLAUDE.md` corrected to verified facts.
- `student-go-docs/design-execution/baseline/` containing the route snapshot, form inventory, screenshot set (or the agreed external location), page-weight table and test-result note.
- Updated `DECISIONS_AND_OPEN_QUESTIONS.md` where answers were received.
- `student-go-docs/design-execution/PHASE_TRACKER.md`: set Phase 0 to COMPLETE, PARTIAL or BLOCKED with date, branch/commit (if the user asked for one) and a one-line note.
- A phase report in the chat: what changed (files), what was verified (with the evidence), what failed or was skipped and why, decisions needed from the user, and the exact next phase the roadmap allows. Then stop and wait; do not start another phase.
