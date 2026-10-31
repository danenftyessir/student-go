{{-- kepala halaman aplikasi (tanpa foto, guardrail 1): jejak navigasi ATAU tautan kembali, satu-satunya <h1> halaman,
     deskripsi satu baris, aksi utama di kanan (slot actions). halaman yang memakai komponen ini tidak menulis <h1> lain. --}}
@props(['title', 'description' => null, 'back' => null, 'backLabel' => 'Kembali', 'breadcrumbs' => []])
<header {{ $attributes->class('sg-page-header') }}>
    @if ($breadcrumbs)
        <x-ui.breadcrumb :items="$breadcrumbs" class="sg-page-header__crumbs" />
    @elseif ($back)
        <a href="{{ $back }}" class="sg-page-header__back"><x-icon name="arrow-left" size="sm" />{{ $backLabel }}</a>
    @endif
    <h1>{{ $title }}</h1>
    @if ($description)
        <p class="sg-page-header__desc">{{ $description }}</p>
    @endif
    @isset($actions)
        <div class="sg-page-header__actions">{{ $actions }}</div>
    @endisset
</header>
