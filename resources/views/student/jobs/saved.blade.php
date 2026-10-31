{{-- resources/views/student/jobs/saved.blade.php --}}
@extends('layouts.app')

@section('title', 'Lowongan Tersimpan')

{{-- phase 15: baris yang sama dengan halaman lowongan (components/job-row). parameter tetap: folder, page.
     hapus = POST student/jobs/{id}/toggle-save; ubah folder/catatan/pengingat = PUT student/jobs/{id}/update-saved.
     jumlah dari controller ($totalSaved, $expiringCount). --}}

@php
    $currentFolder = request('folder');
    $savedRows = $savedJobs->getCollection()->filter(fn ($saved) => $saved->jobPosting);
@endphp

@section('content')
<div class="sg-container sg-browse" x-data="savedJobsPage()">
    <x-ui.page-header
        title="Lowongan Tersimpan"
        description="Lowongan yang Anda simpan untuk dibaca atau dilamar nanti."
        :back="route('student.jobs.index')"
        back-label="Lowongan" />

    @if ($totalSaved > 0)
        <x-ui.summary label="Ringkasan lowongan tersimpan" :items="[
            ['value' => $totalSaved, 'label' => 'lowongan disimpan'],
            ['value' => $expiringCount > 0 ? $expiringCount : null, 'label' => 'berakhir dalam 7 hari'],
        ]" />

        @if (count($folders) > 0)
            {{-- folder sebagai tautan (?folder=…); folder aktif ditandai aria-current, centang dan huruf tebal --}}
            <nav class="sg-browse__active" aria-label="Folder">
                <ul class="sg-browse__chips">
                    @foreach (collect([null])->merge($folders) as $folder)
                        @php $isCurrent = $folder === null ? ! $currentFolder : $currentFolder === $folder; @endphp
                        <li>
                            <a href="{{ $folder === null ? route('student.jobs.saved') : route('student.jobs.saved', ['folder' => $folder]) }}"
                               @class(['sg-browse-chip', 'font-semibold' => $isCurrent]) @if ($isCurrent) aria-current="page" @endif>
                                @if ($isCurrent)<x-icon name="check" size="sm" />@endif{{ $folder ?? 'Semua' }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>
        @endif
    @endif

    <div class="sg-browse__main mt-4">
        @if ($totalSaved === 0)
            <x-ui.empty-state
                title="Belum ada lowongan tersimpan"
                description="Pilih “Simpan” pada lowongan yang menarik agar mudah ditemukan lagi di sini."
                illustration="no-saved-projects"
                class="sg-browse__empty">
                <x-slot:action>
                    <x-ui.button :href="route('student.jobs.index')">Cari Lowongan</x-ui.button>
                </x-slot:action>
            </x-ui.empty-state>
        @elseif ($savedRows->isEmpty())
            {{-- folder tanpa lowongan, atau nomor halaman di luar jangkauan --}}
            <x-ui.empty-state
                title="Tidak ada lowongan di sini"
                description="{{ $currentFolder ? 'Folder “' . $currentFolder . '” tidak berisi lowongan pada halaman ini.' : 'Halaman ini tidak berisi lowongan tersimpan.' }}"
                class="sg-browse__empty">
                <x-slot:action>
                    <x-ui.button variant="secondary" :href="route('student.jobs.saved')">Lihat semua tersimpan</x-ui.button>
                </x-slot:action>
            </x-ui.empty-state>
        @else
            <x-ui.table caption="Lowongan tersimpan, halaman {{ $savedJobs->currentPage() }}" class="sg-browse-table">
                <x-slot:head>
                    <th scope="col">Posisi</th>
                    <th scope="col">Perusahaan</th>
                    <th scope="col">Lokasi</th>
                    <th scope="col" class="min-w-40">SDG</th>
                    <th scope="col">Tenggat</th>
                </x-slot:head>
                @foreach ($savedRows as $saved)
                    @include('student.jobs.components.job-row', ['job' => $saved->jobPosting, 'saved' => $saved])
                @endforeach
            </x-ui.table>

            <x-ui.pagination :paginator="$savedJobs->withQueryString()" label="Halaman lowongan tersimpan" class="sg-browse__pager" />
        @endif
    </div>

    {{-- ubah folder, catatan dan pengingat (field sama seperti sebelumnya: folder, notes, reminder_at) --}}
    <x-ui.modal id="saved-edit" title="Ubah catatan lowongan">
        <form id="saved-edit-form" class="grid gap-4" @submit.prevent="saveEdit()">
            <p class="m-0" x-text="editData.title"></p>
            <x-ui.field label="Folder" for="saved-folder" help="Contoh: Prioritas, Magang">
                <input type="text" id="saved-folder" class="sg-input" x-model="editData.folder" maxlength="100" aria-describedby="saved-folder-help">
            </x-ui.field>
            <x-ui.field label="Catatan" for="saved-notes">
                <textarea id="saved-notes" class="sg-textarea" rows="3" x-model="editData.notes"></textarea>
            </x-ui.field>
            <x-ui.field label="Pengingat" for="saved-reminder">
                <input type="datetime-local" id="saved-reminder" class="sg-input" x-model="editData.reminder_at">
            </x-ui.field>
            <p class="sg-error" x-show="error" x-cloak role="alert" x-text="error"></p>
        </form>
        <x-slot:footer>
            <button type="button" class="sg-btn sg-btn--secondary" data-sg-close>Batal</button>
            <button type="submit" form="saved-edit-form" class="sg-btn sg-btn--primary" :disabled="saving">Simpan</button>
        </x-slot:footer>
    </x-ui.modal>
</div>
@endsection

@push('scripts')
<script>
function savedJobsPage() {
    return {
        editData: { job_id: null, title: '', folder: '', notes: '', reminder_at: '' },
        saving: false,
        error: '',

        openEdit(data) {
            this.editData = { ...data };
            this.error = '';
            this.$dispatch('sg-open-modal', 'saved-edit');
        },

        async saveEdit() {
            this.saving = true;
            this.error = '';
            try {
                const { title, ...payload } = this.editData;
                const res = await fetch(`{{ url('student/jobs') }}/${payload.job_id}/update-saved`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify(payload)
                });
                if (!res.ok) throw new Error(res.status);
                window.location.reload();
            } catch (e) {
                this.error = 'Perubahan gagal disimpan. Coba lagi.';
            } finally {
                this.saving = false;
            }
        },

        async removeSaved(jobId, title) {
            const ok = await window.sgConfirm({
                title: 'Hapus dari tersimpan?',
                message: `“${title}” akan dihapus dari lowongan tersimpan. Lowongannya sendiri tidak terhapus.`,
                confirmLabel: 'Hapus'
            });
            if (!ok) return;
            try {
                const res = await fetch(`{{ url('student/jobs') }}/${jobId}/toggle-save`, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                if (!res.ok) throw new Error(res.status);
                window.location.reload();
            } catch (e) {
                window.showNotification?.('Lowongan gagal dihapus dari tersimpan. Coba lagi.', 'error');
            }
        }
    }
}
</script>
@endpush
