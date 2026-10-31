# PHASE 09 — Project Detail: completion report

| Field | Value |
|---|---|
| Phase | 9 — Project Detail (`phases/PHASE_09_PROJECT_DETAIL.md`); Session A (Student workspace), in parallel with Session B (Phase 19) and Session C (Phase 23) |
| Date | 2026-10-31 |
| Branch / commit | `…/Competition/Mirror/StudentGo`, branch `docs/project-documentation` as found (the user's own commits; HEAD `c1b1621`). Not switched, no commit, no stash (D-27). `CLAUDE.md` shows a modification that is not Session A's (path wording `design-execution` ↔ `design-execution`); left untouched |
| Recommended status | **COMPLETE**: all 6 acceptance criteria met (section 8.4). `PHASE_TRACKER.md` not edited (parallel-session rule) |
| Prerequisites | Phase 1 and Phase 3 (hard) COMPLETE in the tracker. Phase 8 (soft) recommended COMPLETE in its report; the tracker row still says NOT_STARTED because the tracker is updated at integration |
| Gates | D-02 ANSWERED (status words). D-14 answered in the Phase 8 session (seed images in `sg-storage`). D-17 ANSWERED (Source Serif 4). D-19 and D-30 are OPEN for this page, so **no documentary or catalogue photograph was placed**; only the problem's own `problem_images` are shown. Nothing was blocked by an open decision |
| Evidence | `reports/phase9/` (`before/`, `after/`, `interaction-checks.json`, `contrast.json`, `routes-after.json`) |

## 1. Executive Summary

`/student/browse-problems/{id}` is now a brief. A plain header carries the breadcrumb, the only h1 and one meta line (institution · project location · SDG · quota · deadline). A rail shows the student's **real** application state, read from their own `applications` row, plus the key facts. The rail comes first on phones and is sticky from 1024 px. The reading column has subheaded sections set in Source Serif 4 at 16/24 px, at most 68ch wide. Photos appear only after they actually load. Project location and institution address carry separate labels. Similar problems are three list rows.

The audit's J31 defect is gone: student 55's rejected application on #45 used to show a green 'Aplikasi Terkirim' card and now shows **Ditolak** in the danger tone with 'Lamaran Anda untuk masalah ini tidak diterima.' The apply link, eligibility logic, wishlist, share, view counter, breadcrumb and similar-problem links all work as before.

Results: routes 203 = 203; Phase 9 tests 16/16; browser checks 30/30; 32 screenshots across 6 widths and 5 application states, with no overflow, no clipped text and no small targets; every text pair at ≥ 5.21:1.

## 2. Initial Condition

Evidence: `reports/phase9/before/` (S03 = #1 as student 86, S04 = #45 as student 55, plus pending #52/27, reviewed #56/40, accepted #1/73) and `DISCOVERY_REPORT.md` §12.5.

- **Rail:** the controller passed only `$hasApplied` (an `exists()`), so pending, reviewed, accepted and rejected all rendered the same green 'Aplikasi Terkirim' card (VERIFIED in the before screenshots of all four).
- **Visual problems:**
  - White text on blue/green gradients (J10).
  - English text: 'Requirements', 'Apply Sekarang', 'Expected Outcomes', 'Views', 'Browse Problems', 'My Applications' (J8).
  - Emoji ⭐ 🔥 ⏰ (J26).
  - Difficulty shown in English in red/yellow/green.
  - A pulsing 'Mendesak' badge.
  - Fade-in animations and an inline `<style>` block.
- **Gallery:** a static `<img>` with `alt="Gallery N"` thumbnails, and no fallback (J12).
- **Location:** the header said 'Kota Malang, Jawa Timur' while the institution card said 'Jl. Diponegoro …, Kab. Cilacap, Jawa Ten…' (truncated), with neither labelled (J19).
- **Truncation:** the address, email and breadcrumb title were cut by ellipsis.
- **Metrics:** 6 clipped elements; 2 targets under 24 px; h1 at 101–121 px below the top of `<main>`.

## 3. Implemented Features

- **Header:** `x-ui.breadcrumb` (Dashboard / Telusuri Masalah / full title), the h1, a `.sg-summary` meta line, Simpan (`aria-pressed`, same `POST /student/wishlist/{id}`) and Bagikan (Web Share, falling back to copying the link; toasts via the shared `showNotification`). Below it: text flags Mendesak/Unggulan, a neutral difficulty meter and SDG chips.
- **Rail** (`components/brief-rail.blade.php`): the states in section 4; facts for Instansi, Lokasi proyek, Tenggat lamaran, Mulai, Durasi, Kuota (and number accepted) and Dilihat.
- **Reading column:** Deskripsi, Latar belakang, Tujuan, Ruang lingkup, Foto, Persyaratan (skill chips, majors), Hasil dan luaran, Fasilitas dari instansi, Lokasi dan jadwal, Tentang instansi, Masalah serupa. Each section appears only when its field has content. `scope`, `deliverables`, `facilities_provided`, `village`, `detailed_location` and the start/end dates are real columns the old view never showed.
- **Gallery** (`components/brief-gallery.blade.php`): see section 5.
- **Similar problems:** three `x-ui.list-item` rows; the query is unchanged.

## 4. Application State Mapping

| Priority | Condition (source) | Rail | Action |
|---|---|---|---|
| 1 | The student's own row in `applications` (`status`) | `x-ui.status` domain `application`: pending **Menunggu** (neutral), reviewed **Ditinjau** (info), accepted **Diterima** (success), rejected **Ditolak** (danger) + one sentence + 'Dikirim {applied_at}' | 'Lihat lamaran' → `student.applications.show` (existing route). No apply link. No 'Batalkan' (Phase 10) |
| 2 | `problems.status ≠ open` (same check as `ApplicationController@create`) | Problem status badge (Draf / Berjalan / Selesai / Ditutup) + 'Masalah ini tidak menerima lamaran.' | none |
| 3 | `application_deadline < now()` (same predicate as `create`) | 'Pendaftaran ditutup' + 'Tenggat lamaran {date} sudah lewat.' | none |
| 4 | otherwise | 'Lamaran dibuka sampai {date} (N hari lagi).' | **Lamar masalah ini** → `/student/applications/create/{problemId}` |
| info | accepted applications ≥ `required_students` | 'Kuota sudah terisi: N dari M mahasiswa diterima.' (not shown once the student has applied) | apply link **kept**: `create`/`store` do not check quota, so hiding it would change behaviour (decision 10.1) |

**State that was not invented:** there is no 'Kuota penuh' state that blocks applying, because the business logic has none. The quota fact counts real `accepted` rows, because the `accepted_students` counter is 0 everywhere while 259 applications are accepted.

## 5. Media Handling

- **Source:** only `problem_images` rows (`image_path` → `supabase_url()`, `caption`, `order`). Today these are the D-14 seed objects in `sg-storage`, which do not depict the problems. No stock, catalogue or KKN photograph was added (D-19, D-30 open).
- **Display:** the section is `x-show="visible" x-cloak` and becomes visible only after one image fires `load`, so nothing is reserved while loading and nothing appears if every image fails. A failing image is removed together with its caption, and the next one that loaded is shown. There is no static `<img src>` that could appear broken.
- **Captions:** `figcaption` text comes from the `caption` column (for example 'Aktivitas masyarakat'); `alt=""` because the caption is already in the figure. Thumbnails are `aria-pressed` buttons labelled 'Tampilkan foto N: caption'. No 'Gallery N' anywhere.
- **Verified:**
  - #29 (4 images, the most in this database) shows 4 thumbnails.
  - With every storage request blocked: section height 0, 0 visible images, no 'Gallery N' (`after/GALLERY_blocked_1440.png`).
  - With the first image blocked: 3 thumbnails, and the main image is the next one that loaded.

## 6. UI/UX Decisions

- **Hybrid direction:** workbench header and rail (sans, dividers, one bordered panel, no shadow, no gradient). Catatan Lapangan is used only in the reading text (Source Serif 4) and in the package figure caption rule (earth line). No hero, no decorative image.
- **Body size:** 16/24 px as the work order requires; the package `.sg-prose` is 18/28, so it is overridden for this page only. Measured: 16 px, 24 px, widest paragraph 576 px = **68.0ch**.
- **Mobile order:** the rail sits before the article in the DOM, so on phones the state and apply action come right after the header (apply at 607 px vs the description at 1,044 px at 390 px wide). Facts use two columns below 1024 px. No fixed bottom bar, so nothing covers content. Keyboard order is header → Simpan → Bagikan → apply.
- **Institution address:** shown as `address` + regency + province **without repeating** a name already in `address`. `getFullAddress()` printed 'Kab. Cilacap, Jawa Tengah' twice; the model is unchanged.
- **Labels:** 'Lokasi proyek' and 'Detail lokasi proyek' (problem columns) are separate from 'Alamat instansi' (institution columns). Long values wrap: `overflow-wrap: anywhere` for email/URL, `break-word` for text.
- **Sticky offset:** the rail sticks at 5rem (80 px measured) under the sticky header.
- **Generic palettes:** the `ui-ux-pro-max` palettes were not applied; the project design system wins.

## 7. Files Created and Modified

| File | Change |
|---|---|
| `app/Http/Controllers/Student/BrowseProblemsController.php` (`show` only) | + `$application` (own application: id, status, applied_at) and `$acceptedCount` (count of `accepted` applications), passed to the view. Existing queries, `$hasApplied`, `$isWishlisted`, view counter and similar-problems query unchanged |
| `resources/views/student/browse-problems/detail.blade.php` | Rewritten (brief; inline Alpine `sgBriefActions`, `sgBriefGallery`) |
| `resources/views/student/browse-problems/components/brief-rail.blade.php` | New |
| `resources/views/student/browse-problems/components/brief-gallery.blade.php` | New |
| `resources/css/student-go/student-brief.css` | New (`.sg-brief*`) |
| `resources/css/app.css` | + one `@import './student-go/student-brief.css';` after `student-browse.css` (shared) |
| `tests/Feature/Phase9/StudentProblemDetailTest.php` | New, 13 methods / 16 cases |
| `DISCOVERY_REPORT.md` §12.5, `COMPONENT_INVENTORY.md` §11, `ASSET_INVENTORY.md` §27, `DECISIONS_AND_OPEN_QUESTIONS.md` 'Note on Phase 9' | Appended (shared) |
| `handoff/scripts/interact9.py`, `contrast9.py` | Evidence scripts (outside the app; Python Playwright, D-22) |

## 8. Testing Results

### 8.1 Automated

| Command | Result |
|---|---|
| `php artisan test tests/Feature/Phase9` | **PASS** 16/16, 116 assertions |
| `php artisan test` (full) | 210 passed, **1 failed**: `Tests\Feature\Phase23\CompanyJobManagementTest::test_server_validation_returns_to_the_wizard_with_old_input_and_errors` (Session C, Phase 23 in progress; not a Phase 9 file, not touched). Phases 1–9 all pass |
| `vendor/bin/pint --test tests/Feature/Phase9` | **PASS** |
| `npm run build` | **PASS** (CSS 248.60 kB / gzip 38.35 kB, including Sessions B/C styles) |
| Route snapshot vs `reports/phase8/routes-after.json` | **PASS** 203 = 203, identical |
| `public/` baseline hashes | 37/38 identical, 0 changed (`student-go-logo.png` was deleted by the user before Phase 4) |

### 8.2 Browser (`after/interaction-checks.json`, 30/30 PASS; local seeded DB; GET only, Simpan not clicked)

| # | Prompt check | Result |
|---|---|---|
| 1 | Rail = application record | **PASS**: #1/86 none → apply link; #45/55 rejected → Ditolak; #52/27 pending → Menunggu; #56/40 reviewed → Ditinjau; #1/73 accepted → Diterima (each compared with a DB query in the same run) |
| 2 | Rejected not shown as success | **PASS**: tone `danger`, background rgb(253,236,234), no 'Terkirim'/'Diterima' |
| 3 | Pending not shown as 'not applied' | **PASS**: Menunggu, no create link |
| 4 | Eligibility and quota logic unchanged | **PASS**: controller diff adds reads only; PHPUnit: expired → `create` still redirects to the detail page with `error` |
| 5 | Apply link | **PASS**: `/student/applications/create/1` |
| 6–7 | Gallery fallback, no caption or empty space | **PASS** (section 5) |
| 8 | Location and address labelled | **PASS** |
| 9 | Similar links | **PASS**: 3 rows, each 200 |
| 10 | Breadcrumb | **PASS**: Dashboard and Telusuri Masalah 200; current = h1 |
| 11 | ≤ 68ch | **PASS**: 68.0ch, 16/24 px, Source Serif 4 |
| 12–13 | 6 widths, no overflow | **PASS**: S03 and S04 at 360/390/768/1024/1366/1440; other states at 390/1440; 76-character institution (#1) and longest title (#15) at 360/1440. 0 overflow, 0 clipped, 0 targets < 24 px, 0 English words, 0 console errors, 0 failed requests |
| 14 | Keyboard, focus, contrast, reduced motion | **PASS**: focus ring on every tab stop; apply reached in 4 tabs from the breadcrumb; sticky rail stays at top 80 px; reduced motion → transitions 1e-05s; contrast in 8.3 |

### 8.3 Contrast (`contrast.json`, rendered colours, 5 pages)

Every text pair passes, the lowest being 5.21:1 (`dt` 12 px gray-500 on the gray-50 page). Status badges: Ditolak 6.61, Diterima 6.56, Ditinjau 6.95, Menunggu 9.37. Primary button 7.84. Reading text 14.5. The selected thumbnail border (brand on white, 7.84) meets the 3:1 UI minimum. No gradient on the rail or its controls.

### 8.4 Acceptance criteria (section L)

- [x] **Rail state equals the real application state for #45 (rejected) and #56; a rejected application never shows a success colour.** #45/student 55 shows Ditolak in the danger tone. In today's seed #56 has **no pending** application: its real states are reviewed (student 40) and rejected (student 48), and the rail shows Ditinjau for student 40, equal to the record. Pending was verified on #52 (student 27, Menunggu). The audit's '#56 pending' record does not exist in this database (BLOCKED as data, not replaced with invented data).
- [x] **No broken image, alt-text box or 'Gallery N' caption is ever visible; the gallery collapses.** Blocked-image checks plus PHPUnit (no static `<img src>`, no 'Gallery N').
- [x] **Body text 16/24 px, ≤ 68ch.** Measured.
- [x] **Location and institution address are labelled and wrap.** Labels 'Lokasi proyek', 'Detail lokasi proyek', 'Alamat instansi'; a 51-character email wraps at 390 px (it caused the only overflow in the draft, fixed).
- [x] **All controls pass contrast; no gradient on the apply control.** Section 8.3.
- [x] **No English headings.** Browser and PHPUnit checks.

### 8.5 Not tested and why

- **'closed' and 'expired' rail states in the browser:** no open problem has a past deadline, and the only non-open problem (#78, draft) is soft-deleted, so it answers 404. Both states are covered by PHPUnit; no data was created to show them.
- **A problem with 5 images:** none exists (maximum 4, #29). BLOCKED as data.
- **'Simpan' click:** it writes a wishlist row. Not clicked; only the route and the `aria-pressed` wiring were checked.

## 9. Before/after S03 and S04

| | Before | After |
|---|---|---|
| S03 (#1, student 86) | `before/S03_S03-s86_{360…1440}.png`: hero card, gradient CTA 'Apply Sekarang', English 'Requirements', thumbnails 'Gallery N', unlabelled locations; 1,952 px at 1440 | `after/S03_S03-s86_{360…1440}.png`: brief header, rail 'Lamar masalah ini' + 'Kuota sudah terisi: 3 dari 3', labelled location/address; 3,157 px at 1440 (more real fields shown: scope, deliverables, facilities, dates, institution) |
| S04 (#45, student 55, rejected) | `before/S04_S04-s55_*.png`: green 'Aplikasi Terkirim' | `after/S04_S04-s55_*.png`: **Ditolak** (danger), 'Lamaran Anda untuk masalah ini tidak diterima.', 'Lihat lamaran' |
| Other states | `before/P52_pend-s27_*`, `P56_rev-s40_*`, `P01_acc-s73_*`: all green 'Aplikasi Terkirim' | `after/` same names: Menunggu / Ditinjau / Diterima |

h1 offset from the top of `<main>`: 101–121 px before, 56–80 px after. Clipped elements 6 → 0; targets < 24 px 2 → 0; English words 1 → 0.

## 10. Known Limitations and Outstanding Decisions

**Decisions needed**

1. **Full quota (OPEN, written to DECISIONS 'Note on Phase 9').** `create`/`store` do not check quota, and `accepted_students` is 0 on every problem although 259 applications are accepted. The rail therefore shows quota as information only. Options: (a) keep as is; (b) block applying when the quota is full (a behaviour change in `ApplicationController`, Phase 10 or a fix phase); (c) resync `accepted_students` from `applications` (a data change). Among the problems inspected, three are already 'full' by real counts: #1 (3/3), #45 (5/5) and #52 (2/2); #56 is 3/4. A full count across all 77 was not run.
2. **D-19 / D-30 for this page:** no editorial photograph here unless the user decides otherwise. The brief shows only problem images.

**Known limitations**

- **Seed data:**
  - Gallery images are D-14 seed photos unrelated to the problem text.
  - `village`/`detailed_location` hold Faker values with US street addresses, shown as they are under 'Detail lokasi proyek' (data issue, like D-15).
  - Titles and institution descriptions are stored in Title Case.
- **Similar problems:** the query has no `orderBy`, so the three rows may change between loads (query kept as is).
- **View counter:** `views_count` increments on every GET, including test captures (existing behaviour; counts in the local seed DB rose during testing).
- **Sticky rail:** if a future rail grew taller than the viewport it would need a scroll strategy; today it is about 560 px.

## 11. Shared File Impact

- `resources/css/app.css`: one appended `@import` line.
- Docs appended: `DISCOVERY_REPORT.md` §12.5 (inserted before §13), `COMPONENT_INVENTORY.md` §11, `ASSET_INVENTORY.md` §27 (after Session C's §26), `DECISIONS_AND_OPEN_QUESTIONS.md` note.
- `BrowseProblemsController` is Session A's (student) controller; only `show` changed.
- No shared component, token, layout, navigation, footer, route, model or migration was changed. No Session B/C file was touched.
- Branch state: the working tree is on the user's `docs/project-documentation` branch with an unrelated `CLAUDE.md` modification; both left as found.

## 12. Handover Notes for Phase 10

- The rail links 'Lihat lamaran' to `student.applications.show` for any status; Phase 10 owns that page and the 'Batalkan' action (not shown here).
- `ApplicationController@create` redirects back to this page with `error` (deadline) or `info` (already applied); the shared toast shows those flash messages.
- Status words and tones come from `StatusVocabulary` domain `application`; reuse the same sentences for the application list/detail if they fit.
- Decision 10.1 (quota) affects Phase 10 if quota blocking is approved. The `accepted_students` counter is unreliable; count `applications` instead.
- The form at `/student/applications/create/{id}` is untouched (Phase 10).

**Recommended tracker note (Phase 9):** COMPLETE 2026-10-31 (Session A). Problem detail rebuilt as a brief: rail from the real application status (J31 fixed: rejected = Ditolak), 16/24 serif reading column ≤ 68ch, photos only after load, labelled project location vs institution address, 3 similar rows; +2 read queries in `BrowseProblemsController@show`; tests 16/16; routes 203 = 203; quota decision open; no commit.
