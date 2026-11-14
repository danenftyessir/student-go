<?php

namespace App\Support;

use App\Models\Project;

/**
 * kode verifikasi proyek KKN untuk halaman publik /verifikasi/{kode} (masukan juri, 2026-11-14).
 * bentuk kode: "{id}-{10 karakter hmac}"; tanda tangan dari APP_KEY sehingga kode tidak bisa ditebak dari id saja.
 * hanya proyek berstatus selesai yang ditampilkan di portofolio pemiliknya yang bisa diverifikasi.
 */
class ProjectVerification
{
    public static function code(Project $project): string
    {
        return $project->id.'-'.self::signature($project->id);
    }

    /**
     * proyek untuk kode yang sah, atau null bila kode salah / proyek belum selesai / disembunyikan pemiliknya
     */
    public static function find(string $code): ?Project
    {
        if (! preg_match('/^(\d{1,12})-([a-f0-9]{10})$/', $code, $m)) {
            return null;
        }
        if (! hash_equals(self::signature((int) $m[1]), $m[2])) {
            return null;
        }

        return Project::with(['student.user', 'student.university', 'problem.institution', 'problem.province', 'problem.regency', 'institutionReview'])
            ->whereKey((int) $m[1])
            ->where('status', 'completed')
            ->portfolioVisible()
            ->first();
    }

    private static function signature(int $id): string
    {
        return substr(hash_hmac('sha256', 'verifikasi-proyek:'.$id, (string) config('app.key')), 0, 10);
    }
}
