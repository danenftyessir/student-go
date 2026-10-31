@extends('layouts.app')

@section('title', 'Ubah Masalah')

@section('content')
<div class="sg-container sg-inst-page">
    <x-ui.page-header title="Ubah masalah" :description="$problem->title"
                      :back="route('institution.problems.show', $problem->id)" back-label="Kembali ke detail masalah" />

    <x-ui.form-errors class="sg-inst-page__errors" />

    {{-- action, method (PUT), enctype sama seperti sebelumnya --}}
    <form action="{{ route('institution.problems.update', $problem->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('institution.problems.components.brief-form', [
            'problem' => $problem,
            'provinces' => $provinces,
            'regencies' => $regencies,
            'cancelHref' => route('institution.problems.show', $problem->id),
        ])
    </form>
</div>
@endsection
