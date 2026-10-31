{{-- kartu rekaman: HANYA untuk tampilan galeri opsional (default tabel/daftar, guardrail 3).
     image hanya bila ada gambar nyata (tanpa placeholder, guardrail 7); bila gagal dimuat, area media disembunyikan. --}}
@props(['title', 'href' => null, 'image' => null, 'imageAlt' => '', 'headingLevel' => 3])
<article {{ $attributes->class('sg-card') }}>
    @if ($image)
        <div class="sg-card__media sg-media">
            <img src="{{ $image }}" alt="{{ $imageAlt }}" loading="lazy" data-optional onerror="this.closest('.sg-media').hidden = true">
        </div>
    @endif
    <div class="sg-card__body">
        <h{{ $headingLevel }} class="sg-card__title">
            @if ($href)<a href="{{ $href }}">{{ $title }}</a>@else{{ $title }}@endif
        </h{{ $headingLevel }}>
        {{ $slot }}
    </div>
    @isset($footer)
        <div class="sg-card__foot">{{ $footer }}</div>
    @endisset
</article>
