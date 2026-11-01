{{-- phase 18: editor brief masalah dengan enam bagian + navigasi bagian dan progres.
     dipakai create (problem = null) dan edit. semua nama input sama seperti sebelumnya:
     create = 37 input (termasuk address), edit = field edit lama (scope, village, detailed_location, delete_images[]).
     galat server dipetakan ke bagiannya; progres dihitung di peramban dari field wajib. --}}
@php
    $isEdit = $problem !== null;
    $arr = fn ($v) => is_array($v) ? $v : (json_decode((string) $v, true) ?: []);
    $date = fn ($d) => $d ? \Illuminate\Support\Carbon::parse($d)->format('Y-m-d') : null;

    $sections = [
        'informasi' => ['label' => 'Informasi dasar', 'fields' => ['title', 'description', 'background', 'scope']],
        'lokasi' => ['label' => 'Lokasi', 'fields' => ['province_id', 'regency_id', 'address', 'village', 'detailed_location']],
        'sasaran' => ['label' => 'Sasaran & SDG', 'fields' => ['objectives', 'expected_outcomes', 'deliverables', 'sdg_categories']],
        'kebutuhan' => ['label' => 'Kebutuhan & keterampilan', 'fields' => ['required_students', 'required_skills', 'required_majors', 'difficulty_level', 'facilities_provided', 'start_date', 'end_date', 'application_deadline', 'duration_months']],
        'media' => ['label' => 'Media', 'fields' => ['images', 'delete_images']],
        'status' => ['label' => 'Status', 'fields' => ['status']],
    ];
    // jumlah galat server per bagian (kunci 'required_skills.0' -> 'required_skills')
    $sectionErrors = [];
    foreach ($sections as $key => $section) {
        $count = 0;
        foreach (($errors ?? new \Illuminate\Support\ViewErrorBag)->keys() as $errorKey) {
            if (in_array(explode('.', $errorKey)[0], $section['fields'], true)) {
                $count++;
            }
        }
        $sectionErrors[$key] = $count;
    }

    $selectedSdg = array_map('intval', (array) old('sdg_categories', $isEdit ? $arr($problem->sdg_categories) : []));
    $status = old('status', $isEdit ? $problem->status : 'draft');
    $statusOptions = [
        'draft' => 'Draf: disimpan, belum terlihat oleh mahasiswa',
        'open' => 'Dibuka: langsung menerima lamaran',
    ];
    if ($isEdit) {
        $statusOptions['closed'] = 'Ditutup: tidak menerima lamaran baru';
    }
    $submitLabels = $isEdit
        ? ['draft' => 'Simpan perubahan', 'open' => 'Simpan perubahan', 'closed' => 'Simpan perubahan']
        : ['draft' => 'Simpan sebagai draf', 'open' => 'Simpan dan buka lamaran'];
@endphp

<div class="sg-inst-brief" x-data="sgProblemBrief({ errors: @js($sectionErrors), labels: @js($submitLabels) })"
     @input="touch($event)" @change="touch($event)" @invalid.capture="onInvalid($event)">

    {{-- navigasi bagian + progres --}}
    <nav class="sg-inst-brief__nav" aria-label="Bagian formulir">
        <p class="sg-inst-brief__progress">
            <span x-text="doneCount()">0</span> dari {{ count($sections) }} bagian lengkap
        </p>
        <ol class="sg-inst-brief__steps">
            @foreach ($sections as $key => $section)
                <li>
                    <a href="#bagian-{{ $key }}" class="sg-inst-brief__step" @click.prevent="go('{{ $key }}')"
                       data-state="{{ $sectionErrors[$key] ? 'error' : 'todo' }}" :data-state="state['{{ $key }}'] || null"
                       :aria-current="active === '{{ $key }}' ? 'step' : null">
                        <span class="sg-inst-brief__num" aria-hidden="true">{{ $loop->iteration }}</span>
                        <span class="sg-inst-brief__label">{{ $section['label'] }}</span>
                        <x-icon name="circle-alert" size="sm" class="sg-inst-brief__mark sg-inst-brief__mark--error" />
                        <x-icon name="check" size="sm" class="sg-inst-brief__mark sg-inst-brief__mark--done" />
                        <span class="sr-only" x-text="state['{{ $key }}'] === 'error' ? ', ada isian yang perlu diperbaiki' : (state['{{ $key }}'] === 'done' ? ', lengkap' : '')">{{ $sectionErrors[$key] ? ', ada isian yang perlu diperbaiki' : '' }}</span>
                    </a>
                </li>
            @endforeach
        </ol>
    </nav>

    <div class="sg-inst-brief__body">
        <p class="sr-only" aria-live="assertive" x-text="announce"></p>

        {{-- 1. informasi dasar --}}
        <section class="sg-inst-section" id="bagian-informasi" data-section="informasi" aria-labelledby="judul-informasi">
            <h2 id="judul-informasi" tabindex="-1"><span class="sg-inst-section__num">1</span>Informasi dasar</h2>
            <x-ui.field label="Judul masalah" for="title" required>
                <x-ui.input name="title" :value="$problem?->title" required maxlength="255" placeholder="Contoh: Peningkatan Literasi Digital di Desa Sukamaju" />
            </x-ui.field>
            <x-ui.field label="Deskripsi" for="description" required help="Jelaskan masalah yang dihadapi dan siapa yang terdampak.">
                <x-ui.textarea name="description" :value="$problem?->description" rows="5" required help />
            </x-ui.field>
            <x-ui.field label="Latar belakang" for="background">
                <x-ui.textarea name="background" :value="$problem?->background" rows="3" placeholder="Jelaskan latar belakang masalah" />
            </x-ui.field>
            @if ($isEdit)
                <x-ui.field label="Ruang lingkup" for="scope">
                    <x-ui.textarea name="scope" :value="$problem->scope" rows="3" />
                </x-ui.field>
            @endif
        </section>

        {{-- 2. lokasi: kabupaten/kota mengikuti provinsi terakhir (abort + urutan respons, sgRegions) --}}
        <section class="sg-inst-section" id="bagian-lokasi" data-section="lokasi" aria-labelledby="judul-lokasi">
            <h2 id="judul-lokasi" tabindex="-1"><span class="sg-inst-section__num">2</span>Lokasi</h2>
            <div class="sg-inst-grid-2"
                 x-data="sgRegions({ url: @js(url('/api/regencies/__ID__')), province: @js((string) old('province_id', $problem?->province_id)), regency: @js((string) old('regency_id', $problem?->regency_id)), regencies: @js($regencies->map(fn ($r) => ['id' => $r->id, 'name' => $r->name])->values()) })">
                <x-ui.field label="Provinsi" for="province_id" required>
                    <x-ui.select name="province_id" :options="$provinces->pluck('name', 'id')->all()" :selected="$problem?->province_id" placeholder="Pilih provinsi" required
                                 x-model="province" x-ref="province" @change="load()" />
                </x-ui.field>
                <x-ui.field label="Kabupaten/kota" for="regency_id" required>
                    <x-ui.select name="regency_id" placeholder="Pilih kabupaten/kota" required aria-describedby="regency_id-status"
                                 x-model="regency" x-ref="regency" x-bind:disabled="loading">
                        <template x-for="item in regencies" :key="item.id">
                            <option :value="item.id" x-text="item.name"></option>
                        </template>
                    </x-ui.select>
                    <p class="sg-inst-status" id="regency_id-status" aria-live="polite" :data-state="failed ? 'error' : null">
                        <span class="sg-spinner" aria-hidden="true" x-show="loading" x-cloak></span>
                        <span x-text="status || (province ? '' : 'Pilih provinsi terlebih dahulu.')">@unless (old('province_id', $problem?->province_id)) Pilih provinsi terlebih dahulu. @endunless</span>
                        <button type="button" class="sg-btn sg-btn--tertiary" x-show="failed" x-cloak @click="load()">Coba lagi</button>
                    </p>
                </x-ui.field>
            </div>
            @if ($isEdit)
                <x-ui.field label="Desa/kelurahan" for="village">
                    <x-ui.input name="village" :value="$problem->village" maxlength="255" />
                </x-ui.field>
                <x-ui.field label="Detail lokasi" for="detailed_location">
                    <x-ui.textarea name="detailed_location" :value="$problem->detailed_location" rows="2" />
                </x-ui.field>
            @else
                <x-ui.field label="Alamat atau detail lokasi" for="address" help="Disimpan sebagai detail lokasi masalah.">
                    <x-ui.input name="address" placeholder="Contoh: Jl. Raya Desa No. 123" help />
                </x-ui.field>
            @endif
        </section>

        {{-- 3. sasaran & sdg --}}
        <section class="sg-inst-section" id="bagian-sasaran" data-section="sasaran" aria-labelledby="judul-sasaran">
            <h2 id="judul-sasaran" tabindex="-1"><span class="sg-inst-section__num">3</span>Sasaran &amp; SDG</h2>
            <x-ui.field label="Tujuan" for="objectives">
                <x-ui.textarea name="objectives" :value="$problem?->objectives" rows="3" placeholder="Sebutkan tujuan dari proyek ini" />
            </x-ui.field>
            <x-ui.field label="Hasil yang diharapkan" for="expected_outcomes">
                <x-ui.textarea name="expected_outcomes" :value="$problem?->expected_outcomes" rows="3" placeholder="Apa hasil yang diharapkan dari proyek ini?" />
            </x-ui.field>
            @include('institution.problems.components.list-field', [
                'name' => 'deliverables[]', 'id' => 'deliverables-container', 'label' => 'Luaran',
                'help' => 'Hasil kerja yang diserahkan mahasiswa, satu per baris.',
                'values' => old('deliverables', $isEdit ? $arr($problem->deliverables) : []),
                'placeholder' => 'Contoh: Laporan Penelitian', 'addLabel' => 'Tambah luaran',
            ])
            <x-ui.fieldset legend="Kategori SDG" name="sdg_categories" required help="Pilih minimal satu tujuan pembangunan berkelanjutan yang relevan.">
                <div class="sg-inst-sdg" id="sdg-categories">
                    @for ($i = 1; $i <= 17; $i++)
                        <x-ui.checkbox name="sdg_categories[]" :value="$i" :label="$i.'. '.sdg_label($i)" :checked="in_array($i, $selectedSdg, true)" />
                    @endfor
                </div>
                <p class="sg-error" x-show="sdgError" x-cloak role="alert"><x-icon name="circle-alert" size="sm" /><span x-text="sdgError"></span></p>
            </x-ui.fieldset>
        </section>

        {{-- 4. kebutuhan & keterampilan (termasuk jadwal) --}}
        <section class="sg-inst-section" id="bagian-kebutuhan" data-section="kebutuhan" aria-labelledby="judul-kebutuhan">
            <h2 id="judul-kebutuhan" tabindex="-1"><span class="sg-inst-section__num">4</span>Kebutuhan &amp; keterampilan</h2>
            <div class="sg-inst-grid-2">
                <x-ui.field label="Jumlah mahasiswa" for="required_students" required>
                    <x-ui.input type="number" name="required_students" :value="$problem?->required_students ?? 1" min="1" required />
                </x-ui.field>
                <x-ui.field label="Tingkat kesulitan" for="difficulty_level" required>
                    <x-ui.select name="difficulty_level" :options="['beginner' => 'Pemula', 'intermediate' => 'Menengah', 'advanced' => 'Lanjut']" :selected="$problem?->difficulty_level ?? 'intermediate'" required />
                </x-ui.field>
            </div>
            @include('institution.problems.components.list-field', [
                'name' => 'required_skills[]', 'id' => 'skills-container', 'label' => 'Keterampilan yang dibutuhkan', 'required' => true,
                'help' => 'Satu keterampilan per baris.',
                'values' => old('required_skills', $isEdit ? $arr($problem->required_skills) : []),
                'placeholder' => 'Contoh: Komunikasi', 'addLabel' => 'Tambah keterampilan',
            ])
            @include('institution.problems.components.list-field', [
                'name' => 'required_majors[]', 'id' => 'majors-container', 'label' => 'Jurusan yang dibutuhkan (opsional)',
                'values' => old('required_majors', $isEdit ? $arr($problem->required_majors) : []),
                'placeholder' => 'Contoh: Teknik Informatika', 'addLabel' => 'Tambah jurusan',
            ])
            @include('institution.problems.components.list-field', [
                'name' => 'facilities_provided[]', 'id' => 'facilities-container', 'label' => 'Fasilitas yang disediakan',
                'help' => 'Fasilitas yang diberikan kepada mahasiswa, satu per baris.',
                'values' => old('facilities_provided', $isEdit ? $arr($problem->facilities_provided) : []),
                'placeholder' => 'Contoh: Akomodasi', 'addLabel' => 'Tambah fasilitas',
            ])
            <fieldset class="sg-fieldset sg-inst-schedule">
                <legend>Jadwal</legend>
                <div class="sg-inst-grid-2">
                    <x-ui.field label="Batas lamaran" for="application_deadline" required help="Harus sebelum tanggal mulai.">
                        <x-ui.input type="date" name="application_deadline" :value="$date($problem?->application_deadline)" required help />
                    </x-ui.field>
                    <x-ui.field label="Durasi (bulan)" for="duration_months" required>
                        <x-ui.input type="number" name="duration_months" :value="$problem?->duration_months ?? 1" min="1" required />
                    </x-ui.field>
                    <x-ui.field label="Tanggal mulai" for="start_date" required>
                        <x-ui.input type="date" name="start_date" :value="$date($problem?->start_date)" required />
                    </x-ui.field>
                    <x-ui.field label="Tanggal selesai" for="end_date" required help="Harus setelah tanggal mulai.">
                        <x-ui.input type="date" name="end_date" :value="$date($problem?->end_date)" required help />
                    </x-ui.field>
                </div>
            </fieldset>
        </section>

        {{-- 5. media: nama input, jenis berkas dan penyimpanan tidak berubah --}}
        <section class="sg-inst-section" id="bagian-media" data-section="media" aria-labelledby="judul-media">
            <h2 id="judul-media" tabindex="-1"><span class="sg-inst-section__num">5</span>Media <span class="sg-inst-section__opt">opsional</span></h2>
            @if ($isEdit && $problem->images->count() > 0)
                <x-ui.fieldset legend="Foto saat ini" help="Centang foto yang ingin dihapus saat perubahan disimpan.">
                    <ul class="sg-inst-gallery">
                        @foreach ($problem->images as $image)
                            <li class="sg-inst-gallery__item">
                                <span class="sg-inst-gallery__media">
                                    <img src="{{ $image->image_url }}" alt="{{ $image->caption ?: 'Foto '.$loop->iteration.' masalah '.$problem->title }}" loading="lazy"
                                         onerror="this.hidden = true; this.nextElementSibling.hidden = false;">
                                    <span class="sg-inst-gallery__missing" hidden>Foto tidak dapat dimuat</span>
                                </span>
                                @if ($image->is_cover)<span class="sg-inst-gallery__cover">Sampul</span>@endif
                                <x-ui.checkbox name="delete_images[]" :value="$image->id" :label="'Hapus foto '.$loop->iteration" />
                            </li>
                        @endforeach
                    </ul>
                </x-ui.fieldset>
            @endif
            <x-ui.upload name="images[]" :id="$isEdit ? 'new-images-input' : 'images-input'" :label="$isEdit ? 'Tambah foto' : 'Foto dokumentasi'"
                         accept="image/*" multiple :max-size="5242880"
                         :hint="$isEdit
                            ? 'JPG atau PNG, maks. 5 MB per foto. Total foto paling banyak 5 (sisa '.max(0, 5 - $problem->images->count()).').'
                            : 'JPG atau PNG, maks. 5 MB per foto, paling banyak 5 foto. Foto pertama menjadi sampul.'" />
        </section>

        {{-- 6. status publikasi: draf/dibuka (create), + ditutup (edit), nilai sama seperti sebelumnya --}}
        <section class="sg-inst-section" id="bagian-status" data-section="status" aria-labelledby="judul-status">
            <h2 id="judul-status" tabindex="-1"><span class="sg-inst-section__num">6</span>Status</h2>
            <x-ui.field label="Status publikasi" for="status" required help="Pilih Draf untuk menyimpan dulu, atau Dibuka agar mahasiswa langsung dapat melamar.">
                <x-ui.select name="status" :options="$statusOptions" :selected="$status" required help
                             x-init="statusValue = $el.value" @change="statusValue = $event.target.value" />
            </x-ui.field>
        </section>

        <div class="sg-inst-brief__actions">
            <a href="{{ $cancelHref }}" class="sg-btn sg-btn--tertiary">Batal</a>
            <button type="submit" class="sg-btn sg-btn--primary" :disabled="submitting" :aria-busy="submitting ? 'true' : null"
                    x-text="submitting ? 'Menyimpan…' : (labels[statusValue] || @js($submitLabels[$status] ?? reset($submitLabels)))">{{ $submitLabels[$status] ?? reset($submitLabels) }}</button>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
// phase 18: navigasi bagian, progres, penanda galat per bagian, dan daftar isian dinamis
document.addEventListener('alpine:init', () => {
    Alpine.data('sgProblemBrief', (cfg = {}) => ({
        errors: { ...(cfg.errors || {}) },
        labels: cfg.labels || {},
        state: {},
        active: 'informasi',
        statusValue: '',
        submitting: false,
        sdgError: '',
        announce: '',
        announced: false,
        init() {
            // simpan akar komponen: di handler @click pada tautan, $el adalah tautan itu, bukan formulir
            this.root = this.$el;
            this.refresh();
            // sgRegions memasang kabupaten/kota tersimpan setelah opsi x-for dirender; hitung ulang sesudahnya
            this.$nextTick(() => setTimeout(() => this.refresh()));
            const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            this.scrollBehavior = reduce ? 'auto' : 'smooth';
            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => { if (entry.isIntersecting) this.active = entry.target.dataset.section; });
                }, { rootMargin: '-20% 0px -70% 0px' });
                this.sections().forEach((section) => observer.observe(section));
            }
            // galat server: bawa pengguna ke bagian pertama yang bermasalah
            const firstError = Object.keys(this.errors).find((key) => this.errors[key] > 0);
            if (firstError) this.$nextTick(() => this.go(firstError, false));
            this.root.closest('form')?.addEventListener('submit', (e) => this.onSubmit(e));
            // kembali lewat tombol back browser: aktifkan lagi tombol kirim
            window.addEventListener('pageshow', () => { this.submitting = false; });
        },
        sections() { return [...this.root.querySelectorAll('[data-section]')]; },
        refresh() {
            this.sections().forEach((section) => {
                const key = section.dataset.section;
                if (this.errors[key]) { this.state[key] = 'error'; return; }
                const fields = [...section.querySelectorAll('input[required], select[required], textarea[required]')];
                // validity.valid, bukan checkValidity(): yang terakhir memicu event invalid (bagian lain ikut ditandai galat)
                let ok = fields.every((el) => el.validity.valid);
                if (key === 'sasaran') ok = ok && section.querySelectorAll('input[name="sdg_categories[]"]:checked').length > 0;
                this.state[key] = ok ? 'done' : 'todo';
            });
        },
        doneCount() { return Object.values(this.state).filter((value) => value === 'done').length; },
        touch(event) {
            // mengubah isian menghapus tanda galat bagian itu (galat server tetap tertulis di bawah field)
            const section = event.target.closest?.('[data-section]');
            if (section) this.errors[section.dataset.section] = 0;
            if (event.target.name === 'sdg_categories[]') this.sdgError = '';
            this.refresh();
        },
        go(key, focus = true) {
            const section = this.root.querySelector(`[data-section="${key}"]`);
            if (!section) return;
            this.active = key;
            section.scrollIntoView({ behavior: this.scrollBehavior || 'auto', block: 'start' });
            if (focus) section.querySelector('h2')?.focus({ preventScroll: true });
            history.replaceState(null, '', `#bagian-${key}`);
        },
        sectionLabel(key) {
            return this.root.querySelector(`[data-section="${key}"] h2`)?.textContent.replace(/^\d+/, '').trim() || '';
        },
        onInvalid(event) {
            const section = event.target.closest?.('[data-section]');
            if (!section) return;
            const key = section.dataset.section;
            this.errors[key] = Math.max(1, this.errors[key] || 0);
            this.refresh();
            if (!this.announced) {
                this.announced = true;
                this.announce = `Bagian ${this.sectionLabel(key)} belum lengkap. ${event.target.validationMessage}`;
                setTimeout(() => { this.announced = false; }, 500);
            }
        },
        onSubmit(event) {
            // sama seperti sebelumnya: minimal satu kategori SDG (sekarang pesan di bagian, bukan alert)
            if (this.root.querySelectorAll('input[name="sdg_categories[]"]:checked').length === 0) {
                event.preventDefault();
                this.sdgError = 'Pilih minimal satu kategori SDG.';
                this.errors.sasaran = 1;
                this.refresh();
                this.announce = 'Bagian Sasaran & SDG belum lengkap. Pilih minimal satu kategori SDG.';
                this.go('sasaran', false);
                this.root.querySelector('input[name="sdg_categories[]"]')?.focus({ preventScroll: true });
                return;
            }
            // cegah kirim ganda (unggah foto bisa lama; klik kedua membuat masalah dobel)
            if (this.submitting) { event.preventDefault(); return; }
            this.submitting = true;
        },
    }));

    Alpine.data('sgListField', () => ({
        add() {
            const row = this.$refs.row.content.firstElementChild.cloneNode(true);
            this.$refs.list.appendChild(row);
            row.querySelector('input')?.focus();
        },
        remove(event) {
            const row = event.currentTarget.closest('li');
            const rows = this.$refs.list.querySelectorAll('li');
            if (rows.length > 1) {
                const target = row.previousElementSibling || row.nextElementSibling;
                row.remove();
                target?.querySelector('input')?.focus();
            } else {
                const input = row.querySelector('input');
                input.value = '';
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.focus();
            }
        },
    }));
});
</script>
@endpush
@endonce
