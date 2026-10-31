# PHASE 10 — Applications

> Source of truth for status is `student-go-docs/design-execution/PHASE_TRACKER.md`. This file is the work order for exactly one phase. Read `CLAUDE.md` and `student-go-docs/design-execution/EXECUTION_ROADMAP.md` first, execute only this phase, update the tracker, report, then stop.
> Evidence labels: VERIFIED / OBSERVED / INFERRED / UNKNOWN / BLOCKED (definitions in `DISCOVERY_REPORT.md` section 1).

| Field | Value |
|---|---|
| Phase | 10 — Applications |
| Group | Student role |
| Hard dependencies (must be COMPLETE) | Phase 1 (Critical Fixes & Data Integrity), Phase 3 (Shared Shell & Components) |
| Soft dependencies (recommended first) | Phase 9 (Project Detail) |
| Decisions that gate this phase | D-02 Language policy and copy deck, D-21 Charts on dashboards, D-12 A working database for baseline and QA |
| Effort estimate (planning guess, not a commitment) | M |
| Roles touched | Student |
| Audit references | S05, S06, S07, S08; J6, J8, J9, J14, J18, J26, J29, J32; audit D5 |
| Tracker status at planning time | NOT_STARTED |

## A. Phase overview

**Goal.** Make the student's applications a table with status as the primary column, detail pages that behave like records (content column + facts rail), a destructive 'Batalkan' that is secondary and confirmed, and an apply form that validates after interaction.

**In scope**

- `/student/applications` (tabs KKN / Lowongan, status filter), `/student/applications/{id}`, `/student/applications/create/{problemId}`.
- Cancel action with confirm dialog; rejection feedback; proposal upload.

**Out of scope**

- Project detail rail (Phase 9); institution-side review (Phase 19).

**Evidence basis.** The audit (OBSERVED, 2026-10-28, port 8001) plus a read-only look at the running app on 2026-10-30. No repository source was read when this file was written; every statement about templates, controllers, models, middleware and tests is UNKNOWN until Stage One (Phase 0) fills `DISCOVERY_REPORT.md` section 12.

## B. Pages and routes

| Route pattern | Role | Audit status | Evidence | Notes |
|---|---|---|---|---|
| /student/applications | Student | Inspected | OBSERVED | — |
| /student/applications/{id} | Student | 270, 271 inspected | OBSERVED | Other users' IDs return 404 (ownership enforced). |
| /student/applications/create/{problemId} | Student | 52, 60, 62 inspected | OBSERVED | Form is not submitted in discovery. |

**Audit screenshot IDs for this phase** (audit section L; OBSERVED). Page data below is quoted or summarised from the audit; the screenshots themselves are in the PDF, not in this repository.

| ID | Page | URL | Keep (audit) | Design direction (audit) | Backlog |
|---|---|---|---|---|---|
| S05 | Aplikasi Saya (daftar lamaran) | /student/applications | Kotak alasan penolakan yang terlihat langsung di daftar sangat berguna. | Tabel lamaran dengan tab KKN/Lowongan di atasnya dan filter status sebagai segmented control. | J6, J8, J9, J14, J18 |
| S06 | Detail lamaran — menunggu (#271) | /student/applications/271 | Rail “Informasi Aplikasi” berbentuk daftar definisi (ID, tanggal, status, durasi, periode, tenggat) adalah pola terbaik untuk detail record. | Detail record standar: header (judul + status), kolom konten (motivasi, dokumen, timeline), rail fakta, aksi sekunder di bawah rail. | J8, J14, J32 |
| S07 | Detail lamaran — ditolak (#270) | /student/applications/270 | Feedback dari instansi yang ditampilkan eksplisit. | Sama dengan S06; tambahkan tautan “Cari proyek serupa” setelah penolakan. | J8, J32 |
| S08 | Form melamar proyek | /student/applications/create/52 | Dropzone unggah proposal, ringkasan proyek di atas form, fakta proyek di rail. | Form satu kolom dengan rail fakta sticky; tombol primer “Kirim lamaran”, sekunder “Batal”; konfirmasi toast setelah kirim. | J8, J29, J26 |

## C. Existing implementation

- [OBSERVED] List: bar chart 'Statistik Proyek KKN' maps two applications onto four statuses; status words differ across screens ('Pending' in the list, 'Menunggu Review' in the detail); the rejection reason box visible in the list is useful.
- [OBSERVED] Detail (#270 rejected, #271 pending): facts rail as a definition list (ID, date, status, duration, period, deadline) and timeline are the best record pattern in the app; 'Batalkan Aplikasi' is a full-width solid red button (the strongest element on the page); motivational text breaks mid-word ('dalam p / royek', 'meny / elesaikan', J32).
- [OBSERVED] Apply form: dropzone for proposal, project summary above, facts in the rail, tips with emoji; counter reads '0 / min. 100 karakter' in red before typing (J29).
- [OBSERVED] Other users' application IDs return 404 (ownership enforced); `/create/{problemId}` verified for 52, 60, 62.
- [BLOCKED] 'Diterima' and active-project states never seen (no data).

**Repository facts for this phase: UNKNOWN until Stage One.** Locate and record in `DISCOVERY_REPORT.md` section 12 before editing: applications controller (index/show/create/store/cancel), status enum values, the cancel route and verb, view for the timeline, the textarea counter script, upload field names.

## D. Problems found (audit findings)

| Backlog ID | Finding | Evidence |
|---|---|---|
| J14 (P1) | Destructive action styled as the primary action. | OBSERVED |
| J29 (P3) | Validation colour before any input. | OBSERVED |
| J32 (P2) | Text breaks mid-word. | OBSERVED |
| J8 (P1) | Inconsistent status words and English labels. | OBSERVED |
| J9 (P1) | Chart with 2 data points. | OBSERVED |
| J18 (P2) | Card list instead of table. | OBSERVED |
| J26 (P3) | Emoji in tips. | OBSERVED |

## E. Approved design direction

**Approved by the user (binding)**

- Hybrid direction (AD-04): Ruang Kerja workbench foundation; Catatan Lapangan editorial layer only where reading and storytelling matter. This phase uses none of it unless stated below.
- Principles (AD-05) and functional preservation (AD-06). Tokens, fonts and components below come from the asset package and are PROPOSED until D-17 is approved; existing assets are reconciled under AD-09 (section H); D-16 records that reference documents B and C were not found.

**Proposed for this phase** (audit sections I and L, adapted; status PROPOSED until the user approves the gating decisions)

- Table: Proyek · Instansi · Status · Tanggal · Tenggat; tabs KKN/Lowongan; status as segmented control; rejection reason inline. Detail = header (title + status), content column (motivation, documents, timeline), facts rail, secondary actions beneath the rail; 'Cari proyek serupa' after rejection (S07). Apply form: one column with sticky facts rail, 'Kirim lamaran' primary and 'Batal' secondary, toast on success. Cancel: outline-danger button opening a confirm dialog; solid red only inside the dialog.

**Guardrails that bind this phase** (audit section H, numbered as in `EXECUTION_ROADMAP.md`): 1, 2, 3, 4, 5, 6, 8, 9, 10.

## F. Implementation tasks (do in order)

10.1. Gate check: Phase 1 COMPLETE; D-02 vocabulary; D-21 (no chart).

10.2. Remove the bar chart; replace with a summary line ('2 lamaran · 1 Menunggu · 1 Ditolak').

10.3. Build the applications table with the shared status badge and the tabs; keep all filter parameters and the pager.

10.4. Detail: rebuild header, content, timeline and rail using the definition-list pattern; fix text wrapping (`overflow-wrap:anywhere` only for unbroken strings; normal word break otherwise).

10.5. Cancel: secondary outline-danger trigger, confirm dialog (focus lock, Esc, explicit consequence text), same endpoint and verb as today.

10.6. Form: one column, counter neutral until the first interaction, error colour only after blur/submit, helper text for 100-character minimum, dropzone via the shared upload component keeping the field name; success toast.

10.7. Replace emoji tips with icons or plain text.

10.8. Verify with applications #270, #271, and the create form for problems 52, 60, 62; verify the 404 for foreign IDs.

10.9. Update the tracker and report with S05-S08 before/after.

## G. Components

| Component | Source | Notes |
|---|---|---|
| Table + status badge | Phase 3 | — |
| Tabs/segmented filter | Phase 3 | — |
| Definition-list rail | Phase 3 | — |
| Timeline | page-specific | Plain list with markers (`.sg-marker`). |
| Confirm dialog | Phase 3 | Destructive pattern. |
| Upload | Phase 3 / package `.sg-dropzone` | — |
| Toast | Phase 3 | — |

## H. Assets (Source A existing `public/`, Source B custom package, Source C usage)

> Policy AD-09. Three sources, none disposable: **A** existing assets in `public/`, **B** the custom package `student-go-assets/`, **C** `resources/` (shows what is really used). Every asset has one decision: Retain, Reuse, Adapt, Replace or Deprecate. Originals are never deleted, overwritten, moved or renamed by this phase; copying anything into a production directory happens only if a task above says so. Full inventory, rights and integration map: `student-go-docs/design-execution/ASSET_INVENTORY.md`. Evidence labels as in `DISCOVERY_REPORT.md` section 1.

### H.1 Source A: existing application assets that matter in this phase

| Asset | Decision | What happens in this phase | Evidence |
|---|---|---|---|
| `application-student.jpg` | Deprecate (usage) | Probable page hero on `/student/applications`; replaced by the standard page header. File stays. | INFERRED |

### H.2 Source B: custom package assets available for this phase

**Original SVG, brand, fonts, icons and CSS (made for this project; in the package; licences in `ASSET_INVENTORY.md` section 2)**

- `empty-states/no-applications.svg`, Lucide icons (clock, check, x, file).

### H.3 Reconciliation for this phase

Status vocabulary is one: Menunggu, Ditinjau, Diterima, Ditolak, Dibatalkan.

Overlap comparisons that touch this phase are in `ASSET_INVENTORY.md` section 11; where a Source A asset and a Source B asset serve the same purpose, that table gives the starting recommendation and this phase re-checks it against the real page.

### H.4 Native UI (no asset; plain HTML/CSS)

- Table, dialog, forms.

### H.5 Do not touch in this phase

- Student profile photographs and uploads, institution/company logos, verification documents: never moved or re-encoded; never shown in a new public place (D-04, D-31).

## I. Dependencies

- **Phases:** hard — Phase 1 (Critical Fixes & Data Integrity), Phase 3 (Shared Shell & Components); soft — Phase 9 (Project Detail).
- **Decisions:** D-02 (Language policy and copy deck), D-21 (Charts on dashboards), D-12 (A working database for baseline and QA). A gating decision that is still OPEN in `DECISIONS_AND_OPEN_QUESTIONS.md` means the dependent tasks stop and the agent asks the user; it does not guess.
- **Packages:** No new composer or npm package. Anything new needs explicit approval first.
- **Data / environment:** Applications in pending and rejected states (2 exist); accepted state is BLOCKED.

## J. Functionality preservation

The global rules in `CLAUDE.md` section 7 apply in full. For this phase specifically:

- Cancel keeps its route and verb; ownership checks unchanged.
- Apply form fields, minimum length rule and file rules unchanged.
- Rejection feedback still displayed.
- `/student/applications/create/{problemId}` still pre-fills project data.

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

- Submit an invalid apply form (short motivation) and a valid one on the local DB; compare with baseline behaviour.
- Cancel flow including dismissing the dialog (no change) and confirming.
- Foreign application ID returns 404.
- Keyboard: dialog focus lock and Esc.

*Test commands are not listed here because none could be verified without the repository. `CLAUDE.md` section 4 lists candidate commands that Stage One must confirm.*

## L. Acceptance criteria

The phase is COMPLETE only if every box can be checked with evidence in the phase report. Otherwise the status is PARTIAL (say which boxes failed) or BLOCKED (say what unblocks it).

- [ ] The list is a table with status as a prominent column; the chart is gone.
- [ ] Status words are identical in list, detail and notifications.
- [ ] 'Batalkan' is not a solid red button on the page; a confirm dialog is required and Esc closes it.
- [ ] The counter is neutral until interaction.
- [ ] No mid-word text breaks in the sample text.
- [ ] Form field names and endpoints match the Phase 0 inventory.
- [ ] No horizontal scroll at six widths.

## M. Risks and blockers

| Risk or blocker | Mitigation / what unblocks it | Blocks the phase? |
|---|---|---|
| Cancel route/verb unknown | Stage One; keep exactly. | No |
| Accepted-state design cannot be validated | Mark BLOCKED for that state; use vocabulary. | Partially |

## N. Expected deliverables

- Rewritten applications list/detail/create views; dialog usage.
- Screenshots S05-S08 before/after.
- `student-go-docs/design-execution/PHASE_TRACKER.md`: set Phase 10 to COMPLETE, PARTIAL or BLOCKED with date, branch/commit (if the user asked for one) and a one-line note.
- A phase report in the chat: what changed (files), what was verified (with the evidence), what failed or was skipped and why, decisions needed from the user, and the exact next phase the roadmap allows. Then stop and wait; do not start another phase.
