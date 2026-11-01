# Audit pengayaan aset dan halaman — Student Go

Tanggal 2026-11-01 · branch `chore/project-assets` @ `92b9103` (`dev` hanya selisih satu commit merge) · aplikasi `http://127.0.0.1:8000`

**Status: hanya analisis dan usulan.** Tidak ada kode, aset di `public/`, konfigurasi, atau data yang diubah. Satu-satunya berkas baru di repositori adalah dokumen ini.

Label bukti mengikuti CLAUDE.md bagian 11: **VERIFIED** (dibaca atau dijalankan di sesi ini), **OBSERVED** (dari catatan atau analisis sebelumnya, tidak dihitung ulang), **INFERRED** (kesimpulan yang belum dikonfirmasi).

---

## 1. Ringkasan

1. **Pengayaan terbesar bukan menambah foto.** Sebelas pola halaman yang masih hidup belum (atau baru sebagian) masuk sistem desain: sembilan sepenuhnya bermarkup Tailwind lama, dua campuran. Cirinya tombol biru dan ungu terang, bar progres bergradien, label Inggris. Halaman-halaman inilah yang membuat produk terasa tidak seragam. Daftarnya ada di 3.1. Semuanya bisa dibereskan dengan komponen dan aset yang sudah ada.
2. **Foto sudah banyak terpasang.** Keenam foto lapangan KKN ada di Beranda (2) dan About (4), tiga potret tim di About. Enam puluh foto katalog tidak dirujuk template mana pun, tetapi foto-foto itu sudah tampil sebagai foto masalah (data seed di Supabase) di Galeri dan detail masalah, dan **tidak cocok dengan topik masalahnya** (3.2, C10).
3. **Galeri Telusuri Masalah tidak butuh foto cadangan.** 76 dari 77 masalah punya 2–4 foto; kartu sudah menyembunyikan area media bila foto tidak ada. Pilihan Anda untuk Galeri tidak mengubah kodenya (U3).
4. **Aset ilustrasi yang menganggur punya sedikit slot yang membawa isi:** peta wilayah di profil institusi (U4), lima keadaan kosong tingkat halaman (U6), beberapa ikon (U7). Diagram workflow sebaiknya tetap tidak dipakai (bagian 5).
5. **Cacat lain yang ikut terlihat:** dua galat 500, tiga pola halaman yang melebar di mobile, footer yang melayang di halaman pendek, dan dua rujukan ke gambar yang tidak ada (3.2).

## 2. Cakupan dan metode

| Hal | Isi |
|---|---|
| Akun | Satu akun per peran (mahasiswa, institusi, perusahaan), ditambah satu mahasiswa yang punya proyek (agar halaman proyek bisa dibuka). Kata sandi dibaca dari seeder oleh skrip dan tidak dicetak atau disimpan. |
| Alat | Playwright (Python) di luar proyek (D-22 opsi c). Desktop 1440×900 dengan tangkapan layar penuh; mobile 390×844 dengan mengubah ukuran viewport pada halaman yang sama (tanpa emulasi sentuh). |
| Halaman | 110 pasangan peran × rute unik, ditambah 24 kondisi UI (tampilan galeri dan peta, hasil kosong, halaman di luar batas paginasi, ID tidak ada, akses lintas peran, login gagal, menu mobile, dropdown notifikasi). Rinciannya di Lampiran A. |
| Cakupan route GET | Mahasiswa: 19/30 statis (11 sengaja dilewati), 10/13 pola dinamis. Institusi: 9/10 statis, 10/12 dinamis. Perusahaan: 9/12 statis, 4/4 dinamis. Publik dan auth: 10 dari 14 pola. |
| Data | 700 mahasiswa, 79 institusi, 130 perusahaan, 77 masalah, 220 baris `problem_images` (92 berkas berbeda). Foto profil mahasiswa, logo institusi, logo perusahaan, dan foto laporan proyek: **0**. Diambil lewat query hitung baca-saja. |
| Pengaman | Hanya permintaan GET dan login. Satu percobaan login gagal dengan email yang tidak ada (untuk melihat state galat). Halaman 5xx tidak ditangkap layar (halaman debug membocorkan koneksi). Tidak memanggil `/student/chatbot/create`, `/dev/login`, ekspor, atau unduhan. |

Sistem desain yang sudah berjalan, dilihat dari halaman yang sudah masuk: Plus Jakarta Sans untuk UI dan Source Serif 4 untuk teks panjang (nama font menurut CLAUDE.md; serif terlihat di detail masalah dan abstrak repositori); satu warna merek (CLAUDE.md: `#405080`) pada tombol utama; permukaan netral; tabel dan daftar bergaris untuk data; warna hanya untuk status. Dashboard ketiga peran tampak konsisten dengan prinsip desain di CLAUDE.md bagian 6 (penilaian dari tangkapan layar, bukan pemeriksaan tiap prinsip satu per satu).

Kondisi yang berfungsi baik: menu mobile (tombol "Buka menu" membuka panel dengan 10 tautan, Escape menutup dan fokus kembali ke tombol), dropdown notifikasi, halaman 404 dan 403 yang sudah didesain, pengalihan diam-diam untuk akses lintas peran, pengalihan tamu ke `/login`, dan pada uji ubah-ukuran ke 390 px hanya tiga pola halaman yang melebar horizontal (101 dari 108 pemuatan tidak; lihat C3).

## 3. Temuan

### 3.1 Halaman yang belum masuk sistem desain

Ukuran: porsi elemen di dalam `<main>` yang memakai kelas `sg-*` dibanding kelas utilitas Tailwind, dari DOM yang dirender (VERIFIED). Dashboard yang sudah didesain ulang sebagai pembanding: 51–73% `sg-*` dan 0–8% utilitas. Tujuh view yang saya bandingkan di git (profil perusahaan publik, detail lowongan, tracker, detail proyek, manage proyek institusi, status verifikasi, ubah lowongan) identik di `dev`, `feat/company-profile`, `feat/application-workflow`, dan `docs/progress-phase-13-16`, jadi ini kondisi seluruh proyek, bukan kekhasan checkout ini.

| Rute | Peran | `sg-*` / utilitas | Yang terlihat (dari tangkapan layar) | Peta roadmap |
|---|---|---|---|---|
| `/company/{id}` (profil perusahaan publik) | publik | 0% / 98% | Foto gedung (`company-profile.jpg`) sebagai latar hero inline, avatar bergradien ungu, badge biru, ungu, dan hijau, angka statistik berwarna, ikon inline di luar sprite, "Internship" dan "internship" tidak seragam, blok `<style>` inline. Satu-satunya halaman publik di luar sistem. | Fase 25 |
| `/student/jobs/{id}` | mahasiswa | 0% / 68% | Tombol biru terang di luar warna merek, label Inggris ("Cover Letter", "Skills Yang Dibutuhkan", "SDG Alignment", "Diposting 1 day ago", "470 views"), kartu bertumpuk. Melebar 9 px di 390 px. | Fase 15 |
| `/student/applications/tracker` | mahasiswa | 0% / 87% | Status Inggris ("Rejected", "Pending", "Shortlisted", "Applied") padahal dashboard dan `/student/applications` memakai "Ditolak" dan "Menunggu". Grafik "Aktivitas Minggu Ini" berupa tujuh kotak abu-abu kosong. Angka berwarna biru, hijau, merah. "Aksi Cepat" tiga warna. | Fase 10 |
| `/student/projects/{id}` | mahasiswa | 0% / 33%* | Bar progres bergradien ungu, dua tombol ungu, badge "Overdue" dan "1 day ago" berbahasa Inggris, kartu bertumpuk. Dua formulir laporan (`/report/create`, `/final-report/create`) memakai markup yang sama: 0 token `sg-*`, 265–268 token utilitas, `<style>` inline (dari berkas view; tampilannya tidak saya tinjau satu per satu). | Fase 11 |
| `/student/applications/{id}`, `/student/applications/create/{id}` | mahasiswa | 6% / 53% dan 23% / 30% | Campuran: sebagian sudah komponen baru. Angka DOM saja, belum ditinjau visual. | Fase 10 |
| `/institution/projects/{id}/manage` | institusi | 0% / 80% | Angka DOM saja, belum ditinjau visual. | Fase 18 atau 19 |
| `/institution/verification/status` | institusi | 0% / 87% | Angka DOM saja, belum ditinjau visual. | Fase 20 |
| `/company/jobs/{id}/edit` | perusahaan | 0% / 76% | Foto latar inline `company3.jpg`, stepper "5 Kriteria Dampak" melebar 100 px di 390 px. `/company/jobs/create` sudah 43% / 0%, jadi buat dan ubah lowongan tidak seragam. | Fase 23 |
| `/admin/*` (6 view) | admin | kode saja: 0 token `sg-*`, 200–400 token utilitas | **Belum dilihat** (tidak ada akun admin). Roadmap hanya memuat satu pola route admin (halaman 403, Fase 26), jadi enam view ini tidak dijadwalkan. | tidak dijadwalkan |

\* Tiga halaman proyek memberi angka DOM yang identik (2.862 elemen); saya tidak menganggap angka itu andal. Penilaiannya bersandar pada tangkapan layar halaman detail dan pada hitungan token di berkas view.

Catatan: tracker di semua branch yang saya buka menandai Fase 7–28 `NOT_STARTED`, padahal sebagian besar halaman sudah didesain ulang. Nomor fase di tabel hanya penunjuk dari peta roadmap, bukan klaim status.

### 3.2 Cacat fungsi, tata letak, dan konten

| ID | Temuan | Bukti | Arah perbaikan |
|---|---|---|---|
| C1 | `/student/applications/999999` (ID tidak ada) mengembalikan **500**. Aturan fungsional 5 menyebut ID yang bukan milik pengguna harus 404. `/student/browse-problems/999999`, `/institution/problems/999999`, `/company/jobs/999999` benar 404. | HTTP 500; tidak ditangkap layar; penyebab belum diselidiki | Cari asal 500 (kemungkinan query tanpa `findOrFail`) |
| C2 | `GET /student/friends/activities` mengembalikan **500**. | HTTP 500; penyebab belum diselidiki (log tidak saya baca karena bisa memuat detail koneksi) | Periksa log dan pastikan endpoint ini memang dipakai |
| C3 | Pada muat langsung di 390 px, dokumen melebar di dua pola: `/student/jobs/{id}` (399 px; header flex dengan badge) dan `/company/jobs/{id}/edit` (490 px; stepper). `/company/dashboard`: dokumen tidak bergulir pada muat langsung, tetapi tabel data grafik (395 px) melewati tepi kanan sampai 410 px (belum diperiksa apakah terpotong); pada uji ubah-ukuran dari desktop tanpa muat ulang, dokumen ikut melebar. | Elemen terlebar per halaman; `scrollWidth` dokumen pada muat langsung | Dua halaman lama selesai sendiri saat dimigrasi (U1); tabel grafik perlu `overflow-x: auto` |
| C4 | Footer tidak menempel di bawah pada halaman pendek: celah kosong di bawah footer 61 px (`/contact`), 71 (`/student/wishlist`), 91 (`/student/projects`), 97 (404), 165 (`/student/friends`) pada 1440×900. Banyak halaman lain setinggi layar persis. | Diukur di lima halaman | Tata letak kolom flex dengan `main` yang mengisi sisa tinggi |
| C5 | State login gagal: pesan "email atau password salah." berjarak sekitar 100 px kosong di atas dan di bawah dan menjorok dari kolom. Kalimatnya dimulai huruf kecil. | Tangkapan layar `guest-login-error` | Rapikan jarak blok galat di `layouts/auth` |
| C6 | Dua rujukan ke berkas yang tidak ada di `public/`: `placeholder-project.jpg` (`FriendController.php:425`, `:452`) dan `placeholder-profile-cover.jpg` (`student/friends/profile.blade.php:10`). | `ls public` | Hapus rujukan atau ganti dengan inisial, sesuai aturan "tanpa tile pengganti" |
| C7 | View tak terjangkau: `company/talents/show` (tidak ada route GET; hanya POST `contact` dan `toggle-save`) dan `student/friends/profile` (route dialihkan ke `/profile/{username}`). Tujuh view tak dipanggil siapa pun: `institution/create`, `institution/index`, `institution/show`, `student/portfolio/index`, `student/portfolio/public`, `student/repository/components/filter-sidebar`, `welcome`. | Pencarian `view()` dan `@include` | Catat sebagai kandidat arsip; jangan hapus tanpa otoritas fase |
| C8 | Kosakata status tidak seragam: "Ditolak/Menunggu" (dashboard, daftar lamaran) lawan "Rejected/Pending" (tracker); badge "Overdue", "1 day ago", "views", "Internship" di halaman berbahasa Indonesia. Prinsip 9: satu kata per status. | 3.1 | Lewat `App\Support\StatusVocabulary` saat halaman dimigrasi |
| C9 | 10 dari 48 pemakaian `x-ui.empty-state` tanpa ilustrasi (38 sudah punya). | Pemindaian view dengan pencocokan sadar tanda kutip | U6 |
| C10 | Foto seed tidak cocok dengan masalahnya, dengan keterangan yang mengklaim isi foto. Contoh di Galeri: "Database Kolaboratif" memakai foto sawah terasering, "Mediasi Konflik Komunitas" memakai kapal phinisi di pelabuhan; detail "Pelatihan Keterampilan Warga Miskin" memakai kapal dengan keterangan "Area pelaksanaan program". Enam keterangan generik terpakai di 220 baris (23–28 baris masing-masing): "Area pelaksanaan program", "Aktivitas masyarakat", "Dokumentasi kondisi eksisting", "Foto survei lokasi", "Gambaran permasalahan", "Lokasi kegiatan". | Tangkapan layar Galeri dan detail; query hitung | U2 |
| C11 | Catatan desain, bukan cacat: pada tampilan Daftar, chip SDG berisi nama panjang ("SDG 17 · Kemitraan untuk mencapai tujuan") di kolom Masalah yang sempit, sehingga baris setinggi hingga sekitar 120 px dan 12 baris memanjang 2.297 px. Prinsip 6 menyebut SDG hanya sebagai swatch kecil. | Tangkapan layar `/student/browse-problems` | Label pendek dengan swatch, nama lengkap di tooltip |

Catatan data (bukan temuan desain, sejalan dengan AUDIT.md bagian 2.4): alamat proyek berpola Faker bergaya AS ("4370 Haley Cape, Ezrabury, VT 04894-5545") dan provinsi alamat instansi yang tidak sama dengan provinsi proyek berasal dari seed.

### 3.3 Inventaris aset

| Kelompok | Jumlah | Terpakai di | Tidak terpakai | Label |
|---|---|---|---|---|
| Foto lapangan KKN (`photography/field/`) | 12 berkas = 6 foto × (800, 1600 px) | Beranda 2, About 4 | tidak ada | VERIFIED (DOM) |
| Potret tim (`photography/member/`) | 3 | About | tidak ada | VERIFIED |
| Foto katalog Unsplash/Pexels | 60: 37 `available`, 14 `available-with-care`, 9 `held` | Tidak ada template. Foto di Galeri dan detail masalah cocok secara visual dengan foto katalog (92 berkas `problems/masalah-desa-NN.jpg`, 220 baris). | 14 `with-care` dan 9 `held` tidak terlihat di halaman mana pun yang saya buka | VERIFIED (pencarian literal, DOM); asal seed dari D-14: INFERRED |
| Foto lama di akar `public/` | 27 tidak dirujuk (sekitar 20 MB) + 3 masih dirujuk | `company3.jpg` (`company/jobs/edit`), `company-profile.jpg` (`company/profile/public`), `company5.jpg` (`company/talents/show`, tak terjangkau) | 27 file | Tiga rujukan VERIFIED; angka 27 dan 20 MB OBSERVED |
| Ilustrasi empty-state | 5 | 38 dari 48 pemakaian komponen | — | VERIFIED |
| Ilustrasi `workflows/` | 8 | tidak ada (sengaja, alasan di `ASSET_INVENTORY.md`) | 8 | VERIFIED |
| Ilustrasi `geographic/` | 2 | tidak ada | `indonesia-regions.svg` punya 7 grup siap pakai: `region-sumatra`, `jawa`, `kalimantan`, `sulawesi`, `nusa-tenggara`, `maluku`, `papua` | VERIFIED |
| Motif | 4 | `contour-field` (About, panel auth), `map-pin` dan `map-pin-selected` (peta Beranda) | `survey-marks`: kelas `.sg-survey-marks` ada tetapi tidak dipakai view mana pun | VERIFIED |
| Ikon sprite | 71 simbol | 60 tampil di 112 halaman yang dirender | 11 tidak tampil: `arrow-up-right`, `circle-help`, `droplets`, `ellipsis`, `globe`, `lightbulb`, `loader-circle`, `phone`, `shield-check`, `sprout`, `target`. Tujuh di antaranya tanpa rujukan literal di kode (`arrow-up-right`, `droplets`, `ellipsis`, `globe`, `loader-circle`, `sprout`, `target`); ada 21 pengikatan nama ikon dinamis, jadi "tidak terpakai" untuk tujuh itu bukan bukti mutlak. | VERIFIED |
| Video hero | 8 berkas | Beranda | — | VERIFIED |
| `student-go-assets/` | 246 berkas, 77 MB, **tracked** (bukan untracked seperti di ringkasan awal) | — | 166 berkas `public/assets` di paket seluruhnya sudah ada di `public/assets` (tidak ada bahan baru). Yang unik hanya `storage/app/asset-originals/` (60 JPG master). Belum ada aturan `.gitignore` untuknya. | VERIFIED |

## 4. Usulan, urut prioritas

Ukuran kerja: **S** kurang dari setengah hari, **M** sekitar sehari, **L** beberapa hari. Tidak satu pun usulan di bawah memakai aset di luar `public/` dan `student-go-assets/`.

**U1. Masukkan sebelas pola halaman ke sistem desain** (L, bisa per halaman; sembilan sepenuhnya lama, dua campuran). Tanpa aset baru; memakai komponen `x-ui.*`, sprite ikon, dan ilustrasi empty-state yang ada. Urutan yang saya sarankan: `/company/{id}` (tamu melihatnya lebih dulu), `/student/jobs/{id}`, `/student/applications/tracker` (sekaligus menyeragamkan kosakata status, C8), halaman proyek dan dua formulir laporannya, detail dan formulir lamaran, `/institution/projects/{id}/manage`, `/institution/verification/status`, `/company/jobs/{id}/edit` (samakan dengan `/create`). Setelah `company/profile/public` dan `company/jobs/edit` dimigrasi, `company-profile.jpg` dan `company3.jpg` tidak dirujuk lagi, jadi tinggal kandidat arsip. Ini tidak butuh jawaban D-19.

**U2. Rapikan foto seed masalah** (S–M; menulis ke database, butuh persetujuan Anda). Hanya data demo: institusi sungguhan mengunggah foto sendiri. Usul: seeder yang memasangkan foto dari 37 foto `available` ke masalah menurut SDG pertamanya, dengan keterangan netral atau dari data nyata (kabupaten dan provinsi), bukan klaim seperti "Foto survei lokasi". Kolam foto menurut folder katalog:

| Kolam | SDG | Foto `available` (di bawah `public/assets/photography/`) |
|---|---|---|
| Pertanian dan lahan | 1, 2, 15 | `library/pertanian/*` (9), `homepage/purwojati-banyumas-rice-fields`, `homepage/bengkulu-aerial-rice-terraces-sunset`, `homepage/flooded-rice-paddies-farmers-aerial`, `about/*` (3) |
| Pesisir dan air | 6, 13, 14 | `library/pesisir/*` (8), `homepage/maluku-islands-aerial`, `library/desa/waigeo-barat-coastal-huts-aerial`, `library/desa/papua-island-landscape` |
| Pasar dan ekonomi | 8, 12 | `library/pasar/ntb-fish-market`, `banjarmasin-floating-market`, `surakarta-market-shoppers` |
| Pendidikan | 4 | `library/pendidikan/universitas-indonesia-graduation-flags`, `umy-graduates-in-red-gowns` |
| Desa dan wilayah | 9, 10, 11, 16, 17 | `library/desa/*` selain dua yang sudah masuk kolam pesisir (4: `southeast-sulawesi-stilt-houses-by-river`, `penglipuran-village-path`, `rural-road-house-and-mountain`, `kebumen-three-cyclists-across-field`), `homepage/cirebon-aerial-village-red-roofs`, `homepage/kutai-kartanegara-aerial-town-and-river` |
| Tidak ada yang cocok secara tematik | 3, 5, 7 | Pakai kolam "wilayah" netral dengan keterangan "Gambaran wilayah" |

Masalah nyata: SDG 3 (10 masalah), 5 (6), dan 7 (7) tidak punya foto yang sepadan di 37 foto itu, dan SDG 9, 16, 17 (masalah tata kelola dan digital) hanya bisa diwakili foto wilayah. Karena itu keterangan netral lebih jujur daripada memaksa kecocokan tematik.

**U3. Galeri Telusuri Masalah: tidak perlu foto cadangan** (tidak ada kerja). Mode `?view=grid` menampilkan 12 foto dari 12 kartu, semuanya termuat; halaman terakhir 5 dari 5. Hanya 1 masalah tanpa foto, dan kartunya sudah menyembunyikan area media tanpa tile pengganti (komentar di `problem-card.blade.php`). Foto katalog sebagai pengganti akan menyiratkan itu foto masalahnya, bertentangan dengan prinsip 7. Bila Anda tetap ingin, satu-satunya cara yang jujur adalah keterangan "Foto ilustrasi" yang terlihat.

**U4. Profil institusi publik: figur "Wilayah" dari `indonesia-regions.svg`** (M). Aset sudah ada dan punya hook `.is-active` per grup. Isi nyata: grup pulau dari provinsi institusi dan jumlah masalah dibuka per grup, dengan keterangan teks ("4 masalah dibuka di Jawa"). Pemetaan kode provinsi BPS (dua digit pertama) ke tujuh grup belum ada di kode; `RegionCentroids` hanya menyimpan koordinat, jadi perlu satu helper kecil di `app/Support`. Letaknya di sisi "Data instansi" pada `/institution/{id}`. Ini pengganti foto stok yang saya sarankan untuk slot "profil publik institusi" yang Anda pilih: peta membawa isi dan tidak menyiratkan tempat yang salah. Bila Anda tetap ingin foto, pakai satu foto `available` bertanda "Ilustrasi".

**U5. Foto di halaman editorial** (S per halaman). Hasil telaah per slot yang Anda izinkan:
- Detail masalah: sudah ada galeri; yang perlu diperbaiki adalah isinya (U2). Opsional kosmetik: sudut `survey-marks` pada figur, karena kelasnya sudah ada dan belum dipakai.
- Detail dokumen repositori `/student/repository/{id}`: tidak ada foto nyata untuk sebuah laporan PDF. Saya sarankan tidak ada foto. Bila tetap ingin, satu foto `available` dari kolam SDG laporan dengan keterangan wajib "Foto ilustrasi, bukan dokumentasi laporan ini".
- Portofolio dan cerita mahasiswa (`/student/profile`, `/profile/{username}`): **jangan** pakai foto katalog. Foto di dekat nama orang menyiratkan itu dia; 0 dari 700 mahasiswa punya foto profil, jadi avatar inisial tetap yang jujur. Gunakan ilustrasi empty-state untuk cerita yang kosong (U6).
- Profil perusahaan publik: tidak ada data foto atau lokasi. Saat dimigrasi (U1), tanpa hero foto. Baris kontak memakai ikon `globe` dan `phone` (U7).

**U6. Keadaan kosong tanpa ilustrasi** (S). Dari 10, lima bersifat tingkat halaman dan layak: `institution/problems` "Belum ada masalah" → `no-documents`; `institution/projects` "Belum ada proyek" → `no-applications`; `institution/reviews` "Belum ada ulasan" → `no-documents`; `student/jobs/saved` → `no-saved-projects`; `student/jobs` "Halaman ini kosong" → `no-search-results`. Lima sisanya berada di dalam sub-bagian atau tab (dua di portofolio dengan `heading-level 3`, satu di profil institusi publik, "Belum ada saran" dan "Tidak ada permintaan masuk" di tab halaman Jaringan); ilustrasi 160×120 akan terlalu berat di sana, jadi dibiarkan teks.

**U7. Ikon yang menganggur** (S). `phone` dan `globe` untuk baris Telepon dan Website di `/company/{id}` dan `/institution/{id}`; `shield-check` untuk status verifikasi di `/institution/verification/status`; `loader-circle` hanya bila ada tombol dengan state memproses. `target`, `droplets`, `sprout`, `ellipsis`, `arrow-up-right`, `circle-help`, `lightbulb` tidak punya slot yang jelas; jangan dipaksakan.

**U8. Perbaiki cacat C1–C6** (S–M): 404 untuk lamaran yang tidak ada, penyebab 500 `friends/activities`, tiga overflow mobile, footer, jarak galat login, dan dua rujukan gambar yang tidak ada.

**U9. Pembersihan berkas, hanya setelah keputusan dan otoritas fase** (S): arsipkan `workflows/` (8) dan `indonesia-outline.svg`; 27 foto lama setelah D-30 dijawab dan setelah U1; tambahkan `storage/app/asset-originals/` ke `.gitignore`; hapus atau abaikan duplikat `student-go-assets/public`. Berkas sertifikat di `public/assets/certificate/` dilindungi D-32 dan tidak disentuh. Aturan 11 melarang menghapus atau memindahkan aset tanpa otoritas fase; "Deprecate" belum berarti boleh dihapus.

## 5. Yang tidak saya usulkan, dan alasannya

- **Foto di dashboard, formulir, dan tabel.** Prinsip 1 dan 7: halaman tugas dimulai dari judul dan aksi utama, dan gambar hanya bila membawa isi. Ini juga alasan saya tidak menyediakan pilihan itu di pertanyaan.
- **Diagram `workflows/*.svg`.** Label tidak cocok dengan navigasi dan status asli (misalnya "halaman Pelamar" padahal menunya "Aplikasi"; empat tahap tidak sama dengan status tersimpan `active`, `on_hold`, `completed`, `cancelled`), dan teks di dalam gambar tidak ikut skala di 390 px. Ditolak dengan alasan yang sama di `ASSET_INVENTORY.md`.
- **Foto katalog sebagai pengganti di Galeri** dan **di profil mahasiswa**: lihat U3 dan U5.
- **14 foto `available-with-care` dan 9 foto `held`**: sesuai jawaban Anda, hanya 37 foto `available`.
- **Foto lama di akar `public/` pada slot baru.** Hak pakai (D-30) belum dijawab, kecuali foto lapangan yang sudah dikonfirmasi untuk Beranda dan About.
- **`indonesia-outline.svg`**: tidak ada slot yang membawa isi; peta wilayah (U4) lebih informatif.

## 6. Keputusan yang masih perlu dari Anda

1. **U1**: dikerjakan sebagai fase mana, atau halaman mana dulu? CLAUDE.md bagian 8 meminta perintah yang menyebut fase atau cakupan yang jelas sebelum kode diubah.
2. **U2**: setuju menulis ke database seed lewat seeder? Saya tidak menulis apa pun ke database.
3. **U4**: peta wilayah menggantikan foto di profil institusi, atau tetap foto bertanda "Ilustrasi"?
4. **Admin**: enam view `/admin/*` hanya bisa dikerjakan dari kode (tidak ada akun admin). Ikut atau ditunda?
5. **Pemeriksaan manusia untuk lima foto `available`** yang di thumbnail tampak memuat orang cukup jelas padahal `photo-review.json` mencatat "tidak dapat dikenali" (kesan saya, bukan keputusan): `library/pertanian/central-java-farmer-terraced-fields`, `west-java-elderly-farmers-harvest-rice`, `central-java-farmer-casting-net-in-paddy`, `library/pesisir/west-sumatra-fisherman-net-from-canoe`, `library/pasar/banjarmasin-floating-market`. Periksa dulu sebelum foto-foto ini dipasang di mana pun, termasuk lewat U2.
6. **Pencatatan**: jawaban Anda hari ini untuk D-19 (halaman editorial, profil publik, dan Galeri diizinkan; hanya 37 foto `available`) belum saya tulis ke `DECISIONS_AND_OPEN_QUESTIONS.md`, karena itu bukan bagian dari eksekusi fase.

## 7. Batas dan hal yang tidak diperiksa

- **Admin**: tidak dilihat; hanya analisis kode.
- **Tidak dikunjungi**: `/student/chatbot/{id}` (membuat rekaman lewat GET), semua ekspor dan unduhan, verifikasi email dan reset kata sandi, `/institution/applications/{id}/review` (efek samping tidak diketahui). `/institution/projects/{id}/milestones/{id}` mengembalikan non-HTML (kemungkinan JSON) dan `/institution/reviews/create/{id}` mengalihkan ke detail proyek.
- **Bukan inspeksi menyeluruh atas semua record.** Contoh dinamis diambil dari tautan yang ditemukan, sampai tiga per pola (pertama, terakhir, judul terpanjang), bukan seluruh 77 masalah atau 700 mahasiswa. Daftar masalah dibuka di halaman 1, 7, dan 99.
- Uji mobile memakai viewport yang diperkecil, bukan perangkat sungguhan.
- Ubin peta Leaflet datang dari server pihak ketiga (12 ubin termuat di tampilan Peta); itu bukan aset dalam cakupan.
- Server lokal lambat (6–30 detik per permintaan lewat pooler Supabase); itu bukan temuan desain.
- Tangkapan layar ada di folder sementara sesi ini dan akan hilang bila folder dibersihkan. Salin dulu yang ingin disimpan.
- Koreksi atas ringkasan awal: `student-go-assets/` sekarang tracked (246 berkas), bukan untracked.

---

## Lampiran A. Inventaris halaman yang dikunjungi

Kolom "Gambar isi" menghitung gambar selain logo, ilustrasi empty-state, dan ubin peta. Pada `/student/browse-problems` dan `/student/problems`, angka 0/12 adalah kartu Galeri yang tersembunyi pada tampilan Daftar dan belum dimuat; saat Galeri dibuka, 12 dari 12 foto termuat (baris `grid-view` di tabel kondisi UI). "Berakhir di" kosong bila tidak ada pengalihan. Halaman `/`, `/about`, `/contact` hanya dicatat di bagian publik; ketiganya juga dibuka dengan tiap peran untuk melihat shell saat login.

### Publik (tanpa login)

| Rute | HTTP | Berakhir di | H1 | Tinggi (px) | Gambar isi (termuat/total) | Overflow 390 px |
|---|---|---|---|---|---|---|
| `/` | 200 |  | Platform digital untuk Kuliah Kerja Nyata | 6480 | 8/8 | tidak |
| `/about` | 200 |  | Student Go mempertemukan instansi dan mahasiswa  | 3505 | 7/7 | tidak |
| `/contact` | 200 |  | Kontak | 900 | - | tidak |
| `/login` | 200 |  | Masuk ke Student Go | 901 | - | tidak |
| `/register` | 200 |  | Buat akun Student Go | 901 | - | tidak |
| `/register/student` | 200 |  | Daftar sebagai Mahasiswa | 1081 | - | tidak |
| `/register/company` | 200 |  | Daftar sebagai Perusahaan | 1279 | - | tidak |
| `/register/institution` | 200 |  | Daftar sebagai Instansi | 1389 | - | tidak |
| `/forgot-password` | 200 |  | Lupa password | 901 | - | tidak |
| `/halaman-yang-tidak-ada-xyz` | 404 |  | Halaman tidak ditemukan | 900 | - | tidak |
| `/company/1` | 200 |  | Koperasi Tani Makmur Jaya | 1476 | - | tidak |
| `/company/120` | 200 |  | Perkebunan Durian Medan | 1312 | - | tidak |
| `/company/77` | 200 |  | BUMDES Usaha Maju | 1208 | - | tidak |
| `/institution/33` | 200 |  | Pemerintah Desa Cangkringan | 1657 | - | tidak |
| `/institution/56` | 200 |  | Posyandu Teratai Desa Karangploso | 1526 | - | tidak |
| `/institution/59` | 200 |  | Dinas Perikanan Kabupaten Pekalongan | 1496 | - | tidak |
| `/institution/61` | 200 |  | Dinas Peternakan Dan Kesehatan Hewan Kabupaten S | 1345 | - | tidak |
| `/profile/bungaazhari1008` | 200 |  | Widi Hidayat | 900 | - | tidak |
| `/profile/mirarachman1389` | 200 |  | Erlangga Salsabila | 953 | - | tidak |
| `/profile/oliviamaulana1006` | 200 |  | Putri Syahputra | 900 | - | tidak |
| `/profile/putripratama1605` | 200 |  | Hilda Syahputra | 929 | - | tidak |
| `/profile/sarisyahputra1511` | 200 |  | Lina Pratama | 929 | - | tidak |
| `/profile/ulfamaulana1066` | 200 |  | Oki Putri | 900 | - | tidak |
| `/profile/ulihartono1443` | 200 |  | Krisna Lestari | 929 | - | tidak |

### Mahasiswa

| Rute | HTTP | Berakhir di | H1 | Tinggi (px) | Gambar isi (termuat/total) | Overflow 390 px |
|---|---|---|---|---|---|---|
| `/student/applications` | 200 |  | Aplikasi Saya | 977 | - | tidak |
| `/student/applications/tracker` | 200 |  | Application Tracker | 1593 | - | tidak |
| `/student/browse-problems` | 200 |  | Telusuri Masalah | 2297 | 0/12 | tidak |
| `/student/chatbot` | 200 |  | SAGO | 900 | - | tidak |
| `/student/dashboard` | 200 |  | Selamat datang, Oki Putri | 1041 | - | tidak |
| `/student/friends` | 200 |  | Jaringan Anda | 900 | - | tidak |
| `/student/friends/activities` | 500 (5xx, tanpa tangkapan) |  |  | - | - | - |
| `/student/friends/search` | 200 |  | Temukan Koneksi | 2300 | - | tidak |
| `/student/jobs` | 200 |  | Lowongan Kerja & Magang | 2607 | - | tidak |
| `/student/jobs/alerts` | 200 |  | Notifikasi Lowongan | 900 | - | tidak |
| `/student/jobs/compare` | 200 | `/student/jobs` | Lowongan Kerja & Magang | 2607 | - | tidak |
| `/student/jobs/saved` | 200 |  | Lowongan Tersimpan | 900 | - | tidak |
| `/student/portfolio` | 200 | `/student/profile` | Oki Putri | 1113 | - | tidak |
| `/student/problems` | 200 |  | Telusuri Masalah | 2297 | 0/12 | tidak |
| `/student/profile` | 200 |  | Oki Putri | 1113 | - | tidak |
| `/student/profile/edit` | 200 |  | Edit profil | 2607 | - | tidak |
| `/student/projects` | 200 |  | Proyek Saya | 900 | - | tidak |
| `/student/repository` | 200 |  | Repositori Pengetahuan | 1867 | - | tidak |
| `/student/wishlist` | 200 |  | Wishlist Saya | 900 | - | tidak |
| `/student/browse-problems/5` | 200 |  | Pelatihan Keterampilan Warga Miskin | 3057 | 5/5 | tidak |
| `/student/browse-problems/66` | 200 |  | Rehabilitasi Lahan Kritis | 3077 | 3/3 | tidak |
| `/student/applications/330` | 200 |  | Koperasi Simpan Pinjam Desa | 1395 | - | tidak |
| `/student/applications/331` | 200 |  | Edukasi Kesehatan Dan Sanitasi | 1108 | - | tidak |
| `/student/applications/332` | 200 |  | Konservasi Terumbu Karang | 1108 | - | tidak |
| `/student/friends/profile/395` | 200 | `/profile/sintanugroho1394` | Arif Firmansyah | 929 | - | tidak |
| `/student/friends/profile/46` | 200 | `/profile/rezaanggraini1045` | Andi Nurfadilah | 900 | - | tidak |
| `/student/friends/profile/9` | 200 | `/profile/bungaazhari1008` | Widi Hidayat | 900 | - | tidak |
| `/student/jobs/363` | 200 |  | Conservation Volunteer - Reboisasi & Restorasi E | 2019 | 2/2 | YA |
| `/student/jobs/573` | 200 |  | Conservation Volunteer - Reboisasi & Restorasi E | 2019 | 2/2 | YA |
| `/student/repository/57` | 200 |  | Laporan Akhir KKN - Pengurangan Emisi Gas Rumah  | 1095 | - | tidak |
| `/student/repository/63` | 200 |  | Laporan Akhir KKN - Penghijauan Dan Konservasi L | 1095 | - | tidak |
| `/student/repository/98` | 200 |  | Laporan Akhir KKN - Penguatan Kelembagaan Desa | 1095 | - | tidak |
| `/student/problems/5` | 200 |  | Pelatihan Keterampilan Warga Miskin | 3057 | 5/5 | tidak |
| `/student/applications/create/5` | 200 |  | Ajukan lamaran | 1212 | - | tidak |
| `/student/projects/77` | 200 |  | Pembangkit Listrik Mikrohidro | 2101 | 1/1 | tidak |
| `/student/projects/77/report/create` | 200 |  | Buat Laporan Progress | 2151 | - | tidak |
| `/student/projects/77/final-report/create` | 200 |  | Submit Laporan Akhir | 2136 | - | tidak |

### Institusi

| Rute | HTTP | Berakhir di | H1 | Tinggi (px) | Gambar isi (termuat/total) | Overflow 390 px |
|---|---|---|---|---|---|---|
| `/institution/applications` | 200 |  | Aplikasi | 1529 | - | tidak |
| `/institution/dashboard` | 200 |  | Dashboard | 1717 | - | tidak |
| `/institution/problems` | 200 |  | Masalah | 950 | - | tidak |
| `/institution/problems/create` | 200 |  | Buat masalah baru | 3573 | - | tidak |
| `/institution/profile` | 200 |  | Pemerintah Desa Cangkringan | 1299 | - | tidak |
| `/institution/profile/edit` | 200 |  | Edit profil instansi | 1925 | - | tidak |
| `/institution/projects` | 200 |  | Proyek | 1802 | - | tidak |
| `/institution/reviews` | 200 |  | Ulasan | 1571 | - | tidak |
| `/institution/verification/status` | 200 |  | Status Verifikasi Dokumen | 1208 | - | tidak |
| `/student/browse-problems/11` | 200 | `/institution/dashboard` | Dashboard | 1717 | - | tidak |
| `/student/browse-problems/37` | 200 | `/institution/dashboard` | Dashboard | 1717 | - | tidak |
| `/institution/applications/264` | 200 |  | Rafi Syahputra | 954 | - | tidak |
| `/institution/applications/269` | 200 |  | Widi Hidayat | 913 | - | tidak |
| `/institution/applications/284` | 200 |  | Candra Susanto | 969 | - | tidak |
| `/institution/problems/4` | 200 |  | Program Beasiswa Anak Tidak Mampu | 1631 | 4/4 | tidak |
| `/institution/problems/42` | 200 |  | Digitalisasi Administrasi Desa | 1431 | 2/2 | tidak |
| `/institution/projects/118` | 200 |  | Digitalisasi Administrasi Desa | 1357 | - | tidak |
| `/institution/projects/119` | 200 |  | Digitalisasi Administrasi Desa | 1331 | - | tidak |
| `/institution/projects/188` | 200 |  | Program Beasiswa Anak Tidak Mampu | 1278 | - | tidak |
| `/institution/problems/4/edit` | 200 |  | Ubah masalah | 4410 | 4/4 | tidak |
| `/institution/problems/77/edit` | 200 |  | Ubah masalah | 4410 | 4/4 | tidak |
| `/institution/projects/118/manage` | 200 |  | Kelola Proyek | 1736 | - | tidak |
| `/institution/projects/188/manage` | 200 |  | Kelola Proyek | 1672 | - | tidak |
| `/institution/projects/217/manage` | 200 |  | Kelola Proyek | 1720 | - | tidak |
| `/institution/reviews/154` | 200 |  | Ulasan untuk Hilda Syahputra | 900 | - | tidak |
| `/institution/reviews/155` | 200 |  | Ulasan untuk Hesti Maharani | 900 | - | tidak |
| `/institution/reviews/90` | 200 |  | Ulasan untuk Vira Nugroho | 900 | - | tidak |
| `/institution/reviews/154/edit` | 200 |  | Ubah ulasan untuk Hilda Syahputra | 1173 | - | tidak |
| `/institution/reviews/155/edit` | 200 |  | Ubah ulasan untuk Hesti Maharani | 1173 | - | tidak |
| `/institution/projects/119/milestones/473` | 200 |  |  | - | - | - |
| `/institution/reviews/create/117` | 200 | `/institution/projects/117` | Digitalisasi Administrasi Desa | 1462 | - | tidak |

### Perusahaan

| Rute | HTTP | Berakhir di | H1 | Tinggi (px) | Gambar isi (termuat/total) | Overflow 390 px |
|---|---|---|---|---|---|---|
| `/company/applications` | 200 |  | Lamaran | 2209 | - | tidak |
| `/company/dashboard` | 200 |  | Dashboard | 2052 | - | YA |
| `/company/jobs` | 200 |  | Lowongan | 1310 | - | tidak |
| `/company/jobs/create` | 200 |  | Pasang lowongan | 1214 | - | tidak |
| `/company/profile` | 200 |  | Koperasi Tani Makmur Jaya | 1381 | - | tidak |
| `/company/profile/edit` | 200 |  | Edit profil perusahaan | 2279 | - | tidak |
| `/company/talents` | 200 |  | Talenta | 2118 | - | tidak |
| `/company/talents/compare` | 200 | `/company/talents` | Talenta | 2118 | - | tidak |
| `/company/talents/saved` | 200 |  | Talenta Tersimpan | 1995 | - | tidak |
| `/student/browse-problems/34` | 200 | `/company/dashboard` | Dashboard | 2052 | - | YA |
| `/student/browse-problems/58` | 200 | `/company/dashboard` | Dashboard | 2052 | - | YA |
| `/company/applications/1148` | 200 |  | Putra Rachman | 1344 | - | tidak |
| `/company/applications/1158` | 200 |  | Qomar Maulana | 1218 | - | tidak |
| `/company/applications/1159` | 200 |  | Ulfa Maulana | 1218 | - | tidak |
| `/company/jobs/622` | 200 |  | Waste Management Officer - Bank Sampah & Kampany | 2085 | - | tidak |
| `/company/jobs/624` | 200 |  | Analis Data Hasil Panen Anggota | 1709 | - | tidak |
| `/company/jobs/619/edit` | 200 |  | Edit Lowongan Pekerjaan | 1280 | - | YA |
| `/company/jobs/623/edit` | 200 |  | Edit Lowongan Pekerjaan | 1280 | - | YA |

### Kondisi UI (query string, 404, akses lintas peran)

| Kondisi | Rute yang dibuka | HTTP | Berakhir di | Catatan |
|---|---|---|---|---|
| guest-dashboard | `/student/dashboard` | 200 | `/login` |  |
| guest-profile-404 | `/profile/tidak-ada-xyz` | 404 | `/profile/tidak-ada-xyz` |  |
| guest-login-error | `(form login, kata sandi salah sengaja)` | - | `/login` |  |
| browse-gallery | `/student/browse-problems?view=gallery` | 200 | `/student/browse-problems` | uji awal tidak valid: `gallery` bukan nilai `view` yang dikenal (yang valid `list`, `grid`, `map`); lihat `grid-view` |
| browse-map | `/student/browse-problems?view=map` | 200 | `/student/browse-problems?view=map` | kartu=12; peta ubin=12 |
| browse-noresult | `/student/browse-problems?search=zzzzqqqq` | 200 | `/student/browse-problems?search=zzzzqqqq&view=map` | uji awal tidak valid: preferensi tampilan Peta dari localStorage terbawa; lihat `list-noresult` |
| browse-lastpage | `/student/browse-problems?page=7` | 200 | `/student/browse-problems?page=7&view=map` | uji awal tidak valid (tampilan Peta terbawa); lihat `list-lastpage` |
| browse-beyond | `/student/browse-problems?page=99` | 200 | `/student/browse-problems?page=99&view=map` | uji awal tidak valid (tampilan Peta terbawa); lihat `list-beyond` |
| browse-404 | `/student/browse-problems/999999` | 404 | `/student/browse-problems/999999` |  |
| jobs-noresult | `/student/jobs?search=zzzzqqqq` | 200 | `/student/jobs?search=zzzzqqqq` |  |
| repo-noresult | `/student/repository?search=zzzzqqqq` | 200 | `/student/repository?search=zzzzqqqq` |  |
| application-404 | `/student/applications/999999` | 500 | `/student/applications/999999` | 5xx, tanpa tangkapan |
| cross-role-institution | `/institution/dashboard` | 200 | `/student/dashboard` |  |
| non-admin-admin | `/admin/dashboard` | 403 | `/admin/dashboard` |  |
| mobile-menu | `MENU:/student/dashboard` | 200 | `/student/dashboard` | klik gagal pada uji awal (selektor saya mengenai tombol menu akun yang tersembunyi). Diuji ulang dengan benar: tombol "Buka menu" membuka panel berisi 10 tautan, Escape menutup, fokus kembali ke tombol. |
| notif-dropdown | `NOTIF:/student/dashboard` | 200 | `/student/dashboard` |  |
| inst-problems-noresult | `/institution/problems?search=zzzzqqqq` | 200 | `/institution/problems?search=zzzzqqqq` |  |
| inst-problem-404 | `/institution/problems/999999` | 404 | `/institution/problems/999999` |  |
| co-talents-noresult | `/company/talents?search=zzzzqqqq` | 200 | `/company/talents?search=zzzzqqqq` |  |
| co-job-404 | `/company/jobs/999999` | 404 | `/company/jobs/999999` |  |
| grid-view | `/student/browse-problems?view=grid` | 200 | `/student/browse-problems?view=grid` | kartu=12; foto kartu termuat=12 |
| list-noresult | `/student/browse-problems?view=list&search=zzzzqqqq` | 200 | `/student/browse-problems?search=zzzzqqqq` |  |
| list-lastpage | `/student/browse-problems?view=list&page=7` | 200 | `/student/browse-problems?page=7` | kartu=5 |
| list-beyond | `/student/browse-problems?view=list&page=99` | 200 | `/student/browse-problems?page=99` |  |
| grid-lastpage | `/student/browse-problems?view=grid&page=7` | 200 | `/student/browse-problems?view=grid&page=7` | kartu=5; foto kartu termuat=5 |
