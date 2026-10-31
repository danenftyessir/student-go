<div align="center">
  <img src="public/assets/brand/student-go-logo.svg" alt="Student Go" width="280"/>
</div>

# Student Go

**Infrastruktur rekognisi nasional untuk Kuliah Kerja Nyata (KKN):** setiap laporan KKN menjadi aset pengetahuan, setiap proyek menjadi portofolio terverifikasi, dan setiap mahasiswa menjadi talent yang dapat diakses industri.

| | |
|---|---|
| Subtema | Inclusive Education & Future Skills (utama); Smart Public Services & Digital Trust (sekunder) |
| Ketua Tim | Danendra Shafi Athallah (13523136), ITB STEI |
| Anggota | M. Abizzar Gamadrian (13523155), ITB STEI |
| Anggota | Kenzie Raffa Ardhana (18223127), ITB STEI |

Dokumen acuan: [docs/Proposal_StudentGo.pdf](docs/Proposal_StudentGo.pdf)

## Daftar Isi

1. [Problem Statement](#problem-statement)
2. [Solusi: Empat Pilar](#solusi-empat-pilar)
3. [Target Pengguna](#target-pengguna)
4. [Pendekatan Teknologi dan AI](#pendekatan-teknologi-dan-ai)
5. [Arsitektur dan Tech Stack](#arsitektur-dan-tech-stack)
6. [Menjalankan Secara Lokal](#menjalankan-secara-lokal)
7. [Fitur MVP dan Roadmap](#fitur-mvp-dan-roadmap)
8. [AI Use Declaration](#ai-use-declaration)
9. [Third-Party Declaration](#third-party-declaration)
10. [Referensi](#referensi)

## Problem Statement

Indonesia menghadapi paradoks: **1,01 juta sarjana menganggur** (BPS, Februari 2025) sementara **46% perusahaan kesulitan menemukan talent yang tepat** (Populix & KitaLulus, 2024). Di tengah kesenjangan itu, **520.000 mahasiswa menjalani KKN setiap tahun**, tetapi pengalamannya tidak diubah menjadi nilai yang diakui. Proposal mengidentifikasi tiga akar masalah (Issue Tree dan 5 Whys):

| Kode | Akar masalah | Gejala |
|---|---|---|
| RC1 | Ketidaksesuaian program | Hanya 23% program KKN relevan dengan prioritas pembangunan daerah; 68% kepala desa menyatakan program tidak menjawab masalah inti wilayahnya (Hidajat, 2019). Tidak ada pencocokan berbasis data antara kompetensi mahasiswa dan masalah komunitas. |
| RC2 | Ketiadaan validasi karir | 82% alumni KKN tidak dapat memakai pengalaman lapangan sebagai portofolio yang diakui industri. Penilaian instansi manual, tanpa rekam digital, dan tidak dapat diverifikasi perusahaan. |
| RC3 | Pengetahuan tersebar dan tidak dapat diakses | Laporan KKN tersimpan terfragmentasi dan tidak terindeks; setiap generasi mengulang investigasi dari nol. Dari sekitar 83.436 desa, baru sekitar 14.000 (16,6%) yang memakai sistem digital (Kemendesa PDT, 2024). |

**Pertanyaan inti:** bagaimana mengoptimalkan 520.000 mahasiswa KKN per tahun agar menghasilkan dampak daerah yang terukur sekaligus nilai karir yang diakui industri?

> Angka pada bagian ini dikutip dari proposal beserta sumbernya; lihat [Referensi](#referensi).

## Solusi: Empat Pilar

Student Go tidak menambah aplikasi baru, tetapi menyatukan marketplace masalah, basis pengetahuan, portofolio terverifikasi, dan talent marketplace dalam satu ekosistem berbasis bukti. Setiap pilar menjawab satu akar masalah.

| Pilar | Menjawab | Isi |
|---|---|---|
| 1. Marketplace Masalah | RC1 | Instansi mempublikasikan masalah prioritas, dikategorikan menurut 17 tujuan SDGs dan lokasi. Mahasiswa mencari, memfilter, dan mengajukan proposal berbasis kompetensi. |
| 2. Knowledge Repository + AI Assistant (RAG) | RC3 | Laporan KKN menjadi basis pengetahuan yang dapat ditanyai lewat chatbot RAG. Sistem hanya menjawab dari dokumen yang tersedia; bila tidak ada jawaban, sistem menjawab eksplisit bahwa informasi tidak tersedia dalam dokumentasi Student Go. |
| 3. Evidence-Based Impact Portfolio | RC2 | Setelah proyek divalidasi instansi, sertifikat digital diterbitkan otomatis (tanda tangan pejabat, cap instansi, timestamp, metadata verifikasi) dan portofolio terbentuk otomatis. |
| 4. Smart Talent Marketplace | RC2 + RC3 | Perusahaan melihat Verified Talent Profile berbasis proyek KKN yang selesai, mencari talent menurut skill dan lokasi pengalaman, dan membandingkan kandidat berdampingan. |

Pembeda: bukan LinkedIn (tanpa bukti terverifikasi), bukan Jobstreet (tanpa integrasi KKN), bukan portal KKN Kemendesa (tanpa jembatan karir).

## Target Pengguna

Platform memiliki tiga peran terautentikasi, ditambah halaman publik (beranda, tentang, kontak, profil publik institusi dan mahasiswa).

| Peran | Pain point | Nilai yang diterima |
|---|---|---|
| **Mahasiswa** (520.000 per tahun) | Tidak ada portofolio terverifikasi; KKN tidak diakui industri; pencarian masalah manual dan tidak sesuai kompetensi. | Portofolio berbasis bukti instansi, sertifikat digital untuk lamaran kerja, akses basis pengetahuan laporan KKN lewat AI Assistant, rekomendasi lowongan berbasis skill. |
| **Instansi pemerintah daerah** (83.436 desa dan dinas) | Publikasi masalah tidak terstruktur; seleksi mahasiswa tanpa data kompetensi; verifikasi administratif lambat. | Publikasi masalah terstruktur menurut SDGs, seleksi berbasis skill dan lokasi, dashboard progres proyek, penerbitan sertifikat otomatis. |
| **Perusahaan dan mitra industri** | CV tidak dapat diverifikasi; screening memakan waktu dan biaya. | Verified Talent Profile berbasis bukti kerja lapangan, rekomendasi talent, perbandingan kandidat, evidence-based hiring. |

**Dampak yang diharapkan** (proyeksi proposal, bukan hasil terukur): 60.000 mahasiswa onboarded di Tahun 1 dengan 35% mendapat pekerjaan lebih cepat; relevansi program KKN naik dari 23% menuju target 60% di Tahun 2. Mendukung SDG 4 (Pendidikan Berkualitas) dan SDG 8 (Pekerjaan Layak dan Pertumbuhan Ekonomi).

## Pendekatan Teknologi dan AI

RAG dipilih dibanding fine-tuning karena jawabannya dibatasi pada dokumen yang diindeks, dapat diperbarui tanpa retraining, dan biaya operasionalnya jauh lebih rendah.

Pipeline RAG menurut proposal:

1. **Ingestion**: laporan diunggah, dibersihkan, di-chunk menurut struktur semantik, lalu diubah menjadi embedding dengan Cohere `embed-multilingual-v3`.
2. **Storage**: embedding disimpan di Supabase `pgvector`.
3. **Retrieval**: pencarian kemiripan semantik (cosine similarity) mengambil k dokumen paling relevan.
4. **Generation**: konteks dikirim ke Claude (Anthropic) dengan system prompt yang melarang menjawab di luar dokumen, disertai kutipan sumber.
5. **Anti-hallucination layer**: bila skor kemiripan di bawah ambang, sistem mengembalikan jawaban standar: *"Maaf, informasi ini tidak tersedia dalam dokumentasi StudentGo."*

**AI Governance:** keputusan verifikasi instansi melibatkan konfirmasi manusia (human-in-the-loop); aksi AI dicatat dalam audit trail; selaras dengan NIST AI RMF 1.0 (Govern, Map, Measure, Manage); data dikelola sesuai UU Pelindungan Data Pribadi No. 27 Tahun 2022 dan tidak ada PII di vector store.

Implementasi di repositori ini terdapat pada `app/Services/` (`CohereAIService`, `ClaudeAIService`, `DocumentRetrieverService`, `PromptBuilderService`, `ChatbotService`). Ambang kemiripan dan perilaku penyimpanan vektor mengikuti kode tersebut; bila berbeda dengan uraian proposal di atas, kode adalah acuan yang berlaku.

## Arsitektur dan Tech Stack

Arsitektur monolitik (Laravel) dipilih untuk menghindari overhead komunikasi antarlayanan.

| Lapisan | Teknologi |
|---|---|
| Framework | Laravel 13, PHP 8.3 |
| Autentikasi | Laravel Sanctum, RBAC tiga peran (mahasiswa, instansi, perusahaan) ditambah area admin |
| Tampilan | Blade, Tailwind CSS 4, Alpine.js, Vite |
| Database dan storage | PostgreSQL (Supabase), Supabase Storage untuk seluruh unggahan |
| AI | Cohere (embedding), Claude / Anthropic (generation) |
| Sertifikat | Dirender dengan GD dari templat PNG (`CertificateService`) |
| Peta | Leaflet |

Peta kode: controller per peran di `app/Http/Controllers/{Student,Institution,Company,Admin}`, logika bisnis di `app/Services`, view di `resources/views/<peran>`, aset desain di `resources/css/student-go` dan `public/assets`.

## Menjalankan Secara Lokal

Prasyarat: PHP 8.3 (ekstensi `gd`, `pdo_pgsql`), Composer, Node.js, serta proyek Supabase (PostgreSQL dan Storage).

```bash
git clone <url-repositori>
cd StudentGo

cp .env.example .env
# isi .env: koneksi database, kredensial Supabase Storage,
# COHERE_API_KEY, CLAUDE_API_KEY

composer install
php artisan key:generate
php artisan migrate
npm install
npm run build

php artisan serve    # http://127.0.0.1:8000
```

Mode pengembangan (server, queue, log, dan Vite sekaligus): `composer dev`.

Tes: `php artisan test` (memakai SQLite in-memory dan memblokir panggilan HTTP keluar).

Jangan memasukkan `.env` atau kunci API ke repositori.

## Fitur MVP dan Roadmap

**MVP (target Hacking Day 24 jam):**

- Platform multi-role terautentikasi untuk mahasiswa, instansi, dan perusahaan
- Problem Marketplace: CRUD masalah, filter SDGs dan lokasi, sistem aplikasi, review, dan keputusan oleh instansi
- RAG Knowledge Assistant dengan kutipan sumber
- Impact Portfolio otomatis setelah validasi instansi
- Generator sertifikat digital
- Talent Profile yang dapat diakses perusahaan

**Roadmap pasca-hackathon:**

| Fase | Waktu | Sasaran |
|---|---|---|
| 1 | 2026 Q4 sampai 2027 Q1 | Pilot dengan 3 universitas dan 10 instansi mitra |
| 2 | 2027 | 50 universitas, 200 instansi, 20 perusahaan mitra |
| 3 | 2028 dan seterusnya | Integrasi API pemerintah (Kemendesa), aplikasi mobile Android/iOS, ekspansi nasional; AI matching score masalah-kompetensi; dashboard analitik untuk universitas dan Kemendikbud |

## AI Use Declaration

- **AI sebagai fitur produk:** Cohere untuk embedding dan semantic search; Claude (Anthropic) untuk menghasilkan jawaban chatbot, saran proyek, dan bantuan verifikasi dokumen institusi. Hasil verifikasi dokumen oleh AI tetap memerlukan konfirmasi manusia.
- **AI sebagai alat pengembangan:** asisten kode berbasis AI (Claude Code) dipakai untuk membantu pengembangan dan dokumentasi. Seluruh keluaran ditinjau tim sebelum digabungkan.

## Third-Party Declaration

| Komponen | Lisensi / ketentuan | Keterangan |
|---|---|---|
| Laravel, Sanctum, Tinker, smalot/pdfparser, flysystem-aws-s3-v3 | MIT / LGPL sesuai paket | Lihat `composer.lock` |
| Tailwind CSS, Vite, Alpine.js | MIT | Lihat `package.json` |
| Leaflet, leaflet.markercluster | BSD-2 / MIT | Peta |
| Plus Jakarta Sans, Source Serif 4 | SIL OFL | Font lokal di `public/assets/fonts` |
| Lucide | ISC | Ikon, disajikan lokal |
| Poppins, Pinyon Script | SIL OFL | Hanya untuk sertifikat |
| Cohere API, Anthropic API, Supabase | Layanan pihak ketiga, tunduk pada ketentuan masing-masing | Memerlukan kunci API sendiri |

Status hak atas sebagian foto di `public/` masih dikonfirmasi tim; lihat `student-go-docs/redesign-execution/ASSET_INVENTORY.md`.

## Referensi

- Badan Pusat Statistik. (2025). *Keadaan Ketenagakerjaan Indonesia*, Februari dan Agustus 2025.
- Populix & KitaLulus. (2024). *Indonesia Talent & Recruitment Landscape 2024*.
- Hidajat, R. (2019). Evaluasi Program Kuliah Kerja Nyata di Indonesia. *Jurnal Pengabdian Masyarakat Indonesia*, 4(2), 101-112.
- Kementerian Desa, PDT. (2024). *Data Desa Digital Indonesia 2024*.
- Kementerian Komunikasi dan Digital. (2025). *Roadmap AI Nasional Indonesia 2025-2029* dan peluncuran AICoE.
- NIST. (2023). *AI Risk Management Framework 1.0*.
- Lewis, P., dkk. (2020). Retrieval-Augmented Generation for Knowledge-Intensive NLP Tasks. *NeurIPS 33*.
- Undang-Undang Nomor 27 Tahun 2022 tentang Pelindungan Data Pribadi.

Daftar referensi lengkap ada di proposal.
