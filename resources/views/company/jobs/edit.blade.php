@extends('layouts.app')

@section('title', 'Ubah Lowongan')

@section('content')
@php
    // phase 29 paket E: form ubah = wizard create phase 23 (5 langkah yang sama, stepper bersama, validasi per langkah sesuai
    // aturan server update, galat server dipetakan ke langkahnya, rail pratinjau bersama). tanpa foto hero lama.
    // payload tetap: _token, _method=PUT, title … success_criteria, status, skills[], sdg_alignment[] ke company.jobs.update.
    $stepNames = ['Detail Lowongan', 'Deskripsi', 'Keahlian', 'SDG Alignment', 'Kriteria Dampak'];
    $asArray = fn ($value) => is_array($value) ? $value : (is_string($value) ? (json_decode($value, true) ?: []) : []);
    $storedSkills = array_values(array_filter($asArray($jobPosting->skills), fn ($skill) => is_string($skill) && $skill !== ''));

    // gaji tersimpan (tidak diubah lewat form ini), format sama dengan halaman rekaman lowongan
    $fmt = fn ($n) => number_format((float) $n, 0, ',', '.');
    $money = fn ($amount) => ($jobPosting->salary_currency === 'USD' ? 'US$ ' : 'Rp ') . $fmt($amount);
    $salary = null;
    if ($jobPosting->salary_min || $jobPosting->salary_max) {
        $salary = $jobPosting->salary_min && $jobPosting->salary_max && (float) $jobPosting->salary_min !== (float) $jobPosting->salary_max
            ? $money($jobPosting->salary_min) . ' – ' . $money($jobPosting->salary_max)
            : $money($jobPosting->salary_min ?: $jobPosting->salary_max);
        $salary .= ['monthly' => ' per bulan', 'project' => ' per proyek'][$jobPosting->salary_period] ?? '';
    }

    $hasOld = session()->hasOldInput();
    $value = fn ($key, $stored) => $hasOld ? old($key, '') : $stored;
    $formData = [
        'title' => (string) $value('title', $jobPosting->title), 'department' => (string) $value('department', $jobPosting->department),
        'location' => (string) $value('location', $jobPosting->location), 'job_type' => (string) $value('job_type', $jobPosting->job_type),
        'status' => (string) $value('status', $jobPosting->status ?? 'draft'), 'job_category_id' => (string) $value('job_category_id', $jobPosting->job_category_id),
        // kunci salary_range tetap dikirim seperti sebelumnya; update() tidak memakainya (gaji tersimpan tidak berubah).
        // nilainya teks gaji bahasa indonesia untuk pratinjau, bukan accessor model ('Negotiable', 'IDR2000000.00 - …')
        'salary_range' => (string) $value('salary_range', $salary ?? ''),
        'description' => (string) $value('description', $jobPosting->description), 'responsibilities' => (string) $value('responsibilities', $jobPosting->responsibilities),
        'qualifications' => (string) $value('qualifications', $jobPosting->qualifications),
        'skills' => $hasOld ? array_values((array) old('skills', [])) : $storedSkills,
        'sdg_alignment' => array_values(array_map('strval', $hasOld ? (array) old('sdg_alignment', []) : $asArray($jobPosting->sdg_alignment))),
        'impact_metrics' => (string) $value('impact_metrics', $jobPosting->impact_metrics), 'success_criteria' => (string) $value('success_criteria', $jobPosting->success_criteria),
    ];
    $initial = [
        'formData' => $formData,
        // galat server: kunci 'skills.0' -> 'skills'
        'errors' => collect($errors->getMessages())->mapWithKeys(fn ($messages, $key) => [\Illuminate\Support\Str::before($key, '.') => $messages[0]])->all(),
    ];

    // nilai tipe tersimpan di luar daftar (mis. 'internship') tetap menjadi pilihan dengan label yang cocok
    $typeOptions = $jobTypes;
    if ($formData['job_type'] !== '' && ! array_key_exists($formData['job_type'], $typeOptions)) {
        $match = collect($jobTypes)->first(fn ($label, $key) => strcasecmp($key, $formData['job_type']) === 0);
        $typeOptions = [$formData['job_type'] => $match ?? $formData['job_type']] + $typeOptions;
    }

    // status: nilai yang diterima server; label dari StatusVocabulary 'job_posting'
    $statusValues = ['draft', 'posted', 'closed'];
    if (! in_array($formData['status'], $statusValues, true) && $formData['status'] !== '') {
        $statusValues[] = $formData['status'];
    }
    $statusOptions = collect($statusValues)->mapWithKeys(fn ($status) => [$status => \App\Support\StatusVocabulary::label('job_posting', $status)])->all();

    // keahlian tersimpan yang tidak ada di daftar tetap tampil (dan tetap terkirim) di depan daftar
    $skillOptions = array_values(array_unique(array_merge(array_diff($formData['skills'], $availableSkills), $availableSkills)));

    $sdgLabels = collect($sdgOptions)->pluck('name', 'id')->all();
@endphp

<div class="sg-container sg-co-wizard" x-data="jobEditWizard(@js($initial), @js($typeOptions), @js($sdgLabels), @js($categories))">
    <x-ui.page-header title="Ubah lowongan" :description="$jobPosting->title"
                      :back="route('company.jobs.show', $jobPosting->id)" back-label="Kembali ke rekaman lowongan">
        <x-slot:actions>
            <x-ui.button variant="tertiary" icon="eye" data-sg-open="job-preview" class="sg-co-wizard__preview-btn">Pratinjau</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="sg-co-wizard__layout">
        <form class="sg-co-wizard__form" novalidate @submit.prevent="submitForm()">
            @csrf
            @method('PUT')
            <x-ui.stepper :steps="$stepNames" model="currentStep + 1" label="Langkah pengubahan lowongan" class="sg-co-wizard__stepper" />

            {{-- status galat per langkah (sama dengan wizard create) --}}
            <div class="sg-co-wizard__issues" role="alert" x-show="invalidSteps().length" x-cloak>
                <x-icon name="circle-alert" size="sm" />
                <p>Perlu diperbaiki:
                    <template x-for="(s, i) in invalidSteps()" :key="s">
                        <span><button type="button" class="sg-co-wizard__issue-link" @click="goTo(s)" x-text="'Langkah ' + (s + 1) + ' · ' + steps[s]"></button><span x-show="i < invalidSteps().length - 1">, </span></span>
                    </template>
                </p>
            </div>

            @include('company.jobs.components.wizard-steps', ['jobTypes' => $typeOptions, 'availableSkills' => $skillOptions, 'statusOptions' => $statusOptions,
                'editing' => true, 'salaryText' => $salary, 'finalNote' => 'Perubahan disimpan setelah Anda menekan "Simpan perubahan".'])

            <div class="sg-co-wizard__nav">
                <x-ui.button variant="tertiary" :href="route('company.jobs.index')" class="sg-co-wizard__cancel">Batal</x-ui.button>
                <x-ui.button variant="secondary" icon="arrow-left" x-show="currentStep > 0" x-cloak @click="prevStep()">Sebelumnya</x-ui.button>
                <x-ui.button icon-end="arrow-right" x-show="currentStep < steps.length - 1" @click="nextStep()">Berikutnya</x-ui.button>
                <x-ui.button type="submit" icon="check" x-show="currentStep === steps.length - 1" x-cloak x-bind:disabled="submitting">Simpan perubahan</x-ui.button>
            </div>
        </form>

        {{-- rail pratinjau bersama (D-26): hanya dari data formulir di peramban --}}
        <aside class="sg-co-wizard__rail" aria-label="Pratinjau lowongan">
            @include('company.jobs.components.preview', ['companyName' => $company->name ?? '', 'note' => 'Disusun dari isian formulir di peramban ini. Lowongan tersimpan tidak berubah sampai Anda menekan "Simpan perubahan".'])
        </aside>
    </div>

    {{-- pratinjau pada layar < 1024px --}}
    <x-ui.modal id="job-preview" title="Pratinjau lowongan">
        @include('company.jobs.components.preview', ['companyName' => $company->name ?? '', 'note' => 'Disusun dari isian formulir di peramban ini. Lowongan tersimpan tidak berubah sampai Anda menekan "Simpan perubahan".'])
    </x-ui.modal>
</div>
@endsection

@push('scripts')
<script>
function jobEditWizard(initial, jobTypes, sdgLabels, categories) {
    const EMPTY = () => ({
        title: '', department: '', location: '', job_type: '', status: '', job_category_id: '', salary_range: '',
        description: '', responsibilities: '', qualifications: '',
        skills: [], sdg_alignment: [], impact_metrics: '', success_criteria: '',
    });
    const required = (label) => (v) => String(v || '').trim() ? '' : label + ' wajib diisi.';
    const max = (n) => (v) => String(v || '').length > n ? 'Maksimal ' + n + ' karakter.' : '';
    // aturan sama dengan validasi server JobPostingController::update
    const RULES = {
        title: [required('Judul lowongan'), max(255)],
        department: [required('Departemen'), max(100)],
        location: [required('Lokasi'), max(255)],
        job_type: [(v) => v ? '' : 'Pilih tipe pekerjaan.', max(50)],
        status: [(v) => !v || ['draft', 'posted', 'closed', 'archived'].includes(v) ? '' : 'Pilih status dari daftar.'],
        job_category_id: [(v) => !v || categories[v] ? '' : 'Pilih kategori dari daftar.'],
        description: [required('Deskripsi pekerjaan')],
        responsibilities: [required('Tanggung jawab')],
        qualifications: [required('Kualifikasi')],
        skills: [(v) => (v && v.length) ? '' : 'Pilih minimal satu keahlian.'],
    };
    const STEP_FIELDS = [
        ['title', 'department', 'job_type', 'status', 'job_category_id', 'location', 'salary_range'],
        ['description', 'responsibilities', 'qualifications'],
        ['skills'],
        ['sdg_alignment'],
        ['impact_metrics', 'success_criteria'],
    ];
    // field -> elemen yang menerima fokus saat galat
    const FIELD_TARGETS = {
        title: '#job-title', department: '#job-department', job_type: '#job-type', status: '#job-status', job_category_id: '#job-category',
        location: '#job-location', description: '#job-description', responsibilities: '#job-responsibilities',
        qualifications: '#job-qualifications', skills: '.sg-co-wizard__checks--skills input',
    };

    return {
        currentStep: 0,
        steps: ['Detail Lowongan', 'Deskripsi', 'Keahlian', 'SDG Alignment', 'Kriteria Dampak'],
        formData: Object.assign(EMPTY(), initial.formData),
        errors: {},
        jobTypes,
        sdgLabels,
        categories,
        submitting: false,

        init() {
            // kembali dari validasi server: galat dipetakan ke langkahnya
            Object.keys(initial.errors || {}).forEach((field) => {
                this.errors[field] = this.check(field) || initial.errors[field];
            });
            const first = this.invalidSteps()[0];
            if (first !== undefined) this.currentStep = first;

            this.$watch('formData', () => {
                // galat hilang hanya bila isian sudah benar; galat langkah lain tetap tersimpan
                Object.keys(this.errors).forEach((field) => { if (!this.check(field)) delete this.errors[field]; });
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

        submitForm() {
            // semua langkah diperiksa; langkah pertama yang bermasalah dibuka
            const bad = [0, 1, 2, 3, 4].filter((step) => !this.validateStep(step));
            if (bad.length) {
                this.goTo(bad[0]);
                return;
            }
            this.submitting = true;

            // payload sama dengan sebelumnya: _token, _method=PUT + setiap kunci formData (array -> key[])
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = @js(route('company.jobs.update', $jobPosting->id));
            const add = (name, value) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = value;
                form.appendChild(input);
            };
            add('_token', document.querySelector('meta[name="csrf-token"]').content);
            add('_method', 'PUT');
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
