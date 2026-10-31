# Student Go — Design System

Direction: **Ruang Kerja (workbench)** as the foundation, with a restrained **Catatan Lapangan (field notes)** layer for content that deserves editorial treatment. This is the hybrid recommended in the design audit (section I): build Option 1 first, then add the serif and earth accent from Option 2 for reading views.

Living reference: open `docs/styleguide.html` (built from the real CSS in `resources/css/student-go/`). Every component below appears there, tested against long Indonesian strings, a 76-character institution name, a 52-character email, and tabular figures.

> **This copy lives in the Laravel project** at `student-go-docs/design-system/` (Phase 2, 2026-10-30). Section 0 describes what is actually integrated. Sections 1-11 are the package specification, approved as is (D-17); where they say `docs/…`, read `student-go-docs/design-system/…`.

---

## 0. Implementation in this project (Phase 2, 2026-10-30)

### 0.1 Where everything is

| What | Project path | Loaded today? |
|---|---|---|
| Local fonts (`@font-face`, WOFF2, `font-display: swap`) | `resources/css/student-go/fonts.css` → `public/assets/fonts/` | Yes. Imported in `resources/css/app.css`. A font file is downloaded only when something uses the family. |
| Student Go tokens (brand, earth, semantic, `--shadow-overlay`, `--ease-standard`, `--duration-state`, every `--sg-*` role, `.sg-on-dark`) | `resources/css/student-go/tokens.css` | Yes |
| `.sg-*` components | `resources/css/student-go/components.css` | Yes. No page uses them yet. |
| Global overrides: `--font-sans`, `--font-serif`, the 9-step `text-*` scale, `tracking-tight`, re-tuned `gray-*`, `primary-*` aliases, `radius-*` (including the `xl/2xl/3xl` shim), `@layer base` element rules (html font, body colour, h1-h4 weight, tabular figures, `overflow-wrap`, `:focus-visible` ring), and the global `prefers-reduced-motion` rule | `resources/css/student-go/legacy-overrides.css` | **No: staged (D-29).** The `@import` line is commented out in `app.css`. **Phase 3** activates it together with the new shell. |
| Brand | `public/assets/brand/` | Files only. The layout still shows `karsa-logo.png` and has no `<link rel="icon">` until Phase 3. |
| Icons | `public/assets/icons/ui/` (71 SVG + `sprite.svg` + `icons.json` + licence), Blade helper `resources/views/components/icon.blade.php` | Files and helper. Nothing uses them yet. |
| Illustrations | `public/assets/illustrations/{empty-states,workflows,geographic,motifs}/` | Files only |
| Catalogue photographs | `public/assets/photography/{homepage,about,library/*}/` + `credits.json` | Files only. No photograph is placed in any page (D-19). Review state: `photo-review.json`. |
| Photo downloader | `scripts/fetch-photos.php`, `scripts/fetch-photos.sh` | Copied, **not run**: all 60 files were already in the package |
| Living style guide | `student-go-docs/design-system/styleguide.html` (asset paths rewritten to `../../public/assets/`) | Serve the project root over HTTP; do not open it via `file://` |

**Why the split.** In the package, `tokens.css` and `components.css` redefine Tailwind's default names (`gray-*`, `font-sans`, `rounded-xl`…) and style bare elements. Those classes are used in 97 of the 102 views, so importing the files as they are would restyle every page at once. Phase 2 must not change any page (acceptance criterion and the user's scope). The rule for the split is: a token whose name is a Tailwind default theme variable, or a selector that matches plain elements, goes to `legacy-overrides.css`; everything in a Student Go namespace stays in `tokens.css` and `components.css`. No value was changed. Evidence: all 117 token declarations are identical before and after the split, and the moved base block is verbatim (Phase 2 report).

**Until Phase 3 activates `legacy-overrides.css`**, `.sg-*` components take gray, radius and font from Tailwind's defaults (for example `--color-gray-900` is still `oklch(21% .034 264.665)` and `--font-sans` is still the old stack). Do not adopt `.sg-*` in a page before then. To activate it, un-comment `@import './student-go/legacy-overrides.css';` in `resources/css/app.css` and rebuild. A preview build with it active compiles (145.27 kB).

> **Phase 3 update (2026-10-30):** `legacy-overrides.css` is now **imported** (D-29 staging ended), so the re-tuned `gray-*`, `primary-*` aliases, radius shim, type scale, `--font-sans`, base rules, `:focus-visible` ring and reduced-motion rule apply to every page. The old `@theme` block with 'Instrument Sans' was removed from `app.css`. `.sg-*` components may now be adopted by pages. The shell and shared Blade components (`<x-ui.*>`) are documented in `student-go-docs/redesign-execution/COMPONENT_INVENTORY.md`; their CSS was appended to `components.css` (Phase 3 section).

### 0.2 Token map (what the user asked for → where it is)

| Token family | Source of truth | Notes |
|---|---|---|
| Brand colours | `tokens.css` `--color-brand-50…900`; roles `--sg-brand`, `-hover`, `-active`, `-tint`, `--sg-focus` | `#405080` = brand-600 |
| Neutral colours | `legacy-overrides.css` `--color-gray-50…900`; roles `--sg-text-*`, `--sg-surface-*` in `tokens.css` | Re-tuned ramp; active from Phase 3 |
| Semantic colours | `tokens.css` `--color-{success,warning,danger,info}-{50,600,700}` | |
| Status colours | `tokens.css` `--sg-status-{menunggu,ditinjau,diterima,ditolak,dibatalkan}-{bg,fg}`; `.sg-status[data-status]` | One vocabulary; icons in section 3 |
| Editorial accent | `tokens.css` `--color-earth-*`, `--sg-earth`, `--sg-earth-tint` | Editorial pages only |
| Typography scale | `legacy-overrides.css` `--text-xs…5xl` (+ line heights), `--tracking-tight`; families `--font-sans`, `--font-serif` | Same sizes as Tailwind's defaults; line heights are fixed `rem` values, and 4xl/5xl are taller. That is why the scale is staged. |
| Spacing | Tailwind's default `--spacing: 0.25rem` (4px base), unchanged | The package defines no spacing token, so none was invented. Layout widths: `--sg-w-workspace` (1200), `--sg-w-reading` (720), `--sg-w-form` (672); gutter `--sg-gutter` 16/24/32px |
| Radius | `legacy-overrides.css` `--radius-xs…3xl`; roles `--sg-radius-control` (6px), `--sg-radius-panel` (8px) in `tokens.css` | xs/sm/md/lg equal Tailwind's default values |
| Border colours | `tokens.css` `--sg-border-default` (gray-200), `--sg-border-strong` (gray-300), `--sg-border-control` (gray-400) | |
| Elevation | `tokens.css` `--shadow-overlay` (Tailwind utility `shadow-overlay`), `.sg-overlay` | Overlays only |
| Motion | `tokens.css` `--ease-standard`, `--duration-state` (150ms); reduced-motion rule in `legacy-overrides.css` | |
| Breakpoints | Tailwind's defaults, unchanged: `sm` 640, `md` 768, `lg` 1024, `xl` 1280, `2xl` 1536 | `components.css` uses 640 and 1024, which match `sm` and `lg`. No breakpoint token was added. |

### 0.3 Icons in Blade

```blade
<x-icon name="map-pin" />                                 {{-- 20px, decorative (aria-hidden) --}}
<x-icon name="bell" size="sm" label="Notifikasi" />       {{-- 16px, role="img" + aria-label --}}
<x-icon name="calendar" size="lg" class="text-gray-500" />{{-- 24px, extra classes merged --}}
```

The helper renders `<svg class="sg-icon …"><use href="{{ asset('assets/icons/ui/sprite.svg') }}#name"></use></svg>`. Names come from `public/assets/icons/ui/icons.json` (71). Any size other than `sm`/`lg` falls back to 20px. Stroke is 1.5px in every symbol (checked by `tests/Feature/Phase2/DesignSystemAssetsTest.php`). Boxicons and Font Awesome stay loaded until Phase 3 (D-20): nothing was removed.

### 0.4 Contrast, re-measured on 2026-10-30 from the token files

All 41 text and UI pairs pass (text ≥ 4.5:1, UI ≥ 3:1). Three decorative or disabled pairs are exempt. The full table, generated from the hex values in `tokens.css` and `legacy-overrides.css`, is `reports/phase2/contrast.md` in `student-go-docs/redesign-execution/`. The values match section 3 below. One observation for Phase 3: the secondary-button outline (`gray-300` on white, 1.59:1) relies on the label and the white fill to identify the control. That is allowed under WCAG 1.4.11 because the text identifies the button, but re-check it once buttons sit on `gray-50` surfaces.

### 0.5 What is protected and separate

Certificate typography (Poppins, Pinyon Script in `public/assets/certificate/fonts/`, rendered by GD in `app/Services/CertificateService.php`) is independent of these web fonts. Phase 2 left it byte-identical, and a re-rendered sample is pixel-identical to the Phase 0 baseline.

---

## 1. What this system answers from the audit

| Audit finding | What replaces it |
|---|---|
| Three product names (KKN Go, Karsa, "Laravel" in every tab) | Student Go wordmark + symbol + favicon (`public/assets/brand/`); `lang="id"` and real titles are an integration step (see INTEGRATION.md) |
| Four web-font families in three stacks, 12 text sizes | One interface family (Plus Jakarta Sans), one reading serif (Source Serif 4), 9-step scale, max 30px inside the app |
| At least six competing accents; undefined `bg-primary-*` | One brand colour `#405080`; `primary-*` now aliases `brand-*`; earth accent for editorial surfaces only |
| Gradient buttons, mesh blobs, glass tiles | Flat controls; no gradient anywhere in the component CSS |
| Pastel stat tiles repeated on 15+ pages | `.sg-summary` inline row ("2 lamaran · 1 menunggu · 1 ditolak") |
| Card grid for every record type | `.sg-table` / `.sg-list` as default; cards only for an optional gallery |
| Placeholder tiles, broken image galleries | No placeholder components exist. Media regions are omitted when there is no image (`[hidden]`, rule in components.css) |
| Generic mock-UI illustrations, stock office photos | Original SVG diagrams of the real workflows; empty states in one hand-drawn family; documentary photography only where it earns its place |
| Gray-400 text at 2.5:1, no focus ring, no reduced-motion | `gray-*` re-tuned (below), `:focus-visible` ring token, global `prefers-reduced-motion` rule |
| Mixed Indonesian/English status words | One vocabulary: Menunggu, Ditinjau, Diterima, Ditolak, Dibatalkan, each with an icon so colour is never the only signal |
| Difficulty shown with status colours ("Advanced" reads as an error) | Neutral 1–3 step meter: Pemula, Menengah, Lanjut |

---

## 2. Typography

### Decision record

Candidates from the audit were rendered side by side with real Student Go strings (long institution names, SDG labels, numeric columns, a 52-character email, Indonesian body copy).

| | Plus Jakarta Sans | IBM Plex Sans | Source Serif 4 |
|---|---|---|---|
| Role | **Interface + headings (chosen)** | Rejected | **Editorial reading only (chosen)** |
| Delivery | One variable WOFF2, 27 KB latin (+22 KB latin-ext, fetched only when needed) | Four static files (94 KB for 400/500/600/700) | One variable WOFF2, 51 KB latin |
| Tabular figures | Yes: real `tnum` and `pnum` features | Digits are tabular by default, but no `tnum` feature exposed | Yes: `tnum` |
| Indonesian text | Handles long compounds well, but **measured 5.0% wider than Plex** at 14px on five real strings (3.5% to 7.8% per string) | Narrower, more neutral | Comfortable at 18/28 |
| Licence | SIL OFL 1.1 | SIL OFL 1.1 | SIL OFL 1.1 |

Plus Jakarta Sans gives the product a distinct voice that is locally meaningful (the audit notes it was made for Jakarta's city identity) and ships both proportional and tabular figures in one small file. IBM Plex offered no advantage the app needs.

The trade-off is width: about 5% more horizontal space than Plex, which matters in dense tables. It is handled by letting long names wrap (`overflow-wrap: anywhere` on cells, tested with the 76-character institution name and 52-character email) and by `min-width: 0` content columns in the stacked mobile table. If a specific table proves too tight in the real app, `text-sm` (14px) is the floor; do not drop to 13px.

Not chosen on purpose: Inter, Poppins, Montserrat, Outfit, Roboto, Merriweather, Lora (all in use by the current app or on your avoid list).

### Rules

- Weights used: **400** body, **500** labels and navigation, **600** headings and buttons, **700** marketing only. The variable files contain a wider range; `fonts.css` declares `400 700`. Do not use 800/900 or `font-black`.
- Fonts are served from `/assets/fonts/` with `font-display: swap`. There is no Google Fonts request at runtime. Remove the Google/CDN font links from the layouts.
- Figures in tables, summary rows and statistics use `font-variant-numeric: tabular-nums` (already applied to `table` and `.sg-num`).
- Paragraphs max `68ch`. Tracking `-0.01em` on 24px and above. Secondary text is distinguished by colour, never by going below 12px.
- Source Serif 4 is used only for `.sg-prose` (paragraphs longer than three lines in reading views: project brief, about, stories). Never for UI.
- The Latin subset has no `→` (U+2192) or `↗` (U+2197). Use the `arrow-right` and `arrow-up-right` icons instead of typing these characters.

### Scale (px size / line-height)

| Step | Tailwind | Use |
|---|---|---|
| 12/16 | `text-xs` | Metadata and captions only. Never body text |
| 14/20 | `text-sm` | Table cells, dense UI |
| 16/24 | `text-base` | Body, form input |
| 18/28 | `text-lg` | Lead paragraph, `.sg-prose` |
| 20/28 | `text-xl` | Section heading |
| 24/32 | `text-2xl` | Page heading (`h1` in the app) |
| 30/36 | `text-3xl` | Dashboard greeting, marketing `h2` |
| 36/44 | `text-4xl` | Marketing only |
| 48/56 | `text-5xl` | Marketing only |

Nine steps, down from twelve. Nothing larger than 30px inside the authenticated app.

---

## 3. Colour

Brand is derived from the existing logo (`#405080`). The audit estimated 8.3:1 against white; the exact value measures **7.84:1**, which still passes AA for text and meets AAA-level contrast for normal text.

### Measured contrast (WCAG 2.x relative luminance)

| Pair | Ratio | Result |
|---|---|---|
| brand-600 `#405080` on white (links, primary button background with white text) | 7.84 | AA |
| brand-700 `#344168` on white (hover) | 9.99 | AA |
| gray-900 `#1B2236` on white (text-primary) | 15.81 | AA |
| gray-600 `#4B5262` on white (text-secondary) | 7.83 | AA |
| gray-500 `#5F6778` on white (text-tertiary) | 5.68 | AA |
| gray-500 on gray-50 `#F4F5F8` | 5.21 | AA |
| gray-400 `#7C8494` on white (input border) | 3.76 | Passes 3:1 for UI components; **not for text** |
| gray-200 `#DFE2E8` (decorative dividers) | 1.3 | Decorative only; allowed |
| earth-600 `#9A4F28` on white (editorial marker) | 5.96 | AA |
| success-700 on success-50 | 6.56 | AA |
| warning-700 on warning-50 | 6.85 | AA |
| danger-700 on danger-50 | 6.61 | AA |
| info-700 on info-50 | 6.95 | AA |
| gray-700 on gray-100 (Menunggu) | 9.37 | AA |
| On dark `#171C2E`: text `#F4F6FB` | 15.64 | AA |
| On dark `#171C2E`: link/brand `#AEB9DD` | 8.68 | AA |

For comparison, the current app: white on purple-500 (the "Hubungkan" button) 3.96:1, and gray-400 text `#9CA3AF` on white 2.54:1. Both fail AA; the re-tuned `gray-*` ramp fixes the second automatically for existing markup.

### Roles

- **Brand** (`brand-600`): primary button fill, links, selected navigation, focus ring. One brand surface per view.
- **Neutral** (`gray-*`): text, borders, surfaces. Input borders use `--sg-border-control` (gray-400) to satisfy WCAG 1.4.11; dividers use `--sg-border-default` (gray-200).
- **Semantic**: success, warning, danger, info. Danger only for errors and destructive actions.
- **Earth** (`earth-*`): `.sg-marker`, photo captions, the selected map pin, the destination node in diagrams. Never on buttons or large fills.
- **Status vocabulary**: Menunggu (neutral), Ditinjau (info), Diterima (success), Ditolak (danger), Dibatalkan (neutral). The same word, icon and colour in lists, detail pages and notifications.

  | Status | Icon (Lucide) |
  |---|---|
  | Menunggu | `hourglass` |
  | Ditinjau | `eye` |
  | Diterima | `circle-check` |
  | Ditolak | `circle-x` |
  | Dibatalkan | `circle-dashed` |

- **Difficulty** is not a status: `.sg-meter` with 1–3 neutral steps and the words Pemula, Menengah, Lanjut.
- **SDG colours**: small swatch (`.sg-chip__swatch`) beside the goal name, never a filled chip. Check the UN SDG icon usage guidelines before using official icons.
- **Role colours** (student/institution/company): optional, only on the role badge in the avatar menu and public-profile header. Never on buttons.

---

## 4. Space, shape, elevation, motion

- Spacing base 4px: 4, 8, 12, 16, 24, 32, 48, 64. Gutters 16/24/32px (mobile/tablet/desktop). 32–48px between page sections, 12–16px inside components.
- Widths: workspace 1200px, reading column 720px, single-column forms 640–720px.
- Radius: 6px controls, 8px panels, full only for avatars and status pills. `rounded-xl/2xl/3xl` are collapsed to 8px as a migration shim.
- Elevation: border or divider groups content. Shadow only on menus, dialogs, toasts and sticky bars (`--shadow-overlay`, `.sg-overlay`).
- Motion: colour and opacity changes of 150ms on state changes only. No hover scale/lift. A global `prefers-reduced-motion` rule disables transitions and neutralises AOS until it is removed.

---

## 5. Components (`resources/css/student-go/components.css`)

Prefix `.sg-`. All are plain CSS and work with or without Tailwind utilities.

| Component | Classes | Notes |
|---|---|---|
| Buttons | `.sg-btn` + `--primary` `--secondary` `--tertiary` `--danger` `--danger-solid` | One primary per view. Destructive is an outline; solid red only inside a confirmation dialog. 36px tall, 44px on touch |
| Form field | `.sg-field` `.sg-label[data-required]` `.sg-input` `.sg-select` `.sg-textarea` `.sg-help` `.sg-error` | Label above, help below. Validation colour only after `data-touched` is set |
| Status | `.sg-status[data-status]` | Pill is reserved for status; always icon + word |
| Taxonomy chip | `.sg-chip` + `.sg-chip__swatch` | Square-ish, outlined |
| Difficulty | `.sg-meter[data-level]` | Neutral |
| Page header | `.sg-page-header` | Back link, `h1`, one-line description, primary action. No photo |
| Inline summary | `.sg-summary` | Replaces stat tiles. Show only non-zero or actionable numbers |
| Table | `.sg-table-wrap` `.sg-table` `.sg-table--stack` `.sg-num` | Sticky header, tabular figures. Below 640px rows stack using `data-label` attributes; at most one scroll container |
| List | `.sg-list` | Divider-separated rows for people and notifications |
| Panel / overlay | `.sg-panel` / `.sg-overlay` | Border-only vs the single shadowed surface |
| Empty state | `.sg-empty` | Illustration, title, one line, one action. Add a role-specific next step |
| Stepper | `.sg-stepper` | One component for all three registrations and the job wizard. Mobile shows a progress track and "Langkah 2 dari 4" |
| Upload | `.sg-dropzone` `.sg-file-chip` | One upload pattern everywhere |
| Editorial | `.sg-marker` `.sg-prose` `.sg-figure` `.sg-coord` `.sg-divider-route` | Field-notes layer: about, project brief, stories only |
| Backdrops | `.sg-contour` `.sg-survey-marks` | At most one per page |

---

## 6. Icons

**Lucide** v1.64.0 (ISC), 71 icons chosen for the screens the audit documents, each normalised to a 1.5px stroke on a 24px grid. Sizes 16/20/24 (`.sg-icon--sm`, default, `--lg`). Use via the sprite so one request serves every icon:

```html
<svg class="sg-icon" aria-hidden="true"><use href="/assets/icons/ui/sprite.svg#map-pin"/></svg>
```

The sprite is same-origin by design (external `<use>` is blocked across origins and from `file://`). Icon-only buttons need an accessible name (`aria-label`). No emoji as icons. No Font Awesome or Boxicons (the audit found them loaded but used 2 and 0 times).

One meaning, one icon (full list in `public/assets/icons/ui/icons.json`): location `map-pin`, deadline `calendar`, duration `clock`, quota `users`, institution `building-2`, company `briefcase`, student `graduation-cap`, document `file-text`, repository `folder-open`, saved `bookmark`, apply `send`, verified `badge-check`, SDG `target`, KARA assistant `message-square-text`, list view `layout-list`, gallery view `layout-grid`, map view `map`.

---

## 7. Original graphics

All hand-built for Student Go and recorded in `docs/ASSET_SOURCES.md`.

- **Symbol**: a route drawn as an "S". The hollow ring is the origin (campus); the solid dot is the destination (KKN site). Built from two tangent half-circles with horizontal tails, like a transit line. Works down to 16px; the favicon sets it white on a brand tile.
- **Wordmark**: Plus Jakarta Sans Bold converted to outlines (no webfont dependency), "Go" in the brand colour. `-on-dark` variants for dark surfaces.
- **Empty states (5)**: no applications, no saved projects, no documents, no notifications (a *kentongan*, the village slit-drum), no search results. One 1.5px stroke, one brand colour, one tint, one earth accent each, corner survey ticks as the family signature. Displayed at natural size or larger; strokes are non-scaling.
- **Workflow diagrams (4 × horizontal and vertical)**: student, institution and company journeys and the project status progression. Drawn only from workflows documented in the audit. Inline them so the text uses the page font; as `<img>` they fall back to the system font. Use the vertical file under 768px.
- **Geography**: `indonesia-regions.svg` has seven `<g id="region-*">` groups (Sumatra, Jawa, Kalimantan, Sulawesi, Bali dan Nusa Tenggara, Kepulauan Maluku, Papua). Add `class="is-active"` from real data to tint a region. `indonesia-outline.svg` is a background motif. Derived from Natural Earth public-domain data. The interactive Leaflet map stays; these are not a replacement for it.
- **Motifs**: `contour-field.svg` (procedural topography), `survey-marks.svg` (tile), `map-pin.svg` and `map-pin-selected.svg` (Leaflet `L.icon`, 28×38, anchor `[14, 38]`).

Use sparingly. Each element must say something about the content; none is decoration for its own sake.

---

## 8. Photography

Photographs appear only where they communicate something about the content: the public homepage (one or two, never as a hero), the About page, and editorial project stories. Never as a page header in the authenticated app, never as a placeholder.

- Show images with `.sg-figure`: a caption in the form *Place — what is happening. Foto: Name / Source, licence*.
- Alt text describes the scene, not the mood.
- Stock photography is never presented as a Student Go project, student, or institution. `public/assets/photography/projects/` stays empty: project images come from uploads. If a project has none, omit the media region.
- Photos of identifiable people must not imply that those people endorse Student Go. Photos showing children are kept out of prominent placements.
- The package holds a pool of 60 license-checked photos (`homepage/`, `about/`, `library/*`). Choose a few; do not display the pool. Each is listed in `docs/ASSET_SOURCES.md` with its creator, source page and license.

---

## 9. Page composition by role

| Surface | Composition |
|---|---|
| Homepage | Short typographic intro; student and institution sections explained with the workflow diagrams and a screenshot of the real app; the real Leaflet map; one or two documentary photographs in an editorial band. No full-screen photo hero, no invented statistics |
| About | Editorial: asymmetric text/photo layout, captions, `.sg-marker` numbering. No carousel |
| Auth | One shared template: wordmark, form, one role selector, optionally a contour or workflow graphic. One stepper for all registrations |
| Student dashboard | Page header with the next deadline, `.sg-summary` row, "Langkah berikutnya" queue, three recommendations as list rows |
| Browse projects | Filter row + sortable list by default (institution · location · SDG · quota · deadline); optional gallery only when real cover images exist; map view stays |
| Project detail | Reading column (720px) + sticky apply rail; media only if images exist |
| Profile / portfolio | Stories, verified projects, skills; contact private by default (see P0 note) |
| Repository | Document table: title, institution, region, year, views, downloads, human-readable file chip |
| Institution | The calm workspace pattern from the audit kept as the base template for all roles |
| Company | ATS-style: candidate list with filters, pipeline table grouped by stage, one pager |

---

## 10. Responsive and accessibility baseline

- Verified on the styleguide at 1440, 1366, 1024, 768, 390 and 360px: no page-level horizontal overflow, fonts loaded, no failed requests.
- Tables stack under 640px; at most one horizontal-scroll container per table; filter sidebars become drawers with a sticky "Terapkan" button; the mobile menu includes notifications.
- `:focus-visible` ring on every interactive element; skip link; one `h1` per page; `lang="id"`; labels bound to controls; accessible names for icon buttons; `aria-expanded` on menus; `aria-live` for the kabupaten loader and notification count.
- Status always as text + icon. Dialogs trap focus and close on Esc.

---

## 11. Review checklist (the audit's guardrails, as pull-request rules)

1. App pages start with an `h1` page header inside the first 160px. No hero.
2. Every number is real and useful. No marketing counters, no trend chip without two periods of data, no chart under five data points.
3. Cards only for independent, actionable objects in an optional gallery. Default to tables, lists, divider-separated sections.
4. One elevation rule: borders group content; shadows only for menus, dialogs, toasts, sticky bars.
5. No gradients on controls. At most one brand surface.
6. Pill means status. Buttons use the standard radius.
7. Images only when they carry content. No placeholder tiles; show initials or nothing.
8. Motion has a job: 150–200ms colour/opacity on state change, disabled under `prefers-reduced-motion`.
9. Copy comes from the domain: Indonesian first, KKN/SDG/regional examples, one word per status.
10. Test every list against real data: 77 projects, 216 documents, 76-character institution names, empty accounts.
