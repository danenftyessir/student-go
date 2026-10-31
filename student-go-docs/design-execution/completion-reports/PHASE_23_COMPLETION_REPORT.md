# PHASE 23 — Company Job Management: completion report

| Field | Value |
|---|---|
| Phase | 23 — Company Job Management (`phases/PHASE_23_COMPANY_JOB_MANAGEMENT.md`) |
| Session | Session C (company workspace), in parallel with Session A (Phase 9) and Session B (Phase 19), same working directory |
| Date | 2026-10-31 |
| Branch / commit | Working copy as assigned (`main` per `git status`; the whole tree is untracked under one commit `0a06850`). No branch, commit, stash or worktree (D-27) |
| Recommended status | **COMPLETE**: all 5 acceptance criteria met with evidence (section 13.1). `PHASE_TRACKER.md` not edited; proposed row in section 18 |
| Prerequisites | Phase 3 (hard) COMPLETE in the tracker. Phase 1 (soft) COMPLETE. Phase 22 (soft) recommended COMPLETE in `completion-reports/PHASE_22_COMPLETION_REPORT.md`; its tracker row has not been integrated yet. D-12 database reachable. Company authorisation (`auth` + `check.user.type:company`, ownership by `company_id`) re-verified by tests |
| Gate answers (this session, by the user) | **Table**: apply the existing `?search=`/`?status=`/`?category=` on the server, status tabs, real summary instead of the always-0 tiles. **Wizard**: per-step validation that mirrors the server; **'Rentang gaji' stays optional and is now saved** (mapped to `salary_min`/`salary_max`; backend change approved). **Detail**: record page, invented elements removed. **D-26 (preview rail): approved for this item**, front-end only. D-07 stays OPEN (nothing here depends on it: no scope change, no new flow). D-02 ANSWERED. D-13: the seeded data was enough (625 jobs). Not written into `DECISIONS_AND_OPEN_QUESTIONS.md`; text in section 18 |
| Evidence | `reports/phase23/before/` (C04, C05, detail at 6 widths, 5 wizard steps at 390 px, `metrics_before.json` incl. the recorded wizard POST payload), `reports/phase23/after/` (same set + `C05_after_step5_filled_390.png`, `detail_after_new_job_1440.png`, `metrics_after.json`, `interaction_checks.json`) |

## 1. Executive summary

**`/company/jobs` (C04)** keeps its table and all seven columns.

**Gone:**

- the stock-photo hero;
- four stat tiles that always read 0 (they used a variable the controller never sent);
- an empty Status column (it called accessors that do not exist on `JobPosting`).

**New:**

- a page header with one primary action, 'Pasang lowongan';
- a real summary line, e.g. '8 lowongan · 6 dibuka · 21 pelamar';
- status tabs with real counts;
- a labelled filter bar (search plus kategori; a drawer below 768 px);
- shared status badges and the shared pager.

**Filters now work.** The old form wrote `?search=`, `?status=` and `?category=` to the URL, but the controller ignored them. The status value 'published' never matched the stored 'posted'. The controller now applies all three, using the same parameter names; 'published' is accepted as an alias.

**Empty states stay inside the table.**

**Delete** posts to the same `DELETE /company/jobs/{id}` behind the shared confirm dialog.

**`/company/jobs/create` (C05)** keeps the same five steps in the same order, including **SDG Alignment** and **Kriteria Dampak**.

**Same payload.** The POST payload is byte-for-byte the same list of keys and values as the old wizard's, compared on a recorded and aborted submit. It is still sent to `company.jobs.store`.

**Fixes:**

- The page is no longer 490 px wide at 390 px.
- There is now one `<h1>`, not two.
- Placeholders are local ('Pendamping Koperasi Desa', 'Kabupaten Garut, Jawa Barat'), replacing 'Senior Software Engineer' and 'Remote, Berlin, Germany'.

**Validation.** Before, the browser checked only title and skills, while the server required six more fields and showed nothing on failure. Now:

- every required field is validated on blur and on 'Berikutnya';
- errors stay attached to their step;
- a banner links to each step that needs fixing;
- server errors come back to the right step with the old input restored.

**Salary.** The optional salary text is now stored instead of silently dropped.

**Preview rail.** A rail shows the posting as students will see it, built only from the form state. The existing Preview modal remains for small screens.

**Kept from the old wizard:** templates and auto-saved drafts (`localStorage`). Their `confirm()` prompts now use the shared dialog.

**`/company/jobs/{id}`**: the route exists and seeded data exists (625 jobs), so the page is designed as a record page:

- header with breadcrumbs, 'Ubah lowongan' and 'Lihat pelamar (n)';
- the real status;
- content: description, responsibilities, qualifications, benefits, skills, SDG Alignment, Kriteria Dampak;
- a facts rail built from real columns;
- applicants per stage and the latest applicants;
- the existing LinkedIn/SDG reference form, now in a dialog.

**Removed from the detail page** (none of it was backed by data):

- a hard-coded 'Active' badge;
- 'Invite Candidates', which only showed an `alert()`;
- a 'Guest View' toggle that was never saved;
- a 'Messaged' stat;
- the 'Budget', 'Delivery Time' and 'Positions' facts;
- Share and LinkedIn-share links pointing to `/jobs/{slug}-{id}`, a route that does not exist (404).

## 2. Initial condition (before)

All findings below are VERIFIED by source reading, rendering and read-only queries on 2026-10-31.

| Area | Finding |
|---|---|
| Routes | `company.jobs.index/create/store/show/edit/update/destroy/update-linkedin` (`routes/web.php:362-371`), group `auth` + `check.user.type:company`. Unchanged. |
| Controller | `Company\JobPostingController`. `index()` took no `Request`, ignored every filter, ordered by `created_at` without tie-breaker, `paginate(10)`. `create()` passed English department suggestions, English job types and English SDG names. `store()` validated `title, department, location, job_type, description, responsibilities, qualifications` required and `skills` array min 1, with optional salary_min/max/currency, benefits, sdg_alignment, impact_metrics, success_criteria, status, allow_guest_applications. It **did not read `salary_range`**, defaulted `salary_currency` to 'USD', `status` to 'posted' and `published_at` to now, and `json_encode`d skills/sdg_alignment on top of an `array` cast (double encoding). `show()` checks ownership (`company_id`), **increments `views_count` on every view**, and computes statistics for shortlisted/offer/interview/hired. |
| Table view (C04) | Stock hero `company2.jpg`; 4 tiles reading an undefined `$stats` (always 0); filter selects re-initialised to empty after every reload; status option values `published/draft/closed`; Status column blank (`status_badge_class`/`status_label` accessors absent); delete via `fetch` + `confirm()`/`alert()`; empty state inside the table (kept). |
| Wizard (C05) | Stock hero `company3.jpg` + a second `<h1>` in the preview modal. Stepper of circles with 64-96 px connectors, so the page was 475 px wide at 360 and 490 px at 390. Steps: Detail Lowongan, Deskripsi, Keahlian, SDG Alignment, Kriteria Dampak. No `name` attributes: the payload is built in JS from `formData` (13 keys: `_token`, `title`, `department`, `location`, `job_type`, `salary_range`, `description`, `responsibilities`, `qualifications`, `skills[]`, `sdg_alignment[]`, `impact_metrics`, `success_criteria`). Client validation: title and skills only (skills via `alert()`). No `$errors`/`old()` handling. Draft auto-save and templates in `localStorage` with `confirm()`; Preview modal. Foreign placeholders and English SDG names ('SDG 1: No Poverty'), English job types. |
| Detail view | English throughout; hard-coded 'Active'; `$jobPosting['total_applicants']`, `reviewed_count`, `budget`, `delivery_time`, `individual_hires`, `tags` and `owner` are not columns (so the facts were empty or defaults); fake Invite; Guest View toggle not persisted; Share/LinkedIn share to the non-existent `/jobs/{slug}-{id}`; inline `<style>` with the LinkedIn hex colour. |
| Data (read-only) | 625 job postings (posted 399, draft 105, closed 121); job_type Internship 471 / Contract 151 / internship 3; 33 categories; every job has a category, SDGs, impact text and `views_count` > 0; salaries in IDR with periods `monthly`/`project`. Company #1: 8 jobs (6 posted, 2 draft), 21 applicants. The Phase 21 empty company: 0 jobs. |

## 3. Jobs table implementation (C04)

| Spec item | Done |
|---|---|
| Clear header | `x-ui.page-header` 'Lowongan' + description + primary 'Pasang lowongan' (the same words as the company dashboard). |
| Status tabs | `x-ui.tabs`: Semua · Dibuka · Draf · Ditutup · Diarsipkan. Labels come from `StatusVocabulary` 'job_posting'; counts are real per company. A tab appears only when it has jobs or is the current one. Tabs keep `search` and `category`. |
| Labelled filters | `x-ui.filter-bar`: 'Cari lowongan' (title or category name) + 'Kategori' select; the status is carried as a hidden field; a drawer below 768 px. |
| Seven columns | Judul (link + location) · Kategori · Status (`x-ui.status`) · Pelamar (links to `company.applications.index?job_posting_id=` when > 0) · Dilihat · Tanggal dibuat · Aksi (Lihat, Ubah, Hapus as labelled icon buttons). |
| Empty state inside the table | `<td colspan="7">` with 'Belum ada lowongan' (no jobs; CTA to create) or 'Tidak ada lowongan yang cocok' (filtered; 'Tampilkan semua lowongan'). |
| One scroll container | `x-ui.table`: the rows stack below 640 px (no scroll container needed). From 640 px there is at most one container, and only when the table is wider than the space. Measured: 0 scrolling containers at every width, because the table fits (before: 1 at 360-768). |
| Summary | `x-ui.summary` 'n lowongan · n dibuka · n pelamar', all from queries (zeros hidden). |
| Pager | `x-ui.pagination` with `withQueryString()`; order `created_at desc, id desc` (D-05 tie-breaker added). |
| Delete | A form `DELETE company.jobs.destroy` + `data-sg-confirm` (shared dialog). Same endpoint; the controller's non-AJAX branch redirects with 'Lowongan berhasil dihapus!'. |

## 4. Five-step wizard implementation (C05)

- **Layout.**
  - `x-ui.page-header` 'Pasang lowongan' with a back link and three tertiary actions: Template, Pratinjau (< 1024 px only) and Hapus draf.
  - A draft status line ('Draf tersimpan di peramban ini pukul hh.mm').
  - The form panel, and the preview rail from 1024 px (sticky).
- **Steps.** Same names and order (section 6). Each step is a `<section>` with an `<h2>` that receives focus when the step changes.
- **Fields.** Built from shared markup: `.sg-field`, `.sg-label[data-required]`, `.sg-input`, `.sg-select`, `.sg-textarea`, `.sg-help`, `.sg-error`, `.sg-check`, `.sg-fieldset`.
  - Every field has a `<label for>`.
  - Help and error text are linked with `aria-describedby`.
  - `aria-invalid` and `data-touched` drive the shared invalid style.
- **Navigation.** Sebelumnya (secondary), Berikutnya (primary), and Buat lowongan (primary, last step only) are real buttons. They are keyboard-operable and full width below 640 px.
- **Submission.** The same JS-built POST: `_token`, then every `formData` key in the same order, with arrays as `key[]`. Sent to `company.jobs.store`.
- **Kept local features.** Draft auto-save and templates (`localStorage`), with `window.sgConfirm` instead of `confirm()`, and the Preview modal (`x-ui.modal`).
- **Preview rail (D-26, approved for this item).** `company/jobs/components/preview.blade.php`, shared by the rail and the modal. It shows:
  - title or the placeholder 'Judul lowongan';
  - the company name;
  - location, job type label, department and salary text;
  - description, responsibilities, qualifications and impact text (clamped in the rail);
  - skills and SDG chips;
  - the note: 'Disusun dari isian formulir di peramban ini. Belum ada yang terbit sampai Anda menekan "Buat lowongan".'

  No backend, no storage, no publishing.
- **Honest publish note** on step 5: 'Lowongan langsung dibuka untuk mahasiswa setelah Anda menekan "Buat lowongan"' (`store()` writes `status = posted`).

## 5. Stepper behavior

- **Component.** The shared `x-ui.stepper` with `model="currentStep + 1"`, so the page's own step logic drives it and no new stepper exists.
- **Below 640 px.** A text label 'Langkah 2 dari 5 · Deskripsi' (polite live region) plus a 5-segment progress track; no overflow (390 = 390, 360 = 360).
- **From 640 px.** The step names appear under the track; the current one is bold and `aria-current="step"`.
- **States.** Done segments and the current segment are filled with the brand colour; upcoming segments are grey.
- **Error state.** The shared stepper has no error state, and shared components are off-limits in parallel sessions. So the wizard shows an alert banner above the steps: 'Perlu diperbaiki: Langkah 2 · Deskripsi'. Each entry is a button that opens that step and focuses its first invalid field.
- **Keyboard.** All controls are native buttons; the step heading takes focus on change; there is no trap. Measured: focus visible on every tab stop through the page.

## 6. Field inventory and comparison

| Step | Field (label) | Payload key | Rule (server, unchanged) | Before | After |
|---|---|---|---|---|---|
| 1 Detail Lowongan | Judul lowongan | `title` | required, max 255 | kept | kept |
| 1 | Departemen atau bidang (datalist) | `department` | required, max 100 | kept (English suggestions) | kept (Indonesian suggestions; free text) |
| 1 | Tipe pekerjaan (select) | `job_type` | required, max 50 | values Full-time, Part-time, Contract, Internship, Freelance | **same values**, labels Penuh waktu, Paruh waktu, Kontrak, Magang, Lepas |
| 1 | Lokasi | `location` | required, max 255 | kept | kept |
| 1 | Rentang gaji atau honor (optional) | `salary_range` | not validated, not stored | kept | kept; now `nullable\|string\|max:255` and **stored** (section 9) |
| 2 Deskripsi | Deskripsi pekerjaan | `description` | required | kept | kept |
| 2 | Tanggung jawab | `responsibilities` | required | kept | kept |
| 2 | Kualifikasi | `qualifications` | required | kept | kept |
| 3 Keahlian | 50 checkboxes | `skills[]` | required array, min 1 | kept | kept (same 50 values) |
| 4 SDG Alignment | 17 checkboxes | `sdg_alignment[]` | nullable array | values 1-17 | values 1-17, labels 'SDG n · ' + `sdg_label()` |
| 5 Kriteria Dampak | Metrik dampak | `impact_metrics` | nullable string | kept | kept |
| 5 | Kriteria keberhasilan | `success_criteria` | nullable string | kept | kept |
| — | CSRF | `_token` | — | from Blade | from `<meta name="csrf-token">` (same value) |

**Recorded payload comparison.** The same sample was filled into the page's own Alpine state, `submitForm()` was called, and the POST was intercepted and aborted, so nothing was saved:

- before: 15 pairs, 13 keys;
- after: 15 pairs, 13 keys;
- **identical in order and values** (`metrics_before.json` / `metrics_after.json` → `payload`).

Test `test_wizard_keeps_five_steps_payload_and_local_placeholders` asserts the key set against this baseline and the step order.

## 7. Validation behavior

- **Rules.** The client rules in the page script mirror the server rules: required fields, max lengths, and at least 1 skill. The backend validation is untouched; one rule (`salary_range`) was added.
- **On blur.** Each field is checked when it loses focus; a select is also checked on change.
- **'Berikutnya'.** It checks the whole current step. If anything fails, it stays on the step and moves focus to the first invalid field.
- **Errors persist.** Errors stay on their step when the user moves between steps; they clear only when the field becomes valid. The banner lists every step with open errors.
- **'Buat lowongan'.** It checks all five steps and jumps to the first one with an error.
- **Server errors.**
  - When the server rejects the POST, it redirects back and the wizard starts with `old()` input.
  - Each server error is mapped to its field (`skills.0` → `skills`) and its step, in Indonesian: the client message is used when one applies, otherwise the server text.
  - The first step with an error opens.
  - The draft prompt is skipped in this case.
- **Live check (local DB).**
  - The server-error path was triggered with `department` missing.
  - It returned to step 1 with 'Departemen wajib diisi.' visible and the title restored.

## 8. SDG Alignment and Kriteria Dampak

- **Both steps keep their names, positions (4 and 5), field names and value flow.** SDG values are the strings '1'-'17', as before. There is no new algorithm and no scoring.
- **SDG labels.** Indonesian, from the shared `sdg_label()` (the list the problems and the Phase 22 talents use), shown as neutral chips with no colour fill.
- **Help and placeholders.** The impact fields have help text and local example placeholders (koperasi members, monthly reports).
- **Detail page.** Shows both as their own sections ('SDG Alignment' chips; 'Kriteria Dampak' as Metrik dampak / Kriteria keberhasilan).
- **Required SDGs.** The separate `required_sdgs` (set by the LinkedIn reference form) is shown as 'SDG wajib bagi pelamar'.

## 9. Local placeholder changes and salary mapping

| Field | Before | After |
|---|---|---|
| Judul | cth: Senior Software Engineer | cth: Pendamping Koperasi Desa |
| Departemen | cth: Engineering (+ English datalist) | cth: Pemberdayaan Masyarakat (+ Indonesian datalist) |
| Lokasi | cth: Remote, Berlin, Germany | cth: Kabupaten Garut, Jawa Barat |
| Gaji | cth: Rp 15.000.000 - Rp 25.000.000 per bulan | cth: Rp 3 juta - 4,5 juta per bulan (+ help on accepted formats) |
| Deskripsi / Tanggung jawab / Kualifikasi | generic / village examples | koperasi digitalisation examples |
| Metrik dampak | cth: Peningkatan efisiensi sistem sebesar 20% | cth: 30 anggota koperasi mencatat penjualan hasil panen secara digital setiap pekan |
| Kriteria keberhasilan | cth: Mampu deliver minimal 2 fitur utama… | cth: Laporan keuangan bulanan koperasi tersusun tanpa bantuan pendamping pada bulan ketiga |
| Template name | cth: Senior Engineer Template | cth: Template pendamping koperasi |

All placeholders are marked 'cth:' and name no real organisation. Kabupaten Garut and Jawa Barat are used only as place-name examples.

**Salary mapping** (approved). `store()` calls a new private `parseSalaryRange()`:

- **Amounts.** The first amount becomes `salary_min` and the second `salary_max`; a single amount fills both. Thousand separators ('3.000.000') and decimals ('3,5') are understood, as are the words 'juta'/'jt' and 'ribu'/'rb'/'k'.
- **Small numbers.** Numbers under 1,000 are ignored when a larger amount is present, so '3 bulan' is not read as pay.
- **Currency.** IDR, unless 'USD' or '$' appears.
- **Period.** 'bulan' → `monthly`, 'proyek' → `project` (the values already in the data).
- **Fallback.** Text that cannot be read, or an empty field, leaves the columns null, and the job is still saved.

Checked cases:

| Input | Result |
|---|---|
| 'Rp 3.000.000 - Rp 4.500.000 per bulan' | 3,000,000-4,500,000 IDR monthly |
| 'Rp 3,5 juta' | 3,500,000 |
| '2 jt - 3 jt / proyek' | 2,000,000-3,000,000 project |
| 'negosiasi' | null |
| 'USD 500 - 800' | USD |
| 'Rp 4.500.000 - 3.000.000' | swapped to min/max |
| 'Rp 5 juta untuk 3 bulan' | 5,000,000 |

An explicit `salary_min`/`salary_max`/`salary_currency` in the request still wins, which is the old behaviour.

## 10. Job detail route verification

- **Route.** VERIFIED: `GET company/jobs/{id}` → `JobPostingController@show`, ownership enforced (`where company_id`, `firstOrFail` → 404 for another company's job; test).
- **Data.** 625 seeded jobs, so the page is **designed, not BLOCKED**.
- **Page structure.**
  - Breadcrumbs and `<h1>`.
  - Real status badge, category and creation date.
  - Content sections.
  - Facts rail: departemen, tipe, lokasi, gaji, diterbitkan, berakhir, dilihat, pelamar.
  - Applicants per stage: `StatusVocabulary` 'job_application' labels, equal labels summed (pending + new = Menunggu), unknown values counted as 'Lainnya'.
  - The 5 latest applicants (`display_name`, linked to the existing `company.applications.show`).
  - 'Lihat semua pelamar' → `company.applications.index?job_posting_id=` (existing filter).
  - The LinkedIn/SDG reference form, unchanged (`PATCH company.jobs.update-linkedin`, `linkedin_url`, `sdg_goals[]`) and now pre-checked with the stored `required_sdgs`. Its validation errors show on the page after the redirect (`x-ui.form-errors`).
- **Behaviour kept.** `show()` still increments `views_count` on every view (section 15).

## 11. Files created and modified

| File | Change |
|---|---|
| `app/Http/Controllers/Company/JobPostingController.php` | `index(Request)`: filters, tab counts, total applicants, `id` tie-breaker, `withQueryString()`. `create()`: Indonesian department suggestions, job-type value⇒label map, SDG names from `sdg_label()`. `store()`: `salary_range` rule + mapping (min/max/currency/period). `show()`: `stageCounts`, `jobTypeLabels`. New constants `STATUSES`, `JOB_TYPE_LABELS`; new private `parseSalaryRange()`. **`edit`, `update`, `destroy`, `updateLinkedin` identical** to the original (diffed). |
| `resources/views/company/jobs/index.blade.php` | Rewritten (266 → 121 lines). |
| `resources/views/company/jobs/create.blade.php` | Rewritten (740 → 493 lines). |
| `resources/views/company/jobs/show.blade.php` | Rewritten (605 → 201 lines). |
| `resources/views/company/jobs/components/preview.blade.php` | **New** partial (rail + modal). |
| `resources/css/student-go/company.css` | Session C file: **Phase 23 section appended** (`.sg-co-jobs*`, `.sg-co-wizard*`, `.sg-co-preview*`, `.sg-co-job*`). |
| `tests/Feature/Phase23/CompanyJobManagementTest.php` | **New**: 10 tests, 137 assertions. |
| `student-go-docs/design-execution/ASSET_INVENTORY.md` | **Shared, append only**: section 28. |
| `student-go-docs/design-execution/reports/phase23/` | New evidence. |
| `student-go-docs/design-execution/completion-reports/PHASE_23_COMPLETION_REPORT.md` | This file. |
| `public/build/` | Regenerated (git-ignored). |

Not touched: `routes/*`, middleware, models (incl. `JobPosting`), migrations, seeders, `.env`, layouts, navbar, footer, `tokens.css`, `components.css`, `app.css`, `<x-ui.*>` (including the shared stepper), global JS, `company/jobs/edit.blade.php` (not in this phase's route list), `company/applications/*` (Phase 24), `PHASE_TRACKER.md`, `DECISIONS_AND_OPEN_QUESTIONS.md`.

## 12. Before/after screenshots C04 and C05

| Page | Before | After |
|---|---|---|
| C04 table (company #1) | `before/C04_before_populated_{360,390,768,1024,1366,1440}.png` | `after/C04_after_populated_*.png` |
| C04 empty (Phase 21 empty company) | `before/C04_before_empty_*.png` | `after/C04_after_empty_*.png` |
| C05 wizard | `before/C05_before_populated_*.png`, `before/C05_before_step{1-5}_390.png` | `after/C05_after_populated_*.png`, `after/C05_after_step{1-5}_390.png`, `after/C05_after_step5_filled_390.png` |
| Job detail (#619) | `before/detail_before_populated_*.png` | `after/detail_after_populated_*.png`, `after/detail_after_new_job_1440.png` |

## 13. Testing results

### 13.1 Acceptance criteria (section L)

| Criterion | Result | Evidence |
|---|---|---|
| The wizard has no overflow at 360 and 390 px | **PASS** | `scrollWidth` 360/360 and 390/390 on load and on all 5 steps at 390 (before 475/360 and 490/390); filled step 5 at 390: 390/390. |
| Placeholders are local and Indonesian | **PASS** | Section 9; test asserts 'Senior Software Engineer', 'Berlin', 'Germany', 'cth: Engineering', 'No Poverty' absent and the local examples present. |
| Field names and steps match the baseline | **PASS** | Recorded payload identical (15 pairs, same order and values); 5 step headings in the same order; test. |
| The table keeps all seven columns | **PASS** | Headers before/after: Judul (lowongan), Kategori, Status, Pelamar, Dilihat, Tanggal dibuat, Aksi; test asserts the exact list. |
| Job detail designed with data or listed BLOCKED | **PASS** (designed) | Section 10; captures at 6 widths; test `test_detail_is_a_record_page_without_invented_elements`. |

### 13.2 Session test matrix

| # | Test | Result | Evidence |
|---|---|---|---|
| 1 | Table rendering | PASS | 6 widths × populated/empty; one `<h1>`; status badges filled (before: blank). |
| 2 | Seven columns | PASS | 13.1. |
| 3 | Status tabs | PASS | Live: 'Draf' → `status=draft`, 2 rows, all 'Draf'; tab keeps `search`; test for tab + pager query. |
| 4 | Filter behavior | PASS | Live search 'posyandu' → 1 matching row; category 'Public Health' → only that category; tests for search (title + category name, `%` escaped), status (incl. 'published' alias), category, other companies' jobs never listed. |
| 5 | Table actions | PASS | Lihat/Ubah links; live delete through the confirm dialog removes the row; test: endpoint + `_method=DELETE`, soft delete, another company's job untouched. |
| 6 | Empty state | PASS | Inside `<td colspan="7">` for no jobs and for no match (test; captures `C04_after_empty_*`). |
| 7 | One horizontal-scroll container | PASS | 0 scrolling containers at every width (table fits or stacks); never more than 1 (before: 1 at 360-768). |
| 8 | Five steps | PASS | Live: steps 1→5 reached in order; headings '4. SDG Alignment', '5. Kriteria Dampak'. |
| 9 | Step order | PASS | Test (heading positions sorted). |
| 10 | Field names vs baseline | PASS | Section 6. |
| 11 | Validation | PASS | Live: blur error on title; 'Berikutnya' shows 4 step-1 errors and focuses title; skills required on step 3; server error path. |
| 12 | Step navigation | PASS | Next/back; the issue link opens step 2 and focuses 'Deskripsi pekerjaan'. |
| 13 | State kept between steps | PASS | Values kept after going back; step-2 errors still listed while on step 1. |
| 14 | SDG Alignment behavior | PASS | SDG 8 and 9 checked → stored `["8","9"]` → shown 'SDG 8 · Pekerjaan Layak…'. |
| 15 | Kriteria Dampak behavior | PASS | Both texts stored and shown on the record page. |
| 16 | Submission on the local DB | PASS | Live: created job #629 through the UI (company #1), redirected to its record page. |
| 17 | Persisted values vs input | PASS | Every field equal (`interaction_checks.json` → `persisted_job_629`); salary 3,000,000-4,500,000 IDR monthly; skills/SDG equal after decoding (stored double-encoded, as before). Test `test_store_persists_every_value_and_maps_the_salary`. |
| 18 | Company authorisation | PASS | Tests: guest → login; student → redirected and cannot create; another company's job → 404 on detail, not deleted. |
| 19-24 | 360 / 390 / 768 / 1024 / 1366 / 1440 | PASS | 29 captures. |
| 25 | No page-level overflow | PASS | 0 of 29 captures (before: wizard at 360 and 390). |
| — | Keyboard | PASS | Focus visible on every tab stop of the wizard page; Esc closes the reference dialog and returns focus to 'Ubah referensi'; the filter drawer opens at 390 and closes with Esc. |
| — | Contrast | PASS | New pairs: delete icon `#a3201a` on white 7.56:1; issue banner `#a3201a` on `#fdecea` 6.61:1; others use measured design-system pairs. |
| — | Console / network | PASS | 0 errors in all captures and the interactive run; no 4xx/5xx on the changed pages. |
| — | Automated suite | PASS | Final run `php artisan test`: **224 passed, 2,442 assertions** (includes the parallel sessions' tests). Before the change: 185/185. During the session, two Phase 9 tests of Session A failed briefly while that session was working; they passed in the final run. Phase 23 alone: 10/10. |
| — | Routes | PASS | 203 = 203; the 8 job routes unchanged. |
| — | Assets | PASS | 37/38 Phase 0 hashes identical, 0 changed (`student-go-logo.png` removed by the user before Phase 4). |
| — | Browser interaction script | PASS | 31/31 (`interaction_checks.json`). |

### 13.3 Test data on the local dev DB

The local dev database ends this phase unchanged.

- **Job count.** 625 job postings at the start and at the end.
- **Views on job #619.** Opening the detail page increments `views_count` (existing behaviour). Capture runs raised job #619 from 23 to 29 three times; each time it was set back to 23.
- **Job #626.** Created by the first interactive run, soft-deleted through the table action, then hard-deleted. Its leftover slug caused two later runs to fail at submit (section 15.1), and nothing was saved by those runs.
- **Job #629.** Created through the UI with the final title, values compared, soft-deleted through the table action, then hard-deleted.
- Only rows created by these tests were removed.

## 14. Shared file impact

| File | Impact |
|---|---|
| `ASSET_INVENTORY.md` | Section 28 appended after Session A's section 27; no existing line changed. |
| `resources/css/student-go/company.css` | Session C's file; Phase 21/22 sections untouched. Scoped overrides of shared parts: `.sg-co-jobs__empty-row td` (empty row also when stacked), `.sg-co-wizard__grid > .sg-field` (no stretching), `dialog .sg-co-preview`. |
| `public/build/` | Rebuilt; includes the other sessions' CSS of that moment. |
| Shared stepper | Not changed. The missing error state is noted as a request for the component owner (section 17). |

## 15. Known limitations

1. **FIXED after the report (section 19).** ~~Duplicate titles fail with HTTP 500 (existing bug, found while testing).~~ `store()` uses `Str::slug(title)` as a globally unique slug. Another company's job, or a soft-deleted one, with the same title therefore makes the insert fail. Not changed: it is behaviour outside the approved changes. A fix (e.g. append the id or a counter) needs approval.
2. **FIXED after the report (section 19).** ~~`skills` and `sdg_alignment` are stored double-encoded~~: `json_encode` on top of the `array` cast, which is existing behaviour. Every view in this phase decodes both forms. Other readers (student job pages, matching) should do the same until `store()`/`update()` are fixed, which also needs approval.
3. **FIXED after the report (section 19).** ~~The wizard has no category field~~, so jobs created through it have no category ('—' in the table). Adding `job_category_id` would change the payload, which this phase must not do. It is a candidate change.
4. **The wizard always publishes.** `store()` sets `status = posted`, and the wizard has no draft option; the step-5 note says so.
5. **Drafts are not cleared after a successful submit** (old behaviour). The next visit offers to continue the draft.
6. **FIXED after the report (section 19).** ~~`show()` increments `views_count`~~ on every view by the company itself, which inflates 'Dilihat'. Existing behaviour.
7. **No salary means `salary_currency` 'USD'.** When no salary is given, `store()` still writes 'USD'. The value is not displayed without an amount.
8. **The edit page** (`/company/jobs/{id}/edit`) keeps its old design, `company3.jpg` hero and English SDG names. It is outside this phase's route list.
9. **The shared stepper has no error state**; the wizard uses a banner instead.
10. **Data versus labels.** Seeded job content (titles, departments, skills, categories) is partly English. That is stored data, not interface labels.

## 16. Outstanding decisions

| ID | Status | Needed |
|---|---|---|
| D-07 | OPEN | Not needed for this phase. Jobs are presented as they are; no scope change. |
| D-26 | OPEN (approved here for the preview rail only) | Remaining items (kanban pipeline, saved filters, …) stay unbuilt. |
| Answered 2026-10-31 | — | The user asked to fix limitations 1, 2, 3 and 6 ('perbaiki 4 bug tersebut'): done, section 19. |
| Component owner | — | Add an error state to `x-ui.stepper` (e.g. `errors` = list of step numbers). |

## 17. Handover notes for Phase 24 (Applicant Pipeline)

- **Applicant links.** The jobs table and the record page link applicants with the existing filter `company.applications.index?job_posting_id={id}` and to `company.applications.show/{id}`. Keep both contracts.
- **`company/applications/index`** still uses the `company3.jpg` hero (Deprecate in Phase 24).
- **Applicant status words.** Use `StatusVocabulary` 'job_application'. The record page sums equal labels (pending + new = Menunggu, reviewed + reviewing = Ditinjau); follow the same grouping in the pipeline columns.
- **Applicant names.** Always `User::display_name`.
- **Data quirks.** `JobPosting.skills`/`sdg_alignment` can be double-encoded strings (section 15.2); decode both forms. Job types are stored in English (`Internship`, …); `JobPostingController::JOB_TYPE_LABELS` holds the Indonesian labels.
- **CSS.** Append a Phase 24 section to `company.css`. Reusable here:
  - `.sg-co-jobs__empty-row` for an empty state inside a stacked table;
  - `.sg-co-job__stages` for a per-stage count list.
- **Browser checks.** POST round trips on the remote dev DB take 2-4 s; wait on conditions, not sleeps.

## 18. Proposed updates for integration (not applied)

**`PHASE_TRACKER.md` row:**

`| 23 | Company Job Management | COMPLETE | 3 | 1, 22 | D-02, D-07, D-13, D-26 | 2026-10-31 | main (untracked working copy, no commit) | All 5 AC met. Table: 7 columns kept, status tabs with real counts, search/status/category now applied server-side (same params; 'published' alias), real summary instead of 0-tiles, filled status badges, empty state in table, delete via same DELETE + confirm dialog. Wizard: same 5 steps/order/payload (recorded POST identical), shared stepper, no overflow at 360/390 (was 475/490), per-step + blur validation mirroring server, server errors mapped to steps with old(), local placeholders, salary_range now stored (min/max/currency/period), preview rail (D-26 approved for this item). Detail: record page from real columns, invented Invite/Guest View/Messaged/Budget/Share(404) removed. Tests 10 new; suite 224/224; routes 203=203; 29 captures 0 overflow; browser checks 31/31; DB restored (625 jobs). Found: duplicate-title 500 (unique slug), double-encoded skills/SDG. Report: completion-reports/PHASE_23_COMPLETION_REPORT.md. |`

**`DECISIONS_AND_OPEN_QUESTIONS.md` notes:**

- D-26: 'NOTE (2026-10-31, Phase 23): the user approved the job-posting preview rail (front-end only, from the form state; no backend, no storage, no publishing). Other D-26 items remain unbuilt.'
- D-07: 'NOTE (2026-10-31, Phase 23): still OPEN; the job pages were designed as they are, without changing the product scope.'
- New behaviour change approved in Phase 23: '`store()` maps the optional wizard field `salary_range` to `salary_min`/`salary_max`/`salary_currency`/`salary_period`; job table filters `search`/`status`/`category` applied server-side.'

## 19. Follow-up: bug fixes requested by the user (2026-10-31)

The user replied to this report with 'perbaiki 4 bug tersebut'. All four are fixed in `Company\JobPostingController` and the wizard. Behaviour changes are listed below. No route, model, migration or shared component changed.

| # | Bug | Fix | Evidence |
|---|---|---|---|
| 1 | Same title → HTTP 500 (unique `slug` taken by another company's or a soft-deleted job) | New private `uniqueSlug()`: 'judul', then 'judul-2', 'judul-3', …; checks `withTrashed()`; `update()` ignores the job's own row, so an unchanged title keeps its slug | Test `test_same_title_gets_a_unique_slug_instead_of_a_500`; live: two wizard submits with the same title → jobs 630 (`…-190417`) and 631 (`…-190417-2`) |
| 2 | `skills` / `sdg_alignment` / `required_sdgs` stored double-encoded (`json_encode` over the `array` cast) | `store()`, `update()` and `updateLinkedin()` pass plain arrays (`required_sdgs` as integers); the model cast encodes once. Read-only check of the dev DB first: **0 rows** were double-encoded (622 arrays, 3 null), so no data migration was needed | Test `test_skills_and_sdgs_are_stored_as_plain_json_arrays`; live: raw `skills` = `["UMKM","Koperasi"]`; the student job page (`/student/jobs/{id}`, which calls `count($job->skills)`) renders the wizard job with HTTP 200 |
| 3 | Wizard had no category, so new jobs showed no category in the table | Step 1 gains an optional 'Kategori' select (`job_category_id`, all 33 categories, 'Tanpa kategori' first); client rule 'Pilih kategori dari daftar.'; shown in the preview rail. The server rule already existed (`nullable\|exists:job_categories,id`). **Payload change approved by this request**: one extra key, `job_category_id`; the other 13 keys are unchanged | Test `test_wizard_offers_categories_and_stores_the_choice`; live: category stored (21 = UMKM Empowerment) and shown in the table |
| 4 | The company's own visits counted as views | `show()` no longer calls `incrementViews()` (the page is owner-only); student visits are still counted by `Student\JobController::show` | Test `test_company_views_of_its_own_job_are_not_counted`; live: two reloads by the company 0 → 0; one student visit → 1 |
| + | Found while fixing: **every edit erased the salary** (`update()` wrote `salary_min/max = null`, currency 'USD' because the edit form never sends them) | `update()` overwrites the salary fields only when they are present in the request | Test `test_editing_without_salary_fields_keeps_the_stored_salary` |

**Red/green.** The 5 new tests failed against the controller as it was before these fixes (5/5 red, run against the saved copy) and pass now. Phase 23 tests: 15/15 (162 assertions). Full suite: **229/229** (2,467 assertions). Routes 203 = 203.

**Test data.** Live jobs 630 and 631 were created through the UI, viewed once as a seeded student, deleted through the table action, then hard-deleted. The dev DB is back to 625 jobs, and job #619 still has 23 views. Evidence: `reports/phase23/after/bugfix_checks.json` (8/8), `after/bugfix_student_job_page_1440.png`.

**Still open (observed, not changed):** `update()` reads `$validated['status']` without a default, so a PUT without `status` errors. The edit form always sends `status`, so users do not hit it; one-line fix if wanted. The edit page itself (old design) remains outside this phase.

**Proposed tracker note addition:** '… Follow-up 2026-10-31 (user request): unique slug for duplicate titles, single JSON encoding of skills/SDGs/required SDGs, category field in the wizard, own views not counted, salary kept on edit; tests 15/15, suite 229/229.'

