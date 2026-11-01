# Phase 29, paket A — Lamaran mahasiswa (tracker, applications/{id}, applications/create/{problemId})

Date 2026-11-01 · branch `dev` @ `000523d` (user's choice, D-27) · no commit · executed in the same session as paket C at the user's explicit request ("lakukan bersamaan"), which deviates from the one-package-per-request rule.

Soft dependency Phase 10 reads NOT_STARTED in the tracker; the user confirmed to continue.

## 29.A.1 inventory

`show.blade.php` and `create.blade.php` were already migrated by the Phase 10 work (comments "phase 10"): `x-ui.page-header`, `x-ui.status`, `x-ui.dl`, `x-ui.avatar`, `x-ui.upload`, `x-ui.textarea`, `x-ui.form-errors`, shared confirm dialog on "Batalkan lamaran". They have 0 `<style>`, 0 hex, no English label in the rendered pages, and no overflow at any width. They were **kept unchanged** (29.A.4 needs no further change).

## Changes

- `resources/views/student/applications/tracker.blade.php` rewritten: title and h1 "Pelacak lamaran"; one secondary action "Lihat semua aplikasi"; `x-ui.summary` from the controller's existing counts (total, sedang diproses, diterima atau direkrut, ditolak; zero values hidden); two `x-ui.table`s (five newest job and project applications, as before) with `x-ui.status` (`job_application` / `application` domains) and Indonesian dates plus relative time; "Perubahan status terbaru" list; empty state `no-applications` for an account without applications.
- Removed (guardrail 2): "Aktivitas Minggu Ini" (7-day bar chart, mostly empty boxes), the success-rate percentage bar, the coloured stat numbers, the "Aksi Cepat" card and the status-pipeline dots. The controller still computes `$weeklyActivity`; it is no longer read (controller left untouched).
- `tests/Feature/Phase29/StudentApplicationsPackageATest.php` (5 tests, 47 assertions).

## Acceptance criteria (L.A)

| Criterion | Evidence | Result |
|---|---|---|
| Route count before = after | identical `route:list --json`, 203 | ✔ |
| Create and cancel forms unchanged | Rendered-form diff on create/5 and 330–332: identical (action, method, `multipart/form-data`, `_method=DELETE`, names and ids `problem_id`, `motivation`, `proposal`); views not edited | ✔ |
| Suite passes; new tests | 382/382; package A 5/5 | ✔ |
| `npm run build` | OK | ✔ |
| 0 `<style>`, 0 hex in tracker/show/create | `grep`: 0/0 for all three (also 0 `style=` attributes) | ✔ |
| No overflow 360/390/768/1440 | `scrollWidth` equals viewport on all five pages before and after | ✔ |
| Before/after screenshots | `reports/phase29-A/before/`, `after/` (tracker, 330, 331, 332, create-5 × 4 widths) | ✔ |
| No English label | Before on tracker: `Application Tracker, Rejected, Pending, Shortlisted, Applied, ago`; after: none on all five pages | ✔ |
| Statuses from `StatusVocabulary`, same words as dashboard and `/student/applications` | Tracker uses `x-ui.status` with the same domains as `/student/applications` and `/student/dashboard`; test compares badge markup on tracker vs list | ✔ |
| 'Aktivitas Minggu Ini' removed | test asserts absence | ✔ |
| `public/` hashes unchanged | 228 files identical (before/after list; no `baseline/asset-hashes.txt` on this branch) | ✔ |
| Deprecated assets recorded | none; inventory section "Phase 29, paket A dan C" | ✔ |

Other students' application IDs still return 404 (test). Unknown IDs (C1) are paket F. No live form was submitted and "Batalkan" was not pressed on the test account.

## Notes

- The summary label "diterima atau direkrut" reflects the controller's combined count (project `accepted` + job `hired`), which have different status words.
- `.sg-summary` sets `margin: 0` outside Tailwind's utility layer, so a `mb-*` class on the component does nothing; spacing is on a wrapper. `/student/applications` passes `mb-4` the same way (no effect there either; not changed).
