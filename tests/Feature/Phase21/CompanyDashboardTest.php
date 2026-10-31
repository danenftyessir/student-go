<?php

namespace Tests\Feature\Phase21;

use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * phase 21: dashboard perusahaan sebagai antrean kerja (D-10), grafik hanya bila >= 5 bulan berisi data (D-21).
 * kriteria L: tanpa foto hero, grafik kosong atau chip tren; semua label bahasa indonesia; satu aksi utama;
 * nama talenta sama dengan halaman profil; keadaan kosong memberi langkah berikutnya. tautan lama tetap.
 */
class CompanyDashboardTest extends TestCase
{
    use BuildsPhase1Data;
    use RefreshDatabase;

    private int $jobSeq = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRegion();
    }

    private function dashboard(Company $company): string
    {
        return $this->actingAs($company->user)
            ->get(route('company.dashboard'))
            ->assertOk()
            ->getContent();
    }

    private function makeJob(Company $company, string $title, string $status = 'posted', array $attrs = []): int
    {
        $this->jobSeq++;

        return DB::table('job_postings')->insertGetId(array_merge([
            'company_id' => $company->id,
            'title' => $title,
            'slug' => 'lowongan-uji-' . $this->jobSeq,
            'location' => 'Bandung',
            'status' => $status,
            'published_at' => $status === 'draft' ? null : now()->subDays(10),
            'created_at' => now()->subDays(10),
            'updated_at' => now()->subDays(10),
        ], $attrs));
    }

    private function apply(int $jobId, int $userId, string $status, ?Carbon $at = null): int
    {
        $at ??= now()->subDay();

        return DB::table('job_applications')->insertGetId([
            'job_posting_id' => $jobId, 'user_id' => $userId, 'status' => $status,
            'created_at' => $at, 'updated_at' => $at,
        ]);
    }

    /**
     * perusahaan dengan dua lowongan aktif, satu draf, satu ditutup; delapan lamaran di semua tahap; satu talenta tersimpan
     */
    private function populated(): array
    {
        $company = $this->makeCompany();
        $jobA = $this->makeJob($company, 'Pendamping Digitalisasi Koperasi');
        $jobB = $this->makeJob($company, 'Analis Data Hasil Panen', 'posted', ['published_at' => now()->subDays(3)]);
        $this->makeJob($company, 'Penyuluh Pemasaran Daring', 'draft');
        $this->makeJob($company, 'Lowongan Lama Ditutup', 'closed');

        $statuses = ['pending', 'new', 'reviewing', 'shortlisted', 'interview', 'offer', 'hired', 'rejected'];
        $applications = [];
        foreach ($statuses as $i => $status) {
            $student = $this->makeStudent('Pelamar', 'Tahap' . $i);
            $applications[$status] = $this->apply($i % 2 ? $jobB : $jobA, $student->user_id, $status, now()->subHours(10 - $i));
        }

        // J1: nama akun berbeda dari nama mahasiswa; dashboard harus memakai nama profil
        $saved = $this->makeStudent('Ratna', 'Kusumawati', 'akun lama ratna');
        DB::table('saved_talents')->insert([
            'company_id' => $company->id, 'user_id' => $saved->user_id, 'saved_at' => now(),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return compact('company', 'jobA', 'jobB', 'applications', 'saved');
    }

    public function test_empty_account_shows_first_run_steps_and_one_primary_action(): void
    {
        $company = $this->makeCompany();
        $html = $this->dashboard($company);

        $this->assertSame(1, substr_count($html, '<h1'), 'tepat satu h1');
        $this->assertSame(1, substr_count($html, 'sg-btn--primary'), 'tepat satu tombol utama');
        $this->assertStringContainsString('Pasang lowongan', $html);
        $this->assertStringContainsString('Belum ada lamaran', $html);
        $this->assertStringContainsString('Cara kerja di Student Go', $html);
        foreach (['company.talents.index', 'company.talents.saved', 'company.jobs.create', 'company.applications.index'] as $name) {
            $this->assertStringContainsString('href="' . route($name) . '"', $html, "langkah menuju {$name}");
        }
        // tanpa angka nol: akun kosong tidak menampilkan baris ringkasan atau tahap
        $this->assertStringNotContainsString('aria-label="Ringkasan lowongan"', $html);
        $this->assertStringNotContainsString('Pelamar per tahap', $html);
    }

    public function test_no_hero_photo_chart_trend_or_inline_style(): void
    {
        $html = $this->dashboard($this->populated()['company']);

        foreach (['deal-work-together.jpg', 'bulan lalu', '<canvas', 'chart.js', '<style', 'fade-in-up', 'Selamat Datang'] as $gone) {
            $this->assertStringNotContainsString($gone, $html, "tidak boleh ada: {$gone}");
        }
    }

    public function test_visible_labels_are_indonesian(): void
    {
        $html = $this->dashboard($this->populated()['company']);

        foreach (['Post Job', 'Kelola Jobs', 'Browse', 'Cari Talent<', 'Rekomendasi Talenta AI', 'No major specified', 'Unknown',
            '>New<', '>Reviewing<', '>Interview<', '>Offer<', '>Hired<', '>Shortlisted<'] as $english) {
            $this->assertStringNotContainsString($english, $html, "label inggris: {$english}");
        }
        foreach (['Lamaran terbaru', 'Lowongan aktif', 'Talenta tersimpan', 'Talenta terbaru', 'Tinjau lamaran', 'Cari talenta'] as $label) {
            $this->assertStringContainsString($label, $html);
        }
    }

    public function test_populated_queue_uses_real_counts_and_shared_status_words(): void
    {
        $data = $this->populated();
        $html = $this->dashboard($data['company']);

        // ringkasan: hanya lowongan aktif dan draf
        $this->assertMatchesRegularExpression('/<b>2<\/b>\s*lowongan aktif/', $html);
        $this->assertMatchesRegularExpression('/<b>1<\/b>\s*lowongan draf/', $html);

        // tahap: pending + new = Menunggu (2); tahap selesai (hired, rejected) tidak dihitung
        $stages = substr($html, strpos($html, 'Jumlah pelamar per tahap'));
        $stages = substr($stages, 0, strpos($stages, '</ol>'));
        foreach (['2' => 'Menunggu', '1' => 'Ditinjau'] as $count => $label) {
            $this->assertMatchesRegularExpression('/>' . $count . '<\/span>\s*<span class="sg-co-dash__stage-label">' . $label . '</', $stages);
        }
        foreach (['Terpilih', 'Wawancara', 'Penawaran'] as $label) {
            $this->assertStringContainsString($label, $stages);
        }
        $this->assertStringNotContainsString('Direkrut', $stages);
        $this->assertStringNotContainsString('Ditolak', $stages);

        // lamaran terbaru: 5 dari 8, badge dari kosakata bersama
        $this->assertStringContainsString('5 terbaru dari 8 lamaran.', $html);
        $this->assertStringContainsString('data-value="rejected"', $html);
        $this->assertStringContainsString('Ditolak', $html);

        // tabel lowongan aktif: draf dan lowongan ditutup tidak tampil; jumlah pelamar per lowongan
        $this->assertStringContainsString('Pendamping Digitalisasi Koperasi', $html);
        $this->assertStringContainsString('Analis Data Hasil Panen', $html);
        $this->assertStringNotContainsString('Penyuluh Pemasaran Daring', $html);
        $this->assertStringNotContainsString('Lowongan Lama Ditutup', $html);
        $this->assertStringContainsString(route('company.applications.index', ['job_posting_id' => $data['jobA']]), $html);
    }

    public function test_talent_names_match_profile_pages(): void
    {
        $data = $this->populated();
        $html = $this->dashboard($data['company']);

        $this->assertStringContainsString('Ratna Kusumawati', $html);
        $this->assertStringNotContainsString('akun lama ratna', $html);
        $this->assertStringContainsString(route('profile.public', $data['saved']->user->username), $html);
    }

    public function test_chart_needs_five_months_with_data(): void
    {
        Carbon::setTestNow('2026-10-15 10:00:00');
        $company = $this->makeCompany();
        $job = $this->makeJob($company, 'Lowongan Grafik', 'posted', ['published_at' => '2026-04-01 00:00:00']);

        // tiga bulan berisi data: tidak ada grafik
        foreach (['2026-08-05', '2026-09-05', '2026-10-05'] as $i => $date) {
            $this->apply($job, $this->makeStudent('Grafik', 'A' . $i)->user_id, 'pending', Carbon::parse($date));
        }
        $html = $this->dashboard($company);
        $this->assertStringNotContainsString('<canvas', $html);
        $this->assertStringNotContainsString('data-sg-chart', $html);

        // lima bulan berisi data: grafik tampil dengan tabel data untuk pembaca layar
        foreach (['2026-05-05', '2026-06-05'] as $i => $date) {
            $this->apply($job, $this->makeStudent('Grafik', 'B' . $i)->user_id, 'new', Carbon::parse($date));
        }
        $html = $this->dashboard($company);
        $this->assertStringContainsString('<canvas id="grafik-lamaran"', $html);
        // chart.js dari paket npm lokal (resources/js/student-go/charts.js), bukan cdn
        $this->assertStringContainsString('data-sg-chart="[{&quot;label&quot;:&quot;Mei 2026&quot;,&quot;total&quot;:1}', $html);
        $this->assertStringNotContainsString('cdn.jsdelivr.net/npm/chart.js', $html);
        $this->assertStringContainsString('Lamaran masuk per bulan', $html);
        $this->assertStringContainsString('<th scope="row">Mei 2026</th><td>1</td>', $html);
        $this->assertStringContainsString('<th scope="row">Jul 2026</th><td>0</td>', $html);
        Carbon::setTestNow();
    }

    public function test_jobs_without_applications_explain_the_next_step(): void
    {
        $company = $this->makeCompany();
        $this->makeJob($company, 'Lowongan Tanpa Pelamar');
        $html = $this->dashboard($company);
        $this->assertStringContainsString('Belum ada lamaran', $html);
        $this->assertStringContainsString('melamar lowongan aktif Anda', $html);
        $this->assertStringNotContainsString('Cara kerja di Student Go', $html);

        $draftOnly = $this->makeCompany();
        $this->makeJob($draftOnly, 'Lowongan Draf Saja', 'draft');
        $html = $this->dashboard($draftOnly);
        $this->assertStringContainsString('Belum ada lowongan yang dibuka', $html);
        $this->assertStringContainsString('href="' . route('company.jobs.index') . '"', $html);
        $this->assertSame(1, substr_count($html, 'sg-btn--primary'));
    }

    public function test_links_keep_their_targets(): void
    {
        $data = $this->populated();
        $html = $this->dashboard($data['company']);

        $this->assertSame(1, substr_count($html, 'sg-btn--primary'));
        foreach ([
            route('company.jobs.create'),
            route('company.applications.index'),
            route('company.talents.index'),
            route('company.jobs.index'),
            route('company.talents.saved'),
            route('company.applications.show', $data['applications']['offer']),
            route('company.jobs.show', $data['jobA']),
        ] as $url) {
            $this->assertStringContainsString('href="' . $url . '"', $html, "tautan hilang: {$url}");
        }
    }

    public function test_role_access_is_unchanged(): void
    {
        $this->get(route('company.dashboard'))->assertRedirect(route('login'));

        $student = $this->makeStudent('Ayu', 'Lestari');
        $this->actingAs($student->user)->get(route('company.dashboard'))->assertRedirect(route('student.dashboard'));

        $institution = $this->makeInstitution();
        $this->actingAs($institution->user)->get(route('company.dashboard'))->assertRedirect(route('institution.dashboard'));
    }

    public function test_company_user_without_profile_is_sent_home(): void
    {
        $company = $this->makeCompany();
        $user = $company->user;
        $company->delete();

        // perilaku lama tetap: middleware lebih dulu mengarahkan ke beranda dengan pesan galat
        $this->actingAs($user->fresh())->get(route('company.dashboard'))
            ->assertRedirect(route('home'))
            ->assertSessionHas('error', 'profil anda belum lengkap. silakan hubungi administrator.');
    }
}
