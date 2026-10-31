@extends('layouts.app')

@section('title', 'Lamaran '.$application->student?->display_name)

@section('content')
@php
    // phase 19: halaman detail satu lamaran (dipakai di bawah 1024px dan sebagai tujuan redirect setelah keputusan).
    // tautan kembali membawa filter daftar bila pengguna datang dari sana.
    $index = route('institution.applications.index');
    $previous = url()->previous();
    $back = str_starts_with($previous, $index) && ! str_contains($previous, '/applications/') ? $previous : $index;
    $subline = collect([
        $application->student?->university?->name,
        $application->student?->major,
    ])->filter()->implode(' · ');
@endphp

<div class="sg-container sg-inst-page">
    <x-ui.page-header :title="$application->student?->display_name ?? 'Lamaran'" :description="$subline ?: null"
                      :back="$back" back-label="Kembali ke daftar pelamar" />

    <x-ui.form-errors class="sg-inst-page__errors" />

    @include('institution.applications.components.detail', ['application' => $application, 'context' => 'page'])
</div>
@endsection
