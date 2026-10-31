@extends('layouts.app')

@section('title', 'Tulis Ulasan')

@section('content')
@php
    // phase 19: GET /institution/reviews/create/{projectId} (hanya proyek selesai yang belum diulas; aturan di controller).
    // POST ke institution.reviews.store; project_id dikirim sebagai input tersembunyi (disetujui user: tanpa field ini
    // store() selalu 404). nama field lain sama seperti sebelumnya.
    $student = $project->student;
@endphp

<div class="sg-container sg-inst-page">
    <x-ui.page-header :title="'Ulasan untuk '.$student?->display_name"
                      :description="collect([$project->title, $student?->university?->name])->filter()->implode(' · ')"
                      :back="route('institution.projects.show', $project->id)" back-label="Kembali ke detail proyek" />

    <x-ui.form-errors class="sg-inst-page__errors" />

    <form action="{{ route('institution.reviews.store') }}" method="POST">
        @csrf
        <input type="hidden" name="project_id" value="{{ $project->id }}">
        @include('institution.reviews.components.form', [
            'review' => null,
            'submitLabel' => 'Kirim ulasan',
            'cancelHref' => route('institution.projects.show', $project->id),
        ])
    </form>
</div>
@endsection
