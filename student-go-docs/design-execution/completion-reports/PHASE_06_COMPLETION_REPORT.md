# PHASE 06 — Login, Registration & Authentication: completion report

| Field | Value |
|---|---|
| Phase | 6 — Auth (`phases/PHASE_06_AUTH.md`) |
| Date | 2026-10-31 |
| Branch / commit | `feat/students` @ `e824b9d`, no commit (D-27) |
| Status | **COMPLETE** — all 8 acceptance criteria met (section G.3) |
| Prerequisites | Phase 3 (hard) COMPLETE; Phase 1 (soft) COMPLETE; Phase 5 COMPLETE |
| Gate answers (this session) | D-40: 'Student Go' → 'Student Go' in auth emails and auth messages (after the first report); D-15 (a) out of scope; D-23 (b) Claude drafts, DRAFT; no photograph on auth pages; 'Ingat saya' approved; forgot-password message **not** changed; live student/company registration test approved; RN-16/D-38 consent label; RN-17/D-39 no example passwords; success panel instead of `alert()`. All recorded in `DECISIONS_AND_OPEN_QUESTIONS.md`. |
| Evidence folder | `reports/phase6/` (`before/`, `after/`, `interaction/`, `live/`, `routes-after.json`, `step-overflow.json`) |

## A. Summary

The eight authentication views now share one template (`layouts/auth.blade.php`) and one stepper (`x-ui.stepper`): login, role chooser, student (3 steps), institution (4 steps) and company (4 steps, previously one long page) registration, forgot password, reset password and email verification. Copy is Indonesian, the invented statistics and the broken Student Go logo are gone, the photo backgrounds are replaced by the wordmark and the original contour motif, validation appears after blur or submit, the province → regency select cancels stale requests and announces changes, and every form posts to the same route with the same field names. The backend logic (controllers, form requests, routes, middleware, models, database) was **not changed** — only the product name in auth emails and messages (D-40, requested after the first report); its behaviour is pinned by 19 new feature tests and was exercised live on the local test database with throwaway accounts that were deleted afterwards.

## B. Initial condition (before this phase)

Audit of the source on 2026-10-31 (VERIFIED by reading the files and running the app):

| Area | Finding |
|---|---|
| Auth mechanism | Custom controllers, no Breeze/Fortify. `LoginController@login`: `email` field accepts email **or** username, `Hash::check`, `Auth::attempt($credentials, $request->filled('remember'))`, rate limit 5 failures per email+IP (60 s), `session()->regenerate()`, redirect by `user_type` (student/institution/company dashboards; other types are logged out). `logout`: `Auth::logout()`, `session()->invalidate()`, `regenerateToken()`, redirect home with flash. |
| Registration | `RegisterController` with form requests: student (auto-login, `Registered` event → verification mail, JSON `redirect_url` = student dashboard), institution (no auto-login, `is_verified=false`, uploads logo/verification document/KTP/NPWP to Supabase Storage, sends `InstitutionRegistered` mail, dispatches `ValidateInstitutionDocumentsJob`), company (no auto-login, `verification_status=pending_verification`). Role is hard-coded per endpoint; passwords hashed with `Hash::make`; strong-password rule (`min(8)->mixedCase()->numbers()->symbols()`). |
| Password recovery | Laravel password broker (`Password::sendResetLink`, `Password::reset`), routes `password.request/email/reset/update`, 60-minute tokens. The failure message reveals that an email is not registered (left as is, user decision). |
| Middleware | `guest` on auth pages; `auth` + `check.user.type:<role>` on workspaces (cross-role → own dashboard with flash); `auth` + `admin` on `/admin/*` (403). |
| Views | `login`, `register`, three registrations: standalone HTML documents with inline `<style>`, three different layouts (yellow card on photo, dark card on photo, long blue card), English on login ('Welcome Back!', 'Sign In', fabricated '500+ Active Projects …'), 'Back to Home' / 'Back' / 'Kembali Ke Beranda', **`student-go-logo.png` (deleted from disk) shown as a broken image on 5 pages** (1 console error each). Forgot/reset/verify used `layouts.auth` (shared navbar) with no `<h1>`. |
| JS defects | Student password rules did not mention numbers although the server requires them; example passwords (one with the old brand); institution regency fetch without cancellation (J30); institution success path read `data.redirect_url` while the server sends `data.data.redirect_url`, so the user stayed on the form after an `alert()`; company page had a duplicated `<script>` tag and used `alert()` for success and errors. |
| Tests | 86/86 passed (1,082 assertions); none covered auth. |

## C. Implemented features

1. **One auth template** (`layouts/auth.blade.php`): compact header (wordmark → home, Tentang Kami, Kontak), skip link, 400 px column (640 px for chooser and registrations), optional side panel from 1024 px (contour motif; login = what each role does; registrations = "Yang perlu disiapkan"), inline flash alerts for the existing keys.
2. **Login**: 'Email atau username' + password with show/hide toggle, **'Ingat saya di perangkat ini'** (`remember`, approved), 'Lupa password?', 'Belum punya akun? Daftar', loading state on submit, client validation after blur/submit, server errors under the field, `old()` repopulation.
3. **Role chooser**: three bordered rows (icon, 'Daftar sebagai …', main task), same three destinations, 'Sudah punya akun? Masuk'.
4. **Registration wizard** (student 3 steps, institution 4, company 4): `x-ui.stepper` ('Langkah 2 dari 4 · Lokasi' in a polite live region), one heading per step with focus moved to it, Kembali / Lanjutkan, per-step client validation, the student's existing server step check (`validation.student.step`), error summary + field errors from 422 responses with a jump to the first failing step, shared upload component (dropzone, file chip, size warning) for photo, logo, verification document, KTP, NPWP, password rules checked while typing, confirmation match.
5. **Success states**: student → dashboard (`redirect_url`, unchanged); institution and company → in-page status panel with the server's message, 'Masuk' and 'Ke beranda' (replaces `alert()`; fixes the institution 'stuck on form' path).
6. **Province → regency** (J30): previous request aborted, late responses ignored, select disabled with spinner while loading, `aria-live` status ('27 kabupaten/kota dimuat untuk Jawa Barat.'), 'Coba lagi' on failure, old selection restored.
7. **Forgot / reset / verify** on the same template with an `<h1>`, password rules on reset, resend + logout on verify.
8. **Reusable pieces**: `x-ui.password` (shared component), `Alpine.data('sgRegions')` (ready for Phase 18 problem forms), `sgAuthForm`, `sgRegister`.

## D. Files changed

No file was deleted. No route, middleware, form request, model, migration, config, `.env` or `public/` file (other than the generated `public/build/`) was changed. Controllers and mailables changed only in user-facing strings (D-40, see the last rows).

| File | Change | Why |
|---|---|---|
| `resources/views/layouts/auth.blade.php` | Rewritten | The single auth template (task 6.3). |
| `resources/views/auth/login.blade.php` | Rewritten (now extends the layout) | 6.4; removes stats, English copy, broken logo; adds `remember`. |
| `resources/views/auth/register.blade.php` | Rewritten | 6.5 role chooser. |
| `resources/views/auth/student-register.blade.php` | Rewritten | 6.6; same 13 fields, same endpoints; RN-16, RN-17; number rule now shown. |
| `resources/views/auth/institution-register.blade.php` | Rewritten | 6.7; same 18 fields; regency select (J30); documents grouped in step 4 'Verifikasi dan akun'. |
| `resources/views/auth/company-register.blade.php` | Rewritten | 6.8; long form → 4-step wizard, same 17 fields; title 'Daftar sebagai Perusahaan'; local placeholders (J28). |
| `resources/views/auth/forgot-password.blade.php`, `reset-password.blade.php`, `verify-email.blade.php` | Rewritten | 6.9; same forms. |
| `resources/views/auth/components/field-error.blade.php`, `aside-roles.blade.php`, `wizard-feedback.blade.php`, `wizard-done.blade.php` | **New** | Partials shared by the auth views (convention: area `components/` sub-folder). |
| `resources/views/components/ui/password.blade.php` | **New** shared component | Password + toggle + optional rule list; reusable by later password-change forms. |
| `resources/js/student-go/auth.js` | **New** | `registerAuth(Alpine)`: `sgAuthForm`, `sgRegister`, `sgRegions`, `sgPassword`. |
| `resources/js/app.js` | +1 import, `registerAuth` call in `alpine:init` | Wire the module (additive). |
| `resources/css/student-go/auth.css` | **New** | Auth page styles (existing tokens only). |
| `resources/css/app.css` | +1 `@import` | Load `auth.css` after `public-pages.css`. |
| `resources/css/student-go/components.css` | Appended 'Phase 6' block (`.sg-password*`) | Styles of the new shared component; nothing above it changed. |
| `tests/Feature/Phase6/AuthFlowTest.php`, `AuthPagesTest.php`, `AuthBrandTest.php` | **New** (28 tests) | Backend behaviour, page/contract checks, product name in auth mail and messages. |
| `app/Mail/{VerifyEmailMail,InstitutionRegistered,InstitutionApproved,InstitutionRejected}.php` | Subject string only: 'Student Go' → 'Student Go' | D-40 (user, after the first report). |
| `resources/views/emails/institution-{registered,approved,rejected}.blade.php` | Copy only: 'Student Go' → 'Student Go', tagline 'Karya Untuk Bangsa' removed | D-40. |
| `app/Http/Controllers/Auth/RegisterController.php`, `EmailVerificationController.php` | Message strings only: 'Student Go' → 'Student Go' (no logic touched) | D-40. |
| `CLAUDE.md`, `COMPONENT_INVENTORY.md` (rows + section 8), `ASSET_INVENTORY.md` (section 20), `DECISIONS_AND_OPEN_QUESTIONS.md`, `PHASE_TRACKER.md` | Updated | Documentation of this phase. |
| `reports/phase6/**` | **New** | Screenshots and machine-readable evidence. |

## E. Authentication flow (as implemented, unchanged on the server)

- **Login**: GET `/login` (guest) → POST `/login` with `email` (email or username), `password`, optional `remember=1`. Client checks required fields first (no request if empty). Server: validation → rate limit → user lookup → `Hash::check` → `Auth::attempt` → `session()->regenerate()` → redirect `intended()` or the role dashboard. Failure: one message 'email atau password salah.' for wrong password and unknown account; after 5 failures 'terlalu banyak percobaan login …'.
- **Registration**: chooser → role form. Student: per-step POST `validation.student.step` (uniqueness checked on the server before leaving a step) → final POST `register.student.submit` (JSON) → auto-login → `/student/dashboard`. Institution: final POST `register.institution.submit` (multipart, JSON) → 201 → success panel; no login until verified (documents reviewed; verification info by email). Company: final POST `register.company.submit` → 201 → success panel → user signs in.
- **Logout**: POST `/logout` (auth, CSRF) → `Auth::logout`, session invalidated, token regenerated, remember cookie cleared → home with flash.
- **Session / guard**: `guest` sends signed-in users away from all auth pages; `auth` sends guests to `/login`; `check.user.type` sends other roles to their own dashboard with 'anda tidak memiliki akses ke halaman ini'; `/admin/*` 403 for non-admins.
- **Password recovery**: `/forgot-password` → broker email with a 60-minute token → `/reset-password/{token}?email=` → POST `password.update` → `/login` with success flash.
- **Email verification**: `/email/verify` (auth) shows the address, resend (POST `verification.resend`), and 'Keluar'.

## F. Security considerations

- **Validation**: server rules unchanged (form requests and controller rules); client checks only mirror them (required, email/url format, min/max length, the username and PIC-name patterns, password rules, confirmation) and never replace them. Forms carry `novalidate` so the browser does not block hidden wizard steps; the server still validates everything.
- **Passwords**: stored with `Hash::make` (verified in tests: stored value ≠ plain text, `Hash::check` passes); never repopulated after errors; no example password published (RN-17); `autocomplete` `current-password` / `new-password` set.
- **CSRF**: every form keeps `@csrf`; `fetch` sends the `_token` field in the `FormData`. No middleware disabled.
- **Session**: regeneration on login and invalidation + token regeneration on logout verified by tests; remember cookie set only when ticked and cleared on logout (live check).
- **Authorization**: role is fixed per endpoint; a posted `user_type=admin` is ignored (tests on all three registrations). Protection is server-side (middleware), not conditional rendering.
- **Enumeration**: login uses one message for both failures. **Forgot password still reveals unregistered emails** — the user chose not to change it in this phase (known limitation).
- **Logs**: `LoginController` still logs the login input (email/username) on every attempt (pre-existing; not changed).

## G. Testing results

### G.1 Automated

| Run | Result |
|---|---|
| Baseline before changes | `php artisan test`: 86/86, 1,082 assertions |
| Phase 6 tests | `tests/Feature/Phase6`: **28/28** (25 + 3 for D-40) |
| Full suite after D-40 | **114/114, 1,417 assertions** (final run; the Phase 5 test is green again). Earlier: **111/111, 1,402 assertions** (run at ~10:12). A later run (~10:20) gives **110/111**: `Phase5\PublicPagesTest::test_about_is_a_scrolling_article…` errors because `public/assets/photography/member/*-480.webp` were removed and `app/Support/TeamMembers.php` was switched to the PNGs **outside this session** (files changed 10:18; see H). Not caused by Phase 6. |
| `vendor/bin/pint --test tests/Feature/Phase6` | passed |
| `npm run build` | OK. CSS 200.83 → 209.71 kB (gzip 32.26 → 33.53); JS 12.09 → 19.82 kB (gzip 4.55 → 7.13) |
| `php artisan route:list --json` | 203 = 203 routes, identical to the Phase 5 snapshot (method, URI, name, action, middleware) |

### G.2 Required checks (prompt section 7)

| # | Check | Result | Evidence |
|---|---|---|---|
| 1 | Login with valid credentials | **PASS** | Tests (3 roles, email and username); live (student and company) |
| 2 | Wrong password | **PASS** | Test; live: 'email atau password salah.', stays on `/login` |
| 3 | Unknown account | **PASS** | Test (same message); browser check |
| 4 | Required fields | **PASS** | Test (server messages); browser: empty submit shows 2 errors, no request, focus on first field |
| 5 | Valid registration | **PASS** | Tests (student, institution, company); live student + company on the local test DB |
| 6 | Invalid registration | **PASS** | Tests (missing fields, weak password, missing KTP/NPWP/document); browser step errors |
| 7 | Email already used | **PASS** | Tests (student, company); live: blocked at step 1 by the server step check |
| 8 | Password confirmation | **PASS** | Test (422 'Konfirmasi Password Tidak Cocok'); browser 'Konfirmasi password tidak cocok.' after blur |
| 9 | Logout | **PASS** | Test; live (navbar form → home) |
| 10 | Session after login/logout | **PASS** | Tests (session id regenerated, CSRF token regenerated); live (dashboard → `/login` after logout; remember cookie set/cleared) |
| 11 | Protected route without login | **PASS** | Test + browser (`/student/dashboard` → `/login`) |
| 12 | Wrong-role route | **PASS** | Test (student → institution/company dashboards redirected, admin 403); live (student ↛ company, company ↛ student) |
| 13 | Redirect after login by role | **PASS** | Test (three roles) |
| 14 | Responsive desktop/mobile | **PASS** | 7 pages × 6 widths (360–1440): HTTP 200, 1 `<h1>`, no overflow, 0 console errors; every wizard step at 360/390/768: 42/42 without overflow, stepper inside the viewport (`step-overflow.json`) |
| 15 | Error feedback and loading state | **PASS** | Browser: field errors + summary, `aria-invalid` + `aria-describedby`, spinner/`aria-busy` on submit and Lanjutkan, success panel focused |
| — | Forgot/reset full flow on the log mailer | **PASS** | Live on a separate server (port 8001, `MAIL_MAILER=log` in the process env; `.env` untouched): mail written to the log, link opened, password changed, login with the new password |
| — | Rapid province switching (J30) | **PASS** | 12 changes at 50 ms → final list equals the API response for the last province; status announced |
| — | Keyboard | **PARTIAL** | Skip link is the first Tab stop; every focused control on `/register` shows an outline; Enter on a step = Lanjutkan; Esc not applicable. A full keyboard-only completion of each registration was **NOT TESTED** end to end. |
| — | Screen reader | **NOT TESTED** | No assistive technology run (D-24 open). Names/roles checked in the DOM only. |
| — | `prefers-reduced-motion` | **NOT TESTED separately** | Relies on the global Phase 3 rule (transitions and spinner neutralised); no new animation added. |
| — | Contrast of new pairs | **PASS** | 12 pairs measured: text 5.68–14.50:1, UI parts 3.76–4.81:1 (gray-400 unmet-rule icon 3.76:1, success-600 panel border 4.81:1) |
| — | Institution registration live | **NOT TESTED live** (by agreement) | Would upload KTP/NPWP to Supabase Storage; covered by a feature test with fake files and a faked queue/mail |

Browser checks: `reports/phase6/interaction/interaction-checks.json` (53/53 on the final run; an earlier run had 1 timing failure in the test script itself, fixed by waiting for the focus). Live checks: `reports/phase6/live/live-checks.json` (18/20 on the first run; the 2 failures were a focus-timing bug in `auth.js` — **fixed** (focus now waits for the `x-show` paint) — and a missing navigation wait in the script; both re-checked and recorded as PASS in the same file).

Throwaway accounts: `uji.fase6.mhs@example.test` (student) and `uji.fase6.prs@example.test` (company), random passwords never printed. Deleted afterwards in one transaction (2 `users`, 1 `students`, 1 `companies`; no other rows referenced them); user count back to 908. No file was uploaded.

### G.3 Acceptance criteria (section L)

| # | Criterion | Met | Evidence |
|---|---|---|---|
| 1 | One auth template and one stepper render login, chooser, three registrations and password reset | Yes | All 8 views extend `layouts.auth` (test `test_every_auth_page_uses_the_one_template`); the 3 registrations use `x-ui.stepper` with `sgRegister` |
| 2 | No invented statistic; Indonesian copy | Yes | Test `test_copy_is_indonesian_without_invented_numbers_or_old_brand` (no 'Student Go', no English UI words, no 'N+' figures); screenshots |
| 3 | `/forgot-password` 200 without Vite exception | Yes | 200 at 6 widths, test; (fixed in Phase 3, re-verified) |
| 4 | Field names, method, action, enctype match Phase 0 | Yes, with the approved `remember` addition | Rendered-form diff before/after: only `+ remember`; test `test_form_contracts_match_the_phase_0_inventory`; `accept` lists unchanged |
| 5 | Province → regency correct after rapid changes; loading/change announced | Yes | Browser check (12 × 50 ms), `aria-live="polite"` status |
| 6 | Validation after blur/submit, not on first render | Yes | Browser checks (no error on load or on tabbing through untouched fields); test (no `aria-invalid` on first render) |
| 7 | No horizontal scroll at 360/390/768 on any step; stepper fits at 390 | Yes | `step-overflow.json` 42/42 |
| 8 | Password toggle and required markers retained; password rules explained | Yes | Toggle on every password field (name, `aria-pressed`, `aria-controls`); `data-required` asterisks; 4 rules incl. the previously missing number rule |

Before/after for the audit screenshots: G04 `/login` (`before/login_*.png` → `after/login_*.png`), G05 `/register`, G06 `/register/student` (+ `interaction/register-student_step*_390.png`), G07 `/register/institution` (+ steps 1–4 at 390), G08 `/register/company` (+ steps 1–4 at 390).

## H. Known limitations, blockers and decisions needed

1. **External change during this session** — the user said to ignore it; the final full run is green again (114/114): at 10:18 the member photos in `public/assets/photography/member/` were replaced, their `-480.webp` derivatives removed, and `app/Support/TeamMembers.php` pointed to the PNGs; `Phase5\PublicPagesTest` now errors (it opens the WebP files). Phase 6 did not touch these files and did not restore them.
2. **RN-24 / D-40: RESOLVED** at the user's request ('rubah jadi student go'): subjects, institution email views and auth flash messages now say Student Go (test `AuthBrandTest`). Still out of reach without `.env`/deploy changes: links in emails use `APP_URL` (`student-go.railway.app`), the sender name is `MAIL_FROM_NAME`, and the password-reset email is Laravel's default English notification whose header reads `APP_NAME`. `VerifyEmailMail` (view `emails.verification`) and `WelcomeCompany` (`emails.company.welcome`) point to views that do not exist (pre-existing; neither is sent by the current flows).
3. **Account enumeration on `/forgot-password`** kept by decision ('email tidak ditemukan …').
4. **Server messages are unchanged** and therefore inconsistent in case ('email atau password salah.', 'Email Sudah Terdaftar'); they come from controllers/form requests.
5. **Company `pic_name`/`pic_position`** are required by `CompanyRegisterRequest` but not saved by `RegisterController::registerCompany` (pre-existing; not touched).
6. **Terms/Privacy pages do not exist**; the consent label has no links (RN-16 answer). Add links when the pages exist.
7. **Copy is DRAFT (D-23 b)**: page descriptions, role sentences, 'Yang perlu disiapkan' lists, step descriptions, the institution verification sentence (no duration promised; D-09 open), success-panel 'next step' lines. Please review the texts in the screenshots.
8. **Region data**: the database now holds real regencies for 36 provinces (more than the Phase 0 note of 5 provinces); D-15 answered as out of scope anyway.
9. JS bundle grew by 7.7 kB (2.6 kB gzip) because `auth.js` is part of the single app bundle (D-20 keeps one bundle).
10. Not tested: screen reader, full keyboard-only registration, live institution registration (see G.2).

## I. Shared file impact

| Shared file | Change | Impact on later phases |
|---|---|---|
| `resources/views/layouts/auth.blade.php` | Rewritten | Used only by auth views (verified with grep). No workspace page uses it. |
| `resources/views/components/ui/password.blade.php` | New | Optional for Phases 12, 20, 25 (password change). Existing components unchanged. |
| `resources/css/student-go/components.css` | Appended `.sg-password*` block at the end | No existing rule edited. Parallel sessions appending to the same file may get a trivial merge conflict at the end of the file. |
| `resources/css/app.css` | +1 import line (`auth.css`) | Same: a one-line conflict is possible if another session adds an import in the same place. |
| `resources/js/app.js` | +1 import, `alpine:init` now calls `registerAlpine` and `registerAuth` | Phases adding their own Alpine registrations should add a call in the same block. |
| `resources/css/student-go/auth.css`, `resources/js/student-go/auth.js` | New | `.sg-auth*`, `.sg-role-*`, `.sg-wizard*` are page-specific; `sgRegions` is designed for reuse by Phase 18 (problem create/edit). |
| Navbar, footer, `layouts/app.blade.php`, tokens, typography, other `x-ui.*` | **Not changed** | — |

## J. Handover notes for the Student, Institution and Company workspace sessions

- Do not edit `resources/views/auth/**`, `layouts/auth.blade.php`, `auth.css` or `auth.js` from a workspace phase; they are complete and tested by `tests/Feature/Phase6`.
- Login redirects and the role middleware are unchanged: after login each role lands on its dashboard (`student.dashboard`, `institution.dashboard`, `company.dashboard`); cross-role URLs bounce to the user's own dashboard with the flash 'anda tidak memiliki akses ke halaman ini' (rendered by `layouts.app` as a toast).
- Student registration still lands on `/student/dashboard` with no flash (JSON path). Institution and company do not log in after registering.
- For password-change forms use `<x-ui.password name="password" rules autocomplete="new-password" required />` and `data-sg-match="password"` on the confirmation; wrap the form in `x-data="sgAuthForm"` + `@submit="submit($event)"` to get validation after blur and a loading state with native submission.
- For dependent province → regency selects (Phase 18): wrap both selects in `x-data="sgRegions({ url: '/api/regencies/__ID__', province, regency, regencies })"`, bind `x-model="province" x-ref="province" @change="load()"` and `x-model="regency" x-ref="regency" x-bind:disabled="loading"`, and add a `{id}-status` element with `aria-live="polite"` (see `institution-register.blade.php`).
- In Alpine methods use `this.$root`, not `this.$el` (`$el` is the element that triggered the call), and wait one paint after changing an `x-show` before moving focus.
- Test fixtures: `tests/Feature/Phase1/Concerns/BuildsPhase1Data` + `Notification::fake()`, `Mail::fake()`, `Queue::fake()`, `Storage::fake('public')` for anything that registers users.
- The full suite is green (114/114) at the end of this phase.
