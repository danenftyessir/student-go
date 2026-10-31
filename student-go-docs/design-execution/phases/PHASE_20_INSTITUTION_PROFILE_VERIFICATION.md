# PHASE 20 — Institution Profile & Verification

> Source of truth for status is `student-go-docs/design-execution/PHASE_TRACKER.md`. This file is the work order for exactly one phase. Read `CLAUDE.md` and `student-go-docs/design-execution/EXECUTION_ROADMAP.md` first, execute only this phase, update the tracker, report, then stop.
> Evidence labels: VERIFIED / OBSERVED / INFERRED / UNKNOWN / BLOCKED (definitions in `DISCOVERY_REPORT.md` section 1).

| Field | Value |
|---|---|
| Phase | 20 — Institution Profile & Verification |
| Group | Institution role |
| Hard dependencies (must be COMPLETE) | Phase 3 (Shared Shell & Components) |
| Soft dependencies (recommended first) | Phase 1 (Critical Fixes & Data Integrity), Phase 5 (About, Contact, Public Institution Profile) |
| Decisions that gate this phase | D-06 Enum alignment for institution type and company size, D-09 Verification process for institutions and companies, D-13 Seed data for blocked screens |
| Effort estimate (planning guess, not a commitment) | M |
| Roles touched | Institution |
| Audit references | I07, I08, I09; J5, J9, J17, J19, J21; audit D10 |
| Tracker status at planning time | NOT_STARTED |

## A. Phase overview

**Goal.** Make the institution profile an organisation page that survives long emails, an edit form that preselects stored values, and a verification page with a per-document checklist and one upload component.

**In scope**

- `/institution/profile`, `/institution/profile/edit`, `/institution/verification/upload`.

**Out of scope**

- Public profile (Phase 5, shared pattern); admin review UI (BLOCKED, D-11).

**Evidence basis.** The audit (OBSERVED, 2026-10-28, port 8001) plus a read-only look at the running app on 2026-10-30. No repository source was read when this file was written; every statement about templates, controllers, models, middleware and tests is UNKNOWN until Stage One (Phase 0) fills `DISCOVERY_REPORT.md` section 12.

## B. Pages and routes

| Route pattern | Role | Audit status | Evidence | Notes |
|---|---|---|---|---|
| /institution/profile | Institution | Inspected | OBSERVED | Long email overflows the phone column (J19). |
| /institution/profile/edit | Institution | Inspected | OBSERVED | 'Jenis Instansi' not preselected (J5). |
| /institution/verification/upload | Institution | Inspected | OBSERVED | Native file input without styling. |

**Audit screenshot IDs for this phase** (audit section L; OBSERVED). Page data below is quoted or summarised from the audit; the screenshots themselves are in the PDF, not in this repository.

| ID | Page | URL | Keep (audit) | Design direction (audit) | Backlog |
|---|---|---|---|---|---|
| I07 | Profil Instansi | /institution/profile | Tata letak dua kolom, grid definisi, kartu Penanggung Jawab, tombol Edit Profil dan Lihat Profil Publik. | Profil organisasi standar (sama dengan halaman publik) dengan tombol edit; grid fakta 2 kolom yang tahan teks panjang. | J19, J9 |
| I08 | Edit Profil Instansi | /institution/profile/edit | Pengelompokan per kartu, placeholder lokal, tombol Simpan lebar penuh. | Form bersection yang sama dengan form pendaftaran instansi (field identik). | J5, J17 |
| I09 | Verifikasi Dokumen Institusi | /institution/verification/upload | Kartu status “Menunggu Verifikasi”, deskripsi format dan ukuran per dokumen. | Checklist verifikasi bertahap dengan status per dokumen. | J17, J21 |

## C. Existing implementation

- [OBSERVED] Profile: two-column layout, definition grid, 'Penanggung Jawab' card, 'Edit Profil' and 'Lihat Profil Publik' buttons; the email dinaskesehatankabupatenbogor85@dinaskesehatan.go.id (51 characters) overflows into the phone column.
- [OBSERVED] Edit: cards per group, local placeholders, full-width Save; 'Jenis Instansi' shows 'Pilih Jenis' because stored 'Dinas Kesehatan' is not in the options (pemerintah_desa, dinas, ngo, puskesmas, sekolah, perguruan_tinggi, lainnya).
- [OBSERVED] Verification: status card 'Menunggu Verifikasi', per-document format and size notes, raw unstyled native file inputs. The page text mentions automatic verification using AI.

**Repository facts for this phase: UNKNOWN until Stage One.** Locate and record in `DISCOVERY_REPORT.md` section 12 before editing: institution profile controller/views, enum options and stored values, verification upload handling (field names, storage), verification status model.

## D. Problems found (audit findings)

| Backlog ID | Finding | Evidence |
|---|---|---|
| J5 (P1) | Institution type not preselected. | OBSERVED |
| J19 (P2) | Email overflow. | OBSERVED |
| J21 (P2) | Native file inputs. | OBSERVED |
| J17 (P2) | Different upload components. | OBSERVED |
| J9 (P1) | Trend/stat issues carried from dashboard. | OBSERVED |
| D-09 | Verification promise vs real process unknown. | OBSERVED |

## E. Approved design direction

**Approved by the user (binding)**

- Hybrid direction (AD-04): Ruang Kerja workbench foundation; Catatan Lapangan editorial layer only where reading and storytelling matter. This phase uses none of it unless stated below.
- Principles (AD-05) and functional preservation (AD-06). Tokens, fonts and components below come from the asset package and are PROPOSED until D-17 is approved; existing assets are reconciled under AD-09 (section H); D-16 records that reference documents B and C were not found.

**Proposed for this phase** (audit sections I and L, adapted; status PROPOSED until the user approves the gating decisions)

- Profile = the standard organisation profile (same as public page and company profile): header, facts grid with `min-width:0` and wrapping, about, PIC card. Edit = sectioned form with the same fields as registration (parity). Verification = checklist: each required document with status (Belum diunggah, Menunggu, Disetujui, Ditolak), format hint, the shared upload component; copy that states only what the real process does.

**Guardrails that bind this phase** (audit section H, numbered as in `EXECUTION_ROADMAP.md`): 1, 2, 3, 4, 6, 7, 9, 10.

## F. Implementation tasks (do in order)

20.1. Gate check: D-06 (enum alignment done in Phase 1), D-09 (process copy), D-13.

20.2. Profile: rebuild with the standard organisation profile; wrap long emails and URLs; keep both buttons (edit, public profile).

20.3. Edit: sectioned form with shared components; bind all stored values; field names unchanged.

20.4. Verification: build the checklist and the shared upload component with the same field names and `enctype`; show per-document status only if the backend stores it, otherwise show a single status.

20.5. Verify with the 51-character email and a long institution name at 360 px.

20.6. Update the tracker and report with I07-I09 before/after.

## G. Components

| Component | Source | Notes |
|---|---|---|
| Organisation profile (header + facts grid + PIC card) | Phase 3 | Shared with Phases 5 and 25. |
| Sectioned form | Phase 3 | — |
| Upload + file chip | package | — |
| Checklist with status | Phase 3 list + status | — |

## H. Assets (Source A existing `public/`, Source B custom package, Source C usage)

> Policy AD-09. Three sources, none disposable: **A** existing assets in `public/`, **B** the custom package `student-go-assets/`, **C** `resources/` (shows what is really used). Every asset has one decision: Retain, Reuse, Adapt, Replace or Deprecate. Originals are never deleted, overwritten, moved or renamed by this phase; copying anything into a production directory happens only if a task above says so. Full inventory, rights and integration map: `student-go-docs/design-execution/ASSET_INVENTORY.md`. Evidence labels as in `DISCOVERY_REPORT.md` section 1.

### H.1 Source A: existing application assets that matter in this phase

| Asset | Decision | What happens in this phase | Evidence |
|---|---|---|---|
| Institution logo and verification documents (uploads) | Retain | Functional and private. Preserve upload fields and storage; never show verification documents publicly (D-31). | UNKNOWN / BLOCKED |

### H.2 Source B: custom package assets available for this phase

**Original SVG, brand, fonts, icons and CSS (made for this project; in the package; licences in `ASSET_INVENTORY.md` section 2)**

- Lucide icons (file-check, upload, shield-check).

### H.3 Reconciliation for this phase

No overlap.

Overlap comparisons that touch this phase are in `ASSET_INVENTORY.md` section 11; where a Source A asset and a Source B asset serve the same purpose, that table gives the starting recommendation and this phase re-checks it against the real page.

### H.4 Native UI (no asset; plain HTML/CSS)

- Forms.

### H.5 Do not touch in this phase

- All verification documents.
- Student profile photographs and uploads, institution/company logos, verification documents: never moved or re-encoded; never shown in a new public place (D-04, D-31).

## I. Dependencies

- **Phases:** hard — Phase 3 (Shared Shell & Components); soft — Phase 1 (Critical Fixes & Data Integrity), Phase 5 (About, Contact, Public Institution Profile).
- **Decisions:** D-06 (Enum alignment for institution type and company size), D-09 (Verification process for institutions and companies), D-13 (Seed data for blocked screens). A gating decision that is still OPEN in `DECISIONS_AND_OPEN_QUESTIONS.md` means the dependent tasks stop and the agent asks the user; it does not guess.
- **Packages:** No new composer or npm package. Anything new needs explicit approval first.
- **Data / environment:** Institution with a long email and name; pending and verified states.

## J. Functionality preservation

The global rules in `CLAUDE.md` section 7 apply in full. For this phase specifically:

- Verification upload field names, accepted types and sizes unchanged.
- Verification status logic unchanged.
- Edit form field names unchanged.

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

- Long email at 360 px.
- Upload a test file on the local DB.
- Stored type shows as selected.
- Keyboard operation of upload.

*Test commands are not listed here because none could be verified without the repository. `CLAUDE.md` section 4 lists candidate commands that Stage One must confirm.*

## L. Acceptance criteria

The phase is COMPLETE only if every box can be checked with evidence in the phase report. Otherwise the status is PARTIAL (say which boxes failed) or BLOCKED (say what unblocks it).

- [ ] No text overflows its cell at 360 px.
- [ ] Institution type and every other stored value is preselected in the edit form.
- [ ] The verification page uses the shared upload component and does not render a native file input unstyled.
- [ ] Verification copy makes no claim beyond what D-09 states.
- [ ] Field names match the baseline inventory.

## M. Risks and blockers

| Risk or blocker | Mitigation / what unblocks it | Blocks the phase? |
|---|---|---|
| Verification process unknown (D-09) | Neutral copy. | No |
| Admin side invisible | Out of scope (D-11). | No |

## N. Expected deliverables

- Rewritten profile, edit and verification views.
- Screenshots I07-I09.
- `student-go-docs/design-execution/PHASE_TRACKER.md`: set Phase 20 to COMPLETE, PARTIAL or BLOCKED with date, branch/commit (if the user asked for one) and a one-line note.
- A phase report in the chat: what changed (files), what was verified (with the evidence), what failed or was skipped and why, decisions needed from the user, and the exact next phase the roadmap allows. Then stop and wait; do not start another phase.
