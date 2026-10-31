# PHASE 07 — Student Dashboard: completion report

| Field | Value |
|---|---|
| Phase | 7 — Student Dashboard (`phases/PHASE_07_STUDENT_DASHBOARD.md`); run as "Session A — Student Workspace" in parallel with Session B (Phase 17) and Session C (Phase 21) |
| Date | 2026-10-31 |
| Branch / commit | `feat/students` @ `e824b9d`, no commit (D-27). No worktree: the user declined one; all three sessions share this working tree |
| Recommended status | **COMPLETE**: all 6 acceptance criteria met (section 9.3). `PHASE_TRACKER.md` was **not** edited (parallel-session rule); the tracker row should be set at integration |
| Prerequisites | Phase 1 (hard) COMPLETE, Phase 3 (hard) COMPLETE; Phases 0–6 COMPLETE in the tracker |
| Gate answers (this session) | D-03 already answered (Phase 1). D-10: work-queue composition (student only). D-21: option (b), but no chart here (no series ≥ 5 points). D-12 answered (Phase 0). Extra: user approved the `skills` crash fix; friend-request Terima/Tolak stay in the queue and 'Hubungkan' becomes a link to the network page |
| Evidence folder | `reports/phase7/` (`before/`, `after/`, `metrics-*.json`, `interaction-checks.json`, `routes-after.json`) |

## 1. Executive Summary

`/student/dashboard` went from a 3,002 px page that opened with a 370 px photo hero to a work queue of about 1,057 px (1440 px wide, audit account shape). The page now opens with a plain header (greeting h1, major · university, Telusuri Masalah + Aplikasi Saya), then **Langkah berikutnya** (nearest milestone deadline first, incoming connection requests with Terima/Tolak, pending applications, profile completion), **Rekomendasi masalah** (3 rows with a quiet 'Lihat'), one **Jaringan** summary line, **Proyek berjalan** with real progress, and **Lamaran terbaru** as a table with the shared status words. The hero, the pastel network tiles, the 'Statistik Aktivitas' tiles and bars, the purple 'Hubungkan' buttons (4.12:1), the English status words and the page's inline `<style>` and Google Fonts link are gone.

All data comes from `Student\DashboardController`, which already computed it; the old view ignored most of it and re-queried in `@php` (with a wrong project status, so active projects never appeared). One latent crash in that controller was fixed with the user's approval. Routes are unchanged (203 = 203). Tests: 130/130 (1,565 assertions), 9 of them new for this phase.

## 2. Scope Phase 7

In scope (phase file sections A, F): `/student/dashboard` only — header, next-steps block, recommended problems, network summary, recent applications; removal of tiles, duplicate statistics and charts.

Out of scope and untouched: applications page (Phase 10), network page and its 'Hubungkan' UI (Phase 13), the avatar menu and shell (Phase 3), every other student page (Phases 8–16), institution and company workspaces (Sessions B, C).

User journeys supported: (1) see what needs action now (deadline, request, pending decision, incomplete profile) and act from the row; (2) start discovery (Telusuri Masalah, recommendations); (3) check recent applications and open one; (4) open an active project; (5) a new student with no applications gets one clear next step.

Relationship to Phase 6: the dashboard is the post-login target for students; Phase 6's redirects and middleware were not touched. Verified by the Phase 6 tests (login redirect, guest redirect, cross-role redirect) and by `test_other_roles_and_guests_keep_their_redirects`.

## 3. Initial Condition

Read on 2026-10-31 (VERIFIED in source and on the running app):

| Area | Finding |
|---|---|
| Route | `GET /student/dashboard` → `Student\DashboardController@index`, name `student.dashboard`, middleware `auth` + `check.user.type:student` |
| Controller | Computes `$stats` (active/total/pending/completed), `$activeProjects` (status `active`, 3, with milestones), `$recentApplications` (5), `$recommendedProblems` (open, deadline not passed, quota left, major/skills match, random 4), `$unreadNotifications`, `$upcomingMilestones` (non-completed, future, 5), `$profileCompletion` |
| View | 598 lines. Used only the student and the name from the controller; re-queried network, statistics, applications and projects in `@php`. Queried projects with status `in_progress`, a value that does not exist (stored values are `active`/`completed`), so **'Proyek Aktif' never rendered**. Never showed `$recommendedProblems` or `$upcomingMilestones`. Status words via `ucfirst()` ('Rejected', 'Pending'); mapped a non-existent `under_review` |
| Latent defect | `json_decode($student->skills, true)` on an attribute cast to `array` → `TypeError` → HTTP 500 for any student who saves skills (0 of 700 seeded students had skills, so it never showed) |
| Styling | Inline `<style>` (gradient hero with `dashboard-student2.jpeg`, gradient mesh, keyframes), Space Grotesk from Google Fonts, inline `style=` colours |
| Before metrics | `reports/phase7/before/metrics-before.json`: height 3,002 px (#86, 1440), 4,635 px (#86, 390); first action 222 px below `<main>` top at 1440, 207 px at 390 |
| Tests | 111 before this phase (Phase 6 report); one dashboard assertion (Phase 1 name test) |

Test accounts (seeded, identified by ID only): student #86 (2 pending + 1 rejected application, no project; the audit shape), #88 (no application, no project), #85 (1 active project with 2 upcoming milestones, 16 connections).

## 4. Implemented Features

| Block | What it shows | Data source |
|---|---|---|
| Header (`x-ui.page-header`) | h1 'Selamat datang, {display_name}', major · university, Telusuri Masalah (primary), Aplikasi Saya (secondary) | `User::display_name` (D-03), `$student` |
| Langkah berikutnya | Milestone rows (nearest first, kicker 'Tenggat terdekat', date + 'n hari lagi', milestone status, 'Buka proyek'); connection requests (avatar, '{nama} ingin terhubung', **Terima** / **Tolak** forms — same routes, POST, CSRF); '{n} lamaran menunggu keputusan instansi' → `/student/applications?status=pending`; 'Lengkapi profil Anda' with % and the missing fields → `/student/profile/edit`. Nothing to do → one quiet line | `$upcomingMilestones` + `$activeProjects`, `$friendRequests` (3), `$stats['pending_applications']`, `$profileCompletion` |
| Rekomendasi masalah | Up to 3 rows: title, institution, regency, province, 'Ditutup {tanggal}', tertiary 'Lihat' → problem detail; empty → one line; 'Lihat semua' → browse | `$recommendedProblems->take(3)` (controller logic unchanged) |
| Jaringan | One `x-ui.summary` line: 'x koneksi · y permintaan · z saran' (y, z hidden when 0; '10+' when the existing `suggestedFriends(10)` cap is reached) + 'Buka Jaringan' | `$network` |
| Proyek berjalan | Only when there are active projects: title → project, institution, `<progress>` + % | `$activeProjects`, `projects.progress_percentage` |
| Lamaran terbaru | `x-ui.table` (Masalah + instansi, Lokasi, Diajukan, Status) with `x-ui.status domain="application"`; title → application detail; empty → `x-ui.empty-state` 'Belum ada lamaran KKN' with the no-applications illustration and 'Telusuri masalah KKN' | `$recentApplications` |

Removed: photo hero, gradient mesh, profile block (duplicate of the navbar identity; its links remain reachable from the navbar and account menu: Profil, Wishlist, Jaringan, Repositori), network tiles, 4 suggestion cards with 'Hubungkan', 'Statistik Aktivitas' tiles and bars, the dead auto-hide-alert script (no `.alert` exists on the page; toasts are the shell's).

## 5. UI/UX Decisions

- **Work queue, not a dashboard of numbers** (audit S01, D3/L2; D-10 answer). Every row says what to do and has its own action; numbers appear inside sentences, each once.
- **One panel only.** The queue is the single bordered surface; recommendations and network are divided rows on the page ground; applications use the shared table. No shadow, no gradient, no card grid (guardrails 3, 4, 5).
- **Nearest deadline as the first queue row** with the kicker 'Tenggat terdekat', instead of repeating it in the header; repeating the date would have shown the same value twice (guardrail 2). The header keeps identity and the two primary routes.
- **Layout.** Below 1024 px one column; from 1024 px a 2 : 1 grid. DOM order header → queue → recommendations → network → projects → applications, so keyboard order follows the phase file ('header, queue, recommendations'); visually the rail sits beside the main column (grid rows `auto auto 1fr`).
- **Quiet actions.** One primary button per view (Telusuri Masalah); every row action is tertiary; 'Terima' is secondary because it changes data. Screen-reader text names the target ('Lihat Posyandu Digital Desa', 'Terima Farah Wibowo').
- **Status words** only from `StatusVocabulary` (`application`, `milestone`). Dates in Indonesian (`translatedFormat('j M Y')`), 'n hari lagi' never wraps.
- **No photograph, no chart** (guardrails 1, 2, 7; D-21). Catatan Lapangan is not used on this page (phase file E).
- **Copy** is Indonesian, from product facts only, DRAFT for the user (D-23 is open for logged-in pages): 'Langkah berikutnya', 'Tenggat terdekat', '{n} lamaran menunggu keputusan instansi', 'Instansi akan meninjau lamaran Anda. Statusnya berubah di Aplikasi Saya.', 'Lengkapi profil Anda', 'Profil {p}% lengkap. Belum diisi: …', '{nama} ingin terhubung', 'Rekomendasi masalah', 'Belum ada rekomendasi untuk Anda saat ini. Telusuri semua masalah yang sedang dibuka.', 'Ditutup {tanggal}', 'Jaringan', 'Buka Jaringan', 'Proyek berjalan', 'Lamaran terbaru', 'Belum ada lamaran KKN', 'Pilih masalah dari instansi yang sedang membuka pendaftaran, lalu ajukan lamaran pertama Anda.', 'Tidak ada yang perlu Anda tindak lanjuti saat ini.'
- ui-ux-pro-max guidance was consulted (empty states with a message and an action; badges for state, not as buttons); the project's own design system and guardrails take precedence over generic palettes, so no generated design system was applied or persisted.

## 6. Backend and Data Integration

- **Controller changes** (`app/Http/Controllers/Student/DashboardController.php`, student-only file):
  1. Skills fix (approved by the user, recorded under D-25): `$skills = is_array($student->skills) ? $student->skills : json_decode((string) $student->skills, true);`. Proven red → green by `test_skills_saved_as_array_no_longer_break_the_dashboard` (500 without the fix).
  2. The three network queries the old view ran in `@php` (`pendingFriendRequests()`, `friendsCount()`, `suggestedFriends(10)->count()`) moved to the controller unchanged and passed as `$network` and `$friendRequests` (first 3). `pendingFriendRequests()` now runs once instead of three times.
- **No new table, migration, route, query parameter or package.** Recommendation logic, stats and milestone queries are unchanged (phase file J). `$unreadNotifications` and two `$stats` keys are still computed but not shown (left as is to keep the diff small).
- **Forms kept:** `POST student.friends.accept/{friendship}` and `POST student.friends.reject/{friendship}` with `@csrf`; both still `back()` to the dashboard with the flash key (`test_accepting_a_request_from_the_dashboard_still_works`). The send-request form (`student.friends.send-request`) is no longer on the dashboard; the endpoint is unchanged and used by the network page.
- **Links kept:** browse problems, applications list, application detail, problem detail, projects list, project detail, profile edit, friends page. New link target: `/student/applications?status=pending` (existing `status` filter of `ApplicationController@index`).
- **Visible behaviour difference (presentation):** 'Proyek berjalan' now appears for students with active projects (64 seeded projects), because the view uses the controller's `active` query instead of the non-existent `in_progress`.
- **Data finding (not changed):** in the seeded database every one of the 77 open problems stores `required_majors = ["Semua Jurusan"]` (never matched by `whereJsonContains(major)`, none is `null`) and every one is at or over quota by the subquery (which counts all applications, rejected included). Result: **the recommendation list is empty for every student** (0 of 30 majors match). The view shows an honest empty line; see section 10.

## 7. Files Created and Modified

| File | Change |
|---|---|
| `resources/views/student/dashboard/index.blade.php` | Rewritten (598 → about 250 lines); no `<style>`, no `style=`, no CDN link |
| `app/Http/Controllers/Student/DashboardController.php` | Skills fix (1 line) + network data moved from the view (about 10 lines) |
| `resources/css/student-go/student-dashboard.css` | New: `.sg-dash*` classes, existing tokens only |
| `resources/css/app.css` | **Shared**: +1 import line after `auth.css` |
| `tests/Feature/Phase7/StudentDashboardTest.php` | New: 9 tests, 73 assertions |
| `student-go-docs/design-execution/DECISIONS_AND_OPEN_QUESTIONS.md` | **Shared**: ANSWER lines under D-10 (student only) and D-21, ADDITION under D-25 (skills fix) |
| `student-go-docs/design-execution/COMPONENT_INVENTORY.md` | **Shared**: new section 9 (student dashboard) |
| `student-go-docs/design-execution/ASSET_INVENTORY.md` | **Shared**: new section 21 (Phase 7 asset decisions) |
| `student-go-docs/design-execution/reports/phase7/**` | Evidence (screenshots, metrics, interaction checks, route snapshot) |
| `student-go-docs/design-execution/completion-reports/PHASE_07_COMPLETION_REPORT.md` | This report |
| `public/build/**` | Regenerated by `npm run build` (git-ignored; contains every session's current CSS) |

Not modified: `routes/*`, models, migrations, seeders, `.env`, shared components and layouts, navbar, footer, `components.css`, `tokens.css`, `PHASE_TRACKER.md`, any institution or company file.

## 8. Asset Reuse and Asset Changes

No file under `public/` was added, deleted, overwritten, moved or renamed; SHA-256 of `dashboard-student.jpg`, `dashboard-student2.jpeg`, `dashboard-student3.jpg` equal `baseline/asset-hashes.txt`. Decisions (also in `ASSET_INVENTORY.md` section 21):

| Asset | Decision |
|---|---|
| `public/dashboard-student2.jpeg` | Retain file; **Deprecate** as the dashboard hero. Still used by `/student/friends` (Phase 13) and, as a derivative, on About |
| `public/dashboard-student.jpg`, `public/dashboard-student3.jpg` | Retain, untouched (browse and profile heroes, Phases 8 and 12) |
| Student profile photos | Retain; shown only as the requester avatar in the student's own queue (same audience as before), initials when absent |
| `illustrations/empty-states/no-applications.svg` | Reuse (empty 'Lamaran terbaru') |
| Lucide sprite | Reuse; no icon added |
| Space Grotesk (Google Fonts) | Removed from this view |

## 9. Testing Results

### 9.1 Automated

| Check | Result |
|---|---|
| `php artisan test tests/Feature/Phase7` | **PASS** 9/9, 73 assertions |
| Skills fix proven red → green (old line restored temporarily, then the fix) | **PASS** (500 → 200) |
| `php artisan test` (full) | **PASS** 130/130, 1,565 assertions. An earlier run showed 128/130: the two failures were Session B's in-progress `Phase17/InstitutionDashboardTest`; excluding Phase 17 the suite was 123/123. The final run is fully green |
| `vendor/bin/pint --test tests/Feature/Phase7` | **PASS** (the controller was not reformatted: pre-existing style, CLAUDE.md §5) |
| `npm run build` | **PASS** — CSS 216.74 kB (gzip 34.47; includes Session B's `institution.css`), JS 19.82 kB unchanged |
| Route snapshot (`route:list --json` vs `reports/phase6/routes-after.json`) | **PASS** 203 = 203, identical (method, URI, name, action, middleware) |

### 9.2 Browser (Playwright outside the project, D-22 c; logged in with seeded accounts on the local DB)

| Check | Result |
|---|---|
| 6 widths (360, 390, 768, 1024, 1366, 1440) × accounts #86, #88, #85 | **PASS** 18/18: no page-level horizontal scroll, exactly one `<h1>`, `lang="id"`, no English status word, 0 links/buttons under 24 px, no console error, no 4xx/5xx response |
| h1 / first action position (below `<main>` top) | **PASS** h1 at 24 px everywhere; first action 60 px (≥ 768), 96 px (390), 148 px (360, name wraps) |
| Page height #86 | 3,002 → 1,057 px (1440); 4,635 → 1,864 px (390) |
| States not in seed data (recommendation rows, a pending request) | **PASS** via a read-only render (student #85 data + 3 real open problems outside the filter + 1 unsaved `Friend`), 360/768/1440: no overflow, 0 small targets (`after/S01_preview_states_*.png`). Also covered by automated tests |
| Keyboard order | **PASS** header → queue (Buka proyek ×2, Terima, Tolak, Lengkapi profil) → recommendations (Lihat semua, Lihat ×3) → Buka Jaringan → projects → applications → footer; focus ring visible on every stop |
| Contrast of new pairs | **PASS** kicker 7.84:1, tertiary on page 5.21:1, secondary 7.18:1, link 9.16:1, progress fill vs track 6.04:1 |
| Real form submit (accept a request) | **PASS** in the test suite (SQLite); NOT TESTED live, because the seeded DB has no pending friendship and none was created |
| `prefers-reduced-motion` emulation | **NOT TESTED** separately. The page adds no animation; only the shared 150 ms colour transitions, which Phase 3 neutralises globally |
| Student with 0 notifications | **NOT APPLICABLE** to the body: the dashboard no longer renders notifications (bell = shell, Phase 3) |
| Screen reader, real devices | **NOT TESTED** (D-24, Phase 27) |

Screenshots: before `reports/phase7/before/S01_before_s{86,88,85}_{width}.png` (18), after `reports/phase7/after/S01_after_s{86,88,85}_{width}.png` (18) + `S01_preview_states_{360,768,1440}.png`. Audit reference: S01.

### 9.3 Acceptance criteria (phase file L)

| # | Criterion | Evidence | Met |
|---|---|---|---|
| 1 | h1 and first action within the first 160 px; no photo hero | Max 148 px (360); `dashboard-student2.jpeg` absent (test) | Yes |
| 2 | No number twice; no zero tile; no trend chip | Each count once (pending, profile %, project %, network line); tiles/bars removed (test asserts 'Statistik Aktivitas', 'stat-bar', 'Total Aplikasi' absent); zero requests/suggestions hidden | Yes |
| 3 | Shared status vocabulary (no 'Rejected'/'Pending') | `x-ui.status`; tests + 18 captures: 0 English status words | Yes |
| 4 | No control below 4.5:1; 'Hubungkan' gone | 'Hubungkan' absent (test); all buttons are shared `sg-btn` variants (Phase 2/3 measured); new pairs ≥ 5.21:1 | Yes |
| 5 | Name and initials equal the single display name | h1 and navbar from `display_name`/`initials`; Phase 1 identity test + Phase 7 test 'Selamat datang, Farah Wibowo' | Yes |
| 6 | Empty account shows a clear next step | #88 captures; test `test_empty_account_gets_a_clear_next_step` | Yes |

## 10. Known Limitations

1. **Recommendations are always empty with the seeded data** (section 6). Not a view problem; the logic was kept as the phase file requires. Two questions for the user (suggest Phase 8 or a D-25-style approval): should `"Semua Jurusan"` count as 'all majors', and should the quota subquery count only pending/accepted applications?
2. **No pending friendships in the seed** (D-13), so the request rows were checked by tests and a read-only render, not live.
3. The empty-state component enlarges an icon inside its action button (shared `.sg-empty svg { width: 10rem }`); avoided here, not fixed (shared file).
4. Copy is DRAFT (D-23 open for logged-in pages).
5. The 'Tenggat terdekat' row covers milestones of the 3 most recent active projects only (existing controller limit).
6. 'Ditutup {tanggal}' and the milestone dates use the server date; no timezone handling was added.
7. Screen reader, real devices and reduced-motion emulation are not tested (see 9.2).

## 11. Shared File Changes and Conflict Risks

| Shared file | Change | Risk / note for integration |
|---|---|---|
| `resources/css/app.css` | +1 line `@import './student-go/student-dashboard.css';` after `auth.css` | Session B added `institution.css` directly below it during this session; both lines coexist. Keep both and the order (before `legacy-overrides.css`) |
| `DECISIONS_AND_OPEN_QUESTIONS.md` | D-10 ANSWER scoped to the student dashboard; D-21 ANSWER (b) with "no chart in Phase 7"; D-25 ADDITION (skills fix) | Sessions B/C may add their own D-10 lines (institution, company) and may also record D-21; reconcile so D-21 has one answer |
| `COMPONENT_INVENTORY.md` | Section 9 appended | B/C may also append a "section 9"; renumber at integration |
| `ASSET_INVENTORY.md` | Section 21 appended | Same numbering risk |
| `public/build/` | Regenerated | Each session's build overwrites it with the then-current CSS of all sessions; rebuild once after integration |
| `PHASE_TRACKER.md` | **Not edited** | Set Phase 7 to COMPLETE at integration: "2026-10-31, feat/students @ e824b9d (no commit). All 6 AC met. Work-queue dashboard; skills 500 fixed (approved); tests 130/130; routes 203=203; recommendations empty in seed data (data finding)." |

Shared layouts, navbar, footer, components, tokens, auth logic, routes, models and migrations were not touched. No Institution or Company file was opened for editing.

## 12. Handover Notes for Phase 8

- **Recommendation data** (section 10.1): Phase 8 (discovery) owns problem matching and listing; decide the `"Semua Jurusan"` and quota semantics there (needs user approval if behaviour changes). Once fixed, the dashboard rows appear without view changes.
- `public/dashboard-student.jpg` is the browse-problems hero; Phase 8 decides its usage (guardrail 1: no hero on task pages).
- Reuse from this phase: row anatomy of `.sg-dash-reco__item` (title, institution, `map-pin` region, deadline, tertiary 'Lihat' with hidden title), the Indonesian date format `->locale('id')->translatedFormat('j M Y')`, and the read-only render technique for states missing from seed data (render the view in tinker with `URL::forceRootUrl('http://127.0.0.1:8000')`; the app forces https, so rewrite asset URLs to http before loading).
- Avoid `icon` on a button inside `x-ui.empty-state`'s action slot until the shared CSS is fixed.
- The roadmap now allows (dependencies permitting): Phases 8, 9, 10, 11, 12, 13, 14, 15 and 16 per the tracker. Only on explicit request.
