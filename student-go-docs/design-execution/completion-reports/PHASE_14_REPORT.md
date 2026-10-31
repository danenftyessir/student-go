# Phase 14 — Repository: completion report

| Field | Value |
|---|---|
| Phase | 14 — Repository (`phases/PHASE_14_REPOSITORY.md`), run in parallel with the Phase 12 session |
| Date | 2026-10-31 |
| Branch / commit | `docs/progress-phase-11-26` @ `7609d31`; no commit, no branch (D-27) |
| Recommended status | **COMPLETE**: all 6 acceptance criteria in section L met with evidence (section 4). One section K check, end-to-end download, could not be shown working in this environment (section 5). It fails the same way with the old code. |
| Prerequisites | Phase 1 (hard) COMPLETE, Phase 3 (hard) COMPLETE, Phase 2 (soft) COMPLETE, according to `PHASE_TRACKER.md` |
| Gating decisions | D-02 ANSWERED (Indonesian only + copy deck). D-05 ANSWERED (id tie-breaker, 'N dari M'). D-26 OPEN, so the default applies: no PDF preview and no new server sort |
| Tracker | **Not edited** (parallel-session rule). Suggested row note in section 8 |
| Evidence | `reports/phase14/after/` (12 screenshots, `browser-checks.json`), `reports/phase14/related-dedupe.json`, `reports/phase14/routes-after.json`. Before: `baseline/screenshots/S10_*.png`, `S11_*.png` |

## 1. Files changed

| File | Change |
|---|---|
| `resources/views/student/repository/index.blade.php` | Rewritten. Page header (no photo hero), one GET form through `x-ui.filter-bar` with a full-width search above SDG, Provinsi, Tahun diunggah and Urutkan, a count line, a six-column `x-ui.table` that stacks below 640 px, `x-ui.pagination` and empty states. The featured row, statistics cards, Space Grotesk link and inline `<style>`/`<script>` are gone. The KARA link is now the header action. |
| `resources/views/student/repository/show.blade.php` | Rewritten as a catalogue page. Breadcrumb, a single h1, Unduh + Buka di tab baru, the abstract in a serif reading column (`.sg-prose`, 68ch), SDG chips, record metadata as `x-ui.dl`, and a related list. If there is no abstract, the page says "Dokumen ini belum memiliki abstrak." The download script uses the same endpoint and the global toast instead of overriding `window.showNotification`. |
| `app/Support/RepositoryFormat.php` (new) | Turns stored values into readable labels: MIME/extension to PDF/DOCX…, size as '493 KB' / '1,4 MB', SDG number to name via `sdg_label()` (the same list Phase 22 uses), related de-duplication and Indonesian number formatting. |
| `app/Http/Controllers/Student/KnowledgeRepositoryController.php` | Only `show()` changed: the related query now fetches up to 24 candidates (and eager-loads `regency`) and then keeps one per title, skipping the open document's own title, up to 4. The list query, sort, count, view counter and download are unchanged. |
| `resources/css/student-go/student-repository.css` (new) | Page styles. Uses existing tokens, plus the 17 UN SDG colours, which appear only as 8 px swatches. |
| `resources/css/app.css` | One import line, following the pattern of Phases 5–26. |
| `tests/Feature/Phase14/RepositoryTest.php` (new) | 8 tests, 62 assertions. |

Not touched: routes, the download endpoint, file storage, the `Document` model, shared components, `components.css`, layouts, `.env`, `public/` and every Phase 12 file. `components/filter-sidebar.blade.php` was already unused and is left as it was.

## 2. Data checked (read-only, 2026-10-31)

- 214 public and approved documents. The audit counted 216; Phase 1 also found 214. 42 are flagged featured.
- `file_type` is `application/pdf` for 195 documents and `pdf` for 19. Sizes range from 493 KB to 4.5 MB.
- The longest title is 65 characters (#205, 'Bank Sampah Digital: Inovasi Pengelolaan Sampah Berbasis Aplikasi'). The longest institution name is 76 characters.
- Every document has a description, which is used as the abstract. `content` is empty everywhere.
- 59 titles occur more than once. This is the source of J36.
- 36 documents have a `year` that differs from the year in `created_at`. The existing Tahun filter uses `created_at`, so it is labelled **"Tahun diunggah"**, and the table column shows the document's own `year`. The backend was not changed.

## 3. Verification

| Check | Result |
|---|---|
| `php artisan test` | Before: 291/291 (with the new tests). After: **302/302, 3,202 assertions**. The new total includes the parallel Phase 12 tests. |
| `php artisan test tests/Feature/Phase14` | 8/8 |
| `npm run build` | OK (CSS 286.7 kB, including the parallel session's styles) |
| Routes | 203. Baseline 202 + the approved Phase 1 route. The 4 repository routes are identical (method, URI, name, action). |
| Browser (Playwright, seeded student, PostgreSQL) | **31/32** checks pass. The only failure is the download, explained in section 5. |
| Paging | 18 pages, 214 rows, 214 unique. The header reads '214 dokumen'. |
| Viewports 360/390/768/1024/1366/1440 | `scrollWidth <= innerWidth` on both pages at every width (12/12) |
| Longest title at 360 px | Wraps on 2 lines across the full card width (328 px) |
| Search / SDG / sort | 'sampah' shows 'N dari 214 dokumen'. `category=4` shows only SDG 4 rows. `sort=title` comes back in A–Z order. |
| `/student/repository/abc` | 404 |
| Console / network | No asset 404. The only console entries are the download failure (section 5) and the intentional `/abc` 404. |
| Related de-duplication on all 214 documents | Old query: 211/214 had a duplicate or self-titled entry. New: **0**, and no document ends up with an empty list (`related-dedupe.json`). |
| Visits during testing | Detail visits raised `view_count` and download attempts raised `download_count` on the seeded test database. No form was submitted. |

## 4. Acceptance criteria (section L)

| Criterion | Evidence | Met |
|---|---|---|
| Header total = reachable documents | '214 dokumen' = 214 unique rows over 18 pages (`all_reachable`). On the audit dataset this would read 216; the current data has 214. | Yes |
| No raw MIME type or bare SDG number | `no_raw_mime`, `detail_no_raw_mime`, `sdg_named`. Every chip reads 'SDG n · Nama' with a swatch. Feature tests check the same. | Yes |
| Table with six columns that stacks below 640 px | `six_columns` gives Judul · Instansi · Wilayah · Tahun · Dilihat · Diunduh. `sg-table--stack` is applied, and the screenshots at 360 and 390 px show the stacked layout. | Yes |
| No duplicate featured row | The featured section is removed (`no_featured_row`). | Yes |
| Related documents contain no duplicates | `detail_related_unique`, plus 0/214 in the full scan | Yes |
| All labels Indonesian | `indonesian_labels_index`/`_detail`. 'Showing', 'Download', 'Detail', 'Knowledge Repository', 'views' and 'Unknown' are gone. | Yes |

## 5. Download (section K) is blocked by the environment

The endpoint is unchanged. The button calls `route('student.repository.download', id)`, and the server still raises `download_count`. It cannot return the PDF here for two reasons:

1. PHP's `openssl.cafile` points to a file that does not exist on this machine (a path on another user's computer). The controller's `file_get_contents` therefore fails, and the controller falls back to a 302 redirect to the Supabase public URL.
2. That object is not in the bucket. Supabase answers 400 `NoSuchKey` for `documents/reports/…pdf`.

The old page used the same `fetch` call against the same endpoint, so it fails the same way. **To unblock:** fix or clear `openssl.cafile` in php.ini, and upload the document PDFs to the bucket (related to D-14/D-15). Neither is a change inside the project.

## 6. Deviations and notes

- The list rows no longer carry 'Lihat Online' or 'Download' buttons, because the table is limited to six columns. Download and 'Buka di tab baru' are on the detail page, which every title links to.
- The sort options now match what the controller actually supports: latest, oldest, popular, most_viewed, title. The old view offered `most_cited`, which the controller silently treated as latest, so it was removed. No new sort parameter was added.
- `regency_id` and `file_type` still filter when present in the URL and count as active, but they have no control on the page. The old index had no control for them either.
- Statistics cards (total downloads, institutions) were removed under guardrail 2. These were marketing counters; the only count left is the real total.
- No PDF preview and no layout slot for one (D-26 is OPEN).

## 7. Assets (AD-09)

- `repo-student.jpeg`: **Deprecate as hero** on `/student/repository`. It is no longer referenced by this page, and the file itself is untouched. Phase 5 reuses a derivative on About.
- `empty-states/no-documents.svg` and `no-search-results.svg`: **Reuse** through `x-ui.empty-state`.
- Lucide icons used from the sprite: `file-text`, `download`, `external-link`, `message-square-text`.
- Uploaded documents: **Retain**, not touched.
- I did not edit `ASSET_INVENTORY.md` (shared document). Suggested entry: "§ Phase 14: `repo-student.jpeg` deprecated as repository hero (still Retain); no-documents/no-search-results reused."

## 8. Suggested tracker row (to be applied after both sessions finish)

`14 | Repository | COMPLETE | 2026-10-31 | docs/progress-phase-11-26 @ 7609d31 (no commit) | All 6 AC met. Library table (6 cols, stacks <640), prominent search + SDG/provinsi/tahun/urutan, 214/214 reachable = header, PDF · 1,4 MB chips, SDG name + swatch, featured row removed, catalogue detail (dl + serif abstract), related de-duplicated (211→0 of 214). Tests 302/302, browser 31/32 (download blocked by php.ini cafile + missing bucket objects, pre-existing). No PDF preview (D-26). Report: completion-reports/PHASE_14_REPORT.md.`

## 9. Next

Phase 14 has no dependants that it unblocks on its own. Phase 27 needs Phases 4–26 to be COMPLETE. Phases that are still NOT_STARTED and whose hard dependencies are met (for example 13, 15 and 16) are available when you ask for them.
