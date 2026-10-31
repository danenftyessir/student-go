# PHASE TRACKER

The only place where phase status is recorded. Update only the row of the phase you executed. Created 2026-10-30 at planning time; every phase is NOT_STARTED because nothing has been executed.

## Valid statuses

| Status | Meaning |
|---|---|
| NOT_STARTED | No work done. |
| IN_PROGRESS | An agent is executing the phase now. Set at step 7 of the protocol; never left in this state when the session ends without a note. |
| COMPLETE | Every acceptance criterion in section L is met with evidence. |
| PARTIAL | Some work is done; list which acceptance criteria failed or were skipped and why. |
| BLOCKED | Cannot proceed; name what unblocks it (a decision, data, access, an earlier phase). |

## Phases

| Phase | Title | Status | Hard deps (must be COMPLETE) | Soft deps | Gating decisions | Last update | Branch / commit | Notes |
|---|---|---|---|---|---|---|---|---|
| 0 | Readiness & Baseline | COMPLETE | — | — | D-12, D-16, D-22, D-27, D-28, D-32 | 2026-10-30 | feat/students @ e824b9d (no commit) | All 9 AC met. Stage One 36 VERIFIED / 1 BLOCKED (S1-35 photo rights, deferred by user). New Supabase DB migrated (60/60) and seeded (fallback regions: 5 provinces). Baseline: 202 routes, 78 forms, tests 2/2, 38 asset hashes unchanged, 55/56 screens (S21 needs a state-changing GET). Report: `reports/PHASE_00_REPORT.md`. |
| 1 | Critical Fixes & Data Integrity | COMPLETE | 0 | — | D-03, D-04, D-05, D-06, D-25, D-31 | 2026-10-30 | feat/students @ e824b9d (no commit) | All 10 AC met. J1-J5, J13 fixed with 36 regression tests (red 27/34 before, 38/38 suite after) and live checks (44/44 read-only, 12/12 state). Paging: browse 77/77, repository 214/214, friends 699/699, talents 259/259. DB: 1 additive migration (9 nullable `companies` columns) + `users.name` synced for 700 students (CSV backup). Route diff: +1 approved route (`student.chatbot.store`). Open: D-36 talent CSV exports student email. Report: `reports/PHASE_01_REPORT.md`. |
| 2 | Brand Identity & Design System | COMPLETE | 0 | 1 | D-01, D-16, D-17, D-18, D-19, D-20, D-29, D-32, D-35 | 2026-10-30 | feat/students @ e824b9d (no commit) | All 7 AC met. Package copied additively (166 public files, C2PA stripped on copies; 38 existing hashes unchanged). CSS wired staged (D-29): fonts/tokens/components imported, `legacy-overrides.css` waits for Phase 3. Style digests 30/30 identical; compiled CSS 0 existing declarations changed; build 129.31→144.25 kB; tests 45/45; contrast 44 pairs, 0 fail; certificate pixel-identical. 60 photos copied, preliminary review 37/14/9 held (D-19 confirm). Report: `reports/PHASE_02_REPORT.md`. |
| 3 | Shared Shell & Components | COMPLETE | 2 | 1 | D-01, D-02, D-17, D-20, D-25, D-29, D-32 | 2026-10-30 | feat/students @ e824b9d (no commit) | All 9 AC met. D-02 (a) + copy deck and D-20 (a) answered. Shared head/layouts/navbar/bell/footer, 37 `<x-ui.*>` components, `StatusVocabulary`, `legacy-overrides.css` active, Boxicons/FA/Google Fonts removed from layouts. Routes 203=203, page forms identical, tests 63/63, browser checks 144/144, 768px navbar overflow gone, `/forgot-password` 500→200, footer 11.12:1. Docs: `COMPONENT_INVENTORY.md`. Report: `reports/PHASE_03_REPORT.md`. |
| 4 | Homepage | COMPLETE | 3 | 1 | D-01, D-19, D-20, D-23, D-30, D-33, D-34 | 2026-10-31 | feat/students @ e824b9d (no commit) | Closed at the user's instruction ('anggap selesai') after verification of the code as it stands. Revision + hero video (AD-11, recorded) kept. All 9 AC met, AC 4 and AC 9 as amended by AD-11. Tests 80/80 (936), routes 203=203, 0 overflow at 6 widths, browser checks 14/14, 37/38 Phase 0 hashes (`karsa-logo.png` deleted by the user). Known deviation: no pause control on the looping video (WCAG 2.2.2). Report: `reports/PHASE_04_REPORT.md`. |
| 5 | About, Contact, Public Institution Profile | COMPLETE | 3 | 1, 4 | D-19, D-23, D-26, D-12, D-30, D-31, D-33, D-34 | 2026-10-31 | feat/students @ e824b9d (no commit) | All 7 AC met (AC 7 with the user's caption-only rule). About = scrolling article (carousel, unsourced figures and Karsa narrative removed), Contact = one screen (548 px at 1440×900) with the 3 supplied team emails + FAQ, institution profile = organisation page (full 76-char name, summary without zeros, honest empty state, completed work grouped without student names). RN-03…RN-12 done (probe 13 → 0 hits). Tests 80 → 86 (1,072), routes 203 = 203, 30/30 captures without overflow or broken image, browser checks 36/36, About images 5.53 MB → 226 KB. Copy DRAFT (D-23 b); D-37 narrative not published; D-26 form not built. Revised after the user's review (About photos differ from the homepage, transparent uniform team photos, new header, vertical timeline) and closed at the user's instruction; tests 86/86 (1,082), checks 36/36. Report: `reports/PHASE_05_REPORT.md` §11. |
| 6 | Auth | COMPLETE | 3 | 1 | D-01, D-02, D-15, D-23, D-30 | 2026-10-31 | feat/students @ e824b9d (no commit) | All 8 AC met. One auth template (`layouts/auth.blade.php`) + `x-ui.stepper` for login, chooser, 3 registrations (company now a 4-step wizard), forgot/reset, verify; no stats/photo/old logo; `remember` added (approved); same routes (203=203) and form fields; J30 regency select (abort + aria-live); new `x-ui.password`, `auth.js`, `auth.css`. Backend unchanged. Tests 86 → 114 (28 new); browser 53/53; live student/company registration + reset on log mailer, throwaway accounts deleted. D-40 answered: 'Karsa' → 'Student Go' in auth emails and auth messages. Open: forgot-password enumeration kept by decision; `APP_URL`/`MAIL_FROM_NAME`/default reset email depend on `.env`. Report: `completion-reports/PHASE_06_COMPLETION_REPORT.md`. |
| 7 | Student Dashboard | NOT_STARTED | 1, 3 | — | D-03, D-10, D-21, D-12 | — | — | — |
| 8 | Student Project Discovery | NOT_STARTED | 1, 3 | 2 | D-02, D-05, D-14, D-12 | — | — | — |
| 9 | Project Detail | NOT_STARTED | 1, 3 | 8 | D-02, D-14, D-19, D-30 | — | — | — |
| 10 | Applications | NOT_STARTED | 1, 3 | 9 | D-02, D-21, D-12 | — | — | — |
| 11 | Active Projects & Wishlist | NOT_STARTED | 3 | 10, 8 | D-13, D-21 | — | — | — |
| 12 | Profile & Portfolio | NOT_STARTED | 1, 3 | — | D-03, D-04, D-19, D-23, D-31, D-32 | — | — | — |
| 13 | Friends | NOT_STARTED | 1, 3 | 12 | D-02, D-03, D-04, D-31 | — | — | — |
| 14 | Repository | NOT_STARTED | 1, 3 | 2 | D-02, D-05, D-26 | — | — | — |
| 15 | Jobs | NOT_STARTED | 3 | 8 | D-07, D-13, D-02 | — | — | — |
| 16 | KARA Chatbot & Notifications | NOT_STARTED | 1, 3 | — | D-01, D-25 | — | — | — |
| 17 | Institution Dashboard | NOT_STARTED | 3 | 1 | D-09, D-10, D-13, D-21 | — | — | — |
| 18 | Institution Project Management | NOT_STARTED | 3 | 1, 17 | D-02, D-15, D-26, D-13 | — | — | — |
| 19 | Institution Review & Monitoring | NOT_STARTED | 3 | 1, 18 | D-02, D-13, D-25, D-26 | — | — | — |
| 20 | Institution Profile & Verification | NOT_STARTED | 3 | 1, 5 | D-06, D-09, D-13 | — | — | — |
| 21 | Company Dashboard | NOT_STARTED | 3 | 1 | D-02, D-10, D-13, D-21 | — | — | — |
| 22 | Company Talent Discovery | NOT_STARTED | 1, 3 | 13 | D-02, D-04, D-05, D-08, D-26, D-31 | — | — | — |
| 23 | Company Job Management | NOT_STARTED | 3 | 1, 22 | D-02, D-07, D-13, D-26 | — | — | — |
| 24 | Applicant Pipeline | NOT_STARTED | 3, 23 | 1 | D-02, D-13, D-26 | — | — | — |
| 25 | Company Profile | NOT_STARTED | 3 | 1, 20 | D-06 | — | — | — |
| 26 | Error Pages | NOT_STARTED | 1, 2, 3 | — | D-01, D-11, D-25 | — | — | — |
| 27 | Responsiveness & Accessibility | NOT_STARTED | 4-26 (all) | — | D-24, D-22, D-12, D-13 | — | — | — |
| 28 | Final QA | NOT_STARTED | 27 | — | D-12, D-13, D-22, D-24 | — | — | — |

## Rules

- A phase may start only when every hard dependency is COMPLETE. Phase 0 has none and must be executed first; its Stage One block also validates this tracker against the repository.
- Gating decisions are IDs from `DECISIONS_AND_OPEN_QUESTIONS.md`. An OPEN decision pauses the tasks that depend on it; it does not pause the whole phase unless the phase file says so.
- Phase 27 depends on every page phase (4-26). Phase 28 depends on 27.
- Dates use ISO format (YYYY-MM-DD). Add a commit hash only if the user asked for a commit.
- Never mark COMPLETE on the basis of 'looks fine'. The report must list the evidence for each acceptance criterion.
- Never change another phase's row. If a finding invalidates another phase doc, say so in the report and wait for the user.

## Update log

| Date | Phase | Change | By |
|---|---|---|---|
| 2026-10-30 | all | Tracker created; all 29 phases NOT_STARTED. No implementation, design or phase execution has taken place. | discovery and planning session |
| 2026-10-30 | all | Asset reconciliation (AD-09) added: `ASSET_INVENTORY.md` created; section H of all 29 phase files now lists assets from the existing `public/` folder and the custom package; documentation root changed to `student-go-docs/design-execution/` (D-28); decisions D-28 to D-35 added. All phases still NOT_STARTED. No asset was deleted, overwritten, moved, renamed, copied, downloaded or generated; the repository was not touched. | asset correction session |
| 2026-10-30 | 0 | Phase 0 executed: NOT_STARTED → IN_PROGRESS → PARTIAL. Docs copied to the repository root (D-28). Stage One completed; baseline written to `baseline/`; no application file, `.env` or `public/` asset changed. Unblock: D-12 (Postgres credentials of the new Supabase project + approval to migrate/seed), then re-run task 0.11. | Claude Code (Phase 0 session) |
| 2026-10-30 | 0 | PARTIAL → COMPLETE: user supplied the new Supabase DB connection and approved migrate + seed; `.env` updated (backup `.env.backup`); 60 migrations; seeders run (BPS region path broken → seeder fallback); 150 role/data screenshots captured; docs updated. No application file or `public/` asset changed. | Claude Code (Phase 0 session) |
| 2026-10-30 | 1 | Phase 1 executed: NOT_STARTED → IN_PROGRESS → COMPLETE. Gating decisions D-03/04/05/06/25/31 answered by the user. Code, view-binding, route (D-25) and test changes; 1 additive migration and the approved `users.name` sync on the seeded test DB. No `public/` asset or `.env` changed. | Claude Code (Phase 1 session) |
| 2026-10-30 | 2 | Phase 2 executed: NOT_STARTED → IN_PROGRESS → COMPLETE. Gating answers D-01 (b), D-16 (b), D-17 (a), D-18 (a), D-29 (staged), D-33 (copy all), D-35 (strip) recorded. Additive asset copy, staged CSS imports, `<x-icon>` helper, 7 tests, design-system docs. No route, controller, view logic, DB, `.env` or existing `public/` file changed. | Claude Code (Phase 2 session) |
| 2026-10-30 | 3 | Phase 3 executed: NOT_STARTED → IN_PROGRESS → COMPLETE. User answered D-02 (Indonesian only + copy deck) and D-20 (a); gallery without a route. Shell, components, status mapping, tests; head-only title/favicon on 5 standalone auth pages; 10 `fa-` icons replaced. No route, controller, model, migration, `.env` or `public/` file changed; 3 test-run notification read-marks restored. | Claude Code (Phase 3 session) |
| 2026-10-31 | 4 | Phase 4 executed: NOT_STARTED → IN_PROGRESS → COMPLETE. Gating answers D-23 (b), D-19 (homepage: catalogue photos, no visible credit), public counts and SVG-now recorded; D-30/D-34 still OPEN (not needed). Homepage view/controller rewritten, `RegionCentroids` + inline-SVG partial + 8 tests added, Phase 4 CSS section appended. `CONTENT_RENAME_INVENTORY.md` copied to this folder at the user's request. No route, `.env`, DB row or `public/` file changed. | Claude Code (Phase 4 session) |
| 2026-10-31 | 4 | COMPLETE → IN_PROGRESS: reopened at the user's request for a design revision (critique of the first version). | Claude Code (Phase 4 session) |
| 2026-10-31 | 5 | Pre-implementation check only; status stays NOT_STARTED. Blocker: soft dependency Phase 4 is IN_PROGRESS and the user asked to finish it first. Read-only inspection of about/contact/institution public views and controllers; gate answers recorded in `DECISIONS_AND_OPEN_QUESTIONS.md`. No application file, `public/` asset, DB row or `.env` changed. | Claude Code (Phase 5 session) |
| 2026-10-31 | 4 | IN_PROGRESS → COMPLETE at the user's instruction, after re-verifying the current code (a parallel session had removed the hero pause button and added small-screen rules during this session). AD-11 and the homepage answers to D-19/D-30/D-34 recorded; lead copy changed; fixed-header scroll padding added; Phase 4 files normalised to LF. No route, `.env`, DB row or `public/` file changed by this session. | Claude Code (Phase 4 closing session) |
| 2026-10-31 | 5 | Phase 5 executed: NOT_STARTED → IN_PROGRESS → COMPLETE. User answers this session: continue although the Phase 4 row still read IN_PROGRESS (a parallel session then closed Phase 4); Contact = email of M. Abizzar Gamadrian, Danendra Shafi Athallah, Kenzie Raffa Ardhana (no WhatsApp); team photos from `public/assets/photography/member/`; About captions without credit. Rewrote the about/contact/institution-public views and the About/Contact controllers; read-only additions to `showPublic`; added `TeamMembers`, `public-pages.css`, 6 tests and 7 WebP derivatives (originals untouched). No route, `.env`, DB row, model, migration, shared component or existing `public/` file changed. | Claude Code (Phase 5 session) |
| 2026-10-31 | 5 | Revision after the user's review, then closed at the user's instruction ('anggap selesai setelah anda revisi'): About header designed, header photos changed to `dashboard-student2.jpeg`/`repo-student.jpeg` derivatives, team photos from the user's new transparent `*_seragam.png`, 'Cara kerja' as a vertical timeline. Status stays COMPLETE. | Claude Code (Phase 5 session) |
| 2026-10-31 | 6 | Phase 6 executed: NOT_STARTED → IN_PROGRESS → COMPLETE. Gate answers recorded (D-15 a, D-23 b, no auth photo, RN-16/D-38, RN-17/D-39, 'Ingat saya', success panel; forgot-password message unchanged). Auth views, auth layout, `x-ui.password`, `auth.js`/`auth.css`, 25 tests. No controller, route, model, migration, `.env` or `public/` file changed; two throwaway accounts created on the local test DB and deleted. | Claude Code (Phase 6 session) |
| 2026-10-31 | 6 | Follow-up at the user's request (D-40): 'Karsa' → 'Student Go' in 4 mail subjects, 3 institution email views and the auth flash messages; `AuthBrandTest` added (3 tests). Status stays COMPLETE; suite 114/114. | Claude Code (Phase 6 session) |
