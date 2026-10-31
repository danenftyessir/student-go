# PHASE 21 — Company Dashboard: completion report

| Field | Value |
|---|---|
| Phase | 21 — Company Dashboard (`phases/PHASE_21_COMPANY_DASHBOARD.md`); called 'Company Workspace' in the session brief. Its scope is `/company/dashboard` only; talents, jobs, applicants and profile are Phases 22-25 |
| Session | Session C (company workspace), run in parallel with Session A (Phase 7) and Session B (Phase 17) |
| Date | 2026-10-31 |
| Branch / commit | `feat/students` @ `e824b9d`, no commit (D-27). **No separate worktree**: the user declined one; all three sessions share this working directory (section 11) |
| Recommended status | **COMPLETE**: all 5 acceptance criteria met (section 9.1). `PHASE_TRACKER.md` was **not** edited (the user's instruction for parallel sessions); proposed row in section 12.3 |
| Prerequisites | Phase 3 (hard) COMPLETE; Phase 1 (soft) COMPLETE; Phases 0-6 COMPLETE in the tracker. Phase 6 auth for the company role re-checked here: guest → login, other roles → their own dashboard, company login → `/company/dashboard` (tests + live login) |
| Gate answers (this session, by the user) | **D-10 (company)**: work queue: one summary line 'x lowongan aktif · y lowongan draf'; applicants per stage; active jobs table with applicants per job; stat cards and every growth % removed. **D-21**: a chart may show only when there are at least 5 data points: the 'Lamaran masuk per bulan' chart shows when at least 5 of the last 6 months have applications, using the Chart.js 4.4.1 tag the page already loaded. **D-13**: a new seeder may write test data to the Supabase dev database. D-02 was already ANSWERED. Not written into `DECISIONS_AND_OPEN_QUESTIONS.md` (left to the integration owner to avoid concurrent edits; text in 12.3) |
| Evidence folder | `reports/phase21/` (`before/`: 12 full-page captures + `metrics_before.json`; `after/`: 12 captures + `metrics_after.json` incl. reduced-motion and keyboard runs) |

## 1. Executive summary

`/company/dashboard` is now a recruiting queue instead of a welcome hero over stat cards and two charts.

**What the page shows.** Under one page header (h1 'Dashboard', the company's name, one primary action 'Pasang lowongan'), in order:

- a summary line of non-zero real counts;
- two tertiary shortcuts ('Tinjau lamaran', 'Cari talenta');
- applicants per stage: Menunggu, Ditinjau, Terpilih, Wawancara, Penawaran;
- the 5 latest applications, with status badges from the shared vocabulary;
- a table of active jobs with applicants per job;
- the monthly applications chart, only when at least 5 months have data;
- saved talents and the newest talents in a side column.

An account without jobs gets 'Belum ada lamaran' plus the company journey as four linked steps.

**What is gone:**

- the stock photo hero (J6);
- the four '0% bulan lalu' trend tiles and both charts with their decimal 0-1.0 axis (J9);
- all English labels: 'Post Job', 'Kelola Jobs', 'Browse', 'Rekomendasi Talenta AI', 'No major specified' (J8);
- the amber, blue, green and purple accents (J16);
- an invented 'online' dot that was always on;
- the inline `<style>` block.

**Backend.** The controller no longer computes the trend queries or the 18-query chart series. It runs a few read-only queue queries instead. No route, middleware, model, migration, form or validation rule changed.

**Vocabulary.** `StatusVocabulary` gained Indonesian words for the five application statuses the company pipeline actually writes.

**Test data.** A marked, idempotent seeder added test data to the dev database (D-13).

## 2. Scope of Phase 21

| In scope | Out of scope (left untouched) |
|---|---|
| `GET /company/dashboard` (`company.dashboard`): view `resources/views/company/dashboard/index.blade.php`, `Company\DashboardController@index`, page CSS | Talents (Phase 22), jobs (Phase 23), applicant pipeline (Phase 24), company profile (Phase 25); Student workspace (Session A), Institution workspace (Session B); shared shell, navbar, footer, tokens, `components.css`, `<x-ui.*>` |

Company journey on this page:

1. Log in.
2. See how many jobs are open or in draft.
3. See where applicants are in the pipeline and who applied last.
4. Check each active job's applicant count.
5. Look at saved and newly joined talents.
6. Post a new job.

## 3. Initial condition

VERIFIED by reading the source and rendering the page on 2026-10-31.

| Area | Finding |
|---|---|
| Route / auth | `routes/web.php:356-359`: group `auth` + `check.user.type:company`, prefix `company`, name `company.dashboard`. A company user without a `companies` row is sent home by middleware ('profil anda belum lengkap…') before the controller's own check. |
| Controller | `index()` computed total/active/draft jobs, total applications, shortlisted, hires; two 30-day growth % (shown as '0% bulan lalu' when there was no earlier period); `shortlisted_growth` and `hires_growth` hard-coded `0`; 5 latest applications; 5 'AI' talent recommendations, which are really the newest students who have not applied, with `'online' => true` hard-coded; `getApplicationsOverTime` (18 count queries, three statuses only, hex colours); `getJobsByCategory`. |
| View (C01) | 459 lines: full-width `deal-work-together.jpg` hero with 'Selamat Datang Di Dashboard Perusahaan Anda'; four dashed quick-action tiles (amber/blue/green/purple, 'Post Job', 'Kelola Jobs'); four stat tiles with green ↗ trend chips; two Chart.js charts (bar `stepSize: 15`, decimal axis on small counts); English 'Browse Semua', 'Browse Talents'; 0 `.sg-btn--primary`; inline `<style>` + `style=` attributes. Captures: `reports/phase21/before/`. |
| Status values | `job_applications.status` holds two vocabularies. The seeder and the student apply form (`Student\JobController::apply`, the route the UI uses) write `pending, reviewed, shortlisted, accepted, rejected`. The company pipeline validates and writes `new, reviewing, shortlisted, interview, offer, rejected, hired`; `quickApply` writes `new`. `StatusVocabulary` only knew the first set, so new/reviewing/interview/offer/hired would have rendered in English. |
| Seeded DB (read-only queries) | 129 companies (all verified, longest name 38 chars), 622 job postings (397 active, 104 draft, 121 closed), 1,147 job applications (pending 260, reviewed 230, shortlisted 231, accepted 200, rejected 226; Aug-Oct 2026 only), 1,023 saved talents. Every company had jobs, so there was **no empty company account** and no pipeline-stage data. (The D-13 note 'job postings empty' was out of date; the project's `JobPostingKKNSeeder`/`JobApplicationsCompanySeeder` had filled them.) |
| Tests | No test rendered the company dashboard. |

## 4. Implemented features

| Spec task | Done |
|---|---|
| 21.1 Gate check | D-10 (company), D-21, D-13 asked and answered; D-02 already answered (header table). |
| 21.2 Rebuild with Phase 3 components | `x-ui.page-header` (one primary 'Pasang lowongan' → `company.jobs.create`), `x-ui.summary`, `x-ui.list`/`list-item`, `x-ui.avatar` (initials), `x-ui.status domain="job_application"`, `x-ui.table` (stacks below 640 px), `x-ui.empty-state`. The 'Belum ada lamaran' empty state is kept with a specific next step in three variants: no jobs yet (first-run steps), active jobs without applicants, drafts only ('Lihat lowongan' secondary button → `company.jobs.index`). |
| 21.3 Charts and trend chip | Trend tiles and the category chart removed. The summary line shows non-zero counts only. One chart, 'Lamaran masuk per bulan' (all applications by month, last 6 months), renders only when at least 5 of the 6 months have data. It has an integer axis, brand colour and font from tokens, no animation, and a screen-reader data table. Chart.js loads only in that case. |
| 21.4 Talent suggestions | The existing feature is kept with unchanged selection logic: the 5 newest students who have not applied to this company's jobs. They are now list rows titled 'Talenta terbaru' with an honest description, names from `display_name` (Phase 1 single source) and links to `profile.public`. The 'AI' claim and the fake 'online' dot are removed. Added: 'Talenta tersimpan' (5 latest, real total, → `company.talents.saved`), as the spec's ATS composition asks. |
| 21.5 Verify empty + populated | Live: company #1 (populated, with D-13 seed) and the seeded empty company (76-character name) at 6 widths, with and without reduced motion. Automated: 10 tests (section 9). |
| 21.6 Tracker + report | Report: this file, with C01 before/after captures. Tracker: not edited by instruction; proposed row in 12.3. |

## 5. UI/UX decisions

- **Workbench only.** No Catatan Lapangan layer (the phase file says none). The page uses a neutral surface, borders and dividers. There are no cards, tiles or shadows, and no motion. Colour carries meaning only: brand for the primary action, links and chart bars, and status tones from the shared vocabulary.
- **Hierarchy by urgency.** The main column runs from where the applicants are, to who applied, to what is open, to the trend over time. The side column holds reference lists (saved talents, newest talents). Below 1024 px everything is one column in that order.
- **Stages, not totals.** The per-stage strip shows only stages still in progress and only non-zero ones. Closed outcomes (Direkrut, Ditolak, Diterima) are not counted, so the strip answers 'who still needs my attention'. The older stored values share a word with the pipeline values: pending and new are both 'Menunggu'; reviewed and reviewing are both 'Ditinjau'. So the strip never shows the same word twice (guardrail 9).
- **Every number comes from a query and none is zero.** The summary hides zeros. Truncation is explicit ('5 terbaru dari 21 lamaran.', '5 terbaru dari 6 lowongan aktif.', '5 terakhir dari 9 talenta tersimpan.'). The applicant count in the table links to the pipeline already filtered by `job_posting_id` (an existing filter).
- **Chart rule (D-21, guardrail 2).** The series is counted in PHP so it gives the same result on PostgreSQL and SQLite. With 3 months of data there is no chart at all, not an empty one.
- **No photos.** Applicant and talent rows use initials (guardrail 7). The old talent photo path never loaded, so no real photo is lost.
- **First run.** The company journey (package SVG) becomes an HTML list of four linked steps. Its labels were checked against the real UI: the job form has five steps, and the page is called 'Lamaran'.
- **Copy** is drafted in Indonesian by Claude under D-23 (b). It is a DRAFT for the user's review. New words: 'Pasang lowongan' (from the phase file), 'Pelamar per tahap', 'Wawancara', 'Penawaran', 'Direkrut'.

## 6. Backend and data integration

**Controller.** All changes are in `app/Http/Controllers/Company/DashboardController.php::index()` (company-only controller) and are read-only:

| Change | Why |
|---|---|
| Removed: growth % queries, shortlisted/hires counts, the `stats` array, calls to `getApplicationsOverTime` / `getJobsByCategory` | Not displayed (D-10, J9). The two private methods stay in the file, unused (no drive-by deletion; same as Phase 17). |
| Added `activeJobsCount`, `draftJobsCount`, `totalJobs` | Summary line and empty-state branching. 'Active' = existing `JobPosting::active()` scope (posted, published, not expired). |
| Added `activeJobs`: `active()->with('jobCategory')->withCount('jobApplications')`, 5 newest by `published_at` | Active jobs table. |
| Added `statusCounts` (one grouped count over the company's non-deleted postings) → `totalApplications`, `stages` | Stage strip and truncation note. |
| `recentApplications`: same query, now eager-loads `user.student`; drops the unused `avatar` field and the English 'Unknown' fallbacks | Names from `display_name` (J1). |
| Added `savedTalents` (5, by `saved_at` then id) + `savedTalentsCount` via the existing `Company::savedTalents()` relation | Saved talents list. |
| `talentRecommendations`: same query; map drops `'online' => true` and the local photo path; `expertise` is null when no major | No invented data. |
| Added private `applicationsPerMonth()` and `applicationsChart` (null unless at least 5 of 6 months > 0) | D-21. |

**Authorisation is unchanged.** It is still the route middleware plus `Auth::user()->company` scoping on every query.

**Link targets kept.** All the old links still go to the same routes: `company.jobs.create`, `company.applications.index`, `company.applications.show`, `company.talents.index`, `company.jobs.index`, `profile.public`.

**Added links to existing routes:**

- `company.jobs.show`
- `company.talents.saved`
- `company.applications.index?job_posting_id=` (an existing filter)

**Vocabulary.** `app/Support/StatusVocabulary.php` gained five `job_application` entries for values the code writes: new → Menunggu, reviewing → Ditinjau, interview → Wawancara, offer → Penawaran, hired → Direkrut. This is additive; no existing word changed.

**Data written (D-13, approved).** `database/seeders/Phase21CompanyDashboardSeeder.php`, run once with `php artisan db:seed --class=Phase21CompanyDashboardSeeder`. A second run skipped everything (idempotence verified). What it adds:

- On the first seeded company (#1, Koperasi Tani Makmur Jaya): 3 job postings (2 posted on 2026-05-04, 1 draft; slugs `p21-seed-*`) and 12 job applications from students who had not applied there. The applications use statuses new ×3, reviewing ×3, interview ×2, offer ×1, hired ×1, rejected ×2, dated May-Oct 2026, with `notes = 'seed:phase21'` and status timestamps.
- One empty company account: username `p21_perusahaan_kosong`, `p21.perusahaan.kosong@studentgo.test`, name 'Koperasi Uji Pemasaran Hasil Pertanian Organik dan Perikanan Kabupaten Garut' (76 chars). Its password hash is copied from company #1's user, so no password was written anywhere.

All inserts go through `DB::table`: no model events, no notifications, no email. **To remove** the data, delete:

- `job_applications` where `notes = 'seed:phase21'`;
- `job_postings` where `slug like 'p21-seed-%'`;
- the `users` row with username `p21_perusahaan_kosong` (its `companies` row cascades).

Live counts on the page match direct queries: Menunggu 6 (pending 3 + new 3), Ditinjau 4 (reviewed 1 + reviewing 3), Terpilih 3, Wawancara 2, Penawaran 1; 21 applications; 9 saved talents; 2 drafts.

## 7. Files created and modified

| File | Change |
|---|---|
| `resources/views/company/dashboard/index.blade.php` | Rewritten (459 → 274 lines; no inline style, no inline colour, no hex). |
| `app/Http/Controllers/Company/DashboardController.php` | `index()` + new private `applicationsPerMonth()`; see section 6. |
| `resources/css/student-go/company.css` | **New**, `.sg-co-*` prefix; existing tokens only. Phases 22-25 append their own sections. |
| `resources/css/app.css` | **Shared**: +1 line `@import './student-go/company.css';` after Session B's `institution.css` line, before `legacy-overrides.css`. |
| `app/Support/StatusVocabulary.php` | **Shared**: +5 `job_application` entries (additive). |
| `tests/Feature/Phase3/ComponentsTest.php` | **Shared**: the `STORED` list for `job_application` gains the 5 pipeline values (the test locks the set of stored values; comment cites the writers). |
| `tests/Feature/Phase21/CompanyDashboardTest.php` | **New**, 10 tests / 97 assertions. |
| `database/seeders/Phase21CompanyDashboardSeeder.php` | **New** (D-13). |
| `student-go-docs/design-execution/ASSET_INVENTORY.md` | **Shared**: section 23 appended (no existing line changed). |
| `student-go-docs/design-execution/COMPONENT_INVENTORY.md` | **Shared**: the `job_application` vocabulary row extended. |
| `student-go-docs/design-execution/reports/phase21/` | **New** evidence (24 PNG + 2 JSON). |
| `student-go-docs/design-execution/completion-reports/PHASE_21_COMPLETION_REPORT.md` | This file. |
| `public/build/` | Regenerated (git-ignored). |

Not touched: routes, middleware, models, migrations, other seeders, `.env`, `tokens.css`, `components.css`, layouts, navbar, footer, `<x-ui.*>`, any other company view, any file under `public/` other than `build/`, `PHASE_TRACKER.md`, `DECISIONS_AND_OPEN_QUESTIONS.md`.

## 8. Asset reuse and asset changes

Recorded in `ASSET_INVENTORY.md` section 23. In short:

- **No change under `public/`.** No file was added, changed or removed there. The baseline hash check gives 37/38 identical and 0 changed; the missing file is `karsa-logo.png`, deleted by the user before Phase 4.
- **Deprecate as hero:** `deal-work-together.jpg`, the old dashboard hero. The phase file named `company1-5.jpg`, but the source shows that file was this one. The file is retained and is still used by the KARA list.
- **Reuse:** `no-applications.svg` and the Lucide sprite. No icon was added; `message-square-text` is used for Wawancara.
- **Adapt:** `workflows/company-journey(-vertical).svg` is turned into an HTML step list.
- **Retain, conditional:** the existing Chart.js 4.4.1 CDN tag. It is no longer loaded on every visit.
- Nothing was downloaded or generated.

## 9. Testing results

### 9.1 Acceptance criteria (section L)

| Criterion | Result | Evidence |
|---|---|---|
| No hero photo, no empty chart, no trend chip | **PASS** | After-captures: `heroPhoto` false, `trendText` false in 24/24; `canvas` 0 for the empty account and 1 drawn chart (`Chart.getChart` true) for the populated account with 6/6 months of data. Before: hero, 2 canvases and 'bulan lalu' in 12/12. Tests `test_no_hero_photo_chart_trend_or_inline_style`, `test_chart_needs_five_months_with_data` (3 months → no canvas, no Chart.js; 5 months → chart + data table). |
| All visible labels are Indonesian | **PASS** | `englishLabels` [] in 24/24 after (before: 'Post Job', 'Kelola Jobs', 'Browse', 'Rekomendasi Talenta AI' in 12/12); test `test_visible_labels_are_indonesian` (incl. no '>New<', '>Reviewing<', '>Interview<', '>Offer<', '>Hired<'). Navigation was already Indonesian since Phase 3. Data values (job titles, category names such as 'Waste Management') are stored content, not labels. |
| One primary action | **PASS** | `.sg-btn--primary` in `<main>` = 1 in 24/24 after (before 0); tests assert 1 in the whole HTML for empty, drafts-only and populated accounts. |
| Talent names consistent with the profile pages | **PASS** | All names come from `User::display_name` (the Phase 1 source the profile pages use); test `test_talent_names_match_profile_pages` (account name 'akun lama ratna' never shown, 'Ratna Kusumawati' shown, link to `profile.public`). |
| Empty state has a specific next step | **PASS** | No jobs: 'Belum ada lamaran' + 'Mulai dengan tombol Pasang lowongan di atas' + four linked steps; active jobs, no applicants: explains when applications appear; drafts only: 'Terbitkan lowongan draf Anda…' + 'Lihat lowongan'. Live capture `C01_after_empty_*`; test `test_jobs_without_applications_explain_the_next_step`. |

### 9.2 Section K and the requested evaluation

| Check | Result | Evidence |
|---|---|---|
| Page renders (populated, empty) | PASS | HTTP 200 for both accounts at 6 widths (`metrics_after.json`) |
| Backend integration / data retrieval | PASS | Counts on page = direct DB queries (section 6); fixtures in tests |
| Form submission / input validation | NOT APPLICABLE | The dashboard has no form; nothing is submitted |
| Authentication / authorisation / protected route | PASS | `test_role_access_is_unchanged` (guest → login, student → student dashboard, institution → institution dashboard); `test_company_user_without_profile_is_sent_home`; live login of both seeded company accounts → `/company/dashboard`; route list 203 = 203 |
| Navigation and link targets | PASS | `test_links_keep_their_targets` (7 URLs); keyboard order skip link → nav → primary → shortcuts → sections |
| Empty states (3 variants) | PASS | Live (no jobs) + tests (no jobs, active without applicants, drafts only) |
| Viewport matrix 360-1440, no page-level horizontal scroll | PASS | `scrollWidth <= innerWidth` in 24/24 after (12 widths × 2 accounts, plus 24 reduced-motion). A first run found 410 px at 360/390 caused by the screen-reader chart table (`sr-only` is ignored by `<table>` width); fixed by moving `sr-only` to a wrapper, re-captured |
| Long content (76-char company name, long job titles) | PASS | Empty account header wraps at 360 px; job titles wrap in the stacked table; nothing truncated in `<main>` (the navbar account button truncates, unchanged shell behaviour) |
| Guardrail 1 (primary within 160 px) | PASS | Bottom of 'Pasang lowongan' 96 px (≥ 768) to 152 px (360, empty, two-line name) below the top of `<main>` |
| Keyboard | PASS | 48 Tab stops (populated) / 26 (empty), 0 without a visible focus indicator; one `<h1>`; `lang="id"`; no overlay added by this page |
| Contrast | PASS (no new pair) | Only existing token pairs: text-primary/secondary/tertiary on the page surface and links, measured in Phase 2 (`DESIGN_SYSTEM.md`) and Phase 17 (text-tertiary on gray-50 ≈ 5.3:1); chart bars = `--sg-brand` on white (UI part, same pair as the primary button). |
| Reduced motion | PASS | No animation on the page (fade-in removed; Chart.js `animation: false`); captures identical in layout with `reduced_motion='reduce'` |
| Console / network | PASS | 0 console errors, 0 responses ≥ 400, 0 broken images on both accounts (normal and reduced motion) |
| Automated suite | PASS | Phase 21 tests 10/10 (97 assertions). Full suite **149/149 (1,717)**, including tests added meanwhile by Sessions A/B. The suite was not run before this session's edits; the first run after them was 140 (1 Phase 3 failure caused by the vocabulary change, fixed by extending the stored-value list) |
| Build | PASS | `npm run build`: CSS 219.89 kB (gzip 34.82), JS 19.82 kB unchanged; includes other sessions' CSS present at build time |
| Assets | PASS | Section 8 |
| Regression on other company pages | NOT TESTED live | Only the dashboard view/controller changed; `company.css` selectors are scoped to `.sg-co-dash`. The vocabulary addition only adds words; no other view used `job_application` with these values before (grep) |

## 10. Known limitations

1. ~~Applications with status `pending`, `reviewed` or `accepted` are invisible on the company board.~~ **Fixed after review (section 13.1).** The stored values are still two vocabularies; they are mapped, not migrated. The board's own JavaScript labels ('Interview', 'Direview') and its use of `user.name` remain for Phase 24.
2. **Two vocabularies in one column.** `pending`/`new` and `reviewed`/`reviewing` mean the same thing to a company, so they share one word, and `accepted` sits in the 'hired' column (whose board label is already 'Diterima'). Copy approved by the user on 2026-10-31.
3. **'Talenta terbaru' is not a recommendation.** The kept query orders students by id (newest first) and excludes those who applied. No skill matching exists, so the page no longer claims 'AI'.
4. ~~Chart.js still comes from a CDN.~~ **Replaced by the local npm package after review (section 13.2).** `CLAUDE.md` §2 still lists 'Chart.js 4.4.1 (company dashboard only)' among the CDN libraries; that line is for the integration owner to update (shared instruction file, not edited here).
5. **Unused code left in place:** `getApplicationsOverTime()` and `getJobsByCategory()` (no longer called), and the pre-existing unused imports.
6. **Test data in the dev DB** (section 6) is marked and removable. The empty company shares the seeded password.
7. Copy is a DRAFT pending the user's review (D-23 b).

## 11. Shared file changes and conflict risks

All three sessions write to the same working directory and branch: no worktrees, by the user's choice.

| Shared file | Change | Risk / note |
|---|---|---|
| `resources/css/app.css` | +1 `@import` after Session B's Phase 17 line | Low. Keep all workspace imports before `legacy-overrides.css`. |
| `app/Support/StatusVocabulary.php` | +5 `job_application` entries | Low (additive). Any session that also edits the `job_application` block must keep them. Phases 15 (student jobs) and 24 benefit. |
| `tests/Feature/Phase3/ComponentsTest.php` | `STORED['job_application']` extended | Low. Must stay in sync with the vocabulary. |
| `ASSET_INVENTORY.md` | Section 23 appended | Low. Renumber at integration if another session also added a section 23. |
| `COMPONENT_INVENTORY.md` | One row extended | Low. |
| `public/build/` | Rebuilt | Every session overwrites it; rebuild once after all sessions finish. |
| `resources/js/app.js` | +4 lines: dynamic import of `student-go/charts.js` when a chart is on the page (13.2) | Low. Other phases that need a chart reuse it with `canvas[data-sg-chart]`. |
| `package.json`, `package-lock.json` | + `chart.js` 4.4.1 (exact, devDependency) and `@kurkle/color` 0.3.4. npm 11.8.0 also rewrote lockfile metadata: it dropped the top-level `"name": "Karsa"` and the `libc` hints of optional platform packages. No other package version changed | Medium: lockfiles conflict easily. Re-run `npm install` after merging instead of hand-merging; run `npm install` on other machines. |
| `app/Models/JobApplication.php` | + `STATUS_ALIASES`, `statusesFor()` (13.1) | Low (additive). Phase 24 should build on it. |
| `app/Http/Controllers/Company/JobApplicationController.php` | `index()` only: alias-aware filter and columns (13.1) | Phase 24 territory, changed at the user's request. |
| Dev database | Seed rows (section 6) | Session A/B counts are unaffected (company tables only; 12 student users gained one job application each). |

Not changed: layouts, navbar, footer, tokens, `components.css`, `<x-ui.*>`, routes, middleware, models, migrations, auth. Session A's and Session B's files (student and institution dashboards, `student-dashboard.css`, `institution.css`, their tests and reports) were not touched.

## 12. Handover notes for Phase 22

### 12.1 What Phase 22 (and 23-25) can rely on

- `resources/css/student-go/company.css` is the company workspace stylesheet (prefix `.sg-co-`), already imported. Append a 'Phase 22' section instead of adding to `components.css`.
- `<x-ui.status domain="job_application">` now knows every value stored or written. Phase 24 should use it on the board and the application page instead of `JobApplication::getStatusBadgeClass`/English labels.
- The dashboard links to `company.talents.index`, `company.talents.saved` and `profile.public/{username}` for talents. Phase 22 must keep those targets.
- Talent names: always `User::display_name`, as on the dashboard.
- The queue pattern to reuse is the same as in Phase 17:
  - `x-ui.page-header` with one primary action;
  - `x-ui.summary` with non-zero real counts;
  - `x-ui.list` / `x-ui.table` rows;
  - `x-ui.empty-state` with a role-specific next step.

### 12.2 Watch-outs

- `company.talents.compare` without a selection redirects to `/company/talents` (CLAUDE.md §7.9). Keep it.
- D-36 (the talent CSV export includes student email) is still open and belongs to Phase 22.
- Talent search uses `ILIKE` (PostgreSQL only), so it cannot be rendered in SQLite tests without fixtures. Plan tests accordingly.
- The board now maps `pending/reviewed/accepted` into columns (13.1). Phase 24 must keep `JobApplication::statusesFor()` when it rebuilds the board, or migrate the stored values in a decided data fix.

### 12.3 For the integration owner (not done here by instruction)

Proposed tracker row:

`| 21 | Company Dashboard | COMPLETE | 3 | 1 | D-02, D-10, D-13, D-21 | 2026-10-31 | feat/students @ e824b9d (no commit) | All 5 AC met. Work-queue dashboard: no hero, no trend tiles; summary 'lowongan aktif · draf' (non-zero), 1 primary 'Pasang lowongan' + 2 tertiary, applicants per stage, latest applications, active-jobs table with applicants per job, saved + newest talents; one 'Lamaran masuk per bulan' chart only with >= 5 months of data (local npm chart.js 4.4.1, separate chunk loaded only then; CDN removed). StatusVocabulary + 5 pipeline values. Company board now shows pending/reviewed/accepted applications and its ?status= filter works (was ambiguous SQL). D-13 seeder (marked, idempotent) run on dev DB. Tests 13 new (114); suite 152/152; routes 203=203; 24+24 captures no overflow; 0 console errors. Report: completion-reports/PHASE_21_COMPLETION_REPORT.md. |`

Proposed decision notes for `DECISIONS_AND_OPEN_QUESTIONS.md`. These are the answers the user gave on 2026-10-31 for Phase 21:

- **D-10 (company):** work queue. Summary 'x lowongan aktif · y lowongan draf', applicants per stage, active jobs table with applicants per job; stat cards and growth % removed.
- **D-21 (company):** a chart only with at least 5 data points. 'Lamaran masuk per bulan' shows when at least 5 of 6 months have data. Library: npm `chart.js` 4.4.1 (approved 2026-10-31), loaded as a separate chunk only when a chart is on the page; the CDN tag is gone.
- **D-13 (company):** approved a seeder writing to the dev DB. `Phase21CompanyDashboardSeeder` adds 3 jobs and 12 pipeline-stage applications on company #1, plus one empty company account. The Phase 0 note 'job postings empty' is outdated: 622 postings and 1,147 applications exist from the project's seeders.
- **D-02 (addition, approved 2026-10-31):** stage words Wawancara, Penawaran, Direkrut; pipeline new/reviewing share Menunggu/Ditinjau.
- **Behaviour fix (approved 2026-10-31, D-25 style):** the company board maps the older stored values into pipeline columns (pending → new, reviewed → reviewing, accepted → hired) without changing data.

The roadmap allows Phase 22 next (hard dependencies 1 and 3; soft dependency 13) once the user approves this phase. Phase 22 was **not** started.

## 13. Follow-up after the user's review (2026-10-31)

The user answered the three open points: (1) fix the board, (2) the stage words are fine, (3) move Chart.js to a local package.

### 13.1 Applications board shows every stored status

- `JobApplication::STATUS_ALIASES` / `statusesFor()`: each pipeline stage also covers its older stored value (new ⊇ pending, reviewing ⊇ reviewed, hired ⊇ accepted). The dashboard's stage strip now uses the same helper, so the dashboard and the board count the same way.
- `Company\JobApplicationController@index`: the columns and the `new/shortlisted/hired` counts use the helper. The `?status=` filter uses it too and is now written as `job_applications.status`.
- **Pre-existing bug also fixed.** The old `where('status', …)` on the `hasManyThrough` join was ambiguous (`job_postings` also has `status`), so **every** status filter on the board used to fail with an SQL error.
- No stored value was rewritten. The student apply form still writes `pending`; actions on the board still write the pipeline values.
- Evidence:
  - Tests: `tests/Feature/Phase21/ApplicationsBoardStatusTest.php`, 3 tests / 16 assertions. They check that all 8 stored values land in a column, that the filter and counts include the equivalents, and that data is unchanged.
  - Live on PostgreSQL, company #1: 21/21 applications on the board (before: 16). Columns: new 6, reviewing 4, shortlisted 3, interview 2, offer 1, hired 2, rejected 3.
  - Filters: `?status=new` → 6, `?status=pending` → 3, `?status=reviewing` → 4, `?status=hired` → 2; all HTTP 200, 0 console errors.
- Not changed, left for Phases 23-24:
  - the board's JavaScript labels ('Interview', 'Direview');
  - its `user.name` display (J1);
  - `JobPostingController@show`'s `is_new` flag (`status === 'new'` only).

### 13.2 Chart.js from a local npm package

- **Package:** `npm install --save-dev --save-exact chart.js@4.4.1`, the same version the CDN served (MIT). It brings `@kurkle/color` 0.3.4 (MIT). No other dependency changed.
- **New module** `resources/js/student-go/charts.js`:
  - registers only the bar chart parts;
  - reads its data from `canvas[data-sg-chart]` (JSON) and `data-sg-chart-label`;
  - takes colours and font from tokens and turns animation off;
  - sets `data-sg-chart-ready` after drawing.
- **Loading:** `resources/js/app.js` imports the module dynamically, only when such a canvas exists. Vite emits it as a separate chunk, `charts-*.js` (141.30 kB, gzip 49.56 kB). `app.js` grew by 1.55 kB, from 19.82 to 21.37 kB (gzip 7.13 → 7.79). `vite.config.js` is unchanged.
- **View:** the dashboard view has no `<script>` any more; the CDN tag and the inline script are removed.
- Evidence:
  - Re-captured `reports/phase21/after/` (24 + 24 reduced-motion): 0 overflow, one `<h1>` and one primary in every capture.
  - The chart is drawn at all 6 widths on the populated account. The only chart request is `/build/assets/charts-*.js`, with no CDN request. The empty account requests no chart code. 0 console errors.
  - Tests assert `data-sg-chart` and the absence of the CDN URL.

### 13.3 Test totals after the follow-up

Phase 21: 13 tests / 114 assertions. Full suite **152/152 (1,734 assertions)**, including tests added meanwhile by the other sessions. Routes 203 = 203.
