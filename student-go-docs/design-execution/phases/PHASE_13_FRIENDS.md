# PHASE 13 — Friends

> Source of truth for status is `student-go-docs/design-execution/PHASE_TRACKER.md`. This file is the work order for exactly one phase. Read `CLAUDE.md` and `student-go-docs/design-execution/EXECUTION_ROADMAP.md` first, execute only this phase, update the tracker, report, then stop.
> Evidence labels: VERIFIED / OBSERVED / INFERRED / UNKNOWN / BLOCKED (definitions in `DISCOVERY_REPORT.md` section 1).

| Field | Value |
|---|---|
| Phase | 13 — Friends |
| Group | Student role |
| Hard dependencies (must be COMPLETE) | Phase 1 (Critical Fixes & Data Integrity), Phase 3 (Shared Shell & Components) |
| Soft dependencies (recommended first) | Phase 12 (Profile & Portfolio) |
| Decisions that gate this phase | D-02 Language policy and copy deck, D-03 Single source of truth for a student's display name, D-04 Student contact visibility policy, D-31 Personal photographs, student photos and identifying file names |
| Effort estimate (planning guess, not a commitment) | M |
| Roles touched | Student |
| Audit references | S12, S13; J1, J6, J7, J8, J18; audit D7 |
| Tracker status at planning time | NOT_STARTED |

## A. Phase overview

**Goal.** Present the network as a people directory: one filter row, a dense divided list, one action per row; fix the pagination overflow at 768 px and the English 'Add Connection'.

**In scope**

- `/student/friends`, `/student/friends/search`, `/student/friends/profile/{id}` (redirect).

**Out of scope**

- Profile pages (Phase 12); company talent search (Phase 22, same pattern).

**Evidence basis.** The audit (OBSERVED, 2026-10-28, port 8001) plus a read-only look at the running app on 2026-10-30. No repository source was read when this file was written; every statement about templates, controllers, models, middleware and tests is UNKNOWN until Stage One (Phase 0) fills `DISCOVERY_REPORT.md` section 12.

## B. Pages and routes

| Route pattern | Role | Audit status | Evidence | Notes |
|---|---|---|---|---|
| /student/friends | Student | Inspected | OBSERVED | — |
| /student/friends/search | Student | Inspected | OBSERVED | ?page, ?major, ?university_id, ?q. |
| /student/friends/profile/{id} | Student | 699 enumerated | OBSERVED | Redirects to /profile/{username}; keep the redirect. |

**Audit screenshot IDs for this phase** (audit section L; OBSERVED). Page data below is quoted or summarised from the audit; the screenshots themselves are in the PDF, not in this repository.

| ID | Page | URL | Keep (audit) | Design direction (audit) | Backlog |
|---|---|---|---|---|---|
| S12 | Jaringan Anda | /student/friends | Empty state dengan ikon orang dan ajakan yang jelas. | Halaman jaringan berbasis tab dengan daftar orang ber-divider. | J6, J8 |
| S13 | Temukan Koneksi Baru (pencarian mahasiswa) | /student/friends/search | Jumlah hasil “699 mahasiswa ditemukan”, filter cepat. | Direktori orang: search + filter (universitas, jurusan, provinsi) di satu baris, daftar padat, aksi per baris. | J1, J6, J7, J8, J18 |

## C. Existing implementation

- [OBSERVED] Friends: repeated photo hero, empty state with a clear people icon and invitation (kept).
- [OBSERVED] Search: 699 results across 35 pages; compact rows (avatar, name, major, university, two actions); quick filters 'Dari Universitas Saya' and 'Jurusan Yang Sama' fit the product; hero 'Temukan Koneksi Baru' at 48 px; 'Add Connection' in English; pagination overflows at 768 px.
- [OBSERVED] `/student/friends/profile/{id}` redirects to `/profile/{username}`; the opened profile shows a name different from the linking account (J1).

**Repository facts for this phase: UNKNOWN until Stage One.** Locate and record in `DISCOVERY_REPORT.md` section 12 before editing: friends controller, connection model and endpoints (add, accept, remove), filter parameter names (`q`, `major`, `university_id`), pagination view.

## D. Problems found (audit findings)

| Backlog ID | Finding | Evidence |
|---|---|---|
| J1 (P0) | Name mismatch in results and profile. | OBSERVED |
| J6 (P1) | Hero photo. | OBSERVED |
| J7 (P1) | Pagination overflow at 768 px. | OBSERVED |
| J8 (P1) | 'Add Connection' in English. | OBSERVED |
| J18 (P2) | Card-like results where a list suffices. | OBSERVED |

## E. Approved design direction

**Approved by the user (binding)**

- Hybrid direction (AD-04): Ruang Kerja workbench foundation; Catatan Lapangan editorial layer only where reading and storytelling matter. This phase uses none of it unless stated below.
- Principles (AD-05) and functional preservation (AD-06). Tokens, fonts and components below come from the asset package and are PROPOSED until D-17 is approved; existing assets are reconciled under AD-09 (section H); D-16 records that reference documents B and C were not found.

**Proposed for this phase** (audit sections I and L, adapted; status PROPOSED until the user approves the gating decisions)

- Tabs on `/student/friends` (Koneksi, Permintaan, Saran); a directory on `/student/friends/search` with search + filters (universitas, jurusan, provinsi) in one row, a divided list, one primary action per row ('Hubungkan'), view-profile as the row link. Contact info never shown in the list (D-04).

**Guardrails that bind this phase** (audit section H, numbered as in `EXECUTION_ROADMAP.md`): 1, 2, 3, 4, 5, 6, 7, 9, 10.

## F. Implementation tasks (do in order)

13.1. Gate check: Phase 1 COMPLETE; D-02 copy for connection actions ('Hubungkan', 'Tambah Koneksi').

13.2. Rebuild `/student/friends` with tabs and divided list; empty state keeps the people icon and invitation.

13.3. Rebuild search with a single filter row, the quick filters, a result count ('699 mahasiswa ditemukan' as an honest filtered total), a divided list and the stable pager.

13.4. Connect action keeps the existing endpoint; replace English text; show pending/connected state on the row.

13.5. Fix the pagination overflow at 768 px (one paginator component).

13.6. Verify the redirect from `/student/friends/profile/{id}` and that rows link to the right public profile.

13.7. Update the tracker and report with S12/S13 before/after.

## G. Components

| Component | Source | Notes |
|---|---|---|
| Tabs | Phase 3 | — |
| People list row (avatar initials, name, major, university, action) | Phase 3 list | — |
| Filter row | Phase 3 | — |
| Pager | Phase 3 | — |

## H. Assets (Source A existing `public/`, Source B custom package, Source C usage)

> Policy AD-09. Three sources, none disposable: **A** existing assets in `public/`, **B** the custom package `student-go-assets/`, **C** `resources/` (shows what is really used). Every asset has one decision: Retain, Reuse, Adapt, Replace or Deprecate. Originals are never deleted, overwritten, moved or renamed by this phase; copying anything into a production directory happens only if a task above says so. Full inventory, rights and integration map: `student-go-docs/design-execution/ASSET_INVENTORY.md`. Evidence labels as in `DISCOVERY_REPORT.md` section 1.

### H.1 Source A: existing application assets that matter in this phase

| Asset | Decision | What happens in this phase | Evidence |
|---|---|---|---|
| `network-student.JPG`, `search-network.jpg` | Deprecate (usage) / Reuse (elsewhere) | Probable page heroes on `/student/friends` and `/student/friends/search`; replaced by the standard page header. | INFERRED |
| Student profile photographs in friend lists | Retain | Same rules as Phase 12. | UNKNOWN / BLOCKED |

### H.2 Source B: custom package assets available for this phase

**Original SVG, brand, fonts, icons and CSS (made for this project; in the package; licences in `ASSET_INVENTORY.md` section 2)**

- `empty-states/no-search-results.svg`, Lucide icons (user-plus, users).

### H.3 Reconciliation for this phase

Initials when there is no photo (guardrail 7).

Overlap comparisons that touch this phase are in `ASSET_INVENTORY.md` section 11; where a Source A asset and a Source B asset serve the same purpose, that table gives the starting recommendation and this phase re-checks it against the real page.

### H.4 Native UI (no asset; plain HTML/CSS)

- List.

### H.5 Do not touch in this phase

- Student profile photographs and uploads, institution/company logos, verification documents: never moved or re-encoded; never shown in a new public place (D-04, D-31).

## I. Dependencies

- **Phases:** hard — Phase 1 (Critical Fixes & Data Integrity), Phase 3 (Shared Shell & Components); soft — Phase 12 (Profile & Portfolio).
- **Decisions:** D-02 (Language policy and copy deck), D-03 (Single source of truth for a student's display name), D-04 (Student contact visibility policy), D-31 (Personal photographs, student photos and identifying file names). A gating decision that is still OPEN in `DECISIONS_AND_OPEN_QUESTIONS.md` means the dependent tasks stop and the agent asks the user; it does not guess.
- **Packages:** No new composer or npm package. Anything new needs explicit approval first.
- **Data / environment:** 700 students; connections in several states (seed if missing).

## J. Functionality preservation

The global rules in `CLAUDE.md` section 7 apply in full. For this phase specifically:

- Connection endpoints and verbs unchanged.
- Search parameters (`q`, `major`, `university_id`, `page`) unchanged.
- The redirect to the public profile remains.

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

- Search with each filter and with combined filters.
- Connect and observe the row state change.
- 768 px pagination.
- Name in the row equals the name on the profile.

*Test commands are not listed here because none could be verified without the repository. `CLAUDE.md` section 4 lists candidate commands that Stage One must confirm.*

## L. Acceptance criteria

The phase is COMPLETE only if every box can be checked with evidence in the phase report. Otherwise the status is PARTIAL (say which boxes failed) or BLOCKED (say what unblocks it).

- [ ] No hero image on either page.
- [ ] The connect action reads in Indonesian and works as before.
- [ ] No pagination overflow at 768 px.
- [ ] The result count is a labelled filtered total.
- [ ] Names and initials in the list equal the profile's.
- [ ] Both quick filters still work.

## M. Risks and blockers

| Risk or blocker | Mitigation / what unblocks it | Blocks the phase? |
|---|---|---|
| Connection states unknown (pending, accepted) | Stage One; design only states that exist. | No |

## N. Expected deliverables

- Rewritten friends and search views.
- Screenshots S12/S13.
- `student-go-docs/design-execution/PHASE_TRACKER.md`: set Phase 13 to COMPLETE, PARTIAL or BLOCKED with date, branch/commit (if the user asked for one) and a one-line note.
- A phase report in the chat: what changed (files), what was verified (with the evidence), what failed or was skipped and why, decisions needed from the user, and the exact next phase the roadmap allows. Then stop and wait; do not start another phase.
