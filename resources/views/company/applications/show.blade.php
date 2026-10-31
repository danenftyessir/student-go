@extends('layouts.app')

@section('title', 'Lamaran ' . $application->user->display_name)

@section('content')
@php
    // phase 24: halaman rekaman lamaran (pola sama dengan /company/jobs/{id}): isi di kiri, tindakan + penilaian + riwayat di kanan.
    // endpoint tetap: update-status, shortlist, reject, hire, add-rating, add-notes (semua POST JSON, lalu muat ulang).
    use App\Support\StatusVocabulary;

    $name = $application->user->display_name;
    $job = $application->jobPosting;
    $waktu = fn ($date) => $date ? $date->copy()->locale('id')->translatedFormat('j M Y, H.i') : null;
    $stageKeys = ['new', 'reviewing', 'shortlisted', 'interview', 'offer', 'hired', 'rejected'];
    $stageLabels = collect($stageKeys)->mapWithKeys(fn ($s) => [$s => StatusVocabulary::label('job_application', $s)])->all();
    // nilai lama (pending/reviewed/accepted) dipetakan ke tahap setara untuk pilihan awal
    $currentStage = collect($stageKeys)->first(fn ($s) => in_array($application->status, \App\Models\JobApplication::statusesFor($s), true)) ?? 'new';
    $ratingLabels = [1 => 'Sangat kurang', 2 => 'Kurang', 3 => 'Cukup', 4 => 'Baik', 5 => 'Sangat baik'];

    // riwayat dari kolom waktu yang benar-benar tersimpan
    $history = array_filter([
        ['Melamar', $application->created_at, null],
        ['Ditinjau', $application->reviewed_at, $application->reviewer ? 'oleh ' . $application->reviewer->name : null],
        ['Wawancara dijadwalkan', $application->interview_scheduled_at, null],
        ['Penawaran diberikan', $application->offer_extended_at, null],
        [$stageLabels['hired'], $application->hired_at, null],
        [$stageLabels['rejected'], $application->rejected_at, $application->rejection_reason],
    ], fn ($row) => $row[1]);
@endphp

<div class="sg-container sg-co-job sg-co-app" x-data="applicationDetail(@js([
        'status' => route('company.applications.update-status', $application->id),
        'shortlist' => route('company.applications.shortlist', $application->id),
        'reject' => route('company.applications.reject', $application->id),
        'hire' => route('company.applications.hire', $application->id),
        'rating' => route('company.applications.add-rating', $application->id),
        'notes' => route('company.applications.add-notes', $application->id),
    ]), @js($currentStage), {{ (int) ($application->rating ?? 0) }}, @js($name))">
    <x-ui.page-header :title="$name"
                      :breadcrumbs="['Dashboard' => route('company.dashboard'), 'Lamaran' => route('company.applications.index'), \Illuminate\Support\Str::limit($name, 48) => null]">
        <x-slot:actions>
            <x-ui.button icon="clipboard-check" data-sg-open="ubah-tahap">Ubah tahap</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <p class="sg-co-job__meta">
        <x-ui.status domain="job_application" :value="$currentStage" />
        <span>Melamar untuk <a href="{{ route('company.jobs.show', $job->id) }}" class="sg-co-job__link">{{ $job->title }}</a></span>
        <span>{{ $application->created_at->locale('id')->diffForHumans() }}</span>
    </p>

    <div class="sg-co-job__layout">
        <div class="sg-co-job__content">
            <section aria-labelledby="info-pelamar">
                <h2 id="info-pelamar">Informasi pelamar</h2>
                <x-ui.dl :items="[
                    'Nama lengkap' => $name,
                    'Email' => $application->user->email,
                    'Ekspektasi gaji' => $application->expected_salary ? 'Rp ' . number_format($application->expected_salary, 0, ',', '.') : null,
                    'Tersedia mulai' => $application->available_from ? $application->available_from->locale('id')->translatedFormat('j M Y') : null,
                ]" />
            </section>

            @if ($application->cover_letter)
                <section aria-labelledby="surat-lamaran">
                    <h2 id="surat-lamaran">Surat lamaran</h2>
                    <p class="sg-co-job__text">{{ $application->cover_letter }}</p>
                </section>
            @endif

            <section aria-labelledby="dokumen">
                <h2 id="dokumen">Dokumen</h2>
                @if ($application->resume_url || $application->portfolio_url)
                    <ul class="sg-co-app__docs">
                        @if ($application->resume_url)
                            <li>
                                <x-icon name="file-text" />
                                <a href="{{ $application->resume_url }}" target="_blank" rel="noopener" class="sg-co-job__link">Resume / CV<span class="sr-only"> (membuka tab baru)</span></a>
                                <x-icon name="external-link" size="sm" class="sg-co-app__ext" />
                            </li>
                        @endif
                        @if ($application->portfolio_url)
                            <li>
                                <x-icon name="briefcase" />
                                <a href="{{ $application->portfolio_url }}" target="_blank" rel="noopener" class="sg-co-job__link">Portofolio<span class="sr-only"> (membuka tab baru)</span></a>
                                <x-icon name="external-link" size="sm" class="sg-co-app__ext" />
                            </li>
                        @endif
                    </ul>
                @else
                    <p class="sg-co-job__muted">Pelamar tidak melampirkan dokumen.</p>
                @endif
            </section>

            <section aria-labelledby="catatan-internal">
                <h2 id="catatan-internal">Catatan internal</h2>
                @if ($application->notes)
                    <p class="sg-co-job__text">{{ $application->notes }}</p>
                @else
                    <p class="sg-co-job__muted">Belum ada catatan.</p>
                @endif
                <form class="sg-co-app__notes" @submit.prevent="addNotes()">
                    <x-ui.field label="Tambah atau perbarui catatan" for="catatan" help="Catatan ini hanya terlihat oleh tim Anda dan menggantikan catatan sebelumnya.">
                        <x-ui.textarea name="notes" id="catatan" rows="3" maxlength="1000" help x-model="notes" />
                    </x-ui.field>
                    <x-ui.button type="submit" variant="secondary" ::disabled="busy || !notes.trim()">Simpan catatan</x-ui.button>
                </form>
            </section>
        </div>

        <aside class="sg-co-job__rail" aria-label="Tindakan dan riwayat lamaran">
            <section aria-labelledby="tindakan">
                <h2 id="tindakan">Tindakan</h2>
                <div class="sg-co-app__actions">
                    <x-ui.button variant="secondary" block icon="user-check" @click="shortlist()" ::disabled="busy">Pindah ke {{ $stageLabels['shortlisted'] }}</x-ui.button>
                    <x-ui.button variant="secondary" block icon="circle-check" @click="hire()" ::disabled="busy">Rekrut</x-ui.button>
                    <x-ui.button variant="danger" block icon="circle-x" data-sg-open="tolak-lamaran">Tolak</x-ui.button>
                </div>
            </section>

            <section aria-labelledby="penilaian">
                <h2 id="penilaian">Penilaian</h2>
                <fieldset class="sg-co-app__rating">
                    <legend class="sr-only">Rating kandidat (tersimpan saat dipilih)</legend>
                    @foreach ($ratingLabels as $value => $label)
                        <label class="sg-check">
                            <input type="radio" name="rating" value="{{ $value }}" x-model.number="rating" @change="setRating()" :disabled="busy">
                            <span>{{ $value }} · {{ $label }}</span>
                        </label>
                    @endforeach
                </fieldset>
                <p class="sg-co-job__muted" aria-live="polite">
                    <span x-show="rating">Rating saat ini <b x-text="rating + '/5'"></b>.</span>
                    <span x-show="!rating" @if ($application->rating) x-cloak @endif>Belum dinilai.</span>
                </p>
            </section>

            <section aria-labelledby="riwayat">
                <h2 id="riwayat">Riwayat</h2>
                <ol class="sg-co-app__history">
                    @foreach ($history as [$label, $date, $detail])
                        <li>
                            <span class="sg-co-app__history-label">{{ $label }}</span>
                            <time datetime="{{ $date->toIso8601String() }}">{{ $waktu($date) }}</time>
                            @if ($detail)<span class="sg-co-app__history-detail">{{ $detail }}</span>@endif
                        </li>
                    @endforeach
                </ol>
            </section>
        </aside>
    </div>

    <x-ui.modal id="ubah-tahap" title="Ubah tahap lamaran">
        <form class="sg-co-apps__form" @submit.prevent="updateStatus()">
            <x-ui.field label="Tahap baru" for="tahap-baru">
                <x-ui.select name="status" id="tahap-baru" :options="$stageLabels" x-model="status" />
            </x-ui.field>
            <div class="sg-co-app__dialog-actions">
                <x-ui.button variant="tertiary" data-sg-close>Batal</x-ui.button>
                <x-ui.button type="submit" ::disabled="busy">Simpan</x-ui.button>
            </div>
        </form>
    </x-ui.modal>

    <x-ui.modal id="tolak-lamaran" title="Tolak lamaran">
        <form class="sg-co-apps__form" @submit.prevent="reject()">
            <x-ui.field label="Alasan penolakan (opsional)" for="alasan-tolak">
                <x-ui.textarea name="reason" id="alasan-tolak" rows="3" maxlength="500" x-model="reason" />
            </x-ui.field>
            <div class="sg-co-app__dialog-actions">
                <x-ui.button variant="tertiary" data-sg-close>Batal</x-ui.button>
                <x-ui.button type="submit" variant="danger-solid" ::disabled="busy">Tolak</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
@endsection

@push('scripts')
<script>
function applicationDetail(urls, initialStatus, initialRating, name) {
    const FLASH_KEY = 'sg_co_app_flash';

    return {
        status: initialStatus,
        rating: initialRating,
        notes: '',
        reason: '',
        busy: false,

        init() {
            try {
                const flash = sessionStorage.getItem(FLASH_KEY);
                if (flash) {
                    sessionStorage.removeItem(FLASH_KEY);
                    this.$nextTick(() => window.showNotification?.(flash, 'success'));
                }
            } catch (e) {}
        },

        async post(url, body = null) {
            this.busy = true;
            try {
                const headers = { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content, 'Accept': 'application/json' };
                if (body) headers['Content-Type'] = 'application/json';
                const response = await fetch(url, { method: 'POST', headers, body: body ? JSON.stringify(body) : null });
                const data = await response.json().catch(() => ({}));
                return response.ok && data.success;
            } catch (error) {
                console.error('Gagal mengirim permintaan:', error);
                return false;
            } finally {
                this.busy = false;
            }
        },
        reloadWith(message) {
            try { sessionStorage.setItem(FLASH_KEY, message); } catch (e) {}
            location.reload();
        },
        fail(message) { window.showNotification?.(message, 'error'); },

        async updateStatus() {
            (await this.post(urls.status, { status: this.status })) ? this.reloadWith('Tahap lamaran diperbarui.') : this.fail('Tahap lamaran gagal diperbarui.');
        },
        async shortlist() {
            const yes = await window.sgConfirm({ title: 'Pindahkan ke tahap Terpilih?', message: `Lamaran ${name} akan dipindahkan ke tahap Terpilih.`, confirmLabel: 'Pindahkan', tone: 'primary' });
            if (!yes) return;
            (await this.post(urls.shortlist)) ? this.reloadWith('Lamaran dipindahkan ke tahap Terpilih.') : this.fail('Lamaran gagal dipindahkan.');
        },
        async hire() {
            const yes = await window.sgConfirm({ title: 'Rekrut pelamar ini?', message: `Lamaran ${name} akan dipindahkan ke tahap Direkrut.`, confirmLabel: 'Rekrut', tone: 'primary' });
            if (!yes) return;
            (await this.post(urls.hire)) ? this.reloadWith('Pelamar direkrut.') : this.fail('Pelamar gagal direkrut.');
        },
        async reject() {
            (await this.post(urls.reject, { reason: this.reason })) ? this.reloadWith('Lamaran ditolak.') : this.fail('Lamaran gagal ditolak.');
        },
        async setRating() {
            if (!this.rating) return;
            (await this.post(urls.rating, { rating: this.rating })) ? window.showNotification?.('Rating disimpan.', 'success') : this.fail('Rating gagal disimpan.');
        },
        async addNotes() {
            if (!this.notes.trim()) return;
            (await this.post(urls.notes, { notes: this.notes })) ? this.reloadWith('Catatan disimpan.') : this.fail('Catatan gagal disimpan.');
        },
    };
}
</script>
@endpush
