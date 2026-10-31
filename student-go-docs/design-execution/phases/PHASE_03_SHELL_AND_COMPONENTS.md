# PHASE 03 — Shared Shell & Components

> Source of truth for status is `student-go-docs/design-execution/PHASE_TRACKER.md`. This file is the work order for exactly one phase. Read `CLAUDE.md` and `student-go-docs/design-execution/EXECUTION_ROADMAP.md` first, execute only this phase, update the tracker, report, then stop.
> Evidence labels: VERIFIED / OBSERVED / INFERRED / UNKNOWN / BLOCKED (definitions in `DISCOVERY_REPORT.md` section 1).

| Field | Value |
|---|---|
| Phase | 3 — Shared Shell & Components |
| Group | Foundation |
| Hard dependencies (must be COMPLETE) | Phase 2 (Brand Identity & Design System) |
| Soft dependencies (recommended first) | Phase 1 (Critical Fixes & Data Integrity) |
| Decisions that gate this phase | D-01 Product name, wordmark and assistant name, D-02 Language policy and copy deck, D-17 Approve the asset package as the visual foundation, D-20 Delivery of JS/CSS libraries, D-25 Defect fixes that change HTTP behaviour, D-29 Where the asset package CSS lives and how it is wired, D-32 Certificate assets (templates, Pinyon Script, Poppins) |
| Effort estimate (planning guess, not a commitment) | XL |
| Roles touched | All |
| Audit references | Audit C, F, H, I; J6, J8, J10, J11, J17, J20, J23, J24, J25, J26, J37; screens S26, I10, C09 (nav, avatar menu) |
| Tracker status at planning time | NOT_STARTED |

## A. Phase overview

**Goal.** Build the one shell and the shared Blade components every page will reuse: public layout, app layout (role-aware navigation, bell, avatar menu), auth layout, footer, page header, status badge, summary row, filter bar, table, list, record card, empty state, form field, upload, stepper, pagination, toast, confirm dialog, tabs, drawer, definition-list rail, avatar, breadcrumb. Fix document-level accessibility: `lang`, titles, skip link, focus ring, accessible names.

**In scope**

- Layouts: public (`P`), authenticated app (`A`), auth (`Auth`), error shell hook for Phase 26. Naming follows the repository's own convention found in Stage One.
- Navigation in Indonesian per D-02: student (Dashboard, Telusuri Masalah, Aplikasi Saya, Proyek Saya, Repositori, Lowongan), institution (Dashboard, Masalah, Aplikasi, Proyek, Ulasan), company (Dashboard, Talenta, Lowongan, Lamaran, Talenta Tersimpan); bell with accessible name and count, avatar menu with role badge, mobile menu including notifications (J25).
- Footer with contrast-safe text (3.67:1 today) and a real Kebijakan Privasi destination (content from D-23; until it exists the link is hidden, not '#').
- `<title>` pattern `Page — Student Go`, `<html lang="id">`, skip link, global `:focus-visible` ring, one h1 per page rule enforced in the page header component.
- Removal of Boxicons, Font Awesome and the old Google Font links (D-20), after confirming the two `fa-` icons on `/student/jobs/saved` are replaced.
- Shared status mapping (value to label, icon, tone) implementing the D-02 vocabulary in exactly one place.

**Out of scope**

- Restyling page bodies (later phases); AOS removal is done on the homepage in Phase 4 (the only place it is used).
- New JavaScript frameworks; Alpine stays.
- New npm packages.

**Evidence basis.** The audit (OBSERVED, 2026-10-28, port 8001) plus a read-only look at the running app on 2026-10-30. No repository source was read when this file was written; every statement about templates, controllers, models, middleware and tests is UNKNOWN until Stage One (Phase 0) fills `DISCOVERY_REPORT.md` section 12.

## B. Pages and routes

| Route pattern | Role | Audit status | Evidence | Notes |
|---|---|---|---|---|
| logout (path and verb UNKNOWN) | All | Used by the audit | INFERRED | The audit logged out; the route is not in the inventory. Normally a POST form in the nav menu. |

**Audit screenshot IDs for this phase** (audit section L; OBSERVED). Page data below is quoted or summarised from the audit; the screenshots themselves are in the PDF, not in this repository.

| ID | Page | URL | Keep (audit) | Design direction (audit) | Backlog |
|---|---|---|---|---|---|
| S26 | State interaksi: menu avatar terbuka | /student/dashboard (menu avatar terbuka) | Dropdown sebagai overlay berbayang adalah pemakaian bayangan yang benar. | Menu akun standar dengan badge peran kecil (Mahasiswa/Instansi/Perusahaan) di bawah nama. | J8, J11 |

## C. Existing implementation

- [OBSERVED] Three shells: public (top nav + dark footer), authenticated (role-specific top nav, bell, avatar menu + the same footer), auth (full screen, no nav). Roles use different accent colours, header styles and nav languages; company nav is fully English (audit F).
- [VERIFIED live, public nav] `About Us`, `Contact`, `Login`, `Register`; footer links `Beranda`, `Tentang Kami`, `Kontak`, `Kebijakan Privasi` (to `#`). `<html lang="en">` on `/`, `/about`, `/contact`; `lang="id"` on `/login` and `/register`.
- [OBSERVED] 59 `outline-none` and 0 `:focus-visible` rules; the bell is announced as '0'; the mobile menu has no notifications entry and 'Wishlist' is misaligned; sticky nav; floating scroll-to-top button using an undefined `bg-primary-600`.
- [VERIFIED live] Alpine present on `/`; `<style>` blocks inline (2 on `/`); two Google Fonts stylesheet links, Font Awesome, Leaflet, AOS, Boxicons, Alpine from CDNs.
- [OBSERVED] Cross-role access silently redirects (`/institution/*` and `/company/*` to `/student/dashboard` for students; other roles to their own dashboard).

**Repository facts for this phase: UNKNOWN until Stage One.** Locate and record in `DISCOVERY_REPORT.md` section 12 before editing: layouts (`layouts/auth.blade.php` is VERIFIED; find the others), nav and footer partials, any `resources/views/components`, where the bell count and dropdown (5 latest) come from, role detection (middleware, helper), existing Alpine data for the menu, existing toast/modal code, view composers.

## D. Problems found (audit findings)

| Backlog ID | Finding | Evidence |
|---|---|---|
| J11 (P1) | No visible keyboard focus; unnamed controls (bell '0', unlabelled filters); no `aria-expanded` on menus. | OBSERVED |
| J10 (P1) | Footer copyright 3.67:1; gray-400 text. | OBSERVED |
| J25 (P2) | Mobile menu lacks notifications; 'Wishlist' misaligned. | OBSERVED |
| J8 (P1) | Mixed language and status words; company nav entirely English. | OBSERVED |
| J20 (P2) | Tab title starts with 'Laravel'; footer privacy link '#'. | VERIFIED |
| J24 (P2) | Boxicons unused, Font Awesome used twice, AOS only on the homepage, empty JS bundle. | OBSERVED |
| J17 (P2) | Duplicated components (headers, uploads, paginators, card anatomies, placeholders). | OBSERVED |
| J23 / J26 / J37 | Hover scale/translate effects; emoji icons; send icon resembling a warning mark. | OBSERVED |

## E. Approved design direction

**Approved by the user (binding)**

- Hybrid direction (AD-04): Ruang Kerja workbench foundation; Catatan Lapangan editorial layer only where reading and storytelling matter. This phase uses none of it unless stated below.
- Principles (AD-05) and functional preservation (AD-06). Tokens, fonts and components below come from the asset package and are PROPOSED until D-17 is approved; existing assets are reconciled under AD-09 (section H); D-16 records that reference documents B and C were not found.

**Proposed for this phase** (audit sections I and L, adapted; status PROPOSED until the user approves the gating decisions)

- One top navigation plus avatar menu for all roles; the role is shown by a small role badge under the user's name in the menu (S26 direction) and nowhere else. No role-coloured buttons.
- Page header component: breadcrumb or back link, h1, one-line description, primary action on the right, no photo. Used by every app page.
- Record patterns: table (sticky header, sortable columns, row action at the end; stacked rows below 640 px, never cropped), divided list for people and notifications, card only in an optional gallery view.
- Feedback: inline validation after blur or submit; toast after save/apply/cancel; confirm dialog with focus lock and Esc for destructive actions.
- Document baseline: `lang="id"`, titles, skip link, `:focus-visible`, accessible names, `aria-expanded`, `aria-live` regions for the regency loader and notification count.

**Guardrails that bind this phase** (audit section H, numbered as in `EXECUTION_ROADMAP.md`): 1, 3, 4, 5, 6, 7, 8, 9.

## F. Implementation tasks (do in order)

3.1. Gate check: D-01 name, D-02 copy deck (nav labels, status words), D-17, D-20, D-25. List the approved copy deck in the phase report before coding.

3.2. Using the Stage One layout map, create the public, app and auth layouts on top of the Student Go CSS. Keep the old layouts until every page has moved (strangler approach), or replace in place if the repository has a single layout per shell.

3.3. Head: `<html lang="id">`, title pattern, favicon links from `public/assets/brand`, font preloads for the two fonts used above the fold, the Vite directive for the app CSS/JS; keep the Vite input list consistent so `resources/css/auth-institution.css`-style missing-manifest errors cannot recur. Logo: follow D-01/D-18. If Student Go was chosen, reference `student-go-logo.svg` (on-dark variant on dark surfaces); if Karsa stays, keep `karsa-logo.png` or its optimised derivative. Never both in one header, and never delete the PNG. Fonts: remove the Google Fonts `<link>` tags only from the site layouts, and only after confirming that no certificate view or PDF template depends on them (D-32); Pinyon Script and Poppins stay available to the certificate feature. Favicon: link the package set; do not overwrite `public/favicon.ico` unless the user approved it.

3.4. Navigation: build the role-aware nav (desktop and mobile) with the D-02 labels; bell with `aria-label` that includes the count, `aria-live` count update, dropdown of the 5 latest; avatar menu with name, role badge, profile link, wishlist (student), logout form (POST, CSRF) - same endpoints as today.

3.5. Footer: neutral dark or light footer with contrast >= 4.5:1; links Beranda, Tentang Kami, Kontak; privacy link only when the page exists (D-23).

3.6. Shared status component: one mapping from stored status values to label/icon/tone per D-02 (application, problem, verification, job, applicant stage). Stored values come from Stage One; never invent a status.

3.7. Build the components in section G as Blade components with documented props and every state (default, hover, focus, active, selected, disabled, loading, success, warning, error). Add a dev-only gallery page or the existing `docs/styleguide.html` equivalent; do not add a production route unless the user approves.

3.8. Replace the two Font Awesome icons and any emoji used as icons with sprite icons; remove Boxicons and Font Awesome CDN tags; verify no page references them (search views and the live network panel).

3.9. Remove the floating scroll-to-top dependency on `bg-primary-*` (use a token class) or drop the control if unused.

3.10. Smoke-test every role's home page and one page per route family in the new shell without restyling page bodies; confirm navigation links and logout still work.

3.11. Update the tracker and report with the component list, props table and any page that could not move to the new shell.

## G. Components

| Component | Source | Notes |
|---|---|---|
| Public layout, app layout, auth layout | to build (uses package CSS) | Naming per repo convention. |
| Top nav (desktop/mobile), bell + dropdown, avatar menu + role badge | to build | Accessible names; `aria-expanded`; mobile includes notifications. |
| Footer | to build | Contrast-safe. |
| Page header | package `.sg-page-header` | Blade wrapper; enforces one h1. |
| Status badge / mapping | package `.sg-status` | Single source of vocabulary. |
| Summary row | package `.sg-summary` | Replaces stat tiles. |
| Filter bar + mobile drawer | to build | Sticky 'Terapkan' in the drawer. |
| Table, divided list, record card (gallery only) | package `.sg-table`, `.sg-list` | Responsive rules (stack below 640 px). |
| Empty state | package `.sg-empty` + 5 SVG spots | Keep the current pattern; add role-specific next step. |
| Form field set, upload (dropzone + file chip), stepper | package `.sg-field`, `.sg-dropzone`, `.sg-stepper` | One of each. |
| Pagination (stable) | to build | One paginator; load-more only for feeds. |
| Toast, confirm dialog, tabs/segmented control, breadcrumb, definition-list rail, avatar (initials), role badge, skeleton/loading | to build | No shadow except overlays. |

## H. Assets (Source A existing `public/`, Source B custom package, Source C usage)

> Policy AD-09. Three sources, none disposable: **A** existing assets in `public/`, **B** the custom package `student-go-assets/`, **C** `resources/` (shows what is really used). Every asset has one decision: Retain, Reuse, Adapt, Replace or Deprecate. Originals are never deleted, overwritten, moved or renamed by this phase; copying anything into a production directory happens only if a task above says so. Full inventory, rights and integration map: `student-go-docs/design-execution/ASSET_INVENTORY.md`. Evidence labels as in `DISCOVERY_REPORT.md` section 1.

### H.1 Source A: existing application assets that matter in this phase

| Asset | Decision | What happens in this phase | Evidence |
|---|---|---|---|
| `karsa-logo.png` | Retain / Replace (conditional) | Header and footer logo. Replaced in templates by `student-go-logo.svg` only if D-01/D-18 choose Student Go; otherwise adapted. The PNG stays on disk either way. | VERIFIED |
| `favicon.ico` (0 bytes) | Replace | Link the package favicon set from the layout `<head>`. Do not overwrite `public/favicon.ico` unless this phase's report records explicit approval. | VERIFIED |
| `robots.txt` | Retain | No change. | VERIFIED |
| Certificate fonts and templates | Retain | When removing the Google Fonts `<link>` from site layouts, first confirm no certificate view includes the same link or layout (D-32). | UNKNOWN / BLOCKED |

### H.2 Source B: custom package assets available for this phase

**Original SVG, brand, fonts, icons and CSS (made for this project; in the package; licences in `ASSET_INVENTORY.md` section 2)**

- student-go-logo.svg / on-dark and symbol files for header/footer; favicon set; icon sprite and `icons.json`; empty-state SVGs referenced by the empty-state component.

### H.3 Reconciliation for this phase

Overlap 1 (logo) and 2 (favicon) in ASSET_INVENTORY.md section 11. Never ship the Karsa PNG and the Student Go SVG in the same header.

Overlap comparisons that touch this phase are in `ASSET_INVENTORY.md` section 11; where a Source A asset and a Source B asset serve the same purpose, that table gives the starting recommendation and this phase re-checks it against the real page.

### H.4 Native UI (no asset; plain HTML/CSS)

- Nav, footer, menus, dialogs: plain HTML/CSS with Alpine for state.

### H.5 Do not touch in this phase

- Certificate templates and the Pinyon Script and Poppins fonts (and whatever generates certificates): UNKNOWN location (D-32). Do not remove, rename, re-encode or restyle them, and do not remove a Google Fonts link that a certificate view depends on.
- Student profile photographs and uploads, institution/company logos, verification documents: never moved or re-encoded; never shown in a new public place (D-04, D-31).
- All photographs in `public/` (unused after this phase's shell is built, still on disk).

## I. Dependencies

- **Phases:** hard — Phase 2 (Brand Identity & Design System); soft — Phase 1 (Critical Fixes & Data Integrity).
- **Decisions:** D-01 (Product name, wordmark and assistant name), D-02 (Language policy and copy deck), D-17 (Approve the asset package as the visual foundation), D-20 (Delivery of JS/CSS libraries), D-25 (Defect fixes that change HTTP behaviour), D-29 (Where the asset package CSS lives and how it is wired), D-32 (Certificate assets (templates, Pinyon Script, Poppins)). A gating decision that is still OPEN in `DECISIONS_AND_OPEN_QUESTIONS.md` means the dependent tasks stop and the agent asks the user; it does not guess.
- **Packages:** None new. Alpine stays (CDN or self-hosted per D-20); Boxicons, Font Awesome removed.
- **Data / environment:** Accounts for each role to verify navigation; no special data.

## J. Functionality preservation

The global rules in `CLAUDE.md` section 7 apply in full. For this phase specifically:

- Every nav destination keeps its route; logout stays a POST form with CSRF.
- Cross-role redirect behaviour is unchanged.
- Notification count and the 5-latest dropdown keep working with the existing endpoints/data.
- Pages not yet restyled must still render correctly inside the new shell.
- Do not alter controller output beyond passing existing variables.

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

- For each role, click every nav item and confirm HTTP 200 and the same destination as before.
- Keyboard walk of the nav and avatar menu at 1440 and 390 px: focus visible, Esc closes, `aria-expanded` toggles.
- Screen-reader label for the bell reads a meaningful name with the count.
- Search the rendered HTML of ten pages for `bx-`, `fa-`, `boxicons`, `font-awesome`, `data-aos` after removal (expect none except homepage `data-aos`, which Phase 4 removes).
- Verify `<title>`, `lang`, favicon links on one page per route family.
- Component gallery: every component in every state at 390 and 1440 px, no overflow.

*Test commands are not listed here because none could be verified without the repository. `CLAUDE.md` section 4 lists candidate commands that Stage One must confirm.*

## L. Acceptance criteria

The phase is COMPLETE only if every box can be checked with evidence in the phase report. Otherwise the status is PARTIAL (say which boxes failed) or BLOCKED (say what unblocks it).

- [ ] Every authenticated page renders inside one app layout with role-appropriate navigation in Indonesian; the company nav contains no English labels.
- [ ] `<html lang="id">` and `<title>` ending in 'Student Go' on every layout; no tab title contains 'Laravel'.
- [ ] Keyboard focus is visible on every interactive element of the shell; the bell has an accessible name containing the unread count; menus expose `aria-expanded`.
- [ ] Mobile menu contains notifications and aligns every item.
- [ ] Footer text contrast >= 4.5:1.
- [ ] Boxicons and Font Awesome CDN tags are gone and no view references their classes; the two former `fa-` icons are replaced.
- [ ] A single status mapping drives every status badge in migrated pages; statuses read as Menunggu, Ditinjau, Diterima, Ditolak, Dibatalkan (or the approved variant).
- [ ] All components in section G exist with documented states; no component uses shadow except overlays, no gradient on controls.
- [ ] No route, form action or controller behaviour changed.

## M. Risks and blockers

| Risk or blocker | Mitigation / what unblocks it | Blocks the phase? |
|---|---|---|
| A big-bang layout swap breaks un-migrated pages | Strangler migration: old and new layouts coexist until Phase 28 cleanup. | No |
| D-02 copy deck not approved | Stop before writing labels; ask. | Yes for labels |
| Hidden dependencies on Boxicons/FA classes in Blade or JS | Search views and JS; check network panel before removing the tags. | No |
| Alpine and Leaflet CDN decision (D-20) changes load order | Test every page that uses Alpine/Leaflet. | No |
| Components built without real data overflow later | Test with the 76-character name, 65-character title, 51-character email. | No |

## N. Expected deliverables

- Layout files, nav/footer partials, Blade components with props documentation.
- Status mapping file and copy deck (as approved).
- Updated head/asset includes; removal of unused CDN tags.
- Component gallery or documented use of `docs/styleguide.html`.
- `student-go-docs/design-execution/PHASE_TRACKER.md`: set Phase 3 to COMPLETE, PARTIAL or BLOCKED with date, branch/commit (if the user asked for one) and a one-line note.
- A phase report in the chat: what changed (files), what was verified (with the evidence), what failed or was skipped and why, decisions needed from the user, and the exact next phase the roadmap allows. Then stop and wait; do not start another phase.
