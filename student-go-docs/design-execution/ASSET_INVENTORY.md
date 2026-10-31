# ASSET_INVENTORY — Student Go design (discovery, reconciliation and reuse)

> **Phase 0 update (2026-10-30):** Stage One G ran; section 1.5 has the verified `public/` list, usages, certificate assets and upload storage. Nothing was changed.
>
> **Status: DISCOVERY ONLY.** Written 2026-10-30 under AD-09. Only inspected, inventoried, compared and documented: nothing was deleted, overwritten, moved, renamed, copied into a production asset directory, replaced, generated, downloaded or integrated, and no integration file was changed. Originals stay until a later phase authorises integration or replacement.
> Evidence labels (`VERIFIED / OBSERVED / INFERRED / UNKNOWN / BLOCKED`) are defined in `DISCOVERY_REPORT.md` section 1. Read the limits in section 14 before relying on any row.

**The principle.** Do not start from zero. Do not ignore the existing `public/` library. Do not replace everything with the custom package. Use the best asset from both sources by quality, relevance, licensing and consistency, and preserve originals until a future phase authorises integration or replacement.

## 0. How to read this file

**Three sources, none disposable (AD-09)**

| Source | What | Reachable in this session? |
|---|---|---|
| A | Existing application assets in `public/` (photographs, logo, favicon, team portraits, build output), plus user uploads and certificate resources that live elsewhere | Partly: 27 files read through the running app (VERIFIED); uploads, certificates, the rest of `public/`: UNKNOWN / BLOCKED |
| B | Custom package `student-go-assets/` (design tokens, CSS, fonts, icons, brand, illustrations, motifs, 60 photo records, docs, scripts) | Yes, read recursively (124 files) and rendered |
| C | `resources/` Blade, CSS and JS: shows which assets are really used | No: BLOCKED (no repository). Evidence limited to rendered HTML, built Vite files and the audit |

**Decision vocabulary**

| Decision | Meaning here |
|---|---|
| Retain | Keep the file exactly where it is and keep serving it. No change. |
| Reuse | Use the asset in the design more or less as it is (possibly in a new place). |
| Adapt | Keep the original untouched and produce a derivative or a small change (resize, WebP, crop, strip metadata, merge CSS, fix a reference). |
| Replace | A better asset or approach serves the same purpose. The old file stays on disk until a later phase authorises removal; only the template reference changes, and only in that phase. |
| Deprecate | Stop using it in new UI. It is not deleted, moved or renamed. Physical archiving needs explicit approval in a later phase. |

**At a glance.** Source A: 29 records (27 files read through the running app plus the Vite build output; 1 referenced file is missing). Records carrying each decision label (a record can carry several): Retain 28, Reuse 10, Adapt 12, Replace 10, Deprecate 17. Source B: 124 files on disk (every asset file is Reuse or Adapt) plus 60 catalogue photographs that do not exist locally and are deferred (D-33). Decisions needed from the user: D-28 to D-35 (new) and the open items in `DECISIONS_AND_OPEN_QUESTIONS.md`.

**The five findings that change the plan**

1. The earlier plan and the package's INTEGRATION.md treated most existing images as removable. They are not: seven are genuine KKN field photographs that the audit calls the most authentic assets in the app, and the logo is the live brand asset. They are Retained, and the best are Reused.
2. Rights and consent for every existing photograph are undocumented (D-30). Presence in the repository is not permission to place them more prominently.
3. Two of three team portraits on `/contact` are broken today (a `.jpg`/`.jpeg` mismatch and a missing file), and the file names carry student ID numbers (D-31).
4. Certificate assets (templates, Pinyon Script, Poppins) could not be located and appear in neither the audit nor the public paths probed; they are protected and separate from website typography (D-32).
5. The package's CSS is at `student-go-assets/resources/css/student-go/`, not under `public/`; and every SVG/PNG in the package carries an embedded C2PA block that inflates it (D-29, D-35).

## 1. Existing `public/` inventory (Source A)

The complete inventory has the requested fields, split over three tables per asset: identity (1.1), usage and decision (1.2), and assessment (1.3). Every file below was found by name through the running app; the folder was not listed (section 14).

### 1.1 Identity

| # | Asset (file) | Original location | Category | Dimensions (px) | Size | Exists? |
|---|---|---|---|---|---|---|
| 1 | `karsa-logo.png` | `public/karsa-logo.png` | Brand logo (raster) | 1430x566 | 272 KB | VERIFIED (HTTP 200, loaded in the browser) |
| 2 | `hero-background.jpg` | `public/hero-background.jpg` | Photograph (hero background) | 1280x720 | 85 KB | VERIFIED (HTTP 200, loaded in the browser) |
| 3 | `about.jpg` | `public/about.jpg` | Photograph (landscape) | 7360x4912 | 3,602 KB | VERIFIED (HTTP 200, loaded in the browser) |
| 4 | `mahasiswa-about.jpeg` | `public/mahasiswa-about.jpeg` | Photograph (documentary, people) | 4912x2760 | 718 KB | VERIFIED (HTTP 200, loaded in the browser) |
| 5 | `handprints-about.jpeg` | `public/handprints-about.jpeg` | Photograph (documentary, people) | 4912x2760 | 398 KB | VERIFIED (HTTP 200, loaded in the browser) |
| 6 | `jaket-about.jpeg` | `public/jaket-about.jpeg` | Photograph (documentary, object) | 4912x2760 | 687 KB | VERIFIED (HTTP 200, loaded in the browser) |
| 7 | `contact.jpg` | `public/contact.jpg` | Photograph (aerial) | 1280x720 | 94 KB | VERIFIED (HTTP 200, loaded in the browser) |
| 8 | `login-register-view.jpg` | `public/login-register-view.jpg` | Photograph (portrait, auth background) | 900x1600 | 142 KB | VERIFIED (HTTP 200, loaded in the browser) |
| 9 | `dashboard-student2.jpeg` | `public/dashboard-student2.jpeg` | Photograph (documentary, people) | 4912x2760 | 423 KB | VERIFIED (HTTP 200, loaded in the browser) |
| 10 | `network-student.JPG` | `public/network-student.JPG` | Photograph (documentary, people) | 4912x2760 | 1,098 KB | VERIFIED (HTTP 200, loaded in the browser) |
| 11 | `projects-student.jpeg` | `public/projects-student.jpeg` | Photograph (documentary) | 4912x2760 | 1,049 KB | VERIFIED (HTTP 200, loaded in the browser) |
| 12 | `repo-student.jpeg` | `public/repo-student.jpeg` | Photograph (documentary, people) | 4912x2760 | 1,195 KB | VERIFIED (HTTP 200, loaded in the browser) |
| 13 | `dashboard-student.jpg` | `public/dashboard-student.jpg` | Photograph (cityscape) | 1280x720 | 56 KB | VERIFIED (HTTP 200, loaded in the browser) |
| 14 | `dashboard-student3.jpg` | `public/dashboard-student3.jpg` | Photograph (portrait landscape) | 3827x5741 | 3,345 KB | VERIFIED (HTTP 200, loaded in the browser) |
| 15 | `application-student.jpg` | `public/application-student.jpg` | Photograph (aerial) | 1280x720 | 184 KB | VERIFIED (HTTP 200, loaded in the browser) |
| 16 | `search-network.jpg` | `public/search-network.jpg` | Photograph (aerial) | 1280x720 | 188 KB | VERIFIED (HTTP 200, loaded in the browser) |
| 17 | `company-profile.jpg` | `public/company-profile.jpg` | Photograph (corporate stock-style) | 1405x706 | 247 KB | VERIFIED (HTTP 200, loaded in the browser) |
| 18 | `company1.jpg` | `public/company1.jpg` | Photograph (corporate stock-style) | 1408x702 | 226 KB | VERIFIED (HTTP 200, loaded in the browser) |
| 19 | `company2.jpg` | `public/company2.jpg` | Photograph (corporate stock-style) | 1406x722 | 129 KB | VERIFIED (HTTP 200, loaded in the browser) |
| 20 | `company3.jpg` | `public/company3.jpg` | Photograph (corporate stock-style) | 1406x705 | 233 KB | VERIFIED (HTTP 200, loaded in the browser) |
| 21 | `company4.jpg` | `public/company4.jpg` | Photograph (corporate stock-style) | 1024x509 | 84 KB | VERIFIED (HTTP 200, loaded in the browser) |
| 22 | `company5.jpg` | `public/company5.jpg` | Photograph (corporate stock-style) | 1024x510 | 121 KB | VERIFIED (HTTP 200, loaded in the browser) |
| 23 | `KARA-background.png` | `public/KARA-background.png` | Decorative background (raster) | 2389x1319 | 1,763 KB | VERIFIED (HTTP 200, loaded in the browser) |
| 24 | `profile_13523136.jpg` | `public/profile_13523136.jpg` | Personal photograph (team portrait) | 1220x1184 | 238 KB | VERIFIED (HTTP 200, loaded in the browser) |
| 25 | `profile_13523124.jpeg` | `public/profile_13523124.jpeg` | Personal photograph (team portrait) | 990x1034 | 241 KB | VERIFIED (HTTP 200, loaded in the browser) |
| 26 | `profile_18223127.jpg` | referenced as `/profile_18223127.jpg`; no file found | Personal photograph (team portrait) | not found | — | NOT FOUND (referenced by `/contact`) |
| 27 | `favicon.ico` | `public/favicon.ico` | Brand (favicon) | 0 bytes | 0 KB | VERIFIED (HTTP 200, loaded in the browser) |
| 28 | `robots.txt` | `public/robots.txt` | Static file (not visual) | 24 bytes | 0 KB | VERIFIED (HTTP 200, loaded in the browser) |
| 29 | `build/assets/app-CDP40zpX.css, build/assets/app-34mOoJaZ.js, build/manifest.json` | `public/build/` | Vite build output | CSS 125,756 characters; JS 0 bytes; manifest 331 bytes | — | VERIFIED (HTTP 200, loaded in the browser) |

### 1.2 Usage, intended design usage, decision and target path

| Asset | Current usage | Evidence | Intended design usage | Decision | Target implementation path |
|---|---|---|---|---|---|
| `karsa-logo.png` | `<img>` in the header and footer of every guest page checked (`/`, `/about`, `/contact`, `/login`, `/register`); rendered at 40-48 px high. `alt` = 'Karsa - Karya Untuk Bangsa'. | VERIFIED (rendered HTML 2026-10-30) | Stays the live logo until the product-name decision (D-01) and brand-mark decision (D-18) are answered. If the name becomes Student Go it is replaced in templates by `student-go-logo.svg` (Source B) in Phase 3; if it stays Karsa it is adapted into an optimised SVG or 2x PNG. The slate-indigo of this logo is the source of the proposed brand colour `#405080`. | **Retain**; **Adapt** later; **Replace** in templates only after D-01/D-18 | Stays at `public/karsa-logo.png`. Any optimised derivative goes to `public/assets/brand/` under a new name (for example `karsa-logo.svg`); the original is not touched. |
| `hero-background.jpg` | Inline CSS `url(/hero-background.jpg)` on `/`: the homepage hero, stacked under seven radial gradients and white text (audit G01). | VERIFIED (inline style on `/`); layout detail OBSERVED (audit) | Not as a full-bleed hero background (audit guardrail 1 and G01 direction: a short typographic hero). Candidate for one framed, captioned documentary figure on the homepage only if its rights are confirmed (D-30); otherwise it stays unused after Phase 4. | **Retain**; **Deprecate** as hero background; **Reuse** only as a framed figure if rights confirmed | Stays at `public/hero-background.jpg`. A WebP derivative, if reused, goes to `public/assets/photography/field/`. |
| `about.jpg` | `<img>` on `/about` (the full-screen first slide in the audit's carousel). | VERIFIED (rendered HTML) | Reuse as a framed documentary figure on About or the homepage (no people in frame, so no consent issue) after rights are confirmed and after a derivative is made. Original stays. | **Retain**; **Adapt** (responsive WebP derivatives); **Reuse** as a framed figure | Original stays at `public/about.jpg`. Derivatives (for example 2400, 1600, 800 px WebP) go to `public/assets/photography/field/about-rice-terraces-{w}.webp`. |
| `mahasiswa-about.jpeg` | `<img>` on `/about`. | VERIFIED (rendered HTML) | Reuse: the most authentic image type in the app (the audit says to reuse the real KKN field photos in story pages). A framed figure with a Place - what is happening caption on About. | **Retain**; **Reuse**; **Adapt** (derivatives) | Original stays. Derivatives to `public/assets/photography/field/`. |
| `handprints-about.jpeg` | `<img>` on `/about`. | VERIFIED (rendered HTML) | Reuse as a framed figure (audit lists 'cap tangan' among the assets to reuse). | **Retain**; **Reuse**; **Adapt** | Original stays. Derivatives to `public/assets/photography/field/`. |
| `jaket-about.jpeg` | `<img>` on `/about`; the audit says it is the background of the SDG slide. | VERIFIED (rendered HTML); slide use OBSERVED | Reuse as a framed figure beside the SDG list, not as a background (audit slide 6). | **Retain**; **Reuse**; **Adapt** | Original stays. Derivatives to `public/assets/photography/field/`. |
| `contact.jpg` | `<img>` full-screen hero on `/contact` (`object-cover`). | VERIFIED (rendered HTML) | Not as a hero: the contact page becomes a plain page header (audit J35, G03). Low-priority candidate for a framed campus figure on About if rights are confirmed. | **Retain**; **Deprecate** as hero | Stays at `public/contact.jpg`. |
| `login-register-view.jpg` | Inline CSS `url(/login-register-view.jpg)` on `/login` (VERIFIED); the audit also lists it as an auth hero. | VERIFIED (`/login`); other auth pages BLOCKED (three return HTTP 500 locally) | Replace its role on the auth screens with the package's `contour-field.svg` plus the wordmark, or no image (Phase 6). The file stays; optional framed-figure reuse only if rights are confirmed. | **Retain**; **Replace** (usage, Phase 6); **Deprecate** as background | Stays at `public/login-register-view.jpg`. |
| `dashboard-student2.jpeg` | Student dashboard hero (the audit measured this file: 423 KB). | OBSERVED (audit); filename match INFERRED | Stop using it as a dashboard hero (Phase 7: the page starts with an h1 and a task). Reuse as a documentary figure on About or in a story: it is genuine. | **Retain**; **Reuse** (documentary); **Deprecate** as dashboard hero | Original stays. Derivatives to `public/assets/photography/field/`. |
| `network-student.JPG` | Name suggests the student 'network' (friends) pages. Not seen in rendered HTML (login required). | INFERRED (filename); Stage One must confirm by searching Blade | Reuse as a documentary/community figure (About or a story). Not a hero on `/student/friends`. | **Retain**; **Reuse**; **Adapt**; **Deprecate** as hero | Original stays. Derivatives to `public/assets/photography/field/`. |
| `projects-student.jpeg` | Name suggests `/student/projects`. | INFERRED (filename) | Reuse as a documentary figure; not a hero on the projects page. | **Retain**; **Reuse**; **Adapt**; **Deprecate** as hero | Original stays. Derivatives to `public/assets/photography/field/`. |
| `repo-student.jpeg` | Name suggests `/student/repository`. | INFERRED (filename) | Reuse as a documentary figure (an official event, for example in a story). Not a hero on the repository page. | **Retain**; **Reuse** with a review; **Adapt**; **Deprecate** as hero | Original stays. Derivatives to `public/assets/photography/field/`. |
| `dashboard-student.jpg` | Name suggests a student dashboard variant. Not seen in rendered HTML. | INFERRED (filename); may be unreferenced | Undecided: keep and check usage in Stage One. Low priority. | **Retain** (pending usage check); **Deprecate** as hero | Stays. |
| `dashboard-student3.jpg` | Name suggests a student dashboard variant. Not seen in rendered HTML. | INFERRED (filename); may be unreferenced | Possible vertical figure for a story or About. Adapt before any use. | **Retain**; **Adapt**; **Deprecate** as hero | Original stays. Derivatives to `public/assets/photography/field/`. |
| `application-student.jpg` | Name suggests `/student/applications`. Not seen in rendered HTML. | INFERRED (filename) | Not a hero on the applications page. Possible framed figure of a village (the subject matches the KKN domain) once rights are confirmed. | **Retain**; **Deprecate** as hero; **Reuse** (optional) | Stays. |
| `search-network.jpg` | Name suggests `/student/friends/search`. Not seen in rendered HTML. | INFERRED (filename) | Not a hero. No planned use. | **Retain**; **Deprecate** as hero | Stays. |
| `company-profile.jpg` | Name suggests `/company/profile` (or the company dashboard). Not seen in rendered HTML. | INFERRED (filename); audit confirms stock photo heroes on company pages (OBSERVED) | No photo header on company pages (audit: 'foto stok kantor ... ganti dengan foto lapangan KKN atau tanpa foto'). Phase 25 uses the standard page header. | **Retain** (until Phase 25 is verified); **Deprecate**; **Replace** usage with a plain page header | Stays. |
| `company1.jpg` | Company-side page heroes (company dashboard, talents, jobs, applications, profile; exact mapping UNKNOWN). | OBSERVED (audit, 'foto stok kantor generik'); mapping INFERRED | No photo. Standard page header. | **Retain** (until Phases 21-25 are verified); **Deprecate**; **Replace** usage | Stays. |
| `company2.jpg` | As company1.jpg. | OBSERVED (audit); mapping INFERRED | No photo. | **Retain** (until verified); **Deprecate**; **Replace** usage | Stays. |
| `company3.jpg` | As company1.jpg. | OBSERVED (audit); mapping INFERRED | No photo. | **Retain** (until verified); **Deprecate**; **Replace** usage | Stays. |
| `company4.jpg` | As company1.jpg. | OBSERVED (audit); mapping INFERRED | No photo. | **Retain** (until verified); **Deprecate**; **Replace** usage | Stays. |
| `company5.jpg` | As company1.jpg. | OBSERVED (audit); mapping INFERRED | No photo. | **Retain** (until verified); **Deprecate**; **Replace** usage | Stays. |
| `KARA-background.png` | Name suggests the KARA assistant screens (`/student/chatbot*`). Not seen in rendered HTML (login required). | INFERRED (filename) | Replace its role: the design drops gradients and decorative backgrounds (audit guardrails 5 and 7). Use a flat surface, or `contour-field.svg` (Source B) if a motif is wanted (Phase 16). Keep the file until D-01 decides the assistant's name and look. | **Retain** (until Phase 16); **Replace** usage; **Adapt** if it is kept | Stays at `public/KARA-background.png`. |
| `profile_13523136.jpg` | `<img class=team-photo>` in the team card on `/contact`. | VERIFIED (rendered HTML) | Preserve. Phase 5 moves the team to About as a simple list with valid photos or initials (audit G03). | **Retain**; **Adapt** (square crop derivative later) | Stays. Any renamed or cropped derivative goes to `public/assets/photography/team/` and only after D-31 is answered. |
| `profile_13523124.jpeg` | `/contact` references `/profile_13523124.jpg`, but this file is `.jpeg`. | VERIFIED (the `.jpg` URL fails to load; `.jpeg` and `.JPEG` load) | Preserve. Phase 5 repairs the reference (a template change, not a file rename). | **Retain**; **Adapt** (fix reference in Phase 5) | Stays. Do not rename the file; correct the template reference. |
| `profile_18223127.jpg` | `/contact` references `/profile_18223127.jpg` (and its alt text shows instead). | VERIFIED reference; file NOT FOUND under `.jpg`, `.JPG`, `.jpeg`, `.JPEG`, `.png`, `.PNG`, `.webp` | Phase 5: use the file if the user supplies it, otherwise an initials avatar. Nothing is invented. | **Retain** the reference record; user supplies the file or initials are used | n/a |
| `favicon.ico` | Exists but is empty (Laravel default). `/` has no `<link rel=icon>`. | VERIFIED (HTTP 200, 0 bytes); audit agrees (OBSERVED) | Replace with the package favicon set, linked from the layout `<head>` (Phase 3). The empty root file is only overwritten if a later phase explicitly authorises it; the package files live under `public/assets/brand/`. | **Replace** | `public/assets/brand/favicon.ico` (+ `favicon.svg`, `apple-touch-icon.png`), linked from the layout. |
| `robots.txt` | Served at `/robots.txt`. | VERIFIED (HTTP 200) | No change. | **Retain** | Stays. |
| `build/assets/app-CDP40zpX.css, build/assets/app-34mOoJaZ.js, build/manifest.json` | Linked on every page. Manifest entries: `resources/css/app.css`, `resources/js/app.js` (nothing else). | VERIFIED (manifest read; JS length 0 as in the audit) | Never edited by hand. Rebuilt by `npm run build` in Phase 2 onwards. | **Retain** (generated) | `public/build/` (generated). |

### 1.3 Visual assessment, licence and concerns

Images were looked at by eye in thumbnail contact sheets (this session). Quality notes describe composition and resolution, not rights.

| Asset | Visual assessment (looked at by eye) | Licence / source | Concern |
|---|---|---|---|
| `karsa-logo.png` | Sharp wordmark 'Karsa' in slate indigo with a grey chevron mark on a transparent background; far larger than the 40 px it is drawn at. | First-party brand asset (assumed). No licence or source record in the repository that I could see (UNKNOWN). | The package's INTEGRATION.md section 4 says to remove the 272 KB PNG logo. That instruction is superseded by AD-09. |
| `hero-background.jpg` | Terraced rice paddies and banana plants under a hazy mountain sky; soft, low contrast, slightly washed out. Works as a mood image, not as a background for text. | Unknown source and rights (UNKNOWN). | Rights unknown. Compare with Source B homepage photos and `about.jpg` before choosing any single landscape (section 11, overlap 3). |
| `about.jpg` | Terraced rice fields in strong green light, sharp, landscape. 7360x4912 is a camera or stock original size; 3.6 MB to show one image is the heaviest asset in the app. | Unknown source and rights (UNKNOWN). EXIF was not inspected (the file was not downloaded). | 3.6 MB is the weight problem the audit flags in J6. Never serve it unresized. |
| `mahasiswa-about.jpeg` | Students in patterned shirts seen from behind and the side, facing forest; natural light; faces mostly turned away. Genuine field photograph. | Unknown photographer; consent of the people shown not documented (UNKNOWN). | Rights and consent (D-30). Alt text must describe the scene. |
| `handprints-about.jpeg` | A blue wall covered in coloured handprints with one person in a cap seen in profile; warm, human, specific. | Unknown (UNKNOWN). | One person is identifiable in profile: consent (D-30). Do not imply endorsement. |
| `jaket-about.jpeg` | A KKN ITB jacket (year printed on it) covered in handprints, hanging in a room; people softly out of focus behind. | Unknown (UNKNOWN). | Institutional branding (KKN ITB) is visible: confirm that using it publicly is intended. |
| `contact.jpg` | Aerial view of a park, a road and a pond with a few buildings (the audit calls it a campus aerial); clean, but not specific to the product. | Unknown (UNKNOWN). | Rights unknown. |
| `login-register-view.jpg` | Hazy rural field, a hut and a transmission tower under overcast sky; portrait; low contrast. | Unknown (UNKNOWN). | Rights unknown. |
| `dashboard-student2.jpeg` | KKN students in branded vests seen from behind, hats on, banana-leaf forest; faces not visible; high resolution; the best-composed field image in the set. | Unknown photographer; consent undocumented (UNKNOWN). | D-30. |
| `network-student.JPG` | A rainy village lane under strings of coloured flags; people with umbrellas walking away; vivid, genuine, faces not visible. | Unknown (UNKNOWN). | Uppercase `.JPG` extension: keep the reference exactly as is until the derivative exists (case matters on Linux). |
| `projects-student.jpeg` | A truck carrying a large animal statue along a green hillside road, flags on the cab; genuine, lively; few people. | Unknown (UNKNOWN). | D-30. |
| `repo-student.jpeg` | A flag-raising ceremony under a red canopy with people in white uniforms and a crowd; many people but small in frame. | Unknown (UNKNOWN). | Many identifiable people possible at full resolution: consent and a crop decision (D-30). |
| `dashboard-student.jpg` | City and a volcano silhouette at dusk; dark, moody, small. | Unknown (UNKNOWN). | If Stage One finds no reference, record it as unreferenced; it is still not deleted. |
| `dashboard-student3.jpg` | A dirt path through fields toward a small shelter and distant mountains; sharp, portrait, no people. 3.3 MB. | Unknown (UNKNOWN). | Heavy: 3.3 MB. |
| `application-student.jpg` | Aerial view of a dense neighbourhood with red roofs, trees and a winding road; sharp and relevant to village work. | Unknown (UNKNOWN). | Rights unknown. |
| `search-network.jpg` | Aerial view of a narrow road through dense forest; clean but generic. | Unknown (UNKNOWN). | Rights unknown. |
| `company-profile.jpg` | Low-angle view of glass skyscrapers against a blue sky; generic corporate stock. | Unknown (UNKNOWN). | Unrelated to the domain; rights unknown. |
| `company1.jpg` | Staged meeting around a whiteboard in a bright open office; polished, generic. | Unknown (UNKNOWN). | Unrelated to the domain; rights and origin unknown. |
| `company2.jpg` | Hands typing on a laptop with a coffee cup, a dashboard on a monitor behind. | Unknown (UNKNOWN). | Unrelated to the domain. |
| `company3.jpg` | Office presentation to a small team around a glass table; screen shows garbled text. | Unknown (UNKNOWN). | Unrelated to the domain; the on-screen text is not legible. |
| `company4.jpg` | Laptop and monitors on a wooden desk with a latte; lower resolution than the others. | Unknown (UNKNOWN). | Unrelated to the domain; 1024 px wide stretched into a wide hero. |
| `company5.jpg` | Colleagues around a laptop in a loft office; lower resolution. | Unknown (UNKNOWN). | Unrelated to the domain. |
| `KARA-background.png` | Blue-grey teal gradient with fine golden star-burst lines; atmospheric but decorative, 1.7 MB. | Unknown; possibly generated or designed in-house (UNKNOWN). | Heavy PNG for a gradient. |
| `profile_13523136.jpg` | Standing portrait outdoors, near a palm tree and a brick wall; usable square crop. | Personal photograph of a team member; presumably supplied by them (UNKNOWN in writing). | The file name contains a student ID number. Public page (D-31). |
| `profile_13523124.jpeg` | Portrait, slightly tall crop (not viewed individually to avoid exposing a personal photo beyond what the page already shows). | Personal photograph (UNKNOWN in writing). | Broken image on a public page today; identifying file name (D-31). |
| `profile_18223127.jpg` | Cannot be viewed: the file is missing at the referenced path. | n/a | Missing file on a public page (audit G03: 'dua foto profil rusak'). |
| `favicon.ico` | Empty file. | Framework default. | None. |
| `robots.txt` | n/a | Framework default. | None. |
| `build/assets/app-CDP40zpX.css, build/assets/app-34mOoJaZ.js, build/manifest.json` | n/a | Generated. | `/forgot-password` returns HTTP 500 because the manifest lacks `resources/css/auth-institution.css` (VERIFIED earlier in this discovery); `/register/institution` and `/register/company` also return 500 but their cause was not established in this pass (UNKNOWN; the database outage D-12 is the likely cause, as for `/register/student`). Not an asset change; recorded for Stage One. |

### 1.4 Assets that exist but could not be located or opened

| Asset | Evidence | What is known | Handling |
|---|---|---|---|
| Student profile photographs (uploads) | UNKNOWN / BLOCKED | Storage location (public disk under `storage/`, `public/storage` symlink, or remote bucket) cannot be listed without the repository. 700 student profiles exist (audit). A probe of `/storage/...` returned HTTP 403, which suggests the storage route/link behaves differently from a plain static folder (not investigated further). | Preserve (Retain). Never moved or re-encoded. Public exposure follows D-04. Stage One: find where avatars are stored and how Blade renders them (`<img>` vs initials). |
| Certificate templates | UNKNOWN / BLOCKED | The user says they exist. No certificate route is among the audit's 59 patterns, and no template image or PDF was found at probed public paths (`/certificate.png`, `/certificate-template.png`, `/certificate.jpg`, `/images/certificate.png` all 404). | Preserve (Retain). Protected asset group (D-32). Stage One: grep Blade, controllers, `public/`, `storage/app`, `resources/` for 'sertifikat', 'certificate', DomPDF or image-generation code. |
| Certificate fonts: Pinyon Script and Poppins | UNKNOWN / BLOCKED | Stated by the user; not in the audit (which lists Inter, Outfit, Merriweather, Lora and a declared-but-unloaded Instrument Sans). `/fonts/PinyonScript-Regular.ttf`, `/fonts/Poppins-*.ttf`, `/fonts/Pinyon_Script.ttf` all returned 404 at probed paths. | Preserve (Retain). They are NOT part of the website font stack and must survive the switch to Plus Jakarta Sans/Source Serif 4. Stage One: locate the files (`public/fonts`, `storage/fonts`, `resources/fonts`, DomPDF font directory) or the Google Fonts `<link>` inside the certificate view. |
| Institution and company logos (uploads) | UNKNOWN / BLOCKED | Institution logos appear on `/institution/{id}` (audit); company logos on company profiles. Storage unknown. | Preserve (Retain). Shown only when present; initials otherwise (guardrail 7). |
| Verification documents (institution) | UNKNOWN / BLOCKED | Uploaded through `/institution/verification/upload` (audit I09). | Preserve (Retain). Private documents: never shown publicly (D-04, D-31). |
| Problem gallery images | BLOCKED (environment) | 310 gallery images (3-5 per problem) point at a remote Supabase bucket the local app cannot load (audit J12). | Preserve (Retain). Layout must collapse the media region when an image is absent (D-14). |
| Other files under `public/` | UNKNOWN | A directory listing is not possible over HTTP, so any file not referenced by the 8 guest pages, the audit or the user's list is unknown to me. The guest pages `/register/institution`, `/register/company` and `/forgot-password` return HTTP 500 locally, so their references could not be read. | Stage One lists `public/` recursively and records every file with size and SHA-256 before anything is touched. |

### 1.5 Stage One verification (Phase 0, 2026-10-30)

Read-only pass over the repository (branch `feat/students`, HEAD `e824b9d`). `public/` listed recursively: **38 files** outside `build/` (`public/storage` link absent); SHA-256 and size of each in `baseline/asset-hashes.txt`, identical before and after Phase 0 (**no asset changed**). EXIF read with Pillow on all 31 raster images: **no GPS, camera or author tags** in any file. The "Referenced from" column replaces every INFERRED usage in sections 1.2 and 1.3; decisions in 1.2 stand unless a note says otherwise.

| File | Bytes | Pixels | Referenced from (VERIFIED in source) | Was in 1.1? | Note |
|---|---|---|---|---|---|
| `karsa-logo.png` | 278,755 | 1430x566 | `components/navbar.blade.php:9`, `components/footer.blade.php:7`, `auth/login:80`, `auth/register:30`, `auth/student-register:329`, `auth/institution-register:331`, `auth/company-register:243`; also `README.md` | yes | confirmed |
| `hero-background.jpg` | 86,574 | 1280x720 | `home/index.blade.php:19` | yes | confirmed |
| `about.jpg` | 3,688,625 | 7360x4912 | `about/index.blade.php:150` | yes | confirmed |
| `mahasiswa-about.jpeg` | 735,177 | 4912x2760 | `about/index.blade.php:19` | yes | confirmed |
| `handprints-about.jpeg` | 407,945 | 4912x2760 | `about/index.blade.php:218` | yes | confirmed |
| `jaket-about.jpeg` | 703,121 | 4912x2760 | `about/index.blade.php:399` | yes | confirmed |
| `contact.jpg` | 96,176 | 1280x720 | `contact/index.blade.php:178` | yes | confirmed |
| `login-register-view.jpg` | 144,995 | 900x1600 | `auth/login.blade.php:14` only | yes | other auth pages use their own backgrounds (below) |
| `dashboard-student2.jpeg` | 433,311 | 4912x2760 | `student/dashboard/index.blade.php:17, 35, 47`; `student/friends/index.blade.php:100` | yes | INFERRED → VERIFIED (dashboard and friends heroes) |
| `network-student.JPG` | 1,124,240 | 4912x2760 | `student/friends/index.blade.php:166` | yes | INFERRED → VERIFIED |
| `projects-student.jpeg` | 1,074,033 | 4912x2760 | `student/projects/index.blade.php:18` | yes | INFERRED → VERIFIED |
| `repo-student.jpeg` | 1,223,605 | 4912x2760 | `student/repository/index.blade.php:18` | yes | INFERRED → VERIFIED |
| `dashboard-student.jpg` | 57,659 | 1280x720 | `student/browse-problems/index.blade.php:24` | yes | **corrected**: browse-problems hero, not a dashboard |
| `dashboard-student3.jpg` | 3,425,497 | 3827x5741 | `student/profile/index.blade.php:11`, `student/profile/public.blade.php:48` | yes | **corrected**: profile heroes (incl. the public profile) |
| `application-student.jpg` | 188,432 | 1280x720 | `student/applications/index.blade.php:22` | yes | INFERRED → VERIFIED |
| `search-network.jpg` | 192,674 | 1280x720 | `student/friends/search.blade.php:57` | yes | INFERRED → VERIFIED |
| `company-profile.jpg` | 252,990 | 1405x706 | `company/profile/{index,edit,public}.blade.php:8` | yes | INFERRED → VERIFIED |
| `company1.jpg` | 231,533 | 1408x702 | `company/applications/show.blade.php:8`, `company/talents/index.blade.php:94` | yes | mapping VERIFIED |
| `company2.jpg` | 132,255 | 1406x722 | `company/jobs/index.blade.php:8` | yes | mapping VERIFIED |
| `company3.jpg` | 238,773 | 1406x705 | `company/applications/index.blade.php:168`, `company/jobs/{create,edit}.blade.php:9` | yes | mapping VERIFIED |
| `company4.jpg` | 85,856 | 1024x509 | `company/talents/saved.blade.php:166` | yes | mapping VERIFIED |
| `company5.jpg` | 124,036 | 1024x510 | `company/talents/show.blade.php:8` (that view has no route) | yes | effectively unused |
| `KARA-background.png` | 1,805,588 | 2389x1319 | **none** | yes | **corrected**: not used by the chatbot views (they use `deal-work-together.jpg`). Retain. |
| `profile_13523136.jpg` | 243,346 | 1220x1184 | `app/Http/Controllers/ContactController.php` (team photo key) | yes | confirmed |
| `profile_13523124.jpeg` | 246,297 | 990x1034 | referenced as `profile_13523124.jpg` in `ContactController.php` → 404 | yes | confirmed broken reference |
| `profile_18223127.jpg` | — | — | `ContactController.php`; file absent under every extension | yes (NOT FOUND) | confirmed missing |
| `favicon.ico` | 0 | — | none (no `<link rel=icon>` in the layouts) | yes | confirmed |
| `robots.txt` | 24 | — | — | yes | confirmed |
| `deal-work-together.jpg` | 1,092,772 | 6016x4016 | `company/dashboard/index.blade.php:9`, `student/chatbot/index.blade.php:16` | **no — added** | photo hero on the company dashboard and the KARA list. Retain; Deprecate as hero (guardrail 1); rights UNKNOWN (D-30) |
| `magang-preview.jpg` | 76,590 | 960x640 | `student/jobs/index.blade.php:24` | **no — added** | jobs hero. Retain; Deprecate as hero; rights UNKNOWN |
| `institution-register-background.jpeg` | 610,985 | 4912x2760 | `auth/institution-register.blade.php:29` | **no — added** | auth background (Phase 6). Retain; rights UNKNOWN |
| `student-register-background.jpeg` | 427,719 | 4912x2760 | `auth/student-register.blade.php:26` | **no — added** | auth background (Phase 6). Retain; rights UNKNOWN |
| `reg-com-background.jpg` | 1,031,676 | 3649x5362 | **none** | **no — added** | unreferenced; Retain (not deleted) |
| `profile_13523151.jpeg` | 410,163 | 1074x1130 | **none** | **no — added** | personal photo with a student ID in the name; possibly the missing team portrait — user to confirm (D-31). Never published without that. |
| `assets/certificate/template.png` | 332,656 | 2000x1414 | `app/Services/CertificateService.php:160` | **no — added** | **Protected (D-32).** Contains a real signature and stamp. |
| `assets/certificate/fonts/PinyonScript-Regular.ttf` | 148,956 | — | `CertificateService.php:162` | **no — added** | **Protected (D-32).** Certificate only; not a website font. |
| `assets/certificate/fonts/Poppins-Regular.ttf` | 158,240 | — | `CertificateService.php:161` | **no — added** | **Protected (D-32).** Certificate only. |
| `.htaccess`, `index.php` | 740, 543 | — | framework | **no — added** | Retain (framework files) |
| `build/manifest.json`, `build/assets/app-CDP40zpX.css`, `build/assets/app-34mOoJaZ.js` | 331, 125,756, 0 | — | `@vite` | yes | generated; hashes in `baseline/asset-hashes.txt` |

**Section 1.4 resolved by Stage One**

| Item | Result |
|---|---|
| Certificate templates and fonts | VERIFIED, see the three rows above. Generation: `CertificateService::generateCertificate()` → GD PNG → uploaded to Supabase (`uploadCertificate`) → `projects.certificate_path`; triggered on project completion (`Institution/ProjectManagementController`) and by `php artisan certificates:generate-missing`. Baseline sample (placeholder text, rendered offline without DB or upload): `baseline/screenshots/certificate-sample-baseline.png`. Website typography changes cannot affect it (fonts are loaded by file path, not by a `<link>`). |
| Student profile photos, institution/company logos, verification documents (incl. KTP, NPWP), problem images, proposals, reports | VERIFIED: stored in Supabase Storage through `App\Services\SupabaseStorageService` (e.g. `students/profiles/…`); local `storage/app/public` and `private` are empty; `public/storage` link absent. Rendering: `Student::getProfilePhotoUrlAttribute()` (`app/Models/Student.php:174-195`) returns the Supabase URL, a local `storage/` URL, or a **ui-avatars.com** URL built from the student's full name (third-party request; relevant to D-04/D-31). Logos: `Institution::getLogoUrl()` (`app/Models/Institution.php:154`), `Company::getLogoUrlAttribute()` (`app/Models/Company.php:90`). Never opened or copied in Phase 0. |
| Other files under `public/` | VERIFIED: complete list above. |

**Source B check (H.2):** the 111 hashed files in `student-go-assets/assets-manifest.json` match (0 mismatches, 0 missing). The package sits at the repository root as `student-go-assets/` (untracked). **New:** its 60 catalogue photographs now exist as WebP under `student-go-assets/public/assets/photography/`, with 61 originals under `student-go-assets/storage/app/asset-originals/` (54 MB), created 2026-10-30 14:07, while the manifest still says "not yet downloaded; visual review pending". They were not reviewed in Phase 0 (D-19, D-33 still OPEN).

**Collision check (S1-34):** `public/assets/{brand,fonts,icons,illustrations,photography}/`, `resources/css/student-go/`, `scripts/`, `student-go-docs/design-system/` do not exist; `public/assets/certificate/` does and must be left alone by Phase 2.

**S1-35 (rights and consent, D-30):** not answered yet — BLOCKED. Applies to every photograph above, including the six newly listed ones.

## 2. Custom `student-go-assets/` inventory (Source B)

**Fields shared by every row of this section.** *Original location*: inside the package folder `student-go-assets/`, at the path shown. *Current usage*: none; the package has not been integrated, so nothing in the running app references it (VERIFIED: `/assets/brand|fonts|icons|illustrations/*` return 404 on the running app). *Visual assessment*: the SVGs were rendered to contact sheets and looked at in this session; the CSS, fonts and icons were checked through the package's style guide screenshots (earlier session) and have not been seen inside the app. *Licence*: as stated per group and in `docs/ASSET_SOURCES.md`.

**Package facts.** 124 files, 6.2 MB. `assets-manifest.json` lists 111 files with SHA-256; all 111 match the files on disk (VERIFIED this session). Not in the manifest: the manifest itself, `README.md`, `docs/*.md`, `docs/photo-catalog.json`, `docs/screenshots/*`, `docs/styleguide.*`, `scripts/*` and `public/assets/**/LICENSE|OFL` texts are partly covered; the manifest is the authority for the assets.

### 2.1 CSS (design tokens and components)

Actual location is `student-go-assets/resources/css/student-go/`. The request names `student-go-assets/public/resources/css/student-go/`, which does **not** exist in the package (VERIFIED by listing). See D-29.

| Asset | What it is / intended design usage | Decision | Target implementation path |
|---|---|---|---|
| `resources/css/student-go/tokens.css` (7.7 KB) | Tailwind v4 `@theme` + semantic variables: `brand-*`, `primary-*` alias, re-tuned `gray-*`, radii, type scale, shadows, motion. | Reuse; Adapt (merge into the existing `@theme`; re-tuned grays and collapsed radii are GLOBAL changes) | `resources/css/student-go/tokens.css` |
| `resources/css/student-go/components.css` (16.8 KB) | `.sg-*` components (btn, input, field, page-header, table, list, status, chip, meter, summary, stepper, dropzone, file-chip, empty, reading, prose, figure, coord, marker, survey-marks, media, skip, overlay, icon). | Reuse; Adapt per page as pages migrate | `resources/css/student-go/components.css` |
| `resources/css/student-go/fonts.css` (2.1 KB) | Three `@font-face` rules with root-relative `url("/assets/fonts/...")`. | Reuse (only works if the fonts are copied to `public/assets/fonts/`) | `resources/css/student-go/fonts.css` |

Licence: original work for this project. Compiled with Tailwind CSS 4.1.16 in the earlier session; never run through the app's Vite pipeline (UNKNOWN).

### 2.2 Brand

| Asset | Intended design usage | Licence / source | Decision | Target implementation path |
|---|---|---|---|---|
| `apple-touch-icon.png` (10.8 KB) | `<link rel="apple-touch-icon">` | Original work, no third-party rights | **Replace** the empty `public/favicon.ico` (Source A) from the layout `<head>` | `public/assets/brand/apple-touch-icon.png` |
| `favicon.ico` (1.4 KB) | `<link rel="icon" sizes="32x32">` fallback | Original work, no third-party rights | **Replace** the empty `public/favicon.ico` (Source A) from the layout `<head>` | `public/assets/brand/favicon.ico` |
| `favicon.svg` (8.1 KB) | `<link rel="icon" type="image/svg+xml">` | Original work, no third-party rights | **Replace** the empty `public/favicon.ico` (Source A) from the layout `<head>` | `public/assets/brand/favicon.svg` |
| `student-go-logo-on-dark.svg` (12.9 KB) | Auth side panel, dark footer | Original work, no third-party rights | **Reuse** only if D-01 picks Student Go; otherwise **Retain** the Karsa logo (Source A) and do not use this wordmark | `public/assets/brand/student-go-logo-on-dark.svg` |
| `student-go-logo.svg` (12.9 KB) | Header, footer, auth form side (light surfaces) | Original work, no third-party rights | **Reuse** only if D-01 picks Student Go; otherwise **Retain** the Karsa logo (Source A) and do not use this wordmark | `public/assets/brand/student-go-logo.svg` |
| `student-go-symbol-on-dark.svg` (8.1 KB) | Dark surfaces where only the mark fits | Original work, no third-party rights | **Reuse** only if D-01 picks Student Go; otherwise **Retain** the Karsa logo (Source A) and do not use this wordmark | `public/assets/brand/student-go-symbol-on-dark.svg` |
| `student-go-symbol.svg` (8.1 KB) | Mobile header, avatar-less placeholders, app icon source | Original work, no third-party rights | **Reuse** only if D-01 picks Student Go; otherwise **Retain** the Karsa logo (Source A) and do not use this wordmark | `public/assets/brand/student-go-symbol.svg` |

Visual check: the symbol is a route drawn as an 'S' (ring = origin, dot = destination); the wordmark 'Student Go' reads cleanly at header size and on the dark navy variant. This is a brand-name decision, not only an asset decision (D-01, D-18).

### 2.3 Fonts (website typography only)

| Asset | Intended design usage | Licence / source | Decision | Target implementation path |
|---|---|---|---|---|
| `fonts/plus-jakarta-sans/OFL.txt` (4.4 KB) | Licence text, kept beside the font | SIL OFL 1.1 | **Reuse** | `public/assets/fonts/plus-jakarta-sans/OFL.txt` |
| `fonts/plus-jakarta-sans/PlusJakartaSans-latin-ext-wght.woff2` (21.2 KB) | UI and headings (latin-ext only when a name needs it) | SIL OFL 1.1; from the Fontsource npm packages of the Google Fonts releases | **Reuse** (pending D-17) | `public/assets/fonts/plus-jakarta-sans/PlusJakartaSans-latin-ext-wght.woff2` |
| `fonts/plus-jakarta-sans/PlusJakartaSans-latin-wght.woff2` (26.7 KB) | UI and headings (latin-ext only when a name needs it) | SIL OFL 1.1; from the Fontsource npm packages of the Google Fonts releases | **Reuse** (pending D-17) | `public/assets/fonts/plus-jakarta-sans/PlusJakartaSans-latin-wght.woff2` |
| `fonts/source-serif-4/OFL.txt` (4.2 KB) | Licence text, kept beside the font | SIL OFL 1.1 | **Reuse** | `public/assets/fonts/source-serif-4/OFL.txt` |
| `fonts/source-serif-4/SourceSerif4-latin-wght.woff2` (49.7 KB) | Long editorial reading text only | SIL OFL 1.1; from the Fontsource npm packages of the Google Fonts releases | **Reuse** (pending D-17) | `public/assets/fonts/source-serif-4/SourceSerif4-latin-wght.woff2` |

Certificate typography (Pinyon Script, Poppins) is **not** part of this package and is not affected by it (D-32). The OFL Reserved Font Name 'Source' applies to Source Serif 4: the files are Fontsource subsets and keep their original family name; do not rename or modify them.

### 2.4 Icons (Lucide, ISC)

| Asset | Meaning in the UI (icons.json) | Decision | Target implementation path |
|---|---|---|---|
| `arrow-left.svg` (7.8 KB) | Kembali | **Reuse** via the sprite | `public/assets/icons/ui/arrow-left.svg` |
| `arrow-right.svg` (7.8 KB) | Lanjut | **Reuse** via the sprite | `public/assets/icons/ui/arrow-right.svg` |
| `arrow-up-right.svg` (7.8 KB) | Tautan keluar / buka | **Reuse** via the sprite | `public/assets/icons/ui/arrow-up-right.svg` |
| `award.svg` (8.0 KB) | Sertifikat | **Reuse** via the sprite | `public/assets/icons/ui/award.svg` |
| `badge-check.svg` (8.0 KB) | Terverifikasi | **Reuse** via the sprite | `public/assets/icons/ui/badge-check.svg` |
| `bell.svg` (8.0 KB) | Notifikasi | **Reuse** via the sprite | `public/assets/icons/ui/bell.svg` |
| `book-open.svg` (8.0 KB) | Pendidikan | **Reuse** via the sprite | `public/assets/icons/ui/book-open.svg` |
| `bookmark.svg` (7.9 KB) | Simpan / wishlist | **Reuse** via the sprite | `public/assets/icons/ui/bookmark.svg` |
| `briefcase.svg` (7.9 KB) | Perusahaan / lowongan | **Reuse** via the sprite | `public/assets/icons/ui/briefcase.svg` |
| `building-2.svg` (8.0 KB) | Instansi | **Reuse** via the sprite | `public/assets/icons/ui/building-2.svg` |
| `calendar.svg` (7.9 KB) | Tenggat / tanggal | **Reuse** via the sprite | `public/assets/icons/ui/calendar.svg` |
| `check.svg` (7.8 KB) | Berhasil | **Reuse** via the sprite | `public/assets/icons/ui/check.svg` |
| `chevron-down.svg` (7.8 KB) | Buka | **Reuse** via the sprite | `public/assets/icons/ui/chevron-down.svg` |
| `chevron-left.svg` (7.8 KB) | Sebelumnya | **Reuse** via the sprite | `public/assets/icons/ui/chevron-left.svg` |
| `chevron-right.svg` (7.8 KB) | Berikutnya | **Reuse** via the sprite | `public/assets/icons/ui/chevron-right.svg` |
| `chevron-up.svg` (7.8 KB) | Tutup | **Reuse** via the sprite | `public/assets/icons/ui/chevron-up.svg` |
| `circle-alert.svg` (7.9 KB) | Perhatian | **Reuse** via the sprite | `public/assets/icons/ui/circle-alert.svg` |
| `circle-check.svg` (7.9 KB) | Diterima | **Reuse** via the sprite | `public/assets/icons/ui/circle-check.svg` |
| `circle-dashed.svg` (8.2 KB) | Dibatalkan / belum | **Reuse** via the sprite | `public/assets/icons/ui/circle-dashed.svg` |
| `circle-help.svg` (7.9 KB) | Bantuan | **Reuse** via the sprite | `public/assets/icons/ui/circle-help.svg` |
| `circle-x.svg` (7.9 KB) | Ditolak | **Reuse** via the sprite | `public/assets/icons/ui/circle-x.svg` |
| `clipboard-check.svg` (8.0 KB) | Review | **Reuse** via the sprite | `public/assets/icons/ui/clipboard-check.svg` |
| `clipboard-list.svg` (8.0 KB) | Lamaran | **Reuse** via the sprite | `public/assets/icons/ui/clipboard-list.svg` |
| `clock.svg` (7.9 KB) | Durasi / waktu | **Reuse** via the sprite | `public/assets/icons/ui/clock.svg` |
| `copy.svg` (7.9 KB) | Salin | **Reuse** via the sprite | `public/assets/icons/ui/copy.svg` |
| `download.svg` (7.9 KB) | Unduh | **Reuse** via the sprite | `public/assets/icons/ui/download.svg` |
| `droplets.svg` (8.1 KB) | Air bersih / sanitasi | **Reuse** via the sprite | `public/assets/icons/ui/droplets.svg` |
| `ellipsis.svg` (7.9 KB) | Aksi lain | **Reuse** via the sprite | `public/assets/icons/ui/ellipsis.svg` |
| `external-link.svg` (7.9 KB) | Tautan eksternal | **Reuse** via the sprite | `public/assets/icons/ui/external-link.svg` |
| `eye-off.svg` (8.1 KB) | Sembunyikan | **Reuse** via the sprite | `public/assets/icons/ui/eye-off.svg` |
| `eye.svg` (7.9 KB) | Lihat | **Reuse** via the sprite | `public/assets/icons/ui/eye.svg` |
| `file-text.svg` (8.0 KB) | Dokumen | **Reuse** via the sprite | `public/assets/icons/ui/file-text.svg` |
| `folder-open.svg` (8.0 KB) | Repositori | **Reuse** via the sprite | `public/assets/icons/ui/folder-open.svg` |
| `globe.svg` (7.9 KB) | Situs web | **Reuse** via the sprite | `public/assets/icons/ui/globe.svg` |
| `graduation-cap.svg` (8.0 KB) | Mahasiswa / universitas | **Reuse** via the sprite | `public/assets/icons/ui/graduation-cap.svg` |
| `hourglass.svg` (8.0 KB) | Menunggu | **Reuse** via the sprite | `public/assets/icons/ui/hourglass.svg` |
| `house.svg` (8.0 KB) | Beranda | **Reuse** via the sprite | `public/assets/icons/ui/house.svg` |
| `image.svg` (7.9 KB) | Gambar | **Reuse** via the sprite | `public/assets/icons/ui/image.svg` |
| `info.svg` (7.9 KB) | Informasi | **Reuse** via the sprite | `public/assets/icons/ui/info.svg` |
| `layout-grid.svg` (8.0 KB) | Tampilan galeri | **Reuse** via the sprite | `public/assets/icons/ui/layout-grid.svg` |
| `layout-list.svg` (8.0 KB) | Tampilan daftar | **Reuse** via the sprite | `public/assets/icons/ui/layout-list.svg` |
| `lightbulb.svg` (8.0 KB) | Gagasan | **Reuse** via the sprite | `public/assets/icons/ui/lightbulb.svg` |
| `link.svg` (7.9 KB) | Salin tautan | **Reuse** via the sprite | `public/assets/icons/ui/link.svg` |
| `list-filter.svg` (7.9 KB) | Filter | **Reuse** via the sprite | `public/assets/icons/ui/list-filter.svg` |
| `loader-circle.svg` (7.8 KB) | Memuat | **Reuse** via the sprite | `public/assets/icons/ui/loader-circle.svg` |
| `lock.svg` (7.9 KB) | Privat | **Reuse** via the sprite | `public/assets/icons/ui/lock.svg` |
| `log-in.svg` (7.9 KB) | Masuk | **Reuse** via the sprite | `public/assets/icons/ui/log-in.svg` |
| `log-out.svg` (7.9 KB) | Keluar | **Reuse** via the sprite | `public/assets/icons/ui/log-out.svg` |
| `mail.svg` (7.9 KB) | Email | **Reuse** via the sprite | `public/assets/icons/ui/mail.svg` |
| `map-pin.svg` (7.9 KB) | Lokasi | **Reuse** via the sprite | `public/assets/icons/ui/map-pin.svg` |
| `map.svg` (8.1 KB) | Tampilan peta | **Reuse** via the sprite | `public/assets/icons/ui/map.svg` |
| `menu.svg` (7.9 KB) | Menu mobile | **Reuse** via the sprite | `public/assets/icons/ui/menu.svg` |
| `message-square-text.svg` (8.0 KB) | Asisten KARA | **Reuse** via the sprite | `public/assets/icons/ui/message-square-text.svg` |
| `pencil.svg` (8.0 KB) | Ubah | **Reuse** via the sprite | `public/assets/icons/ui/pencil.svg` |
| `phone.svg` (8.0 KB) | Telepon / WhatsApp | **Reuse** via the sprite | `public/assets/icons/ui/phone.svg` |
| `plus.svg` (7.8 KB) | Tambah | **Reuse** via the sprite | `public/assets/icons/ui/plus.svg` |
| `refresh-cw.svg` (8.0 KB) | Muat ulang | **Reuse** via the sprite | `public/assets/icons/ui/refresh-cw.svg` |
| `search.svg` (7.9 KB) | Cari | **Reuse** via the sprite | `public/assets/icons/ui/search.svg` |
| `send.svg` (8.0 KB) | Kirim lamaran | **Reuse** via the sprite | `public/assets/icons/ui/send.svg` |
| `settings.svg` (8.2 KB) | Pengaturan | **Reuse** via the sprite | `public/assets/icons/ui/settings.svg` |
| `shield-check.svg` (8.0 KB) | Keamanan / privasi | **Reuse** via the sprite | `public/assets/icons/ui/shield-check.svg` |
| `sprout.svg` (8.0 KB) | Pertanian / lingkungan | **Reuse** via the sprite | `public/assets/icons/ui/sprout.svg` |
| `target.svg` (7.9 KB) | Tujuan SDG | **Reuse** via the sprite | `public/assets/icons/ui/target.svg` |
| `trash-2.svg` (8.0 KB) | Hapus | **Reuse** via the sprite | `public/assets/icons/ui/trash-2.svg` |
| `triangle-alert.svg` (7.9 KB) | Peringatan | **Reuse** via the sprite | `public/assets/icons/ui/triangle-alert.svg` |
| `upload.svg` (7.9 KB) | Unggah | **Reuse** via the sprite | `public/assets/icons/ui/upload.svg` |
| `user-check.svg` (7.9 KB) | Terhubung | **Reuse** via the sprite | `public/assets/icons/ui/user-check.svg` |
| `user-plus.svg` (8.0 KB) | Hubungkan | **Reuse** via the sprite | `public/assets/icons/ui/user-plus.svg` |
| `user.svg` (7.9 KB) | Profil | **Reuse** via the sprite | `public/assets/icons/ui/user.svg` |
| `users.svg` (8.0 KB) | Jaringan / kuota | **Reuse** via the sprite | `public/assets/icons/ui/users.svg` |
| `x.svg` (7.8 KB) | Tutup | **Reuse** via the sprite | `public/assets/icons/ui/x.svg` |
| `sprite.svg` (27.7 KB) | One request for every icon: `<use href="/assets/icons/ui/sprite.svg#name"/>`; same-origin only | **Reuse** (primary delivery) | `public/assets/icons/ui/sprite.svg` |
| `icons.json` (1.9 KB) | Icon-to-meaning map ('one meaning, one icon') | **Reuse** (reference) | `public/assets/icons/ui/icons.json` |
| `LICENSE-lucide.txt` (3.1 KB) | ISC licence text (portions derived from Feather, MIT) | **Reuse**: must stay beside the icons | `public/assets/icons/ui/LICENSE-lucide.txt` |

Visual check: strokes were normalised to 1.5 px by the package build; the icon set was looked at inside the style guide screenshots (earlier session). Note the embedded C2PA block that makes each icon about 8 KB (section 6, D-35). `icons/ui/award.svg` ('Sertifikat') is an icon only; it does not replace any certificate asset.

### 2.5 Illustrations and motifs (original SVG)

| Asset | Intended design usage | Licence / source | Decision | Target implementation path |
|---|---|---|---|---|
| `empty-states/no-applications.svg` (8.7 KB) | Student: Lamaran Saya (no applications yet) | Original work, no third-party rights | **Reuse** | `public/assets/illustrations/empty-states/no-applications.svg` |
| `empty-states/no-documents.svg` (8.7 KB) | Repositori (no documents / no results in folder) | Original work, no third-party rights | **Reuse** | `public/assets/illustrations/empty-states/no-documents.svg` |
| `empty-states/no-notifications.svg` (8.9 KB) | Notifikasi (inbox clear) | Original work, no third-party rights | **Reuse** | `public/assets/illustrations/empty-states/no-notifications.svg` |
| `empty-states/no-saved-projects.svg` (8.8 KB) | Student: Tersimpan / Wishlist (nothing saved) | Original work, no third-party rights | **Reuse** | `public/assets/illustrations/empty-states/no-saved-projects.svg` |
| `empty-states/no-search-results.svg` (8.8 KB) | Jelajahi Proyek, Talenta, Repositori (filter returns 0) | Original work, no third-party rights | **Reuse** | `public/assets/illustrations/empty-states/no-search-results.svg` |
| `geographic/indonesia-outline.svg` (33.9 KB) | Single-colour outline for small placements / print | Public domain data; drawing is original | **Reuse** (decorative; boundary note in ASSET_SOURCES.md section 4) | `public/assets/illustrations/geographic/indonesia-outline.svg` |
| `geographic/indonesia-regions.svg` (35.8 KB) | Homepage + About region strip; 7 island-group `<g>` regions with an `.is-active` hook | Public domain data; drawing is original | **Reuse** (decorative; boundary note in ASSET_SOURCES.md section 4) | `public/assets/illustrations/geographic/indonesia-regions.svg` |
| `motifs/contour-field.svg` (53.3 KB) | Editorial surfaces only (homepage band, auth side panel) | Original work, no third-party rights | **Reuse** (editorial surfaces only) | `public/assets/illustrations/motifs/contour-field.svg` |
| `motifs/map-pin-selected.svg` (7.9 KB) | Leaflet `L.icon` for the selected project marker | Original work, no third-party rights | **Adapt**: use as the Leaflet `L.icon` image (28x38, anchor [14, 38]) | `public/assets/illustrations/motifs/map-pin-selected.svg` |
| `motifs/map-pin.svg` (7.9 KB) | Leaflet `L.icon` for project markers (replaces default blue pin) | Original work, no third-party rights | **Adapt**: use as the Leaflet `L.icon` image (28x38, anchor [14, 38]) | `public/assets/illustrations/motifs/map-pin.svg` |
| `motifs/survey-marks.svg` (7.8 KB) | Corner/crop marks on figures and photo frames | Original work, no third-party rights | **Reuse** (editorial surfaces only) | `public/assets/illustrations/motifs/survey-marks.svg` |
| `workflows/company-journey-vertical.svg` (9.7 KB) | Same diagram, vertical (< 768px) | Original work, no third-party rights | **Reuse**; **Adapt** (inline the SVG so its text uses the page font; check every label against real routes) | `public/assets/illustrations/workflows/company-journey-vertical.svg` |
| `workflows/company-journey.svg` (9.8 KB) | Homepage / About "Cara kerja", company lane (horizontal, >= 768px) | Original work, no third-party rights | **Reuse**; **Adapt** (inline the SVG so its text uses the page font; check every label against real routes) | `public/assets/illustrations/workflows/company-journey.svg` |
| `workflows/institution-journey-vertical.svg` (10.1 KB) | Same diagram, vertical (< 768px) | Original work, no third-party rights | **Reuse**; **Adapt** (inline the SVG so its text uses the page font; check every label against real routes) | `public/assets/illustrations/workflows/institution-journey-vertical.svg` |
| `workflows/institution-journey.svg` (10.5 KB) | Homepage / About "Cara kerja", institution lane (horizontal, >= 768px) | Original work, no third-party rights | **Reuse**; **Adapt** (inline the SVG so its text uses the page font; check every label against real routes) | `public/assets/illustrations/workflows/institution-journey.svg` |
| `workflows/project-status-vertical.svg` (9.7 KB) | Same diagram, vertical (< 768px) | Original work, no third-party rights | **Reuse**; **Adapt** (inline the SVG so its text uses the page font; check every label against real routes) | `public/assets/illustrations/workflows/project-status-vertical.svg` |
| `workflows/project-status.svg` (9.9 KB) | Project/application status lifecycle (help page, detail side note) | Original work, no third-party rights | **Reuse**; **Adapt** (inline the SVG so its text uses the page font; check every label against real routes) | `public/assets/illustrations/workflows/project-status.svg` |
| `workflows/student-journey-vertical.svg` (10.2 KB) | Same diagram, vertical (< 768px) | Original work, no third-party rights | **Reuse**; **Adapt** (inline the SVG so its text uses the page font; check every label against real routes) | `public/assets/illustrations/workflows/student-journey-vertical.svg` |
| `workflows/student-journey.svg` (10.6 KB) | Homepage / About "Cara kerja", student lane (horizontal, >= 768px) | Original work, no third-party rights | **Reuse**; **Adapt** (inline the SVG so its text uses the page font; check every label against real routes) | `public/assets/illustrations/workflows/student-journey.svg` |

Visual check (this session): the five empty-state drawings share one line weight, one brand colour and corner ticks and read clearly at natural size; the Indonesia map shows seven island-group regions plus an outline; the workflow diagrams are legible only at their natural size (small type) and must be inlined; `contour-field.svg` is a quiet topographic texture; `survey-marks.svg` is a corner/crop tile. Workflow labels come from the audit's routes and statuses and must be confirmed against the real application.

### 2.6 Photography (60 catalogue records; NOT downloaded, NOT viewed)

`public/assets/photography/` is empty in the package (README: 'empty until you run the downloader'; `projects/` stays empty by design). The 60 records come from `docs/photo-catalog.json`: homepage 6, about 4, library 50 (pertanian 13, pesisir 12, pasar 9, pendidikan 8, desa 7, komunitas 1). Licences: Pexels 41, Unsplash 19; no attribution required, courtesy credit printed. `people` comes from page text only; nothing was checked by eye. `credits.json` is produced by `scripts/fetch-photos.php`, which has **not** been run, so it does not exist yet. 8 groups of candidates were deliberately held back (Unsplash+/Getty, flagged path, outside Indonesia, location unconfirmed, children close-ups, unreadable pages, unreachable sites).

**Decision for all 60: Defer (D-33).** Do not download any until the Source A rights check (D-30) and the Phase 4/5 layouts show a slot that no existing photograph fills. Where a record says `minors`, hold it back from prominent placement; where it says `unknown`, review before use.

| Local path (when downloaded) | Title | Location | Creator | Source | Licence | People | Catalogue use |
|---|---|---|---|---|---|---|---|
| `homepage/purwojati-banyumas-rice-fields.webp` | Peaceful Green Rice Fields in Purwojati Village, Banyumas | Purwojati Village, Banyumas, Indonesia | Mufid Majnun | Unsplash | Unsplash License | unknown | Homepage band |
| `homepage/flooded-rice-paddies-farmers-aerial.webp` | Farmers working in flooded rice paddies surrounded by palm trees | Indonesia (page lists 'Indonesien') | Jan Hildebrand | Unsplash | Unsplash License | unknown | Homepage band |
| `about/jatiluwih-bali-rice-planting.webp` | person planting rice on field during daytime | Jatiluwih Rice Terraces, Tabanan Regency, Bali, Indonesia | Shayan Ghiasvand | Unsplash | Unsplash License | unknown | About: editorial figure |
| `about/aceh-rice-field-farmer.webp` | person farming on rice field | Aceh, Indonesia | Sandy Zebua | Unsplash | Unsplash License | unknown | About: editorial figure |
| `about/pati-farmers-breakfast-in-field.webp` | a group of women sitting in a field (caption: 'Sarapan') | Pohijo, Pati Regency, Central Java, Indonesia | Redicul Pict | Unsplash | Unsplash License | unknown | About: editorial figure |
| `about/tegalwaru-west-java-aerial-rice-fields.webp` | Aerial View of Landscape | Tegalwaru, Jawa Barat, Indonesia | Tom Fisk | Pexels | Pexels License | unknown | About: editorial figure |
| `library/komunitas/jawa-barat-community-park-cleanup.webp` | Community Cleanup Effort in Jawa Barat | Jawa Barat, Indonesia | May umaran | Pexels | Pexels License | unknown | Story: kerja bakti / community action |
| `library/pesisir/ntb-mangrove-reforestation-volunteer.webp` | Man Volunteering in Mangrove Reforestation | Nusa Tenggara Barat, Indonesia | ADollarForCoral | Pexels | Pexels License | one | Story: SDG 13/14 projects |
| `library/pesisir/jawa-barat-planting-mangroves-adult-and-child.webp` | Father and Son Planting Mangroves in Indonesia | Jawa Barat, Indonesia | Noel Snpr | Pexels | Pexels License | minors | Story (check crop; shows a child) |
| `library/pendidikan/pandeglang-classroom-teacher-and-students.webp` | Indonesian Classroom Scene with Teacher and Students | Pandeglang, Banten, Indonesia | el jusuf | Pexels | Pexels License | minors | Story: KKN teaching |
| `library/pendidikan/pandeglang-classroom-lesson.webp` | Indonesian Classroom with Students and Teacher | Pandeglang, Banten, Indonesia | el jusuf | Pexels | Pexels License | minors | Story: KKN teaching |
| `library/pendidikan/pandeglang-students-learning.webp` | Students Learning in a Classroom in Indonesia | Pandeglang, Banten, Indonesia | el jusuf | Pexels | Pexels License | minors | Story: KKN teaching |
| `library/pendidikan/yogyakarta-graduate-in-university-corridor.webp` | Southeast Asian Graduate Smiling in Corridor | Yogyakarta, Indonesia | Yazid N | Pexels | Pexels License | one | Story: student profile (generic; do not caption as a user) |
| `library/pertanian/central-java-farmer-terraced-fields.webp` | Farmer Working in Central Java Agricultural Fields | Central Java, Indonesia | Ruyat Supriazi | Pexels | Pexels License | one | About / story |
| `library/pertanian/west-java-elderly-farmers-harvest-rice.webp` | Farmers Harvesting Rice in West Java Field | West Java, Indonesia | Ruyat Supriazi | Pexels | Pexels License | adults | About / story |
| `library/pertanian/yogyakarta-farmer-planting-rice.webp` | Indonesian Farmer Planting Rice in Jogja Field | Yogyakarta, Indonesia | Suryo Patriandito | Pexels | Pexels License | one | About / story |
| `library/pertanian/bali-aerial-tractor-plowing-rice-field.webp` | Aerial View of Farmer Plowing Rice Field in Bali | Bali, Indonesia | Tom Fisk | Pexels | Pexels License | one | Homepage band candidate (aerial; person is small) |
| `library/pertanian/bali-farmers-picking-chili.webp` | Farmers harvesting chilis under sunny skies in Bali | Bali, Indonesia | Danang DKW | Pexels | Pexels License | adults | Story |
| `library/pertanian/tomohon-cabbage-field-farmers.webp` | Woman Working on Rural Field | Tomohon, North Sulawesi, Indonesia | Ronny Buol | Pexels | Pexels License | adults | Story; Sulawesi coverage |
| `library/pertanian/tangerang-farmers-packing-vegetables.webp` | Farmers Working on Field | Tangerang, Banten, Indonesia | Deden R | Pexels | Pexels License | adults | Story |
| `library/pertanian/east-java-woman-harvesting-tobacco.webp` | Traditional Farming in East Java with Tobacco Plants | East Java, Indonesia | setengah lima sore | Pexels | Pexels License | one | Story |
| `library/pertanian/west-java-drone-cropland-near-forest.webp` | Groomed paddy plantation near tropical forest in sunlight | West Java, Indonesia | Tom Fisk | Pexels | Pexels License | none | Homepage band candidate |
| `library/pertanian/bali-rice-terraces-lone-farmer.webp` | Serene Rice Terraces of Bali, Indonesia | Bali, Indonesia | Stijn Dijkstra | Pexels | Pexels License | one | Tall crop; story |
| `homepage/bengkulu-aerial-rice-terraces-sunset.webp` | Aerial View of Lush Rice Terraces in Bengkulu | Bengkulu, Indonesia | Rahmad Himawan | Pexels | Pexels License | none | Homepage band; Sumatra |
| `library/pertanian/central-java-farmer-casting-net-in-paddy.webp` | Indonesian Farmer Casting Fish Net in Rice Field | Central Java, Indonesia | Found GHI | Pexels | Pexels License | one | Story |
| `library/pesisir/west-java-fisherman-mending-nets.webp` | Indonesian Fisherman Repairing Fishing Nets at Harbor | West Java, Indonesia | kevin yung | Pexels | Pexels License | one | Story |
| `library/pesisir/phinisi-boats-in-harbor.webp` | Traditional Phinisi Boats in Harbor with Fisherman | Indonesia (no finer location on page) | Jeffry Surianto | Pexels | Pexels License | one | Story; Sulawesi/Nusantara maritime |
| `library/pesisir/bangka-belitung-fisherman-casting-net-sunrise.webp` | Fisherman at Sunrise in Bangka Belitung Islands | Bangka Belitung Islands, Indonesia | Faris Munandar | Pexels | Pexels License | one | Tall crop; story |
| `library/pesisir/west-sumatra-fisherman-net-from-canoe.webp` | Traditional Fishing in West Sumatra, Indonesia | West Sumatra, Indonesia | sumarno sumarno | Pexels | Pexels License | one | Story |
| `library/pasar/jakarta-fish-market-sorting.webp` | Fish Market Scene in Jakarta, Indonesia | Jakarta, Indonesia | Andri Wijayanto | Pexels | Pexels License | one | Story: UMKM |
| `library/pasar/ntb-fish-market.webp` | Bustling Fish Market in West Nusa Tenggara | West Nusa Tenggara, Indonesia | Fahry Samalewa | Pexels | Pexels License | unknown | Story: UMKM |
| `library/pasar/flores-local-market.webp` | Vibrant Local Market Scene in Flores, Indonesia | Flores, Indonesia | Smaart | Pexels | Pexels License | unknown | Story: UMKM; Nusa Tenggara |
| `library/pasar/banjarmasin-floating-market.webp` | Traditional Floating Market in Banjarmasin | Banjarmasin, South Kalimantan, Indonesia | muhammad djasmail | Pexels | Pexels License | adults | Story: UMKM; Kalimantan |
| `library/pasar/west-java-traditional-market.webp` | Vibrant Traditional Market in West Java, Indonesia | West Java, Indonesia | TaZe | Pexels | Pexels License | unknown | Story: UMKM |
| `library/pasar/pontianak-women-selling-at-market.webp` | Women Selling Food at the Market | Pontianak, Kalimantan Barat, Indonesia | Rizky Rianto | Pexels | Pexels License | adults | Story: UMKM; Kalimantan |
| `library/pasar/banten-elderly-women-at-market.webp` | Elderly Women Sitting on Ground Together | Banten, Indonesia | Tom Fisk | Pexels | Pexels License | adults | Story |
| `library/pasar/banten-street-market-shoppers.webp` | People with Shopping Bags Walking around a Street Market | Banten, Indonesia | Tom Fisk | Pexels | Pexels License | adults | Tall crop; story |
| `library/pendidikan/high-school-students-in-classroom.webp` | Indonesian High School Students in Classroom | Indonesia (no finer location on page) | Airlangga Jati | Pexels | Pexels License | minors | Story |
| `library/pendidikan/jawa-barat-student-with-tablet-on-campus.webp` | Young Adult Holding Tablet in Campus Setting Outdoors | Jawa Barat, Indonesia | Irma Sjachlan | Pexels | Pexels License | one | Story: student (generic; do not caption as a user) |
| `homepage/maluku-islands-aerial.webp` | Aerial View of Maluku Islands | Maluku Islands, Indonesia | Danny Hollander | Pexels | Pexels License | none | Homepage band; Maluku coverage |
| `library/pesisir/banda-neira-woman-drying-fish.webp` | Traditional Fish Drying in Banda Neira, Indonesia | Banda Neira, Maluku, Indonesia | Zainal Lakembe | Pexels | Pexels License | one | Story; Maluku |
| `library/pesisir/north-maluku-boats-on-beach-aerial.webp` | Aerial View of Boats on Tropical Beach in Indonesia | North Maluku, Indonesia | Rizky Arief Imanullah | Pexels | Pexels License | none | Story; Maluku |
| `library/desa/waigeo-barat-coastal-huts-aerial.webp` | Wooden Sheds on Tropical Sea Coast | Waigeo Barat Kepulauan, West Papua, Indonesia | Melson Dreams | Pexels | Pexels License | none | Story; Papua |
| `library/desa/papua-harbor-boats-and-locals.webp` | Bustling Harbor Scene in Papua, Indonesia | Papua, Indonesia | ria via | Pexels | Pexels License | unknown | Story; Papua |
| `library/desa/papua-island-landscape.webp` | Serene Island Scenery in Papua, Indonesia | Papua, Indonesia | Adiardi Zulfansyah | Pexels | Pexels License | none | Story; Papua |
| `library/desa/southeast-sulawesi-stilt-houses-by-river.webp` | Tranquil Waterfront Scene in South East Sulawesi | South East Sulawesi, Indonesia | Albar Buton | Pexels | Pexels License | none | Tall crop; Sulawesi |
| `homepage/cirebon-aerial-village-red-roofs.webp` | an aerial view of a village with red roofs | Ciledug, Cirebon, West Java, Indonesia | Mujib | Unsplash | Unsplash License | none | Homepage band; village + ricefield |
| `library/desa/penglipuran-village-path.webp` | a long narrow path between two buildings in a village | Desa Penglipuran, Bangli Regency, Bali, Indonesia | satria setiawan | Unsplash | Unsplash License | unknown | Story: desa |
| `library/desa/rural-road-house-and-mountain.webp` | a rural road with a house and a mountain in the background | Indonesia (no finer location on page) | Ahmad Hasan Hariri | Unsplash | Unsplash License | unknown | Story: desa |
| `homepage/kutai-kartanegara-aerial-town-and-river.webp` | Aerial view of a town, muddy river, and lush forest | Loa Kulu Kota, Kutai Kartanegara, East Kalimantan, Indonesia | Fiqih Alfarish | Unsplash | Unsplash License | none | Homepage band; Kalimantan |
| `library/pertanian/sidemen-bali-rice-terraces-at-dawn.webp` | A person walking through bright green rice terraces below a misty mountain at dawn | Sidemen, Bali, Indonesia (page: 'Mount Agung in the sunrise') | Geio Tischler | Unsplash | Unsplash License | one | Story |
| `library/pertanian/bali-woman-working-in-rice-field.webp` | a woman is working in a rice field | Bali, Indonesia | Deny Napitupulu | Unsplash | Unsplash License | one | Story |
| `library/pendidikan/universitas-indonesia-graduation-flags.webp` | Graduation ceremony with flags and student in cap | Universitas Indonesia, Depok, West Java, Indonesia | Reyhan Aviseno | Unsplash | Unsplash License | one | Story: campus (generic; do not caption as a user) |
| `library/pasar/surakarta-market-shoppers.webp` | a group of people shopping in a market | Surakarta, Central Java, Indonesia | Prabu Panji | Unsplash | Unsplash License | adults | Story: UMKM |
| `library/pendidikan/umy-graduates-in-red-gowns.webp` | A large group of people in red graduation gowns | Universitas Muhammadiyah Yogyakarta, Bantul, DI Yogyakarta, Indonesia | Mardanafin | Unsplash | Unsplash License | adults | Story: campus (crowd) |
| `library/pesisir/gili-gede-mangroves-on-shore.webp` | Mangrove trees with exposed roots on a sandy shore by a calm blue bay | Gili Gede, West Nusa Tenggara, Indonesia | Rowan Heuvel | Unsplash | Unsplash License | none | Story: mangrove / SDG 14 |
| `library/pesisir/batam-beach-litter-in-tree-roots.webp` | Litter is tangled in the roots of a tree | Viovio Beach, Batam, Riau Islands, Indonesia | Carl Tronders | Unsplash | Unsplash License | none | Story: waste / SDG 12-14 |
| `library/pesisir/bontang-wooden-dock-and-mangroves.webp` | brown wooden dock on lake during daytime | Bontang, East Kalimantan, Indonesia | Hanif Ramadhani | Unsplash | Unsplash License | none | Story; Kalimantan |
| `library/pesisir/komodo-boats-on-water.webp` | A group of boats floating on top of a body of water | Komodo Island, West Manggarai, East Nusa Tenggara, Indonesia | Dhiemas Afif Febriyan | Unsplash | Unsplash License | none | Story; NTT |
| `library/desa/kebumen-three-cyclists-across-field.webp` | Three person riding bikes on green grass field | Kebumen, Central Java, Indonesia | Dikaseva | Unsplash | Unsplash License | adults | Story: desa |

Target implementation path for any photo that is later downloaded and approved: `public/assets/photography/<homepage|about|library/*>/` exactly as in the catalogue (`credits.json` beside them).

### 2.7 Documentation, tools and fixtures

| Asset | What it is | Decision | Target implementation path |
|---|---|---|---|
| `README.md` | Package overview | Reuse (reference) | Keep with the package |
| `assets-manifest.json` (89.7 KB) | SHA-256 and byte size of 111 files; photo records | Reuse; extend when assets are added (ASSET_SOURCES.md section 7) | Repository root (INTEGRATION.md) or `student-go-docs/design-system/` (proposal) |
| `docs/DESIGN_SYSTEM.md` (17.8 KB) | Tokens, type, colour with measured contrast, components, page composition, review checklist | Reuse (pending D-17) | `student-go-docs/design-system/DESIGN_SYSTEM.md` (proposal) |
| `docs/INTEGRATION.md` (18.9 KB) | Copy-in steps, CSS wiring, layout sketch, migration order, separate record of P0 issues not fixed | **Adapt**: follow it except where section 13 of this inventory lists a conflict (logo and photo removal, `docs/` copy target) | Same as above |
| `docs/ASSET_SOURCES.md` (50.7 KB) | Source, creator, licence and path of every asset; 60 photograph rows | Reuse; this inventory extends it with Source A | Same as above |
| `docs/photo-catalog.json` (70.7 KB) | The 60 photo records, rules and held-back list | Reuse (input to the downloader) | Same as above |
| `docs/styleguide.html`, `docs/styleguide.css` | Living reference built from the real CSS; uses relative `../public/assets` paths | Reuse; **Adapt** the relative paths if it is moved out of `docs/` (and serve over HTTP, not `file://`) | Same as above |
| `docs/screenshots/styleguide-{1440,1366,1024,768,390,360}.png` | Style guide at six widths | Retain as reference only; do not ship in the app | Same as above |
| `scripts/fetch-photos.php` v1.2, `fetch-photos.sh` | Downloads the 60 photos to WebP and writes `credits.json`; PHP-curl with a system-curl fallback; needs a WebP encoder | Reuse; **do not run** until D-33 | `scripts/` |


## 3. Asset usage in existing Blade and CSS (Source C)

**What I could and could not read.** The repository is not linked to this session, so no Blade file, CSS source, JavaScript source, `vite.config.js` or `composer.json` was opened. Usage below comes from (1) the HTML that eight guest pages return when fetched read-only from the running app, (2) the two built Vite files and the manifest that the app serves, (3) the audit, and (4) file names. Existence of a file is never taken as proof of use.

### 3.1 Rendered guest pages (VERIFIED 2026-10-30)

| Route | Asset references found in the returned HTML | Notes |
|---|---|---|
| / | `karsa-logo.png` (img); `hero-background.jpg` (inline CSS url) | Also loads Google Fonts, Font Awesome 6.5.1, Leaflet 1.9.4, AOS 2.3.1, Boxicons 2.1.4, Alpine 3.13.3 from CDNs; Vite bundle `build/assets/app-CDP40zpX.css` / `app-34mOoJaZ.js`. |
| /about | `karsa-logo.png`; `mahasiswa-about.jpeg`; `about.jpg`; `handprints-about.jpeg`; `jaket-about.jpeg` | Four documentary or landscape photographs, all 4912x2760 or larger. |
| /contact | `karsa-logo.png` (2x); `contact.jpg`; `profile_18223127.jpg` (missing); `profile_13523136.jpg`; `profile_13523124.jpg` (404: file is `.jpeg`) | Two of three team portraits are broken. |
| /login | `karsa-logo.png`; `login-register-view.jpg` (inline CSS url) | Auth layout. |
| /register | `karsa-logo.png` | No photo; role chooser. |
| /register/institution, /register/company, /forgot-password | BLOCKED: HTTP 500 locally, nothing to read | Cause verified earlier only for `/forgot-password` (Vite manifest lacks `resources/css/auth-institution.css`) and `/register/student` (database query); for the other two UNKNOWN. Fix is outside discovery. |
| /student/dashboard (not logged in) | Redirects to the login page, so the page behind it was not read | All authenticated pages (21 student, 11 institution, 12 company) are BLOCKED for rendered-HTML evidence this session; their asset usage comes from the audit (OBSERVED) or the filenames (INFERRED). |

### 3.2 Built CSS, JS and the Vite manifest (VERIFIED)

- `public/build/manifest.json` has exactly two entries: `resources/css/app.css` -> `assets/app-CDP40zpX.css` and `resources/js/app.js` -> `assets/app-34mOoJaZ.js`. `/hot` returns 404, so the Vite dev server is not running and the app serves the built files.
- The built CSS (125,756 characters) contains **no** `url(...)` to an image or font and no `@font-face`. Images are therefore referenced from Blade: `<img src>` and inline `style="... url(/file)"`. So `resources/css/app.css` is not where the photo usage lives.
- The built JS file is 0 bytes (the audit reported the same). No image path can be read from it.
- Three guest pages (`/register/institution`, `/register/company`, `/forgot-password`) return HTTP 500 locally. For `/forgot-password` the cause was verified earlier in this discovery: the auth layout asks Vite for `resources/css/auth-institution.css`, which is not in the manifest, so `resources/css/` contains a file the build does not include. For `/register/student` the failure is a database query (D-12). For the other two the cause was not established (UNKNOWN). None of this is an asset decision; it is Stage One item S1-04.
- No `<link rel="icon">` and no `og:image` were found on `/` (VERIFIED earlier); `favicon.ico` is 0 bytes.

### 3.3 Libraries loaded at runtime from CDNs on `/` (VERIFIED)

| Library | Source | Used for | Decision |
|---|---|---|---|
| Google Fonts: Inter, Outfit, Merriweather, Lora | fonts.googleapis.com / fonts.gstatic.com | Website typography (3 stacks) | **Replace** by local Plus Jakarta Sans / Source Serif 4 (Phase 2-3, D-17). Keep any link a certificate view needs (D-32). |
| Font Awesome 6.5.1 | cdnjs | Used 2 times (jobs) (audit J24) | **Deprecate** after those two icons move to Lucide (D-20) |
| Boxicons 2.1.4 | unpkg | Used 0 times | **Deprecate** (D-20) |
| AOS 2.3.1 | unpkg | Decorative scroll animation | **Deprecate** (D-20; `data-aos` attributes removed with the templates) |
| Leaflet 1.9.4 + OpenStreetMap tiles | unpkg / OSM | Homepage coverage map and project map view | **Retain** (AD-08); only the marker icon is adapted |
| Alpine.js 3.13.3 | jsDelivr | Interactions (steppers, toggles, dropdowns) | **Retain** (vendoring is a separate decision, D-20) |

### 3.4 What a repository-connected agent must still do (Stage One)

Commands are read-only and have **not** been run; they are listed so Stage One can turn every INFERRED row of section 1 into a confirmed row (`PHASE_00` task list, checklist in section 12).

```bash
# 1. every file in public/, with size (excluding generated build output and the storage link)
find public -type f -not -path 'public/build/*' -not -path 'public/storage/*' -printf '%s\t%p\n' | sort -k2
# 2. for each file name, where is it referenced? (repeat per name; -F = fixed string)
grep -RIn -F 'hero-background.jpg' resources app routes config database --include='*.php' --include='*.css' --include='*.js' --include='*.blade.php'
# 3. generic patterns that reveal assets not named in this inventory
grep -RInE "asset\(|url\(|public_path\(|storage_path\(|Storage::(url|disk)" resources app routes config
# 4. certificate and font code
grep -RInEi "sertifikat|certificate|pinyon|poppins|dompdf|font-family" resources app routes config public --include='*.php' --include='*.css' --include='*.js'
# 5. build wiring
cat vite.config.js package.json; sed -n '1,80p' resources/css/app.css; ls -la resources/css resources/js public
```

## 4. Existing assets worth retaining

Retain and Reuse are different: *retain* means the file stays and keeps working; *reuse* means it earns a place in the design.

| Asset(s) | Why it is worth keeping | How it is used in the design | Gate |
|---|---|---|---|
| `mahasiswa-about.jpeg`, `handprints-about.jpeg`, `jaket-about.jpeg`, `dashboard-student2.jpeg`, `network-student.JPG`, `projects-student.jpeg`, `repo-student.jpeg` | Genuine KKN field photographs; the audit calls them the most authentic assets in the application and says to reuse them in story pages. High resolution, specific to the domain, most with faces turned away. | Framed, captioned figures on About and Home (at most one or two per page), documentary stories, never page backgrounds. Derivatives, not originals. | D-30 (rights and consent), D-34 (derivatives) |
| `about.jpg` | A strong landscape with no people (no consent issue). | One framed landscape on About or Home. | D-30, D-34 |
| `karsa-logo.png` | Live brand asset; the source of the brand colour; the product name is undecided. | Stays live until D-01/D-18; then replaced in templates or adapted. | D-01, D-18 |
| `profile_13523136.jpg`, `profile_13523124.jpeg` | Genuine personal photographs of team members. | Team list on About (Phase 5) with valid references; initials where a file is missing. | D-31 |
| Student profile photographs, institution/company logos, verification documents, problem gallery images, repository documents | Functional feature assets and user data. | Shown exactly as today; no new public exposure. | D-04, D-14, D-31 |
| Certificate templates, Pinyon Script, Poppins | Functional feature assets the user explicitly asked to keep; separate from website typography. | Untouched; compared before and after phases that touch fonts, layout or the asset pipeline. | D-32 |
| `robots.txt`, `public/build/*` | Framework and build output. | No change (build output is regenerated). | — |
| `contact.jpg`, `application-student.jpg`, `search-network.jpg`, `hero-background.jpg`, `login-register-view.jpg`, `dashboard-student.jpg`, `dashboard-student3.jpg` | Landscape or aerial photographs that may have value as framed figures if their origin is confirmed. | Held as a reserve library; no new placement until rights are known. They leave page-header and background roles. | D-30 |

## 5. Custom assets ready for integration

'Ready' means the file exists, its hash matches the manifest and it has a recorded licence. It does **not** mean it was tested inside the app (UNKNOWN).

| Group | Count | Ready for | Preconditions before Phase 2 copies it |
|---|---|---|---|
| Design tokens, components, fonts CSS | 3 files | Phase 2 (import into `resources/css/app.css`) | D-17 approved; D-29 location confirmed; Stage One reads `app.css` and the existing `@theme`; the re-tuned gray ramp and 8 px radii are global, so look at ten pages after the first build. |
| Local fonts (Plus Jakarta Sans, Source Serif 4) + OFL texts | 5 files | Phase 2 | D-17; certificate fonts excluded (D-32). |
| Lucide icons, sprite, icons.json, licence | 74 files | Phase 2/3 | D-35 (metadata strip); sprite must be same-origin. |
| Brand set (wordmark, symbol, favicon, touch icon) | 7 files | Phase 3 | D-01 and D-18 (name and mark). The favicon set can be adopted independently of the wordmark. |
| Empty-state SVGs | 5 files | Phases 3, 7, 10, 11, 13, 14, 15, 16, 19, 22-24 | None beyond Phase 2. |
| Workflow diagrams | 8 files | Phases 4, 5, 17, 19, 21 | Inline them; confirm each label against real routes and statuses. |
| Indonesia map SVGs, contour field, survey marks | 5 files | Phases 4, 5, 6, 26 | Confirm region naming; use sparingly. |
| Leaflet pins | 2 files | Phase 8 (and Phase 4 map) | Stage One reads how the map initialises markers. |
| Style guide, design-system docs, manifest | docs | Reference from Phase 2 on | Fix relative paths if moved. |
| `scripts/fetch-photos.*` | 2 files | Only if D-33 chooses (b) or (c) | A WebP encoder and `curl.cainfo` fixed (INTEGRATION.md section 7). |

## 6. Assets requiring adaptation

| Asset | Adaptation (original always kept) | Why | Phase |
|---|---|---|---|
| `about.jpg` (7360x4912, 3.6 MB), `dashboard-student3.jpg` (3827x5741, 3.3 MB) | Responsive WebP derivatives (for example 2400, 1600, 800 px wide), `srcset` and `sizes`, explicit `width`/`height`; Indonesian alt text; caption in the form 'Place - what is happening. Foto: name'. | Weight (audit J6), layout shift, accessibility. | 4, 5 (D-34) |
| `mahasiswa-about.jpeg`, `handprints-about.jpeg`, `jaket-about.jpeg`, `dashboard-student2.jpeg`, `network-student.JPG`, `projects-student.jpeg`, `repo-student.jpeg` (each 4912x2760, 0.4-1.2 MB) | Same derivative set; crop decisions (for example avoid showing identifiable people at full size in `repo-student.jpeg`); keep the uppercase `.JPG` reference as is until the derivative replaces it. | Weight; consent; Linux is case-sensitive. | 4, 5 (D-30, D-34) |
| `KARA-background.png` (1.7 MB) if it is kept | Optimise or replace with a flat surface / `contour-field.svg`. | No gradient backgrounds; weight. | 16 |
| `karsa-logo.png` (1430x566, 272 KB, drawn at 40 px) | If Karsa stays: optimised SVG or 2x PNG at its display size. If Student Go: replaced by `student-go-logo.svg` in templates. | Weight (audit J24); brand decision. | 3 (D-01, D-18) |
| `profile_13523124.jpeg` | Fix the template reference (`.jpg` -> `.jpeg`); do not rename the file. | Broken image on a public page. | 5 (D-31) |
| `profile_*` team portraits | Square crop derivative with a neutral file name, only if the user approves. | Student ID numbers in public file names. | 5 (D-31) |
| Package SVG and PNG files (96 + 7) | Strip the embedded C2PA `<metadata>` block on the copy in `public/assets/` (about 7.7 KB per SVG; 71 icons are about 570 KB instead of tens of KB); keep package originals and record both hashes. | Weight; hygiene. | 2 (D-35) |
| `tokens.css` / `components.css` | Merge with the app's existing `@theme` (it declares Instrument Sans without loading it); import after `@import "tailwindcss"`; keep the app's `@source` lines. | The tokens change gray and radius globally. | 2 (D-17, D-29) |
| `fonts.css` | Root-relative `/assets/fonts/...` URLs work only when the site is served from the domain root; check `APP_URL` and any sub-path deployment. | Would 404 under a sub-path. | 2 |
| Workflow SVGs | Inline into Blade (not `<img>`) so text uses Plus Jakarta Sans; use the vertical files under 768 px. | As `<img>` they fall back to a system font. | 4, 5, 17, 19, 21 |
| `map-pin.svg`, `map-pin-selected.svg` | `L.icon({ iconUrl, iconSize: [28, 38], iconAnchor: [14, 38] })`; map, tiles, clustering and handlers unchanged. | Replace the default blue pin only. | 8, 4 |
| `docs/styleguide.html` | Relative `../public/assets` paths break if the file moves; serve over HTTP. | Reference page. | 2 |
| `INTEGRATION.md` | Follow it, with the corrections in section 13 (logo and photo removal, `docs/` copy target). | It predates AD-09. | 2 |

## 7. Assets to deprecate

**Deprecate means: stop using in new UI.** No file below is deleted, moved or renamed. Archiving a deprecated file is a separate, explicit approval at Phase 28.

| What is deprecated | Reason | Replacement | Phase | Fate of the file |
|---|---|---|---|---|
| `hero-background.jpg` as the homepage hero background (with the radial-gradient blobs) | Audit G01: generic hero recipe; guardrails 1, 5, 7 | Short typographic hero; optional framed figure | 4 | Stays on disk |
| `contact.jpg` as a one-screen hero | Audit G03/J35: contact info hidden below the fold | Plain page header | 5 | Stays |
| `login-register-view.jpg` as the auth background | Audit G04: photo hero on a task page; low contrast | `contour-field.svg` + wordmark, or nothing | 6 | Stays |
| `dashboard-student2.jpeg` as the dashboard hero (and `dashboard-student.jpg`, `dashboard-student3.jpg` if referenced) | Audit S01: 370 px hero before any data; guardrail 1 | Page header with greeting and the next task | 7 | Stays; reused elsewhere |
| `application-student.jpg` (applications), `projects-student.jpeg` (projects), `network-student.JPG` and `search-network.jpg` (friends), `repo-student.jpeg` (repository) as page heroes (INFERRED mapping) | Same | Standard `.sg-page-header` | 10, 11, 13, 14 | Stay; documentary candidates |
| `company1-5.jpg`, `company-profile.jpg` as company/jobs heroes | Audit: unrelated corporate stock; guardrail 7 | No photo | 15, 21-25 | Stay until verified; archive only with approval |
| `KARA-background.png` as the chatbot background (INFERRED) | Gradient, decorative, 1.7 MB | Flat surface or `contour-field.svg` | 16 | Stays |
| Inline SVG mock-UI and flat-building illustration on `/` | Audit G01: generic illustration | Workflow diagrams; real product screenshot later | 4 | Markup in Blade (git history is the backup) |
| Leaflet default marker images | Off-brand | `map-pin*.svg` | 8 | Library file (CDN) untouched |
| Google Fonts, Font Awesome, Boxicons, AOS | Four font families in three stacks; unused or decorative libraries (audit C, J15, J23, J24) | Local fonts; Lucide; no AOS | 2, 3, 15 | CDN links removed from layouts; certificate-view links excepted (D-32) |
| Empty `favicon.ico` | 0 bytes | Package favicon set | 3 | Stays unless overwriting is approved |
| `karsa-logo.png` in templates | Only if D-01 picks Student Go | `student-go-logo.svg` | 3 | Stays on disk |

## 8. Missing assets

| Missing asset | Why it is missing / evidence | Needed for | Who can supply it |
|---|---|---|---|
| Provenance and consent records for every existing photograph | No credit, licence, `credits.json` or consent record exists for any Source A image (UNKNOWN). | Any new or larger placement (D-30) | The user / the KKN documentation team |
| `profile_18223127.jpg` | Referenced by `/contact`, not found under seven extensions (VERIFIED). | Team list (Phase 5) | The user (or use initials) |
| A vector source of the Karsa logo | Only a 1430x566 PNG exists (OBSERVED/VERIFIED). | Option (c) of D-18 | The user / designer |
| Certificate templates and fonts: locations | Not found at probed paths; no certificate route in the audit (UNKNOWN / BLOCKED). | Protecting them (D-32) | Stage One or the user |
| Location and storage of uploaded avatars, logos, verification documents | Cannot be listed without the repository (BLOCKED). | Preservation and privacy checks | Stage One |
| A real product screenshot of the finished application | INTEGRATION.md: the homepage 'needs ... a real screenshot of the finished app'; none exists. | Homepage (Phase 4, last) | Produced after Phases 7-25 |
| Official SDG icons / colours | The audit says to use the official UN guidance; neither source includes them (the package has a generic `target` icon). | About, project tags (Phase 5, 9, 18) | The user (UN SDG logo terms apply) |
| Empty-state art for institution and company lists | Package has 5 empty states (applications, documents, notifications, saved projects, search results). No 'no problems yet', 'no jobs', 'no talent saved', 'no reviews'. | Phases 17-19, 22-25 | Use a plain text empty state, or commission four more SVGs in the same family (not generated in this task) |
| 404 / 403 / 500 illustration | Neither source has one; the audit's E01/E02 call for plain text. | Phase 26 | None needed |
| Share image (`og:image`) and PWA icons | Not found on `/` (VERIFIED); none in the package. | Public pages | To be decided after D-01 |
| Student photograph of the team in a neutral file name | Current files embed student IDs (D-31). | Contact/About | The user |
| Photographs of real projects and institutions | The package rule: `photography/projects/` stays empty; project photos come from uploads (and 310 gallery images are unreachable locally, J12). | Phase 8, 9 | Environment fix / uploads |

## 9. Licence and attribution concerns

Nothing below is legal advice; it lists what has to be confirmed.

| Item | Concern | Severity | Action |
|---|---|---|---|
| All Source A photographs (about 25 files) | No source, licence or credit recorded. Some look like genuine field photographs, some like landscape or aerial images of unknown origin, the `company*.jpg` set like generic corporate stock whose origin (licensed stock, generated, scraped) cannot be told from the file. | High | User confirms origin and rights per file (D-30); no new prominent placement until confirmed; keep a Source A credits table in this inventory. |
| People in the field photographs | Consent and intended public use are undocumented. `handprints-about.jpeg` shows one person in profile; `repo-student.jpeg` shows a crowd; `jaket-about.jpeg` shows KKN ITB branding. Minors are not clearly present but cannot be ruled out in full-resolution crops. | High | Consent check, crop decisions, captions that do not imply endorsement. |
| Team portraits (`profile_<id>.jpg`) | Personal photographs on a public page; file names contain student ID numbers; two links are broken. | Medium | D-31. |
| Student profile photographs | Public profiles expose contact data to guests (J3, D-04); photos may be public too; storage unknown. | High | Preserve; no new exposure; decide with D-04. |
| Pexels photographs in the catalogue (41) | Pexels licence: free for commercial use; do not depict identifiable people in a bad light; do not sell unmodified copies; do not imply endorsement; not for use as a trademark; no redistribution on other stock platforms. | Medium | Courtesy credit printed anyway; review people; do not commit downloads to a public stock-like repository without checking. |
| Unsplash photographs in the catalogue (19) | Unsplash licence: may not compile images into a competing service. Items under Unsplash+ or credited to Getty were excluded. | Low | Keep the catalogue's `license_url`. |
| 5 catalogue photos with minors, 13 with unknown people status | Identifiability not checked by eye. | Medium | Hold back from prominent placement; review before use. |
| Fonts: Plus Jakarta Sans, Source Serif 4 | SIL OFL 1.1; Reserved Font Name 'Source'; the WOFF2 files are Fontsource subsets. | Low | Keep `OFL.txt` beside each font; do not rename or modify the files. |
| Lucide icons | ISC; portions from Feather (MIT). | Low | Keep `LICENSE-lucide.txt` beside the icons. |
| Indonesia map SVGs | Natural Earth is public domain; boundaries follow Natural Earth's policy, not an official claim. | Low | Keep the decorative label; do not use as an authoritative border map. |
| Certificate fonts (Pinyon Script, Poppins) | Licences not checked (location unknown). Both are commonly distributed under the SIL OFL, but that was not verified here. | Unknown | Stage One records where the files come from and their licence. |
| Embedded C2PA metadata in the package files | A Content Credentials block is embedded in 96 SVGs, 7 PNGs and `styleguide.html`; it says Claude provided the file at a user's request and may have created or modified it. This is provenance, not a licence problem, but it adds weight. | Low | D-35. |
| Original work in the package (brand, illustrations, CSS, scripts) | Created for this project; no third-party rights recorded. | Low | None. |

## 10. Proposed integration paths

**These are proposals, not permission.** Nothing is copied, moved or renamed until the phase that owns the step starts and its gating decisions are answered. The package does not have to stay nested; its internal folder layout already mirrors the Laravel project root, which is why INTEGRATION.md can copy it in.

### 10.1 Map

| From (package, `student-go-assets/...`) | Proposed canonical destination (project root) | Collision check | Laravel / Vite notes | Phase |
|---|---|---|---|---|
| `public/assets/brand/` | `public/assets/brand/` | `/assets/brand/*` 404 (VERIFIED); does not touch root `karsa-logo.png` or `favicon.ico` | Reference with `{{ asset('assets/brand/...') }}` | 2-3 |
| `public/assets/fonts/` | `public/assets/fonts/` | `/assets/fonts/*` 404 (VERIFIED) | Served from `public/`; `fonts.css` URLs are root-relative | 2 |
| `public/assets/icons/ui/` | `public/assets/icons/ui/` | 404 (VERIFIED) | Sprite must be same-origin | 2-3 |
| `public/assets/illustrations/{empty-states,workflows,geographic,motifs}/` | `public/assets/illustrations/...` | 404 (VERIFIED for `motifs/map-pin.svg`) | Inline workflows; `L.icon` for pins | 2-8 |
| `public/assets/photography/{homepage,about,library/*}/` (empty until the downloader runs) | `public/assets/photography/{homepage,about,library/*}/` | No collision; nothing exists | `credits.json` written beside the files by the script | 4-5 only if D-33 chooses |
| *(new, from Source A)* WebP derivatives of existing field photographs | `public/assets/photography/field/` | New folder; originals stay at `public/*.jpg\|jpeg\|JPG` | Names `<subject>-<width>.webp`; D-34 | 4-5 |
| *(new, from Source A)* approved team crops | `public/assets/photography/team/` | New folder | Only after D-31 | 5 |
| `resources/css/student-go/{fonts,tokens,components}.css` (the real location; the request's `public/resources/css/student-go/` does not exist) | `resources/css/student-go/` | Unknown until Stage One lists `resources/css/` (the build already misses at least one CSS entry) | `@import` from `resources/css/app.css` after `@import "tailwindcss"`; manifest entries today: `resources/css/app.css`, `resources/js/app.js`; merge, do not duplicate, the existing `@theme`; run `npm run build` | 2 |
| `docs/*` (design system, asset sources, integration, catalogue, style guide, screenshots) | `student-go-docs/design-system/` (proposal; INTEGRATION.md copies to `docs/`) | Avoids any existing `docs/` folder (UNKNOWN until Stage One) | Update the style guide's relative `../public/assets` paths if moved | 2 |
| `scripts/fetch-photos.php`, `fetch-photos.sh` | `scripts/` | UNKNOWN until Stage One | Run only after D-33 | 2 (copy), later (run) |
| `assets-manifest.json` | Repository root, or `student-go-docs/design-system/` | UNKNOWN | Extend when assets are added | 2 |
| `.gitignore` line `/storage/app/asset-originals/` | `.gitignore` | Stage One reads it (S1-27) | Needed only if originals are stored there | 2 |
| Existing `public/*` (Source A) | **No move** | — | References change only in the phase that replaces them | — |
| `public/build/` | Generated by `npm run build` | — | Never edit by hand | — |

### 10.2 CSS wiring (Laravel 13, Tailwind CSS 4.1.16, Vite)

```css
/* resources/css/app.css (proposed; Stage One first reads the current file) */
@import "tailwindcss";
@import "./student-go/fonts.css";
@import "./student-go/tokens.css";
@import "./student-go/components.css";
/* keep the existing @source lines; merge any existing @theme instead of duplicating it */
```

- Check after the build: `getComputedStyle(document.documentElement).getPropertyValue('--sg-brand')` returns `#405080`; the Network tab shows the two Plus Jakarta Sans files from `/assets/fonts/` with status 200 and no request to `fonts.googleapis.com` (INTEGRATION.md section 3).
- Vite normally leaves root-relative `url("/assets/...")` untouched; a 'will be resolved at runtime' notice is expected. Not yet verified through Vite in this project (UNKNOWN).
- Blade references use `asset('assets/...')`; CSS uses root-relative URLs. If the app is ever served from a sub-path, the CSS URLs must be revisited.
- Keep the layout's existing `@vite([...])` list; do not add a new entry unless Stage One finds the other CSS files (`auth-institution.css`) that the manifest is missing.
- Filename collisions: none known. The rule is never overwrite: a target name that already exists stops the step and is reported.
- Originals stay: every Source A file keeps its path and name; every package file keeps its package copy until integration is tested (rollback = remove the three `@import` lines and the new folders).

### 10.3 Gates and order

1. **Phase 0 (Stage One)**: read-only; confirm sections 1-3; locate certificate and upload assets; list `public/` with hashes.
2. **Phase 2**: copy Source B to the targets above after D-17, D-29, D-35 (and D-32 for fonts). Source A untouched.
3. **Phase 3**: link favicon and (if D-01/D-18 allow) the wordmark from the layout; Source A files stay.
4. **Phases 4-5**: derivatives of Source A photographs (D-30, D-34); Source B photographs only if D-33 allows.
5. **Phases 6-25**: each page stops referencing its deprecated hero in that phase and not before.
6. **Phase 28**: final reconciliation; archiving of deprecated files only with explicit approval.

## 11. Overlaps: where both sources serve the same purpose

Where the two sources serve the same purpose, the recommendation below is the starting position; each is a planning recommendation that the owning phase re-checks.

| # | Purpose | Source A (existing) | Source B (custom) | Recommendation |
|---|---|---|---|---|
| 1 | Logo / wordmark | `karsa-logo.png` (A) | `brand/student-go-logo.svg`, `-on-dark.svg`, symbol files (B) | These are two names, not two drawings of one name. Gated by D-01 and D-18. Keep the PNG live until the decision; if Student Go is chosen, use the SVG and keep the PNG on disk; if Karsa is kept, adapt the PNG into an SVG or 2x PNG and do not use the Student Go wordmark. Never ship both in one header. |
| 2 | Favicon and app icons | `favicon.ico` 0 bytes (A) | `brand/favicon.svg`, `favicon.ico`, `apple-touch-icon.png` (B) | B wins. A is empty. Link the package files from the layout; do not overwrite the root file. |
| 3 | Homepage imagery | `hero-background.jpg`, `about.jpg`, `dashboard-student2.jpeg`, `network-student.JPG`, `projects-student.jpeg` (A) | 6 homepage candidates in the catalogue, not downloaded (B) | Prefer A: it is already in the project, it is Indonesian, and several frames show real KKN activity (audit: the most authentic assets). Decide only after rights are confirmed (D-30). Use at most one or two, framed and captioned, never as a background. Download nothing from B until a slot is left empty (D-33). |
| 4 | About-page photographs | `mahasiswa-about.jpeg`, `handprints-about.jpeg`, `jaket-about.jpeg`, `about.jpg` (A) | 4 About candidates in the catalogue, not downloaded (B) | Prefer A for the same reason. B is the fallback for a missing landscape or for any A photo whose rights cannot be confirmed. |
| 5 | Auth side imagery | `login-register-view.jpg` (A) | `motifs/contour-field.svg` + wordmark (B) | Prefer B (no photo): it is lighter, has no rights question, and matches the design principle that the auth panel is a task surface. Keep A as an unused fallback figure. |
| 6 | KARA assistant background | `KARA-background.png` (A) | `motifs/contour-field.svg` or a flat surface (B) | Prefer B or flat. The gradient PNG breaks the no-gradient rule and weighs 1.7 MB. Keep the file until Phase 16. |
| 7 | Company page imagery | `company-profile.jpg`, `company1-5.jpg` (A) | Nothing; the package deliberately has no corporate photos (B) | No photo. The plain page header carries the page. If a company logo exists it is shown, otherwise initials. |
| 8 | Icons | Inline SVG (outline style), Boxicons 2.1.4 (used 0 times), Font Awesome 6.5.1 (used 2 times) (A/C) | Lucide sprite, 71 icons at 1.5 px (B) | B wins: one family, local, ISC licence. Migrate page by page; remove the two CDN libraries only when no template uses them (D-20). |
| 9 | Website typography | Inter, Outfit, Merriweather, Lora from Google Fonts (A/C) | Plus Jakarta Sans + Source Serif 4, local WOFF2 (B) | B wins for the website (pending D-17). Certificate typography (Pinyon Script, Poppins) is a separate, protected stack and is outside this decision (D-32). |
| 10 | Map markers | Leaflet default pin (images from unpkg) (C) | `motifs/map-pin.svg`, `map-pin-selected.svg` (B) | B via `L.icon`; the map, tiles and handlers do not change (AD-08). The Leaflet library itself stays. |
| 11 | Empty states | Blue icon tiles, broken `<img>`, 'No Image' boxes (C) | 5 empty-state SVGs (B) | B where one fits the page; otherwise a plain text empty state. Institution/company lists have no matching SVG (section 8). |
| 12 | Home-page generic illustrations | Inline SVG mock UI with star badge and flat buildings (C) | Workflow diagrams and `indonesia-regions.svg` (B) | B. Those inline SVGs are markup inside Blade, not files; they are retired in Phase 4 by changing the template, with git history as the backup. |
| 13 | Team and profile photographs | `profile_*.jpg\|jpeg` (A) | Initials avatar in CSS (B) | Keep A where valid; B (initials) where the file is missing. Never replace a genuine photo with a stock one. |
| 14 | Social / share image | None found on the pages read (UNKNOWN) | None | Missing in both sources (section 8). |

## 12. Stage One asset verification checklist

These extend the Stage One list in `DISCOVERY_REPORT.md` section 12 (S1-01 to S1-28). All are read-only.

| ID | Check (read-only) | Why | How |
|---|---|---|---|
| S1-29 | List `public/` recursively with size and SHA-256 (excluding `build/` and `storage/`); diff against section 1 of this inventory. | Confirms the list is complete; adds files nobody named. | `find`, `sha256sum` |
| S1-30 | For every file in `public/` (and every package asset to be used) search `resources/`, `app/`, `routes/`, `config/`, `database/` for its name. | Turns INFERRED usage into confirmed usage; finds unreferenced files. | `grep -RIn -F` |
| S1-31 | Find certificate templates, certificate fonts (Pinyon Script, Poppins) and the code that renders certificates; produce one sample certificate if safe. | Protected asset group (D-32); baseline for later comparison. | `grep -RInEi`, controllers, DomPDF config |
| S1-32 | Find where student avatars, institution/company logos, verification documents, problem images are stored and how Blade renders them; check `public/storage` and `FILESYSTEM_DISK`. | Preservation and privacy (D-04, D-31). | `config/filesystems.php`, Blade |
| S1-33 | Read `vite.config.js`, `resources/css/app.css`, `resources/js/app.js`, `package.json`; list `resources/css/` and explain every CSS file missing from the manifest. | Confirms CSS wiring (D-29) and the HTTP 500 cause. | — |
| S1-34 | Check `public/assets/` and the proposed targets for collisions; list `docs/`, `scripts/`. | Section 10 collision column. | `ls` |
| S1-35 | Ask the user for provenance and consent per existing photograph (table in section 1) and record the answers here. | D-30. | — |
| S1-36 | Confirm the two broken Contact references and whether `profile_18223127.*` exists under another name. | D-31, Phase 5. | `find public -name 'profile_*'` |
| S1-37 | Check EXIF and metadata (camera, GPS, author) on the field photographs; GPS must not be shipped. | Privacy of locations and people. | `exiftool` (if installed) |

## 13. Where INTEGRATION.md is adapted

`student-go-assets/docs/INTEGRATION.md` was read first, as directed. Its approach (copy folders into the Laravel root, import three CSS files, migrate layout first) is adopted. Where it conflicts with AD-09 it is adapted:

| INTEGRATION.md says | Conflict | Adapted instruction |
|---|---|---|
| Section 4: remove 'The 272 KB PNG logo' (J24) | Deletes a live brand asset before the name decision. | Retain `karsa-logo.png`; stop referencing it from templates only when D-01/D-18 choose Student Go, and only in Phase 3. |
| Section 4: wordmark 'replaces the Karsa/PNG logo' and set `APP_NAME="Student Go"` | A naming decision, not an asset swap. | D-01 first. `.env` is not touched by any asset phase. |
| Section 4: remove Google Fonts links and font-family overrides | Could break certificate rendering if a certificate view shares the layout or link. | Remove from site layouts only after D-32 locates certificate fonts. |
| Section 6 step 7 and Section 5 J6: replace each photo hero | Treats the photo files as disposable. | Replace the hero usage (template change); keep the photos; reuse the genuine ones as framed figures. |
| Section 2: `cp -R docs/* $L/docs/` | May collide with an existing `docs/` folder. | Target `student-go-docs/design-system/` (proposal) or check Stage One first. |
| Section 7 / ASSET_SOURCES: catalogue photographs are 'the pool' | Ignores the existing authentic photographs. | Existing field photographs first (D-30); catalogue only for gaps (D-33). |
| Section 2: `mkdir -p`, `cp -R` into `public/assets` | Safe only if nothing there is overwritten. | Never overwrite; stop on any name collision; keep package originals. |
| `README.md`: 'copy it over the project root' | Same. | Same. |

## 14. Evidence, limits and what was not done

- No repository access in this session (no linked computer). Source C (`resources/`) was **not read**; its content is inferred from rendered HTML and the built Vite files only.
- Source A: file existence, pixel size and byte size are VERIFIED for 27 files by loading them from the running app; images were looked at in thumbnails (contact sheet in a scratch browser tab; nothing saved or copied). EXIF/metadata, true file dates and the rest of `public/` were not inspected.
- Authenticated pages (21 student, 11 institution, 12 company) were not rendered this session (not logged in), so their asset usage is OBSERVED (audit) or INFERRED (file name) only. Three guest pages return HTTP 500 locally and could not be read.
- Source B was read recursively on disk (124 files); SVGs were rendered and viewed; the 60 photographs do not exist locally and were not viewed.
- Nothing was deleted, overwritten, moved, renamed, copied into a production directory, generated, downloaded or integrated. The package hashes recorded before this work (`package-snapshot.sha256`) are re-checked at the end of the task.
- Probing `public/` by guessing file names is not a directory listing; a file I did not guess may exist.

## 15. Phase 2 integration record (2026-10-30)

Phase 2 copied Source B into the project under the answers D-17 (a), D-18 (a), D-29 (staged), D-33 (copy all 60) and D-35 (strip on the copy). The copy was additive: a collision check ran first, and the script refuses to overwrite. Source A was not touched: the 38 hashes in `baseline/asset-hashes.txt` still match, and `git status` under `public/` shows only new folders. Full evidence: `reports/PHASE_02_REPORT.md`.

### 15.1 Source B, now in the project

| Package path | Project path | Files | Decision | Placed in a page? |
|---|---|---|---|---|
| `public/assets/brand/` | `public/assets/brand/` | 7 | Reuse (Student Go wordmark, symbol, favicons; D-01 b, D-18 a) | No (Phase 3 links them) |
| `public/assets/fonts/` | `public/assets/fonts/` | 5 (3 WOFF2 + 2 OFL) | Reuse (website fonts) | Declared in `fonts.css`; downloaded only when used |
| `public/assets/icons/ui/` | `public/assets/icons/ui/` | 74 (71 SVG, sprite, icons.json, licence) | Reuse (single icon system) | No; helper `<x-icon>` exists |
| `public/assets/illustrations/**` | `public/assets/illustrations/**` | 21 SVG | Reuse | No |
| `public/assets/photography/**` | `public/assets/photography/**` | 60 WebP + `credits.json` | Reuse only for slots Source A cannot fill (D-33). Preliminary review: 37 available, 14 with care, 9 held (`student-go-docs/design-system/photo-review.json`) | No (D-19 open) |
| `resources/css/student-go/` | `resources/css/student-go/` | 3 + `legacy-overrides.css` (new, split from the package files without changing any value) | Adapt (staged wiring D-29) | 3 imported; overrides staged for Phase 3 |
| `scripts/fetch-photos.*` | `scripts/` | 2 | Retain (not run) | — |
| `docs/*`, `assets-manifest.json`, `README.md` | `student-go-docs/design-system/` (README → `PACKAGE_README.md`) | 14 | Retain + updated with the actual implementation | — |
| `storage/app/asset-originals/` | **not copied** | — | Stays in the package only | — |

The project copies of the 96 SVGs, 7 PNGs and the style guide HTML differ from the package only by the removed C2PA block. Pixel-identical rendering was verified. Both hashes are in `student-go-docs/design-system/c2pa-strip-hashes.tsv`, which was written at copy time; files edited later in Phase 2 (the three docs and the two CSS files) differ from the copy-time hash on purpose.

### 15.2 Source A in Phase 2

| Asset | Decision | Status |
|---|---|---|
| `karsa-logo.png` | Retain until Phase 3 replaces the reference (D-18 a); never deleted | Untouched, still in the layout |
| `favicon.ico` (0 bytes, root) | Retain on disk; `brand/favicon.*` replaces the reference in Phase 3 | Untouched |
| All photographs in `public/` | Retain; rights still open (D-30) | Untouched; the regression pages that show them load them with HTTP 200 |
| `public/assets/certificate/**` | Protected (D-32) | Byte-identical; re-rendered certificate sample is pixel-identical to the Phase 0 baseline |
| Google Fonts, Font Awesome, Boxicons, AOS (CDN) | Deprecate in Phase 3 (D-20 open) | Still loaded; nothing removed |

### 15.3 Findings for later phases

- Two catalogue photographs show children although the catalogue says `one`: `east-java-woman-harvesting-tobacco.webp` and `ntb-mangrove-reforestation-volunteer.webp`. Both are held.
- The dev server (`php artisan serve`) sends no `Cache-Control` header for `/assets/**`. Long-lived caching for fonts and icons needs web-server configuration at deployment; it is outside the app code.
- `legacy-overrides.css` must be activated in Phase 3 before any page adopts `.sg-*` classes.

## 16. Phase 3 shell record (2026-10-30)

Phase 3 changed **references only**; no file under `public/` was added, deleted, overwritten, moved or renamed (38/38 Phase 0 hashes still match; only the generated, git-ignored `public/build/` was rebuilt).

| Asset | Source | Decision in Phase 3 | Where it is used now |
|---|---|---|---|
| `public/karsa-logo.png` | A | **Replace** in the header and footer templates (D-01, D-18). Kept on disk. Still referenced by pages not yet migrated, if any (none found in the shell). | — |
| `public/favicon.ico` (0 bytes) | A | **Retain** on disk, not overwritten. The layouts link the package set instead. | — |
| `public/assets/brand/student-go-logo.svg` | B | **Reuse** | Navbar wordmark (all layouts) |
| `public/assets/brand/student-go-logo-on-dark.svg` | B | **Reuse** | Footer |
| `public/assets/brand/favicon.svg`, `favicon.ico`, `apple-touch-icon.png` | B | **Reuse** | `<head>` of both layouts and the 5 standalone auth pages |
| `public/assets/fonts/plus-jakarta-sans/*.woff2` | B | **Reuse** | Site font (`legacy-overrides.css` now active); latin file preloaded; also aliased to the names 'Outfit' and 'Inter' for un-migrated inline styles |
| `public/assets/icons/ui/sprite.svg` | B | **Reuse** | Shell icons, notification types, toasts, components; replaces the 10 Font Awesome icons on `/student/jobs/saved` |
| `public/assets/illustrations/empty-states/*.svg` (5) | B | **Reuse** | `x-ui.empty-state`; `no-notifications.svg` in the bell panel |
| Google Fonts Inter/Outfit (layout), fonts.bunny.net Inter (auth layout), Font Awesome 6.5.1, Boxicons 2.1.4 | CDN | **Deprecate** (D-20 a): links removed from the layouts | Page-level Google Fonts links (Space Grotesk on 8 pages, Lora/Merriweather on home) remain until their page phases |
| `ui-avatars.com` generated avatars | external | **Deprecate in the shell**: `x-ui.avatar` shows initials instead of requesting the generated image; the model accessors are unchanged | Pages not yet migrated still request it |
| Certificate template and fonts | A (protected, D-32) | **Retain**, untouched (hashes asserted by `tests/Feature/Phase2/DesignSystemAssetsTest.php`) | `CertificateService` (unchanged) |

## 17. Phase 4 homepage record (2026-10-31)

Phase 4 changed **references only**. No file under `public/` was added, deleted, overwritten, moved or renamed (38/38 Phase 0 hashes still match; only the generated `public/build/` was rebuilt). No image was downloaded or generated.

| Asset | Source | Decision in Phase 4 | Where it is used now |
|---|---|---|---|
| `public/hero-background.jpg` | A | **Deprecate (usage)**: no longer referenced by the homepage (it was the CSS background of the old hero). Kept on disk. D-30 still OPEN, so it got no new placement. | — |
| `about.jpg`, `mahasiswa-about.jpeg`, `handprints-about.jpeg`, `jaket-about.jpeg`, `dashboard-student2.jpeg`, `network-student.JPG`, `projects-student.jpeg`, `repo-student.jpeg` | A | **Retain**, not placed: rights and consent unconfirmed (D-30). Candidates for later phases once confirmed (they show real KKN ITB 2025 field work). | unchanged |
| `public/assets/photography/homepage/purwojati-banyumas-rice-fields.webp` | B (Unsplash licence, `credits.json`) | **Reuse**: framed figure, place caption, no visible credit (user, D-19 answer 2026-10-31; the licence does not require attribution). Preliminary review 'available' (no identifiable people). Alt: 'Hamparan sawah hijau di tepi desa dengan deretan pohon dan bukit di kejauhan.' | Homepage, field-notes band (`loading="lazy"`) |
| `public/assets/photography/homepage/cirebon-aerial-village-red-roofs.webp` | B (Unsplash licence) | **Reuse**: second framed figure, ≥ 768 px only. Alt: 'Foto udara permukiman desa beratap genteng merah, dikelilingi sawah dan ladang.' | Homepage, field-notes band |
| Other 4 `homepage/*.webp` and the `about/`, `library/` pool | B | Not placed | — |
| `illustrations/geographic/indonesia-regions.svg` | B | **Reuse**, inlined; island groups tinted from real data (`is-active`), styles scoped to `.sg-home-regions` at render time (file unchanged) | Homepage hero plate |
| `illustrations/motifs/contour-field.svg` | B | **Reuse** via `.sg-contour` (the page's one backdrop) | Homepage hero plate |
| `illustrations/motifs/map-pin.svg`, `map-pin-selected.svg` | B | **Reuse** as Leaflet `L.icon` (28×38, anchor 14,38) | Homepage map |
| `illustrations/workflows/student-journey(-vertical).svg`, `institution-journey(-vertical).svg` | B | **Reuse**, inlined (page font), styles scoped to `.sg-home-wf`; horizontal ≥ 1024 px, vertical below | Homepage 'Cara kerja' |
| `illustrations/workflows/company-journey*.svg`, `project-status*.svg` | B | Not placed (company described in text only; diagram not needed for the page's purpose) | — |
| `illustrations/geographic/indonesia-outline.svg`, `motifs/survey-marks.svg` | B | Not placed | — |
| AOS 2.3.1 (unpkg), Google Fonts Merriweather/Lora | CDN | **Deprecate**: removed from the homepage, their last user (D-20 a) | — |
| Leaflet 1.9.4 (unpkg) + OSM tiles | CDN | **Retain** (AD-08, D-20 a) | Homepage map |

## 18. Phase 4 revision and close (2026-10-31)

The homepage was revised after the first Phase 4 version (section 17). Section 17 stays as the record of that version; this section is the state at the close of Phase 4.

| Asset | Source | Decision | Where it is used now |
|---|---|---|---|
| `public/video/hero-loop-desktop.webm` (2,736,578 B, VP9 1920×1080, 24 fps, 12 s), `hero-loop-desktop.mp4` (4,792,887 B, H.264), `hero-loop-mobile.webm` (1,212,085 B, VP9 720×1280), `hero-loop-mobile.mp4` (1,732,253 B, H.264); no audio track | New, original (AD-11; `student-go-docs/design-system/VIDEO_SOURCES.md`) | **New** (additive) | Homepage hero background; loaded by `resources/js/student-go/hero-video.js` (WebM when VP9 is 'probably' supported, otherwise MP4); never requested under reduced motion or Save-Data |
| `public/video/hero-loop-desktop.{webp,jpg}` (81,012 / 96,602 B), `hero-loop-mobile.{webp,jpg}` (33,688 / 58,938 B) | New, original (first frame of the loop) | **New** | Hero poster (`<picture>`, `fetchpriority="high"`, `alt=""`) |
| `public/assets/photography/field/kkn-itb-2025-mahasiswa-jalan-desa-{800,1600}.webp` | Derivative of `public/mahasiswa-about.jpeg` (Source A; matched by image comparison) | **Adapt** (D-34 a), **Reuse** (D-30 answered for the homepage) | Homepage 'Dari lapangan'. Alt: 'Mahasiswa berompi KKN ITB 2025, dilihat dari belakang, menumpang bak mobil terbuka di jalan desa yang diapit kebun pisang.' |
| `public/assets/photography/field/kkn-itb-2025-rombongan-hujan-{800,1600}.webp` | Derivative of `public/network-student.JPG` (Source A) | **Adapt**, **Reuse** | Homepage 'Dari lapangan'. Alt: 'Rombongan mahasiswa berompi KKN ITB 2025 berjalan di bawah hujan, sebagian berpayung, di gang desa yang dihiasi umbul-umbul warna-warni.' |
| `public/assets/photography/homepage/purwojati-banyumas-rice-fields.webp`, `cirebon-aerial-village-red-roofs.webp` | B | **Not placed any more** (replaced by the field photographs) | — |
| `illustrations/geographic/indonesia-regions.svg`, `motifs/contour-field.svg`, `workflows/student-journey*.svg`, `institution-journey*.svg` | B | **Not placed any more** (the revised page uses data rows, a table and the video instead) | — |
| `illustrations/motifs/map-pin.svg`, `map-pin-selected.svg` | B | **Reuse** inside a Leaflet `divIcon` with the count | Homepage map |
| `public/assets/brand/student-go-logo-on-dark.svg` | B | **Reuse** in the header over the hero (homepage only) | Navbar |
| `public/karsa-logo.png` | A | **Deleted from the working tree by the user** (stated 2026-10-31 in the Phase 4 closing session: deliberate). This deviates from AD-09 by the owner's choice; it is still in git HEAD. Five standalone auth views still reference it (broken image): `auth/login`, `auth/register`, `auth/student-register`, `auth/institution-register`, `auth/company-register` → Phase 6 (RN-13). | — |

Hashes: 37 of the 38 Phase 0 `public/` files match the baseline; the 38th is `karsa-logo.png` (above). Nothing else under `public/` was deleted, overwritten, moved or renamed.

## 19. Phase 5 record: about, contact, public institution profile (2026-10-31)

New files only; no file under `public/` was deleted, overwritten, moved or renamed by this phase (SHA-256 of every original below unchanged). Derivatives follow D-34 (a): WebP, new names, originals untouched, EXIF not copied.

| Asset | Source | Decision | Where it is used now |
|---|---|---|---|
| `public/mahasiswa-about.jpeg` | A (KKN ITB 2025; rights D-30) | **Reuse** via the existing `field/kkn-itb-2025-mahasiswa-jalan-desa-{800,1600}.webp` (no duplicate made) | `/about`, wide figure under the h1 (4:3 → 16:9 → 21:9 by width). Alt as on the homepage. Caption 'KKN ITB 2025. Menumpang bak mobil terbuka di jalan desa.' |
| `public/handprints-about.jpeg` | A (rights D-30) | **Adapt** → `field/kkn-itb-2025-dinding-cap-tangan-{800,1600}.webp` (30,088 / 74,136 B) + **Reuse** | `/about` 'Dari lapangan'. Alt 'Seorang pria bertopi memandangi dinding biru penuh cap tangan cat warna-warni, masing-masing dengan nama yang ditulis di bawahnya.' Caption 'KKN ITB 2025. Dinding cap tangan, masing-masing bertuliskan nama.' |
| `public/jaket-about.jpeg` | A (rights D-30) | **Adapt** → `field/kkn-itb-2025-rompi-cap-tangan-{800,1600}.webp` (28,746 / 98,122 B) + **Reuse** | `/about` 'Dari lapangan' (4:5 crop from 768 px). Alt 'Rompi hijau keabuan berbordir bunga dan tulisan KKN ITB 2025, penuh cap tangan cat, tergantung pada tiang beton.' Caption 'KKN ITB 2025. Rompi peserta setelah penuh cap tangan cat.' |
| `public/about.jpg` | A | **Deprecate (usage)**: former slide background (RN-04); landscape without KKN content, place unknown. Kept on disk. | — |
| `public/contact.jpg` | A | **Deprecate (usage)**: the aerial hero was removed (J6, J35). Kept on disk. | — |
| `public/profile_13523136.jpg`, `profile_13523124.jpeg`, `profile_13523151.jpeg` | A (personal photos, D-31) | **Deprecate (usage)**: replaced by the user-supplied team photos. Not renamed, moved or deleted. | — |
| `public/assets/photography/member/{Danendra Shafi Athallah,Kenzie Raffa Ardhana,M. Abizzar Gamadrian}.png` | Supplied by the user on 2026-10-31 for the team list (D-31 answer) | **Retain** originals; **Adapt** → `member/danendra-shafi-athallah-480.webp`, `m-abizzar-gamadrian-480.webp`, `kenzie-raffa-ardhana-330.webp` (square head-and-shoulders crops on the photos' black ground; 8,380 / 8,664 / 4,268 B) | `/about` 'Tim pengembang', alt 'Foto {nama}' |
| Lucide sprite: `graduation-cap`, `building-2`, `briefcase`, `mail`, `info`, `chevron-down`, `map-pin`, `external-link` | B | **Reuse** | About roles, Contact rows and FAQ, institution profile |
| `illustrations/workflows/*-journey*.svg` | B | Not placed: the same verified steps are HTML text lists on `/about` | — |
| `photography/about/*` (catalogue) | B | Not placed: Source A filled every slot | — |
| Institution logos (uploads) | A (user uploads) | **Retain**: shown in the square avatar when `logo_path` exists (none in the current data); initials otherwise; the old `via.placeholder.com` fallback and the green gradient are gone | `/institution/{id}` |

Image bytes loaded by `/about`: 5,534,868 B (four originals) → 225,992 B at 1440 px, 129,446 B at 390 px.

### 19.1 Revision after the user's review (2026-10-31)

Supersedes the `mahasiswa-about.jpeg` and member rows above.

| Asset | Source | Decision | Where it is used now |
|---|---|---|---|
| `public/mahasiswa-about.jpeg` (via `field/kkn-itb-2025-mahasiswa-jalan-desa-*`) | A | No longer on `/about` (user: About uses photographs other than the homepage's). Still on the homepage. | Homepage only |
| `public/dashboard-student2.jpeg` | A (KKN ITB 2025 vests visible; user 2026-10-31: any photograph that shows KKN may be used on About) | **Adapt** → `field/kkn-itb-2025-bersama-warga-di-kebun-{800,1600}.webp` (35,684 / 82,846 B) + **Reuse** | `/about` header, main photograph. Alt 'Mahasiswa berompi KKN ITB 2025, dilihat dari belakang, merapikan topi di kebun; di depannya seorang warga bertopi memegang cangkul.' Caption 'KKN ITB 2025. Di kebun bersama warga desa.' |
| `public/repo-student.jpeg` | A (same field series; no KKN branding visible) | **Adapt** → `field/upacara-bendera-bersama-warga-{800,1600}.webp` (91,888 / 289,766 B) + **Reuse** | `/about` header inset (≥ 640 px). Alt 'Tiga pengibar berseragam putih menaikkan bendera Merah Putih di lapangan rumput, disaksikan perangkat desa yang memberi hormat di bawah tenda merah putih.' Caption 'Upacara bendera bersama warga desa.' |
| `public/assets/photography/member/*_seragam.png` (3 files, RGBA 1200×1500) | Supplied by the user on 2026-10-31, replacing the earlier member PNGs (which, with this phase's first WebP crops, were removed from the folder outside this session) | **Retain** originals; **Adapt** → `member/{danendra-shafi-athallah,m-abizzar-gamadrian,kenzie-raffa-ardhana}-480.webp` (480×600, transparency kept; 19,048 / 19,734 / 17,352 B) | `/about` 'Tim pengembang' on a light 4:5 tile |
| `illustrations/motifs/contour-field.svg` | B | **Reuse** via `.sg-contour` (the page's one backdrop) | `/about` header, behind the photographs |

## 20. Phase 6 — authentication pages (2026-10-31)

No file under `public/` was added, deleted, overwritten, moved or renamed by this phase (generated `public/build/` only). Usage decisions:

| Asset | Source | Decision | Where it is used now |
|---|---|---|---|
| `public/login-register-view.jpg` | A | **Retain** file; **Deprecate** as auth background (D-19 answer for auth: no photograph) | Nowhere (was the `/login` background) |
| `public/student-register-background.jpeg` | A | **Retain** file; **Deprecate** as background | Nowhere (was the `/register/student` background) |
| `public/institution-register-background.jpeg` | A | **Retain** file; **Deprecate** as background | Nowhere (was the `/register/institution` background) |
| `public/karsa-logo.png` | A | References removed from the five auth views (RN-13). The file itself was deleted outside the design before this phase (git shows `D`); the auth pages showed a broken image until now | Nowhere |
| `assets/brand/student-go-logo.svg` | B | **Reuse** | Auth header (`layouts/auth.blade.php`), `alt=""` inside a link named "Student Go, ke beranda" |
| `illustrations/motifs/contour-field.svg` | B | **Reuse** via `.sg-contour` (one per page) | Auth side panel from 1024 px (login, three registrations) |
| `icons/ui/sprite.svg` | B | **Reuse** (no icon added; the sprite has no `building`, so Instansi uses `map-pin`) | eye / eye-off, graduation-cap, map-pin, briefcase, mail, user, image, file-text, upload, check, circle-dashed, circle-alert, circle-check, arrow-left/right, chevron-right, log-in |
| `resources/css/auth*.css`, `partials/auth-navbar.blade.php` | C | **Retain**, unused (not Vite inputs; not included anywhere) | — |

**Update (2026-10-31, later):** the user re-edited the three `*_seragam.png` files (same names, aligned framing) and asked only that they be placed. `/about` now references the PNGs **directly** (`App\Support\TeamMembers`); the WebP derivatives listed above no longer exist (removed from the folder outside this session) and are not used. Phase 5 tests 6/6 (the transparency check now reads the PNGs); `/about` re-captured at six widths: 0 broken images, no overflow.

## 21. Phase 7 — student dashboard (2026-10-31)

No file under `public/` was added, deleted, overwritten, moved or renamed by this phase (generated `public/build/` only; SHA-256 of the three `dashboard-student*` files equal `baseline/asset-hashes.txt`). Usage decisions:

| Asset | Source | Decision | Where it is used now |
|---|---|---|---|
| `public/dashboard-student2.jpeg` | A | **Retain** file; **Deprecate** as the dashboard hero (guardrail 1: no photo on a task page) | `/student/friends` hero (Phase 13 decides); About (Phase 5 derivative) |
| `public/dashboard-student.jpg`, `public/dashboard-student3.jpg` | A | **Retain**, untouched (not used by the dashboard; browse-problems and profile heroes belong to Phases 8 and 12) | unchanged |
| Student profile photos (`profile_photo_url`) | user uploads | **Retain**; shown only as the requester's avatar in a friend-request row of the student's own dashboard (same audience as before); initials when there is no photo | `/student/dashboard` |
| `illustrations/empty-states/no-applications.svg` | B | **Reuse** | Empty 'Lamaran terbaru' (account without applications) |
| `icons/ui/sprite.svg` | B | **Reuse** (no icon added) | search, calendar, hourglass, user, user-check, map-pin, arrow-right + status icons |
| Space Grotesk (Google Fonts link in the old view) | external | **Removed** from this view (D-20 a) | — |

## 22. Phase 17 — institution dashboard (2026-10-31)

No file under `public/` was added, deleted, overwritten, moved or renamed by this phase (generated `public/build/` only). Baseline hash check after the phase: 37/38 identical, 0 changed; the one missing file is `public/karsa-logo.png`, deleted by the user before Phase 4 (recorded there). Usage decisions for `/institution/dashboard`:

| Asset | Source | Decision | Where it is used now |
|---|---|---|---|
| Institution logo (`logo_path` upload) | user upload | **Retain**, not shown: the audit keeps the dashboard header without a photo (I01 'Header tanpa foto'); the file and its other placements are untouched | unchanged (profile pages) |
| Student profile photos | user uploads | **Retain**, not shown: applicant rows use initials (`x-ui.avatar`, decorative) as before | — |
| `illustrations/empty-states/no-applications.svg` | B | **Reuse** | Empty main column (no problems yet / nothing waiting) |
| `illustrations/workflows/institution-journey(-vertical).svg` | B | **Not placed** (section H.2 suggested it for the first-run dashboard): its labels say 'halaman Pelamar' while the navigation says 'Aplikasi', and text inside an image does not scale at 360 px. The empty state states the next step in HTML instead | — |
| `icons/ui/sprite.svg` | B | **Reuse** (no icon added) | plus, upload, clipboard-list, folder-open, calendar, file-text + status icons |
| Inline SVG heroicons and the inline `<style>` fade-in block of the old view | C | **Replaced** by the sprite and `resources/css/student-go/institution.css` | — |

## 23. Phase 21 — company dashboard (2026-10-31)

No file under `public/` was added, deleted, overwritten, moved or renamed by this phase (generated `public/build/` only). Baseline hash check after the phase: 37/38 identical, 0 changed; the one missing file is `public/karsa-logo.png`, deleted by the user before Phase 4. Usage decisions for `/company/dashboard`:

| Asset | Source | Decision | Where it is used now |
|---|---|---|---|
| `deal-work-together.jpg` (6016x4016, 1.09 MB; rights UNKNOWN, D-30) | A | **Retain** (file); **Deprecate** as the company dashboard hero (J6, guardrail 1): the page starts with `x-ui.page-header`. Still referenced by `student/chatbot/index.blade.php` (Phase 16) | KARA list only |
| `company1-5.jpg`, `company-profile.jpg` | A | **Retain**, unchanged: not referenced by the company dashboard (VERIFIED in source; section H.1's mapping was INFERRED). Still used by other company pages (Phases 22-25) | other company pages |
| Company logo (`logo` upload) | user upload | **Retain**, not shown on the dashboard; header shows the name only (audit C01) | navbar initials, profile pages (unchanged) |
| Student profile photos | user uploads | **Retain**, not shown: applicant and talent rows use initials (`x-ui.avatar`, decorative). The old talent rows pointed `asset()` at a Supabase path, which never loaded | — |
| `illustrations/empty-states/no-applications.svg` | B | **Reuse** | 'Belum ada lamaran' empty state |
| `illustrations/workflows/company-journey(-vertical).svg` | B | **Adapt**: its four steps (labels checked against real routes and the 5-step job form) are rendered as an HTML ordered list with links, not as an image, so the text scales and is read by assistive tech | First-run dashboard (company without jobs) |
| `icons/ui/sprite.svg` | B | **Reuse** (no icon added) | plus, clipboard-list, search, briefcase + status icons (`message-square-text` for 'Wawancara') |
| Chart.js 4.4.1 | npm package `chart.js` 4.4.1 (MIT) + `@kurkle/color` 0.3.4 (MIT), exact version of the former CDN file; approved by the user 2026-10-31 | **Replace** the CDN tag (cdn.jsdelivr.net) with the local package: `resources/js/student-go/charts.js` (tree-shaken: bar chart, category/linear scales, tooltip), loaded by a dynamic import in `app.js` only when the page has `canvas[data-sg-chart]` (separate chunk `charts-*.js`, 141.30 kB / gzip 49.56 kB); no CDN request remains for charts | company dashboard (populated, >= 5 of 6 months with data, D-21) |
| Inline SVG heroicons and the inline `<style>` fade-in block of the old view | C | **Replaced** by the sprite and `resources/css/student-go/company.css` | — |

## 24. Phase 8 — student project discovery (2026-10-31)

No file under `public/` was added, deleted, overwritten, moved or renamed by this phase (generated `public/build/` only). Baseline hash check after the phase: 37/38 identical, 0 changed; the one missing file is `public/karsa-logo.png`, deleted by the user before Phase 4. Usage decisions for `/student/browse-problems`:

| Asset | Source | Decision | Where it is used now |
|---|---|---|---|
| `public/dashboard-student.jpg` | A | **Retain** (file, hash unchanged); **Deprecate** as the browse-page hero (guardrail 1: no hero on a task page) | not referenced by the student workspace any more (still listed for Phase 12's profile decision) |
| `resources/css/browse-problems.css`, `resources/js/pages/browse-problems.js` | C | **Retain**, unused: the old view linked them through `asset()` and both answered 404 (never built); the links are removed | — |
| Space Grotesk (Google Fonts link in the old view) | external | **Removed** from this view (D-20 a) | — |
| `illustrations/motifs/map-pin.svg` | B | **Reuse** as the Leaflet marker icon (28 × 38 px, `alt=""`; the marker carries the problem title) | map view |
| `illustrations/empty-states/no-search-results.svg` | B | **Reuse** | 'Tidak ada masalah yang cocok', 'Belum ada masalah yang dibuka', 'Halaman N tidak ada' |
| `icons/ui/sprite.svg` | B | **Reuse** (no icon added) | search, list-filter, x, layout-list, layout-grid, map, chevron-up/down, clock, award + status icons |
| Catalogue photographs `public/assets/photography/**` marked `available` in `photo-review.json` (37; Unsplash / Pexels licences as recorded in `student-go-docs/design-system/ASSET_SOURCES.md`; no identifiable people) | B | **Reuse** as **dev seed images** (D-14 answer): converted to JPEG ≤ 1600 px q82 and uploaded as 92 objects to the public Supabase bucket `sg-storage` under the paths already stored in `problem_images.image_path` (`problems/masalah-desa-NN.jpg|.jpeg`). Nothing was copied into `public/`; the database is unchanged. Each photo is reused 2–3 times and does not depict the problem it is attached to. Log: `reports/phase8/seed-images-upload-log.json` | gallery view (image only when a record exists and loads); detail pages (Phase 9) read the same objects |
| KKN photographs with people in `public/` | A | **Not used** (D-30 open) | — |
| Leaflet 1.9.4, leaflet.markercluster 1.5.3 (unpkg) | external | **Retain** (AD-08), unchanged versions and URLs | map view |

## 25. Phase 18 — institution problem management (2026-10-31)

No file under `public/` was added, deleted, overwritten, moved or renamed by this phase (generated `public/build/` only). Baseline hash check after the phase: 37/38 identical, 0 changed. The one missing file is `public/karsa-logo.png`, deleted by the user before Phase 4. Usage decisions for `/institution/problems`, `/create`, `/{id}`, `/{id}/edit`:

| Asset | Source | Decision | Where it is used now |
|---|---|---|---|
| Problem gallery uploads (`problem_images`, Supabase Storage; seed objects from Phase 8 in `sg-storage`) | user upload | **Retain**: same files, same storage paths, same `image_url` accessor. Detail page: documentation grid with a 'Sampul' label on the cover. Edit page: current photos, each with a visible 'Hapus foto N' checkbox (`delete_images[]`; the old checkbox was `hidden` and could not be reached by keyboard). If an image fails to load it is hidden and the text 'Foto tidak dapat dimuat' is shown instead. No placeholder tile | detail, edit |
| `https://via.placeholder.com/…` image fallbacks in the old list/detail views | external (hot-linked) | **Removed** (no hot-linked images): the list is a table without thumbnails; detail and edit show the text fallback above | — |
| `illustrations/empty-states/no-search-results.svg` | B | **Reuse** | 'Tidak ada masalah yang cocok' (filters with no match) |
| `icons/ui/sprite.svg` | B | **Reuse** (no icon added) | plus, pencil, x, check, circle-alert, upload, file-text + status icons |
| Inline `<style>` blocks, inline heroicons and the `alert()` SDG check of the old views | C | **Replaced** by `resources/css/student-go/institution.css` (Phase 18 section), the sprite and an inline error in section 3 | — |
| Institution logo, student photos | user uploads | **Retain**, not shown on these pages (unchanged elsewhere) | — |

## 26. Phase 22 — company talent discovery (2026-10-31)

Pages: `/company/talents`, `/company/talents/saved`, `/company/talents/compare`. No file under `public/` was added, changed, moved or removed (37/38 Phase 0 hashes identical; `karsa-logo.png` was deleted by the user before Phase 4).

| Asset | Source | Decision | Where / note |
|---|---|---|---|
| `company1.jpg` (talents hero) | A | **Deprecate (usage)** | No longer referenced by `company/talents/index`; file retained. Its URL also returned 404 in the before capture. |
| `company4.jpg` (saved talents hero) | A | **Deprecate (usage)** | No longer referenced by `company/talents/saved`; file retained. |
| Student profile photographs | user uploads | **Retain**, not shown | Rows use initials (`x-ui.avatar` without `src`), as the old grid effectively did; no new or larger exposure (D-04, D-31). The compare page no longer requests `ui-avatars.com`. |
| `illustrations/empty-states/no-saved-projects.svg` | B | **Reuse** | Empty saved talents ('Belum ada talenta tersimpan'). |
| `illustrations/empty-states/no-search-results.svg` | B | **Reuse** | Empty pool and 'Tidak ada mahasiswa yang cocok'. |
| `icons/ui/sprite.svg` | B | **Reuse** (no icon added) | bookmark, download, map-pin, folder-open, trash-2, badge-check, plus, x, list-filter, arrow-left. |
| Inline `<style>` blocks, inline heroicons, `prompt()`/`confirm()` of the old views | C | **Replaced** | `resources/css/student-go/company.css` (Phase 22 section), the sprite, `window.sgConfirm`. |

## 27. Phase 9 — problem detail (2026-10-31)

No file under `public/` was added, deleted, overwritten, moved or renamed by this phase (generated `public/build/` only). Usage decisions for `/student/browse-problems/{id}`:

| Asset | Source | Decision | Where it is used now |
|---|---|---|---|
| Problem images (`problem_images`, D-14 seed objects in `sg-storage`) | remote | **Retain**; shown only after they load, dropped with their caption on error; no placeholder, no 'Gallery N' | 'Foto' section of the brief |
| Institution logo (`logo_path` upload) | user upload | **Retain**, same placement as before (this authenticated page); initials when there is none (0 logos in the seed) | 'Tentang instansi' |
| `icons/ui/sprite.svg` | B | **Reuse** (no icon added; no `heart`/`share` in the sprite, so Simpan uses `bookmark` and Bagikan `link`) | bookmark, link, clock, award, info, lock, arrow-right + status icons |
| Inline heroicons, emoji (⭐ 🔥 ⏰), gradients and the inline `<style>` block of the old view | C | **Replaced** by the sprite, text flags and `student-brief.css` | — |
| Catalogue / KKN photographs | A, B | **Not used** (D-19 and D-30 stay open for this page; a brief shows only the problem's own images) | — |

## 28. Phase 23 — company job management (2026-10-31)

Pages: `/company/jobs`, `/company/jobs/create`, `/company/jobs/{id}`. No file under `public/` was added, changed, moved or removed (37/38 Phase 0 hashes identical; `karsa-logo.png` was deleted by the user before Phase 4).

| Asset | Source | Decision | Where / note |
|---|---|---|---|
| `company2.jpg` (jobs table hero) | A | **Deprecate (usage)** | No longer referenced by `company/jobs/index`; file retained. Referenced nowhere else now. |
| `company3.jpg` (wizard hero) | A | **Deprecate (usage) on the wizard** | No longer referenced by `company/jobs/create`; still used by `company/jobs/edit` (not in this phase's scope) and `company/applications/index` (Phase 24). File retained. |
| `illustrations/empty-states/no-applications.svg` | B | **Reuse** | 'Belum ada lowongan' inside the jobs table. |
| `illustrations/empty-states/no-search-results.svg` | B | **Reuse** | 'Tidak ada lowongan yang cocok' inside the jobs table. |
| `icons/ui/sprite.svg` | B | **Reuse** (no icon added) | plus, eye, pencil, trash-2, users, arrow-left/right, check, bookmark, map-pin, briefcase, building-2, clipboard-list, circle-alert. |
| LinkedIn brand colour, inline heroicons, inline `<style>` and `alert()`/`confirm()` of the old views | C | **Replaced** | `resources/css/student-go/company.css` (Phase 23 section), the sprite, `window.sgConfirm`. |
| Applicant photos (`storage/profiles/…` paths in the old detail view) | user uploads | **Retain**, not shown | Detail lists applicants with initials (the old local `storage/profiles` path never resolved; uploads live in Supabase). No new exposure. |

## 29. Phase 19 — institution review and monitoring (2026-10-31)

No file under `public/` was added, deleted, overwritten, moved or renamed by this phase (generated `public/build/` only). Baseline hash check after the phase: 37/38 identical, 0 changed. The one missing file is `public/karsa-logo.png`, deleted by the user before Phase 4. Usage decisions for `/institution/applications` (+ `/{id}`, `/{id}/review`), `/institution/projects` (+ `/{id}`) and `/institution/reviews` (+ `/{id}`, `/{id}/edit`, `/create/{projectId}`):

| Asset | Source | Decision | Where it is used now |
|---|---|---|---|
| `illustrations/empty-states/no-applications.svg` | B | **Reuse** | 'Belum ada lamaran' (applications, empty account) |
| `illustrations/empty-states/no-search-results.svg` | B | **Reuse** | no-match states on applications, projects and reviews |
| `illustrations/workflows/project-status.svg` (suggested in section H.2) | B | **Not placed**: the stage meter and the milestone timeline already state each project's stage in text. The image labels do not match the stored project statuses (`active`, `on_hold`, `completed`, `cancelled`) | — |
| `icons/ui/sprite.svg` | B | **Reuse** (no icon added; there is no star icon, so ratings are shown as 'n/5' with a neutral five-segment meter) | check, x, settings, pencil, file-text + status icons |
| Student profile photos (`profile_photo_url`) | user upload | **Retain**, not shown: the queue, panels and review list use initials (`x-ui.avatar`, decorative because the name is written next to it). The old review views linked `asset('storage/'.…)` (empty folder) and `ui-avatars.com` (hot-linked); both are gone | — |
| Proposal files (`applications.proposal_content`, base64 in the database) | user upload | **Retain**: same route `institution.applications.download-proposal`, link 'Buka proposal' (0 seeded rows have one) | application detail |
| Inline heroicons, inline `<style>` blocks, gradient headers and yellow star SVGs of the old views | C | **Replaced** by the sprite, `resources/css/student-go/institution.css` (Phase 19 section) and the neutral rating meter | — |

## 30. Phase 24 — applicant pipeline (2026-10-31)

No file under `public/` was added, deleted, overwritten, moved or renamed (generated `public/build/` only; `git diff --name-status -- public/` is empty). Usage decisions for `/company/applications` and `/company/applications/{id}`:

| Asset | Source | Decision | Where it is used now |
|---|---|---|---|
| `company3.jpg` (list hero background) | A | **Deprecate (usage)** on `company/applications/index` | No longer referenced there; still used by `company/jobs/edit` (not in scope). File retained. |
| `company1.jpg` (detail hero background) | A | **Deprecate (usage)** on `company/applications/show` | No longer referenced there. File retained. |
| `illustrations/empty-states/no-applications.svg` | B | **Reuse** | 'Belum ada pelamar' (empty account, and empty stage inside the table) |
| `illustrations/empty-states/no-search-results.svg` | B | **Reuse** | 'Tidak ada pelamar yang cocok' (filters) |
| `icons/ui/sprite.svg` | B | **Reuse** (no icon added; no star icon, so ratings are 'n/5' with a neutral five-segment meter) | briefcase, eye, clipboard-check, users, download, user-check, circle-check, circle-x, file-text, external-link + status icons |
| Applicant photographs | user upload | **Retain**, not shown (as before: the old list read `user.profile.avatar_url`, which does not exist, so it always showed initials; the old detail showed no photo). Initials via `x-ui.avatar`, decorative | — |
| Inline `<style>` blocks, gradient avatars, yellow star SVGs, inline heroicons, custom toast and `alert()` of the old views | C | **Replaced** by `resources/css/student-go/company.css` (Phase 24 section), the sprite, `window.showNotification` and `window.sgConfirm` | — |

## 31. Phase 10 — student applications (2026-10-31)

| Asset | Source | Decision | Where / note |
|---|---|---|---|
| `application-student.jpg` (list hero background) | A | **Deprecate (usage)** on `student/applications/index` | Replaced by the standard page header. File retained, hash unchanged. |
| `illustrations/empty-states/no-applications.svg` | B | **Reuse** | 'Belum ada lamaran proyek / lowongan' |
| `illustrations/empty-states/no-search-results.svg` | B | **Reuse** | 'Tidak ada lamaran yang cocok' (filters) |
| `icons/ui/sprite.svg` | B | **Reuse** (no icon added) | search, x, file-text, external-link, map-pin, arrow-right, check, circle-x + status icons |
| Institution logos | user upload | **Retain** (same placement size as before; initials when no logo) | `x-ui.avatar`, decorative |
| Space Grotesk link, inline `<style>` blocks, gradient image placeholders, `via.placeholder.com` fallback, emoji tips, `alert()`/`confirm()` of the old views | C | **Replaced** by shared components, Tailwind utilities on existing tokens, the sprite and `window.sgConfirm` | No CSS file added or changed |
