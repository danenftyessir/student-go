# Prompt pelaksanaan — audit pengayaan aset

Tanggal 2026-11-01 · sumber: `reports/ASSET_ENRICHMENT_AUDIT.md`

Setiap prompt di bawah dipakai di **sesi Claude Code baru**, satu prompt per sesi, sesuai urutan. Salin isi blok kode apa adanya.

## Kenapa perlu Prompt 0

Delapan rute lama tidak tercantum di `EXECUTION_ROADMAP.md` (bagian 6) maupun di file fase mana pun:
- `student.applications.tracker`
- `student.projects.show`, `create-report`, `create-final-report`
- `student.jobs.show`
- `institution.projects.manage`
- `institution.verification.status`
- `company.jobs.edit`
- `company.public`

Laporan penyelesaian Fase 19, 20, 23, dan 25 juga menyebutnya "di luar cakupan". Karena itu perintah "Execute Phase N" untuk fase yang sudah ada tidak akan menyentuh halaman-halaman ini. Agen yang patuh pada CLAUDE.md bagian 8 langkah 8 akan berhenti dan bertanya.

Prompt 0 membuat satu fase baru, **Fase 29**, khusus untuk halaman-halaman tersebut. Isinya hanya dokumen, tanpa kode. Prompt 1–8 masing-masing menjalankan satu paket dari fase itu.

Urutan yang disarankan: 0 → E → C → A → B → D → F → (G) → (H).
- G dan H hanya dijalankan bila Anda menyetujui keputusannya.
- Admin tidak diberi prompt, karena belum ada akun admin untuk memverifikasi hasilnya.

Sebelum Prompt 0, sunting blok "Keputusan saya" di dalamnya. Hapus atau ubah baris yang tidak Anda setujui.

---

## Prompt 0 — Perencanaan Fase 29 (hanya dokumen)

```text
Tugas perencanaan, hanya dokumen. Jangan mengubah kode, view, CSS, JS, route, database, .env, atau berkas apa pun di public/.

Baca dulu, dalam urutan ini:
1. CLAUDE.md
2. student-go-docs/design-execution/EXECUTION_ROADMAP.md
3. student-go-docs/design-execution/PHASE_TRACKER.md
4. student-go-docs/design-execution/DECISIONS_AND_OPEN_QUESTIONS.md
5. student-go-docs/design-execution/ASSET_INVENTORY.md
6. student-go-docs/design-execution/reports/ASSET_ENRICHMENT_AUDIT.md (sumber temuan; rujuk ID-nya: C1–C11, U1–U9)
Lalu baca phases/PHASE_25_COMPANY_PROFILE.md sebagai contoh format bagian A–N.

Sebelum mengubah apa pun, jalankan git status. Working tree sudah punya perubahan yang bukan dari tugas ini: CLAUDE.md, empat berkas di student-go-assets/, serta berkas untracked AUDIT.md dan user. Jangan sentuh, revert, atau stash perubahan itu. Tanyakan saya dulu sebelum berpindah branch. Jangan commit.

Latar: delapan rute GET berikut belum masuk sistem desain dan tidak dipetakan ke fase mana pun.
- Roadmap bagian 6 tidak memuatnya.
- Laporan Fase 19, 20, 23, dan 25 menyebutnya di luar cakupan.
- Bukti tampilan dan ukuran DOM ada di laporan audit 3.1.

Rutenya:
- student.applications.tracker   GET student/applications/tracker   view student/applications/tracker.blade.php
- student.projects.show          GET student/projects/{id}          view student/projects/show.blade.php
- student.projects.create-report GET student/projects/{id}/report/create  view student/projects/create-report.blade.php
- student.projects.create-final-report GET student/projects/{id}/final-report/create  view student/projects/create-final-report.blade.php
- student.jobs.show              GET student/jobs/{id}              view student/jobs/show.blade.php
- institution.projects.manage    GET institution/projects/{id}/manage  view institution/projects/manage.blade.php
- institution.verification.status GET institution/verification/status  view institution/verification/status.blade.php
- company.jobs.edit              GET company/jobs/{id}/edit         view company/jobs/edit.blade.php
- company.public                 GET company/{id}                   view company/profile/public.blade.php
Dua rute campuran juga masuk karena isinya ikut dirombak bersama tracker. Keduanya tercantum di Fase 10, tetapi fase itu belum punya laporan penyelesaian:
- student.applications.show      GET student/applications/{id}      view student/applications/show.blade.php
- student.applications.create    GET student/applications/create/{problemId}  view student/applications/create.blade.php

Yang harus dibuat:

1. phases/PHASE_29_UNMAPPED_PAGES_AND_ENRICHMENT.md, dengan format bagian A–N yang sama dengan file fase lain. Fase ini dibagi menjadi paket yang masing-masing dijalankan dengan perintah terpisah ("Execute Phase 29, paket X"):
   - Paket A, Lamaran mahasiswa: tracker, applications/{id}, applications/create/{problemId}. Status lewat App\Support\StatusVocabulary (C8). Grafik "Aktivitas Minggu Ini" mengikuti guardrail 2: dihapus atau diganti angka nyata.
   - Paket B, Proyek aktif mahasiswa: projects/{id} dan dua formulir laporan.
     * Bar progres bergradien diganti pengukur tahap seperti institution/projects/components/stage-meter.blade.php (Fase 19).
     * Tombol unduh sertifikat tetap ada.
   - Paket C, Detail lowongan mahasiswa: student/jobs/{id}. Label Inggris diganti, overflow 390 px diperbaiki (C3).
   - Paket D, Institusi: projects/{id}/manage dan verification/status.
     * Semua formulir milestone, laporan, dan status tetap sama.
     * Ikon shield-check di halaman status (U7).
     * Kata "AI" di halaman status bergantung pada D-09 yang masih OPEN, jadi jangan diubah.
   - Paket E, Perusahaan:
     * company/{id}: pola organisasi .sg-org seperti institution public dan company/profile/index dari Fase 25, tanpa hero foto, logo atau inisial, ikon globe dan phone (U5, U7). company-profile.jpg berhenti dirujuk.
     * company/jobs/{id}/edit: disamakan dengan wizard create dari Fase 23 (komponen dan stepper bersama, PUT dan nama field tetap). company3.jpg berhenti dirujuk dan overflow 390 px hilang.
   - Paket F, Cacat dan polesan:
     * C4: footer menempel di bawah di layouts.app.
     * C5: jarak blok galat login di layouts/auth.
     * C6: dua rujukan placeholder-*.jpg yang tidak ada diganti inisial atau dihapus.
     * C3: tabel data grafik /company/dashboard mendapat overflow-x.
     * U6: ilustrasi untuk lima keadaan kosong tingkat halaman.
     * C1: /student/applications/{id} yang tidak ada harus 404, bukan 500.
     * C2: /student/friends/activities 500.
     C1 dan C2 adalah perubahan perilaku: tulis dulu uji yang gagal, lalu perbaiki. Rute tidak ditambah, dihapus, atau diganti nama.
   - Paket G, Peta wilayah di profil institusi publik (U4). Statusnya bergantung pada "Keputusan saya" di bawah.
     * Pakai helper baru di app/Support yang memetakan dua digit pertama kode provinsi BPS ke tujuh grup di public/assets/illustrations/geographic/indonesia-regions.svg.
     * Grup aktif ditandai .is-active, dengan padanan teks seperti "4 masalah dibuka di Jawa".
     * Hanya angka dari query nyata.
   - Paket H, Foto seed masalah (U2). Menulis ke database dan mungkin ke Supabase Storage, jadi dikerjakan dalam dua tahap:
     * Tahap 1 baca-saja: rencana pemasangan foto, keterangan, cadangan, dan cara memulihkan.
     * Tahap 2 hanya setelah saya menyetujui rencana itu secara eksplisit.
   Untuk tiap paket, tulis di bagian F (tugas) dan L (kriteria penerimaan):
   - jumlah route sebelum dan sesudah sama;
   - action, method, enctype, dan nama/id input setiap formulir sama, dibuktikan dengan diff;
   - suite tes lulus dan ada tes baru untuk paket itu;
   - npm run build lulus;
   - 0 blok <style> dan 0 hex di view yang ditulis ulang;
   - tidak ada overflow horizontal di 360, 390, 768, dan 1440 px;
   - tangkapan layar sebelum dan sesudah;
   - tidak ada label Inggris yang tersisa;
   - status diambil dari StatusVocabulary;
   - hash berkas public/ tidak berubah (baseline/asset-hashes.txt; perhatikan student-go-logo.png yang sudah dihapus pengguna);
   - setiap aset yang berhenti dirujuk dicatat di ASSET_INVENTORY.md sebagai Deprecate, tanpa dihapus.
   Bagian H (aset) memakai hanya aset yang sudah ada di public/ dan student-go-assets/. Tidak ada aset baru, unduhan, atau aset hasil generate.

2. Tracker: tambah baris Fase 29 dengan status NOT_STARTED, hard deps 1 dan 3. Kolom Notes memuat daftar paket A–H beserta status masing-masing. Status fase tetap PARTIAL sampai semua paket yang disetujui selesai. Saya mengizinkan satu perubahan di baris lain: tambahkan 29 ke kolom Hard deps baris Fase 27. Jangan mengubah kolom lain atau baris lain. Catat semuanya di Update log.

3. Roadmap: tambahkan Fase 29 ke tabel fase dan ke tabel cakupan route bagian 6, yaitu delapan rute di atas ditambah student.friends.activities untuk paket F. Tambahkan juga 29 ke dependensi Fase 27.

4. DECISIONS_AND_OPEN_QUESTIONS.md: catat keputusan berikut dengan tanggal 2026-11-01, memakai ID baru berikutnya yang belum terpakai. Rujuk D-19 bila relevan.

   Keputusan saya:
   - D-19 (bagian yang masih terbuka): foto boleh dipakai di halaman editorial (detail masalah, detail dokumen repositori, cerita/portofolio mahasiswa), profil publik (institusi, perusahaan, mahasiswa), dan tampilan Galeri Telusuri Masalah. Yang boleh dipakai hanya 37 foto berstatus "available" di student-go-docs/design-system/photo-review.json. 14 foto "available-with-care" dan 9 foto "held" tidak dipakai.
   - Lima foto "available" ini tidak dipakai sampai saya memeriksanya sendiri: library/pertanian/central-java-farmer-terraced-fields, library/pertanian/west-java-elderly-farmers-harvest-rice, library/pertanian/central-java-farmer-casting-net-in-paddy, library/pesisir/west-sumatra-fisherman-net-from-canoe, library/pasar/banjarmasin-floating-market.
   - Galeri Telusuri Masalah tanpa foto cadangan dari katalog (U3).
   - Tidak ada foto katalog di profil atau portofolio mahasiswa maupun di detail dokumen repositori (U5).
   - Profil institusi publik memakai peta wilayah (U4), bukan foto. Paket G disetujui.
   - Paket H (foto seed): BELUM disetujui. Tulis paketnya, tandai menunggu persetujuan.
   - Enam view /admin/*: ditunda sampai ada akun admin. Catat sebagai pertanyaan terbuka.
   - Diagram workflows/*.svg dan indonesia-outline.svg tetap tidak dipakai (alasan di laporan audit bagian 5).

5. Jangan memperbaiki ketidaksesuaian tracker Fase 7–26. Laporan penyelesaian beberapa fase merekomendasikan COMPLETE, tetapi barisnya masih NOT_STARTED. Itu di luar tugas ini. Sebutkan saja di laporan akhir.

Selesai = keempat dokumen diperbarui dan git diff hanya menyentuh berkas di student-go-docs/design-execution/. Laporkan berkas yang berubah, ID keputusan yang dipakai, dan apa pun yang memerlukan jawaban saya. Lalu berhenti.
```

---

## Prompt paket (Prompt 1–6)

Polanya sama untuk semua paket. Ganti huruf paket dan catatan khususnya.

### Prompt 1 — Paket E (Perusahaan: profil publik dan ubah lowongan)

```text
Execute Phase 29, paket E (Perusahaan: company/{id} dan company/jobs/{id}/edit).

Ikuti CLAUDE.md bagian 8. Kerjakan hanya paket E, lalu berhenti.

Sebelum mulai:
- Jalankan git status. Perubahan yang bukan dari tugas ini (CLAUDE.md, empat berkas student-go-assets/, AUDIT.md, user) jangan disentuh.
- Tanyakan saya branch mana yang dipakai.
- Jangan commit kecuali saya minta.

Akun uji (kata sandi dibaca dari seeder di dalam skrip, jangan dicetak atau disimpan):
- perusahaan hr@koperasitanimakmurjaya.co.id, kata sandi di database/seeders/UserSeeder.php:135
- profil publik untuk dicek: /company/1, /company/77, /company/120

Catatan khusus:
- Rujukan pola: company/profile/index.blade.php (Fase 25) dan institution profile publik (Fase 5). Rujukan wizard: company/jobs/create.blade.php dan company/jobs/components/ (Fase 23).
- company/{id} bisa dibuka tamu, jadi jangan tampilkan data yang tidak tampil di versi lama atau di profil institusi publik.
- Setelah selesai, company-profile.jpg dan company3.jpg tidak dirujuk lagi. Catat sebagai Deprecate di ASSET_INVENTORY.md. Jangan hapus berkasnya.
- Uji ubah lowongan sampai submit hanya pada lowongan milik akun uji. Kembalikan data persis seperti semula sesudahnya, dan buktikan di laporan.
```

### Prompt 2 — Paket C (Detail lowongan mahasiswa)

```text
Execute Phase 29, paket C (Detail lowongan mahasiswa: student/jobs/{id}).

Ikuti CLAUDE.md bagian 8. Kerjakan hanya paket C, lalu berhenti. Jalankan git status dulu (perubahan yang bukan dari tugas ini jangan disentuh), tanyakan branch, dan jangan commit kecuali saya minta.

Akun uji: mahasiswa ulfamaulana1066@untag-smd.ac.id, kata sandi di database/seeders/DummyDataSeeder.php:359 (baca di dalam skrip, jangan dicetak). Halaman contoh: /student/jobs/363, /student/jobs/573.

Catatan khusus:
- Formulir lamar, simpan, dan bandingkan tetap sama (action, method, nama field).
- Header berisi badge yang melebar 9 px di 390 px (laporan audit C3).
- Ganti "Cover Letter", "Skills Yang Dibutuhkan", "SDG Alignment", "Diposting 1 day ago", dan "views" dengan istilah dari copy deck.
- Waktu relatif ditulis dalam bahasa Indonesia.
```

### Prompt 3 — Paket A (Lamaran mahasiswa)

```text
Execute Phase 29, paket A (Lamaran mahasiswa: tracker, applications/{id}, applications/create/{problemId}).

Ikuti CLAUDE.md bagian 8. Kerjakan hanya paket A, lalu berhenti. Jalankan git status dulu, tanyakan branch, dan jangan commit kecuali saya minta.

Akun uji: mahasiswa ulfamaulana1066@untag-smd.ac.id (3 lamaran: 330, 331, 332), kata sandi di database/seeders/DummyDataSeeder.php:359 (baca di dalam skrip, jangan dicetak). Formulir contoh: /student/applications/create/5.

Catatan khusus:
- Phases/PHASE_10_APPLICATIONS.md (layar S05–S08) dipakai sebagai rujukan desain untuk detail dan formulir.
- Jangan submit formulir lamaran kecuali ke data yang Anda buat sendiri dan hapus lagi. Jangan menekan "Batalkan" pada lamaran milik akun uji.
- Status di tracker harus sama katanya dengan dashboard dan /student/applications (StatusVocabulary).
- ID lamaran milik orang lain tetap 404. ID yang tidak ada (500, C1) dikerjakan di paket F, bukan di sini.
```

### Prompt 4 — Paket B (Proyek aktif mahasiswa)

```text
Execute Phase 29, paket B (Proyek aktif mahasiswa: projects/{id}, report/create, final-report/create).

Ikuti CLAUDE.md bagian 8. Kerjakan hanya paket B, lalu berhenti. Jalankan git status dulu, tanyakan branch, dan jangan commit kecuali saya minta.

Akun uji: mahasiswa yudifirdaus1638@uinjkt.ac.id (proyek 77), kata sandi di database/seeders/DummyDataSeeder.php:359 (baca di dalam skrip, jangan dicetak).

Catatan khusus:
- Kedua formulir laporan: action, method, enctype, field unggah, jenis berkas, dan batas ukuran tetap sama. Jangan submit laporan ke proyek milik akun uji. Kalau perlu menguji submit, pakai feature test.
- Tombol dan nomor sertifikat pada proyek selesai (dari feat/student-certificate) tetap ada. Berkas di public/assets/certificate/ tidak disentuh (D-32).
- Progres ditampilkan dari jumlah tahap selesai dibanding total. Persentase tersimpan tidak ditampilkan, mengikuti stage-meter Fase 19.
- Ganti "Overdue" dan "1 day ago".
```

### Prompt 5 — Paket D (Institusi: kelola proyek dan status verifikasi)

```text
Execute Phase 29, paket D (Institusi: projects/{id}/manage dan verification/status).

Ikuti CLAUDE.md bagian 8. Kerjakan hanya paket D, lalu berhenti. Jalankan git status dulu, tanyakan branch, dan jangan commit kecuali saya minta.

Akun uji: institusi pemerintahdesacangkringan28@pemerintahdesa.go.id (kelola proyek 118, 188, 217), kata sandi di database/seeders/DummyDataSeeder.php:359 (baca di dalam skrip, jangan dicetak).

Catatan khusus:
- manage.blade.php memuat formulir milestone, laporan, dan status. Semua action, method, dan nama field tetap sama, termasuk endpoint milestone yang mengembalikan JSON.
- Jangan mengubah status proyek atau milestone di data milik akun uji. Pengujian submit dilakukan lewat feature test, atau pada baris buatan sendiri yang dihapus lagi.
- verification/status: kata-kata soal "AI" bergantung pada D-09 (masih OPEN). Jangan diubah. Tanyakan saya kalau desainnya memerlukan perubahan itu.
```

### Prompt 6 — Paket F (Cacat dan polesan)

```text
Execute Phase 29, paket F (cacat dan polesan: C1–C6 dan U6 dari reports/ASSET_ENRICHMENT_AUDIT.md).

Ikuti CLAUDE.md bagian 8. Kerjakan hanya paket F, lalu berhenti. Jalankan git status dulu, tanyakan branch, dan jangan commit kecuali saya minta.

Saya menyetujui dua perubahan perilaku berikut. Untuk keduanya, tulis dulu uji yang gagal, lalu perbaiki:
- C1: GET /student/applications/{id} untuk ID yang tidak ada harus 404, bukan 500. ID milik orang lain tetap 404.
- C2: GET /student/friends/activities tidak boleh 500. Periksa dulu apakah endpoint ini dipanggil JS mana pun. Rutenya jangan dihapus atau diganti nama. Jangan membaca atau menyalin isi storage/logs ke laporan, karena bisa memuat detail koneksi.

Sisanya presentasi:
- C4: footer menempel di bawah di layouts.app (shell bersama). Uji regresi di halaman pendek dan panjang ketiga peran dan publik.
- C5: jarak blok galat login di layouts/auth. Kalimatnya diawali huruf besar.
- C6: placeholder-project.jpg (FriendController.php sekitar baris 425 dan 452) dan placeholder-profile-cover.jpg (student/friends/profile.blade.php baris 10) tidak ada di public/. Ganti dengan inisial atau tanpa gambar. Jangan membuat berkas gambar baru.
- C3: tabel data grafik di /company/dashboard diberi pembungkus overflow-x pada 390 px.
- U6: ilustrasi empty-state yang sudah ada untuk lima keadaan kosong tingkat halaman: institution/problems → no-documents, institution/projects → no-applications, institution/reviews → no-documents, student/jobs/saved → no-saved-projects, student/jobs "Halaman ini kosong" → no-search-results. Lima keadaan kosong di dalam tab atau sub-bagian dibiarkan tanpa ilustrasi.
```

---

## Prompt 7 — Paket G (Peta wilayah, hanya bila disetujui di Prompt 0)

```text
Execute Phase 29, paket G (peta wilayah di profil institusi publik /institution/{id}, usulan U4).

Ikuti CLAUDE.md bagian 8. Kerjakan hanya paket G, lalu berhenti. Jalankan git status dulu, tanyakan branch, dan jangan commit kecuali saya minta.

Catatan khusus:
- Pakai public/assets/illustrations/geographic/indonesia-regions.svg. Grupnya: region-sumatra, region-jawa, region-kalimantan, region-sulawesi, region-nusa-tenggara, region-maluku, region-papua. Berkas aslinya jangan diubah. Sisipkan sebagai SVG inline lewat partial, seperti peta Beranda Fase 4.
- Helper baru di app/Support memetakan dua digit pertama kode provinsi BPS ke tujuh grup. Seeder hanya punya 5 provinsi (D-14), jadi uji pemetaannya dengan unit test yang memuat semua 38 kode provinsi.
- Hanya angka dari query nyata. Sediakan padanan teks yang bisa dibaca pembaca layar, misalnya "4 masalah dibuka di Jawa". Bila institusi tidak punya masalah, tampilkan wilayah asalnya saja, tanpa angka 0.
- Halaman ini publik. Data yang ditampilkan tidak boleh melebihi yang sudah tampil sekarang (provinsi dan jumlah masalah dibuka).
- Contoh: /institution/33, /institution/56, /institution/59, /institution/61.
```

---

## Prompt 8 — Paket H (Foto seed masalah, hanya setelah Anda setuju)

Tahap 1 aman (baca-saja). Tahap 2 menulis ke database dan mungkin ke Supabase Storage, jadi baru dijalankan setelah Anda membaca rencananya.

```text
Execute Phase 29, paket H, TAHAP 1 SAJA (rencana baca-saja untuk foto seed masalah, usulan U2 dan temuan C10).

Ikuti CLAUDE.md bagian 8. Tahap ini tidak boleh menulis ke database, storage, public/, atau kode aplikasi.

Yang perlu diselidiki dan dilaporkan:
1. Bagaimana image_path di tabel problem_images (220 baris, 92 berkas problems/masalah-desa-NN.jpg) diubah menjadi URL: lewat SupabaseStorageService atau cara lain. Seeder mana yang membuatnya, dan dari mana asal berkasnya.
2. Berkas masalah-desa-NN.jpg mana yang sama dengan foto katalog mana. Bandingkan hash atau dimensi dengan public/assets/photography/** dan student-go-assets/storage/app/asset-originals/. Pengunduhan dari bucket hanya boleh bila memang perlu dan hanya baca.
3. Usulan pemasangan: setiap masalah mendapat foto dari kolam yang sesuai SDG pertamanya (tabel kolam ada di laporan audit U2).
   - Hanya foto "available".
   - Kelima foto yang ditahan di D-19 dikecualikan.
   - Keterangan netral atau dari data nyata (kabupaten dan provinsi), bukan klaim seperti "Foto survei lokasi".
   - SDG 3, 5, dan 7 memakai kolam wilayah dengan keterangan "Gambaran wilayah".
4. Cara menulisnya: seeder baru atau perintah sekali jalan. Sebutkan apakah perlu menulis ke Supabase Storage atau cukup mengubah baris. Jelaskan cadangan sebelum menulis (CSV di folder scratchpad, bukan di repositori) dan cara memulihkan.
5. Dampak pada tes yang ada.

Laporkan rencananya, lalu berhenti dan tunggu persetujuan saya untuk tahap 2.
```

---

## Yang sengaja tidak diberi prompt

- **Enam view `/admin/*`**: belum ada akun admin, jadi hasilnya tidak bisa diperiksa di browser. Membuat akun admin berarti menulis ke database dan perlu keputusan Anda.
- **Pembersihan berkas (U9)**: menunggu D-30, dan dikerjakan setelah paket E selesai (paket E membuat `company-profile.jpg` dan `company3.jpg` tidak dirujuk lagi). Aturan 11 melarang menghapus aset tanpa otoritas fase.
- **Sinkronisasi tracker Fase 7–26**: barisnya masih `NOT_STARTED` di semua branch, padahal laporan penyelesaian Fase 6–9, 12, 14, dan 17–26 ada. Ini perlu satu tugas dokumen tersendiri, bila Anda mau.
