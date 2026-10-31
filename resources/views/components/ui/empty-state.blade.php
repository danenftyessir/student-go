{{-- keadaan kosong: ilustrasi (5 svg asli student go), judul, satu kalimat, satu aksi (slot action) sesuai peran.
     illustration: no-applications | no-saved-projects | no-documents | no-notifications | no-search-results | null --}}
@props(['title', 'description' => null, 'illustration' => null, 'headingLevel' => 2])
@php
    $known = ['no-applications', 'no-saved-projects', 'no-documents', 'no-notifications', 'no-search-results'];
@endphp
<div {{ $attributes->class('sg-empty') }}>
    @if (in_array($illustration, $known, true))
        <img src="{{ asset('assets/illustrations/empty-states/'.$illustration.'.svg') }}" alt="" width="160" height="120">
    @endif
    <h{{ $headingLevel }}>{{ $title }}</h{{ $headingLevel }}>
    @if ($description)<p>{{ $description }}</p>@endif
    {{ $slot }}
    @isset($action)
        {{ $action }}
    @endisset
</div>
