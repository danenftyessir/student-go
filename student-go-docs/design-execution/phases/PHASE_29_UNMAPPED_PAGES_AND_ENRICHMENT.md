# PHASE 29 — Unmapped Pages & Enrichment

> Source of truth for status is `student-go-docs/design-execution/PHASE_TRACKER.md`. This file is the work order for exactly one phase, executed **one package per request** ("Execute Phase 29, paket X"). Read `CLAUDE.md` and `student-go-docs/design-execution/EXECUTION_ROADMAP.md` first, execute only the named package, update the tracker, report, then stop.
> Evidence labels: VERIFIED / OBSERVED / INFERRED / UNKNOWN / BLOCKED (definitions in `DISCOVERY_REPORT.md` section 1).
> Written 2026-11-01 (planning only, no code changed) from `reports/ASSET_ENRICHMENT_AUDIT.md` (finding IDs C1–C11, proposal IDs U1–U9) and the user's decisions D-41 to D-48.

| Field | Value |
|---|---|
| Phase | 29 — Unmapped Pages & Enrichment |
| Group | Cross-cutting (student, institution, company, public) |
| Hard dependencies (must be COMPLETE) | Phase 1 (Critical Fixes & Data Integrity), Phase 3 (Shared Shell & Components) |
| Soft dependencies (recommended first) | Per package: A → 10; B → 11, 19 (stage meter); C → 15; D → 18, 19, 20; E → 23, 25; F → 6, 13, 21; G → 5; H → 8, 9 |
| Decisions that gate this phase | D-02, D-04, D-09 (package D), D-11/D-47 (admin, out of scope), D-13, D-14 (package H), D-19 with D-41 to D-45 and D-48, D-25 (C1, C2), D-30, D-32 (package B), D-46 (package H) |
| Effort estimate (planning guess, not a commitment) | L in total; A M, B M, C S, D M, E M, F M, G M, H S (stage 1) + S (stage 2) |
| Roles touched | Student, Institution, Company, Guest (public company profile, public institution profile) |
| Audit references | Enrichment audit 3.1 (DOM share and screenshots), C1–C11, U1–U9; design audit J6, J8, J9, J16, J17 |
| Tracker status at planning time | NOT_STARTED |

**Why this phase exists.** Nine GET routes (eight page groups) render legacy Tailwind markup (0–23 % `sg-*` classes in `<main>`; enrichment audit 3.1, VERIFIED from the rendered DOM on 2026-11-01) and are listed in no phase: they are missing from roadmap section 6, and the completion reports of Phases 19, 20, 23 and 25 name them as out of scope. Under `CLAUDE.md` section 8 step 8 no existing "Execute Phase N" request may touch them. Phase 29 gives them, and the defects found next to them, one owner.

## A. Phase overview

**Goal.** Bring the unmapped pages into the design system with the existing components, icons and illustrations, fix the defects C1–C6 found by the enrichment audit, add the region map to the public institution profile (U4), and plan (not yet perform) the seed photo repair (U2), without changing what the product does.

**Packages.** Each package is executed by its own request and closed in its own report. A package never starts another one. Recommended order (from `reports/ASSET_ENRICHMENT_PROMPTS.md`): E → C → A → B → D → F → G → H.

| Package | Scope | Routes / files | Approval |
|---|---|---|---|
| A | Student applications | `student.applications.tracker`, `student.applications.show`, `student.applications.create` | approved |
| B | Student active project | `student.projects.show`, `student.projects.create-report`, `student.projects.create-final-report` | approved |
| C | Student job detail | `student.jobs.show` | approved |
| D | Institution | `institution.projects.manage`, `institution.verification.status` | approved (D-09 wording frozen) |
| E | Company | `company.public`, `company.jobs.edit` | approved |
| F | Defects and polish | C1, C2, C3 (company dashboard table), C4, C5, C6, U6 | approved; C1 and C2 are behaviour changes approved by the user on 2026-11-01 |
| G | Region map on the public institution profile | `/institution/{id}` (Phase 5 page), new helper in `app/Support` | approved (D-45) |
| H | Seed problem photographs | `problem_images` rows (database), possibly Supabase Storage | **awaiting approval** (D-46); stage 1 is read-only, stage 2 only after explicit approval of the stage 1 plan |

**In scope**

- The routes and views in the table above; the shared layouts only for C4 (`layouts/app.blade.php`) and C5 (`layouts/auth.blade.php`); `/company/dashboard` only for the chart data table (C3); the five page-level empty states of U6; `FriendController` and `student/friends/profile.blade.php` only for C2 and C6.
- New tests for every package (`tests/Feature/Phase29/`, one file or folder per package).
- Additive CSS in `resources/css/student-go/` (a new `phase29.css` or a section in the existing role file, imported by `app.css`).

**Out of scope**

- The six `/admin/*` views (D-47: deferred until an admin account exists).
- Any route added, removed, renamed or re-verbed; any controller change other than C1, C2, C6, the read-only queries named in a task, and package G's helper call.
- Photographs on new slots: D-43, D-44 and D-45 close every candidate slot this phase touches; package H changes seed data only and only after D-46.
- `workflows/*.svg` and `indonesia-outline.svg` (D-48).
- Correcting the tracker rows of Phases 7–26 (several completion reports recommend COMPLETE while the rows read NOT_STARTED; a separate document task).
- Deleting or archiving any file (U9).

**Evidence basis.** Enrichment audit of 2026-11-01 (Playwright outside the project, 1440×900 and 390×844, GET requests and logins only) plus a read-only check on 2026-11-01 for this file: `php artisan route:list --json` (203 routes; all twelve route names below VERIFIED with their controller actions), `grep` over the views (counts of `<style>` blocks and asset references below VERIFIED). Everything about how a view looks is OBSERVED from the audit screenshots unless marked otherwise.

## B. Pages and routes

| Route name | Method and URI | Controller action | View | `<style>` blocks | Audit 3.1 (`sg-*` / utility) | Package |
|---|---|---|---|---|---|---|
| `student.applications.tracker` | GET `student/applications/tracker` | `Student\ApplicationController@tracker` | `student/applications/tracker.blade.php` | 1 | 0 % / 87 % | A |
| `student.applications.show` | GET `student/applications/{id}` | `ApplicationController@show` | `student/applications/show.blade.php` | 0 | 6 % / 53 % (mixed) | A (also listed in Phase 10) |
| `student.applications.create` | GET `student/applications/create/{problemId}` | `ApplicationController@create` | `student/applications/create.blade.php` | 0 | 23 % / 30 % (mixed) | A (also listed in Phase 10) |
| `student.projects.show` | GET `student/projects/{id}` | `Student\MyProjectsController@show` | `student/projects/show.blade.php` | 1 | 0 % / 33 % (DOM figure unreliable, see audit note) | B |
| `student.projects.create-report` | GET `student/projects/{id}/report/create` | `MyProjectsController@createReport` | `student/projects/create-report.blade.php` | 1 | 0 `sg-*` tokens in the view file | B |
| `student.projects.create-final-report` | GET `student/projects/{id}/final-report/create` | `MyProjectsController@createFinalReport` | `student/projects/create-final-report.blade.php` | 1 | 0 `sg-*` tokens in the view file | B |
| `student.jobs.show` | GET `student/jobs/{id}` | `Student\JobController@show` | `student/jobs/show.blade.php` | 1 | 0 % / 68 % | C |
| `institution.projects.manage` | GET `institution/projects/{id}/manage` | `Institution\ProjectManagementController@manage` | `institution/projects/manage.blade.php` | 0 | 0 % / 80 % | D |
| `institution.verification.status` | GET `institution/verification/status` | `Institution\VerificationController@showStatus` | `institution/verification/status.blade.php` | 0 | 0 % / 87 % | D |
| `company.public` | GET `company/{id}` | `Company\ProfileController@showPublic` | `company/profile/public.blade.php` | 1 | 0 % / 98 % | E |
| `company.jobs.edit` | GET `company/jobs/{id}/edit` | `Company\JobPostingController@edit` | `company/jobs/edit.blade.php` | 1 | 0 % / 76 % | E |
| `student.friends.activities` | GET `student/friends/activities` | `Student\FriendController@getActivities` | none (JSON/partial; UNKNOWN) | — | HTTP 500 (C2) | F |

Package F also touches pages that already belong to completed or reported phases: `/company/dashboard` (Phase 21, C3 table only), `layouts/app` (Phase 3, C4), `layouts/auth` (Phase 6, C5), `/student/friends/*` (Phase 13, C6 only), and the U6 empty states on `/institution/problems`, `/institution/projects`, `/institution/reviews`, `/student/jobs`, `/student/jobs/saved`. Package G touches `/institution/{id}` (Phase 5).

**Sample records for live checks** (from the audit; test-account e-mails and seeder line numbers are in `reports/ASSET_ENRICHMENT_PROMPTS.md`; passwords are read by the script from the seeders and never printed or stored): applications 330, 331, 332 and form `/student/applications/create/5`; project 77; jobs 363, 573; manage 118, 188, 217; company profiles 1, 77, 120; job edit 619, 623 (owned by the test company); institution profiles 33, 56, 59, 61.

## C. Existing implementation

- [VERIFIED 2026-11-01] Eight of the eleven views carry an inline `<style>` block (table in B). `company/jobs/edit.blade.php:9` uses `company3.jpg` and `company/profile/public.blade.php:8` uses `company-profile.jpg`, each as an inline `background-image` hero.
- [VERIFIED] `tracker.blade.php` hard-codes the title and h1 'Application Tracker' (lines 4, 42) and a block 'Aktivitas Minggu Ini' (line 79). The audit saw seven empty grey boxes there (OBSERVED) and English statuses 'Rejected', 'Pending', 'Shortlisted', 'Applied' (OBSERVED).
- [VERIFIED] `institution/verification/status.blade.php` says 'Hasil verifikasi AI' (line 14), 'AI Score' (123) and 'Hasil Analisis AI' (134). This wording is gated by D-09 (OPEN) and is not changed by this phase.
- [VERIFIED] `student/projects/show.blade.php` on `chore/project-assets` @ `92b9103` contains no certificate button (`grep -i 'sertifikat|certificate'` finds only `student/projects/index.blade.php` and `student/profile/components/portfolio.blade.php`). The branch `feat/student-certificate` exists and may add one; package B keeps whatever certificate control exists on the branch it runs on and adds none.
- [VERIFIED] `App\Support\StatusVocabulary` has the domains `application`, `job_application`, `problem`, `project`, `milestone` and others; it has no `active` entry for the institution `verification` domain (D-09 note, Phase 17).
- [VERIFIED] `institution/projects/components/stage-meter.blade.php` (Phase 19): one segment per milestone, filled = completed, the stored `progress_percentage` is not shown.
- [VERIFIED] `.sg-org*` lives in `resources/css/student-go/public-pages.css` (Phase 5) and is reused by `company.css` (`.sg-co-profile`, Phase 25) and `institution.css`.
- [VERIFIED] `asset('placeholder-project.jpg')` at `FriendController.php:425` and `:452` (inside `getActivities`, which starts at line 362) and `asset('placeholder-profile-cover.jpg')` at `student/friends/profile.blade.php:10`; neither file exists in `public/`. No view or JS under `resources/` references `friends/activities` (grep; dynamic URLs not excluded).
- [VERIFIED] `public/assets/illustrations/geographic/indonesia-regions.svg` and the five empty-state SVGs (`no-applications`, `no-documents`, `no-notifications`, `no-saved-projects`, `no-search-results`) exist. `app/Support/` holds `NotificationTypes`, `RegionCentroids`, `RepositoryFormat`, `StatusVocabulary`, `TeamMembers`; no province-to-island-group mapping exists.
- [VERIFIED] `student-go-docs/design-execution/baseline/asset-hashes.txt` is **not present** in this checkout and is not tracked on any local or remote branch (`git ls-tree` over all refs). Earlier phase reports cite "37/38 Phase 0 hashes". See K (asset hashes) for the fallback.
- [OBSERVED] Overflow at 390 px: `/student/jobs/{id}` 399 px (badge header), `/company/jobs/{id}/edit` 490 px (stepper '5 Kriteria Dampak'), `/company/dashboard` chart data table 395 px (C3).

## D. Problems found (audit findings)

| ID | Finding | Package | Evidence |
|---|---|---|---|
| U1 | Nine legacy and two mixed page patterns outside the design system | A–E | VERIFIED (DOM share) |
| C1 | `/student/applications/999999` answers 500 instead of 404 | F | VERIFIED (HTTP status) |
| C2 | `GET /student/friends/activities` answers 500 | F | VERIFIED (HTTP status); cause UNKNOWN |
| C3 | Overflow at 390 px on job detail, job edit, company dashboard chart table | C, E, F | VERIFIED |
| C4 | Footer floats on short pages (61–165 px gap at 1440×900) | F | VERIFIED (five pages) |
| C5 | Login error block: ~100 px empty space, indented, lower-case sentence | F | OBSERVED (screenshot) |
| C6 | Two references to non-existent placeholder images | F | VERIFIED |
| C7 | Unreachable and uncalled views | — (recorded only; archive candidates, no action) | VERIFIED |
| C8 | Status vocabulary not uniform (tracker English, 'Overdue', '1 day ago', 'views', 'Internship') | A, B, C, E | OBSERVED |
| C9 | 10 of 48 `x-ui.empty-state` uses without illustration | F (five page-level ones, U6) | VERIFIED |
| C10 | Seed photographs do not match their problems; six generic captions claim content | H | VERIFIED (screenshots, count query) |
| C11 | Long SDG chip names in the browse list | — (Phase 8 territory; not in this phase) | OBSERVED |
| U4 | Public institution profile has no figure that carries content | G | proposal |
| U5 | Photos on editorial pages | — (D-44: none on student profile/portfolio or repository document; problem detail content via H) | proposal |
| U7 | Idle icons with a real slot: `globe`, `phone`, `shield-check` | D, E | VERIFIED (sprite) |

## E. Approved design direction

**Approved by the user (binding)**

- Hybrid direction (AD-04), principles (AD-05), functional preservation (AD-06), asset policy (AD-09). Every page in this phase is a task page (Ruang Kerja) except the public company and institution profiles (organisation pattern, no editorial photography).
- D-41: photographs only from the 37 `available` files of `photo-review.json`, minus the five withheld by D-42, and only in editorial pages, public profiles and the browse gallery. D-43, D-44, D-45 and package E's brief narrow that further: **no package A–G places a photograph**.
- D-45: package G approved. D-46: package H not approved. D-48: `workflows/*.svg` and `indonesia-outline.svg` stay unused.
- C1 and C2 are approved behaviour changes (user, 2026-11-01): failing test first, then the fix; routes unchanged.

**Proposed for this phase** (status PROPOSED; the executing session may refine within the guardrails)

- A: one page header 'Lamaran' style, a list or table of applications with the shared status badge, no chart unless it has at least five real points.
- B: page header, stage meter (Phase 19 component pattern), milestone list, report history; report forms on the shared sectioned form and `x-ui` upload component.
- C: page header with title, company and status chips that wrap; description in reading width; one primary action.
- D: manage page as a work surface with sectioned forms; status page as a status summary with the `shield-check` icon, wording unchanged.
- E: public company profile on `.sg-org`; job edit on the Phase 23 wizard (same partials and stepper as `create`).

**Guardrails that bind this phase** (numbered as in `EXECUTION_ROADMAP.md` section 9): 1, 2, 3, 4, 5, 6, 7, 8, 9, 10 (all).

## F. Implementation tasks (do in order, only for the package named in the request)

### F.0 Common steps (every package, before and after its own tasks)

29.0.1. Protocol (`CLAUDE.md` section 8): read the files, run `git status`, record branch and HEAD, ask the user which branch to use (D-27), set the Phase 29 row to IN_PROGRESS and the package's entry in the Notes column to IN_PROGRESS.
29.0.2. Soft-dependency check: the package's soft dependencies (header table) are NOT_STARTED in the tracker even where a completion report exists. Warn the user and continue only with their confirmation.
29.0.3. Baseline before any edit: `php artisan route:list --json` saved to the scratchpad (count; expected 203 on `92b9103`); `php artisan test` (pass count); `npm run build`; for every view the package rewrites, the list of forms (`action`, method including `@method`, `enctype`) and every `name`/`id` of `input`, `select`, `textarea`, `button[name]`, extracted from the rendered HTML of a sample record into a text file; SHA-256 of every file under `public/` except `public/build/` (see K); screenshots at 360, 390, 768 and 1440 px of every page the package changes.
29.0.4. After the package's tasks: repeat 29.0.3 and diff it against the baseline; record the evidence for every box of the package's section L; update `ASSET_INVENTORY.md` (new section 'Phase 29, paket X'); update the tracker (package status in Notes; phase status per the rule in L.0); report and stop.

### F.A Package A — Student applications

29.A.1. Read `phases/PHASE_10_APPLICATIONS.md` (screens S05–S08) and `ASSET_INVENTORY.md` section 31 (Phase 10 record): Phase 10 has no completion report, so establish which parts of `show` and `create` are already on `x-ui` components and keep them.
29.A.2. Tracker: rewrite `tracker.blade.php` without `<style>`, hex values or inline colours; h1 and title in Indonesian (copy deck, D-02); every status through `App\Support\StatusVocabulary` (C8), same words as `/student/dashboard` and `/student/applications`. If a stored value has no vocabulary entry, add it additively only after confirming the value is really stored (query or enum); never invent a status.
29.A.3. 'Aktivitas Minggu Ini' (guardrail 2): remove it, or replace it with a number from a query the controller already runs or one read-only query added to `ApplicationController@tracker` (state which). No chart under five real points; no coloured stat numbers; 'Aksi Cepat' becomes at most one primary and secondary buttons in the standard style.
29.A.4. Detail and form: finish the migration of `show` and `create` to the shared components; the application form keeps `action`, method, `enctype`, CSRF and every field name; the destructive 'Batalkan' keeps the shared confirm dialog (J14).
29.A.5. Tests (`tests/Feature/Phase29/PackageA*`): tracker renders the vocabulary words and no English status words; tracker has no 'Aktivitas Minggu Ini' (or the replacement shows the queried number); field names of the create form equal the baseline list; another student's application still 404.
29.A.6. F.0 closing steps.

### F.B Package B — Student active project

29.B.1. Detail `show.blade.php`: replace the gradient progress bar with a stage meter that follows `institution/projects/components/stage-meter.blade.php` (one segment per milestone, filled = completed, text 'x dari y tahap'); reuse the partial or extract a shared one without changing the institution output. The stored `progress_percentage` is not shown.
29.B.2. Replace 'Overdue' and '1 day ago' with vocabulary and Indonesian relative time; purple buttons become the standard primary/secondary buttons.
29.B.3. Certificate: keep every certificate control that exists on the branch in use (on `92b9103` there is none in `show`; see C). Do not add one, do not change the certificate route, service or `public/assets/certificate/**` (D-32).
29.B.4. Both report forms: shared sectioned form and `x-ui` upload component; `action`, method, `enctype`, upload field names, accepted types and size hints unchanged; no `<style>` block.
29.B.5. Tests: stage meter text equals the milestone count of a seeded project; form field names, `enctype` and `accept` equal the baseline; no submit against the test account's real project (submit only in feature tests).
29.B.6. F.0 closing steps.

### F.C Package C — Student job detail

29.C.1. Rewrite `student/jobs/show.blade.php` without `<style>`; header wraps at 360 px (C3: the badge header widened the document to 399 px at 390 px).
29.C.2. Replace 'Cover Letter', 'Skills Yang Dibutuhkan', 'SDG Alignment', 'Diposting 1 day ago', '470 views' and 'Internship'/'internship' with copy-deck terms (D-02); relative time in Indonesian; job type through one mapping.
29.C.3. Apply, save and compare forms or links keep `action`, method and field names.
29.C.4. Tests: no English label from the list above in the response; field names equal the baseline.
29.C.5. F.0 closing steps.

### F.D Package D — Institution

29.D.1. `institution/projects/manage.blade.php`: sectioned work surface with the shared components; every milestone, report and status form (including `requires_report`, Phase 17, and the milestone endpoint that answers JSON) keeps `action`, method, `@method` and field names; stage meter as in Phase 19.
29.D.2. `institution/verification/status.blade.php`: status summary with `<x-icon name="shield-check">` (U7) and the shared status component. **Every 'AI' sentence stays word for word** (D-09 OPEN); if the design seems to need different wording, stop and ask. If the stored value `active` needs a `verification` vocabulary entry, add it additively and say so in the report.
29.D.3. Tests: manage forms' field names equal the baseline; the status page still contains the D-09 sentences; no status change is made on the test account's data (submits only in feature tests or on rows created and deleted by the session).
29.D.4. F.0 closing steps.

### F.E Package E — Company

29.E.1. `company/profile/public.blade.php` (guest-visible): rewrite on `.sg-org` like the public institution profile (Phase 5) and `company/profile/index.blade.php` (Phase 25); no photo hero; logo if uploaded, initials otherwise (guardrail 7); contact rows with `<x-icon name="globe">` and `<x-icon name="phone">` (U5, U7). Show no field that the old public page or the public institution profile does not show (D-04).
29.E.2. `company-profile.jpg` stops being referenced; record it as Deprecate (usage) in `ASSET_INVENTORY.md`; the file stays.
29.E.3. `company/jobs/edit.blade.php`: same wizard as `company/jobs/create.blade.php` (Phase 23): same step partials, the shared `x-ui.stepper`, `company/jobs/components/preview.blade.php`; form keeps `action`, `PUT` via `@method`, `enctype` and every field name; stored values prefilled; the stepper wraps at 390 px (C3: 490 px today).
29.E.4. `company3.jpg` stops being referenced (its last template use); record Deprecate (usage); the file stays.
29.E.5. Tests: public profile has no `company-profile.jpg` and no field outside the allowed set; edit form field names and method equal the baseline; edit prefills stored values. A live edit submit only on a job owned by the test company, restored exactly afterwards with proof in the report.
29.E.6. F.0 closing steps.

### F.F Package F — Defects and polish

29.F.1. C1: write a failing feature test (`GET /student/applications/999999` as a student → 404), find the cause (expected: a query without `findOrFail`/ownership-scoped `firstOrFail`), fix it; another student's application still 404 (rule 5).
29.F.2. C2: write a failing test for `GET /student/friends/activities` (no 500), check whether any JS calls the endpoint (grep found none in `resources/`), find the cause without copying `storage/logs` content into any document, fix it. Route kept as is.
29.F.3. C6: the two `asset('placeholder-project.jpg')` uses in `FriendController` (lines 425, 452, inside `getActivities`) and `placeholder-profile-cover.jpg` in `student/friends/profile.blade.php:10` become initials or no image (guardrail 7). No new image file. `student/friends/profile` is unreachable (C7), so only the reference is removed.
29.F.4. C4: `layouts/app.blade.php` becomes a column flex layout whose `main` fills the remaining height; regression screenshots of short and long pages for guest and all three roles (at least `/contact`, `/student/wishlist`, `/student/projects`, 404, `/student/friends`, plus one long page per role).
29.F.5. C5: error block spacing in `layouts/auth.blade.php` (aligned with the column, no ~100 px gaps); the sentence starts with a capital letter (copy only; the message key and flash/session behaviour unchanged).
29.F.6. C3: the chart data table on `/company/dashboard` gets an `overflow-x: auto` wrapper (one scrolling container per table).
29.F.7. U6: illustrations for the five page-level empty states: `institution/problems` → `no-documents`, `institution/projects` → `no-applications`, `institution/reviews` → `no-documents`, `student/jobs/saved` → `no-saved-projects`, `student/jobs` 'Halaman ini kosong' → `no-search-results`. The five empty states inside tabs or sub-sections stay text only.
29.F.8. Tests: C1 and C2 (red before, green after), the five empty states render their illustration, no `placeholder-*.jpg` in any response.
29.F.9. F.0 closing steps.

### F.G Package G — Region map on the public institution profile (approved, D-45)

29.G.1. New helper in `app/Support` (for example `ProvinceRegion`) mapping the first two digits of a BPS province code to one of the seven groups of `indonesia-regions.svg`: `region-sumatra`, `region-jawa`, `region-kalimantan`, `region-sulawesi`, `region-nusa-tenggara`, `region-maluku`, `region-papua`. Unit test over all 38 province codes (the seeded database holds only 5 provinces; D-15).
29.G.2. Inline the SVG through a partial (as the homepage map of Phase 4); the original file is not edited. Active groups get `.is-active`.
29.G.3. Numbers only from real queries: open problems of the institution per group. Text equivalent for screen readers, for example '4 masalah dibuka di Jawa'. Without open problems show only the home region, never '0'.
29.G.4. The page is public: show no data beyond what it shows today (province and open-problem count).
29.G.5. Tests: helper mapping (38 codes), text equivalent equals the queried count, no '0 masalah' text. Live checks on `/institution/33`, `/56`, `/59`, `/61`.
29.G.6. F.0 closing steps.

### F.H Package H — Seed problem photographs (AWAITING APPROVAL, D-46)

**Stage 1 (read-only; may run when the user asks for it).** No write to the database, Supabase Storage, `public/` or application code.

29.H.1. Trace how `problem_images.image_path` (220 rows, 92 files `problems/masalah-desa-NN.jpg`) becomes a URL (`SupabaseStorageService` or otherwise), which seeder writes the rows and where the files came from.
29.H.2. Match each `masalah-desa-NN.jpg` to a catalogue photo by hash or dimensions against `public/assets/photography/**` and `student-go-assets/storage/app/asset-originals/`; download from the bucket only if needed and read-only.
29.H.3. Placement plan: each problem gets photographs from the pool of its first SDG (pool table in the audit, U2); only `available` photos; the five photos withheld by D-42 excluded; captions neutral or from real data (regency and province), never claims such as 'Foto survei lokasi'; SDG 3, 5 and 7 use the region pool with the caption 'Gambaran wilayah'.
29.H.4. Write method (new seeder or one-off command), whether Supabase Storage needs a write or only rows change, backup before writing (CSV in the scratchpad, not in the repository) and restore procedure; impact on existing tests.
29.H.5. Report the plan and stop.

**Stage 2 (only after the user explicitly approves the stage 1 plan and D-46 is answered).**

29.H.6. Take the backup, apply the plan exactly, verify on `/student/browse-problems?view=grid` and three problem details, keep the restore procedure in the report.
29.H.7. F.0 closing steps (no view is rewritten; the view-related boxes are N/A).

## G. Components

| Component | Source | Packages |
|---|---|---|
| Page header, sectioned form, field, upload/dropzone, file chip, stepper, status badge, empty state, confirm dialog, table/list | Phase 3 `<x-ui.*>` (`COMPONENT_INVENTORY.md`) | A–F |
| Stage meter | Phase 19 `institution/projects/components/stage-meter.blade.php` | B, D |
| Organisation profile `.sg-org*` | Phase 5 `public-pages.css`, Phase 25 `company.css` | E, G |
| Job wizard steps and preview | Phase 23 `company/jobs/create.blade.php`, `company/jobs/components/` | E |
| Status words | `App\Support\StatusVocabulary` (Phase 3) | A, B, C, D, E |
| Icons | Lucide sprite via `<x-icon>` | all |
| Region map partial | new in package G (inline SVG, pattern of Phase 4) | G |

## H. Assets (Source A existing `public/`, Source B custom package, Source C usage)

> Policy AD-09. Only assets that already exist in `public/` and `student-go-assets/` are used. **No new asset, no download, no generated asset** in any package (package H stage 1 may read files from the bucket only to compare them). Nothing is deleted, overwritten, moved or renamed; an asset that stops being referenced is recorded as Deprecate in `ASSET_INVENTORY.md` and stays on disk.

### H.1 Source A: existing application assets that matter in this phase

| Asset | Decision | What happens in this phase | Evidence |
|---|---|---|---|
| `company-profile.jpg` | Deprecate (usage) | Last reference (`company/profile/public.blade.php:8`) removed in package E | VERIFIED (grep) |
| `company3.jpg` | Deprecate (usage) | Last reference (`company/jobs/edit.blade.php:9`) removed in package E | VERIFIED (grep) |
| `placeholder-project.jpg`, `placeholder-profile-cover.jpg` | — (files do not exist) | References removed in package F (C6) | VERIFIED |
| `company5.jpg` | Retain | Used by the unreachable `company/talents/show` (C7); not touched | VERIFIED (audit) |
| Company and institution logos (uploads) | Retain | Shown if present, initials otherwise | VERIFIED: 0 uploaded in the seed |
| Problem gallery uploads (`problem_images`) | Retain until D-46 | Changed only by package H stage 2 | VERIFIED (220 rows) |
| `public/assets/certificate/**` | Protected (D-32) | Not touched (package B) | VERIFIED |

### H.2 Source B: custom package assets available for this phase

- Icons from the sprite: `globe`, `phone` (E, and G if a contact row is shown), `shield-check` (D); `loader-circle` only where a button has a real processing state.
- Empty-state illustrations: `no-documents`, `no-applications`, `no-saved-projects`, `no-search-results` (F, U6).
- `illustrations/geographic/indonesia-regions.svg` (G).
- Catalogue photographs: only package H, only the 37 `available` minus the five of D-42.

### H.3 Reconciliation for this phase

No overlap between sources: Source A corporate photos are deprecated with no Source B replacement (`ASSET_INVENTORY.md` section 11 row 7: 'No photo'). Not used, by decision: `workflows/*.svg`, `indonesia-outline.svg` (D-48), the 14 `available-with-care` and 9 `held` photos (D-41), any catalogue photo in A–G (D-43, D-44, D-45).

### H.4 Native UI (no asset; plain HTML/CSS)

- Forms, stage meter, tables, initials avatars, the region map's text equivalent.

### H.5 Do not touch in this phase

- Certificate template and fonts (D-32); student photographs and uploads, logos, verification documents (D-04, D-31); every file under `public/` (rule 11); `student-go-assets/` originals.

## I. Dependencies

- **Phases:** hard — Phase 1, Phase 3; soft — per package (header table). Phase 27 now also depends on Phase 29.
- **Decisions:** D-41 to D-48 (2026-11-01), D-09 (wording frozen), D-25 (behaviour fixes; C1/C2 approved by the user for this phase), D-30 (no existing photograph gets a new slot), D-32.
- **Packages:** No new composer or npm package.
- **Data / environment:** seeded test database (D-12); a student with applications and a project, an institution with projects and a verification record, a company with jobs (all present in the seed per the audit). No admin account (D-47).

## J. Functionality preservation

The global rules in `CLAUDE.md` section 7 apply in full. For this phase specifically:

- Route count before and after each package is identical (203 on `92b9103`); no route name, URI, verb, middleware or parameter changes.
- Every form keeps `action`, method (including `@method('PUT')` on job edit and every spoofed verb on the manage page), `enctype`, CSRF and every input `name`/`id` read by JS or the controller.
- Ownership: other students' application IDs still 404; other institutions' projects and other companies' jobs keep their current response.
- C1 and C2 are the only behaviour changes; both get a failing test first.
- D-09 wording on the verification status page is unchanged.

## K. Testing plan

**Standard checks (every package)**

- Viewport matrix: 360, 390, 768 and 1440 px are mandatory for this phase (1024 and 1366 recommended). At each width `document.documentElement.scrollWidth <= window.innerWidth`.
- State matrix for every changed page: default, empty, long content (76-character institution name, 65-character title, 51-character email), missing media, validation error, role variants.
- Keyboard, contrast, reduced motion, one `<h1>`, `<html lang="id">`.
- Console and network: no JS error, no 404 for an asset.
- Automated: `php artisan test` before and after (pass counts), `npm run build`.
- Visual: before and after screenshots of every changed page at the four widths, stored with the package report.

**Phase-specific checks**

- Form diff: extract forms and field names from the rendered HTML before and after; `diff` must be empty except for removed decorative markup (no attribute of a form or field changes).
- Route diff: `php artisan route:list --json` before and after; identical.
- Asset hashes: if `baseline/asset-hashes.txt` exists on the branch in use, compare against it (expect `public/student-go-logo.png` missing, deleted by the user before Phase 4, and everything else identical). It is not present on `chore/project-assets` @ `92b9103`; then compare the SHA-256 list taken in 29.0.3 with the one taken after the package (every line identical) and say in the report which method was used.
- English label probe: search the rendered HTML of each changed page for the English labels named in the audit (C8 list and package-specific lists) and for 'Laravel'.
- Style and hex probe: `grep -c '<style'` and a hex-colour regex over each rewritten view: 0 and 0.

## L. Acceptance criteria

### L.0 Phase status rule

The phase row stays **PARTIAL** after the first package completes and until every **approved** package (A–G, plus H if D-46 is approved) is COMPLETE; then it becomes COMPLETE. A package that is not approved (H today) is listed in the Notes column as AWAITING APPROVAL and does not block COMPLETE unless the user approves it. Each package is COMPLETE only if every box below for that package is checked with evidence.

### L.A Package A

- [ ] Route count before = after (203 on `92b9103`).
- [ ] `action`, method, `enctype` and every input `name`/`id` of the application form (`create`) and of the cancel form on `show` are unchanged, shown by a diff.
- [ ] Test suite passes; new tests in `tests/Feature/Phase29/` for package A.
- [ ] `npm run build` passes.
- [ ] 0 `<style>` blocks and 0 hex values in `tracker`, `show`, `create`.
- [ ] No horizontal overflow at 360, 390, 768, 1440 px on the three pages.
- [ ] Before and after screenshots of the three pages.
- [ ] No English label left ('Application Tracker', 'Rejected', 'Pending', 'Shortlisted', 'Applied' and any other found).
- [ ] Every status comes from `StatusVocabulary`; the tracker uses the same words as `/student/dashboard` and `/student/applications`.
- [ ] 'Aktivitas Minggu Ini' removed or replaced by a number from a real query (guardrail 2).
- [ ] `public/` hashes unchanged (method stated; `student-go-logo.png` accounted for).
- [ ] Every asset that stopped being referenced recorded as Deprecate in `ASSET_INVENTORY.md`, not deleted (none expected).

### L.B Package B

- [ ] Route count before = after.
- [ ] `action`, method, `enctype` and every input `name`/`id`, `accept` and size hint of both report forms unchanged, shown by a diff.
- [ ] Test suite passes; new package B tests.
- [ ] `npm run build` passes.
- [ ] 0 `<style>` blocks and 0 hex values in `show`, `create-report`, `create-final-report`.
- [ ] No horizontal overflow at 360, 390, 768, 1440 px.
- [ ] Before and after screenshots.
- [ ] No English label left ('Overdue', '1 day ago' and any other found).
- [ ] Statuses from `StatusVocabulary`.
- [ ] Gradient progress bar replaced by the stage meter ('x dari y tahap', from milestones); stored percentage not shown.
- [ ] Certificate control: every one that existed on the branch is still present and works (or 'none existed', with the grep as evidence); certificate files untouched.
- [ ] `public/` hashes unchanged.
- [ ] Assets no longer referenced recorded as Deprecate (none expected).

### L.C Package C

- [ ] Route count before = after.
- [ ] Apply, save and compare forms/links unchanged (`action`, method, `enctype`, names/ids), shown by a diff.
- [ ] Test suite passes; new package C tests.
- [ ] `npm run build` passes.
- [ ] 0 `<style>` blocks and 0 hex values in `student/jobs/show`.
- [ ] No horizontal overflow at 360, 390, 768, 1440 px (C3: 399 px at 390 px before).
- [ ] Before and after screenshots of `/student/jobs/363` and `/573`.
- [ ] No English label left ('Cover Letter', 'Skills Yang Dibutuhkan', 'SDG Alignment', 'Diposting 1 day ago', 'views', 'Internship').
- [ ] Statuses and job type from `StatusVocabulary` (or one documented mapping where no domain exists).
- [ ] `public/` hashes unchanged.
- [ ] Assets no longer referenced recorded as Deprecate (none expected).

### L.D Package D

- [ ] Route count before = after.
- [ ] Every milestone, report and status form on the manage page unchanged (`action`, method, `@method`, `enctype`, names/ids), shown by a diff.
- [ ] Test suite passes; new package D tests.
- [ ] `npm run build` passes.
- [ ] 0 `<style>` blocks and 0 hex values in `manage` and `verification/status`.
- [ ] No horizontal overflow at 360, 390, 768, 1440 px.
- [ ] Before and after screenshots (manage 118, 188, 217; status page).
- [ ] No English label left, except the D-09 'AI' wording, which is unchanged word for word.
- [ ] Statuses from `StatusVocabulary` (any additive entry named in the report).
- [ ] `shield-check` icon on the status page.
- [ ] No status of the test account's projects or milestones changed.
- [ ] `public/` hashes unchanged.
- [ ] Assets no longer referenced recorded as Deprecate (none expected).

### L.E Package E

- [ ] Route count before = after.
- [ ] Job edit form unchanged (`action`, `PUT`, `enctype`, every name/id) and prefilled, shown by a diff; the public profile has no form (or its forms are unchanged).
- [ ] Test suite passes; new package E tests.
- [ ] `npm run build` passes.
- [ ] 0 `<style>` blocks and 0 hex values in `company/profile/public` and `company/jobs/edit`.
- [ ] No horizontal overflow at 360, 390, 768, 1440 px (C3: 490 px at 390 px on edit before).
- [ ] Before and after screenshots (`/company/1`, `/77`, `/120`; edit 619 or 623).
- [ ] No English label left ('Internship' and any other found).
- [ ] Statuses from `StatusVocabulary`.
- [ ] Public profile on `.sg-org`, no photo hero, logo or initials, `globe` and `phone` icons; no field beyond the old page and the institution profile.
- [ ] Edit uses the same wizard partials and stepper as create.
- [ ] Any live edit restored exactly, with proof.
- [ ] `public/` hashes unchanged.
- [ ] `company-profile.jpg` and `company3.jpg` recorded as Deprecate (usage) in `ASSET_INVENTORY.md`, files still present.

### L.F Package F

- [ ] Route count before = after.
- [ ] Forms on every touched page (login, company dashboard, the five empty-state pages, friends pages) unchanged, shown by a diff.
- [ ] Test suite passes; new package F tests, with C1 and C2 tests shown red before the fix and green after.
- [ ] `npm run build` passes.
- [ ] 0 `<style>` blocks and 0 hex values in every view the package rewrites (layouts are edited, not rewritten: no new `<style>` or hex added there).
- [ ] No horizontal overflow at 360, 390, 768, 1440 px on the touched pages, including `/company/dashboard` (C3 table scrolls inside its wrapper).
- [ ] Before and after screenshots (C4 pages, login error, company dashboard at 390 px, five empty states).
- [ ] No English label left on the touched parts.
- [ ] Statuses from `StatusVocabulary` where the package shows one.
- [ ] C1: unknown application ID → 404; another student's ID → 404.
- [ ] C2: `/student/friends/activities` no longer 500; route unchanged.
- [ ] C4: no gap under the footer on the five measured pages at 1440×900.
- [ ] C5: error block aligned, sentence capitalised.
- [ ] C6: no `placeholder-*.jpg` reference left; no image file added.
- [ ] U6: five page-level empty states show their illustration; tab/sub-section ones unchanged.
- [ ] `public/` hashes unchanged.
- [ ] Assets no longer referenced recorded as Deprecate (none expected).

### L.G Package G

- [ ] Route count before = after.
- [ ] Forms on `/institution/{id}` (if any) unchanged, shown by a diff.
- [ ] Test suite passes; new package G tests including the 38-code mapping unit test.
- [ ] `npm run build` passes.
- [ ] 0 `<style>` blocks and 0 hex values in the changed view and the new partial.
- [ ] No horizontal overflow at 360, 390, 768, 1440 px.
- [ ] Before and after screenshots of `/institution/33`, `/56`, `/59`, `/61`.
- [ ] No English label left.
- [ ] Statuses from `StatusVocabulary` (unchanged from Phase 5).
- [ ] Active groups marked `.is-active`; text equivalent present; every number from a real query; no '0'.
- [ ] No data beyond today's public data; `indonesia-regions.svg` byte-identical.
- [ ] `public/` hashes unchanged.
- [ ] Assets no longer referenced recorded as Deprecate (none expected).

### L.H Package H

Stage 1:
- [ ] No write to the database, storage, `public/` or code (`git status` and a row count before/after as evidence).
- [ ] Plan covers trace, matching, placement per SDG pool, captions, write method, backup and restore, test impact.
- [ ] Only `available` photos, the five of D-42 excluded.

Stage 2 (only after approval):
- [ ] Route count before = after.
- [ ] No form changed (no view rewritten).
- [ ] Test suite passes; new package H test (for example: no generic claim caption in seeded rows, only allowed photo paths).
- [ ] `npm run build` passes.
- [ ] 0 `<style>` / 0 hex: N/A (no view rewritten).
- [ ] No horizontal overflow at 360, 390, 768, 1440 px on the gallery and three problem details.
- [ ] Before and after screenshots of the gallery and three details.
- [ ] No English caption.
- [ ] Statuses: N/A (none shown by the package).
- [ ] Backup taken and restore procedure tested or documented.
- [ ] `public/` hashes unchanged.
- [ ] Catalogue photos newly used recorded in `ASSET_INVENTORY.md` (Reuse); nothing deleted.

## M. Risks and blockers

| Risk or blocker | Mitigation / what unblocks it | Blocks the phase? |
|---|---|---|
| Soft-dependency phases read NOT_STARTED in the tracker although reports exist | Warn and ask (protocol step 4); correcting those rows is a separate task | No (asks per package) |
| `baseline/asset-hashes.txt` missing | Before/after SHA-256 list per package (K) | No |
| Phase 10 has no completion report; `show`/`create` are half migrated | 29.A.1 inventory before editing | No |
| Certificate button differs per branch | 29.B.3: keep what exists, add none | No |
| D-09 OPEN | Wording frozen; ask if the design needs it | Partially (D wording) |
| C2 cause unknown; logs may contain connection details | Diagnose by test and code reading; never copy log content | Partially (F) |
| C4 changes the shared shell | Regression screenshots across roles | No |
| Seed has 5 provinces only | 38-code unit test (G) | No |
| D-46 not approved | Package H stage 2 not run | Yes for H only |
| No admin account | D-47; admin views out of scope | No |

## N. Expected deliverables

- Per package: rewritten views, additive CSS, tests in `tests/Feature/Phase29/`, a package report with evidence for every box of its section L (route diff, form diff, test counts, build output, overflow table, screenshots before/after, hash comparison, English-label probe), and a new section in `ASSET_INVENTORY.md`.
- `student-go-docs/design-execution/PHASE_TRACKER.md`: the package's status in the Phase 29 Notes column; the phase status per L.0; date; branch/commit only if the user asked for a commit.
- A report in the chat: files changed, evidence, deviations, decisions needed, and which packages remain. Then stop; do not start another package.
