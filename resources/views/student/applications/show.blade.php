{{-- resources/views/student/applications/show.blade.php --}}
{{-- halaman detail aplikasi mahasiswa --}}

@extends('layouts.app')

@section('title', 'Detail Aplikasi')

@section('content')
@php
    // phase 10: detail lamaran sebagai record: kepala (judul + status), kolom isi (tanggapan, motivasi, dokumen, riwayat,
    // proyek), rel fakta di kanan mulai 1024px, aksi sekunder di bawah rel. kepemilikan tetap dicek di controller (404).
    $problem = $application->problem;
    $institution = $problem->institution;
    $waktu = fn ($date) => $date ? $date->locale('id')->translatedFormat('j M Y, H.i') : null;
    $tanggal = fn ($date) => $date ? $date->locale('id')->translatedFormat('j M Y') : null;
    $bulan = fn ($date) => $date ? $date->locale('id')->translatedFormat('M Y') : null;
    $relatif = fn ($date) => $date ? $date->locale('id')->diffForHumans() : null;
    $lokasi = collect([
        $problem->regency->name ?? $problem->location_regency,
        $problem->province->name ?? $problem->location_province,
    ])->filter()->implode(', ');
    $periode = $problem->start_date && $problem->end_date ? $bulan($problem->start_date).' – '.$bulan($problem->end_date) : null;
    $sdgs = collect($problem->sdg_categories ?? [])->filter()->values()->all();
    $serupa = $sdgs
        ? route('student.browse-problems.index', ['sdg_categories' => $sdgs])
        : route('student.browse-problems.index', array_filter(['province_id' => $problem->province_id]));
    $proyek = $application->status === 'accepted' ? $application->project : null;

    // riwayat: hanya tanggal yang benar-benar tersimpan; langkah berikutnya ditulis tanpa tanggal
    $riwayat = collect([
        ['label' => 'Diajukan', 'date' => $application->applied_at, 'tone' => 'bg-gray-500'],
        ['label' => 'Ditinjau', 'date' => $application->reviewed_at, 'tone' => 'bg-info-600'],
        ['label' => 'Diterima', 'date' => $application->accepted_at, 'tone' => 'bg-success-600'],
        ['label' => 'Ditolak', 'date' => $application->rejected_at, 'tone' => 'bg-danger-600'],
    ])->filter(fn ($step) => $step['date'])->values();
@endphp

<div class="sg-container pb-16">
    <x-ui.page-header :title="$problem->title" :description="$institution->name"
                      :back="route('student.applications.index')" back-label="Kembali ke Aplikasi Saya" class="[&_h1]:wrap-break-word">
        <x-slot:actions>
            <x-ui.status domain="application" :value="$application->status" />
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-[minmax(0,1fr)_20rem] lg:gap-12">
        <div class="min-w-0 divide-y divide-gray-200 [&>section]:py-6 [&>section:first-child]:pt-0">
            @if ($application->status === 'rejected')
                <section aria-labelledby="tanggapan-instansi">
                    <div class="rounded-lg border border-danger-600 bg-danger-50 p-4">
                        <h2 id="tanggapan-instansi" class="m-0 flex items-center gap-2 text-base font-semibold text-gray-900">
                            <x-icon name="circle-x" class="text-danger-700" />Lamaran ditolak
                        </h2>
                        @if (filled($application->feedback))
                            <p class="mt-2 mb-0 text-sm font-medium text-gray-700">Tanggapan dari {{ $institution->name }}:</p>
                            <p class="mt-1 mb-0 max-w-prose text-sm text-gray-900 wrap-break-word">{!! nl2br(e($application->feedback)) !!}</p>
                        @else
                            <p class="mt-2 mb-0 text-sm text-gray-700">Instansi tidak menuliskan alasan penolakan.</p>
                        @endif
                        <div class="mt-4">
                            <x-ui.button variant="secondary" icon="search" :href="$serupa">Cari proyek serupa</x-ui.button>
                        </div>
                    </div>
                </section>
            @elseif (filled($application->feedback))
                <section aria-labelledby="tanggapan-instansi">
                    <h2 id="tanggapan-instansi" class="mt-0 mb-3 text-lg font-semibold text-gray-900">Tanggapan instansi</h2>
                    <p class="m-0 max-w-prose text-base text-gray-900 wrap-break-word">{!! nl2br(e($application->feedback)) !!}</p>
                    @if ($proyek)
                        <div class="mt-4">
                            <x-ui.button variant="secondary" icon-end="arrow-right" :href="route('student.projects.show', $proyek->id)">Buka proyek</x-ui.button>
                        </div>
                    @endif
                </section>
            @endif

            <section aria-labelledby="motivasi">
                <h2 id="motivasi" class="mt-0 mb-3 text-lg font-semibold text-gray-900">Motivasi</h2>
                @if (filled($application->motivation))
                    {{-- pemenggalan normal per kata; hanya untai panjang tanpa spasi yang dipotong --}}
                    <p class="m-0 max-w-prose text-base text-gray-900 wrap-break-word">{!! nl2br(e($application->motivation)) !!}</p>
                @else
                    <p class="m-0 text-sm text-gray-600">Tidak ada motivasi yang tersimpan.</p>
                @endif
            </section>

            <section aria-labelledby="dokumen">
                <h2 id="dokumen" class="mt-0 mb-3 text-lg font-semibold text-gray-900">Dokumen</h2>
                @if ($application->proposal_content)
                    <div class="flex flex-wrap items-center gap-3 rounded-lg border border-gray-200 p-3">
                        <x-icon name="file-text" class="text-gray-500" />
                        <div class="min-w-0 flex-1">
                            <p class="m-0 text-sm font-medium text-gray-900 wrap-anywhere">{{ $application->proposal_filename ?? 'Proposal' }}</p>
                            <p class="m-0 text-xs text-gray-500">Proposal · {{ $application->proposal_size_formatted }}</p>
                        </div>
                        <x-ui.button variant="secondary" icon="external-link" :href="route('student.applications.download-proposal', $application->id)"
                                     target="_blank" rel="noopener">Lihat proposal</x-ui.button>
                    </div>
                @else
                    <p class="m-0 text-sm text-gray-600">Tidak ada proposal yang dilampirkan.</p>
                @endif
            </section>

            <section aria-labelledby="riwayat">
                <h2 id="riwayat" class="mt-0 mb-3 text-lg font-semibold text-gray-900">Riwayat</h2>
                <ol class="m-0 ml-1 list-none border-l border-gray-200 p-0">
                    @foreach ($riwayat as $step)
                        <li class="relative pb-4 pl-5 last:pb-0">
                            <span class="absolute top-1.5 -left-[5px] size-2.5 rounded-full {{ $step['tone'] }}" aria-hidden="true"></span>
                            <p class="m-0 text-sm font-semibold text-gray-900">{{ $step['label'] }}</p>
                            <p class="m-0 text-sm text-gray-600 tabular-nums">
                                <time datetime="{{ $step['date']->toIso8601String() }}">{{ $waktu($step['date']) }}</time>
                                <span class="text-gray-500">· {{ $relatif($step['date']) }}</span>
                            </p>
                        </li>
                    @endforeach
                    @if (in_array($application->status, ['pending', 'reviewed'], true))
                        <li class="relative pl-5">
                            <span class="absolute top-1.5 -left-[5px] size-2.5 rounded-full border-2 border-gray-400 bg-white" aria-hidden="true"></span>
                            <p class="m-0 text-sm font-semibold text-gray-900">{{ $application->status === 'pending' ? 'Menunggu tinjauan instansi' : 'Menunggu keputusan instansi' }}</p>
                            <p class="m-0 text-sm text-gray-600">Anda akan mendapat notifikasi saat status berubah.</p>
                        </li>
                    @endif
                </ol>
            </section>

            <section aria-labelledby="tentang-proyek">
                <h2 id="tentang-proyek" class="mt-0 mb-3 text-lg font-semibold text-gray-900">Tentang proyek</h2>
                @if (filled($problem->description))
                    <p class="m-0 max-w-prose text-base text-gray-900 wrap-break-word">{{ $problem->description }}</p>
                @endif
                @if ($lokasi !== '')
                    <p class="mt-3 mb-0 flex items-start gap-2 text-sm text-gray-600"><x-icon name="map-pin" size="sm" class="mt-0.5 flex-none" /><span><span class="sr-only">Lokasi: </span>{{ $lokasi }}</span></p>
                @endif
                <p class="mt-3 mb-0 text-sm">
                    <a href="{{ route('student.browse-problems.show', $problem->id) }}" class="inline-flex min-h-6 items-center gap-1 font-medium text-brand-700 underline-offset-2 hover:underline">
                        Lihat detail masalah<x-icon name="arrow-right" size="sm" />
                    </a>
                </p>
            </section>
        </div>

        <aside class="min-w-0 lg:sticky lg:top-20 lg:self-start" aria-labelledby="info-lamaran">
            <div class="grid gap-4 rounded-lg border border-gray-200 bg-white p-5">
                <h2 id="info-lamaran" class="m-0 text-base font-semibold text-gray-900">Informasi lamaran</h2>
                <x-ui.dl class="grid-cols-2 gap-x-4 lg:grid-cols-1">
                    <div><dt>ID lamaran</dt><dd class="tabular-nums">#{{ str_pad($application->id, 6, '0', STR_PAD_LEFT) }}</dd></div>
                    <div><dt>Status</dt><dd><x-ui.status domain="application" :value="$application->status" /></dd></div>
                    <div><dt>Diajukan</dt><dd class="tabular-nums">{{ $tanggal($application->applied_at) ?? '—' }}</dd></div>
                    @if ($problem->duration_months)
                        <div><dt>Durasi proyek</dt><dd>{{ $problem->duration_months }} bulan</dd></div>
                    @endif
                    @if ($periode)
                        <div><dt>Periode</dt><dd>{{ $periode }}</dd></div>
                    @endif
                    @if ($problem->application_deadline)
                        <div><dt>Tenggat pendaftaran</dt><dd class="tabular-nums">{{ $tanggal($problem->application_deadline) }}</dd></div>
                    @endif
                </x-ui.dl>

                <div class="flex items-center gap-3 border-t border-gray-200 pt-4">
                    <x-ui.avatar :src="$institution->logo_path ? $institution->getLogoUrl() : null" :name="$institution->name" square decorative />
                    <div class="min-w-0">
                        <p class="m-0 text-sm font-semibold text-gray-900 wrap-break-word">{{ $institution->name }}</p>
                        @if (filled($institution->type))
                            <p class="m-0 text-xs text-gray-500">{{ $institution->type }}</p>
                        @endif
                    </div>
                </div>
            </div>

            @if ($application->status === 'pending')
                <div class="mt-4">
                    <form action="{{ route('student.applications.withdraw', $application->id) }}" method="POST"
                          data-sg-confirm-title="Batalkan lamaran?"
                          data-sg-confirm="Lamaran untuk “{{ $problem->title }}” akan dihapus beserta motivasi dan proposalnya. Tindakan ini tidak dapat dibatalkan."
                          data-sg-confirm-label="Ya, batalkan lamaran">
                        @csrf
                        @method('DELETE')
                        <x-ui.button variant="danger" type="submit" icon="x" block>Batalkan lamaran</x-ui.button>
                    </form>
                    <p class="mt-2 mb-0 text-xs text-gray-500">Lamaran hanya dapat dibatalkan selama berstatus Menunggu.</p>
                </div>
            @endif
        </aside>
    </div>
</div>
@endsection
