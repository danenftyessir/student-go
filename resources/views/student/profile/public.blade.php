{{-- resources/views/student/profile/public.blade.php --}}
@extends('layouts.app')

{{-- phase 12: portofolio publik dengan susunan yang sama seperti profil sendiri (components/portfolio), tanpa aksi pemilik.
     kontak hanya bila $canViewContact (D-04). judul, deskripsi dan open graph dari data nyata; tanpa og:image karena
     foto mahasiswa tidak diberi tempat publik baru (D-31). --}}
@php
    $name = $student->display_name;
    $shareUrl = route('profile.public', $student->user->username);
    $visibleCount = $completed_projects->count();
    $metaDescription = \Illuminate\Support\Str::limit(trim(implode(' ', array_filter([
        'Portofolio KKN ' . $name . (filled($student->major) ? ', ' . $student->major : '') . (filled($student->university->name ?? null) ? ' di ' . $student->university->name : '') . '.',
        $visibleCount > 0 ? $visibleCount . ' proyek selesai.' : null,
        filled($student->bio) ? $student->bio : null,
    ]))), 160);
    $summary = [
        ['value' => $visibleCount ?: null, 'label' => 'proyek selesai'],
        ['value' => $statistics['sdgs_addressed'] ?: null, 'label' => 'SDG disentuh'],
        ['value' => $statistics['average_rating'] > 0 ? number_format((float) $statistics['average_rating'], 1, ',', '.') : null, 'label' => 'rata-rata nilai instansi'],
    ];
@endphp

@section('title', 'Portofolio ' . $name)

@push('styles')
    {{-- meta berbagi (stack 'styles' berada di <head>) --}}
    <meta name="description" content="{{ $metaDescription }}">
    <link rel="canonical" href="{{ $shareUrl }}">
    <meta property="og:type" content="profile">
    <meta property="og:site_name" content="Student Go">
    <meta property="og:locale" content="id_ID">
    <meta property="og:title" content="Portofolio {{ $name }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $shareUrl }}">
    <meta property="profile:first_name" content="{{ $student->first_name }}">
    <meta property="profile:last_name" content="{{ $student->last_name }}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="Portofolio {{ $name }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
@endpush

@section('content')
    @if (auth()->id() === $student->user_id)
        <div class="sg-container pt-6">
            <x-ui.alert>Ini tampilan publik profil Anda. <a href="{{ route('student.profile.index') }}" class="text-brand-700 underline">Kembali ke profil saya</a></x-ui.alert>
        </div>
    @endif

    @include('student.profile.components.portfolio', [
        'projects' => $completed_projects,
        'owner' => false,
        'canViewContact' => $canViewContact ?? false,
    ])
@endsection
