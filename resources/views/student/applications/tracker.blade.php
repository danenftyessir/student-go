{{-- resources/views/student/applications/tracker.blade.php --}}
@extends('layouts.app')

@section('title', 'Pelacak Lamaran')

@section('content')
@php
    // phase 29 paket A: pelacak sebagai ringkasan + dua tabel (5 lamaran terbaru per jenis), status dari StatusVocabulary
    // (kata sama dengan dashboard dan Aplikasi Saya). angka hanya dari variabel controller (guardrail 2);
    // grafik 'Aktivitas Minggu Ini', persentase keberhasilan dan kartu 'Aksi Cepat' dihapus.
    $tanggal = fn ($date) => $date ? $date->locale('id')->translatedFormat('j M Y') : '—';
    $relatif = fn ($date) => $date ? $date->locale('id')->diffForHumans() : null;
@endphp

<div class="sg-container pb-16">
    <x-ui.page-header title="Pelacak lamaran" description="Pantau perkembangan lamaran proyek KKN dan lowongan Anda di satu tempat.">
        <x-slot:actions>
            <x-ui.button variant="secondary" icon-end="arrow-right" :href="route('student.applications.index')">Lihat semua aplikasi</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($totalApplications === 0)
        <x-ui.empty-state illustration="no-applications" title="Belum ada lamaran"
                          description="Lamaran proyek KKN dan lowongan yang Anda kirim akan muncul di sini beserta statusnya.">
            <x-slot:action>
                <div class="flex flex-wrap justify-center gap-3">
                    <x-ui.button :href="route('student.browse-problems.index')">Telusuri masalah</x-ui.button>
                    <x-ui.button variant="secondary" :href="route('student.jobs.index')">Cari lowongan</x-ui.button>
                </div>
            </x-slot:action>
        </x-ui.empty-state>
    @else
        {{-- .sg-summary memasang margin 0 di luar layer utilitas, jadi jaraknya di pembungkus --}}
        <div class="mb-8">
            <x-ui.summary label="Ringkasan semua lamaran" :items="[
                ['value' => $totalApplications, 'label' => 'lamaran'],
                ['value' => $activeCount ?: null, 'label' => 'sedang diproses'],
                ['value' => $successCount ?: null, 'label' => 'diterima atau direkrut'],
                ['value' => $rejectedCount ?: null, 'label' => 'ditolak'],
            ]" />
        </div>

        <div class="grid gap-10">
            <section aria-labelledby="lamaran-lowongan">
                <div class="mb-3 flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
                    <h2 id="lamaran-lowongan" class="m-0 text-lg font-semibold text-gray-900">Lamaran lowongan</h2>
                    @if ($jobApplications->count() > 5)
                        <a href="{{ route('student.applications.index', ['tab' => 'jobs']) }}" class="inline-flex min-h-6 items-center gap-1 text-sm font-medium text-brand-700 underline-offset-2 hover:underline">
                            Lihat semua {{ $jobApplications->count() }} lamaran<x-icon name="arrow-right" size="sm" />
                        </a>
                    @endif
                </div>
                @if ($jobApplications->isEmpty())
                    <p class="m-0 text-sm text-gray-600">Belum ada lamaran lowongan. <a href="{{ route('student.jobs.index') }}" class="font-medium text-brand-700 underline-offset-2 hover:underline">Cari lowongan</a></p>
                @else
                    <x-ui.table caption="Lima lamaran lowongan terbaru">
                        <x-slot:head>
                            <th scope="col">Posisi</th>
                            <th scope="col">Perusahaan</th>
                            <th scope="col">Status</th>
                            <th scope="col">Dilamar</th>
                        </x-slot:head>
                        @foreach ($jobApplications->take(5) as $app)
                            @php $applied = $app->applied_at ?? $app->created_at; @endphp
                            <tr>
                                <td data-label="Posisi">
                                    @if ($app->jobPosting)
                                        <a href="{{ route('student.jobs.show', $app->job_posting_id) }}" class="sg-table__primary">{{ $app->jobPosting->title }}</a>
                                    @else
                                        <span class="sg-table__primary">Lowongan tidak tersedia</span>
                                    @endif
                                </td>
                                <td data-label="Perusahaan"><span>{{ $app->jobPosting?->company->name ?? '—' }}</span></td>
                                <td data-label="Status"><span><x-ui.status domain="job_application" :value="$app->status" /></span></td>
                                <td data-label="Dilamar" class="whitespace-nowrap tabular-nums">
                                    <span>{{ $tanggal($applied) }}@if ($applied)<span class="sg-table__meta">{{ $relatif($applied) }}</span>@endif</span>
                                </td>
                            </tr>
                        @endforeach
                    </x-ui.table>
                @endif
            </section>

            <section aria-labelledby="lamaran-proyek">
                <div class="mb-3 flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
                    <h2 id="lamaran-proyek" class="m-0 text-lg font-semibold text-gray-900">Lamaran proyek KKN</h2>
                    @if ($projectApplications->count() > 5)
                        <a href="{{ route('student.applications.index', ['tab' => 'projects']) }}" class="inline-flex min-h-6 items-center gap-1 text-sm font-medium text-brand-700 underline-offset-2 hover:underline">
                            Lihat semua {{ $projectApplications->count() }} lamaran<x-icon name="arrow-right" size="sm" />
                        </a>
                    @endif
                </div>
                @if ($projectApplications->isEmpty())
                    <p class="m-0 text-sm text-gray-600">Belum ada lamaran proyek KKN. <a href="{{ route('student.browse-problems.index') }}" class="font-medium text-brand-700 underline-offset-2 hover:underline">Telusuri masalah</a></p>
                @else
                    <x-ui.table caption="Lima lamaran proyek KKN terbaru">
                        <x-slot:head>
                            <th scope="col">Proyek</th>
                            <th scope="col">Instansi</th>
                            <th scope="col">Status</th>
                            <th scope="col">Diajukan</th>
                        </x-slot:head>
                        @foreach ($projectApplications->take(5) as $app)
                            <tr>
                                <td data-label="Proyek">
                                    <a href="{{ route('student.applications.show', $app->id) }}" class="sg-table__primary">{{ $app->problem->title ?? 'Masalah tidak tersedia' }}</a>
                                </td>
                                <td data-label="Instansi"><span>{{ $app->problem?->institution->name ?? '—' }}</span></td>
                                <td data-label="Status"><span><x-ui.status domain="application" :value="$app->status" /></span></td>
                                <td data-label="Diajukan" class="whitespace-nowrap tabular-nums">
                                    <span>{{ $tanggal($app->applied_at) }}@if ($app->applied_at)<span class="sg-table__meta">{{ $relatif($app->applied_at) }}</span>@endif</span>
                                </td>
                            </tr>
                        @endforeach
                    </x-ui.table>
                @endif
            </section>

            @if ($recentUpdates->count() > 0)
                <section aria-labelledby="perubahan-terbaru">
                    <h2 id="perubahan-terbaru" class="mt-0 mb-3 text-lg font-semibold text-gray-900">Perubahan status terbaru</h2>
                    <ul class="m-0 max-w-2xl list-none divide-y divide-gray-200 p-0">
                        @foreach ($recentUpdates as $update)
                            <li class="flex flex-wrap items-center justify-between gap-x-4 gap-y-1 py-3">
                                <span class="min-w-0 text-sm text-gray-900 wrap-break-word">{{ $update->jobPosting->title ?? 'Lamaran lowongan' }}</span>
                                <span class="flex items-center gap-3">
                                    <x-ui.status domain="job_application" :value="$update->status" />
                                    <time class="text-xs text-gray-500" datetime="{{ $update->updated_at->toIso8601String() }}">{{ $relatif($update->updated_at) }}</time>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>
    @endif
</div>
@endsection
