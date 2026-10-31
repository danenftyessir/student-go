{{-- phase 26: 503 memakai templat mandiri (tanpa database, session atau auth). status http tidak diubah oleh view ini. --}}
@include('errors.standalone', [
    'code' => 503,
    'title' => 'Student Go sedang dalam pemeliharaan',
    'description' => 'Layanan sementara tidak tersedia. Silakan kembali beberapa saat lagi.',
])
