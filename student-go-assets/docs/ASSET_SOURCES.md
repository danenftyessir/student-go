# ASSET_SOURCES

Source, creator, license and local path for every third-party and original asset in this package. Generated from `assets-manifest.json` on 2026-10-30 (`sha256` and byte size per file live there).

## 0. Status you should read first

| Item | State |
|---|---|
| Original SVGs (brand, empty states, workflows, geographic, motifs) | **Created and verified** in the package. |
| Fonts (Plus Jakarta Sans, Source Serif 4) and OFL texts | **Included** as local WOFF2. |
| Icons (Lucide subset, sprite, ISC text) | **Included.** |
| Photographs | **60 selected from Unsplash and Pexels; license, creator and location read on each photo's own page; NOT downloaded yet.** The session that built this package had no linked computer and its network policy blocked the image hosts, so `public/assets/photography/*` is empty. Run `php scripts/fetch-photos.php` from the project root (section 1.5). The downloader was tested against a local stub server (redirects, fallback URL, error-page rejection, EXIF rotation, size cap), not against Unsplash or Pexels themselves. |
| Visual review of photographs | **Not done.** A photo's composition, crop and whether a person is identifiable can only be judged by looking at it. Tick the *Reviewed* box under each photo after you have. |
| Wikimedia Commons / Openverse | **Not evaluated.** Both were unreachable from the build session. No Commons file is listed here because none was opened and license-checked. |

## 1. Photography

60 photographs (19 Unsplash, 41 Pexels). The machine-readable version is `docs/photo-catalog.json`; the downloader reads it.

### 1.1 Rules applied

- Allowed: CC0, public domain, CC BY, or another permissive license that allows commercial use and modification. Refused: CC BY-NC, CC BY-ND, unknown license, watermarked.
- The license was read **on each photo's own page**, not inferred from a search result. This caught items that look free in search results but are not: several Unsplash photos are labelled *Unsplash+* (a paid tier) on their page and were dropped (section 1.4).
- Unsplash License and Pexels License are custom permissive licenses, not Creative Commons. They allow commercial use and modification and need no attribution. They are the *other permissive licenses* the brief allows; if your policy accepts only CC0 / CC BY / public domain, replace them with Commons files.
- Nobody pictured endorses Student Go. No quote, name, role or testimonial next to a photo of a person. Captions name the place and the photographer only.
- **People column** comes from the source page text only (`none`, `one`, `adults`, `minors`, `unknown`). **Faces and identifiability have not been checked by eye.** Photos marked `minors` show school pupils or a child; keep them out of prominent placements and never use them as testimonials or ads.
- Use is selective: homepage band, About, editorial stories. Never as a hero on an app page, never as a placeholder for a missing project image. The `library/` folders are a pool to choose from, not a list of images to display.
- No hotlinks: every `<img>` points at `/assets/photography/...webp`. Longest side 1800 px, WebP quality 80, EXIF stripped, orientation applied.
- Courtesy credit (not required by either license) is still printed in `<figcaption>` as *Foto: {creator} / {source}*. `credits.json`, written by the downloader, has it ready for Blade.

### 1.2 Licenses as read

- **Unsplash License** — Commercial and non-commercial use; no permission or attribution required (credit appreciated); may not compile images into a competing service. Source: https://unsplash.com/license
- **Pexels License** — Free for commercial use, attribution not required; do not depict identifiable people in a bad light; do not sell unmodified copies; do not imply endorsement by people or brands shown; no redistribution on other stock platforms; not for use as a trademark. Source: https://www.pexels.com/license/

### 1.3 Selected photographs

Source URL is the photo's own page. Attribution: none required; the courtesy line shown is printed anyway. Status of every row: *license verified on source page; not yet downloaded; visual review pending*. Reviewed by eye: ☐ for every row until you have looked.

#### Homepage band (landscapes and aerials) — 6

| Filename | Component (suggested use) | Source | Source URL | Creator | License | Attribution | Location (per source page) | People | Local path | ☐ Reviewed |
|---|---|---|---|---|---|---|---|---|---|---|
| `purwojati-banyumas-rice-fields.webp` | Homepage band — Landscape; no people expected (confirm visually). | Unsplash | <https://unsplash.com/photos/WClKJ2cW99Y> | [Mufid Majnun](https://unsplash.com/@mufidpwt) | [Unsplash License](https://unsplash.com/license) | Foto: Mufid Majnun / Unsplash (courtesy) | Purwojati Village, Banyumas, Indonesia | unknown | `public/assets/photography/homepage/purwojati-banyumas-rice-fields.webp` | ☐ |
| `flooded-rice-paddies-farmers-aerial.webp` | Homepage band — Drone view; published 2026-05-12. | Unsplash | <https://unsplash.com/photos/farmers-working-in-flooded-rice-paddies-surrounded-by-palm-trees-azdSLacrFMA> | [Jan Hildebrand](https://unsplash.com/@hildi_) | [Unsplash License](https://unsplash.com/license) | Foto: Jan Hildebrand / Unsplash (courtesy) | Indonesia (page lists 'Indonesien') | unknown | `public/assets/photography/homepage/flooded-rice-paddies-farmers-aerial.webp` | ☐ |
| `bengkulu-aerial-rice-terraces-sunset.webp` | Homepage band; Sumatra | Pexels | <https://www.pexels.com/photo/aerial-view-of-lush-rice-terraces-in-bengkulu-39072482/> | [Rahmad Himawan](https://www.pexels.com/@rahmadhimawan/) | [Pexels License](https://www.pexels.com/license/) | Foto: Rahmad Himawan / Pexels (courtesy) | Bengkulu, Indonesia | none | `public/assets/photography/homepage/bengkulu-aerial-rice-terraces-sunset.webp` | ☐ |
| `maluku-islands-aerial.webp` | Homepage band; Maluku coverage | Pexels | <https://www.pexels.com/photo/aerial-view-of-maluku-islands-22305144/> | [Danny Hollander](https://www.pexels.com/@danny-hollander-532626220/) | [Pexels License](https://www.pexels.com/license/) | Foto: Danny Hollander / Pexels (courtesy) | Maluku Islands, Indonesia | none | `public/assets/photography/homepage/maluku-islands-aerial.webp` | ☐ |
| `cirebon-aerial-village-red-roofs.webp` | Homepage band; village + ricefield | Unsplash | <https://unsplash.com/photos/an-aerial-view-of-a-village-with-red-roofs-6MCcHDmGra4> | [Mujib](https://unsplash.com/@mujibanget) | [Unsplash License](https://unsplash.com/license) | Foto: Mujib / Unsplash (courtesy) | Ciledug, Cirebon, West Java, Indonesia | none | `public/assets/photography/homepage/cirebon-aerial-village-red-roofs.webp` | ☐ |
| `kutai-kartanegara-aerial-town-and-river.webp` | Homepage band; Kalimantan | Unsplash | <https://unsplash.com/photos/aerial-view-of-a-town-muddy-river-and-lush-forest-2KMngfLtLKY> | [Fiqih Alfarish](https://unsplash.com/@fiqihalfarish) | [Unsplash License](https://unsplash.com/license) | Foto: Fiqih Alfarish / Unsplash (courtesy) | Loa Kulu Kota, Kutai Kartanegara, East Kalimantan, Indonesia | none | `public/assets/photography/homepage/kutai-kartanegara-aerial-town-and-river.webp` | ☐ |

#### About page (editorial figures) — 4

| Filename | Component (suggested use) | Source | Source URL | Creator | License | Attribution | Location (per source page) | People | Local path | ☐ Reviewed |
|---|---|---|---|---|---|---|---|---|---|---|
| `jatiluwih-bali-rice-planting.webp` | About: editorial figure — Whether the person is identifiable cannot be determined from the page text; check the image. | Unsplash | <https://unsplash.com/photos/person-planting-rice-on-field-during-daytime-I-8e7wx2hao> | [Shayan Ghiasvand](https://unsplash.com/@shayangh96) | [Unsplash License](https://unsplash.com/license) | Foto: Shayan Ghiasvand / Unsplash (courtesy) | Jatiluwih Rice Terraces, Tabanan Regency, Bali, Indonesia | unknown | `public/assets/photography/about/jatiluwih-bali-rice-planting.webp` | ☐ |
| `aceh-rice-field-farmer.webp` | About: editorial figure — Whether the person is identifiable cannot be determined from the page text; check the image. | Unsplash | <https://unsplash.com/photos/person-farming-on-rice-field-a7n65pmnJ4Q> | [Sandy Zebua](https://unsplash.com/@sandyzebua) | [Unsplash License](https://unsplash.com/license) | Foto: Sandy Zebua / Unsplash (courtesy) | Aceh, Indonesia | unknown | `public/assets/photography/about/aceh-rice-field-farmer.webp` | ☐ |
| `pati-farmers-breakfast-in-field.webp` | About: editorial figure — Caption on source page: farmers having breakfast in the fields while planting rice. Page heading and caption differ slightly on who is shown; check the image. | Unsplash | <https://unsplash.com/photos/a-group-of-women-sitting-in-a-field-W8_OxCsSuHg> | [Redicul Pict](https://unsplash.com/@rdcl) | [Unsplash License](https://unsplash.com/license) | Foto: Redicul Pict / Unsplash (courtesy) | Pohijo, Pati Regency, Central Java, Indonesia | unknown | `public/assets/photography/about/pati-farmers-breakfast-in-field.webp` | ☐ |
| `tegalwaru-west-java-aerial-rice-fields.webp` | About: editorial figure — Source image 5496x7200 (portrait); crop intentionally. Creator profile URL is inferred from the download filename (tomfisk); confirm on the page. | Pexels | <https://www.pexels.com/photo/2015589> | [Tom Fisk](https://www.pexels.com/@tomfisk/) | [Pexels License](https://www.pexels.com/license/) | Foto: Tom Fisk / Pexels (courtesy) | Tegalwaru, Jawa Barat, Indonesia | unknown | `public/assets/photography/about/tegalwaru-west-java-aerial-rice-fields.webp` | ☐ |

#### Library: pertanian (farming) — 13

| Filename | Component (suggested use) | Source | Source URL | Creator | License | Attribution | Location (per source page) | People | Local path | ☐ Reviewed |
|---|---|---|---|---|---|---|---|---|---|---|
| `central-java-farmer-terraced-fields.webp` | About / story | Pexels | <https://www.pexels.com/photo/farmer-working-in-central-java-agricultural-fields-34033925/> | [Ruyat Supriazi](https://www.pexels.com/@ruyat-supriazi-2154700906/) | [Pexels License](https://www.pexels.com/license/) | Foto: Ruyat Supriazi / Pexels (courtesy) | Central Java, Indonesia | one | `public/assets/photography/library/pertanian/central-java-farmer-terraced-fields.webp` | ☐ |
| `west-java-elderly-farmers-harvest-rice.webp` | About / story | Pexels | <https://www.pexels.com/photo/farmers-harvesting-rice-in-west-java-field-33691398/> | [Ruyat Supriazi](https://www.pexels.com/@ruyat-supriazi-2154700906/) | [Pexels License](https://www.pexels.com/license/) | Foto: Ruyat Supriazi / Pexels (courtesy) | West Java, Indonesia | adults | `public/assets/photography/library/pertanian/west-java-elderly-farmers-harvest-rice.webp` | ☐ |
| `yogyakarta-farmer-planting-rice.webp` | About / story | Pexels | <https://www.pexels.com/photo/indonesian-farmer-planting-rice-in-jogja-field-35544010/> | [Suryo Patriandito](https://www.pexels.com/@suryo-patriandito-2158032438/) | [Pexels License](https://www.pexels.com/license/) | Foto: Suryo Patriandito / Pexels (courtesy) | Yogyakarta, Indonesia | one | `public/assets/photography/library/pertanian/yogyakarta-farmer-planting-rice.webp` | ☐ |
| `bali-aerial-tractor-plowing-rice-field.webp` | Homepage band candidate (aerial; person is small) | Pexels | <https://www.pexels.com/photo/aerial-view-of-farmer-plowing-rice-field-in-bali-35343779/> | [Tom Fisk](https://www.pexels.com/@tomfisk/) | [Pexels License](https://www.pexels.com/license/) | Foto: Tom Fisk / Pexels (courtesy) | Bali, Indonesia | one | `public/assets/photography/library/pertanian/bali-aerial-tractor-plowing-rice-field.webp` | ☐ |
| `bali-farmers-picking-chili.webp` | Story | Pexels | <https://www.pexels.com/photo/farmers-picking-chili-in-a-farm-12470180/> | [Danang DKW](https://www.pexels.com/@danangdkw/) | [Pexels License](https://www.pexels.com/license/) | Foto: Danang DKW / Pexels (courtesy) | Bali, Indonesia | adults | `public/assets/photography/library/pertanian/bali-farmers-picking-chili.webp` | ☐ |
| `tomohon-cabbage-field-farmers.webp` | Story; Sulawesi coverage | Pexels | <https://www.pexels.com/photo/woman-working-on-rural-field-16560054/> | [Ronny Buol](https://www.pexels.com/@ronnybuol/) | [Pexels License](https://www.pexels.com/license/) | Foto: Ronny Buol / Pexels (courtesy) | Tomohon, North Sulawesi, Indonesia | adults | `public/assets/photography/library/pertanian/tomohon-cabbage-field-farmers.webp` | ☐ |
| `tangerang-farmers-packing-vegetables.webp` | Story | Pexels | <https://www.pexels.com/photo/farmers-working-on-field-26690966/> | [Deden R](https://www.pexels.com/@ddnrs/) | [Pexels License](https://www.pexels.com/license/) | Foto: Deden R / Pexels (courtesy) | Tangerang, Banten, Indonesia | adults | `public/assets/photography/library/pertanian/tangerang-farmers-packing-vegetables.webp` | ☐ |
| `east-java-woman-harvesting-tobacco.webp` | Story | Pexels | <https://www.pexels.com/photo/traditional-farming-in-east-java-with-tobacco-plants-30733459/> | [setengah lima sore](https://www.pexels.com/@setengah-lima-sore-1061582751/) | [Pexels License](https://www.pexels.com/license/) | Foto: setengah lima sore / Pexels (courtesy) | East Java, Indonesia | one | `public/assets/photography/library/pertanian/east-java-woman-harvesting-tobacco.webp` | ☐ |
| `west-java-drone-cropland-near-forest.webp` | Homepage band candidate — Page description calls the crop a banana plantation; the title says paddy. | Pexels | <https://www.pexels.com/photo/groomed-paddy-plantation-near-tropical-forest-in-sunlight-6135322/> | [Tom Fisk](https://www.pexels.com/@tomfisk/) | [Pexels License](https://www.pexels.com/license/) | Foto: Tom Fisk / Pexels (courtesy) | West Java, Indonesia | none | `public/assets/photography/library/pertanian/west-java-drone-cropland-near-forest.webp` | ☐ |
| `bali-rice-terraces-lone-farmer.webp` | Tall crop; story | Pexels | <https://www.pexels.com/photo/serene-rice-terraces-of-bali-indonesia-29989220/> | [Stijn Dijkstra](https://www.pexels.com/@stijn-dijkstra-1306815/) | [Pexels License](https://www.pexels.com/license/) | Foto: Stijn Dijkstra / Pexels (courtesy) | Bali, Indonesia | one | `public/assets/photography/library/pertanian/bali-rice-terraces-lone-farmer.webp` | ☐ |
| `central-java-farmer-casting-net-in-paddy.webp` | Story | Pexels | <https://www.pexels.com/photo/indonesian-farmer-casting-fish-net-in-rice-field-34514243/> | [Found GHI](https://www.pexels.com/@found-ghi-2156772983/) | [Pexels License](https://www.pexels.com/license/) | Foto: Found GHI / Pexels (courtesy) | Central Java, Indonesia | one | `public/assets/photography/library/pertanian/central-java-farmer-casting-net-in-paddy.webp` | ☐ |
| `sidemen-bali-rice-terraces-at-dawn.webp` | Story | Unsplash | <https://unsplash.com/photos/person-walking-through-green-rice-terraces-7hww7t6NLcg> | [Geio Tischler](https://unsplash.com/@oww) | [Unsplash License](https://unsplash.com/license) | Foto: Geio Tischler / Unsplash (courtesy) | Sidemen, Bali, Indonesia (page: 'Mount Agung in the sunrise') | one | `public/assets/photography/library/pertanian/sidemen-bali-rice-terraces-at-dawn.webp` | ☐ |
| `bali-woman-working-in-rice-field.webp` | Story — Page tags also mention a man; count unconfirmed. | Unsplash | <https://unsplash.com/photos/a-woman-is-working-in-a-rice-field-Wft59e9QNhc> | [Deny Napitupulu](https://unsplash.com/@denynapitupulu) | [Unsplash License](https://unsplash.com/license) | Foto: Deny Napitupulu / Unsplash (courtesy) | Bali, Indonesia | one | `public/assets/photography/library/pertanian/bali-woman-working-in-rice-field.webp` | ☐ |

#### Library: pesisir dan maritim (coast, fishing, mangrove) — 12

| Filename | Component (suggested use) | Source | Source URL | Creator | License | Attribution | Location (per source page) | People | Local path | ☐ Reviewed |
|---|---|---|---|---|---|---|---|---|---|---|
| `ntb-mangrove-reforestation-volunteer.webp` | Story: SDG 13/14 projects — Page keyword tags do not match the photo; ignore them. | Pexels | <https://www.pexels.com/photo/man-volunteering-in-mangrove-reforestation-35623102/> | [ADollarForCoral](https://www.pexels.com/@adollarforcoral-2158729554/) | [Pexels License](https://www.pexels.com/license/) | Foto: ADollarForCoral / Pexels (courtesy) | Nusa Tenggara Barat, Indonesia | one | `public/assets/photography/library/pesisir/ntb-mangrove-reforestation-volunteer.webp` | ☐ |
| `jawa-barat-planting-mangroves-adult-and-child.webp` | Story (check crop; shows a child) — A child is pictured. Do not use as a testimonial or ad; keep out of prominent placements. | Pexels | <https://www.pexels.com/photo/father-and-son-planting-mangroves-in-indonesia-35861397/> | [Noel Snpr](https://www.pexels.com/@noel-snpr-2148726663/) | [Pexels License](https://www.pexels.com/license/) | Foto: Noel Snpr / Pexels (courtesy) | Jawa Barat, Indonesia | minors | `public/assets/photography/library/pesisir/jawa-barat-planting-mangroves-adult-and-child.webp` | ☐ |
| `west-java-fisherman-mending-nets.webp` | Story | Pexels | <https://www.pexels.com/photo/indonesian-fisherman-repairing-fishing-nets-at-harbor-37805453/> | [kevin yung](https://www.pexels.com/@kevin-yung-2152346613/) | [Pexels License](https://www.pexels.com/license/) | Foto: kevin yung / Pexels (courtesy) | West Java, Indonesia | one | `public/assets/photography/library/pesisir/west-java-fisherman-mending-nets.webp` | ☐ |
| `phinisi-boats-in-harbor.webp` | Story; Sulawesi/Nusantara maritime | Pexels | <https://www.pexels.com/photo/traditional-phinisi-boats-in-harbor-with-fisherman-36341641/> | [Jeffry Surianto](https://www.pexels.com/@andromeda99/) | [Pexels License](https://www.pexels.com/license/) | Foto: Jeffry Surianto / Pexels (courtesy) | Indonesia (no finer location on page) | one | `public/assets/photography/library/pesisir/phinisi-boats-in-harbor.webp` | ☐ |
| `bangka-belitung-fisherman-casting-net-sunrise.webp` | Tall crop; story | Pexels | <https://www.pexels.com/photo/fisherman-at-sunrise-in-bangka-belitung-islands-37606547/> | [Faris Munandar](https://www.pexels.com/@fmphotograph/) | [Pexels License](https://www.pexels.com/license/) | Foto: Faris Munandar / Pexels (courtesy) | Bangka Belitung Islands, Indonesia | one | `public/assets/photography/library/pesisir/bangka-belitung-fisherman-casting-net-sunrise.webp` | ☐ |
| `west-sumatra-fisherman-net-from-canoe.webp` | Story | Pexels | <https://www.pexels.com/photo/traditional-fishing-in-west-sumatra-indonesia-37891877/> | [sumarno sumarno](https://www.pexels.com/@sumarno-sumarno-2161431466/) | [Pexels License](https://www.pexels.com/license/) | Foto: sumarno sumarno / Pexels (courtesy) | West Sumatra, Indonesia | one | `public/assets/photography/library/pesisir/west-sumatra-fisherman-net-from-canoe.webp` | ☐ |
| `banda-neira-woman-drying-fish.webp` | Story; Maluku | Pexels | <https://www.pexels.com/photo/traditional-fish-drying-in-banda-neira-indonesia-31949631/> | [Zainal Lakembe](https://www.pexels.com/@zainal-lakembe-2149585068/) | [Pexels License](https://www.pexels.com/license/) | Foto: Zainal Lakembe / Pexels (courtesy) | Banda Neira, Maluku, Indonesia | one | `public/assets/photography/library/pesisir/banda-neira-woman-drying-fish.webp` | ☐ |
| `north-maluku-boats-on-beach-aerial.webp` | Story; Maluku | Pexels | <https://www.pexels.com/photo/aerial-view-of-boats-on-tropical-beach-in-indonesia-38463229/> | [Rizky Arief Imanullah](https://www.pexels.com/@r12kyarief/) | [Pexels License](https://www.pexels.com/license/) | Foto: Rizky Arief Imanullah / Pexels (courtesy) | North Maluku, Indonesia | none | `public/assets/photography/library/pesisir/north-maluku-boats-on-beach-aerial.webp` | ☐ |
| `gili-gede-mangroves-on-shore.webp` | Story: mangrove / SDG 14 | Unsplash | <https://unsplash.com/photos/mangrove-trees-on-sandy-bay-shore-Sacec1fSDB4> | [Rowan Heuvel](https://unsplash.com/@insolitus) | [Unsplash License](https://unsplash.com/license) | Foto: Rowan Heuvel / Unsplash (courtesy) | Gili Gede, West Nusa Tenggara, Indonesia | none | `public/assets/photography/library/pesisir/gili-gede-mangroves-on-shore.webp` | ☐ |
| `batam-beach-litter-in-tree-roots.webp` | Story: waste / SDG 12-14 | Unsplash | <https://unsplash.com/photos/litter-is-tangled-in-the-roots-of-a-tree-umLdkZrAqXQ> | [Carl Tronders](https://unsplash.com/@allvar) | [Unsplash License](https://unsplash.com/license) | Foto: Carl Tronders / Unsplash (courtesy) | Viovio Beach, Batam, Riau Islands, Indonesia | none | `public/assets/photography/library/pesisir/batam-beach-litter-in-tree-roots.webp` | ☐ |
| `bontang-wooden-dock-and-mangroves.webp` | Story; Kalimantan | Unsplash | <https://unsplash.com/photos/brown-wooden-dock-on-lake-during-daytime-bE5OCKdC9Rw> | [Hanif Ramadhani](https://unsplash.com/@haniframadhani) | [Unsplash License](https://unsplash.com/license) | Foto: Hanif Ramadhani / Unsplash (courtesy) | Bontang, East Kalimantan, Indonesia | none | `public/assets/photography/library/pesisir/bontang-wooden-dock-and-mangroves.webp` | ☐ |
| `komodo-boats-on-water.webp` | Story; NTT | Unsplash | <https://unsplash.com/photos/a-group-of-boats-floating-on-top-of-a-body-of-water-cT0nqZOlAME> | [Dhiemas Afif Febriyan](https://unsplash.com/@afebriyan) | [Unsplash License](https://unsplash.com/license) | Foto: Dhiemas Afif Febriyan / Unsplash (courtesy) | Komodo Island, West Manggarai, East Nusa Tenggara, Indonesia | none | `public/assets/photography/library/pesisir/komodo-boats-on-water.webp` | ☐ |

#### Library: pasar dan UMKM (markets) — 9

| Filename | Component (suggested use) | Source | Source URL | Creator | License | Attribution | Location (per source page) | People | Local path | ☐ Reviewed |
|---|---|---|---|---|---|---|---|---|---|---|
| `jakarta-fish-market-sorting.webp` | Story: UMKM | Pexels | <https://www.pexels.com/photo/fish-market-scene-in-jakarta-indonesia-38252858/> | [Andri Wijayanto](https://www.pexels.com/@andri-wijayanto-2162120540/) | [Pexels License](https://www.pexels.com/license/) | Foto: Andri Wijayanto / Pexels (courtesy) | Jakarta, Indonesia | one | `public/assets/photography/library/pasar/jakarta-fish-market-sorting.webp` | ☐ |
| `ntb-fish-market.webp` | Story: UMKM | Pexels | <https://www.pexels.com/photo/bustling-fish-market-in-west-nusa-tenggara-37024065/> | [Fahry Samalewa](https://www.pexels.com/@fahry-samalewa-2389454/) | [Pexels License](https://www.pexels.com/license/) | Foto: Fahry Samalewa / Pexels (courtesy) | West Nusa Tenggara, Indonesia | unknown | `public/assets/photography/library/pasar/ntb-fish-market.webp` | ☐ |
| `flores-local-market.webp` | Story: UMKM; Nusa Tenggara | Pexels | <https://www.pexels.com/photo/vibrant-local-market-scene-in-flores-indonesia-32054310/> | [Smaart](https://www.pexels.com/@smaart-343619394/) | [Pexels License](https://www.pexels.com/license/) | Foto: Smaart / Pexels (courtesy) | Flores, Indonesia | unknown | `public/assets/photography/library/pasar/flores-local-market.webp` | ☐ |
| `banjarmasin-floating-market.webp` | Story: UMKM; Kalimantan | Pexels | <https://www.pexels.com/photo/traditional-floating-market-in-banjarmasin-38289584/> | [muhammad djasmail](https://www.pexels.com/@muhammad-djasmail-2159583776/) | [Pexels License](https://www.pexels.com/license/) | Foto: muhammad djasmail / Pexels (courtesy) | Banjarmasin, South Kalimantan, Indonesia | adults | `public/assets/photography/library/pasar/banjarmasin-floating-market.webp` | ☐ |
| `west-java-traditional-market.webp` | Story: UMKM | Pexels | <https://www.pexels.com/photo/vibrant-traditional-market-in-west-java-indonesia-28572618/> | [TaZe](https://www.pexels.com/@taze-1342414269/) | [Pexels License](https://www.pexels.com/license/) | Foto: TaZe / Pexels (courtesy) | West Java, Indonesia | unknown | `public/assets/photography/library/pasar/west-java-traditional-market.webp` | ☐ |
| `pontianak-women-selling-at-market.webp` | Story: UMKM; Kalimantan | Pexels | <https://www.pexels.com/photo/women-selling-food-at-the-market-27109736/> | [Rizky Rianto](https://www.pexels.com/@rizky-rianto-1515216241/) | [Pexels License](https://www.pexels.com/license/) | Foto: Rizky Rianto / Pexels (courtesy) | Pontianak, Kalimantan Barat, Indonesia | adults | `public/assets/photography/library/pasar/pontianak-women-selling-at-market.webp` | ☐ |
| `banten-elderly-women-at-market.webp` | Story | Pexels | <https://www.pexels.com/photo/elderly-women-sitting-on-ground-together-4454063/> | [Tom Fisk](https://www.pexels.com/@tomfisk/) | [Pexels License](https://www.pexels.com/license/) | Foto: Tom Fisk / Pexels (courtesy) | Banten, Indonesia | adults | `public/assets/photography/library/pasar/banten-elderly-women-at-market.webp` | ☐ |
| `banten-street-market-shoppers.webp` | Tall crop; story | Pexels | <https://www.pexels.com/photo/people-with-shopping-bags-walking-around-a-street-market-4454060/> | [Tom Fisk](https://www.pexels.com/@tomfisk/) | [Pexels License](https://www.pexels.com/license/) | Foto: Tom Fisk / Pexels (courtesy) | Banten, Indonesia | adults | `public/assets/photography/library/pasar/banten-street-market-shoppers.webp` | ☐ |
| `surakarta-market-shoppers.webp` | Story: UMKM | Unsplash | <https://unsplash.com/photos/a-group-of-people-shopping-in-a-market-8vF5Kikv3RI> | [Prabu Panji](https://unsplash.com/@prabuuuuu) | [Unsplash License](https://unsplash.com/license) | Foto: Prabu Panji / Unsplash (courtesy) | Surakarta, Central Java, Indonesia | adults | `public/assets/photography/library/pasar/surakarta-market-shoppers.webp` | ☐ |

#### Library: pendidikan dan kampus — 8

| Filename | Component (suggested use) | Source | Source URL | Creator | License | Attribution | Location (per source page) | People | Local path | ☐ Reviewed |
|---|---|---|---|---|---|---|---|---|---|---|
| `pandeglang-classroom-teacher-and-students.webp` | Story: KKN teaching — School pupils are pictured. Do not imply they endorse Student Go. | Pexels | <https://www.pexels.com/photo/indonesian-classroom-scene-with-teacher-and-students-35548841/> | [el jusuf](https://www.pexels.com/@pamanjoe/) | [Pexels License](https://www.pexels.com/license/) | Foto: el jusuf / Pexels (courtesy) | Pandeglang, Banten, Indonesia | minors | `public/assets/photography/library/pendidikan/pandeglang-classroom-teacher-and-students.webp` | ☐ |
| `pandeglang-classroom-lesson.webp` | Story: KKN teaching — School pupils are pictured. Do not imply they endorse Student Go. | Pexels | <https://www.pexels.com/photo/indonesian-classroom-with-students-and-teacher-35548842/> | [el jusuf](https://www.pexels.com/@pamanjoe/) | [Pexels License](https://www.pexels.com/license/) | Foto: el jusuf / Pexels (courtesy) | Pandeglang, Banten, Indonesia | minors | `public/assets/photography/library/pendidikan/pandeglang-classroom-lesson.webp` | ☐ |
| `pandeglang-students-learning.webp` | Story: KKN teaching — School pupils are pictured. Do not imply they endorse Student Go. | Pexels | <https://www.pexels.com/photo/students-learning-in-a-classroom-in-indonesia-35548840/> | [el jusuf](https://www.pexels.com/@pamanjoe/) | [Pexels License](https://www.pexels.com/license/) | Foto: el jusuf / Pexels (courtesy) | Pandeglang, Banten, Indonesia | minors | `public/assets/photography/library/pendidikan/pandeglang-students-learning.webp` | ☐ |
| `yogyakarta-graduate-in-university-corridor.webp` | Story: student profile (generic; do not caption as a user) — Photographer asks for credit on the page; the courtesy line below already gives it. Identifiable person. | Pexels | <https://www.pexels.com/photo/southeast-asian-graduate-smiling-in-corridor-33767807/> | [Yazid N](https://www.pexels.com/@mzynasx/) | [Pexels License](https://www.pexels.com/license/) | Foto: Yazid N / Pexels (courtesy) | Yogyakarta, Indonesia | one | `public/assets/photography/library/pendidikan/yogyakarta-graduate-in-university-corridor.webp` | ☐ |
| `high-school-students-in-classroom.webp` | Story — School pupils are pictured. Do not imply they endorse Student Go. | Pexels | <https://www.pexels.com/photo/indonesian-high-school-students-in-classroom-35865718/> | [Airlangga Jati](https://www.pexels.com/@airlangga/) | [Pexels License](https://www.pexels.com/license/) | Foto: Airlangga Jati / Pexels (courtesy) | Indonesia (no finer location on page) | minors | `public/assets/photography/library/pendidikan/high-school-students-in-classroom.webp` | ☐ |
| `jawa-barat-student-with-tablet-on-campus.webp` | Story: student (generic; do not caption as a user) — Identifiable person. | Pexels | <https://www.pexels.com/photo/young-adult-holding-tablet-in-campus-setting-outdoors-40551009/> | [Irma Sjachlan](https://www.pexels.com/@irma-sjachlan-2157444897/) | [Pexels License](https://www.pexels.com/license/) | Foto: Irma Sjachlan / Pexels (courtesy) | Jawa Barat, Indonesia | one | `public/assets/photography/library/pendidikan/jawa-barat-student-with-tablet-on-campus.webp` | ☐ |
| `universitas-indonesia-graduation-flags.webp` | Story: campus (generic; do not caption as a user) — Identifiable person. | Unsplash | <https://unsplash.com/photos/graduation-ceremony-with-flags-and-student-in-cap-kXCrTG_Ap_Q> | [Reyhan Aviseno](https://unsplash.com/@aavisenoo) | [Unsplash License](https://unsplash.com/license) | Foto: Reyhan Aviseno / Unsplash (courtesy) | Universitas Indonesia, Depok, West Java, Indonesia | one | `public/assets/photography/library/pendidikan/universitas-indonesia-graduation-flags.webp` | ☐ |
| `umy-graduates-in-red-gowns.webp` | Story: campus (crowd) | Unsplash | <https://unsplash.com/photos/a-large-group-of-people-in-red-graduation-gowns-Zfee_GUBs_k> | [Mardanafin](https://unsplash.com/@mardanafin) | [Unsplash License](https://unsplash.com/license) | Foto: Mardanafin / Unsplash (courtesy) | Universitas Muhammadiyah Yogyakarta, Bantul, DI Yogyakarta, Indonesia | adults | `public/assets/photography/library/pendidikan/umy-graduates-in-red-gowns.webp` | ☐ |

#### Library: komunitas (community action) — 1

| Filename | Component (suggested use) | Source | Source URL | Creator | License | Attribution | Location (per source page) | People | Local path | ☐ Reviewed |
|---|---|---|---|---|---|---|---|---|---|---|
| `jawa-barat-community-park-cleanup.webp` | Story: kerja bakti / community action | Pexels | <https://www.pexels.com/photo/community-cleanup-effort-in-jawa-barat-37595624/> | [May umaran](https://www.pexels.com/@may-umaran-2157785843/) | [Pexels License](https://www.pexels.com/license/) | Foto: May umaran / Pexels (courtesy) | Jawa Barat, Indonesia | unknown | `public/assets/photography/library/komunitas/jawa-barat-community-park-cleanup.webp` | ☐ |

#### Library: desa dan wilayah (villages, regions) — 7

| Filename | Component (suggested use) | Source | Source URL | Creator | License | Attribution | Location (per source page) | People | Local path | ☐ Reviewed |
|---|---|---|---|---|---|---|---|---|---|---|
| `waigeo-barat-coastal-huts-aerial.webp` | Story; Papua | Pexels | <https://www.pexels.com/photo/wooden-sheds-on-tropical-sea-coast-19758798/> | [Melson Dreams](https://www.pexels.com/@melson-dreams-349386037/) | [Pexels License](https://www.pexels.com/license/) | Foto: Melson Dreams / Pexels (courtesy) | Waigeo Barat Kepulauan, West Papua, Indonesia | none | `public/assets/photography/library/desa/waigeo-barat-coastal-huts-aerial.webp` | ☐ |
| `papua-harbor-boats-and-locals.webp` | Story; Papua | Pexels | <https://www.pexels.com/photo/bustling-harbor-scene-in-papua-indonesia-32344943/> | [ria via](https://www.pexels.com/@ria-via-2152711206/) | [Pexels License](https://www.pexels.com/license/) | Foto: ria via / Pexels (courtesy) | Papua, Indonesia | unknown | `public/assets/photography/library/desa/papua-harbor-boats-and-locals.webp` | ☐ |
| `papua-island-landscape.webp` | Story; Papua | Pexels | <https://www.pexels.com/photo/serene-island-scenery-in-papua-indonesia-32507843/> | [Adiardi Zulfansyah](https://www.pexels.com/@adiardizulfansyah/) | [Pexels License](https://www.pexels.com/license/) | Foto: Adiardi Zulfansyah / Pexels (courtesy) | Papua, Indonesia | none | `public/assets/photography/library/desa/papua-island-landscape.webp` | ☐ |
| `southeast-sulawesi-stilt-houses-by-river.webp` | Tall crop; Sulawesi | Pexels | <https://www.pexels.com/photo/tranquil-waterfront-scene-in-south-east-sulawesi-30187266/> | [Albar Buton](https://www.pexels.com/@albar-buton-939228963/) | [Pexels License](https://www.pexels.com/license/) | Foto: Albar Buton / Pexels (courtesy) | South East Sulawesi, Indonesia | none | `public/assets/photography/library/desa/southeast-sulawesi-stilt-houses-by-river.webp` | ☐ |
| `penglipuran-village-path.webp` | Story: desa | Unsplash | <https://unsplash.com/photos/a-long-narrow-path-between-two-buildings-in-a-village-pziaKacFYkw> | [satria setiawan](https://unsplash.com/@satriaesetiawan) | [Unsplash License](https://unsplash.com/license) | Foto: satria setiawan / Unsplash (courtesy) | Desa Penglipuran, Bangli Regency, Bali, Indonesia | unknown | `public/assets/photography/library/desa/penglipuran-village-path.webp` | ☐ |
| `rural-road-house-and-mountain.webp` | Story: desa | Unsplash | <https://unsplash.com/photos/a-rural-road-with-a-house-and-a-mountain-in-the-background-4r1WzVLfYWI> | [Ahmad Hasan Hariri](https://unsplash.com/@ahasanhariri) | [Unsplash License](https://unsplash.com/license) | Foto: Ahmad Hasan Hariri / Unsplash (courtesy) | Indonesia (no finer location on page) | unknown | `public/assets/photography/library/desa/rural-road-house-and-mountain.webp` | ☐ |
| `kebumen-three-cyclists-across-field.webp` | Story: desa | Unsplash | <https://unsplash.com/photos/three-person-riding-bikes-on-green-grass-field-8o4W9LZv6eo> | [Dikaseva](https://unsplash.com/@dikaseva) | [Unsplash License](https://unsplash.com/license) | Foto: Dikaseva / Unsplash (courtesy) | Kebumen, Central Java, Indonesia | adults | `public/assets/photography/library/desa/kebumen-three-cyclists-across-field.webp` | ☐ |

### 1.4 Considered and not used

| Why | Items | Detail |
|---|---|---|
| Unsplash+ (paid license, not the free Unsplash License) | LBS73iBHivg and Y9FNcU3Llqw (creator Ahmed @mutecevvil), xO3Y90Pd5kI (Polina Kuzovkova); also listed as Unsplash+ on the Papua/Maluku result pages | The page states 'Unsplash+ License'. It needs a subscription, so it fails the license rule. |
| Credited to 'Getty Images' on Unsplash | _UPXYs5HUx4, HA9gM2AkyvQ, IG-B3AxMoe0, ak9lLbQmzSI, unaeRAszPeM and similar (seen in result lists only) | Items credited to Getty Images on Unsplash belong to its paid Unsplash+ tier. I did not open these pages; the credit alone was enough to leave them out. |
| Unsplash 'flagged' image path | eJZVVUut27g (Pasar Perumnas Klender, Jakarta) | Its image URL sits under images.unsplash.com/flagged/, a path Unsplash uses for content it has flagged. I could not tell why, so I left it out. |
| Outside Indonesia | 7hd0gZtdeNU (Kota Bharu, Malaysia) | Page location is Malaysia. |
| Indonesian location not confirmed on the page | Unsplash dcn4xqKLQTI, qFqa-hLAuRo (woven textiles), n26U2jY5mQE (chilis), CAtdHwUK3qc (National Education Day, children); Pexels 36596595 (alley cleanup) | License is fine, but the page names no Indonesian place. Add them once you can confirm where they were taken. |
| Close-ups of children with little context | Unsplash uaPaEM7MiQQ; Pexels 35861356 (boys in canoe); Papua cultural-parade portraits (many by pamanjoe, assomyron, andromeda99) | Identifiable minors, or portraits of people in ceremonial dress, are a poor fit for a product page and risk implying endorsement or exoticising. Left out on purpose. |
| Page not readable by automated tools | Pexels search pages for coffee, weaving, batik, Sulawesi village; Pexels photo 38737900 (Southwest Papua coast) | Pexels' robots.txt refused the fetch, so these were not read or used. Search them in a browser if you want more. |
| Not reachable from the build session | Wikimedia Commons, Openverse | Both were blocked from this environment. Nothing from them was evaluated. |

- **Real project photos** (brief section 4.6) cannot come from stock. They must come from institutions' own uploads, with consent recorded. `public/assets/photography/projects/` stays empty and the project-detail design omits the media region when there is no image (`components.css`, `.sg-media[hidden]`).
- Photos of people at work (farmers, fishers, vendors) were included because they show the places Student Go projects happen in; they are **not** Student Go users and must never be captioned as such.

### 1.5 Download and verify

```bash
php scripts/fetch-photos.php --dry-run          # list what would be fetched
php scripts/fetch-photos.php --only=homepage    # try one folder first (6 photos)
php scripts/fetch-photos.php                    # all 60
```

Works the same on Windows, macOS and Linux because it only needs PHP (already installed for Laravel). It needs a WebP encoder: GD with WebP support (default in current PHP builds), or the Imagick extension, or ImageMagick on the PATH; it says so if none is found. Originals go to `storage/app/asset-originals/` (add to `.gitignore`); the script refuses any response that is not a real image, so a blocked page can never be saved as a photo. Expect about 60 downloads of 0.5–1.5 MB and about 10–15 MB of WebP in `public/`. Delete the photos you do not want.

The primary URL is the CDN rendition at 2400 px; the fallback is the page's own download link. If both fail for a photo, open its Source URL in a browser, use the site's Download button, and save it as the `Local path` shown (convert to WebP first).

## 2. Fonts

| Filename | Component | Source | Source URL | Creator | License | Attribution | Local path |
|---|---|---|---|---|---|---|---|
| `OFL.txt` | License text | @fontsource-variable/plus-jakarta-sans (npm), from google/fonts | <https://github.com/tokotype/PlusJakartaSans> | The Plus Jakarta Sans Project Authors (Tokotype) | SIL Open Font License 1.1 (OFL.txt) | Keep `OFL.txt` beside the files; no on-page credit needed | `public/assets/fonts/plus-jakarta-sans/OFL.txt` |
| `PlusJakartaSans-latin-ext-wght.woff2` | UI + headings, accented Latin (loaded on demand) | @fontsource-variable/plus-jakarta-sans (npm), from google/fonts | <https://github.com/tokotype/PlusJakartaSans> | The Plus Jakarta Sans Project Authors (Tokotype) | SIL Open Font License 1.1 (OFL.txt) | Keep `OFL.txt` beside the files; no on-page credit needed | `public/assets/fonts/plus-jakarta-sans/PlusJakartaSans-latin-ext-wght.woff2` |
| `PlusJakartaSans-latin-wght.woff2` | UI + headings, Latin | @fontsource-variable/plus-jakarta-sans (npm), from google/fonts | <https://github.com/tokotype/PlusJakartaSans> | The Plus Jakarta Sans Project Authors (Tokotype) | SIL Open Font License 1.1 (OFL.txt) | Keep `OFL.txt` beside the files; no on-page credit needed | `public/assets/fonts/plus-jakarta-sans/PlusJakartaSans-latin-wght.woff2` |
| `OFL.txt` | License text | @fontsource-variable/source-serif-4 (npm), from google/fonts | <https://fonts.google.com/specimen/Source+Serif+4> | Adobe Systems Incorporated (Source Serif 4) | SIL Open Font License 1.1 (OFL.txt) | Keep `OFL.txt` beside the files; no on-page credit needed | `public/assets/fonts/source-serif-4/OFL.txt` |
| `SourceSerif4-latin-wght.woff2` | Editorial reading text only | @fontsource-variable/source-serif-4 (npm), from google/fonts | <https://fonts.google.com/specimen/Source+Serif+4> | Adobe Systems Incorporated (Source Serif 4) | SIL Open Font License 1.1 (OFL.txt) | Keep `OFL.txt` beside the files; no on-page credit needed | `public/assets/fonts/source-serif-4/SourceSerif4-latin-wght.woff2` |

**How the files were obtained.** `fonts.googleapis.com` and `fonts.gstatic.com` were blocked from the build session, so the WOFF2 files come from the Fontsource npm packages `@fontsource-variable/plus-jakarta-sans@5.3.0` and `@fontsource-variable/source-serif-4@5.3.1`, which redistribute the Google Fonts releases under the same OFL 1.1. Nothing is loaded from a CDN at runtime. If you require byte-identical files from fonts.google.com, download the two families there and replace the WOFF2s; the CSS needs no change as long as the file names are kept.

Copyright lines read from the font name tables: Plus Jakarta Sans — "Copyright 2020 The Plus Jakarta Sans Project Authors (https://github.com/tokotype/PlusJakartaSans)"; Source Serif 4 — "© 2014 – 2021 Adobe Systems Incorporated … Reserved Font Name 'Source'". The OFL permits bundling and self-hosting; the Reserved Font Name clause applies only if you modify and rename the font, which this package does not do.

Weights declared in CSS: 400, 500, 600, 700. IBM Plex Sans was evaluated and **not shipped** (decision record in `docs/DESIGN_SYSTEM.md`).

## 3. Icons

| Item | Value |
|---|---|
| Family | Lucide, lucide-static@1.64.0 (npm) |
| Source URL | <https://lucide.dev/> |
| Creator | Lucide Icons and Contributors |
| License | ISC (LICENSE-lucide.txt; portions derived from Feather, MIT, included in that file) |
| Attribution | Keep `LICENSE-lucide.txt` with the icons; no on-page credit needed |
| Local path | `public/assets/icons/ui/` (71 SVGs, `sprite.svg`, `icons.json`, `LICENSE-lucide.txt`) |
| Changes made | 71 icons, stroke-width normalised from 2 to 1.5 and attributes simplified. Sprite and icons.json are build outputs. |

One family only, 1.5 px stroke, drawn on 24 px and used at 16 / 20 / 24 px. No emoji. No second icon library (the audit's Boxicons and Font Awesome are removed, see `docs/INTEGRATION.md`).

## 4. Geographic data

Source: Natural Earth admin-0 countries 1:50m via npm package world-atlas@2.0.2 (ISC packaging). Terms: <https://www.naturalearthdata.com/about/terms-of-use/> (public domain). Processing: TopoJSON → GeoJSON, Mercator projection, Ramer–Douglas–Peucker simplification, grouped into seven island-group regions. The interactive Leaflet map in the application is **kept**; these SVGs are the editorial/static layer, not a replacement for it. Leaflet's own OpenStreetMap tiles still need their "© OpenStreetMap contributors" credit in the map control.

Note on boundaries: Natural Earth draws international boundaries from its own policy. These SVGs show island-group silhouettes, not an authoritative border claim, and are labelled decoratively.

## 5. Original SVG and CSS (created for Student Go)

| Filename | Component | Source | Source URL | Creator | License | Attribution | Local path |
|---|---|---|---|---|---|---|---|
| `apple-touch-icon.png` | `<link rel="apple-touch-icon">` | Drawn in code (generator script) | n/a | Student Go design (generated for this project) | Original work, no third-party rights | None | `public/assets/brand/apple-touch-icon.png` |
| `favicon.ico` | `<link rel="icon" sizes="32x32">` fallback | Drawn in code (generator script) | n/a | Student Go design (generated for this project) | Original work, no third-party rights | None | `public/assets/brand/favicon.ico` |
| `favicon.svg` | `<link rel="icon" type="image/svg+xml">` | Drawn in code (generator script) | n/a | Student Go design (generated for this project) | Original work, no third-party rights | None | `public/assets/brand/favicon.svg` |
| `student-go-logo-on-dark.svg` | Auth side panel, dark footer | Drawn in code (generator script) | n/a | Student Go design (generated for this project) | Original work, no third-party rights | None | `public/assets/brand/student-go-logo-on-dark.svg` |
| `student-go-logo.svg` | Header, footer, auth form side (light surfaces) | Drawn in code (generator script) | n/a | Student Go design (generated for this project) | Original work, no third-party rights | None | `public/assets/brand/student-go-logo.svg` |
| `student-go-symbol-on-dark.svg` | Dark surfaces where only the mark fits | Drawn in code (generator script) | n/a | Student Go design (generated for this project) | Original work, no third-party rights | None | `public/assets/brand/student-go-symbol-on-dark.svg` |
| `student-go-symbol.svg` | Mobile header, avatar-less placeholders, app icon source | Drawn in code (generator script) | n/a | Student Go design (generated for this project) | Original work, no third-party rights | None | `public/assets/brand/student-go-symbol.svg` |
| `no-applications.svg` | Student: Lamaran Saya (no applications yet) | Drawn in code (generator script) | n/a | Student Go design (generated for this project) | Original work, no third-party rights | None | `public/assets/illustrations/empty-states/no-applications.svg` |
| `no-documents.svg` | Repositori (no documents / no results in folder) | Drawn in code (generator script) | n/a | Student Go design (generated for this project) | Original work, no third-party rights | None | `public/assets/illustrations/empty-states/no-documents.svg` |
| `no-notifications.svg` | Notifikasi (inbox clear) | Drawn in code (generator script) | n/a | Student Go design (generated for this project) | Original work, no third-party rights | None | `public/assets/illustrations/empty-states/no-notifications.svg` |
| `no-saved-projects.svg` | Student: Tersimpan / Wishlist (nothing saved) | Drawn in code (generator script) | n/a | Student Go design (generated for this project) | Original work, no third-party rights | None | `public/assets/illustrations/empty-states/no-saved-projects.svg` |
| `no-search-results.svg` | Jelajahi Proyek, Talenta, Repositori (filter returns 0) | Drawn in code (generator script) | n/a | Student Go design (generated for this project) | Original work, no third-party rights | None | `public/assets/illustrations/empty-states/no-search-results.svg` |
| `company-journey-vertical.svg` | Same diagram, vertical (< 768px) | Drawn in code (generator script) | n/a | Student Go design (generated for this project) | Original work, no third-party rights | None | `public/assets/illustrations/workflows/company-journey-vertical.svg` |
| `company-journey.svg` | Homepage / About "Cara kerja", company lane (horizontal, >= 768px) | Drawn in code (generator script) | n/a | Student Go design (generated for this project) | Original work, no third-party rights | None | `public/assets/illustrations/workflows/company-journey.svg` |
| `institution-journey-vertical.svg` | Same diagram, vertical (< 768px) | Drawn in code (generator script) | n/a | Student Go design (generated for this project) | Original work, no third-party rights | None | `public/assets/illustrations/workflows/institution-journey-vertical.svg` |
| `institution-journey.svg` | Homepage / About "Cara kerja", institution lane (horizontal, >= 768px) | Drawn in code (generator script) | n/a | Student Go design (generated for this project) | Original work, no third-party rights | None | `public/assets/illustrations/workflows/institution-journey.svg` |
| `project-status-vertical.svg` | Same diagram, vertical (< 768px) | Drawn in code (generator script) | n/a | Student Go design (generated for this project) | Original work, no third-party rights | None | `public/assets/illustrations/workflows/project-status-vertical.svg` |
| `project-status.svg` | Project/application status lifecycle (help page, detail side note) | Drawn in code (generator script) | n/a | Student Go design (generated for this project) | Original work, no third-party rights | None | `public/assets/illustrations/workflows/project-status.svg` |
| `student-journey-vertical.svg` | Same diagram, vertical (< 768px) | Drawn in code (generator script) | n/a | Student Go design (generated for this project) | Original work, no third-party rights | None | `public/assets/illustrations/workflows/student-journey-vertical.svg` |
| `student-journey.svg` | Homepage / About "Cara kerja", student lane (horizontal, >= 768px) | Drawn in code (generator script) | n/a | Student Go design (generated for this project) | Original work, no third-party rights | None | `public/assets/illustrations/workflows/student-journey.svg` |
| `indonesia-outline.svg` | Single-colour outline for small placements / print | Natural Earth 1:50m via world-atlas@2.0.2 | n/a | Student Go design (generated for this project) | Public domain data; drawing is original | None | `public/assets/illustrations/geographic/indonesia-outline.svg` |
| `indonesia-regions.svg` | Homepage + About region strip; 7 island-group `<g>` regions with an `.is-active` hook | Natural Earth 1:50m via world-atlas@2.0.2 | n/a | Student Go design (generated for this project) | Public domain data; drawing is original | None | `public/assets/illustrations/geographic/indonesia-regions.svg` |
| `contour-field.svg` | Editorial surfaces only (homepage band, auth side panel) | Drawn in code (generator script) | n/a | Student Go design (generated for this project) | Original work, no third-party rights | None | `public/assets/illustrations/motifs/contour-field.svg` |
| `map-pin-selected.svg` | Leaflet `L.icon` for the selected project marker | Drawn in code (generator script) | n/a | Student Go design (generated for this project) | Original work, no third-party rights | None | `public/assets/illustrations/motifs/map-pin-selected.svg` |
| `map-pin.svg` | Leaflet `L.icon` for project markers (replaces default blue pin) | Drawn in code (generator script) | n/a | Student Go design (generated for this project) | Original work, no third-party rights | None | `public/assets/illustrations/motifs/map-pin.svg` |
| `survey-marks.svg` | Corner/crop marks on figures and photo frames | Drawn in code (generator script) | n/a | Student Go design (generated for this project) | Original work, no third-party rights | None | `public/assets/illustrations/motifs/survey-marks.svg` |

Notes from the manifest:

- **brand** — Wordmark outlines were converted from Plus Jakarta Sans Bold (SIL OFL 1.1) glyph shapes; the OFL permits use of the font's output.
- **workflows** — Step labels derive from routes and statuses documented in the design audit; confirm against the application's real routes.
- **motifs** — contour-field.svg is procedurally generated from a synthetic terrain (no real survey data).

Workflow diagrams show only steps the audit documents (routes and statuses it observed). Nothing was added that the application does not already do. Check each label against the real routes before publishing.

Code files (`resources/css/student-go/{fonts,tokens,components}.css`, `docs/styleguide.*`, `scripts/fetch-photos.php` and its `.sh` wrapper) are original work for this project.

## 6. Build-time tools (not shipped, not runtime dependencies)

| Tool | Version | License | Used for |
|---|---|---|---|
| lucide-static | 1.64.0 | ISC | icon source |
| @fontsource-variable/plus-jakarta-sans | 5.3.0 | OFL-1.1 | font source |
| @fontsource-variable/source-serif-4 | 5.3.1 | OFL-1.1 | font source |
| world-atlas | 2.0.2 | ISC (data: Natural Earth, public domain) | geography source |
| topojson-client | 3.1.0 | ISC | decode atlas |
| @tailwindcss/cli, tailwindcss | 4.1.16 | MIT | verify the CSS compiles |
| fontTools + HarfBuzz (Python) | — | MIT / MIT | outline the wordmark from Plus Jakarta Sans Bold |
| Playwright / Chromium | — | Apache-2.0 | screenshots and overflow probes |

The application itself gains **no new runtime dependency**.

## 7. When you add an asset later

1. Open the asset's own page and read the license line. 2. Add a row in the format above (all eight fields). 3. Put the file under `public/assets/…`; never hotlink. 4. Re-run the manifest builder or add the entry to `assets-manifest.json` by hand. 5. If the license is NC, ND, unknown or the image is watermarked, do not add it.
