# Video sources

Every moving image the site serves. Recorded 2026-10-31 at the close of Phase 4 (decision AD-11 in `student-go-docs/redesign-execution/DECISIONS_AND_OPEN_QUESTIONS.md`).

## Homepage hero loop

| Field | Value |
|---|---|
| Files | `public/video/hero-loop-desktop.webm`, `hero-loop-desktop.mp4`, `hero-loop-mobile.webm`, `hero-loop-mobile.mp4`; posters `hero-loop-desktop.{webp,jpg}`, `hero-loop-mobile.{webp,jpg}` |
| Origin | Original work made for this project (the user, 2026-10-31: 'Dibuat untuk proyek ini'). No stock footage, no third-party embed. |
| Rights | Owned by the project. No licence or credit line required. |
| People | None. The loop shows the Indonesian archipelago outline, a route line and contour lines on a dark ground. |
| Content | Decorative. It carries no information that is not also in the text, so the `<video>` and poster have `aria-hidden="true"` / `alt=""`. |
| Desktop | 1920×1080, 24 fps, 12.0 s. WebM VP9 2,736,578 B (≈ 1.82 Mbit/s); MP4 H.264 4,792,887 B (≈ 3.20 Mbit/s). No audio track. |
| Mobile | 720×1280 (portrait), 24 fps, 12.0 s. WebM VP9 1,212,085 B; MP4 H.264 1,732,253 B. No audio track. |
| Posters | Desktop WebP 81,012 B / JPEG 96,602 B; mobile WebP 33,688 B / JPEG 58,938 B. |
| Budgets (asserted by `tests/Feature/Phase4/HeroVideoTest.php`) | desktop WebM ≤ 3.5 MB, MP4 ≤ 5 MB; mobile WebM and MP4 ≤ 2 MB; each poster ≤ 120 KB |
| Loading | `resources/js/student-go/hero-video.js`: the markup has no `src`; the script picks mobile (≤ 767 px) or desktop and WebM (VP9 'probably') or MP4, and only when reduced motion is off and Save-Data is off. It pauses when the tab is hidden or the hero leaves the viewport, and resumes when it returns. There is no on-page pause control (AD-11; known deviation from WCAG 2.2.2). |
| Measured (Chromium, 2026-10-31) | 1440 px: WebM 2,736,726 B transferred; 390 px: WebM 1,212,233 B; reduced motion: no video request. |
| Contrast | White h1 over the brightest pixel of the text area in any frame (sampled at 2 fps, with the 0.35 scrim): 17.1:1; lead text (88% white): 13.4:1. |

Source files of the animation (project files, renders) are not in the repository. If the loop is changed, keep the file names or update `hero.blade.php`, re-run the budgets test, and add a row here.
