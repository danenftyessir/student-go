<?php

namespace App\Http\Controllers;

use App\Support\ProjectVerification;

class ProjectVerificationController extends Controller
{
    /**
     * halaman publik verifikasi proyek KKN: siapa pun yang memegang kode dari portofolio
     * bisa memastikan proyek itu benar-benar selesai dan divalidasi instansi. tanpa data kontak (D-04).
     */
    public function show(string $kode)
    {
        $project = ProjectVerification::find($kode);

        if (! $project) {
            abort(404, 'Kode verifikasi tidak dikenal atau proyek tidak dapat ditampilkan.');
        }

        return view('verification.show', ['project' => $project, 'kode' => $kode]);
    }
}
