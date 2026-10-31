{{-- resources/views/student/applications/create.blade.php --}}
{{-- form untuk apply ke proyek --}}

@extends('layouts.app')

@section('title', 'Ajukan Lamaran')

@section('content')
@php
    // phase 10: formulir satu kolom + rel fakta masalah. kontrak tetap: POST student.applications.store, multipart,
    // problem_id (hidden), motivation (min. 100 karakter, aturan server), proposal (opsional, pdf/doc/docx, maks. 5 MB).
    $institution = $problem->institution;
    $tanggal = fn ($date) => $date ? $date->locale('id')->translatedFormat('j M Y') : null;
    $bulan = fn ($date) => $date ? $date->locale('id')->translatedFormat('M Y') : null;
    $lokasi = collect([
        $problem->regency->name ?? $problem->location_regency,
        $problem->province->name ?? $problem->location_province,
    ])->filter()->implode(', ');
    $periode = $problem->start_date && $problem->end_date ? $bulan($problem->start_date).' – '.$bulan($problem->end_date) : null;
    $detail = route('student.browse-problems.show', $problem->id);
    $motivationError = $errors->first('motivation');
@endphp

<div class="sg-container pb-16">
    <x-ui.page-header title="Ajukan lamaran" :description="$problem->title.' · '.$institution->name"
                      :back="$detail" back-label="Kembali ke detail masalah" class="[&_p]:wrap-break-word" />

    <x-ui.form-errors class="mb-6 max-w-2xl" />

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-[minmax(0,1fr)_20rem] lg:gap-12">
        <form action="{{ route('student.applications.store') }}" method="POST" enctype="multipart/form-data"
              class="grid max-w-2xl min-w-0 content-start gap-6" novalidate
              x-data="applicationForm({ touched: @js($motivationError !== '') })" @submit="submit($event)">
            @csrf

            {{-- hidden problem id --}}
            <input type="hidden" name="problem_id" value="{{ $problem->id }}">

            <div class="sg-field">
                <label for="motivation" class="sg-label" data-required>Motivasi</label>
                <p class="sg-help m-0" id="motivation-help">Ceritakan mengapa Anda tertarik dengan masalah ini dan apa yang bisa Anda kontribusikan. Minimal 100 karakter.</p>
                <x-ui.textarea name="motivation" rows="8" required x-ref="motivation" aria-describedby="motivation-help motivation-count"
                               placeholder="Contoh: Saya pernah mendampingi kelompok tani di desa asal saya, sehingga…"
                               x-on:input="count = $el.value.length; server = false" x-on:blur="touched = true"
                               x-bind:aria-invalid="invalid ? 'true' : null" x-bind:data-touched="touched ? '' : null" />
                {{-- penghitung netral sampai bidang disentuh (blur) atau formulir dikirim --}}
                <p id="motivation-count" class="m-0 text-xs text-gray-500 tabular-nums" aria-live="polite">
                    <span x-text="count.toLocaleString('id-ID') + ' karakter'">Minimal 100 karakter</span><span x-show="count < 100" x-cloak x-text="' · kurang ' + (100 - count) + ' lagi'"></span>
                </p>
                @if ($motivationError)
                    <p class="sg-error" id="motivation-error" x-show="server" x-cloak><x-icon name="circle-alert" size="sm" />{{ $motivationError }}</p>
                @endif
                <p class="sg-error" id="motivation-client-error" x-show="invalid && !server" x-cloak role="alert"><x-icon name="circle-alert" size="sm" /><span>Motivasi minimal 100 karakter. Tambahkan <span x-text="100 - count"></span> karakter lagi.</span></p>
            </div>

            <x-ui.upload name="proposal" id="proposal" label="Proposal (opsional)" accept=".pdf,.doc,.docx"
                         hint="PDF, DOC atau DOCX, maks. 5 MB" :max-size="5 * 1024 * 1024" />

            <div class="grid gap-3 border-t border-gray-200 pt-6">
                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="sg-btn sg-btn--primary" :disabled="submitting" :aria-busy="submitting ? 'true' : null">
                        <span class="sg-spinner" x-show="submitting" x-cloak aria-hidden="true"></span>
                        <span x-text="submitting ? 'Mengirim…' : 'Kirim lamaran'">Kirim lamaran</span>
                    </button>
                    <x-ui.button variant="secondary" :href="$detail">Batal</x-ui.button>
                </div>
                <p class="m-0 text-xs text-gray-500">Dengan mengirim lamaran, Anda menyatakan bahwa informasi yang diberikan benar dan dapat dipertanggungjawabkan.</p>
            </div>
        </form>

        <aside class="min-w-0 lg:sticky lg:top-20 lg:self-start" aria-labelledby="ringkasan-masalah">
            <div class="grid gap-4 rounded-lg border border-gray-200 bg-white p-5">
                <div class="grid gap-2">
                    <h2 id="ringkasan-masalah" class="m-0 text-base font-semibold text-gray-900 wrap-break-word">
                        <a href="{{ $detail }}" class="text-gray-900 no-underline hover:text-brand-700 hover:underline">{{ $problem->title }}</a>
                    </h2>
                    <div class="flex items-center gap-2">
                        <x-ui.avatar :src="$institution->logo_path ? $institution->getLogoUrl() : null" :name="$institution->name" size="sm" square decorative />
                        <p class="m-0 min-w-0 text-sm text-gray-600 wrap-break-word">{{ $institution->name }}</p>
                    </div>
                </div>

                <x-ui.dl class="grid-cols-2 border-t border-gray-200 pt-4 lg:grid-cols-1">
                    @if ($lokasi !== '')
                        <div><dt>Lokasi</dt><dd>{{ $lokasi }}</dd></div>
                    @endif
                    @if ($problem->required_students)
                        <div><dt>Mahasiswa dibutuhkan</dt><dd class="tabular-nums">{{ $problem->required_students }} orang</dd></div>
                    @endif
                    <div><dt>Sudah melamar</dt><dd class="tabular-nums">{{ (int) $problem->applications_count }} lamaran</dd></div>
                    @if ($problem->duration_months)
                        <div><dt>Durasi</dt><dd>{{ $problem->duration_months }} bulan</dd></div>
                    @endif
                    @if ($periode)
                        <div><dt>Periode</dt><dd>{{ $periode }}</dd></div>
                    @endif
                    <div>
                        <dt>Tenggat pendaftaran</dt>
                        <dd class="tabular-nums">{{ $tanggal($problem->application_deadline) }}
                            <span class="block text-xs text-gray-500">{{ $problem->application_deadline->locale('id')->diffForHumans() }}</span>
                        </dd>
                    </div>
                </x-ui.dl>
            </div>

            <div class="mt-4 px-1">
                <h2 class="mt-0 mb-2 text-sm font-semibold text-gray-900">Agar motivasi mudah dinilai</h2>
                <ul class="m-0 grid list-none gap-2 p-0 text-sm text-gray-600">
                    <li class="flex gap-2"><x-icon name="check" size="sm" class="mt-0.5 flex-none text-gray-500" /><span>Sebutkan pengalaman atau keahlian yang relevan dengan masalah ini.</span></li>
                    <li class="flex gap-2"><x-icon name="check" size="sm" class="mt-0.5 flex-none text-gray-500" /><span>Hubungkan keahlian Anda dengan kebutuhan instansi.</span></li>
                    <li class="flex gap-2"><x-icon name="check" size="sm" class="mt-0.5 flex-none text-gray-500" /><span>Tawarkan satu ide konkret yang ingin Anda kerjakan.</span></li>
                    <li class="flex gap-2"><x-icon name="check" size="sm" class="mt-0.5 flex-none text-gray-500" /><span>Proposal bersifat opsional, tetapi membantu instansi memahami rencana Anda.</span></li>
                </ul>
            </div>
        </aside>
    </div>
</div>

@push('scripts')
<script>
// formulir lamaran: penghitung netral sampai disentuh; galat hanya setelah blur atau kirim
function applicationForm({ touched = false } = {}) {
    return {
        count: 0,
        touched: touched,
        server: touched,
        submitting: false,

        init() {
            this.count = this.$refs.motivation.value.length;
        },

        get invalid() {
            return this.touched && this.count < 100;
        },

        submit(event) {
            // validasi minimal karakter motivasi (aturan server tetap berlaku)
            this.count = this.$refs.motivation.value.length;
            if (this.count < 100) {
                event.preventDefault();
                this.touched = true;
                this.server = false;
                this.$refs.motivation.focus();
                return;
            }
            this.submitting = true;
        },
    };
}
</script>
@endpush
@endsection
