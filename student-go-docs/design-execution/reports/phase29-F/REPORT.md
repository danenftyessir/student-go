# Phase 29, paket F — Cacat dan polesan (C1–C6, U6)

Date 2026-11-01. Branch `feat/institution-manage` @ `000523d` (user's choice), no commit. Other sessions were editing packages A, B, C, D, E, G and H in the same working tree at the same time; package F changed only its own lines (in `company.css`, which package E also edits, only the two C3 rules) and appended to shared documents.

**Status: COMPLETE.** First pass ended PARTIAL; the second pass ('lanjut hingga complete') closed the three open boxes with evidence (section "Second pass").

## Files changed

| File | Item | Change |
|---|---|---|
| `app/Http/Controllers/Student/FriendController.php` | C2, C6 | `getActivities`: grouped `whereIn … orWhereIn` so `status = accepted` applies to both sides; timestamp falls back to `updated_at`/`created_at` when `responded_at` is null; `preview.image` is `null` (was `asset('placeholder-project.jpg')`; `problems.cover_image` does not exist) |
| `resources/views/student/friends/profile.blade.php` | C6 | Cover `<img placeholder-profile-cover.jpg>` and its gradient replaced by a plain neutral band (view unreachable, C7) |
| `resources/views/layouts/app.blade.php` | C4 | Wrapper `min-h-screen` → `sg-shell` |
| `resources/css/student-go/components.css` | C4 | `.sg-shell` (flex column, `min-height: 100dvh`), `.sg-shell > .sg-main { flex: 1 0 auto }` |
| `resources/css/student-go/errors.css` | C5 | Error-page padding scoped from `.sg-error` to `section.sg-error` |
| `resources/css/student-go/auth.css` | C5 | Auth field errors: block layout, icon out of flow, `::first-letter { text-transform: uppercase }` |
| `resources/css/student-go/company.css` | C3 | `.sg-co-dash__col` gets `grid-template-columns: minmax(0, 1fr)`; new `.sg-co-dash__table-scroll { overflow-x: auto }` |
| `resources/views/company/dashboard/index.blade.php` | C3 | Chart data table wrapped in `.sg-co-dash__table-scroll` (inside the existing `sr-only` wrapper) |
| `resources/views/institution/{problems,projects,reviews}/index.blade.php`, `student/jobs/{saved,index}.blade.php` | U6 | `illustration` on the five page-level empty states |
| `tests/Feature/Phase29/PackageFDefectsTest.php`, `PackageFPolishTest.php` | tests | 13 tests |
| `ASSET_INVENTORY.md` (new section), `PHASE_TRACKER.md` (package F entry) | docs | |

## Findings and causes

- **C1.** `ApplicationController@show` uses an ownership-scoped `firstOrFail()`, identical on the audit commit `92b9103` and on HEAD `000523d` (`git show`), and `errors/404.blade.php` exists on both. The audit's 500 therefore did not come from this code; the most likely cause is the same database outage seen today (the Supabase pooler timed out and then failed DNS during this session, and four institution pages returned 500 once, then 200). Live status-only probe on this branch: unknown ID → 404, another student's ID → 404. No code change; three regression tests (unknown → 404, foreign → 404, own → 200).
- **C2.** Cause from code reading (no log content read or copied): the ungrouped `orWhereIn` let pending friendships through, and `responded_at->diffForHumans()` was called on null (accepted rows can also have a null `responded_at`). Test red before (`Call to a member function diffForHumans() on null`, `FriendController.php:399`), green after. No view, JS or `public/` file calls `friends/activities` (grep over `resources`, `public`, `routes`, `app`); route unchanged. Live: 500 before; the fix is verified by feature tests (the database was unreachable for a second live probe).
- **C3.** Two parts. (1) Cause of the page overflow: `.sg-co-dash__col` is a grid without a column template, so its implicit `auto` column grows to the chart canvas; once Chart.js has drawn at a wide window, the canvas (1,136 px) keeps the column wide after the window narrows. (2) The data table (395 px, the audit's figure) sits in the `sr-only` wrapper; it now scrolls in its own `overflow-x: auto` wrapper as requested.
- **C4.** Shell column flex; `main` takes the remaining height.
- **C5.** Class collision: `errors.css` (Phase 26) gave `.sg-error` page padding (3rem/5rem top, 4rem/6rem bottom, side gutter), and `.sg-error` is also the field-error class. Field errors outside auth had the same defect (for example `company/jobs/create`) and are fixed by the same selector change. Capitalisation by CSS only (user's choice): the server message `email atau password salah.`, the `email` key and the session behaviour are unchanged; screen readers still read the lower-case text.

## Evidence per acceptance box (L.F)

| Box | Result |
|---|---|
| Route count before = after | 203 = 203, `route:list --json` byte-identical (checked twice) |
| Forms unchanged | No changed line touches a form, `action`, method, `name` or `id` (`git diff` of every changed view) |
| Test suite | Before: 357/358 (1 failure in `CompanyPackageETest`, package E in progress elsewhere). After: **453/453**, 4,381 assertions (count includes other sessions' tests) |
| C1/C2 red then green | C2 red → green. C1: no defect in code (see above); regression tests green |
| `npm run build` | OK: CSS 253.27 kB (gzip 41.06), JS 21.37 kB |
| 0 `<style>` / hex added | 0 in every changed view; layouts edited, not rewritten |
| Overflow 360/390/768/1440 | 0 on all 16 live pages after (`after/metrics.log`); 0 on all 9 test-data pages after (`testdata/metrics.json`, `c3/after-*`) |
| Screenshots | Live: `before/` (guest, student), `after/` (all roles). Test data: `testdata/` before/after for the five empty states and institution/company pages. C3: `c3/{before,after}-{alpine,noalpine}/` at 390 and 1440 |
| No English label | None added |
| Statuses | None shown by this package |
| C1 | Unknown → 404, foreign → 404 (tests, live) |
| C2 | No 500 (tests); route name and URI unchanged (test) |
| C4 | Gap below the footer at 1440×900, before → after. Live: `/contact` 61 → 0, 404 page 97 → 0, `/student/wishlist` 71 → 0, `/student/projects` 91 → 0, `/student/friends` 165 → 0, `/student/jobs/saved` 53 → 0. Test data: `/institution/problems` 243 → 0, `/projects` 223 → 0, `/reviews` 223 → 0, `/student/jobs?page=99` 103 → 0, `/student/jobs/saved?folder=…` 101 → 0, `/company/jobs` 40 → 0. Long pages 0 → 0 |
| C5 | Error directly under the field, aligned with the column, 'Email atau …' (`after/guest-login-error-1440.png`) |
| C3 | `/company/dashboard` with a chart (5 months of data), window 1440 → narrower: overflow at 360/390/768 **792/762/392 → 0/0/0**, with and without Alpine; table in `.sg-co-dash__table-scroll` (test) |
| C6 | No `placeholder-*.jpg` in `app/`, `resources/`, `routes/`; no image file added |
| U6 | Five illustrations rendered (tests and `testdata/after-*`); 'Tidak ada permintaan masuk' and 'Belum ada saran' stay text only (test, live `/student/friends`) |
| `public/` hashes | 228 files identical (before/after list; checked again at the end) |
| Deprecate | None |

## Method notes

- **Test-data renders.** The live database was unreachable in the second pass, and the seeded test accounts have no empty lists or chart. Pages were rendered by a temporary feature test (deleted afterwards) with the package's own test data, then opened by Playwright against the real `public/build` CSS/JS served by a local static server (`render_dump.py`; every other host blocked, except the Alpine CDN script in the `alpine` runs). Temporary files under `public/_sg_f_tmp*` were removed in the same script; the hash list proves `public/` unchanged.
- **Before variants.** C4/U6: the dumped after-HTML with exactly the reverted lines (old wrapper, no empty-state `<img>`). C3: the old HTML with the old CSS, built by removing the two C3 rules, measured, then restored and rebuilt (package E's lines verified intact).
- **A harness error, corrected.** Some intermediate C3 runs loaded a hashed CSS file that a later build had deleted (pages without CSS); those runs were discarded. The final scripts rewrite the CSS/JS links to the files in the current `manifest.json`.
- **Unrelated observation.** The Supabase pooler timed out and then failed DNS during this session; the transient 500s and the 9-minute company login timeout match that. Not investigated further (logs not read).

## Second pass ('lanjut hingga complete')

| Open box after pass 1 | How it was closed |
|---|---|
| C1 red before fix | Shown that the code is identical on the audit commit and HEAD; the 500 was not a code defect. Regression tests kept |
| C3 wrapper | Reproduced the real overflow (762 px at 390 with a chart), fixed its cause in `.sg-co-dash__col`, added the requested wrapper |
| Missing institution/company before screenshots | Before/after from test data with the real CSS (`testdata/`, `c3/`) |
