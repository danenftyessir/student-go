@extends('layouts.app')

@section('title', 'Profil Perusahaan')

{{-- phase 25: profil organisasi standar (pola .sg-org dari phase 5, sama dengan profil instansi phase 20): kepala dengan
     logo atau inisial, grid fakta yang tahan teks panjang, kontak, media sosial, info akun. tanpa foto hero.
     angka ringkasan dari query controller; nilai nol tidak ditampilkan (guardrail 2). --}}

@php
    // label jumlah karyawan sama dengan registrasi; nilai lain (mis. '51-100' dari seeder) tampil apa adanya (D-06)
    $sizeLabels = ['1-10' => '1–10 karyawan', '11-50' => '11–50 karyawan', '51-200' => '51–200 karyawan', '201-500' => '201–500 karyawan', '501-1000' => '501–1.000 karyawan', '1000+' => 'Lebih dari 1.000 karyawan'];
    $size = $company->company_size ?? $company->employee_count;
    $sizeLabel = $size ? ($sizeLabels[$size] ?? $size . ' karyawan') : null;
    $industry = $company->industry === 'Retail' ? 'Ritel' : $company->industry;
    $place = collect([$company->city, $company->province?->name])->filter()->unique()->implode(', ');
    // email kontak = email perusahaan; bila belum diisi, email akun (sama dengan isian awal form edit)
    $email = $company->email ?: $company->user?->email;
    // hanya tautan http(s) yang dijadikan link
    $link = fn ($url) => filled($url) && preg_match('#^https?://#i', $url) ? $url : null;
    $socials = array_filter([
        'LinkedIn' => $company->linkedin,
        'Twitter/X' => $company->twitter,
        'Facebook' => $company->facebook,
        'Instagram' => $company->instagram,
    ], fn ($url) => filled($url));
    $verification = $company->is_verified ? 'verified' : ($company->verification_status ?: 'pending_verification');
@endphp

@section('content')
<div class="sg-container sg-org sg-co-profile">
    @if (session('success'))
        {{-- pesan simpan controller (bahasa inggris) diterjemahkan; pesan lain tampil apa adanya --}}
        <x-ui.alert tone="success" class="mt-6">{{ session('success') === 'Profile updated successfully!' ? 'Profil perusahaan disimpan.' : session('success') }}</x-ui.alert>
    @endif
    @if (session('error'))
        <x-ui.alert tone="danger" class="mt-6">{{ session('error') }}</x-ui.alert>
    @endif

    <header class="sg-org__head">
        <x-ui.avatar :src="$company->logo ? $company->logo_url : null" :name="$company->name" size="xl" square decorative />
        <div class="sg-org__id">
            <p class="sg-org__kicker">Profil perusahaan</p>
            <h1 class="sg-org__name">{{ $company->name }}</h1>
            @if (filled($company->tagline))
                <p class="sg-co-profile__tagline">{{ $company->tagline }}</p>
            @endif
            <p class="sg-org__meta">
                @if ($industry)<span>{{ $industry }}</span>@endif
                @if ($place)<span class="sg-org__place"><x-icon name="map-pin" size="sm" />{{ $place }}</span>@endif
            </p>
            <x-ui.summary class="sg-org__summary" label="Ringkasan perusahaan" :items="[
                ['value' => $stats['active_jobs'] ?: null, 'label' => 'lowongan dibuka'],
                ['value' => $stats['total_jobs'] ?: null, 'label' => 'lowongan'],
                ['value' => $stats['total_applications'] ?: null, 'label' => 'pelamar'],
                ['value' => $stats['total_hires'] ?: null, 'label' => 'direkrut'],
            ]" />
            <div class="sg-co-profile__actions">
                <x-ui.button :href="route('company.profile.edit')" icon="pencil">Edit Profil</x-ui.button>
                <x-ui.button variant="secondary" :href="route('company.public', $company->id)" icon-end="external-link"
                             target="_blank" rel="noopener">Lihat Profil Publik<span class="sr-only"> (tab baru)</span></x-ui.button>
            </div>
        </div>
    </header>

    <div class="sg-org__grid">
        <div class="sg-org__main">
            <section class="sg-org__section" aria-labelledby="profil-tentang">
                <h2 id="profil-tentang" class="sg-org__h2">Tentang perusahaan</h2>
                @if (filled($company->description))
                    <p class="sg-org__about sg-co-profile__about">{{ $company->description }}</p>
                @else
                    <p class="sg-co-profile__hint">Belum ada deskripsi. Deskripsi tampil di profil publik;
                        <a href="{{ route('company.profile.edit') }}">tambahkan lewat Edit Profil</a>.</p>
                @endif
            </section>

            <section class="sg-org__section" aria-labelledby="profil-informasi">
                <h2 id="profil-informasi" class="sg-org__h2">Informasi perusahaan</h2>
                <dl class="sg-co-facts">
                    <div class="sg-co-facts__wide"><dt>Nama perusahaan</dt><dd>{{ $company->name }}</dd></div>
                    <div><dt>Industri</dt><dd>{{ $industry ?: '—' }}</dd></div>
                    <div><dt>Jumlah karyawan</dt><dd>{{ $sizeLabel ?? '—' }}</dd></div>
                    <div><dt>Tahun berdiri</dt><dd>{{ $company->founded_year ?: '—' }}</dd></div>
                </dl>
            </section>

            <section class="sg-org__section" aria-labelledby="profil-kontak">
                <h2 id="profil-kontak" class="sg-org__h2">Kontak dan alamat</h2>
                <dl class="sg-co-facts">
                    <div class="sg-co-facts__wide"><dt>Email</dt><dd>{{ $email ?: '—' }}</dd></div>
                    <div><dt>Nomor telepon</dt><dd>{{ $company->phone ?: '—' }}</dd></div>
                    <div class="sg-co-facts__wide">
                        <dt>Website</dt>
                        <dd>
                            @if ($link($company->website))
                                <a href="{{ $company->website }}" target="_blank" rel="noopener">{{ $company->website }}<span class="sr-only"> (tab baru)</span></a>
                            @elseif (filled($company->website))
                                {{ $company->website }}
                            @else
                                <span class="sg-co-facts__empty">Belum diisi</span>
                            @endif
                        </dd>
                    </div>
                    <div class="sg-co-facts__wide"><dt>Alamat</dt><dd>{{ $company->address ?: '—' }}</dd></div>
                    @if ($place)
                        <div><dt>Wilayah</dt><dd>{{ $place }}</dd></div>
                    @endif
                    @if (filled($company->location))
                        <div><dt>Lokasi</dt><dd>{{ $company->location }}</dd></div>
                    @endif
                    @if (filled($company->postal_code))
                        <div><dt>Kode pos</dt><dd>{{ $company->postal_code }}</dd></div>
                    @endif
                </dl>
            </section>

            @if ($socials)
                <section class="sg-org__section" aria-labelledby="profil-sosial">
                    <h2 id="profil-sosial" class="sg-org__h2">Media sosial</h2>
                    <dl class="sg-co-facts">
                        @foreach ($socials as $network => $url)
                            <div class="sg-co-facts__wide">
                                <dt>{{ $network }}</dt>
                                <dd>
                                    @if ($link($url))
                                        <a href="{{ $url }}" target="_blank" rel="noopener">{{ $url }}<span class="sr-only"> (tab baru)</span></a>
                                    @else
                                        {{ $url }}
                                    @endif
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                </section>
            @endif
        </div>

        {{-- info akun + status verifikasi dari kolom tersimpan (tanpa tombol yang tidak melakukan apa pun) --}}
        <aside class="sg-org__aside" aria-labelledby="profil-akun">
            <h2 id="profil-akun" class="sg-org__h2">Info akun</h2>
            <x-ui.status domain="verification" :value="$verification" />
            <dl class="sg-co-facts sg-co-facts--single">
                <div><dt>Email masuk</dt><dd>{{ $company->user?->email ?? '—' }}</dd></div>
                <div><dt>Bergabung sejak</dt><dd>{{ $company->created_at?->locale('id')->translatedFormat('F Y') ?? '—' }}</dd></div>
                <div><dt>Terakhir diperbarui</dt><dd>{{ $company->updated_at?->locale('id')->diffForHumans() ?? '—' }}</dd></div>
            </dl>
        </aside>
    </div>
</div>
@endsection
