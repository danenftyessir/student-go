@extends('layouts.app')

@section('title', 'Talenta')

@section('content')
@php
    // phase 22: direktori talenta. filter di server (satu form GET), satu pager, jumlah = mahasiswa yang cocok.
    // pool tetap 'mahasiswa dengan minimal 1 proyek' (D-05/D-08); nama dari display_name; lokasi = kampus.
    $fmt = fn ($n) => number_format((float) $n, 0, ',', '.');
    $sdgOpen = count($filters['sdg_alignment']) > 0;
    $skillOpen = count($filters['skills']) > 0;
@endphp

<div class="sg-container sg-co-talents" x-data="talentDirectory(@js($savedIds))">
    <x-ui.page-header title="Talenta" description="Cari mahasiswa berdasarkan SDG, kampus, jurusan dan keterampilan, lalu simpan atau bandingkan.">
        <x-slot:actions>
            <x-ui.button variant="secondary" icon="bookmark" :href="route('company.talents.saved')">Talenta tersimpan</x-ui.button>
            <x-ui.button variant="tertiary" icon="download" :href="route('company.talents.export')">Ekspor CSV</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="sg-co-talents__layout">
        {{-- rail filter kiri; di bawah 768px bidang filter dibuka sebagai drawer --}}
        <div class="sg-co-talents__rail">
            <x-ui.filter-bar :action="route('company.talents.index')" search-label="Cari talenta"
                             search-placeholder="Nama, jurusan, kampus" :reset-href="route('company.talents.index')"
                             :active="$activeFilters" id="filter-talenta">
                <details class="sg-co-talents__group" @if ($sdgOpen) open @endif>
                    <summary>SDG @if ($sdgOpen)<span class="sg-co-talents__group-count">({{ count($filters['sdg_alignment']) }})</span>@endif</summary>
                    <x-ui.fieldset legend="SDG dari proyek selesai" class="sg-co-talents__checks">
                        @foreach ($sdgOptions as $id => $label)
                            <x-ui.checkbox name="sdg_alignment[]" :value="$id" :label="$label" :checked="in_array($id, $filters['sdg_alignment'], true)" />
                        @endforeach
                    </x-ui.fieldset>
                </details>

                <x-ui.field label="Universitas" for="university_id">
                    <x-ui.select name="university_id" :options="$universityOptions" :selected="$filters['university_id']" placeholder="Semua universitas" />
                </x-ui.field>

                <x-ui.field label="Jurusan" for="major">
                    <x-ui.select name="major" :options="array_combine($majorOptions, $majorOptions) ?: []" :selected="$filters['major'] !== '' ? $filters['major'] : null" placeholder="Semua jurusan" />
                </x-ui.field>

                <x-ui.field label="Provinsi kampus" for="province_id">
                    <x-ui.select name="province_id" :options="$provinceOptions" :selected="$filters['province_id']" placeholder="Semua provinsi" />
                </x-ui.field>

                @if ($filters['location'] !== '')
                    {{-- parameter lama ?location= tetap berlaku; ditampilkan agar bisa dihapus --}}
                    <x-ui.field label="Lokasi kampus" for="location">
                        <x-ui.input name="location" :value="$filters['location']" />
                    </x-ui.field>
                @endif

                <details class="sg-co-talents__group" @if ($skillOpen) open @endif>
                    <summary>Keterampilan @if ($skillOpen)<span class="sg-co-talents__group-count">({{ count($filters['skills']) }})</span>@endif</summary>
                    @if (count($skillOptions))
                        <x-ui.fieldset legend="Keterampilan di profil" class="sg-co-talents__checks">
                            @foreach ($skillOptions as $skill)
                                <x-ui.checkbox name="skills[]" :value="$skill" :label="$skill" :checked="in_array($skill, $filters['skills'], true)" />
                            @endforeach
                        </x-ui.fieldset>
                    @else
                        <p class="sg-co-talents__group-note">Belum ada mahasiswa di pool yang mengisi keterampilan di profilnya.</p>
                        @foreach ($filters['skills'] as $skill)
                            <input type="hidden" name="skills[]" value="{{ $skill }}">
                        @endforeach
                    @endif
                </details>

                <x-ui.checkbox name="verified_only" value="1" label="Hanya email terverifikasi" :checked="$filters['verified_only']" />

                @if (request()->filled('per_page'))
                    <input type="hidden" name="per_page" value="{{ (int) request('per_page') }}">
                @endif
            </x-ui.filter-bar>
        </div>

        <section class="sg-co-talents__results" aria-labelledby="hasil-talenta">
            <div class="sg-co-talents__head">
                <h2 id="hasil-talenta" data-sg-count>
                    @if ($isFiltered)
                        {{ $fmt($totalTalents) }} dari {{ $fmt($poolTotal) }} mahasiswa cocok
                    @else
                        {{ $fmt($poolTotal) }} mahasiswa
                    @endif
                </h2>
                <p class="sg-co-talents__rule">Pool talenta: mahasiswa dengan minimal 1 proyek KKN di Student Go. Diurutkan dari yang terakhir bergabung.</p>
            </div>

            {{-- pencarian tersimpan: fitur lama (localStorage peramban ini), kini menyimpan URL filter --}}
            <div class="sg-co-talents__searches" x-show="savedSearches.length || {{ $isFiltered ? 'true' : 'false' }}" x-cloak>
                <template x-if="savedSearches.length">
                    <ul class="sg-co-talents__search-list" aria-label="Pencarian tersimpan">
                        <template x-for="(item, index) in savedSearches" :key="index">
                            <li>
                                <a :href="'{{ route('company.talents.index') }}' + (item.query ? '?' + item.query : '')" class="sg-co-talents__search-link" x-text="item.name"></a>
                                <button type="button" class="sg-icon-btn sg-co-talents__search-remove" @click="removeSearch(index)" :aria-label="'Hapus pencarian ' + item.name">
                                    <x-icon name="x" size="sm" />
                                </button>
                            </li>
                        </template>
                    </ul>
                </template>
                @if ($isFiltered)
                    <form class="sg-co-talents__search-save" @submit.prevent="saveSearch()" x-show="naming" x-cloak>
                        <label for="nama-pencarian" class="sr-only">Nama pencarian</label>
                        <input id="nama-pencarian" type="text" class="sg-input" x-model="searchName" x-ref="searchName" maxlength="60" placeholder="Nama pencarian, mis. Kesehatan Jawa Barat" required>
                        <x-ui.button type="submit" variant="secondary">Simpan</x-ui.button>
                        <x-ui.button variant="tertiary" @click="naming = false">Batal</x-ui.button>
                    </form>
                    <x-ui.button variant="tertiary" icon="plus" x-show="!naming" @click="startNaming()">Simpan pencarian ini</x-ui.button>
                @endif
            </div>

            @if ($talents->count())
                <x-ui.list label="Daftar talenta" class="sg-co-talents__list">
                    @foreach ($talents as $talent)
                        @php
                            $profileUrl = $talent['username'] ? route('profile.public', $talent['username']) : null;
                            $meta = collect([$talent['title'], $talent['university']])->filter()->implode(' · ');
                        @endphp
                        <x-ui.list-item :title="$talent['name']" :href="$profileUrl" :meta="$meta !== '' ? $meta : null" data-talent-id="{{ $talent['id'] }}">
                            <x-slot:leading>
                                <x-ui.avatar :name="$talent['name']" decorative />
                            </x-slot:leading>

                            <p class="sg-co-talents__facts">
                                @if ($talent['location'])
                                    <span><x-icon name="map-pin" size="sm" /><span class="sr-only">Lokasi kampus: </span>{{ $talent['location'] }}</span>
                                @endif
                                <span>{{ $fmt($talent['projects_count']) }} proyek KKN</span>
                            </p>

                            @if (count($talent['sdg_badges']))
                                <ul class="sg-co-talents__chips" aria-label="SDG dari proyek selesai">
                                    @foreach ($talent['sdg_badges'] as $sdg)
                                        <li class="sg-chip">SDG {{ $sdg['id'] }} · {{ $sdg['name'] }}</li>
                                    @endforeach
                                </ul>
                            @endif

                            @if (count($talent['skills']))
                                <ul class="sg-co-talents__chips" aria-label="Keterampilan">
                                    @foreach ($talent['skills'] as $skill)
                                        <li class="sg-chip">{{ $skill }}</li>
                                    @endforeach
                                </ul>
                            @endif

                            <x-slot:end>
                                <label class="sg-check sg-co-talents__pick">
                                    <input type="checkbox" :checked="isPicked({{ $talent['id'] }})"
                                           :disabled="!isPicked({{ $talent['id'] }}) && picked.length >= 3"
                                           @change="togglePick({{ $talent['id'] }}, @js($talent['name']))">
                                    <span>Bandingkan<span class="sr-only"> {{ $talent['name'] }}</span></span>
                                </label>
                                <button type="button" class="sg-btn sg-btn--secondary sg-co-talents__save"
                                        :aria-pressed="isSaved({{ $talent['id'] }}).toString()"
                                        aria-pressed="{{ in_array($talent['id'], $savedIds, true) ? 'true' : 'false' }}"
                                        :disabled="busy === {{ $talent['id'] }}"
                                        @click="toggleSave({{ $talent['id'] }}, @js($talent['name']))">
                                    <x-icon name="bookmark" size="sm" />
                                    <span x-text="isSaved({{ $talent['id'] }}) ? 'Tersimpan' : 'Simpan'">{{ in_array($talent['id'], $savedIds, true) ? 'Tersimpan' : 'Simpan' }}</span>
                                    <span class="sr-only">{{ $talent['name'] }}</span>
                                </button>
                            </x-slot:end>
                        </x-ui.list-item>
                    @endforeach
                </x-ui.list>

                <x-ui.pagination :paginator="$talents" label="Halaman talenta" class="sg-co-talents__pager" />
            @elseif ($isFiltered)
                <x-ui.empty-state title="Tidak ada mahasiswa yang cocok" illustration="no-search-results"
                                  description="Tidak ada mahasiswa di pool yang memenuhi semua filter ini. Kurangi atau ubah filter.">
                    <x-slot:action>
                        <x-ui.button variant="secondary" :href="route('company.talents.index')">Atur ulang filter</x-ui.button>
                    </x-slot:action>
                </x-ui.empty-state>
            @else
                <x-ui.empty-state title="Belum ada talenta" illustration="no-search-results"
                                  description="Mahasiswa muncul di sini setelah menyelesaikan minimal 1 proyek KKN di Student Go." />
            @endif
        </section>
    </div>

    {{-- bilah banding: hanya muncul bila ada talenta yang dipilih (2-3 untuk dibandingkan) --}}
    <div class="sg-co-talents__compare" x-show="picked.length > 0" x-cloak role="region" aria-label="Perbandingan talenta">
        <p class="sg-co-talents__compare-text" aria-live="polite">
            <b x-text="picked.length"></b> dipilih<span x-show="picked.length < 2">, pilih minimal 2</span><span x-show="picked.length >= 3"> (maksimal 3)</span>:
            <span x-text="picked.map(p => p.name).join(', ')"></span>
        </p>
        <div class="sg-co-talents__compare-actions">
            <x-ui.button variant="tertiary" @click="clearPicks()">Batal</x-ui.button>
            <a class="sg-btn sg-btn--secondary" x-show="picked.length >= 2" :href="compareUrl()">Bandingkan</a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function talentDirectory(savedIds) {
    const SEARCH_KEY = 'company_saved_searches';
    const PICK_KEY = 'sg_talent_compare';
    const store = (area) => { try { return window[area]; } catch (e) { return null; } };

    return {
        saved: savedIds.map(Number),
        busy: null,
        picked: [],
        savedSearches: [],
        naming: false,
        searchName: '',

        init() {
            // pilihan banding bertahan antarhalaman pager (sessionStorage)
            try { this.picked = JSON.parse(store('sessionStorage')?.getItem(PICK_KEY) || '[]').slice(0, 3); } catch (e) { this.picked = []; }

            // pencarian tersimpan lama ({ name, filters }) diubah menjadi { name, query }
            try {
                const raw = JSON.parse(store('localStorage')?.getItem(SEARCH_KEY) || '[]');
                this.savedSearches = (Array.isArray(raw) ? raw : []).map((item) => ({
                    name: String(item.name || 'Pencarian'),
                    query: typeof item.query === 'string' ? item.query : this.legacyQuery(item.filters || {}),
                }));
            } catch (e) { this.savedSearches = []; }
        },

        legacyQuery(filters) {
            const params = new URLSearchParams();
            (filters.sdg_alignment || []).forEach((id) => params.append('sdg_alignment[]', id));
            (filters.skills || []).forEach((skill) => params.append('skills[]', skill));
            if (filters.location) params.set('location', filters.location);
            if (filters.verified_only) params.set('verified_only', '1');
            return params.toString();
        },

        persistSearches() {
            try { store('localStorage')?.setItem(SEARCH_KEY, JSON.stringify(this.savedSearches)); } catch (e) {}
        },

        startNaming() {
            this.naming = true;
            this.$nextTick(() => this.$refs.searchName?.focus());
        },

        saveSearch() {
            const name = this.searchName.trim();
            if (!name) return;
            const params = new URLSearchParams(window.location.search);
            params.delete('page');
            this.savedSearches.push({ name, query: params.toString() });
            this.persistSearches();
            this.searchName = '';
            this.naming = false;
            window.showNotification('Pencarian disimpan di peramban ini', 'success');
        },

        removeSearch(index) {
            const [removed] = this.savedSearches.splice(index, 1);
            this.persistSearches();
            window.showNotification('Pencarian "' + (removed?.name || '') + '" dihapus', 'info');
        },

        isSaved(id) {
            return this.saved.includes(id);
        },

        async toggleSave(id, name) {
            if (this.busy) return;
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

        isPicked(id) {
            return this.picked.some((p) => p.id === id);
        },

        togglePick(id, name) {
            this.picked = this.isPicked(id)
                ? this.picked.filter((p) => p.id !== id)
                : (this.picked.length < 3 ? [...this.picked, { id, name }] : this.picked);
            try { store('sessionStorage')?.setItem(PICK_KEY, JSON.stringify(this.picked)); } catch (e) {}
        },

        clearPicks() {
            this.picked = [];
            try { store('sessionStorage')?.removeItem(PICK_KEY); } catch (e) {}
        },

        compareUrl() {
            return `{{ route('company.talents.compare') }}?ids=${this.picked.map((p) => p.id).join(',')}`;
        },
    };
}
</script>
@endpush
