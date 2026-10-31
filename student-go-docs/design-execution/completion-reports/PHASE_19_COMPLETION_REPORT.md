# PHASE 19 — Institution Review & Monitoring: completion report

| Field | Value |
|---|---|
| Phase | 19 — Institution Review & Monitoring (`phases/PHASE_19_INSTITUTION_REVIEW_MONITORING.md`) |
| Session | Session B (institution workspace), in parallel with Session A (Phase 9) and Session C (Phase 23), same working copy |
| Date | 2026-10-31 |
| Branch / commit | Working copy on `docs/project-documentation` @ `c1b1621` (set by someone else before this phase). No branch, commit, merge, stash or reset by this session (D-27). Session B files remain untracked |
| Recommended status | **COMPLETE**: all 6 acceptance criteria met (section 11.1). `PHASE_TRACKER.md` **not** edited (parallel-session instruction); proposed row in section 18 |
| Prerequisites | Phase 3 (hard) COMPLETE; Phase 1 (soft) COMPLETE; Phase 18 (soft) done, report recommends COMPLETE (not yet in this branch's tracker). Suite at the start of this phase: 185/185 (Phase 18 end) |
| Gates | **D-02** answered (`StatusVocabulary`). **D-25** answered: `/institution/reviews/create` returns 404 since Phase 1, and the real route is `/institution/reviews/create/{projectId}`. **D-13** OPEN: the seed data is enough, and missing states are listed BLOCKED (section 14). **D-26** OPEN: keyboard queue navigation approved for this phase only (recorded as an ANSWER under D-26) |
| Behaviour changes | Seven decisions, asked through two questions with recommended options; the user chose the recommended option every time. Recorded in `DECISIONS_AND_OPEN_QUESTIONS.md`, 'Note on Phase 19 behaviour fixes' |
| Evidence | `reports/phase19/`: `before/` (72 captures + metrics), `after/` (84 captures + metrics), `checks/` (interaction, live decisions with DB verification and cleanup, a11y, contrast), `tools/` |

## 1. Executive summary

The three institution review areas are rebuilt on the shared shell:

- **Applications (I03):** split view from 1024 px. The queue (name, campus, major, problem, date, status) sits next to the selected application (motivation, cover letter, proposal, visible profile facts, decision bar). Below 1024 px the queue links to the detail page, which has a back link that keeps the filters. Decisions use the same endpoints in dialogs: primary 'Terima', outline-danger 'Tolak' with both required fields. Arrow, Home and End keys navigate the queue.
- **Projects (I04):** a seven-column table with a stage meter (one segment per milestone) and the last report (type, period, status). The detail page shows a milestone timeline and a report list.
- **Reviews (I05):** a divided list (student, project, rating 'n/5', comment, date), a detail page, and one shared create/edit form. The review form now saves (fix approved by the user).

Approved fixes:

- review saving;
- in-app notifications on accept and reject;
- quota counted from accepted applications;
- the dashboard's 'Menunggu ulasan' based on the reviews table;
- milestone-based progress.

D-04 (already answered) is now applied to the application detail: no student email or phone.

Tests:

- Phase 19: 13/13.
- Full suite: **224/224** (2,442 assertions). One transient failure from a shared compiled-view race passed on re-run (section 11.3).

Live checks:

- 84 captures without overflow (4 overflows before);
- queue keyboard, dialog and narrow-screen flows pass;
- accept and reject on throwaway rows verified in the database (project created, counter, one notification each), then removed.

## 2. Initial condition (before, `reports/phase19/before/`)

- **Applications:** a card list with English 'Pending'. 'Terima' on the list used `confirm()`. The detail page printed the student's **email and phone** (against D-04). Its 'Tolak' button posted **without** `feedback` and `rejection_reason`, so it always failed validation. The working reject form was on `/{id}/review`. 18 px overflow at 360 px.
- **Projects:** cards with a bar from the stored `progress_percentage`, while the detail page computed progress from milestones (62/259 projects disagree). The status filter offered `planning` and `review`, which never exist. The `sort` values were ignored by the controller. Pagination dropped the filters (no `withQueryString`). 18 px overflow at 360 px.
- **Reviews:** a card list with yellow stars, a gradient header and hot-linked `ui-avatars.com` images. The detail page overflowed by 103 / 73 px at 360 / 390 px. The create form had no `project_id` and `store()` called the wrong service method, so a review could never be saved. `/institution/reviews/create` (the audit's guessed URL) already returned 404 since Phase 1.

## 3. Existing implementation audit (behaviour documented before changes)

| Area | Finding |
|---|---|
| Routes | `applications`: index, `{id}`, `{id}/download-proposal`, `{id}/review` (GET), `{id}/accept`, `{id}/reject` (POST). `projects`: index, `{id}`, `{id}/manage` + milestone/status/report endpoints. `reviews`: index, `create/{projectId}`, store (POST), `{id}`, `{id}/edit`, update (PUT). Middleware `auth` + `check.user.type:institution`; numeric `{id}` (Phase 1) |
| Ownership | Every application query is scoped by `problem.institution_id`, project queries by `institution_id`, review queries by `project.institution_id` → 404 for other institutions (verified, section 10) |
| Accept | Accepts pending/reviewed only. Slot check, then `feedback` (opt., max 1000) and `notes` (opt., max 500). Sets `accepted` + `reviewed_at`, creates the project (`active`), increments `problems.accepted_students`. Redirects to show |
| Reject | Pending/reviewed only. `feedback` **required** (1000) and `rejection_reason` **required** (500). Stores status, `reviewed_at`, `feedback`. `rejection_reason` is not fillable and has no column, so it is discarded |
| Notifications | None before this phase (`NotificationService` methods existed, never called). No email in either action |
| Projects | Progress: the model rule (`Project::updateProgress`) and the old detail page use completed/total milestones. Reports: types weekly/monthly/final, statuses pending/reviewed/approved (+ revision_needed in the vocabulary) |
| Reviews | Type `institution_to_student`; reviewee = the student's user. Create only for completed, unreviewed projects. Edit for 30 days |
| Unused | `bulkAction` and `cancel` exist in the controller but have no route (untouched) |

## 4. Applicant review implementation

- `applications/index.blade.php`:
  - page header 'Aplikasi'; summary from real counts;
  - filter bar with the same `search`, `status`, `problem_id`, `sort`;
  - `.sg-inst-review` grid: queue + detail panels (one per row on the page, max 10, server-rendered; the inactive ones are hidden with `x-show`);
  - empty states 'Belum ada lamaran' (`no-applications.svg`) and 'Tidak ada lamaran yang cocok'.
- `sgReviewQueue` (inline Alpine, project convention):
  - from 1024 px a click or Enter selects in place and moves focus to the applicant's name in the panel;
  - Arrow Up/Down, Home and End move the selection and focus in the queue;
  - `aria-current="true"` marks the selected row, the URL hash `#lamaran-{id}` is updated (a hash on load selects that row), and a polite live region announces 'Detail lamaran … ditampilkan.';
  - below 1024 px, and without JS, the rows are plain links to `/applications/{id}`.
- `applications/components/detail.blade.php` (panel / page / inline):
  - header (panel only);
  - decision bar;
  - problem link, motivation, cover letter, proposal ('Buka proposal' via the existing download route, or 'Tidak ada proposal yang diunggah');
  - stored feedback and internal notes;
  - rail with campus, major, semester, sent and decided dates, and a link to the public profile;
  - **no email or phone** (D-04).
- `show.blade.php`: page header with the student name, and a back link that keeps the list filters when the user came from the list. `review.blade.php`: same detail with both forms inline (the GET route is kept).

## 5. Decision workflow

| Item | Baseline | After |
|---|---|---|
| Accept | `POST institution.applications.accept`, `feedback`, `notes` (optional) | Same endpoint, verb and fields; dialog 'Terima lamaran' (primary) |
| Reject | `POST institution.applications.reject`, `feedback` + `rejection_reason` required | Same; dialog 'Tolak lamaran' (danger), both `required` + `maxlength` matching the server rules; help text states that the reason is not stored |
| Status transitions | pending/reviewed → accepted / rejected | Unchanged; decided applications show 'Keputusan: Diterima/Ditolak pada …' and no buttons |
| Confirmation | `confirm()` | Native `<dialog>` (`x-ui.modal`): focus enters the dialog, Esc closes it and focus returns to the trigger. The dialog is the confirmation step |
| Side effects | Project created on accept; counter increment | Same, plus approved in-app notifications after the commit and the quota counted from accepted applications |

## 6. Project monitoring implementation

- `projects/index.blade.php`:
  - columns Proyek · Mahasiswa · Status · Tahap · Laporan terakhir · Jadwal · Aksi (Detail, Kelola);
  - filters `search`, `status`, `problem_id` (status options from the vocabulary: Berjalan, Ditunda, Selesai, Dibatalkan);
  - pagination appends the filters;
  - 'Melewati tanggal selesai' for active projects past `end_date`;
  - the last report is the one with the latest period end ('s.d. <date>' + status), with 'N laporan menunggu tinjauan' when other reports wait.
- `projects/components/stage-meter.blade.php`: one segment per milestone, filled = completed, with the text 'x dari y tahap' (approved source; the stored percentage is not shown).
- `projects/show.blade.php`:
  - milestone timeline (status, 'Wajib laporan' flag from Phase 17, target and completed dates);
  - report list (type, period, summary, institution feedback, document link, status);
  - rail with status, stage, report counts, problem, schedule and student;
  - actions 'Kelola proyek' and, for completed projects, 'Tulis ulasan' or 'Lihat ulasan'.
- `projects/manage.blade.php` is unchanged (Phase 17 legacy styling; milestone and report management stays there).

## 7. Student reviews implementation

- `reviews/index.blade.php`:
  - summary of review count and average rating (from the controller's real statistics);
  - filters `search`, `rating`, `sort`;
  - divided list: avatar initials, name (link to detail), project · campus, a three-line comment excerpt, 'Ditulis <date>', 'Ubah' within 30 days, and rating 'n/5' with a neutral meter;
  - empty state that explains when reviews can be written, linking to the completed projects.
- `reviews/show.blade.php`: the text blocks (Ulasan, Kekuatan, Saran pengembangan) and a rail. 'Ubah ulasan' follows the controller's 30-day rule.
- `reviews/components/form.blade.php` (create + edit):
  - `rating` is now a radio group 1–5 with labels (before: a hidden input set by star buttons; same name and values);
  - `review` (required, 1000), `strengths`, `improvements` (500);
  - `would_collaborate_again` on create, the same checkbox as before.

## 8. Review create route verification

| URL | Result |
|---|---|
| `/institution/reviews/create` (audit guess) | **404** (Phase 1 numeric constraint), no QueryException. Verified by test and live capture `after/I05c_*` |
| `/institution/reviews/create/{projectId}` completed, unreviewed | Form renders (test). **No such project in the seed data**, so the live view is BLOCKED (section 14) |
| `/institution/reviews/create/{projectId}` already reviewed / not completed | Redirect to the project with the controller's message (live: project 117; test: active project) |
| `POST /institution/reviews` | Saves an `institution_to_student` review and sets `projects.reviewed_at` (test). Before this phase it always failed |

## 9. Files created and modified

| File | Change |
|---|---|
| `app/Http/Controllers/Institution/ApplicationReviewController.php` | Quota from accepted applications; notifications after commit (`notifyStudent()`, failures logged) |
| `app/Http/Controllers/Institution/ReviewController.php` | `store()` calls `ReviewService::createReview()` |
| `app/Http/Controllers/Institution/ProjectManagementController.php` | `index()` eager-loads `reports`; `show()` passes `$institutionReview` |
| `app/Http/Controllers/Institution/DashboardController.php` | 'Menunggu ulasan' from the reviews table (two queries) |
| `resources/views/institution/applications/{index,show,review}.blade.php` | Rewritten |
| `resources/views/institution/applications/components/{detail,decision-forms,accept-form,reject-form}.blade.php` | New |
| `resources/views/institution/projects/{index,show}.blade.php` | Rewritten |
| `resources/views/institution/projects/components/stage-meter.blade.php` | New |
| `resources/views/institution/reviews/{index,show,create,edit}.blade.php` | Rewritten |
| `resources/views/institution/reviews/components/{form,rating}.blade.php` | New |
| `resources/css/student-go/institution.css` | Phase 19 section appended (file 231 lines) |
| `tests/Feature/Phase19/InstitutionReviewMonitoringTest.php` | New, 13 tests |
| `ASSET_INVENTORY.md` §29; `DECISIONS_AND_OPEN_QUESTIONS.md` (D-13 NOTE, D-26 ANSWER for Phase 19, 'Note on Phase 19 behaviour fixes') | Docs |
| `reports/phase19/**` | Evidence and tools |

## 10. Backend, data integration, notification and ownership verification

- Numbers on these pages come from queries: the controller statistics, milestone counts, report counts and statuses, review statistics. No invented values; zero-count statuses are hidden from the summaries.
- **Live decisions** (throwaway rows, `checks/live-decisions-db-verify.json`; ids in `checks/throwaway-ids.json`):
  - setup: problem #79 for institution #64, applications 360 and 361 from two seeded students;
  - accepted 360 in the dialog: status `accepted`, feedback and notes stored, project #260 `active` for #64, counter 1, one `application_accepted` notification for the student;
  - rejected 361: status `rejected`, feedback stored, one `application_rejected` notification whose message contains the feedback;
  - deltas: notifications +2, projects +1;
  - cleanup (`checks/throwaway-cleanup.json`): notifications 58 → 58, projects 259 → 259, problem removed;
  - no email was sent (neither action sends mail).
- **Ownership:** another institution gets 404 on show, review, accept and reject, and no notification is created (test `test_applications_of_another_institution_are_not_reachable`). Guests are redirected to login, students and companies are redirected (test).
- **D-04:** the application detail contains neither the student's email nor phone (test asserts both values are absent).

## 11. Testing results

### 11.1 Acceptance criteria (section L)

| Criterion | Result | Evidence |
|---|---|---|
| Applicants screen shows queue and detail together at ≥ 1024 px and sequentially below | **PASS** | `after/I03_after_populated_{1024,1366,1440}.png` (split), `_{360,390,768}` (list); `checks/interaction-checks-partA.json` `narrow` (detail column `display:none`, row → `/applications/269`, back → `?status=pending`) |
| Decision actions post to the same endpoints as the baseline | **PASS** | `test_decision_forms_post_to_the_baseline_endpoints_with_the_same_fields`; input diff on `/review`: identical (`feedback`×2, `notes`, `rejection_reason`); live decisions |
| Projects table shows a stage meter and last report | **PASS** | `test_projects_table_shows_stage_meter_from_milestones_and_last_report`; `after/I04_*` |
| `/institution/reviews/create` no longer renders a QueryException | **PASS** | 404 at 6 widths (`after/I05c_*`), test |
| Statuses use the shared vocabulary | **PASS** | `x-ui.status` for application, project, milestone and report statuses; tests assert 'Menunggu', 'Ditolak', 'Berjalan' and no 'Pending' |
| Populated states verified or listed BLOCKED | **PASS** | Verified with seed data (section 14 lists the BLOCKED states) |

### 11.2 Requested test list

| # | Test | Result |
|---|---|---|
| 1 | Applicant queue | PASS (test + live) |
| 2 | Filter by problem and status | PASS (test) |
| 3 | Applicant selection | PASS (live: click, Enter, arrows, Home/End, hash, live region) |
| 4 | Application detail | PASS (test + live) |
| 5 | Accept | PASS (test + live throwaway) |
| 6 | Reject | PASS (test + live throwaway) |
| 7 | Required reason validation | PASS (server: test, both fields; browser: empty submit blocked, dialog stays open) |
| 8 | Notification side effects | PASS: one notification each, none on refused or foreign decisions (test + live) |
| 9 | Applicant ownership | PASS (test) |
| 10 | Project monitoring table | PASS |
| 11 | Stage meter from actual data | PASS: milestones, stored 41% not shown (test) |
| 12 | Last report date | PASS: '28 Feb 2026' period end (test), seed data live |
| 13 | Reviews list | PASS |
| 14 | Reviews create route behaviour | PASS (404 bare URL; redirect when reviewed or active; form + store by test). Live form view BLOCKED (no data) |
| 15 | Institution authorisation | PASS (test) |
| 16 | Empty state | PASS (#64 live at 6 widths; tests) |
| 17 | Split view desktop | PASS |
| 18 | Sequential navigation below 1024 px | PASS |
| 19 | Responsive 360–1440 | PASS: 84 captures |
| 20 | No horizontal overflow | PASS: 0 everywhere (before: I03 / I04 18 px at 360, review detail 103 / 73 px) |

### 11.3 Standard checks

- **Keyboard:** one `<h1>` and `lang="id"` on 8 pages; visible focus on the first 40 tab stops of each page (`checks/a11y-checks.json`); dialogs return focus on Esc; no keyboard trap observed.
- **Contrast:** new pairs measured (`checks/contrast-new-pairs.txt`). Text on the selected row ≥ 5.21:1, meter segments ≥ 6.03:1; the empty track is decorative because the value is written as text.
- **Reduced motion:** only colour/background transitions, covered by the global reduced-motion rule.
- **Console/network:** no JS errors; the only console error is the intended 404 page. No hot-linked image remains (`ui-avatars.com` and `asset('storage/…')` removed).
- **Assets:** `public/` hash check 37/38 identical, 0 changed (`karsa-logo.png` deleted by the user before Phase 4).
- **Suite:** 185 → **224/224** (2,442 assertions; includes other sessions' tests). One run had a transient `ExampleTest` 500 (`rename()` in the shared `storage/framework/views` right after `view:clear` while other sessions were compiling views); it passed alone and in a full re-run.

## 12. Before/after screenshots

| ID | Before (`before/`) | After (`after/`) |
|---|---|---|
| I03 Applications | `I03_before_{populated,empty}_<w>.png`, detail `I03d_*`, review `I03r_*` | `I03_after_{populated,empty}_<w>.png`, `I03d_*`, `I03r_*`, decided `I03a_*`; dialogs `checks/I03_accept_dialog_1366.png`, `checks/I03_reject_dialog_1366.png`; narrow detail `checks/I03d_narrow_detail_768.png`; after accept `checks/I03d_after_accept_1366.png` |
| I04 Projects | `I04_before_*`, detail `I04d_*` | `I04_after_*`, `I04d_*` (active), `I04c_*` (completed) |
| I05 Reviews | `I05_before_*`, `I05d_*`, `I05e_*`, `I05c_*` (404) | `I05_after_*`, `I05d_*`, `I05e_*`, `I05c_*` (404) |

Widths: 360, 390, 768, 1024, 1366 and 1440. The queue column is a sticky scroll area from 1024 px, so full-page captures show only part of it.

## 13. Known limitations

- `rejection_reason` is required but not stored (no column; user decision: keep it and label it honestly). Storing it needs a migration or another column choice.
- `problems.accepted_students` stays out of sync in the seed. The institution quota no longer depends on it, but the student-side display still does (see 'Note on Phase 9').
- Stored `progress_percentage` differs from the milestones for 62/259 projects. It is not shown here, and is still written by `updateStatus` (100 on completion) and `updateProgress`.
- `would_collaborate_again` is validated but has no column (unchanged).
- The accept dialog's first focus is the close button (native `<dialog>` default), not a form field.
- On the index, after a server-side validation error the old input fills every dialog's fields (shared names); browser `required`/`maxlength` make that case unlikely.
- `projects/manage.blade.php` keeps the Phase 17 legacy styling (management screen, not designed here).
- The projects `sort` select was removed because the controller ignored it; the parameter is still accepted.

## 14. BLOCKED data states (no data; no seeder run, no fake UI)

| State | Why | Covered by |
|---|---|---|
| Application with an uploaded proposal | 0 rows have `proposal_content` | Feature test (link and file name) |
| Review create form for a completed, unreviewed project | All 195 completed projects already have a review | Feature tests (form, store, redirects) |
| Projects `on_hold` / `cancelled` | Not in the seed | Status vocabulary; filter no-match tested |
| Report `revision_needed` | Not in the seed | Vocabulary only |

## 15. Outstanding decisions

1. Storing the rejection reason (migration `applications.rejection_reason`, or store it in `institution_notes`); currently kept as is.
2. Resynchronising `accepted_students` and `progress_percentage` from the source data (data change; needs approval).
3. D-13 and D-26 stay OPEN outside this phase.

## 16. Shared file impact

- Session B's own files only: the institution controllers, institution views, `institution.css` (Phase 19 section) and Phase 19 tests. `DashboardController` is Session B's Phase 17 file.
- No change to routes, middleware, models, services, `NotificationService`, `ReviewService`, `StatusVocabulary`, `<x-ui.*>`, tokens, layouts, navbar or footer, or student/company files.
- Shared docs (`ASSET_INVENTORY.md` §29, `DECISIONS_AND_OPEN_QUESTIONS.md`) were appended.
- Notifications created by accept and reject are read by the student notification bell (Phase 16 area). The type and content come from the existing service methods.

## 17. Handover notes for Phase 20 (Institution Profile & Verification)

- The dashboard verification banner (Phase 17, D-09 for that phase only) is still the entry point to verification. Phase 20 owns D-09.
- Institution #64 (pending verification, no problems) is the empty account. Its problem/application/notification sequences advanced during the throwaway test (ids 79, 360–361, 260, 59–60 consumed and removed).
- Reusable: `.sg-inst-detail` (main + rail), `.sg-inst-timeline`, `x-ui.modal` decision pattern, the `reports/phase19/tools` scripts (`SG_PW`, `SG_ACCOUNTS`, `SG_PAGES` without a leading slash in Git Bash; `set_default_timeout(60000)` because the shared dev server is slow).

## 18. Proposed tracker row (for the integration owner)

| 19 | Institution Review & Monitoring | COMPLETE | 3 | 1, 18 | D-02, D-13, D-25, D-26 | 2026-10-31 | — (no commit) | 6/6 criteria; suite 224/224; live accept/reject on throwaway rows (removed); report `completion-reports/PHASE_19_COMPLETION_REPORT.md` |
