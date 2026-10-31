# PHASE 06 — Auth

> Source of truth for status is `student-go-docs/design-execution/PHASE_TRACKER.md`. This file is the work order for exactly one phase. Read `CLAUDE.md` and `student-go-docs/design-execution/EXECUTION_ROADMAP.md` first, execute only this phase, update the tracker, report, then stop.
> Evidence labels: VERIFIED / OBSERVED / INFERRED / UNKNOWN / BLOCKED (definitions in `DISCOVERY_REPORT.md` section 1).

| Field | Value |
|---|---|
| Phase | 6 — Auth |
| Group | Public and authentication |
| Hard dependencies (must be COMPLETE) | Phase 3 (Shared Shell & Components) |
| Soft dependencies (recommended first) | Phase 1 (Critical Fixes & Data Integrity) |
| Decisions that gate this phase | D-01 Product name, wordmark and assistant name, D-02 Language policy and copy deck, D-15 Placeholder region data, D-23 Content ownership for new or rewritten copy, D-30 Provenance and consent for existing photographs in `public/` |
| Effort estimate (planning guess, not a commitment) | L |
| Roles touched | Guest |
| Audit references | G04, G05, G06, G07, G08; J8, J9, J15, J17, J28, J30; audit D2, G |
| Tracker status at planning time | NOT_STARTED |

## A. Phase overview

**Goal.** One authentication template and one stepper for login, role selection, three registrations and password reset; Indonesian copy; no invented statistics; validation after interaction; the dependent province-to-regency selects stop showing stale options.

**In scope**

- `/login`, `/register`, `/register/student` (3 steps, 13 fields), `/register/institution` (4 steps, 18 fields incl. KTP/NPWP uploads), `/register/company` (single page, 17 fields).
- `/forgot-password` and the reset flow: the route exists and currently fails with a ViteException (VERIFIED) because `resources/views/layouts/auth.blade.php` references `resources/css/auth-institution.css`, which is missing from the Vite manifest.
- Shared auth template, shared stepper, shared upload component, one 'Kembali' wording.
- `/api/regencies/{provinceId}` consumers: abort previous requests, show loading, announce changes (J30).

**Out of scope**

- Changing authentication logic, validation rules, field names, roles or redirects.
- Creating real accounts outside the local test database.
- New auth features (social login, 2FA).

**Evidence basis.** The audit (OBSERVED, 2026-10-28, port 8001) plus a read-only look at the running app on 2026-10-30. No repository source was read when this file was written; every statement about templates, controllers, models, middleware and tests is UNKNOWN until Stage One (Phase 0) fills `DISCOVERY_REPORT.md` section 12.

## B. Pages and routes

| Route pattern | Role | Audit status | Evidence | Notes |
|---|---|---|---|---|
| /login | Guest | Inspected | OBSERVED; title VERIFIED | Split layout with invented stats; <title> 'Login - Student Go' (VERIFIED); lang id (VERIFIED). |
| /register | Guest | Inspected | OBSERVED; title VERIFIED | Role picker; <title> 'Register - Student Go' (VERIFIED). |
| /register/student | Guest | Inspected (audit); failing locally on 2026-10-30 | OBSERVED; local failure VERIFIED | 3 steps, 13 fields (OBSERVED). On 2026-10-30 it returns a QueryException on a `universities` query from app/Http/Controllers/Auth/RegisterController.php line 67 because the DB is unreachable (VERIFIED). |
| /register/institution | Guest | Inspected | OBSERVED | 4 steps, 18 fields incl. KTP/NPWP uploads (OBSERVED). |
| /register/company | Guest | Inspected | OBSERVED | No stepper, 17 fields, titled 'Daftar Sebagai Company' (OBSERVED). |
| /forgot-password | Guest | BLOCKED in audit; route exists (VERIFIED) | VERIFIED | Audit tool refused it. Live 2026-10-30: HTTP 500 ViteException from resources/views/layouts/auth.blade.php: 'Unable to locate file in Vite manifest: resources/css/auth-institution.css' (VERIFIED). Cause INFERRED: asset not in vite.config inputs or stale build. |
| /api/regencies/{provinceId} | JSON API | Verified via form | OBSERVED | No visual; consumed by registration (Phase 6) and problem creation (Phase 18). Race condition J30. Preserve response shape. |
| Password reset and email verification routes | Guest | Not discovered | UNKNOWN | Standard Laravel auth names, if present. Stage One lists them. |

**Audit screenshot IDs for this phase** (audit section L; OBSERVED). Page data below is quoted or summarised from the audit; the screenshots themselves are in the PDF, not in this repository.

| ID | Page | URL | Keep (audit) | Design direction (audit) | Backlog |
|---|---|---|---|---|---|
| G04 | Login | /login | Form pendek, toggle tampil password, penanda field wajib, tombol primer lebar penuh. | Template autentikasi tunggal: kartu form 400 px di tengah, logo/wordmark Student Go di atas, opsional foto dokumenter di kolom kiri tanpa statistik. | J8, J9, J15, J17 |
| G05 | Pilih jenis akun (Register) | /register | Struktur pilihan peran dengan judul + deskripsi + panah sudah jelas dan layak menjadi pola baku. | Tiga baris pilihan dengan border tipis, ikon outline, dan deskripsi yang menyebut tugas utama tiap peran; tautan “Sudah punya akun? Masuk”. | J8, J17 |
| G06 | Daftar sebagai Mahasiswa | /register/student | Pemecahan form panjang menjadi langkah, ikon di dalam input, dropzone foto opsional. | Template autentikasi bersama + stepper bersama; form satu kolom 560–640 px; validasi setelah blur. | J8, J15, J17 |
| G07 | Daftar sebagai Instansi | /register/institution | Urutan langkah yang logis untuk instansi pemerintah (data, lokasi, PIC, verifikasi). | Sama dengan pendaftaran mahasiswa di atas template bersama; langkah verifikasi menjelaskan dokumen apa yang diperlukan dan berapa lama proses review. | J8, J17, J30 |
| G08 | Daftar sebagai Perusahaan | /register/company | Pengelompokan field per section dengan subjudul, dropzone logo, aturan password dijelaskan. | Wizard bersama, field yang sama dengan form edit profil perusahaan agar tidak ada field yang hilang setelah daftar. | J8, J17, J28 |

## C. Existing implementation

- [VERIFIED live] `/login` title 'Login - Student Go', `lang="id"`, one form with 3 inputs; h1 'Connect. Collaborate. Create Impact.'. `/register` title 'Register - Student Go', h1 'Bergabung dengan Student Go'.
- [OBSERVED] Login: split layout, left photo of a village with overlay and fabricated stats ('500+ Active Projects, 50+ Universities, 10.000+ Students, 200+ Institutions'; reality 77 problems, 700 students, 79 institutions); English 'Welcome Back!', 'Sign In'; no web font requested (system fallback); password show/hide toggle and required markers are good and kept.
- [OBSERVED] Three different registration layouts: student = yellow 3-step card on a photo, institution = dark 4-step card on a photo, company = one long card on a light-blue background titled 'Daftar Sebagai Company'; back links read 'Kembali Ke Beranda', 'Back', 'Kembali'.
- [VERIFIED 2026-10-30] `/register/student` currently returns a QueryException: a `universities` query in `app/Http/Controllers/Auth/RegisterController.php` line 67 fails because the database is unreachable. `/forgot-password` returns the ViteException above.
- [OBSERVED] Province dropdown loads 34 provinces; regencies come from `/api/regencies/{id}`; for most provinces the API returns a single placeholder ('Kota <Provinsi>'); rapid province changes show the previous province's list (no request cancellation).
- [BLOCKED] Auth pages could not be measured at 390 px while logged in (redirect to `/`).

**Repository facts for this phase: UNKNOWN until Stage One.** Locate and record in `DISCOVERY_REPORT.md` section 12 before editing: the three register views and the auth layout; RegisterController (VERIFIED path) and LoginController; forgot/reset views and routes; `vite.config.js` inputs for `auth-institution.css`; field names for each form; upload handling; Alpine stepper code; the regency fetch script.

## D. Problems found (audit findings)

| Backlog ID | Finding | Evidence |
|---|---|---|
| J9 (P1) | Invented statistics on the login panel. | OBSERVED |
| J8 (P1) | English copy on login; 'Company' in registration; mixed back-link wording. | OBSERVED |
| J17 (P2) | Three different registration layouts and steppers. | OBSERVED |
| J15 (P2) | No web font loaded on auth pages (system UI fallback). | OBSERVED |
| J30 (P3) | Regency select shows stale response when the province changes quickly. | OBSERVED |
| J28 (P3) | Placeholder text from other markets in company forms. | OBSERVED |
| New (VERIFIED) | `/forgot-password` fails with ViteException: missing manifest entry for `resources/css/auth-institution.css` in `layouts/auth.blade.php`. | VERIFIED |
| Blocked | `/forgot-password` and password-reset emails were never inspected in the audit. | BLOCKED -> Stage One / this phase |

## E. Approved design direction

**Approved by the user (binding)**

- Hybrid direction (AD-04): Ruang Kerja workbench foundation; Catatan Lapangan editorial layer only where reading and storytelling matter. This phase uses none of it unless stated below.
- Principles (AD-05) and functional preservation (AD-06). Tokens, fonts and components below come from the asset package and are PROPOSED until D-17 is approved; existing assets are reconciled under AD-09 (section H); D-16 records that reference documents B and C were not found.

**Proposed for this phase** (audit sections I and L, adapted; status PROPOSED until the user approves the gating decisions)

- Single auth template: 400 px form card centred, Student Go wordmark above, optional documentary photo in a left column on wide screens (no statistics, never required, D-19). Registration uses a 560-640 px single column with the shared stepper (horizontal from 640 px, 'Langkah 2 dari 5' under it).
- Role selection (`/register`): three bordered rows with outline icon, role name and one sentence about the main task; link 'Sudah punya akun? Masuk'.
- Institution step 4 explains which documents are needed and how long review takes (copy per D-09/D-23, no promise beyond the real process). Company form uses the same fields as company profile edit (parity avoids the 'missing field after registering' bug, J5).
- Validation after blur or submit; errors via `.sg-error`; success state minimal.

**Guardrails that bind this phase** (audit section H, numbered as in `EXECUTION_ROADMAP.md`): 1, 2, 5, 6, 8, 9.

## F. Implementation tasks (do in order)

6.1. Gate check: D-01 (name/wordmark), D-02 (copy), D-15 (placeholder regions), D-23 (copy owner).

6.2. Fix the missing manifest entry for `/forgot-password`: determine from `vite.config.js` whether `resources/css/auth-institution.css` should be an input or whether the layout reference is stale; make the smallest correction; rebuild; verify the page renders. If the file is no longer needed, remove the reference and record it.

6.3. Create the auth layout and card using the Phase 3 auth layout; set title, lang, favicon; remove invented stats and Indonesian-English mixtures.

6.4. Login: rebuild with the same inputs/names (email/username, password, remember if present), show/hide password button with an accessible name, error summary, link to forgot-password and register.

6.5. Role chooser: three rows, same destinations (`/register/student`, `/register/institution`, `/register/company`).

6.6. Student registration: shared stepper, three steps (Data Pribadi, Data Akademik, Buat Akun), same 13 field names, optional photo dropzone using the shared upload component; universities list from the same source.

6.7. Institution registration: four steps (Data Instansi, Lokasi, Penanggung Jawab, Akun & Verifikasi), 18 fields, KTP/NPWP uploads with file chips; province/regency selects with request cancellation and a loading state; `aria-live` announcement when options update.

6.8. Company registration: convert the single long form to the shared wizard with the same 17 fields and section subheads (Informasi Dasar, Kontak, Media Sosial); fix the title ('Daftar sebagai Perusahaan'); replace foreign placeholders with local examples (cooperative, desa, kabupaten).

6.9. Forgot/reset pages: same template; verify the full flow on the local test mail driver only (log driver). Do not send real email.

6.10. Test every form: field names unchanged (compare with the Phase 0 form inventory), validation errors render per field, successful registration redirects as before on the local test database with a throwaway account that is removed afterwards if approved.

6.11. Measure at 360/390/768/1440 px; fix any overflow (the stepper must not overflow at 390 px).

6.12. Update the tracker and report with G04-G08 before/after.

## G. Components

| Component | Source | Notes |
|---|---|---|
| Auth layout and card | Phase 3 | Single template. |
| Stepper | package `.sg-stepper` | One implementation for 3-, 4- and 5-step flows. |
| Form fields, password toggle, error text | package `.sg-field`, `.sg-input`, `.sg-error` | Validation after blur/submit. |
| Upload (dropzone + file chip) | package `.sg-dropzone`, `.sg-file-chip` | Photo, KTP, NPWP, logo. |
| Role chooser rows | page-specific | Bordered rows with icon. |
| Province/regency dependent selects | existing JS + abort/loading | `aria-live` status. |

## H. Assets (Source A existing `public/`, Source B custom package, Source C usage)

> Policy AD-09. Three sources, none disposable: **A** existing assets in `public/`, **B** the custom package `student-go-assets/`, **C** `resources/` (shows what is really used). Every asset has one decision: Retain, Reuse, Adapt, Replace or Deprecate. Originals are never deleted, overwritten, moved or renamed by this phase; copying anything into a production directory happens only if a task above says so. Full inventory, rights and integration map: `student-go-docs/design-execution/ASSET_INVENTORY.md`. Evidence labels as in `DISCOVERY_REPORT.md` section 1.

### H.1 Source A: existing application assets that matter in this phase

| Asset | Decision | What happens in this phase | Evidence |
|---|---|---|---|
| `login-register-view.jpg` | Replace (usage) / Retain (file) | Auth screens stop using it as a background; the file stays. | VERIFIED (`/login`); other auth pages BLOCKED |
| `student-go-logo.png` | Retain / Replace (conditional) | Follows Phase 3. | VERIFIED |
| `SAGO-background.png` | Retain | Not an auth asset; untouched. | INFERRED |

### H.2 Source B: custom package assets available for this phase

**Original SVG, brand, fonts, icons and CSS (made for this project; in the package; licences in `ASSET_INVENTORY.md` section 2)**

- Wordmark SVG, Lucide icons for roles (user, building, briefcase), optionally `motifs/contour-field.svg` behind the card edge.

**Catalogue photographs (Source B: licence-checked, NOT downloaded; deferred by D-33; used only when no Source A photograph fills the slot)**

- Default is no photo (`contour-field.svg` instead). If the user wants one documentary photo on the left column: an existing field photograph first (D-30), a catalogue photo (`photography/homepage/*` or `about/*`) only if D-33 and D-19 allow.

### H.3 Reconciliation for this phase

Overlap 5: prefer `contour-field.svg` plus the wordmark to any photo on the auth panel; the old photo stays unused on disk.

Overlap comparisons that touch this phase are in `ASSET_INVENTORY.md` section 11; where a Source A asset and a Source B asset serve the same purpose, that table gives the starting recommendation and this phase re-checks it against the real page.

### H.4 Native UI (no asset; plain HTML/CSS)

- Forms, text, dividers.

### H.5 Do not touch in this phase

- Student profile photographs and uploads, institution/company logos, verification documents: never moved or re-encoded; never shown in a new public place (D-04, D-31).
- Three guest auth pages (`/register/institution`, `/register/company`, `/forgot-password`) return HTTP 500 locally, so their asset usage is unread until the causes are fixed (not an asset change).

## I. Dependencies

- **Phases:** hard — Phase 3 (Shared Shell & Components); soft — Phase 1 (Critical Fixes & Data Integrity).
- **Decisions:** D-01 (Product name, wordmark and assistant name), D-02 (Language policy and copy deck), D-15 (Placeholder region data), D-23 (Content ownership for new or rewritten copy), D-30 (Provenance and consent for existing photographs in `public/`). A gating decision that is still OPEN in `DECISIONS_AND_OPEN_QUESTIONS.md` means the dependent tasks stop and the agent asks the user; it does not guess.
- **Packages:** None.
- **Data / environment:** A reachable DB for the universities list and regency API; throwaway local test accounts.

## J. Functionality preservation

The global rules in `CLAUDE.md` section 7 apply in full. For this phase specifically:

- Form actions, methods, input names, file field names and `enctype` for login and all three registrations are unchanged.
- `/api/regencies/{id}` response shape and consumer element ids unchanged.
- Role redirects after login and logout unchanged.
- Password reset uses the same routes and token handling.
- Required-field markers and the password show/hide toggle remain.

## K. Testing plan

**Standard checks (every phase)**

- Viewport matrix: 360, 390, 768, 1024, 1366 and 1440 px wide. At each width `document.documentElement.scrollWidth <= window.innerWidth` (no page-level horizontal scroll). At most one horizontally scrolling container per table.
- State matrix for every changed page: default, empty, long content (76-character institution name, 65-character document title, 51-character email), missing media, validation error, and the role/permission variants that exist for that page.
- Keyboard: Tab through the whole page; focus is always visible; no keyboard trap; Esc closes overlays; exactly one <h1>; `<html lang="id">`.
- Contrast: text pairs >= 4.5:1, large text and UI parts >= 3:1 (use the measured table in docs/DESIGN_SYSTEM.md; re-measure any new pair). Check with `prefers-reduced-motion: reduce` emulated.
- Functionality: compare against the Phase 0 baseline (routes, form field names, query parameters, redirects). Submit forms only against the local seeded test database with test accounts found in the project's seeders; never against production.
- Console and network: no JavaScript errors; no request to a CDN that this phase removed; no 404 for a new asset.
- Automated: run the project's own test suite (command confirmed in Stage One; see CLAUDE.md section 4) and report pass/fail counts before and after.
- Visual: take screenshots of every changed page at the matrix widths and store them in the phase report; compare against the Phase 0 baseline and the audit's screenshot IDs.
- Assets: `git status` / `git diff --name-status` shows no deleted, renamed or overwritten file under `public/`; every asset the changed pages reference returns 200; nothing is copied into a production asset directory unless this phase's section H authorises it; certificate output (if the phase touches fonts, layout or the asset pipeline) is byte-comparable or visually identical to the Phase 0 baseline.

**Phase-specific checks**

- Submit each registration on the local test DB with valid and invalid data; compare responses with the baseline.
- Rapid province switching: final regency list matches the final province (script a change every 50 ms).
- `/forgot-password` renders and the reset mail is produced by the log/mail-trap driver only.
- Keyboard-only completion of each form; errors announced.
- No horizontal scroll at 360/390 px on any step.

*Test commands are not listed here because none could be verified without the repository. `CLAUDE.md` section 4 lists candidate commands that Stage One must confirm.*

## L. Acceptance criteria

The phase is COMPLETE only if every box can be checked with evidence in the phase report. Otherwise the status is PARTIAL (say which boxes failed) or BLOCKED (say what unblocks it).

- [ ] One auth template and one stepper component render login, role chooser, all three registrations and password reset.
- [ ] No invented statistic remains on any auth page; all visible copy is Indonesian (or the approved variant).
- [ ] `/forgot-password` returns 200 and renders; the missing Vite manifest entry no longer causes an exception.
- [ ] Every form's field names, method, action and enctype match the Phase 0 inventory (diff shows no change).
- [ ] Province-to-regency selects always show options for the currently selected province after rapid changes; loading and change are announced via `aria-live`.
- [ ] Validation errors show after blur/submit, not on first render.
- [ ] No horizontal scroll at 360, 390, 768 px on any step; stepper fits at 390 px.
- [ ] Password toggle and required markers retained; password rules explained.

## M. Risks and blockers

| Risk or blocker | Mitigation / what unblocks it | Blocks the phase? |
|---|---|---|
| Database unreachable (`universities` query fails) | Resolve D-12; otherwise only the static parts can be verified. | Yes for registration |
| `auth-institution.css` was intentionally removed | Check git history; remove the reference instead of re-adding. | No |
| Placeholder regency data hides real wrapping problems (D-15) | Test with long real names manually. | No |
| Creating test accounts pollutes data | Only on the local DB; remove with approval. | No |

## N. Expected deliverables

- New auth layout/card, stepper usage, and rewritten login, register, student/institution/company, forgot/reset views.
- Fix for the Vite manifest reference.
- Form-field diff against the baseline inventory (empty diff expected).
- `student-go-docs/design-execution/PHASE_TRACKER.md`: set Phase 6 to COMPLETE, PARTIAL or BLOCKED with date, branch/commit (if the user asked for one) and a one-line note.
- A phase report in the chat: what changed (files), what was verified (with the evidence), what failed or was skipped and why, decisions needed from the user, and the exact next phase the roadmap allows. Then stop and wait; do not start another phase.
