@extends('layouts.app')

@section('title', 'Pasang Lowongan')

@section('content')
@php
    // phase 23: wizard 5 langkah yang sama (Detail Lowongan, Deskripsi, Keahlian, SDG Alignment, Kriteria Dampak), stepper bersama,
    // validasi per langkah sesuai aturan server (saat blur dan saat 'Berikutnya'), galat server dipetakan ke langkahnya,
    // payload tetap (title … success_criteria, skills[], sdg_alignment[]) dan tetap dikirim ke company.jobs.store.
    $stepNames = ['Detail Lowongan', 'Deskripsi', 'Keahlian', 'SDG Alignment', 'Kriteria Dampak'];
    $hasOld = session()->hasOldInput();
    $initial = [
        'formData' => $hasOld ? [
            'title' => (string) old('title', ''), 'department' => (string) old('department', ''),
            'location' => (string) old('location', ''), 'job_type' => (string) old('job_type', ''),
            'job_category_id' => (string) old('job_category_id', ''),
            'salary_range' => (string) old('salary_range', ''), 'description' => (string) old('description', ''),
            'responsibilities' => (string) old('responsibilities', ''), 'qualifications' => (string) old('qualifications', ''),
            'skills' => array_values((array) old('skills', [])), 'sdg_alignment' => array_values(array_map('strval', (array) old('sdg_alignment', []))),
            'impact_metrics' => (string) old('impact_metrics', ''), 'success_criteria' => (string) old('success_criteria', ''),
        ] : null,
        // galat server: kunci 'skills.0' -> 'skills'
        'errors' => collect($errors->getMessages())->mapWithKeys(fn ($messages, $key) => [\Illuminate\Support\Str::before($key, '.') => $messages[0]])->all(),
    ];
    $sdgLabels = collect($sdgOptions)->pluck('name', 'id')->all();
@endphp

<div class="sg-container sg-co-wizard" x-data="jobWizard(@js($initial), @js($jobTypes), @js($sdgLabels), @js($categories))">
    <x-ui.page-header title="Pasang lowongan" description="Lima langkah: detail, deskripsi, keahlian, SDG dan kriteria dampak."
                      :back="route('company.jobs.index')" back-label="Kembali ke Lowongan">
        <x-slot:actions>
            <x-ui.button variant="tertiary" icon="bookmark" data-sg-open="job-templates">Template</x-ui.button>
            <x-ui.button variant="tertiary" icon="eye" data-sg-open="job-preview" class="sg-co-wizard__preview-btn">Pratinjau</x-ui.button>
            <x-ui.button variant="tertiary" icon="trash-2" @click="clearDraft()">Hapus draf</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <p class="sg-co-wizard__draft" role="status">
        <span x-show="autoSaving" x-cloak>Menyimpan draf di peramban ini…</span>
        <span x-show="!autoSaving && lastSaved" x-cloak x-text="'Draf tersimpan di peramban ini pukul ' + lastSaved"></span>
    </p>

    <div class="sg-co-wizard__layout">
        <form class="sg-co-wizard__form" novalidate @submit.prevent="submitForm()">
            <x-ui.stepper :steps="$stepNames" model="currentStep + 1" label="Langkah pembuatan lowongan" class="sg-co-wizard__stepper" />

            {{-- status galat per langkah: stepper bersama belum punya keadaan galat, jadi ditulis di sini --}}
            <div class="sg-co-wizard__issues" role="alert" x-show="invalidSteps().length" x-cloak>
                <x-icon name="circle-alert" size="sm" />
                <p>Perlu diperbaiki:
                    <template x-for="(s, i) in invalidSteps()" :key="s">
                        <span><button type="button" class="sg-co-wizard__issue-link" @click="goTo(s)" x-text="'Langkah ' + (s + 1) + ' · ' + steps[s]"></button><span x-show="i < invalidSteps().length - 1">, </span></span>
                    </template>
                </p>
            </div>

            @include('company.jobs.components.wizard-steps', ['finalNote' => 'Lowongan langsung dibuka untuk mahasiswa setelah Anda menekan "Buat lowongan".'])

            <div class="sg-co-wizard__nav">
                <x-ui.button variant="secondary" icon="arrow-left" x-show="currentStep > 0" x-cloak @click="prevStep()">Sebelumnya</x-ui.button>
                <x-ui.button icon-end="arrow-right" x-show="currentStep < steps.length - 1" @click="nextStep()">Berikutnya</x-ui.button>
                <x-ui.button type="submit" icon="check" x-show="currentStep === steps.length - 1" x-cloak x-bind:disabled="submitting">Buat lowongan</x-ui.button>
            </div>
        </form>

        {{-- rail pratinjau (D-26 disetujui untuk item ini, 2026-10-31): hanya dari data formulir di peramban --}}
        <aside class="sg-co-wizard__rail" aria-label="Pratinjau lowongan">
            @include('company.jobs.components.preview', ['companyName' => $company->name ?? ''])
        </aside>
    </div>

    {{-- pratinjau pada layar < 1024px: modal yang sudah ada sebelumnya --}}
    <x-ui.modal id="job-preview" title="Pratinjau lowongan">
        @include('company.jobs.components.preview', ['companyName' => $company->name ?? ''])
    </x-ui.modal>

    {{-- template (fitur lama, localStorage peramban ini) --}}
    <x-ui.modal id="job-templates" title="Template lowongan">
        <form class="sg-co-wizard__template-save" @submit.prevent="saveAsTemplate()">
            <div class="sg-field">
                <label for="template-name" class="sg-label">Simpan isian saat ini sebagai template</label>
                <div class="sg-co-wizard__template-row">
                    <input id="template-name" type="text" class="sg-input" x-model="newTemplateName" maxlength="80" placeholder="cth: Template pendamping koperasi">
                    <x-ui.button type="submit" variant="secondary">Simpan</x-ui.button>
                </div>
                <p class="sg-help">Template hanya tersimpan di peramban ini.</p>
            </div>
        </form>
        <h3 class="sg-co-wizard__template-title">Template tersimpan</h3>
        <p class="sg-help" x-show="savedTemplates.length === 0">Belum ada template tersimpan.</p>
        <ul class="sg-list" x-show="savedTemplates.length > 0">
            <template x-for="(template, index) in savedTemplates" :key="index">
                <li>
                    <div class="sg-list__row">
                        <div class="sg-list__main">
                            <p class="sg-list__title" x-text="template.name"></p>
                            <p class="sg-list__meta" x-text="'Dibuat ' + new Date(template.createdAt).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })"></p>
                        </div>
                        <div class="sg-list__end">
                            <button type="button" class="sg-btn sg-btn--secondary" @click="loadTemplate(index)">Pakai</button>
                            <button type="button" class="sg-btn sg-btn--danger" @click="deleteTemplate(index)">Hapus</button>
                        </div>
                    </div>
                </li>
            </template>
        </ul>
    </x-ui.modal>
</div>
@endsection

@push('scripts')
<script>
function jobWizard(initial, jobTypes, sdgLabels, categories) {
    const EMPTY = () => ({
        title: '', department: '', location: '', job_type: '', job_category_id: '', salary_range: '',
        description: '', responsibilities: '', qualifications: '',
        skills: [], sdg_alignment: [], impact_metrics: '', success_criteria: '',
    });
    const required = (label) => (v) => String(v || '').trim() ? '' : label + ' wajib diisi.';
    const max = (n) => (v) => String(v || '').length > n ? 'Maksimal ' + n + ' karakter.' : '';
    // aturan sama dengan validasi server JobPostingController::store
    const RULES = {
        title: [required('Judul lowongan'), max(255)],
        department: [required('Departemen'), max(100)],
        location: [required('Lokasi'), max(255)],
        job_type: [(v) => v ? '' : 'Pilih tipe pekerjaan.', max(50)],
        job_category_id: [(v) => !v || categories[v] ? '' : 'Pilih kategori dari daftar.'],
        salary_range: [max(255)],
        description: [required('Deskripsi pekerjaan')],
        responsibilities: [required('Tanggung jawab')],
        qualifications: [required('Kualifikasi')],
        skills: [(v) => (v && v.length) ? '' : 'Pilih minimal satu keahlian.'],
    };
    const STEP_FIELDS = [
        ['title', 'department', 'job_type', 'job_category_id', 'location', 'salary_range'],
        ['description', 'responsibilities', 'qualifications'],
        ['skills'],
        ['sdg_alignment'],
        ['impact_metrics', 'success_criteria'],
    ];
    // field -> elemen yang menerima fokus saat galat
    const FIELD_TARGETS = {
        title: '#job-title', department: '#job-department', job_type: '#job-type', job_category_id: '#job-category', location: '#job-location',
        salary_range: '#job-salary', description: '#job-description', responsibilities: '#job-responsibilities',
        qualifications: '#job-qualifications', skills: '.sg-co-wizard__checks--skills input',
    };
    const store = () => { try { return window.localStorage; } catch (e) { return null; } };

    return {
        currentStep: 0,
        steps: ['Detail Lowongan', 'Deskripsi', 'Keahlian', 'SDG Alignment', 'Kriteria Dampak'],
        formData: EMPTY(),
        errors: {},
        jobTypes,
        sdgLabels,
        categories,
        submitting: false,
        autoSaving: false,
        lastSaved: '',
        autoSaveTimeout: null,
        newTemplateName: '',
        savedTemplates: [],

        init() {
            try { this.savedTemplates = JSON.parse(store()?.getItem('company_job_templates') || '[]'); } catch (e) { this.savedTemplates = []; }

            if (initial.formData) {
                // kembali dari validasi server: isian lama + galat dipetakan ke langkahnya
                this.formData = Object.assign(EMPTY(), initial.formData);
                Object.keys(initial.errors || {}).forEach((field) => {
                    this.errors[field] = this.check(field) || initial.errors[field];
                });
                const first = this.invalidSteps()[0];
                if (first !== undefined) this.currentStep = first;
            } else {
                this.loadDraft();
            }

            this.$watch('formData', () => {
                // galat hilang hanya bila isian sudah benar; galat langkah lain tetap tersimpan
                Object.keys(this.errors).forEach((field) => { if (!this.check(field)) delete this.errors[field]; });
                this.scheduleAutoSave();
            }, { deep: true });
        },

        check(field) {
            for (const rule of RULES[field] || []) {
                const message = rule(this.formData[field]);
                if (message) return message;
            }
            return '';
        },

        touch(field) {
            const message = this.check(field);
            if (message) this.errors[field] = message; else delete this.errors[field];
        },

        validateStep(step) {
            let ok = true;
            STEP_FIELDS[step].forEach((field) => {
                const message = this.check(field);
                if (message) { this.errors[field] = message; ok = false; } else delete this.errors[field];
            });
            return ok;
        },

        invalidSteps() {
            return STEP_FIELDS.map((fields, i) => fields.some((f) => this.errors[f]) ? i : null).filter((i) => i !== null);
        },

        focusStep(step, field = null) {
            this.$nextTick(() => {
                const target = field && FIELD_TARGETS[field] ? this.$root.querySelector(FIELD_TARGETS[field]) : null;
                (target || document.getElementById('langkah-' + (step + 1)))?.focus();
            });
        },

        goTo(step) {
            this.currentStep = step;
            const field = STEP_FIELDS[step].find((f) => this.errors[f]);
            this.focusStep(step, field);
        },

        nextStep() {
            if (!this.validateStep(this.currentStep)) {
                this.focusStep(this.currentStep, STEP_FIELDS[this.currentStep].find((f) => this.errors[f]));
                return;
            }
            if (this.currentStep < this.steps.length - 1) {
                this.currentStep++;
                this.focusStep(this.currentStep);
            }
        },

        prevStep() {
            if (this.currentStep > 0) {
                this.currentStep--;
                this.focusStep(this.currentStep);
            }
        },

        categoryLabel() {
            return this.categories[this.formData.job_category_id] || '';
        },

        jobTypeLabel() {
            return this.jobTypes[this.formData.job_type] || this.formData.job_type;
        },

        previewParts() {
            return [
                ['Deskripsi', this.formData.description], ['Tanggung jawab', this.formData.responsibilities],
                ['Kualifikasi', this.formData.qualifications], ['Metrik dampak', this.formData.impact_metrics],
                ['Kriteria keberhasilan', this.formData.success_criteria],
            ].filter(([, text]) => String(text).trim()).map(([label, text]) => ({ label, text }));
        },

        // draf otomatis (fitur lama, localStorage peramban ini)
        scheduleAutoSave() {
            clearTimeout(this.autoSaveTimeout);
            this.autoSaveTimeout = setTimeout(() => this.autoSaveDraft(), 2000);
        },

        autoSaveDraft() {
            this.autoSaving = true;
            try {
                store()?.setItem('company_job_draft', JSON.stringify({ formData: this.formData, currentStep: this.currentStep, savedAt: new Date().toISOString() }));
                this.lastSaved = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            } catch (error) {
                console.error('Error auto-saving draft:', error);
            }
            setTimeout(() => { this.autoSaving = false; }, 500);
        },

        async loadDraft() {
            let parsed = null;
            try { parsed = JSON.parse(store()?.getItem('company_job_draft') || 'null'); } catch (e) { parsed = null; }
            if (!parsed || !parsed.formData) return;
            const ok = await window.sgConfirm({
                title: 'Lanjutkan draf?',
                message: 'Ada draf lowongan yang tersimpan di peramban ini.',
                confirmLabel: 'Lanjutkan draf', cancelLabel: 'Mulai baru', tone: 'primary',
            });
            if (!ok) return;
            this.formData = Object.assign(EMPTY(), parsed.formData);
            this.currentStep = Math.min(Math.max(parseInt(parsed.currentStep, 10) || 0, 0), 4);
            this.lastSaved = new Date(parsed.savedAt).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        },

        async clearDraft() {
            const ok = await window.sgConfirm({ title: 'Hapus draf?', message: 'Semua isian dihapus dan Anda mulai dari langkah 1.', confirmLabel: 'Hapus draf' });
            if (!ok) return;
            try { store()?.removeItem('company_job_draft'); } catch (e) {}
            this.formData = EMPTY();
            this.errors = {};
            this.currentStep = 0;
            this.lastSaved = '';
            window.showNotification('Draf dihapus', 'info');
        },

        // template (fitur lama, localStorage peramban ini)
        saveAsTemplate() {
            if (!this.newTemplateName.trim()) {
                window.showNotification('Tulis nama template', 'warning');
                return;
            }
            this.savedTemplates.push({ name: this.newTemplateName.trim(), data: JSON.parse(JSON.stringify(this.formData)), createdAt: new Date().toISOString() });
            try { store()?.setItem('company_job_templates', JSON.stringify(this.savedTemplates)); } catch (e) {}
            this.newTemplateName = '';
            window.showNotification('Template disimpan', 'success');
        },

        async loadTemplate(index) {
            const template = this.savedTemplates[index];
            if (!template) return;
            const ok = await window.sgConfirm({ title: 'Pakai template?', message: 'Isian formulir saat ini diganti dengan template "' + template.name + '".', confirmLabel: 'Pakai template', tone: 'primary' });
            if (!ok) return;
            this.formData = Object.assign(EMPTY(), JSON.parse(JSON.stringify(template.data)));
            document.getElementById('job-templates')?.close();
            window.showNotification('Template dipakai: ' + template.name, 'success');
        },

        async deleteTemplate(index) {
            const ok = await window.sgConfirm({ title: 'Hapus template?', message: 'Template "' + (this.savedTemplates[index]?.name || '') + '" dihapus dari peramban ini.', confirmLabel: 'Hapus' });
            if (!ok) return;
            this.savedTemplates.splice(index, 1);
            try { store()?.setItem('company_job_templates', JSON.stringify(this.savedTemplates)); } catch (e) {}
            window.showNotification('Template dihapus', 'info');
        },

        submitForm() {
            // semua langkah diperiksa; langkah pertama yang bermasalah dibuka
            const bad = [0, 1, 2, 3, 4].filter((step) => !this.validateStep(step));
            if (bad.length) {
                this.goTo(bad[0]);
                return;
            }
            this.submitting = true;

            // payload sama dengan sebelumnya: _token + setiap kunci formData (array -> key[])
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = @js(route('company.jobs.store'));
            const add = (name, value) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = value;
                form.appendChild(input);
            };
            add('_token', document.querySelector('meta[name="csrf-token"]').content);
            for (const [key, value] of Object.entries(this.formData)) {
                if (Array.isArray(value)) value.forEach((v) => add(`${key}[]`, v));
                else add(key, value);
            }
            document.body.appendChild(form);
            form.submit();
        },
    };
}
</script>
@endpush
