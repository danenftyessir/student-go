{{-- stepper tunggal (registrasi mahasiswa/instansi/perusahaan, wizard lowongan). steps = ['Akun', 'Data diri', …].
     current = langkah (mulai 1) dari server; ATAU model = ekspresi alpine yang sudah ada di halaman (mis. 'step' / 'currentStep')
     sehingga logika stepper halaman tidak berubah. teks "Langkah 2 dari 4 · Label" diumumkan saat berganti. --}}
@props(['steps' => [], 'current' => 1, 'model' => null, 'label' => 'Kemajuan pengisian'])
@php
    $total = count($steps);
    $current = max(1, min((int) $current, max(1, $total)));
@endphp
<div {{ $attributes->class('sg-stepper') }} role="group" aria-label="{{ $label }}">
    <p class="sg-stepper__label" aria-live="polite"
       @if ($model) x-text="'Langkah ' + ({{ $model }}) + ' dari {{ $total }} · ' + (@js($steps)[({{ $model }}) - 1] ?? '')" @endif>
        Langkah {{ $current }} dari {{ $total }} · {{ $steps[$current - 1] ?? '' }}
    </p>
    <ol class="sg-stepper__track" aria-hidden="true">
        @foreach ($steps as $i => $step)
            @php $n = $i + 1; @endphp
            <li @if ($n < $current) data-state="done" @endif @if ($n === $current) aria-current="step" @endif
                @if ($model) :data-state="({{ $model }}) > {{ $n }} ? 'done' : null" :aria-current="({{ $model }}) === {{ $n }} ? 'step' : null" @endif></li>
        @endforeach
    </ol>
    <ol class="sg-stepper__labels">
        @foreach ($steps as $i => $step)
            @php $n = $i + 1; @endphp
            <li @if ($n === $current) aria-current="step" @endif
                @if ($model) :aria-current="({{ $model }}) === {{ $n }} ? 'step' : null" @endif>{{ $step }}</li>
        @endforeach
    </ol>
</div>
