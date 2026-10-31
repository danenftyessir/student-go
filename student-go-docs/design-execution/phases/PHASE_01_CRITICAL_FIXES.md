# PHASE 01 — Critical Fixes & Data Integrity

> Source of truth for status is `student-go-docs/design-execution/PHASE_TRACKER.md`. This file is the work order for exactly one phase. Read `CLAUDE.md` and `student-go-docs/design-execution/EXECUTION_ROADMAP.md` first, execute only this phase, update the tracker, report, then stop.
> Evidence labels: VERIFIED / OBSERVED / INFERRED / UNKNOWN / BLOCKED (definitions in `DISCOVERY_REPORT.md` section 1).

| Field | Value |
|---|---|
| Phase | 1 — Critical Fixes & Data Integrity |
| Group | Foundation |
| Hard dependencies (must be COMPLETE) | Phase 0 (Readiness & Baseline) |
| Soft dependencies (recommended first) | none |
| Decisions that gate this phase | D-03 Single source of truth for a student's display name, D-04 Student contact visibility policy, D-05 Stable ordering and count definitions, D-06 Enum alignment for institution type and company size, D-25 Defect fixes that change HTTP behaviour, D-31 Personal photographs, student photos and identifying file names |
| Effort estimate (planning guess, not a commitment) | L |
| Roles touched | Student (primary), Company, Institution, Public |
| Audit references | Backlog J1, J2, J3, J4 (P0); J5, J13 (P1); audit E, G, M; INTEGRATION.md 'P0 data/privacy issues not fixed here' |
| Tracker status at planning time | NOT_STARTED |

## A. Phase overview

**Goal.** Fix the defects that must not be hidden behind a new look: one person shown with two names, lists that repeat or hide records, contradictory counts, student contact data exposed to guests, edit forms that lose saved values, state-changing GETs and debug/500 pages. These are behaviour fixes, so each one is gated on a user decision and ships with a regression test.

**In scope**

- J1: one source of truth for a student's display name and initials, used by nav, dashboard, profile, public profile, edit form, friends and company talents; fix the seeder mismatch if the repository shows one.
- J2: deterministic ordering with an `id` tiebreaker on every paginated list (browse-problems, repository, friends search, company talents) so pages never repeat or hide records.
- J4: one count source per list and honest labels ('N dari M' for filtered totals); fix 221 vs 216, 77/73/62/'1 dari 77', '12 of 699', '0 perusahaan'.
- J3: contact data (email, WhatsApp) is not sent to guests on `/profile/{username}`, enforced on the server, per D-04.
- J5: edit forms bind stored values (student name, institution type, company email and size); align enum options with stored values per D-06.
- J13 (as approved in D-25): `GET /student/chatbot/create` no longer creates a record; `/student/repository/abc` returns 404; `/institution/reviews/create` no longer renders a QueryException; debug page not shown outside local development.

**Out of scope**

- Any visual design (later phases restyle the same screens).
- Data migrations that change stored values unless D-06 option (b) is explicitly approved.
- The cross-role silent redirect behaviour (kept as is unless D-25 says otherwise).
- Region placeholder data (D-15) and the problem gallery image storage (D-14); Phase 9 handles the UI fallback.

**Evidence basis.** The audit (OBSERVED, 2026-10-28, port 8001) plus a read-only look at the running app on 2026-10-30. No repository source was read when this file was written; every statement about templates, controllers, models, middleware and tests is UNKNOWN until Stage One (Phase 0) fills `DISCOVERY_REPORT.md` section 12.

## B. Pages and routes

No route is owned by this phase; it works on shared files and every route (see section A).

## C. Existing implementation

- [OBSERVED] Navigation and dashboard show 'Julia Suryanto'; profile and public profile show 'Desi Hartono' with initials 'DH'; the edit form's first/last name fields are empty while university, major, NIM, semester and WhatsApp load correctly (audit E, D8).
- [OBSERVED] 127 of 127 sampled public profiles show a name different from the account username (example: username silvihidayat1001 shows 'Wulan Nabila'); a company clicking talent 'Uli Hidayat' opens a profile titled 'Oki Nabila'.
- [OBSERVED] Browse-problems: 7 pages x 12, ID 47/49/50/51/61 repeat on pages 1-3, only 62 unique IDs reachable; 15 open problems never appear (31, 32, 36, 41, 46, 53, 55, 57, 64, 66, 67, 71, 72, 73, 74). Repository: 19 pages yield 209 unique of 216; header claims 221.
- [OBSERVED] Guests can read email and WhatsApp on `/profile/{username}`.
- [OBSERVED] `GET /student/chatbot/create` created two empty SAGO conversations; `/student/repository/abc` returns HTTP 500; `/institution/reviews/create` renders a full QueryException page.
- [VERIFIED 2026-10-30] Debug mode is on (stack traces rendered on `/institution/19`, `/register/student`, `/forgot-password`).
- [INFERRED] The name mismatch is a seeder issue (users.name vs first/last name), per audit G.

**Repository facts for this phase: UNKNOWN until Stage One.** Locate and record in `DISCOVERY_REPORT.md` section 12 before editing: models and migrations for users/students/institutions/companies; any `getNameAttribute`/accessor; the controllers and queries for browse-problems, repository, friends search and company talents (orderBy, paginate, count); the public profile controller and view; chatbot routes/controller; exception handler/bootstrap; config/app.php debug; where institution type and company size options are defined; existing feature tests.

## D. Problems found (audit findings)

| Backlog ID | Finding | Evidence |
|---|---|---|
| J1 (P0) | One person appears with two unrelated names and initials; 127/127 sampled profiles mismatch. | OBSERVED |
| J2 (P0) | Lists repeat and hide records: 15 of 77 problems and 7 of 216 documents unreachable. | OBSERVED |
| J3 (P0) | Email and WhatsApp of 700 students visible to guests. | OBSERVED |
| J4 (P0) | Contradictory totals: 77 / 73 / 62 / '1 dari 77'; 221 vs 216; '12 of 699'; '0 perusahaan' of 129. | OBSERVED |
| J5 (P1) | Edit forms drop saved values (student name; institution type; company email, size). | OBSERVED |
| J13 (P1) | State-changing GET, HTTP 500 on non-numeric ID, QueryException page, debug on. | OBSERVED; debug VERIFIED |
| J8 note | Counts must be formatted in Indonesian ('dari'), but copy changes belong to Phase 3 onward. | INFERRED |

## E. Approved design direction

**Approved by the user (binding)**

- Hybrid direction (AD-04): Ruang Kerja workbench foundation; Catatan Lapangan editorial layer only where reading and storytelling matter. This phase uses none of it unless stated below.
- Principles (AD-05) and functional preservation (AD-06). Tokens, fonts and components below come from the asset package and are PROPOSED until D-17 is approved; existing assets are reconciled under AD-09 (section H); D-16 records that reference documents B and C were not found.

**Proposed for this phase** (audit sections I and L, adapted; status PROPOSED until the user approves the gating decisions)

- Not a visual phase. Fixes are server-side first, with the smallest possible view change (swap the field that is read).
- Ordering: `ORDER BY <chosen primary key>, id` everywhere a list paginates. Counts come from the same query constraints as the list, via one method per list.
- Privacy: the contact block is not rendered, and the values are not present in the HTML for a guest. Hiding with CSS does not count.
- Defect fixes follow D-25 exactly: do not widen them.

**Guardrails that bind this phase** (audit section H, numbered as in `EXECUTION_ROADMAP.md`): 2, 10.

## F. Implementation tasks (do in order)

1.1. Gate check: confirm D-03, D-04, D-05, D-06 and D-25 have answers recorded in `DECISIONS_AND_OPEN_QUESTIONS.md`. For each unanswered decision, skip its tasks and report it; do not choose for the user.

1.2. Write failing tests first where a test framework exists: (a) paging through a list returns each record exactly once; (b) a guest response for `/profile/{username}` contains neither email nor WhatsApp; (c) displayed name equals the canonical name everywhere; (d) `GET /student/chatbot/create` creates no row; (e) `/student/repository/abc` returns 404. If no framework exists, write the same checks as a manual script in the phase report.

1.3. J1: add the single name accessor and initials helper chosen in D-03. Replace every read of the other name source (use the Stage One list of views). Fix the seeder/data mismatch if D-03 says to; never run a destructive reseed on a database that has real data without approval.

1.4. J5 (name): bind the edit-profile name fields to the stored value. Keep the field names.

1.5. J2: add the tiebreaker to browse-problems, repository, friends search and company talents queries. Verify by fetching every page of each list in the local database and comparing the set of IDs with the table's total.

1.6. J4: introduce one count source per list (total, filtered). Update the visible labels to the agreed form ('N dari M', 'Total X'). Repository header must equal the number of reachable records. Company talents must state the real pool size and the rule that narrows it (D-08).

1.7. J3: remove email and WhatsApp from the guest response of `/profile/{username}` and show the agreed visibility per D-04 for logged-in viewers. Check the company recruiter view separately.

1.8. J5 (enums): align institution type and company size options with stored values per D-06; if stored values need migrating, stop and ask. Bind company email.

1.9. J13 as approved: change `GET /student/chatbot/create` so a GET does not persist (for example render the new-chat screen and create on the first POST); add `whereNumber` (or equivalent) on ID routes so `abc` is 404; find and fix the cause of the `/institution/reviews/create` QueryException (missing route handler or wrong model call); ensure the debug page is off outside local development (config is environment-specific; document the setting, do not edit `.env`).

1.10. Run the project's test suite and the manual matrix; confirm no route was added or removed except what D-25 allows (diff the route snapshot against the Phase 0 baseline).

1.11. Update the tracker and report with a table: defect, fix, test, result, and anything deferred.

## G. Components

| Component | Source | Notes |
|---|---|---|
| None visual | n/a | Behaviour-only phase. Reuse existing markup; swap data bindings. |

## H. Assets (Source A existing `public/`, Source B custom package, Source C usage)

> Policy AD-09. Three sources, none disposable: **A** existing assets in `public/`, **B** the custom package `student-go-assets/`, **C** `resources/` (shows what is really used). Every asset has one decision: Retain, Reuse, Adapt, Replace or Deprecate. Originals are never deleted, overwritten, moved or renamed by this phase; copying anything into a production directory happens only if a task above says so. Full inventory, rights and integration map: `student-go-docs/design-execution/ASSET_INVENTORY.md`. Evidence labels as in `DISCOVERY_REPORT.md` section 1.

### H.1 Source A: existing application assets that matter in this phase

| Asset | Decision | What happens in this phase | Evidence |
|---|---|---|---|
| Student profile photographs and uploads (avatars) | Retain | The identity bug (J1) is about names and initials, not photos. Do not change how photos are stored or shown beyond what the fix needs. | VERIFIED |
| `profile_18223127.jpg`, `profile_13523124.jpeg` | Retain | The two broken Contact portraits are page content, repaired in Phase 5, not here. | VERIFIED |

### H.2 Source B: custom package assets available for this phase

None for this phase.

### H.3 Reconciliation for this phase

No asset is introduced in this phase. It repairs data and behaviour only.

Overlap comparisons that touch this phase are in `ASSET_INVENTORY.md` section 11; where a Source A asset and a Source B asset serve the same purpose, that table gives the starting recommendation and this phase re-checks it against the real page.

### H.4 Native UI (no asset; plain HTML/CSS)

- Existing markup.

### H.5 Do not touch in this phase

- Student profile photographs and uploads, institution/company logos, verification documents: never moved or re-encoded; never shown in a new public place (D-04, D-31).
- Every file under `public/`.

## I. Dependencies

- **Phases:** hard — Phase 0 (Readiness & Baseline); soft — none.
- **Decisions:** D-03 (Single source of truth for a student's display name), D-04 (Student contact visibility policy), D-05 (Stable ordering and count definitions), D-06 (Enum alignment for institution type and company size), D-25 (Defect fixes that change HTTP behaviour), D-31 (Personal photographs, student photos and identifying file names). A gating decision that is still OPEN in `DECISIONS_AND_OPEN_QUESTIONS.md` means the dependent tasks stop and the agent asks the user; it does not guess.
- **Packages:** None.
- **Data / environment:** Seeded database with students, problems, repository documents, institutions and companies (D-12, D-13). Real data is not modified except where a seeder fix is approved.

## J. Functionality preservation

The global rules in `CLAUDE.md` section 7 apply in full. For this phase specifically:

- Do not change the order in which routes are defined or any URL.
- Public profile URL stays `/profile/{username}`; `/student/friends/profile/{id}` still redirects to it.
- Pagination query parameters (`page`, `search`, `q`, `major`, `university_id`) keep working; existing links remain valid.
- Chatbot: creating a conversation and sending messages keep working from the UI.
- No schema or data change unless the user approved it for that item.

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

- For each paginated list: collect all IDs across all pages; assert uniqueness and equality with the total count (browse-problems: 77; repository: 216 on the audit dataset).
- Guest `curl`/fetch of three public profiles: response body contains no email address pattern and no phone number pattern of the student.
- Logged-in student, connected student, and company recruiter fetch the same profile: contact visibility matches D-04.
- Name consistency: for 10 sampled students, the name in nav, dashboard, profile, public profile, edit form and company talent list is identical.
- Non-numeric IDs on `/student/repository/{id}`, `/student/browse-problems/{id}`, `/institution/{id}` return 404, not 500.
- `GET /student/chatbot/create` leaves the conversation count unchanged.

*Test commands are not listed here because none could be verified without the repository. `CLAUDE.md` section 4 lists candidate commands that Stage One must confirm.*

## L. Acceptance criteria

The phase is COMPLETE only if every box can be checked with evidence in the phase report. Otherwise the status is PARTIAL (say which boxes failed) or BLOCKED (say what unblocks it).

- [ ] Every paginated list reachable by paging returns each record exactly once and the union equals the stated total (browse-problems 77/77 and repository 216/216 on the audit dataset).
- [ ] No list shows two different totals for the same set; filtered totals are labelled as filtered.
- [ ] A guest request for any `/profile/{username}` returns HTML with no student email or WhatsApp number (verified by response-body check, not by looking at the page).
- [ ] A student has exactly one display name and one set of initials across nav, dashboard, profile, public profile, edit form, friends list and company talents (sample of at least 10 students).
- [ ] The student edit form loads first and last name (or the field D-03 defines) with the stored value.
- [ ] Institution type and company size selects show the stored value as selected; company email is populated.
- [ ] `GET /student/chatbot/create` creates no record; `/student/repository/abc` returns 404; `/institution/reviews/create` returns neither a QueryException nor a 500.
- [ ] With the app configured for non-local use, an exception shows a branded or minimal error without stack trace or connection details (full branded page is Phase 26).
- [ ] Route snapshot diff against Phase 0 shows only the changes approved in D-25.
- [ ] Project tests pass; new regression tests exist for J1, J2, J3, J13.

## M. Risks and blockers

| Risk or blocker | Mitigation / what unblocks it | Blocks the phase? |
|---|---|---|
| D-03/D-04/D-06 unanswered | Skip dependent tasks; report; the phase can end PARTIAL. | Yes for those tasks |
| Seeder fix is destructive on a database with real data | Never reseed without explicit approval; prefer a data-fix script reviewed by the user. | Possibly |
| Changing GET create to POST breaks existing links | Use the 'render screen, create on first POST' option; update every link found in Stage One. | No |
| A sort key hides new records in unexpected positions | Agree the primary sort per list (D-05) before coding. | No |
| Legal exposure from contact data | Treat J3 as a blocker for any public launch; legal review recommended. | No (but launch-blocking) |

## N. Expected deliverables

- Code changes limited to accessors, queries, route constraints, controller logic and the minimal view bindings.
- Regression tests (or the manual script) for J1-J5 and J13.
- A short defect table in the phase report with before/after evidence (IDs reached, response-body checks).
- `student-go-docs/design-execution/PHASE_TRACKER.md`: set Phase 1 to COMPLETE, PARTIAL or BLOCKED with date, branch/commit (if the user asked for one) and a one-line note.
- A phase report in the chat: what changed (files), what was verified (with the evidence), what failed or was skipped and why, decisions needed from the user, and the exact next phase the roadmap allows. Then stop and wait; do not start another phase.
