# Phase 29, paket G — Peta wilayah di profil publik instansi (U4, D-45)

Date 2026-11-01. Branch `feat/institution-manage` @ `000523d` (user's choice; no commit). The working tree already held uncommitted work of other packages (A, B, C, E, F, H, run in parallel sessions); the user allowed continuing without touching it.

## Files

| File | Change |
|---|---|
| `app/Support/ProvinceRegion.php` | **New.** 38 BPS province codes (two digits) → seven groups of `indonesia-regions.svg`; `summary()` counts open problems per group (no zero entries; home region only when there are none); `inlineSvg()` reads the file at render time, drops its own `<style>` and root `<title id="t">`, sets `aria-hidden="true"`, adds `.is-active` |
| `app/Http/Controllers/Institution/ProfileController.php` | `showPublic`: one helper call on the problems already loaded (`status = open`), passed as `$regions`; no new query |
| `resources/views/institution/profile/components/region-map.blade.php` | **New** partial: heading 'Wilayah', inline SVG, visible text list ('4 masalah dibuka di Jawa', 'Wilayah asal instansi: Jawa') |
| `resources/views/institution/profile/public.blade.php` | `@include` of the partial at the end of the aside |
| `resources/css/student-go/public-pages.css` | Section `.sg-region-map*` (tokens only; graticule labels hidden in the narrow column) |
| `tests/Unit/ProvinceRegionTest.php` | **New**, 43 tests (38 province codes + table, unknown codes, labels = SVG titles, summary) |
| `tests/Feature/Phase29/InstitutionRegionMapPackageGTest.php` | **New**, 6 tests |

## Acceptance criteria (L.G)

| Criterion | Evidence | Result |
|---|---|---|
| Route count before = after | `route:list --json` 203 before and after, files byte-identical (`cmp`) | ✅ |
| Forms unchanged | The page has no form before or after (`metrics-*.json`, `forms: []` on all 16 captures) | ✅ (N/A) |
| Tests pass; new package G tests incl. 38-code unit test | Package G: 49/49 passed (133 assertions). Full suite: 433/437; the 4 failures are all in `PackageFPolishTest` (package F, in progress in another session, red tests written before its fix). Baseline before package G: 374/375, failure also package F (`PackageFDefectsTest` C2) | ✅ for G; suite not fully green because of package F |
| `npm run build` | OK (CSS 249.44 kB, gzip 40.46 kB; size also moves with parallel packages' CSS) | ✅ |
| 0 `<style>` / 0 hex in changed view and partial | Feature test `test_changed_views_have_no_style_block_or_hex_colour`; the SVG's internal `<style>` (with hex fallbacks) is stripped from the output | ✅ |
| No overflow at 360/390/768/1440 | `scrollWidth == innerWidth` on all 16 captures (`metrics-after.json`) | ✅ |
| Before/after screenshots 33, 56, 59, 61 | `phase29-G/before/`, `phase29-G/after/` (4 widths each) | ✅ |
| No English label | New copy: 'Wilayah', 'masalah dibuka di', 'Wilayah asal instansi'; group labels from the SVG titles | ✅ |
| Statuses from `StatusVocabulary` unchanged | No status markup touched | ✅ |
| `.is-active`, text equivalent, real numbers, no '0' | Live: 33 → '4 masalah dibuka di Jawa', 56 → 3, 59 → 3, 61 → 2, each equal to the header's 'N masalah dibuka' (existing query); `region-jawa` active only. Tests: count = `problems()->where('status','open')` sum; without open problems only 'Wilayah asal instansi: Jawa' and no '0 masalah'; unknown province → section hidden | ✅ |
| No data beyond today's public data; SVG byte-identical | Shown: island group (derived from the province already shown) and open-problem counts (already shown per problem and in the header). Test asserts address, email, phone absent. SVG SHA-256 `d2debbc8…0c51ad` unchanged (test + hash list) | ✅ |
| `public/` hashes unchanged | Method: SHA-256 list before/after (no `baseline/asset-hashes.txt` on this branch); 228 files, identical | ✅ |
| Deprecate records | None expected, none | ✅ |

## Deviations and notes

- **'Like the Phase 4 homepage map':** the homepage map is Leaflet, not inline SVG. Only the partial pattern was followed; the SVG is read and inlined server-side.
- **Existing `RegionCentroids::islandGroup()`:** the phase file said no mapping existed; one does (first digit only, unused). It has no entry for '2', so Kepulauan Riau (21) maps to null. Left unchanged (out of scope); `ProvinceRegion` is the two-digit replacement for package G.
- **Papua codes:** BPS codes 91, 92, 94, 95, 96, 97. The Kemendagri variant 93 is treated as unknown.
- **Screen readers:** the SVG is `aria-hidden`; the visible list is the text equivalent. Chrome's `innerText` still includes the hidden SVG `<text>`, but they are `display: none` and not exposed.
- **Two transient 500s** during the first after-capture (`/institution/33` at 360 and 390) came from a DNS failure reaching the database host; the page answered 200 on retry and the full capture was repeated. No debug page was screenshotted.
