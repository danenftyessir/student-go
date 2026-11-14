@extends('layouts.app')

@section('title', 'Bandingkan Talenta')

@section('content')
@php
    // phase 22: perilaku tetap (2-3 id dari ?ids=, kriteria sama); nama dari display_name (J1), tanpa teks inggris.
    $fmt = fn ($n) => number_format((float) $n, 0, ',', '.');
@endphp

<div class="sg-container sg-co-compare" x-data="sgTalentCompare(@js($savedIds))">
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
                <td class="tabular-nums">{{ $fmt($talent->profile?->projects?->count() ?? 0) }}</td>
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
                        {{-- pola sama dengan daftar talenta: aria-pressed + teks simpan/tersimpan mengikuti status --}}
                        <button type="button" class="sg-btn sg-btn--secondary sg-co-talents__save"
                                :aria-pressed="isSaved({{ $talent->id }}).toString()"
                                aria-pressed="{{ in_array($talent->id, $savedIds, true) ? 'true' : 'false' }}"
                                :disabled="busy === {{ $talent->id }}"
                                @click="toggleSave({{ $talent->id }}, @js($talent->display_name))">
                            <x-icon name="bookmark" size="sm" />
                            <span x-text="isSaved({{ $talent->id }}) ? 'Tersimpan' : 'Simpan'">{{ in_array($talent->id, $savedIds, true) ? 'Tersimpan' : 'Simpan' }}</span>
                            <span class="sr-only"> {{ $talent->display_name }}</span>
                        </button>
                    </span>
                </td>
            @endforeach
        </tr>
    </x-ui.table>
</div>
@endsection

@push('scripts')
<script>
// simpan/tersimpan di halaman bandingkan: status awal dari server, berganti mengikuti respons toggle-save
function sgTalentCompare(saved) {
    return {
        saved,
        busy: null,
        isSaved(id) {
            return this.saved.includes(id);
        },
        async toggleSave(id, name) {
            if (this.busy) return;
            this.busy = id;
            try {
                const response = await fetch(@js(route('company.talents.toggle-save', '__ID__')).replace('__ID__', id), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });
                const data = await response.json();
                if (!response.ok || !data.success) throw new Error('gagal');

                if (data.action === 'saved') {
                    if (!this.isSaved(id)) this.saved.push(id);
                    window.showNotification(name + ' disimpan ke Talenta Tersimpan', 'success');
                } else {
                    this.saved = this.saved.filter((savedId) => savedId !== id);
                    window.showNotification(name + ' dihapus dari Talenta Tersimpan', 'info');
                }
            } catch (error) {
                window.showNotification('Gagal menyimpan talenta. Coba lagi.', 'error');
            } finally {
                this.busy = null;
            }
        },
    };
}
</script>
@endpush
