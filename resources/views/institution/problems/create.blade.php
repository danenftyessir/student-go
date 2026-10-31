@extends('layouts.app')

@section('title', 'Buat Masalah Baru')

@section('content')
<div class="sg-container sg-inst-page">
    <x-ui.page-header title="Buat masalah baru"
                      description="Susun brief masalah KKN untuk mahasiswa. Simpan sebagai draf bila belum siap dipublikasikan."
                      :back="route('institution.problems.index')" back-label="Kembali ke daftar masalah" />

    <x-ui.form-errors class="sg-inst-page__errors" />

    {{-- action, method, enctype dan id formulir sama seperti sebelumnya --}}
    <form action="{{ route('institution.problems.store') }}" method="POST" enctype="multipart/form-data" id="problemForm">
        @csrf
        @include('institution.problems.components.brief-form', [
            'problem' => null,
            'provinces' => $provinces,
            'regencies' => $regencies ?? collect(),
            'cancelHref' => route('institution.problems.index'),
        ])
    </form>
</div>
@endsection
