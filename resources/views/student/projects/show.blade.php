{{-- resources/views/student/projects/show.blade.php --}}
@extends('layouts.app')

@section('title', $project->title)

@section('content')
@php
    // phase 29 paket B: detail proyek aktif mahasiswa di atas pola detail proyek instansi (phase 19).
    // progres = meter tahap (milestone selesai / total); progress_percentage tersimpan tidak ditampilkan.
    // syarat tombol laporan akhir (progress_percentage >= 80) dan endpoint milestone tetap sama.
    $tanggal = fn ($date) => $date ? \Illuminate\Support\Carbon::parse($date)->locale('id')->translatedFormat('j M Y') : null;
    $institution = $project->institution;
    $isActive = $project->status === 'active';
    $sdgCategories = is_array($project->problem->sdg_categories)
        ? $project->problem->sdg_categories
        : (is_string($project->problem->sdg_categories) ? json_decode($project->problem->sdg_categories, true) ?? [] : []);
    $impactMetrics = $project->status === 'completed' && $project->impact_metrics
        ? (is_array($project->impact_metrics) ? $project->impact_metrics : json_decode($project->impact_metrics, true) ?? [])
        : [];
@endphp

<div class="sg-container sg-inst-page">
    <x-ui.page-header :title="$project->title" :description="$institution->name" class="sg-sp-header"
                      :breadcrumbs="['Proyek Saya' => route('student.projects.index'), Str::limit($project->title, 40) => null]">
        @if ($isActive || $project->final_report_path)
            <x-slot:actions>
                @if ($isActive)
                    <x-ui.button :href="route('student.projects.create-report', $project->id)" icon="file-text">Buat laporan kemajuan</x-ui.button>
                    @if ($project->progress_percentage >= 80)
                        <x-ui.button variant="secondary" :href="route('student.projects.create-final-report', $project->id)" icon="send">Kirim laporan akhir</x-ui.button>
                    @endif
                @endif
                @if ($project->final_report_path)
                    <a href="{{ asset('storage/' . $project->final_report_path) }}" target="_blank" class="sg-btn sg-btn--secondary">
                        <x-icon name="download" size="sm" />Unduh laporan akhir
                    </a>
                @endif
            </x-slot:actions>
        @endif
    </x-ui.page-header>

    <div class="sg-inst-detail">
        <div class="sg-inst-detail__main">
            <section class="sg-inst-detail__block" aria-labelledby="judul-tahap">
                <h2 id="judul-tahap">Tahap proyek</h2>
                @if ($project->milestones->count() > 0)
                    <ol class="sg-inst-timeline">
                        @foreach ($project->milestones as $milestone)
                            @php
                                $done = $milestone->status === 'completed';
                                $canUpdate = $isActive && ! $done;
                            @endphp
                            <li @if ($canUpdate) x-data="{ open: false }" @endif>
                                <span class="sg-inst-timeline__dot" @if ($done) data-done @endif aria-hidden="true">@if ($done)<x-icon name="check" size="sm" />@endif</span>
                                <div class="sg-inst-timeline__body">
                                    <p class="sg-inst-timeline__title">
                                        {{ $milestone->title }}
                                        <x-ui.status domain="milestone" :value="$milestone->status" />
                                    </p>
                                    @if (filled($milestone->description))
                                        <p class="sg-inst-detail__note">{{ $milestone->description }}</p>
                                    @endif
                                    <p class="sg-inst-timeline__meta">
                                        @if ($milestone->target_date)Target {{ $tanggal($milestone->target_date) }}@endif
                                        @if ($done)
                                            @if ($milestone->completed_at) · Selesai {{ $tanggal($milestone->completed_at) }}@endif
                                        @elseif ($milestone->is_overdue)
                                            · <span class="sg-inst-warn">Melewati target</span>
                                        @else
                                            · {{ $milestone->days_remaining }} hari lagi
                                        @endif
                                    </p>
                                    @if (filled($milestone->notes))
                                        <p class="sg-inst-detail__note">Catatan: {{ $milestone->notes }}</p>
                                    @endif

                                    @if ($canUpdate)
                                        <div class="sg-sp-milestone__actions">
                                            <button type="button" class="sg-btn sg-btn--tertiary" x-on:click="open = !open"
                                                    :aria-expanded="open.toString()" aria-controls="milestone-form-{{ $milestone->id }}">
                                                <x-icon name="pencil" size="sm" />Perbarui kemajuan<span class="sr-only"> {{ $milestone->title }}</span>
                                            </button>
                                        </div>
                                        {{-- formulir sama: dikirim lewat updateMilestone (PUT JSON), field progress_percentage dan notes --}}
                                        <div id="milestone-form-{{ $milestone->id }}" x-show="open" x-cloak class="sg-sp-milestone__form">
                                            <form @submit.prevent="updateMilestone({{ $milestone->id }}, $event)" class="sg-inst-form">
                                                <x-ui.field label="Kemajuan tahap (%)" for="progress-{{ $milestone->id }}" class="sg-sp-milestone__pct">
                                                    <input type="number" name="progress_percentage" id="progress-{{ $milestone->id }}"
                                                           min="0" max="100" value="{{ $milestone->progress_percentage }}" class="sg-input">
                                                </x-ui.field>
                                                <x-ui.field label="Catatan" for="notes-{{ $milestone->id }}">
                                                    <textarea name="notes" id="notes-{{ $milestone->id }}" rows="2" class="sg-textarea">{{ $milestone->notes }}</textarea>
                                                </x-ui.field>
                                                <div class="flex flex-wrap gap-2">
                                                    <x-ui.button type="submit">Simpan</x-ui.button>
                                                    <x-ui.button variant="secondary" x-on:click="open = false">Batal</x-ui.button>
                                                </div>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ol>
                @else
                    <p class="sg-inst-detail__note">Belum ada tahap. Instansi menambahkan milestone proyek ini.</p>
                @endif
            </section>

            <section class="sg-inst-detail__block" aria-labelledby="judul-laporan">
                <h2 id="judul-laporan">Laporan kemajuan</h2>
                @if ($project->reports->isNotEmpty())
                    <x-ui.list label="Laporan kemajuan proyek">
                        @foreach ($project->reports as $report)
                            <x-ui.list-item :title="$report->title" :meta="collect([
                                    $report->type_label,
                                    $report->period_start ? $report->period_start->locale('id')->translatedFormat('j M').' – '.$tanggal($report->period_end) : null,
                                    $report->created_at ? 'dikirim '.$report->created_at->locale('id')->diffForHumans() : null,
                                ])->filter()->implode(' · ')">
                                @if (filled($report->summary))
                                    <p class="sg-inst-review-item__text">{{ Str::limit($report->summary, 150) }}</p>
                                @endif
                                @if ($report->document_path)
                                    <p class="sg-inst-review-item__foot">
                                        <a href="{{ document_url($report->document_path) }}" class="sg-inst-link" target="_blank" download>Unduh dokumen<span class="sr-only"> {{ $report->title }}</span></a>
                                    </p>
                                @endif
                                <x-slot:end><x-ui.status domain="report" :value="$report->status" /></x-slot:end>
                            </x-ui.list-item>
                        @endforeach
                    </x-ui.list>
                @else
                    <p class="sg-inst-detail__note">Belum ada laporan kemajuan.</p>
                @endif
            </section>

            @if (filled($project->description))
                <section class="sg-inst-detail__block">
                    <h2>Deskripsi</h2>
                    <div class="sg-inst-detail__text">{!! nl2br(e($project->description)) !!}</div>
                </section>
            @endif
        </div>

        <aside class="sg-inst-detail__rail" aria-label="Informasi proyek">
            <x-ui.dl>
                <div>
                    <dt>Status</dt>
                    <dd>
                        <x-ui.status domain="project" :value="$project->status" />
                        @if ($project->is_overdue && $isActive)
                            <span class="sg-table__meta mt-1 sg-inst-warn">Melewati tanggal selesai</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt>Tahap</dt>
                    <dd>@include('institution.projects.components.stage-meter', ['milestones' => $project->milestones])</dd>
                </div>
            </x-ui.dl>

            <h2 class="sg-inst-detail__rail-h">Instansi</h2>
            <div class="sg-sp-org">
                <x-ui.avatar :src="$institution->logo_path ? $institution->getLogoUrl() : null" :name="$institution->name" size="sm" square decorative />
                <div>
                    <p class="sg-sp-org__name">{{ $institution->name }}</p>
                    @if (filled($institution->type))<p class="sg-inst-detail__note">{{ $institution->type }}</p>@endif
                </div>
            </div>

            <h2 class="sg-inst-detail__rail-h">Jadwal</h2>
            <x-ui.dl :items="[
                'Mulai' => $tanggal($project->start_date),
                'Rencana selesai' => $tanggal($project->end_date),
                'Durasi' => ($project->duration_days ?? $project->start_date->diffInDays($project->end_date)).' hari',
            ]" />

            <h2 class="sg-inst-detail__rail-h">Lokasi dan SDG</h2>
            <x-ui.dl>
                <div>
                    <dt>Lokasi</dt>
                    <dd>{{ $project->problem->regency->name }}, {{ $project->problem->province->name }}</dd>
                </div>
                @if (count($sdgCategories))
                    <div>
                        <dt>Kategori SDG</dt>
                        <dd class="flex flex-wrap gap-1">
                            @foreach ($sdgCategories as $sdg)
                                <x-ui.chip title="{{ sdg_label($sdg) }}">SDG {{ $sdg }}</x-ui.chip>
                            @endforeach
                        </dd>
                    </div>
                @endif
            </x-ui.dl>

            @if (count($impactMetrics))
                <h2 class="sg-inst-detail__rail-h">Dampak yang dilaporkan</h2>
                <x-ui.dl :items="[
                    'Penerima manfaat' => isset($impactMetrics['beneficiaries']) ? number_format((int) $impactMetrics['beneficiaries'], 0, ',', '.').' orang' : null,
                    'Kegiatan terlaksana' => isset($impactMetrics['activities']) ? number_format((int) $impactMetrics['activities'], 0, ',', '.').' kegiatan' : null,
                ]" />
            @endif

            <h2 class="sg-inst-detail__rail-h">Anggota tim</h2>
            @if (isset($teamMembers) && $teamMembers->count() > 0)
                <x-ui.list label="Mahasiswa lain di masalah ini">
                    @foreach ($teamMembers as $member)
                        <x-ui.list-item :title="$member->user->display_name" :href="route('student.friends.profile', $member->id)" :meta="$member->university->name ?? null">
                            <x-slot:leading><x-ui.avatar :src="$member->profile_photo_url" :name="$member->user->display_name" size="sm" decorative /></x-slot:leading>
                        </x-ui.list-item>
                    @endforeach
                </x-ui.list>
            @else
                <p class="sg-inst-detail__note">Belum ada mahasiswa lain yang mengerjakan masalah ini.</p>
            @endif
        </aside>
    </div>
</div>

@push('scripts')
<script>
// perbarui kemajuan milestone (PUT /student/projects/milestones/{id}/update, JSON); hasil lewat toast bersama
function updateMilestone(milestoneId, event) {
    const formData = new FormData(event.target);

    fetch(`/student/projects/milestones/${milestoneId}/update`, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            progress_percentage: formData.get('progress_percentage'),
            notes: formData.get('notes')
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            window.showNotification('Gagal memperbarui milestone: ' + data.message, 'error');
        }
    })
    .catch(() => window.showNotification('Terjadi kesalahan. Silakan coba lagi.', 'error'));
}
</script>
@endpush
@endsection
