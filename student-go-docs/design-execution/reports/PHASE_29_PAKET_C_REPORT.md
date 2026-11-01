# Phase 29, paket C — Detail lowongan mahasiswa (`student.jobs.show`)

Date 2026-11-01 · branch `dev` @ `000523d` (user's choice, D-27) · no commit · executed in the same session as paket A at the user's explicit request ("lakukan bersamaan"), which deviates from the one-package-per-request rule of the work order.

Soft dependency Phase 15 reads NOT_STARTED in the tracker; the user confirmed to continue.

## Changes

- `resources/views/student/jobs/show.blade.php` rewritten: `x-ui.page-header` (back link, title, company, one primary action "Lamar posisi ini" → `#lamar`, or the status badge once applied); a wrapping fact list (type, location, department, "Dipublikasikan … yang lalu"); sections divided by rules (Deskripsi pekerjaan, Tanggung jawab, Kualifikasi, Manfaat, Keahlian yang dibutuhkan, Tujuan SDG); rail with the apply form, "Informasi lowongan" (`x-ui.dl`), "Tentang perusahaan" (logo or initials, `globe` link) and "Lowongan serupa" as a list. The `<style>` block, inline hex colours, page-level toasts (the layout already shows flash messages) and the empty `jobDetailPage()` Alpine object are gone.
- `tests/Feature/Phase29/StudentJobPackageCTest.php` (6 tests, 64 assertions).

Controller, route, model and CSS files are unchanged (only existing utilities and `sg-*` classes are used).

## Copy changes (D-02 copy deck; terms already used in designed views)

| Before | After | Source of the term |
|---|---|---|
| Cover Letter | Surat lamaran (opsional) | `company/applications/show` "Surat lamaran" |
| Skills Yang Dibutuhkan | Keahlian yang dibutuhkan | job wizard (Phase 23) |
| SDG Alignment | Tujuan SDG | job list row `aria-label` (Phase 15) |
| Diposting 1 day ago | Dipublikasikan 1 hari yang lalu | `diffForHumans()` with locale `id` |
| 470 views | Dilihat: 470 kali | company job table column "Dilihat" (Phase 23) |
| Internship / ucfirst(job_type) | Magang, Penuh waktu, Paruh waktu, Kontrak, Lepas | the same map as `student/jobs/components/job-row.blade.php` (no `StatusVocabulary` domain exists for job type, which is a category, not a status) |
| Status: ucfirst(status) ("New") | `x-ui.status domain="job_application"` (Menunggu, Wawancara, …) | `StatusVocabulary` |
| Benefit / Link Resume / Link Portfolio / Kunjungi Website | Manfaat / Tautan CV / Tautan portofolio / Kunjungi situs web | — |

## Acceptance criteria (L.C)

| Criterion | Evidence | Result |
|---|---|---|
| Route count before = after | `route:list --json` byte-identical, 203 routes | ✔ |
| Apply/withdraw forms unchanged | Rendered-form diff: same `action`, `method`, no `enctype`, same `_method=DELETE`, same field names (`cover_letter`, `resume_url`, `portfolio_url`). Change: the three fields now have `id`s equal to their names so the labels are associated (they had no `id` before; nothing reads one). The withdraw form uses the shared confirm dialog (`data-sg-confirm`) instead of `onsubmit="return confirm(…)"`. The page has no save or compare form (those live in the list) | ✔ |
| Suite passes; new tests | 382/382 (3,909 assertions); package C 6/6. Baseline before this session 349/349 (the difference includes tests from parallel package sessions) | ✔ |
| `npm run build` | OK (CSS 250.55 kB, gzip 40.37) | ✔ |
| 0 `<style>`, 0 hex | `grep` over the view: 0 / 0 / 0 `style=` | ✔ |
| No horizontal overflow 360/390/768/1440 | `scrollWidth` before 399/399/768/1440 → after 360/390/768/1440 on 363 and 573 (`phase29-C/*/metrics.json`) | ✔ (C3 fixed) |
| Before/after screenshots | `reports/phase29-C/before/`, `after/` (job-363, job-573 × 4 widths) | ✔ |
| No English label | Probe on `<main>` text: before `Cover Letter, Skills Yang Dibutuhkan, SDG Alignment, views, Internship, ago`; after none | ✔ |
| Status and job type from vocabulary / one mapping | see table above | ✔ |
| `public/` hashes unchanged | before/after SHA-256 list of 228 files (`public/build/` excluded) identical; `baseline/asset-hashes.txt` does not exist on this branch | ✔ |
| Deprecated assets recorded | none stopped being referenced; `ASSET_INVENTORY.md` section "Phase 29, paket A dan C" | ✔ |

## Notes and findings outside the package

- Every GET of the page increments `views_count` (existing behaviour); the live checks added a few views to jobs 363 and 573.
- The tab title prints `&amp;` for titles containing `&`: `partials/head.blade.php:12` escapes a section value that `@section('title', …)` already escaped. Shell issue (Phase 3), not changed here.
- `student/applications/index.blade.php:180` and `student/jobs/compare.blade.php` still print `ucfirst(job_type)` ("Internship"); not in this package's routes.
- Live apply was not submitted on the test account; submit is covered only in the SQLite feature test.
