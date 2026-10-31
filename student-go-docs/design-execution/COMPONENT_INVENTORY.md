# COMPONENT INVENTORY — Student Go shell and shared components

Built in Phase 3 (2026-10-30). This is the reference for Phases 4–25: use these components when a page is migrated instead of writing new markup or inline styles. CSS is in `resources/css/student-go/components.css` (`.sg-*`), shared JavaScript in `resources/js/app.js` + `resources/js/student-go/*.js`, Blade components in `resources/views/components/ui/` (used as `<x-ui.name>`).

- **Living reference:** `student-go-docs/design-system/gallery/component-gallery.blade.php` renders every component in every state with stress strings (76-character institution name, 65-character document title, 51-character email). There is no route; render it to `component-gallery.html` with the command at the top of the file and open it on the app origin (the icon sprite must be same-origin). Screenshots: `reports/phase3/screenshots/gallery/`.
- **Tests:** `tests/Feature/Phase3/ComponentsTest.php` (component contracts, status mapping) and `ShellTest.php` (shell per role). Browser checks: `reports/phase3/interaction-checks.json` (144 checks).
- **Rules that every component follows:** tokens only (no hex in views), no gradient, shadow only on overlays (`.sg-overlay`, dialogs, toasts, the filter drawer), pill only for status and counts, 150 ms colour/opacity transitions that switch off under `prefers-reduced-motion`, a visible `:focus-visible` ring (2 px brand), 44 px targets on touch screens (`pointer: coarse`).
- **Language:** Indonesian only (D-02). Status words come only from `App\Support\StatusVocabulary`.

## 1. Application shell

| Component | Location | Purpose and behaviour |
|---|---|---|
| Shared `<head>` | `resources/views/partials/head.blade.php` | `charset`, viewport, CSRF meta, `sg-sprite` meta, title pattern **`Halaman — Student Go`** (a trailing `- Karsa`, `- Laravel` or `- <APP_NAME>` written by a page is stripped), favicon set from `public/assets/brand/`, preload of the Plus Jakarta Sans latin WOFF2, `@vite(['resources/css/app.css', 'resources/js/app.js'])`, then Alpine 3.13.3 (CDN, `defer`, D-20). The bundle comes before Alpine so `alpine:init` registrations always exist. |
| App layout | `resources/views/layouts/app.blade.php` | `<html lang="id">`, skip link `Lewati ke konten utama` → `<main id="main" tabindex="-1">`, navbar, footer, "back to top" button (`#scroll-to-top`, appears after 300 px), toast container `#notification-container` (`aria-live="polite"`), flash data `#sg-flash` (session keys `success`, `error`, `info`, `warning`, unchanged), the shared confirm dialog. `@stack('styles')` / `@stack('scripts')` unchanged. Used by 76 views. |
| Auth layout | `resources/views/layouts/auth.blade.php` | **Phase 6: the single auth template** for `/login`, `/register`, the three registrations, `/forgot-password`, `/reset-password/{token}` and `/email/verify`. Shared head, skip link, compact header (`.sg-auth-header`: wordmark to home named "Student Go, ke beranda" + Tentang Kami · Kontak; not the full navbar), `<main id="main">` with a 400 px column (`auth_width` = `wide` gives 640 px), optional `@section('aside')` shown from 1024 px as a contour panel (`.sg-contour`, no photo, no numbers). Flash keys `success`/`status`, `error`, `info`, `warning` render as inline `x-ui.alert` (unchanged keys). No footer. |
| Auth pages | `resources/views/auth/*.blade.php` | **Migrated in Phase 6** (no standalone HTML documents remain). See section 8. |
| Navbar | `resources/views/components/navbar.blade.php` (`@include('components.navbar')`) | One header for every role, white with a bottom border (sticky; no blur, no shadow). Student Go wordmark (`assets/brand/student-go-logo.svg`) linking home with the name "Student Go, ke beranda". Desktop navigation from **1024 px**; below that a menu button. Labels (D-02): **Mahasiswa** Dashboard · Telusuri Masalah · Aplikasi Saya · Proyek Saya · Repositori · Lowongan; **Instansi** Dashboard · Masalah · Aplikasi · Proyek · Ulasan; **Perusahaan** Dashboard · Talenta · Lowongan · Lamaran · Talenta Tersimpan; **tamu** Tentang Kami · Kontak + Masuk (tertiary) and Daftar (primary). Routes and active patterns are the same as before. The current page has `aria-current="page"` and a 2 px brand underline. |
| Account menu | inside the navbar (desktop) | Trigger: avatar + name (name visible from 1280 px), chevron; accessible name "Menu akun {nama}". Panel: name, email, **role badge**, then Profil (+ Wishlist and Jaringan for students), Notifikasi, separator, **Keluar** (POST `route('logout')` with `@csrf`). Disclosure pattern: `aria-expanded`, `aria-controls`, Esc closes and returns focus, click outside or Tab out closes, ArrowUp/ArrowDown move between items. |
| Mobile menu | inside the navbar (< 1024 px) | Opens below the header: account block (avatar, name, email, role badge), role navigation, then **Notifikasi with the unread count**, account links, Keluar (POST + CSRF). Every row starts on the same left edge and is ≥ 44 px tall. The toggle exposes `aria-expanded` and its name switches between "Buka menu" and "Tutup menu"; Esc closes and returns focus; the menu closes itself when the window grows past 1024 px. Guests get Tentang Kami, Kontak, Masuk and Daftar. |
| Notification bell + panel | `resources/views/components/notification-dropdown.blade.php`, logic `Alpine.data('sgNotifications')` | Same endpoints and behaviour as before: `notifications.latest` (5 latest + `unread_count`) on load, every 30 s and on open; clicking an item POSTs `/notifications/{id}/read` and does **not** redirect; "Tandai semua dibaca" POSTs `notifications.read-all`; "Lihat semua notifikasi" links to `notifications.index`. Accessible name **"Notifikasi, 3 belum dibaca"** / "Notifikasi, tidak ada yang belum dibaca"; a polite live region announces count changes. Items are real `<button>`s; emoji were replaced by Lucide icons (type → icon map in `alpine.js`). The empty state uses `no-notifications.svg`. Under 640 px the panel is pinned under the header across the full width (never clipped). |
| Footer | `resources/views/components/footer.blade.php` | Dark surface `.sg-on-dark` (`#171c2e`), on-dark wordmark, tagline, links Beranda · Tentang Kami · Kontak (≥ 40 px targets), copyright text unchanged. Contrast: text 11.12:1, links 15.64:1. **Kebijakan Privasi is hidden** until the page exists (D-23; it pointed to `#`). |
| Back to top | `#scroll-to-top` in the app layout | `sg-btn--secondary` icon button "Kembali ke atas" (the undefined `bg-primary-600` is gone). Scrolls smoothly unless reduced motion is set, then focuses the wordmark link. |
| Toasts | `resources/js/student-go/toast.js` | `window.showNotification(message, type, duration)` and `window.notificationManager.show(...)` keep their signatures. Types `success`, `error`, `info`, `warning`, each with an icon and a coloured left rule (colour is never the only signal). Errors use `role="alert"`. Close button "Tutup pesan"; auto-dismiss after 5 s, paused while hovered or focused. Messages are rendered as **text** (before: `innerHTML`). |

## 2. Shared components (`<x-ui.*>`)

Columns: **Variants / props**, **States**, **Responsive**, **Accessibility**. Every form component keeps the `name` and `id` it is given (controllers and JS read them) and repopulates with `old()`.

### Actions

| Component | Props / variants | States | Responsive | Accessibility |
|---|---|---|---|---|
| `x-ui.button` | `variant` primary (one per view) · secondary · tertiary · danger (outline) · danger-solid (only inside a confirm dialog); `size="lg"`; `href` renders `<a>`; `type`; `icon`, `icon-end`; `block` | default, hover, active, focus, disabled, **loading** (spinner + `disabled` + `aria-busy`) | 36 px tall; 44 px on touch | Text label always; disabled link uses `aria-disabled` |
| `x-ui.icon-button` | `icon`, **`label` (required)**, `bordered`, `href` | hover, `aria-expanded` pressed look | 40 px; 44 px on touch | `label` becomes `aria-label` |
| `x-ui.menu-item` | `href`; `method="post|delete|…"` renders a form with `@csrf` (+ `@method`); `icon`; `danger`; `current` | hover, focus (inset ring), current | full-width rows | `aria-current="page"` when current |
| `x-ui.dropdown` | `id`, `label` or `trigger` slot, `align` start/end, `trigger-class` | open/closed | panel ≤ viewport − 2 rem | disclosure: `aria-expanded`, Esc, outside click, focus-out, ArrowUp/Down |

### Forms

| Component | Props / variants | States | Responsive | Accessibility |
|---|---|---|---|---|
| `x-ui.field` | `label`, `for`, `name` (error key), `required` (asterisk), `help`, `error` | default, with help, with server error | full width | `<label for>`; help id `{for}-help`; error id `{for}-error` with icon |
| `x-ui.input` | `name`, `id`, `type`, `value`, `help` | default, hover, focus, disabled, **invalid** (danger border only after a server error or `data-touched`) | 40 px; 44 px on touch | `aria-invalid`, `aria-describedby` linked automatically; passwords are never repopulated |
| `x-ui.textarea` | `name`, `id`, `value`, `rows`, `help` | as input | resizable vertically | as input |
| `x-ui.select` | `name`, `id`, `options` [value ⇒ label] or slot, `selected`, `placeholder` (empty first option) | as input | native select | as input |
| `x-ui.search` | `name` (default `search`; keep `q`/`search` contracts), `label` (visually hidden by default), `placeholder`, `value` (defaults to `request(name)`) | as input | flexible width | real `<label>`; `type="search"` |
| `x-ui.checkbox`, `x-ui.radio` | `name` (supports `x[]`), `value`, `label`, `checked` | checked, disabled; repopulated from `old()` after a failed submit | 44 px rows on touch | label wraps the control |
| `x-ui.fieldset` | `legend`, `name` (error key), `required`, `help` | with error | — | `<fieldset>/<legend>` |
| `x-ui.form-errors` | `title` | shown only when `$errors` has messages | — | danger alert (`role="alert"`) listing every message |
| `x-ui.upload` | `name`, `id`, `label`, `accept`, `multiple`, `required`, `hint` (type + size), `max-size` (bytes, browser warning only), `current` (stored file) | idle, hover, **drag-over**, focused, files chosen (chip with name + size + remove), oversize warning, wrong type on drop, server error | full width | The real `<input type="file">` (visually hidden, focusable) is what gets submitted, so `enctype="multipart/form-data"` stays on the page's form. Keyboard: Space/Enter opens the picker. Remove buttons are named "Hapus {berkas}". |
| `x-ui.password` (Phase 6) | `name`, `id`, `rules` (show the four `Password::min(8)->mixedCase()->numbers()->symbols()` rules, checked while typing, as `{id}-help`); other attributes go to the `<input>` | hidden/shown, rules met/unmet, server error | toggle 44 px on touch | wraps `x-ui.input` (same `name`/`id`/error wiring); toggle is a `<button>` named "Tampilkan password" with `aria-pressed` and `aria-controls`; each met rule reads "(terpenuhi)" |

### Data presentation

| Component | Props / variants | States | Responsive | Accessibility |
|---|---|---|---|---|
| `x-ui.page-header` | `title` (**the page's only `<h1>`**), `description`, `back` + `back-label`, or `breadcrumbs`; `actions` slot | — | actions drop under the title below 640 px | — |
| `x-ui.breadcrumb` | `items` [label ⇒ url]; the last item is the current page | — | wraps | `nav[aria-label="Jejak navigasi"]`, `aria-current` |
| `x-ui.table` | `caption` (required), `stack` (default on), `caption-visible`; `head` slot of `<th>`; rows in the slot with `<td data-label>` | row hover, sticky header | **stacked rows below 640 px** (label column + value), otherwise one horizontal scroll container | scroll region is focusable and labelled; `<caption>` |
| `x-ui.list` + `x-ui.list-item` | `title`, `href`, `meta`; `leading` and `end` slots | — | end slot wraps under the text below 640 px | real `<ul>/<li>` |
| `x-ui.status` | `domain`, `value` (stored DB value) | tones neutral, info, success, warning, danger, muted | no wrap | word + icon, colour never alone |
| `x-ui.chip` | `icon` | — | — | taxonomy (SDG, category); square corners, not a pill |
| `x-ui.role-badge` | `role` (`users.user_type`) | — | — | Mahasiswa · Instansi · Perusahaan (· Admin) |
| `x-ui.summary` | `items` [['value', 'label']]; null values are not shown | — | wraps; middots never lead a line | numbers must come from a real query (guardrail 2) |
| `x-ui.tabs` | link tabs: `items` [label, href, active, count], `label` | current | scrolls horizontally inside the tab bar | `aria-current="page"` |
| `x-ui.tablist` + `x-ui.tab-panel` | in-page tabs: `tabs` [id ⇒ label], `selected`, `prefix`, `label` | selected | as above | `role=tablist/tab/tabpanel`, roving tabindex, ArrowLeft/Right, Home, End |
| `x-ui.pagination` | `paginator` (`paginate()` / `simplePaginate()`), `label` | first/last page disabled, current page | below 640 px only Sebelumnya / Berikutnya + "Halaman x dari y" | `nav` label, `aria-current="page"`, "Halaman" read before numbers. URLs come from `$paginator->url()`, so query strings are kept when the caller uses `->withQueryString()`. Shows "Menampilkan 21–40 dari 77". |
| `x-ui.filter-bar` | `action`, `search` (param name or null), `search-label`, `search-placeholder`, `reset-href`, `active` (count), `id`; slot = filter fields | open / closed drawer | inline from 768 px; **below 768 px the same fields open in a right drawer** with a sticky **Terapkan** | One GET `<form>`, so parameter names are unchanged; the drawer is `role="dialog"` while open, traps focus, Esc closes and returns focus |
| `x-ui.dl` | `items` [label ⇒ value] (null hidden), `inline` | — | label/value side by side from 640 px | `<dl>` |
| `x-ui.card` | `title`, `href`, `image` (only a real image), `image-alt`, `heading-level`; `footer` slot | image fails → media area hidden | grid by the caller | **gallery view only** (guardrail 3); never a placeholder image |
| `x-ui.avatar` | `src`, `name`, `size` xs/sm/md/lg/xl, `square` (logos), `decorative`, `alt` | photo; **initials** when there is no photo, or when the URL is the generated `ui-avatars.com` placeholder (it is never requested); photo fails → initials | — | `role="img"` + name for initials; decorative hides it when the name is printed next to it. Real user photos are always shown as they are. |

### Feedback and overlays

| Component | Props / variants | States | Responsive | Accessibility |
|---|---|---|---|---|
| `x-ui.alert` | `tone` info/success/warning/danger, `title` | — | — | icon + text; danger uses `role="alert"`, the others `role="status"` |
| Toast | `window.showNotification(msg, type)` | success, error, info, warning; auto-dismiss | width min(24 rem, 100vw − 2 rem) | see section 1 |
| `x-ui.modal` | `id`, `title`, `drawer` (side panel); `footer` slot | open/closed | full-width buttons below 640 px | native `<dialog>` via `showModal()`: focus moves in, background is inert, Esc closes, focus returns to the trigger, backdrop click closes, page scroll locked. Open with `data-sg-open="{id}"` or `$dispatch('sg-open-modal', '{id}')`; close with `data-sg-close`. |
| Confirm dialog | `x-ui.confirm-dialog` (rendered once in the app layout); `<form data-sg-confirm="Pesan" data-sg-confirm-title data-sg-confirm-label data-sg-confirm-tone="primary">` or `await window.sgConfirm({...})` | cancel / confirm | stacked buttons below 640 px | Focus starts on **Batal**; Esc and backdrop click cancel. On confirm the original form is re-submitted with `requestSubmit(submitter)`, so the submit button's `name`/`value` and every field are sent unchanged. Without the dialog (standalone pages) it falls back to `window.confirm`. |
| `x-ui.empty-state` | `title`, `description`, `illustration` (no-applications · no-saved-projects · no-documents · no-notifications · no-search-results), `heading-level`; `action` slot | — | centred, max 28 rem | illustration `alt=""` |
| `x-ui.loading` | `label`; `lines` > 0 renders a static skeleton | — | — | `role="status"`; no shimmer; the spinner stops under reduced motion |
| `x-ui.stepper` | `steps` (labels), `current` (server), **or `model`** (the page's existing Alpine variable, e.g. `step`) | done, current, upcoming | progress track; step labels from 640 px | "Langkah 2 dari 4 · Label" in a polite live region; `aria-current="step"`. With `model`, the page's own stepper logic is not touched. |

### Icons

`<x-icon name size label>` (Phase 2) remains the only icon helper: 71 Lucide icons from `public/assets/icons/ui/sprite.svg`. Without `label` an icon is decorative. No emoji, Font Awesome or Boxicons.

## 3. Status vocabulary (`app/Support/StatusVocabulary.php`, D-02)

`StatusVocabulary::resolve($domain, $value)` returns `label`, `tone`, `icon`, `known`. An unknown value is shown as it is (humanised, neutral, `circle-help`); a status is never invented.

| Domain | Stored value → label (tone, icon) |
|---|---|
| application | pending Menunggu (neutral, hourglass) · reviewed Ditinjau (info, eye) · accepted Diterima (success, circle-check) · rejected Ditolak (danger, circle-x) |
| job_application | as application + shortlisted **Terpilih** (info, user-check); Phase 21 adds the values the company pipeline writes: new **Menunggu**, reviewing **Ditinjau**, interview **Wawancara** (info, message-square-text), offer **Penawaran** (info, send), hired **Direkrut** (success, circle-check) |
| problem | draft Draf (neutral, pencil) · open Dibuka (success, send) · in_progress Berjalan (info, refresh-cw) · completed Selesai (success, check) · closed Ditutup (muted, lock) |
| project | active Berjalan · on_hold Ditunda (warning, clock) · completed Selesai · cancelled **Dibatalkan** (muted, circle-dashed) |
| milestone | pending Menunggu · in_progress Berjalan · completed Selesai · delayed Terlambat (warning, triangle-alert) |
| report | pending Menunggu · reviewed Ditinjau · approved Disetujui (success, circle-check) · revision_needed Perlu Revisi (warning, circle-alert) |
| document | pending Menunggu · approved Disetujui · rejected Ditolak |
| verification | pending_verification Menunggu Verifikasi · verified Terverifikasi (success, badge-check) · rejected Ditolak · needs_review Perlu Ditinjau (warning, circle-alert) |
| job_posting | draft Draf · posted Dibuka · closed Ditutup · archived Diarsipkan (muted, folder-open) |
| friend | pending Menunggu · accepted Terhubung (success, user-check) · rejected Ditolak · blocked Diblokir (danger, lock) |

Contrast of every tone: 6.56–9.37:1 (`reports/phase2/contrast.md`; warning 6.85:1).

## 4. Old pieces: kept, replaced, deprecated

| Old piece | Decision | Note |
|---|---|---|
| `layouts/app.blade.php` inline `<style>` (Inter/Outfit, scrollbar, avatar sizes, hero h1 colour, `.text-secondary`) | Replaced | The rules still used by pages (`.avatar-*`, `.avatar-circle`, `.marketplace-hero-* h1/h2`, `.text-secondary`) moved verbatim to `legacy-overrides.css`; the font and scrollbar rules were dropped. |
| `NotificationManager` (inline in the layout) | Replaced, API kept | Now `resources/js/student-go/toast.js`; `window.showNotification` and `window.notificationManager` unchanged. |
| `notificationDropdown()` global | Replaced | `Alpine.data('sgNotifications')`; same endpoints. |
| `karsa-logo.png` in header and footer | Replaced in templates (D-01, D-18) | The PNG stays on disk, untouched. |
| `public/favicon.ico` (0 bytes) | Not overwritten | The layouts link the package favicon set instead. |
| Boxicons CDN, Font Awesome CDN, Google Fonts (Inter/Outfit), fonts.bunny.net (Inter) | Removed from the layouts (D-20 a) | The 10 `fa-` icons on `/student/jobs/saved` now use the sprite (no money icon exists, so the salary row has a spacer and a visually hidden "Gaji:"). |
| `partials/auth-navbar.blade.php` | Kept, unused | Not included anywhere (verified again in Phase 6); the auth template has its own compact header. |
| `partials/pagination.blade.php` (AJAX paginator of browse problems) | Kept | Page-specific (Phase 8). `x-ui.pagination` is the paginator for migrated pages; Laravel's default `->links()` view was **not** changed globally. |
| Page-level Google Fonts links (Space Grotesk on 8 pages, Lora/Merriweather on home) | Kept | Page bodies are migrated by their phases. |
| `font-family: 'Outfit'` / `'Inter'` in 20 un-migrated views | Shimmed | `legacy-overrides.css` maps these two names to the local Plus Jakarta Sans files (no network); remove each alias when no view names it. |

## 5. Responsive and accessibility results (Phase 3)

| Check | Result |
|---|---|
| Widths 1440 / 1366 / 1024 / 768 / 390 / 360 | Gallery: no page-level horizontal overflow at any width. Shell: the 768 px overflow caused by the old navbar (28 of 29 authenticated pages, 821–870 px) is gone. Remaining overflows belong to page bodies and existed before (see the Phase 3 report). |
| Keyboard | Skip link first, visible, moves focus to `<main>`; focus ring on every header stop and 40 consecutive gallery stops; Esc closes the bell, account menu, mobile menu, dropdown, modal, confirm and filter drawer and returns focus. |
| Screen-reader names | Bell "Notifikasi, 3 belum dibaca"; account "Menu akun {nama}"; menu toggle "Buka menu"/"Tutup menu"; wordmark "Student Go, ke beranda". |
| Touch | 44 px targets under `pointer: coarse` (buttons, icon buttons, inputs, selects, pagination, mobile menu rows). |
| Reduced motion | Transitions and the spinner are neutralised (computed duration 0.01 ms). |
| Contrast | All new pairs ≥ 4.5:1 for text and ≥ 3:1 for UI parts (table in the Phase 3 report). |

## 6. Page-specific: homepage (Phase 4, 2026-10-31, revised)

Classes `.sg-home-*` (CSS section 'Phase 4' in `components.css`) are used only by `resources/views/home/index.blade.php`; they compose shared pieces rather than replace them: `x-ui.button`, `x-ui.summary`, `x-ui.chip`, `x-ui.status` (domains `problem`, `application`, `project`), `x-ui.table`, `x-ui.empty-state`, `x-icon`, `.sg-meter`, `.sg-figure`, `.sg-select`/`.sg-label`/`.sg-field`. The map panel is an `aria-live` region; Enter or Space on a marker selects it and moves focus to the panel title.

**Hero (AD-11).** `resources/views/home/components/hero.blade.php` + `resources/css/student-go/hero.css` (imported by `app.css` after `components.css`) + `resources/js/student-go/hero-video.js` (`initHeroVideo()`, called from `app.js`). `.sg-hero` (video, poster, scrim, h1, lead, actions, `.sg-hero__sentinel`; no pause control, see AD-11). The navbar adds `.sg-header--hero.is-over-hero` and `data-sg-header` on the `home` route only; the script removes `.is-over-hero` once the sentinel passes under the header. `html:has(.sg-header--hero)` sets `scroll-padding-top: 5rem` so anchors and keyboard focus do not land under the fixed header. Tokens: `--sg-on-media*`, `--sg-hero-*` in `tokens.css`.

## 7. Page-specific: about, contact, public institution profile (Phase 5, 2026-10-31)

Classes live in `resources/css/student-go/public-pages.css` (imported by `app.css` after `hero.css`; existing tokens only). They compose shared pieces: `x-ui.page-header` (Contact), `x-ui.summary`, `x-ui.avatar` (square, initials when there is no logo), `x-ui.chip`, `x-ui.status` (domain `problem`), `x-ui.empty-state`, `x-ui.dl`, `x-ui.button`, `x-icon`, `.sg-marker`, `.sg-prose`, `.sg-figure`.

| Class group | Page | Pattern |
|---|---|---|
| `.sg-about-hero*`, `.sg-about*` | `/about` | Article. Header `.sg-about-hero`: light band, text + role links left, a framed photo with an overlapping inset (≥ 640 px) on the contour backdrop right. Numbered sections (`.sg-about__num`, earth accent) with a 4/8 split from 1024 px. Three role columns, each a vertical timeline (`.sg-about__steps`: ring per step, line to the next, last ring filled). A wide + narrow photo pair. Transparent team portraits on 4:5 tiles (`.sg-about__portrait`). |
| `.sg-contact*`, `.sg-faq*` | `/contact` | Two columns from 1024 px: email rows (name + `mailto:` link, 44 px on touch) and an FAQ of native `<details>` (chevron rotates; transition off under reduced motion). |
| `.sg-org*`, `.sg-org-problem*` | `/institution/{id}` | Organisation header (square avatar, kicker, h1 with `overflow-wrap:anywhere` and `text-wrap:balance`, meta, summary), main column + 18 rem data rail from 1024 px, problem rows (main + facts `dl`; facts in two columns below 768 px), grouped completed work, quiet empty-state panel. Reusable by Phases 20 and 25 for the organisation header. |

## 8. Page-specific: authentication (Phase 6, 2026-10-31)

CSS: `resources/css/student-go/auth.css` (imported by `app.css` after `public-pages.css`; existing tokens only) + the shared `.sg-password` block at the end of `components.css`. JS: `resources/js/student-go/auth.js`, registered from `app.js` on `alpine:init` (`registerAuth`). Partials: `resources/views/auth/components/{field-error,aside-roles,wizard-feedback,wizard-done}.blade.php`.

| Piece | Used by | Pattern |
|---|---|---|
| `Alpine.data('sgAuthForm')` | login, forgot, reset, verify (resend) | Native submit (same action/method, so server redirects and flash are unchanged). Validation after blur on a changed field, then live while typing; on submit all fields are checked, the first invalid one gets focus and no request is sent. Submit button shows a spinner + `aria-busy` and is disabled while the request runs; re-enabled on `pageshow` (back button). |
| `Alpine.data('sgRegister', cfg)` | the three registrations | One wizard for 3- and 4-step forms with `x-ui.stepper model="step"`. `cfg.steps` = field names per step; `validateUrl` (student only: `validation.student.step`, unchanged) is called before leaving a step; `submitUrl` = the same submit route via `fetch` (JSON, `Accept` + `X-Requested-With`). 422 errors are placed under each field, listed in a summary (`role=alert`) and the wizard jumps to the first step with an error. `mode: 'redirect'` follows `redirect_url` (student, as before); `mode: 'panel'` shows `wizard-done` (institution, company) instead of `alert()`. Enter on a non-final step = Lanjutkan. Focus moves to the step heading (`data-step-heading`, `tabindex=-1`) after each step change, after the `x-show` paint. |
| `Alpine.data('sgRegions', cfg)` | institution step 2; **reusable by Phase 18** | Province → regency select. `cfg.url` contains `__ID__` (here `/api/public/regencies/__ID__`, response shape unchanged). Each change aborts the previous request (`AbortController`) and ignores late responses (sequence number); the regency select is disabled with a spinner while loading; a polite live region (`{id}-status`) says "27 kabupaten/kota dimuat untuk Jawa Barat." or offers **Coba lagi** on failure. Old input (`old('regency_id')`) is re-selected after the options render. |
| Field errors | all auth forms | `x-ui.field` server error (`{id}-error`) on reload; client/422 errors in `[data-sg-error-for=name]` (`{name}-live-error`), linked by `aria-describedby`; the server message is hidden as soon as the field is re-validated. Red borders only with `data-touched` (inputs, selects, and the dropzone via `:has()`). |
| `.sg-role-list` / `.sg-role-row` | `/register` | Three bordered rows in one frame: outline icon (graduation-cap · map-pin · briefcase), "Daftar sebagai …", one sentence about the main task, chevron. |
| `.sg-wizard*` | registrations | Stepper rule, step head (h2 + one line), optional `.sg-wizard__group` with an h3, actions row (Kembali secondary left, primary right; stacked full width below 640 px). |
| `.sg-auth-aside*` | login (roles), registrations ("Yang perlu disiapkan") | One heading + divided rows with a small outline icon; text sits on the panel's plain ground (`outline` of the same colour), contour lines around it. |
| `.sg-auth-done` | institution, company | Success panel (`role=status`, focused): server message, next step, **Masuk** (primary) and **Ke beranda** (`redirect_url`). |

## 9. Page-specific: student dashboard (Phase 7, 2026-10-31)

CSS: `resources/css/student-go/student-dashboard.css` (imported by `app.css` after `auth.css`; existing tokens only). The page composes shared pieces: `x-ui.page-header` (greeting h1, major · university, Telusuri Masalah primary + Aplikasi Saya secondary), `x-ui.button` (tertiary for every row action), `x-ui.status` (domains `milestone`, `application`), `x-ui.avatar` (requester, initials when no photo), `x-ui.summary` (network line), `x-ui.table` (recent applications, stacked below 640 px), `x-ui.empty-state` (`no-applications`), `x-icon`.

| Class group | Pattern |
|---|---|
| `.sg-dash`, `.sg-dash__grid`, `.sg-dash__rail` | One column below 1024 px; from 1024 px main column 2fr + rail 1fr. DOM order header → queue → rail (recommendations, network) → projects → applications, so keyboard order is header → queue → recommendations; the rail spans the three main rows (`grid-template-rows: auto auto 1fr`). |
| `.sg-dash__queue`, `.sg-dash-queue__*` | The page's only bordered panel ('Langkah berikutnya'). Row = 36 px icon tile (or avatar) · title + meta · end slot (status + tertiary action, or the Terima/Tolak forms). End slot wraps under the text below 640 px. The first milestone row carries the kicker 'Tenggat terdekat'. Empty queue: one quiet line, no illustration. |
| `.sg-dash-reco__*` | Recommended problem rows (max 3): title, institution, regency/province with `map-pin`, 'Ditutup {tanggal}', tertiary 'Lihat' (+ visually hidden title). No image (guardrail 7). |
| `.sg-dash-project*` | Active project row with a native `<progress>` (brand fill on gray-200, 6.04:1) and the % as text. |
| `.sg-dash__head`, `.sg-dash__more`, `.sg-dash__h2`, `.sg-dash__none`, `.sg-dash__empty` | Section head (h2 + 'Lihat semua', 24 px min, 44 px on touch), quiet empty line, dashed frame around the empty state. |

Known shared-CSS issue (not changed here): `.sg-empty img, .sg-empty svg { width: 10rem }` also enlarges an icon inside a button placed in the empty state's `action` slot. Phase 7 avoids it by using a button without an icon; a fix belongs to the shared component file (Phase 27 or the next phase that owns `components.css`).

## 10. Page-specific: student project discovery (Phase 8, 2026-10-31)

CSS: `resources/css/student-go/student-browse.css` (imported by `app.css` after `student-dashboard.css`; existing tokens only). Page: `/student/browse-problems` (and its alias `/student/problems`). It composes shared pieces: `x-ui.page-header` (h1 + one sentence, no hero), `x-ui.search`, `x-ui.select`, `x-ui.field`, `x-ui.checkbox`, `x-ui.table` (stack mode), `x-ui.chip` (SDG taxonomy), `x-ui.status` (domain `application`), `x-ui.card` (gallery), `x-ui.pagination`, `x-ui.empty-state` (`no-search-results`), `x-ui.loading`, `x-ui.button`, `x-icon`, and the shared Alpine `sgFilterBar` (drawer: focus trap, Esc, focus return). Page Alpine: `sgBrowseView` (view tabs), `sgBrowseRegions` (province → regency), `mapView` (Leaflet).

| Class group | Pattern |
|---|---|
| `.sg-browse`, `.sg-browse__layout`, `.sg-browse__main` | One column below 1024 px; from 1024 px filter panel 15.5rem + results. `.sg-browse__main` is a size container (`sg-browse-main`) so the table can react to the width of the results column, not the viewport. |
| `.sg-browse__toolbar`, `__search`, `__sort`, `__filter-toggle` | One search field (`form="browse-filters"`) + 'Cari', 'Urutkan' select (submits on change), 'Filter (N aktif)' button below 1024 px. |
| `.sg-browse__active`, `.sg-browse__chips`, `.sg-browse-chip`, `.sg-browse__clear` | Active filters as links that remove one filter (page back to 1; removing the province also removes the regency) + 'Hapus semua filter' (keeps `view`). 32 px tall, 44 px on touch. |
| `.sg-browse__bar`, `.sg-browse__count`, `.sg-browse-views`, `.sg-browse-views__tab` | Count `M masalah dibuka`, or `N dari M masalah dibuka sesuai filter` when a filter is active (D-05). View switch Daftar / Galeri / Peta as `role=tablist` (arrow keys, Home/End); state in `?view=` (legacy `grid` = Galeri) and `localStorage` key `sgBrowseView`; default list. |
| `.sg-browse-filters*` | One GET form `#browse-filters`. ≥ 1024 px: sticky side panel, every change applies at once (no 'Terapkan'). < 1024 px: right-hand drawer (`role=dialog` while open) with sticky 'Terapkan' / 'Atur ulang'. SDG list in a `<details>` with 'N dipilih'. |
| `.sg-browse-table`, `.sg-browse-row*`, `.sg-browse-flag`, `.sg-browse-num`, `.sg-browse-table__sort` | List view: one row per problem (Masalah · Instansi · Lokasi · Kuota · Tenggat · Kesulitan). Title link, own application status, Mendesak/Unggulan (icon + text, gray-600), SDG chips; full institution name; regency over province; deadline + 'N hari lagi'; difficulty as the neutral `.sg-meter`. Rows **stack** below 640 px (shared `x-ui.table`) **and** whenever the results column is narrower than 50rem (container query: 640–850 px without the sidebar, 1024–1150 px with it), so the title column is never squeezed. Titles and chips wrap with `overflow-wrap: break-word` (never letter by letter). |
| `.sg-browse-grid`, `.sg-browse-card*` | Optional gallery: `x-ui.card` per problem, image only when a `problem_images` record exists (hidden again if it fails to load), facts as a `<dl>`. |
| `.sg-browse-map*`, `.sg-browse-pin`, `.sg-browse-cluster`, `.sg-browse-popup*` | Leaflet panel (37.5rem; ≥ 22rem / 70vh on phones), contained stacking so the sticky header stays above it, note that markers sit at province points, package pin, square brand cluster (white on brand 7.84:1), escaped popup with 'Lihat masalah'. Pager hidden in map view. |
| `.sg-browse__empty`, `.sg-browse__pager` | Dashed frame for 'Tidak ada masalah yang cocok' (with 'Hapus semua filter'), 'Belum ada masalah yang dibuka', and the out-of-range page state 'Halaman N tidak ada' with 'Ke halaman 1' (keeps filters). |
