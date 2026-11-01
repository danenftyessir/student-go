<?php

namespace Tests\Feature\Phase29;

use App\Models\Institution;
use App\Models\Project;
use App\Models\VerificationDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * phase 29 paket D: kelola proyek (/institution/projects/{id}/manage) dan status verifikasi.
 * kontrak form dan endpoint json tetap; status dari kosakata bersama; kalimat "AI" tetap (D-09 OPEN).
 * semua submit di sini terjadi pada data buatan test (sqlite in-memory), bukan data akun uji.
 */
class InstitutionPackageDTest extends TestCase
{
    use BuildsPhase1Data;
    use RefreshDatabase;

    private const VIEWS = [
        'resources/views/institution/projects/manage.blade.php',
        'resources/views/institution/verification/status.blade.php',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRegion();
    }

    /** proyek dengan empat milestone (dua selesai, satu lewat target) dan tiga laporan */
    private function project(Institution $institution, string $status = 'active'): Project
    {
        $problemId = $this->makeOpenProblems($institution->id, 1)[0];
        $student = $this->makeStudent('Bima', 'Saputra');
        $this->makeProject($student, $institution->id, $problemId);
        $project = Project::where('student_id', $student->id)->firstOrFail();
        $project->update(['status' => $status, 'progress_percentage' => 41, 'title' => 'Sanitasi Air Bersih Desa']);
        foreach (['Survei awal', 'Perencanaan', 'Pelaksanaan', 'Evaluasi'] as $i => $title) {
            DB::table('project_milestones')->insert([
                'project_id' => $project->id, 'title' => $title, 'order' => $i + 1, 'target_date' => '2026-0'.($i + 1).'-10',
                'status' => $i < 2 ? 'completed' : 'pending', 'requires_report' => $i === 3,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        foreach ([
            ['Laporan Mingguan #1', 'weekly', 'approved', 'Bagus, lanjutkan.'],
            ['Laporan Mingguan #2', 'weekly', 'revision_needed', 'Lengkapi foto kegiatan.'],
            ['Laporan Bulanan #3', 'monthly', 'pending', null],
        ] as [$title, $type, $st, $feedback]) {
            DB::table('project_reports')->insert([
                'project_id' => $project->id, 'student_id' => $student->id, 'type' => $type, 'title' => $title,
                'summary' => 'Ringkasan '.$title, 'activities' => 'Kegiatan', 'period_start' => '2026-01-01', 'period_end' => '2026-01-07',
                'status' => $st, 'institution_feedback' => $feedback, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        return $project->fresh();
    }

    private function milestoneId(Project $project, string $title): int
    {
        return (int) DB::table('project_milestones')->where('project_id', $project->id)->where('title', $title)->value('id');
    }

    public function test_manage_keeps_every_form_field_and_option_value(): void
    {
        $institution = $this->makeInstitution();
        $project = $this->project($institution);

        $html = $this->actingAs($institution->user)->get(route('institution.projects.manage', $project->id))->assertOk()->getContent();

        // tambah milestone: POST biasa ke route yang sama, csrf, field dan hidden requires_report=0
        $this->assertMatchesRegularExpression('/<form method="POST" action="'.preg_quote(route('institution.projects.add-milestone', $project->id), '/').'"/', $html);
        $this->assertMatchesRegularExpression('/<input type="text" name="title" required/', $html);
        $this->assertMatchesRegularExpression('/<textarea name="description" rows="3"/', $html);
        $this->assertMatchesRegularExpression('/<input type="date" name="target_date" required/', $html);
        $this->assertStringContainsString('<input type="hidden" name="requires_report" value="0">', $html);
        $this->assertMatchesRegularExpression('/<input type="checkbox" name="requires_report" value="1">/', $html);
        // setujui / minta revisi: form POST tanpa action (diisi js), textarea feedback (wajib pada revisi)
        $this->assertMatchesRegularExpression('/<form id="approve-form" method="POST"/', $html);
        $this->assertMatchesRegularExpression('/<form id="reject-form" method="POST"/', $html);
        $this->assertDoesNotMatchRegularExpression('/<form id="(approve|reject)-form"[^>]*action=/', $html);
        $this->assertSame(2, substr_count($html, '<textarea name="feedback"'));
        $this->assertMatchesRegularExpression('/<textarea name="feedback" rows="4" required/', $html);
        // edit milestone: id dan name yang dibaca js tetap
        $this->assertStringContainsString('<form id="edit-milestone-form" onsubmit="submitEditMilestone(event)"', $html);
        foreach (['<input type="hidden" id="edit-milestone-id">', 'id="edit-milestone-title" name="title"', 'id="edit-milestone-description" name="description"',
            'id="edit-milestone-target-date" name="target_date"', 'id="edit-milestone-status" name="status"', 'id="edit-milestone-requires-report" name="requires_report" value="1"'] as $needle) {
            $this->assertStringContainsString($needle, $html);
        }
        // nilai opsi persis seperti sebelumnya (label boleh berubah)
        preg_match('/<select id="project-status"[^>]*>(.*?)<\/select>/s', $html, $m);
        preg_match_all('/value="([^"]+)"/', $m[1], $values);
        $this->assertSame(['planning', 'active', 'review', 'completed', 'cancelled'], $values[1]);
        $this->assertMatchesRegularExpression('/<option value="active" selected>Berjalan<\/option>/', $html);
        preg_match('/<select id="edit-milestone-status"[^>]*>(.*?)<\/select>/s', $html, $m);
        preg_match_all('/value="([^"]+)"/', $m[1], $values);
        $this->assertSame(['pending', 'in_progress', 'completed'], $values[1]);
        // endpoint fetch dan verb tetap
        $base = '/institution/projects/'.$project->id;
        $this->assertStringContainsString("fetch(`{$base}/status`", $html);
        $this->assertSame(3, substr_count($html, "fetch(`{$base}/milestones/\${milestoneId}`"));
        $this->assertStringContainsString("fetch(`{$base}/milestones/\${milestoneId}`)", $html);
        foreach (["method: 'POST'", "method: 'PUT'", "method: 'DELETE'"] as $verb) {
            $this->assertStringContainsString($verb, $html);
        }
        $this->assertStringContainsString("form.action = `{$base}/reports/\${reportId}/approve`", $html);
        $this->assertStringContainsString("form.action = `{$base}/reports/\${reportId}/reject`", $html);
        // prefill tanggal edit memakai bagian yyyy-mm-dd dari json
        $this->assertStringContainsString("(milestone.target_date || '').slice(0, 10)", $html);
        // hapus memakai dialog konfirmasi bersama, bukan confirm()/alert()
        $this->assertStringContainsString('window.sgConfirm(', $html);
        $this->assertDoesNotMatchRegularExpression('/[^.]\b(alert|confirm)\(/', $html);
    }

    public function test_manage_is_a_work_surface_with_stage_meter_and_shared_status_words(): void
    {
        $institution = $this->makeInstitution();
        $project = $this->project($institution);

        $html = $this->actingAs($institution->user)->get(route('institution.projects.manage', $project->id))->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, '<h1'));
        $this->assertStringContainsString('Kelola proyek', $html);
        $this->assertStringContainsString('Sanitasi Air Bersih Desa', $html);
        // meter tahap dari milestone; persen tersimpan (41) tidak tampil
        $this->assertStringContainsString('2 dari 4 tahap', $html);
        $this->assertStringNotContainsString('41%', $html);
        // kosakata bersama
        $this->assertStringContainsString('data-value="completed"', $html);
        $this->assertStringContainsString('Menunggu', $html);
        $this->assertStringContainsString('Lewat target', $html);
        $this->assertStringContainsString('Wajib laporan', $html);
        // riwayat memuat laporan revision_needed (nilai yang ditulis rejectReport)
        $this->assertStringContainsString('Riwayat tinjauan', $html);
        $this->assertStringContainsString('Laporan Mingguan #2', $html);
        $this->assertStringContainsString('Perlu Revisi', $html);
        $this->assertStringContainsString('Masukan: Lengkapi foto kegiatan.', $html);
        // laporan menunggu: tombol setujui / minta revisi
        $this->assertStringContainsString('showApproveModal(', $html);
        $this->assertStringContainsString('showRejectModal(', $html);
        $this->assertStringContainsString('3 laporan · 1 menunggu tinjauan', $html);
        // dialog native bersama
        foreach (['add-milestone-modal', 'approve-modal', 'reject-modal', 'edit-milestone-modal'] as $id) {
            $this->assertMatchesRegularExpression('/<dialog id="'.$id.'" class="sg-dialog"/', $html);
        }
        // label inggris lama tidak tersisa
        $text = strip_tags(preg_replace('/<script\b.*?<\/script>/s', '', $html));
        foreach (['Milestone Management', 'Overdue', 'Pending Review', 'Total Milestones', 'Download Dokumen', 'Actions', 'Progress', '>Update<', 'Planning'] as $english) {
            $this->assertStringNotContainsString($english, $text, $english);
        }
    }

    public function test_review_action_only_for_completed_projects_without_rating(): void
    {
        $institution = $this->makeInstitution();
        $project = $this->project($institution, 'completed');

        $html = $this->actingAs($institution->user)->get(route('institution.projects.manage', $project->id))->assertOk()->getContent();
        $this->assertStringContainsString('href="'.route('institution.reviews.create', $project->id).'"', $html);

        $project->update(['status' => 'active']);
        $html = $this->get(route('institution.projects.manage', $project->id))->getContent();
        $this->assertStringNotContainsString('href="'.route('institution.reviews.create', $project->id).'"', $html);
    }

    public function test_milestone_endpoints_still_answer_json_on_test_rows(): void
    {
        $institution = $this->makeInstitution();
        $project = $this->project($institution);
        $id = $this->milestoneId($project, 'Pelaksanaan');
        $this->actingAs($institution->user);

        $this->getJson(route('institution.projects.get-milestone', [$project->id, $id]))
            ->assertOk()->assertJsonPath('success', true)->assertJsonPath('milestone.title', 'Pelaksanaan');

        $this->putJson(route('institution.projects.update-milestone', [$project->id, $id]), [
            'title' => 'Pelaksanaan inti', 'description' => '', 'target_date' => '2026-03-20', 'status' => 'completed', 'requires_report' => true,
        ])->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseHas('project_milestones', ['id' => $id, 'title' => 'Pelaksanaan inti', 'status' => 'completed']);

        // milestone selesai tidak dapat dihapus (aturan controller lama); yang belum selesai dapat
        $this->deleteJson(route('institution.projects.delete-milestone', [$project->id, $id]))->assertStatus(400)->assertJsonPath('success', false);
        $pending = $this->milestoneId($project, 'Evaluasi');
        $this->deleteJson(route('institution.projects.delete-milestone', [$project->id, $pending]))->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseMissing('project_milestones', ['id' => $pending]);

        $this->postJson(route('institution.projects.update-status', $project->id), ['status' => 'on_hold'])->assertOk()->assertJsonPath('success', true);
        $this->assertSame('on_hold', $project->fresh()->status);

        // nilai opsi lama yang tidak ada di enum tetap ditolak seperti sebelumnya (tidak diubah di paket D)
        $this->postJson(route('institution.projects.update-status', $project->id), ['status' => 'planning'])->assertStatus(422);
    }

    public function test_add_milestone_and_report_review_forms_post_as_before(): void
    {
        $institution = $this->makeInstitution();
        $project = $this->project($institution);
        $reportId = (int) DB::table('project_reports')->where('project_id', $project->id)->where('status', 'pending')->value('id');
        $this->actingAs($institution->user);

        $this->post(route('institution.projects.add-milestone', $project->id), [
            'title' => 'Serah terima', 'description' => 'Dokumen akhir', 'target_date' => '2026-05-01', 'requires_report' => '1',
        ])->assertRedirect();
        $this->assertDatabaseHas('project_milestones', ['project_id' => $project->id, 'title' => 'Serah terima', 'requires_report' => true]);

        $this->post("/institution/projects/{$project->id}/reports/{$reportId}/reject", ['feedback' => 'Tambahkan daftar hadir.'])->assertRedirect();
        $this->assertDatabaseHas('project_reports', ['id' => $reportId, 'status' => 'revision_needed']);

        $this->post("/institution/projects/{$project->id}/reports/{$reportId}/approve", ['feedback' => ''])->assertRedirect();
        $this->assertDatabaseHas('project_reports', ['id' => $reportId, 'status' => 'approved']);
    }

    public function test_other_institutions_project_still_404(): void
    {
        $owner = $this->makeInstitution();
        $project = $this->project($owner);
        $other = $this->makeInstitution();

        $this->actingAs($other->user)->get(route('institution.projects.manage', $project->id))->assertNotFound();
    }

    public function test_status_page_keeps_d09_wording_and_uses_shared_status(): void
    {
        $institution = $this->makeInstitution();
        $institution->update(['verification_status' => 'needs_review', 'verification_score' => 72.5, 'verification_confidence' => 0.8]);
        VerificationDocument::create(['institution_id' => $institution->id, 'document_type' => 'official_letter', 'file_url' => 'x',
            'file_name' => 'sk-pengangkatan-kepala-dinas-kesehatan-kabupaten-bogor-2026-final.pdf', 'file_size' => 734003, 'mime_type' => 'application/pdf',
            'ai_status' => 'needs_review', 'ai_score' => 64, 'ai_reasoning' => 'Stempel tidak terbaca jelas.',
            'ai_flags' => [['type' => 'warning', 'message' => 'Stempel buram', 'severity' => 6]],
            'ai_extracted_data' => ['institution_name' => 'Dinas Kesehatan'], 'ai_processed_at' => now()]);
        VerificationDocument::create(['institution_id' => $institution->id, 'document_type' => 'npwp', 'file_url' => 'x',
            'file_name' => 'npwp.pdf', 'file_size' => 2048, 'mime_type' => 'application/pdf']);

        $html = $this->actingAs($institution->user)->get(route('institution.verification.status'))->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, '<h1'));
        // D-09: kalimat "AI" kata per kata
        foreach (['Hasil verifikasi AI untuk '.e($institution->name), 'AI Score', 'Hasil Analisis AI', 'Belum diverifikasi AI'] as $sentence) {
            $this->assertStringContainsString($sentence, $html, $sentence);
        }
        $this->assertStringContainsString('#shield-check', $html);
        $this->assertStringContainsString('Perlu Ditinjau', $html);
        $this->assertStringContainsString('data-value="needs_review"', $html);
        $this->assertStringContainsString('72,5/100', $html);
        $this->assertStringContainsString('80%', $html);
        $this->assertStringContainsString('Stempel tidak terbaca jelas.', $html);
        $this->assertStringContainsString('Peringatan', $html);
        $this->assertStringContainsString('Tingkat keparahan: 6/10', $html);
        $this->assertStringContainsString('Institution name', $html);
        $this->assertStringContainsString(route('institution.verification.upload'), $html);
        $this->assertStringContainsString(route('institution.dashboard'), $html);
        $text = strip_tags($html);
        foreach (['Verification Score', 'Confidence Level', 'Severity', 'Needs review'] as $english) {
            $this->assertStringNotContainsString($english, $text, $english);
        }
        // url berkas privat tidak ditautkan
        $this->assertStringNotContainsString('href="x"', $html);
    }

    public function test_status_page_shows_active_as_verified_and_no_invented_score(): void
    {
        $institution = $this->makeInstitution();
        $institution->update(['verification_status' => 'active', 'verification_score' => null, 'verification_confidence' => null]);

        $html = $this->actingAs($institution->user)->get(route('institution.verification.status'))->assertOk()->getContent();

        $this->assertStringContainsString('data-value="active"', $html);
        $this->assertStringContainsString('Terverifikasi', $html);
        $this->assertStringNotContainsString('/100', $html);
        $this->assertStringNotContainsString('Tingkat keyakinan', $html);
        $this->assertStringContainsString('Belum ada dokumen yang diupload', $html);
    }

    public function test_views_have_no_style_block_and_no_hex_colour(): void
    {
        foreach (self::VIEWS as $view) {
            $source = file_get_contents(base_path($view));
            $this->assertSame(0, substr_count($source, '<style'), $view);
            $this->assertDoesNotMatchRegularExpression('/#[0-9a-fA-F]{3,8}\b/', $source, $view);
        }
    }
}
