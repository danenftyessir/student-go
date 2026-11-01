# Phase 29, paket B — Proyek aktif mahasiswa (completion report)

| Field | Value |
|---|---|
| Date | 2026-11-01 |
| Branch / HEAD | `dev` @ `000523d` (user chose to stay on `dev`; no commit). During the session another session checked out `feat/institution-manage` (same commit `000523d`) in this working tree; the uncommitted package B changes are unaffected |
| Scope | `student.projects.show`, `student.projects.create-report`, `student.projects.create-final-report` |
| Soft dependencies | Phases 11 and 19 read NOT_STARTED in the tracker although their code and tests are on `dev`; user confirmed to continue |
| Live sample | project 77 (seeded student; GET requests and login only, nothing submitted) |
| Status | **COMPLETE** (every L.B box below has evidence) |

Working tree note: packages A, C, D, E, F and G were being executed by other sessions in the same tree at the same time. Package B touched only the files listed below; the tracker row and `ASSET_INVENTORY.md` were edited by appending/replacing only package B's own text.

## Files changed

| File | Change |
|---|---|
| `resources/views/student/projects/show.blade.php` | Rewritten: page header with breadcrumb and actions; Phase 19 detail pattern (`sg-inst-detail`, milestone timeline, report list); stage meter partial reused unchanged (`institution/projects/components/stage-meter.blade.php`); statuses through `x-ui.status`; milestone update form kept (same `updateMilestone()` PUT JSON call), `alert()` replaced by the shared toast; no `<style>`, no inline style |
| `resources/views/student/projects/create-report.blade.php` | Rewritten on the sectioned form (`sg-inst-section`), `x-ui.field/input/select/textarea`, `x-ui.upload` (ids `documentUpload`, `photosUpload` passed explicitly), `x-ui.form-errors`, sticky save bar |
| `resources/views/student/projects/create-final-report.blade.php` | Same treatment; warning box as `x-ui.alert`; `confirm()` replaced by the shared dialog (`data-sg-confirm`, tone primary); three required checkboxes kept without `name` |
| `resources/css/student-go/student-project.css` | New, additive (header action wrap, inline milestone form, rail institution row, notice list); tokens only, 0 hex |
| `resources/css/app.css` | One `@import` line for the file above |
| `tests/Feature/Phase29/StudentProjectPackageBTest.php` | New, 7 tests / 87 assertions |
| `student-go-docs/design-execution/ASSET_INVENTORY.md` | New section 'Phase 29, paket B' |
| `student-go-docs/design-execution/PHASE_TRACKER.md` | Phase 29 row: package B status |

Not changed: routes, controllers, models, services, `student/projects/index.blade.php` (certificate controls), `public/**`.

## Acceptance criteria (L.B)

| Criterion | Evidence |
|---|---|
| Route count before = after | `php artisan route:list --json` 203 before, 203 after, files byte-identical (`cmp`) |
| Forms unchanged (action, method, enctype, name/id, accept, size hint) | `form-diff.txt`: action, `POST`, `multipart/form-data` identical on both forms; every `name` identical; file inputs identical (`document`/`documentUpload`/`.pdf,.doc,.docx`; `photos[]`/`photosUpload`/`image/*`/multiple; `final_report`/`finalReportUpload`/`.pdf`/required); three required checkboxes without name identical. Only difference: fields that had **no** id now carry `id` = name (for `<label for>`), additive. Size hints: 'PDF, DOC, DOCX maksimal 10MB', 'JPG, PNG maksimal 5MB per foto', 'PDF maksimal 20MB' (same values; controller rules 10240 / 5120 / 20480 KB untouched) |
| Test suite passes; new package B tests | Package B: 7/7 passed (87 assertions), including a real `store-report` submit in a feature test (SQLite) with redirect + row, the `after:period_start` validation, and 404 for another student's project on all three routes. Full suite at the end: 446 tests, 442 passed; the 4 failures are `PackageFPolishTest` (C4, C5, U6 ×2), package F's red-first tests in another session, not related to package B. Baseline before any edit: 358 / 354 (4 failures then were package E's in-progress tests) |
| `npm run build` passes | OK, CSS 249.49 kB (gzip 40.47 kB) — includes other sessions' CSS |
| 0 `<style>` and 0 hex in the three views | grep: `<style` 0/0/0, hex 0/0/0, `style=` 0/0/0; `student-project.css` hex 0 |
| No horizontal overflow at 360, 390, 768, 1440 | `metrics.json` after: scrollWidth = viewport at all four widths on all three pages. (First after-run showed 408 px at 360/390 on `show`: the header actions did not wrap; fixed by `.sg-sp-header .sg-page-header__actions { flex-wrap: wrap }`, page-scoped, shared component untouched) |
| Before and after screenshots | `before/` and `after/`: `show-77`, `report-77`, `final-77` at 360, 390, 768, 1440 |
| No English label left | Probe on `<main>` text before: 'Overdue', '1 day ago', 'Update', 'Progress', 'Submit', 'Optional', 'Upload', 'Impact Metrics'. After: none (probe list also covers 'Download', 'Unknown', 'Laravel'). 'Overdue' → 'Melewati tanggal selesai' (same words as `/student/projects`), milestone 'Terlambat' → 'Melewati target', '1 day ago' → 'dikirim 1 hari yang lalu' (`locale('id')->diffForHumans()`) |
| Statuses from `StatusVocabulary` | Project, milestone and report statuses via `x-ui.status` (`project`, `milestone`, `report`); the model's `status_badge` ('Menunggu Review', 'Direview', 'Unknown') is no longer used on this page. Stored report values checked against the migration enum: `pending, reviewed, approved, revision_needed`, all in the vocabulary; no entry added |
| Stage meter replaces the gradient bar; stored percentage not shown | Rail 'Tahap': Phase 19 partial, '3 dari 4 tahap' on project 77; test asserts 3 filled + 1 empty segment and that '89%' (stored value), 'Progress Keseluruhan', gradients and `style="width"` are absent. The per-milestone percentage bars are also gone; the milestone's own value remains only as the prefilled value of its update form |
| Certificate control | `grep -i 'sertifikat|certificate' resources/views/student/projects/`: only `index.blade.php:97-137` (number + 'Unduh sertifikat', from `feat/student-certificate`, merged in `dev`); `show` had none on `dev` before and none was added. `index.blade.php`, `CertificateService.php` and `public/assets/certificate/**` have no diff; Phase 11 certificate tests pass |
| `public/` hashes unchanged | SHA-256 of 228 files (`public/build/` excluded) before and after: identical (`cmp`). Method: before/after list (`baseline/asset-hashes.txt` not present) |
| Assets no longer referenced recorded as Deprecate | None: the old views used inline SVG only. Recorded in `ASSET_INVENTORY.md` |

## Behaviour kept (and the small differences)

- Final report button still appears only when `progress_percentage >= 80` on an active project (the stored number is used as before, just not displayed). Test covers 89 → shown, 75 → hidden.
- Milestone update: same endpoint, verb, headers and JSON keys; reload on success. Error feedback now uses the shared toast instead of `alert()`.
- Hidden milestone forms: the old view rendered a hidden update form for every milestone, but only active, unfinished ones had a button to open it; the new view renders the form only where it can be opened (project 77: 4 → 1 form). No reachable form was removed.
- Final report submit: confirmation now through the shared dialog (focus starts on 'Batal'); the form is submitted only after 'Kirim dan selesaikan'.
- 'Unduh laporan akhir' keeps its old target `asset('storage/'.final_report_path)`. **Finding, not changed:** `public/storage` does not exist and final reports are stored through the project service, so this link probably 404s; other documents on the page use `document_url()`. Changing the link target is a behaviour change outside this package; decision for the user.
- Institution logo now rendered through `getLogoUrl()` like every other designed page (the old `asset('storage/...')` path does not exist); the seed has no logos, so the live page shows initials either way.
- Impact metrics (completed projects): only values that are stored are shown (no '0' fallback), as a definition list instead of coloured stat tiles (guardrail 2).

## Other observations

- Login from the browser script failed twice with HTTP 500: the dev server could not resolve the database host (DNS), a local network issue; the retry succeeded. No 5xx page was opened or captured.
- One after-run logged 'gagal load notifikasi: Failed to fetch' from the shared notification bell on the last page (request cut when the script closed the browser); not package B.

## Remaining

Packages A, C, D, E, F, G are in other sessions; H awaits approval (D-46). Phase 29 stays PARTIAL per L.0 until every approved package is COMPLETE.
