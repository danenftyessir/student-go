# PHASE 24 — Applicant Pipeline: completion report

| Field | Value |
|---|---|
| Phase | 24 — Applicant Pipeline (`phases/PHASE_24_APPLICANT_PIPELINE.md`) |
| Date | 2026-10-31 |
| Branch / commit | `feat/student-workspace`, uncommitted (D-27). No branch, commit, reset, clean or stash. Other sessions' changes in the tree were not touched |
| Recommended status | **COMPLETE** (all 5 acceptance criteria met, section 3). `PHASE_TRACKER.md` not edited (parallel sessions); proposed row in section 6 |
| Dependencies | Phase 3 COMPLETE (tracker). Phase 23 recommended COMPLETE in `completion-reports/PHASE_23_COMPLETION_REPORT.md` (tracker row not yet consolidated). Phase 1 COMPLETE |
| Gates | D-02 ANSWERED (labels from `StatusVocabulary`). D-13 OPEN, not needed: the local DB holds 1,159 seeded job applications in every stage (company #1: 21 + legacy values; company #60: empty Wawancara/Penawaran; company #131: none). D-26 OPEN: no Kanban board built; tabs + table only |

## 1. What changed

| File | Change |
|---|---|
| `resources/views/company/applications/index.blade.php` | Rewritten. `sg-container` + page header (no photo hero, no full-bleed). Summary line, 8 stage tabs (Semua + 7 stored stages) with counts from the controller query, labelled filters (posisi, tanggal, nama; client-side as before), 6-column table + checkbox column (`x-ui.table`, stacks below 640 px, sticky header), shared status badge, empty states (account / stage / filter), bulk bar on selection, follow-up dialog (tahap, rating, catatan, wawancara), comparison dialog (2-3) |
| `resources/views/company/applications/show.blade.php` | Rewritten as a record page (same layout as `/company/jobs/{id}`): breadcrumb header, status meta, applicant facts, cover letter, documents, internal notes; rail with actions, rating, history from stored timestamps |
| `resources/css/student-go/company.css` | Phase 24 section appended (`.sg-co-apps*`, `.sg-co-app*`). No global CSS, shell or shared component changed |
| `tests/Feature/Phase24/ApplicantPipelineTest.php` | 9 tests, 75 assertions |
| `ASSET_INVENTORY.md` | Section 30 (company3.jpg / company1.jpg usage deprecated on these pages; files kept) |

**Stage mapping (no new enum).** Tabs use the 7 values the controller validates; legacy values are grouped by the existing `JobApplication::statusesFor` (Phase 21): Baru = `new`+`pending` (label **Menunggu**), Ditinjau = `reviewing`+`reviewed`, Terpilih = `shortlisted`, Wawancara = `interview`, Penawaran = `offer`, Diterima = `hired`+`accepted` (label **Direkrut**), Ditolak = `rejected`. Labels come from the shared vocabulary so tab, badge and select use one word; the badge shows the grouped stage (a stored `accepted` reads 'Direkrut', not 'Diterima').

**Kept unchanged.** Routes (203; 10 under `company/applications`), controller, model, query params (`job_posting_id`, `status`, `search`), ownership checks, all endpoints and payloads: `POST /company/applications/bulk-update {application_ids, status}` (recorded live), `/{id}/status`, `/{id}/rating`, `/{id}/notes`, `/{id}/shortlist`, `/{id}/reject`, `/{id}/hire`, `/export?ids=`.

**Deliberate UI differences.**
- After a successful action the page reloads, so tabs, counts and badges come from the database.
- Bulk reject now asks for confirmation (`sgConfirm`), as do shortlist and hire on the detail page.
- Rating in the table is read-only ('n/5' meter); it is edited in the dialog with keyboard-operable radios.
- Removed:
  - two 'kirim email' checkboxes: the server ignores `send_email`;
  - an unreachable timeline modal with invented entries;
  - the 'pelamar akan dinotifikasi' promise on hire: `hire()` sends no notification.

## 2. Verification

- `php artisan test`: 259/259 passed (2,779 assertions), Phase 24: 9/9.
- `npm run build`: OK.
- Capture (`reports/phase24/before|after`): C06 populated and empty, plus detail, at 360/390/768/1024/1366/1440. 0 page overflow, 0 console errors.
- Interaction (`reports/phase24/checks/interaction_checks.json`): 47/47 checks pass. They cover:
  - each tab's visible rows equal its count;
  - arrow/Home/End keys, and hash restore after reload;
  - 390 px: all 7 cells inside the viewport, with no table scroll;
  - selection, indeterminate select-all, the bulk bar, compare and Esc;
  - live bulk moves to Wawancara (same payload), confirm-cancel sends nothing, then confirmed reject;
  - dialog stage, rating and notes;
  - detail hire, notes and rating;
  - empty stage on company #60.
- Contrast of the new pairs on the `brand-50` selected row: at least 5.21:1 (`checks/contrast-new-pairs.txt`).
- Throwaway rows (job 632, applications 1160-1162) were hard-deleted. The DB is back to 625 jobs and 1,159 applications (`checks/throwaway-cleanup.json`).
- `public/` unchanged.

## 3. Acceptance criteria

- [x] Standard container (`sg-container`, page header).
- [x] 390 px: every column visible as stacked rows, no cropped table (before: 3 of 6 columns, names one letter per line).
- [x] Empty stage shows 'Belum ada pelamar' + 'Kelola lowongan'.
- [x] Tab labels = stored stages via the mapping above.
- [x] Bulk actions keep their endpoint, method and keys.

## 4. Found defects (not fixed; backend, outside this phase)

1. **Reject with a reason returns 500 but still rejects.** `job_applications` has no `rejection_reason` column. `reject()` and `updateStatus()` save the status first, then the reason write throws. The UI shows 'gagal' while the DB says `rejected` (verified live on a throwaway row). The old page behaved the same way. **Decision needed:** add the column (migration) or stop sending the reason.
2. The detail fields `resume_url`, `portfolio_url`, `expected_salary` and `available_from` do not exist in the table. The detail page therefore always shows 'Pelamar tidak melampirkan dokumen.' (`resume_path` exists but has never been displayed).
3. Scheduling an interview overwrites the internal notes; the help text now says so.
4. The Posisi filter lists only `active()` jobs, so closed jobs that have applicants cannot be selected there.

## 5. Next

Roadmap: Phase 25 (company group) once the tracker is consolidated; not started.

## 6. Proposed tracker row

`| 24 | Applicant Pipeline | COMPLETE | 3, 23 | 1 | D-02, D-13, D-26 | 2026-10-31 | feat/student-workspace (no commit) | All 5 AC met. Container + header, 8 stage tabs with query counts (legacy values grouped), 6-col table stacking <640px, bulk bar (same POST bulk-update payload, reject confirmed), empty states, record detail page. Tests 9 new, suite 259/259; captures 0 overflow; checks 47/47; throwaway rows removed. Found: reject reason 500 (missing column), detail columns missing. Report: completion-reports/PHASE_24_COMPLETION_REPORT.md. |`
