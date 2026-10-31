{{-- tab berbasis tautan (filter lewat query string, mis. ?status=pending): tiap tab = halaman/URL sendiri.
     items = [['label' => 'Menunggu', 'href' => …, 'active' => bool, 'count' => int|null], …] --}}
@props(['items' => [], 'label' => 'Bagian'])
<nav {{ $attributes }} aria-label="{{ $label }}">
    <ul class="sg-tabs">
        @foreach ($items as $item)
            <li>
                <a href="{{ $item['href'] }}" class="sg-tabs__tab" @if (! empty($item['active'])) aria-current="page" @endif>
                    {{ $item['label'] }}
                    @if (isset($item['count']) && $item['count'] !== null)
                        <span class="sg-tabs__count">{{ number_format((float) $item['count'], 0, ',', '.') }}</span>
                    @endif
                </a>
            </li>
        @endforeach
    </ul>
</nav>
