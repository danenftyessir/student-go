@extends('layouts.app')

@section('title', 'Profil Instansi')

{{-- phase 20: profil organisasi standar (pola .sg-org dari profil publik, phase 5) dengan grid fakta dua kolom
     yang tahan teks panjang (J19: email 51+ karakter, nama 76 karakter, url). tombol Edit Profil dan
     Lihat Profil Publik tetap ada. angka ringkasan dari query controller; nilai nol tidak ditampilkan (guardrail 2). --}}

@php
    // label jenis sama dengan form registrasi; nilai teks bebas yang tersimpan ditampilkan apa adanya (D-06)
    $typeLabels = [
        'pemerintah_desa' => 'Pemerintah desa',
        'dinas' => 'Dinas',
        'ngo' => 'Lembaga swadaya masyarakat (LSM/NGO)',
        'puskesmas' => 'Puskesmas',
        'sekolah' => 'Sekolah',
        'perguruan_tinggi' => 'Perguruan tinggi',
        'lainnya' => 'Lainnya',
    ];
    $typeLabel = $institution->type ? ($typeLabels[$institution->type] ?? $institution->type) : null;
    $place = collect([$institution->regency?->name, $institution->province?->name])->filter()->implode(', ');
    // hanya tautan http(s) yang dijadikan link
    $website = filled($institution->website) && preg_match('#^https?://#i', $institution->website) ? $institution->website : null;
    $verified = $institution->verification_status === \App\Models\Institution::STATUS_ACTIVE;
@endphp

@section('content')
<div class="sg-container sg-org">
    @if (session('success'))
        <x-ui.alert tone="success" class="mt-6">{{ session('success') }}</x-ui.alert>
    @endif
    @if (session('error'))
        <x-ui.alert tone="danger" class="mt-6">{{ session('error') }}</x-ui.alert>
    @endif

    <header class="sg-org__head">
        <x-ui.avatar :src="$institution->logo_path ? $institution->getLogoUrl() : null" :name="$institution->name"
                     size="xl" square decorative />
        <div class="sg-org__id">
            <p class="sg-org__kicker">Profil instansi</p>
            <h1 class="sg-org__name">{{ $institution->name }}</h1>
            <p class="sg-org__meta">
                @if ($typeLabel)<span>{{ $typeLabel }}</span>@endif
                @if ($place)<span class="sg-org__place"><x-icon name="map-pin" size="sm" />{{ $place }}</span>@endif
            </p>
            <x-ui.summary class="sg-org__summary" label="Ringkasan instansi" :items="[
                ['value' => $stats['total_problems'] ?: null, 'label' => 'masalah'],
                ['value' => $stats['active_problems'] ?: null, 'label' => 'masalah dibuka'],
                ['value' => $stats['completed_problems'] ?: null, 'label' => 'masalah selesai'],
                ['value' => $stats['active_projects'] ?: null, 'label' => 'proyek berjalan'],
                ['value' => $stats['completed_projects'] ?: null, 'label' => 'proyek selesai'],
            ]" />
            <div class="sg-inst-profile__actions">
                <x-ui.button :href="route('institution.profile.edit')" icon="pencil">Edit Profil</x-ui.button>
                <x-ui.button variant="secondary" :href="route('institution.public', $institution->id)" icon-end="external-link"
                             target="_blank" rel="noopener">Lihat Profil Publik<span class="sr-only"> (tab baru)</span></x-ui.button>
            </div>
        </div>
    </header>

    <div class="sg-org__grid">
        <div class="sg-org__main">
            <section class="sg-org__section" aria-labelledby="profil-tentang">
                <h2 id="profil-tentang" class="sg-org__h2">Tentang instansi</h2>
                @if (filled($institution->description))
                    <p class="sg-org__about">{{ $institution->description }}</p>
                @else
                    <p class="sg-inst-profile__hint">Belum ada deskripsi. Deskripsi tampil di profil publik;
                        <a href="{{ route('institution.profile.edit') }}">tambahkan lewat Edit Profil</a>.</p>
                @endif
            </section>

            <section class="sg-org__section" aria-labelledby="profil-informasi">
                <h2 id="profil-informasi" class="sg-org__h2">Informasi instansi</h2>
                <dl class="sg-inst-facts">
                    <div class="sg-inst-facts__wide"><dt>Nama instansi</dt><dd>{{ $institution->name }}</dd></div>
                    <div><dt>Jenis instansi</dt><dd>{{ $typeLabel ?? '—' }}</dd></div>
                    <div><dt>Nomor telepon</dt><dd>{{ $institution->phone ?: '—' }}</dd></div>
                    <div class="sg-inst-facts__wide"><dt>Email</dt><dd>{{ $user->email }}</dd></div>
                    <div class="sg-inst-facts__wide">
                        <dt>Website</dt>
                        <dd>
                            @if ($website)
                                <a href="{{ $website }}" target="_blank" rel="noopener">{{ $website }}<span class="sr-only"> (tab baru)</span></a>
                            @elseif (filled($institution->website))
                                {{ $institution->website }}
                            @else
                                <span class="sg-inst-facts__empty">Belum diisi</span>
                            @endif
                        </dd>
                    </div>
                    <div class="sg-inst-facts__wide"><dt>Alamat</dt><dd>{{ $institution->address ?: '—' }}</dd></div>
                    @if ($place)
                        <div class="sg-inst-facts__wide"><dt>Wilayah</dt><dd>{{ $place }}</dd></div>
                    @endif
                </dl>
            </section>

            <section class="sg-org__section" aria-labelledby="profil-pic">
                <h2 id="profil-pic" class="sg-org__h2">Penanggung jawab</h2>
                <dl class="sg-inst-facts">
                    <div><dt>Nama PIC</dt><dd>{{ $institution->pic_name ?: '—' }}</dd></div>
                    <div><dt>Jabatan</dt><dd>{{ $institution->pic_position ?: '—' }}</dd></div>
                </dl>
            </section>
        </div>

        {{-- status verifikasi dari verification_status (satu status global); tanpa janji metode atau durasi (D-09) --}}
        <aside class="sg-org__aside" aria-labelledby="profil-verifikasi">
            <h2 id="profil-verifikasi" class="sg-org__h2">Verifikasi</h2>
            <x-ui.status domain="verification" :value="$institution->verification_status" />
            @if ($verified)
                <p class="sg-inst-profile__aside-text">Dokumen instansi sudah diverifikasi.</p>
                <x-ui.button variant="tertiary" :href="route('institution.verification.status')">Lihat hasil verifikasi</x-ui.button>
            @else
                <p class="sg-inst-profile__aside-text">Unggah atau perbarui dokumen resmi instansi untuk diperiksa.</p>
                <x-ui.button variant="secondary" :href="route('institution.verification.upload')" icon="upload">Dokumen verifikasi</x-ui.button>
            @endif
        </aside>
    </div>
</div>
@endsection
