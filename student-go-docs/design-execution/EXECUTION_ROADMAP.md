# EXECUTION ROADMAP — Student Go design

**Primary source of truth** for the design. Created 2026-10-30 by a discovery-and-planning session that did **not** implement anything. It was drafted from the design audit (run 2026-10-28) plus a read-only look at the running app, **without repository access**; therefore Stage One (Phase 0) must verify it before any other phase runs.

Read `CLAUDE.md` first (rules), then this file (order and coverage), then the one phase file you were asked to execute, then `PHASE_TRACKER.md` (status).

## 1. What the design is

A presentation-layer design of Student Go with a hybrid direction (AD-04): 'Ruang Kerja' workbench foundation, 'Catatan Lapangan' selective editorial layer, one brand colour, one type system, original SVG identity graphics, open-source licence-checked assets. Functionality, routes, authentication, data and business logic are preserved (AD-06). P0 data and privacy defects (J1-J4) are fixed first, in Phase 1, because a design must not hide them.

## 2. Evidence labels

| Label | Meaning |
|---|---|
| VERIFIED | Confirmed directly during this discovery session (2026-10-30): either read live in the browser on the running app at http://127.0.0.1:8000 (read-only GET requests, no form submitted, not logged in), or read from a file that was actually opened. No repository file was opened, so VERIFIED never means 'confirmed in source' unless the text says so explicitly (a few file paths and versions leaked through Laravel's own debug error pages and are marked VERIFIED with that origin). |
| OBSERVED | Reported by the audit (run 2026-10-28 against port 8001, three test accounts) and not re-checked in this session. Treat as strong evidence about the app as it was on 28 Oct, not as proof about today's code. |
| INFERRED | A reasonable conclusion from VERIFIED/OBSERVED facts that nobody has confirmed. Must be confirmed before it is relied on. |
| UNKNOWN | No evidence either way. Mostly repository facts (Blade structure, controllers, models, middleware, tests, versions in composer/npm files). |
| BLOCKED | Cannot be investigated from the discovery session at all (no repository access, no admin credentials, no seeded records, tool restrictions, unreachable database). A BLOCKED item names what would unblock it. |

## 3. Phase execution protocol (mandatory)

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

## 4. Phase overview

| # | Phase | Group | Hard deps | Soft deps | Gating decisions | Size (guess) |
|---|---|---|---|---|---|---|
| 0 | [Readiness & Baseline](phases/PHASE_00_READINESS.md) | Foundation | — | — | D-12, D-16, D-22, D-27, D-28, D-32 | M |
| 1 | [Critical Fixes & Data Integrity](phases/PHASE_01_CRITICAL_FIXES.md) | Foundation | 0 | — | D-03, D-04, D-05, D-06, D-25, D-31 | L |
| 2 | [Brand Identity & Design System](phases/PHASE_02_BRAND_AND_DESIGN_SYSTEM.md) | Foundation | 0 | 1 | D-01, D-16, D-17, D-18, D-19, D-20, D-29, D-32, D-35 | L |
| 3 | [Shared Shell & Components](phases/PHASE_03_SHELL_AND_COMPONENTS.md) | Foundation | 2 | 1 | D-01, D-02, D-17, D-20, D-25, D-29, D-32 | XL |
| 4 | [Homepage](phases/PHASE_04_HOMEPAGE.md) | Public and authentication | 3 | 1 | D-01, D-19, D-20, D-23, D-30, D-33, D-34 | L |
| 5 | [About, Contact, Public Institution Profile](phases/PHASE_05_ABOUT_CONTACT_INSTITUTION_PROFILE.md) | Public and authentication | 3 | 1, 4 | D-19, D-23, D-26, D-12, D-30, D-31, D-33, D-34 | M |
| 6 | [Auth](phases/PHASE_06_AUTH.md) | Public and authentication | 3 | 1 | D-01, D-02, D-15, D-23, D-30 | L |
| 7 | [Student Dashboard](phases/PHASE_07_STUDENT_DASHBOARD.md) | Student role | 1, 3 | — | D-03, D-10, D-21, D-12 | M |
| 8 | [Student Project Discovery](phases/PHASE_08_PROJECT_DISCOVERY.md) | Student role | 1, 3 | 2 | D-02, D-05, D-14, D-12 | L |
| 9 | [Project Detail](phases/PHASE_09_PROJECT_DETAIL.md) | Student role | 1, 3 | 8 | D-02, D-14, D-19, D-30 | M |
| 10 | [Applications](phases/PHASE_10_APPLICATIONS.md) | Student role | 1, 3 | 9 | D-02, D-21, D-12 | M |
| 11 | [Active Projects & Wishlist](phases/PHASE_11_ACTIVE_PROJECTS_WISHLIST.md) | Student role | 3 | 10, 8 | D-13, D-21 | S |
| 12 | [Profile & Portfolio](phases/PHASE_12_PROFILE_PORTFOLIO.md) | Student role | 1, 3 | — | D-03, D-04, D-19, D-23, D-31, D-32 | L |
| 13 | [Friends](phases/PHASE_13_FRIENDS.md) | Student role | 1, 3 | 12 | D-02, D-03, D-04, D-31 | M |
| 14 | [Repository](phases/PHASE_14_REPOSITORY.md) | Student role | 1, 3 | 2 | D-02, D-05, D-26 | M |
| 15 | [Jobs](phases/PHASE_15_JOBS.md) | Student role | 3 | 8 | D-07, D-13, D-02 | S |
| 16 | [SAGO Chatbot & Notifications](phases/PHASE_16_SAGO_AND_NOTIFICATIONS.md) | Student role | 1, 3 | — | D-01, D-25 | M |
| 17 | [Institution Dashboard](phases/PHASE_17_INSTITUTION_DASHBOARD.md) | Institution role | 3 | 1 | D-09, D-10, D-13, D-21 | S |
| 18 | [Institution Project Management](phases/PHASE_18_INSTITUTION_PROJECT_MANAGEMENT.md) | Institution role | 3 | 1, 17 | D-02, D-15, D-26, D-13 | L |
| 19 | [Institution Review & Monitoring](phases/PHASE_19_INSTITUTION_REVIEW_MONITORING.md) | Institution role | 3 | 1, 18 | D-02, D-13, D-25, D-26 | L |
| 20 | [Institution Profile & Verification](phases/PHASE_20_INSTITUTION_PROFILE_VERIFICATION.md) | Institution role | 3 | 1, 5 | D-06, D-09, D-13 | M |
| 21 | [Company Dashboard](phases/PHASE_21_COMPANY_DASHBOARD.md) | Company role | 3 | 1 | D-02, D-10, D-13, D-21 | S |
| 22 | [Company Talent Discovery](phases/PHASE_22_COMPANY_TALENT_DISCOVERY.md) | Company role | 1, 3 | 13 | D-02, D-04, D-05, D-08, D-26, D-31 | L |
| 23 | [Company Job Management](phases/PHASE_23_COMPANY_JOB_MANAGEMENT.md) | Company role | 3 | 1, 22 | D-02, D-07, D-13, D-26 | M |
| 24 | [Applicant Pipeline](phases/PHASE_24_APPLICANT_PIPELINE.md) | Company role | 3, 23 | 1 | D-02, D-13, D-26 | M |
| 25 | [Company Profile](phases/PHASE_25_COMPANY_PROFILE.md) | Company role | 3 | 1, 20 | D-06 | S |
| 26 | [Error Pages](phases/PHASE_26_ERROR_PAGES.md) | Cross-cutting | 1, 2, 3 | — | D-01, D-11, D-25 | S |
| 27 | [Responsiveness & Accessibility](phases/PHASE_27_RESPONSIVE_ACCESSIBILITY.md) | Cross-cutting | 4-26 | — | D-24, D-22, D-12, D-13 | L |
| 28 | [Final QA](phases/PHASE_28_FINAL_QA.md) | Cross-cutting | 27 | — | D-12, D-13, D-22, D-24 | L |

Sizes are planning guesses (S, M, L, XL), not commitments.

## 5. Order and dependencies

Default order is numeric. Dependencies are in the table above and in the tracker. Rationale:

- **Phase 0** first: verifies the plan against the repository (Stage One), fixes the environment problem found on 2026-10-30 (database unreachable, D-12) and captures the baseline everything is compared against.
- **Phase 1** before any visual phase: J1-J4 are P0 integrity and privacy defects; page phases 7, 8, 9, 10, 12, 13, 14, 16, 22 depend on them.
- **Phase 2** (tokens, fonts, icons, assets) then **Phase 3** (shell and components): every page phase depends on Phase 3. Phase 1 and Phase 2 do not depend on each other and may run in either order after Phase 0.
- **Phases 4-6** (public and auth), **7-16** (student), **17-20** (institution), **21-25** (company) depend on Phase 3 and, where listed, on Phase 1. Within a role the numeric order is a suggestion; the hard dependencies are the rule. Inside the student role the core flow is 8 (discover), 9 (detail), 10 (apply).
- **Phase 23 before 24**: the applicant pipeline needs the jobs model understood first.
- **Phase 26** (error pages) depends only on Phases 1, 2, 3, so it can run right after Phase 3 if the user wants debug-page exposure handled early.
- **Phase 27** (responsive and accessibility sweep) depends on every page phase 4-26. **Phase 28** (final QA) depends on 27.
- Never run phases in parallel in one working tree. If two agents are used, give each its own branch and never the same phase.

## 6. Route coverage (every route found, mapped to a phase)

The audit found 59 route patterns: 13 public/shared, 21 student, 11 institution, 12 company, 1 admin, 1 JSON API. They are listed here with their phase. Nothing is dropped silently: routes whose status is BLOCKED or INFERRED say so. Full evidence notes are in `DISCOVERY_REPORT.md` section 5. **`php artisan route:list` was not available**, so the inventory is incomplete until Stage One reconciles it.

| Route pattern | Role | Audit status | Evidence | Phase |
|---|---|---|---|---|
| / | Public | Inspected | OBSERVED; page VERIFIED live | Phase 4 |
| /about | Public | Inspected | OBSERVED; title VERIFIED | Phase 5 |
| /contact | Public | Inspected | OBSERVED; title VERIFIED | Phase 5 |
| /login | Guest | Inspected | OBSERVED; title VERIFIED | Phase 6 |
| /register | Guest | Inspected | OBSERVED; title VERIFIED | Phase 6 |
| /register/student | Guest | Inspected (audit); failing locally on 2026-10-30 | OBSERVED; local failure VERIFIED | Phase 6 |
| /register/institution | Guest | Inspected | OBSERVED | Phase 6 |
| /register/company | Guest | Inspected | OBSERVED | Phase 6 |
| /forgot-password | Guest | BLOCKED in audit; route exists (VERIFIED) | VERIFIED | Phase 6 |
| /profile/{username} | Anyone incl. guest | Inspected (6 opened, 699 enumerated) | OBSERVED | Phase 12 |
| /institution/{id} | Anyone | Inspected (4), 79 fetched | OBSERVED; VERIFIED failing locally | Phase 5 |
| /notifications | All roles | Inspected | OBSERVED | Phase 16 |
| 404 / 403 / 500 fallbacks | Anyone | Inspected | OBSERVED; 404 and 500 VERIFIED | Phase 26 |
| /student/dashboard | Student | Inspected (desktop, 390, 768) | OBSERVED | Phase 7 |
| /student/browse-problems | Student | Inspected | OBSERVED | Phase 8 |
| /student/browse-problems/{id} | Student | 1-80 fetched; 1, 45, 52, 60, 62 inspected | OBSERVED | Phase 9 |
| /student/applications | Student | Inspected | OBSERVED | Phase 10 |
| /student/applications/{id} | Student | 270, 271 inspected | OBSERVED | Phase 10 |
| /student/applications/create/{problemId} | Student | 52, 60, 62 inspected | OBSERVED | Phase 10 |
| /student/projects | Student | Inspected (empty) | OBSERVED | Phase 11 |
| /student/repository | Student | Inspected | OBSERVED | Phase 14 |
| /student/repository/{id} | Student | 1-216 fetched; 160 inspected | OBSERVED | Phase 14 |
| /student/friends | Student | Inspected | OBSERVED | Phase 13 |
| /student/friends/search | Student | Inspected | OBSERVED | Phase 13 |
| /student/friends/profile/{id} | Student | 699 enumerated | OBSERVED | Phase 13 |
| /student/jobs | Student | Inspected (empty) | OBSERVED | Phase 15 |
| /student/jobs/alerts | Student | Inspected + measured | OBSERVED | Phase 15 |
| /student/jobs/saved | Student | Inspected + measured | OBSERVED | Phase 15 |
| /student/profile | Student | Inspected | OBSERVED | Phase 12 |
| /student/profile/edit | Student | Inspected | OBSERVED | Phase 12 |
| /student/wishlist | Student | Inspected + measured (empty) | OBSERVED | Phase 11 |
| /student/chatbot | Student | Inspected | OBSERVED | Phase 16 |
| /student/chatbot/create | Student | Fetched | OBSERVED | Phase 16 |
| /student/chatbot/{id} | Student | 1, 2 (created by audit); 3 redirects | OBSERVED | Phase 16 |
| /institution/dashboard | Institution | Inspected | OBSERVED | Phase 17 |
| /institution/problems | Institution | Inspected (empty) | OBSERVED | Phase 18 |
| /institution/problems/create | Institution | Inspected, not submitted | OBSERVED | Phase 18 |
| /institution/problems/{id} | Institution | BLOCKED (0 records; every ID 404) | INFERRED | Phase 18 |
| /institution/problems/{id}/edit | Institution | BLOCKED | INFERRED | Phase 18 |
| /institution/applications | Institution | Inspected (empty) | OBSERVED | Phase 19 |
| /institution/projects | Institution | Inspected (empty) | OBSERVED | Phase 19 |
| /institution/reviews | Institution | Inspected (empty) | OBSERVED | Phase 19 |
| /institution/profile | Institution | Inspected | OBSERVED | Phase 20 |
| /institution/profile/edit | Institution | Inspected | OBSERVED | Phase 20 |
| /institution/verification/upload | Institution | Inspected | OBSERVED | Phase 20 |
| /company/dashboard | Company | Inspected | OBSERVED | Phase 21 |
| /company/talents | Company | Inspected + clicked | OBSERVED | Phase 22 |
| /company/talents/saved | Company | Inspected (empty) | OBSERVED | Phase 22 |
| /company/talents/export | Company | Fetched read-only | OBSERVED | Phase 22 |
| /company/talents/compare | Company | Fetched | OBSERVED | Phase 22 |
| /company/jobs | Company | Inspected (empty) | OBSERVED | Phase 23 |
| /company/jobs/create | Company | Inspected, not submitted | OBSERVED | Phase 23 |
| /company/jobs/{id} | Company | BLOCKED (0 records) | INFERRED | Phase 23 |
| /company/applications | Company | Inspected (empty) | OBSERVED | Phase 24 |
| /company/applications/{id} | Company | BLOCKED (0 records) | INFERRED | Phase 24 |
| /company/profile | Company | Inspected | OBSERVED | Phase 25 |
| /company/profile/edit | Company | Inspected | OBSERVED | Phase 25 |
| /admin/dashboard | Admin | BLOCKED (no credentials) | OBSERVED (403 for non-admin) | Phase 26 |
| /api/regencies/{provinceId} | JSON API | Verified via form | OBSERVED | Phase 6 |

**Routes found outside the 59 patterns, and routes expected but not discoverable from the browser**

| Pattern | Role | Status | Evidence | Phase | Note |
|---|---|---|---|---|---|
| /institution/reviews/create | Institution | Probed by guess | OBSERVED | Phase 19 | Renders a full Illuminate\Database\QueryException debug page (J13). Also relevant to Phase 1. |
| /student/repository/abc | Student | Probed by guess | OBSERVED | Phase 14 | Non-numeric ID returns HTTP 500 (J13). Fix in Phase 1; error page in Phase 26. |
| logout (path and verb UNKNOWN) | All | Used by the audit | INFERRED | Phase 3 | The audit logged out; the route is not in the inventory. Normally a POST form in the nav menu. |
| Password reset and email verification routes | Guest | Not discovered | UNKNOWN | Phase 6 | Standard Laravel auth names, if present. Stage One lists them. |
| Non-GET action routes (apply, cancel application, add connection, wishlist toggle, save job/talent, save search, notification mark-read/delete, chatbot send/archive/delete, profile update, verification upload) | Student/Institution/Company | Not enumerable from GET crawling | UNKNOWN | Phase 0 | Stage One lists them from `route:list` and records form actions per page so designed forms keep posting to the same endpoints. |

Count check: 59 patterns in the table (expected 59).

## 7. Screen coverage (audit page IDs mapped to phases)

All 56 catalogued screens (audit section L, 91 screenshots) are assigned to a phase. The screenshots live in the audit PDF; Phase 0 re-captures them as the baseline.

| Screen ID | Page | URL | Phase |
|---|---|---|---|
| G01 | Beranda publik | / | Phase 4 |
| G02 | Tentang Kami (About) — 7 slide | /about | Phase 5 |
| G03 | Kontak (Contact) | /contact | Phase 5 |
| G04 | Login | /login | Phase 6 |
| G05 | Pilih jenis akun (Register) | /register | Phase 6 |
| G06 | Daftar sebagai Mahasiswa | /register/student | Phase 6 |
| G07 | Daftar sebagai Instansi | /register/institution | Phase 6 |
| G08 | Daftar sebagai Perusahaan | /register/company | Phase 6 |
| S01 | Dashboard mahasiswa | /student/dashboard | Phase 7 |
| S02 | Jelajahi Proyek KKN (daftar) | /student/browse-problems | Phase 8 |
| S03 | Detail proyek KKN (masalah #1) | /student/browse-problems/1 | Phase 9 |
| S04 | Detail proyek — varian sudah melamar (masalah #45) | /student/browse-problems/45 | Phase 9 |
| S05 | Aplikasi Saya (daftar lamaran) | /student/applications | Phase 10 |
| S06 | Detail lamaran — menunggu (#271) | /student/applications/271 | Phase 10 |
| S07 | Detail lamaran — ditolak (#270) | /student/applications/270 | Phase 10 |
| S08 | Form melamar proyek | /student/applications/create/52 | Phase 10 |
| S09 | Proyek Saya | /student/projects | Phase 11 |
| S10 | Repositori Pengetahuan (daftar) | /student/repository | Phase 14 |
| S11 | Detail dokumen repositori (#160) | /student/repository/160 | Phase 14 |
| S12 | Jaringan Anda | /student/friends | Phase 13 |
| S13 | Temukan Koneksi Baru (pencarian mahasiswa) | /student/friends/search | Phase 13 |
| S14 | Lowongan Kerja & Magang | /student/jobs | Phase 15 |
| S15 | Job Alerts | /student/jobs/alerts | Phase 15 |
| S16 | Lowongan Tersimpan | /student/jobs/saved | Phase 15 |
| S17 | Wishlist Saya | /student/wishlist | Phase 11 |
| S18 | Profil Saya (mahasiswa) | /student/profile | Phase 12 |
| S19 | Edit Profil (mahasiswa) | /student/profile/edit | Phase 12 |
| S20 | SAGO — daftar percakapan | /student/chatbot | Phase 16 |
| S21 | SAGO — percakapan | /student/chatbot/1 | Phase 16 |
| S22 | Notifikasi (mahasiswa) | /notifications | Phase 16 |
| S23 | Profil publik mahasiswa | /profile/juliasuryanto1000 | Phase 12 |
| S24 | Profil publik instansi (#19, punya masalah) | /institution/19 | Phase 5 |
| S25 | Jelajahi Proyek — hasil pencarian kosong | /student/browse-problems?search=zzzzqqq | Phase 8 |
| S26 | State interaksi: menu avatar terbuka | /student/dashboard (menu avatar terbuka) | Phase 3 |
| I01 | Dashboard institusi | /institution/dashboard | Phase 17 |
| I02 | Kelola Masalah | /institution/problems | Phase 18 |
| I03 | Review Aplikasi Mahasiswa | /institution/applications | Phase 19 |
| I04 | Kelola Proyek | /institution/projects | Phase 19 |
| I05 | Review Mahasiswa (Ulasan) | /institution/reviews | Phase 19 |
| I06 | Buat Masalah Baru | /institution/problems/create | Phase 18 |
| I07 | Profil Instansi | /institution/profile | Phase 20 |
| I08 | Edit Profil Instansi | /institution/profile/edit | Phase 20 |
| I09 | Verifikasi Dokumen Institusi | /institution/verification/upload | Phase 20 |
| I10 | Notifikasi (institusi) | /notifications | Phase 16 |
| I11 | Profil publik instansi (#1, tanpa masalah) | /institution/1 | Phase 5 |
| C01 | Dashboard perusahaan | /company/dashboard | Phase 21 |
| C02 | Temukan Talenta | /company/talents | Phase 22 |
| C03 | Talent Tersimpan | /company/talents/saved | Phase 22 |
| C04 | Kelola Lowongan Pekerjaan | /company/jobs | Phase 23 |
| C05 | Buat Lowongan Baru (wizard) | /company/jobs/create | Phase 23 |
| C06 | Manajemen Lamaran | /company/applications | Phase 24 |
| C07 | Profil Perusahaan | /company/profile | Phase 25 |
| C08 | Edit Profil Perusahaan | /company/profile/edit | Phase 25 |
| C09 | Notifikasi (perusahaan) | /notifications | Phase 16 |
| E01 | Error 403 (admin) | /admin/dashboard (403) | Phase 26 |
| E02 | Error 404 | /student/browse-problems/999 (404) | Phase 26 |

## 8. Backlog coverage (audit J1-J38)

Every backlog item has a primary phase and the phases that touch it. P0 items are fixed in Phase 1 and re-verified in the page phases listed.

| ID | Priority | Finding | Primary phase | Also |
|---|---|---|---|---|
| J1 | P0 | One person, two names/initials | 1 | 7, 12, 13, 22 (verify) |
| J2 | P0 | Lists repeat and hide records | 1 | 8, 14 (verify) |
| J3 | P0 | Contact data exposed to guests | 1 | 12 (verify) |
| J4 | P0 | Contradictory counts | 1 | 8, 14, 22 (labels) |
| J5 | P1 | Edit forms lose stored values | 1 | 12, 20, 25 |
| J6 | P1 | Photo heroes on task pages | 3 | 5, 7, 8, 10, 11, 12, 13, 14, 15, 16, 21, 22, 23, 25 |
| J7 | P1 | Broken mobile layouts | 27 | 4, 6, 13, 23, 24 |
| J8 | P1 | Mixed language and status vocabulary | 3 | all page phases |
| J9 | P1 | Fabricated or meaningless metrics | 4 | 5, 6, 7, 17, 21 |
| J10 | P1 | Contrast failures | 2 | 3, 7, 9, 12, 22, 27 |
| J11 | P1 | Focus and naming gaps | 3 | 16, 27 |
| J12 | P1 | No fallback for missing problem images | 9 | 8 (and D-14) |
| J13 | P1 | State-changing GET and debug/500 pages | 1 | 16, 19, 26 |
| J14 | P1 | Destructive action styled as primary | 10 | 3 (dialog) |
| J15 | P2 | Type split across 4 families | 2 | 4, 6 |
| J16 | P2 | Six competing accents | 2 | 17, 18, 21, 25 |
| J17 | P2 | Duplicated components | 3 | 6, 10, 11, 15, 16, 18, 19, 20, 22, 23, 24, 25 |
| J18 | P2 | Card grids where users compare | 8 | 10, 13, 14, 15, 18, 19, 22 |
| J19 | P2 | Overflow and truncation with real content | 8 | 5, 9, 20 |
| J20 | P2 | Naming leftovers (Laravel, Student Go, favicon, '#') | 2 | 3, 4, 5 |
| J21 | P2 | Long forms, native upload inputs | 18 | 20 |
| J22 | P2 | Conflicting talent search patterns | 22 | 1 (counts) |
| J23 | P2 | Decorative motion without reduced-motion | 2 | 3, 4, 27 |
| J24 | P2 | Unused/heavy assets | 3 | 2, 15 |
| J25 | P2 | Mobile menu gaps | 3 | 27 |
| J26 | P3 | Emoji as icons | 16 | 3, 9, 10 |
| J27 | P3 | Raw data labels | 14 | — |
| J28 | P3 | Foreign placeholder text | 23 | 6 |
| J29 | P3 | Validation colour too early | 10 | 6 |
| J30 | P3 | Regency select race | 6 | 18 |
| J31 | P1 | Rejected application shown as submitted | 9 | — |
| J32 | P2 | Text broken mid-word | 10 | — |
| J33 | P1 | Invisible CTA in saved-talents empty state | 22 | — |
| J34 | P2 | About is a 7-slide carousel | 5 | — |
| J35 | P2 | Contact has no official channel; broken team photos | 5 | — |
| J36 | P3 | Duplicate related documents | 14 | — |
| J37 | P3 | SAGO send icon looks like a warning | 16 | 3 |
| J38 | P2 | Seed notifications inconsistent with account state | 16 | 0 (D-13) |

## 9. Guardrails (audit section H, binding on every page phase)

1. No hero on task pages: the page starts with an <h1> page header and the primary action within the first 160 px.
2. Every number is real and useful: no marketing counters, no trend chip without two periods of data, no chart under five points.
3. Cards earn their place: default to table, list or divided sections; cards only for an optional gallery view.
4. One elevation rule: borders and dividers group content; shadow only on menus, dialogs, toasts and sticky bars.
5. No gradients on controls; at most one brand surface.
6. Pill means status: buttons use the standard radius; chips and badges are only for status and taxonomy.
7. Images only when they carry content: no placeholder tiles; initials or nothing when there is no logo or photo.
8. Motion has a job: 150-200 ms colour/opacity on state changes only; everything off under prefers-reduced-motion.
9. Text comes from the domain: Indonesian first, KKN/SDG/region examples, one word per status on every screen.
10. Design with the real dataset: check every list against 77 problems, 216 documents, a 76-character institution name and an empty account.

## 10. Global preservation rules

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

## 11. Standard checks (every phase)

- Viewport matrix: 360, 390, 768, 1024, 1366 and 1440 px wide. At each width `document.documentElement.scrollWidth <= window.innerWidth` (no page-level horizontal scroll). At most one horizontally scrolling container per table.
- State matrix for every changed page: default, empty, long content (76-character institution name, 65-character document title, 51-character email), missing media, validation error, and the role/permission variants that exist for that page.
- Keyboard: Tab through the whole page; focus is always visible; no keyboard trap; Esc closes overlays; exactly one <h1>; `<html lang="id">`.
- Contrast: text pairs >= 4.5:1, large text and UI parts >= 3:1 (use the measured table in docs/DESIGN_SYSTEM.md; re-measure any new pair). Check with `prefers-reduced-motion: reduce` emulated.
- Functionality: compare against the Phase 0 baseline (routes, form field names, query parameters, redirects). Submit forms only against the local seeded test database with test accounts found in the project's seeders; never against production.
- Console and network: no JavaScript errors; no request to a CDN that this phase removed; no 404 for a new asset.
- Automated: run the project's own test suite (command confirmed in Stage One; see CLAUDE.md section 4) and report pass/fail counts before and after.
- Visual: take screenshots of every changed page at the matrix widths and store them in the phase report; compare against the Phase 0 baseline and the audit's screenshot IDs.
- Assets: `git status` / `git diff --name-status` shows no deleted, renamed or overwritten file under `public/`; every asset the changed pages reference returns 200; nothing is copied into a production asset directory unless this phase's section H authorises it; certificate output (if the phase touches fonts, layout or the asset pipeline) is byte-comparable or visually identical to the Phase 0 baseline.

## 12. Asset reconciliation (AD-09)

Every phase file now has a section H with five parts: H.1 existing `public/` assets that matter in the phase (with decision and evidence), H.2 custom package assets available for it, H.3 the reconciliation recommendation, H.4 native UI, H.5 what must not be touched. The master list is `ASSET_INVENTORY.md` (14 sections). Rules: nothing is deleted, overwritten, moved or renamed; replacing an asset changes a template reference in the phase that owns the page and nowhere else; certificate resources and genuine user photographs are protected; originals stay until Phase 28 or until the user authorises archiving.

**Asset findings added by the reconciliation (not in the audit; IDs prefixed A- to avoid clashing with J1-J38)**

| ID | Finding | Evidence | Phase | Decision |
|---|---|---|---|---|
| A-01 | Two of three team portraits on `/contact` are broken: `/profile_13523124.jpg` does not exist (the file is `.jpeg`) and `/profile_18223127.jpg` is missing | VERIFIED 2026-10-30 | 5 | D-31 |
| A-02 | Team portrait file names embed student ID numbers on a public page | VERIFIED | 5 | D-31 |
| A-03 | Every SVG, PNG and the style guide in the custom package carries an embedded C2PA block (about 7.7 KB per SVG) | VERIFIED (package read) | 2 | D-35 |
| A-04 | Heavy originals: `about.jpg` 3.6 MB (7360x4912), `dashboard-student3.jpg` 3.3 MB, `SAGO-background.png` 1.7 MB, seven 4912x2760 photographs of 0.4-1.2 MB | VERIFIED (byte and pixel sizes) | 4, 5, 16 | D-34 |
| A-05 | The Vite manifest lacks `resources/css/auth-institution.css`, which breaks `/forgot-password` (HTTP 500); `/register/institution` and `/register/company` also return 500 locally with a cause not established in this pass | VERIFIED (500 on all three; Vite cause verified for `/forgot-password` only) | 0 (S1-04), 6 | D-29 |
| A-06 | No source, licence or consent record exists for any existing photograph | UNKNOWN (no record found) | 4, 5 | D-30 |
| A-07 | The package's CSS is at `resources/css/student-go/`, not under `public/`; INTEGRATION.md instructs removing the logo and photo heroes, which conflicts with AD-09 | VERIFIED | 2 | D-29, ASSET_INVENTORY.md section 13 |
| A-08 | Certificate templates and the Pinyon Script and Poppins fonts could not be located; no certificate route appears in the audit | UNKNOWN / BLOCKED | 0 (S1-31) | D-32 |

## 13. What this roadmap does not decide

Product naming, language policy, privacy policy, data model choices, scope, content ownership and tooling are open decisions, listed with recommendations (kept separate from approved decisions) in `DECISIONS_AND_OPEN_QUESTIONS.md`. Reference documents B ('Design Implementation Plan') and C ('Custom Visual & Asset Plan') were not found; nothing has been silently substituted (D-16).
