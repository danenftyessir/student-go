# PHASE 22 — Company Talent Discovery: completion report

| Field | Value |
|---|---|
| Phase | 22 — Company Talent Discovery (`phases/PHASE_22_COMPANY_TALENT_DISCOVERY.md`) |
| Session | Session C (company workspace), run in parallel with Session A (Phase 8) and Session B (Phase 18) in the same working directory |
| Date | 2026-10-31 |
| Branch / commit | Working copy as assigned, branch `main` per `git status`. Everything in the repository is untracked: one commit `0a06850 first commit`, so `git status` cannot separate sessions. No branch, commit, stash or worktree was made (D-27) |
| Recommended status | **COMPLETE**: all 6 acceptance criteria are met with evidence (section 10.1). Two caveats: D-08 and D-36 stay OPEN by the user's choice, and the pool rule and the export are kept as they are (section 12). `PHASE_TRACKER.md` was **not** edited; the proposed row is in section 15 |
| Prerequisites | Phase 1 COMPLETE (tracker). Phase 3 COMPLETE (tracker). Phase 21 COMPLETE: `completion-reports/PHASE_21_COMPLETION_REPORT.md`; its tracker row has not been integrated yet. D-12 database reachable. Company authorisation `auth` + `check.user.type:company` verified by tests and live login. Phase 13 (soft) NOT_STARTED, so no Friends people-list component exists to reuse; the shared `x-ui.list`/`x-ui.list-item` from Phase 3 is used instead |
| Gate answers (this session, by the user) | **D-08**: keep the existing pool (students with at least 1 project), state it honestly, no expansion. **Directory**: server-side filters + one numbered pager, list view only (Grid removed), browser-local 'Simpan pencarian' kept. **Saved / D-26**: remove the controls that persist nothing; show stored folders (`category`) and notes read-only; no new backend. **D-36**: leave both CSV exports exactly as they are. D-02, D-04, D-05, D-31 were already ANSWERED. These answers are not written into `DECISIONS_AND_OPEN_QUESTIONS.md`; the text is proposed in section 15 for the integration owner |
| Candidate-profile route | VERIFIED before coding: `profile.public` (`GET /profile/{username}`), `DISCOVERY_REPORT.md` S1-22. `TalentController@show` has no route (commented out in `routes/web.php:381`) and was not revived |
| Evidence | `reports/phase22/before/` (18 PNG + `metrics_before.json`), `reports/phase22/after/` (26 PNG + `metrics_after.json` + `interaction_checks.json`) |

## 1. Executive summary

**`/company/talents` (C02)** is now a talent directory. The old version had a stock-photo hero, a 'quick actions' tile row, card grids and two pagers. It also filtered only the 12 rows already loaded in the browser.

The new page has:

- a page header with two actions;
- a left filter rail (a drawer below 768 px) covering SDG, universitas, jurusan, provinsi kampus, keterampilan and email verification;
- a divider-separated people list showing each student's name, jurusan · universitas, real campus regency and province, KKN project count and SDG names in Indonesian;
- one numbered pager.

**Filtering and counts.** Every filter now runs on the server, so the heading counts real matches against the real pool, for example '21 dari 259 mahasiswa cocok'. Under it, the page states why the pool is limited: 'Pool talenta: mahasiswa dengan minimal 1 proyek KKN di Student Go'.

**Save state** is read from the database instead of `localStorage`.

**Compare.** 'Bandingkan' appears only once something is picked, and becomes a link only at 2 picks or more.

**`/company/talents/saved` (C03)** now has two columns:

- **Left:** folders, which are the stored `saved_talents.category` values.
- **Right:** the list, with each talent's stored note shown read-only.

Every control that pretended to save but persisted nothing is gone:

- create, edit and delete folder;
- drag-to-folder;
- status, rating and reminder;
- note editing and bulk remove;
- 'export selected', which exported everything anyway;
- 'Hubungi', which posted to a `messages` table that does not exist.

What remains are the real actions: Hapus (existing `toggle-save`, behind a confirm dialog), Bandingkan (existing compare route) and Ekspor CSV (existing `export-saved`).

The empty-state CTA is the shared primary button, measured at 7.84:1 contrast.

**`/company/talents/compare`** keeps its behaviour and has only been restyled. Names now come from `display_name` (J1), and the English placeholders ('No Major', 'N/A', 'No bio available') are replaced.

**Exports** are untouched. The CSV bytes are identical to the baseline for three queries.

**Privacy.** No email or WhatsApp value appears in any of the three pages. The pool was not widened. No route, model, migration, middleware or shared component changed.

## 2. Initial condition (before)

All findings below are VERIFIED by reading the source and rendering the pages on 2026-10-31. Captures are in `reports/phase22/before/`.

| Area | Finding |
|---|---|
| Why '12 of 699' | **Pagination, not a visibility rule.** `index()` returns `paginate(12)` from a pool that `whereHas('student.projects')` narrows to **259 of 700** students. Phase 1 already labelled it '12 dari 259 Talenta Ditampilkan · Pool talenta: mahasiswa dengan minimal 1 proyek' (D-05). |
| Search and filters | Search, SDG, skills and location filtered **only the rows already loaded in the browser** (Alpine `filteredTalents`). The count therefore could not say how many students match. The server read `sdg_alignment` but never applied it. The server's `location` filter queried `students.location`, a column that does not exist (it would have returned HTTP 500). `impact_score` was read but unused. The skills list was a hard-coded list of 50 words. |
| Pagination | A client-side numbered pager over the loaded rows **plus** 'Muat Lebih Banyak' (server pages via JSON), with a per-page select. |
| SDG | English names (`getSdgName()`: 'No Poverty', …) and English filter labels ('SDG 1: No Poverty'), while the project's Indonesian list `sdg_label()` (`app/helpers.php`) already existed. |
| Location | `university->city`; `universities` has no `city` column, so every row read the literal default **'Indonesia'** (13 occurrences on page 1). The real data is `universities.regency_id` / `province_id` (117 of 117 set). |
| Save state | Read from `localStorage` `company_saved_talents`, not from the database. It was wrong on any other browser and after any change elsewhere. |
| Saved page | Folders came from `saved_talents.category`. Create, rename and delete folder, drag-to-folder, status, rating, reminder, note editing and bulk remove changed only browser memory (`// placeholder` in the source), each with a success toast. 'Hubungi' posted to `/company/talents/{id}/contact`, which inserts into a non-existent `messages` table, and the page showed 'Pesan berhasil dikirim' without checking the response. 'Export terpilih' sent `?ids=`, which `exportSaved()` ignores. The page overflowed at 360 and 390 px (485 px scroll width). |
| Empty-state CTA (J33) | Since Phase 3's overrides the button was already visible (brand background), but it was not the shared component. |
| Compare | Header names used `users.name` (`$talent->name`), not the Phase 1 single source; `onerror` fallback to `ui-avatars.com`; English 'No Major', 'N/A', 'No bio available'. |
| Export | `export()` uses **all students** (`whereHas('student')`, 700 rows), not the 259 pool; CSV columns `Name, Email, Title, Location, Skills, Verified, Projects Count`; Location is always empty (no `city`). `exportSaved()` columns `Name, Email, Title, Location, Category, Notes, Saved At`. Both include student email (D-36). |
| Data (seeded dev DB, read-only queries) | 700 students; pool 259; 195 with a completed project; **0 students with skills**; 117 universities, 34 provinces, 514 regencies; 1,023 saved talents, all with a category (Baru, Dihubungi, Interview, Ditawari, Ditolak) and a note; 651 saved talents are outside the pool; 1 company (the Phase 21 empty account) has none. Longest student name 18 chars, university 62, major 25. |
| Console | One 404 on C02 (hero `company1.jpg` URL). |

## 3. Company talent directory implementation (C02)

| Spec task | Done |
|---|---|
| 22.1 Gate check | D-08, D-26, D-36 and the directory approach asked and answered (header table). |
| 22.2 Candidate-profile route | `profile.public` confirmed from S1-22 and source; used for every name link. |
| 22.3 Directory | `x-ui.page-header` ('Talenta'; actions 'Talenta tersimpan' secondary, 'Ekspor CSV' tertiary). Left rail = the shared `x-ui.filter-bar` (one GET form; below 768 px its drawer with sticky 'Terapkan'; from 768 px a column via page CSS). The heading states the count and the pool rule. Rows are `x-ui.list` / `x-ui.list-item` with initials avatar, name → `profile.public`, jurusan · universitas, campus location, 'n proyek KKN', SDG chips, skills chips (when present), 'Bandingkan' checkbox and 'Simpan'/'Tersimpan' toggle (`aria-pressed`). One pager: `x-ui.pagination` (numbered; Sebelumnya/Berikutnya + 'Halaman x dari y' below 640 px). 'Muat Lebih Banyak', the per-page select and the Grid/List toggle are removed. |
| 22.4 SDG names | `sdg_label()` (the one list, also used by `Problem` and the institution pages). Shown as neutral chips 'SDG 4 · Pendidikan Berkualitas', as in Phase 18; no colour fill (guardrail: SDG colour only as a swatch; no swatch added because it would need hex values outside `tokens.css`). |
| 22.5 Locations | The campus's real regency and province ('Kota Bandung, Jawa Barat'); nothing when absent; labelled honestly ('Provinsi kampus' filter, screen-reader prefix 'Lokasi kampus:'). Students have no location field of their own. |
| 22.8 Grid/List | Removed with the user's approval (list only). |

Empty states: 'Belum ada talenta' (empty pool) and 'Tidak ada mahasiswa yang cocok' with 'Atur ulang filter' (no match).

**Compare bar.** A sticky bottom bar appears only while talents are picked. It shows 'n dipilih' and the names, plus 'Batal'. 'Bandingkan' appears only at 2 picks or more, and at most 3 can be picked. Picks survive the pager (`sessionStorage`).

## 4. Saved talent implementation (C03)

| Item | Implementation |
|---|---|
| Layout | Two columns from 768 px: left `nav` 'Folder talenta tersimpan' (Semua + one button per stored category, alphabetical, 'Tanpa folder' last, live counts, `aria-pressed`); right: count heading, search box, people list. Below 768 px the folder buttons wrap above the list. |
| Rows | Compare checkbox, initials, name → `profile.public`, jurusan · universitas, campus location, folder, 'Disimpan j M Y', the stored note under 'Catatan' (read-only, `pre-line`), 'Hapus'. |
| Folder / search | Filter the already-loaded rows in the browser. Every saved row is loaded by the existing `saved()` query, so the counts are complete. |
| Hapus | `window.sgConfirm` → existing `POST /company/talents/{id}/toggle-save`; the row is hidden only when the server answers `unsaved` (errors are reported, not hidden). |
| Bandingkan | 2-3 picks → existing `company.talents.compare?ids=`; replaces the in-page comparison modal. |
| Ekspor CSV | Existing `company.talents.export-saved`, unchanged. |
| Empty state | `x-ui.empty-state` (`no-saved-projects.svg`), 'Belum ada talenta tersimpan', CTA `x-ui.button` primary 'Cari talenta' → `company.talents.index`. |
| Removed (persisted nothing) | Folder Baru / edit / delete, drag-and-drop, status, rating, reminder, note editing, bulk move/status/remove, 'export terpilih', 'Hubungi' tab. |

## 5. Search and filter behavior

| Parameter | Status | Server rule |
|---|---|---|
| `search` | Read by the UI since Phase 1 (`perPageChanged`) but ignored by the server → **now applied** | case-insensitive match on first + last name, major, university name; `%`, `_`, `!` escaped (`ESCAPE '!'`, works in PostgreSQL and SQLite) |
| `sdg_alignment[]` | Existing name → **now applied** | student has a *completed* project whose problem's `sdg_categories` contains any selected goal (same source as the row chips; int or string values) |
| `skills[]` | Existing, unchanged (AND, `whereJsonContains`) | Options now come from skills actually stored on pool students. There are none in the seed, so the group says so honestly. |
| `location` | Existing name; queried a missing column → **now** matches the campus regency or province name | shown as a field only when present in the URL (old saved searches) |
| `verified_only` | Existing (`email_verified_at`); now read with `boolean()` | labelled 'Hanya email terverifikasi' |
| `university_id`, `major`, `province_id` | **New** GET parameters (names follow the CLAUDE.md §7.4 list; `province_id` = campus province) | options only from the pool's own universities/majors/provinces |
| `page`, `per_page` | Existing, unchanged | `->withQueryString()` keeps every filter on pager links |
| `view` | No longer read (Grid removed) | — |

**Ordering.** `created_at desc, id desc`, unchanged (D-05). No new sort was added.

**Count definition.** The heading is `total()` of the filtered query out of `count()` of the unfiltered pool, written as 'N dari M mahasiswa cocok'. Without filters it reads 'M mahasiswa'.

**JSON.** The JSON response for `X-Requested-With`/`Accept: application/json` is kept with the same envelope (`talents`, `hasMore`, `currentPage`, `total`). The rows lost the always-zero keys (`impact_score`, `success_rate`, `online`, …) and gained `university`, `projects_count` and a real `location`.

**Saved searches.** The 'Simpan pencarian' feature still lives in `localStorage` (`company_saved_searches`), as before. It now stores the URL query of the current search, and old `{ name, filters }` entries are converted on load. The name is typed in an inline field instead of `prompt()`.

## 6. Candidate profile route

- Every directory, saved and compare link is `route('profile.public', $username)`, the route recorded in S1-22. No route or profile view was added or changed. The student public profile belongs to Phase 12.
- **Same record (J1).**
  - Names: `User::display_name`, the Phase 1 single source the profile page also uses.
  - Live check: the first row 'Luki Maharani' opens a profile whose `<h1>` is 'Luki Maharani'.
  - Sample: 10 rows from page 3 were matched against their profile pages, with 0 mismatches.
  - Tests: one test uses an account name ('akun lama ratna') that differs from the student name; the old account name never appears in the directory or on compare.

## 7. Privacy and authorization assessment

| Check | Result |
|---|---|
| Pool not widened (D-08 OPEN) | The pool query `whereHas('student')->whereHas('student.projects')` is unchanged and remains the base of every filter. Test: a student without a project never appears, even when searched by name. |
| Contact data (D-04) | No email or phone value is sent to the HTML of directory, saved or compare. Tests assert the student's email and phone are absent. |
| Authorisation | Unchanged middleware `auth` + `check.user.type:company`. Tests: guest → `/login`; student → silent redirect away. Saved rows are scoped to `company_id` of the logged-in company (unchanged query). |
| IDOR | Profiles are reached only through `profile.public`, which is public by design. Its contact gate (D-04) is enforced server-side in Phase 1. `compare?ids=` and `toggle-save/{id}` accept any student id: **existing behaviour, not widened**. They expose nothing beyond the public profile (name, university, major, project count, bio). See section 12 for the hardening this would need. |
| Query string / logs | Only filter values (search text, ids, names of majors/skills) go into the URL; no personal data. Nothing is logged by the new code. |
| Photos (D-31) | Rows show initials; no student photo is shown in a new or larger place. The old list effectively showed initials too. |
| Export (D-36 OPEN) | Untouched by the user's choice. It still contains email for all 700 students: a known privacy risk (section 12). |
| UI as the only safeguard? | No. Every restriction above is in the controller query or middleware; the UI only reflects it. |

## 8. Files created and modified

| File | Change |
|---|---|
| `app/Http/Controllers/Company/TalentController.php` | `index()`: server-side filters, real location, Indonesian SDG names, real project count, filter options from pool data, saved state from DB, `withQueryString()`. `saved()`: rows with location/folder/note/date, `id` tie-breaker, folder list. New private `likePattern()`; the unused English `getSdgName()` removed. **`show`, `toggleSave`, `contact`, `compare`, `export`, `exportSaved` byte-identical** (diffed against the original). Imports `Province`, `Student`, `University`. |
| `resources/views/company/talents/index.blade.php` | Rewritten (1,074 → 322 lines). No inline `<style>`, no `style=`, no hex. |
| `resources/views/company/talents/saved.blade.php` | Rewritten (1,122 → 227 lines). |
| `resources/views/company/talents/compare.blade.php` | Restyled (154 → 107 lines); same rows, same `saveTalent()` call (moved to `@push('scripts')`, Indonesian toast). |
| `resources/css/student-go/company.css` | Session C's file: **Phase 22 section appended** (`.sg-co-talents*`, `.sg-co-saved*`, `.sg-co-compare*`); existing tokens only; the Phase 21 section untouched. |
| `tests/Feature/Phase22/CompanyTalentDiscoveryTest.php` | **New**: 12 tests. |
| `tests/Feature/Phase1/PaginationIntegrityTest.php` | **Shared, 1 assertion**: `'totalTalents: 14'` (the removed Alpine variable) → `'14 mahasiswa'` (the rendered count). The test's intent (real pool size + rule on the page) is unchanged. |
| `student-go-docs/design-execution/ASSET_INVENTORY.md` | **Shared, append only**: section 26. |
| `student-go-docs/design-execution/reports/phase22/` | New evidence. |
| `student-go-docs/design-execution/completion-reports/PHASE_22_COMPLETION_REPORT.md` | This file. |
| `public/build/` | Regenerated by `npm run build` (git-ignored; includes the other sessions' current CSS). |

Not touched: `routes/web.php`, `routes/api.php`, middleware, models, migrations, seeders, `.env`, layouts, navbar, footer, `tokens.css`, `components.css`, `app.css`, `<x-ui.*>`, global JS, `company/talents/show.blade.php` (unrouted), any other company view, any file under `public/` other than `build/`, `PHASE_TRACKER.md`, `DECISIONS_AND_OPEN_QUESTIONS.md`.

## 9. Backend and data integration

- **Queries are read-only.** Nothing new is written.
- **The directory runs these queries:** one pool count, one filtered page, and the eager loads `student.university.regency/province` and `student.projects.problem`. Filter options take four small `distinct`/`pluck` queries over the pool, and save state takes one `saved_talents` lookup for the 12 ids on the page.
- **`saved()`** runs the same query as before, plus eager-loaded location.
- **No new endpoint and no new column.** Folders and notes can still only be set at save time through `toggleSave`'s optional `category`/`notes` input, which no UI sends; see section 12.
- **Test data.** The local dev database ends this phase unchanged:
  - Company #1 has 9 saved talents and the Phase 21 empty company has 0, as at the start.
  - The live checks saved and removed talents net-zero.
  - One run of my check script had a stale locator and saved two extra talents for company #1 (rows 1024 and 1025, no category or note). I deleted those two rows afterwards; they were the only rows I created.

## 10. Testing results

### 10.1 Acceptance criteria (section L)

| Criterion | Result | Evidence |
|---|---|---|
| One pager only | **PASS** | 'Muat Lebih Banyak' absent in 18/18 after captures (`loadMore: false`; before: true in 6/6 C02); one `nav[aria-label="Halaman talenta"]`; test `test_directory_has_one_pager_indonesian_sdg_and_real_campus_location` (no 'Muat Lebih Banyak', no `loadMore`; page 1 = 12 rows, page 2 = 2). |
| SDG labels are Indonesian everywhere | **PASS** | Rows and filter use `sdg_label()`; after captures contain none of 'No Poverty', 'Zero Hunger', 'Quality Education' (before: all three); `getSdgName()` removed; test asserts 'SDG 1 · Tanpa Kemiskinan' and 'SDG 4 · Pendidikan Berkualitas'. |
| The count states how many students match and why the pool is limited | **PASS** | Live: '259 mahasiswa' + 'Pool talenta: mahasiswa dengan minimal 1 proyek KKN di Student Go'; with SDG 4: '21 dari 259 mahasiswa cocok' = JSON `total` 21, every row shows SDG 4; tests `test_count_states_matches_and_the_rule_that_limits_the_pool`, `test_empty_pool_and_no_match_states` ('0 dari 1 mahasiswa cocok'). The rule is the existing one (D-08 kept OPEN by the user). |
| No row shows the literal 'Indonesia' as a placeholder location | **PASS** | Rows show regency + province from `universities` (e.g. 'Kab. Pacitan, Jawa Timur'); test regex `>\s*Indonesia\s*<` absent. Remaining 'Indonesia' text on the page is part of real university names ('Universitas Komputer Indonesia') and the footer. |
| The empty saved page CTA is visible with contrast >= 4.5:1 | **PASS** | Shared `x-ui.button` primary; measured `rgb(255,255,255)` on `rgb(64,80,128)` = **7.84:1**; test asserts the `sg-btn sg-btn--primary` link to `company.talents.index`. Captures `C03_after_empty_*` (an icon that the shared `.sg-empty svg` rule enlarged was removed from the button). |
| Export CSV bytes equal the baseline for the same query | **PASS** | Section 11: 3/3 identical (SHA-256 and size); test `test_csv_headers_are_unchanged`. |

### 10.2 Test matrix requested by the session brief

| # | Test | Result | Evidence |
|---|---|---|---|
| 1 | Directory rendering | PASS | 6 widths, HTTP 200, one `<h1>`, `lang="id"`. |
| 2 | Search and filter | PASS | Live SDG filter via the rail; tests for search (name/major/university, case-insensitive, `%` escaped), `university_id`, `major`, `province_id`, `location`, `skills[]` (AND), `sdg_alignment[]` (completed projects only). |
| 3 | Pagination | PASS | Pager links keep `search` + `sdg_alignment` (test + live `page=2`). |
| 4 | Stable ordering | PASS | 14 students with the same `created_at`: pages 1+2 = 14 unique (test); Phase 1 tie-breaker test still green. |
| 5 | Result count and visibility rule | PASS | 10.1 row 3; student without project never listed. |
| 6 | Save / unsave | PASS | Live, on an unsaved talent: `aria-pressed` false → true (saved page lists it) → false (gone); test: state from DB. |
| 7 | Folders and notes | PASS | Live folder filter (1 of 9 for 'Baru'), search no-match state; test: folders, 'Tanpa folder', notes shown, fake controls absent. |
| 8 | Compare two candidates | PASS | Live: bar hidden → 1 pick (no Bandingkan) → 2 picks → compare page lists both names; saved page builds the same `compare?ids=` link; test: redirect without selection kept. |
| 9 | Export CSV | PASS | Section 11. |
| 10 | CSV headers and bytes vs baseline | PASS | Section 11. |
| 11 | Empty-state CTA contrast | PASS | 7.84:1. |
| 12 | Candidate profile click-through | PASS | Opens `/profile/{username}`. |
| 13 | Name consistent with profile | PASS | 1 + 10 sampled, 0 mismatches; J1 test. |
| 14 | Privacy field visibility | PASS | Section 7; tests. |
| 15 | Company role authorisation | PASS | Tests (guest, student); live company login. |
| 16 | Empty talent list | PASS | Test (empty pool + no match); live 'zzzz' search. |
| 17 | Responsive 360-1440 | PASS | 18 captures (C02 populated, C03 populated, C03 empty × 6 widths). |
| 18 | No horizontal overflow | PASS | `scrollWidth <= innerWidth` in 18/18 (before: C03 485 px at 360 and 390, 374 at 360 empty); compare page 360 = 360. That one failed first because of `sr-only` labels escaping the scroll container; fixed with `position: relative` on the wrapper. |
| — | Keyboard | PASS | Drawer opens as `role="dialog"`, Esc closes and returns focus to 'Filter'; focus visible on the first 25 tab stops; native `<details>`, buttons with `aria-pressed`. |
| — | Accessible labels | PASS | Every checkbox has a label ('Bandingkan <nama>' / 'Pilih <nama> untuk dibandingkan'); save/remove buttons carry the name for screen readers; search fields labelled; lists labelled. |
| — | Reduced motion | PASS | Empty-account run with `prefers-reduced-motion: reduce`, no errors; the new CSS only has a 150 ms colour transition (global reduced-motion rule applies). |
| — | Console / network | PASS (Phase 22 pages) | 0 errors in 18 captures (before: one 404). In multi-page runs the shared notification dropdown can log 'gagal load notifikasi: Failed to fetch' when a navigation aborts its request: shell behaviour, not this phase. |
| — | Automated suite | PASS | `php artisan test`: **185 passed, 2,058 assertions** (includes the parallel sessions' tests); Phase 22 alone 12 tests. Not run before the change in this session. After the change, before fixing it, 1 Phase 1 assertion failed as expected (section 8). |
| — | Routes | PASS | `route:list` 203 routes = Phase 0/3 count; the 7 talent routes are unchanged. |
| — | Assets | PASS | 37/38 Phase 0 hashes identical, 0 changed; missing `student-go-logo.png` was deleted by the user before Phase 4. |
| — | Browser interaction script | PASS | `reports/phase22/after/interaction_checks.json`: 29/29. |

## 11. CSV export comparison

Same company account (#1), same day, fetched with the logged-in session before and after the change:

| Query | Before | After | Header |
|---|---|---|---|
| `/company/talents/export` | 50,522 B, `a32b701e…5875` | identical | `Name,Email,Title,Location,Skills,Verified,"Projects Count"` |
| `/company/talents/export?verified_only=1` | 50,522 B, `a32b701e…5875` | identical | same |
| `/company/talents/export-saved` | 1,359 B, `2a2bd1f9…1487` | identical | `Name,Email,Title,Location,Category,Notes,"Saved At"` |

Full hashes are in `metrics_before.json` / `metrics_after.json`. The CSV files themselves were deleted after hashing because they contain the email addresses of 700 (seeded) students. No export file is kept in the documentation folder.

## 12. Known limitations

1. **D-36 export privacy.** The talents CSV still lists **all 700 students with email**, more than the 259-student pool the page shows. Its Location column is empty because it still reads the missing `city` column. Left as is by the user's choice so the bytes match the baseline; the button label does not claim otherwise.
2. **`compare?ids=` and `toggle-save/{id}`** accept any user id. `toggleSave` does not even check that the id is a student. This is existing behaviour, not widened, and nothing private is exposed. Restricting both to the pool or to students is a behaviour change and needs approval.
3. **`POST /company/talents/{id}/contact`** still exists and still writes to the missing `messages` table (HTTP 500). Its UI is removed; the route is kept (CLAUDE.md §7.1). Messaging is a D-26 feature.
4. **Folders and notes are read-only.** No UI can set them; `toggleSave` accepts `category`/`notes` only when saving. Editing them would be new backend (D-26).
5. **Skills.** 0 of 700 seeded students have skills, so the skills group shows 'Belum ada mahasiswa di pool yang mengisi keterampilan'. With real data its options appear automatically.
6. **'Terverifikasi' means email verified**, labelled so. In the seed every student is verified.
7. **Saved searches stay in the browser** (`localStorage`). They are not shared across devices, as before.
8. **Saving takes about 2-4 s** against the remote dev database. The button is disabled meanwhile, with no spinner; the toast confirms.
9. **`per_page` is unbounded** (existing). The UI no longer sends it, but a hidden field keeps it when it is present in the URL.
10. **SDG names are title case** as `sdg_label()` writes them ('Kehidupan Sehat Dan Sejahtera'). Changing the shared list belongs to the copy deck, not this phase.
11. **Location is the campus location**, the only location the data has, and it is labelled as such.
12. **`company/talents/show.blade.php`** is still on disk but unrouted. Left untouched.

## 13. Outstanding decisions

| ID | Status after this phase | What is needed |
|---|---|---|
| D-08 | OPEN (Phase 22 answer: keep the existing pool and state it) | Opt-in versus all students. Opt-in needs a consent field and a student setting (new feature). |
| D-36 | OPEN (Phase 22 answer: exports unchanged) | Remove email from both exports, or require consent. Either changes the CSV bytes, so this phase's AC 6 would need a new baseline. |
| D-26 | OPEN (Phase 22 answer: no new feature; fake controls removed) | Whether to build: editable folders and notes, messaging (`contact`), a match-reason column. None exists today. |
| New (proposed) | — | Whether `compare`/`toggle-save` should reject ids outside the pool or non-students (limitation 2). |

## 14. Shared file impact

| File | Owner / risk | Impact |
|---|---|---|
| `tests/Feature/Phase1/PaginationIntegrityTest.php` | Phase 1 (shared) | One assertion string; same intent. Re-run: green. |
| `ASSET_INVENTORY.md` | shared doc | Section 26 appended after Session B's section 25; no existing line changed. |
| `resources/css/student-go/company.css` | Session C | Appended. Selectors are scoped `.sg-co-talents*`, `.sg-co-saved*` and `.sg-co-compare*`, so the shared filter bar, list and table are restyled only inside these pages. |
| `public/build/` | generated | Rebuilt three times; each build includes whatever the other sessions' CSS was at that moment. |
| `app/Http/Controllers/Company/TalentController.php` | company-only controller | Not shared with other roles; Phase 21's dashboard does not call it. |

## 15. Proposed updates for integration (not applied)

**`PHASE_TRACKER.md` row:**

`| 22 | Company Talent Discovery | COMPLETE | 1, 3 | 13 | D-02, D-04, D-05, D-08, D-26, D-31 | 2026-10-31 | main (untracked working copy, no commit) | All 6 AC met. Directory: server-side filters (search, SDG, universitas, jurusan, provinsi kampus, keterampilan, verified; old params kept, broken 'location' fixed), one numbered pager (load-more, per-page, Grid removed), count 'N dari 259 mahasiswa cocok' + pool rule, campus regency/province instead of 'Indonesia', SDG via sdg_label(), save state from DB, compare bar only on selection. Saved: folders (category) + notes read-only, fake controls and broken 'Hubungi' removed, shared CTA 7.84:1. Compare restyled (display_name). Exports untouched: 3/3 CSV byte-identical. Pool unchanged (D-08), D-36 open. Tests 12 new; suite 185/185; routes 203=203; 18 captures 0 overflow; browser checks 29/29. Report: completion-reports/PHASE_22_COMPLETION_REPORT.md. |`

**`DECISIONS_AND_OPEN_QUESTIONS.md` notes:**

- D-08: 'NOTE (2026-10-31, Phase 22): still OPEN. The user chose to keep the existing pool (students with at least 1 project; 259 of 700 in the seed); the directory states it under the count. Nothing widened.'
- D-26: 'NOTE (2026-10-31, Phase 22): no new feature. Saved talents: client-only folder/status/rating/reminder/note controls and the broken "Hubungi" removed at the user's request; folders (`category`) and notes shown read-only.'
- D-36: 'NOTE (2026-10-31, Phase 22): still OPEN. Exports left unchanged at the user's request (bytes identical to the baseline). The talents export also covers all students, not the pool.'

**Optional:** add the Phase 22 page pattern ('people directory: filter-bar as rail + list + pager') to `COMPONENT_INVENTORY.md`. It is not added, to avoid concurrent edits.

## 16. Handover notes for Phase 23 (Company Job Management)

- Company CSS lives in `resources/css/student-go/company.css`. Phase 21 and 22 sections exist; append a Phase 23 section with its own `.sg-co-*` prefix.
- **Patterns to reuse:**
  - A rail filter is the shared `x-ui.filter-bar` plus the `@media (min-width: 768px)` column rules in `.sg-co-talents__rail`.
  - The selection bar is `.sg-co-talents__compare`.
  - Shared-list rows with an end slot use `x-ui.list-item`.
- Talent names: always `User::display_name`. Profile links: always `route('profile.public', $username)`.
- In `x-ui.empty-state`, do not put an icon inside the action button: the shared `.sg-empty svg { width: 10rem }` rule enlarges it.
- In a horizontally scrolling `x-ui.table` with `:stack="false"`, give the wrapper `position: relative`, or `sr-only` labels inside it widen the page.
- Status words for job applications come from `StatusVocabulary` (Phase 21 added the pipeline values).
- The remote dev DB makes POST round trips take 2-4 s. Wait on conditions in browser checks, not on fixed sleeps.
- Do not start Phase 23 from this report. It needs its own explicit request.
