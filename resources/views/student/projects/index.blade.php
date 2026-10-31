{{-- resources/views/student/projects/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Proyek Saya')

@section('content')
@php
    // phase 11: proyek sebagai baris (tabel bertumpuk di bawah 640px), tanpa foto pembuka. tahap = status proyek
    // (active/on_hold/completed/cancelled), progres = progress_percentage yang dihitung dari milestone (Project::updateProgress).
    // tidak ada tanggal jatuh tempo laporan di database: kolom laporan menampilkan laporan terakhir yang benar-benar ada.
    $tanggal = fn ($date) => $date ? \Illuminate\Support\Carbon::parse($date)->locale('id')->translatedFormat('j M Y') : null;
    $bulan = fn ($date) => $date ? \Illuminate\Support\Carbon::parse($date)->locale('id')->translatedFormat('j M') : null;
    $label = fn (string $domain, string $value) => \App\Support\StatusVocabulary::label($domain, $value);
    $jenisLaporan = ['weekly' => 'Mingguan', 'monthly' => 'Bulanan', 'final' => 'Akhir'];

    $statusOptions = collect(['active', 'on_hold', 'completed'])->mapWithKeys(fn ($s) => [$s => $label('project', $s)])->all();
    $filtered = request()->filled('status');
    $activeFilters = (int) request()->filled('status') + (int) (request()->filled('sort') && request('sort') !== 'latest');
@endphp

<div class="sg-container pb-16">
    <x-ui.page-header title="Proyek Saya" description="Pantau tahap, milestone dan laporan proyek KKN yang sedang dan sudah Anda jalankan." />

    @if ($stats['total'] > 0)
        <x-ui.summary label="Ringkasan proyek" class="mb-4" :items="[
            ['value' => $stats['total'], 'label' => 'proyek'],
            ['value' => $stats['active'] ?: null, 'label' => $label('project', 'active')],
            ['value' => $stats['completed'] ?: null, 'label' => $label('project', 'completed')],
        ]" />

        {{-- filter hanya bila ada proyek (tidak di atas keadaan kosong) --}}
        <x-ui.filter-bar :action="route('student.projects.index')" :search="false"
                         :reset-href="route('student.projects.index')" :active="$activeFilters" id="filter-proyek" class="mb-4 md:[&_.sg-filter-bar\_\_body]:flex-nowrap!">
            <x-ui.field label="Tahap" for="status" class="md:min-w-44!">
                <x-ui.select name="status" :options="$statusOptions" :selected="request('status')" placeholder="Semua tahap" />
            </x-ui.field>
            <x-ui.field label="Urutkan" for="sort" class="md:min-w-44!">
                <x-ui.select name="sort" :options="['latest' => 'Terbaru', 'oldest' => 'Terlama']" :selected="request('sort', 'latest')" />
            </x-ui.field>
        </x-ui.filter-bar>
    @endif

    @if ($projects->isEmpty())
        @if ($stats['total'] > 0 && $filtered)
            <x-ui.empty-state illustration="no-search-results" title="Tidak ada proyek pada tahap ini"
                              description="Pilih tahap lain atau tampilkan semua proyek Anda.">
                <x-slot:action>
                    <x-ui.button variant="secondary" :href="route('student.projects.index')">Tampilkan semua</x-ui.button>
                </x-slot:action>
            </x-ui.empty-state>
        @else
            <x-ui.empty-state illustration="no-applications" title="Belum ada proyek KKN"
                              description="Proyek muncul di sini setelah lamaran Anda diterima instansi.">
                <x-slot:action>
                    <x-ui.button :href="route('student.browse-problems.index')">Telusuri masalah KKN</x-ui.button>
                </x-slot:action>
            </x-ui.empty-state>
        @endif
    @else
        <x-ui.table caption="Proyek KKN saya">
            <x-slot:head>
                <th scope="col">Proyek</th>
                <th scope="col">Tahap</th>
                <th scope="col">Progres</th>
                <th scope="col">Laporan terakhir</th>
                <th scope="col">Periode</th>
                <th scope="col"><span class="sr-only">Aksi</span></th>
            </x-slot:head>
            @foreach ($projects as $project)
                @php
                    $judul = $project->title ?: ($project->problem->title ?? 'Proyek');
                    $progres = max(0, min(100, (int) $project->progress_percentage));
                    $milestones = $project->milestones;
                    $selesai = $milestones->where('status', 'completed')->count();
                    $berikutnya = $milestones->where('status', '!=', 'completed')->sortBy('target_date')->first();
                    $laporan = $project->reports->sortByDesc('period_end')->first();
                    $saran = $aiSuggestions[$project->id] ?? null;
                @endphp
                <tr>
                    <td data-label="Proyek">
                        <div>
                            <a href="{{ route('student.projects.show', $project->id) }}" class="sg-table__primary">{{ $judul }}</a>
                            <span class="sg-table__meta">{{ $project->institution->name ?? '—' }}</span>
                            @if (filled($saran))
                                <p class="mt-2 mb-0 flex max-w-prose gap-1.5 text-xs leading-4 text-gray-600 wrap-break-word">
                                    <x-icon name="lightbulb" size="sm" class="mt-px flex-none text-gray-500" /><span><span class="font-semibold text-gray-900">Saran AI:</span> {{ $saran }}</span>
                                </p>
                            @endif
                        </div>
                    </td>
                    <td data-label="Tahap">
                        <div>
                            <x-ui.status domain="project" :value="$project->status" />
                            @if ($project->is_overdue && $project->status === 'active')
                                <span class="sg-table__meta mt-1">Melewati tanggal selesai</span>
                            @endif
                        </div>
                    </td>
                    <td data-label="Progres" class="min-w-40">
                        <div>
                            <div class="sg-dash-project__progress">
                                <progress max="100" value="{{ $progres }}" aria-label="Progres {{ $judul }}">{{ $progres }}%</progress>
                                <span>{{ $progres }}%</span>
                            </div>
                            @if ($milestones->isNotEmpty())
                                <span class="sg-table__meta mt-1 tabular-nums">{{ $selesai }} dari {{ $milestones->count() }} milestone selesai</span>
                            @endif
                            @if ($berikutnya)
                                <span class="sg-table__meta">Berikutnya: {{ $berikutnya->title }} · {{ $tanggal($berikutnya->target_date) }}</span>
                            @endif
                        </div>
                    </td>
                    <td data-label="Laporan terakhir">
                        <div>
                            @if ($laporan)
                                <span class="block">{{ $jenisLaporan[$laporan->type] ?? ucfirst($laporan->type) }} · {{ $bulan($laporan->period_start) }}–{{ $tanggal($laporan->period_end) }}</span>
                                <x-ui.status domain="report" :value="$laporan->status" class="mt-1" />
                            @else
                                <span class="text-gray-600">Belum ada laporan</span>
                            @endif
                        </div>
                    </td>
                    <td data-label="Periode" class="whitespace-nowrap tabular-nums">
                        <span>{{ $tanggal($project->start_date) ?? '—' }} – {{ $tanggal($project->end_date) ?? '—' }}</span>
                    </td>
                    <td data-label="Aksi">
                        <div class="flex flex-wrap gap-2">
                            @if ($project->status === 'active')
                                <x-ui.button variant="secondary" icon="file-text" :href="route('student.projects.create-report', $project->id)" class="whitespace-nowrap">Buat laporan</x-ui.button>
                            @elseif ($project->status === 'completed')
                                {{-- toggle portofolio: endpoint dan metode sama (POST, AJAX) --}}
                                <button type="button" class="sg-btn sg-btn--secondary whitespace-nowrap"
                                        data-portfolio-toggle="{{ $project->id }}" aria-pressed="{{ $project->is_portfolio_visible ? 'true' : 'false' }}"
                                        onclick="togglePortfolio({{ $project->id }}, this)">
                                    <x-icon name="{{ $project->is_portfolio_visible ? 'check' : 'plus' }}" size="sm" />
                                    <span class="portfolio-text">{{ $project->is_portfolio_visible ? 'Di portofolio' : 'Tambah ke portofolio' }}</span>
                                </button>
                            @else
                                <span class="text-gray-500" aria-hidden="true">—</span>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-ui.table>

        <x-ui.pagination :paginator="$projects->withQueryString()" label="Halaman proyek" class="mt-4" />
    @endif
</div>

@push('scripts')
<script>
// toggle visibilitas portofolio (POST /student/projects/{id}/toggle-portfolio); notifikasi lewat toast bersama
function togglePortfolio(projectId, button) {
    const text = button.querySelector('.portfolio-text');
    const icon = button.querySelector('use');
    const sprite = icon.getAttribute('href').split('#')[0];
    // aria-disabled, bukan disabled: tombol tetap memegang fokus keyboard selama permintaan berjalan
    if (button.getAttribute('aria-busy') === 'true') return;
    button.setAttribute('aria-busy', 'true');
    button.setAttribute('aria-disabled', 'true');

    fetch(`/student/projects/${projectId}/toggle-portfolio`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            button.setAttribute('aria-pressed', data.is_visible ? 'true' : 'false');
            text.textContent = data.is_visible ? 'Di portofolio' : 'Tambah ke portofolio';
            icon.setAttribute('href', sprite + '#' + (data.is_visible ? 'check' : 'plus'));
            window.showNotification(data.message, 'success');
        } else {
            window.showNotification(data.message || 'Terjadi kesalahan', 'error');
        }
    })
    .catch(() => window.showNotification('Terjadi kesalahan saat mengubah visibilitas portofolio', 'error'))
    .finally(() => {
        button.removeAttribute('aria-busy');
        button.removeAttribute('aria-disabled');
    });
}
</script>
@endpush
@endsection
