@extends('layouts.app')

@section('title', 'Kelola Proyek')

@section('content')
@php
    // phase 29 paket D: permukaan kerja kelola proyek. semua form, id, name dan endpoint fetch tetap
    // (status: POST json; milestone: GET/PUT/DELETE json; laporan: approve/reject POST, action diisi js).
    $tanggal = fn ($date) => $date ? \Illuminate\Support\Carbon::parse($date)->locale('id')->translatedFormat('j M Y') : null;
    $waktu = fn ($date) => $date ? \Illuminate\Support\Carbon::parse($date)->locale('id')->translatedFormat('j M Y, H.i') : null;
    $pendingReports = $project->reports->where('status', 'pending');
    // riwayat: revision_needed = nilai yang ditulis rejectReport (enum); revision_required dipertahankan dari filter lama
    $reviewedReports = $project->reports->whereIn('status', ['approved', 'revision_needed', 'revision_required']);
    $vocab = \App\Support\StatusVocabulary::class;
    // nilai opsi tetap persis (kontrak form, keputusan user 2026-11-01); planning/review tidak ada di enum proyek
    $projectStatusOptions = [
        'planning' => 'Perencanaan',
        'active' => $vocab::label('project', 'active'),
        'review' => 'Tinjauan',
        'completed' => $vocab::label('project', 'completed'),
        'cancelled' => $vocab::label('project', 'cancelled'),
    ];
@endphp

<div class="sg-container sg-inst-page">
    <x-ui.page-header title="Kelola proyek" :description="$project->title"
                      :back="route('institution.projects.show', $project->id)" back-label="Kembali ke detail proyek">
        <x-slot:actions>
            <x-ui.button variant="secondary" :href="route('institution.projects.show', $project->id)" icon="eye">Lihat detail lengkap</x-ui.button>
            @if ($project->status == 'completed' && ! $project->rating)
                <x-ui.button :href="route('institution.reviews.create', $project->id)" icon="pencil">Berikan ulasan</x-ui.button>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.form-errors class="sg-inst-page__errors" />

    <div class="sg-inst-detail">
        <div class="sg-inst-detail__main">

            {{-- status proyek (fetch POST json) --}}
            <section class="sg-inst-manage__block" aria-labelledby="judul-status">
                <h2 id="judul-status">Status proyek</h2>
                <div class="sg-inst-manage__status">
                    <div class="sg-field">
                        <label class="sg-label" for="project-status">Ubah status</label>
                        <select id="project-status" class="sg-select">
                            @foreach ($projectStatusOptions as $value => $label)
                                <option value="{{ $value }}" {{ $project->status == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-ui.button variant="secondary" onclick="updateProjectStatus()">Perbarui status</x-ui.button>
                </div>
                <p class="sg-inst-detail__note">Status saat ini: <x-ui.status domain="project" :value="$project->status" /></p>
            </section>

            {{-- milestone --}}
            <section class="sg-inst-manage__block" aria-labelledby="judul-milestone">
                <div class="sg-inst-manage__head">
                    <h2 id="judul-milestone">Tahap proyek</h2>
                    <x-ui.button variant="secondary" icon="plus" onclick="showAddMilestoneModal()">Tambah milestone</x-ui.button>
                </div>

                @if ($project->milestones->count() > 0)
                    <p class="sg-inst-manage__meter">@include('institution.projects.components.stage-meter', ['milestones' => $project->milestones])</p>
                    <x-ui.list label="Milestone proyek">
                        @foreach ($project->milestones as $milestone)
                            @php $late = $milestone->target_date && $milestone->target_date < now() && $milestone->status != 'completed'; @endphp
                            <x-ui.list-item :title="$milestone->title"
                                            :meta="$milestone->target_date ? 'Target '.$tanggal($milestone->target_date) : 'Tanpa target'">
                                @if (filled($milestone->description))
                                    <p class="sg-inst-manage__text">{{ $milestone->description }}</p>
                                @endif
                                @if ($late || $milestone->requires_report)
                                    <p class="sg-inst-manage__flags">
                                        @if ($late)<span class="sg-inst-flag sg-inst-flag--warn"><x-icon name="triangle-alert" size="sm" />Lewat target</span>@endif
                                        @if ($milestone->requires_report)<span class="sg-inst-flag">Wajib laporan</span>@endif
                                    </p>
                                @endif
                                <p class="sg-inst-manage__actions">
                                    <x-ui.button variant="tertiary" icon="pencil" onclick="editMilestone({{ $milestone->id }})">Edit<span class="sr-only"> {{ $milestone->title }}</span></x-ui.button>
                                    <x-ui.button variant="tertiary" icon="trash-2" onclick="deleteMilestone({{ $milestone->id }})">Hapus<span class="sr-only"> {{ $milestone->title }}</span></x-ui.button>
                                </p>
                                <x-slot:end><x-ui.status domain="milestone" :value="$milestone->status" /></x-slot:end>
                            </x-ui.list-item>
                        @endforeach
                    </x-ui.list>
                @else
                    <p class="sg-inst-detail__note">Belum ada milestone. Tambahkan milestone pertama!</p>
                @endif
            </section>

            {{-- laporan menunggu tinjauan --}}
            <section class="sg-inst-manage__block" aria-labelledby="judul-laporan">
                <h2 id="judul-laporan">Tinjau laporan</h2>
                @if ($pendingReports->count() > 0)
                    <x-ui.list label="Laporan yang menunggu tinjauan">
                        @foreach ($pendingReports as $report)
                            <x-ui.list-item :title="$report->title" :meta="collect([$report->type_label, 'Dikirim '.$waktu($report->created_at)])->filter()->implode(' · ')">
                                @php $ringkasan = \Illuminate\Support\Str::limit($report->summary ?? $report->activities ?? '', 200); @endphp
                                @if (filled($ringkasan))
                                    <p class="sg-inst-manage__text">{{ $ringkasan }}</p>
                                @endif
                                @if ($report->document_path)
                                    <p class="sg-inst-manage__text">
                                        <a href="{{ document_url($report->document_path) }}" target="_blank" download class="sg-inst-link">Unduh dokumen<span class="sr-only"> {{ $report->title }}</span></a>
                                    </p>
                                @endif
                                <p class="sg-inst-manage__actions">
                                    <x-ui.button variant="secondary" icon="check" onclick="showApproveModal({{ $report->id }})">Setujui<span class="sr-only"> {{ $report->title }}</span></x-ui.button>
                                    <x-ui.button variant="secondary" icon="message-square-text" onclick="showRejectModal({{ $report->id }})">Minta revisi<span class="sr-only"> {{ $report->title }}</span></x-ui.button>
                                </p>
                                <x-slot:end><x-ui.status domain="report" :value="$report->status" /></x-slot:end>
                            </x-ui.list-item>
                        @endforeach
                    </x-ui.list>
                @else
                    <p class="sg-inst-detail__note">Tidak ada laporan yang perlu ditinjau.</p>
                @endif

                @if ($reviewedReports->count() > 0)
                    <h3 class="sg-inst-manage__sub">Riwayat tinjauan</h3>
                    <x-ui.list label="Laporan yang sudah ditinjau">
                        @foreach ($reviewedReports as $report)
                            <x-ui.list-item :title="$report->title" :meta="$report->type_label">
                                @if ($report->institution_feedback)
                                    <p class="sg-inst-manage__text">Masukan: {{ $report->institution_feedback }}</p>
                                @endif
                                <x-slot:end><x-ui.status domain="report" :value="$report->status" /></x-slot:end>
                            </x-ui.list-item>
                        @endforeach
                    </x-ui.list>
                @endif
            </section>
        </div>

        <aside class="sg-inst-detail__rail" aria-label="Informasi proyek">
            <x-ui.dl>
                <div>
                    <dt>Mahasiswa</dt>
                    <dd>{{ $project->student->display_name }}</dd>
                </div>
                <div>
                    <dt>Status</dt>
                    <dd><x-ui.status domain="project" :value="$project->status" /></dd>
                </div>
                <div>
                    <dt>Tahap</dt>
                    <dd>@include('institution.projects.components.stage-meter', ['milestones' => $project->milestones])</dd>
                </div>
                <div>
                    <dt>Laporan</dt>
                    <dd>{{ $project->reports->count() }} laporan · {{ $pendingReports->count() }} menunggu tinjauan</dd>
                </div>
            </x-ui.dl>

            <h2 class="sg-inst-detail__rail-h">Jadwal</h2>
            <x-ui.dl :items="[
                'Mulai' => $tanggal($project->start_date),
                'Rencana selesai' => $tanggal($project->end_date),
            ]" />
        </aside>
    </div>
</div>

{{-- dialog tambah milestone (POST biasa) --}}
<x-ui.modal id="add-milestone-modal" title="Tambah milestone">
    <form method="POST" action="{{ route('institution.projects.add-milestone', $project->id) }}" class="sg-inst-form sg-inst-dialog-form">
        @csrf
        <label class="sg-field">
            <span class="sg-label" data-required>Judul</span>
            <input type="text" name="title" required class="sg-input">
        </label>
        <label class="sg-field">
            <span class="sg-label">Deskripsi</span>
            <textarea name="description" rows="3" class="sg-textarea"></textarea>
        </label>
        <label class="sg-field">
            <span class="sg-label" data-required>Target tanggal</span>
            <input type="date" name="target_date" required class="sg-input">
        </label>
        {{-- phase 17: tenggat laporan --}}
        <div>
            <input type="hidden" name="requires_report" value="0">
            <label class="sg-check">
                <input type="checkbox" name="requires_report" value="1">
                <span><strong>Wajib laporan</strong>: target tanggal milestone ini menjadi tenggat laporan yang dipantau di dashboard.</span>
            </label>
        </div>
        <div class="sg-dialog__foot">
            <x-ui.button variant="secondary" data-sg-close>Batal</x-ui.button>
            <x-ui.button type="submit">Simpan</x-ui.button>
        </div>
    </form>
</x-ui.modal>

{{-- dialog setujui laporan (action diisi showApproveModal) --}}
<x-ui.modal id="approve-modal" title="Setujui laporan">
    <form id="approve-form" method="POST" class="sg-inst-form sg-inst-dialog-form">
        @csrf
        <label class="sg-field">
            <span class="sg-label">Masukan (opsional)</span>
            <textarea name="feedback" rows="4" class="sg-textarea" placeholder="Berikan masukan positif untuk mahasiswa..."></textarea>
        </label>
        <div class="sg-dialog__foot">
            <x-ui.button variant="secondary" data-sg-close>Batal</x-ui.button>
            <x-ui.button type="submit" icon="check">Setujui laporan</x-ui.button>
        </div>
    </form>
</x-ui.modal>

{{-- dialog minta revisi (action diisi showRejectModal) --}}
<x-ui.modal id="reject-modal" title="Minta revisi laporan">
    <form id="reject-form" method="POST" class="sg-inst-form sg-inst-dialog-form">
        @csrf
        <label class="sg-field">
            <span class="sg-label" data-required>Masukan</span>
            <textarea name="feedback" rows="4" required class="sg-textarea" placeholder="Jelaskan apa yang perlu diperbaiki..."></textarea>
        </label>
        <div class="sg-dialog__foot">
            <x-ui.button variant="secondary" data-sg-close>Batal</x-ui.button>
            <x-ui.button type="submit" icon="message-square-text">Minta revisi</x-ui.button>
        </div>
    </form>
</x-ui.modal>

{{-- dialog edit milestone (fetch PUT json) --}}
<x-ui.modal id="edit-milestone-modal" title="Edit milestone">
    <form id="edit-milestone-form" onsubmit="submitEditMilestone(event)" class="sg-inst-form sg-inst-dialog-form">
        <input type="hidden" id="edit-milestone-id">
        <x-ui.field label="Judul" for="edit-milestone-title" required>
            <input type="text" id="edit-milestone-title" name="title" required class="sg-input">
        </x-ui.field>
        <x-ui.field label="Deskripsi" for="edit-milestone-description">
            <textarea id="edit-milestone-description" name="description" rows="3" class="sg-textarea"></textarea>
        </x-ui.field>
        <x-ui.field label="Target tanggal" for="edit-milestone-target-date" required>
            <input type="date" id="edit-milestone-target-date" name="target_date" required class="sg-input">
        </x-ui.field>
        <x-ui.field label="Status" for="edit-milestone-status">
            <select id="edit-milestone-status" name="status" class="sg-select">
                @foreach (['pending', 'in_progress', 'completed'] as $value)
                    <option value="{{ $value }}">{{ $vocab::label('milestone', $value) }}</option>
                @endforeach
            </select>
        </x-ui.field>
        {{-- phase 17: tenggat laporan --}}
        <label class="sg-check" for="edit-milestone-requires-report">
            <input type="checkbox" id="edit-milestone-requires-report" name="requires_report" value="1">
            <span><strong>Wajib laporan</strong>: target tanggal milestone ini menjadi tenggat laporan yang dipantau di dashboard.</span>
        </label>
        <div class="sg-dialog__foot">
            <x-ui.button variant="secondary" data-sg-close>Batal</x-ui.button>
            <x-ui.button type="submit">Simpan perubahan</x-ui.button>
        </div>
    </form>
</x-ui.modal>

<script>
// dialog native: buka/tutup lewat showModal()/close()
function openModal(modalId) {
    const dialog = document.getElementById(modalId);
    if (dialog && !dialog.open) dialog.showModal();
}

function closeModal(modalId) {
    document.getElementById(modalId)?.close();
}

function notify(message, type) {
    if (typeof window.showNotification === 'function') window.showNotification(message, type);
}

function showAddMilestoneModal() {
    openModal('add-milestone-modal');
}

function showApproveModal(reportId) {
    const form = document.getElementById('approve-form');
    form.action = `/institution/projects/{{ $project->id }}/reports/${reportId}/approve`;
    openModal('approve-modal');
}

function showRejectModal(reportId) {
    const form = document.getElementById('reject-form');
    form.action = `/institution/projects/{{ $project->id }}/reports/${reportId}/reject`;
    openModal('reject-modal');
}

// update project status
function updateProjectStatus() {
    const status = document.getElementById('project-status').value;

    fetch(`/institution/projects/{{ $project->id }}/status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            notify('Status berhasil diperbarui.', 'success');
            location.reload();
        } else {
            notify('Status tidak dapat diperbarui. Pilih status lain.', 'error');
        }
    })
    .catch(error => {
        notify('Terjadi kesalahan: ' + error, 'error');
    });
}

// edit milestone
function editMilestone(milestoneId) {
    // ambil data milestone
    fetch(`/institution/projects/{{ $project->id }}/milestones/${milestoneId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const milestone = data.milestone;

                // isi form
                document.getElementById('edit-milestone-id').value = milestoneId;
                document.getElementById('edit-milestone-title').value = milestone.title;
                document.getElementById('edit-milestone-description').value = milestone.description || '';
                // json mengirim tanggal iso lengkap; input date hanya menerima yyyy-mm-dd (disetujui user 2026-11-01)
                document.getElementById('edit-milestone-target-date').value = (milestone.target_date || '').slice(0, 10);
                document.getElementById('edit-milestone-status').value = milestone.status;
                document.getElementById('edit-milestone-requires-report').checked = !!milestone.requires_report;

                openModal('edit-milestone-modal');
            }
        })
        .catch(error => {
            notify('Terjadi kesalahan: ' + error, 'error');
        });
}

// submit edit milestone
function submitEditMilestone(event) {
    event.preventDefault();

    const milestoneId = document.getElementById('edit-milestone-id').value;
    const formData = {
        title: document.getElementById('edit-milestone-title').value,
        description: document.getElementById('edit-milestone-description').value,
        target_date: document.getElementById('edit-milestone-target-date').value,
        status: document.getElementById('edit-milestone-status').value,
        requires_report: document.getElementById('edit-milestone-requires-report').checked
    };

    fetch(`/institution/projects/{{ $project->id }}/milestones/${milestoneId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            notify('Terjadi kesalahan: ' + (data.message || 'Milestone tidak dapat disimpan.'), 'error');
        }
    })
    .catch(error => {
        notify('Terjadi kesalahan: ' + error, 'error');
    });
}

// delete milestone (dialog konfirmasi bersama)
async function deleteMilestone(milestoneId) {
    const ok = await window.sgConfirm({
        title: 'Hapus milestone?',
        message: 'Yakin ingin menghapus milestone ini?',
        confirmLabel: 'Hapus',
    });
    if (!ok) return;

    fetch(`/institution/projects/{{ $project->id }}/milestones/${milestoneId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            notify('Terjadi kesalahan: ' + (data.message || 'Milestone tidak dapat dihapus.'), 'error');
        }
    })
    .catch(error => {
        notify('Terjadi kesalahan: ' + error, 'error');
    });
}
</script>
@endsection
