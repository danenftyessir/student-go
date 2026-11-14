<?php

namespace Tests\Feature\UiAudit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * audit ui/ux F-08: tanggal reviewed_at diberi label "Diputuskan" hanya bila lamaran diterima/ditolak;
 * lamaran yang baru ditinjau memakai label "Ditinjau". dirender sungguhan agar markup komponen ikut teruji.
 */
class LabelKeputusanTest extends TestCase
{
    use BuildsPhase1Data;
    use RefreshDatabase;

    private function detail(string $status): string
    {
        $this->seedRegion();
        $institution = $this->makeInstitution();
        $problemId = $this->makeOpenProblems($institution->id, 1)[0];
        $student = $this->makeStudent('Sari', 'Wulandari');
        $id = DB::table('applications')->insertGetId([
            'student_id' => $student->id, 'problem_id' => $problemId, 'status' => $status,
            'motivation' => 'Saya ingin membantu posyandu desa.', 'applied_at' => now()->subDays(3),
            'reviewed_at' => now()->subDay(), 'created_at' => now()->subDays(3), 'updated_at' => now(),
        ]);

        return $this->actingAs($institution->user)->get(route('institution.applications.show', $id))->assertOk()->getContent();
    }

    public function test_reviewed_application_is_labelled_ditinjau(): void
    {
        $html = $this->detail('reviewed');

        $this->assertMatchesRegularExpression('#<dt>\s*Ditinjau\s*</dt>#', $html);
        $this->assertDoesNotMatchRegularExpression('#<dt>\s*Diputuskan\s*</dt>#', $html);
        // kode php tidak boleh bocor ke halaman
        $this->assertStringNotContainsString('in_array(', $html);
    }

    public function test_accepted_application_is_labelled_diputuskan(): void
    {
        $html = $this->detail('accepted');

        $this->assertMatchesRegularExpression('#<dt>\s*Diputuskan\s*</dt>#', $html);
        $this->assertStringNotContainsString('in_array(', $html);
    }
}
