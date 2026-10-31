# PHASE 12 — Profile & Portfolio

> Source of truth for status is `student-go-docs/design-execution/PHASE_TRACKER.md`. This file is the work order for exactly one phase. Read `CLAUDE.md` and `student-go-docs/design-execution/EXECUTION_ROADMAP.md` first, execute only this phase, update the tracker, report, then stop.
> Evidence labels: VERIFIED / OBSERVED / INFERRED / UNKNOWN / BLOCKED (definitions in `DISCOVERY_REPORT.md` section 1).

| Field | Value |
|---|---|
| Phase | 12 — Profile & Portfolio |
| Group | Student role |
| Hard dependencies (must be COMPLETE) | Phase 1 (Critical Fixes & Data Integrity), Phase 3 (Shared Shell & Components) |
| Soft dependencies (recommended first) | none |
| Decisions that gate this phase | D-03 Single source of truth for a student's display name, D-04 Student contact visibility policy, D-19 Documentary photography, D-23 Content ownership for new or rewritten copy, D-31 Personal photographs, student photos and identifying file names, D-32 Certificate assets (templates, Pinyon Script, Poppins) |
| Effort estimate (planning guess, not a commitment) | L |
| Roles touched | Student; public viewers |
| Audit references | S18, S19, S23; J1, J3, J5, J6, J8, J10, J11; audit D7, D8 |
| Tracker status at planning time | NOT_STARTED |

## A. Phase overview

**Goal.** Make the student profile a shareable portfolio (story, verified projects, skills, private contact by default) and its edit form a sectioned form whose fields always show the stored values.

**In scope**

- `/student/profile`, `/student/profile/edit`, `/profile/{username}` (public).

**Out of scope**

- Friends/network (Phase 13).
- Backend identity and privacy logic (Phase 1).

**Evidence basis.** The audit (OBSERVED, 2026-10-28, port 8001) plus a read-only look at the running app on 2026-10-30. No repository source was read when this file was written; every statement about templates, controllers, models, middleware and tests is UNKNOWN until Stage One (Phase 0) fills `DISCOVERY_REPORT.md` section 12.

## B. Pages and routes

| Route pattern | Role | Audit status | Evidence | Notes |
|---|---|---|---|---|
| /profile/{username} | Anyone incl. guest | Inspected (6 opened, 699 enumerated) | OBSERVED | Shows email and WhatsApp to guests (P0, J3); name differs from account (J1). Privacy fix in Phase 1. |
| /student/profile | Student | Inspected | OBSERVED | — |
| /student/profile/edit | Student | Inspected | OBSERVED | Name fields load empty (J5). |

**Audit screenshot IDs for this phase** (audit section L; OBSERVED). Page data below is quoted or summarised from the audit; the screenshots themselves are in the PDF, not in this repository.

| ID | Page | URL | Keep (audit) | Design direction (audit) | Backlog |
|---|---|---|---|---|---|
| S18 | Profil Saya (mahasiswa) | /student/profile | Urutan konten portofolio (berbagi, cerita, proyek selesai) sesuai tujuan profil. | Profil sebagai portofolio yang bisa dibagikan: header identitas, cerita (kolom bacaan), proyek terverifikasi (daftar dengan instansi dan tahun), keterampilan; kontak privat. | J1, J6, J10 |
| S19 | Edit Profil (mahasiswa) | /student/profile/edit | Header polos, form satu kolom, pemisahan form profil dan password, footer form dengan Batal/Simpan. | Form bersection (Identitas, Akademik, Kontak, Portofolio, Keamanan) dengan navigasi section di kiri pada desktop. | J5, J8, J11 |
| S23 | Profil publik mahasiswa | /profile/juliasuryanto1000 | Halaman publik yang bisa dibagikan sebagai portofolio adalah fitur bernilai. | Portofolio publik tanpa data kontak; meta untuk dibagikan (judul, deskripsi) diisi. | J1, J3, J6 |

## C. Existing implementation

- [OBSERVED] Profile: hero with four translucent glass stat tiles; 'Salin Link Portfolio' is a gradient button; titled 'Desi Hartono' for the account shown as 'Julia Suryanto' (J1); the portfolio order (share, story, completed projects) is right.
- [OBSERVED] Edit: header + single-column form, labelled fields, separate profile and password forms, Cancel/Save footer; first/last name empty although university, major, NIM, semester and WhatsApp load (J5).
- [OBSERVED] Public profile: name differs from the account; 0 of 127 sampled profiles have a story; email and WhatsApp visible to guests (J3).
- [VERIFIED 2026-10-30] Cannot view data pages locally (DB unreachable).

**Repository facts for this phase: UNKNOWN until Stage One.** Locate and record in `DISCOVERY_REPORT.md` section 12 before editing: profile views, public profile controller, edit form field names and validation, avatar/photo handling, share-link generation, meta tags for sharing.

## D. Problems found (audit findings)

| Backlog ID | Finding | Evidence |
|---|---|---|
| J1 (P0) | Name and initials mismatch (verify after Phase 1). | OBSERVED |
| J3 (P0) | Guest sees email and WhatsApp (verify after Phase 1). | OBSERVED |
| J5 (P1) | Edit form drops stored name. | OBSERVED |
| J6 (P1) | Hero with glass tiles. | OBSERVED |
| J10 (P1) | Contrast issues. | OBSERVED |
| J11 (P1) | Unlabelled/unnamed controls. | OBSERVED |
| J8 (P1) | Mixed language. | OBSERVED |

## E. Approved design direction

**Approved by the user (binding)**

- Hybrid direction (AD-04): Ruang Kerja workbench foundation; Catatan Lapangan editorial layer only where the student story and portfolio are reading surfaces.
- Principles (AD-05) and functional preservation (AD-06). Tokens, fonts and components below come from the asset package and are PROPOSED until D-17 is approved; existing assets are reconciled under AD-09 (section H); D-16 records that reference documents B and C were not found.

**Proposed for this phase** (audit sections I and L, adapted; status PROPOSED until the user approves the gating decisions)

- Portfolio page: identity header (name, university, major, location), story as a reading column (serif, Catatan Lapangan), verified projects as a list with institution and year, skills as chips, share button as secondary ('Salin tautan portofolio'). Contact block private by default and visible only under the D-04 policy. Public page has the same layout, with `<title>` and description meta filled for sharing. Edit page: section navigation at left on desktop (Identitas, Akademik, Kontak, Portofolio, Keamanan), single-column form, stored values always loaded.

**Guardrails that bind this phase** (audit section H, numbered as in `EXECUTION_ROADMAP.md`): 1, 2, 3, 4, 5, 6, 7, 9, 10.

## F. Implementation tasks (do in order)

12.1. Gate check: Phase 1 COMPLETE (identity, binding, privacy), D-04, D-19 (any photo use), D-23.

12.2. Portfolio header and story: no glass tiles, no gradient button; 'Salin tautan portofolio' as a secondary button with a toast confirmation.

12.3. Verified projects list: only completed projects with institution and year; empty state invites the student to apply, without fake content.

12.4. Public profile: same layout without private fields; add title/description/OG meta from real data; confirm that guests see no contact data.

12.5. Edit form: sectioned form with the shared stepper/section nav; every field bound to the stored value; keep names and validation; separate password form stays separate.

12.6. Avatar: initials from the single display name helper; no placeholder photo tiles.

12.7. Verify with a student that has a story and with one that has none (0 of 127 have stories: test the empty state primarily).

12.8. Update the tracker and report with S18/S19/S23 before/after.

## G. Components

| Component | Source | Notes |
|---|---|---|
| Identity header | Phase 3 page header + avatar | — |
| Reading column | package `.sg-reading` | Story. |
| Project list | Phase 3 list | — |
| Chips | package `.sg-chip` | — |
| Sectioned form + section nav | Phase 3 | — |
| Toast | Phase 3 | — |

## H. Assets (Source A existing `public/`, Source B custom package, Source C usage)

> Policy AD-09. Three sources, none disposable: **A** existing assets in `public/`, **B** the custom package `student-go-assets/`, **C** `resources/` (shows what is really used). Every asset has one decision: Retain, Reuse, Adapt, Replace or Deprecate. Originals are never deleted, overwritten, moved or renamed by this phase; copying anything into a production directory happens only if a task above says so. Full inventory, rights and integration map: `student-go-docs/design-execution/ASSET_INVENTORY.md`. Evidence labels as in `DISCOVERY_REPORT.md` section 1.

### H.1 Source A: existing application assets that matter in this phase

| Asset | Decision | What happens in this phase | Evidence |
|---|---|---|---|
| Student profile photographs (uploads) | Retain | Genuine user photographs: shown where they are today. The public profile exposes contact data to guests (J3, D-04); do not add a larger or more prominent public photo before that is decided. | UNKNOWN / BLOCKED |
| Certificates shown in the portfolio (if any) | Retain | Whatever the profile displays about certificates stays as it works today (D-32). | UNKNOWN / BLOCKED |
| `profile_*` team portraits | Retain | Not student-profile assets; unaffected. | VERIFIED |

### H.2 Source B: custom package assets available for this phase

**Original SVG, brand, fonts, icons and CSS (made for this project; in the package; licences in `ASSET_INVENTORY.md` section 2)**

- Lucide icons; initials avatar (CSS); `motifs/contour-field.svg` optional on the profile header.

**Catalogue photographs (Source B: licence-checked, NOT downloaded; deferred by D-33; used only when no Source A photograph fills the slot)**

- Student-provided photos only; no stock photos on student profiles. Documentary photographs only inside a story if the student uploads their own (needs the existing upload feature; do not add one).

### H.3 Reconciliation for this phase

No stock photo on a student profile, ever. Documentary photos only inside a story the student uploads, and only through an existing feature.

Overlap comparisons that touch this phase are in `ASSET_INVENTORY.md` section 11; where a Source A asset and a Source B asset serve the same purpose, that table gives the starting recommendation and this phase re-checks it against the real page.

### H.4 Native UI (no asset; plain HTML/CSS)

- Forms and text.

### H.5 Do not touch in this phase

- Student profile photographs and uploads, institution/company logos, verification documents: never moved or re-encoded; never shown in a new public place (D-04, D-31).
- Certificate templates and the Pinyon Script and Poppins fonts (and whatever generates certificates): UNKNOWN location (D-32). Do not remove, rename, re-encode or restyle them, and do not remove a Google Fonts link that a certificate view depends on.

## I. Dependencies

- **Phases:** hard — Phase 1 (Critical Fixes & Data Integrity), Phase 3 (Shared Shell & Components); soft — none.
- **Decisions:** D-03 (Single source of truth for a student's display name), D-04 (Student contact visibility policy), D-19 (Documentary photography), D-23 (Content ownership for new or rewritten copy), D-31 (Personal photographs, student photos and identifying file names), D-32 (Certificate assets (templates, Pinyon Script, Poppins)). A gating decision that is still OPEN in `DECISIONS_AND_OPEN_QUESTIONS.md` means the dependent tasks stop and the agent asks the user; it does not guess.
- **Packages:** No new composer or npm package. Anything new needs explicit approval first.
- **Data / environment:** Students with and without stories; one student with a long name.

## J. Functionality preservation

The global rules in `CLAUDE.md` section 7 apply in full. For this phase specifically:

- Edit form field names, validation rules and the password-change flow unchanged.
- `/profile/{username}` keeps working for guests (minus private fields).
- Share link uses the existing URL.
- Photo upload behaviour unchanged.

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

- Edit form: change one field, save, compare stored data; confirm all fields prefilled before editing.
- Guest, other student, connected student and company views of the same public profile for D-04 compliance.
- Copy link action puts the real URL on the clipboard and shows a toast.
- Keyboard/section nav.

*Test commands are not listed here because none could be verified without the repository. `CLAUDE.md` section 4 lists candidate commands that Stage One must confirm.*

## L. Acceptance criteria

The phase is COMPLETE only if every box can be checked with evidence in the phase report. Otherwise the status is PARTIAL (say which boxes failed) or BLOCKED (say what unblocks it).

- [ ] Edit form loads every stored value including first and last name.
- [ ] Display name and initials match the account everywhere.
- [ ] A guest's response for the public profile contains no email or phone.
- [ ] Profile has no hero image or glass tiles and no gradient button.
- [ ] Public profile has a meaningful `<title>` and meta description.
- [ ] An empty story shows an honest empty state.
- [ ] Passes contrast and keyboard checks.

## M. Risks and blockers

| Risk or blocker | Mitigation / what unblocks it | Blocks the phase? |
|---|---|---|
| Phase 1 incomplete | Hard dependency. | Yes |
| Contact policy undecided | D-04 gate. | Yes |
| Photo uploads are not part of the current profile | Do not add; ask via D-26. | No |

## N. Expected deliverables

- Rewritten profile, edit and public profile views.
- Screenshots S18/S19/S23.
- `student-go-docs/design-execution/PHASE_TRACKER.md`: set Phase 12 to COMPLETE, PARTIAL or BLOCKED with date, branch/commit (if the user asked for one) and a one-line note.
- A phase report in the chat: what changed (files), what was verified (with the evidence), what failed or was skipped and why, decisions needed from the user, and the exact next phase the roadmap allows. Then stop and wait; do not start another phase.
