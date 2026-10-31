@extends('layouts.app')

@section('title', 'Talenta Tersimpan')

@section('content')
@php
    // phase 22: dua kolom. folder = saved_talents.category, catatan = saved_talents.notes (hanya dibaca).
    // aksi nyata saja: hapus dari tersimpan (toggle-save), bandingkan 2-3 (compare), ekspor (export-saved).
    $tanggal = fn ($date) => $date ? \Illuminate\Support\Carbon::parse($date)->locale('id')->translatedFormat('j M Y') : null;
    $fmt = fn ($n) => number_format((float) $n, 0, ',', '.');
    $folderKey = fn ($name) => $name === null ? '__none' : $name;
    // data untuk penyaring folder/cari di peramban (semua baris sudah dimuat server)
    $clientRows = $savedRows->map(fn ($row) => [
        'id' => $row['id'],
        'folder' => $folderKey($row['folder']),
        'text' => mb_strtolower(collect([$row['name'], $row['title'], $row['university']])->filter()->implode(' ')),
    ])->values();
@endphp

<div class="sg-container sg-co-saved" x-data="savedTalents(@js($clientRows))">
    <x-ui.page-header title="Talenta Tersimpan" description="Shortlist mahasiswa perusahaan Anda, dikelompokkan per folder.">
        @if ($totalSavedTalents > 0)
            <x-slot:actions>
                <x-ui.button variant="secondary" icon="download" :href="route('company.talents.export-saved')">Ekspor CSV</x-ui.button>
            </x-slot:actions>
        @endif
    </x-ui.page-header>

    @if ($totalSavedTalents === 0)
        <x-ui.empty-state title="Belum ada talenta tersimpan" illustration="no-saved-projects"
                          description="Simpan mahasiswa dari halaman Talenta untuk menyusun shortlist perusahaan Anda.">
            <x-slot:action>
                <x-ui.button :href="route('company.talents.index')">Cari talenta</x-ui.button>
            </x-slot:action>
        </x-ui.empty-state>
    @else
        <div class="sg-co-saved__layout">
            <nav class="sg-co-saved__folders" aria-label="Folder talenta tersimpan">
                <h2 class="sg-co-saved__folders-title">Folder</h2>
                <ul>
                    <li>
                        <button type="button" class="sg-co-saved__folder" :aria-pressed="(folder === null).toString()" aria-pressed="true" @click="folder = null">
                            <span>Semua</span><span class="sg-co-saved__folder-count" x-text="total">{{ $fmt($totalSavedTalents) }}</span>
                        </button>
                    </li>
                    @foreach ($folders as $item)
                        <li>
                            <button type="button" class="sg-co-saved__folder" :aria-pressed="(folder === @js($folderKey($item['name']))).toString()" aria-pressed="false"
                                    @click="folder = @js($folderKey($item['name']))">
                                <span>{{ $item['name'] ?? 'Tanpa folder' }}</span>
                                <span class="sg-co-saved__folder-count" x-text="countIn(@js($folderKey($item['name'])))">{{ $fmt($item['count']) }}</span>
                            </button>
                        </li>
                    @endforeach
                </ul>
                <p class="sg-co-saved__folders-note">Folder berasal dari kategori yang tercatat saat talenta disimpan.</p>
            </nav>

            <section class="sg-co-saved__main" aria-labelledby="judul-tersimpan">
                <div class="sg-co-saved__head">
                    <h2 id="judul-tersimpan" aria-live="polite"><span x-text="visibleCount()">{{ $fmt($totalSavedTalents) }}</span> talenta</h2>
                    <div class="sg-co-saved__search">
                        <label for="cari-tersimpan" class="sr-only">Cari talenta tersimpan</label>
                        <input id="cari-tersimpan" type="search" class="sg-input" x-model.debounce.150ms="query" placeholder="Cari nama, jurusan atau universitas">
                    </div>
                </div>

                <ul class="sg-list sg-co-saved__list" aria-label="Daftar talenta tersimpan">
                    @foreach ($savedRows as $row)
                        @php
                            $meta = collect([$row['title'], $row['university']])->filter()->implode(' · ');
                        @endphp
                        <li data-talent-id="{{ $row['id'] }}" x-show="isVisible({{ $row['id'] }})">
                            <div class="sg-list__row">
                                <label class="sg-co-saved__pick">
                                    <input type="checkbox" :checked="isPicked({{ $row['id'] }})"
                                           :disabled="!isPicked({{ $row['id'] }}) && picked.length >= 3"
                                           @change="togglePick({{ $row['id'] }}, @js($row['name']))">
                                    <span class="sr-only">Pilih {{ $row['name'] }} untuk dibandingkan</span>
                                </label>
                                <x-ui.avatar :name="$row['name']" decorative />
                                <div class="sg-list__main">
                                    <p class="sg-list__title">
                                        @if ($row['username'])
                                            <a href="{{ route('profile.public', $row['username']) }}" class="hover:underline">{{ $row['name'] }}</a>
                                        @else
                                            {{ $row['name'] }}
                                        @endif
                                    </p>
                                    @if ($meta !== '')<p class="sg-list__meta">{{ $meta }}</p>@endif
                                    <p class="sg-co-talents__facts">
                                        @if ($row['location'])
                                            <span><x-icon name="map-pin" size="sm" /><span class="sr-only">Lokasi kampus: </span>{{ $row['location'] }}</span>
                                        @endif
                                        <span><x-icon name="folder-open" size="sm" /><span class="sr-only">Folder: </span>{{ $row['folder'] ?? 'Tanpa folder' }}</span>
                                        @if ($tanggal($row['saved_at']))
                                            <span>Disimpan {{ $tanggal($row['saved_at']) }}</span>
                                        @endif
                                    </p>
                                    @if ($row['notes'])
                                        <div class="sg-co-saved__note">
                                            <p class="sg-co-saved__note-label">Catatan</p>
                                            <p>{{ $row['notes'] }}</p>
                                        </div>
                                    @endif
                                </div>
                                <div class="sg-list__end">
                                    <button type="button" class="sg-btn sg-btn--danger" :disabled="busy === {{ $row['id'] }}"
                                            @click="remove({{ $row['id'] }}, @js($row['name']))">
                                        <x-icon name="trash-2" size="sm" />Hapus<span class="sr-only"> {{ $row['name'] }} dari tersimpan</span>
                                    </button>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>

                <div x-show="visibleCount() === 0" x-cloak>
                    <x-ui.empty-state title="Tidak ada talenta yang cocok" illustration="no-search-results"
                                      description="Tidak ada talenta tersimpan di folder ini yang cocok dengan pencarian Anda.">
                        <x-slot:action>
                            <x-ui.button variant="secondary" @click="query = ''; folder = null">Tampilkan semua</x-ui.button>
                        </x-slot:action>
                    </x-ui.empty-state>
                </div>
            </section>
        </div>

        {{-- bilah banding: hanya muncul bila ada talenta yang dipilih --}}
        <div class="sg-co-talents__compare" x-show="picked.length > 0" x-cloak role="region" aria-label="Perbandingan talenta">
            <p class="sg-co-talents__compare-text" aria-live="polite">
                <b x-text="picked.length"></b> dipilih<span x-show="picked.length < 2">, pilih minimal 2</span><span x-show="picked.length >= 3"> (maksimal 3)</span>:
                <span x-text="picked.map(p => p.name).join(', ')"></span>
            </p>
            <div class="sg-co-talents__compare-actions">
                <x-ui.button variant="tertiary" @click="picked = []">Batal</x-ui.button>
                <a class="sg-btn sg-btn--secondary" x-show="picked.length >= 2" :href="compareUrl()">Bandingkan</a>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function savedTalents(rows) {
    return {
        rows,
        folder: null,
        query: '',
        picked: [],
        removed: [],
        busy: null,

        get total() {
            return this.rows.length - this.removed.length;
        },

        matches(row) {
            if (this.removed.includes(row.id)) return false;
            if (this.folder !== null && this.folder !== row.folder) return false;
            const q = this.query.trim().toLowerCase();
            return !q || row.text.includes(q);
        },

        isVisible(id) {
            const row = this.rows.find((r) => r.id === id);
            return row ? this.matches(row) : false;
        },

        visibleCount() {
            return this.rows.filter((row) => this.matches(row)).length;
        },

        countIn(folderKey) {
            return this.rows.filter((row) => row.folder === folderKey && !this.removed.includes(row.id)).length;
        },

        isPicked(id) {
            return this.picked.some((p) => p.id === id);
        },

        togglePick(id, name) {
            this.picked = this.isPicked(id)
                ? this.picked.filter((p) => p.id !== id)
                : (this.picked.length < 3 ? [...this.picked, { id, name }] : this.picked);
        },

        compareUrl() {
            return `{{ route('company.talents.compare') }}?ids=${this.picked.map((p) => p.id).join(',')}`;
        },

        async remove(id, name) {
            const ok = await window.sgConfirm({
                title: 'Hapus dari tersimpan?',
                message: name + ' akan dihapus dari Talenta Tersimpan beserta folder dan catatannya.',
                confirmLabel: 'Hapus',
            });
            if (!ok) return;

            this.busy = id;
            try {
                const response = await fetch(`{{ url('/company/talents') }}/${id}/toggle-save`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });
                const data = await response.json();
                // toggle-save menghapus bila tersimpan; 'saved' berarti baris sudah tidak ada di server
                if (!response.ok || !data.success || data.action !== 'unsaved') throw new Error('gagal');

                this.removed.push(id);
                this.picked = this.picked.filter((p) => p.id !== id);
                window.showNotification(name + ' dihapus dari Talenta Tersimpan', 'info');
            } catch (error) {
                window.showNotification('Gagal menghapus talenta. Muat ulang halaman lalu coba lagi.', 'error');
            } finally {
                this.busy = null;
            }
        },
    };
}
</script>
@endpush
