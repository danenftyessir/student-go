# Phase 20 — Institution Profile & Verification: completion report

Date 2026-10-31 · branch `feat/student-workspace` · uncommitted (D-27) · `PHASE_TRACKER.md` not edited (parallel sessions). **Status for consolidation: COMPLETE.**

## Changes
| File | Change |
|---|---|
| `resources/views/institution/profile/index.blade.php` | I07: organisation profile (`.sg-org*` from Phase 5), facts grid (`dl`, wraps), PIC, verification aside. Edit Profil and Lihat Profil Publik kept. Summary shows non-zero counts only. |
| `resources/views/institution/profile/edit.blade.php` | I08: three sections in the registration order, `x-ui.*` fields, `sgRegions`. Same PUT/multipart contract, 12 names, ids `logo-input`/`province-select`/`regency-select`. A free-text stored type is selected inside "Tersimpan saat ini" (D-06, no data change). |
| `resources/views/institution/verification/upload.blade.php` | I09: global status panel plus a 4-document checklist using `x-ui.upload` and a file chip. Status comes from the stored `ai_status`/`human_status` only. Copy is neutral. Trigger form unchanged. |
| `app/Support/StatusVocabulary.php`, `tests/Feature/Phase3/ComponentsTest.php` | Additive: `verification.active`/`suspended` (as the D-09 note asked) and a new `verification_document` domain. |
| `app/Http/Controllers/Institution/VerificationController.php` | Upload flash text only (it named the removed "Mulai Verifikasi AI" button). |
| `resources/css/student-go/institution.css` | Phase 20 section, existing tokens only. |
| `tests/Feature/Phase20/InstitutionProfileVerificationTest.php` | 12 tests. |

## Evidence (`reports/phase20/`)
- Tests: Phase 20 + Phase 3 + Phase 1 binding pass 30/30. Full suite 247/250. The 3 failures are all in `tests/Feature/Phase10/`, a parallel session's in-progress work; none of them touches Phase 20 files.
- Captures: `after/` (3 accounts: 59-char email, 76-char name, pending × 3 pages × 6 widths). 54/54 loaded with status 200, 0 overflow, 0 JS errors, 1 `<h1>`, the same input count as `before/` (12 and 4).
- `checks/interaction-checks.json`: Tab reaches all 4 upload inputs, with a focus ring on the dropzone. Chip, keyboard remove (focus goes back to the input), client size warning, real PDF upload ("1 dari 4"), logo preview, regency reload: all pass, no AI claim. Test file and DB row removed (`throwaway-cleanup.json`). The file went to the local disk; nothing was written to Supabase.
- Routes 203 = 203; `public/` unchanged; no new asset. `file-check` is not in the sprite, so `clipboard-check`/`check` are used instead.

## Open
- D-09 is still OPEN. `/institution/verification/status` (out of scope) still says "AI", and in non-production the analysis is a mock (`AIDocumentVerificationService::callClaudeAPI`).
- Pre-existing defect: `ProfileController::update` catches `ValidationException`, so field errors show as one generic message (old input is kept). Not changed, because it is backend logic.
