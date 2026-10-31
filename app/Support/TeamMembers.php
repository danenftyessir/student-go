<?php

namespace App\Support;

/**
 * tim pengembang student go yang ditampilkan di halaman publik (phase 5).
 * nama, email dan foto (*_seragam.png) diberikan pemilik produk pada 2026-10-31; tidak ada peran
 * atau nomor telepon karena keduanya tidak diberikan (jangan mengarang).
 * about memakai nama + foto (png transparan 4:5 dari pemilik, dipakai apa adanya), contact memakai nama + email.
 */
class TeamMembers
{
    /**
     * @return array<int, array{name: string, email: string, photo: string}>
     */
    public static function all(): array
    {
        return [
            [
                'name' => 'M. Abizzar Gamadrian',
                'email' => '13523155@std.stei.itb.ac.id',
                'photo' => 'assets/photography/member/M._Abizzar_Gamadrian_seragam.png',
            ],
            [
                'name' => 'Danendra Shafi Athallah',
                'email' => '13523136@std.stei.itb.ac.id',
                'photo' => 'assets/photography/member/Danendra_Shafi_Athallah_seragam.png',
            ],
            [
                'name' => 'Kenzie Raffa Ardhana',
                'email' => '18223127@std.stei.itb.ac.id',
                'photo' => 'assets/photography/member/Kenzie_Raffa_Ardhana_seragam.png',
            ],
        ];
    }
}
