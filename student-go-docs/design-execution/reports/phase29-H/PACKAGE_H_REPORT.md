# Phase 29, paket H — Foto seed masalah (2026-11-01)

Branch `feat/institution-manage` (no commit). Decision: D-46 (answered 2026-11-01 after the stage 1 plan).

## What changed

- Database: `problem_images` rows 1–220 (the seed rows), columns `image_path`, `caption`, `updated_at` only. No insert, no delete. Supabase Storage not written.
- Code: `database/seeders/ProblemSeedPhotosSeeder.php` (new; not registered in `DatabaseSeeder`, D-46 point 5), `tests/Feature/Phase29/ProblemSeedPhotosPackageHTest.php` (new, 5 tests).
- Docs: `DECISIONS_AND_OPEN_QUESTIONS.md` (D-46 answer), `ASSET_INVENTORY.md` (paket H section), `PHASE_TRACKER.md` (paket H note), this folder.

## Stage 1 findings (read-only)

- URL: `ProblemImage::image_url` and the detail view call `supabase_url()` (`app/helpers.php:140`): local `storage/app/public/<path>` if present, else the public URL in bucket `sg-storage`. Not `SupabaseStorageService`.
- Rows: written by `ProblemImagesSeeder` (random 2–4 paths of 92, random one of 10 captions). Files: uploaded in Phase 8 (D-14). The Phase 8 upload log is not in the repository, so the mapping was proven by matching.
- Matching (`stage1-bucket-match.csv`): 92/92 objects downloaded once by GET into the session scratchpad; no SHA-256 equal (JPEG conversion); 32×32 pixel difference ≤ 0.18 for the match vs ≥ 26.2 for the runner-up. Every object = available photo number `(NN−1) mod 37` in `photo-review.json`. All 32 usable photos already exist as objects, so only rows change.
- `stage1-plan-narrow.csv` / `-wide.csv` are the stage 1 variants (SDG 1 still in the farming pool). `stage1-plan.py` is the final version of the planning script (SDG 1 in the region pool) that produced `stage2-applied-plan.csv`.

## Applied plan (`stage2-applied-plan.csv`)

- Pools: pertanian (SDG 2, 15) 12 photos; pesisir (6, 13, 14) 10; pasar (8, 12) 2; pendidikan (4) 2; desa (9, 10, 11, 16, 17) 6; wilayah (1, 3, 5, 7, and slots beyond a small pool) 9 = desa + three landscapes.
- Captions: 131 'Foto ilustrasi: <real photo location>', 17 'Foto ilustrasi' (no location recorded), 72 'Gambaran wilayah'. None of the 10 old captions remain.
- 0 problems with a repeated photo; 32 distinct objects referenced; order 1 is always a pool photo.

## Evidence

| Check (L.H stage 2) | Result |
|---|---|
| Dry run before writing | `ProblemSeedPhotosSeeder::plan()` on the live DB = planning CSV, 220/220, 0 mismatch |
| Backup | CSV of all 220 rows in the session scratchpad (not in the repository), SHA-256 recorded; equal to the stage 1 snapshot |
| Applied | `php artisan db:seed --class=ProblemSeedPhotosSeeder`: '220 baris foto seed masalah diperbarui.' DB after = plan, 0 mismatch; 32 distinct paths |
| Restore procedure | `restore-problem-images.php <backup.csv>` (dry run) reported 0 differing before the write and 220 differing after it; `--apply` restores `image_path`, `caption`, `updated_at` by id in one transaction and re-checks. Documented and dry-run tested; not applied |
| Route count | 203 = 203, route list identical |
| Forms / views | No view changed; form diff N/A |
| Tests | Before 447 passed. After 453: 452 passed, 1 failed — `PackageFPolishTest::test_c3_…` (a `company.css` rule of paket F being written by another session; not touched here). Paket H 5/5, Phase 8 + 9 + H 33/33 |
| `npm run build` | Passes |
| 0 `<style>` / 0 hex | N/A (no view rewritten) |
| Overflow 360/390/768/1440 | No overflow on the gallery and details 5, 70, 77 (`after/metrics.json`) |
| Screenshots | `before/` 14 of 16, `after/` 16 of 16. Two before shots skipped: the dev server returned 500 (intermittent DNS failure to the database host); the capture script never saves a 5xx page |
| English caption | None; all captions Indonesian |
| `public/` hashes | 228 files, identical before and after |
| Asset inventory | Paket H section added (Reuse 32, Deprecate usage for the rest; nothing deleted) |

## Notes

- Gallery `?view=grid` shows 12 images, 10 load: the two that fail belong to problems 80 and 81 (rows 221–225, uploaded paths `<id>/<hash>.jpg`), created by another session at 09:12–09:16 WIB during this run. They are not seed rows and were not touched.
- In the detail gallery thumbnails appear only after each 1600 px image loads, so screenshots show 1–2 thumbnails; same behaviour before and after.
- Rerunning `ProblemImagesSeeder` would bring back random photos and captions; run `ProblemSeedPhotosSeeder` after it (it is idempotent and only touches `problems/masalah-desa-NN` rows).
