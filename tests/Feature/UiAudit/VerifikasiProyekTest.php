<?php

namespace Tests\Feature\UiAudit;

use App\Models\Project;
use App\Support\ProjectVerification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * masukan juri (2026-11-14): halaman publik /verifikasi/{kode} untuk proyek KKN yang selesai dan divalidasi instansi,
 * ditautkan dari portofolio. kode bertanda tangan, tanpa data kontak, hanya proyek selesai yang tampil di portofolio.
 */
class VerifikasiProyekTest extends TestCase
{
    use BuildsPhase1Data;
    use RefreshDatabase;

    private function completedProject(array $attrs = []): Project
    {
        $this->seedRegion();
        $institution = $this->makeInstitution();
        $problemId = $this->makeOpenProblems($institution->id, 1)[0];
        $student = $this->makeStudent('Sari', 'Wulandari');
        $this->makeProject($student, $institution->id, $problemId);
        $project = Project::where('student_id', $student->id)->firstOrFail();
        $project->update(array_merge(['status' => 'completed', 'is_portfolio_visible' => true, 'actual_end_date' => '2026-03-01'], $attrs));

        return $project->fresh();
    }

    public function test_valid_code_shows_verified_project_without_contact_data(): void
    {
        $project = $this->completedProject();
        $html = $this->get(route('project.verify', ProjectVerification::code($project)))->assertOk()->getContent();

        $this->assertStringContainsString('Terverifikasi', $html);
        $this->assertStringContainsString($project->problem->institution->name, $html);
        $this->assertStringContainsString('Sari Wulandari', $html);
        $this->assertStringNotContainsString($project->student->user->email, $html);
    }

    public function test_tampered_unfinished_or_hidden_projects_are_not_found(): void
    {
        $project = $this->completedProject();
        $code = ProjectVerification::code($project);

        $this->get('/verifikasi/'.$project->id.'-0000000000')->assertNotFound();
        $this->get('/verifikasi/'.($project->id + 1).substr($code, strlen((string) $project->id)))->assertNotFound();
        $this->get('/verifikasi/bukan-kode')->assertNotFound();

        $project->update(['is_portfolio_visible' => false]);
        $this->get('/verifikasi/'.$code)->assertNotFound();

        $project->update(['is_portfolio_visible' => true, 'status' => 'active']);
        $this->get('/verifikasi/'.$code)->assertNotFound();
    }

    public function test_portfolio_links_each_completed_project_to_its_verification_page(): void
    {
        $project = $this->completedProject();
        $html = $this->get(route('profile.public', $project->student->user->username))->assertOk()->getContent();

        $this->assertStringContainsString('href="'.route('project.verify', ProjectVerification::code($project)).'"', $html);
        $this->assertStringContainsString('Cek keaslian', $html);
    }
}
