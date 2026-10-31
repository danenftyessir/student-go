# CONTENT RENAME INVENTORY — Karsa to Student Go

> **Status: DISCOVERY ONLY.** Written 2026-10-30 under AD-10. Nothing in the application was changed: pages were read with GET requests from a browser that was not logged in, and no form was submitted. Evidence labels as in `DISCOVERY_REPORT.md` section 1. This file lists *content* (words the user reads). Files and image assets stay in `ASSET_INVENTORY.md`.

## 1. The decision (AD-10)

The product is called **Student Go**. The user said so on 2026-10-30 after seeing that page content still said 'Karsa'. Every user-facing occurrence of the old name has to change. The earlier planning pack treated the name as undecided (D-01) and kept the old logo 'until a decision'; that was a gap, and this file and AD-10 close it.

AD-10 decides the **name**. It does not decide five neighbouring questions, which stay OPEN so nobody invents answers: the assistant's name (D-36), the Karsa tagline and Karsa-era narrative (D-37), the legal name inside consent text (D-38), example passwords (D-39) and non-visible identifiers such as `APP_NAME` (D-40).

## 2. What counts as content

- Visible text in any role's pages: headings, paragraphs, buttons, labels, placeholders, empty states, tooltips.
- Text that assistive technology or crawlers read: `alt`, `aria-label`, `title` attributes, `<title>`, `meta`, `og:*`, JSON-LD.
- Messages: validation, flash, toast, notification text, emails, PDFs and any other generated output.
- Consent and policy wording (handled with care: rule 5 below).
- Images that contain the name as pixels (the logo file; possibly others).

**Not content, left alone:** CSS class names, route names, variable and file names, code comments, `vendor/`, `public/build/`, historical planning documents, and database rows (rule 8).

## 3. Rules for the rename

- **1. No blind find-and-replace.** Read each hit in context. 'Karsa' is a name, not a word to swap mechanically: grammar, capitalisation and the surrounding claim all matter.
- **2. Name only.** 'Karsa' becomes 'Student Go'. The Karsa tagline ('Karya Untuk Bangsa') is not carried over and no new tagline is invented (D-37).
- **3. History and numbers belong to the owner.** Sentences about founding, age ('usia ke-2 tahun'), growth or impact were written about Karsa. Replacing the name would put unconfirmed claims on Student Go. Keep the structure, mark the text DRAFT, ask (D-37, D-23).
- **4. Indonesian first.** When a renamed sentence is in English it moves to Indonesian under the copy deck (D-02). Keep sentence case.
- **5. Consent and legal wording is not renamed silently.** The terms checkbox names the party the user agrees to, and the Privacy link points to '#' (J20); whether Terms and Privacy pages exist is UNKNOWN. The owner confirms the legal name first (D-38).
- **6. `alt` describes, it does not brand.** An image `alt` says what the picture shows. A logo inside a labelled link has an empty `alt`; the link's `aria-label` carries 'Student Go'.
- **7. `.env` is the user's.** No phase edits `.env`. `APP_NAME` is changed by the user, at a moment the report names, because it can change the session cookie and cache namespace (D-40, RN-25).
- **8. Data is not edited.** Database rows (descriptions, reviews, stored notifications, seeded accounts) are listed, never rewritten by a design phase.
- **9. Files stay.** `karsa-logo.png` and other old assets stay on disk (AD-09). Templates stop referencing them in the phase named in the table.
- **10. Everything gated stays visible.** A hit that waits for an OPEN decision is listed in the phase report with its RN id. It is never silently skipped and never guessed.

## 4. Inventory A — pages a guest can open (VERIFIED 2026-10-30)

Read from the DOM of the running app (http://127.0.0.1:8000), regular expression over text nodes, `alt`, `aria-label`, `title`, `placeholder`, `value`, `<title>` and `meta`. Pages read: `/`, `/about`, `/contact`, `/login`, `/register`, `/register/student`, `/register/institution`, `/register/company`, `/forgot-password`. Quoted strings are short samples, not the full copy.

| ID | Page | Where | Says today | Treatment | Gate | Phase |
|---|---|---|---|---|---|---|
| RN-01 | `/` | `<h2>` in `section#tentang` | 'Kenapa Memilih Karsa?' | Rename to 'Kenapa Memilih Student Go?' only if the section survives. Phase 4 already plans to remove this feature row, so the usual outcome is that the heading disappears. | AD-10; D-37 | 4 |
| RN-02 | `/` | `<p>` inside a `<section>` of `<main>` (not in the footer) | 'Karsa - Karya Untuk Bangsa' | The name becomes Student Go. 'Karya Untuk Bangsa' is the Karsa-era tagline and nothing in the pack replaces it. Do not invent a tagline: remove the line unless the user supplies one. | D-37 | 4 |
| RN-03 | `/about` | `<img alt>` (photo) | 'About Us Karsa' | An `alt` describes the picture, not the brand. Rewrite it in Indonesian when the photo is placed (Phase 5); never copy the old words over. | AD-10; D-02 | 5 |
| RN-04 | `/about` | `<img alt>` (background) | 'Karsa Background' | A decorative background gets `alt=""` or is removed (guardrail 7: images only where they carry content). | AD-10 | 5 |
| RN-05 | `/about` | `<img alt>` (SDG graphic) | 'SDGs Karsa' | Describe the graphic (which SDGs), not the brand. | AD-10 | 5 |
| RN-06 | `/about` | `<span>` brand label in the slider | 'Karsa' | Rename to 'Student Go'. | AD-10 | 5 |
| RN-07 | `/about` | `<p>` mission line | 'Karsa hadir untuk mentransformasi ekosistem KKN Indonesia' | Name becomes Student Go. The mission claim itself needs the owner's approval, because it was written for Karsa. | AD-10; D-37; D-23 | 5 |
| RN-08 | `/about` | `<h2>` history heading | 'Karsa Dari Tahun Ke Tahun' | Not a pure rename: 'dari tahun ke tahun' asserts a timeline. Keep, rewrite or drop only after D-37. | D-37 | 5 |
| RN-09 | `/about` | four `<p>` blocks: founding story ('berdiri sebagai katalisator'), age ('Menyongsong usia ke-2 tahun'), call to action ('membentuk Karsa sebagai'), SDG impact claim | Karsa as subject of every sentence | A find-and-replace would put Student Go's name on a founding story, an age and impact figures that nobody has confirmed for Student Go (the figures are already unsourced, J9). The owner supplies or approves this text (D-23, D-37). Until then the agent keeps the structure, marks the copy DRAFT and asks; it does not write a history. | D-37; D-23 | 5 |
| RN-10 | `/contact` | `<img alt>` (hero photo) | 'Contact Us Karsa' | As RN-03. | AD-10; D-02 | 5 |
| RN-11 | `/contact` | `<p>` invitation | 'Ingin Berkolaborasi Dengan Karsa Hubungi Kami Di Sini.' | Name becomes Student Go; also fix the Title Case ('Dengan', 'Di', 'Sini'). | AD-10; D-02 | 5 |
| RN-12 | `/contact` | `<p>` intro | 'Hubungi tim kami untuk informasi lebih lanjut mengenai platform Karsa dan layanan yang tersedia.' | Name becomes Student Go. | AD-10 | 5 |
| RN-13 | `/login`, `/register`, `/register/student`, `/register/institution`, `/register/company` | Auth layout logo `<img src="/karsa-logo.png">` | alt 'Karsa - Karya Untuk Bangsa' | Switch the shared auth layout to `student-go-logo.svg` (the public layout already uses it). The PNG stays on disk (AD-09). Accessible name: 'Student Go'. | AD-10; D-18; AD-09 | 3 |
| RN-14 | `/login` | `<p>` (English) | 'Sign in to your Karsa account and continue making impact in your community.' | Name becomes Student Go, and the sentence moves to Indonesian under the copy deck. | AD-10; D-02 | 6 |
| RN-15 | `/register` | `<h1>` | 'Bergabung dengan Karsa' | Rename to 'Bergabung dengan Student Go'. | AD-10 | 6 |
| RN-16 | `/register/student` | label of the `terms` checkbox | 'Saya Setuju Dengan Syarat Dan Ketentuan Serta Kebijakan Privasi Karsa' | This is consent wording: it names the party the user agrees to. Do not rename it silently. The owner confirms the legal name and the Terms and Privacy pages exist (the footer privacy link is '#', J20). Keep the checkbox `id`/`name` `terms`. | D-38 | 6 |
| RN-17 | `/register/student` | password rules block, `<code>` in 'Contoh password yang valid' | two example passwords, one built from the old brand name | A published example password is a poor practice (people copy it). Options in D-39; whichever is chosen, no password example contains the old brand. Do not copy the examples into any document or test. | D-39 | 6 |

`/register/institution`, `/register/company` and `/forgot-password` showed no text hit; the first two carry the old logo (RN-13). `/register/student` returned a database error page once and the normal form on five other requests during the same session, so the database connection is intermittent (see D-12); the form text above was read from a normal response.

## 5. Inventory B — places that could not be read

Everything behind a login, every generated message and every non-template file. These are UNKNOWN or BLOCKED until Stage One (S1-38 to S1-43) searches the repository.

| ID | Place | What is known or expected | Evidence | Treatment | Gate | Phase |
|---|---|---|---|---|---|---|
| RN-20 | Authenticated layouts: top navigation, avatar menu, footer, page `<title>` | The audit says all roles share the public footer and a role-specific nav (OBSERVED). The footer text changed on the public layout (see section 6); whether the authenticated footer, nav labels and titles carry 'Karsa' or 'KKN Go' is not known. | UNKNOWN | Search all layouts and partials (S1-38). Rename in the same task that rebuilds the shell. | AD-10 | 3 |
| RN-21 | Dashboards, welcome lines, empty states, flash messages, validation messages (`lang/` or inline strings) | Unknown; the audit reports 'KKN Go' at least once (OBSERVED). | OBSERVED / UNKNOWN | Search `resources/views`, `lang`, `app` for the patterns in section 7; rename in the phase that owns the page. | AD-10 | 7-25 |
| RN-22 | KARA assistant: screens `/student/chatbot*`, greeting and system texts, `KARA-background.png` | The assistant name KARA is a separate question; the file stays (AD-09). | OBSERVED | Do not rename the assistant until D-36 is answered. Rename only occurrences of the product name. | D-36 | 16 |
| RN-23 | Notifications (bell and `/notifications`): texts built in notification classes, in Blade, or stored as database rows | Unknown where the strings live. | UNKNOWN | Stage One finds the sources (S1-21, S1-41). Code-built strings are renamed; stored rows are data and are not edited (section 3, rule 8). | AD-10; D-40 | 16 |
| RN-24 | Emails: password reset, verification, application status; mail 'from' name; mail layout | Unknown. In Laravel the default 'from' name and the mail header read `APP_NAME`. | UNKNOWN (INFERRED convention) | Stage One lists mail classes and `config/mail.php` (S1-41). Decide with D-40. | D-40 | 0, 6 |
| RN-25 | `APP_NAME`, `config/app.php`, `.env.example`, README, `composer.json` / `package.json` `name` | Laravel's default session cookie name and cache prefix are built from `APP_NAME` (INFERRED from the framework defaults; confirm in `config/session.php` and `config/cache.php`). Changing it logs everyone out once and empties the cache namespace. | UNKNOWN (INFERRED) | Never edit `.env`. Update `.env.example`, `config/app.php` default and README in the phase that owns them; tell the user the exact `.env` line to change and when. | D-40 | 0, 3 |
| RN-26 | Error pages 404 / 403 / 419 / 500 / 503 | The 404 page is Laravel's default and carries no brand (VERIFIED); the others are UNKNOWN. | VERIFIED (404) | Nothing to rename; Phase 26 brands them as Student Go. | AD-10 | 26 |
| RN-27 | Certificates and generated PDFs | Unknown; certificate resources are protected (D-32). | UNKNOWN | Any text change in certificate output is an output change: ask first, compare before and after. | D-32 | — |
| RN-28 | Database content (institution descriptions, reviews, stored notifications, seeded names, user emails on a 'karsa' domain) | Unknown. Rows are data, not templates. | BLOCKED | Do not edit database rows. List them in the phase report and let the user decide. Seeders are code: rename only fixtures that are display text, and tell the user before changing any seeded email address because tests may log in with it. | D-40 | 0, 1 |
| RN-29 | Images that contain the old name as pixels | `karsa-logo.png` (VERIFIED). Other raster images (photographs, banners, `login-register-view.jpg`, dashboard images) may show the word on a sign, shirt or banner; not looked at for this purpose. | VERIFIED (logo) / UNKNOWN | Keep the PNG on disk and stop referencing it (AD-09). Check the photographs during the rights review (D-30, S1-35) and flag any that show the old name. | AD-09; D-30 | 2, 3 |
| RN-30 | SEO and sharing metadata (`description`, `og:*`, JSON-LD, `robots.txt`, `sitemap`) | No `og:` or description tags were found on the five pages read; `robots.txt` exists and was not read. | VERIFIED (absence on 5 pages) | When Phase 3 adds metadata it uses 'Student Go' only. | AD-10 | 3 |
| RN-31 | Code identifiers: CSS classes, route names, variable names, file names containing 'karsa' | Unknown. They are not content. | UNKNOWN | Leave them. Renaming identifiers is a refactor (AD-06). Only `karsa-logo.png` matters, and only as a reference to stop using. | AD-06 | — |

## 6. Already changed in the running app (not by this planning session)

The first reading of these pages (earlier on 2026-10-30) showed 'Laravel' in titles and the old logo in the public header. A later reading showed the following. This session has no write access to the app, so someone else changed it, probably by running the pack's phases (INFERRED). The tracker still says NOT_STARTED; S1-43 reconciles that.

| Item | State now (VERIFIED 2026-10-30) |
|---|---|
| `<title>` of every guest page | Ends with ' — Student Go' on `/`, `/about`, `/contact`, `/login` ('Masuk'), `/register` ('Daftar'), `/register/student\|institution\|company`, `/forgot-password` ('Lupa Password'). The earlier planning text said the titles started with 'Laravel': that was true when it was read and is no longer true. |
| Logo and favicon on the public layout | Header and footer of `/`, `/about`, `/contact` use `/assets/brand/student-go-logo.svg` and `student-go-logo-on-dark.svg` (empty `alt`, the link carries `aria-label` 'Student Go, ke beranda'). `<link rel=icon>` points to `favicon.svg`, `favicon.ico`, `apple-touch-icon.png` under `/assets/brand/`. |
| Footer sentence on the public layout | Now 'Platform digital yang menghubungkan mahasiswa dengan instansi untuk program Kuliah Kerja Nyata berkelanjutan.' (no brand name in it). |
| `/forgot-password` | Renders (it returned HTTP 500 on the first pass), shows the new logo, title 'Lupa Password — Student Go', no 'Karsa' text. |
| `<html lang>` | `id` on `/`, `/about`, `/contact`, `/login`, `/register`, `/forgot-password` (it was `en` on the first three). |

Still old on the same pages: the body copy, `alt` texts, the consent label and the auth-layout logo listed in section 4.

## 7. How to search and how to check

Patterns (case-insensitive):

| Pattern | Why | Action |
|---|---|---|
| `karsa` (case-insensitive: Karsa, KARSA, karsa) | The old name in text, titles, alts, emails, domains, file names. | Rename user-facing hits; leave identifiers (RN-31). |
| `karya untuk bangsa` | The Karsa tagline (RN-02, RN-13). | Do not carry it over; D-37. |
| `kkn go` and `kkngo` | A third product name seen once in the audit (OBSERVED). | Becomes Student Go (AD-10). |
| `laravel` in titles, headings, meta, the `APP_NAME` default | The framework default leaking as a product name. | Replace in user-facing places only; framework class names and docs are not content. |
| `\bkara\b` (the assistant) | The assistant is a separate name (D-36). | Do not change until D-36 is answered. |
| `karsa-logo` and `karsa-logo.png` | References to the old logo file. | Stop referencing from templates in Phase 3; never delete the file (AD-09). |

Searches the repository agent runs in Stage One (read-only; the development machine is probably Windows, so a PowerShell form is given too):

```
rg -n -i "karsa|kkn ?go|karya untuk bangsa" resources lang app routes config database public --glob "!public/build/**"
Get-ChildItem -Recurse resources,lang,app,routes,config,database,public -File | Where-Object { $_.FullName -notmatch 'public\\build' } | Select-String -Pattern 'karsa|kkn ?go|karya untuk bangsa'
```

Check after each page phase (browser console on the page, or the same logic over a fetched page; read-only):

```js
(() => {
  const re = /karsa|kkn ?go|karya untuk bangsa|laravel/i, hits = [];
  if (re.test(document.title)) hits.push('title: ' + document.title);
  document.querySelectorAll('meta[content]').forEach(m => re.test(m.content) && hits.push('meta: ' + m.content));
  document.querySelectorAll('[alt],[aria-label],[title],[placeholder]').forEach(e =>
    ['alt','aria-label','title','placeholder'].forEach(a => re.test(e.getAttribute(a) || '') && hits.push(a + ': ' + e.getAttribute(a))));
  const w = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
  for (let n; (n = w.nextNode()); ) { const t = n.nodeValue.trim(); if (t && re.test(t) && !/SCRIPT|STYLE/.test(n.parentElement.tagName)) hits.push('text: ' + t.slice(0, 80)); }
  return hits;
})()
```

An empty result on every page state (including empty, error and long-content states) is the acceptance test for the content rename. The only allowed non-empty results are listed in the phase report with their RN id and the OPEN decision that holds them back.

Stage One additions (also listed in `DISCOVERY_REPORT.md` section 12 and in `PHASE_00_READINESS.md`):

| ID | Check (read-only) | Why | How |
|---|---|---|---|
| S1-38 | Search `resources/views`, `resources/js`, `lang`, `app`, `routes`, `config`, `database`, `public` (not `public/build`, not `vendor`) for the patterns in section 7 of `CONTENT_RENAME_INVENTORY.md`; list file, line, kind (visible text / attribute / identifier / comment) and the phase that owns the file. | Turns RN-20 to RN-31 from UNKNOWN into a hit list. | `rg -n -i "karsa\|kkn ?go\|karya untuk bangsa" ...` (Windows without ripgrep: `Select-String -Path ... -Pattern ...`) |
| S1-39 | Search Blade for page `<title>` and layout `<title>` patterns and for `config('app.name')` / `env('APP_NAME')` use. | Shows which titles still come from `APP_NAME` (D-40). | `rg -n "<title\|app.name\|APP_NAME" resources/views config` |
| S1-40 | Read `config/app.php`, `config/session.php`, `config/cache.php`, `.env.example` (never `.env`): where `APP_NAME` flows (cookie name, cache prefix, mail). | Confirms or refutes the INFERRED side effects in RN-25. | file reads |
| S1-41 | List mail classes, mail views, notification classes and their strings; `config/mail.php` sender. | RN-23, RN-24. | `app/Mail`, `app/Notifications`, `resources/views/emails` |
| S1-42 | Read seeders and factories for display text or email domains containing the old name (record file and line, never credentials). | RN-28; tests may log in with seeded emails. | `database/seeders`, `database/factories` |
| S1-43 | Drift check: compare the repository (`git log`, `git status`, `git diff --stat` since the commit the plan was written against) with this pack. The running app already serves the Student Go brand files, new fonts, the icon sprite and a rebuilt bundle; find which phase's work that is, by whom and whether it was tested. | The tracker says NOT_STARTED for every phase; the running app disagrees. | `git log --stat`, `git status` |

## 8. Phase ownership

| Phase | Rename work | Rows |
|---|---|---|
| 0 | Stage One searches (S1-38 to S1-43); reconcile drift | RN-20 to RN-31 (find), section 6 |
| 2 | Compare existing `public/assets/` with the package before copying anything | RN-29 |
| 3 | Auth-layout logo; authenticated layouts, titles, metadata | RN-13, RN-20, RN-30 |
| 4 | Homepage copy | RN-01, RN-02 |
| 5 | About and Contact copy and alts | RN-03 to RN-12 |
| 6 | Login and registration copy, consent wording, password examples, auth emails | RN-14 to RN-17, RN-24 |
| 7-25 | Each page phase renames the product name on its own pages | RN-21 |
| 16 | KARA and notification texts | RN-22, RN-23 |
| 26 | Error pages | RN-26 |
| 27-28 | Accessibility and final QA re-run the DOM probe on every page | all |

## 9. Decisions this file depends on

| ID | Question | Gates phases |
|---|---|---|
| D-36 | Assistant name KARA and its background image | 16 (and the nav label in 3) |
| D-37 | Tagline 'Karya Untuk Bangsa' and Karsa-era narrative (About history, age and impact claims) | 4, 5 |
| D-38 | Legal party named in consent text and the Terms and Privacy pages | 3, 6 |
| D-39 | Example passwords shown on the student registration form | 6 |
| D-40 | `APP_NAME`, mail sender, seeded fixtures and other non-visible identifiers | 0, 3, 6 |

Full wording, options and recommendations: `DECISIONS_AND_OPEN_QUESTIONS.md`. AD-10 itself is in Part 1 of that file.

## 10. Limits of this inventory

- Only guest-reachable pages were read, with no login and no form submission. Text that appears only after a click, in a modal, or after JavaScript runs on an authenticated page was not seen.
- The repository, language files, mail and notification classes, seeders and the database were not reachable. Section 5 is therefore a list of places to search, not a list of findings.
- The quoted strings are samples taken from the DOM and can be truncated; the exact text is whatever the view contains when the phase runs.
- The running app changed during the session (section 6), so any page text may have changed again by the time a phase runs. The DOM probe in section 7 is the source of truth at that time.

## 11. Outcome log

| ID | Phase | Outcome (2026-10-31) |
|---|---|---|
| RN-01 | 4 | **Removed.** The generic three-feature section ('Kenapa Memilih Karsa?') was deleted with its content; no replacement heading was written. |
| RN-02 | 4 | **Removed.** The line 'Karsa - Karya Untuk Bangsa' and its quote block were deleted; no tagline was created (user decision for the homepage, D-37). |
| RN-03 | 5 | **Rewritten.** The truck photo's alt now describes the picture in Indonesian ('Mahasiswa berompi KKN ITB 2025, dilihat dari belakang, menumpang bak mobil terbuka …'). |
| RN-04 | 5 | **Removed with the image.** `about.jpg` (slide background) is no longer placed; the file stays on disk. |
| RN-05 | 5 | **Rewritten.** 'SDGs Karsa' was on the vest photograph; the alt now describes the vest. No SDG graphic is shown. |
| RN-06 | 5 | **Removed** with the 'Perkenalkan, Karsa' slide; the h1 names Student Go. |
| RN-07 | 5 | **Not published** (mission claim; D-37, D-23). Not renamed; no replacement mission. |
| RN-08 | 5 | **Not published** ('Dari Tahun Ke Tahun' timeline; D-37). No timeline written. |
| RN-09 | 5 | **Not published**: founding story, age, 'revolusi mahasiswa', SDG impact claim (D-37, D-23). About now states product facts only (DRAFT). |
| RN-10 | 5 | **Removed with the hero image**; Contact has no photograph. |
| RN-11 | 5 | **Renamed + sentence case**: 'Ingin berkolaborasi dengan Student Go? Hubungi kami di sini.' |
| RN-12 | 5 | **Renamed only**: 'Hubungi tim kami untuk informasi lebih lanjut mengenai platform Student Go dan layanan yang tersedia.' |

Phase 5 check: the section 7 DOM probe returned 13 hits before (About 10, Contact 3) and 0 after on `/about`, `/contact` and `/institution/{1,21,77}` at six widths (`reports/phase5/screenshots/after/after-metrics.json`).
