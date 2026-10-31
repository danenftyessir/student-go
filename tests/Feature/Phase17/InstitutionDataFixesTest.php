<?php

namespace Tests\Feature\Phase17;

use App\Models\Institution;
use App\Models\ProjectMilestone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * phase 17, perbaikan yang disetujui user (2026-10-31):
 * 1) status lamaran 'reviewed' (nilai enum yang benar) bisa diputuskan instansi; kode lama memeriksa 'under_review'
 *    yang tidak pernah tersimpan. 3) milestone dapat ditandai 'wajib laporan' (kolom requires_report)
 *    dan tenggatnya tampil di dashboard instansi.
 */
class InstitutionDataFixesTest extends TestCase
{
    use BuildsPhase1Data;
    use RefreshDatabase;

    private Institution $institution;
    private int $problemId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRegion();
        $this->institution = $this->makeInstitution();
        [$this->problemId] = $this->makeOpenProblems($this->institution->id, 1);
    }

    private function application(string $status): int
    {
        $student = $this->makeStudent('Rani', 'Kusuma');

        return DB::table('applications')->insertGetId([
            'student_id' => $student->id, 'problem_id' => $this->problemId, 'motivation' => 'Motivasi',
            'status' => $status, 'applied_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function activeProject(): int
    {
        $student = $this->makeStudent('Bima', 'Saputra');
        $this->makeProject($student, $this->institution->id, $this->problemId);

        return (int) DB::table('projects')->where('student_id', $student->id)->value('id');
    }

    // ── 1. reviewed vs under_review ──

    public function test_reviewed_application_can_be_accepted(): void
    {
        $id = $this->application('reviewed');

        $this->actingAs($this->institution->user)
            ->get(route('institution.applications.review', $id))
            ->assertOk();

        $this->post(route('institution.applications.accept', $id), ['feedback' => 'Selamat bergabung'])
            ->assertRedirect(route('institution.applications.show', $id))
            ->assertSessionHas('success');

        $this->assertSame('accepted', DB::table('applications')->where('id', $id)->value('status'));
        $this->assertTrue(DB::table('projects')->where('application_id', $id)->exists());
    }

    public function test_reviewed_application_can_be_rejected(): void
    {
        $id = $this->application('reviewed');

        $this->actingAs($this->institution->user)
            ->post(route('institution.applications.reject', $id), ['feedback' => 'Belum sesuai', 'rejection_reason' => 'Kuota terpenuhi'])
            ->assertRedirect(route('institution.applications.show', $id));

        $this->assertSame('rejected', DB::table('applications')->where('id', $id)->value('status'));
    }

    public function test_decided_application_is_still_refused(): void
    {
        $id = $this->application('accepted');

        $this->actingAs($this->institution->user)
            ->post(route('institution.applications.reject', $id), ['feedback' => 'x', 'rejection_reason' => 'y'])
            ->assertSessionHas('error', 'aplikasi ini sudah diproses');

        $this->assertSame('accepted', DB::table('applications')->where('id', $id)->value('status'));
    }

    public function test_reviewed_filter_and_counts(): void
    {
        $reviewed = $this->application('reviewed');
        $this->application('pending');

        $html = $this->actingAs($this->institution->user)
            ->get(route('institution.applications.index', ['status' => 'reviewed']))
            ->assertOk()
            ->assertViewHas('stats', fn ($stats) => $stats['under_review'] === 1 && $stats['pending'] === 1)
            ->getContent();
        $this->assertStringContainsString(route('institution.applications.show', $reviewed), $html);
        $this->assertStringContainsString('<option value="reviewed" selected', $html);

        // dashboard: menunggu keputusan = pending + reviewed
        $dashboard = $this->get(route('institution.dashboard'))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/<b>2<\/b> lamaran menunggu keputusan/', $dashboard);
        $this->assertMatchesRegularExpression('/data-value="reviewed">\s*<svg[^>]*>.*?<\/svg>\s*Ditinjau/s', $dashboard);
    }

    // ── 3. tenggat laporan pada milestone ──

    public function test_column_exists_and_defaults_to_false(): void
    {
        $this->assertTrue(Schema::hasColumn('project_milestones', 'requires_report'));

        $projectId = $this->activeProject();
        $this->actingAs($this->institution->user)
            ->post(route('institution.projects.add-milestone', $projectId), [
                'title' => 'Survei awal', 'target_date' => '2099-01-10',
            ])
            ->assertRedirect(route('institution.projects.manage', $projectId));

        $this->assertFalse(ProjectMilestone::where('project_id', $projectId)->firstOrFail()->requires_report);
    }

    public function test_institution_marks_a_milestone_as_report_due(): void
    {
        $projectId = $this->activeProject();
        $this->actingAs($this->institution->user)
            ->post(route('institution.projects.add-milestone', $projectId), [
                'title' => 'Laporan tengah', 'target_date' => '2099-02-15', 'requires_report' => '1',
            ]);

        $milestone = ProjectMilestone::where('project_id', $projectId)->firstOrFail();
        $this->assertTrue($milestone->requires_report);

        // edit lewat json (seperti modal edit di halaman kelola proyek)
        $this->getJson(route('institution.projects.get-milestone', [$projectId, $milestone->id]))
            ->assertOk()->assertJsonPath('milestone.requires_report', true);
        $this->putJson(route('institution.projects.update-milestone', [$projectId, $milestone->id]), ['requires_report' => false])
            ->assertOk()->assertJsonPath('milestone.requires_report', false);
        $this->putJson(route('institution.projects.update-milestone', [$projectId, $milestone->id]), ['requires_report' => true])
            ->assertOk()->assertJsonPath('milestone.requires_report', true);

        $this->get(route('institution.projects.manage', $projectId))->assertOk()->assertSee('Wajib laporan');
    }

    public function test_milestone_delete_no_longer_fails(): void
    {
        $projectId = $this->activeProject();
        $id = DB::table('project_milestones')->insertGetId([
            'project_id' => $projectId, 'title' => 'Survei awal', 'order' => 1, 'target_date' => '2099-01-10',
            'status' => 'pending', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($this->institution->user)
            ->deleteJson(route('institution.projects.delete-milestone', [$projectId, $id]))
            ->assertOk()->assertJsonPath('success', true);
        $this->assertFalse(DB::table('project_milestones')->where('id', $id)->exists());
    }

    public function test_dashboard_shows_the_next_report_due(): void
    {
        $projectId = $this->activeProject();
        DB::table('project_milestones')->insert([
            ['project_id' => $projectId, 'title' => 'Survei awal', 'order' => 1, 'target_date' => '2099-01-10', 'status' => 'in_progress', 'requires_report' => false, 'created_at' => now(), 'updated_at' => now()],
            ['project_id' => $projectId, 'title' => 'Laporan tengah', 'order' => 2, 'target_date' => '2099-02-15', 'status' => 'pending', 'requires_report' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $html = $this->actingAs($this->institution->user)->get(route('institution.dashboard'))->assertOk()->getContent();
        $this->assertStringContainsString('Laporan jatuh tempo: Laporan tengah · 15 Feb 2099', $html);
        $this->assertStringContainsString('Milestone berikutnya: Survei awal', $html);
    }

    public function test_dashboard_does_not_repeat_the_same_milestone(): void
    {
        $projectId = $this->activeProject();
        DB::table('project_milestones')->insert([
            'project_id' => $projectId, 'title' => 'Laporan akhir', 'order' => 1, 'target_date' => '2020-03-01',
            'status' => 'pending', 'requires_report' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $html = $this->actingAs($this->institution->user)->get(route('institution.dashboard'))->assertOk()->getContent();
        $this->assertStringContainsString('Laporan jatuh tempo: Laporan akhir · 1 Mar 2020 (lewat tenggat)', $html);
        $this->assertStringNotContainsString('Milestone berikutnya: Laporan akhir', $html);
    }
}
