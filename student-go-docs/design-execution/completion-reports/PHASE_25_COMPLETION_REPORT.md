# PHASE 25 — Company Profile: completion report

| Field | Value |
|---|---|
| Date / branch | 2026-10-31, `feat/student-workspace`, uncommitted (D-27); `PHASE_TRACKER.md` not edited (parallel sessions) |
| Recommended status | **COMPLETE** |
| Dependencies / gates | Phase 3 COMPLETE. Phase 1 COMPLETE. Phase 20 recommended COMPLETE in its report. D-06 ANSWERED (option a + additive columns, done in Phase 1): applied as-is, no enum or data change, no migration |

## Changes

| File | Change |
|---|---|
| `resources/views/company/profile/index.blade.php` | C07: organisation profile (`.sg-org`, same as institution profile). No hero. Logo only if stored, else initials. Facts grid (industri, jumlah karyawan, tahun berdiri), kontak dan alamat, media sosial (only if filled), info akun (status verifikasi, email masuk, bergabung, diperbarui). Summary shows real non-zero counts only. Removed: 'Talenta Tersimpan' and 'Profil Dilihat' (the controller never sends them, so they always read 0) and the dead 'Ajukan Verifikasi' button (no form). Added 'Lihat Profil Publik' (existing route `company.public`, as on the institution profile) |
| `resources/views/company/profile/edit.blade.php` | C08: 3 numbered sections (Informasi dasar, Kontak dan alamat, Media sosial) with `x-ui.*` and the sticky save bar from Phase 20. Same PUT/multipart action and same field names. Added the registration fields that `update()` already validates: `founded_year`, `city`, `province_id`. Industri and jumlah karyawan use the registration options; a stored value outside the list is selected under 'Tersimpan saat ini' (D-06). Email falls back to the account email; size falls back to `employee_count`. Logo: same `logo` field, preview, delete via the same DELETE (now `sgConfirm`); the hint now matches the server (JPG/PNG/WEBP, 5 MB; the old view said 2 MB) |
| `resources/css/student-go/company.css` | Phase 25 section (`.sg-co-profile*`, `.sg-co-facts*`, `.sg-co-section*`, `.sg-co-grid-2`, `.sg-co-logo*`, `.sg-co-savebar`) mirroring the Phase 20 rules |
| `tests/Feature/Phase25/CompanyProfileTest.php` | 7 tests (60 assertions) |
| `ASSET_INVENTORY.md` | Section 32 |

## Verification

- `php artisan test`: 273/273 (Phase 25: 7/7; Phase 1 J5 tests still pass). `npm run build` OK.
- Field parity (test): the edit form's names equal the 19 validated fields; the industri and jumlah karyawan option values equal the registration form's.
- Live checks on a throwaway seeder-shaped company (`checks/interaction_checks.json`, 17/17):
  - email, size, industry, province, year and city are prefilled;
  - validation error keeps `old()` values;
  - an unchanged save keeps email and size (DB verified);
  - tagline and size edits save;
  - logo preview works and clears;
  - no overflow at 6 widths, no console errors, no 4xx/5xx.
  - The throwaway user and company were deleted (`checks/throwaway-cleanup.json`).
- Logo upload: verified in PHPUnit with the storage service mocked (path stored, PDF rejected). No file was sent to Supabase Storage.
- Captures: `reports/phase25/before|after`, 6 widths. After: 24/24 at status 200, 0 overflow, 0 errors.

## Notes

- Seeded `employee_count` values (`51-100`, `21-50`, `101-200`, `11-20`) are not registration options. They are shown and kept as stored. Normalising them would be D-06 option (b), which needs approval.
- `company/profile/public` (outside this phase's routes) still uses `company-profile.jpg`.

## Proposed tracker row

`| 25 | Company Profile | COMPLETE | 3 | 1, 20 | D-06 | 2026-10-31 | feat/student-workspace (no commit) | C07 organisation profile (no hero, logo only if stored, real counts, invented stats and dead verification button removed); C08 3-section shared form, registration fields + options, stored values always selected (D-06), email/size prefilled and kept after save. Tests 7 new, suite 273/273; live checks 17/17; throwaway account removed. Report: completion-reports/PHASE_25_COMPLETION_REPORT.md. |`
