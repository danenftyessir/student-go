<?php

namespace Tests\Feature\Phase17;

use App\Models\Institution;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * phase 17: dashboard instansi sebagai antrean kerja (D-10), tanpa grafik (D-21), banner verifikasi netral (D-09).
 * kriteria L: tanpa chip tren, tanpa grid tile serba-nol, tepat satu tombol utama, status dari kosakata bersama,
 * verifikasi menjadi blok pertama selama belum terverifikasi. tautan lama tetap menuju target yang sama.
 */
class InstitutionDashboardTest extends TestCase
{
    use BuildsPhase1Data;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRegion();
    }

    private function dashboard(Institution $institution): string
    {
        return $this->actingAs($institution->user)
            ->get(route('institution.dashboard'))
            ->assertOk()
            ->getContent();
    }

    /**
     * instansi dengan satu lamaran menunggu, satu proyek berjalan (milestone + laporan menunggu),
     * enam proyek selesai tanpa ulasan dan satu masalah draf
     */
    private function populated(): array
    {
        $institution = $this->makeInstitution();
        [$openId, $draftId] = $this->makeOpenProblems($institution->id, 2);
        DB::table('problems')->where('id', $draftId)->update(['status' => 'draft', 'title' => 'Pendataan Posyandu Draf']);
        DB::table('problems')->where('id', $openId)->update(['title' => 'Sanitasi Air Bersih Desa']);

        $applicant = $this->makeStudent('Sari', 'Wulandari');
        $applicationId = DB::table('applications')->insertGetId([
            'student_id' => $applicant->id, 'problem_id' => $openId, 'motivation' => 'Motivasi',
            'status' => 'pending', 'applied_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);

        $runner = $this->makeStudent('Bima', 'Saputra');
        $this->makeProject($runner, $institution->id, $openId);
        $projectId = DB::table('projects')->where('student_id', $runner->id)->value('id');
        DB::table('projects')->where('id', $projectId)->update(['title' => 'Sanitasi Air Bersih Desa', 'progress_percentage' => 41]);
        DB::table('project_milestones')->insert([
            ['project_id' => $projectId, 'title' => 'Survei awal', 'order' => 1, 'target_date' => '2026-01-10', 'status' => 'completed', 'created_at' => now(), 'updated_at' => now()],
            ['project_id' => $projectId, 'title' => 'Laporan tengah', 'order' => 2, 'target_date' => '2099-02-15', 'status' => 'in_progress', 'created_at' => now(), 'updated_at' => now()],
        ]);
        DB::table('project_reports')->insert([
            'project_id' => $projectId, 'student_id' => $runner->id, 'type' => 'weekly', 'title' => 'Minggu 1',
            'summary' => 'Ringkasan', 'activities' => 'Kegiatan', 'period_start' => '2026-01-01', 'period_end' => '2026-01-07',
            'status' => 'pending', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $completed = [];
        for ($i = 1; $i <= 6; $i++) {
            $student = $this->makeStudent('Lulus', 'Proyek' . $i);
            $this->makeProject($student, $institution->id, $openId);
            $id = DB::table('projects')->where('student_id', $student->id)->value('id');
            DB::table('projects')->where('id', $id)->update(['status' => 'completed', 'actual_end_date' => '2026-03-01', 'title' => 'Proyek Selesai ' . $i]);
            $completed[] = $id;
        }

        return compact('institution', 'openId', 'draftId', 'applicationId', 'projectId', 'completed');
    }

    public function test_empty_account_shows_no_tiles_no_trend_and_one_next_step(): void
    {
        $institution = $this->makeInstitution();
        $html = $this->dashboard($institution);

        $this->assertSame(1, substr_count($html, '<h1'), 'tepat satu h1');
        $this->assertSame(1, substr_count($html, 'sg-btn--primary'), 'tepat satu tombol utama');
        $this->assertStringContainsString(route('institution.problems.create'), $html);
        foreach (['dari bulan lalu', '↓', '↑', '0.0%', 'Acceptance Rate', 'Total Views', 'Quick Actions'] as $gone) {
            $this->assertStringNotContainsString($gone, $html, "tidak boleh ada: {$gone}");
        }
        $this->assertStringContainsString('Belum ada masalah yang diterbitkan', $html);
        // ringkasan tanpa angka nol: akun kosong tidak menampilkan baris ringkasan sama sekali
        $this->assertStringNotContainsString('aria-label="Antrean kerja"', $html);
        $this->assertStringNotContainsString('<style', $html);
    }

    public function test_unverified_institution_sees_verification_first_with_neutral_copy(): void
    {
        $institution = $this->makeInstitution();
        $html = $this->dashboard($institution);

        $verify = strpos($html, 'Verifikasi instansi');
        $this->assertNotFalse($verify);
        $this->assertLessThan(strpos($html, 'sg-inst-dash__bar'), $verify, 'verifikasi sebelum antrean');
        $this->assertGreaterThan(strpos($html, '<h1'), $verify);
        $this->assertStringContainsString('Menunggu Verifikasi', $html);
        $this->assertStringContainsString(route('institution.verification.upload'), $html);
        // D-09: tidak menjanjikan cara atau lama proses
        $this->assertStringNotContainsString('otomatis menggunakan AI', $html);
    }

    public function test_rejected_and_needs_review_keep_their_links_without_promises(): void
    {
        $institution = $this->makeInstitution();
        $institution->update(['verification_status' => 'rejected']);
        $html = $this->dashboard($institution);
        $this->assertStringContainsString('Ditolak', $html);
        $this->assertStringContainsString(route('institution.verification.status'), $html);
        $this->assertStringContainsString(route('institution.verification.upload'), $html);
        $this->assertStringContainsString('role="alert"', $html);

        $institution->update(['verification_status' => 'needs_review']);
        $institution = $institution->fresh();
        $html = $this->dashboard($institution);
        $this->assertStringContainsString('Perlu Ditinjau', $html);
        $this->assertStringContainsString(route('institution.verification.status'), $html);
        $this->assertStringNotContainsString('1-2 hari kerja', $html);
    }

    public function test_verified_institution_has_no_verification_block(): void
    {
        $institution = $this->makeInstitution();
        $institution->update(['verification_status' => 'active']); // Institution::STATUS_ACTIVE
        $html = $this->dashboard($institution);

        $this->assertStringNotContainsString('Verifikasi instansi', $html);
        $this->assertStringNotContainsString(route('institution.verification.upload'), $html);
    }

    public function test_populated_queue_uses_real_counts_and_shared_status_words(): void
    {
        $data = $this->populated();
        $html = $this->dashboard($data['institution']);

        $this->assertSame(1, substr_count($html, 'sg-btn--primary'));
        foreach (['Lamaran menunggu keputusan', 'Proyek berjalan', 'Menunggu ulasan', 'Masalah draf', 'Masalah terbaru'] as $heading) {
            $this->assertStringContainsString('>' . $heading . '</h2>', $html);
        }
        // ringkasan: angka nyata (6 proyek selesai, bukan dibatasi 5)
        $this->assertMatchesRegularExpression('/<b>1<\/b> lamaran menunggu keputusan/', $html);
        $this->assertMatchesRegularExpression('/<b>1<\/b> proyek berjalan/', $html);
        $this->assertMatchesRegularExpression('/<b>6<\/b> proyek selesai menunggu ulasan/', $html);
        $this->assertMatchesRegularExpression('/<b>1<\/b> masalah draf/', $html);
        $this->assertStringContainsString('5 dari 6 proyek selesai.', $html);

        // kosakata status bersama (D-02), bukan Pending/Open/Review
        $this->assertStringContainsString('data-value="pending"', $html);
        $this->assertMatchesRegularExpression('/data-value="pending">\s*<svg[^>]*>.*?<\/svg>\s*Menunggu/s', $html);
        $this->assertMatchesRegularExpression('/data-value="open">\s*<svg[^>]*>.*?<\/svg>\s*Dibuka/s', $html);
        $this->assertMatchesRegularExpression('/data-value="draft">\s*<svg[^>]*>.*?<\/svg>\s*Draf/s', $html);
        foreach (['>Pending<', '>Open<', '>Review<'] as $english) {
            $this->assertStringNotContainsString($english, $html);
        }

        // proyek berjalan: milestone berikutnya (yang belum selesai) dan laporan menunggu tinjauan
        $this->assertStringContainsString('Milestone berikutnya: Laporan tengah', $html);
        $this->assertStringNotContainsString('Milestone berikutnya: Survei awal', $html);
        $this->assertStringContainsString('1 laporan menunggu tinjauan', $html);
        $this->assertStringContainsString('<progress value="41" max="100"', $html);

        $this->assertStringNotContainsString('dari bulan lalu', $html);
    }

    public function test_links_keep_their_targets(): void
    {
        $data = $this->populated();
        $html = $this->dashboard($data['institution']);

        $targets = [
            route('institution.problems.create'),
            route('institution.applications.index'),
            route('institution.applications.index', ['status' => 'pending']),
            route('institution.applications.show', $data['applicationId']),
            route('institution.projects.index'),
            route('institution.projects.index', ['status' => 'active']),
            route('institution.projects.show', $data['projectId']),
            route('institution.reviews.create', $data['completed'][0]),
            route('institution.problems.index'),
            route('institution.problems.show', $data['openId']),
            route('institution.problems.edit', $data['draftId']),
        ];
        foreach ($targets as $url) {
            $this->assertStringContainsString('href="' . e($url) . '"', $html, "tautan hilang: {$url}");
        }
    }

    public function test_role_access_is_unchanged(): void
    {
        $this->get(route('institution.dashboard'))->assertRedirect(route('login'));

        $student = $this->makeStudent('Dimas', 'Prasetyo');
        $this->actingAs($student->user)->get(route('institution.dashboard'))->assertRedirect(route('student.dashboard'));

        $company = $this->makeCompany();
        $this->actingAs($company->user)->get(route('institution.dashboard'))->assertRedirect(route('company.dashboard'));
    }
}
