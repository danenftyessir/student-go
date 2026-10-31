# Phase 26 — Error Pages: completion report (2026-10-31)

**Recommended tracker status:** COMPLETE (central `PHASE_TRACKER.md` not edited: parallel sessions; update it when consolidating).
Branch `feat/application-workflow`, no commit (D-27).

## Gates
D-01 ANSWERED (Student Go). D-25 ANSWERED (debug only when `APP_ENV=local`, already in `config/app.php`). D-11 still OPEN: only the 403 view was built; the admin UI was not touched. No 429 view: no route uses `throttle` (login uses its own RateLimiter and returns a form error); the 4xx fallback covers any other code.

## Files
- `resources/views/errors/`: `403`, `404`, `419`, `4xx` (in `layouts.app`); `500`, `503`, `5xx` (standalone); `standalone.blade.php` (no session, csrf, auth, query or JS; CSS via Vite in try/catch); `partials/content.blade.php` (code, h1, sentence, Kembali, Ke beranda, contact link).
- `resources/css/student-go/errors.css` + one import line in `resources/css/app.css`. Existing tokens only.
- `tests/Feature/Phase26/ErrorPagesTest.php` (10 tests; test routes exist only inside the tests).
- 403 shows the middleware message without the 'Unauthorized.' prefix; `AdminMiddleware` unchanged.

## Verification
- Tests 283/283 (3,034 assertions); Phase 26 10/10. Routes 203 = 203. Build OK.
- Live, port 8000: 404 `/zz-not-a-route`; 403 seeded company account on `/admin/dashboard` ('Hanya admin yang dapat mengakses halaman ini.'); 419 POST with an expired token. All: `lang="id"`, one h1, branded title, Kembali/Ke beranda/contact, no overflow at 360/1440.
- Live, throwaway server on port 8011 (shell env overrides, `.env` untouched): `APP_DEBUG=false`, DB pointed at a closed port, maintenance driver `cache`/`file`. 500 on `/institution/19` and 503 via `php artisan down` (then `up`): branded, 0 scripts, no class name, SQL, path or host. Port 8000 stayed 200 throughout.

## Configuration for non-local environments
- `APP_ENV=production` and `APP_DEBUG=false` (the user sets them; `.env` was not edited). `config/app.php` forces debug off when `APP_ENV` is not `local`.
- Run `php artisan view:cache` after deploy. For maintenance prefer `php artisan down --render="errors::503"`.

## Known limits
- 404 for an unmatched URL runs before the session starts, so the navbar shows the guest state even for a logged-in user (Laravel behaviour, unchanged).
- With `APP_DEBUG=true` locally, 500 still shows the debug page (by design).
