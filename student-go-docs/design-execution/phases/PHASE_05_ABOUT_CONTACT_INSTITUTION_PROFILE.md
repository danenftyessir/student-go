# PHASE 05 — About, Contact, Public Institution Profile

> Source of truth for status is `student-go-docs/design-execution/PHASE_TRACKER.md`. This file is the work order for exactly one phase. Read `CLAUDE.md` and `student-go-docs/design-execution/EXECUTION_ROADMAP.md` first, execute only this phase, update the tracker, report, then stop.
> Evidence labels: VERIFIED / OBSERVED / INFERRED / UNKNOWN / BLOCKED (definitions in `DISCOVERY_REPORT.md` section 1).

| Field | Value |
|---|---|
| Phase | 5 — About, Contact, Public Institution Profile |
| Group | Public and authentication |
| Hard dependencies (must be COMPLETE) | Phase 3 (Shared Shell & Components) |
| Soft dependencies (recommended first) | Phase 1 (Critical Fixes & Data Integrity), Phase 4 (Homepage) |
| Decisions that gate this phase | D-19 Documentary photography, D-23 Content ownership for new or rewritten copy, D-26 Features the audit proposes that do not exist today, D-12 A working database for baseline and QA, D-30 Provenance and consent for existing photographs in `public/`, D-31 Personal photographs, student photos and identifying file names, D-33 Whether to download the 60 catalogue photographs at all, D-34 Photograph derivatives: format, sizes and location |
| Effort estimate (planning guess, not a commitment) | M |
| Roles touched | Public (guest), all roles (institution profile) |
| Audit references | G02, G03, S24, I11; J6, J8, J9, J11, J12, J18, J20, J23, J34, J35; audit D12 |
| Tracker status at planning time | NOT_STARTED |

## A. Phase overview

**Goal.** Make About a scrolling article, Contact a one-screen page with official channels and an FAQ, and the public institution profile a plain organisation page that tolerates 33 institutions with no problems and 76-character names.

**In scope**

- `/about`: convert the full-screen 7-slide carousel into a readable scrolling page (mission, problem solved with sources, how it works per role, SDGs supported, team). Photos as framed figures between paragraphs.
- `/contact`: header, official contact channels (email and WhatsApp exist today), FAQ; move the developer/team cards to About; no full-screen hero.
- `/institution/{id}`: header, about, facts grid, list of active problems, completed projects; honest empty state; wrapping rules for long names.
- Remove unsourced figures ('520K+ Mahasiswa KKN', '83K+ Desa Target', '100K+ Repository', '8,3 juta mahasiswa', '76%', '±Rp 1,2 triliun', '94% mahasiswa') unless the user supplies sources.
- Privacy policy page destination if the user supplies the text (D-23); otherwise the link stays hidden (Phase 3).

**Out of scope**

- A contact message form unless D-26 approves new backend work.
- Inventing team members, testimonials or statistics.
- Institution dashboard/profile for the logged-in institution (Phase 20).

**Evidence basis.** The audit (OBSERVED, 2026-10-28, port 8001) plus a read-only look at the running app on 2026-10-30. No repository source was read when this file was written; every statement about templates, controllers, models, middleware and tests is UNKNOWN until Stage One (Phase 0) fills `DISCOVERY_REPORT.md` section 12.

## B. Pages and routes

| Route pattern | Role | Audit status | Evidence | Notes |
|---|---|---|---|---|
| /about | Public | Inspected | OBSERVED; title VERIFIED | Full-screen 7-slide carousel, 'About Us' in English; <title> 'Laravel - Tentang Kami - Karsa' (VERIFIED). |
| /contact | Public | Inspected | OBSERVED; title VERIFIED | <title> 'Laravel - Contact Us - Laravel' (VERIFIED). |
| /institution/{id} | Anyone | Inspected (4), 79 fetched | OBSERVED; VERIFIED failing locally | On 2026-10-30 /institution/19 returns a QueryException (DB unreachable) (VERIFIED). 33 of 79 institutions have no problems (OBSERVED). |

**Audit screenshot IDs for this phase** (audit section L; OBSERVED). Page data below is quoted or summarised from the audit; the screenshots themselves are in the PDF, not in this repository.

| ID | Page | URL | Keep (audit) | Design direction (audit) | Backlog |
|---|---|---|---|---|---|
| G02 | Tentang Kami (About) — 7 slide | /about | Foto-foto lapangan KKN asli (mahasiswa di truk, cap tangan, jaket KKN) adalah aset paling autentik di seluruh aplikasi; pakai ulang di halaman cerita. | Ubah menjadi halaman artikel yang di-scroll: misi, masalah yang diselesaikan (dengan sumber), cara kerja per peran, SDG yang didukung, tim. Foto dokumenter dipakai sebagai gambar berbingkai di antara paragraf, bukan latar penuh. | J8, J9, J11, J23 |
| G03 | Kontak (Contact) | /contact | Dua kanal kontak (email + WhatsApp) relevan untuk pengguna Indonesia. | Halaman satu layar: header polos, kartu kanal kontak resmi, form pesan singkat, FAQ. Tanpa hero foto layar penuh. | J6, J8, J20 |
| S24 | Profil publik instansi (#19, punya masalah) | /institution/19 | Halaman publik per instansi sebagai tujuan tautan dari detail masalah. | Profil organisasi: header, tentang, daftar masalah aktif, proyek selesai. | J9, J12, J18 |
| I11 | Profil publik instansi (#1, tanpa masalah) | /institution/1 | Empty state yang jujur. | Sama dengan S24. | J9 |

## C. Existing implementation

- [OBSERVED] About is a full-screen 7-slide carousel readable only through arrows; 'About Us' title in English; English feature list ('Real-Time Updates', 'Managed by Experts'); photos of real KKN field scenes (students on a truck, hand prints, KKN jackets) are the app's most authentic assets (audit G02).
- [VERIFIED live] `<title>` of `/about` is 'Laravel - Tentang Kami - Karsa' and of `/contact` is 'Laravel - Contact Us - Laravel'; `<html lang="en">` on both; no forms and no inputs on either page.
- [OBSERVED] Contact: one-screen hero with an aerial campus photo, three developer cards with broken photos, two channels (email + WhatsApp), no official service contact.
- [OBSERVED] `/institution/{id}`: green banner header, five stat tiles, grid of problem cards with grey 'No Image' boxes; 79 institutions (1-79 render, 80+ are 404); 33 have no problems; names up to 76 characters truncated at about 25 characters on browse cards; email up to 51 characters overflowing grid cells on the logged-in profile.
- [VERIFIED 2026-10-30] `/institution/19` currently returns a QueryException locally (database unreachable), so the page cannot be viewed with data until D-12 is resolved.

**Repository facts for this phase: UNKNOWN until Stage One.** Locate and record in `DISCOVERY_REPORT.md` section 12 before editing: about and contact views (slider JS), the contact data source (config or hard-coded), the institution public controller (`show`) and view, what the five stat tiles count, link from problem detail to institution.

## D. Problems found (audit findings)

| Backlog ID | Finding | Evidence |
|---|---|---|
| J34 (P2) | About is a 7-slide carousel: content cannot be scanned, linked or read well by screen readers. | OBSERVED |
| J35 (P2) | Contact has developer cards with broken photos, no official service channel, and a full-screen hero. | OBSERVED |
| J9 (P1) | Unsourced marketing numbers on About. | OBSERVED |
| J6 (P1) | Photo heroes on About/Contact (200-370 px+ bands). | OBSERVED |
| J8 (P1) | English headings ('About Us', 'Contact Us'). | OBSERVED; titles VERIFIED |
| J12 / J18 / J19 | Institution page: 'No Image' placeholders; card grid for problems; long names clipped. | OBSERVED |
| J20 (P2) | Titles contain 'Laravel'/'Karsa'. | VERIFIED |

## E. Approved design direction

**Approved by the user (binding)**

- Hybrid direction (AD-04): Ruang Kerja workbench foundation; Catatan Lapangan editorial layer only where About is an editorial page and the institution profile may show one logo/photo if it exists.
- Principles (AD-05) and functional preservation (AD-06). Tokens, fonts and components below come from the asset package and are PROPOSED until D-17 is approved; existing assets are reconciled under AD-09 (section H); D-16 records that reference documents B and C were not found.

**Proposed for this phase** (audit sections I and L, adapted; status PROPOSED until the user approves the gating decisions)

- About = article page (audit L1): mission, the problem (with cited sources only), how it works per role, SDGs, team. Documentary photographs as framed figures with captions and credits between paragraphs; serif for the reading column (Catatan Lapangan).
- Contact = one screen: plain header, official channel cards, short FAQ. A message form is a new feature (D-26) and is not built without approval.
- Institution profile = standard organisation profile (shared with Phase 20/25): header with name (wraps fully), verified status, facts grid with `min-width:0` and wrapping, about text, active problems as a divided list/table, completed projects, honest empty state ('Belum ada masalah dipublikasikan').
- Stats on the institution page only if real and non-zero (guardrail 2); a one-line summary replaces five tiles.

**Guardrails that bind this phase** (audit section H, numbered as in `EXECUTION_ROADMAP.md`): 1, 2, 3, 6, 7, 9, 10.

## F. Implementation tasks (do in order)

5.1. Gate check: D-23 (copy and sources), D-19 (approved photos), D-26 (contact form), D-12 (data to view the institution page).

5.2. About: extract all current text and images from the carousel slides into a content inventory (read-only). Mark each claim as sourced, unsourced, or product fact; drop unsourced numbers unless the user supplies a source.

5.3. About: build the article layout with the Phase 3 public shell: h1, short intro, sections, framed figures from the existing About photographs (`mahasiswa-about.jpeg`, `handprints-about.jpeg`, `jaket-about.jpeg`, `about.jpg`; derivatives per D-34, rights per D-30) and, only if D-33 allows, `photography/about/*`, with caption and credit lines; keep the original photographs' meaning (no re-captioning that changes facts).

5.4. Contact: build header, channel cards (email and WhatsApp as they exist today; verify they are real before publishing), FAQ with real questions the user approves; relocate team cards to About only if photos and names are provided. Repair the two broken team-portrait references (A-01): `/profile_13523124.jpg` should point to the existing `.jpeg` file and `/profile_18223127.jpg` needs the user's file or an initials avatar; do not rename or delete any portrait file, and do not add a new public exposure (D-31).

5.5. Institution public profile: rebuild header and facts; wrap long names and emails (no truncation; show the full name in the header and a title attribute elsewhere); problem list rows with a link to the problem detail; hide stat tiles that are zero.

5.6. Empty institution (33 of 79): show the honest empty state with no media placeholder.

5.7. Verify with institutions 1 (no problems), 19 (has problems) and the one with the 76-character name (Sidoarjo) at 360, 390, 768 and 1440 px.

5.8. Remove carousel JavaScript if nothing else uses it; keep any content the user wants preserved.

5.9. Update the tracker and report with screenshots G02, G03, S24, I11 before/after.

## G. Components

| Component | Source | Notes |
|---|---|---|
| Article layout with `.sg-reading` and framed `.sg-figure` | package | Serif for long text. |
| Channel cards / contact list | page-specific | Plain bordered rows. |
| FAQ (disclosure list) | to build in Phase 3 or page | Native `<details>` preferred. |
| Organisation header + facts grid | Phase 3 definition list | Shared with Phase 20/25. |
| Problem list rows | Phase 3 list | Links preserved. |
| Empty state | Phase 3 | Institution has no problems. |

## H. Assets (Source A existing `public/`, Source B custom package, Source C usage)

> Policy AD-09. Three sources, none disposable: **A** existing assets in `public/`, **B** the custom package `student-go-assets/`, **C** `resources/` (shows what is really used). Every asset has one decision: Retain, Reuse, Adapt, Replace or Deprecate. Originals are never deleted, overwritten, moved or renamed by this phase; copying anything into a production directory happens only if a task above says so. Full inventory, rights and integration map: `student-go-docs/design-execution/ASSET_INVENTORY.md`. Evidence labels as in `DISCOVERY_REPORT.md` section 1.

### H.1 Source A: existing application assets that matter in this phase

| Asset | Decision | What happens in this phase | Evidence |
|---|---|---|---|
| `mahasiswa-about.jpeg`, `handprints-about.jpeg`, `jaket-about.jpeg`, `about.jpg` | Retain / Reuse / Adapt | The four About photographs become framed figures between paragraphs (audit: reuse the real KKN photos), with derivatives and Indonesian alt text. Rights and consent first (D-30). | VERIFIED |
| `contact.jpg` | Deprecate (usage) | The one-screen aerial hero is removed from Contact; the file stays. | VERIFIED |
| `profile_13523136.jpg`, `profile_13523124.jpeg`, `profile_18223127.jpg` (missing) | Retain / Adapt | Repair the two broken references (a template fix); the team moves to About as a simple list with valid photos or initials. Do not rename files; file names carry student IDs (D-31). | VERIFIED |
| Institution logos (uploads) | Retain | Public institution profile `/institution/{id}`: shown when present, initials otherwise. | UNKNOWN / BLOCKED |

### H.2 Source B: custom package assets available for this phase

**Original SVG, brand, fonts, icons and CSS (made for this project; in the package; licences in `ASSET_INVENTORY.md` section 2)**

- `motifs/survey-marks.svg`, `motifs/contour-field.svg` (sparingly); `empty-states/no-saved-projects.svg` or a text-only empty state; workflow SVGs for 'how it works'.

**Catalogue photographs (Source B: licence-checked, NOT downloaded; deferred by D-33; used only when no Source A photograph fills the slot)**

- `photography/about/*` (4 candidates): a fallback only for a slot the existing About photographs cannot fill, or for any existing photograph whose rights cannot be confirmed (D-30, D-33). The audit calls the real KKN field photos already in About the most authentic assets in the app.

### H.3 Reconciliation for this phase

Overlap 4 and 13. Real field photographs first; catalogue photos only as gap-fillers. The package's own rule applies to everything shown: stock is never presented as a Student Go project.

Overlap comparisons that touch this phase are in `ASSET_INVENTORY.md` section 11; where a Source A asset and a Source B asset serve the same purpose, that table gives the starting recommendation and this phase re-checks it against the real page.

### H.4 Native UI (no asset; plain HTML/CSS)

- FAQ `<details>`, text, dividers.

### H.5 Do not touch in this phase

- Student profile photographs and uploads, institution/company logos, verification documents: never moved or re-encoded; never shown in a new public place (D-04, D-31).
- All About and Contact originals, including `contact.jpg` and the unused portraits.

## I. Dependencies

- **Phases:** hard — Phase 3 (Shared Shell & Components); soft — Phase 1 (Critical Fixes & Data Integrity), Phase 4 (Homepage).
- **Decisions:** D-19 (Documentary photography), D-23 (Content ownership for new or rewritten copy), D-26 (Features the audit proposes that do not exist today), D-12 (A working database for baseline and QA), D-30 (Provenance and consent for existing photographs in `public/`), D-31 (Personal photographs, student photos and identifying file names), D-33 (Whether to download the 60 catalogue photographs at all), D-34 (Photograph derivatives: format, sizes and location). A gating decision that is still OPEN in `DECISIONS_AND_OPEN_QUESTIONS.md` means the dependent tasks stop and the agent asks the user; it does not guess.
- **Packages:** None.
- **Data / environment:** Institution records with and without problems (D-12, D-13).

## J. Functionality preservation

The global rules in `CLAUDE.md` section 7 apply in full. For this phase specifically:

- URLs `/about`, `/contact`, `/institution/{id}` unchanged; links from problem detail to the institution page keep working.
- Real contact channels stay real; do not invent a phone number, address or email.
- Institution public page remains available to guests and all roles.

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

- No horizontal scroll at six widths for each page.
- Institution pages for ids 1, 19, the 76-character name, and a 404 id (80).
- Heading outline: exactly one h1 per page, sections as h2.
- Screen-reader pass on About: content is linear and linkable (no slider needed).
- All images have meaningful alt text; credit lines present.

*Test commands are not listed here because none could be verified without the repository. `CLAUDE.md` section 4 lists candidate commands that Stage One must confirm.*

## L. Acceptance criteria

The phase is COMPLETE only if every box can be checked with evidence in the phase report. Otherwise the status is PARTIAL (say which boxes failed) or BLOCKED (say what unblocks it).

- [ ] About is a scrolling article with no carousel, no unsourced statistic, and one h1.
- [ ] Contact is one screen at 1440 px with official channels and FAQ and without a photo hero.
- [ ] The institution page shows the full institution name (including the 76-character one) without truncation and no email overflows its cell at 390 px.
- [ ] An institution with no problems shows an honest empty state and no 'No Image' box; no stat tile shows zero.
- [ ] All page titles end with 'Student Go' and `<html lang="id">`.
- [ ] No broken images on these pages.
- [ ] Every photograph has caption, credit and Indonesian alt text, has confirmed rights (D-30) and was approved in D-19; no team portrait renders as a broken image; no portrait file was renamed or deleted.

## M. Risks and blockers

| Risk or blocker | Mitigation / what unblocks it | Blocks the phase? |
|---|---|---|
| About content must come from the user | Draft only from product facts; ask for sources (D-23). | Partially |
| Contact form requested | New backend work; needs D-26 approval. | No |
| Database down so institution pages cannot be viewed | Resolve D-12 first. | Yes for the institution part |
| Photo rights for existing About images | Ask the user to confirm rights before reusing. | No |

## N. Expected deliverables

- Rewritten about, contact and institution-public views; removal of carousel script if unused.
- Content inventory table (claim, source status) in the phase report.
- Screenshots before/after for the four screen IDs.
- `student-go-docs/design-execution/PHASE_TRACKER.md`: set Phase 5 to COMPLETE, PARTIAL or BLOCKED with date, branch/commit (if the user asked for one) and a one-line note.
- A phase report in the chat: what changed (files), what was verified (with the evidence), what failed or was skipped and why, decisions needed from the user, and the exact next phase the roadmap allows. Then stop and wait; do not start another phase.
