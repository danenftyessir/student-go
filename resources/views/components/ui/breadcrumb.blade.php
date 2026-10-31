{{-- jejak navigasi. items = [label => url]; item terakhir (url null) = halaman ini (aria-current). --}}
@props(['items' => []])
<nav {{ $attributes->class('sg-breadcrumb') }} aria-label="Jejak navigasi">
    <ol>
        @foreach ($items as $label => $url)
            <li>
                @if ($url && ! $loop->last)
                    <a href="{{ $url }}">{{ $label }}</a>
                @else
                    <span aria-current="page">{{ $label }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
