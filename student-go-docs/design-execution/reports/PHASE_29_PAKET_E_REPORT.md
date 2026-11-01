# Phase 29, paket E — Perusahaan: profil publik dan ubah lowongan

Tanggal: 2026-11-01. Branch: dimulai di `dev` (pilihan pengguna); working tree kemudian dipindah sesi lain ke `feat/institution-manage`, commit yang sama `000523d`. Tidak ada commit.

Catatan protokol: pengguna meminta paket ini dijalankan dari prompt paket E (`reports/ASSET_ENRICHMENT_PROMPTS.md`) sebelum work order Fase 29 ada. Work order dibuat sesi lain selama pengerjaan; pekerjaan lalu dicocokkan dengan F.E dan L.E di `phases/PHASE_29_UNMAPPED_PAGES_AND_ENRICHMENT.md`. Status baris tracker tidak di-set IN_PROGRESS selama pengerjaan.

## Berkas yang berubah

| Berkas | Perubahan |
|---|---|
| `resources/views/company/profile/public.blade.php` | Ditulis ulang pada pola `.sg-org`: kepala dengan logo atau inisial, ringkasan sebaris (angka nol disembunyikan), lowongan dibuka sebagai daftar bergaris, data perusahaan dan kontak berikon `mail`/`phone`/`globe` di rel kanan, media sosial. Tanpa foto hero, `<style>` dan hex. |
| `resources/views/company/jobs/edit.blade.php` | Ditulis ulang pada wizard create Fase 23: `x-ui.page-header`, `x-ui.stepper`, partial langkah bersama, rail/modal pratinjau bersama, validasi per langkah sesuai aturan server `update()`, galat server dan `old()` dipetakan ke langkahnya. Tanpa foto hero, `<style>`, hex dan `alert()`. |
| `resources/views/company/jobs/components/wizard-steps.blade.php` (baru) | Lima langkah wizard, dipakai create dan edit. Mode edit menambah pilihan Status dan menampilkan gaji tersimpan sebagai teks. |
| `resources/views/company/jobs/create.blade.php` | Langkah inline diganti `@include` partial. Keluaran langkah identik (13.658 karakter setelah normalisasi spasi); satu-satunya beda bita adalah `"` → `&quot;` di catatan langkah 5. |
| `resources/views/company/jobs/components/preview.blade.php` | Parameter opsional `$note`. Tanpa parameter, keluaran sama seperti sebelumnya. |
| `app/Http/Controllers/Company/JobPostingController.php` | Hanya `edit()`: data form sama dengan `create()` (saran departemen Indonesia, `JOB_TYPE_LABELS`, nama SDG `sdg_label()`, kategori `id => nama`). Validasi dan `update()` tidak diubah. |
| `resources/css/student-go/company.css` | Dua blok baru: `.sg-co-public*` dan `.sg-co-wizard__fixed*`/`__cancel`. |
| `tests/Feature/Phase29/CompanyPackageETest.php` (baru) | 10 tes, 104+ asersi. |
| `ASSET_INVENTORY.md`, `PHASE_TRACKER.md` | Bagian "Phase 29, paket E"; entri E di Notes dan baris Update log. |

## Kriteria L.E

| Kriteria | Bukti |
|---|---|
| Jumlah rute sebelum = sesudah | 203 = 203; `git diff routes/` kosong. |
| Form edit tidak berubah dan terisi | `phase29-E/form-contract.txt`: `<form>` dengan `@csrf` + `@method('PUT')`, tanpa field bernama di kedua versi; formulir JS `POST` ke `company.jobs.update` dengan `_method=PUT`. Kunci payload yang terekam di peramban identik sebelum/sesudah (16 kunci). Tidak ada `enctype` (sebelum maupun sesudah). Nilai tersimpan terisi (tes `test_edit_keeps_stored_values_that_are_outside_the_lists`, `metrics.json` `edit_formData`). Profil publik tidak punya form. |
| Suite lulus + tes baru | `php artisan test`: 452/452, 4.376 asersi (awal sesi di branch ini: 358/358). Tes paket E 10/10. |
| `npm run build` | Lulus (CSS 253,15 kB, gzip 41,04 kB). |
| 0 `<style>`, 0 hex | `public`, `edit`, `wizard-steps`: 0 dan 0. |
| Tanpa overflow 360/390/768/1440 | Sesudah: 20/20 halaman `scrollWidth = innerWidth` (`/company/1`, `/77`, `/120`; edit 618 dan 623). Sebelum: edit 475 px di 360 dan 490 px di 390. |
| Tangkapan layar sebelum/sesudah | `phase29-E/before/` (16), `phase29-E/after/` (20 + 2 tangkapan uji submit). |
| Tanpa label Inggris | Probe teks pada 20 halaman sesudah: 0 temuan. `Full-time`/`Internship` dll. jadi `Penuh waktu`/`Magang`; status `published` jadi Draf/Dibuka/Ditutup; "Negotiable" tidak lagi masuk pratinjau. Data seeder berbahasa Inggris (judul lowongan, departemen "Economic Empowerment") tampil apa adanya. |
| Status dari `StatusVocabulary` | Pilihan status edit: `StatusVocabulary::label('job_posting', …)`; lencana terverifikasi: `x-ui.status domain="verification"`. |
| Profil publik `.sg-org`, tanpa hero, logo/inisial, ikon `globe` dan `phone`, tanpa field baru | Tes `test_public_profile_is_the_organisation_pattern_without_hero_photo`, `test_public_profile_shows_only_public_data`. Field: tagline, industri, jumlah karyawan, terverifikasi, deskripsi, ≤5 lowongan dibuka, email perusahaan, telepon, situs web, lokasi, media sosial, dua angka lowongan (semua dari versi lama); kabupaten/kota, provinsi, "Terdaftar sejak" (dari profil instansi publik). Tidak tampil: email akun, alamat, kode pos, tahun berdiri. |
| Edit memakai partial dan stepper yang sama dengan create | `wizard-steps.blade.php` di-include keduanya; tes `test_create_and_edit_share_the_step_partial` (langkah 2–4 identik per bita). |
| Edit langsung dipulihkan persis | Lowongan 618 (milik perusahaan 1, akun uji). Diuji dua kali (sebelum dan sesudah refaktor partial): wizard diisi sampai "Simpan perubahan", POST 16 kunci `_method=PUT`, mendarat di `/company/jobs/618` dengan pesan sukses. Baris dipulihkan dari potret: 31/31 kolom sama, `updated_at` termasuk (`phase29-E/job618-restore-proof.json`). |
| Hash `public/` tidak berubah | `baseline/asset-hashes.txt` tidak ada di branch ini. Metode: blob hash git dari 228 berkas terlacak di luar `public/build/` dibanding HEAD: SHA-256 gabungan sama (`d41322c9…`). |
| `company-profile.jpg`, `company3.jpg` Deprecate, berkas tetap ada | `ASSET_INVENTORY.md` bagian paket E; `grep` di `resources/` dan `app/`: 0 rujukan; kedua berkas ada dengan hash tidak berubah. |

## Penyimpangan dan catatan

- **`salary_range`**: kuncinya tetap dikirim seperti dulu, tetapi nilainya kini teks gaji Indonesia (`Rp 2.000.000 – Rp 3.500.000 per bulan`, atau kosong), bukan accessor model (`Negotiable`, `IDR2000000.00 - …`). `update()` tidak membaca kunci ini, jadi perilaku server sama. Isian gaji di edit menjadi teks baca-saja karena form ini memang tidak pernah bisa mengubah gaji.
- **Status**: pilihan lama `published` selalu ditolak validasi server (`in:draft,posted,closed,archived`); kini `posted` (Dibuka). Nilai tersimpan di luar daftar (mis. `archived`) tetap jadi pilihan.
- **Nilai tersimpan di luar daftar** tetap dipertahankan: tipe `internship` huruf kecil (lowongan 623–625) berlabel "Magang" dengan nilai tetap; keahlian di luar daftar centang (mis. "Business Development" pada 618) tampil tercentang di depan daftar.
- **Gangguan DNS basis data** dari server lokal menyebabkan beberapa 5xx sesaat selama pengambilan bukti; halaman 5xx tidak pernah difoto, dan pengambilan diulang sampai bersih (0 retry di run terakhir).
- **Tidak diuji terpisah**: matriks konten panjang (nama 76 karakter) hanya lewat CSS `overflow-wrap: anywhere` yang sudah ada; keyboard, kontras dan reduced motion memakai komponen bersama tanpa pemeriksaan baru.

## Cacat lama yang ditemukan (tidak diperbaiki; perubahan perilaku server)

1. **`benefits` terhapus setiap kali form ubah disimpan.** Form lama maupun baru tidak punya field `benefits`, sedangkan `update()` menulis `'benefits' => $validated['benefits'] ?? null`. Terbukti di uji langsung 618 (dipulihkan). Usul: pertahankan nilai tersimpan bila kunci tidak dikirim, seperti perbaikan gaji 2026-10-31.
2. `update()` membuat ulang `slug` dari judul pada setiap simpan (slug lama berakhiran seeder berubah walau judul sama).
3. Peramban mengirim baris baru textarea sebagai CRLF, sehingga teks multi-baris tersimpan dengan `\r\n` (isi sama setelah dinormalisasi).
4. `sdg_alignment` tersimpan sebagai string (`["1","8"]`) setelah disimpan lewat form; sama seperti form lama.

## Keputusan yang diperlukan

- Perbaiki cacat 1 (dan 2) di `update()`? Ini perubahan perilaku, jadi butuh persetujuan.
