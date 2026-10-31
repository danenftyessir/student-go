# PHASE 08 — Student Project Discovery: completion report

| Field | Value |
|---|---|
| Phase | 8 — Student Project Discovery (`phases/PHASE_08_PROJECT_DISCOVERY.md`); run as "Session A — Student Workspace" in parallel with Session B (Phase 18) and Session C (Phase 22) |
| Date | 2026-10-31 |
| Branch / commit | Started in `…/Competition/StudentGo` (`feat/students` @ `e824b9d`); finished in `…/Competition/Mirror/StudentGo`, a new repository (`main` @ `0a06850 first commit`, every project file untracked). No commit (D-27) |
| Recommended status | **COMPLETE**: all 8 acceptance criteria met (section 9.4). `PHASE_TRACKER.md` was **not** edited (parallel-session rule) |
| Prerequisites | Phase 1 (hard) COMPLETE, Phase 3 (hard) COMPLETE |
| Gate answers | D-02, D-05, D-12, D-15 answered earlier. **D-14 answered in this session** (seed images in bucket `sg-storage`). Extra approvals: application status per row, regency-filter fix, map clean-up (see `DECISIONS_AND_OPEN_QUESTIONS.md`, 'Note on Phase 8 decisions') |
| Evidence folder | `reports/phase8/` (`before/`, `after/`, `metrics-*.json`, `interaction-checks.json`, `contrast.json`, `routes-after.json`, `seed-images-upload-log.json`) |

## 1. Executive Summary

`/student/browse-problems` went from a 3,852 px page (1440 px wide) that opened with a photo hero, a filter form with its own 'Terapkan Filter' button, a card grid with grey placeholder tiles, English difficulty words in traffic-light colours and two 404 requests per load, to a 2,297 px directory: a plain page header, one search field, a sort select, removable filter chips, a labelled count (`77 masalah dibuka` / `N dari 77 masalah dibuka sesuai filter`), and a **table as the default view** that shows institution, location, SDG, quota, deadline and difficulty for every problem without opening it. Gallery and Leaflet map remain as alternative views. Filters apply at once on desktop and sit in a drawer with a sticky 'Terapkan' below 1024 px. The Kab/Kota filter, broken before (it called a non-existent URL), works.

Routes unchanged (203 = 203). Phase 8 tests 12/12 (103 assertions); browser checks 23/23; six widths × four states with no horizontal scroll, no clipped text, no small targets and no console errors.

## 2. Scope Phase 8

In scope (sections A, F): `/student/browse-problems` (and the alias `/student/problems`, same controller and view): list, gallery, map, filters, sort, pagination, empty states. Out of scope and untouched: problem detail (`browse-problems.show`, Phase 9), `partials/pagination.blade.php`, other student pages, shared component CSS, Session B/C files.

## 3. Initial Condition

Evidence: `reports/phase8/before/` (S02, S25, P99 at six widths, `metrics-before.json`).

| Metric (S02) | 360 px | 1440 px |
|---|---|---|
| Page height | 9,694 px | 3,852 px |
| h1 offset from top of `<main>` | 154 px | 174 px |
| Clipped text elements (ellipsis / line-clamp) | 42 | 47 |
| English words (Beginner, Advanced, Featured …) | 12 | 13 |
| Failed requests / console errors per load | 2 / 2 (`css/browse-problems.css`, `js/pages/browse-problems.js`, both 404) | 2 / 2 |

Also found: Kab/Kota select never filled (JS fetched `/student/browse-problems/get-regencies`, which falls through to `{id}`); map popup inserted titles unescaped; a second 'Filter Peta' panel duplicated the page filters; grey media tiles for problems without images; difficulty in red/yellow/green.

## 4. Implemented Features

- Page header (h1 at 24 px from the top of `<main>`), no hero.
- Toolbar: one search (`form="browse-filters"`, Enter or 'Cari'), 'Urutkan' with the four existing `sort` values, 'Filter (N aktif)' button below 1024 px.
- Filter panel = one GET form with the old parameter names (`province_id`, `regency_id`, `sdg_categories[]`, `difficulty`, `duration`, `is_urgent`, `is_featured`, hidden `view`); auto-apply ≥ 1024 px; drawer < 1024 px (shared `sgFilterBar`: dialog role, focus trap, Esc, focus return) with sticky Terapkan / Atur ulang.
- Active-filter chips that each remove one filter; 'Hapus semua filter'.
- Count label from the controller's two counts (D-05): unfiltered `M masalah dibuka`, filtered `N dari M masalah dibuka sesuai filter`.
- View tabs Daftar / Galeri / Peta (`role=tablist`, arrow keys), state in `?view=` (legacy `grid` kept) and `localStorage`; pager links carry the view.
- List: table with the student's own application status, Mendesak/Unggulan, SDG chips, full institution name, regency + province, quota, deadline + days left, neutral difficulty meter; 'Tenggat' header toggles `sort=deadline` with `aria-sort`.
- Gallery: `x-ui.card`, image only when a record exists and loads.
- Map: Leaflet + cluster kept; duplicate filter removed; escaped popup; package pin; keyboard-openable markers; note that markers sit at province points.
- States: no result (S25), no open problem, out-of-range page (`?page=99`) with 'Ke halaman 1' keeping filters.

## 5. UI/UX Decisions

- **Table first** (guardrail 3; audit S02): comparison of location, quota and deadline is the task; cards are the optional gallery.
- **Stacking by container width, not viewport** (found in the final captures of this session): at 1024 px the sidebar appears and the results column drops to ~680 px, so the title column was squeezed to ~80 px and titles broke letter by letter (page 5,206 px tall). The table now stacks whenever the results column is under 50rem (640–850 px without sidebar, 1024–1150 px with it), using the same stacked pattern as `x-ui.table` below 640 px; titles and chips wrap with `break-word`. Measured: title column ≥ 189 px whenever the table is shown.
- Difficulty: neutral three-bar `.sg-meter` + word (Pemula / Menengah / Lanjut), never colour-coded.
- Flags use icon + text in gray-600; status badges only for the student's own applications (one vocabulary, D-02).
- Chips only for status and taxonomy (SDG, active filters); buttons keep the standard radius.
- The `ui-ux-pro-max` skill's generic palettes were not applied; the project design system wins.

## 6. Backend and Data Integration

- `Student\BrowseProblemsController@index`: **one** added read query (`$applicationStatuses`, own applications for problems on the current page, approved). All filters, sort cases, the deterministic tie-breaker (Phase 1, J2), both counts, the `$allProblems` map data and pagination (12 per page, `withQueryString`) are unchanged.
- Regency list: existing route `student.problems.get-regencies` (unchanged backend).
- D-14: 92 seed objects in Supabase bucket `sg-storage` at the paths already in `problem_images`; DB unchanged; `.env` only `SUPABASE_BUCKET`.
- Kept as they are: `ILIKE` search (PostgreSQL only), `whereJsonContains` SDG filter, duration buckets.

## 7. Files Created and Modified

| File | Change |
|---|---|
| `app/Http/Controllers/Student/BrowseProblemsController.php` | + `$applicationStatuses` read query, passed to the view |
| `resources/views/student/browse-problems/index.blade.php` | Rewritten (header, toolbar, chips, count, view tabs, panels, states; inline Alpine `sgBrowseView`, `sgBrowseRegions`) |
| `…/components/filter-sidebar.blade.php` | Rewritten (one GET form, same names) |
| `…/components/problem-table.blade.php` | New |
| `…/components/problem-card-list.blade.php` | Rewritten as one table row |
| `…/components/problems-grid.blade.php`, `problem-card.blade.php` | Rewritten (gallery) |
| `…/components/page-empty.blade.php` | New (`?page=99`) |
| `…/components/map-view.blade.php` | Rewritten (escape, pin, keyboard, no duplicate filter) |
| `resources/css/student-go/student-browse.css` | New (`.sg-browse*`) |
| `resources/css/app.css` | + one `@import` line after `student-dashboard.css` (shared) |
| `tests/Feature/Phase8/StudentBrowseProblemsTest.php` | New, 12 tests |
| `DECISIONS_AND_OPEN_QUESTIONS.md`, `COMPONENT_INVENTORY.md` (§10), `ASSET_INVENTORY.md` (§24) | Appended (shared) |
| `.env` | `SUPABASE_BUCKET=sg-storage` only (the temporary backup was deleted, see section 11) |
| `app/Http/Controllers/Student/DashboardController.php` | Removed the unused `images` eager load filtered on the non-existent `is_primary` (Phase 7 follow-up, user chose option b) |
| `handoff/scripts/capture.py`, `interact.py` | Evidence scripts (outside the app) |

## 8. Asset Reuse and Asset Changes

See `ASSET_INVENTORY.md` §24. No file under `public/` added, deleted, overwritten, moved or renamed: 37/38 baseline hashes identical, 0 changed (missing `karsa-logo.png` was deleted by the user before Phase 4). `dashboard-student.jpg` Retain / Deprecate as hero; `map-pin.svg`, `no-search-results.svg`, sprite Reuse; 37 catalogue photos Reuse as seed objects in `sg-storage` (not in `public/`); Space Grotesk link and the two 404 links removed; Leaflet unchanged (AD-08).

## 9. Testing Results

### 9.1 Automated

| Command | Result |
|---|---|
| `php artisan test tests/Feature/Phase8` | **PASS** 12/12, 103 assertions |
| `php artisan test` (full) | **PASS** 185/185, 2,058 assertions (re-run after Session B removed its temporary `tests/Feature/Phase18tmp/` probe; the earlier run had 1 error from that probe only) |
| `vendor/bin/pint --test tests/Feature/Phase8` | **PASS** |
| `npm run build` | **PASS** (CSS 236.47 kB / gzip 36.77 kB including Sessions B/C styles) |
| `php artisan route:list --json` vs `reports/phase6/routes-after.json` | **PASS** 203 = 203, identical (method, URI, name, action, middleware) → `reports/phase8/routes-after.json` |

Not covered in SQLite (by design): `search` (`ILIKE`) and the SDG filter (`whereJsonContains`); both covered by the browser checks against PostgreSQL.

### 9.2 Browser (Playwright outside the project, D-22; local seeded DB, student #86)

`reports/phase8/after/interaction-checks.json`: **23/23 PASS**, re-run after the final CSS change — all 77 IDs exactly once over 7 pages; default list + stable order; view tabs by keyboard and `?view=`; pager keeps view; map tiles/markers and keyboard popup (escaped); desktop auto-apply; province + SDG + difficulty count = DB, labelled 'N dari M'; chip removal removes only that filter; sort=deadline ascending + `aria-sort`; pager keeps filters + sort; search via Enter = DB count; S25 and `?page=99` states; application badges = `applications` table; mobile drawer dialog + Esc + focus return; regency list from the fixed route; Terapkan submits province + regency; 76-char institution name in full at 360 px; focus ring on 14 stops; reduced motion; no console errors.

### 9.3 Visual / metrics (`after/metrics-after.json`, 24 captures)

| Page | 360 px height | 1440 px height | Overflow | Clipped | English | Small targets | Errors / failed requests |
|---|---|---|---|---|---|---|---|
| S02 list | 5,105 (was 9,694) | 2,297 (was 3,852) | none at 6 widths | 0 | 0 | 0 | 0 / 0 |
| S25 no result | 1,314 | 977 | none | 0 | 0 | 0 | 0 / 0 |
| P99 page 99 | 1,404 | 983 | none | 0 | 0 | 0 | 0 / 0 |
| GAL gallery | 6,520 | 2,614 | none | 0 | 0 | 0 | 0 / 0 |

h1 at 24 px from the top of `<main>` at every width (was 154–174 px). Contrast (`contrast.json`): every new text pair ≥ 5.21:1 (selected tab 9.16, chip text 15.81/14.49, cluster 7.84, flags 7.83/7.18, meta on hover 5.21). The neutral chip border (gray-300, 1.59:1) is the same as the design system's secondary-button outline; chips are identified by their text and icon, so WCAG 1.4.11 does not require it.

### 9.4 Acceptance criteria (section L)

- [x] **List is the default view and exposes institution, location, SDG, quota and deadline without opening a record** — `test_list_is_default_view…`, `test_row_shows_full_institution_name…`; S02 screenshots.
- [x] **One search field; no separate 'Terapkan Filter' on desktop; mobile drawer has sticky apply** — one `name="search"`; desktop auto-apply and drawer checks (9.2); `DRAWER_after_390.png`.
- [x] **No media placeholder tile anywhere** — list has no media; gallery shows an image only when a record exists and hides it on error; `GAL_after_*`.
- [x] **Difficulty is a neutral meter** — `.sg-meter` (`data-level` 1–3, gray bars + word); test asserts `data-level="2"` + 'Menengah', no English words.
- [x] **Counts match the query; filtered totals are labelled** — `test_active_filters_show_removable_chips_and_n_of_m_label`; browser count = DB for filters and search.
- [x] **Map view still works** — tiles, clusters, markers, keyboard popup (9.2); `MAP_after_1440.png`, `MAP_popup_after_1440.png`; test for Leaflet tags and escape helper.
- [x] **No horizontal scroll at six widths; table stacks below 640 px without cropping** — 24 captures, `scrollWidth ≤ innerWidth`; stacks below 640 px and also when the results column < 50rem; 0 clipped elements.
- [x] **Reachability: all 77 problems reachable and unique** — browser: 7 pages, 77 rows, 77 unique = DB 77; `test_every_open_problem_appears_exactly_once_across_pages`.

## 10. Known Limitations

- Seed images are reused (2–3×) and unrelated to the problem text; dev data only (D-14).
- Local PHP cannot call Supabase: `curl.cainfo` in `php.ini` points to a missing file on another user's path, so app-side uploads fail locally (cURL error 77). Outside the project; the seed upload was done from Python.
- Mobile 'Terapkan' sends empty parameters (`difficulty=&duration=&search=`); harmless (`filled()`), same as the old form.
- Duration data: only 1–2 months exist, so `3-4`, `5-6`, `6+` always return 0 (kept as the parameter contract).
- Map markers sit at province coordinates with a random offset (unchanged behaviour; stated under the map). Seeded DB has 5 provinces, so 5 marker groups.
- Search and SDG filter cannot be unit-tested in SQLite (PostgreSQL operators).
- `DECISIONS_AND_OPEN_QUESTIONS.md` Part 3 still lists 'Real gallery images in layouts' as BLOCKED; with seed images it is now testable for dev, real photographs still depend on institution uploads. Left for integration to update.
- Latent Phase 7 defect **fixed after the user's answer (option b)**: `Student\DashboardController` no longer eager-loads recommended problems' `images` (it filtered on the non-existent column `is_primary`; the view never used images). Phase 7 + 8 tests 21/21; live dashboard loads for #85, #86, #88.

## 11. Shared File Changes and Conflict Risks

- `resources/css/app.css`: one appended `@import './student-go/student-browse.css';`.
- `DECISIONS_AND_OPEN_QUESTIONS.md` (D-14 ANSWER + 'Note on Phase 8 decisions'), `COMPONENT_INVENTORY.md` (§10), `ASSET_INVENTORY.md` (§24): appended; numbers checked against Sessions B/C first.
- `.env`: `SUPABASE_BUCKET` → `sg-storage` affects every session that reads images (intended).
- `.env.backup-phase8` (copy of `.env` made before the bucket change, not ignored by `.gitignore`) was **deleted** at the user's request. A separate `.env.backup` (not created by Session A) is ignored by `.gitignore` line 4 and was left alone.
- `tests/Feature/Phase18tmp/` (Session B's temporary probe) caused 1 full-suite error; Session B was notified and removed it; full suite then 185/185.
- No Session B/C file was edited.

## 12. Handover Notes for Phase 9

- Detail route `student.browse-problems.show` is linked from every row, card and popup (`/student/browse-problems/{id}`); the list keeps the user's filters in the URL, so a 'Kembali ke daftar' link can use the referrer query.
- Images for the gallery on the detail page exist now (`sg-storage`, 220 `problem_images` rows, `is_cover` column — not `is_primary`); they are seed images, so do not write alt text that describes the problem from the photo.
- Reuse: `HomeController::sdgName()` / `sdg_label` for SDG names, `.sg-meter` for difficulty, `x-ui.status` domain `application` for the student's own status, the deadline format `j M Y` + 'N hari lagi'.
- The student's application status for a problem is available with the same query pattern as `$applicationStatuses`.

**Recommended tracker note (Phase 8):** COMPLETE 2026-10-31 (Session A). Browse problems rebuilt as directory (list default, gallery, map); D-14 answered (seed images in `sg-storage`); regency filter fixed; +1 read query in `BrowseProblemsController`; tests 12/12 Phase 8; routes 203 = 203; no commit.
