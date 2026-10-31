@extends('layouts.app')

@section('title', $problem->title)

@section('content')
@php
    // phase 18: detail masalah sebagai brief (kiri: isi brief, kanan: rel metadata). angka dari data tersimpan;
    // jumlah diterima dihitung dari lamaran yang sudah dimuat controller (counter accepted_students tidak selalu sinkron).
    $arr = fn ($v) => is_array($v) ? $v : (json_decode((string) $v, true) ?: []);
    $tanggal = fn ($date) => $date ? \Illuminate\Support\Carbon::parse($date)->locale('id')->translatedFormat('j F Y') : '—';
    $sdgs = $arr($problem->sdg_categories);
    $deliverables = $arr($problem->deliverables);
    $facilities = $arr($problem->facilities_provided);
    $skills = $arr($problem->required_skills);
    $majors = $arr($problem->required_majors);
    $accepted = $problem->applications->where('status', 'accepted')->count();
    $difficulty = ['beginner' => [1, 'Pemula'], 'intermediate' => [2, 'Menengah'], 'advanced' => [3, 'Lanjut']][$problem->difficulty_level] ?? null;
    $location = collect([$problem->village, $problem->regency?->name, $problem->province?->name])->filter()->implode(', ');
    $texts = [
        'Deskripsi' => $problem->description,
        'Latar belakang' => $problem->background,
        'Tujuan' => $problem->objectives,
        'Ruang lingkup' => $problem->scope,
        'Hasil yang diharapkan' => $problem->expected_outcomes,
    ];
    $images = $problem->images->sortBy('order')->values();
@endphp

<div class="sg-container sg-inst-page">
    <x-ui.page-header :title="$problem->title" :description="$location"
                      :back="route('institution.problems.index')" back-label="Kembali ke daftar masalah">
        <x-slot:actions>
            <x-ui.button :href="route('institution.problems.edit', $problem->id)" icon="pencil">Ubah masalah</x-ui.button>
            {{-- hapus: route, method DELETE dan aturan controller sama; konfirmasi memakai dialog bersama --}}
            <form action="{{ route('institution.problems.destroy', $problem->id) }}" method="POST"
                  data-sg-confirm="Hapus masalah “{{ $problem->title }}”? Tindakan ini tidak dapat dibatalkan." data-sg-confirm-label="Hapus masalah">
                @csrf
                @method('DELETE')
                <x-ui.button type="submit" variant="danger">Hapus</x-ui.button>
            </form>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="sg-inst-detail">
        <article class="sg-inst-detail__main" aria-label="Brief masalah">
            @foreach ($texts as $heading => $text)
                @if (filled($text))
                    <section class="sg-inst-detail__block">
                        <h2>{{ $heading }}</h2>
                        <div class="sg-inst-detail__text">{!! nl2br(e($text)) !!}</div>
                    </section>
                @endif
            @endforeach

            @if (count($deliverables))
                <section class="sg-inst-detail__block">
                    <h2>Luaran</h2>
                    <ul class="sg-inst-detail__bullets">
                        @foreach ($deliverables as $item)<li>{{ $item }}</li>@endforeach
                    </ul>
                </section>
            @endif

            @if (count($facilities))
                <section class="sg-inst-detail__block">
                    <h2>Fasilitas yang disediakan</h2>
                    <ul class="sg-inst-detail__bullets">
                        @foreach ($facilities as $item)<li>{{ $item }}</li>@endforeach
                    </ul>
                </section>
            @endif

            @if ($images->count())
                <section class="sg-inst-detail__block">
                    <h2>Foto dokumentasi</h2>
                    <ul class="sg-inst-gallery sg-inst-gallery--detail">
                        @foreach ($images as $image)
                            <li class="sg-inst-gallery__item">
                                <span class="sg-inst-gallery__media">
                                    <img src="{{ $image->image_url }}" alt="{{ $image->caption ?: 'Foto '.$loop->iteration.' masalah '.$problem->title }}" loading="lazy"
                                         onerror="this.hidden = true; this.nextElementSibling.hidden = false;">
                                    <span class="sg-inst-gallery__missing" hidden>Foto tidak dapat dimuat</span>
                                </span>
                                @if ($image->is_cover)<span class="sg-inst-gallery__cover">Sampul</span>@endif
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </article>

        <aside class="sg-inst-detail__rail" aria-label="Informasi masalah">
            <x-ui.dl>
                <div>
                    <dt>Status</dt>
                    <dd><x-ui.status domain="problem" :value="$problem->status" /></dd>
                </div>
                @if ($difficulty)
                    <div>
                        <dt>Tingkat kesulitan</dt>
                        <dd>
                            <span class="sg-meter" data-level="{{ $difficulty[0] }}">
                                <span class="sg-meter__bars" aria-hidden="true"><i></i><i></i><i></i></span>{{ $difficulty[1] }}
                            </span>
                        </dd>
                    </div>
                @endif
                <div>
                    <dt>Kuota</dt>
                    <dd>{{ $problem->required_students }} mahasiswa · {{ $accepted }} diterima</dd>
                </div>
                <div>
                    <dt>Pelamar</dt>
                    <dd>
                        @if ($problem->applications_count > 0)
                            <a href="{{ route('institution.applications.index', ['problem_id' => $problem->id]) }}" class="sg-inst-link">{{ $problem->applications_count }} pelamar</a>
                        @else
                            Belum ada pelamar
                        @endif
                    </dd>
                </div>
                <div>
                    <dt>Dilihat</dt>
                    <dd>{{ number_format($problem->views_count, 0, ',', '.') }} kali</dd>
                </div>
            </x-ui.dl>

            <h2 class="sg-inst-detail__rail-h">Jadwal</h2>
            <x-ui.dl :items="[
                'Batas lamaran' => $tanggal($problem->application_deadline),
                'Mulai' => $tanggal($problem->start_date),
                'Selesai' => $tanggal($problem->end_date),
                'Durasi' => $problem->duration_months ? $problem->duration_months.' bulan' : null,
            ]" />

            @if (count($sdgs))
                <h2 class="sg-inst-detail__rail-h">Kategori SDG</h2>
                <ul class="sg-inst-chips">
                    @foreach ($sdgs as $sdg)
                        <li class="sg-chip">SDG {{ (int) $sdg }} · {{ sdg_label($sdg) }}</li>
                    @endforeach
                </ul>
            @endif

            @if (count($skills))
                <h2 class="sg-inst-detail__rail-h">Keterampilan</h2>
                <ul class="sg-inst-chips">
                    @foreach ($skills as $skill)<li class="sg-chip">{{ $skill }}</li>@endforeach
                </ul>
            @endif

            @if (count($majors))
                <h2 class="sg-inst-detail__rail-h">Jurusan</h2>
                <ul class="sg-inst-chips">
                    @foreach ($majors as $major)<li class="sg-chip">{{ $major }}</li>@endforeach
                </ul>
            @endif

            @if (filled($problem->detailed_location))
                <h2 class="sg-inst-detail__rail-h">Detail lokasi</h2>
                <p class="sg-inst-detail__note">{{ $problem->detailed_location }}</p>
            @endif
        </aside>
    </div>
</div>
@endsection
