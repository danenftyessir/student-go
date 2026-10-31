# PHASE 18 — Institution Project Management: completion report

| Field | Value |
|---|---|
| Phase | 18 — Institution Project Management (`phases/PHASE_18_INSTITUTION_PROJECT_MANAGEMENT.md`): problem list, create, detail, edit |
| Session | Session B (institution workspace), in parallel with Sessions A (student) and C (company) on the same working copy. Continued from the Session B handoff in the `Mirror/StudentGo` folder |
| Date | 2026-10-31 |
| Branch / commit | No commit by this session (D-27). At session start the folder was on `main` @ `0a06850`. During the session someone else switched it to `docs/project-documentation` @ `c1b1621`. This session ran no state-changing git command. Every Session B file is still untracked and intact (checked with `git status` at the end) |
| Recommended status | **COMPLETE**: all 6 acceptance criteria met (section 9.1). `PHASE_TRACKER.md` was **not** edited (user instruction for parallel sessions); the proposed row is in section 13 |
| Prerequisites | Phase 3 (hard) COMPLETE; Phase 1 (soft) COMPLETE; Phase 17 (soft) done (report recommends COMPLETE) |
| Gates | **D-02** answered (status words from `StatusVocabulary`). **D-15** answered (a): no region data change. Selects must tolerate long names and have abort, loading and `aria-live`. **D-26** OPEN: no preview rail (user instruction). **D-13** OPEN: seeded data is enough for detail and edit; other statuses are covered by feature tests |
| Evidence folder | `reports/phase18/`: `before/` (24 captures + `metrics_before.json`), `after/` (36 captures + `metrics_after_populated.json`, `metrics_after_empty.json`), `checks/` (interaction, a11y, stored draft row, delete), `tools/` (capture and check scripts; GET only except the approved draft create and delete) |

## 1. Executive summary

The four institution problem pages were rebuilt on the shared shell and components:

- **List** (`/institution/problems`, I02): a seven-column table (Judul · Status · Pelamar · Kuota · Tenggat · Diperbarui · Aksi) with shared status words, a filter bar that keeps `search`, `status` and `sort`, and separate empty and no-match states.
- **Create and edit** (I06): one six-section brief editor. It has a section navigation with progress, and server and browser errors are mapped to their sections. All 37 create inputs keep their names and types. Province to regency uses the shared race-safe `sgRegions`.
- **Detail**: a brief layout (text on the left, metadata rail on the right) with the real accepted count.

Three behaviour fixes were approved by the user and recorded in `DECISIONS_AND_OPEN_QUESTIONS.md` ('Note on Phase 18 behaviour fixes'):

- `address` is saved as `detailed_location`;
- `sort=most_applied` works;
- the edit form loads regencies from the existing `/api/regencies/{id}` (the old URL always answered 404).

Testing in this session found and fixed three defects in Phase 18's own new code (section 9.3).

Tests: Phase 18 9/9, full suite **185/185** (2,058 assertions). Live checks:

- no overflow at six widths on all pages (36 captures);
- keyboard section navigation works;
- rapid province changes end on the last province's regencies;
- a blocked regency API shows an error and recovers through 'Coba lagi';
- a draft created live with every section filled was stored exactly as entered, then deleted through the shared confirm dialog.

## 2. Scope

In scope: `resources/views/institution/problems/{index,create,edit,show}.blade.php`, two new partials in `problems/components/`, a section in `resources/css/student-go/institution.css`, and the approved changes in `Institution\ProblemController`. Out of scope and untouched: routes, middleware, validation rules, `update()`/`destroy()`, `StatusVocabulary`, `<x-ui.*>`, `components.css`, `tokens.css`, layouts, student and company files, and the orphan views `resources/views/institution/{create,index,show}.blade.php` (not referenced by any controller).

## 3. Initial condition (before, `reports/phase18/before/`)

- The list was a card grid without a table. Status and labels were English ('Draft', 'Open', 'In Progress', 'Completed'), and the filter offered `most_applied`, which the controller ignored (it fell back to 'latest').
- The create form was one long page with 37 inputs, English labels ('Requirements', 'Deliverables', 'Beginner/Intermediate/Advanced'), and an `alert('Pilih minimal 1 kategori SDG!')`. The `address` text was posted but never stored. After a validation error the regency list was empty.
- The edit form fetched regencies from `/institution/problems/regencies/{id}` (404). The delete-image checkboxes were `hidden` (not keyboard reachable).
- List and detail used `via.placeholder.com` (hot-linked) as image fallbacks.
- No page-level overflow was measured before (the before metrics show none at the six widths).

## 4. Input inventory (37 create inputs) and section mapping

Baseline names and types from `before/metrics_before.json`, compared with the after capture (`after/metrics_after_*.json`, `name:type`):

- **Create:** 37 = 37, diff empty.
- **Edit:** 50 = 50, diff empty (this problem has 4 images, 4 skills, 3 deliverables and 3 facilities).

The feature test `test_create_form_keeps_all_37_inputs_and_six_sections` asserts the same inventory.

| Section | Inputs (names unchanged) |
|---|---|
| 1 Informasi dasar | `title`, `description`, `background` (+ `scope` on edit) |
| 2 Lokasi | `province_id`, `regency_id` (`sgRegions`), `address` (+ `village`, `detailed_location` on edit) |
| 3 Sasaran & SDG | `objectives`, `expected_outcomes`, `deliverables[]`, `sdg_categories[]` ×17 |
| 4 Kebutuhan & keterampilan | `required_students`, `difficulty_level`, `required_skills[]`, `required_majors[]`, `facilities_provided[]`; Jadwal: `application_deadline`, `duration_months`, `start_date`, `end_date` |
| 5 Media | `images[]` (id `images-input`, `new-images-input` on edit; `accept="image/*"`, multiple) (+ `delete_images[]` on edit) |
| 6 Status | `status` (create: draft/open; edit: draft/open/closed) |

Form `action`, `method` (POST, `@method('PUT')` on edit), `enctype="multipart/form-data"`, `id="problemForm"` (create) and the CSRF token are unchanged. Dynamic list fields add and remove rows client-side under the same `name[]`. The last row is emptied, never removed.

## 5. Implemented features

- **List:**
  - page header with one primary action, 'Buat masalah baru';
  - summary line built from real counts (zero statuses hidden);
  - `x-ui.filter-bar` (`search`, `status`, `sort`: Terbaru, Terlama, Paling banyak pelamar);
  - `x-ui.table`, which stacks to label–value rows under 640 px;
  - the applicant count links to `institution.applications.index?problem_id=`;
  - 'N diterima' comes from a real count, and an open problem past its deadline shows 'Tenggat lewat';
  - `x-ui.pagination`;
  - empty state 'Belum ada masalah', and a no-match state using `no-search-results.svg`.
- **Brief editor:**
  - sticky section navigation (anchors `#bagian-<key>`; Enter or click moves focus to the section `<h2 tabindex=-1>`; `aria-current="step"` follows scrolling);
  - progress 'x dari 6 bagian lengkap', and each step's state (`todo`, `done`, `error`) has a screen-reader suffix;
  - server errors are counted per section in Blade; browser `invalid` events mark the section and are announced through an assertive live region;
  - the SDG minimum is an inline error in section 3 that moves focus to the first SDG box (submit is still blocked as before);
  - the submit label follows the status ('Simpan sebagai draf' or 'Simpan dan buka lamaran');
  - the save bar is sticky, with a top border and no shadow;
  - smooth scrolling is turned off under `prefers-reduced-motion`.
- **Detail:**
  - brief blocks (Deskripsi, Latar belakang, Tujuan, Ruang lingkup, Hasil yang diharapkan, Luaran, Fasilitas) and a photo grid with a 'Sampul' label;
  - the rail shows status, a neutral difficulty meter, quota with the real accepted count, applicants (link), views, schedule, SDG, skills, majors and location detail;
  - 'Ubah masalah', and 'Hapus' through the shared `data-sg-confirm` dialog (route, `DELETE` and the controller's accepted-application guard are unchanged).
- **Edit:** the same editor, with visible 'Hapus foto N' checkboxes and an upload hint that shows how many slots are left.

## 6. I02 / I06 before and after

| Page | Before | After (files in `after/`) |
|---|---|---|
| I02 list | `before/I02_before_{populated,empty}_<w>.png`: cards, English status words | `I02_after_{populated,empty}_<w>.png`: table, 'Dibuka/Draf…', empty state |
| I06 create | `before/I06_before_*`: single long form, English labels | `I06_after_{populated,empty}_<w>.png`: six sections + nav; `checks/I06_regency_error_1366.png` (blocked API state) |
| Detail | `before/I02d_before_*` | `I02d_after_populated_<w>.png`; `checks/I02d_after_created-draft_1366.png`, `checks/I02d_delete_confirm_1366.png` |
| Edit | `before/I02e_before_*` | `I02e_after_populated_<w>.png` |

Widths: 360, 390, 768, 1024, 1366 and 1440. Note on the full-page captures of create and edit: the sticky save bar is drawn at the viewport's bottom position (about 900 px down). This is a full-page screenshot effect, not an overlap in the browser.

## 7. Files created and modified (Phase 18)

| File | T/U | Change |
|---|---|---|
| `app/Http/Controllers/Institution/ProblemController.php` | T | `index()` accepted count + `most_applied` alias; `create()` passes old regencies; `store()` maps `address` → `detailed_location` |
| `resources/views/institution/problems/index.blade.php` | T | Rewritten |
| `resources/views/institution/problems/create.blade.php` | T | Rewritten |
| `resources/views/institution/problems/edit.blade.php` | T | Rewritten |
| `resources/views/institution/problems/show.blade.php` | T | Rewritten |
| `resources/views/institution/problems/components/brief-form.blade.php` | U | New: six-section editor + inline Alpine `sgProblemBrief`, `sgListField` |
| `resources/views/institution/problems/components/list-field.blade.php` | U | New: dynamic list rows |
| `resources/css/student-go/institution.css` | U | Phase 18 section appended (file now 148 lines) |
| `tests/Feature/Phase18/InstitutionProblemsTest.php` | U | New: 9 tests |
| `student-go-docs/design-execution/reports/phase18/{after,checks,tools}/` | U | Evidence |
| `ASSET_INVENTORY.md` §25, `DECISIONS_AND_OPEN_QUESTIONS.md` (D-26 NOTE + 'Note on Phase 18 behaviour fixes') | U | Docs |

## 8. Backend and data integration

- Every number on these pages comes from a query:
  - `applications_count` column (77/77 match the real count in the seed);
  - accepted applications (`withCount`, or the loaded relation on detail), because the `accepted_students` counter is 0 for all seeded problems;
  - `views_count`;
  - status counts.
- The approved behaviour changes are in section 1 and in the decisions note. No migration and no seeder change.
- Writes to the shared database in this phase: one draft (#78, institution #64), created and then soft-deleted (section 9.2). Nothing else.
- Since the handoff, the region data in the shared database has grown to 34 provinces / 514 regencies (not changed by this session). The seeded problem images load from `sg-storage` (Phase 8).

## 9. Testing results

### 9.1 Acceptance criteria (section L)

| Criterion | Result | Evidence |
|---|---|---|
| The list is a table with the seven columns and shared status words | **PASS** | `test_list_is_a_table_with_seven_columns_and_shared_status_words`; `after/I02_after_populated_*.png` |
| Every one of the 37 inputs keeps its name and value flow (inventory diff empty) | **PASS** | name:type diff empty (create 37/37, edit 50/50); `test_create_form_keeps_all_37_inputs_and_six_sections`; `test_store_saves_every_field_and_address_becomes_detailed_location`; live draft #78 (`checks/row78.json`) |
| A user can navigate sections by keyboard and errors are announced | **PASS** | `checks/interaction-checks-partA-final.json` `keyboard` (Tab to step 3, Enter: focus `judul-sasaran`, `aria-current`, hash) and `empty_submit` (sections marked `error`, live region text); `test_validation_errors_are_mapped_to_their_sections_and_regencies_survive` |
| Regency options always match the selected province | **PASS** | `rapid_province`: 4 changes within ~100 ms, options = API answer for the last province (Bengkulu, 10); `blocked_api` + `retry` (error state with 'Coba lagi', then 23 regencies of Aceh); edit uses `/api/regencies/` (test) |
| No horizontal scroll at six widths | **PASS** | 36 captures, `overflow = 0` on all (`after/metrics_after_*.json`) |
| Detail/edit are either designed with data or listed BLOCKED | **PASS: designed with seeded data** (not BLOCKED) | `after/I02d_*`, `after/I02e_*`; tests for detail and edit |

### 9.2 Section K checks

| Check | Result |
|---|---|
| Viewport matrix, 6 widths × 4 pages (populated) + list/create (empty account) | PASS: no overflow, HTTP 200, no JS error |
| States: default, empty (#64, 71-character name), no-match, validation error, missing media (fallback text), role variants | PASS. Long content: the table and rail wrap long titles. A 76-character institution name is not shown on these pages |
| Keyboard: one `<h1>`, `lang="id"`, visible focus through 40 tab stops on each page, no trap | PASS (`checks/a11y-checks.json`) |
| Reduced motion: section scroll is `auto` under `prefers-reduced-motion: reduce` | PASS |
| Contrast: only existing design-system tokens are used (no new colour pair) | PASS by construction; not re-measured |
| Functionality vs baseline: routes, input names, query parameters, redirects, ownership | PASS: role test (guest → login; student/company redirected; another institution's problem → 404 on show, edit and destroy) |
| Console/network: no JS errors; no hot-linked image; no 404 for a new asset | PASS (the only 404 is the deliberate cross-institution ownership check) |
| Automated suite | Before this session's changes: Phase 17 16/16. After: Phase 18 9/9 (98 assertions); **full suite 185/185, 2,058 assertions** |
| Assets: `public/` hash check | 37/38 identical, 0 changed; missing `public/student-go-logo.png` (deleted by the user before Phase 4) |
| Phase-specific: create a draft with every section filled, compare stored values | PASS: draft #78 as institution #64, no image upload (uploads go to Supabase Storage). Every field matched the input (`checks/row78.json`), including `detailed_location` from `address`, lists without blank rows, SDG `[3,4]`, status `draft`. Then deleted through the detail page's confirm dialog (focus starts on 'Batal'). The redirect showed the empty state, detail now answers 404, and `deleted_at` is set (the model uses soft deletes; the row remains as trashed) |
| Phase-specific: validation errors inside the right section, nav indicates them | PASS (test + live) |
| Phase-specific: rapid province change | PASS |
| Phase-specific: 390 px layout of the long form | PASS: one column; the section nav becomes a two-column grid above the form (`after/I06_after_*_390.png`) |

### 9.3 Defects found and fixed during testing (Phase 18's own new code)

1. **Progress wrong on edit** ('5 dari 6'). `refresh()` ran before `sgRegions` set the saved regency. Fixed by a second `refresh()` after the next tick.
2. **Sections marked as errors without user action.** `refresh()` used `checkValidity()`, which fires `invalid` events, so typing in one field marked other sections as errors. Replaced with `validity.valid`.
3. **Section links did nothing on Enter or click.** `go()` used `this.$el`, which in Alpine 3 is the clicked link inside an `@click` handler, so the section was never found. The component now stores its root in `init()`.
4. **Stretched controls in two-column grids.** The province select was 54 px instead of 41 px, and 'Durasi' was taller: grid stretch inside `.sg-field`. Fixed with `align-items: start` on `.sg-inst-grid-2`.

The Phase 17 failure (15/16) mentioned in the handoff did not reproduce in this folder: 16/16.

## 10. Known limitations

- **D-26 open:** no live preview rail, no new server sort.
- `scope`, `village` and `detailed_location` still have no input on the create form (validated by `store()`; unchanged).
- The browser's native validation message is in the browser's language. The headless test browser was English ('Please fill out this field.'); an Indonesian browser shows Indonesian.
- SDG labels come from the shared `sdg_label()` helper in Title Case ('Kehidupan Sehat Dan Sejahtera'). The helper is shared and was not changed.
- On phones the status chip in the stacked table rows stretches across the cell (shared table CSS).
- Success flashes are 5-second toasts. On the slow shared dev server the delete toast had already disappeared when it was checked; the create toast was seen.
- `store()`/`update()` call `DB::rollBack()` before any `beginTransaction()` on validation errors. Harmless in production, but in tests it undoes `RefreshDatabase` (the test is written around it). Not changed.
- Test-only: with `session.serialization = json`, an error bag must be put into the session in its marshalled array form in feature tests.

## 11. Shared file impact and conflict risks

- `resources/css/student-go/institution.css` is Session B's own file (imported once in `app.css` since Phase 17). This phase added `align-items: start` to `.sg-inst-grid-2`, which only Phase 18 uses.
- `ProblemController` is institution-only. No shared component, token, layout, helper or route was changed in this phase.
- `ASSET_INVENTORY.md` and `DECISIONS_AND_OPEN_QUESTIONS.md` are shared docs. Additions were appended (§25; D-26 NOTE; a new note at the end). The handoff planned §23, but §23 and §24 had already been taken by Phases 21 and 8.
- Branch: the working copy changed branch during the session (see the header). Session B's files are untracked and are not part of any commit yet.

## 12. Handover for Phase 19 (Institution Review & Monitoring)

- The slot check in `ApplicationReviewController@accept` still uses `problems.accepted_students`, which is 0 in the seed although accepted applications exist. Phase 19 should decide whether to count applications (ask the user first: behaviour change).
- The list links each applicant count to `institution.applications.index?problem_id=<id>`. Keep that parameter.
- Status words for applications are already fixed to `reviewed` (Phase 17 addendum).
- Reusable pieces: `sgRegions` (shared, `auth.js`), the `.sg-inst-*` rules in `institution.css`, the scripts in `reports/phase18/tools/` (set `SG_PW`, accounts and pages through env; paths without a leading slash in Git Bash).

## 13. Proposed tracker row (for the integration owner)

| 18 | Institution Project Management | COMPLETE | 3 | 1, 17 | D-02, D-15, D-26, D-13 | 2026-10-31 | — (no commit) | 6/6 criteria; suite 185/185; report `completion-reports/PHASE_18_COMPLETION_REPORT.md` |
