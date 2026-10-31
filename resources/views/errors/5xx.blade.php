{{-- phase 26: 5xx memakai templat mandiri (tanpa database, session atau auth). status http tidak diubah oleh view ini. --}}
@include('errors.standalone', [
    'code' => $exception->getStatusCode(),
    'title' => 'Layanan sedang bermasalah',
    'description' => 'Permintaan Anda tidak dapat diproses saat ini. Coba lagi beberapa saat lagi.',
])
