{{-- phase 29 paket G (D-45): peta kelompok pulau dari indonesia-regions.svg, disisipkan inline.
     $regions dari ProvinceRegion::summary(): grup asal, jumlah masalah dibuka per grup, grup aktif.
     angka hanya dari masalah dibuka yang dimuat controller; tanpa masalah dibuka hanya wilayah asal yang tampil.
     svg disembunyikan dari pembaca layar, daftar teks di bawahnya adalah padanannya. --}}
@use('App\Support\ProvinceRegion')

@if ($regions['active'])
    <section class="sg-org__section sg-region-map" aria-labelledby="org-wilayah">
        <h2 id="org-wilayah" class="sg-org__h2">Wilayah</h2>
        <div class="sg-region-map__figure">{!! ProvinceRegion::inlineSvg($regions['active']) !!}</div>
        <ul class="sg-region-map__list">
            @foreach ($regions['open'] as $group => $count)
                <li><span class="sg-region-map__key" aria-hidden="true"></span>{{ $count }} masalah dibuka di {{ ProvinceRegion::label($group) }}</li>
            @endforeach
            @if ($regions['home'])
                <li class="sg-region-map__home">@unless ($regions['open'])<span class="sg-region-map__key" aria-hidden="true"></span>@endunless Wilayah asal instansi: {{ ProvinceRegion::label($regions['home']) }}</li>
            @endif
        </ul>
    </section>
@endif
