<?php

namespace App\Support;

/**
 * tim pengembang student go yang ditampilkan di halaman publik (phase 5).
 * nama, email dan foto (*_seragam.png) diberikan pemilik produk pada 2026-10-31; tidak ada peran
 * atau nomor telepon karena keduanya tidak diberikan (jangan mengarang).
 * about memakai nama + foto (png transparan 4:5 dari pemilik, dipakai apa adanya), contact memakai nama + email.
 * photo_webp = salinan 480x600 dari png yang sama (audit F-15, 2026-11-13) agar halaman tidak mengunduh ±2 MB;
 * png tetap menjadi fallback.
 */
class TeamMembers
{
    /**
     * @return array<int, array{name: string, email: string, photo: string, photo_webp: string}>
     */
    public static function all(): array
    {
        return [
            [
                'name' => 'M. Abizzar Gamadrian',
                'email' => '13523155@std.stei.itb.ac.id',
                'photo' => 'assets/photography/member/M._Abizzar_Gamadrian_seragam.png',
                'photo_webp' => 'assets/photography/member/m-abizzar-gamadrian-480.webp',
            ],
            [
                'name' => 'Danendra Shafi Athallah',
                'email' => '13523136@std.stei.itb.ac.id',
                'photo' => 'assets/photography/member/Danendra_Shafi_Athallah_seragam.png',
                'photo_webp' => 'assets/photography/member/danendra-shafi-athallah-480.webp',
            ],
            [
                'name' => 'Kenzie Raffa Ardhana',
                'email' => '18223127@std.stei.itb.ac.id',
                'photo' => 'assets/photography/member/Kenzie_Raffa_Ardhana_seragam.png',
                'photo_webp' => 'assets/photography/member/kenzie-raffa-ardhana-480.webp',
            ],
        ];
    }
}
