{{-- phase 19: rating sebagai teks "4/5" + meter netral lima segmen (tanpa bintang berwarna; warna hanya untuk makna) --}}
@php $value = (int) round((float) $rating); @endphp
<span class="sg-inst-rating">
    <span class="sg-inst-rating__bars" aria-hidden="true">
        @for ($i = 1; $i <= 5; $i++)<i @if ($i <= $value) data-on @endif></i>@endfor
    </span>
    <span><span class="sr-only">Rating </span>{{ $value }}/5</span>
</span>
