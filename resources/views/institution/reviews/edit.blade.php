@extends('layouts.app')

@section('title', 'Ubah Ulasan')

@section('content')
@php
    // phase 19: ubah ulasan (controller hanya mengizinkan 30 hari pertama). PUT ke institution.reviews.update; nama field sama.
    $student = $review->reviewee?->student;
@endphp

<div class="sg-container sg-inst-page">
    <x-ui.page-header :title="'Ubah ulasan untuk '.($review->reviewee?->display_name ?? 'mahasiswa')"
                      :description="collect([$review->project?->problem?->title ?? $review->project?->title, $student?->university?->name])->filter()->implode(' · ')"
                      :back="route('institution.reviews.show', $review->id)" back-label="Kembali ke ulasan" />

    <x-ui.form-errors class="sg-inst-page__errors" />

    <form action="{{ route('institution.reviews.update', $review->id) }}" method="POST">
        @csrf
        @method('PUT')
        @include('institution.reviews.components.form', [
            'review' => $review,
            'submitLabel' => 'Simpan perubahan',
            'cancelHref' => route('institution.reviews.show', $review->id),
        ])
    </form>
</div>
@endsection
