{{-- ringkasan sebaris pengganti kartu statistik: "2 lamaran · 1 menunggu · 1 ditolak".
     items = [['value' => int|null, 'label' => 'lamaran'], …]; item dengan value null tidak ditampilkan.
     angka HARUS dari query nyata (guardrail 2); komponen ini tidak menghitung apa pun. --}}
@props(['items' => [], 'label' => 'Ringkasan'])
@php
    $visible = array_values(array_filter($items, fn ($item) => isset($item['value']) && $item['value'] !== null));
@endphp
@if (count($visible))
    <ul {{ $attributes->class('sg-summary') }} aria-label="{{ $label }}">
        @foreach ($visible as $item)
            <li><b>{{ is_numeric($item['value']) ? number_format((float) $item['value'], 0, ',', '.') : $item['value'] }}</b> {{ $item['label'] }}</li>
        @endforeach
    </ul>
@endif
