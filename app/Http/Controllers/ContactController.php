<?php

namespace App\Http\Controllers;

use App\Support\TeamMembers;

class ContactController extends Controller
{
    /**
     * tampilkan halaman contact
     */
    public function index()
    {
        // kanal kontak: email tim yang diberikan pemilik produk (2026-10-31); tanpa whatsapp
        $teamMembers = TeamMembers::all();

        return view('contact.index', compact('teamMembers'));
    }
}
