{{-- badge status: satu kata + satu ikon + satu nada dari App\Support\StatusVocabulary (D-02). warna tidak pernah sendirian.
     domain: application | job_application | problem | project | milestone | report | document | verification | job_posting | friend
     value: nilai tersimpan di database (mis. 'pending'). --}}
@props(['domain', 'value'])
@php
    $status = \App\Support\StatusVocabulary::resolve($domain, $value);
@endphp
<span {{ $attributes->class('sg-status') }} @if ($status['tone'] !== 'neutral') data-tone="{{ $status['tone'] }}" @endif data-value="{{ $value }}">
    <x-icon :name="$status['icon']" size="sm" />{{ $status['label'] }}
</span>
