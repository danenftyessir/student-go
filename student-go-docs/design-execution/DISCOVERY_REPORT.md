# DISCOVERY REPORT — Student Go

Written 2026-10-30 by a discovery-and-planning session. **Nothing was implemented, designed or executed.** No application file, route, database record or setting was changed; the only files created are the planning documents listed in `CLAUDE.md` section 3.

## 1. Evidence labels and method

| Label | Meaning |
|---|---|
| VERIFIED | Confirmed directly during this discovery session (2026-10-30): either read live in the browser on the running app at http://127.0.0.1:8000 (read-only GET requests, no form submitted, not logged in), or read from a file that was actually opened. No repository file was opened, so VERIFIED never means 'confirmed in source' unless the text says so explicitly (a few file paths and versions leaked through Laravel's own debug error pages and are marked VERIFIED with that origin). |
| OBSERVED | Reported by the audit (run 2026-10-28 against port 8001, three test accounts) and not re-checked in this session. Treat as strong evidence about the app as it was on 28 Oct, not as proof about today's code. |
| INFERRED | A reasonable conclusion from VERIFIED/OBSERVED facts that nobody has confirmed. Must be confirmed before it is relied on. |
| UNKNOWN | No evidence either way. Mostly repository facts (Blade structure, controllers, models, middleware, tests, versions in composer/npm files). |
| BLOCKED | Cannot be investigated from the discovery session at all (no repository access, no admin credentials, no seeded records, tool restrictions, unreachable database). A BLOCKED item names what would unblock it. |

**Method.** (1) Read the design audit end to end (PDF, run 2026-10-28 on port 8001; sections A-M and the 56-page screenshot catalogue). (2) Read-only inspection of the running app on http://127.0.0.1:8000 in the user's browser tab, **not logged in**: public pages, page source facts through the DOM, a few GET fetches of public routes, two screenshots of error pages. No form was submitted, no login was attempted, no state-changing URL (`/student/chatbot/create`, `/institution/reviews/create`, logout) was opened. (3) Read the earlier asset package (`student-go-assets`) for what already exists. (4) The repository, `php artisan route:list`, admin access, test credentials and seeded record pages were **not reachable**.

## 2. Executive summary

- Student Go works end to end for three roles but does not read as one product: three names (Laravel/Karsa/KKN Go), four web-font families in three stacks, six competing accents, three header systems, four card anatomies (OBSERVED; titles and fonts re-VERIFIED).
- The audit catalogued **59 route patterns** (13 public/shared, 21 student, 11 institution, 12 company, 1 admin, 1 JSON API) and **56 screens**. All are mapped to phases in `EXECUTION_ROADMAP.md`. Four record routes are INFERRED, `/admin/dashboard` is BLOCKED, and `/forgot-password` is now VERIFIED to exist but currently crashes (Vite manifest).
- The audit's backlog has **38 items**: 4 P0 (J1-J4: identity, lists hiding records, guest-visible contact data, contradictory counts), 12 P1, 15 P2, 7 P3. P0 items are fixed first (Phase 1) because a design must not mask them.
- **New on 2026-10-30 (VERIFIED):** on the local app the database is unreachable (Supabase pooler error 'tenant/user not found'), so every DB-backed page returns Laravel's debug page; static pages render. Debug mode is on and leaks stack traces. The front-end build is byte-for-byte the audit's (same hashed filenames), so the audit's front-end findings still apply. Framework facts leaked by the debug page: Laravel 13.7.0, PHP 8.3.24, PostgreSQL, Windows-style paths.
- Reference documents B ('Design Implementation Plan') and C ('Custom Visual & Asset Plan') **were not found**. Nothing was substituted silently (D-16).
- Everything about code structure (Blade, controllers, models, tests, seeders, composer/npm files) is UNKNOWN/BLOCKED. Stage One in Phase 0 closes that gap; the checklist is in section 12.
- There are **35 open decisions** (naming, language, privacy, data, scope, tooling, assets) in `DECISIONS_AND_OPEN_QUESTIONS.md`; recommendations are kept separate from approvals. Nine decisions are already approved by the user (AD-01 to AD-09).
- **Asset reconciliation (AD-09, same date).** Three asset sources are in scope: existing `public/` assets (A), the custom package `student-go-assets/` (B) and `resources/` (C). A is partly readable through the running app (27 files VERIFIED to exist, sized and looked at); C is BLOCKED; B was read recursively. Seven existing photographs are genuine KKN field images that the audit calls the most authentic assets; they are Retained and the best are Reused. Rights and consent for every existing photograph are undocumented (D-30). Certificate resources (templates, Pinyon Script, Poppins) could not be located and are protected (D-32). Master list: `ASSET_INVENTORY.md`.

## 3. Sources

| Source | Status | Used for |
|---|---|---|
| A. Audit PDF 'Student Go — Audit Desain & Discovery Desain' | Available (supplied by the user). Run 2026-10-28, port 8001, three test accounts. | Routes, findings, backlog, screenshots, limits |
| B. 'Design Implementation Plan' | NOT FOUND | — |
| C. 'Custom Visual & Asset Plan' | NOT FOUND as a file (the asset package may overlap; unconfirmed) | — |
| Live app at 127.0.0.1:8000 | Reachable, not logged in; DB unreachable | VERIFIED facts in sections 4, 10, 11 |
| Asset package `student-go-assets` (built in an earlier session) | Available in the session workspace (124 files, 111 manifest hashes verified); not in the repository | Section 8; `ASSET_INVENTORY.md` section 2; Phase 2 inputs |
| Existing `public/` assets (Source A) | 27 files readable through the running app (HTTP 200, sizes, pixel dimensions, thumbnails viewed); folder listing, uploads and certificate files BLOCKED | `ASSET_INVENTORY.md` section 1 |
| `resources/` Blade, CSS, JS (Source C) | BLOCKED. Usage inferred from rendered guest HTML, built Vite files and the audit | `ASSET_INVENTORY.md` section 3 |
| Repository | BLOCKED (no linked computer) | — |
| `php artisan route:list` | BLOCKED | — |
| Admin credentials, seeded institution/company records | BLOCKED / absent in the audit | — |

## 4. Architecture and stack

| Area | Finding | Evidence |
|---|---|---|
| Framework / language | Laravel 13.7.0, PHP 8.3.24 | VERIFIED (debug page) |
| Database | PostgreSQL via `pgsql` connection to a Supabase pooler; unreachable on 2026-10-30 (SQLSTATE 08006, 'tenant/user not found') | VERIFIED |
| Views | Blade; `resources/views/layouts/auth.blade.php` exists and calls `@vite` for `resources/css/auth-institution.css` | VERIFIED for that file |
| Controllers | `app/Http/Controllers/Auth/RegisterController.php` queries `universities` at line 67 | VERIFIED |
| Asset pipeline | Vite with hashed bundle files `app-CDP40zpX.css` and `app-34mOoJaZ.js` (the JS file was 0 bytes on 2026-10-28) | VERIFIED names; size OBSERVED |
| CSS | Tailwind CSS 4.1.16, default theme, no custom tokens (compiled CSS 125,756 bytes) | OBSERVED |
| JS libraries | Alpine 3.13.3 (jsDelivr), Leaflet 1.9.4 (unpkg), AOS 2.3.1 (unpkg), Boxicons 2.1.4 (unpkg, 0 uses), Font Awesome 6.5.1 (cdnjs, 2 uses) | VERIFIED on `/`; usage OBSERVED |
| Fonts | Google Fonts: Inter 400/500/600/700/900, Outfit 600/700, Lora 400, Merriweather 700 loaded on `/`; auth pages request none (system fallback); `--font-sans` is 'Instrument Sans' with no @font-face | VERIFIED on `/`; rest OBSERVED |
| Maps | Leaflet with OSM tiles on the homepage and the project map view | VERIFIED library; OBSERVED views |
| Auth and roles | Session login for student, institution, company; admin exists (`/admin/dashboard` 403 for non-admin); cross-role access silently redirects to the user's own dashboard | OBSERVED |
| Data (audit dataset) | 700 students, 77 problems (ID 1-77), 216 repository documents (header says 221), 79 institutions, 0 jobs, 129 companies expected but no public route, 310 problem gallery images on remote Supabase storage | OBSERVED |
| Regions | 34 provinces; `/api/regencies/{id}` returns a placeholder 'Kota <Provinsi>' for most provinces (real data for about 5) | OBSERVED / INFERRED count |
| Dev environment | Server on 127.0.0.1:8000 (audit on 8001); backslash paths in stack traces suggest a Windows host; how it is started is unknown | VERIFIED / INFERRED |
| Debug mode | On | VERIFIED |
| Tests, CI, factories | — | UNKNOWN |
| Composer/npm dependency list | — | UNKNOWN (BLOCKED) |

## 5. Route inventory

Source: audit section B (59 patterns) with the 2026-10-30 live notes. 'Phase' is where the route is designed. See also the compact coverage table in the roadmap.

| Route pattern | Role | Audit status | Evidence | Phase | Notes |
|---|---|---|---|---|---|
| / | Public | Inspected | OBSERVED; page VERIFIED live | 4 | Title 'Laravel - ...' (VERIFIED). Hard-coded stats 34/514/85+/120+ (VERIFIED). Hero Merriweather/Lora, 7-layer mesh, Leaflet map, AOS (OBSERVED; fonts and libs VERIFIED). |
| /about | Public | Inspected | OBSERVED; title VERIFIED | 5 | Full-screen 7-slide carousel, 'About Us' in English; <title> 'Laravel - Tentang Kami - Karsa' (VERIFIED). |
| /contact | Public | Inspected | OBSERVED; title VERIFIED | 5 | <title> 'Laravel - Contact Us - Laravel' (VERIFIED). |
| /login | Guest | Inspected | OBSERVED; title VERIFIED | 6 | Split layout with invented stats; <title> 'Login - Karsa' (VERIFIED); lang id (VERIFIED). |
| /register | Guest | Inspected | OBSERVED; title VERIFIED | 6 | Role picker; <title> 'Register - Karsa' (VERIFIED). |
| /register/student | Guest | Inspected (audit); failing locally on 2026-10-30 | OBSERVED; local failure VERIFIED | 6 | 3 steps, 13 fields (OBSERVED). On 2026-10-30 it returns a QueryException on a `universities` query from app/Http/Controllers/Auth/RegisterController.php line 67 because the DB is unreachable (VERIFIED). |
| /register/institution | Guest | Inspected | OBSERVED | 6 | 4 steps, 18 fields incl. KTP/NPWP uploads (OBSERVED). |
| /register/company | Guest | Inspected | OBSERVED | 6 | No stepper, 17 fields, titled 'Daftar Sebagai Company' (OBSERVED). |
| /forgot-password | Guest | BLOCKED in audit; route exists (VERIFIED) | VERIFIED | 6 | Audit tool refused it. Live 2026-10-30: HTTP 500 ViteException from resources/views/layouts/auth.blade.php: 'Unable to locate file in Vite manifest: resources/css/auth-institution.css' (VERIFIED). Cause INFERRED: asset not in vite.config inputs or stale build. |
| /profile/{username} | Anyone incl. guest | Inspected (6 opened, 699 enumerated) | OBSERVED | 12 | Shows email and WhatsApp to guests (P0, J3); name differs from account (J1). Privacy fix in Phase 1. |
| /institution/{id} | Anyone | Inspected (4), 79 fetched | OBSERVED; VERIFIED failing locally | 5 | On 2026-10-30 /institution/19 returns a QueryException (DB unreachable) (VERIFIED). 33 of 79 institutions have no problems (OBSERVED). |
| /notifications | All roles | Inspected | OBSERVED | 16 | Same page for 3 roles (S22, I10, C09). Emoji in titles; filter selects unlabelled. |
| 404 / 403 / 500 fallbacks | Anyone | Inspected | OBSERVED; 404 and 500 VERIFIED | 26 | 404 is a bare default 'Not Found' page with no navigation (VERIFIED). 500 is a full debug page with stack trace (VERIFIED; APP_DEBUG is on). |
| /student/dashboard | Student | Inspected (desktop, 390, 768) | OBSERVED | 7 | — |
| /student/browse-problems | Student | Inspected | OBSERVED | 8 | ?page=1-7, ?search=, ?page=99. |
| /student/browse-problems/{id} | Student | 1-80 fetched; 1, 45, 52, 60, 62 inspected | OBSERVED | 9 | 77 x 200, 3 x 404. |
| /student/applications | Student | Inspected | OBSERVED | 10 | — |
| /student/applications/{id} | Student | 270, 271 inspected | OBSERVED | 10 | Other users' IDs return 404 (ownership enforced). |
| /student/applications/create/{problemId} | Student | 52, 60, 62 inspected | OBSERVED | 10 | Form is not submitted in discovery. |
| /student/projects | Student | Inspected (empty) | OBSERVED | 11 | Active-project data BLOCKED (0 records). |
| /student/repository | Student | Inspected | OBSERVED | 14 | ?page=1-19, ?search=. |
| /student/repository/{id} | Student | 1-216 fetched; 160 inspected | OBSERVED | 14 | /abc returns HTTP 500 (J13). |
| /student/friends | Student | Inspected | OBSERVED | 13 | — |
| /student/friends/search | Student | Inspected | OBSERVED | 13 | ?page, ?major, ?university_id, ?q. |
| /student/friends/profile/{id} | Student | 699 enumerated | OBSERVED | 13 | Redirects to /profile/{username}; keep the redirect. |
| /student/jobs | Student | Inspected (empty) | OBSERVED | 15 | — |
| /student/jobs/alerts | Student | Inspected + measured | OBSERVED | 15 | — |
| /student/jobs/saved | Student | Inspected + measured | OBSERVED | 15 | Only page using fa- icons (2). |
| /student/profile | Student | Inspected | OBSERVED | 12 | — |
| /student/profile/edit | Student | Inspected | OBSERVED | 12 | Name fields load empty (J5). |
| /student/wishlist | Student | Inspected + measured (empty) | OBSERVED | 11 | — |
| /student/chatbot | Student | Inspected | OBSERVED | 16 | KARA conversation list. |
| /student/chatbot/create | Student | Fetched | OBSERVED | 16 | GET creates a record (J13). Do NOT open in discovery or tests without approval. Behaviour change in Phase 1 (D-25). |
| /student/chatbot/{id} | Student | 1, 2 (created by audit); 3 redirects | OBSERVED | 16 | No h1 (J11). |
| /institution/dashboard | Institution | Inspected | OBSERVED | 17 | — |
| /institution/problems | Institution | Inspected (empty) | OBSERVED | 18 | Statuses in English: Draft/Open/In Progress/Completed. |
| /institution/problems/create | Institution | Inspected, not submitted | OBSERVED | 18 | 37 inputs; /api/regencies/{id} dependency. |
| /institution/problems/{id} | Institution | BLOCKED (0 records; every ID 404) | INFERRED | 18 | Route inferred; layout cannot be designed from evidence. |
| /institution/problems/{id}/edit | Institution | BLOCKED | INFERRED | 18 | Same. |
| /institution/applications | Institution | Inspected (empty) | OBSERVED | 19 | ?status=pending. |
| /institution/projects | Institution | Inspected (empty) | OBSERVED | 19 | ?status=active. |
| /institution/reviews | Institution | Inspected (empty) | OBSERVED | 19 | — |
| /institution/profile | Institution | Inspected | OBSERVED | 20 | Long email overflows the phone column (J19). |
| /institution/profile/edit | Institution | Inspected | OBSERVED | 20 | 'Jenis Instansi' not preselected (J5). |
| /institution/verification/upload | Institution | Inspected | OBSERVED | 20 | Native file input without styling. |
| /company/dashboard | Company | Inspected | OBSERVED | 21 | — |
| /company/talents | Company | Inspected + clicked | OBSERVED | 22 | 12 of 699 shown; numbered pager and load-more together. |
| /company/talents/saved | Company | Inspected (empty) | OBSERVED | 22 | CTA invisible (J33). |
| /company/talents/export | Company | Fetched read-only | OBSERVED | 22 | CSV export. Preserve. |
| /company/talents/compare | Company | Fetched | OBSERVED | 22 | Redirects to /company/talents without selection. |
| /company/jobs | Company | Inspected (empty) | OBSERVED | 23 | Good table pattern. |
| /company/jobs/create | Company | Inspected, not submitted | OBSERVED | 23 | 5-step wizard; overflows at 390 px. |
| /company/jobs/{id} | Company | BLOCKED (0 records) | INFERRED | 23 | — |
| /company/applications | Company | Inspected (empty) | OBSERVED | 24 | Full-bleed; 3 of 6 columns at 390 px; no empty state. |
| /company/applications/{id} | Company | BLOCKED (0 records) | INFERRED | 24 | — |
| /company/profile | Company | Inspected | OBSERVED | 25 | — |
| /company/profile/edit | Company | Inspected | OBSERVED | 25 | Email empty and company size unselected (J5). |
| /admin/dashboard | Admin | BLOCKED (no credentials) | OBSERVED (403 for non-admin) | 26 | Not designed (D-11). 403 page only. 22 other /admin/* guesses were 404. |
| /api/regencies/{provinceId} | JSON API | Verified via form | OBSERVED | 6 | No visual; consumed by registration (Phase 6) and problem creation (Phase 18). Race condition J30. Preserve response shape. |

**Outside the 59, or not discoverable from the browser**

| Pattern | Role | Status | Evidence | Phase | Note |
|---|---|---|---|---|---|
| /institution/reviews/create | Institution | Probed by guess | OBSERVED | 19 | Renders a full Illuminate\Database\QueryException debug page (J13). Also relevant to Phase 1. |
| /student/repository/abc | Student | Probed by guess | OBSERVED | 14 | Non-numeric ID returns HTTP 500 (J13). Fix in Phase 1; error page in Phase 26. |
| logout (path and verb UNKNOWN) | All | Used by the audit | INFERRED | 3 | The audit logged out; the route is not in the inventory. Normally a POST form in the nav menu. |
| Password reset and email verification routes | Guest | Not discovered | UNKNOWN | 6 | Standard Laravel auth names, if present. Stage One lists them. |
| Non-GET action routes (apply, cancel application, add connection, wishlist toggle, save job/talent, save search, notification mark-read/delete, chatbot send/archive/delete, profile update, verification upload) | Student/Institution/Company | Not enumerable from GET crawling | UNKNOWN | 0 | Stage One lists them from `route:list` and records form actions per page so designed forms keep posting to the same endpoints. |

**Route facts checked live on 2026-10-30 (not logged in; GET only)**

| URL | Result | Evidence |
|---|---|---|
| `/` | 200; title 'Laravel - Platform Digital untuk Kuliah Kerja Nyata Berkelanjutan'; `lang="en"`; nav About Us/Contact/Login/Register | VERIFIED |
| `/about` | 200; title 'Laravel - Tentang Kami - Karsa'; `lang="en"`; h1 'About Us'; no forms | VERIFIED |
| `/contact` | 200; title 'Laravel - Contact Us - Laravel'; `lang="en"`; h1 'Contact Us'; no forms | VERIFIED |
| `/login` | 200; title 'Login - Karsa'; `lang="id"`; one form, three inputs; h1 'Connect. Collaborate. Create Impact.' | VERIFIED |
| `/register` | 200; title 'Register - Karsa'; `lang="id"`; h1 'Bergabung dengan Karsa' | VERIFIED |
| `/register/student` | 500 QueryException on a `universities` query (DB unreachable) | VERIFIED |
| `/forgot-password` | 500 ViteException: 'Unable to locate file in Vite manifest: resources/css/auth-institution.css' from `layouts/auth.blade.php` | VERIFIED |
| `/institution/19` | 500 QueryException (DB unreachable) | VERIFIED |
| `/zz-not-a-route` | 404 bare default page ('404 \| Not Found'), no navigation, no branding | VERIFIED |
| `/register/institution`, `/register/company` | Not re-checked in this session | OBSERVED (audit) |

## 6. Page and role structure

Three shells: public (top nav + dark footer), authenticated (role-specific top nav, bell, avatar menu + the same footer), auth (full screen without nav), plus Laravel's default error pages. (OBSERVED)

| Dimension | Student | Institution | Company |
|---|---|---|---|
| Home | /student/dashboard | /institution/dashboard | /company/dashboard |
| Nav items | Dashboard, Telusuri Masalah, Aplikasi Saya, Proyek Saya, Repositori, Jobs (+ bell, avatar menu: profile, wishlist) | Dashboard, Masalah, Aplikasi, Proyek, Ulasan (+ bell, avatar: Profil Instansi) | Dashboard, Talents, Jobs, Applications, Saved Talents (+ bell, avatar: Profil Perusahaan) |
| Nav language | Indonesian + 'Jobs' | Indonesian | English |
| Page header | Photo hero with white text on most pages | Plain title + subtitle, no photo | Stock-office photo hero on every page |
| Accent colours | Indigo / purple / yellow | Blue + green avatar | Amber/orange avatar + purple filters |
| Main object | Problem (KKN project) | Own problems + applicants | Jobs + talents |
| Main tasks | Find, apply, monitor, network | Publish, review applicants, monitor, review students | Post jobs, search talent, run applicant pipeline |
| Audit test data | 2 applications, 3 notifications, 0 projects, 0 connections | 0 problems/applications/projects/reviews; verification pending | 0 jobs/applications; 12 talent suggestions |
| Container | max-w-7xl | max-w-7xl; forms max-w-3xl | max-w-7xl except applications (full-bleed) |
| Cross-role access | `/institution/*`, `/company/*` silently redirect to `/student/dashboard` | Silently redirected to own dashboard | Silently redirected to own dashboard |

Evidence for the whole table: OBSERVED (audit F). Admin: `/admin/dashboard` exists, guest redirected to login, non-admin 403 'Unauthorized. Hanya admin yang dapat mengakses'; no other admin route found among 22 guesses; admin UI BLOCKED. The institution page text mentions automatic verification using AI; an admin or automatic review flow is INFERRED but invisible.

## 7. Shared components and UI patterns

| Pattern | Variants found | Verdict (audit) |
|---|---|---|
| Page header | Photo hero with white text; plain title + subtitle; full-screen photo (about, contact) | Three systems for one function; keep the plain one |
| Stat tiles | Icon in tinted box + number + label, 3-5 per row on almost every page, shown even when all zero | Replace with one summary line |
| Filter bar | Top search; left sidebar with checkboxes; horizontal select row | Browse shows two searches for one query |
| Record card | Problem, talent, repository, connection: four anatomies | Default to table/list |
| Status badge | Pastel pills, English and Indonesian mixed | One vocabulary |
| Empty state | Grey outline icon + title + one line + optional CTA | Most consistent pattern; keep |
| Image placeholder | Blue icon tile, broken `<img>` with alt text, grey 'No Image' box, initials avatar | Four strategies; remove placeholders |
| File upload | Dashed dropzone (apply, register), bordered input (edit profile), raw native input (verification) | One component |
| Pagination | Laravel paginator, custom Alpine pager + 'Muat Lebih Banyak' (talents), numbered only | One paginator |

Measured on the compiled CSS and 25 rendered pages (OBSERVED, audit C): 12 text sizes; font weights medium 728 / semibold 371 / bold 287 / black 8; radius values rounded-lg 620, md 236, full 200, xl 110, base 69, 2xl 33, 3xl 9; shadows sm 156, lg 149, md 58, xl 35, 2xl 8; 73 gradient classes on browse-problems alone; 349 transitions; hover scale x60; translate-y x24; no `prefers-reduced-motion` rule; 59 `outline-none`, 0 `focus-visible`; undefined `bg-primary-600/700` x25.

## 8. Asset inventory

**Existing in the app (Source A; full detail in `ASSET_INVENTORY.md` section 1)**

- 27 files under `public/` were read through the running app (VERIFIED 2026-10-30): the logo `karsa-logo.png` (1430x566, 272 KB, drawn at 40 px), 21 photographs (seven genuine KKN field photographs at 4912x2760, landscapes and aerials of unknown origin, six corporate stock-style office images, a 1.7 MB gradient background `KARA-background.png`), two team portraits, an empty `favicon.ico` (0 bytes) and `robots.txt`. One more portrait is referenced by `/contact` but missing.
- Rendered guest pages reference: `/` logo + `hero-background.jpg`; `/about` logo + `mahasiswa-about.jpeg`, `about.jpg`, `handprints-about.jpeg`, `jaket-about.jpeg`; `/contact` logo + `contact.jpg` + three team portraits (two broken); `/login` logo + `login-register-view.jpg`; `/register` logo. Authenticated-page usage is OBSERVED (audit) or INFERRED (file names).
- Weight: `about.jpg` 3.6 MB (7360x4912), `dashboard-student3.jpg` 3.3 MB, `KARA-background.png` 1.7 MB, the seven field photographs 0.4-1.2 MB each.
- No credit, licence or consent record exists for any of them (UNKNOWN; D-30).
- Problem gallery: 310 images on remote Supabase storage, none load locally. Uploads (avatars, logos, verification documents) and certificate resources: location UNKNOWN / BLOCKED.
- Icons: inline SVG outline set (Heroicons-like, 10-138 per page); Boxicons and Font Awesome loaded but nearly unused. Hand-drawn generic SVGs on the homepage (mock UI with star badge, flat building with round figures): markup in Blade.
- Built output: `public/build/manifest.json` (entries `resources/css/app.css`, `resources/js/app.js`), a 125,756-character CSS file with no image or font URLs, and a 0-byte JS file.

**Custom asset package `student-go-assets` (Source B; built earlier; verified in isolation; not integrated; not approved: D-17). Complementary to Source A, not a replacement for it (AD-09)**

- CSS: `resources/css/student-go/{fonts,tokens,components}.css` (Tailwind v4 `@theme`, semantic variables, `.sg-*` components).
- Original SVG: brand (wordmark, symbol, on-dark variants, favicon set), 5 empty-state spots, 4 workflow diagrams (horizontal and vertical), Indonesia region map and outline (Natural Earth, public domain), motifs (contour field, survey marks, map pins).
- Icons: 71 Lucide icons (ISC) at 1.5 px stroke with sprite and `icons.json`.
- Fonts: Plus Jakarta Sans and Source Serif 4, WOFF2, OFL texts. IBM Plex Sans was evaluated and not shipped (Plus Jakarta Sans measured 5.0% wider).
- Photography: 60 records in `docs/photo-catalog.json` (homepage 6, about 4, library 50: pertanian 13, pesisir 12, pasar 9, pendidikan 8, desa 7, komunitas 1), Unsplash/Pexels licences read page by page; **not downloaded and not visually reviewed; deferred by D-33 because existing field photographs may already fill the slots**. The catalogue marks 5 photographs with minors and 13 with unknown people status. Downloader `scripts/fetch-photos.php` v1.2 with a system-curl fallback (the user hit a Windows certificate-path error with v1.1).
- Docs: DESIGN_SYSTEM.md, INTEGRATION.md (including the P0 issues 'not fixed here'), ASSET_SOURCES.md, style guide HTML + screenshots, `assets-manifest.json` with SHA-256 hashes.

## 9. Test setup and dependencies

| Item | Finding | Evidence |
|---|---|---|
| Test framework / suite / CI | — | UNKNOWN (BLOCKED) |
| Factories and seeders | The audit's dataset (700 students etc.) implies seeders; whether they can be re-run is unknown | INFERRED / UNKNOWN |
| Test accounts | The audit used three accounts (student 'Julia Suryanto', institution 'Dinas Kesehatan Kabupaten Bogor', company 'Koperasi Tani Makmur Jaya'). Credentials are not in this session and must not be guessed or written into docs | OBSERVED names; credentials BLOCKED |
| Composer / npm dependencies | — | UNKNOWN (BLOCKED) |
| Browser tooling available to the planning session | Chromium + Playwright in the cloud sandbox; the user's Chrome tab on 127.0.0.1:8000 | VERIFIED |
| Real devices, screen readers | None used by the audit; none available here | BLOCKED (D-24) |

## 10. Audit findings verified (running app) and against the code

**Verified against the code: none.** The repository was not reachable, so no audit finding could be confirmed in source (BLOCKED; Stage One items S1-01 to S1-37; S1-29 to S1-37 are the asset checks added under AD-09). The table below records only what could be re-checked against the running app on 2026-10-30.

| Finding (audit) | Re-check result | Label |
|---|---|---|
| Tab titles start with 'Laravel' (J20) | Same on `/`, `/about`, `/contact` | VERIFIED |
| Four web fonts on public/authenticated pages (C) | Inter, Outfit, Lora, Merriweather loaded on `/` with the same weights | VERIFIED |
| CDN libraries incl. unused Boxicons and Font Awesome (J24) | Alpine, Boxicons, Font Awesome, Leaflet, AOS and two Google Fonts stylesheets requested on `/` | VERIFIED |
| Compiled bundles `app-CDP40zpX.css`, `app-34mOoJaZ.js` | Same filenames | VERIFIED |
| No usable favicon (J20) | No `<link rel=icon>` on `/` | VERIFIED |
| Homepage marketing numbers (J9) | 34 Provinsi, 514 Kabupaten/Kota, 85+ Instansi Mitra, 120+ Proyek Selesai render with the DB down: hard-coded | VERIFIED |
| Privacy link points to '#' (J20) | Footer 'Kebijakan Privasi' href '#' | VERIFIED |
| Homepage uses AOS | 14 elements with `data-aos` | VERIFIED |
| Default 404 page, unbranded (J13) | Bare '404 \| Not Found', no navigation | VERIFIED |
| Debug mode appears active (G, J13) | Debug pages with stack traces on three routes | VERIFIED |
| `/forgot-password` blocked, not inspected (G) | Route exists; returns a ViteException from the auth layout | VERIFIED (new) |
| Public pages have `lang` unset to 'id' (I baseline) | `lang="en"` on `/`, `/about`, `/contact`; `lang="id"` on `/login`, `/register` | VERIFIED |
| Identity mismatch, list repetition, guest-visible contact data, counts, edit forms losing values, mobile overflow, contrast, English statuses, chat GET mutation, 500 on `/abc`, etc. (J1-J38) | Not re-checked: they need a logged-in session or data, the DB is unreachable, no credentials are available, and some URLs change state | OBSERVED only |

## 11. Differences between the audit and the current implementation

| Topic | Audit (2026-10-28) | Now (2026-10-30) | Label |
|---|---|---|---|
| Instance | Port 8001 | Port 8000 | VERIFIED / OBSERVED |
| Database | Reachable; 700 students, 77 problems, 216 documents, 79 institutions | Unreachable: Supabase pooler 'tenant/user not found'; DB-backed pages show debug pages | VERIFIED |
| `/register/student` | Inspected: 3 steps, 13 fields, yellow stepper | 500 QueryException on `universities` | VERIFIED (env-driven) |
| `/forgot-password` | BLOCKED by the audit tool | Exists; 500 ViteException (manifest lacks `auth-institution.css`) | VERIFIED |
| Framework versions | Tailwind 4.1.16 only | Laravel 13.7.0, PHP 8.3.24 added | VERIFIED |
| Debug mode | 'Appears active' | Active | VERIFIED |
| Front-end build | `app-CDP40zpX.css`, `app-34mOoJaZ.js` | Identical | VERIFIED |
| Titles and fonts on public pages | As listed | Identical | VERIFIED |

The database difference means the app on port 8000 may be a different environment from port 8001, or the remote project/credentials changed in between (INFERRED; cause UNKNOWN). Either way, data-driven baseline screenshots are blocked until D-12 is resolved.

## 12. Verification Addendum (Stage One checklist for Phase 0)

Stage One is read-only. **Completed 2026-10-30 (Phase 0)** on branch `feat/students`, HEAD `e824b9d`: 36 items VERIFIED, 1 BLOCKED (S1-35 photo rights; the user deferred it). S1-26 (database) was resolved later the same day. In this section VERIFIED means confirmed in the repository source or by a command run in Phase 0. Baseline artefacts: `student-go-docs/design-execution/baseline/`.

| ID | Item | Feeds | Where to look | Status |
|---|---|---|---|---|
| S1-01 | Repository root, branch, HEAD, clean tree | Everything | `git status`, `git branch --show-current`, `git log -1 --oneline` | VERIFIED 2026-10-30: Laravel root = repository root; branch `feat/students`; HEAD `e824b9d fix: remove unused commands`. Tree not clean at start: user's own changes (deleted `doc/Proposal_Si Nangor_…pdf`; untracked `Project Structure.txt`, `docs/`, `student-go-assets/`, `student-go-design-docs/`, `student-go-design-docs.zip`) — preserved untouched at the user's instruction. |
| S1-02 | PHP, Laravel and package versions | Stack table (VERIFIED live: Laravel 13.7.0, PHP 8.3.24) | `composer.json`, `composer.lock` | VERIFIED: PHP 8.3.24 CLI; Laravel v13.7.0, sanctum v4.3.1, tinker v3.0.2, pint v1.29.1, phpunit 12.5.24, pail v1.2.6, pao v1.0.6, smalot/pdfparser v2.12.5, flysystem-aws-s3-v3 3.32.0 (`composer.lock`). Matches the live values. Details: `baseline/TESTS_AND_TOOLING.md`. |
| S1-03 | Node tooling and front-end packages (Tailwind 4.1.16? Vite? any chart library?) | Stack, D-20, D-21 | `package.json`, `package-lock.json` | VERIFIED: tailwindcss 4.1.16, @tailwindcss/vite 4.1.16, vite 7.3.2, laravel-vite-plugin 2.1.0, concurrently 9.2.1 (`package-lock.json`). No chart library in npm; Chart.js 4.4.1 from CDN on `company/dashboard/index.blade.php:339`. Alpine, Leaflet, AOS, Font Awesome, Boxicons come from CDNs, not npm. |
| S1-04 | Vite inputs and manifest; why `resources/css/auth-institution.css` is missing from the manifest | `/forgot-password` ViteException | `vite.config.js`, `public/build/manifest.json`, git history of the CSS file | VERIFIED: `vite.config.js` inputs are only `resources/css/app.css` and `resources/js/app.js`; `layouts/auth.blade.php:14` requests `auth-institution.css`, `auth-student.css`, `auth.css` (exist in `resources/css/` since commit `73bf936`) which were never Vite inputs → ViteException on `/forgot-password` (and every view extending `layouts.auth`). A rebuild alone does not fix it (Phase 1/6, D-29). |
| S1-05 | Tailwind v4 entry (`@import`, `@source`, `@theme`) and any CSS in `resources/css` | Phase 2 wiring | `resources/css/app.css` and siblings | VERIFIED: `resources/css/app.css` (11 lines): `@import 'tailwindcss'`, 4 `@source`, `@theme { --font-sans: 'Instrument Sans' … }` (font never loaded). Siblings: auth*.css (3), browse-problems.css, friends.css, navbar-dropdown.css — the last three are not built and the two referenced via `asset('css/…')` 404 (`public/css/` absent). |
| S1-06 | Full route list with names, methods, actions, middleware | Routes section 5 | `php artisan route:list --json` | VERIFIED: 202 routes, `baseline/route-snapshot.json` + `baseline/ROUTES.md`. `route:list` works without the database; two runs byte-identical. |
| S1-07 | The four INFERRED record routes and `/institution/reviews/create` | Phases 18, 19, 23, 24 | route list; controllers | VERIFIED: all four exist (`institution.problems.show|edit`, `company.jobs.show`, `company.applications.show`). The real review-create route is `/institution/reviews/create/{projectId}`; bare `/create` hits `institution.reviews.show` with id `create` (cause of the audit's QueryException). |
| S1-08 | Admin routes and middleware | D-11 | route list; middleware classes | VERIFIED: 9 `admin/*` routes, middleware `web, auth, AdminMiddleware`; admin = `user_type === 'admin'` (`app/Models/User.php:126-129`); non-admin → `abort(403)` (`app/Http/Middleware/AdminMiddleware.php`). No seeder creates an admin user. Admin UI still not viewed (BLOCKED: no admin account, D-11/D-13). |
| S1-09 | Password reset, email verification and logout routes | Phase 6, Phase 3 | route list | VERIFIED: `password.request|email|reset|update`, `verification.notice|verify|resend`, `logout` = **POST** `/logout` (`ROUTES.md` section 1). |
| S1-10 | Non-GET action routes (apply, cancel, connect, wishlist, saved job/talent, notification actions, chatbot send/archive/delete, profile update, uploads) | Functional preservation | route list; Blade forms | VERIFIED: 85 non-GET routes listed in `ROUTES.md`; form/JS endpoints in `FORMS.md` and `VIEWS_AND_JS.md` 3.1. No 'save search' route exists. |
| S1-11 | Layouts, partials, Blade components and page views mapped to routes | Phase 3 and all page phases | `resources/views/**` | VERIFIED: route→view map in `VIEWS_AND_JS.md` section 4 (every referenced view exists). Layouts: `layouts/app`, `layouts/auth`; 5 auth pages are standalone HTML; components `navbar`, `footer`, `notification-dropdown`; no `errors/` views; orphan candidates listed. |
| S1-12 | Inline `<style>` blocks, `@push`/`@stack` scripts, CDN tags per layout | Phases 2, 3, 4 | `resources/views/**` | VERIFIED: per-view inline `<style>`, `@push`/`@stack`, CDN hosts and `<h1>` counts in `VIEWS_AND_JS.md` section 5. CDN tags in `layouts/app.blade.php` lines 13, 19, 22, 25. |
| S1-13 | Form inventory: action, method, enctype, input names per view | Section J of every phase | `resources/views/**` | VERIFIED: `baseline/FORMS.md`, 51/51 views with `<form`, 78/78 forms. |
| S1-14 | Alpine components and inline JS; the regency fetch; Leaflet init and marker data source; About slider; AOS init | Phases 4, 5, 6, 18 | `resources/js/**`, views | VERIFIED: `VIEWS_AND_JS.md` section 3 (Alpine 27 files, Leaflet `home/index.blade.php:432` and `map-view.blade.php:174`, AOS `home/index.blade.php:424`, About slider 7 slides, regency fetches incl. 3 calls to routes that do not exist, `resources/js/app.js` empty). |
| S1-15 | How a student's display name is stored and read (J1) | Phase 1, D-03 | migrations, models, accessors, seeders, views | VERIFIED: two sources: `users.name` (`database/migrations/2026_05_01_000020_create_users_table.php:16`) and `students.first_name`/`last_name` (`…000040_create_students_table.php:14-15`) with accessor `Student::getFullNameAttribute()` (`app/Models/Student.php:150-152`). Friends search filters on `users.name` (`app/Http/Controllers/Student/FriendController.php` ~line 91). Input for D-03. |
| S1-16 | Queries behind browse-problems, repository, friends search, company talents (order, paginate, counts) (J2, J4) | Phase 1, D-05 | controllers, scopes | VERIFIED: browse-problems `BrowseProblemsController.php:117-153` orders by one non-unique column (deadline/views/created_at), no `id` tie-breaker, `paginate(12)`, count from a cloned query (line 135); repository `KnowledgeRepositoryController.php:85-106` same pattern, `paginate(12)`; friends search `FriendController.php:117` `paginate(20)` with **no orderBy**; company talents `Company/TalentController.php:70-71` `orderBy('created_at','desc')->paginate(per_page)`. Non-deterministic ordering explains J2. Input for D-05. |
| S1-17 | Public profile controller and view (what is exposed to guests) (J3) | Phase 1, D-04 | controller, view | VERIFIED: `profile.public` → `Student\ProfileController@publicView` (no auth) renders `student.profile.public`, which prints `{{ $student->user->email }}` (`resources/views/student/profile/public.blade.php:201`) and WhatsApp (205-214) to guests (J3). Input for D-04. |
| S1-18 | Institution type and company size options vs stored values (J5) | Phase 1, D-06 | views, migrations, seeders | VERIFIED: institution type options match register validation (`app/Http/Requests/InstitutionRegisterRequest.php:30-33`, `Institution/ProfileController.php:55-63`; column `institutions.type` string). Company size: register accepts `1-10…1000+` (`CompanyRegisterRequest.php:35-38`), stores a **remapped** value in `companies.employee_count` (`RegisterController.php:611-623`: `51-200`→`51-100`, `201-500`→`101-200`, …), while `company/profile/edit.blade.php:82-86` pre-selects from `$company->company_size` (no such column) → edit loses the value (J5). Input for D-06. |
| S1-19 | Chatbot create route and controller (J13); exception handler; where APP_DEBUG is read | Phases 1, 16, 26 | routes, controllers, `bootstrap/app.php` or handler, `config/app.php` | VERIFIED: GET `student/chatbot/create` creates a conversation (`ChatbotController.php:58-72`). `bootstrap/app.php` `withExceptions` is empty; no `resources/views/errors/`. `APP_DEBUG` read at `config/app.php:42`; `.env` value is set (not printed). |
| S1-20 | Status values: application, problem, verification, job, applicant stage | Phases 3, 10, 18, 19, 24 | migrations, enums, models | VERIFIED (migrations): problems `draft|open|in_progress|completed|closed`; applications `pending|reviewed|accepted|rejected`; projects `active|on_hold|completed|cancelled`; milestones `pending|in_progress|completed|delayed`; project reports `pending|reviewed|approved|revision_needed`; documents `pending|approved|rejected`; friends `pending|accepted|rejected|blocked`; companies.verification_status `pending_verification|verified|rejected|needs_review`; job_postings.status string (`draft, posted, closed, archived` per comment); job_applications.status string (`pending, reviewed, shortlisted, rejected, accepted`); chat_conversations.status string (`active, archived, deleted`). |
| S1-21 | Notification types and payloads | Phase 16 | notification classes, models, seeders | VERIFIED (partly): `notifications.type` is a free string column (`…000025_create_notifications_table.php:29`); types are passed by callers of `NotificationService::create()` and by `NotificationsSeeder` (`$typeKey`, lines 120, 154). 56 seeded notifications exist; a complete type list needs a pass over all callers (Phase 16). |
| S1-22 | Route used when a company opens a talent's profile | Phase 22 | company views and controller | VERIFIED: compare view links to `route('profile.public', $talent->username)` (`company/talents/compare.blade.php:112`); index cards are rendered by Alpine `talentBrowser()`. `TalentController@show` (`company.talents.show` view) has no route. |
| S1-23 | Student project, milestone and weekly-report routes (if any) | Phase 11 | route list; models | VERIFIED: `student/projects/{id}`, `{id}/report/create` + POST `{id}/report`, `{id}/final-report/create` + POST, POST `{id}/toggle-portfolio`, PUT `milestones/{milestoneId}/update`; institution side `institution/projects/{id}/manage`, milestones CRUD, report approve/reject, status. |
| S1-24 | Tests: framework, location, count, CI | D-22, every phase | `tests/`, `phpunit.xml`, pest config | VERIFIED: PHPUnit 12, 2 example tests, SQLite in-memory; **2 passed, 2 assertions, 373 ms**; no CI; Pint not configured (default preset `--test`: 194 files fail). `baseline/TESTS_AND_TOOLING.md`. |
| S1-25 | Seeders, factories and where test accounts per role are defined (record file and line, never the credentials) | D-12, D-13 | `database/seeders`, README | VERIFIED: `DatabaseSeeder` order: ProvincesRegencies, DummyData, User, Company, Problems, ProblemImages, Applications, Projects, Documents, Notifications. Accounts: students `database/seeders/DummyDataSeeder.php:354-357`, institutions `:588-591`, companies `UserSeeder.php:134-136`; one shared password set at `DummyDataSeeder.php:356, 590`, `UserSeeder.php:135` (not recorded). **No admin account** is seeded. **Defect found while seeding:** the BPS API path of `ProvincesRegenciesSeeder` / `BpsApiService` files regencies under the wrong provinces (duplicate primary keys for 29/34 provinces), so the full `db:seed` fails in `DummyDataSeeder`; the seeder's own fallback (5 provinces, 22 regencies) was used instead. `DatabaseSeederKKN` references a missing `CompanySeederRealIndonesia` (not run). Result counts: `baseline/TESTS_AND_TOOLING.md` section 4. |
| S1-26 | Database configuration and why the pooler rejects the connection ('tenant/user not found') | D-12 | `config/database.php`, `.env.example` (never print `.env`) | VERIFIED (resolved later on 2026-10-30): the old Supabase project was unreachable (`SQLSTATE[08006]`). With the user's approval, `.env` now points to the user's new Supabase project (values not recorded; backup `.env.backup`). `php artisan migrate` ran 60/60 on the empty database, and seeding succeeded via the seeder's fallback region data (see S1-25). `/api/test-db` and every DB-backed page return 200. `config/database.php:20` default `sqlite`; `.env` uses `pgsql`. |
| S1-27 | `.gitignore` (is `public/build` ignored? `vendor`? `node_modules`?) | Phase 2, safety rules | `.gitignore` | VERIFIED: `.gitignore` ignores `/vendor`, `/node_modules`, `/public/build`, `/public/hot`, `/public/storage`, `.env`, `.env.backup`, `.env.production`. |
| S1-28 | README: how the app is started and tested on this machine (Windows?) | CLAUDE.md section 4 | README, scripts | VERIFIED: README has no run/test instructions; `composer.json` scripts `setup`, `dev`, `test`. Host is Windows 11 (Git Bash). App started with `php artisan serve` on 127.0.0.1:8000. |
| S1-29 | List `public/` recursively with size and SHA-256 (excluding `build/` and `storage/`); diff against section 1 of this inventory. | Confirms the list is complete; adds files nobody named. | `find`, `sha256sum` | VERIFIED: 38 files in `public/` (excl. `build/`; `public/storage` link absent) with SHA-256 in `baseline/asset-hashes.txt`; identical before and after Phase 0. 10 files were missing from the inventory (added to ASSET_INVENTORY.md 1.5). |
| S1-30 | For every file in `public/` (and every package asset to be used) search `resources/`, `app/`, `routes/`, `config/`, `database/` for its name. | Turns INFERRED usage into confirmed usage; finds unreferenced files. | `grep -RIn -F` | VERIFIED: references per file recorded in `ASSET_INVENTORY.md` section 1.5. Unreferenced: `KARA-background.png`, `reg-com-background.jpg`, `profile_13523124.jpeg` (referenced as `.jpg`), `profile_13523151.jpeg`, `favicon.ico`. |
| S1-31 | Find certificate templates, certificate fonts (Pinyon Script, Poppins) and the code that renders certificates; produce one sample certificate if safe. | Protected asset group (D-32); baseline for later comparison. | `grep -RInEi`, controllers, DomPDF config | VERIFIED: `public/assets/certificate/template.png`, `public/assets/certificate/fonts/PinyonScript-Regular.ttf`, `…/Poppins-Regular.ttf`, used by `app/Services/CertificateService.php:160-162` (GD `imagettftext`, PNG, uploaded to Supabase, path saved in `projects.certificate_path`); triggered from `Institution/ProjectManagementController` on completion and `php artisan certificates:generate-missing`. Sample rendered offline: `baseline/screenshots/certificate-sample-baseline.png` (2000x1414). Independent of website fonts. |
| S1-32 | Find where student avatars, institution/company logos, verification documents, problem images are stored and how Blade renders them; check `public/storage` and `FILESYSTEM_DISK`. | Preservation and privacy (D-04, D-31). | `config/filesystems.php`, Blade | VERIFIED: uploads go through `App\Services\SupabaseStorageService` (profile photos `students/profiles/…`, institution/company logos, verification documents, KTP, NPWP, problem images, proposals, reports, certificates) to Supabase Storage; `storage/app/public|private` are empty locally; `public/storage` link absent. Avatars: `Student::getProfilePhotoUrlAttribute()` (`app/Models/Student.php:174-195`) → Supabase URL, local `storage/` URL, or **ui-avatars.com** with the student's name. |
| S1-33 | Read `vite.config.js`, `resources/css/app.css`, `resources/js/app.js`, `package.json`; list `resources/css/` and explain every CSS file missing from the manifest. | Confirms CSS wiring (D-29) and the HTTP 500 cause. | — | VERIFIED: see S1-04/S1-05 and `VIEWS_AND_JS.md` section 2. |
| S1-34 | Check `public/assets/` and the proposed targets for collisions; list `docs/`, `scripts/`. | Section 10 collision column. | `ls` | VERIFIED: `public/assets/` **exists** and holds only `certificate/`; proposed targets `public/assets/{brand,fonts,icons,illustrations,photography}/`, `resources/css/student-go/`, `scripts/`, `student-go-docs/design-system/` are all absent → no collision, but Phase 2 must not touch `public/assets/certificate/`. Root `docs/` holds the two PDFs (proposal, design audit). |
| S1-35 | Ask the user for provenance and consent per existing photograph (table in section 1) and record the answers here. | D-30. | — | BLOCKED: provenance and consent of existing photographs not yet answered by the user (D-30). |
| S1-36 | Confirm the two broken Contact references and whether `profile_18223127.*` exists under another name. | D-31, Phase 5. | `find public -name 'profile_*'` | VERIFIED: `ContactController.php` lists `profile_18223127.jpg` (no such file under any extension), `profile_13523136.jpg` (OK), `profile_13523124.jpg` (file is `.jpeg`). An unreferenced `public/profile_13523151.jpeg` exists — whether it is the missing team portrait is for the user to say (D-31). |
| S1-37 | Check EXIF and metadata (camera, GPS, author) on the field photographs; GPS must not be shipped. | Privacy of locations and people. | `exiftool` (if installed) | VERIFIED: Pillow read of all 31 raster images in `public/`: **no EXIF GPS, camera or author/copyright tags** in any file (values never printed). |

### 12.1 What Stage One contradicted or added (2026-10-30)

| # | Plan said | Repository says | Affects |
|---|---|---|---|
| 1 | Certificate templates and fonts could not be located (A-08) | `public/assets/certificate/{template.png, fonts/PinyonScript-Regular.ttf, fonts/Poppins-Regular.ttf}`, rendered by `app/Services/CertificateService.php` (GD, not DomPDF; no Google Fonts link involved) | D-32, Phases 2, 3, 12 |
| 2 | `public/assets/` does not exist (404 probes) | It exists and contains `certificate/` | Phase 2 copy targets must not overwrite it (no collision with the proposed sub-folders) |
| 3 | 27 files in `public/` | 38 files (+ `build/`): `deal-work-together.jpg`, `magang-preview.jpg`, `reg-com-background.jpg`, `institution-register-background.jpeg`, `student-register-background.jpeg`, `profile_13523151.jpeg`, `.htaccess`, `index.php`, and the 3 certificate files were not in the inventory | ASSET_INVENTORY.md 1.5 |
| 4 | `/forgot-password` fails because the manifest is stale | The auth CSS files were never Vite inputs; a rebuild does not fix it | Phase 1/6, D-29 |
| 5 | `/institution/reviews/create` is a page | The route is `/institution/reviews/create/{projectId}`; the bare URL is the `show` route with id `create` | Phases 1, 19 |
| 6 | Audit's 59 route patterns | All 59 exist, plus 144 more routes (202 total), incl. `dev/login` (broken view), public `api/test-db`, `api/health`, duplicate `student/problems*`, unrouted `TalentController@show` | Phases 1, 8, 22, 26 |
| 7 | Asset package photographs "not downloaded" | 60 WebP files under `student-go-assets/public/assets/photography/` and 61 originals (54 MB) under `student-go-assets/storage/app/asset-originals/` exist (created 2026-10-30 14:07); `assets-manifest.json` still says "not yet downloaded". The 111 hashed files match the manifest. | D-33, D-19, Phase 2 |
| 8 | Product name in docs: Student Go | README and certificate say **Karsa** ("Platform Magang dan Rekrutmen Mahasiswa"); `APP_NAME` default 'Laravel' in titles | D-01 |
| 9 | Static `public/css`, `public/js` assumed fine | `asset('css/browse-problems.css')`, `asset('css/friends.css')`, `asset('js/pages/browse-problems.js')` point to folders that do not exist (404) | Phases 8, 13 |
| 10 | Homepage counters (J9) OBSERVED | Hard-coded in `HomeController.php:15-20` | Phase 4 |
| 11 | Database: restore remote | The old project is gone. The user's new Supabase project was migrated and seeded on 2026-10-30, but the BPS-based region seeder is broken, so the data has 5 provinces / 22 regencies instead of the audit's spread. Problem images point to an empty bucket. | D-12, D-14, D-15 |
| 12 | Docs root `student-go-docs/design-execution/` with `CLAUDE.md` at root | Delivered inside `student-go-design-docs/`; copied to the root on 2026-10-30 at the user's choice (D-28). The original folder and zip are untouched. | D-28 |
| 13 | J1 (two names) OBSERVED | Confirmed in seeded data: student #86 is 'Candra Hasanah' in `users.name` and 'Farah Wibowo' in `students`; the navbar shows both (name vs initials) | Phase 1, D-03 |
| 14 | Responsive issues OBSERVED (J7) | Every logged-in page overflows at 768 px (student 881, institution 821, company 833 px) | Phases 3, 27 |

### 12.2 Changes made by Phase 1 (2026-10-30)

Facts above that Phase 1 changed (details: `reports/PHASE_01_REPORT.md`):

- **S1-15:** `students.first_name` + `last_name` are canonical (D-03), via `Student::display_name`/`initials` and `User::display_name`/`initials`. Seeded `users.name` is synced (0 mismatches); the seeder now picks one name per student.
- **S1-16:** every paginated list has an `id` tie-breaker. Friends search orders by name, then id.
- **S1-17:** `profile.public` sends email and WhatsApp only to the owner and accepted connections.
- **S1-18:** `companies` has 9 new nullable columns: `email`, `company_size`, `tagline`, `location`, `postal_code`, `linkedin`, `twitter`, `facebook`, `instagram`. Company profile save works.
- **S1-19:** `GET student/chatbot/create` no longer persists; `POST student/chatbot/create` (`student.chatbot.store`) creates a conversation. `app.debug` is true only when `APP_ENV=local`.
- **S1-24:** the suite now has 38 tests, and `tests/TestCase.php` forces SQLite `:memory:` for the hard-coded `pgsql` connection and blocks outbound HTTP.

### 12.3 Homepage facts found and changed by Phase 4 (2026-10-31)

- **Before:** `HomeController@index` ran no query; it passed hard-coded `$stats` (150 / 1200 / 85 / 120), and the view also printed literal '34' and '514'. The Leaflet map showed **10 invented projects** written in the page script ('Jakarta - Proyek Smart Village', 'Medan - Pertanian Organik', …); none exists in the database. AOS (14 `data-aos`) caused the 390/768 px overflow (and 1366/1024 after Phase 3).
- **Data (VERIFIED read-only query, 2026-10-31):** 77 problems, all `open` and within deadline; 47 institutions have open problems (79 institutions in total); 22 regencies in 5 provinces (all on Java). The database stores no coordinates (provinces/regencies have `name` + BPS `code` only).
- **After:** the controller queries open problems (latest 5, counts per regency, distinct institutions/regencies/provinces) inside `try/catch`; on failure the data blocks are omitted. Map pins come from these counts placed at reference centre points (`App\Support\RegionCentroids`, keyed by BPS code; a geographic lookup, not product data).
- **Revision (closed 2026-10-31):** the controller now loads all open problems once (up to 500, soonest deadline first) and derives the summary, the five soonest rows, open problems per SDG, per-province totals, map points and the map's problem list from that set; project outcomes come from `projects` (status counts, distinct students and institutions), and an outcome that is zero is not shown. The hero is a background video (AD-11) with no number in it.
- **Access:** problem detail (`student.browse-problems.show`) requires a student login, so guests clicking a row are sent to `/login` (unchanged behaviour); institution names link to the public `institution.public` page.

### 12.4 Public-page facts found and changed by Phase 5 (2026-10-31)

- **Source (VERIFIED):** `AboutController` passed four arrays the view never used (one held unsourced figures); `ContactController` hard-coded three members with personal emails/WhatsApp and two broken photo names; `/institution/{id}` is `Institution\ProfileController@showPublic` → `institution/profile/public.blade.php`, which never showed email, phone, address, PIC or documents (kept). Its problem constraint used `where/orWhere` without grouping (now `whereIn`, same statuses).
- **Data (VERIFIED read-only queries):** 79 institutions, 32 without problems, 0 logos, 0 websites; descriptions ≤ 135 characters, stored in Title Case; every institution has `is_verified = true` and `verification_status = pending_verification` (contradictory; no badge shown); id 21 holds the 76-character name; id 19 now has no problems (the audit's S24 example); projects repeat titles per student and have empty `completed_at`.
- **Changed:** About, Contact and the public profile were rebuilt (report `reports/PHASE_05_REPORT.md`); team data now comes from `App\Support\TeamMembers` (supplied by the user).

## 13. Limitations and blockers

> **Updated by Phase 0 (2026-10-30):** the repository is now read (Stage One done), `route:list` ran, `public/` was listed and hashed, certificate assets were found, EXIF was read. The database was restored and seeded later the same day, and logged-in screens for all three roles have a baseline. Still blocked: admin UI (no admin account, D-11/D-13), jobs/connections/wishlist/chat data (empty tables, D-13), photo rights (D-30), reference documents B and C (D-16). The bullets below are the original discovery-time limits.

- **No repository** was available: all code-level statements are UNKNOWN or INFERRED, except the file paths and versions that debug pages exposed.
- **No `php artisan route:list`**: the 59-pattern inventory is incomplete (unlinked routes and all non-GET action routes are missing).
- **Database unreachable** on 2026-10-30: no data-driven page could be viewed; no baseline screenshots were taken; audit data findings are OBSERVED only.
- **Not logged in**: authenticated pages were not opened. No credentials were requested, guessed or used.
- **Admin UI**, record-level institution/company pages (`/institution/problems/{id}`, `/company/jobs/{id}`, `/company/applications/{id}`), accepted/active application and project states, and `/forgot-password` rendering: BLOCKED.
- **Reference documents B and C** not found (D-16).
- **Real devices and screen readers**: never tested (D-24).
- **Photographs** in the asset package are licence-checked but not downloaded or viewed (D-19, D-33).
- **Existing photographs**: provenance, rights and consent are unknown; EXIF was not read; the `public/` folder could not be listed, only files that were named, referenced or guessed were checked (`ASSET_INVENTORY.md` section 14).
- **Certificate templates and fonts (Pinyon Script, Poppins)**, uploaded avatars, logos and verification documents: location UNKNOWN / BLOCKED (D-32, D-31).
- **Source C (`resources/`)** was not read; asset usage in Blade is inferred from rendered guest pages, built files and the audit.
- The browser tool refused some script calls on 2026-10-30 ('Cookie/query string data'); facts were taken with narrower read-only probes instead.
- Side effects of this session: none on the app. (The audit itself created two empty KARA conversations through a GET and inflated view counters; see audit G. Those remain for the user to delete if wanted.)
