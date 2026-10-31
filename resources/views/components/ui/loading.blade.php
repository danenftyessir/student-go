{{-- pemuatan: inline (spinner + teks, role=status) atau kerangka statis (lines > 0, tanpa animasi kilau) --}}
@props(['label' => 'Memuat…', 'lines' => 0])
@if ($lines > 0)
    <div {{ $attributes->class('grid gap-2') }} role="status" aria-label="{{ $label }}">
        <span class="sg-skeleton sg-skeleton--title"></span>
        @for ($i = 0; $i < $lines; $i++)
            <span class="sg-skeleton" @style(['width: '.(100 - ($i % 3) * 15).'%'])></span>
        @endfor
    </div>
@else
    <span {{ $attributes->class('sg-loading') }} role="status"><span class="sg-spinner" aria-hidden="true"></span>{{ $label }}</span>
@endif
