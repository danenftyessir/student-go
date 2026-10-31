@extends('layouts.app')

@section('title', 'Platform Digital untuk Kuliah Kerja Nyata')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@php
    // tujuan tombol utama: tamu mendaftar; pengguna yang sudah masuk kembali ke dashboard perannya
    $dashboardRoute = auth()->check()
        ? match (auth()->user()->user_type) {
            'student' => 'student.dashboard',
            'institution' => 'institution.dashboard',
            'company' => 'company.dashboard',
            'admin' => 'admin.dashboard',
            default => null,
        }
        : null;
    $hasData = $home !== null;
    $hasOpen = $hasData && $home['openTotal'] > 0;
    $difficulty = [
        'beginner' => [1, 'Pemula'],
        'intermediate' => [2, 'Menengah'],
        'advanced' => [3, 'Lanjut'],
    ];
    $example = $hasOpen ? $home['soonest']->first() : null;
@endphp

@section('content')

{{-- 1. pembuka dengan video latar (AD-11): judul, satu kalimat, aksi, tombol jeda. tanpa angka di dalamnya --}}
@include('home.components.hero', ['dashboardRoute' => $dashboardRoute])

{{-- 1b. ringkasan dan kebutuhan yang sedang dibuka (data nyata), dipindah dari pembuka lama tanpa diubah --}}
<div class="sg-home-hero sg-home-intro">
    <div class="sg-container sg-home-hero__grid">
        <div class="sg-home-hero__text">
            <p class="sg-home-lead">
                Instansi menuliskan masalah nyata di daerahnya. Mahasiswa memilih masalah yang sesuai, melamar,
                dan mengerjakannya sebagai proyek KKN. Hasilnya tercatat di portofolio mahasiswa.
            </p>

            @if ($hasOpen)
                <div class="sg-home-facts">
                    <p class="sg-home-facts__label">Saat ini di Student Go</p>
                    <x-ui.summary :items="$home['summary']" label="Data Student Go saat ini" />
                </div>
            @endif
        </div>

        @if ($hasOpen && count($home['sdgs']))
            <section class="sg-home-needs" aria-labelledby="home-needs-title">
                <h2 id="home-needs-title" class="sg-home-needs__title">Bidang yang paling banyak dibutuhkan</h2>
                <p class="sg-home-needs__desc">Jumlah masalah dibuka per tujuan SDG. Satu masalah bisa mencakup lebih dari satu tujuan.</p>
                <ol class="sg-home-bars">
                    @foreach (array_slice($home['sdgs'], 0, 6) as $sdg)
                        <li>
                            <span class="sg-home-bars__label"><span class="sg-home-bars__code">SDG {{ $sdg['id'] }}</span>{{ $sdg['label'] }}</span>
                            <span class="sg-home-bars__track" aria-hidden="true"><span class="sg-home-bars__fill" style="--w: {{ round($sdg['total'] / max(1, $home['sdgMax']) * 100) }}%"></span></span>
                            <span class="sg-home-bars__value">{{ $sdg['total'] }}</span>
                        </li>
                    @endforeach
                </ol>
                @if ($home['urgentTotal'] > 0)
                    <p class="sg-home-needs__note"><x-icon name="triangle-alert" size="sm" />{{ $home['urgentTotal'] }} masalah ditandai mendesak oleh instansinya.</p>
                @endif
            </section>
        @endif
    </div>
</div>

@if ($hasData)
{{-- 2. penemuan: masalah dengan tenggat terdekat, dengan informasi yang membedakan satu sama lain --}}
<section class="sg-home-section" aria-labelledby="home-problems-title">
    <div class="sg-container">
        <div class="sg-home-section__head">
            <div>
                <h2 id="home-problems-title" class="sg-home-h2">Masalah dengan tenggat terdekat</h2>
                <p class="sg-home-section__desc">Ditulis oleh instansi dan masih menerima lamaran. Pilih yang sesuai bidang, waktu, dan kesiapan tim Anda.</p>
            </div>
            @if ($hasOpen)
                <x-ui.button :href="route('student.browse-problems.index')" variant="tertiary" icon-end="arrow-right">Lihat semua {{ $home['openTotal'] }} masalah</x-ui.button>
            @endif
        </div>

        @if (! $hasOpen)
            <x-ui.empty-state title="Belum ada masalah yang dibuka" description="Masalah baru tampil di sini setelah instansi mempublikasikannya." illustration="no-search-results" heading-level="3" />
        @else
            <ul class="sg-home-problems" aria-label="Masalah dengan tenggat terdekat">
                @foreach ($home['soonest'] as $problem)
                    @php
                        // selisih hari kalender sampai tenggat (bukan jam), agar sama dengan tanggal yang tertulis
                        $days = max(0, (int) now()->startOfDay()->diffInDays($problem->application_deadline->copy()->startOfDay(), false));
                        $level = $difficulty[$problem->difficulty_level] ?? null;
                    @endphp
                    <li class="sg-home-problem">
                        <div class="sg-home-problem__main">
                            <h3 class="sg-home-problem__title">
                                <a href="{{ route('student.browse-problems.show', $problem->id) }}">{{ $problem->title }}</a>
                            </h3>
                            <p class="sg-home-problem__by">
                                @if ($problem->institution)
                                    <a href="{{ route('institution.public', $problem->institution->id) }}">{{ $problem->institution->name }}</a>
                                @endif
                                @if ($problem->regency || $problem->province)
                                    <span class="sg-home-problem__place"><x-icon name="map-pin" size="sm" />{{ collect([$problem->regency?->name, $problem->province?->name])->filter()->implode(', ') }}</span>
                                @endif
                            </p>
                            @if (count((array) $problem->sdg_categories))
                                <ul class="sg-home-problem__sdgs" aria-label="Tujuan SDG">
                                    @foreach ((array) $problem->sdg_categories as $sdg)
                                        <li><x-ui.chip>SDG {{ $sdg }} · {{ \App\Http\Controllers\HomeController::sdgName((int) $sdg) }}</x-ui.chip></li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                        <dl class="sg-home-problem__facts">
                            <div><dt>Kebutuhan</dt><dd>{{ $problem->required_students }} mahasiswa</dd></div>
                            <div><dt>Durasi</dt><dd>{{ $problem->duration_months }} bulan</dd></div>
                            @if ($level)
                                <div><dt>Kesulitan</dt><dd><span class="sg-meter" data-level="{{ $level[0] }}"><span class="sg-meter__bars" aria-hidden="true"><i></i><i></i><i></i></span>{{ $level[1] }}</span></dd></div>
                            @endif
                        </dl>
                        <div class="sg-home-problem__deadline">
                            <p class="sg-home-problem__days">{{ $days === 0 ? 'Tutup hari ini' : 'Tutup dalam '.$days.' hari' }}</p>
                            <p class="sg-home-problem__date">Tenggat {{ $problem->application_deadline->locale('id')->translatedFormat('j M Y') }}</p>
                            @if ($problem->is_urgent)
                                <span class="sg-status" data-tone="warning"><x-icon name="triangle-alert" size="sm" />Mendesak</span>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
            @guest
                <p class="sg-home-note"><x-icon name="info" size="sm" />Detail masalah dan lamaran tersedia setelah masuk sebagai mahasiswa.</p>
            @endguest
        @endif
    </div>
</section>

{{-- 3. eksplorasi lokasi: filter di atas, peta leaflet dengan pengelompokan, panel detail lokasi --}}
@if ($hasOpen && count($home['points']))
<section class="sg-home-section sg-home-explore" id="lokasi" aria-labelledby="home-map-title">
    <div class="sg-container">
        <div class="sg-home-section__head">
            <div>
                <h2 id="home-map-title" class="sg-home-h2">Jelajahi menurut lokasi</h2>
                <p class="sg-home-section__desc">Angka pada penanda adalah jumlah masalah dibuka. Di skala provinsi penanda digabung; perbesar peta atau pilih penanda untuk melihat kabupaten/kota.</p>
            </div>
        </div>

        <div class="sg-home-filters" role="group" aria-label="Saring peta">
            <div class="sg-field">
                <label class="sg-label" for="map-province">Provinsi</label>
                <select id="map-province" class="sg-select">
                    <option value="">Semua provinsi ({{ $home['openTotal'] }})</option>
                    @foreach ($home['provinceTotals'] as $province)
                        <option value="{{ $province['id'] }}">{{ $province['name'] }} ({{ $province['total'] }})</option>
                    @endforeach
                </select>
            </div>
            <div class="sg-field">
                <label class="sg-label" for="map-sdg">Bidang (SDG)</label>
                <select id="map-sdg" class="sg-select">
                    <option value="">Semua bidang</option>
                    @foreach ($home['sdgs'] as $sdg)
                        <option value="{{ $sdg['id'] }}">SDG {{ $sdg['id'] }} · {{ $sdg['label'] }} ({{ $sdg['total'] }})</option>
                    @endforeach
                </select>
            </div>
            <p class="sg-home-filters__result" id="map-result" aria-live="polite"></p>
        </div>

        <div class="sg-home-explore__grid">
            <div id="map" class="sg-home-map" role="region" aria-label="Peta lokasi masalah KKN yang dibuka"></div>
            <aside class="sg-home-panel" id="map-panel" aria-live="polite" aria-labelledby="map-panel-title">
                <h3 id="map-panel-title" class="sg-home-panel__title" tabindex="-1">Pilih lokasi</h3>
                <p class="sg-home-panel__meta">Pilih penanda di peta untuk melihat masalah di lokasi itu.</p>
                <ul class="sg-home-panel__list"></ul>
            </aside>
        </div>
    </div>
</section>
@endif
@endif

{{-- 4. dari lapangan: dokumentasi asli KKN ITB 2025 (D-30, dikonfirmasi pengguna 2026-10-31) --}}
<section class="sg-home-section sg-home-field" aria-labelledby="home-field-title">
    <div class="sg-container">
        <div class="sg-home-field__head">
            <div>
                <p class="sg-home-kicker">Dari lapangan</p>
                <h2 id="home-field-title" class="sg-home-pull">KKN dikerjakan di desa dan kelurahan, bukan di ruang kuliah.</h2>
            </div>
            <p class="sg-home-field__text">
                Karena itu setiap masalah di Student Go mencantumkan lokasinya sampai kabupaten/kota dan desa,
                bersama kuota mahasiswa, durasi, dan tenggat lamaran.
            </p>
        </div>
        <div class="sg-home-field__photos">
            <figure class="sg-figure">
                <img src="{{ asset('assets/photography/field/kkn-itb-2025-mahasiswa-jalan-desa-1600.webp') }}"
                     srcset="{{ asset('assets/photography/field/kkn-itb-2025-mahasiswa-jalan-desa-800.webp') }} 800w, {{ asset('assets/photography/field/kkn-itb-2025-mahasiswa-jalan-desa-1600.webp') }} 1600w"
                     sizes="(min-width: 1024px) 58vw, 100vw"
                     alt="Mahasiswa berompi KKN ITB 2025, dilihat dari belakang, menumpang bak mobil terbuka di jalan desa yang diapit kebun pisang."
                     width="1600" height="899" loading="lazy" decoding="async">
                <figcaption>KKN ITB 2025. Menumpang bak mobil terbuka di jalan desa.</figcaption>
            </figure>
            <figure class="sg-figure">
                <img src="{{ asset('assets/photography/field/kkn-itb-2025-rombongan-hujan-1600.webp') }}"
                     srcset="{{ asset('assets/photography/field/kkn-itb-2025-rombongan-hujan-800.webp') }} 800w, {{ asset('assets/photography/field/kkn-itb-2025-rombongan-hujan-1600.webp') }} 1600w"
                     sizes="(min-width: 1024px) 40vw, 100vw"
                     alt="Rombongan mahasiswa berompi KKN ITB 2025 berjalan di bawah hujan, sebagian berpayung, di gang desa yang dihiasi umbul-umbul warna-warni."
                     width="1600" height="899" loading="lazy" decoding="async">
                <figcaption>KKN ITB 2025. Berjalan di gang desa saat hujan.</figcaption>
            </figure>
        </div>
    </div>
</section>

{{-- 5a. alur mahasiswa: tiga tahap dengan contoh dari produk (bukan timeline bernomor) --}}
<section class="sg-home-section" aria-labelledby="home-student-title">
    <div class="sg-container">
        <div class="sg-home-section__head">
            <div>
                <p class="sg-home-kicker">Untuk mahasiswa</p>
                <h2 id="home-student-title" class="sg-home-h2">Dari memilih masalah sampai portofolio</h2>
            </div>
        </div>
        <div class="sg-home-steps">
            <div class="sg-home-step">
                <h3 class="sg-home-step__title">Pilih masalah</h3>
                <p>Saring menurut provinsi, bidang SDG, tingkat kesulitan, dan durasi.</p>
                @if ($example)
                    <div class="sg-home-step__sample" aria-label="Contoh masalah yang sedang dibuka">
                        <span class="sg-home-step__sample-label">Contoh yang sedang dibuka</span>
                        <strong>{{ $example->title }}</strong>
                        <span>{{ collect([$example->regency?->name, $example->province?->name])->filter()->implode(', ') }}</span>
                        <span>{{ $example->required_students }} mahasiswa · {{ $example->duration_months }} bulan</span>
                    </div>
                @endif
                <span class="sg-home-step__num sg-home-step__num--below" aria-hidden="true">1</span>
            </div>
            <div class="sg-home-step">
                <span class="sg-home-step__num sg-home-step__num--above" aria-hidden="true">2</span>
                <h3 class="sg-home-step__title">Kirim lamaran</h3>
                <p>Satu formulir per masalah. Instansi meninjau lamaran yang masuk.</p>
                <dl class="sg-home-step__sample">
                    <span class="sg-home-step__sample-label">Yang diisi</span>
                    <div><dt>Motivasi</dt><dd>minimal 100 karakter</dd></div>
                    <div><dt>Proposal</dt><dd>opsional, PDF/DOC/DOCX, maks. 5 MB</dd></div>
                </dl>
            </div>
            <div class="sg-home-step">
                <h3 class="sg-home-step__title">Kerjakan dan catat</h3>
                <p>Status lamaran terlihat di halaman Aplikasi Saya. Proyek yang selesai dan ulasan instansi tampil di portofolio publik.</p>
                <div class="sg-home-step__sample sg-home-step__sample--numbered">
                    <span class="sg-home-step__num sg-home-step__num--beside" aria-hidden="true">3</span>
                    <span class="sg-home-step__sample-label">Status lamaran</span>
                    <span class="sg-home-step__statuses">
                        <x-ui.status domain="application" value="pending" />
                        <x-ui.status domain="application" value="reviewed" />
                        <x-ui.status domain="application" value="accepted" />
                    </span>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- 5b. alur instansi: ruang kerja yang dikelola, sebagai tabel (bukan timeline kedua) --}}
<section class="sg-home-section sg-home-inst" aria-labelledby="home-inst-title">
    <div class="sg-container">
        <div class="sg-home-section__head sg-home-inst__head">
            <div>
                <p class="sg-home-kicker">Untuk instansi</p>
                <h2 id="home-inst-title" class="sg-home-h2">Satu ruang kerja untuk masalah, pelamar, dan proyek</h2>
            </div>
            <p class="sg-home-section__desc">Instansi mendaftar dengan dokumen verifikasi. Setelah itu semua tahap dikelola dari dashboard instansi.</p>
        </div>
        <x-ui.table caption="Yang dikelola instansi di Student Go">
            <x-slot:head>
                <th scope="col">Yang dikelola</th>
                <th scope="col">Yang dilakukan instansi</th>
                <th scope="col">Status yang terlihat</th>
            </x-slot:head>
            <tr>
                <td data-label="Yang dikelola"><span class="sg-table__primary">Masalah</span></td>
                <td data-label="Yang dilakukan">Menulis lokasi, bidang SDG, kuota mahasiswa, tenggat, dan persyaratan, lalu mempublikasikannya.</td>
                <td data-label="Status"><span class="sg-home-cell-status"><x-ui.status domain="problem" value="draft" /><x-ui.status domain="problem" value="open" /><x-ui.status domain="problem" value="closed" /></span></td>
            </tr>
            <tr>
                <td data-label="Yang dikelola"><span class="sg-table__primary">Pelamar</span></td>
                <td data-label="Yang dilakukan">Membaca motivasi dan proposal, lalu menerima atau menolak.</td>
                <td data-label="Status"><span class="sg-home-cell-status"><x-ui.status domain="application" value="pending" /><x-ui.status domain="application" value="accepted" /><x-ui.status domain="application" value="rejected" /></span></td>
            </tr>
            <tr>
                <td data-label="Yang dikelola"><span class="sg-table__primary">Proyek</span></td>
                <td data-label="Yang dilakukan">Memantau proyek yang berjalan bersama mahasiswa yang diterima.</td>
                <td data-label="Status"><span class="sg-home-cell-status"><x-ui.status domain="project" value="active" /><x-ui.status domain="project" value="completed" /></span></td>
            </tr>
            <tr>
                <td data-label="Yang dikelola"><span class="sg-table__primary">Ulasan</span></td>
                <td data-label="Yang dilakukan">Menilai profesionalisme, komunikasi, kualitas, dan ketepatan waktu mahasiswa setelah proyek selesai.</td>
                <td data-label="Status"><span class="sg-home-cell-muted">Tampil di portofolio mahasiswa</span></td>
            </tr>
        </x-ui.table>
    </div>
</section>

{{-- 6. hasil yang tercatat: angka langsung dari tabel projects --}}
@if ($hasData && count($home['outcomes']))
<section class="sg-home-section sg-home-outcomes" aria-labelledby="home-outcomes-title">
    <div class="sg-container">
        <p class="sg-home-kicker">Hasil yang tercatat</p>
        <h2 id="home-outcomes-title" class="sg-home-h2">Proyek KKN di Student Go sejauh ini</h2>
        <dl class="sg-home-outcomes__list">
            @foreach ($home['outcomes'] as $item)
                <div>
                    <dt>{{ $item['label'] }}</dt>
                    <dd>{{ number_format($item['value'], 0, ',', '.') }}</dd>
                </div>
            @endforeach
        </dl>
        <p class="sg-home-note">Dihitung langsung dari data proyek di Student Go saat halaman dibuka.</p>
    </div>
</section>
@endif

{{-- 7. pilihan peran (hanya tamu): manfaat yang berbeda per peran, dari fitur yang ada --}}
@guest
<section class="sg-home-section sg-home-close" aria-labelledby="home-close-title">
    <div class="sg-container">
        <h2 id="home-close-title" class="sg-home-h2">Pilih peran saat mendaftar</h2>
        <p class="sg-home-section__desc">Satu akun untuk satu peran. Sudah punya akun? <a href="{{ route('login') }}" class="sg-home-link">Masuk</a></p>
        <div class="sg-home-roles">
            <div class="sg-home-role-col">
                <p class="sg-home-kicker"><x-icon name="graduation-cap" size="sm" />Mahasiswa</p>
                <h3 class="sg-home-h3">Kerjakan masalah nyata untuk KKN Anda</h3>
                <ul>
                    <li>Cari masalah menurut lokasi, bidang SDG, kesulitan, dan durasi</li>
                    <li>Lamar dengan motivasi dan proposal</li>
                    <li>Proyek selesai masuk portofolio publik</li>
                </ul>
                <x-ui.button :href="route('register.student')" variant="secondary" icon-end="arrow-right">Daftar sebagai mahasiswa</x-ui.button>
            </div>
            <div class="sg-home-role-col">
                <p class="sg-home-kicker"><x-icon name="building-2" size="sm" />Instansi</p>
                <h3 class="sg-home-h3">Dapatkan tim mahasiswa untuk masalah daerah Anda</h3>
                <ul>
                    <li>Publikasikan masalah dengan kuota dan tenggat</li>
                    <li>Terima atau tolak pelamar</li>
                    <li>Pantau proyek dan beri ulasan</li>
                </ul>
                <x-ui.button :href="route('register.institution')" variant="secondary" icon-end="arrow-right">Daftar sebagai instansi</x-ui.button>
            </div>
            <div class="sg-home-role-col">
                <p class="sg-home-kicker"><x-icon name="briefcase" size="sm" />Perusahaan</p>
                <h3 class="sg-home-h3">Rekrut mahasiswa dengan pengalaman lapangan</h3>
                <ul>
                    <li>Telusuri profil dan portofolio KKN mahasiswa</li>
                    <li>Simpan dan bandingkan kandidat</li>
                    <li>Pasang lowongan</li>
                </ul>
                <x-ui.button :href="route('register.company')" variant="secondary" icon-end="arrow-right">Daftar sebagai perusahaan</x-ui.button>
            </div>
        </div>
    </div>
</section>
@endguest

@endsection

@if ($hasOpen && count($home['points']))
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// peta eksplorasi: leaflet + ubin openstreetmap. penanda digabung per provinsi di zoom jauh (< 8)
// dan dipecah per kabupaten/kota di zoom dekat. angka = jumlah masalah dibuka sesuai filter.
document.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('map');
    if (!el || typeof L === 'undefined') return;

    const points = @json($home['points']);
    const problems = @json($home['mapProblems']);
    const pinUrl = @json(asset('assets/illustrations/motifs/map-pin.svg'));
    const pinSelectedUrl = @json(asset('assets/illustrations/motifs/map-pin-selected.svg'));
    const guest = @json(auth()->guest());
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const SPLIT_ZOOM = 8;

    const provinceSelect = document.getElementById('map-province');
    const sdgSelect = document.getElementById('map-sdg');
    const result = document.getElementById('map-result');
    const panelTitle = document.getElementById('map-panel-title');
    const panelMeta = document.querySelector('#map-panel .sg-home-panel__meta');
    const panelList = document.querySelector('#map-panel .sg-home-panel__list');

    const map = L.map(el, { zoomControl: true, scrollWheelZoom: true, zoomAnimation: !reduceMotion, fadeAnimation: !reduceMotion, markerZoomAnimation: !reduceMotion });
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 18,
    }).addTo(map);

    const layer = L.layerGroup().addTo(map);
    let selectedKey = null;

    function filtered() {
        const prov = provinceSelect.value, sdg = sdgSelect.value;
        return problems.filter(function (p) {
            return (!prov || String(p.province_id) === prov) && (!sdg || p.sdgs.indexOf(Number(sdg)) !== -1);
        });
    }

    function pinIcon(count, selected, big) {
        // pin paket + angka; ikon dibuat dari teks angka saja
        return L.divIcon({
            className: 'sg-home-pin' + (big ? ' sg-home-pin--group' : '') + (selected ? ' is-selected' : ''),
            html: '<img src="' + (selected ? pinSelectedUrl : pinUrl) + '" alt="" width="28" height="38"><b>' + Number(count) + '</b>',
            iconSize: [28, 38], iconAnchor: [14, 38],
        });
    }

    function groups(list) {
        const split = map.getZoom() >= SPLIT_ZOOM;
        const out = {};
        list.forEach(function (p) {
            const pt = points.find(function (x) { return x.key === p.point; });
            if (!pt) return;
            const key = split ? pt.key : 'prov-' + pt.province_id;
            if (!out[key]) out[key] = { key: key, group: !split, name: split ? pt.name : pt.province, province: pt.province, province_id: pt.province_id, lats: [], lngs: [], seen: {}, items: [] };
            if (!out[key].seen[pt.key]) { out[key].seen[pt.key] = true; out[key].lats.push(pt.lat); out[key].lngs.push(pt.lng); }
            out[key].items.push(p);
        });
        return Object.values(out).map(function (g) {
            g.lat = g.lats.reduce(function (a, b) { return a + b; }, 0) / g.lats.length;
            g.lng = g.lngs.reduce(function (a, b) { return a + b; }, 0) / g.lngs.length;
            return g;
        });
    }

    function showPanel(g) {
        panelTitle.textContent = g.group ? g.name : g.name + ', ' + g.province;
        panelMeta.textContent = g.items.length + ' masalah dibuka' + (g.group ? ' di ' + g.lats.length + ' kabupaten/kota' : '') + (guest ? '. Detail tersedia setelah masuk sebagai mahasiswa.' : '.');
        panelList.replaceChildren();
        g.items.forEach(function (p) {
            const li = document.createElement('li');
            const a = document.createElement('a');
            a.href = p.url; a.textContent = p.title;
            const meta = document.createElement('span');
            meta.textContent = [p.institution, 'Tenggat ' + p.deadline].filter(Boolean).join(' · ');
            li.append(a, meta);
            if (p.urgent) {
                const tag = document.createElement('span');
                tag.className = 'sg-home-panel__urgent';
                tag.textContent = 'Mendesak';
                li.append(tag);
            }
            panelList.append(li);
        });
    }

    function render() {
        layer.clearLayers();
        const list = filtered();
        const gs = groups(list);
        result.textContent = list.length + ' masalah di ' + new Set(list.map(function (p) { return p.point; })).size + ' kabupaten/kota';
        gs.forEach(function (g) {
            const label = (g.group ? g.name + ': ' : g.name + ', ' + g.province + ': ') + g.items.length + ' masalah dibuka';
            const marker = L.marker([g.lat, g.lng], { icon: pinIcon(g.items.length, g.key === selectedKey, g.group), title: label, keyboard: true, riseOnHover: true }).addTo(layer);
            const node = marker.getElement();
            if (node) {
                node.setAttribute('role', 'button');
                node.setAttribute('aria-label', label);
                // enter/spasi pada penanda divIcon tidak diteruskan leaflet sebagai klik; tangani sendiri
                node.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); marker.fire('click', { viaKeyboard: true }); }
                });
            }
            marker.on('click', function (e) {
                selectedKey = g.key;
                showPanel(g);
                // penanda dibuat ulang setelah zoom; pengguna keyboard dipindahkan ke judul panel
                if (e && e.viaKeyboard) { panelTitle.focus(); }
                if (g.group) {
                    // grup provinsi: perbesar ke kabupaten/kota di dalamnya
                    map.fitBounds(L.latLngBounds(g.lats.map(function (lat, i) { return [lat, g.lngs[i]]; })), { padding: [40, 40], maxZoom: 11, animate: !reduceMotion });
                } else {
                    render();
                }
            });
        });
    }

    function fitAll() {
        const list = filtered();
        const keys = new Set(list.map(function (p) { return p.point; }));
        const pts = points.filter(function (x) { return keys.has(x.key); });
        if (!pts.length) { render(); return; }
        map.fitBounds(L.latLngBounds(pts.map(function (x) { return [x.lat, x.lng]; })), { padding: [40, 40], maxZoom: 10, animate: !reduceMotion });
    }

    map.on('zoomend', render);
    [provinceSelect, sdgSelect].forEach(function (s) {
        s.addEventListener('change', function () { selectedKey = null; fitAll(); render(); });
    });
    fitAll();
    render();
});
</script>
@endpush
@endif
