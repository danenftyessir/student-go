# INTEGRATION — putting the Student Go redesign into the Laravel project

Read this before copying anything. It says what is finished, what is not, and the order to do the rest in.

---

## Phase 2 — what was actually done in this project (2026-10-30)

This copy lives at `student-go-docs/design-system/INTEGRATION.md`. Sections 0-8 below are the package's original instructions, kept for reference. Where they differ from what was done, **this section wins**. The decision records are in `student-go-docs/redesign-execution/DECISIONS_AND_OPEN_QUESTIONS.md` (D-01, D-16, D-17, D-18, D-29, D-33, D-35) and `ASSET_INVENTORY.md` section 13.

| Package step | What was done | Deviation and why |
|---|---|---|
| §1 new branch `redesign/student-go-visual` | Stayed on `feat/students`, no commit | D-27: no new branch and no commits until the repository move |
| §2 `cp -R` into the Laravel root | 185 files copied by a script that **refuses to overwrite** (`open(…, 'x')`). Before the copy, a collision check found no existing target. | Docs went to `student-go-docs/design-system/` instead of `docs/` (that folder already holds the audit PDF and proposal). The package README became `PACKAGE_README.md`. `storage/app/asset-originals/` (54 MB of JPEG originals) was **not** copied, so `.gitignore` is unchanged. |
| §2 assets byte-identical | The C2PA `<metadata>` block was stripped from the 96 SVGs and the styleguide HTML, and the `caBX` chunk from the 7 PNGs, **on the copies only** (D-35). Every other file is byte-identical. | Package originals untouched (111/111 manifest hashes still match). Both hashes for every file are in `c2pa-strip-hashes.tsv`. The 96 stripped SVGs render pixel-identically to the originals, and the 7 PNGs are pixel-identical. Icons shrank from 668 KB to 123 KB. |
| §2 style guide paths `../public/assets` | Rewritten to `../../public/assets/` in the copied `styleguide.html` and `styleguide.css` (one level deeper) | Needed for the new location |
| §3 import the three files | `fonts.css`, `tokens.css`, `components.css` imported after `@import 'tailwindcss'`. The existing `@source` lines and `@theme` (Instrument Sans) were kept. | **Staged (D-29).** Everything that changes existing pages globally was moved unchanged into `resources/css/student-go/legacy-overrides.css`, which is **not imported yet**. Phase 3 un-comments one line. See `DESIGN_SYSTEM.md` §0. |
| §3 checks | `--sg-brand` = `#405080` on a real page. All three WOFF2 files load from `/assets/fonts/` (HTTP 200, `font/woff2`) when used, and no Google request is added by the new CSS. | The old Google Fonts links stay in the layouts until Phase 3 removes them, so `fonts.googleapis.com` is still requested by existing pages. Phase 2 removed nothing. |
| §4 layout head, favicon, wordmark, `APP_NAME` | **Not done** (Phase 3 owns the shell) | D-01 = Student Go with a new assistant name (TBD in Phase 16). D-18 = package wordmark. `karsa-logo.png` and the 0-byte root `favicon.ico` are untouched. |
| §4 icon component | Added `resources/views/components/icon.blade.php` → `<x-icon name="…" size="sm\|lg" label="…" />` | Adds `focusable="false"`, and `role="img"` + `aria-label` when `label` is given |
| §4 remove Google Fonts, Boxicons, Font Awesome, AOS | **Not done** (Phase 3, D-20 still open) | |
| §7 photographs | All 60 WebP and `credits.json` were already in the package. They were copied, **not downloaded**, and `fetch-photos.php` was not run. All 60 are valid WebP (17.1 MB, longest side 1800 px), with no EXIF or XMP. | A preliminary visual review (agent, contact sheets) is in `photo-review.json`: 37 available, 14 available with care, 9 held. The user confirms it before any placement (D-19). No photo was placed. |
| §8 verification | Done at 360/390/768/1024/1366/1440. Evidence is in `student-go-docs/redesign-execution/reports/phase2/`. | |

**Rollback:** comment out the three `@import` lines in `resources/css/app.css` and rebuild. The new folders are additive and can be left in place.

## Phase 3 — shell (2026-10-30)

§4 is now done: shared `<head>` (`partials/head.blade.php`: `lang="id"`, title pattern `… — Student Go`, favicon set, font preload, Vite bundle before Alpine), Student Go wordmark in header and footer, `legacy-overrides.css` imported, Google Fonts / fonts.bunny / Font Awesome / Boxicons removed from the layouts (D-20 a; Alpine and Leaflet stay on their CDNs; AOS goes with the homepage in Phase 4). `APP_NAME` in `.env` was **not** changed: the layouts print "Student Go" directly, because changing `APP_NAME` also renames the session cookie and cache prefix (everyone would be logged out). Components: `student-go-docs/redesign-execution/COMPONENT_INVENTORY.md`.

## 0. Honest status

| | |
|---|---|
| **Done and verified** | Design tokens, component CSS, brand SVGs, 5 empty states, 4 workflow diagrams (horizontal + vertical), Indonesia region map, motifs, 71 Lucide icons + sprite, local fonts with OFL texts, a living style guide (`docs/styleguide.html`), manifest. CSS compiles with **Tailwind 4.1.16** (the version your compiled CSS reports in the audit). The style guide was screenshotted at 1440 / 1366 / 1024 / 768 / 390 / 360 px (`docs/screenshots/`) with no page-level horizontal overflow, both font families loaded, no console messages and no failed requests. |
| **Not done** | **Your Blade views are not redesigned.** The session that produced this package could not reach your project folder (no linked computer) and could not run `composer install` (Packagist blocked), so no view, layout, controller or route was read or changed, and the app was never run with this CSS. Role dashboards, auth, browse, detail, profile, repository, institution and company pages are specified (`DESIGN_SYSTEM.md` section 9) but not built. |
| **Not downloaded** | The 60 photographs. Each one was opened on its own Unsplash or Pexels page and its license read there, but the files could not be fetched from this session. `php scripts/fetch-photos.php` does it on your machine (section 7). None has been looked at by eye. |
| **Not tested** | Vite build, Alpine interactions, Leaflet markers with the new pin, any logged-in page, any of the three roles, real-data stress (77 projects / 216 documents / 76-character institution names) *inside the app*. The style guide does exercise long strings, a 76-character name and a 52-character email, but that is a fixture page, not your data. |

To finish the job, either link the Student Go folder to this task in the Claude desktop app, or attach a zip of the project **without** `vendor/` and `node_modules/`. Then the remaining steps below can be done and checked in a browser.

---

## 1. Before you touch anything

```bash
git status                       # start from a clean tree
git switch -c redesign/student-go-visual
cat composer.json package.json   # confirm Laravel version, tailwindcss ^4, @tailwindcss/vite, alpinejs
ls resources/views resources/views/layouts resources/views/components 2>/dev/null
php artisan route:list --except-vendor > route-list.before.txt   # your rollback reference
```

Do not change routes, controllers, models, migrations or seeders in this pass. Everything below is `public/`, `resources/css`, `resources/views` markup and class names only.

## 2. Copy the package in

From the package root, into the Laravel root (adjust the path):

```bash
L=/path/to/laravel
mkdir -p "$L/public/assets" "$L/resources/css" "$L/docs" "$L/scripts"
cp -R public/assets/*          "$L/public/assets/"
cp -R resources/css/student-go "$L/resources/css/"
cp -R docs/*                   "$L/docs/"
cp    scripts/fetch-photos.*   "$L/scripts/"      # the .php does the work; the .sh is a wrapper
cp    assets-manifest.json     "$L/"
```

`docs/styleguide.html` uses relative paths into `../public/assets`, so it opens correctly only from a local web server rooted at the project (for example `php -S 127.0.0.1:8080 -t .` is enough for this static file) — not from `file://`, where the icon sprite is blocked.

Add to `.gitignore`: `/storage/app/asset-originals/`.

## 3. Wire the CSS (Tailwind v4)

In `resources/css/app.css`, keep your existing `@import "tailwindcss"` line first, then:

```css
@import "tailwindcss";
@import "./student-go/fonts.css";
@import "./student-go/tokens.css";
@import "./student-go/components.css";

/* keep your existing @source lines, e.g. @source "../views"; */
```

Merge, do not duplicate, any `@theme` block already in `app.css`. `tokens.css` deliberately does **not** reset Tailwind's default palette, so pages you have not migrated keep working. It does three things worth knowing:

- Re-tunes `gray-*` so the existing `text-gray-400/500` (the audit's 2.5:1 failures) become readable. The gray ramp is therefore changed for every page at once.
- Defines `primary-*` (aliased to `brand-*`), which fixes the 25 uses of the previously undefined `bg-primary-600`.
- Collapses `rounded-xl/2xl/3xl` to 8px, which is also a global change.

Check after `npm run build` (or `npm run dev`): in the browser console,
`getComputedStyle(document.documentElement).getPropertyValue('--sg-brand')` must return `#405080`, and the Network tab must show the two Plus Jakarta Sans files from `/assets/fonts/...` with status 200 and no request to `fonts.googleapis.com`.

*Unverified:* the CSS was compiled with the Tailwind CLI, not through Laravel's Vite plugin. Vite normally leaves root-relative `url("/assets/…")` untouched (files are served from `public/`); if it prints a "will be resolved at runtime" notice, that is expected.

## 4. Layout `<head>` and shell (sketch, not run)

Adapt to whatever title and asset mechanism the current layout uses.

```blade
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ trim(($title ?? '') . ' · Student Go', ' ·') }}</title>

  <link rel="icon" href="{{ asset('assets/brand/favicon.svg') }}" type="image/svg+xml">
  <link rel="icon" href="{{ asset('assets/brand/favicon.ico') }}" sizes="32x32">
  <link rel="apple-touch-icon" href="{{ asset('assets/brand/apple-touch-icon.png') }}">
  <link rel="preload" as="font" type="font/woff2" crossorigin
        href="{{ asset('assets/fonts/plus-jakarta-sans/PlusJakartaSans-latin-wght.woff2') }}">

  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-gray-900 antialiased">
  <a class="sg-skip" href="#main">Lewati ke konten</a>
  …
  <main id="main"> … </main>
</body>
</html>
```

Remove from the layout(s), after confirming nothing else uses them:

| Remove | Why (audit) |
|---|---|
| Google Fonts `<link>`s and any `font-family` overrides in `<style>` | J15: four families in three stacks |
| Boxicons CDN | J24: loaded, used 0 times |
| Font Awesome CDN | J24: used 2 times — replace those two with Lucide icons, then remove |
| AOS CSS/JS and `data-aos` attributes | J23: decorative motion; `components.css` already neutralises `[data-aos]` under `prefers-reduced-motion`, but deleting is cleaner |
| The 272 KB PNG logo | J24 |

**Keep** Leaflet and the OpenStreetMap tiles (the brief says keep the interactive map). Replace the default marker with `assets/illustrations/motifs/map-pin.svg` (and `map-pin-selected.svg` for the selected project) via `L.icon({ iconUrl, iconSize: [28, 38], iconAnchor: [14, 38] })`. This swaps only the icon image; the map, tiles, clustering and click handlers stay as they are.

Wordmark (replaces the "Karsa"/PNG logo, J20):

```blade
<a href="{{ route('home') }}" aria-label="Student Go — beranda">
  <img src="{{ asset('assets/brand/student-go-logo.svg') }}" alt="Student Go" height="28">
</a>
```

Use `student-go-logo-on-dark.svg` on the dark auth panel and footer. Set `APP_NAME="Student Go"` in `.env` — a naming decision (J20) for you, not applied here.

Icons:

```blade
<svg class="sg-icon" aria-hidden="true"><use href="{{ asset('assets/icons/ui/sprite.svg') }}#map-pin"/></svg>
```

Wrap that in `resources/views/components/icon.blade.php` if you want `<x-icon name="map-pin"/>`. The sprite must be same-origin; if you serve assets from a CDN host, inline the sprite once per page instead.

## 5. Audit finding → what this package does about it

"Visual layer" means the CSS/assets exist; the pages still have to adopt them.

| Audit ID | Finding | In this package | Still on you |
|---|---|---|---|
| J6 | Photo heroes on 15+ app pages | `.sg-page-header` (no photo) | Replace each hero block |
| J7 | Mobile layout broken | `.sg-table--stack`, `.sg-stepper`, container rules | Convert the three tables and the job wizard |
| J8 | Mixed EN/ID statuses | `[data-status]` vocabulary + status rules in DESIGN_SYSTEM | Rewrite strings; map DB values to the five statuses |
| J10 | Contrast failures | Token pairs measured ≥ 4.5:1 | Remove inline gray-400 text |
| J11 | No focus ring, unnamed controls | `:focus-visible` token, `.sg-skip` | Add `aria-label`/`aria-expanded` where missing |
| J14 | Destructive action styled primary | `.sg-btn--danger` outline + `.sg-overlay` | Add the confirm dialog |
| J15 | 4 font families | Plus Jakarta Sans + Source Serif 4 | Delete the old stacks |
| J16 | 6 accents, undefined `primary` | One brand + earth accent, `primary-*` defined | Replace `indigo/emerald/amber…` utility classes |
| J17 | Duplicate components | One of each in `components.css` | Replace page by page |
| J18 | Card grids for comparison | `.sg-table`, `.sg-list` | Switch default view to list |
| J19 | Overflow / truncation | `overflow-wrap:anywhere`, `min-width:0` rules | Show full name on a detail row |
| J20 | Leftover naming/brand | Wordmark, favicon, symbol | `APP_NAME`, titles, real privacy page |
| J23 | Decorative motion | Reduced-motion block, 150 ms max | Delete `hover:scale-*`, AOS |
| J24 | Heavy unused libs | Local fonts, SVG logo | Remove CDNs (above) |
| J25 | Mobile menu lacks notifications | Spec in DESIGN_SYSTEM | Add bell entry to the mobile menu |
| J26–J27 | Emoji titles, raw labels | Lucide icon map | Replace; format "PDF · 1,4 MB" |
| J29 | Premature validation colour | Validation colour only after `data-touched` | Add the attribute on blur |
| J31 | Rejected application drawn as green "sent" | `[data-status="ditolak"]` visual | **Logic**: bind the rail to the real status |
| J32 | Text breaking mid-word | `overflow-wrap:anywhere` only on strings without spaces | — |
| J33 | Invisible "Cari Talent" CTA | `.sg-btn--primary` always has a filled background | Replace the button |
| J34–J35 | About carousel, Contact page | Editorial pattern + photos | Rebuild both pages; add a real service contact |

## 6. Migration order (smallest blast radius first)

1. **Layout, head, fonts, favicon** (section 4). Ship this alone; every page changes typography and gray contrast at once, so look at ten pages before going on.
2. **Shared partials**: nav (desktop + mobile, with notifications), footer, flash messages, page header, empty state, status badge, pagination.
3. **Auth** (login, 3 registrations → one stepper). Keep the form field names, `@csrf`, validation and redirect logic.
4. **Student**: dashboard → browse (list default, map toggle kept) → project detail → applications → profile edit → repository.
5. **Institution**: use the existing calm workspace as the template for the others.
6. **Company**: talent list, pipeline, job wizard.
7. **Homepage and About last**: they need the photos, a real screenshot of the finished app, and copy without invented statistics (J9).

For each page: replace the shell and components, keep every route name, form field and Alpine handler, then check the six widths, an empty account, a 76-character institution name, and the page with images blocked (J12).

## 7. Photographs

```bash
php scripts/fetch-photos.php --dry-run          # lists the 60 photos, fetches nothing
php scripts/fetch-photos.php --only=homepage    # try 6 first
php scripts/fetch-photos.php                    # all 60 (about 60 downloads, 10–15 MB of WebP afterwards)
```

PHP is already on your machine (Laravel needs it), so this works the same on Windows, macOS and Linux. It needs a WebP encoder (GD with WebP, Imagick, or ImageMagick) and says so if it finds none. Photos land in `public/assets/photography/homepage/`, `about/` and `library/{pertanian,pesisir,pasar,pendidikan,komunitas,desa}/`; `credits.json` beside them lists creator, source URL, license and courtesy line for each one on disk, for the Blade captions.

Then **look at every file.** Keep a photo only if the crop works and nobody is shown in a way that suggests they endorse the product; photos tagged `minors` in `docs/ASSET_SOURCES.md` stay out of prominent placements. Write an Indonesian alt text (describes the scene, not the mood) into `credits.json` under `alt`; re-running the script keeps what you wrote. Tick *Reviewed* in `docs/ASSET_SOURCES.md` and delete the files you reject. The `library/` folders are a pool to pick from, not a gallery to display. Use `<figure class="sg-figure">` with a caption "Place — what is happening. Foto: Name / Source".

### If every download says `error setting certificate file: C:\Users\<someone else>\Downloads\cacert.pem`

That message does not come from this script or from Unsplash. It means `php.ini` on **this** computer has `curl.cainfo` (or `openssl.cafile`) pointing at a CA-bundle file that does not exist here, usually because the PHP folder or `php.ini` was copied from another computer. PHP then cannot verify any HTTPS site, so the same setting will also break Composer and any `Http::get()` call in Laravel. Fix it once:

```powershell
# 1. Which php.ini is used, and what does it say?
php --ini
php -r "echo ini_get('curl.cainfo'), PHP_EOL, ini_get('openssl.cafile'), PHP_EOL;"

# 2. Get the official CA bundle (maintained by the curl project) into a folder that exists on this computer
New-Item -ItemType Directory -Force C:\php-certs | Out-Null
Invoke-WebRequest https://curl.se/ca/cacert.pem -OutFile C:\php-certs\cacert.pem

# 3. Open the php.ini printed in step 1 and set BOTH lines (remove any leading semicolon)
#      curl.cainfo = "C:\php-certs\cacert.pem"
#      openssl.cafile = "C:\php-certs\cacert.pem"
notepad "<path of php.ini from step 1>"

# 4. Check, then run the downloader again (restart `php artisan serve` if it is running)
php -r "echo ini_get('curl.cainfo'), PHP_EOL;"
php scripts/fetch-photos.php --only=homepage
```

If notepad cannot save the file (for example it lives under `C:\Program Files`), open Notepad as Administrator first. To skip editing `php.ini` for now, run once with `php scripts/fetch-photos.php --cacert="C:\php-certs\cacert.pem"`.

Version 1.2 of the script also works around this on its own: when it sees the broken path it prints a notice and downloads through the operating system's `curl` command (`curl.exe`, included with Windows 10 1803+ and Windows 11), which uses the Windows certificate store. Certificate checking stays on. Fixing `php.ini` is still worth doing. *Tested here on Linux with a simulated bad path; not run on Windows.*

If a download fails, the script lists it; open that photo's Source URL (in `docs/ASSET_SOURCES.md`), use the site's Download button, and save it at the listed local path.

## 8. Verification you should run (and what to look for)

| Check | How |
|---|---|
| Widths | 1440, 1366, 1024, 768, 390, 360 px; `document.documentElement.scrollWidth <= innerWidth` on each page |
| Fonts | `document.fonts.check('600 16px "Plus Jakarta Sans"')` is true; no request to Google |
| Console and network | zero errors, zero 4xx/5xx on assets |
| Keyboard | Tab through nav, forms, tables; ring always visible; Esc closes overlays |
| Reduced motion | OS setting on: no animation |
| Contrast | Use browser devtools on text over `gray-500` and `brand-600` surfaces |
| Real data | 77 projects, 216 documents, long names, empty account, missing image, each status |
| Roles | Student, institution, company, each logged in |

---

# SEPARATE RECORD — data-integrity and privacy problems this pass did NOT fix

The audit marks these P0/P1 and says a redesign must not hide them. Nothing in this package changes data, access control, routes or business logic, and no component was designed to disguise them. Each needs a decision or backend work.

## P0

| ID | Problem (audit evidence) | Why the redesign must not mask it | Needed from you |
|---|---|---|---|
| **J1** | One person shows two names and unrelated initials: 127 of 127 sample profiles mismatch; "Uli Hidayat" can appear to companies as "Oki Nabila". | Avatar initials in the new design are derived from the name passed in. If you pick one name source and derive initials from it, the bug is fixed; if you "fix" it by hiding the avatar or the second name, a company can still contact the wrong person. | Choose the single source of truth for a student's display name; fix the seeder/model. |
| **J2** | Lists repeat records and hide others (15 of 77 problems and 7 of 216 documents never appear). | The new list/table will show exactly what the query returns. Do not "solve" this with infinite scroll or a larger page size. | Deterministic sort with an `id` tie-breaker; real counts. |
| **J3** | Guests can fetch a student's email and WhatsApp from `/profile/{username}` without logging in (700 students). | The profile redesign shows a contact block as a separate component. **It displays whatever the backend sends, as before.** It was not hidden or reduced here because that is an access-control change. | A product/legal decision on contact visibility; then a policy in the controller. |
| **J4** | Counts contradict each other (77 / 73 / 62 / "1 dari 77"; 221 vs 216; 12 of 699). | Do not put any new number in a summary row until its definition is settled. `.sg-summary` shows only values you pass it. | One definition per count; label filtered totals as filtered. |

## P1 data/behaviour items

| ID | Problem | Note |
|---|---|---|
| **J5** | Edit forms lose saved values (empty name, "Pilih Jenis", empty size). | Restyling the fields will not fix the binding. Align enums with stored values. |
| **J9** | Invented or meaningless metrics on login, home, About ("34 Provinsi / 514 Kab/Kota / 85+ mitra…", "520K+ mahasiswa", "±Rp 1,2 triliun", ±0% trends). | The new homepage/About design contains none. Do not paste the old numbers back into `.sg-summary`. |
| **J12** | All 77 problem pages reference 310 gallery images in a remote Supabase bucket the local app cannot load. | Environmental. The design omits the media region when there is no image; pointing local dev at local storage is yours. |
| **J13** | GET requests that create records (`/student/chatbot/create`, `/institution/reviews/create`) and branded 500/debug pages (`/student/repository/abc`). | Route and `APP_DEBUG` changes; not touched. Turn `APP_DEBUG` off outside local. |
| **J22** | Talent search mixes a numbered pager with load-more; every location says "Indonesia". | Data and query work. |
| **J31** | A rejected application renders as a green "Aplikasi Terkirim" card. | The `ditolak` status style exists; choosing it is view/controller logic. |
| **J38** | Seed notifications do not match account state. | Seeder content. |

## Decisions the audit lists as blocking (still open)

Product name (Student Go vs Karsa in `APP_NAME`, titles, KARA assistant); contact-visibility policy; sort order and count definitions; Indonesian-first language policy with an approved status dictionary; enum alignment for institution type and company size; whether Student Go stays KKN-only.

---

## Rollback

All changes are additive until you edit views. To undo the global effects: remove the three `@import` lines from `app.css`; the pre-existing CSS and fonts return. `git switch -` restores the earlier branch.
