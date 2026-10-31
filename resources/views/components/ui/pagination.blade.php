{{-- paginasi tunggal untuk semua daftar (load-more hanya untuk feed). paginator = hasil ->paginate()/->simplePaginate().
     URL dari $paginator->url(n): parameter query (search, status, …) ikut bila pemanggil memakai ->withQueryString()/appends().
     di bawah 640px hanya Sebelumnya/Berikutnya + "Halaman x dari y". --}}
@props(['paginator', 'label' => 'Navigasi halaman'])
@php
    $lengthAware = $paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator;
    $current = $paginator->currentPage();
    $last = $lengthAware ? $paginator->lastPage() : null;
    $pages = [];
    if ($lengthAware && $last > 1) {
        // 1 … (n-1) n (n+1) … last
        $window = collect([1, $last, $current - 1, $current, $current + 1])->filter(fn ($p) => $p >= 1 && $p <= $last)->unique()->sort()->values();
        $prev = null;
        foreach ($window as $p) {
            if ($prev !== null && $p - $prev > 1) {
                $pages[] = null;
            }
            $pages[] = $p;
            $prev = $p;
        }
    }
    $fmt = fn ($n) => number_format((float) $n, 0, ',', '.');
@endphp
@if ($paginator->hasPages() || ($lengthAware && $paginator->total() > 0))
    <div {{ $attributes->class('sg-pagination') }}>
        @if ($lengthAware && $paginator->total() > 0)
            <p class="sg-pagination__info">Menampilkan {{ $fmt($paginator->firstItem()) }}–{{ $fmt($paginator->lastItem()) }} dari {{ $fmt($paginator->total()) }}</p>
        @endif
        @if ($paginator->hasPages())
            <nav aria-label="{{ $label }}">
                <ul class="sg-pagination__list">
                    <li>
                        @if ($paginator->onFirstPage())
                            <span class="sg-pagination__link" aria-disabled="true"><x-icon name="chevron-left" size="sm" />Sebelumnya</span>
                        @else
                            <a class="sg-pagination__link" href="{{ $paginator->previousPageUrl() }}" rel="prev"><x-icon name="chevron-left" size="sm" />Sebelumnya</a>
                        @endif
                    </li>
                    @foreach ($pages as $page)
                        @if ($page === null)
                            <li class="sg-pagination__page"><span class="sg-pagination__gap" aria-hidden="true">…</span></li>
                        @elseif ($page === $current)
                            <li class="sg-pagination__page"><span class="sg-pagination__link" aria-current="page"><span class="sr-only">Halaman </span>{{ $page }}</span></li>
                        @else
                            <li class="sg-pagination__page"><a class="sg-pagination__link" href="{{ $paginator->url($page) }}"><span class="sr-only">Halaman </span>{{ $page }}</a></li>
                        @endif
                    @endforeach
                    @if ($lengthAware)
                        <li class="sg-pagination__info sm:hidden self-center px-1">Halaman {{ $current }} dari {{ $last }}</li>
                    @endif
                    <li>
                        @if ($paginator->hasMorePages())
                            <a class="sg-pagination__link" href="{{ $paginator->nextPageUrl() }}" rel="next">Berikutnya<x-icon name="chevron-right" size="sm" /></a>
                        @else
                            <span class="sg-pagination__link" aria-disabled="true">Berikutnya<x-icon name="chevron-right" size="sm" /></span>
                        @endif
                    </li>
                </ul>
            </nav>
        @endif
    </div>
@endif
