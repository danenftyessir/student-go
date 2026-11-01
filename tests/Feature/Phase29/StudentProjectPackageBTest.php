<?php

namespace Tests\Feature\Phase29;

use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * phase 29 paket B: detail proyek aktif mahasiswa dan dua formulir laporan.
 * progres = meter tahap dari milestone (persen tersimpan tidak tampil), status dari StatusVocabulary,
 * kontrak formulir (action, method, enctype, name/id, accept, batas ukuran) sama dengan baseline.
 */
class StudentProjectPackageBTest extends TestCase
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

    private function project(Student $student, array $attrs = []): int
    {
        $problemId = $this->makeOpenProblems($this->institutionId, 1)[0];
        $this->makeProject($student, $this->institutionId, $problemId);
        $id = DB::table('projects')->latest('id')->value('id');
        DB::table('projects')->where('id', $id)->update(array_merge([
            'title' => 'Pembangkit Listrik Mikrohidro', 'status' => 'active', 'progress_percentage' => 89,
            'start_date' => '2026-04-30', 'end_date' => '2026-07-30',
        ], $attrs));

        return $id;
    }

    private function milestones(int $projectId): void
    {
        $row = fn (string $title, int $order, string $status, string $date) => [
            'project_id' => $projectId, 'title' => $title, 'order' => $order, 'target_date' => $date,
            'status' => $status, 'progress_percentage' => 0, 'created_at' => now(), 'updated_at' => now(),
        ];
        DB::table('project_milestones')->insert([
            $row('Survei dan pemetaan masalah', 1, 'completed', '2026-05-07'),
            $row('Perencanaan program', 2, 'completed', '2026-05-14'),
            $row('Pelaksanaan kegiatan utama', 3, 'completed', '2026-06-11'),
            $row('Evaluasi dan dokumentasi', 4, 'in_progress', '2026-07-30'),
        ]);
    }

    private function main(string $url): string
    {
        $html = $this->actingAs($this->student->user)->get($url)->assertOk()->getContent();
        $start = strpos($html, '<main');

        return substr($html, $start, strpos($html, '</main>') - $start);
    }

    public function test_detail_shows_stage_meter_from_milestones_and_not_the_stored_percentage(): void
    {
        $id = $this->project($this->student);
        $this->milestones($id);

        $main = $this->main("/student/projects/{$id}");

        $this->assertSame(1, substr_count($main, '<h1'));
        $this->assertStringContainsString('<header class="sg-page-header sg-sp-header">', $main);
        $this->assertMatchesRegularExpression('/<span class="sg-inst-stages">.*?3 dari 4 tahap/s', $main);
        $this->assertSame(3, preg_match_all('/<i\s+data-done\s*><\/i>/', $main));
        $this->assertSame(1, preg_match_all('/<i\s*><\/i>/', $main));
        foreach (['89%', 'Progress Keseluruhan', 'bg-gradient', 'from-indigo', 'bg-purple', '<style', 'style="width'] as $gone) {
            $this->assertStringNotContainsString($gone, $main);
        }
    }

    public function test_detail_has_no_english_labels_and_uses_vocabulary_and_indonesian_relative_time(): void
    {
        $id = $this->project($this->student, ['end_date' => now()->subDays(3)->toDateString()]);
        $this->milestones($id);
        DB::table('project_reports')->insert([
            'project_id' => $id, 'student_id' => $this->student->id, 'type' => 'monthly', 'title' => 'Laporan Bulanan #3',
            'summary' => 'Ringkasan', 'activities' => 'A', 'period_start' => '2026-06-25', 'period_end' => '2026-07-23',
            'status' => 'pending', 'created_at' => now()->subDay(), 'updated_at' => now()->subDay(),
        ]);

        $main = $this->main("/student/projects/{$id}");

        foreach (['Overdue', 'day ago', 'days ago', '>Update', 'Progress', 'Submit', 'Download', 'Impact Metrics', 'Menunggu Review', 'Unknown'] as $english) {
            $this->assertStringNotContainsString($english, $main);
        }
        $this->assertStringContainsString('Melewati tanggal selesai', $main);
        $this->assertStringContainsString('dikirim 1 hari yang lalu', $main);
        $this->assertMatchesRegularExpression('/data-value="active">\s*<svg.*?<\/svg>\s*Berjalan/s', $main);
        $this->assertMatchesRegularExpression('/data-value="pending">\s*<svg.*?<\/svg>\s*Menunggu/s', $main);
        $this->assertMatchesRegularExpression('/data-value="in_progress">\s*<svg.*?<\/svg>\s*Berjalan/s', $main);
    }

    public function test_detail_keeps_milestone_form_actions_and_final_report_gate(): void
    {
        $id = $this->project($this->student);
        $this->milestones($id);
        $open = DB::table('project_milestones')->where('project_id', $id)->where('status', 'in_progress')->value('id');

        $main = $this->main("/student/projects/{$id}");

        $this->assertStringContainsString('@submit.prevent="updateMilestone('.$open.', $event)"', $main);
        $this->assertStringContainsString('name="progress_percentage"', $main);
        $this->assertStringContainsString('name="notes"', $main);
        $this->assertSame(1, substr_count($main, 'updateMilestone('));
        $this->assertStringContainsString('href="'.route('student.projects.create-report', $id).'"', $main);
        $this->assertStringContainsString('href="'.route('student.projects.create-final-report', $id).'"', $main);
        $html = $this->actingAs($this->student->user)->get("/student/projects/{$id}")->getContent();
        $this->assertStringContainsString('/student/projects/milestones/${milestoneId}/update', $html);

        // di bawah 80 % tombol laporan akhir tidak muncul (syarat lama tetap)
        DB::table('projects')->where('id', $id)->update(['progress_percentage' => 75]);
        $this->assertStringNotContainsString(route('student.projects.create-final-report', $id), $this->main("/student/projects/{$id}"));
    }

    public function test_report_form_contract_is_unchanged(): void
    {
        $id = $this->project($this->student);

        $main = $this->main("/student/projects/{$id}/report/create");

        $this->assertStringContainsString('<form action="'.route('student.projects.store-report', $id).'" method="POST" enctype="multipart/form-data">', $main);
        foreach (['type', 'title', 'period_start', 'period_end', 'summary', 'activities', 'challenges', 'next_plans', 'document', 'photos[]'] as $name) {
            $this->assertStringContainsString('name="'.$name.'"', $main);
        }
        $this->assertMatchesRegularExpression('/name="document" id="documentUpload"[^>]*accept="\.pdf,\.doc,\.docx"/s', $main);
        $this->assertMatchesRegularExpression('/name="photos\[\]" id="photosUpload"[^>]*accept="image\/\*"\s+multiple/s', $main);
        $this->assertStringContainsString('<option value="weekly"', $main);
        $this->assertStringContainsString('<option value="monthly"', $main);
        $this->assertStringContainsString('PDF, DOC, DOCX maksimal 10MB', $main);
        $this->assertStringContainsString('JPG, PNG maksimal 5MB per foto', $main);
        foreach (['<style', 'Optional', 'Submit', '>Upload', 'bg-gradient'] as $gone) {
            $this->assertStringNotContainsString($gone, $main);
        }
    }

    public function test_final_report_form_contract_is_unchanged_and_confirms_with_the_shared_dialog(): void
    {
        $id = $this->project($this->student);

        $main = $this->main("/student/projects/{$id}/final-report/create");

        $this->assertMatchesRegularExpression('/<form action="'.preg_quote(route('student.projects.store-final-report', $id), '/').'" method="POST" enctype="multipart\/form-data"\s+data-sg-confirm=/', $main);
        foreach (['summary', 'activities', 'beneficiaries', 'activities_count'] as $name) {
            $this->assertStringContainsString('name="'.$name.'"', $main);
        }
        $this->assertMatchesRegularExpression('/name="final_report" id="finalReportUpload"[^>]*accept="\.pdf"[^>]*required/s', $main);
        $this->assertStringContainsString('PDF maksimal 20MB', $main);
        $this->assertSame(3, substr_count($main, '<input type="checkbox" required>'));
        $this->assertMatchesRegularExpression('/name="beneficiaries"[^>]*value="0"/', $main);
        foreach (['<style', 'confirm(', 'Impact Metrics', 'Submit', '>Upload', 'bg-gradient'] as $gone) {
            $this->assertStringNotContainsString($gone, $main);
        }
    }

    public function test_report_submit_still_stores_and_redirects_to_the_detail(): void
    {
        $id = $this->project($this->student);

        $this->actingAs($this->student->user)->post(route('student.projects.store-report', $id), [
            'type' => 'weekly', 'title' => 'Minggu 1', 'summary' => 'Ringkasan', 'activities' => 'Kegiatan',
            'period_start' => '2026-05-01', 'period_end' => '2026-05-07',
        ])->assertRedirect(route('student.projects.show', $id))->assertSessionHas('success');

        $this->assertDatabaseHas('project_reports', ['project_id' => $id, 'title' => 'Minggu 1', 'type' => 'weekly']);

        // validasi lama tetap: periode berakhir harus setelah periode mulai
        $this->actingAs($this->student->user)->from(route('student.projects.create-report', $id))
            ->post(route('student.projects.store-report', $id), [
                'type' => 'weekly', 'title' => 'X', 'summary' => 'R', 'activities' => 'A',
                'period_start' => '2026-05-07', 'period_end' => '2026-05-01',
            ])->assertSessionHasErrors('period_end');
    }

    public function test_other_students_project_pages_still_404(): void
    {
        $other = $this->makeStudent('Budi', 'Santoso');
        $id = $this->project($other);

        foreach (["/student/projects/{$id}", "/student/projects/{$id}/report/create", "/student/projects/{$id}/final-report/create"] as $url) {
            $this->actingAs($this->student->user)->get($url)->assertNotFound();
        }
    }
}
