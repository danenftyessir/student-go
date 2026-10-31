# PHASE 16 — KARA Chatbot & Notifications

> Source of truth for status is `student-go-docs/design-execution/PHASE_TRACKER.md`. This file is the work order for exactly one phase. Read `CLAUDE.md` and `student-go-docs/design-execution/EXECUTION_ROADMAP.md` first, execute only this phase, update the tracker, report, then stop.
> Evidence labels: VERIFIED / OBSERVED / INFERRED / UNKNOWN / BLOCKED (definitions in `DISCOVERY_REPORT.md` section 1).

| Field | Value |
|---|---|
| Phase | 16 — KARA Chatbot & Notifications |
| Group | Student role |
| Hard dependencies (must be COMPLETE) | Phase 1 (Critical Fixes & Data Integrity), Phase 3 (Shared Shell & Components) |
| Soft dependencies (recommended first) | none |
| Decisions that gate this phase | D-01 Product name, wordmark and assistant name, D-25 Defect fixes that change HTTP behaviour |
| Effort estimate (planning guess, not a commitment) | M |
| Roles touched | Student (chatbot); all roles (notifications) |
| Audit references | S20, S21, S22, I10, C09; J11, J13, J26, J37, J38; audit D9 |
| Tracker status at planning time | NOT_STARTED |

## A. Phase overview

**Goal.** Standard two-column chat for KARA with a real h1, a send icon that does not look like a warning, and a notifications inbox with icons by type instead of emoji and labelled filters; the same notification page serves all three roles.

**In scope**

- `/student/chatbot`, `/student/chatbot/create` (behaviour fixed in Phase 1), `/student/chatbot/{id}`; `/notifications` for student, institution and company.

**Out of scope**

- The bell dropdown (Phase 3); chatbot AI behaviour and prompts.

**Evidence basis.** The audit (OBSERVED, 2026-10-28, port 8001) plus a read-only look at the running app on 2026-10-30. No repository source was read when this file was written; every statement about templates, controllers, models, middleware and tests is UNKNOWN until Stage One (Phase 0) fills `DISCOVERY_REPORT.md` section 12.

## B. Pages and routes

| Route pattern | Role | Audit status | Evidence | Notes |
|---|---|---|---|---|
| /notifications | All roles | Inspected | OBSERVED | Same page for 3 roles (S22, I10, C09). Emoji in titles; filter selects unlabelled. |
| /student/chatbot | Student | Inspected | OBSERVED | KARA conversation list. |
| /student/chatbot/create | Student | Fetched | OBSERVED | GET creates a record (J13). Do NOT open in discovery or tests without approval. Behaviour change in Phase 1 (D-25). |
| /student/chatbot/{id} | Student | 1, 2 (created by audit); 3 redirects | OBSERVED | No h1 (J11). |

**Audit screenshot IDs for this phase** (audit section L; OBSERVED). Page data below is quoted or summarised from the audit; the screenshots themselves are in the PDF, not in this repository.

| ID | Page | URL | Keep (audit) | Design direction (audit) | Backlog |
|---|---|---|---|---|---|
| S20 | KARA — daftar percakapan | /student/chatbot | Konsep percakapan tersimpan. | Tata letak chat dua kolom: riwayat di kiri, percakapan di kanan, contoh pertanyaan saat kosong. | J6, J8, J13 (GET create) |
| S21 | KARA — percakapan | /student/chatbot/1 | Header panel dengan tombol kembali, arsip, hapus; penghitung karakter. | Layout chat standar dengan riwayat di sidebar. | J11, J37 |
| S22 | Notifikasi (mahasiswa) | /notifications | Filter status/tipe dan aksi tandai/hapus. | Pusat notifikasi bergaya inbox; dropdown lonceng di navigasi menampilkan 5 terbaru. | J11, J26, J38 |
| I10 | Notifikasi (institusi) | /notifications | Tombol “Tandai Semua Dibaca”. | Sama dengan S22. | J26, J38 |
| C09 | Notifikasi (perusahaan) | /notifications | Empty state. | Sama dengan S22. | J9 |

## C. Existing implementation

- [OBSERVED] Chat list is clean but `GET /student/chatbot/create` creates empty conversations (2 created by the audit); the conversation page has no h1; header has back, archive and delete buttons and a character counter; the send icon resembles a warning mark (J37).
- [OBSERVED] Notifications: emoji in titles ('Laporan Disetujui' with a check emoji, 'Masalah Baru Dipublikasikan' with a megaphone), three stat tiles over a three-item list, a pink 'Hapus yang Sudah Dibaca' button, an unlabelled filter select; opening the page does not mark items as read.
- [OBSERVED] Seed notification content (milestone, weekly report approved, project started) does not match account state (J38).

**Repository facts for this phase: UNKNOWN until Stage One.** Locate and record in `DISCOVERY_REPORT.md` section 12 before editing: chatbot controller and model (create/store/archive/delete), the KARA message transport, notifications controller, notification types and payloads, the bell API.

## D. Problems found (audit findings)

| Backlog ID | Finding | Evidence |
|---|---|---|
| J13 (P1) | State-changing GET create. | OBSERVED |
| J11 (P1) | No h1; unlabelled filter selects. | OBSERVED |
| J26 (P3) | Emoji icons in titles. | OBSERVED |
| J37 (P3) | Send icon ambiguous. | OBSERVED |
| J38 (P2) | Seeds inconsistent with state. | OBSERVED |
| J6 (P1) | Photo hero on the chat list. | OBSERVED |

## E. Approved design direction

**Approved by the user (binding)**

- Hybrid direction (AD-04): Ruang Kerja workbench foundation; Catatan Lapangan editorial layer only where reading and storytelling matter. This phase uses none of it unless stated below.
- Principles (AD-05) and functional preservation (AD-06). Tokens, fonts and components below come from the asset package and are PROPOSED until D-17 is approved; existing assets are reconciled under AD-09 (section H); D-16 records that reference documents B and C were not found.

**Proposed for this phase** (audit sections I and L, adapted; status PROPOSED until the user approves the gating decisions)

- Chat: history list left, conversation right, example questions in the empty state, a real h1 ('KARA' plus conversation title), send icon from the shared set. Notifications: inbox with type icons, grouped by date, filters with visible labels (status, type), 'Tandai semua dibaca' as secondary action and 'Hapus yang sudah dibaca' as outline-danger with confirmation.

**Guardrails that bind this phase** (audit section H, numbered as in `EXECUTION_ROADMAP.md`): 1, 2, 3, 6, 7, 8, 9.

## F. Implementation tasks (do in order)

16.1. Gate check: Phase 1 COMPLETE (chatbot create fix); D-01 for the assistant name.

16.2. Chat list: two-column layout; new-chat action follows the Phase 1 behaviour (no GET mutation).

16.3. Conversation: add the h1; bubbles as plain bordered blocks; message counter neutral; send icon from the sprite; archive/delete with the shared confirm dialog; keep existing endpoints.

16.4. Notifications page: type-to-icon mapping (only types that exist; Stage One lists them); remove emoji from stored titles at render time if the DB holds them (do not edit stored data); labelled filters; inbox rows with read/unread state.

16.5. Keep the bell dropdown behaviour (Phase 3 owns it).

16.6. Verify for all three roles using I10 and C09 as references; empty states per role.

16.7. Update the tracker and report with S20-S22, I10, C09 before/after.

## G. Components

| Component | Source | Notes |
|---|---|---|
| Chat layout | page-specific | Two columns. |
| Message bubble | page-specific | No shadows. |
| Inbox row with type icon | Phase 3 list | — |
| Labelled select | Phase 3 | — |
| Confirm dialog | Phase 3 | — |
| Empty states | `no-notifications.svg` | — |

## H. Assets (Source A existing `public/`, Source B custom package, Source C usage)

> Policy AD-09. Three sources, none disposable: **A** existing assets in `public/`, **B** the custom package `student-go-assets/`, **C** `resources/` (shows what is really used). Every asset has one decision: Retain, Reuse, Adapt, Replace or Deprecate. Originals are never deleted, overwritten, moved or renamed by this phase; copying anything into a production directory happens only if a task above says so. Full inventory, rights and integration map: `student-go-docs/design-execution/ASSET_INVENTORY.md`. Evidence labels as in `DISCOVERY_REPORT.md` section 1.

### H.1 Source A: existing application assets that matter in this phase

| Asset | Decision | What happens in this phase | Evidence |
|---|---|---|---|
| `KARA-background.png` | Replace (usage) / Retain (file) | Drop the gradient background; use a flat surface or `contour-field.svg`. File stays until D-01 settles the assistant's name and look. | INFERRED |
| Notification content and avatars | Retain | Unchanged. | OBSERVED |

### H.2 Source B: custom package assets available for this phase

**Original SVG, brand, fonts, icons and CSS (made for this project; in the package; licences in `ASSET_INVENTORY.md` section 2)**

- `empty-states/no-notifications.svg`; Lucide icons (message-square, send, bell, check, megaphone).

### H.3 Reconciliation for this phase

Overlap 6: B or flat.

Overlap comparisons that touch this phase are in `ASSET_INVENTORY.md` section 11; where a Source A asset and a Source B asset serve the same purpose, that table gives the starting recommendation and this phase re-checks it against the real page.

### H.4 Native UI (no asset; plain HTML/CSS)

- Chat bubbles.

### H.5 Do not touch in this phase

- Student profile photographs and uploads, institution/company logos, verification documents: never moved or re-encoded; never shown in a new public place (D-04, D-31).

## I. Dependencies

- **Phases:** hard — Phase 1 (Critical Fixes & Data Integrity), Phase 3 (Shared Shell & Components); soft — none.
- **Decisions:** D-01 (Product name, wordmark and assistant name), D-25 (Defect fixes that change HTTP behaviour). A gating decision that is still OPEN in `DECISIONS_AND_OPEN_QUESTIONS.md` means the dependent tasks stop and the agent asks the user; it does not guess.
- **Packages:** No new composer or npm package. Anything new needs explicit approval first.
- **Data / environment:** Notification records of several types; chat conversations (create them only through the normal UI on the local DB).

## J. Functionality preservation

The global rules in `CLAUDE.md` section 7 apply in full. For this phase specifically:

- Chat send, archive and delete endpoints unchanged; conversation ownership unchanged.
- Unknown conversation id still redirects to the list with a 'not found' message.
- Notification read/delete endpoints unchanged; 'opening the page does not mark as read' stays as is unless the user decides otherwise.

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

- Send a message in a local conversation; archive; delete.
- Confirm no conversation is created by simply navigating.
- Filters by status and type; delete read ones with confirmation.
- Screen reader: filter labels, h1.

*Test commands are not listed here because none could be verified without the repository. `CLAUDE.md` section 4 lists candidate commands that Stage One must confirm.*

## L. Acceptance criteria

The phase is COMPLETE only if every box can be checked with evidence in the phase report. Otherwise the status is PARTIAL (say which boxes failed) or BLOCKED (say what unblocks it).

- [ ] The conversation page has exactly one h1.
- [ ] No emoji is used as an icon in any notification title.
- [ ] Notification filters have visible labels connected to controls.
- [ ] The send icon is a standard send glyph.
- [ ] No GET request creates a conversation.
- [ ] The three roles' notification pages use one template.
- [ ] No hero photos on these pages.

## M. Risks and blockers

| Risk or blocker | Mitigation / what unblocks it | Blocks the phase? |
|---|---|---|
| Phase 1 not done | Hard dependency. | Yes |
| Notification types unknown | Stage One lists payload types. | No |

## N. Expected deliverables

- Rewritten chatbot and notifications views.
- Screenshots S20-S22, I10, C09.
- `student-go-docs/design-execution/PHASE_TRACKER.md`: set Phase 16 to COMPLETE, PARTIAL or BLOCKED with date, branch/commit (if the user asked for one) and a one-line note.
- A phase report in the chat: what changed (files), what was verified (with the evidence), what failed or was skipped and why, decisions needed from the user, and the exact next phase the roadmap allows. Then stop and wait; do not start another phase.
