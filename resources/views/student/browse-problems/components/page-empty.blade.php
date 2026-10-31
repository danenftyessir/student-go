{{-- halaman di luar jangkauan (mis. ?page=99): hasil ada, tetapi tidak di halaman ini. filter tetap dibawa ke halaman 1. --}}
<x-ui.empty-state
    title="Halaman {{ $problems->currentPage() }} tidak ada"
    description="Daftar ini hanya punya {{ $problems->lastPage() }} halaman untuk pencarian dan filter yang sama."
    illustration="no-search-results"
    class="sg-browse__empty">
    <x-slot:action>
        <x-ui.button variant="secondary" :href="$problems->url(1)">Ke halaman 1</x-ui.button>
    </x-slot:action>
</x-ui.empty-state>
