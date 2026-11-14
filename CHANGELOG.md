# Finalist Changelog

Perubahan dari `submission-24h` (tag `v1-24h`, commit `b803023`) menuju `finalist-release`.

Seluruh perubahan berada dalam ruang lingkup penyempurnaan finalis: perbaikan bug, polish UI/UX, peningkatan performa, keamanan dasar, dan dokumentasi. Substansi produk tidak berubah: tidak ada tabel, migrasi, peran, atau alur bisnis baru. Setiap perubahan ada di branch tersendiri (`fix/`, `feat/`, `chore/`, `docs/`), digabung lewat `dev` ke `main`, dan diperiksa dengan rangkaian tes otomatis (477 tes lulus pada `finalist-release`).

Ringkasan: 46 commit, 45 di antaranya dari `v1-24h` sampai penggabungan terakhir ke `main` dan 1 commit dokumentasi untuk rilis ini; sekitar 125 berkas berubah.

## 1. Perbaikan bug

| Perubahan | Commit |
|---|---|
| Judul tab browser tidak lagi ter-escape dua kali (mis. `&amp;`) | `8526152` |
| Komentar di dalam atribut komponen merusak panel detail lamaran; diperbaiki dan kini diuji dengan merender halaman | `de23541` |
| Tanggal tinjau lamaran diberi label "Diputuskan" hanya bila lamaran sudah diterima atau ditolak | `fc1645d` |
| Berkas dokumen yang belum ada di storage menampilkan pesan "Berkas ini belum tersedia di penyimpanan", bukan JSON galat | `63b4872` |
| Halaman bandingkan lowongan memakai design system dan tidak mengarang nilai untuk data kosong | `70b6675` |
| Tombol simpan di halaman bandingkan talenta menampilkan status "Tersimpan" sesuai data | `3f6e0e4` |
| Masalah yang tenggatnya lewat ditandai "Pendaftaran ditutup" di daftar dan galeri | `afc345d` |
| Pemanggilan `fetch` di view memakai `route()` alih-alih path yang ditulis manual | `18c203f`, `2639758` |
| Build CSS tidak lagi bergantung pada cache view hasil kompilasi | `79eaf4d` |
| Tombol yang sudah punya spinner tidak diberi spinner kedua saat submit | `1818b22` |
| Ejaan footer dan parameter nullable eksplisit untuk kompatibilitas PHP 8.4 | `01ff150` |

## 2. UI/UX polish

| Perubahan | Commit |
|---|---|
| Tombol kirim form POST menampilkan status proses dan mencegah submit ganda | `f13e321` |
| Filter Telusuri Masalah diterapkan lewat tombol "Terapkan", tidak memuat ulang di setiap perubahan | `54a097f` |
| Kerangka (skeleton) saat memuat notifikasi; status memuat dan galat pada peta beranda | `862ebbd` |
| Gerak halus 150-200 ms pada menu, dialog, drawer, toast, dan tombol; dimatikan untuk `prefers-reduced-motion` | `135f5ba` |
| Tooltip pada tombol ikon dan latar gelap untuk menu mobile | `bf0be6f` |
| Tabel tidak memenggal kata, muat di tablet, dan lebih ringkas di mobile; kontrol formulir setinggi 40 px | `ba2fb23`, `cc39c2c`, `6ffb4f0`, `0e39af0` |
| Ukuran teks 13 px dan 15 px disamakan dengan skala tipografi | `14b41a0` |
| Bagian tahapan, pilihan peran, dan angka hasil proyek di beranda dibuat seragam dan sejajar | `986661b`, `52d9556`, `5adac6c` |
| Pilihan SDG di peta beranda diurutkan menurut nomor SDG, provinsi menurut abjad | `e146306`, `1b137f8` |
| Kotak warna SDG yang tidak bermakna dihapus; chip SDG cukup teks | `97651d4` |
| Istilah "aplikasi" untuk lamaran diseragamkan menjadi "lamaran" | `1426913` |
| Tombol Simpan/Tersimpan di daftar talenta sama lebar; jumlah proyek di halaman bandingkan rata kiri | `a1203a5`, `0812e11` |
| Keterangan kecil "Data uji coba (demo)" pada angka beranda, agar juri tahu data bersifat sintetis | `a18d0cb` |

## 3. Bukti validasi (menindaklanjuti masukan juri)

Masukan juri menyoroti bahwa klaim "portofolio terverifikasi" perlu bukti yang bisa dicek. Perubahan berikut hanya menampilkan data proyek selesai yang sudah ada; tidak ada data atau alur bisnis baru.

| Perubahan | Commit |
|---|---|
| Halaman publik `/verifikasi/{kode}` untuk mengecek keaslian proyek KKN yang sudah selesai; kode ditandatangani HMAC dengan `APP_KEY` sehingga tidak bisa ditebak, dibatasi 60 permintaan per menit; tautan "Cek keaslian" di portofolio | `8263866` |
| Lencana "N divalidasi instansi" di kartu talenta perusahaan (hanya menghitung proyek berstatus selesai) dan lencana yang sama di portofolio | `a5b574c`, `0833d28` |

## 4. Performa

| Perubahan | Commit |
|---|---|
| Polling notifikasi berhenti saat tab browser tidak terlihat | `17b2035` |
| Status pertemanan di pencarian koneksi diambil dalam satu query (sebelumnya satu query per mahasiswa) | `ddcaafc` |
| Hitungan per status di halaman lamaran, masalah, proyek, dan analitik memakai satu query berkelompok | `ff80dd8` |
| Foto tim di halaman Tentang Kami memakai WebP 480 px dengan PNG sebagai cadangan | `e5eab2d` |

## 5. Kebersihan kode

| Perubahan | Commit |
|---|---|
| Menghapus 11 view, 6 CSS, 3 JS, dan method controller yang tidak pernah dipakai | `6cff24c`, `6fca596` |
| Membersihkan alias font dan kelas lama di `legacy-overrides.css` | `40c5761` |
| Label jenis dan gaji lowongan dari satu helper | `e234966` |

## 6. Dokumentasi dan data

| Perubahan | Commit |
|---|---|
| Kebijakan branch dan commit (D-27) dan inventaris aset diperbarui | `d59620a`, `18518c9` |
| README final: panduan demo, kontribusi tim, known limitations; changelog ini | rilis ini |
| Data uji manual (akun "tes tes", instansi uji "Desa Sukamundur" beserta masalahnya, draf masalah uji, perusahaan kosong uji) dihapus dari database demo; cadangan disimpan tim di luar repositori | tanpa commit (operasi data) |

## Pengujian

- `php vendor/bin/phpunit`: 477 tes lulus (SQLite in-memory, panggilan HTTP keluar diblokir).
- `npm run build`: berhasil.
- Pemeriksaan di browser (Playwright) untuk halaman yang berubah, di desktop dan mobile.
