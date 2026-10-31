# CLAUDE.md — Student Go design

> **Verified against the repository by Phase 0 Stage One on 2026-10-30** (branch `feat/students`, HEAD `e824b9d`). Sections 2-5 now state repository facts; VERIFIED here means read in source or confirmed by a command run on that date. Details and evidence: `student-go-docs/design-execution/DISCOVERY_REPORT.md` section 12 and `student-go-docs/design-execution/baseline/`.
>
> **Still unresolved (explicitly listed, not provisional guesses):** (1) the seeded database has only 5 provinces / 22 regencies (the BPS seeder path is broken) and problem images do not load (empty bucket; D-14, D-15); (2) no seeded admin account, and no jobs, connections, wishlists or chats (D-11, D-13); (3) rights and consent for existing photographs (D-30); (4) reference documents B and C (D-16). Phase 0 status is in `PHASE_TRACKER.md`.

## 1. Project overview

Student Go is a Laravel web platform for Kuliah Kerja Nyata (KKN, Indonesian community-service programmes). Institutions publish problems, students discover and apply to them, and companies search talent and post jobs. It has three authenticated roles (student, institution, company), public pages (home, about, contact, public institution and student profiles) and auth pages, plus an admin area that exists but was not inspected (BLOCKED). It is branded inconsistently today: tab titles say 'Laravel' (`APP_NAME` default; VERIFIED), the logo, auth pages, README ('Karsa - Platform Magang dan Rekrutmen Mahasiswa') and the generated certificate say 'Karsa' (VERIFIED), 'KKN Go' also appears (OBSERVED). The admin area (9 routes, `user_type = 'admin'`) exists in source; its UI has not been viewed (no admin account).

**The task in this repository** is a phased design: a custom visual identity and design system, a shared shell and component set, then every page by role, then responsiveness, accessibility and QA, without changing what the product does. The plan lives in `student-go-docs/design-execution/`; this file tells you how to work within it.

## 2. Tech stack

| Item | Value | Evidence |
|---|---|---|
| Framework | Laravel 13.7.0 (`laravel/framework` v13.7.0) | VERIFIED (`composer.lock`, `php artisan --version`, 2026-10-30) |
| PHP | 8.3.24 NTS x64 (CLI); `gd`, `pdo_pgsql`, `pdo_sqlite` loaded | VERIFIED (`php -v`, `php -m`) |
| Other PHP packages | sanctum 4.3.1, tinker 3.0.2, smalot/pdfparser 2.12.5, flysystem-aws-s3-v3 3.32.0; dev: phpunit 12.5.24, pint 1.29.1, pail 1.2.6, pao 1.0.6 | VERIFIED (`composer.lock`) |
| Database | PostgreSQL via a Supabase pooler (`DB_CONNECTION=pgsql` in `.env`; `config/database.php:20` default `sqlite`), on the user's **new** Supabase project since 2026-10-30, migrated (60/60) and seeded with the project's seeders (D-12). Test accounts come from the seeders (one shared seeded password; never written down). Credentials live only in `.env` and must never be copied anywhere. | VERIFIED |
| File storage | All uploads (profile photos, logos, verification documents, KTP/NPWP, problem images, proposals, reports, certificates) go to Supabase Storage via `app/Services/SupabaseStorageService.php`; `storage/app/public` is empty and `public/storage` does not exist | VERIFIED |
| Views | Blade. Main layout `resources/views/layouts/app.blade.php` (`@yield('title')`, `@yield('content')`, `@stack('styles')`, `@stack('scripts')`); `layouts/auth.blade.php` is the single auth template (Phase 6: login, role chooser, three registrations, forgot/reset, email verification; compact header, no footer); both include `partials/head.blade.php` (Phase 3: `lang="id"`, title `… — Student Go`, favicons, Vite, Alpine); shell includes `components.navbar`, `components.footer`, `components.notification-dropdown`; shared components `<x-ui.*>` in `resources/views/components/ui/` (38 since Phase 6 added `x-ui.password`, see `COMPONENT_INVENTORY.md`) and `<x-icon>`; no `resources/views/errors/` | VERIFIED (Phase 3) |
| CSS | Tailwind CSS 4.1.16 via `@tailwindcss/vite`, entry `resources/css/app.css` (`@import 'tailwindcss'`, then the Student Go `fonts.css`/`tokens.css`/`components.css`/`hero.css`/`public-pages.css`/`auth.css`/`legacy-overrides.css`, `@source`); since Phase 3 the global overrides (`gray-*`, `font-sans`, `text-*`, radius, base rules, focus ring, reduced motion) are active and 'Outfit'/'Inter' are aliased to local Plus Jakarta Sans; inline `<style>` blocks remain in most page views. Vite 7.3.2, laravel-vite-plugin 2.1.0; Vite inputs: `resources/css/app.css`, `resources/js/app.js` only | VERIFIED |
| JS | `resources/js/app.js` + `resources/js/student-go/*.js` (Phase 3: Alpine data `sgDisclosure`, `sgMobileNav`, `sgNotifications`, `sgUpload`, `sgTabs`, `sgFilterBar`; toasts `window.showNotification`; dialogs and `window.sgConfirm`; Phase 6 `auth.js`: `sgAuthForm`, `sgRegister`, `sgRegions`, `sgPassword`), bundle 10.76 kB, loaded in `<head>` before Alpine. Alpine.js 3.13.3 from cdn.jsdelivr.net (layout + register pages; `institution/reviews/create|edit` load a second, floating `alpinejs@3.x.x`); Leaflet 1.9.4 + leaflet.markercluster 1.5.3 from unpkg (home, problem map); AOS 2.3.1 (home only); Chart.js 4.4.1 (company dashboard only) | VERIFIED |
| Libraries to retire in the design | Removed from the layouts in Phase 3 (D-20 a): Boxicons, Font Awesome (its 10 uses replaced), Google Fonts Inter/Outfit, fonts.bunny.net. Still present: page-level Google Fonts (Space Grotesk on 8 views). Lora/Merriweather and AOS were removed with the homepage rewrite (Phase 4). The homepage hero plays a local background video (`public/video/`, AD-11; no third-party embed) | VERIFIED (Phase 4, 2026-10-31) |
| Certificates | GD-rendered PNG from `public/assets/certificate/template.png` with `fonts/Poppins-Regular.ttf` and `fonts/PinyonScript-Regular.ttf` (`app/Services/CertificateService.php`); independent of website fonts | VERIFIED |
| Dev server | `php artisan serve` on http://127.0.0.1:8000 (`composer dev` also runs queue, pail and `npm run dev`) | VERIFIED 2026-10-30 |
| Dev host OS | Windows 11; the agent shell is Git Bash (POSIX syntax) | VERIFIED |
| Debug mode | `APP_DEBUG` on locally (`config/app.php:42`); debug pages print connection details — never copy or screenshot them | VERIFIED |
| Locale | `config/app.php:81` `env('APP_LOCALE', 'en')` (unchanged); since Phase 3 both layouts hard-code `<html lang="id">`; standalone auth pages `lang="id"`. `APP_NAME` is still the `.env` value (layouts print 'Student Go'); changing it renames the session cookie and cache prefix | VERIFIED (Phase 3) |
| Tests | PHPUnit 12; 2 example tests + 36 Phase 1 regression tests (`tests/Feature/Phase1/`) + 7 Phase 2 design-system tests (`tests/Feature/Phase2/`) + 18 Phase 3 shell/component tests (`tests/Feature/Phase3/`) + 17 Phase 4 homepage and hero-video tests (`tests/Feature/Phase4/`) + 6 Phase 5 public-page tests (`tests/Feature/Phase5/`) + 28 Phase 6 auth tests (`tests/Feature/Phase6/`). `phpunit.xml` uses SQLite `:memory:`, and `tests/TestCase.php` also redirects the hard-coded `pgsql` model connection to SQLite `:memory:` (it fails any test that is not) and blocks outbound HTTP (`Http::preventStrayRequests()`, blank Supabase config). No CI; Pint installed without `pint.json` | VERIFIED (2026-10-30, Phase 1) |
| Browser checks | Playwright (Python) **outside** the project (D-22 option c); nothing added to the project | VERIFIED |

## 3. Repository navigation

| Path | What | Evidence |
|---|---|---|
| `CLAUDE.md` | This file (copied to the root from `student-go-design-docs/` on 2026-10-30, D-28) | VERIFIED |
| `student-go-docs/design-execution/EXECUTION_ROADMAP.md` | Primary source of truth: 29 phases, protocol, dependencies, coverage tables | planning |
| `student-go-docs/design-execution/PHASE_TRACKER.md` | Status of each phase (the only place status lives) | planning |
| `student-go-docs/design-execution/DISCOVERY_REPORT.md` | What is known about the app; section 12 = Stage One results | updated in Phase 0 |
| `student-go-docs/design-execution/DECISIONS_AND_OPEN_QUESTIONS.md` | Approved decisions vs open questions vs recommendations | updated in Phase 0 |
| `student-go-docs/design-execution/ASSET_INVENTORY.md` | Every asset (existing `public/`, custom package, usage); section 1.5 = verified `public/` list | updated in Phase 0 |
| `student-go-docs/design-execution/phases/PHASE_NN_*.md` | One work order per phase (sections A-N) | planning |
| `student-go-docs/design-execution/baseline/` | Phase 0 baseline: `route-snapshot.json`, `ROUTES.md`, `FORMS.md`, `VIEWS_AND_JS.md`, `VISUAL_BASELINE.md`, `TESTS_AND_TOOLING.md`, `asset-hashes.txt`, `page-metrics.json`, `screenshots/` | Phase 0 |
| `student-go-docs/design-execution/reports/` | Phase completion reports | Phase 0 |
| `student-go-design-docs/`, `student-go-design-docs.zip` | Original delivery of this documentation pack; superseded by the root copies; leave untouched | VERIFIED |
| `student-go-assets/` | Custom asset package (untracked; 111 hashed files verified; 60 catalogue photos + 61 originals present but unreviewed) | VERIFIED |
| `docs/` | `Proposal_StudentGo.pdf`, `Student_Go_Audit_Desain_ID.pdf` (the design audit, reference A) | VERIFIED |
| `routes/web.php` (487 lines), `routes/api.php` (216), `routes/console.php` | Routes; middleware aliases in `bootstrap/app.php` (`check.user.type`, `admin`, `institution`, `verified`) | VERIFIED |
| `app/Http/Controllers/{Student,Institution,Company,Admin,Auth,API}/` | Controllers by role | VERIFIED |
| `app/Services/` | Business services (Supabase storage, certificates, notifications, chatbot/AI, BPS API) | VERIFIED |
| `app/Models/`, `database/migrations/` (60), `database/seeders/` (17), `database/factories/` (3) | Data layer | VERIFIED |
| `resources/views/{layouts,components,partials,auth,home,about,contact,student,institution,company,admin,notifications,emails}/` | Blade views by area | VERIFIED |
| `resources/css/` | `app.css` (Tailwind entry) plus `auth*.css`, `browse-problems.css`, `friends.css`, `navbar-dropdown.css` (not built; see baseline/VIEWS_AND_JS.md) | VERIFIED |
| `resources/js/` | `app.js` (empty), `bootstrap.js` (unused), `pages/browse-problems.js`, `utils/wishlist.js` (neither built nor served) | VERIFIED |
| `public/` | 38 existing files (photographs, logo, team portraits, favicon, robots, `assets/certificate/**`) + generated `build/`. Never deleted, overwritten, moved or renamed without explicit phase authority; hashes in `baseline/asset-hashes.txt` | VERIFIED |
| `public/assets/certificate/` | **Protected** certificate template and fonts (D-32) | VERIFIED |
| `resources/css/student-go/` (`fonts.css`, `tokens.css`, `components.css` imported by `app.css`; `legacy-overrides.css` staged, activated in Phase 3), `public/assets/{brand,fonts,icons,illustrations,photography}/`, `scripts/fetch-photos.*` (not run), `resources/views/components/icon.blade.php` (`<x-icon>`), `student-go-docs/design-system/` (design system, asset sources, integration, style guide, `photo-review.json`, `c2pa-strip-hashes.tsv`) | Custom package integrated in Phase 2 (additive; see `student-go-docs/design-system/DESIGN_SYSTEM.md` section 0) | VERIFIED (Phase 2, 2026-10-30) |

## 4. Development commands

Commands below were **run successfully on 2026-10-30** (Windows 11, Git Bash, repository root). Anything not listed is unverified.

| Command | What it does | Result on 2026-10-30 |
|---|---|---|
| `git status`, `git branch --show-current`, `git log -1 --oneline` | Read-only safety checks before and after every phase | OK |
| `php artisan --version` | Framework version | `Laravel Framework 13.7.0` |
| `php artisan route:list --json` | Route snapshot (does not need the database) | 202 routes; two runs identical |
| `php artisan test` | Test suite (SQLite in memory; the `TestCase` guard keeps it off the real DB) | Phase 0: 2 passed. Phase 1: 38 passed, 158 assertions. Phase 2: 45 passed, 523 assertions. Phase 3: 63 passed, 786 assertions. Phase 4: 80 passed, 936 assertions. Phase 5: 86 passed, 1,082 assertions. Phase 6: 114 passed, 1,417 assertions |
| `npm run build` | Production Vite build (writes the generated, git-ignored `public/build/`) | Phase 2: OK, CSS 129.31 kB → 144.25 kB (gzip 19.93 → 23.52 kB). Phase 3: CSS 169.78 kB (gzip 27.79), JS 10.76 kB. Phase 4: CSS 188.05 kB (gzip 30.63), JS 12.09 kB (gzip 4.55; adds `hero-video.js`). Phase 5: CSS 200.83 kB (gzip 32.26; adds `public-pages.css`), JS 12.09 kB. Phase 6: CSS 209.71 kB (gzip 33.53; adds `auth.css`), JS 19.82 kB (gzip 7.13; adds `auth.js`) |
| `vendor/bin/pint --test` | Style check only (never run Pint without `--test` unless a phase asks) | Exit non-zero: 194 files fail the default preset (pre-existing) |
| `php artisan serve --host=127.0.0.1 --port=8000` | Dev server | OK (`/forgot-password` fixed in Phase 3; `/dev/login` not checked) |
| `php artisan config:clear` | Reload `.env` after editing it | OK |
| `php artisan migrate:status` | Read-only migration state | OK (60 ran) |
| `php artisan migrate` | Ran on the **empty** new database with explicit approval (Phase 0), then once more in Phase 1 for one approved additive migration (D-06) | 60/60, then 61/61 |
| `php artisan db:seed --class=<Seeder>` | Ran once per seeder on that empty database with explicit approval (full `db:seed` fails in `ProvincesRegenciesSeeder`'s BPS path; see `baseline/TESTS_AND_TOOLING.md`) | OK |
| `python <script>` with Playwright (outside the project) | Screenshots and page metrics | OK |

Not run in Phase 0 (and why): `composer install`, `npm ci` (dependencies already present); `npm run build` (not needed; it also cannot fix the `layouts.auth` Vite error); `composer setup`, plain `php artisan db:seed` (fails on the BPS region path), `migrate`/seed again (the database now holds seeded data: never re-run without approval); `php scripts/fetch-photos.php` (does not exist in the app yet).

Never run: `php artisan migrate:fresh`, `migrate:refresh`, `db:wipe`, `db:seed` against a database with data you did not create, `git reset --hard`, `git clean -fd`, `rm -rf` on anything you did not create, or any command that sends email or contacts a production service. Never request GET `/student/chatbot/create` (it creates a record).

## 5. Coding conventions

Observed in the repository (Stage One) — match them in every file you touch:

- Controllers are grouped by role namespace (`App\Http\Controllers\Student\…`, `Institution\…`, `Company\…`, `Admin\…`); role access uses the `check.user.type:<role>` middleware alias; route names follow `<role>.<area>.<action>` (`student.browse-problems.index`). Never rename them (section 7).
- Views live under `resources/views/<role>/<area>/<action>.blade.php`, extend `layouts.app`, and fill `@section('title')` and `@section('content')`; page CSS/JS goes through `@push('styles')` / `@push('scripts')`. Partial pieces live in a `components/` sub-folder of the area and are pulled in with `@include` (e.g. `student/browse-problems/components/`). Interactive pieces use Alpine `x-data` objects defined in an inline `<script>`.
- Code comments are short, lower-case Indonesian. Validation messages are Indonesian (form requests in `app/Http/Requests/`).
- Pint is not configured; the existing code does not follow the default preset. Do not reformat files you do not otherwise change.

Rules for design code (planning decisions, unchanged):

- Student Go component classes use the `.sg-*` prefix and live in `resources/css/student-go/components.css`; design tokens live in `tokens.css` (Tailwind v4 `@theme` plus semantic variables — supported by the installed Tailwind 4.1.16). Do not scatter hex values in views.
- No inline `<style>` blocks in new or rewritten views; no inline colour or font values; no new CDN tags; no `outline-none` without a replacement focus style.
- New fonts, icons and images are local files (`public/assets/**`, never inside `public/assets/certificate/`); icons come from the Lucide sprite. Photographs come from the existing `public/` library first (once rights are confirmed, D-30) and from the catalogue only for gaps (D-33). Existing assets are reconciled, not discarded (AD-09).
- UI copy is Indonesian first, in the words of the approved copy deck (D-02). Status words come from one mapping (status enums: `DISCOVERY_REPORT.md` S1-20).
- Small, reviewable changes. Branch/commit policy: D-27 (for now: stay on the current branch, never commit unless asked). No drive-by refactors, no dependency bumps.

## 6. Design principles (approved by the user)

- **Hybrid direction.** 'Ruang Kerja' (workbench) is the foundation: calm neutral surfaces, strong type hierarchy, tables and lists for records, colour reserved for meaning. 'Catatan Lapangan' (field notes) is a selective editorial layer: a reading serif, a single earth accent, coordinate labels, route-line motifs and documentary photography, used only on the homepage, About, project brief, repository document and student story/portfolio.
- **Custom identity.** The product looks like Student Go: one brand colour (proposed `#405080`), original wordmark and symbol, original SVG for identity graphics (map, empty states, workflow diagrams).
- **Neutral colour system.** One neutral ramp with named roles; semantic pairs; one status vocabulary; difficulty is a neutral meter; SDG colours only as small swatches; role colour only on the role badge.
- **One type system.** Plus Jakarta Sans for UI, Source Serif 4 only for long reading, tabular figures in tables; nine-step scale. (Proposed in the asset package; approval is D-17.)
- **Licensed or owned assets only, and nothing discarded.** New fonts are OFL, Lucide is ISC, catalogue photographs are Unsplash/Pexels. Existing application assets are kept and judged one by one (AD-09); their rights are confirmed by the user (D-30) before they get a new or larger placement. Nothing hot-linked.
- **Anti-AI-slop guardrails** (audit section H): 1. No hero on task pages: the page starts with an <h1> page header and the primary action within the first 160 px; 2. Every number is real and useful: no marketing counters, no trend chip without two periods of data, no chart under five points; 3. Cards earn their place: default to table, list or divided sections; cards only for an optional gallery view; 4. One elevation rule: borders and dividers group content; shadow only on menus, dialogs, toasts and sticky bars; 5. No gradients on controls; at most one brand surface; 6. Pill means status: buttons use the standard radius; chips and badges are only for status and taxonomy; 7. Images only when they carry content: no placeholder tiles; initials or nothing when there is no logo or photo; 8. Motion has a job: 150-200 ms colour/opacity on state changes only; everything off under prefers-reduced-motion; 9. Text comes from the domain: Indonesian first, KKN/SDG/region examples, one word per status on every screen; 10. Design with the real dataset: check every list against 77 problems, 216 documents, a 76-character institution name and an empty account.

## 7. Functional preservation rules (global)

The design is presentation. These rules apply to every phase and are repeated by reference in each phase file's section J.

1. Do not add, remove, rename or re-verb a route, route name, middleware or route parameter. Only the defects approved under D-25 may change behaviour, and only in Phase 1 (and the pages they affect).
2. Keep every form's `action`, HTTP method (including @method spoofing), `enctype`, CSRF token and every input `name`/`id` that JavaScript or the controller reads. Re-style, regroup and relabel; never rename.
3. Keep validation behaviour: server-side rules untouched, `old()` repopulation, `$errors` display, and flash/session message keys. Visual placement may change.
4. Keep query-string contracts for lists: `page`, `search`, `q`, `status`, `major`, `university_id`, and every filter name found in Stage One.
5. Keep authentication and authorisation behaviour: guest redirects, role redirects (including the silent redirect of cross-role access), ownership checks (other users' application IDs return 404), 403 for non-admin on /admin/*.
6. Keep JavaScript behaviour that the product relies on: the province-to-regency dependent selects via /api/regencies/{id}, the Leaflet maps, Alpine-driven steppers, toggles, dropdowns and the About slider's content (the slider may become a scrolling page only through Phase 5).
7. Keep file-upload behaviour: field names, accepted types and size hints, drop zones, and storage paths.
8. Keep data meaning: never invent statuses, counts, names, testimonials, metrics, or images. A number is shown only if it comes from a real query.
9. Keep redirects such as /student/friends/profile/{id} -> /profile/{username}, /company/talents/compare -> /company/talents (without selection), unknown chatbot id -> list with a 'not found' message.
10. Keep accessibility gains monotonic: never remove a label, alt text, aria attribute or heading that exists today.
11. Keep every existing asset until a later phase authorises its replacement: never delete, overwrite, move or rename a file under `public/`; stop referencing a file from a template only in the phase that replaces it, and record the decision (Retain, Reuse, Adapt, Replace, Deprecate) in `ASSET_INVENTORY.md`. Deprecated means 'no longer used in new UI', not 'deleted'.
12. Preserve genuine user photographs and functional feature assets: student profile photos and uploads, institution and company logos, verification documents, problem gallery uploads. Do not expose a private or personal image on a public page, or in a larger placement, without checking its intended use and access permissions (D-04, D-30, D-31).
13. Preserve certificate resources (templates, Pinyon Script, Poppins and whatever generates the certificates) exactly as they are. Certificate typography is independent of website typography: removing Google Fonts links, changing the site font stack or editing the layout must not change certificate output (D-32).

## 8. Phase execution protocol (mandatory)

1. Wait for an explicit user request that names one phase (for example 'Execute Phase 8'). Never infer a phase from context, never start the 'next' phase on your own, and never treat 'continue' as permission to start another phase.
2. Read, in this order: `CLAUDE.md`, `student-go-docs/design-execution/EXECUTION_ROADMAP.md`, the requested `student-go-docs/design-execution/phases/PHASE_NN_*.md`, then `student-go-docs/design-execution/PHASE_TRACKER.md`. Skim `student-go-docs/design-execution/DISCOVERY_REPORT.md` and `student-go-docs/design-execution/DECISIONS_AND_OPEN_QUESTIONS.md` for the decisions that gate the phase, and read the phase's section H together with `student-go-docs/design-execution/ASSET_INVENTORY.md` for every asset the phase may touch (existing `public/` assets and the custom package are both in scope; AD-09).
3. If this file still carries the PROVISIONAL banner and the requested phase is not Phase 0, stop and tell the user that Stage One (inside Phase 0) must run first.
4. Check dependencies in the tracker. Every hard dependency must be COMPLETE. If one is not, stop and say which. For soft dependencies, warn and continue only if the user confirms.
5. Check the gating decisions listed in the phase file. For any that is still OPEN in `DECISIONS_AND_OPEN_QUESTIONS.md`, ask the user; skip the tasks that depend on it; never choose for the user.
6. Safety checks before touching anything: `git status` (stop if the tree has unrelated uncommitted changes), note the current branch, follow the branch policy decided in D-27, and do not overwrite anyone's unrelated work.
7. Set only that phase's row in the tracker to IN_PROGRESS.
8. Execute the tasks in section F in order. Stay inside the phase's scope. If a task needs a file or behaviour outside it, stop and ask.
9. Test: run section K and check every box in section L, collecting evidence (command output, screenshots, diffs).
10. Update the tracker: COMPLETE only if every acceptance criterion is met; PARTIAL (list what failed) or BLOCKED (state what unblocks it) otherwise. Add the date and, only if the user asked for a commit, the commit hash.
11. Report to the user: files changed, evidence for each acceptance criterion, deviations, decisions needed, and which phases the roadmap now allows.
12. Stop. Do not begin another phase. Never run multiple phases automatically.

## 9. Reference documents

| Reference | Status | Where |
|---|---|---|
| A. Design audit ('Student Go — Audit Desain & Discovery Desain', PDF) | Available; its findings are cited as OBSERVED with backlog IDs J1-J38 and screenshot IDs | `docs/Student_Go_Audit_Desain_ID.pdf` (in the repository, untracked) |
| B. 'Design Implementation Plan' | **NOT FOUND** at planning time. Nothing was substituted silently. See D-16. | — |
| C. 'Custom Visual & Asset Plan' | **NOT FOUND as a file.** The asset package built earlier follows the earlier brief and may overlap, but it is not confirmed to be C. See D-16. | `student-go-assets` package (docs/DESIGN_SYSTEM.md, INTEGRATION.md, ASSET_SOURCES.md after Phase 2) |
| Asset package `student-go-assets/` | At the repository root (untracked). 111 hashes re-verified in Phase 0 (0 mismatches). Not yet integrated. 60 catalogue photographs are now present as WebP (+ 61 originals) although the manifest says 'not yet downloaded'; not reviewed (D-19, D-33) | To be copied in Phase 2 per `ASSET_INVENTORY.md` section 10 |
| Asset inventory (AD-09) | Updated in Phase 0: 38 `public/` files listed and hashed, usages verified in source, certificate assets and upload storage located (section 1.5). Photo rights still open (D-30) | `student-go-docs/design-execution/ASSET_INVENTORY.md` |

**Asset policy (AD-09).** Three sources are all in scope and none is disposable: (A) existing assets in `public/`, (B) the custom package `student-go-assets/`, (C) `resources/` (the Blade, CSS and JS that show what is really used). Each asset has one decision: Retain, Reuse, Adapt, Replace or Deprecate, recorded in `ASSET_INVENTORY.md`. Preserve genuine student photographs, functional feature assets and certificate resources (templates, Pinyon Script, Poppins); certificate typography is separate from website typography. Replace only what conflicts with the design or has a technical problem. Never delete, overwrite, move or rename an existing asset, never copy anything into a production asset directory, and never download or generate images unless the phase you were asked to run says so. Deprecated means 'not used in new UI', not 'deleted'.

## 10. Safety rules

- Run `git status` before and after every phase. Never overwrite, revert or stash changes you did not make. Never commit unless the user asks; never commit `.env`, credentials, `vendor/` or `node_modules/`.
- No destructive changes: no data deletion, no `migrate:fresh`/reseed on data you did not create, no removal, renaming, moving or overwriting of files you did not add (this includes every file under `public/`), no force-push.
- Do not invent data: no fake statistics, testimonials, names, statuses, images or quotes. A number on screen must come from a real query.
- No unnecessary dependencies. Any new composer or npm package needs the user's explicit approval, with the reason.
- No unverified NEW assets: no hot-linked images or fonts, no download from a source not in the catalogue, no new asset without a recorded licence. Existing assets keep working; their provenance is recorded in `ASSET_INVENTORY.md` and unconfirmed ones get no new or larger placement (D-30).
- Never call a state-changing GET (for example `/student/chatbot/create`) while testing, never submit a form against anything but the local seeded test database, and never use real users' data.
- Never print or store secrets (`.env` values, DB host/user, tokens, test passwords). Debug pages leak them: do not copy them into docs, commits or reports.
- If a fact is not verified, label it. Do not present assumptions as confirmed.
- If something is blocked, say so and stop that task; do not work around a missing decision or missing data.

## 11. Evidence labels

| Label | Meaning |
|---|---|
| VERIFIED | Confirmed directly during this discovery session (2026-10-30): either read live in the browser on the running app at http://127.0.0.1:8000 (read-only GET requests, no form submitted, not logged in), or read from a file that was actually opened. No repository file was opened, so VERIFIED never means 'confirmed in source' unless the text says so explicitly (a few file paths and versions leaked through Laravel's own debug error pages and are marked VERIFIED with that origin). |
| OBSERVED | Reported by the audit (run 2026-10-28 against port 8001, three test accounts) and not re-checked in this session. Treat as strong evidence about the app as it was on 28 Oct, not as proof about today's code. |
| INFERRED | A reasonable conclusion from VERIFIED/OBSERVED facts that nobody has confirmed. Must be confirmed before it is relied on. |
| UNKNOWN | No evidence either way. Mostly repository facts (Blade structure, controllers, models, middleware, tests, versions in composer/npm files). |
| BLOCKED | Cannot be investigated from the discovery session at all (no repository access, no admin credentials, no seeded records, tool restrictions, unreachable database). A BLOCKED item names what would unblock it. |

## 12. Stage One (before any other phase)

Stage One is the first block of `PHASE_00_READINESS.md`: a read-only pass over the repository that checks every PROVISIONAL statement in this file and in `DISCOVERY_REPORT.md`. It exists because the plan was drafted without repository access (AD-03). When it is done, this file's banner, commands (section 4) and conventions (section 5) are rewritten from facts. **Done 2026-10-30** (Phase 0); results in `DISCOVERY_REPORT.md` section 12.
