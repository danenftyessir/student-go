@extends('layouts.app')

@section('title', 'Tentang Kami')

{{-- phase 5: tentang kami sebagai artikel yang di-scroll (pengganti carousel 7 slide, J34).
     isi ditulis dari fakta produk (D-23 b, DRAFT); tanpa angka tanpa sumber (J9), tanpa sejarah,
     usia, misi atau klaim dampak era karsa (RN-07, RN-08, RN-09; D-37). foto: dokumentasi KKN ITB 2025
     (hak dikonfirmasi D-30), turunan webp (D-34), caption tanpa kredit (D-19). --}}

@php
    // alur per peran: isi sama dengan diagram alur paket aset (fungsi yang diverifikasi terhadap kode di phase 4)
    $roles = [
        [
            'id' => 'mahasiswa',
            'icon' => 'graduation-cap',
            'title' => 'Mahasiswa',
            'steps' => [
                ['Telusuri masalah', 'Saring menurut provinsi, SDG, tingkat kesulitan, dan durasi, atau buka peta.'],
                ['Kirim lamaran', 'Tulis motivasi minimal 100 karakter dan lampirkan berkas yang diminta.'],
                ['Pantau tinjauan', 'Lihat status lamaran dan linimasanya di halaman Aplikasi Saya.'],
                ['Jalankan proyek', 'Lamaran yang diterima tampil di halaman Proyek Saya.'],
                ['Bangun portofolio', 'Hasil dan penilaian instansi tampil di profil dan portofolio publik.'],
            ],
        ],
        [
            'id' => 'instansi',
            'icon' => 'building-2',
            'title' => 'Instansi',
            'steps' => [
                ['Daftar dan verifikasi', 'Unggah dokumen verifikasi saat mendaftar.'],
                ['Publikasikan masalah', 'Tulis lokasi, SDG, kuota mahasiswa, tenggat, dan persyaratannya.'],
                ['Tinjau pelamar', 'Terima atau tolak lamaran yang masuk.'],
                ['Pantau proyek', 'Ikuti proyek yang sedang berjalan bersama mahasiswa.'],
                ['Beri ulasan', 'Nilai kontribusi mahasiswa setelah proyek selesai.'],
            ],
        ],
        [
            'id' => 'perusahaan',
            'icon' => 'briefcase',
            'title' => 'Perusahaan',
            'steps' => [
                ['Cari talenta', 'Telusuri mahasiswa dengan pencarian dan filter.'],
                ['Simpan dan bandingkan', 'Kumpulkan kandidat ke daftar tersimpan, lalu bandingkan.'],
                ['Terbitkan lowongan', 'Susun lowongan lewat formulir bertahap.'],
                ['Kelola pelamar', 'Pantau pelamar per tahap di halaman Lamaran.'],
            ],
        ],
    ];
@endphp

@section('content')
<article class="sg-about">
    {{-- kepala artikel: pita terang dua kolom. kiri judul, pengantar, tautan ke tiap peran; kanan komposisi foto
         berbingkai di atas motif kontur (satu-satunya di halaman). foto berbeda dari beranda; tidak ada foto latar (J6). --}}
    <header class="sg-about-hero">
        <div class="sg-container sg-about-hero__grid">
            <div class="sg-about-hero__text">
                <p class="sg-marker">Tentang Kami</p>
                <h1 class="sg-about__title">Student Go mempertemukan instansi dan mahasiswa KKN</h1>
                <p class="sg-about__lead">
                    Instansi di daerah menuliskan masalah yang mereka hadapi. Mahasiswa memilih masalah yang sesuai, melamar,
                    lalu mengerjakannya selama Kuliah Kerja Nyata. Hasilnya tercatat di portofolio mahasiswa.
                </p>
                <nav class="sg-about-hero__roles" aria-label="Cara kerja per peran">
                    <span class="sg-about-hero__roles-label">Cara kerja untuk</span>
                    <ul>
                        @foreach ($roles as $role)
                            <li><a href="#peran-{{ $role['id'] }}"><x-icon :name="$role['icon']" size="sm" />{{ $role['title'] }}</a></li>
                        @endforeach
                    </ul>
                </nav>
            </div>

            <div class="sg-about-hero__media">
                <div class="sg-contour sg-about-hero__backdrop" aria-hidden="true"></div>
                <figure class="sg-figure sg-about-hero__main">
                    <img src="{{ asset('assets/photography/field/kkn-itb-2025-bersama-warga-di-kebun-1600.webp') }}"
                         srcset="{{ asset('assets/photography/field/kkn-itb-2025-bersama-warga-di-kebun-800.webp') }} 800w, {{ asset('assets/photography/field/kkn-itb-2025-bersama-warga-di-kebun-1600.webp') }} 1600w"
                         sizes="(min-width: 1024px) 55vw, calc(100vw - 2rem)"
                         alt="Mahasiswa berompi KKN ITB 2025, dilihat dari belakang, merapikan topi di kebun; di depannya seorang warga bertopi memegang cangkul."
                         width="1600" height="899" fetchpriority="high" decoding="async">
                    <figcaption>KKN ITB 2025. Di kebun bersama warga desa.</figcaption>
                </figure>
                <figure class="sg-figure sg-about-hero__inset">
                    <img src="{{ asset('assets/photography/field/upacara-bendera-bersama-warga-800.webp') }}"
                         alt="Tiga pengibar berseragam putih menaikkan bendera Merah Putih di lapangan rumput, disaksikan perangkat desa yang memberi hormat di bawah tenda merah putih."
                         width="800" height="450" loading="lazy" decoding="async">
                    <figcaption>Upacara bendera bersama warga desa.</figcaption>
                </figure>
            </div>
        </div>
    </header>

    {{-- 1. apa yang dikerjakan student go: kolom baca serif (catatan lapangan) --}}
    <section class="sg-about__section" aria-labelledby="about-apa">
        <div class="sg-container sg-about__split">
            <div>
                <p class="sg-about__num" aria-hidden="true">01</p>
                <h2 id="about-apa" class="sg-about__h2">Satu tempat sebelum KKN dimulai</h2>
            </div>
            <div class="sg-prose sg-about__prose">
                <p>
                    Kuliah Kerja Nyata menempatkan mahasiswa di desa dan kelurahan untuk bekerja bersama warga dan
                    pemerintah setempat. Student Go menyediakan tempat bagi kedua pihak itu untuk bertemu lebih dulu:
                    instansi menuliskan masalahnya, mahasiswa memilih masalah yang sesuai dengan bidangnya.
                </p>
                <p>
                    Setiap masalah ditulis lengkap: lokasi sampai tingkat desa, tujuan pembangunan berkelanjutan (SDG)
                    yang terkait, jumlah mahasiswa yang dibutuhkan, durasi, dan tenggat lamaran. Setelah proyek selesai,
                    instansi memberi penilaian, dan hasilnya tampil di portofolio mahasiswa.
                </p>
            </div>
        </div>
    </section>

    {{-- 2. cara kerja per peran: tiga kolom, setiap peran sebagai garis waktu vertikal (ol, teks, bukan gambar) --}}
    <section class="sg-about__section" aria-labelledby="about-cara">
        <div class="sg-container">
            <div class="sg-about__split sg-about__split--head">
                <div>
                    <p class="sg-about__num" aria-hidden="true">02</p>
                    <h2 id="about-cara" class="sg-about__h2">Cara kerja untuk setiap peran</h2>
                </div>
                <p class="sg-about__desc">Setiap akun memilih satu peran saat mendaftar, dan setiap peran punya ruang kerjanya sendiri.</p>
            </div>

            <div class="sg-about__roles">
                @foreach ($roles as $role)
                    <section class="sg-about__role" aria-labelledby="peran-{{ $role['id'] }}">
                        <h3 id="peran-{{ $role['id'] }}" class="sg-about__h3">
                            <x-icon :name="$role['icon']" />{{ $role['title'] }}
                        </h3>
                        <ol class="sg-about__steps">
                            @foreach ($role['steps'] as [$step, $detail])
                                <li>
                                    <strong>{{ $step }}</strong>
                                    <span>{{ $detail }}</span>
                                </li>
                            @endforeach
                        </ol>
                    </section>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 3. dari lapangan: dua foto dokumenter berbingkai dengan caption tempat/konteks --}}
    <section class="sg-about__section" aria-labelledby="about-lapangan">
        <div class="sg-container">
            <div class="sg-about__split sg-about__split--head">
                <div>
                    <p class="sg-about__num" aria-hidden="true">03</p>
                    <h2 id="about-lapangan" class="sg-about__h2">Dari lapangan</h2>
                </div>
                <p class="sg-about__desc">Dokumentasi kegiatan KKN ITB 2025.</p>
            </div>

            <div class="sg-about__photos">
                <figure class="sg-figure">
                    <img src="{{ asset('assets/photography/field/kkn-itb-2025-dinding-cap-tangan-1600.webp') }}"
                         srcset="{{ asset('assets/photography/field/kkn-itb-2025-dinding-cap-tangan-800.webp') }} 800w, {{ asset('assets/photography/field/kkn-itb-2025-dinding-cap-tangan-1600.webp') }} 1600w"
                         sizes="(min-width: 768px) 55vw, calc(100vw - 2rem)"
                         alt="Seorang pria bertopi memandangi dinding biru penuh cap tangan cat warna-warni, masing-masing dengan nama yang ditulis di bawahnya."
                         width="1600" height="899" loading="lazy" decoding="async">
                    <figcaption>KKN ITB 2025. Dinding cap tangan, masing-masing bertuliskan nama.</figcaption>
                </figure>
                <figure class="sg-figure">
                    <img src="{{ asset('assets/photography/field/kkn-itb-2025-rompi-cap-tangan-1600.webp') }}"
                         srcset="{{ asset('assets/photography/field/kkn-itb-2025-rompi-cap-tangan-800.webp') }} 800w, {{ asset('assets/photography/field/kkn-itb-2025-rompi-cap-tangan-1600.webp') }} 1600w"
                         sizes="(min-width: 768px) 40vw, calc(100vw - 2rem)"
                         alt="Rompi hijau keabuan berbordir bunga dan tulisan KKN ITB 2025, penuh cap tangan cat, tergantung pada tiang beton."
                         width="1600" height="899" loading="lazy" decoding="async">
                    <figcaption>KKN ITB 2025. Rompi peserta setelah penuh cap tangan cat.</figcaption>
                </figure>
            </div>
        </div>
    </section>

    {{-- 4. tim pengembang: nama + foto dari pemilik produk; kontak di halaman kontak --}}
    <section class="sg-about__section" aria-labelledby="about-tim">
        <div class="sg-container">
            <div class="sg-about__split sg-about__split--head">
                <div>
                    <p class="sg-about__num" aria-hidden="true">04</p>
                    <h2 id="about-tim" class="sg-about__h2">Tim pengembang</h2>
                </div>
                <p class="sg-about__desc">
                    Email setiap anggota tim tersedia di halaman
                    <a href="{{ route('contact') }}" class="sg-about__link">Kontak</a>.
                </p>
            </div>

            {{-- foto potongan transparan (latar sudah dihapus) di atas bidang terang --}}
            <ul class="sg-about__team">
                @foreach ($team as $member)
                    <li>
                        <div class="sg-about__portrait">
                            <img src="{{ asset($member['photo']) }}" alt="Foto {{ $member['name'] }}"
                                 width="480" height="600" loading="lazy" decoding="async">
                        </div>
                        <p>{{ $member['name'] }}</p>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
</article>
@endsection
