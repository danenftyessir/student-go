# PHASE 09 — Project Detail

> Source of truth for status is `student-go-docs/design-execution/PHASE_TRACKER.md`. This file is the work order for exactly one phase. Read `CLAUDE.md` and `student-go-docs/design-execution/EXECUTION_ROADMAP.md` first, execute only this phase, update the tracker, report, then stop.
> Evidence labels: VERIFIED / OBSERVED / INFERRED / UNKNOWN / BLOCKED (definitions in `DISCOVERY_REPORT.md` section 1).

| Field | Value |
|---|---|
| Phase | 9 — Project Detail |
| Group | Student role |
| Hard dependencies (must be COMPLETE) | Phase 1 (Critical Fixes & Data Integrity), Phase 3 (Shared Shell & Components) |
| Soft dependencies (recommended first) | Phase 8 (Student Project Discovery) |
| Decisions that gate this phase | D-02 Language policy and copy deck, D-14 Problem gallery images, D-19 Documentary photography, D-30 Provenance and consent for existing photographs in `public/` |
| Effort estimate (planning guess, not a commitment) | M |
| Roles touched | Student |
| Audit references | S03, S04; J8, J10, J12, J19, J26, J31; audit D4 |
| Tracker status at planning time | NOT_STARTED |

## A. Phase overview

**Goal.** Design `/student/browse-problems/{id}` as a brief: title and one-line metadata, a reading column with sectioned content, and a sticky apply rail whose state always matches the student's real application status. Media appears only when it exists.

**In scope**

- Brief layout, reading column (Catatan Lapangan: serif body), apply rail states, media/gallery with fallback, institution and location labels, similar projects, breadcrumbs.

**Out of scope**

- Application form (Phase 10), list (Phase 8).

**Evidence basis.** The audit (OBSERVED, 2026-10-28, port 8001) plus a read-only look at the running app on 2026-10-30. No repository source was read when this file was written; every statement about templates, controllers, models, middleware and tests is UNKNOWN until Stage One (Phase 0) fills `DISCOVERY_REPORT.md` section 12.

## B. Pages and routes

| Route pattern | Role | Audit status | Evidence | Notes |
|---|---|---|---|---|
| /student/browse-problems/{id} | Student | 1-80 fetched; 1, 45, 52, 60, 62 inspected | OBSERVED | 77 x 200, 3 x 404. |

**Audit screenshot IDs for this phase** (audit section L; OBSERVED). Page data below is quoted or summarised from the audit; the screenshots themselves are in the PDF, not in this repository.

| ID | Page | URL | Keep (audit) | Design direction (audit) | Backlog |
|---|---|---|---|---|---|
| S03 | Detail proyek KKN (masalah #1) | /student/browse-problems/1 | Struktur dua kolom (konten + rail), breadcrumb, proyek serupa, chip keterampilan. | Halaman detail bergaya brief: judul + meta satu baris, kolom bacaan dengan section bersubjudul, rail sticky berisi fakta dan tombol lamar. Media hanya bila ada. | J8, J10, J12, J19 |
| S04 | Detail proyek — varian sudah melamar (masalah #45) | /student/browse-problems/45 | Rail yang berubah sesuai state pengguna adalah ide yang tepat. | Komponen rail lamar dengan state: belum melamar, menunggu, ditinjau, diterima, ditolak, kuota penuh, tenggat lewat; masing-masing dengan teks dan warna dari kamus status. | J8, J12, J26 (emoji), J31 |

## C. Existing implementation

- [OBSERVED] Two-column layout (content + rail), breadcrumb, similar projects, skill chips (kept); the media panel shows a broken image with its alt text and 'Gallery 1 / Gallery 2' captions (about 300 px of empty space); sections fade in from partial opacity; header location ('Kota Papua, Papua') differs from the institution address ('Kota Jawa Barat') and neither is labelled; the apply card is white text on a light-blue gradient; the heading 'Requirements' is English.
- [OBSERVED] Every problem references 3-5 gallery images on remote storage: 22 problems x 3, 31 x 4, 24 x 5 (310 total) and all fail locally.
- [OBSERVED] #45 (rejected application) shows a green 'Aplikasi Terkirim' card (J31: wrong state) and #56 (pending) differ from the 75 problems that show 'Apply Sekarang'.

**Repository facts for this phase: UNKNOWN until Stage One.** Locate and record in `DISCOVERY_REPORT.md` section 12 before editing: detail controller/view; how application status is computed for the rail; gallery image field and URL; the similar-projects query; the location fields.

## D. Problems found (audit findings)

| Backlog ID | Finding | Evidence |
|---|---|---|
| J31 (P1) | A rejected application shows a green 'submitted' card. | OBSERVED |
| J12 (P1) | No fallback when gallery images are missing or fail. | OBSERVED |
| J19 (P2) | Unlabelled location mismatch. | OBSERVED |
| J10 (P1) | White text on pale gradient (contrast). | OBSERVED |
| J8 (P1) | English heading 'Requirements' and button 'Apply Sekarang'. | OBSERVED |
| J26 (P3) | Emoji in apply tips. | OBSERVED |

## E. Approved design direction

**Approved by the user (binding)**

- Hybrid direction (AD-04): Ruang Kerja workbench foundation; Catatan Lapangan editorial layer only where the project brief is a reading page.
- Principles (AD-05) and functional preservation (AD-06). Tokens, fonts and components below come from the asset package and are PROPOSED until D-17 is approved; existing assets are reconciled under AD-09 (section H); D-16 records that reference documents B and C were not found.

**Proposed for this phase** (audit sections I and L, adapted; status PROPOSED until the user approves the gating decisions)

- Brief layout: title + one-line meta (instansi · lokasi · SDG · kuota · tenggat); reading column (max 68ch) with subheaded sections; sticky rail with facts and the apply control. Rail states from the status vocabulary: belum melamar, Menunggu, Ditinjau, Diterima, Ditolak, kuota penuh, tenggat lewat; each with text and colour from the shared mapping. Media only when present; gallery collapses on error. Location labelled 'Lokasi proyek' vs 'Alamat instansi'.

**Guardrails that bind this phase** (audit section H, numbered as in `EXECUTION_ROADMAP.md`): 1, 2, 3, 4, 5, 7, 8, 9, 10.

## F. Implementation tasks (do in order)

9.1. Gate check: Phase 1 COMPLETE; D-14 for images; D-02 for state labels.

9.2. Read the controller to learn how the rail currently decides its state; map each real state to the shared vocabulary without inventing states that do not exist (quota full and deadline passed only if the data supports them).

9.3. Build the brief layout: h1 and meta, reading column using `.sg-reading`/`.sg-prose`, sticky rail (from 1024 px; on mobile the apply bar is a bottom action or the first element).

9.4. Rail component with all states; the destructive state 'Batalkan' is not shown here (lives in Phase 10).

9.5. Gallery: render the figure only if at least one image loads; on error remove the element and its caption; never show alt text in a grey box.

9.6. Labels for location and institution address; wrap long values.

9.7. Similar projects as three list rows.

9.8. Verify with problems #1 (no application), #45 (rejected), #56 (pending), and one with 5 gallery images.

9.9. Update the tracker and report with S03/S04 before/after.

## G. Components

| Component | Source | Notes |
|---|---|---|
| Brief header | Phase 3 page header | — |
| Reading column | package `.sg-reading` | Serif body. |
| Sticky rail with state | Phase 3 definition list + status | States listed above. |
| Gallery figure with fallback | package `.sg-figure` | — |
| Skill chips | package `.sg-chip` | — |
| Similar rows | Phase 3 list | — |

## H. Assets (Source A existing `public/`, Source B custom package, Source C usage)

> Policy AD-09. Three sources, none disposable: **A** existing assets in `public/`, **B** the custom package `student-go-assets/`, **C** `resources/` (shows what is really used). Every asset has one decision: Retain, Reuse, Adapt, Replace or Deprecate. Originals are never deleted, overwritten, moved or renamed by this phase; copying anything into a production directory happens only if a task above says so. Full inventory, rights and integration map: `student-go-docs/design-execution/ASSET_INVENTORY.md`. Evidence labels as in `DISCOVERY_REPORT.md` section 1.

### H.1 Source A: existing application assets that matter in this phase

| Asset | Decision | What happens in this phase | Evidence |
|---|---|---|---|
| Problem gallery images (remote) | Retain | Detail pages show images only when they load (D-14). | OBSERVED (audit J12) |

### H.2 Source B: custom package assets available for this phase

**Original SVG, brand, fonts, icons and CSS (made for this project; in the package; licences in `ASSET_INVENTORY.md` section 2)**

- Lucide icons; `motifs/survey-marks.svg` for section dividers (sparingly).

**Catalogue photographs (Source B: licence-checked, NOT downloaded; deferred by D-33; used only when no Source A photograph fills the slot)**

- Optional: none by default. A documentary photo in a brief only if it is the real photo of that project; stock never.

### H.3 Reconciliation for this phase

Nothing from Source A is promoted to a project image: a project photo comes from the project's own upload.

Overlap comparisons that touch this phase are in `ASSET_INVENTORY.md` section 11; where a Source A asset and a Source B asset serve the same purpose, that table gives the starting recommendation and this phase re-checks it against the real page.

### H.4 Native UI (no asset; plain HTML/CSS)

- Text, rail.

### H.5 Do not touch in this phase

- Student profile photographs and uploads, institution/company logos, verification documents: never moved or re-encoded; never shown in a new public place (D-04, D-31).

## I. Dependencies

- **Phases:** hard — Phase 1 (Critical Fixes & Data Integrity), Phase 3 (Shared Shell & Components); soft — Phase 8 (Student Project Discovery).
- **Decisions:** D-02 (Language policy and copy deck), D-14 (Problem gallery images), D-19 (Documentary photography), D-30 (Provenance and consent for existing photographs in `public/`). A gating decision that is still OPEN in `DECISIONS_AND_OPEN_QUESTIONS.md` means the dependent tasks stop and the agent asks the user; it does not guess.
- **Packages:** No new composer or npm package. Anything new needs explicit approval first.
- **Data / environment:** Problems with 0, 3 and 5 images; applications in pending and rejected states.

## J. Functionality preservation

The global rules in `CLAUDE.md` section 7 apply in full. For this phase specifically:

- The apply link still points to `/student/applications/create/{problemId}`.
- Eligibility and quota logic unchanged.
- Similar-projects and breadcrumb links keep working.
- View counters keep behaving as before.

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

- Open #1, #45, #56, #52; the rail state matches the application records.
- Block image requests: page shows no empty box.
- Long title, long institution name at 360 px.
- Reading column width <= 68ch at 1440 px.

*Test commands are not listed here because none could be verified without the repository. `CLAUDE.md` section 4 lists candidate commands that Stage One must confirm.*

## L. Acceptance criteria

The phase is COMPLETE only if every box can be checked with evidence in the phase report. Otherwise the status is PARTIAL (say which boxes failed) or BLOCKED (say what unblocks it).

- [ ] Rail state equals the real application state for #45 (rejected) and #56 (pending); a rejected application never shows a success colour.
- [ ] No broken image, alt-text box or 'Gallery N' caption is ever visible; the gallery collapses.
- [ ] Body text is 16 px/24 px with <= 68ch line length.
- [ ] Location and institution address are labelled and wrap.
- [ ] All controls pass contrast; no gradient on the apply control.
- [ ] No English headings.

## M. Risks and blockers

| Risk or blocker | Mitigation / what unblocks it | Blocks the phase? |
|---|---|---|
| Application states not fully known (accepted/active never seen) | BLOCKED visual for 'Diterima'; design from the vocabulary and test when data exists. | Partially |
| Real images change the layout | Re-check with D-14 images. | No |

## N. Expected deliverables

- Rewritten detail view and rail component.
- Before/after screenshots (S03, S04).
- `student-go-docs/design-execution/PHASE_TRACKER.md`: set Phase 9 to COMPLETE, PARTIAL or BLOCKED with date, branch/commit (if the user asked for one) and a one-line note.
- A phase report in the chat: what changed (files), what was verified (with the evidence), what failed or was skipped and why, decisions needed from the user, and the exact next phase the roadmap allows. Then stop and wait; do not start another phase.
