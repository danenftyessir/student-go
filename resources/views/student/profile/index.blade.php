{{-- resources/views/student/profile/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Profil Saya')

{{-- phase 12: profil sebagai portofolio yang bisa dibagikan. isi bersama profil publik (components/portfolio);
     di sini ditambah aksi pemilik: edit, salin tautan (toast), atur proyek yang tampil di publik.
     angka ringkasan dari query nyata ($stats, $statistics); angka nol tidak ditampilkan. --}}
@php
    $shareUrl = route('profile.public', $student->user->username);
    $summary = [
        ['value' => $stats['completed_projects'] ?: null, 'label' => 'proyek selesai'],
        ['value' => $stats['active_projects'] ?: null, 'label' => 'proyek berjalan'],
        ['value' => $statistics['sdgs_addressed'] ?: null, 'label' => 'SDG disentuh'],
    ];
@endphp

@section('content')
    @include('student.profile.components.portfolio', [
        'projects' => $completedProjects,
        'owner' => true,
        'canViewContact' => true,
    ])
@endsection

@push('scripts')
<script>
// salin tautan portofolio (url asli profile.public) lalu konfirmasi lewat toast bersama
document.addEventListener('click', function (event) {
    const button = event.target.closest('[data-sg-copy]');
    if (!button) return;
    const url = button.dataset.sgCopy;
    const done = () => window.showNotification('Tautan portofolio disalin.', 'success');
    const failed = () => window.showNotification('Tautan belum tersalin. Salin manual: ' + url, 'error', 0);

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(url).then(done).catch(() => (fallbackCopy(url) ? done() : failed()));
    } else {
        fallbackCopy(url) ? done() : failed();
    }
});

// cadangan untuk http lokal (clipboard api hanya di konteks aman)
function fallbackCopy(text) {
    const area = document.createElement('textarea');
    area.value = text;
    area.setAttribute('readonly', '');
    area.className = 'sr-only';
    document.body.appendChild(area);
    area.select();
    let ok = false;
    try { ok = document.execCommand('copy'); } catch (e) { ok = false; }
    area.remove();
    return ok;
}

// tampil/sembunyi proyek di profil publik (endpoint toggle-visibility yang sama)
document.addEventListener('alpine:init', () => {
    Alpine.data('sgPortfolioVisibility', (projectId, initial) => ({
        visible: initial,
        busy: false,
        toggle(event) {
            if (this.busy) return;
            this.busy = true;
            fetch(`/student/profile/project/${projectId}/toggle-visibility`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
            })
                .then((response) => response.json().then((data) => ({ ok: response.ok, data })))
                .then(({ ok, data }) => {
                    if (!ok || !data.success) throw new Error(data.message || 'Gagal mengubah tampilan proyek');
                    this.visible = !!data.is_visible;
                    window.showNotification(this.visible ? 'Proyek ditampilkan di profil publik.' : 'Proyek disembunyikan dari profil publik.', 'success');
                })
                .catch(() => {
                    window.showNotification('Tampilan proyek belum berubah. Coba lagi.', 'error');
                })
                .finally(() => {
                    this.busy = false;
                    if (event && event.target) event.target.checked = this.visible;
                });
        },
    }));
});
</script>
@endpush
