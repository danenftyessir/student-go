# DECISIONS AND OPEN QUESTIONS

Written 2026-10-30. This file keeps three things apart: **approved decisions** (the user said so), **open decisions with recommendations** (nobody approved them), and **blockers/unknowns**. A recommendation is not an approval: an agent must not act on one until the user records an answer here.

## Part 1. Approved decisions

| ID | Topic | Decision | Source |
|---|---|---|---|
| AD-01 | Mode | This task is exploration and planning only. The only files created are the ones listed in the brief (CLAUDE.md, student-go-docs/design-execution/*, 29 phase files). No application change, no phase executed. | User brief, current task |
| AD-02 | Agent instruction file | Claude Code is the coding agent, so the instruction file is CLAUDE.md. No AGENTS.md is created. | User answer to AskUserQuestion |
| AD-03 | Draft basis | Docs are drafted now from the audit PDF plus a read-only look at the running app, without repository access. Every source-code fact is therefore marked UNKNOWN/BLOCKED, CLAUDE.md is PROVISIONAL, and a repo-connected agent must run Stage One (repository verification, inside PHASE_00) before any other phase. | User answer to AskUserQuestion |
| AD-04 | Design direction | Hybrid: 'Ruang Kerja' (workbench) is the operational foundation for every logged-in page; 'Catatan Lapangan' (field notes) is a selective editorial/documentary layer used only where reading and storytelling matter (homepage, about, project brief, repository document, student story/portfolio). | User brief, current task |
| AD-05 | Design principles | Custom identity; neutral colour with one brand colour; one consistent type system; original SVG for identity graphics; every NEW asset is open-source or license-checked; existing application assets are reconciled under AD-09, not discarded; anti-AI-slop guardrails (audit section H). | User brief, current task; amended by AD-09 |
| AD-06 | Functional preservation | Routes, authentication, database, and business logic are preserved. The design is presentation unless a defect fix is explicitly approved (see D-25). | User brief (current task and the earlier design brief) |
| AD-07 | Execution model | 29 phases (0-28). One phase per request. The agent reads the roadmap, the phase doc and the tracker, executes only the requested phase, tests, updates the tracker, reports, and stops. Multiple phases are never auto-run. | User brief, current task |
| AD-08 | Interactive map stays | The Leaflet interactive map (homepage coverage map and the project map view) is kept. Carried forward from the earlier brief as summarised at session handoff; not re-verified against the original wording. | Earlier brief (carried forward) |
| AD-09 | Asset discovery, reconciliation and reuse policy | Three asset sources are all in scope and none is disposable: (A) existing application assets in `public/`, (B) the custom package `student-go-assets/`, (C) `resources/` (the Blade, CSS and JS that show which assets are really used). Every asset gets one decision: Retain, Reuse, Adapt, Replace or Deprecate. Genuine student photographs, functional feature assets and certificate resources (templates, Pinyon Script, Poppins) are preserved; certificate typography is separate from website typography. Replace only what conflicts with the approved design or has a technical problem. Originals are kept until a later phase explicitly authorises integration or replacement. Assets are never deleted, overwritten, moved or renamed during discovery. The full inventory is `student-go-docs/design-execution/ASSET_INVENTORY.md`. | User message 2026-10-30 (ASSET DISCOVERY, RECONCILIATION & REUSE POLICY) |
| AD-11 | Homepage hero with a looping background video | The homepage (`/`) opens with a full-window, muted, looping background video (`public/video/hero-loop-{desktop,mobile}.{webm,mp4}`, 12 s, no audio; poster `.webp`/`.jpg` as the first image), one scrim for legibility, white h1 + one-sentence lead + Daftar/Masuk at the lower left, **no pause/play button** (removed in a parallel session; the user closed the phase with it removed: a known deviation from WCAG 2.2.2, see the Phase 4 report), and a header that is transparent over the hero and solid after it (homepage only). The video loads only from script, never under `prefers-reduced-motion` or Save-Data, and pauses off-screen or in a hidden tab. Below 640 px the page shows the first 3 problem rows, the first 4 SDG bars and one field photograph (the rest are one link away). The video is original work made for this project (user, 2026-10-31; `student-go-docs/design-system/VIDEO_SOURCES.md`). This amends Phase 4 acceptance criteria 4 (a background video is allowed in this hero; h1 and primary CTA must be inside the first viewport at 390 px instead of the first 160 px) and 9 (page weight is measured without the video file, which is an opt-out enhancement). Applies to the homepage only; guardrail 1 (no hero on task pages) is unchanged. | User, 2026-10-31: added in a parallel Phase 4 session, confirmed ('Pertahankan video layar penuh'; video 'Dibuat untuk proyek ini') in the Phase 4 closing session |

## Part 2. Open decisions (need the user)

Every row was OPEN at planning time; Phase 0 (2026-10-30) recorded answers for D-12, D-22, D-27, D-28 and D-32 (see each block). Each lists the phases it gates. To answer one, add a line under it: `ANSWER (date): option, notes`, and tell the agent. Where a recommendation is given it is the planner's suggestion only.

### D-01 — Product name, wordmark and assistant name

- **Status:** ANSWERED (2026-10-30)
- **Question:** Which name does the product carry in every user-facing place: logo, <title>, APP_NAME, footer, emails, and the KARA assistant?
- **Why it matters (evidence):** The app shows three names at once: 'Laravel' in tab titles (VERIFIED), 'Karsa' in the logo, auth titles and footer copy (VERIFIED on /login, /register, /about), and 'KKN Go' (OBSERVED). The brief and the asset package use 'Student Go'.
- **Options:**
  - (a) Student Go everywhere; keep the assistant name KARA.
  - (b) Student Go everywhere; rename the assistant.
  - (c) Keep Karsa as the product name.
- **Recommendation (not approved):** (a), pending the user's confirmation that KARA stays as the assistant name.
- **Gates phases:** 2, 3, 4, 5, 6, 16, 26
- **Source:** Audit J20, K; live titles
- ANSWER (2026-10-30): option (b) — **Student Go** everywhere; the assistant gets a **new name** (not yet chosen; decided in Phase 16). Phase 2 only integrated the Student Go brand files; titles, `APP_NAME` and the layout logo change in Phase 3 (shell).

### D-02 — Language policy and copy deck

- **Status:** ANSWERED (2026-10-30)
- **Question:** Is the UI Indonesian-only, or Indonesian-first with an English toggle? Which exact words are used for statuses, roles, navigation and common actions?
- **Why it matters (evidence):** Mixed English/Indonesian appears on the same screens ('Rejected' vs 'Menunggu Review', 'Talents / Jobs / Applications', 'Showing', 'Add Connection').
- **Options:**
  - (a) Indonesian only at launch, no toggle (recommended).
  - (b) Indonesian-first with an English toggle (needs a translation mechanism: UNKNOWN whether one exists).
  - (c) Keep mixed.
- **Recommendation (not approved):** (a). Application statuses: Menunggu, Ditinjau, Diterima, Ditolak, Dibatalkan (audit I). Problem statuses proposed: Draf, Dibuka, Berjalan, Selesai. Navigation: Lowongan, Talenta, Lamaran, Talenta Tersimpan. The user must approve the final copy deck before Phase 3.
- **Gates phases:** 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26
- **Source:** Audit J8, K
- ANSWER (2026-10-30, Phase 3): option (a) — **Indonesian only**, no toggle. Copy deck approved as proposed by the Phase 3 session:
  - Navigation. Public: Tentang Kami · Kontak · Masuk · Daftar. Student: Dashboard · Telusuri Masalah · Aplikasi Saya · Proyek Saya · Repositori · Lowongan. Institution: Dashboard · Masalah · Aplikasi · Proyek · Ulasan. Company: Dashboard · Talenta · Lowongan · Lamaran · Talenta Tersimpan. Account menu: Profil · Wishlist (student) · Jaringan (student) · Notifikasi · Keluar. Role badge: Mahasiswa · Instansi · Perusahaan.
  - Status words (stored value → label), implemented once in `app/Support/StatusVocabulary.php`: applications pending Menunggu, reviewed Ditinjau, accepted Diterima, rejected Ditolak; job applications add shortlisted Terpilih; problems draft Draf, open Dibuka, in_progress Berjalan, completed Selesai, closed Ditutup; projects active Berjalan, on_hold Ditunda, completed Selesai, cancelled Dibatalkan; milestones pending Menunggu, in_progress Berjalan, completed Selesai, delayed Terlambat; project reports pending Menunggu, reviewed Ditinjau, approved Disetujui, revision_needed Perlu Revisi; documents pending Menunggu, approved Disetujui, rejected Ditolak; verification pending_verification Menunggu Verifikasi, verified Terverifikasi, rejected Ditolak, needs_review Perlu Ditinjau; job postings draft Draf, posted Dibuka, closed Ditutup, archived Diarsipkan; friends pending Menunggu, accepted Terhubung, rejected Ditolak, blocked Diblokir.

### D-03 — Single source of truth for a student's display name

- **Status:** ANSWERED (2026-10-30)
- **Question:** Which field(s) define a student's display name and initials everywhere (nav, dashboard, profile, public profile, edit form, company talent list)?
- **Why it matters (evidence):** 127 of 127 sampled public profiles show a different name from the account that links to them; the logged-in account shows 'Julia Suryanto' in the nav and 'Desi Hartono' on the profile; the edit form loads first/last name empty (OBSERVED, audit E, J1).
- **Options:**
  - (a) users.name is canonical; profile names derive from it.
  - (b) first_name + last_name on the student profile are canonical; users.name derives from them.
  - (c) Keep both but show one consistently and fix the seeder.
- **Recommendation (not approved):** Decide after Stage One shows the real schema. Do not choose before the data model is read. Whatever is chosen, one accessor produces name and initials.
- **Gates phases:** 1, 7, 12, 13, 22
- **Source:** Audit J1, K, G (seeder mismatch INFERRED)
- ANSWER (2026-10-30): option (b) — `students.first_name` + `last_name` are canonical. One accessor (`Student::display_name`, `Student::initials`; `User::display_name`/`initials` delegate for students) feeds every view. Seeder fixed to use one random name per person. The 700 seeded `users.name` values are synced from `students` once (non-destructive UPDATE, CSV backup first). Root cause (Phase 1): `DummyDataSeeder::seedStudents()` picks the name twice independently (lines 348-349 vs 376-377); register and profile update always keep both in sync.

### D-04 — Student contact visibility policy

- **Status:** ANSWERED (2026-10-30)
- **Question:** Who may see a student's email and WhatsApp number?
- **Why it matters (evidence):** A guest (not logged in) can read both on /profile/{username} (OBSERVED, J3). 700 students affected. This is a privacy exposure, not a styling issue.
- **Options:**
  - (a) Private by default; visible to accepted connections.
  - (b) (a) plus company recruiters after the student consents.
  - (c) Student chooses per field (opt-in public).
- **Recommendation (not approved):** (b) or (c). Legal review against Indonesian personal-data protection rules is recommended before launch; Claude is not giving legal advice.
- **Gates phases:** 1, 12, 13, 22
- **Source:** Audit J3, K
- ANSWER (2026-10-30): option (a) — email and WhatsApp are visible only to the student themself and to accepted connections (`friends.status = accepted`, either direction). Guests, other students, institutions and companies receive no contact value in the HTML. Enforced server-side in Phase 1.

### D-05 — Stable ordering and count definitions

- **Status:** ANSWERED (2026-10-30)
- **Question:** What is each list's default sort, and what does each count mean (total, published, filtered)?
- **Why it matters (evidence):** Browse-problems repeats cards across pages (only 62 of 77 reachable), repository hides 7 of 216 documents, and counts contradict each other (77 / 73 / 62 / '1 of 77'; 221 vs 216; '12 of 699'; '0 of 129') (OBSERVED, E, J2, J4).
- **Options:**
  - (a) Deterministic ORDER BY <chosen key>, id as tiebreaker on every paginated list; one count query per list; label filtered totals as 'N dari M'.
- **Recommendation (not approved):** (a). The chosen primary sort per list (newest, deadline, relevance) needs the user's call.
- **Gates phases:** 1, 8, 14, 15, 22
- **Source:** Audit J2, J4, K
- ANSWER (2026-10-30): option (a). Existing primary sorts and sort options are kept; `id` is added as the tie-breaker on every paginated list (same direction as the primary key); friends search (no order before) sorts by first name, last name, then id; company talents by newest then id, labelled with the real pool ('mahasiswa dengan minimal 1 proyek'). Filtered totals are labelled 'N dari M'. One count source per list.

### D-06 — Enum alignment for institution type and company size

- **Status:** ANSWERED (2026-10-30)
- **Question:** Do the form options change, or does stored data get migrated?
- **Why it matters (evidence):** Institution edit shows 'Pilih Jenis' because stored 'Dinas Kesehatan' is not among the options (pemerintah_desa, dinas, ngo, puskesmas, sekolah, perguruan_tinggi, lainnya); company edit loses email and company size (OBSERVED, J5).
- **Options:**
  - (a) Expand/normalise the option list to match stored values.
  - (b) Data migration to the option list (needs explicit approval; DB change).
  - (c) Both, staged.
- **Recommendation (not approved):** (a) first, (b) only with approval. Decide after Stage One shows real stored values.
- **Gates phases:** 1, 20, 25
- **Source:** Audit J5, K
- ANSWER (2026-10-30): option (a) plus an additive schema fix. Institution type: a stored value that is not one of the 7 options is shown as an extra, selected option (no data change; validation is already `required|string`). Company: one ADDITIVE migration adds the 9 nullable columns the edit form already reads and writes (`email`, `company_size`, `tagline`, `location`, `postal_code`, `linkedin`, `twitter`, `facebook`, `instagram`); the size select falls back to `employee_count` and shows a stored value that is not an option. No stored value is changed. Reason: the edit form posts those 9 fields and every save failed with SQLSTATE 42703 (verified 2026-10-30).

### D-07 — Product scope: KKN-only or jobs/internships as a main flow

- **Status:** OPEN
- **Question:** Do jobs and internships become a main student flow, or stay secondary?
- **Why it matters (evidence):** Jobs, saved jobs and job alerts are empty (0 openings, 0 companies; OBSERVED) and the company role depends on them.
- **Options:**
  - (a) KKN is primary; jobs stay visible but secondary.
  - (b) Jobs become a first-class flow.
- **Recommendation (not approved):** (a) until real job data exists. Affects ordering of phases 15, 21-25.
- **Gates phases:** 15, 21, 22, 23, 24
- **Source:** Audit K open questions

### D-08 — Should companies see all students or only opted-in ones?

- **Status:** OPEN
- **Question:** What is the talent pool a company can search?
- **Why it matters (evidence):** /company/talents shows 12 of 699 students (OBSERVED). Unknown whether that is intended filtering.
- **Options:**
  - (a) Opt-in only (recommended).
  - (b) All students.
- **Recommendation (not approved):** (a). Ties into D-04.
- **Gates phases:** 22
- **Source:** Audit K, J22

### D-09 — Verification process for institutions and companies

- **Status:** OPEN
- **Question:** Who verifies, and what does the UI promise?
- **Why it matters (evidence):** Institution pages say verification is automatic using AI and show 'Menunggu Verifikasi'; an admin or automatic review flow is INFERRED but not visible (audit F, G).
- **Options:**
  - (a) Admin review.
  - (b) Automatic check.
  - (c) Hybrid.
- **Recommendation (not approved):** No recommendation without product input. UI copy for Phase 20 must not promise more than the real process does.
- **Gates phases:** 20
- **Source:** Audit F, K
- PARTIAL ANSWER (2026-10-31, Phase 17 session, **institution dashboard only**): the dashboard banner shows the real `verification_status` only (shared vocabulary) and promises neither a method ('otomatis menggunakan AI' removed) nor a duration ('1-2 hari kerja' removed). Who verifies (a/b/c) stays OPEN for Phase 20. Fact found in source: for institutions the verified value is `active` (`Institution::STATUS_ACTIVE`, set by `AIDocumentVerificationService`); `verified` is the companies' enum value. `StatusVocabulary` has no `active` entry for the `verification` domain yet (Phase 20).

### D-10 — Which statistics help each role decide?

- **Status:** OPEN
- **Question:** Which numbers appear on each dashboard?
- **Why it matters (evidence):** Dashboards show all-zero tiles, duplicated stats and trend chips for new accounts; guardrail 2 says every number must be real and useful.
- **Options:**
  - Per-role list to be approved by the user.
- **Recommendation (not approved):** Start from the audit's compositions (student: next steps; institution: verification, pending applicants, running projects; company: active jobs, new applicants).
- **Gates phases:** 7, 17, 21
- **Source:** Audit H guardrail 2, K
- ANSWER (2026-10-31, Phase 7 session, **student dashboard only**): the work-queue composition. Numbers shown, each once and each from a query `Student\DashboardController` already runs: pending applications (one queue line), profile completeness (%), progress of each active project (% from `projects.progress_percentage`), and one network line 'x koneksi · y permintaan · z saran' (requests and suggestions hidden when 0; suggestions capped by the existing `suggestedFriends(10)` query, so 10 is shown as '10+'). The 'Statistik Aktivitas' tiles and bars (total applications, active/completed projects, connections) are removed. Institution and company dashboards (Phases 17, 21) are not answered here.
- ANSWER (2026-10-31, Phase 17 session, **institution dashboard only**): the work queue — verification status (first block while not verified), applicants awaiting a decision (`pending` + `reviewed`), running projects (next milestone, report due from a milestone marked 'wajib laporan', reports awaiting review), completed projects awaiting a review (real total), draft problems. One summary line with the non-zero queue counts only; no stat tiles, no trend chip, no acceptance rate, no views total.
- NOTE (2026-10-31, Phase 19): the existing seed data covers applications in four statuses (institution #33: 4 pending, 1 reviewed, 12 accepted, 2 rejected), active and completed projects with milestones and reports, and 195 institution reviews. No seeder was run. Not in the data, so BLOCKED for live checks and covered by feature tests only: an uploaded proposal (0 rows have `proposal_content`), a completed project without a review (the review form cannot be opened live), and the `on_hold`/`cancelled` project statuses. Accept and reject were tested live on throwaway rows (one problem and two applications for institution #64), which were deleted afterwards.

### D-11 — Admin area scope

- **Status:** OPEN
- **Question:** Is the admin UI part of this design?
- **Why it matters (evidence):** /admin/dashboard exists (403 for non-admin; OBSERVED). No credentials, no other admin route found. The 29-phase list has no admin phase.
- **Options:**
  - (a) Out of scope; Phase 26 covers only the 403 page (recommended).
  - (b) Add an admin phase after seed credentials and a route list exist.
- **Recommendation (not approved):** (a).
- **Gates phases:** 26
- **Source:** Audit F, G, K

### D-12 — A working database for baseline and QA

- **Status:** ANSWERED and UNBLOCKED (2026-10-30)
- **Question:** Which database do baseline screenshots, tests and every data-driven phase run against?
- **Why it matters (evidence):** On 2026-10-30 the app on 127.0.0.1:8000 cannot reach its PostgreSQL (Supabase pooler) database: /institution/19 and /register/student return Laravel debug pages with a QueryException (SQLSTATE 08006, 'tenant/user not found'). Static pages still render. The audit's 28 Oct run against port 8001 had data (VERIFIED live / OBSERVED audit).
- **Options:**
  - (a) Restore the remote DB credentials/project.
  - (b) Local PostgreSQL seeded from the project's seeders.
  - (c) SQLite for tests only plus (a) or (b) for visual QA.
- **Recommendation (not approved):** (b) or (a). Credentials must stay in .env and never enter any doc. Changing .env or the database needs the user's explicit approval.
- **Gates phases:** 0, 1, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 27, 28
- **Source:** Live check 2026-10-30
- ANSWER (2026-10-30): option (a) — the user restores the remote Supabase database. Status after Phase 0: still unreachable. The user supplied the URL and REST keys of a **new** Supabase project; a read-only REST probe shows it has **no application tables** (`PGRST205`), every `DB_*`/`SUPABASE_*` value in `.env` points to the old project, and no Postgres password was supplied. `.env` was **not** changed. To unblock: put the new project's Postgres connection (pooler host, port, `postgres.<project-ref>` user, password) in `.env`, then explicitly approve `php artisan migrate` and seeding of that empty database (seeders call the BPS API; `ProblemImagesSeeder` points at the old bucket). Recommendation: rotate the service key that was pasted into the chat.
- UPDATE (2026-10-30, later): the user supplied the new project's Postgres connection and approved migrate + seed. `.env` updated (backup `.env.backup`), `php artisan migrate` 60/60, seeding done. The BPS region path failed (duplicate keys), so the seeder's built-in fallback was used (5 provinces, 22 regencies); see `baseline/TESTS_AND_TOOLING.md` section 4. All DB-backed pages render. The database password was also pasted into the chat: rotate it together with the service key.

### D-13 — Seed data for blocked screens

- **Status:** OPEN (partly reduced by Phase 0)
- **Question:** Who seeds an institution and a company with problems, jobs, applicants in every status, active projects and reviews, plus an admin account and consistent notifications?
- **Why it matters (evidence):** Record-level institution/company pages and several role screens were never seen with data (BLOCKED in the audit). Notification seeds contradict account state (J38).
- **Options:**
  - (a) Add seeders in a dedicated phase-0 task (needs approval).
  - (b) User provides a database dump.
- **Recommendation (not approved):** (a) or (b), before phases 11, 17-25.
- NOTE (Phase 0, 2026-10-30): the standard seeders now provide problems, applications in four statuses, active and completed projects, milestones, reports, reviews and notifications. Still empty: job postings, job applications, friendships, wishlists, saved talents, chat conversations; **no admin user**.
- **Gates phases:** 11, 17, 18, 19, 20, 21, 22, 23, 24, 25, 27, 28
- **Source:** Audit G, K, J38
- ANSWER (2026-10-31, Phase 17 session, **institution dashboard only**): the existing seed data is enough for Phase 17; no new seeder. States absent from the data (draft problems, rejected / needs_review verification) are covered by feature tests. Seed-data correction approved in the same session: see 'Note on Phase 17 data fixes' at the end of this file.

### D-14 — Problem gallery images

- **Status:** OPEN
- **Question:** How are the 310 gallery images (3-5 per problem, on remote Supabase storage) handled in local development and in layouts?
- **Why it matters (evidence):** All 310 fail to load locally, so layouts were never judged with real photos (OBSERVED, J12). The design flaw is the missing fallback.
- **Options:**
  - (a) Collapse the gallery when images are absent or fail; point dev to local storage.
  - (b) Provide sample local images.
- **Recommendation (not approved):** (a), plus verify with real images once available.
- **Gates phases:** 8, 9
- **Source:** Audit J12, G
- ANSWER (2026-10-31, Phase 8 session): option (b), as **dev seed images** in remote storage. The user: the old Supabase images were not kept; upload images from `public/` to Supabase Storage, in a bucket named **`sg-storage`** (not 'karsa-storage'). Done: a public bucket `sg-storage` was created in the new Supabase project (it had none); 92 objects were uploaded to the exact paths already stored in `problem_images.image_path` (`problems/masalah-desa-NN.jpg|.jpeg`; 220 rows, 77 problems; **database unchanged**). Source: the 37 catalogue photographs marked `available` in `student-go-docs/design-system/photo-review.json` (Unsplash/Pexels, no identifiable people), converted to JPEG ≤ 1600 px q82, photo `(NN-1) mod 37`, so each is reused 2–3 times and **does not match the problem's content**. KKN photographs with people (D-30) were not used. `.env`: only `SUPABASE_BUCKET` changed (to `sg-storage`). Mapping and HTTP results: `reports/phase8/seed-images-upload-log.json` (92 × 200). Layouts still collapse the image when a record is absent or fails (option a's fallback is kept). Real problem photographs replace these when institutions upload them.

### D-15 — Placeholder region data

- **Status:** OPEN
- **Question:** Is the 'Kota <Provinsi>' placeholder dataset treated as a data issue outside the design?
- **Why it matters (evidence):** Most provinces return one placeholder regency; real names ('Kabupaten Penajam Paser Utara') are untested for wrapping (OBSERVED, G).
- **Options:**
  - (a) Out of scope; layouts must tolerate long real names (recommended).
  - (b) Fix dataset inside Phase 1.
- **Recommendation (not approved):** (a).
- **Gates phases:** 6, 18
- **Source:** Audit G
- ANSWER (2026-10-31, Phase 6): option (a) — the placeholder regency data is out of scope for the design; the dependent selects must tolerate long real names (tested with 'Kabupaten Penajam Paser Utara') and get request cancellation, a loading state and an `aria-live` announcement. No region data is changed.

### D-16 — Missing reference documents B and C

- **Status:** ANSWERED (2026-10-30)
- **Question:** Provide 'Design Implementation Plan' (B) and 'Custom Visual & Asset Plan' (C), or approve an explicit substitution?
- **Why it matters (evidence):** Neither was found. Only the audit (A) was provided. The asset package built earlier (docs/DESIGN_SYSTEM.md, INTEGRATION.md, ASSET_SOURCES.md) follows the earlier brief and may overlap with C, but it is not confirmed to be C.
- **Options:**
  - (a) User supplies B and C; E sections and tokens are reconciled before Phase 2.
  - (b) User approves audit section I + the asset package as the substitute, recorded here.
- **Recommendation (not approved):** (a) if the documents exist; otherwise (b). Nothing is silently substituted in the meantime.
- **Gates phases:** 2
- **Source:** Brief, current task
- Not answered in Phase 0; carried as a blocker for Phase 2 (it does not gate any Phase 0 task).
- ANSWER (2026-10-30): option (b) — audit section I + the `student-go-assets` package are the approved substitute for documents B and C.

### D-17 — Approve the asset package as the visual foundation

- **Status:** ANSWERED (2026-10-30)
- **Question:** Are the proposed tokens and type system approved: brand #405080; neutral ramp; earth accent for editorial only; Plus Jakarta Sans (UI) + Source Serif 4 (reading); 9-step type scale; radii 6/8 px; shadow only on overlays; 150 ms motion; Lucide 1.5 px icons; the status vocabulary?
- **Why it matters (evidence):** Built before this planning task, not yet approved by the user, and not integrated into the app.
- **Options:**
  - (a) Approve as is.
  - (b) Approve with changes.
  - (c) Reject and restart.
- **Recommendation (not approved):** (a) or (b) after viewing docs/styleguide.html.
- **Gates phases:** 2, 3
- **Source:** student-go-assets package
- ANSWER (2026-10-30): option (a) — approved as is (brand #405080, neutral ramp, earth accent editorial-only, Plus Jakarta Sans + Source Serif 4, 9-step scale, radii 6/8, overlay-only shadow, 150 ms motion, Lucide 1.5 px, status vocabulary). Token values are locked as in the package.

### D-18 — Brand mark

- **Status:** ANSWERED (2026-10-30)
- **Question:** Is the redrawn original Student Go wordmark/symbol approved, or is the user supplying one?
- **Why it matters (evidence):** The current logo is `karsa-logo.png`, 1430x566 px, 272 KB, labelled Karsa (VERIFIED live 2026-10-30). The audit recommends keeping the slate-indigo equity (about #405080). Under AD-09 the PNG is retained and stays live until this decision and D-01 are answered; it is never deleted by a phase.
- **Options:**
  - (a) Use the package's SVG wordmark/symbol (only valid if D-01 picks Student Go).
  - (b) User supplies the mark.
  - (c) Keep the Karsa mark: produce an optimised SVG or 2x PNG of it instead of the package wordmark.
- **Recommendation (not approved):** Follow D-01. If Student Go: (a) unless the user has an existing Student Go logo. If Karsa: (c). In both cases `karsa-logo.png` stays in place until a later phase authorises removal from templates.
- **Gates phases:** 2, 3, 4
- **Source:** Audit I, J20, J24
- ANSWER (2026-10-30): option (a) — the package SVG wordmark/symbol (follows D-01 = Student Go). `karsa-logo.png` stays on disk and stays in the layout until Phase 3.

### D-19 — Documentary photography

- **Status:** ANSWERED for the homepage (2026-10-31); OPEN for other pages
- **Question:** Where may photographs appear, and who reviews them: the existing field photographs in `public/` (Source A) first, and the 60 catalogue photographs (Source B) only if gaps remain?
- **Why it matters (evidence):** Source B photos are license-checked but not downloaded or viewed; the catalogue marks 5 photos with minors and 13 with unknown people status. Source A holds real KKN field photographs that the audit calls the most authentic assets in the app, but their provenance and consent are undocumented (D-30). Audit guardrail 7: images only where they carry content, never as page backgrounds.
- **Options:**
  - (a) Editorial use only (homepage band, about, project story, repository story).
  - (b) Add auth side photo.
  - (c) No photography.
- **Recommendation (not approved):** (a), using the existing field photographs first (after D-30), and downloading Source B photos only for a slot nothing in Source A can fill (D-33). Human visual and consent review of every photo that shows people, especially minors.
- **Gates phases:** 4, 5, 9, 12
- **Source:** Asset package catalogue; ASSET_INVENTORY.md
- NOTE (2026-10-30, Phase 2): still OPEN for placement. Phase 2 copied the catalogue files (D-33) and recorded a preliminary people/minors flag per file; no photograph was placed in any page.
- ANSWER (2026-10-31, Phase 4, homepage only): editorial use of **catalogue photographs** (`public/assets/photography/homepage/`, preliminary review 'available', no identifiable people), at most two, framed with a place caption and **no visible credit line** (the user: "gak perlu pakai credit"; the Unsplash and Pexels licences do not require attribution; credits stay in `credits.json`). Never a background. Placement on other pages stays OPEN.
- ANSWER (2026-10-31, Phase 4 revision, homepage): the two catalogue photographs were replaced by two **KKN ITB 2025 field photographs** (WebP derivatives of `mahasiswa-about.jpeg` and `network-student.JPG`, see D-30 and D-34), framed, place caption 'KKN ITB 2025. …', no visible credit line, never a background. Recorded from the Phase 4 revision session (its tracker note of 2026-10-31); written into this log by the Phase 4 closing session.
- ANSWER (2026-10-31, Phase 5 pre-check, About only): editorial framed figures on `/about` from the four existing About photographs (`mahasiswa-about.jpeg`, `handprints-about.jpeg`, `jaket-about.jpeg`, `about.jpg`), with the D-30 and D-34 answers below. Whether a visible credit line is shown on About is not yet answered (the homepage has none). Not yet placed: Phase 5 waits for Phase 4.
- ANSWER (2026-10-31, Phase 5 session, About): **caption only, no visible credit line** (as on the homepage). Placed: `mahasiswa-about.jpeg` (existing Phase 4 derivative), `handprints-about.jpeg` and `jaket-about.jpeg` (new derivatives). `about.jpg` was not placed (decorative, no KKN content; RN-04). Contact has no photograph.
- ANSWER (2026-10-31, Phase 6, auth pages): **no photograph** (option c for auth). The wide-screen side panel uses the Student Go wordmark and the original `contour-field.svg` motif only; `login-register-view.jpg` stops being referenced and stays on disk.

### D-20 — Delivery of JS/CSS libraries

- **Status:** ANSWERED (2026-10-30)
- **Question:** Keep Alpine/Leaflet on CDNs, vendor them, or add npm packages? Remove AOS, Boxicons and Font Awesome?
- **Why it matters (evidence):** Runtime CDN calls (VERIFIED on /): Google Fonts, cdnjs (Font Awesome 6.5.1), unpkg (Leaflet 1.9.4, AOS 2.3.1, Boxicons 2.1.4), jsDelivr (Alpine 3.13.3). The app bundle app-34mOoJaZ.js is 0 bytes (OBSERVED). Boxicons is used 0 times and Font Awesome 2 times (OBSERVED, J24).
- **Options:**
  - (a) Remove AOS, Boxicons, Font Awesome; keep Alpine and Leaflet as they are for now.
  - (b) (a) plus self-host Alpine and Leaflet (npm or vendored files).
  - (c) Keep everything.
- **Recommendation (not approved):** (a) now, (b) as a separate approved step. New npm/composer dependencies need explicit approval.
- **Gates phases:** 2, 3, 4
- **Source:** Audit C, J24; live
- NOTE (2026-10-30, Phase 2): still OPEN. Phase 2 removed no CDN tag (Phase 3 owns that); no Phase 2 task depended on the answer.
- ANSWER (2026-10-30, Phase 3): option (a) — remove Boxicons, Font Awesome and the Google Fonts / fonts.bunny.net links from the site layouts (the 10 `fa-` icons on `/student/jobs/saved` become Lucide sprite icons); keep Alpine and Leaflet on their CDNs for now; AOS is removed with the homepage in Phase 4 (its only user). Self-hosting Alpine/Leaflet (option b) would be a separate approved step.

### D-21 — Charts on dashboards

- **Status:** OPEN
- **Question:** Which chart library exists (UNKNOWN), and are charts shown at all?
- **Why it matters (evidence):** Company dashboard draws two empty charts, one with a 0-1.0 decimal axis for counts (OBSERVED). Guardrail 2: no chart under five data points.
- **Options:**
  - (a) No charts until data exists; reuse whatever library already exists.
  - (b) Add a library.
- **Recommendation (not approved):** (a).
- **Gates phases:** 7, 10, 17, 21
- **Source:** Audit D5, D11, H
- ANSWER (2026-10-31, Phase 7 session): option (b) — a chart library may be added. Applied in Phase 7: the student dashboard has **no chart**, because none of its approved numbers (D-10) is a series of at least five points (guardrail 2); no package was installed, since an unused dependency is not allowed. The library itself (a new npm package, or reusing Chart.js 4.4.1 that the company dashboard already loads from a CDN) is chosen, with the reason, in the first phase that draws a chart.
- ANSWER (2026-10-31, Phase 17 session, **institution dashboard**): no chart on the institution dashboard (the user: 'Tanpa grafik'). `Institution\DashboardController@index` no longer computes the unused time-series and SDG-category data; no package installed.

### D-22 — Test approach

- **Status:** ANSWERED
- **Question:** Which automated tests exist (UNKNOWN) and may a browser-test or visual-regression dev dependency be added?
- **Why it matters (evidence):** No repository access; the test framework, factories and CI are UNKNOWN.
- **Options:**
  - (a) Use only what exists plus manual matrix checks.
  - (b) Add Playwright as a dev dependency (approval needed).
  - (c) Run Playwright via npx outside the project, adding nothing.
- **Recommendation (not approved):** (c), then (b) if the user wants repeatable visual checks.
- **Gates phases:** 0, 27, 28
- **Source:** Brief, section on dependencies
- ANSWER (2026-10-30): option (c) — Playwright run outside the project (Python Playwright already installed in the user profile); nothing added to `composer.json`/`package.json`. Existing suite: PHPUnit, 2 example tests.

### D-23 — Content ownership for new or rewritten copy

- **Status:** ANSWERED for the homepage (2026-10-31); OPEN for other pages
- **Question:** Who writes and approves About copy, Contact channels and FAQ, homepage proof, example placeholders, and the privacy policy?
- **Why it matters (evidence):** About carries unsourced figures ('520K+', '83K+', '8,3 juta mahasiswa', '76%', '±Rp 1,2 triliun', '94%'); homepage carries 34 / 514 / 85+ / 120+ (VERIFIED on /: hard-coded text); Kebijakan Privasi points to '#' (VERIFIED). Nothing may be invented.
- **Options:**
  - (a) User supplies copy and sources.
  - (b) Claude drafts Indonesian copy from verifiable product facts only, user approves.
- **Recommendation (not approved):** (b) with sources, or (a).
- **Gates phases:** 4, 5, 6
- **Source:** Audit D1, J9, J20, M
- NOTE (2026-10-30, Phase 3): still OPEN. Per the Phase 3 work order (task 3.5) the footer's 'Kebijakan Privasi' link, which pointed to `#`, is hidden until a privacy page exists; no copy was written.
- ANSWER (2026-10-31, Phase 4, homepage only): option (b) — Claude drafts Indonesian homepage copy from verifiable product facts only, marked DRAFT in the Phase 4 report for the user's approval. No tagline, history, impact claim or testimonial. About/Contact/privacy copy stays OPEN.
- ANSWER (2026-10-31, Phase 5 pre-check, About): option (b) — Claude drafts neutral Indonesian About copy from verifiable product facts only, marked DRAFT in the Phase 5 report. The Karsa-era mission line (RN-07), the 'Dari Tahun ke Tahun' heading (RN-08), the founding story, age, growth and SDG impact claims (RN-09) and every unsourced figure are **not published** (also the About part of D-37). Contact: the user will supply the photos and the contact details; Contact copy waits for them. FAQ: Claude drafts from product facts, DRAFT. Privacy copy stays OPEN.
- ANSWER (2026-10-31, Phase 5 session, Contact): the user supplied the contact channels: **email only**, M. Abizzar Gamadrian (13523155@std.stei.itb.ac.id), Danendra Shafi Athallah (13523136@std.stei.itb.ac.id), Kenzie Raffa Ardhana (18223127@std.stei.itb.ac.id); no WhatsApp, no roles. Contact copy (RN-11/RN-12) and the FAQ were drafted from product facts and are DRAFT in `reports/PHASE_05_REPORT.md` §5, as is the About copy. Privacy copy stays OPEN.
- ANSWER (2026-10-31, Phase 6, auth pages): option (b) — Claude drafts the Indonesian copy for login, role chooser, the three registrations, password reset and email verification from product facts only (validation rules, the documents the form really asks for); no promise about how long verification takes (D-09 stays OPEN). Marked DRAFT in `completion-reports/PHASE_06_COMPLETION_REPORT.md`.

### D-24 — Real-device and screen-reader testing

- **Status:** OPEN
- **Question:** Who tests on iOS Safari, Android Chrome (360-414 px), and NVDA/VoiceOver?
- **Why it matters (evidence):** The audit tested viewports only in iframes, never on devices.
- **Options:**
  - (a) User/teammate tests on devices using a checklist from Phase 27.
  - (b) Skip (leaves a documented gap).
- **Recommendation (not approved):** (a).
- **Gates phases:** 27, 28
- **Source:** Audit G, K

### D-25 — Defect fixes that change HTTP behaviour

- **Status:** ANSWERED (2026-10-30)
- **Question:** May Phase 1 change: GET /student/chatbot/create (creates a record on GET); /student/repository/abc (500 to 404); /institution/reviews/create (QueryException); APP_DEBUG handling; and the silent redirect for cross-role access?
- **Why it matters (evidence):** These are defects (J13), but they touch routing and behaviour, which the brief says to preserve.
- **Options:**
  - (a) Approve all four fixes.
  - (b) Approve selectively.
  - (c) Keep silent cross-role redirect as is (recommended for that one item).
- **Recommendation (not approved):** (a) for the first three; keep the cross-role redirect unchanged.
- **Gates phases:** 1, 16, 19, 26
- **Source:** Audit J13, F
- ADDITION (2026-10-31, Phase 7 session): one more defect fix approved by the user, outside the Phase 1 list. `Student\DashboardController` called `json_decode()` on `students.skills`, which the model casts to an array, so the dashboard returned HTTP 500 as soon as a student saved skills through Edit Profil (0 of 700 seeded students had skills, so it never showed). Now the array is used as it is (a legacy JSON string is still decoded); recommendation logic otherwise unchanged. Regression test `tests/Feature/Phase7/StudentDashboardTest::test_skills_saved_as_array_no_longer_break_the_dashboard` (red without the fix, green with it).
- ANSWER (2026-10-30): option (b), three fixes approved: (1) `GET /student/chatbot/create` persists nothing (redirects to the list); a NEW route `POST /student/chatbot/create` (`student.chatbot.store`) creates the conversation and the 'Percakapan Baru' links become POST forms; (2) numeric constraint on `{id}` parameters in `routes/web.php` so non-numeric IDs (`/student/repository/abc`, `/institution/reviews/create`) return 404; (3) `app.debug` can only be true when `APP_ENV=local` (`.env` not edited). The silent cross-role redirect is kept unchanged.

### D-26 — Features the audit proposes that do not exist today

- **Status:** OPEN
- **Question:** Which proposed interactions are new features, not design, and may they be built?
- **Why it matters (evidence):** Contact message form (G03), PDF preview (S10/S11), match-reason column for talents (C02), kanban pipeline (C06), saved filters, live preview rail for forms (I06, C05), sortable columns requiring server sort. Building them is feature work with backend impact.
- **Options:**
  - Decide each individually; default is 'not built', 'design only the layout slot'.
- **Recommendation (not approved):** Default: do not build; implement only the presentation of existing data.
- **Gates phases:** 5, 14, 18, 22, 23, 24
- **Source:** Audit L (arah rombakan)
- NOTE (2026-10-31, Phase 5): still OPEN. Default applied: no contact message form was built; `/contact` lists the supplied email channels and an FAQ.
- NOTE (2026-10-31, Phase 18): still OPEN. The live preview rail of the problem form (I06) was **not built**, on the user's instruction. The editor has a section navigation and a progress count instead (presentation of the existing form only). No new server sort: the list keeps its existing `sort` values; `most_applied` became an alias of `applications` (see 'Note on Phase 18 behaviour fixes').
- ANSWER (2026-10-31, Phase 19, **institution applicant queue only**): keyboard navigation of the applicant queue is approved as a front-end-only interaction. From 1024 px, Arrow Up/Down, Home and End move the selection, and Enter or click shows the application next to the queue. No endpoint, parameter or data change. Everything else under D-26 stays OPEN.

### D-27 — Git workflow for phase execution

- **Status:** ANSWERED (interim)
- **Question:** Branch and commit policy per phase?
- **Why it matters (evidence):** CLAUDE.md forbids committing unless asked. Phases are independent units.
- **Options:**
  - (a) One branch per phase `design/phase-NN-slug`; commit only on the user's request at phase end.
  - (b) One long-lived design branch.
- **Recommendation (not approved):** (a).
- **Gates phases:** 0
- **Source:** Brief, safety rules
- ANSWER (2026-10-30): no new branch and no commits for now; work stays on `feat/students` uncommitted. The user will move the project to a **new GitHub repository** later and decide the branch policy then. Until then, never commit unless asked.

### D-28 — Documentation root path

- **Status:** ANSWERED
- **Question:** Is `student-go-docs/design-execution/` the documentation root (with `CLAUDE.md` at the repository root)?
- **Why it matters (evidence):** The first planning pack used `docs/design/`. The user's asset-correction message names `student-go-docs/design-execution/ASSET_INVENTORY.md` and `student-go-docs/design-execution/phases/`. ASSUMED: the user's path is intended. This pack therefore uses it everywhere. It also keeps planning docs apart from the asset package's own `docs/` folder.
- **Options:**
  - (a) Use `student-go-docs/design-execution/` (assumed, applied in this pack).
  - (b) Go back to `docs/design/`.
- **Recommendation (not approved):** (a). If a copy of the first pack already sits under `docs/design/` in the repository, it is superseded: delete it yourself after copying this one (this session did not touch the repository).
- **Gates phases:** 0
- **Source:** User message 2026-10-30
- ANSWER (2026-10-30): option (a). The pack arrived as `student-go-design-docs/` (+ zip); at the user's choice `CLAUDE.md` and `student-go-docs/` were **copied** to the repository root and all Phase 0 updates are made there. `student-go-design-docs/` and `student-go-design-docs.zip` are left untouched as the original delivery (the copies at the root are now authoritative).

### D-29 — Where the asset package CSS lives and how it is wired

- **Status:** ANSWERED (2026-10-30)
- **Question:** Confirm the canonical CSS location `resources/css/student-go/` and the import order in `resources/css/app.css`.
- **Why it matters (evidence):** The user's message says `student-go-assets/public/resources/css/student-go/`; the package on disk has `student-go-assets/resources/css/student-go/` (VERIFIED by listing the package). The Vite manifest shows two entries only (`resources/css/app.css`, `resources/js/app.js`; VERIFIED). A missing `resources/css/auth-institution.css` manifest entry already breaks `/forgot-password` locally (VERIFIED earlier; see DISCOVERY_REPORT S1-04).
- **Options:**
  - (a) `resources/css/student-go/{fonts,tokens,components}.css`, imported from `resources/css/app.css` after `@import "tailwindcss"` (INTEGRATION.md section 3).
  - (b) Keep a separate Vite entry for the new CSS (needs `vite.config` and layout changes).
- **Recommendation (not approved):** (a), after Stage One reads `vite.config.js` and `resources/css/app.css`.
- **Gates phases:** 2, 3
- **Source:** ASSET_INVENTORY.md section 10
- ANSWER (2026-10-30): option (a), **staged**. `resources/css/student-go/` imported from `resources/css/app.css` after `@import "tailwindcss"`. The parts of the package that change existing pages globally (re-tuned `gray-*`, `primary-*` aliases, `rounded-xl/2xl/3xl` shim, `--font-sans`, `text-4xl/5xl` line heights, `tracking-tight`, the `@layer base` element rules and the global reduced-motion rule) were moved **unchanged** into `resources/css/student-go/legacy-overrides.css`, which is not imported until Phase 3 activates it with the shell.

### D-30 — Provenance and consent for existing photographs in `public/`

- **Status:** OPEN
- **Question:** For each existing photograph (see ASSET_INVENTORY.md section 1): who took or supplied it, under what rights, and do the people shown (and any minors) agree to public use?
- **Why it matters (evidence):** No credit, licence or consent record exists for any Source A image (UNKNOWN). Seven look like genuine KKN field photographs, one is a team portrait, the rest look like landscape/aerial or corporate stock of unknown origin. The audit recommends reusing the real field photographs; reuse needs rights, not only presence in the repository.
- **Options:**
  - (a) User confirms rights per file (a short table is enough); unconfirmed files stay where they are today and get no new prominent placement.
  - (b) User replaces unconfirmed files with photos whose rights are known.
- **Recommendation (not approved):** (a). Do not publish any photo in a new, larger or more prominent placement until its row in ASSET_INVENTORY.md says rights confirmed.
- **Gates phases:** 4, 5, 6, 9
- **Source:** ASSET_INVENTORY.md sections 1 and 9
- NOTE (2026-10-31, Phase 4): still OPEN. The homepage uses no Source A photograph (catalogue photographs were chosen instead), so no right was assumed; `hero-background.jpg` only stops being referenced by the homepage and stays on disk.
- ANSWER (2026-10-31, Phase 4 revision, homepage): the user confirmed that the KKN ITB field photographs may be used on the homepage; placed: `mahasiswa-about.jpeg` and `network-student.JPG` (as derivatives). Recorded from the Phase 4 revision session's tracker note; the Phase 5 pre-check answer below separately confirms `mahasiswa-about.jpeg`. The NOTE above is superseded for these two files.
- ANSWER (2026-10-31, Phase 5 pre-check): the user confirms rights for public use of `mahasiswa-about.jpeg`, `handprints-about.jpeg`, `jaket-about.jpeg` and `about.jpg` on About (option (a) for these four files). Other Source A files stay OPEN unless answered elsewhere.
- ANSWER (2026-10-31, Phase 5 review): for About the user asked for photographs different from the homepage, 'apapun yang penting terlihat KKN' (any photograph that shows KKN). Placed: `dashboard-student2.jpeg` and `repo-student.jpeg` (WebP derivatives, D-34 a). `projects-student.jpeg` was not placed. Other uses of these files stay under this decision's general rule.
- NOTE (2026-10-31, Phase 6): not needed for auth; the user chose no photograph on auth pages (see D-19).

### D-31 — Personal photographs, student photos and identifying file names

- **Status:** ANSWERED (2026-10-30)
- **Question:** How are the team portraits (`profile_<student-id>.jpg`), student profile photos and other personal images handled?
- **Why it matters (evidence):** `/contact` publishes three team portraits whose file names contain student ID numbers; two of the three links are broken today (one is a `.jpg`/`.jpeg` mismatch, one file is missing; VERIFIED). Guests can read a student's contact data on `/profile/{username}` (J3, D-04), so profile photos may be public too (UNKNOWN where they are stored).
- **Options:**
  - (a) Preserve all personal photos untouched in discovery; Phase 5 repairs the two broken references and decides on renaming; no new public exposure anywhere.
  - (b) Rename to neutral file names in Phase 5 (needs approval; keep originals until the new references are tested).
- **Recommendation (not approved):** (a) now, (b) in Phase 5 if the user agrees. Student profile photos follow D-04.
- **Gates phases:** 1, 5, 12, 13, 22
- **Source:** ASSET_INVENTORY.md sections 1 and 9
- ANSWER (2026-10-30): option (a) for Phase 1 — personal photos untouched, no new public exposure; renaming the team photos is decided in Phase 5.
- NOTE (2026-10-31, Phase 5 pre-check): for `/contact` the user will supply the team photos and the contact details to publish. Until then no portrait file is renamed, and the missing `profile_18223127.jpg` is not substituted (for example by the unreferenced `profile_13523151.jpeg`) without the user's word.
- ANSWER (2026-10-31, Phase 5 session): the user supplied the team photos in `public/assets/photography/member/` (three PNGs, names as file names). They are shown on About as square WebP crops (new files beside the PNGs). `profile_13523136.jpg`, `profile_13523124.jpeg` and `profile_13523151.jpeg` are **no longer referenced** (Deprecate usage); none was renamed, moved or deleted. Renaming them is no longer needed for any page.
- NOTE (2026-10-31, Phase 5 review): the user replaced the member photos with transparent `*_seragam.png` files (the earlier PNGs were removed from the folder outside this session). About shows WebP derivatives of the new files with transparency kept.

### D-32 — Certificate assets (templates, Pinyon Script, Poppins)

- **Status:** RESOLVED BY STAGE ONE
- **Question:** Where do the certificate templates and the Pinyon Script and Poppins fonts live, and which routes or services generate certificates?
- **Why it matters (evidence):** The user states they exist and must be preserved. They were NOT found at probed public paths and the audit's 59 route patterns contain no certificate route (UNKNOWN; BLOCKED without repository access). Removing site-wide Google Fonts links or changing site typography must not touch them.
- **Options:**
  - (a) Stage One locates them (Blade, `public/fonts`, `storage`, DomPDF config) and records them as a protected asset group; certificate output is compared before and after every phase that touches fonts or the layout.
  - (b) User lists the paths.
- **Recommendation (not approved):** (a), with (b) as a shortcut.
- **Gates phases:** 0, 2, 3, 12
- **Source:** User message 2026-10-30
- ANSWER (2026-10-30): option (a) done. `public/assets/certificate/template.png`, `public/assets/certificate/fonts/PinyonScript-Regular.ttf`, `public/assets/certificate/fonts/Poppins-Regular.ttf`; rendered by `app/Services/CertificateService.php` (GD, fonts by file path) and stored in Supabase. Protected group; baseline sample in `baseline/screenshots/certificate-sample-baseline.png`. See ASSET_INVENTORY.md 1.5.

### D-33 — Whether to download the 60 catalogue photographs at all

- **Status:** ANSWERED (2026-10-30)
- **Question:** Run `scripts/fetch-photos.php` (all 60, only `--only=homepage`, or none)?
- **Why it matters (evidence):** Existing field photographs may already fill the homepage and About slots; the downloader needs network and the review burden is real. Nothing may be downloaded during discovery.
- **Options:**
  - (a) Defer. Decide after the Source A rights check (D-30) and the Phase 4/5 layouts show which slots remain empty.
  - (b) Run `--only=homepage` once as a comparison set.
  - (c) Run all 60 now.
- **Recommendation (not approved):** (a).
- **Gates phases:** 4, 5
- **Source:** ASSET_INVENTORY.md section 10
- ANSWER (2026-10-30): option (c) variant — the 60 photographs were already present as WebP inside the package (not downloaded in Phase 2); **copy all 60** plus `credits.json` to `public/assets/photography/`. No placement in any page; held/flag status recorded in metadata (D-19).

### D-34 — Photograph derivatives: format, sizes and location

- **Status:** OPEN
- **Question:** May Phase 2 or 4 produce responsive WebP derivatives of the large existing photographs (for example 2400, 1600 and 800 px wide) while the originals stay untouched, and where do they live?
- **Why it matters (evidence):** Seven field photographs are 4912x2760 and `about.jpg` is 7360x4912 at 3.6 MB; `dashboard-student3.jpg` is 3.3 MB (VERIFIED). Serving originals is the weight problem of audit J6. AD-09 forbids replacing or moving the originals during discovery.
- **Options:**
  - (a) Derivatives under `public/assets/photography/field/` (new files with new names), originals untouched, templates switched in the phase that places the photo (recommended).
  - (b) Re-encode in place (not allowed under AD-09).
- **Recommendation (not approved):** (a). Naming: `<subject>-<width>.webp`, plus an Indonesian alt text and a caption recorded in `ASSET_INVENTORY.md`.
- **Gates phases:** 4, 5
- **Source:** ASSET_INVENTORY.md sections 1 and 6
- NOTE (2026-10-31, Phase 4): not needed for the homepage (no Source A photograph placed; the catalogue WebP files are used as they are). Still OPEN for Phase 5.
- ANSWER (2026-10-31, Phase 4 revision): option (a) for the homepage — `public/assets/photography/field/kkn-itb-2025-mahasiswa-jalan-desa-{800,1600}.webp` (from `mahasiswa-about.jpeg`) and `kkn-itb-2025-rombongan-hujan-{800,1600}.webp` (from `network-student.JPG`); originals untouched (hashes unchanged). The NOTE above is superseded.
- ANSWER (2026-10-31, Phase 5 pre-check): option (a) for the four About photographs — new WebP derivatives under `public/assets/photography/field/` (new file names), originals untouched. To be produced when Phase 5 runs.

### D-35 — Embedded C2PA metadata in the package's SVG and PNG files

- **Status:** ANSWERED (2026-10-30)
- **Question:** Strip the embedded `<metadata><c2pa:manifest>` block (about 7.7 KB) from the 96 SVGs and 7 PNGs when they are copied into the project, or keep it as provenance?
- **Why it matters (evidence):** VERIFIED by reading the package: every SVG (and the PNG and HTML files) carries an embedded Content Credentials block. It makes each Lucide icon about 8 KB instead of a few hundred bytes (71 icons about 570 KB, `sprite.svg` 28 KB). It is INFERRED to have been added by the tool environment that wrote the files, not by the project.
- **Options:**
  - (a) Strip it on the copy in `public/assets/` (keep the package originals untouched) and keep provenance in `ASSET_SOURCES.md` (recommended).
  - (b) Keep it.
- **Recommendation (not approved):** (a), as part of the Phase 2 copy, with SHA-256 of both versions recorded.
- **Gates phases:** 2
- **Source:** ASSET_INVENTORY.md section 6
- ANSWER (2026-10-30): option (a) — strip on the copy only; package originals untouched; SHA-256 of both versions recorded (`student-go-docs/design-system/c2pa-strip-hashes.tsv`).

### D-36 — May companies export student email addresses?

- **Status:** OPEN (raised by Phase 1, 2026-10-30)
- **Question:** `/company/talents/export` and the saved-talents export write each student's **email** into a CSV for any company account. Is that allowed?
- **Why it matters (evidence):** D-04 (answered: contact only for the owner and accepted connections) was asked about the profile page; the exports were found afterwards (`app/Http/Controllers/Company/TalentController.php`, the `fputcsv` rows). Phase 1 did not change them.
- **Options:**
  - (a) Remove email from both exports (consistent with D-04 (a)).
  - (b) Keep email only for students who consented (needs a consent field: feature work).
  - (c) Keep as is.
- **Recommendation (not approved):** (a).
- **Gates phases:** 22
- **Source:** Phase 1 report section 7

## Part 3. Blockers and unknowns

| Item | Label | What unblocks it |
|---|---|---|
| Repository access for the agent | BLOCKED | Run the coding agent in the Laravel project (Phase 0 Stage One). |
| `php artisan route:list` | BLOCKED | Stage One (S1-06). |
| Reference documents B and C | UNKNOWN | D-16. |
| Working database | BLOCKED (VERIFIED failing) | D-12. |
| Seeded institution/company records, active projects, accepted applications, reviews, admin account | BLOCKED | D-13. |
| Admin UI | BLOCKED | D-11 and credentials. |
| `/forgot-password` visual and reset flow | BLOCKED (route VERIFIED broken) | Phase 6 after the Vite manifest fix. |
| Real-device and screen-reader testing | BLOCKED | D-24. |
| Catalogue photographs not downloaded/reviewed | BLOCKED (deliberately deferred) | D-33; review only if downloaded (D-19). |
| Rights and consent for existing photographs | UNKNOWN | D-30 (user confirms per file). |
| Certificate templates and fonts; uploaded avatars, logos, verification documents | UNKNOWN / BLOCKED | Stage One S1-31, S1-32; D-31, D-32. |
| `resources/` Blade, CSS and JS (what is really used) | BLOCKED | Stage One S1-30, S1-33. |
| Test credentials | BLOCKED | Found in seeders by Stage One or supplied by the user; never written into docs. |
| Real gallery images in layouts | BLOCKED | D-14. |

## Part 4. Gating matrix (phase to decisions)

| Phase | Title | Decisions that gate it |
|---|---|---|
| 0 | Readiness & Baseline | D-12, D-16, D-22, D-27, D-28, D-32 |
| 1 | Critical Fixes & Data Integrity | D-03, D-04, D-05, D-06, D-25, D-31 |
| 2 | Brand Identity & Design System | D-01, D-16, D-17, D-18, D-19, D-20, D-29, D-32, D-35 |
| 3 | Shared Shell & Components | D-01, D-02, D-17, D-20, D-25, D-29, D-32 |
| 4 | Homepage | D-01, D-19, D-20, D-23, D-30, D-33, D-34 |
| 5 | About, Contact, Public Institution Profile | D-19, D-23, D-26, D-12, D-30, D-31, D-33, D-34 |
| 6 | Auth | D-01, D-02, D-15, D-23, D-30 |
| 7 | Student Dashboard | D-03, D-10, D-21, D-12 |
| 8 | Student Project Discovery | D-02, D-05, D-14, D-12 |
| 9 | Project Detail | D-02, D-14, D-19, D-30 |
| 10 | Applications | D-02, D-21, D-12 |
| 11 | Active Projects & Wishlist | D-13, D-21 |
| 12 | Profile & Portfolio | D-03, D-04, D-19, D-23, D-31, D-32 |
| 13 | Friends | D-02, D-03, D-04, D-31 |
| 14 | Repository | D-02, D-05, D-26 |
| 15 | Jobs | D-07, D-13, D-02 |
| 16 | KARA Chatbot & Notifications | D-01, D-25 |
| 17 | Institution Dashboard | D-09, D-10, D-13, D-21 |
| 18 | Institution Project Management | D-02, D-15, D-26, D-13 |
| 19 | Institution Review & Monitoring | D-02, D-13, D-25, D-26 |
| 20 | Institution Profile & Verification | D-06, D-09, D-13 |
| 21 | Company Dashboard | D-02, D-10, D-13, D-21 |
| 22 | Company Talent Discovery | D-02, D-04, D-05, D-08, D-26, D-31 |
| 23 | Company Job Management | D-02, D-07, D-13, D-26 |
| 24 | Applicant Pipeline | D-02, D-13, D-26 |
| 25 | Company Profile | D-06 |
| 26 | Error Pages | D-01, D-11, D-25 |
| 27 | Responsiveness & Accessibility | D-24, D-22, D-12, D-13 |
| 28 | Final QA | D-12, D-13, D-22, D-24 |

## Part 5. Minimum answers before each wave

- **Before Phase 0:** D-27 (git workflow) and permission for read-only repository access; D-12 and D-22 can be answered during Phase 0.
- **Before Phase 1:** D-03, D-04, D-05, D-06, D-25.
- **Before Phase 2:** D-01, D-16, D-17, D-18, D-19, D-20, D-29, D-32, D-35 (D-28 is applied by assumption: confirm it).
- **Before Phases 4 and 5 (photographs):** D-30, D-31, D-33, D-34.
- **Before Phase 3:** D-02 (copy deck) and D-20; D-25 for the cross-role redirect.
- **Before the page phases:** the decisions listed for each phase in Part 4.

### Note on D-37 (2026-10-31, Phase 4)

`CONTENT_RENAME_INVENTORY.md` (copied to this folder on 2026-10-31 at the user's request) refers to D-36…D-40 for naming questions; in this file D-36 is the talent-export question, and D-37…D-40 have no entry yet. For the homepage the user decided RN-02 directly: the Karsa tagline 'Karya Untuk Bangsa' is removed and no replacement tagline is written. The rest of D-37 (About history and claims) is for Phase 5.

ANSWER (2026-10-31, Phase 5 pre-check, About): the Karsa-era narrative is not carried over: no mission sentence, history heading, founding story, age or impact claim is published on About; Claude drafts neutral copy from product facts, marked DRAFT for approval.

OUTCOME (2026-10-31, Phase 5): applied. RN-07, RN-08 and RN-09 were removed, not renamed; About publishes product facts only (DRAFT). A sentence saying the team are ITB students was drafted and withdrawn before completion (inferred from email domains, not approved). D-37 stays open only if the user wants a mission, history or impact text: supply the wording and sources.

### Note on Phase 6 behaviour questions (2026-10-31)

Asked in the Phase 6 session because they change behaviour, not only presentation:

- **'Ingat saya' checkbox on `/login`: APPROVED.** `LoginController@login` already reads `remember` (`$request->filled('remember')`); the form gains one input `remember` (value `1`). No controller change.
- **Neutral message on `/forgot-password` (no account enumeration): NOT APPROVED.** `ForgotPasswordController` keeps its current messages ('email tidak ditemukan …' on failure). Recorded as a known limitation in the Phase 6 report.
- **Live registration test: APPROVED.** Throwaway student and company accounts may be created through the forms on the local test database (mail redirected to the `log` driver through the process environment; `.env` unchanged) and deleted afterwards. Institution registration is tested only by feature tests (it uploads KTP/NPWP to Supabase Storage).
- **RN-16 / D-38 (consent label of the `terms` checkbox on `/register/student`): ANSWERED.** 'Saya menyetujui syarat dan ketentuan serta kebijakan privasi Student Go.' without links (the Terms and Privacy pages do not exist; the old links pointed to `#`). Checkbox `id`/`name` `terms` unchanged. Links are added when the pages exist.
- **RN-17 / D-39 (example passwords in the password-rules block): ANSWERED.** Remove every example password; show only the rules (min. 8 characters, upper and lower case, a number, a symbol), checked live while typing.
- **Success state after institution/company registration: ANSWERED.** Replace the blocking `alert()` with an in-page status panel that shows the server's message and offers 'Masuk' and 'Ke beranda' (the JSON `redirect_url`). JSON responses and server redirects are unchanged.
- **RN-24 / D-40 (product name in auth emails and auth flash messages): ANSWERED (2026-10-31, Phase 6).** The user: 'rubah jadi student go'. 'Karsa' → 'Student Go' in the subjects of `VerifyEmailMail`, `InstitutionRegistered`, `InstitutionApproved`, `InstitutionRejected`, in the three institution email views (the tagline 'Karya Untuk Bangsa' removed, as RN-02; the header subtitle now reads 'Platform Digital untuk Kuliah Kerja Nyata'), and in the flash/JSON messages of `RegisterController` (student) and `EmailVerificationController`. Not changed: `APP_URL` (`karsa.railway.app` in email links; `.env`/deploy), `MAIL_FROM_NAME` and the default Laravel password-reset email (English, header from `APP_NAME`), and 'Karsa' outside authentication (chatbot, storage bucket name, certificate service under D-32).

### Note on Phase 17 data fixes (2026-10-31)

Approved by the user in the Phase 17 session after the first report ('1. perbaiki, 2. perbaiki, 3. tambahkan'). They change behaviour or data, not only presentation:

- **Application status `reviewed` vs `under_review`: FIXED.** The `applications.status` enum is `pending, reviewed, accepted, rejected`; institution code compared with `under_review`, a value that is never stored, so `reviewed` applications (31 in the seed) could not be accepted or rejected. `Institution\ApplicationReviewController` (review, accept, reject, bulk action, index count), `AnalyticsService` (counts) and `Institution\DashboardController` now compare with `reviewed`; the array key `under_review` is kept so the views read the same variables. Institution views `applications/index` (filter option value `reviewed`, label 'Ditinjau') and `applications/show` compare with `reviewed`. `helpers.php` `status_badge_color`/`status_label` gain a `reviewed` entry (additive). Student code already used `reviewed` and was not touched. Query string: `?status=under_review` → `?status=reviewed` (parameter name unchanged).
- **Seeded institution verification: FIXED ('campur').** `DummyDataSeeder` now writes `is_verified`, `verification_status` and `verified_at` together: every 16th institution stays unverified (`false` / `pending_verification` / null), the rest are verified (`true` / `active` / now). The local database was corrected the same way (backup `reports/phase17/db-backup-institutions-verification.csv`, ids and status columns only): 75 × `active` with `verified_at = created_at`, 4 × `pending_verification` with `is_verified = false` (#16, #32, #48, #64).
- **Report due date: ADDED (option 'tanda wajib laporan di milestone').** Additive migration `2026_10_31_000001_add_requires_report_to_project_milestones_table` (`boolean requires_report default false`), run alone with `--path` (62 migrations). The institution ticks 'Wajib laporan' when adding or editing a milestone on `/institution/projects/{id}/manage` (existing routes and forms; new field `requires_report`); the milestone list shows 'Wajib laporan'; the dashboard shows 'Laporan jatuh tempo: <milestone> · <date>' for the next not-completed milestone so marked. Existing milestones are `false` (not guessed). Students do not see the flag yet (student workspace, Phase 11).
- **Found while testing the above: FIXED.** `ProjectManagementController@updateMilestone` and `@deleteMilestone` called `ProjectService::updateProjectProgress()`, which does not exist, so every milestone edit or delete answered HTTP 500 after the write. Both now call `Project::updateProgress()` (the model method the student side and `ProjectService` already use).

### Note on Phase 8 decisions (2026-10-31, student project discovery)

Asked and approved in the Phase 8 session (Session A) because they touch a controller, data or behaviour, not only presentation:

- **D-14: ANSWERED**: seed images in the public bucket `sg-storage`; see the ANSWER under D-14.
- **Application status per problem on the list: APPROVED.** `Student\BrowseProblemsController@index` gains one read query (`$applicationStatuses`: the logged-in student's application status for the problems on the current page), shown as the shared status badge (D-02) in the row. Nothing else in the controller changed.
- **Regency filter: FIXED (approved).** The old page JS fetched `/student/browse-problems/get-regencies`, which is not a route (it fell through to `browse-problems/{id}`), so the Kab/Kota select never filled. The view now calls the existing route `student.problems.get-regencies` (`/student/problems/api/get-regencies`), cancels the previous request, shows a loading state and announces the result (`aria-live`). Backend unchanged.
- **Map: APPROVED.** The duplicate 'Filter Peta' overlay is removed (the page filters already filter the map data); popup text is HTML-escaped (the old popup inserted titles and names unescaped); package pin `assets/illustrations/motifs/map-pin.svg`; markers open from the keyboard. Leaflet 1.9.4 + markercluster 1.5.3 (AD-08), all-results data, province coordinates and `mapViewActivated` are kept.
- **Sorting: no new server sort (D-26 stays open).** Only the four existing `sort` values (`latest`, `deadline`, `popular`, `oldest`); the 'Tenggat' column header toggles `sort=deadline`.
- **ANSWERED (b), FIXED (2026-10-31): `is_primary` in `Student\DashboardController`.** The Phase 7 dashboard eager-loads recommended problems' `images` with `where('is_primary', true)`, but `problem_images` has no such column (it has `is_cover`). SQLite treats the unknown double-quoted identifier as a string, so tests pass; on PostgreSQL the dashboard would answer 500 as soon as a recommendation exists (today the list is always empty, so it is latent). Options were (a) use `is_cover`; (b) drop the unused `images` eager load. The user chose (b): the eager load is removed (the dashboard view never read `images`); Phase 7 tests 9/9 and the live dashboard on PostgreSQL loads for students #85, #86, #88.

### Note on Phase 18 behaviour fixes (2026-10-31, institution problem management)

Asked and approved in the Phase 18 session (Session B), each through a question with a recommended option, because they change behaviour, not only presentation. Validation rules, `update()`, `destroy()`, routes and input names are unchanged.

- **`address` on the create form: FIXED.** The create form has always posted `address`, but `problems` has no such column and `store()` did not validate it, so the text was silently dropped. `Institution\ProblemController@store` now saves a non-empty `address` as `detailed_location` when `detailed_location` itself is empty. No migration; the input name is unchanged. The field is labelled 'Alamat atau detail lokasi', with the help text 'Disimpan sebagai detail lokasi masalah.' Verified live: draft #78 stored 'Balai Desa, Jl. Raya Desa No. 12' as `detailed_location` (then deleted, see the Phase 18 report).
- **`sort=most_applied`: FIXED.** The list's sort select sent `most_applied`, which the controller did not know, so it fell back to 'latest'. `most_applied` is now a `case` alias of the existing `applications` sort (`applications_count desc`). The parameter name `sort` and the other values (`latest`, `oldest`, `title`, `applications`, `views`) are unchanged.
- **Regency list on the edit form: FIXED.** The old edit view fetched `/institution/problems/regencies/{id}`, which is not a route (it always answered 404), so changing the province on the edit form emptied the regency select. Create and edit now share `sgRegions` (Phase 6, `auth.js`) on the existing public endpoint `/api/regencies/{id}`: the previous request is aborted, a sequence guard drops late answers, a loading state disables the select, the result or the failure is announced (`aria-live`), and a 'Coba lagi' button retries.
- **Regencies after a failed submit (create): ADDED, presentation support.** `create()` passes the regencies of `old('province_id')`, so after a validation error the regency select still offers the old province's regencies, with the old choice kept. The old view lost the list until the province was changed again.
- **Accepted count from applications: presentation.** The list and detail show 'N diterima' from `withCount(applications where status = accepted)` (list) or the loaded applications (detail). The `accepted_students` counter is 0 for all 77 seeded problems although accepted applications exist. The counter itself is not changed: `ApplicationReviewController@accept` still checks slots against it (Phase 19).
- **Not changed (recorded):** `scope`, `village` and `detailed_location` are validated by `store()` but have no input on the create form (kept as before; the edit form has them). `store()`/`update()` call `DB::rollBack()` in the validation-error branch before any `beginTransaction()`: harmless in production, but in tests it rolls back the `RefreshDatabase` transaction, so a feature test must not make another request after a failed submit.

### Note on Phase 9 (2026-10-31, problem detail)

- **Rail state source:** `Student\BrowseProblemsController@show` gains two read queries: the logged-in student's own application (`id`, `status`, `applied_at`) and the number of accepted applications for the problem. The rail shows the real status (D-02 words); eligibility stays in `ApplicationController@create` (status `open`, deadline, one application per student).
- **OPEN (asked in the Phase 9 report): full quota.** `ApplicationController@create`/`@store` never check quota, and `problems.accepted_students` is 0 on every problem while 259 applications are accepted (the counter is out of sync; the institution accept action still checks that counter). Phase 9 therefore shows 'Kuota sudah terisi: N dari M mahasiswa diterima' as information and keeps the apply link (no behaviour change). Options: (a) keep as is; (b) block applying when accepted ≥ quota (behaviour change in `ApplicationController`, belongs to Phase 10 or a fix phase); (c) resync `accepted_students` from `applications` first (data change, needs approval).

### Note on Phase 19 behaviour fixes (2026-10-31, institution review and monitoring)

Asked and approved in the Phase 19 session (Session B) through two questions, each with a recommended option; the user chose the recommended option every time. Routes, HTTP verbs, validation rules and field names are unchanged.

- **Saving an institution review: FIXED.** `POST /institution/reviews` always failed for two reasons. The create form sent no `project_id` (its action was `route('institution.reviews.store', $id)`, which only appends a query string), so `store()` answered 404. And `store()` called `ReviewService::createInstitutionReview()`, which is the student-to-institution method and takes different arguments. The form now posts a hidden `project_id`, and `store()` calls the existing `ReviewService::createReview($projectId, $institutionId, $data)`. That method writes a review of type `institution_to_student` and sets `projects.rating`, `institution_review` and `reviewed_at`.
- **Notifications on accept and reject: ADDED.** Before, deciding on an application created no notification, although `NotificationService::applicationAccepted()` and `applicationRejected()` existed and were never called. `Institution\ApplicationReviewController@accept` and `@reject` now call them after the commit. These are in-app notifications only, with no email. A notification failure is logged and does not undo the decision. Verified live: one notification of each type, with the reject notification carrying the feedback text.
- **Quota check on accept: FIXED.** The slot check now counts the problem's applications with status `accepted` instead of trusting the `problems.accepted_students` counter, which is 0 in the seed although accepted applications exist. The counter is still incremented as before; it is not resynchronised (see 'Note on Phase 9' for the student-side display of the same counter).
- **Institution dashboard 'Menunggu ulasan': FIXED (Phase 17 code).** Completed projects count as reviewed when a review of type `institution_to_student` exists, which is the same rule as `ReviewController@create`. Before, the dashboard used `projects.reviewed_at`, which is empty for all 195 seeded completed projects although each one has a review, so the dashboard listed six 'to review' projects whose review form then refused to open.
- **Project stage meter source: DECIDED.** The projects table and detail page show 'x dari y tahap' from milestone statuses, matching `Project::updateProgress()` and the old detail page. The stored `progress_percentage` (which differs from the milestones for 62 of 259 projects) is not shown and not changed.
- **Rejection reason: KEPT, labelled as it is.** `reject()` still requires `feedback` and `rejection_reason`. `applications` has no `rejection_reason` column and the attribute is not fillable, so the reason is validated but not stored. The form's help text says so. No migration was made; this is listed as an open item.
- **Applying D-04 (already answered) on the application detail.** The old institution application page printed the student's email and phone. D-04 says institutions receive no contact values in the HTML, so the designed detail shows the campus, major, semester, the application content and a link to the public profile (which enforces D-04 itself), but no email or phone.

