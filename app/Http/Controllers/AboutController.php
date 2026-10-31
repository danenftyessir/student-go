<?php

namespace App\Http\Controllers;

use App\Support\TeamMembers;

/**
 * controller untuk halaman tentang kami (phase 5).
 * angka statistik lama (520.000+, 83.436, rp 540 miliar, 100.000+) tidak bersumber dan
 * tidak pernah tampil di view; dihapus (J9). isi halaman ditulis dari fakta produk.
 */
class AboutController extends Controller
{
    /**
     * tampilkan halaman tentang kami
     */
    public function index()
    {
        // tim pengembang (nama + foto dari pemilik produk)
        $team = TeamMembers::all();

        return view('about.index', compact('team'));
    }
}
