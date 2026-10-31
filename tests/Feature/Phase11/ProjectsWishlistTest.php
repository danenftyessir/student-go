<?php

namespace Tests\Feature\Phase11;

use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * phase 11: proyek saya dan wishlist sebagai baris dengan kepala halaman standar. tahap dari status proyek,
 * progres dari progress_percentage (milestone), laporan terakhir yang tersimpan; filter hanya bila ada isi.
 * wishlist: tautan detail, "Lamar" ke rute create yang ada, "Hapus" tetap DELETE /student/wishlist/{id}/remove.
 */
class ProjectsWishlistTest extends TestCase
{
    use BuildsPhase1Data;
    use RefreshDatabase;

    private int $institutionId;

    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRegion();
        $this->institutionId = $this->makeInstitution()->id;
        $this->student = $this->makeStudent('Rina', 'Zahira');
    }

    private function problem(string $title, array $attrs = []): int
    {
        $id = $this->makeOpenProblems($this->institutionId, 1)[0];
        DB::table('problems')->where('id', $id)->update(array_merge([
            'title' => $title,
            'application_deadline' => now()->addDays(20)->toDateString(),
        ], $attrs));

        return $id;
    }

    private function project(string $status, int $progress = 0): int
    {
        $problemId = $this->problem('Pemberdayaan Ekonomi Desa');
        $this->makeProject($this->student, $this->institutionId, $problemId);
        $id = DB::table('projects')->latest('id')->value('id');
        DB::table('projects')->where('id', $id)->update([
            'title' => 'Pemberdayaan Ekonomi Desa', 'status' => $status, 'progress_percentage' => $progress,
            'start_date' => '2026-10-01', 'end_date' => '2027-01-31',
        ]);

        return $id;
    }

    private function main(string $url): string
    {
        $html = $this->actingAs($this->student->user)->get($url)->assertOk()->getContent();
        $start = strpos($html, '<main');

        return substr($html, $start, strpos($html, '</main>') - $start);
    }

    public function test_projects_empty_state_has_svg_one_cta_and_no_filter_or_hero(): void
    {
        $main = $this->main('/student/projects');

        $this->assertSame(1, substr_count($main, '<h1'));
        $this->assertStringContainsString('<header class="sg-page-header">', $main);
        $this->assertStringContainsString('no-applications.svg', $main);
        $this->assertStringContainsString('Belum ada proyek KKN', $main);
        $this->assertStringContainsString('href="'.route('student.browse-problems.index').'" class="sg-btn sg-btn--primary"', $main);
        $this->assertStringContainsString('Telusuri masalah KKN', $main);
        foreach (['role="search"', 'name="status"', '<table', 'marketplace-hero', '<style', 'fonts.googleapis'] as $gone) {
            $this->assertStringNotContainsString($gone, $main);
        }
    }

    public function test_active_project_row_shows_stage_progress_milestones_and_latest_report(): void
    {
        $id = $this->project('active', 50);
        DB::table('project_milestones')->insert([
            ['project_id' => $id, 'title' => 'Survei awal', 'order' => 1, 'target_date' => '2026-10-15', 'status' => 'completed', 'created_at' => now(), 'updated_at' => now()],
            ['project_id' => $id, 'title' => 'Pelatihan UMKM', 'order' => 2, 'target_date' => '2026-11-20', 'status' => 'in_progress', 'created_at' => now(), 'updated_at' => now()],
        ]);
        DB::table('project_reports')->insert([
            'project_id' => $id, 'student_id' => $this->student->id, 'type' => 'weekly', 'title' => 'Minggu 1', 'summary' => 'R', 'activities' => 'A',
            'period_start' => '2026-10-01', 'period_end' => '2026-10-07', 'status' => 'reviewed', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $main = $this->main('/student/projects');

        $this->assertStringContainsString('<table class="sg-table sg-table--stack">', $main);
        foreach (['Proyek', 'Tahap', 'Progres', 'Laporan terakhir', 'Periode'] as $column) {
            $this->assertStringContainsString('<th scope="col">'.$column.'</th>', $main);
        }
        $this->assertStringContainsString('href="'.route('student.projects.show', $id).'"', $main);
        $this->assertMatchesRegularExpression('/data-value="active">\s*<svg.*?<\/svg>\s*Berjalan/s', $main);
        $this->assertStringContainsString('<progress max="100" value="50"', $main);
        $this->assertStringContainsString('1 dari 2 milestone selesai', $main);
        $this->assertStringContainsString('Berikutnya: Pelatihan UMKM · 20 Nov 2026', $main);
        $this->assertStringContainsString('Mingguan · 1 Okt–7 Okt 2026', $main);
        $this->assertStringContainsString('data-value="reviewed"', $main);
        $this->assertStringContainsString('href="'.route('student.projects.create-report', $id).'"', $main);
        $this->assertMatchesRegularExpression('/<b>1<\/b> proyek.*<b>1<\/b> Berjalan/s', $main);
        $this->assertStringContainsString('role="search"', $main);
        $this->assertStringNotContainsString('name="search"', $main);
        $this->assertStringContainsString('<option value="on_hold"', $main);
    }

    public function test_completed_project_keeps_portfolio_toggle_endpoint(): void
    {
        $id = $this->project('completed', 100);

        $main = $this->main('/student/projects');

        $this->assertStringContainsString('data-portfolio-toggle="'.$id.'" aria-pressed="true"', $main);
        $this->assertStringContainsString('Belum ada laporan', $main);
        $this->assertStringContainsString('/toggle-portfolio', $this->actingAs($this->student->user)->get('/student/projects')->getContent());
        $this->actingAs($this->student->user)->postJson(route('student.projects.toggle-portfolio', $id))
            ->assertOk()->assertJson(['success' => true, 'is_visible' => false]);
        $this->assertStringNotContainsString('Unduh sertifikat', $main);
        $this->assertStringNotContainsString('Sertifikat No.', $main);
    }

    public function test_completed_project_with_certificate_shows_number_and_download(): void
    {
        $id = $this->project('completed', 100);
        DB::table('projects')->where('id', $id)->update([
            'certificate_path' => 'certificates/1/sertifikat.png',
            'certificate_number' => '007/KKN/MHS/STUDENTGO/XI/2026',
            'certificate_generated_at' => now(),
        ]);

        $main = $this->main('/student/projects');

        $this->assertStringContainsString('Sertifikat No. 007/KKN/MHS/STUDENTGO/XI/2026', $main);
        // href dari document_url(); di tes konfigurasi supabase kosong, jadi yang diuji elemen dan atributnya
        $this->assertMatchesRegularExpression('/<a href="[^"]*" target="_blank" download class="sg-btn sg-btn--secondary whitespace-nowrap">/', $main);
        $this->assertStringContainsString('Unduh sertifikat<span class="sr-only"> Pemberdayaan Ekonomi Desa</span>', $main);
        $this->assertStringContainsString('data-portfolio-toggle="'.$id.'"', $main);
    }

    public function test_filter_with_no_match_keeps_filter_and_reset(): void
    {
        $this->project('active');

        $main = $this->main('/student/projects?status=on_hold');

        $this->assertStringContainsString('role="search"', $main);
        $this->assertStringContainsString('Tidak ada proyek pada tahap ini', $main);
        $this->assertStringNotContainsString('<table', $main);
    }

    public function test_wishlist_empty_state(): void
    {
        $main = $this->main('/student/wishlist');

        $this->assertSame(1, substr_count($main, '<h1'));
        $this->assertStringContainsString('no-saved-projects.svg', $main);
        $this->assertStringContainsString('Telusuri masalah KKN', $main);
        $this->assertStringNotContainsString('<table', $main);
        $this->assertStringNotContainsString('sg-page-header__actions', $main);
    }

    public function test_wishlist_rows_with_lamar_hapus_and_states(): void
    {
        $open = $this->problem('Sistem Transportasi Ramah Lingkungan');
        $closed = $this->problem('Masalah Ditutup', ['status' => 'closed']);
        $past = $this->problem('Tenggat Lewat', ['application_deadline' => now()->subDay()->toDateString()]);
        $applied = $this->problem('Sudah Dilamar');
        DB::table('applications')->insert(['student_id' => $this->student->id, 'problem_id' => $applied, 'motivation' => 'M', 'status' => 'pending', 'applied_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
        foreach ([$open, $closed, $past, $applied] as $problemId) {
            DB::table('wishlists')->insert(['student_id' => $this->student->id, 'problem_id' => $problemId, 'notes' => $problemId === $open ? 'Cocok dengan jurusan saya' : null, 'created_at' => now(), 'updated_at' => now()]);
        }

        $main = $this->main('/student/wishlist');

        $this->assertStringContainsString('<b>4</b> masalah tersimpan', $main);
        foreach (['Masalah', 'Instansi', 'Tenggat'] as $column) {
            $this->assertStringContainsString('<th scope="col">'.$column.'</th>', $main);
        }
        $this->assertStringContainsString('href="'.route('student.browse-problems.show', $open).'"', $main);
        // lamar hanya untuk masalah yang masih dibuka dan belum dilamar
        $this->assertSame(1, preg_match_all('/>\s*Lamar\s*</', $main));
        $this->assertStringContainsString('href="'.route('student.applications.create', $open).'"', $main);
        $this->assertStringNotContainsString(route('student.applications.create', $closed), $main);
        $this->assertStringNotContainsString(route('student.applications.create', $past).'"', $main);
        $this->assertSame(2, substr_count($main, 'Pendaftaran ditutup'));   // ditutup + tenggat lewat
        $this->assertStringContainsString('data-value="pending"', $main);
        $this->assertStringContainsString('Catatan saya:', $main);
        $this->assertSame(4, substr_count($main, 'class="sg-btn sg-btn--tertiary whitespace-nowrap"'));
        $html = $this->actingAs($this->student->user)->get('/student/wishlist')->getContent();
        $this->assertStringContainsString('/student/wishlist/${wishlistId}/remove', $html);
        $this->assertStringContainsString("method: 'DELETE'", $html);
        $this->assertStringContainsString('window.sgConfirm(', $html);
        $this->assertStringNotContainsString('if (!confirm(', $html);
    }

    public function test_wishlist_remove_endpoint_unchanged_and_owner_scoped(): void
    {
        $problemId = $this->problem('Sistem Transportasi');
        $id = DB::table('wishlists')->insertGetId(['student_id' => $this->student->id, 'problem_id' => $problemId, 'created_at' => now(), 'updated_at' => now()]);
        $other = $this->makeStudent('Desi', 'Hartono');

        $this->actingAs($other->user)->deleteJson('/student/wishlist/'.$id.'/remove')->assertNotFound();
        $this->actingAs($this->student->user)->deleteJson('/student/wishlist/'.$id.'/remove')
            ->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseMissing('wishlists', ['id' => $id]);
    }
}
