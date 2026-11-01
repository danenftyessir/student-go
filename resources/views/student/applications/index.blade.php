{{-- resources/views/student/applications/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Aplikasi Saya')

@section('content')
@php
    // phase 10: daftar lamaran sebagai tabel. tab KKN / Lowongan lewat ?tab= (kontrak lama tetap: tab, search, status,
    // job_status, sort, project_page, job_page). angka ringkasan hanya dari $stats / $jobStats (query controller).
    $tab = $activeTab === 'jobs' ? 'jobs' : 'projects';
    $tanggal = fn ($date) => $date ? \Illuminate\Support\Carbon::parse($date)->locale('id')->translatedFormat('j M Y') : '—';
    $index = route('student.applications.index');
    $label = fn (string $domain, string $value) => \App\Support\StatusVocabulary::label($domain, $value);

    $projectStatuses = collect(['pending', 'reviewed', 'accepted', 'rejected'])->mapWithKeys(fn ($s) => [$s => $label('application', $s)])->all();
    $jobStatuses = collect(['new', 'reviewed', 'shortlisted', 'interview', 'hired', 'rejected'])->mapWithKeys(fn ($s) => [$s => $label('job_application', $s)])->all();

    $statusParam = $tab === 'jobs' ? 'job_status' : 'status';
    $filtered = request()->filled('search') || request()->filled($statusParam);
    $activeFilters = (int) request()->filled($statusParam) + (int) (request()->filled('sort') && request('sort') !== 'latest');
@endphp

<div class="sg-container pb-16">
    <x-ui.page-header title="Aplikasi Saya" description="Pantau lamaran proyek KKN dan lowongan yang sudah Anda kirim.">
        <x-slot:actions>
            <x-ui.button variant="secondary" icon="search" :href="$tab === 'jobs' ? route('student.jobs.index') : route('student.browse-problems.index')">
                {{ $tab === 'jobs' ? 'Cari lowongan' : 'Telusuri masalah' }}
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.tabs label="Jenis lamaran" class="mb-4" :items="[
        ['label' => 'Proyek KKN', 'href' => route('student.applications.index', ['tab' => 'projects']), 'active' => $tab === 'projects', 'count' => $stats['total']],
        ['label' => 'Lowongan', 'href' => route('student.applications.index', ['tab' => 'jobs']), 'active' => $tab === 'jobs', 'count' => $jobStats['total']],
    ]" />

    @if ($tab === 'projects')
        @if ($stats['total'] > 0)
            <x-ui.summary label="Ringkasan lamaran proyek" class="mb-4" :items="[
                ['value' => $stats['total'], 'label' => 'lamaran'],
                ['value' => $stats['pending'] ?: null, 'label' => 'Menunggu'],
                ['value' => $stats['reviewed'] ?: null, 'label' => 'Ditinjau'],
                ['value' => $stats['accepted'] ?: null, 'label' => 'Diterima'],
                ['value' => $stats['rejected'] ?: null, 'label' => 'Ditolak'],
            ]" />

            <x-ui.filter-bar :action="$index" search-label="Cari lamaran proyek" search-placeholder="Judul masalah atau nama instansi"
                             :reset-href="route('student.applications.index', ['tab' => 'projects'])" :active="$activeFilters" id="filter-proyek" class="mb-4 md:[&_.sg-filter-bar\_\_body]:flex-nowrap!">
                <input type="hidden" name="tab" value="projects">
                <x-ui.field label="Status" for="status">
                    <x-ui.select name="status" :options="$projectStatuses" :selected="request('status')" placeholder="Semua status" />
                </x-ui.field>
                <x-ui.field label="Urutkan" for="sort">
                    <x-ui.select name="sort" :options="['latest' => 'Terbaru', 'oldest' => 'Terlama']" :selected="request('sort', 'latest')" />
                </x-ui.field>
            </x-ui.filter-bar>
        @endif

        @if ($applications->isEmpty())
            @if ($stats['total'] > 0 && $filtered)
                <x-ui.empty-state illustration="no-search-results" title="Tidak ada lamaran yang cocok"
                                  description="Ubah kata kunci atau status, atau tampilkan semua lamaran proyek.">
                    <x-slot:action>
                        <x-ui.button variant="secondary" :href="route('student.applications.index', ['tab' => 'projects'])">Tampilkan semua</x-ui.button>
                    </x-slot:action>
                </x-ui.empty-state>
            @else
                <x-ui.empty-state illustration="no-applications" title="Belum ada lamaran proyek"
                                  description="Lamaran yang Anda kirim ke masalah instansi akan muncul di sini beserta statusnya.">
                    <x-slot:action>
                        <x-ui.button :href="route('student.browse-problems.index')">Telusuri masalah</x-ui.button>
                    </x-slot:action>
                </x-ui.empty-state>
            @endif
        @else
            <x-ui.table caption="Lamaran proyek KKN">
                <x-slot:head>
                    <th scope="col">Proyek</th>
                    <th scope="col">Instansi</th>
                    <th scope="col">Status</th>
                    <th scope="col">Diajukan</th>
                    <th scope="col">Tenggat</th>
                    <th scope="col"><span class="sr-only">Aksi</span></th>
                </x-slot:head>
                @foreach ($applications as $application)
                    @php
                        $problem = $application->problem;
                        $place = $problem?->regency->name ?? $problem?->location_regency;
                        $note = in_array($application->status, ['accepted', 'rejected'], true) && filled($application->feedback) ? $application->feedback : null;
                    @endphp
                    <tr>
                        <td data-label="Proyek">
                            <div>
                                <a href="{{ route('student.applications.show', $application->id) }}" class="sg-table__primary">{{ $problem?->title ?? 'Masalah tidak tersedia' }}</a>
                                @if ($place)<span class="sg-table__meta">{{ $place }}</span>@endif
                                @if ($note)
                                    <p class="mt-2 mb-0 max-w-prose rounded-sm bg-gray-50 px-2.5 py-1.5 text-xs leading-4 wrap-break-word">
                                        <span class="font-semibold text-gray-900">{{ $application->status === 'rejected' ? 'Alasan penolakan:' : 'Catatan instansi:' }}</span>
                                        <span class="text-gray-600">{{ $note }}</span>
                                    </p>
                                @endif
                            </div>
                        </td>
                        <td data-label="Instansi"><span>{{ $problem?->institution->name ?? '—' }}</span></td>
                        <td data-label="Status"><span><x-ui.status domain="application" :value="$application->status" /></span></td>
                        <td data-label="Diajukan" class="whitespace-nowrap tabular-nums"><span>{{ $tanggal($application->applied_at) }}</span></td>
                        <td data-label="Tenggat" class="whitespace-nowrap tabular-nums"><span>{{ $tanggal($problem?->application_deadline) }}</span></td>
                        <td data-label="Aksi">
                            @if ($application->status === 'pending')
                                <form action="{{ route('student.applications.withdraw', $application->id) }}" method="POST"
                                      data-sg-confirm-title="Batalkan lamaran?"
                                      data-sg-confirm="Lamaran untuk “{{ $problem?->title }}” akan dihapus beserta motivasi dan proposalnya. Tindakan ini tidak dapat dibatalkan."
                                      data-sg-confirm-label="Ya, batalkan lamaran">
                                    @csrf
                                    @method('DELETE')
                                    <x-ui.button variant="danger" type="submit" class="whitespace-nowrap">Batalkan</x-ui.button>
                                </form>
                            @else
                                <span class="text-gray-500" aria-hidden="true">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>

            <x-ui.pagination :paginator="$applications" label="Halaman lamaran proyek" class="mt-4" />
        @endif
    @else
        @if ($jobStats['total'] > 0)
            <x-ui.summary label="Ringkasan lamaran lowongan" class="mb-4" :items="[
                ['value' => $jobStats['total'], 'label' => 'lamaran'],
                ['value' => $jobStats['new'] ?: null, 'label' => $jobStatuses['new']],
                ['value' => $jobStats['reviewed'] ?: null, 'label' => $jobStatuses['reviewed']],
                ['value' => $jobStats['shortlisted'] ?: null, 'label' => $jobStatuses['shortlisted']],
                ['value' => $jobStats['interview'] ?: null, 'label' => $jobStatuses['interview']],
                ['value' => $jobStats['hired'] ?: null, 'label' => $jobStatuses['hired']],
                ['value' => $jobStats['rejected'] ?: null, 'label' => $jobStatuses['rejected']],
            ]" />

            <x-ui.filter-bar :action="$index" search-label="Cari lamaran lowongan" search-placeholder="Posisi atau nama perusahaan"
                             :reset-href="route('student.applications.index', ['tab' => 'jobs'])" :active="$activeFilters" id="filter-lowongan" class="mb-4 md:[&_.sg-filter-bar\_\_body]:flex-nowrap!">
                <input type="hidden" name="tab" value="jobs">
                <x-ui.field label="Status" for="job_status">
                    <x-ui.select name="job_status" :options="$jobStatuses" :selected="request('job_status')" placeholder="Semua status" />
                </x-ui.field>
                <x-ui.field label="Urutkan" for="sort">
                    <x-ui.select name="sort" :options="['latest' => 'Terbaru', 'oldest' => 'Terlama']" :selected="request('sort', 'latest')" />
                </x-ui.field>
            </x-ui.filter-bar>
        @endif

        @if ($jobApplications->isEmpty())
            @if ($jobStats['total'] > 0 && $filtered)
                <x-ui.empty-state illustration="no-search-results" title="Tidak ada lamaran yang cocok"
                                  description="Ubah kata kunci atau status, atau tampilkan semua lamaran lowongan.">
                    <x-slot:action>
                        <x-ui.button variant="secondary" :href="route('student.applications.index', ['tab' => 'jobs'])">Tampilkan semua</x-ui.button>
                    </x-slot:action>
                </x-ui.empty-state>
            @else
                <x-ui.empty-state illustration="no-applications" title="Belum ada lamaran lowongan"
                                  description="Lamaran kerja atau magang yang Anda kirim akan muncul di sini beserta statusnya.">
                    <x-slot:action>
                        <x-ui.button :href="route('student.jobs.index')">Cari lowongan</x-ui.button>
                    </x-slot:action>
                </x-ui.empty-state>
            @endif
        @else
            <x-ui.table caption="Lamaran lowongan">
                <x-slot:head>
                    <th scope="col">Posisi</th>
                    <th scope="col">Perusahaan</th>
                    <th scope="col">Status</th>
                    <th scope="col">Dilamar</th>
                    <th scope="col"><span class="sr-only">Aksi</span></th>
                </x-slot:head>
                @foreach ($jobApplications as $jobApp)
                    @php
                        $job = $jobApp->jobPosting;
                        $meta = collect([$job?->location, $job?->job_type ? ucfirst(str_replace('_', ' ', $job->job_type)) : null])->filter()->implode(' · ');
                    @endphp
                    <tr>
                        <td data-label="Posisi">
                            <div>
                                @if ($job)
                                    <a href="{{ route('student.jobs.show', $jobApp->job_posting_id) }}" class="sg-table__primary">{{ $job->title }}</a>
                                @else
                                    <span class="sg-table__primary">Lowongan tidak tersedia</span>
                                @endif
                                @if ($meta !== '')<span class="sg-table__meta">{{ $meta }}</span>@endif
                            </div>
                        </td>
                        <td data-label="Perusahaan"><span>{{ $job?->company->name ?? '—' }}</span></td>
                        <td data-label="Status"><span><x-ui.status domain="job_application" :value="$jobApp->status" /></span></td>
                        <td data-label="Dilamar" class="whitespace-nowrap tabular-nums"><span>{{ $tanggal($jobApp->applied_at ?? $jobApp->created_at) }}</span></td>
                        <td data-label="Aksi">
                            @if ($jobApp->status === 'new')
                                <form action="{{ route('student.jobs.withdraw', $jobApp->job_posting_id) }}" method="POST"
                                      data-sg-confirm-title="Batalkan lamaran?"
                                      data-sg-confirm="Lamaran untuk posisi “{{ $job?->title }}” akan ditarik. Tindakan ini tidak dapat dibatalkan."
                                      data-sg-confirm-label="Ya, batalkan lamaran">
                                    @csrf
                                    @method('DELETE')
                                    <x-ui.button variant="danger" type="submit" class="whitespace-nowrap">Batalkan</x-ui.button>
                                </form>
                            @else
                                <span class="text-gray-500" aria-hidden="true">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>

            <x-ui.pagination :paginator="$jobApplications" label="Halaman lamaran lowongan" class="mt-4" />
        @endif
    @endif
</div>
@endsection
