{{-- resources/views/student/jobs/alerts.blade.php --}}
@extends('layouts.app')

@section('title', 'Notifikasi Lowongan')

{{-- phase 15: halaman pengaturan sederhana. endpoint tetap: POST alerts/store (buat), POST alerts/{id}/toggle (aktif),
     DELETE alerts/{id} (hapus). tidak ada endpoint ubah, jadi tidak ada tombol ubah. job_alerts tidak menyimpan SDG,
     jadi SDG tidak ditampilkan. field payload sama seperti sebelumnya: name, keywords, job_types, locations, frequency. --}}

@php
    $frequencies = ['instant' => 'Langsung', 'daily' => 'Harian', 'weekly' => 'Mingguan'];
    $typeLabels = ['full_time' => 'Penuh waktu', 'part_time' => 'Paruh waktu', 'contract' => 'Kontrak', 'internship' => 'Magang', 'magang' => 'Magang', 'freelance' => 'Lepas'];
    $rupiah = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
@endphp

@section('content')
<div class="sg-container sg-browse" x-data="alertsPage()">
    <x-ui.page-header
        title="Notifikasi Lowongan"
        description="Dapatkan kabar saat lowongan baru cocok dengan kata kunci, tipe dan lokasi pilihan Anda."
        :back="route('student.jobs.index')"
        back-label="Lowongan">
        <x-slot:actions>
            <x-ui.button icon="plus" data-sg-open="alert-create">Buat notifikasi</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="max-w-3xl">
        @if ($alerts->isEmpty())
            <x-ui.empty-state
                title="Belum ada notifikasi lowongan"
                description="Buat notifikasi dengan kata kunci, tipe pekerjaan dan lokasi. Anda akan dikabari saat lowongan baru yang cocok dibuka."
                illustration="no-notifications"
                class="sg-browse__empty">
                <x-slot:action>
                    <x-ui.button data-sg-open="alert-create">Buat notifikasi pertama</x-ui.button>
                </x-slot:action>
            </x-ui.empty-state>
        @else
            <h2 class="sr-only">Daftar notifikasi</h2>
            <ul class="sg-list">
                @foreach ($alerts as $alert)
                    @php
                        $types = collect((array) $alert->job_types)->filter()->map(fn ($t) => $typeLabels[strtolower($t)] ?? $t)->unique()->values();
                        $locations = collect((array) $alert->locations)->filter()->values();
                        $skills = collect((array) $alert->skills)->filter()->values();
                        $salary = $alert->salary_min && $alert->salary_max ? $rupiah($alert->salary_min) . '–' . $rupiah($alert->salary_max)
                            : ($alert->salary_min ? 'mulai ' . $rupiah($alert->salary_min) : ($alert->salary_max ? 'hingga ' . $rupiah($alert->salary_max) : null));
                    @endphp
                    <li data-alert-id="{{ $alert->id }}">
                        <div class="sg-list__row">
                            <div class="sg-list__main">
                                <h3 class="sg-list__title break-words">{{ $alert->name }}</h3>
                                <dl class="mt-2 grid grid-cols-[auto_minmax(0,1fr)] gap-x-4 gap-y-1 text-sm">
                                    <dt class="text-[var(--sg-text-tertiary)]">Kata kunci</dt>
                                    <dd class="m-0 break-words">{{ $alert->keywords ?: 'Semua kata kunci' }}</dd>
                                    <dt class="text-[var(--sg-text-tertiary)]">Tipe</dt>
                                    <dd class="m-0">{{ $types->isNotEmpty() ? $types->implode(', ') : 'Semua tipe' }}</dd>
                                    <dt class="text-[var(--sg-text-tertiary)]">Lokasi</dt>
                                    <dd class="m-0 break-words">{{ $locations->isNotEmpty() ? $locations->implode(', ') : 'Semua lokasi' }}</dd>
                                    @if ($salary)
                                        <dt class="text-[var(--sg-text-tertiary)]">Gaji</dt>
                                        <dd class="m-0">{{ $salary }}</dd>
                                    @endif
                                    @if ($skills->isNotEmpty())
                                        <dt class="text-[var(--sg-text-tertiary)]">Keahlian</dt>
                                        <dd class="m-0 break-words">{{ $skills->implode(', ') }}</dd>
                                    @endif
                                    <dt class="text-[var(--sg-text-tertiary)]">Frekuensi</dt>
                                    <dd class="m-0">
                                        {{ $frequencies[$alert->frequency] ?? $alert->frequency }}
                                        @if ($alert->last_sent_at)
                                            <span class="text-[var(--sg-text-tertiary)]">· terakhir dikirim {{ $alert->last_sent_at->locale('id')->diffForHumans() }}</span>
                                        @endif
                                    </dd>
                                </dl>
                            </div>
                            <div class="sg-list__end">
                                {{-- aktif/nonaktif: POST alerts/{id}/toggle; status dibaca ulang dari respons --}}
                                <label class="sg-check py-1" for="alert-active-{{ $alert->id }}">
                                    <input type="checkbox" role="switch" id="alert-active-{{ $alert->id }}" @checked($alert->is_active)
                                           @change="toggleAlert({{ $alert->id }}, $event.target)">
                                    <span>Aktif<span class="sr-only">: {{ $alert->name }}</span></span>
                                </label>
                                <button type="button" class="sg-btn sg-btn--tertiary" @click="deleteAlert({{ $alert->id }}, @js($alert->name))">
                                    <x-icon name="trash-2" size="sm" />Hapus<span class="sr-only"> notifikasi: {{ $alert->name }}</span>
                                </button>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <x-ui.modal id="alert-create" title="Buat notifikasi lowongan">
        <form id="alert-create-form" class="grid gap-4" @submit.prevent="createAlert()" novalidate>
            <x-ui.field label="Nama notifikasi" for="alert-name" required :error="null">
                <input type="text" id="alert-name" class="sg-input" x-model="newAlert.name" maxlength="100" required
                       placeholder="Contoh: Magang pengembang web di Bandung"
                       :aria-invalid="errors.name ? 'true' : null" aria-describedby="alert-name-error">
                <p class="sg-error" id="alert-name-error" x-show="errors.name" x-cloak x-text="errors.name"></p>
            </x-ui.field>

            <x-ui.field label="Kata kunci" for="alert-keywords" help="Pisahkan dengan koma, misalnya: frontend, laravel">
                <input type="text" id="alert-keywords" class="sg-input" x-model="newAlert.keywords" maxlength="255" aria-describedby="alert-keywords-help">
            </x-ui.field>

            <fieldset class="m-0 min-w-0 border-0 p-0">
                <legend class="sg-label mb-2">Tipe pekerjaan</legend>
                <div class="flex flex-wrap gap-x-4">
                    @foreach (['full_time' => 'Penuh waktu', 'part_time' => 'Paruh waktu', 'contract' => 'Kontrak', 'internship' => 'Magang', 'freelance' => 'Lepas'] as $value => $label)
                        <label class="sg-check py-1" for="alert-type-{{ $value }}">
                            <input type="checkbox" id="alert-type-{{ $value }}" value="{{ $value }}" x-model="newAlert.job_types">
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <x-ui.field label="Lokasi" for="alert-location" help="Tekan Enter atau Tambah untuk setiap lokasi.">
                <div class="flex gap-2">
                    <input type="text" id="alert-location" class="sg-input" x-model="locationInput" @keydown.enter.prevent="addLocation()"
                           aria-describedby="alert-location-help">
                    <button type="button" class="sg-btn sg-btn--secondary" @click="addLocation()">Tambah</button>
                </div>
                <ul class="m-0 mt-2 flex list-none flex-wrap gap-2 p-0" x-show="newAlert.locations.length" x-cloak aria-label="Lokasi dipilih">
                    <template x-for="(loc, idx) in newAlert.locations" :key="loc + idx">
                        <li>
                            <button type="button" class="sg-browse-chip" @click="newAlert.locations.splice(idx, 1)">
                                <span x-text="loc"></span><x-icon name="x" size="sm" /><span class="sr-only"> (hapus lokasi)</span>
                            </button>
                        </li>
                    </template>
                </ul>
            </x-ui.field>

            <x-ui.field label="Frekuensi" for="alert-frequency" required>
                <select id="alert-frequency" class="sg-select" x-model="newAlert.frequency" required>
                    @foreach ($frequencies as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </x-ui.field>

            <p class="sg-error" x-show="errors.general" x-cloak role="alert" x-text="errors.general"></p>
        </form>
        <x-slot:footer>
            <button type="button" class="sg-btn sg-btn--secondary" data-sg-close>Batal</button>
            <button type="submit" form="alert-create-form" class="sg-btn sg-btn--primary" :disabled="saving">Buat notifikasi</button>
        </x-slot:footer>
    </x-ui.modal>
</div>
@endsection

@push('scripts')
<script>
function alertsPage() {
    return {
        locationInput: '',
        saving: false,
        errors: {},
        newAlert: { name: '', keywords: '', job_types: [], locations: [], frequency: 'daily' },

        addLocation() {
            const loc = this.locationInput.trim();
            if (loc && !this.newAlert.locations.includes(loc)) this.newAlert.locations.push(loc);
            this.locationInput = '';
        },

        async createAlert() {
            this.errors = {};
            if (!this.newAlert.name.trim()) {
                this.errors = { name: 'Nama notifikasi wajib diisi.' };
                document.getElementById('alert-name').focus();
                return;
            }
            this.addLocation();
            this.saving = true;
            try {
                const res = await fetch('{{ route('student.jobs.alerts.store') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify(this.newAlert)
                });
                if (res.status === 422) {
                    const data = await res.json();
                    const e = data.errors || {};
                    this.errors = { name: e.name ? 'Periksa nama notifikasi (maksimal 100 karakter).' : '', general: Object.keys(e).length ? 'Periksa kembali isian yang ditandai.' : '' };
                    return;
                }
                if (!res.ok) throw new Error(res.status);
                window.location.reload();
            } catch (e) {
                this.errors = { general: 'Notifikasi gagal dibuat. Coba lagi.' };
            } finally {
                this.saving = false;
            }
        },

        // input tidak dinonaktifkan selama request (disabled membuang fokus keyboard); klik kedua saat sibuk dibatalkan
        async toggleAlert(id, input) {
            if (input.dataset.busy) {
                input.checked = !input.checked;
                return;
            }
            const wanted = input.checked;
            input.dataset.busy = '1';
            input.setAttribute('aria-busy', 'true');
            try {
                const res = await fetch(`{{ url('student/jobs/alerts') }}/${id}/toggle`, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                if (!res.ok) throw new Error(res.status);
                const data = await res.json();
                input.checked = !!data.is_active;
                window.showNotification?.(data.is_active ? 'Notifikasi diaktifkan.' : 'Notifikasi dinonaktifkan.', 'success');
            } catch (e) {
                input.checked = !wanted;
                window.showNotification?.('Status notifikasi gagal diubah. Coba lagi.', 'error');
            } finally {
                delete input.dataset.busy;
                input.removeAttribute('aria-busy');
            }
        },

        async deleteAlert(id, name) {
            const ok = await window.sgConfirm({
                title: 'Hapus notifikasi?',
                message: `Notifikasi “${name}” akan dihapus dan Anda tidak lagi menerima kabarnya.`,
                confirmLabel: 'Hapus'
            });
            if (!ok) return;
            try {
                const res = await fetch(`{{ url('student/jobs/alerts') }}/${id}`, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                if (!res.ok) throw new Error(res.status);
                window.location.reload();
            } catch (e) {
                window.showNotification?.('Notifikasi gagal dihapus. Coba lagi.', 'error');
            }
        }
    }
}
</script>
@endpush
