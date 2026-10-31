# Student Go — redesign asset package

Design system, original SVG graphics, local fonts, icons and a photo-fetch script for the Student Go redesign, built against the design audit (`Student_Go_Audit_Desain_ID 1.pdf`). The folder layout mirrors the Laravel project, so you can copy it over the project root.

**Direction:** *Ruang Kerja* (workbench: neutral surfaces, strong type, tables and lists, one brand colour) as the foundation, with a restrained *Catatan Lapangan* (field notes) layer for editorial pages: a reading serif, a single earth accent, route-line motifs, coordinate labels, documentary photography.

## What is in the box

| Path | What |
|---|---|
| `resources/css/student-go/` | `fonts.css`, `tokens.css` (Tailwind v4 `@theme` + semantic variables), `components.css` (`.sg-*` components) |
| `public/assets/brand/` | wordmark and symbol (light and on-dark), `favicon.svg`, `favicon.ico`, `apple-touch-icon.png` |
| `public/assets/illustrations/empty-states/` | 5 empty-state illustrations |
| `public/assets/illustrations/workflows/` | student, institution, company journeys and project-status diagrams, horizontal and vertical |
| `public/assets/illustrations/geographic/` | Indonesia island-group map (7 regions) and outline, from Natural Earth public-domain data |
| `public/assets/illustrations/motifs/` | contour field, survey marks, Leaflet map pins |
| `public/assets/icons/ui/` | 71 Lucide icons at 1.5px stroke, `sprite.svg`, `icons.json`, ISC license |
| `public/assets/fonts/` | Plus Jakarta Sans (UI) and Source Serif 4 (reading), WOFF2, with OFL texts |
| `public/assets/photography/` | **empty until you run the downloader** (`homepage/`, `about/`, `library/*`, `projects/`) |
| `scripts/fetch-photos.php` | downloads the 60 license-checked photographs, writes optimized WebP and `credits.json` (`fetch-photos.sh` is a wrapper) |
| `docs/photo-catalog.json` | the 60 photo records (source page, creator, license, location, download URLs) plus the items I looked at and rejected, with reasons |
| `docs/DESIGN_SYSTEM.md` | tokens, type, colour with measured contrast, components, page composition, review checklist |
| `docs/ASSET_SOURCES.md` | source, creator, license, attribution and local path for every asset |
| `docs/INTEGRATION.md` | copy-in steps, CSS and layout wiring, migration order, and the **separate record of P0 data/privacy issues not fixed here** |
| `docs/styleguide.html` | living reference built from the real CSS (serve over HTTP; not `file://`) |
| `docs/screenshots/` | the style guide at 1440, 1366, 1024, 768, 390 and 360 px |
| `assets-manifest.json` | every asset and CSS file with byte size and SHA-256, plus the photo records |

## What this package is not

It is not the redesigned application. No Blade view, layout, controller or route was changed, because the session that built it could not reach the Student Go project folder, and it never ran inside the app. The 60 photographs are selected and license-checked page by page but **not downloaded or looked at**. Both limits and the next steps are in `docs/INTEGRATION.md` section 0.

## Quick start

```bash
# from the package root, copy into the project (see docs/INTEGRATION.md section 2), then in the project:
php scripts/fetch-photos.php      # 60 photos -> public/assets/photography/{homepage,about,library/*}/
```

```css
/* resources/css/app.css */
@import "tailwindcss";
@import "./student-go/fonts.css";
@import "./student-go/tokens.css";
@import "./student-go/components.css";
```

Then follow the migration order in `docs/INTEGRATION.md` section 6.

## Principles the CSS enforces

One brand colour (`#405080`) · no gradients on controls · radii 6px (controls) and 8px (panels) · shadows only on overlays · pill shape means status, and status is always icon plus word · motion limited to 150ms state changes and off under `prefers-reduced-motion` · visible focus ring · text pairs at least 4.5:1 and input borders at least 3:1 (measured, in `DESIGN_SYSTEM.md`) · no emoji · no CDN at runtime for fonts or icons.

## Licenses

Fonts: SIL OFL 1.1. Icons: ISC. Map data: Natural Earth, public domain. Photographs: Unsplash License and Pexels License (permissive, no attribution required; courtesy credits are printed anyway). Everything else is original to this project. Details and URLs: `docs/ASSET_SOURCES.md`.
