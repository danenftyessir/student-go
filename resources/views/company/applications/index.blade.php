@extends('layouts.app')

@section('title', 'Lamaran')

@section('content')
@php
    // phase 24: header + tab tahap (jumlah dari query controller) + filter berlabel + tabel 6 kolom dengan checkbox aksi massal.
    // tahap = 7 nilai yang divalidasi controller (nilai lama pending/reviewed/accepted sudah digabung oleh JobApplication::statusesFor).
    // endpoint tetap: POST /company/applications/bulk-update {application_ids, status}, /{id}/status, /{id}/rating, /{id}/notes.
    use App\Support\StatusVocabulary;

    $stageKeys = ['new', 'reviewing', 'shortlisted', 'interview', 'offer', 'hired', 'rejected'];
    $stageLabels = collect($stageKeys)->mapWithKeys(fn ($s) => [$s => StatusVocabulary::label('job_application', $s)])->all();
    $counts = collect($stageKeys)->mapWithKeys(fn ($s) => [$s => ($applicationsByStatus[$s] ?? collect())->count()])->all();
    $total = $stats['total'] ?? array_sum($counts);
    $tanggal = fn ($date) => $date ? \Illuminate\Support\Carbon::parse($date)->locale('id')->translatedFormat('j M Y') : '—';

    $applications = collect($applicationsByStatus)
        ->flatMap(fn ($apps, $stage) => collect($apps)->map(fn ($app) => ['stage' => $stage, 'app' => $app]))
        ->sortByDesc(fn ($item) => $item['app']->created_at)
        ->values();

    // data baris untuk filter, dialog aksi dan perbandingan (hanya kolom yang sudah tampil di halaman lama)
    $rows = $applications->map(fn ($item) => [
        'id' => $item['app']->id,
        'stage' => $item['stage'],
        'job' => $item['app']->job_posting_id,
        'date' => optional($item['app']->created_at)->toIso8601String(),
        'dateLabel' => $tanggal($item['app']->created_at),
        'name' => $item['app']->user->name ?? 'Tanpa nama',
        'email' => $item['app']->user->email ?? '',
        'position' => $item['app']->jobPosting->title ?? '—',
        'rating' => (int) ($item['app']->rating ?? 0),
        'notes' => $item['app']->notes ?? '',
    ])->all();

    // lowongan yang sedang difilter lewat ?job_posting_id= (tautan dari tabel lowongan)
    $jobFilterId = request('job_posting_id');
    $jobFilterTitle = $jobFilterId
        ? ($jobPostings->firstWhere('id', (int) $jobFilterId)->title ?? optional($applications->first())['app']?->jobPosting?->title)
        : null;
    $ratingLabels = [1 => 'Sangat kurang', 2 => 'Kurang', 3 => 'Cukup', 4 => 'Baik', 5 => 'Sangat baik'];
@endphp

<div class="sg-container sg-co-apps" x-data="applicantPipeline(@js($rows), @js($stageLabels), @js(request('status')))">
    <x-ui.page-header title="Lamaran" description="Pantau pelamar lowongan Anda per tahap dan pindahkan mereka ke tahap berikutnya.">
        @if ($total > 0)
            {{-- akun kosong: aksi ini ada di keadaan kosong (tidak dobel) --}}
            <x-slot:actions>
                <x-ui.button variant="secondary" :href="route('company.jobs.index')" icon="briefcase">Kelola lowongan</x-ui.button>
            </x-slot:actions>
        @endif
    </x-ui.page-header>

    @if ($jobFilterId)
        <p class="sg-co-apps__scope">
            Pelamar untuk lowongan <b>{{ $jobFilterTitle ?? '#' . $jobFilterId }}</b>.
            <a href="{{ route('company.applications.index') }}">Tampilkan semua lowongan</a>
        </p>
    @endif

    @if ($total === 0)
        <div class="sg-co-apps__empty-page">
            <x-ui.empty-state title="Belum ada pelamar" illustration="no-applications"
                              description="Lamaran mahasiswa akan muncul di sini setelah lowongan Anda dibuka dan dilamar.">
                <x-slot:action>
                    <x-ui.button variant="secondary" :href="route('company.jobs.index')">Kelola lowongan</x-ui.button>
                </x-slot:action>
            </x-ui.empty-state>
        </div>
    @else
        <x-ui.summary label="Ringkasan lamaran" class="sg-co-apps__summary" :items="[
            ['value' => $total, 'label' => 'lamaran'],
            ['value' => $counts['new'] ?: null, 'label' => strtolower($stageLabels['new'])],
            ['value' => $counts['interview'] ?: null, 'label' => strtolower($stageLabels['interview'])],
            ['value' => $counts['hired'] ?: null, 'label' => strtolower($stageLabels['hired'])],
        ]" />

        {{-- tab tahap: menyaring tabel di halaman yang sama; jumlah = hasil query controller --}}
        <div class="sg-tabs sg-co-apps__tabs" role="tablist" aria-label="Tahap lamaran" x-ref="tablist" @keydown="onTabKey($event)">
            @foreach (['all' => 'Semua'] + $stageLabels as $key => $label)
                <button type="button" role="tab" class="sg-tabs__tab" id="tahap-{{ $key }}-tab" data-tab="{{ $key }}" aria-controls="tahap-panel"
                        aria-selected="{{ $key === 'all' ? 'true' : 'false' }}" tabindex="{{ $key === 'all' ? '0' : '-1' }}"
                        :aria-selected="(stage === @js($key)).toString()" :tabindex="stage === @js($key) ? 0 : -1"
                        @click="setStage(@js($key))">
                    {{ $label }}
                    <span class="sg-tabs__count">{{ number_format($key === 'all' ? $total : $counts[$key], 0, ',', '.') }}</span>
                </button>
            @endforeach
        </div>

        <div id="tahap-panel" role="tabpanel" aria-labelledby="tahap-all-tab" :aria-labelledby="'tahap-' + stage + '-tab'" class="sg-co-apps__panel">
            {{-- filter di sisi klien (sama seperti halaman lama): posisi, tanggal melamar, nama --}}
            <div class="sg-co-apps__filters" role="search" aria-label="Filter lamaran">
                <x-ui.field label="Posisi" for="filter-posisi">
                    <x-ui.select name="filter_position" id="filter-posisi" :options="$jobPostings->pluck('title', 'id')->all()" placeholder="Semua posisi" x-model="filters.position" />
                </x-ui.field>
                <x-ui.field label="Tanggal melamar" for="filter-tanggal">
                    <x-ui.select name="filter_date" id="filter-tanggal" :options="['today' => 'Hari ini', 'week' => '7 hari terakhir', 'month' => '30 hari terakhir']" placeholder="Semua waktu" x-model="filters.dateRange" />
                </x-ui.field>
                <x-ui.search name="filter_search" id="filter-nama" label="Cari nama kandidat" :hide-label="false" placeholder="Nama kandidat" x-model.debounce.200ms="filters.search" />
                <x-ui.button variant="tertiary" class="sg-co-apps__reset" x-show="hasActiveFilters" x-cloak @click="resetFilters()">Atur ulang</x-ui.button>
            </div>

            <div class="sg-co-apps__toolbar">
                <label class="sg-check">
                    <input type="checkbox" :checked="allSelected" x-effect="$el.indeterminate = someSelected" :disabled="visibleCount === 0" @change="toggleAll($event.target.checked)">
                    <span>Pilih semua yang tampil</span>
                </label>
                <p class="sg-co-apps__count" aria-live="polite"><b x-text="visibleCount">{{ $total }}</b> pelamar ditampilkan</p>
            </div>

            <x-ui.table caption="Daftar pelamar" class="sg-co-apps__table">
                <x-slot:head>
                    <th scope="col" class="sg-co-apps__pick"><span class="sr-only">Pilih</span></th>
                    <th scope="col">Kandidat</th>
                    <th scope="col">Posisi</th>
                    <th scope="col">Rating</th>
                    <th scope="col">Tanggal melamar</th>
                    <th scope="col">Status</th>
                    <th scope="col">Aksi</th>
                </x-slot:head>

                @foreach ($applications as $item)
                    @php($app = $item['app'])
                    @php($name = $app->user->name ?? 'Tanpa nama')
                    <tr data-application-id="{{ $app->id }}" x-show="shows({{ $app->id }})" :class="{ 'is-selected': isSelected({{ $app->id }}) }">
                        <td data-label="Pilih" class="sg-co-apps__pick">
                            <input type="checkbox" value="{{ $app->id }}" x-model="selected" aria-label="Pilih {{ $name }}">
                        </td>
                        <td data-label="Kandidat">
                            <span class="sg-co-apps__person">
                                <x-ui.avatar :name="$name" size="sm" decorative />
                                <span class="sg-co-apps__who">
                                    <a href="{{ route('company.applications.show', $app->id) }}" class="sg-table__primary">{{ $name }}</a>
                                    @if ($app->user?->email)<span class="sg-table__meta">{{ $app->user->email }}</span>@endif
                                </span>
                            </span>
                        </td>
                        <td data-label="Posisi">{{ $app->jobPosting->title ?? '—' }}</td>
                        <td data-label="Rating">
                            @if ($app->rating)
                                <span class="sg-co-apps__rating" role="img" aria-label="Rating {{ $app->rating }} dari 5">
                                    <span class="sg-co-apps__rating-bars" aria-hidden="true">@for ($i = 1; $i <= 5; $i++)<i @class(['is-on' => $i <= $app->rating])></i>@endfor</span>
                                    <span aria-hidden="true">{{ $app->rating }}/5</span>
                                </span>
                            @else
                                <span class="sg-co-apps__muted">Belum dinilai</span>
                            @endif
                        </td>
                        <td data-label="Tanggal melamar" class="sg-co-apps__date">{{ $tanggal($app->created_at) }}</td>
                        {{-- badge dari tahap (nilai lama accepted = Direkrut, sama dengan tabnya) --}}
                        <td data-label="Status"><x-ui.status domain="job_application" :value="$item['stage']" /></td>
                        <td data-label="Aksi">
                            <span class="sg-co-apps__actions">
                                <x-ui.icon-button icon="eye" :label="'Lihat lamaran ' . $name" :href="route('company.applications.show', $app->id)" />
                                <x-ui.button variant="tertiary" icon="clipboard-check" @click="openActions({{ $app->id }})" aria-label="Ubah tahap, rating atau catatan {{ $name }}">Tindak lanjut</x-ui.button>
                            </span>
                        </td>
                    </tr>
                @endforeach

                {{-- keadaan kosong per tahap / per filter, di dalam tabel --}}
                <tr class="sg-co-apps__empty-row" x-show="visibleCount === 0" x-cloak>
                    <td colspan="7">
                        <div class="sg-empty" x-show="!hasActiveFilters">
                            <img src="{{ asset('assets/illustrations/empty-states/no-applications.svg') }}" alt="" width="160" height="120">
                            <h2>Belum ada pelamar</h2>
                            <p>Belum ada pelamar di tahap <span x-text="stageLabels[stage] || 'ini'"></span>.</p>
                            <x-ui.button variant="secondary" :href="route('company.jobs.index')">Kelola lowongan</x-ui.button>
                        </div>
                        <div class="sg-empty" x-show="hasActiveFilters">
                            <img src="{{ asset('assets/illustrations/empty-states/no-search-results.svg') }}" alt="" width="160" height="120">
                            <h2>Tidak ada pelamar yang cocok</h2>
                            <p>Tidak ada pelamar dengan posisi, tanggal atau nama ini di tahap yang dipilih.</p>
                            <x-ui.button variant="secondary" @click="resetFilters()">Atur ulang filter</x-ui.button>
                        </div>
                    </td>
                </tr>
            </x-ui.table>
        </div>

        {{-- bilah aksi massal: hanya muncul bila ada lamaran yang dipilih --}}
        <div class="sg-co-apps__bulk" x-show="selected.length > 0" x-cloak role="region" aria-label="Aksi massal">
            <p class="sg-co-apps__bulk-text" aria-live="polite"><b x-text="selected.length"></b> lamaran dipilih</p>
            <div class="sg-co-apps__bulk-actions">
                <x-ui.button variant="secondary" @click="bulkUpdateStatus('shortlisted')" ::disabled="isLoading">Pindah ke {{ $stageLabels['shortlisted'] }}</x-ui.button>
                <x-ui.button variant="secondary" @click="bulkUpdateStatus('interview')" ::disabled="isLoading">Pindah ke {{ $stageLabels['interview'] }}</x-ui.button>
                <x-ui.button variant="danger" @click="bulkUpdateStatus('rejected')" ::disabled="isLoading">Tolak</x-ui.button>
                <x-ui.button variant="tertiary" icon="users" x-show="selected.length >= 2 && selected.length <= 3" @click="openComparison()">Bandingkan</x-ui.button>
                <x-ui.button variant="tertiary" icon="download" @click="exportSelected()">Ekspor CSV</x-ui.button>
                <x-ui.button variant="tertiary" @click="selected = []">Batal pilih</x-ui.button>
            </div>
        </div>

        {{-- dialog tindak lanjut: tahap, rating, catatan, wawancara (endpoint sama dengan halaman lama) --}}
        <x-ui.modal id="aksi-pelamar" title="Tindak lanjut pelamar" class="sg-co-apps__dialog">
            <template x-if="current">
                <div class="sg-co-apps__dialog-body">
                    <p class="sg-co-apps__dialog-who">
                        <b x-text="current.name"></b>
                        <span x-text="current.position"></span>
                    </p>

                    <x-ui.tablist :tabs="['status' => 'Tahap', 'rating' => 'Rating', 'notes' => 'Catatan', 'interview' => 'Wawancara']" label="Jenis tindak lanjut" prefix="aksi">
                        <x-ui.tab-panel id="status" prefix="aksi" :active="true">
                            <div class="sg-co-apps__form">
                                <x-ui.field label="Pindahkan ke tahap" for="aksi-status">
                                    <x-ui.select name="status" id="aksi-status" :options="$stageLabels" x-model="newStatus" />
                                </x-ui.field>
                                <x-ui.field label="Alasan penolakan (opsional)" for="aksi-alasan" x-show="newStatus === 'rejected'">
                                    <x-ui.textarea name="rejection_reason" id="aksi-alasan" rows="3" maxlength="500" x-model="rejectionReason" />
                                </x-ui.field>
                                <x-ui.button @click="updateApplicationStatus()" ::disabled="isLoading">Simpan tahap</x-ui.button>
                            </div>
                        </x-ui.tab-panel>

                        <x-ui.tab-panel id="rating" prefix="aksi">
                            <fieldset class="sg-co-apps__form">
                                <legend class="sg-label">Rating kandidat</legend>
                                <div class="sg-co-apps__rating-choices">
                                    @foreach ($ratingLabels as $value => $label)
                                        <label class="sg-check">
                                            <input type="radio" name="rating" value="{{ $value }}" x-model.number="tempRating">
                                            <span>{{ $value }} · {{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                <x-ui.button @click="saveRating()" ::disabled="isLoading || !tempRating">Simpan rating</x-ui.button>
                            </fieldset>
                        </x-ui.tab-panel>

                        <x-ui.tab-panel id="notes" prefix="aksi">
                            <div class="sg-co-apps__form">
                                <x-ui.field label="Catatan internal" for="aksi-catatan" help="Catatan ini hanya terlihat oleh tim Anda.">
                                    <x-ui.textarea name="notes" id="aksi-catatan" rows="5" maxlength="1000" help x-model="tempNotes" />
                                </x-ui.field>
                                <x-ui.button @click="saveNotes()" ::disabled="isLoading || !tempNotes.trim()">Simpan catatan</x-ui.button>
                            </div>
                        </x-ui.tab-panel>

                        <x-ui.tab-panel id="interview" prefix="aksi">
                            <div class="sg-co-apps__form">
                                <div class="sg-co-apps__pair">
                                    <x-ui.field label="Tanggal" for="aksi-tanggal">
                                        <x-ui.input type="date" name="interview_date" id="aksi-tanggal" x-model="interviewDate" />
                                    </x-ui.field>
                                    <x-ui.field label="Waktu" for="aksi-waktu">
                                        <x-ui.input type="time" name="interview_time" id="aksi-waktu" x-model="interviewTime" />
                                    </x-ui.field>
                                </div>
                                <x-ui.field label="Jenis wawancara" for="aksi-jenis">
                                    <x-ui.select name="interview_type" id="aksi-jenis" :options="['online' => 'Daring (panggilan video)', 'onsite' => 'Tatap muka (di kantor)', 'phone' => 'Telepon']" x-model="interviewType" />
                                </x-ui.field>
                                <x-ui.field label="Catatan wawancara" for="aksi-catatan-wawancara" help="Disimpan sebagai catatan internal (menggantikan catatan sebelumnya) dan tahap berubah menjadi Wawancara.">
                                    <x-ui.textarea name="interview_notes" id="aksi-catatan-wawancara" rows="3" help placeholder="Tautan rapat atau alamat" x-model="interviewNotes" />
                                </x-ui.field>
                                <x-ui.button @click="scheduleInterview()" ::disabled="isLoading || !interviewDate || !interviewTime">Jadwalkan wawancara</x-ui.button>
                            </div>
                        </x-ui.tab-panel>
                    </x-ui.tablist>
                </div>
            </template>
        </x-ui.modal>

        {{-- perbandingan 2-3 kandidat terpilih (fitur lama) --}}
        <x-ui.modal id="banding-pelamar" title="Perbandingan kandidat" class="sg-co-apps__compare">
            <div class="sg-table-wrap sg-co-apps__compare-table" role="region" aria-label="Perbandingan kandidat" tabindex="0">
                <table class="sg-table">
                    <caption class="sr-only">Perbandingan kandidat terpilih</caption>
                    <thead>
                        <tr>
                            <th scope="col"><span class="sr-only">Kriteria</span></th>
                            <template x-for="c in comparison" :key="c.id"><th scope="col" x-text="c.name"></th></template>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><th scope="row">Posisi</th><template x-for="c in comparison" :key="c.id"><td x-text="c.position"></td></template></tr>
                        <tr><th scope="row">Tahap</th><template x-for="c in comparison" :key="c.id"><td x-text="stageLabels[c.stage]"></td></template></tr>
                        <tr><th scope="row">Rating</th><template x-for="c in comparison" :key="c.id"><td x-text="c.rating ? c.rating + '/5' : 'Belum dinilai'"></td></template></tr>
                        <tr><th scope="row">Tanggal melamar</th><template x-for="c in comparison" :key="c.id"><td x-text="c.dateLabel"></td></template></tr>
                        <tr><th scope="row">Email</th><template x-for="c in comparison" :key="c.id"><td x-text="c.email || '—'"></td></template></tr>
                        <tr><th scope="row">Catatan</th><template x-for="c in comparison" :key="c.id"><td class="sg-co-apps__compare-notes" x-text="c.notes || '—'"></td></template></tr>
                        <tr><th scope="row"><span class="sr-only">Tautan</span></th><template x-for="c in comparison" :key="c.id"><td><a :href="'{{ url('company/applications') }}/' + c.id">Lihat lamaran</a></td></template></tr>
                    </tbody>
                </table>
            </div>
        </x-ui.modal>
    @endif
</div>
@endsection

@push('scripts')
<script>
function applicantPipeline(rows, stageLabels, initialStage) {
    const FLASH_KEY = 'sg_co_apps_flash';
    const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content;
    const byId = Object.fromEntries(rows.map((r) => [r.id, r]));

    return {
        rows,
        stageLabels,
        stage: 'all',
        filters: { position: '', dateRange: '', search: '' },
        selected: [],
        isLoading: false,

        // dialog tindak lanjut
        current: null,
        newStatus: '',
        rejectionReason: '',
        tempRating: 0,
        tempNotes: '',
        interviewDate: '',
        interviewTime: '',
        interviewType: 'online',
        interviewNotes: '',

        comparison: [],

        init() {
            // tab dari #tahap-… (bertahan setelah muat ulang) atau ?status= lama
            const fromHash = location.hash.replace('#tahap-', '');
            if (fromHash in stageLabels) this.stage = fromHash;
            else if (initialStage && initialStage in stageLabels) this.stage = initialStage;
            this.$watch('filters', () => this.prune(), { deep: true });

            // pesan sukses setelah muat ulang
            try {
                const flash = sessionStorage.getItem(FLASH_KEY);
                if (flash) {
                    sessionStorage.removeItem(FLASH_KEY);
                    this.$nextTick(() => window.showNotification?.(flash, 'success'));
                }
            } catch (e) {}
        },

        // filter
        matches(row) {
            if (this.stage !== 'all' && row.stage !== this.stage) return false;
            if (this.filters.position && String(row.job) !== String(this.filters.position)) return false;
            if (this.filters.search && !row.name.toLowerCase().includes(this.filters.search.trim().toLowerCase())) return false;
            if (this.filters.dateRange) {
                const applied = new Date(row.date);
                const now = new Date();
                if (this.filters.dateRange === 'today' && applied.toDateString() !== now.toDateString()) return false;
                if (this.filters.dateRange === 'week' && applied < new Date(now - 7 * 864e5)) return false;
                if (this.filters.dateRange === 'month' && applied < new Date(now - 30 * 864e5)) return false;
            }
            return true;
        },
        shows(id) { return this.matches(byId[id]); },
        get visibleRows() { return this.rows.filter((r) => this.matches(r)); },
        get visibleCount() { return this.visibleRows.length; },
        get hasActiveFilters() { return !!(this.filters.position || this.filters.dateRange || this.filters.search.trim()); },
        resetFilters() { this.filters = { position: '', dateRange: '', search: '' }; },

        // tab tahap
        setStage(key) {
            this.stage = key;
            history.replaceState(null, '', key === 'all' ? location.pathname + location.search : '#tahap-' + key);
            this.prune();
        },
        onTabKey(e) {
            const tabs = [...this.$refs.tablist.querySelectorAll('[role="tab"]')];
            const i = tabs.indexOf(document.activeElement);
            const keys = { ArrowRight: i + 1, ArrowLeft: i - 1, Home: 0, End: tabs.length - 1 };
            if (i === -1 || !(e.key in keys)) return;
            e.preventDefault();
            const next = tabs[(keys[e.key] + tabs.length) % tabs.length];
            next.focus();
            this.setStage(next.dataset.tab);
        },

        // pilihan (yang tersembunyi oleh tab/filter tidak ikut aksi massal)
        isSelected(id) { return this.selected.includes(String(id)); },
        prune() {
            const visible = new Set(this.visibleRows.map((r) => String(r.id)));
            this.selected = this.selected.filter((id) => visible.has(id));
        },
        get allSelected() { return this.visibleCount > 0 && this.selected.length === this.visibleCount; },
        get someSelected() { return this.selected.length > 0 && !this.allSelected; },
        toggleAll(checked) { this.selected = checked ? this.visibleRows.map((r) => String(r.id)) : []; },

        // permintaan ke endpoint lama
        async post(url, body) {
            this.isLoading = true;
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' },
                    body: JSON.stringify(body),
                });
                return response.ok;
            } catch (error) {
                console.error('Gagal mengirim permintaan:', error);
                return false;
            } finally {
                this.isLoading = false;
            }
        },
        done(message) {
            // muat ulang agar tab, jumlah dan badge kembali dari database
            try { sessionStorage.setItem(FLASH_KEY, message); } catch (e) {}
            location.reload();
        },
        fail(message) { window.showNotification?.(message, 'error'); },

        // dialog tindak lanjut
        openActions(id) {
            const row = byId[id];
            this.current = row;
            this.newStatus = row.stage;
            this.rejectionReason = '';
            this.tempRating = row.rating || 0;
            this.tempNotes = row.notes || '';
            this.interviewDate = '';
            this.interviewTime = '';
            this.interviewType = 'online';
            this.interviewNotes = '';
            this.$dispatch('sg-open-modal', 'aksi-pelamar');
        },
        async updateApplicationStatus() {
            const ok = await this.post(`/company/applications/${this.current.id}/status`, {
                status: this.newStatus,
                rejection_reason: this.rejectionReason,
            });
            ok ? this.done('Tahap lamaran diperbarui.') : this.fail('Tahap lamaran gagal diperbarui.');
        },
        async saveRating() {
            if (!this.tempRating) return;
            const ok = await this.post(`/company/applications/${this.current.id}/rating`, { rating: this.tempRating });
            ok ? this.done('Rating disimpan.') : this.fail('Rating gagal disimpan.');
        },
        async saveNotes() {
            const ok = await this.post(`/company/applications/${this.current.id}/notes`, { notes: this.tempNotes });
            ok ? this.done('Catatan disimpan.') : this.fail('Catatan gagal disimpan.');
        },
        async scheduleInterview() {
            if (!this.interviewDate || !this.interviewTime) return;
            const ok = await this.post(`/company/applications/${this.current.id}/status`, {
                status: 'interview',
                notes: `Wawancara dijadwalkan: ${this.interviewDate} ${this.interviewTime} (${this.interviewType}). ${this.interviewNotes}`,
            });
            ok ? this.done('Wawancara dijadwalkan.') : this.fail('Wawancara gagal dijadwalkan.');
        },

        // aksi massal
        async bulkUpdateStatus(status) {
            if (this.selected.length === 0) return;
            const count = this.selected.length;
            if (status === 'rejected') {
                const yes = await window.sgConfirm({
                    title: 'Tolak lamaran terpilih?',
                    message: `${count} lamaran akan dipindahkan ke tahap ${stageLabels.rejected}.`,
                    confirmLabel: 'Tolak',
                    tone: 'danger',
                });
                if (!yes) return;
            }
            const ok = await this.post('/company/applications/bulk-update', {
                application_ids: this.selected.map(Number),
                status,
            });
            ok ? this.done(`${count} lamaran dipindahkan ke tahap ${stageLabels[status]}.`) : this.fail('Tahap lamaran gagal diperbarui.');
        },
        openComparison() {
            this.comparison = this.selected.map((id) => byId[id]).filter(Boolean);
            this.$dispatch('sg-open-modal', 'banding-pelamar');
        },
        exportSelected() {
            window.location.href = `/company/applications/export?ids=${this.selected.join(',')}`;
        },
    };
}
</script>
@endpush
