@extends('layouts.app')

@section('title', 'Tinjau Lamaran '.$application->student?->display_name)

@section('content')
@php
    // phase 19: GET /applications/{id}/review tetap ada (controller hanya membukanya untuk lamaran menunggu/ditinjau).
    // isi lamaran + dua formulir keputusan di halaman (tanpa dialog); action, method dan nama field sama seperti sebelumnya.
    $subline = collect([
        $application->student?->university?->name,
        $application->student?->major,
    ])->filter()->implode(' · ');
@endphp

<div class="sg-container sg-inst-page">
    <x-ui.page-header :title="'Tinjau lamaran '.$application->student?->display_name" :description="$subline ?: null"
                      :back="route('institution.applications.show', $application->id)" back-label="Kembali ke detail lamaran" />

    <x-ui.form-errors class="sg-inst-page__errors" />

    @include('institution.applications.components.detail', ['application' => $application, 'context' => 'inline'])

    <div class="sg-inst-detail__main sg-inst-page__narrow">
        @include('institution.applications.components.decision-forms', ['application' => $application, 'modal' => false])
    </div>
</div>
@endsection
