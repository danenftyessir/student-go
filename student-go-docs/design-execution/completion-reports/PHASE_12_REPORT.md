# Phase 12 — Profile & Portfolio: completion report

Date 2026-10-31 · branch `docs/progress-phase-11-26` (no commit, D-27) · run in parallel with the Phase 14 session.
**Recommended tracker status: COMPLETE** (the tracker was not edited during parallel execution; see §8).

## 1. Gate check

| Item | State | Evidence |
|---|---|---|
| Phase 1, Phase 3 (hard deps) | COMPLETE | `PHASE_TRACKER.md` rows 1 and 3 (both copies `design-execution/` and `design-execution/`) |
| D-03 display name | ANSWERED (b) | `Student::display_name` / `initials` used for h1, avatar, meta |
| D-04 contact | ANSWERED (a) | `Student::canViewContact()` (Phase 1, unchanged) decides; view only renders |
| D-19 photography | **Answered this session:** student's own uploaded photo only, in the avatar slot; no documentary or catalogue photo; initials otherwise | user answer via AskUserQuestion, 2026-10-31 |
| D-23 copy | **Answered this session:** option (b), Claude drafts; copy below is DRAFT | user answer, 2026-10-31 |
| D-31, D-32 | ANSWERED / RESOLVED | no photo moved or given a new public place (no `og:image`); certificate links unchanged, certificate assets untouched |

Please record the two new answers (D-19 and D-23 for the profile pages) in `DECISIONS_AND_OPEN_QUESTIONS.md` when the tracker is updated.

## 2. Files changed

| File | Change |
|---|---|
| `resources/views/student/profile/index.blade.php` | Rewritten: shared portfolio partial + owner script (copy link with toast, visibility switch). Inline `<style>`, custom toast and share modal removed |
| `resources/views/student/profile/public.blade.php` | Rewritten: same partial without owner actions; `<title>`, meta description, canonical, Open Graph and Twitter meta from real data |
| `resources/views/student/profile/edit.blade.php` | Rewritten: five sections + section nav, separate password form, `<x-ui.*>` fields |
| `resources/views/student/profile/components/portfolio.blade.php` | New: identity header, story reading column, completed projects, skills/interests, contact, share link |
| `resources/views/student/profile/components/repeat-list.blade.php`, `repeat-row.blade.php` | New: server-rendered `stories[]` / `skills[]` / `interests[]` rows |
| `tests/Feature/Phase12/StudentPortfolioTest.php` | New: 11 tests, 104 assertions |

Not changed: controllers, requests, models, services, routes, `app.css` or any shared CSS/JS/component/layout, `.env`, DB schema, `public/`. No new CSS file: the pages use `sg-*` components plus Tailwind utilities on the design tokens, so the global `app.css` (also being edited by Phase 14) was left alone.

## 3. Acceptance criteria (section L)

| # | Criterion | Result | Evidence |
|---|---|---|---|
| 1 | Edit form loads every stored value incl. first and last name | PASS | `test_edit_form_loads_every_stored_value` (13 fields incl. arrays, selects); live: all 8 scalar fields prefilled for a seeded student. Fixed a latent crash: the old view called `json_decode()` on attributes already cast to array |
| 2 | Display name and initials match the account everywhere | PASS | live: nav "Putra Rachman" = h1 "Putra Rachman", avatar `PR`; tests assert h1, `data-initials`, and that the stale `users.name` never appears |
| 3 | Guest response contains no email or phone | PASS | Phase 1 `ContactPrivacyTest` (9) still green; new test; live guest HTML and unconnected-student HTML contain neither email nor a phone pattern |
| 4 | No hero image, glass tiles or gradient button | PASS | tests assert no `dashboard-student3.jpg`, `backdrop-blur`, `bg-gradient`; live DOM check `[]` |
| 5 | Meaningful `<title>` and meta description | PASS | live: "Portofolio Putra Rachman — Student Go"; description "Portofolio KKN Putra Rachman, Kebidanan di Universitas Trisakti. 1 proyek selesai."; `og:title`, `og:url` = real profile URL |
| 6 | Empty story shows an honest empty state | PASS | owner: "Belum ada cerita" + "Tulis cerita" link to the edit form; public: "{Nama} belum menulis cerita pengalaman." (0 of 700 seeded students have a story; story-present path verified live by adding and then removing one) |
| 7 | Contrast and keyboard | PASS | measured: 134 text nodes on 4 page states, 0 below 4.5:1 (large 3:1); form-control borders min 3.45:1. Tab audit: 68 / 69 / 55 stops on profile / edit / public, 0 without a visible focus ring; one `<h1>`, `lang="id"` |

## 4. Other checks requested

- **Copy link:** clicking "Salin tautan portofolio" (secondary button) put `http://127.0.0.1:8000/profile/ekanabila1000` (= `route('profile.public')`, the URL `getShareLink` returns) on the clipboard and showed the toast "Tautan portofolio disalin." Falls back to `execCommand` on non-secure origins.
- **Section nav:** click and Enter on a link scroll to the section and move focus to its heading (`judul-keamanan`, `judul-kontak`); the next Tab reaches the first field; `aria-current` follows scrolling; on narrow screens the 5 links sit as a grid above the form; smooth scroll is off under reduced motion.
- **Edit save (seeded test DB, then restored):** added a story and a skill and left an empty "minat" row → redirect to `/student/profile`, toast "Profil berhasil diperbarui!", story rendered in the reading column, other 8 fields unchanged; then removed both rows and saved → stories 0, skills 0 (original state).
- **Password form:** still a separate `PUT student.profile.password.update` form; wrong current password → back to edit with a `current_password` error, still logged in (test).
- **Contract:** routes 203 = 203; all 16 input names present; two `PUT` forms with the same actions, multipart on the profile form; `profile_photo` accept/2 MB unchanged; validation rules, flash keys and redirects untouched; the toggle-visibility endpoint and its JSON contract are unchanged.
- **Viewports:** 360/390/768/1024/1366/1440 for profile (with project), profile (empty), edit, public (with project), public (empty): 30/30 without page overflow; screenshots in `reports/phase12/after/`. Before = Phase 0 baseline (`baseline/screenshots/`, S18/S19/S23).
- **Console/network:** 0 errors in the final run.
- **Suite:** 291 passed (3,093 assertions) before → 302 passed (3,202) after.

## 5. Copy (DRAFT, D-23 b)

Section headings: Cerita pengalaman · Proyek selesai · Keterampilan · Dipakai dalam proyek selesai · Minat · Kontak · Tautan portofolio. Empty states: "Belum ada cerita / Tulis pengalaman KKN Anda: apa yang dikerjakan, bersama siapa, dan apa yang dipelajari. Cerita tampil di profil publik."; "Belum ada proyek selesai / Proyek muncul di sini setelah statusnya Selesai. Mulai dengan melamar masalah yang sesuai minat Anda." Contact note: "Email dan WhatsApp hanya terlihat oleh koneksi yang sudah diterima." Share text: "Lihat portofolio KKN saya di Student Go: {nama}" (was "…di Karsa", D-01). Edit help texts as in the view.

## 6. Deviations and decisions for the user

1. **"Verified" projects.** No verification column exists, and a project becomes `completed` either when the student submits the final report or when the institution sets it. The list therefore shows all `completed` projects (as before; on the public page only those marked visible), and per project shows the real evidence: "Diulas instansi · nilai x dari 5" (institution review) and the certificate link. Filtering to reviewed-only would hide real work and needs your decision.
2. **Empty repeat rows are not submitted.** The existing rule `stories.*|skills.*|interests.*: string` rejects the `null` that an empty row becomes, so the old form failed with "Setiap story harus berupa teks." when a row was left empty. The form now disables empty rows on submit. Server validation is unchanged.
3. **Share modal → inline links.** The modal (WhatsApp/Email/LinkedIn/X + copy) became the copy button plus plain share links under "Tautan portofolio". Same targets, no modal.
4. **Visibility toggle** is now a labelled switch (`role="switch"`) instead of an eye-icon button with a changing label.
5. **Skills:** the student's own `skills` are shown (they were never shown before); the project-derived skills shown previously stay, labelled "Dipakai dalam proyek selesai".
6. **Meta tags** are pushed through the `styles` stack (inside `<head>`), so the shared head partial was not edited.
7. **Asset decision (AD-09):** `public/dashboard-student3.jpg` → **Deprecate (usage)** on the three profile pages; it is now referenced by no view, and the file is untouched. Record it in `ASSET_INVENTORY.md` together with the tracker update (not edited during the parallel run).
8. **Incident:** during the first browser run this machine briefly lost DNS (Supabase host and CDN unresolvable). Six screenshots captured in that window were Laravel debug pages showing the DB host and a cookie fragment. They were **deleted immediately** and retaken with a guard that refuses to save any page containing an exception. Nothing from them was copied anywhere.

## 7. Blockers

None for this phase. Open items are the decisions in §6 (1) and approval of the DRAFT copy.

## 8. Tracker (to apply after both parallel sessions finish)

`| 12 | Profile & Portfolio | COMPLETE | 1, 3 | — | D-03, D-04, D-19, D-23, D-31, D-32 | 2026-10-31 | docs/progress-phase-11-26 (no commit) | All 7 AC met. Shared portfolio partial for /student/profile and /profile/{username} (no hero/glass/gradient, story reading column, completed projects with institution + year, skills chips, contact per D-04, OG/meta from real data); edit = 5 sections + section nav, separate password form, all stored values loaded (latent json_decode crash fixed). D-19 (own photo only) and D-23 (b) answered. Routes 203=203, tests 291→302, 30/30 viewports no overflow, contrast 0 fail, focus 0 missing. Report: completion-reports/PHASE_12_REPORT.md. |`

Next phases the roadmap allows (hard deps 1 and 3 only): 13 Friends (soft dep 12 now satisfied), 15, 16, and any other NOT_STARTED phase whose hard dependencies are COMPLETE; 27 still needs every page phase 4–26.
