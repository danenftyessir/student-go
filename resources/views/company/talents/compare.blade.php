@extends('layouts.app')

@section('title', 'Bandingkan Talenta')

@section('content')
@php
    // phase 22: perilaku tetap (2-3 id dari ?ids=, kriteria sama); nama dari display_name (J1), tanpa teks inggris.
    $fmt = fn ($n) => number_format((float) $n, 0, ',', '.');
@endphp

<div class="sg-container sg-co-compare">
    <x-ui.page-header title="Bandingkan Talenta" :description="'Membandingkan ' . $talents->count() . ' talenta berdampingan.'"
                      :back="route('company.talents.index')" back-label="Kembali ke Talenta" />

    <x-ui.table caption="Perbandingan talenta" :stack="false" class="sg-co-compare__table">
        <x-slot:head>
            <th scope="col">Kriteria</th>
            @foreach ($talents as $talent)
                <th scope="col">
                    <span class="sg-co-compare__person">
                        <x-ui.avatar :name="$talent->display_name" decorative />
                        <span>{{ $talent->display_name }}</span>
                    </span>
                </th>
            @endforeach
        </x-slot:head>

        <tr>
            <th scope="row">Universitas</th>
            @foreach ($talents as $talent)
                <td>{{ $talent->profile?->university?->name ?? '—' }}</td>
            @endforeach
        </tr>
        <tr>
            <th scope="row">Status verifikasi</th>
            @foreach ($talents as $talent)
                <td>
                    @if ($talent->email_verified_at)
                        <span class="sg-co-compare__ok"><x-icon name="badge-check" size="sm" />Email terverifikasi</span>
                    @else
                        Belum terverifikasi
                    @endif
                </td>
            @endforeach
        </tr>
        <tr>
            <th scope="row">Jurusan</th>
            @foreach ($talents as $talent)
                <td>{{ $talent->profile?->major ?: '—' }}</td>
            @endforeach
        </tr>
        <tr>
            <th scope="row">Jumlah proyek</th>
            @foreach ($talents as $talent)
                <td class="sg-num">{{ $fmt($talent->profile?->projects?->count() ?? 0) }}</td>
            @endforeach
        </tr>
        <tr>
            <th scope="row">Bio</th>
            @foreach ($talents as $talent)
                <td class="sg-co-compare__bio">{{ $talent->profile?->bio ?: 'Belum ada bio.' }}</td>
            @endforeach
        </tr>
        <tr>
            <th scope="row">Aksi</th>
            @foreach ($talents as $talent)
                <td>
                    <span class="sg-co-compare__actions">
                        <x-ui.button variant="secondary" :href="route('profile.public', $talent->username)">
                            Lihat profil<span class="sr-only"> {{ $talent->display_name }}</span>
                        </x-ui.button>
                        <x-ui.button variant="tertiary" icon="bookmark" onclick="saveTalent({{ $talent->id }})">
                            Simpan<span class="sr-only"> {{ $talent->display_name }}</span>
                        </x-ui.button>
                    </span>
                </td>
            @endforeach
        </tr>
    </x-ui.table>
</div>
@endsection

@push('scripts')
<script>
async function saveTalent(talentId) {
    try {
        const response = await fetch(`/company/talents/${talentId}/toggle-save`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        const data = await response.json();

        if (data.success) {
            // pesan server berbahasa inggris; tampilkan versi indonesia (D-02)
            window.showNotification(data.action === 'saved' ? 'Talenta disimpan ke Talenta Tersimpan' : 'Talenta dihapus dari Talenta Tersimpan', data.action === 'saved' ? 'success' : 'info');
        }
    } catch (error) {
        console.error('Error toggling save:', error);
        window.showNotification('Terjadi kesalahan', 'error');
    }
}
</script>
@endpush
