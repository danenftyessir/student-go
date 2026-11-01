# Phase 29, paket D — Institusi: kelola proyek dan status verifikasi

Date: 2026-11-01 · Branch: `feat/institution-manage` (created from `dev` @ `000523d` at the user's request; no commit) · Status: **COMPLETE**

Routes: `institution.projects.manage` (GET `/institution/projects/{id}/manage`), `institution.verification.status` (GET `/institution/verification/status`). Live checks with the seeded institution account (projects 118, 188, 217), GET requests and logins only. No status of the account's projects or milestones was changed. Submits ran only in feature tests on SQLite in-memory rows.

## Files changed

| File | Change |
|---|---|
| `resources/views/institution/projects/manage.blade.php` | Rewritten as a work surface: `x-ui.page-header`, sections (status, tahap with the Phase 19 stage meter, tinjau laporan, riwayat tinjauan), `x-ui.list`/`x-ui.status`, metadata rail, native `<dialog>` via `x-ui.modal`; `alert()`/`confirm()` replaced by `showNotification`/`sgConfirm` |
| `resources/views/institution/verification/status.blade.php` | Rewritten as a status summary: `x-ui.page-header`, `.sg-inst-panel` with `<x-icon name="shield-check">`, `x-ui.status` (domains `verification`, `verification_document`), divided per-document list |
| `resources/css/student-go/institution.css` | Additive section 'Phase 29 paket D' (`.sg-inst-manage__*`, `.sg-inst-flag--warn`, `.sg-inst-dialog-form`, `.sg-inst-verify--status`, `.sg-inst-docs*`); existing tokens only |
| `tests/Feature/Phase29/InstitutionPackageDTest.php` | New, 9 tests |

No controller, route, model, JS bundle, `StatusVocabulary` or `public/` file changed. `StatusVocabulary` already had `verification.active` (Phase 20), so no entry was added.

## Decisions taken with the user during the package (2026-11-01)

1. **Project status select**: option values kept exactly (`planning`, `active`, `review`, `completed`, `cancelled`). The controller only accepts `active`, `on_hold`, `completed`, `cancelled`, so `planning`/`review` still answer 422 and `on_hold` still cannot be chosen. Labels are now Indonesian ('Perencanaan', 'Berjalan', 'Tinjauan', 'Selesai', 'Dibatalkan'). **Open defect** for a later D-25 decision.
2. **Review history**: the filter used `revision_required` (not an enum value); reports rejected by `rejectReport` (`revision_needed`) never appeared. The display filter now includes `revision_needed` (and still `revision_required`). Display only.
3. **Edit-milestone date prefill**: the JSON endpoint sends `2026-07-07T00:00:00.000000Z`, which `<input type="date">` rejects, so the date field was always empty. The JS now uses `(milestone.target_date || '').slice(0, 10)`. Endpoint, controller and PUT payload keys unchanged.

Further UI-only adjustments (no server change): the status update and milestone delete now show an error toast when the server answers `success: false` (before: silent); the bottom 'Upload Dokumen Baru' link on the status page was a duplicate of the header action and was dropped, and 'Kembali ke Dashboard' became the page-header back link; score and confidence are shown only when stored (before: `?? 0` printed '0/100' and '0%' for null values, guardrail 2); the stored `progress_percentage` is no longer shown on the manage page (stage meter instead, Phase 19 rule).

## D-09 (OPEN): wording kept

'Hasil verifikasi AI untuk {nama}', 'AI Score', 'Hasil Analisis AI' and 'Belum diverifikasi AI' are unchanged word for word (test `test_status_page_keeps_d09_wording_and_uses_shared_status`). Non-AI English labels were translated: 'Verification Score' → 'Skor verifikasi', 'Confidence Level' → 'Tingkat keyakinan', 'Severity' → 'Tingkat keparahan', flag types → 'Masalah'/'Peringatan'/'Info'. The status label for `needs_review` is now the shared 'Perlu Ditinjau' (before: 'Perlu Review Manual'), for `active` 'Terverifikasi' (before: 'Aktif - Terverifikasi').

## Acceptance criteria (L.D)

| Criterion | Evidence | Result |
|---|---|---|
| Route count before = after | `route:list --json` byte-identical, 203 = 203 | ✅ |
| Forms unchanged (`action`, method, `@method`, `enctype`, names/ids) | `form-diff.txt`: forms, fields, select option values, fetch URLs and verbs, JS-set actions identical on 118, 188, 217 and status. The only delta (`edit-milestone-id` value 749 on 188 after) is a runtime value set when the capture opened the edit dialog | ✅ |
| Test suite passes; new package D tests | Before: 358 tests, 357 passed (1 failure in the then in-progress package E test). After: **447 passed, 4,225 assertions**; package D: 9 tests, 120 assertions | ✅ |
| `npm run build` passes | CSS 253.15 kB (gzip 41.04), JS 21.37 kB; includes other packages' concurrent CSS | ✅ |
| 0 `<style>` and 0 hex in both views | `grep`: 0/0 and 0/0; also a test | ✅ |
| No overflow at 360, 390, 768, 1440 | `metrics.json` after: `scrollWidth == innerWidth` on all 16 page×width pairs; add dialog at 390 = 390. (An intermediate build overflowed 366 px at 360 on the status page; fixed) | ✅ |
| Before/after screenshots | `before/` 16, `after/` 16 + 4 dialog shots (manage 188 at 390 and 1440) | ✅ |
| No English label left, except D-09 'AI' wording | Text probe of all four pages: none; feature test over manage and status labels | ✅ |
| Statuses from `StatusVocabulary` | `x-ui.status` for project, milestone, report, verification, verification_document; select labels from `StatusVocabulary::label` (except the two non-enum options, see decision 1) | ✅ |
| `shield-check` icon on the status page | Rendered (`#shield-check` in HTML, test) | ✅ |
| No status of the test account's data changed | Only GET requests and dialog opening (the edit dialog loads via GET JSON); no submit against the live DB | ✅ |
| `public/` hashes unchanged | SHA-256 list of 228 files (`public/build/` excluded) identical before/after; method: before/after list (`baseline/asset-hashes.txt` not present) | ✅ |
| Assets no longer referenced recorded as Deprecate | None (both old views used inline SVG only) | ✅ |

Console errors: 0 and asset 4xx: 0 on every page and width. One `<h1>`, `lang="id"`.

## Environment note

The dev server's connection to the database failed DNS lookups intermittently during this session (HTTP 500 on some loads). The capture script retried until the page rendered with its `<h1>`; no screenshot of an error page was kept. Packages A, B, C, E, F and G were executed concurrently in the same working tree; package D touched none of their files.

## Remaining / for the user

- Decide the project-status option mismatch (decision 1) under D-25.
- `edit-milestone-status` still offers no `delayed` option (enum value exists); unchanged.
- D-09 stays OPEN.
