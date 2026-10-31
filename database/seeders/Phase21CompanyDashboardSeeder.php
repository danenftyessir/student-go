<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * phase 21 (D-13, disetujui 2026-10-31): data uji dashboard perusahaan di database pengembangan.
 *
 * - perusahaan seed pertama (id terkecil) mendapat 2 lowongan aktif + 1 draf dan 12 lamaran
 *   bertahap pipeline (new, reviewing, interview, offer, hired, rejected) yang tersebar mei-okt 2026,
 *   agar tahap pelamar dan aturan grafik (>= 5 bulan berisi data) dapat diperiksa.
 * - satu akun perusahaan kosong (nama 76 karakter) untuk keadaan kosong. password menyalin hash
 *   akun perusahaan seed pertama, jadi tidak ada password baru yang ditulis.
 *
 * aman dijalankan ulang. semua baris bertanda: slug 'p21-seed-*', notes 'seed:phase21',
 * username 'p21_perusahaan_kosong'. insert lewat DB::table: tanpa event, notifikasi atau email.
 * jalankan: php artisan db:seed --class=Phase21CompanyDashboardSeeder
 */
class Phase21CompanyDashboardSeeder extends Seeder
{
    private const MARK = 'seed:phase21';
    private const EMPTY_USERNAME = 'p21_perusahaan_kosong';

    public function run(): void
    {
        $company = DB::table('companies')->orderBy('id')->first();
        if (!$company) {
            $this->command->warn('Tidak ada perusahaan. Jalankan seeder perusahaan lebih dulu.');
            return;
        }

        $jobIds = $this->seedJobs($company);
        $this->seedApplications($company, $jobIds);
        $this->seedEmptyCompany($company);
    }

    // dua lowongan aktif (tayang sejak awal mei) dan satu draf
    private function seedJobs(object $company): array
    {
        $category = fn (string $name) => DB::table('job_categories')->where('name', $name)->value('id');
        $jobs = [
            'p21-seed-pendamping-digitalisasi' => ['Pendamping Digitalisasi Pencatatan Koperasi', 'Digital Transformation', 'posted'],
            'p21-seed-analis-data-panen' => ['Analis Data Hasil Panen Anggota', 'Data Analytics & AI', 'posted'],
            'p21-seed-konten-pemasaran' => ['Penyusun Konten Pemasaran Daring Produk Tani', 'Digital Marketing', 'draft'],
        ];

        $ids = [];
        foreach ($jobs as $slug => [$title, $categoryName, $status]) {
            $existing = DB::table('job_postings')->where('slug', $slug)->value('id');
            if ($existing) {
                $ids[$slug] = $existing;
                continue;
            }

            $created = Carbon::parse('2026-05-04 09:00:00');
            $ids[$slug] = DB::table('job_postings')->insertGetId([
                'company_id' => $company->id,
                'job_category_id' => $category($categoryName),
                'title' => $title,
                'slug' => $slug,
                'location' => 'Kabupaten Bandung',
                'job_type' => 'internship',
                'description' => 'Lowongan uji untuk pemeriksaan dashboard perusahaan (Phase 21).',
                'status' => $status,
                'published_at' => $status === 'posted' ? $created : null,
                'created_at' => $created,
                'updated_at' => $created,
            ]);
        }

        $this->command->info('Lowongan phase 21: ' . count($ids) . ' (perusahaan #' . $company->id . ')');

        return $ids;
    }

    // 12 lamaran dari mahasiswa yang belum pernah melamar ke perusahaan ini
    private function seedApplications(object $company, array $jobIds): void
    {
        if (DB::table('job_applications')->where('notes', self::MARK)->exists()) {
            $this->command->info('Lamaran phase 21 sudah ada; dilewati.');
            return;
        }

        $companyJobIds = DB::table('job_postings')->where('company_id', $company->id)->pluck('id');
        $students = DB::table('users')
            ->where('user_type', 'student')
            ->whereNotIn('id', DB::table('job_applications')->whereIn('job_posting_id', $companyJobIds)->select('user_id'))
            ->orderBy('id')
            ->limit(12)
            ->pluck('id');

        // [tanggal masuk, status] tersebar enam bulan agar grafik punya data di >= 5 bulan
        $plan = [
            ['2026-05-12', 'hired'], ['2026-05-20', 'rejected'],
            ['2026-06-09', 'offer'], ['2026-06-23', 'rejected'],
            ['2026-07-07', 'interview'], ['2026-07-21', 'reviewing'],
            ['2026-08-11', 'interview'], ['2026-08-25', 'reviewing'],
            ['2026-09-08', 'new'], ['2026-09-22', 'reviewing'],
            ['2026-10-13', 'new'], ['2026-10-27', 'new'],
        ];
        $posted = [$jobIds['p21-seed-pendamping-digitalisasi'], $jobIds['p21-seed-analis-data-panen']];

        $rows = [];
        foreach ($plan as $i => [$date, $status]) {
            if (!isset($students[$i])) {
                break;
            }
            $at = Carbon::parse($date . ' 10:00:00');
            $rows[] = [
                'job_posting_id' => $posted[$i % 2],
                'user_id' => $students[$i],
                'status' => $status,
                'notes' => self::MARK,
                'reviewed_at' => $status === 'new' ? null : $at->copy()->addDays(2),
                'interview_scheduled_at' => $status === 'interview' ? $at->copy()->addDays(9) : null,
                'offer_extended_at' => in_array($status, ['offer', 'hired'], true) ? $at->copy()->addDays(14) : null,
                'hired_at' => $status === 'hired' ? $at->copy()->addDays(18) : null,
                'rejected_at' => $status === 'rejected' ? $at->copy()->addDays(5) : null,
                'created_at' => $at,
                'updated_at' => $at,
            ];
        }
        DB::table('job_applications')->insert($rows);

        foreach ($posted as $jobId) {
            DB::table('job_postings')->where('id', $jobId)
                ->update(['applications_count' => DB::table('job_applications')->where('job_posting_id', $jobId)->count()]);
        }

        $this->command->info('Lamaran phase 21: ' . count($rows));
    }

    // akun perusahaan tanpa lowongan, lamaran atau talenta tersimpan
    private function seedEmptyCompany(object $company): void
    {
        if (DB::table('users')->where('username', self::EMPTY_USERNAME)->exists()) {
            $this->command->info('Akun perusahaan kosong phase 21 sudah ada; dilewati.');
            return;
        }

        $now = now();
        $userId = DB::table('users')->insertGetId([
            'name' => 'Koperasi Uji Pemasaran Hasil Pertanian Organik dan Perikanan Kabupaten Garut',
            'email' => 'p21.perusahaan.kosong@studentgo.test',
            'username' => self::EMPTY_USERNAME,
            'password' => DB::table('users')->where('id', $company->user_id)->value('password'),
            'user_type' => 'company',
            'is_active' => true,
            'email_verified_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('companies')->insert([
            'user_id' => $userId,
            'name' => 'Koperasi Uji Pemasaran Hasil Pertanian Organik dan Perikanan Kabupaten Garut',
            'industry' => 'Pertanian',
            'city' => 'Garut',
            'verification_status' => 'verified',
            'verified_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->command->info('Akun perusahaan kosong phase 21 dibuat (username ' . self::EMPTY_USERNAME . ').');
    }
}
