@extends('layouts.app')

@section('title', 'Ulasan '.($review->reviewee?->display_name ?? ''))

@section('content')
@php
    // phase 19: detail satu ulasan. ubah hanya selama 30 hari pertama (aturan controller; tombol mengikuti aturan yang sama).
    $tanggal = fn ($date) => $date ? \Illuminate\Support\Carbon::parse($date)->locale('id')->translatedFormat('j F Y') : null;
    $editable = $review->created_at && ! $review->created_at->copy()->addDays(30)->isPast();
    $student = $review->reviewee?->student;
@endphp

<div class="sg-container sg-inst-page">
    <x-ui.page-header :title="'Ulasan untuk '.($review->reviewee?->display_name ?? 'mahasiswa')"
                      :description="collect([$review->project?->problem?->title ?? $review->project?->title, $student?->university?->name])->filter()->implode(' · ')"
                      :back="route('institution.reviews.index')" back-label="Kembali ke daftar ulasan">
        @if ($editable)
            <x-slot:actions>
                <x-ui.button :href="route('institution.reviews.edit', $review->id)" icon="pencil">Ubah ulasan</x-ui.button>
            </x-slot:actions>
        @endif
    </x-ui.page-header>

    <div class="sg-inst-detail">
        <article class="sg-inst-detail__main" aria-label="Isi ulasan">
            <section class="sg-inst-detail__block">
                <h2>Ulasan</h2>
                <div class="sg-inst-detail__text">{!! nl2br(e($review->review_text)) !!}</div>
            </section>
            @if (filled($review->strengths))
                <section class="sg-inst-detail__block">
                    <h2>Kekuatan</h2>
                    <div class="sg-inst-detail__text">{!! nl2br(e($review->strengths)) !!}</div>
                </section>
            @endif
            @if (filled($review->improvements))
                <section class="sg-inst-detail__block">
                    <h2>Saran pengembangan</h2>
                    <div class="sg-inst-detail__text">{!! nl2br(e($review->improvements)) !!}</div>
                </section>
            @endif
        </article>

        <aside class="sg-inst-detail__rail" aria-label="Informasi ulasan">
            <x-ui.dl>
                <div>
                    <dt>Rating</dt>
                    <dd>@include('institution.reviews.components.rating', ['rating' => $review->rating])</dd>
                </div>
                @if ($review->project)
                    <div>
                        <dt>Proyek</dt>
                        <dd><a href="{{ route('institution.projects.show', $review->project_id) }}" class="sg-inst-link">{{ $review->project->title }}</a></dd>
                    </div>
                @endif
                <div>
                    <dt>Mahasiswa</dt>
                    <dd>{{ $review->reviewee?->display_name ?? '—' }}</dd>
                </div>
                <div>
                    <dt>Ditulis</dt>
                    <dd>{{ $tanggal($review->created_at) ?? '—' }}</dd>
                </div>
                @if ($review->updated_at && $review->created_at && $review->updated_at->gt($review->created_at))
                    <div>
                        <dt>Terakhir diubah</dt>
                        <dd>{{ $tanggal($review->updated_at) }}</dd>
                    </div>
                @endif
            </x-ui.dl>
            @unless ($editable)
                <p class="sg-inst-detail__note">Ulasan hanya dapat diubah dalam 30 hari setelah ditulis.</p>
            @endunless
        </aside>
    </div>
</div>
@endsection
