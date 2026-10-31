{{-- phase 26: 500 memakai templat mandiri (tanpa database, session atau auth). status http tidak diubah oleh view ini. --}}
@include('errors.standalone', [
    'code' => 500,
    'title' => 'Terjadi kesalahan di server',
    'description' => 'Permintaan Anda tidak dapat diproses saat ini. Coba lagi beberapa saat lagi; jika masalah berlanjut, hubungi tim Student Go.',
])
