# PHASE 17 — Institution Dashboard: completion report

| Field | Value |
|---|---|
| Phase | 17 — Institution Dashboard (`phases/PHASE_17_INSTITUTION_DASHBOARD.md`) |
| Session | Session B (institution workspace), run in parallel with Session A (Phase 7) and Session C (Phase 21) |
| Date | 2026-10-31 |
| Branch / commit | `feat/students` @ `e824b9d`, no commit (D-27). **No separate worktree**: the user declined one; all three sessions share this working directory (see section 11) |
| Recommended status | **COMPLETE**: all 5 acceptance criteria met (section 9.1). `PHASE_TRACKER.md` was **not** edited (the user's instruction for parallel sessions); proposed row in section 12.3 |
| Prerequisites | Phase 3 (hard) COMPLETE; Phase 1 (soft) COMPLETE; Phases 0-6 COMPLETE in the tracker; suite 111/111 at session start |
| Gate answers (this session, by the user) | **D-10**: institution dashboard = work queue (verification status, applicants awaiting a decision, running projects, drafts); no zero tiles, no trend chip. **D-21**: no charts (option a). **D-13**: existing seed data is enough; no seeder, no DB change. **D-09** (for this phase only): banner shows the real status only, with no promise about the method or duration; the full D-09 decision stays with Phase 20. After the first report the user asked for these answers to be recorded and approved three data fixes (section 13); all of it is now in `DECISIONS_AND_OPEN_QUESTIONS.md` (D-09, D-10, D-13, D-21 + 'Note on Phase 17 data fixes'). Page copy approved ('jika konten saja saya acc') |
| Evidence folder | `reports/phase17/` (`before/`, `after/`: 12 full-page captures each + `metrics_*.json`) |

## 1. Executive summary

`/institution/dashboard` is now a work queue instead of a stat board. Under one page header (h1 'Dashboard', the institution's full name, one primary action 'Buat masalah baru'), the page shows:

- the verification status as the first block while the institution is not verified;
- one summary line of real, non-zero queue counts;
- two tertiary shortcuts;
- divider lists: applicants awaiting a decision, running projects (next milestone and reports awaiting review), completed projects awaiting a review, draft problems, recent problems.

Gone from the page:

- the 4 + 8 stat tiles and the red '↓ 0.0% dari bulan lalu' trend;
- the three coloured quick-action buttons;
- English status words ('Pending', 'Open', 'Review');
- the 'otomatis menggunakan AI' and '1-2 hari kerja' promises;
- the inline `<style>` fade-in.

The controller stopped computing chart, trend and 'AI recommendation' data that the page never displayed. One of those computations used PostgreSQL-only SQL and invented category labels. In its place the controller now runs three read-only queries the queue needs. No route, middleware, model, migration, form, validation rule or database row changed.

## 2. Scope of Phase 17

| In scope | Out of scope (left untouched) |
|---|---|
| `GET /institution/dashboard` (`institution.dashboard`): view `resources/views/institution/dashboard/index.blade.php`, its controller method `Institution\DashboardController@index`, page CSS | Problem management (Phase 18), application review / projects / reviews screens (Phase 19), profile and verification pages (Phase 20), Student workspace (Session A), Company workspace (Session C), shared shell, tokens, components |

Institution journey covered by this page: log in → see whether verification is still pending → see who is waiting for a decision → check running projects (next milestone, reports to review) → write reviews for completed projects → finish draft problems → create a new problem.

## 3. Initial condition

VERIFIED by reading the source and rendering the page on 2026-10-31 (accounts: seeded institution #33 with data, #64 without problems and with a 71-character name; credentials not recorded).

| Area | Finding |
|---|---|
| Route / auth | `routes/web.php:274-277`: group `auth` + `check.user.type:institution`, prefix `institution`, name `institution.dashboard`. Guest → `/login`; other roles → their own dashboard with flash (Phase 6 tests). |
| Controller | `DashboardController@index` computed `AnalyticsService::getInstitutionAnalytics`, a monthly growth %, 'AI recommendations' (actually the latest `under_review`/`accepted` applications), a 6-month time series (18 count queries), and SDG 'jobs by category' using `json_array_elements_text` (PostgreSQL only) with an invented fallback `['Engineering', 'Marketing', 'Design', 'HR', 'Sales']`. The view used none of the last three. `urgentItems['pending_reviews']` was the count of a `limit(5)` collection (showed 5 when 13 were waiting). |
| View (I01) | 418 lines; 4 stat tiles (problem growth trend shown as red '↓ 0.0%' even for an empty account) + 2 × 4 number grids (acceptance rate, average progress); yellow 'Perhatian Diperlukan' banner; three solid quick actions (blue, dark yellow, green); English badges 'Pending', 'Review', 'Open'; verification banner copy promising automatic AI verification and '1-2 hari kerja'; inline `<style>` animation; 0 `.sg-btn--primary`. Captures: `reports/phase17/before/`. |
| Data model facts | `applications.status` ∈ pending, reviewed, accepted, rejected; the review flow only decides `pending` or `under_review` (a value not in the enum). `project_reports` has no due date (only `period_start/end`, `status`); milestones have `target_date`. `institutions.verification_status` string, default `pending_verification`. |
| Seeded DB (read-only queries) | 79 institutions, **all** `pending_verification` (while the seeder sets `is_verified = true`); 77 problems, all `open` (no draft); applications 44 pending / 31 reviewed / 259 accepted / 25 rejected; projects 64 active / 195 completed; 1,036 milestones; 254 reports pending. |
| Tests | 111/111 (1,402 assertions). No test rendered the dashboard (it could not render on SQLite because of the PostgreSQL-only query). |

## 4. Implemented features

| Spec task | Done |
|---|---|
| 17.1 Gate check | D-10, D-13, D-09, D-21 asked and answered (header table). |
| 17.2 Verification banner | Same display logic (`in_array(status, ['pending_verification','rejected','needs_review'])`, same branches). `x-ui.alert` (info / danger / warning) titled 'Verifikasi instansi' with an `x-ui.status domain="verification"` badge ('Menunggu Verifikasi', 'Ditolak', 'Perlu Ditinjau') and neutral copy. Same targets: `institution.verification.upload`, `institution.verification.status`. Its buttons are secondary/tertiary so the page keeps one primary. |
| 17.3 Queue sections | `x-ui.list` / `x-ui.list-item` rows: **Lamaran menunggu keputusan** (initials, name → `applications.show`, problem title, university · 'Diajukan j M Y', status badge; '5 terbaru dari N' when truncated); **Proyek berjalan** (title → `projects.show`, student, next not-completed milestone + target date, '(lewat target)' in warning text when overdue, 'N laporan menunggu tinjauan', native `<progress>` + %); **Menunggu ulasan** (completed, unreviewed projects, 'Tulis ulasan' → `reviews.create`, real total); **Masalah draf** (→ `problems.edit`); **Masalah terbaru** (→ `problems.show`, application count, status). A section with nothing in it is hidden; when both main sections are empty an `x-ui.empty-state` gives the next step ('Belum ada masalah yang diterbitkan' or 'Tidak ada lamaran yang menunggu atau proyek berjalan'). |
| 17.4 Actions | One primary `x-ui.button` 'Buat masalah baru' in the page header; two tertiary shortcuts 'Tinjau lamaran' (`applications.index?status=pending`, the old 'Review Aplikasi' target) and 'Pantau proyek' (`projects.index`, the old 'Monitor Proyek' target). |
| 17.5 Tiles and trend | Removed. `x-ui.summary` line with non-zero queue counts only ('4 lamaran menunggu keputusan · 6 proyek berjalan · 6 proyek selesai menunggu ulasan'); an empty account shows no summary line at all. |
| 17.6 Verify empty + populated | Live: #33 (populated) and #64 (empty, 71-char name) at 6 widths. Automated: verified, rejected, needs_review, draft and >5 reviews states (not present in the seeded DB). |
| 17.7 Tracker + report | Report: this file. Tracker: not edited by instruction; proposed text in 12.3. |

## 5. UI/UX decisions

- **Workbench only.** No Catatan Lapangan layer (the phase file says none). Neutral surface, borders and dividers, no cards, no shadows, no tiles, no motion. Colour carries meaning only: brand = primary action, links, progress fill; status tones from the shared vocabulary; warning text for a past milestone date, always with words ('lewat target').
- **Hierarchy by urgency.** Main column (2/3 from 1024 px): decisions waiting (applications), then running work. Side column: reviews owed, drafts, recent problems (reference). One column below 1024 px in that order. When the side column is empty the grid becomes a single column.
- **Every number comes from a query and none is zero.** The summary hides zero items; truncation is explicit ('5 dari 6 proyek selesai.'). No trend appears at all: D-10/D-21 and guardrail 2 require two periods of data, and the dashboard does not need one.
- **Status words.** Only `<x-ui.status>` (D-02). A value the vocabulary does not know (e.g. the never-stored `under_review`) falls back to a neutral label, never an invented status.
- **Honest verification copy (D-09).** 'Unggah dokumen verifikasi agar statusnya dapat diperiksa.' / '…belum lolos. Periksa hasil verifikasi, lalu unggah ulang…' / '…perlu ditinjau secara manual. Status verifikasi akan diperbarui setelah peninjauan selesai.' No method (AI) and no duration promised.
- **Long content.** Titles, names and the 71/76-character institution names wrap (`overflow-wrap: anywhere`), never truncate; `Str::limit` removed.
- **Progress** uses native `<progress value max=100>` with an accessible name ('Progres <project title>') plus the visible percentage. The bar is not the only carrier of the value.
- **Not adopted from `/ui-ux-pro-max`:** a generated design system (the project's Phase 2/3 system is binding). Targeted UX searches (empty state with next action, progress feedback) confirmed the choices above. One query ('dashboard work queue') returned off-topic results and was not used.
- **Copy** was drafted by Claude in Indonesian under D-23 (b) and **approved by the user** after the first report.

## 6. Backend and data integration

All changes are in `app/Http/Controllers/Institution/DashboardController.php::index()` (institution-only controller), read-only:

| Change | Why |
|---|---|
| Stopped calling `calculateMonthlyGrowth`, `getAIRecommendations`, `prepareTimeSeriesChartData`, `getJobsCategoryData`; removed `aiRecommendations`, `timeSeriesData`, `jobsCategoryData` from the view data | Not displayed (D-21 no charts; D-10 no trend). Saves ~20 queries per load; removes the PostgreSQL-only SQL and the invented fallback labels from the page path. The private methods remain in the file, unused (no drive-by deletion). |
| `activeProjects`: eager-load `milestones` where status ≠ completed (relation order `order`, then `target_date`) and `withCount(reports as pending_reports_count where status = pending)` | Next milestone and reports waiting for review. `project_reports` has no due date, so 'laporan jatuh tempo' is shown as the next milestone target + pending reports (real columns only). |
| `urgentItems['pending_reviews']` = `count()` of the same query instead of the count of the 5-row collection | Real total (institution #77 has 13, the old page said 5). |
| `draftProblems` (5 latest by `updated_at`) + `draftProblemsCount` | Draft queue (D-10). |
| Kept: `stats` (AnalyticsService), `recentApplications` (pending/under_review, unchanged filter), `pendingReviews`, `recentProblems`, `institution` | Unchanged data sources. |

Authorisation is unchanged: route middleware and `auth()->user()->institution` scoping on every query (`institution_id` / `whereHas problem.institution_id`). Link targets kept: `problems.create`, `applications.index`, `applications.index?status=pending`, `applications.show`, `projects.index`, `projects.index?status=active`, `projects.show`, `reviews.create`, `problems.index`, `problems.show`, `verification.upload`, `verification.status`. **Added links to existing routes:** `reviews.index` ('Lihat semua' on the reviews section), `problems.index?status=draft` (an existing filter) and `problems.edit` (draft rows).

## 7. Files created and modified

| File | Change |
|---|---|
| `resources/views/institution/dashboard/index.blade.php` | Rewritten (418 → 218 lines; no inline style, no inline colour). |
| `app/Http/Controllers/Institution/DashboardController.php` | `index()` only (+27 / −19); see section 6. |
| `resources/css/student-go/institution.css` | **New**, `.sg-inst-*` prefix; existing tokens only. Phases 18-20 append their own sections. |
| `resources/css/app.css` | **Shared**: +1 line `@import './student-go/institution.css';` after Session A's `student-dashboard.css` line, before `legacy-overrides.css`. |
| `tests/Feature/Phase17/InstitutionDashboardTest.php` | **New**, 7 tests / 75 assertions. |
| `student-go-docs/design-execution/ASSET_INVENTORY.md` | **Shared**: section 22 appended (no existing line changed). |
| `student-go-docs/design-execution/reports/phase17/` | **New** evidence (24 PNG + 2 JSON). |
| `student-go-docs/design-execution/completion-reports/PHASE_17_COMPLETION_REPORT.md` | This file. |
| `public/build/` | Regenerated (git-ignored). |

Not touched: routes, middleware, models, migrations, seeders, `.env`, `components.css`, `tokens.css`, layouts, navbar, footer, `StatusVocabulary`, `<x-ui.*>` components, any file under `public/` other than `build/`, `PHASE_TRACKER.md`, `DECISIONS_AND_OPEN_QUESTIONS.md`, `COMPONENT_INVENTORY.md`.

## 8. Asset reuse and asset changes

Recorded in `ASSET_INVENTORY.md` section 22. In short:

- **No file was added, changed or removed under `public/`.** The baseline hash check gives 37/38 identical and 0 changed. The one missing file is `student-go-logo.png`, deleted by the user before Phase 4.
- **Reuse:** `no-applications.svg` (empty state) and the Lucide sprite (no new icon).
- **Retain, not shown:** the institution logo (the audit keeps the header without a photo) and student photos (applicant rows use initials).
- **Not placed:** `workflows/institution-journey*.svg`. Its label 'halaman Pelamar' contradicts the navigation ('Aplikasi'), and image text does not scale at 360 px.
- **Replaced:** inline heroicon SVGs and the inline `<style>` block.
- Nothing downloaded or generated.

## 9. Testing results

### 9.1 Acceptance criteria (section L)

| Criterion | Result | Evidence |
|---|---|---|
| No trend chip without two periods of data | **PASS** | No trend rendered at all. `trendText` false in 12/12 after-captures (before: true 12/12); tests assert no 'dari bulan lalu', '↓', '↑', '0.0%'. |
| No all-zero tile grid | **PASS** | No tiles; summary hides zeros; empty account shows no summary (test `test_empty_account_…`, capture `I01_after_empty_*`). |
| Exactly one primary button | **PASS** | `.sg-btn--primary` in `<main>` = 1 in 12/12 captures (before: 0, three coloured buttons); `substr_count` = 1 in the full HTML in tests (empty and populated). |
| Statuses use the shared vocabulary | **PASS** | Live: 'Menunggu', 'Dibuka', 'Menunggu Verifikasi'. Tests: 'Draf', 'Ditolak', 'Perlu Ditinjau'; no '>Pending<', '>Open<', '>Review<'. |
| Verification state is the first block when unverified | **PASS** | Test asserts header `<h1>` < 'Verifikasi instansi' < summary bar; live captures show it directly under the header; verified institution → no block (test). |

### 9.2 Section K and the requested evaluation

| Check | Result | Evidence |
|---|---|---|
| Page renders (populated, empty) | PASS | HTTP 200 × 12 (`metrics_after.json`) |
| Backend integration / data retrieval | PASS | Counts on page = DB (#33: 4 pending, 6 active, 6 completed unreviewed; milestone and report lines from real rows); tests with fixtures |
| Data storage / form validation | NOT APPLICABLE | The dashboard has no form; nothing is submitted |
| Authentication / authorisation | PASS | Test: guest → login, student → student dashboard, company → company dashboard; route middleware unchanged (`route:list`) |
| Link targets preserved | PASS | `test_links_keep_their_targets` (11 URLs) |
| Empty states | PASS | Empty account live; 'nothing waiting' variant by code path (not live: every seeded institution with problems also has activity) |
| Verified / rejected / needs_review / draft states | PASS (automated only) | Not present in the seeded DB; D-13 answered 'seed enough', so the DB was not modified; covered by feature tests |
| Viewport matrix 360-1440, no page-level horizontal scroll | PASS | `scrollWidth == innerWidth` in 24/24 captures (before and after), 12/12 again with `prefers-reduced-motion: reduce` |
| Long content (71-char institution name, long titles) | PASS | #64 wraps in header at 360 px; no truncation |
| Keyboard | PASS | 40 Tab stops per account, 0 without a visible focus indicator; order: skip link → nav → primary → verification → shortcuts → sections; one `<h1>`; `lang="id"` |
| Guardrail 1 (primary within 160 px) | PASS | Bottom of the primary button 97 px (1440) to 153 px (360, empty, 2-line name) below the top of `<main>` |
| Contrast (new pairs) | PASS | warning-700 on white 7.48:1 / on gray-50 ≈7.0:1; text-tertiary on gray-50 ≈5.3:1, on info-50 4.98:1; progress fill vs track 6.04:1 (≥3:1 UI) |
| Console / network | PASS | 0 console errors, 0 responses ≥ 400 on both accounts |
| Reduced motion | PASS | The page has no animation (old fade-in removed) |
| Automated suite | PASS | Before: 111/111 (1,402). After: **130/130 (1,565)**, which includes the 7 Phase 17 tests (75 assertions) and tests added meanwhile by Session A |
| Build | PASS | `npm run build`: CSS 216.79 kB (gzip 34.48), JS 19.82 kB unchanged; includes Session A's CSS present at build time |
| Routes | PASS | 203 = 203 |
| Assets | PASS | Section 8 |
| Regression on other pages | NOT TESTED live | Only the dashboard view and its controller changed; `institution.css` selectors are scoped to `.sg-inst-dash` |

## 10. Known limitations

1. **RESOLVED (section 13.3): 'laporan jatuh tempo'.** A milestone can now be marked 'Wajib laporan' (`project_milestones.requires_report`); the dashboard shows its target date as the report due date. Students do not see the flag yet (student workspace).
2. **RESOLVED (section 13.1): `reviewed` vs `under_review`.** Institution code now compares with `reviewed`; the 31 seeded `reviewed` applications can be accepted or rejected and appear in the dashboard queue.
3. **RESOLVED (section 13.2): seeded verification.** 75 institutions `active`/verified, 4 (#16, #32, #48, #64) `pending_verification`/unverified, in the seeder and in the local database.
4. **Unused code left in place:** the four private methods no longer called by `index()`. In the same controller, `getChartData`/`getSdgDistribution`/`exportReport` call service methods that do not exist; none is routed. They are pre-existing, not removed (no drive-by refactor).
5. Sections with nothing in them are hidden (17.3). An institution with running projects but no pending applicants does not see an 'applications' heading; the summary line and the 'Tinjau lamaran' shortcut remain.
6. Copy: approved by the user (section 13).
7. The empty-state illustration `no-applications.svg` is shared with the student dashboard (Phase 7); its picture (document + map pin) is generic enough for both.

## 11. Shared file changes and conflict risks

All three sessions write to the same working directory and branch (no worktrees, by the user's choice). What this phase touched outside institution-only files:

| Shared file | Change | Risk / note |
|---|---|---|
| `resources/css/app.css` | +1 `@import` line after Session A's Phase 7 line | Low. Session C will add its own line; keep all three, all before `legacy-overrides.css`. |
| `student-go-docs/design-execution/ASSET_INVENTORY.md` | Section 22 appended | Low. If Session C also appends a 'section 22', renumber at integration. |
| `public/build/` | Rebuilt | Every session overwrites it. Rebuild once after all three sessions finish before final visual checks. |
| `tests/` | New folder `Phase17/` only | None. The full-suite count includes the other sessions' tests. |

Not changed: `components.css`, `tokens.css`, layouts, navbar, footer, `StatusVocabulary`, `<x-ui.*>`, routes, models, migrations, middleware, `PHASE_TRACKER.md`, `DECISIONS_AND_OPEN_QUESTIONS.md`. Session C (company dashboard) uses a different controller and view. Session A's `student-dashboard.css` and Phase 7 report were left untouched.

## 12. Handover notes for Phase 18

### 12.1 What Phase 18 can rely on

- `resources/css/student-go/institution.css` is the institution workspace stylesheet (prefix `.sg-inst-`), already imported. Append a 'Phase 18' section rather than adding to `components.css`.
- The dashboard links to `problems.index` (and `?status=draft`), `problems.show`, `problems.edit`, `problems.create`. Phase 18 must keep these routes and the `status` query filter (`ProblemController@index` already supports it).
- The queue pattern to reuse: `x-ui.page-header` (one primary in `actions`), `x-ui.summary` (non-zero real counts), `x-ui.list`/`list-item` rows with `<x-ui.status domain="problem">`, `x-ui.empty-state` with a role-specific next step.
- Problem statuses in the DB are `draft, open, in_progress, completed, closed` (`StatusVocabulary`: Draf, Dibuka, Berjalan, Selesai, Ditutup). The seeded DB has only `open`; draft/closed states need fixtures or D-13 data.

### 12.2 Watch-outs

- Do not GET `/institution/reviews/create/*` during browser checks (Phase 0 rule). The dashboard links to it but the captures never follow links.
- The `under_review`/`reviewed` mismatch (section 10.2) affects Phase 19, not 18.
- `ProblemController` stats include `draft`, so the dashboard's 'masalah draf' count and the problems list should agree once Phase 18 restyles the list.

### 12.3 For the integration owner (tracker not edited by instruction; decisions now recorded, section 13)

Proposed tracker row:

`| 17 | Institution Dashboard | COMPLETE | 3 | 1 | D-09, D-10, D-13, D-21 | 2026-10-31 | feat/students @ e824b9d (no commit) | All 5 AC met. Work-queue dashboard: verification first (neutral copy), summary line of non-zero real counts, 1 primary + 2 tertiary, lists for pending applicants / running projects (next milestone, pending reports) / reviews owed (real total) / drafts / recent problems; no tiles, trend or charts. Controller: unused chart/trend/AI queries no longer run; 3 read-only queue queries. Approved data fixes: reviewed status decidable, seeded verification consistent (75 active / 4 pending), milestone requires_report (1 additive migration), milestone edit/delete 500 fixed. Tests 16 new (115); suite 149/149; routes 203=203; 24 captures no overflow; 0 console errors. Report: completion-reports/PHASE_17_COMPLETION_REPORT.md. |`

Proposed decision notes for `DECISIONS_AND_OPEN_QUESTIONS.md` (answers given by the user on 2026-10-31 for Phase 17):

- D-10 (institution): verification status, applicants awaiting a decision, running projects, drafts; no zero tiles, no trend.
- D-21: (a) no charts on the institution dashboard.
- D-13: seeded data sufficient for Phase 17; no new seeder or DB change.
- D-09: Phase 17 banner shows the real status only, without promising a method or duration; the full decision remains open for Phase 20.

The roadmap allows Phase 18 next (hard dependency Phase 3; soft 1 and 17) once the user approves this phase. Phase 18 was **not** started.

## 13. Addendum: data fixes after the first report (2026-10-31)

The user answered the first report: '1. perbaiki, 2. perbaiki, 3. tambahkan, 4. rekomendasi anda silahkan kerjakan, 5. konten saya acc'. Item 2 was then answered 'campur (sebagian pending)', item 3 'tanda wajib laporan di milestone' (asked with options). These change behaviour or data. Each is recorded in `DECISIONS_AND_OPEN_QUESTIONS.md`, 'Note on Phase 17 data fixes'.

### 13.1 Application status `reviewed` (fix)

- `Institution\ApplicationReviewController`: review / accept / reject / bulk accept / bulk reject now accept `pending` or `reviewed` (was `under_review`, which is never stored). The index count under the kept key `under_review` now counts `reviewed`.
- `AnalyticsService` (counts, funnel) and `Institution\DashboardController` (queue filter; 'menunggu keputusan' = pending + reviewed) compare with `reviewed`.
- Views `institution/applications/index` (filter option `reviewed` 'Ditinjau', badge 'Ditinjau', action buttons) and `institution/applications/show` (badge colour, action block) compare with `reviewed`. No restyle (Phase 19).
- `app/helpers.php`: `status_badge_color` / `status_label` gain an additive `reviewed` entry.
- Query string: `?status=under_review` → `?status=reviewed` (parameter name unchanged). Student code already used `reviewed` and was not touched.

### 13.2 Seeded institution verification (fix, 'campur')

- Source fact: for institutions the verified value is `active` (`Institution::STATUS_ACTIVE`, written by `AIDocumentVerificationService`; the verification pages map `active` → 'Aktif - Terverifikasi'). `verified` belongs to the companies enum. A first pass in this session wrote `verified`; it was corrected to `active` before any check ran.
- `DummyDataSeeder`: `is_verified`, `verification_status` and `verified_at` are now written together; every 16th institution (`($index + 1) % 16 === 0`) stays unverified.
- Local database (approved): backup first, `reports/phase17/db-backup-institutions-verification.csv` (79 rows: id + three status columns, no personal data). Then one transaction: 75 × `true`/`active`/`verified_at = created_at`, 4 × `false`/`pending_verification`/null (#16, #32, #48, #64).
- Effect: seeded account #33 (verified, populated) no longer shows the verification block; #16 (pending, populated) and #64 (pending, empty, 71-character name) do.

### 13.3 Report due date (added)

- Migration `database/migrations/2026_10_31_000001_add_requires_report_to_project_milestones_table.php`: additive, `boolean default false`, guarded by `hasColumn`. Run alone with `--path` after confirming no other migration was pending; 62 migrations have run.
- `ProjectMilestone`: fillable + boolean cast. `ProjectManagementController@addMilestone` / `@updateMilestone`: optional `requires_report` (`nullable|boolean`).
- `institution/projects/manage`: checkbox 'Wajib laporan' in the add modal (hidden `0` + checkbox `1`) and in the edit modal (filled from the existing JSON, sent in the existing PUT body); a 'Wajib laporan' label in the milestone list. Legacy styling kept (Phase 19 restyles).
- Dashboard: 'Laporan jatuh tempo: <milestone> · <date>' (warning text + '(lewat tenggat)' when past) for the next not-completed milestone marked 'Wajib laporan'. 'Milestone berikutnya' appears only when it is a different milestone.
- Existing milestones are `false` (not guessed), so the line appears once an institution marks a milestone.

### 13.4 Bug found while testing 13.3 (fix)

`ProjectManagementController@updateMilestone` and `@deleteMilestone` called `ProjectService::updateProjectProgress()`, which does not exist, so every milestone edit or delete returned HTTP 500 after the write. Both now call `Project::updateProgress()`, the model method that the student side and `ProjectService` already use.

### 13.5 Tests and checks after the fixes

| Check | Result | Evidence |
|---|---|---|
| Phase 17 tests | PASS 16/16 (115 assertions) | `tests/Feature/Phase17/InstitutionDashboardTest.php` (7) + `InstitutionDataFixesTest.php` (9: accept/reject `reviewed`, decided application still refused, filter + counts, column default, add/get/update `requires_report`, delete milestone, report-due line, no duplicate line) |
| Full suite | PASS 149/149 (1,717 assertions) | Includes tests added meanwhile by Sessions A and C |
| Routes | PASS 203 = 203 | `route:list` |
| Build | PASS | CSS 219.89 kB (gzip 34.82), includes the other sessions' CSS |
| Dashboard live, 3 accounts × 6 widths | PASS | `reports/phase17/after-fixes/` (verified #33, pending #16, empty pending #64): HTTP 200, 0 overflow, 1 h1, 1 primary, no trend, 0 console errors, 0 responses ≥ 400, focus visible on every stop |
| Applications filter `?status=reviewed` live (#33) | PASS | Option selected, 2 'Ditinjau' occurrences; `reports/phase17/data-fixes/` |
| Manage page live (project #119) | PASS | Checkbox present in both modals; edit modal filled from JSON (unchecked); 0 console errors. No form submitted |
| Accept / reject / edit / delete live | NOT TESTED live (by design) | State-changing; covered by feature tests only |

### 13.6 Files added or changed by the addendum

- `app/Http/Controllers/Institution/ApplicationReviewController.php`
- `app/Http/Controllers/Institution/ProjectManagementController.php`
- `app/Http/Controllers/Institution/DashboardController.php`
- `app/Services/AnalyticsService.php`
- `app/Models/ProjectMilestone.php`
- `app/helpers.php` (shared, additive)
- `database/seeders/DummyDataSeeder.php` (shared)
- the new migration
- `resources/views/institution/applications/index.blade.php`
- `resources/views/institution/applications/show.blade.php`
- `resources/views/institution/projects/manage.blade.php`
- `resources/views/institution/dashboard/index.blade.php`
- `tests/Feature/Phase17/InstitutionDataFixesTest.php`
- `student-go-docs/design-execution/DECISIONS_AND_OPEN_QUESTIONS.md` (shared; four ANSWER lines + one appended note)
- evidence in `reports/phase17/after-fixes/` and `reports/phase17/data-fixes/`
- the DB backup CSV

### 13.7 Conflict risks and handover added by the addendum

- `app/helpers.php` and `DummyDataSeeder.php` are shared files. The changes are additive or limited to the institution batch. Re-seeding is not required and was not run.
- The local database now has a new column and changed institution rows, and all sessions share it. Session A's student pages may show four institutions without the verified badge.
- `StatusVocabulary` (shared) has no `active` entry for `verification`. The dashboard never shows that value; Phase 20 should add 'active' → 'Terverifikasi'.
- For Phase 19: the manage-page modals have a black overlay (`bg-opacity-50` does nothing in Tailwind v4; pre-existing), and the edit modal fills `target_date` from an ISO timestamp (pre-existing; check when restyling).
- For the student workspace (Phase 11): show 'Wajib laporan' and its due date to the student on their project page.

**Recommended status remains COMPLETE.** Phase 18 was not started.
