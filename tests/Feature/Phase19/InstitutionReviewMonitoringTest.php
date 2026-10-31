<?php

namespace Tests\Feature\Phase19;

use App\Models\Institution;
use App\Models\Notification;
use App\Models\Project;
use App\Models\Review;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * phase 19: antrean pelamar + detail (split view), keputusan Terima/Tolak (endpoint, field dan aturan tetap;
 * notifikasi dan cek kuota disetujui user), tabel proyek dengan meter tahap dari milestone dan laporan terakhir,
 * daftar dan formulir ulasan (store diperbaiki, disetujui user), dashboard "menunggu ulasan" dari tabel reviews.
 */
class InstitutionReviewMonitoringTest extends TestCase
{
    use BuildsPhase1Data;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRegion();
    }

    private function application(int $problemId, Student $student, string $status = 'pending', array $attrs = []): int
    {
        return DB::table('applications')->insertGetId(array_merge([
            'student_id' => $student->id, 'problem_id' => $problemId, 'status' => $status,
            'motivation' => 'Saya ingin membantu posyandu desa.', 'cover_letter' => 'Yth. panitia, saya siap bergabung.',
            'applied_at' => now()->subDays(2), 'created_at' => now()->subDays(2), 'updated_at' => now(),
        ], $attrs));
    }

    /** proyek dengan empat milestone (dua selesai) dan dua laporan; progress tersimpan sengaja tidak cocok */
    private function project(Institution $institution, string $status = 'active'): Project
    {
        $problemId = $this->makeOpenProblems($institution->id, 1)[0];
        $student = $this->makeStudent('Bima', 'Saputra');
        $this->makeProject($student, $institution->id, $problemId);
        $project = Project::where('student_id', $student->id)->firstOrFail();
        $project->update(['status' => $status, 'progress_percentage' => 41, 'title' => 'Sanitasi Air Bersih Desa',
            'actual_end_date' => $status === 'completed' ? '2026-03-01' : null]);
        foreach (['Survei awal', 'Perencanaan', 'Pelaksanaan', 'Evaluasi'] as $i => $title) {
            DB::table('project_milestones')->insert([
                'project_id' => $project->id, 'title' => $title, 'order' => $i + 1, 'target_date' => '2026-0'.($i + 1).'-10',
                'status' => $i < 2 ? 'completed' : 'pending', 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        foreach ([['Laporan Mingguan #1', 'weekly', '2026-01-07', 'approved'], ['Laporan Bulanan #2', 'monthly', '2026-02-28', 'pending']] as [$title, $type, $end, $st]) {
            DB::table('project_reports')->insert([
                'project_id' => $project->id, 'student_id' => $student->id, 'type' => $type, 'title' => $title,
                'summary' => 'Ringkasan '.$title, 'activities' => 'Kegiatan', 'period_start' => '2026-01-01', 'period_end' => $end,
                'status' => $st, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        return $project->fresh();
    }

    public function test_queue_and_detail_are_rendered_together_with_shared_status_words_and_filters(): void
    {
        $institution = $this->makeInstitution();
        [$problemA, $problemB] = $this->makeOpenProblems($institution->id, 2);
        $pending = $this->application($problemA, $this->makeStudent('Sari', 'Wulandari'));
        $rejected = $this->application($problemB, $this->makeStudent('Rudi', 'Hartono'), 'rejected');

        $html = $this->actingAs($institution->user)->get(route('institution.applications.index'))->assertOk()->getContent();

        $this->assertStringContainsString('class="sg-inst-review"', $html);
        $this->assertStringContainsString('sgReviewQueue', $html);
        foreach ([$pending, $rejected] as $id) {
            $this->assertStringContainsString('href="'.route('institution.applications.show', $id).'"', $html);
            $this->assertStringContainsString('id="lamaran-'.$id.'"', $html);
        }
        $this->assertStringContainsString('Menunggu', $html);
        $this->assertStringContainsString('Ditolak', $html);
        $this->assertStringNotContainsString('>Pending<', $html);
        foreach (['name="search"', 'name="status"', 'name="problem_id"', 'name="sort"'] as $field) {
            $this->assertStringContainsString($field, $html);
        }
        // terima/tolak hanya untuk lamaran yang belum diputuskan
        $this->assertStringContainsString('data-sg-open="lamaran-'.$pending.'-terima"', $html);
        $this->assertStringNotContainsString('data-sg-open="lamaran-'.$rejected.'-terima"', $html);

        $html = $this->get(route('institution.applications.index', ['status' => 'rejected']))->getContent();
        $this->assertStringContainsString('id="lamaran-'.$rejected.'"', $html);
        $this->assertStringNotContainsString('id="lamaran-'.$pending.'"', $html);

        $html = $this->get(route('institution.applications.index', ['problem_id' => $problemA]))->getContent();
        $this->assertStringContainsString('id="lamaran-'.$pending.'"', $html);
        $this->assertStringNotContainsString('id="lamaran-'.$rejected.'"', $html);

        $html = $this->get(route('institution.applications.index', ['search' => 'tidak-ada-xyz']))->getContent();
        $this->assertStringContainsString('Tidak ada lamaran yang cocok', $html);
    }

    public function test_detail_shows_application_content_but_no_contact_values(): void
    {
        $institution = $this->makeInstitution();
        $problemId = $this->makeOpenProblems($institution->id, 1)[0];
        $student = $this->makeStudent('Sari', 'Wulandari');
        $id = $this->application($problemId, $student, 'pending', [
            'proposal_content' => base64_encode('%PDF-1.4 uji'), 'proposal_filename' => 'proposal-posyandu.pdf',
            'proposal_mime_type' => 'application/pdf', 'proposal_size' => 2048,
        ]);

        $html = $this->actingAs($institution->user)->get(route('institution.applications.show', $id))->assertOk()->getContent();

        $this->assertStringContainsString('Saya ingin membantu posyandu desa.', $html);
        $this->assertStringContainsString('Yth. panitia, saya siap bergabung.', $html);
        $this->assertStringContainsString('proposal-posyandu.pdf', $html);
        $this->assertStringContainsString(route('institution.applications.download-proposal', $id), $html);
        // D-04: instansi tidak menerima email/whatsapp mahasiswa
        $this->assertStringNotContainsString($student->user->email, $html);
        $this->assertStringNotContainsString($student->phone, $html);
        $this->assertStringContainsString(route('profile.public', $student->user->username), $html);
    }

    public function test_decision_forms_post_to_the_baseline_endpoints_with_the_same_fields(): void
    {
        $institution = $this->makeInstitution();
        $problemId = $this->makeOpenProblems($institution->id, 1)[0];
        $id = $this->application($problemId, $this->makeStudent('Sari', 'Wulandari'));

        foreach ([route('institution.applications.show', $id), route('institution.applications.review', $id)] as $url) {
            $html = $this->actingAs($institution->user)->get($url)->assertOk()->getContent();
            $this->assertMatchesRegularExpression('/<form method="POST" action="'.preg_quote(route('institution.applications.accept', $id), '/').'"/', $html);
            $this->assertMatchesRegularExpression('/<form method="POST" action="'.preg_quote(route('institution.applications.reject', $id), '/').'"/', $html);
            $this->assertMatchesRegularExpression('/<textarea name="notes"/', $html);
            $this->assertMatchesRegularExpression('/<textarea name="rejection_reason"[^>]*required/s', $html);
            $this->assertSame(2, substr_count($html, '<textarea name="feedback"'));
        }
    }

    public function test_accept_creates_the_project_and_notifies_the_student(): void
    {
        $institution = $this->makeInstitution();
        $problemId = $this->makeOpenProblems($institution->id, 1)[0];
        $student = $this->makeStudent('Sari', 'Wulandari');
        $id = $this->application($problemId, $student);

        $this->actingAs($institution->user)
            ->post(route('institution.applications.accept', $id), ['feedback' => 'Selamat bergabung', 'notes' => 'Catatan'])
            ->assertRedirect(route('institution.applications.show', $id));

        $this->assertDatabaseHas('applications', ['id' => $id, 'status' => 'accepted', 'feedback' => 'Selamat bergabung', 'institution_notes' => 'Catatan']);
        $this->assertDatabaseHas('projects', ['application_id' => $id, 'status' => 'active', 'institution_id' => $institution->id]);
        $this->assertSame(1, (int) DB::table('problems')->where('id', $problemId)->value('accepted_students'));
        $this->assertSame(1, Notification::where('user_id', $student->user_id)->where('type', 'application_accepted')->count());
    }

    public function test_accept_counts_accepted_applications_for_the_quota(): void
    {
        $institution = $this->makeInstitution();
        $problemId = $this->makeOpenProblems($institution->id, 1)[0];
        DB::table('problems')->where('id', $problemId)->update(['required_students' => 1, 'accepted_students' => 0]);
        $this->application($problemId, $this->makeStudent('Rudi', 'Hartono'), 'accepted');
        $id = $this->application($problemId, $this->makeStudent('Sari', 'Wulandari'));

        $this->actingAs($institution->user)->post(route('institution.applications.accept', $id))
            ->assertSessionHas('error', 'slot mahasiswa untuk masalah ini sudah penuh');
        $this->assertDatabaseHas('applications', ['id' => $id, 'status' => 'pending']);
        $this->assertSame(0, Notification::count());
    }

    public function test_reject_requires_both_fields_and_notifies_with_the_feedback(): void
    {
        $institution = $this->makeInstitution();
        $problemId = $this->makeOpenProblems($institution->id, 1)[0];
        $student = $this->makeStudent('Sari', 'Wulandari');
        $id = $this->application($problemId, $student);
        $this->actingAs($institution->user);

        $this->post(route('institution.applications.reject', $id), [])->assertSessionHasErrors(['feedback', 'rejection_reason']);
        $this->post(route('institution.applications.reject', $id), ['feedback' => 'Kuota bidang ini sudah terisi'])->assertSessionHasErrors(['rejection_reason']);
        $this->assertDatabaseHas('applications', ['id' => $id, 'status' => 'pending']);
        $this->assertSame(0, Notification::count());

        $this->post(route('institution.applications.reject', $id), ['feedback' => 'Kuota bidang ini sudah terisi', 'rejection_reason' => 'Kuota penuh'])
            ->assertRedirect(route('institution.applications.show', $id));
        $this->assertDatabaseHas('applications', ['id' => $id, 'status' => 'rejected', 'feedback' => 'Kuota bidang ini sudah terisi']);
        $notification = Notification::where('user_id', $student->user_id)->where('type', 'application_rejected')->firstOrFail();
        $this->assertStringContainsString('Kuota bidang ini sudah terisi', $notification->message);

        // sudah diputuskan: tidak bisa diputuskan lagi
        $this->post(route('institution.applications.accept', $id))->assertSessionHas('error', 'aplikasi ini sudah diproses');
    }

    public function test_applications_of_another_institution_are_not_reachable(): void
    {
        $owner = $this->makeInstitution();
        $problemId = $this->makeOpenProblems($owner->id, 1)[0];
        $id = $this->application($problemId, $this->makeStudent('Sari', 'Wulandari'));
        $this->actingAs($this->makeInstitution()->user);

        $this->get(route('institution.applications.show', $id))->assertNotFound();
        $this->get(route('institution.applications.review', $id))->assertNotFound();
        $this->post(route('institution.applications.accept', $id))->assertNotFound();
        $this->post(route('institution.applications.reject', $id), ['feedback' => 'x', 'rejection_reason' => 'y'])->assertNotFound();
        $this->assertDatabaseHas('applications', ['id' => $id, 'status' => 'pending']);
        $this->assertSame(0, Notification::count());
        $this->assertStringNotContainsString('id="lamaran-'.$id.'"', $this->get(route('institution.applications.index'))->getContent());
    }

    public function test_projects_table_shows_stage_meter_from_milestones_and_last_report(): void
    {
        $institution = $this->makeInstitution();
        $project = $this->project($institution);

        $html = $this->actingAs($institution->user)->get(route('institution.projects.index'))->assertOk()->getContent();

        preg_match_all('/<th scope="col"[^>]*>([^<]+)<\/th>/', $html, $m);
        $this->assertSame(['Proyek', 'Mahasiswa', 'Status', 'Tahap', 'Laporan terakhir', 'Jadwal', 'Aksi'], array_map('trim', $m[1]));
        $this->assertStringContainsString('2 dari 4 tahap', $html);
        $this->assertStringNotContainsString('41%', $html);
        // dua segmen terisi (milestone selesai) dari empat
        $this->assertSame(2, preg_match_all('/<i\s+data-done\s*><\/i>/', $html));
        $this->assertStringContainsString('Laporan Bulanan', $html);
        $this->assertStringContainsString('28 Feb 2026', $html);
        $this->assertStringContainsString('Berjalan', $html);
        $this->assertStringContainsString(route('institution.projects.show', $project->id), $html);
        $this->assertStringContainsString(route('institution.projects.manage', $project->id), $html);

        $this->assertStringContainsString('Tidak ada proyek yang cocok', $this->get(route('institution.projects.index', ['status' => 'cancelled']))->getContent());
    }

    public function test_project_detail_is_a_timeline_with_a_review_action_for_completed_projects(): void
    {
        $institution = $this->makeInstitution();
        $project = $this->project($institution, 'completed');
        $this->actingAs($institution->user);

        $html = $this->get(route('institution.projects.show', $project->id))->assertOk()->getContent();
        $this->assertStringContainsString('class="sg-inst-timeline"', $html);
        foreach (['Survei awal', 'Perencanaan', 'Pelaksanaan', 'Evaluasi', 'Laporan Mingguan #1', 'Laporan Bulanan #2'] as $text) {
            $this->assertStringContainsString($text, $html);
        }
        $this->assertStringContainsString(route('institution.reviews.create', $project->id), $html);

        $reviewId = DB::table('reviews')->insertGetId([
            'project_id' => $project->id, 'reviewer_id' => $institution->user_id, 'reviewee_id' => $project->student->user_id,
            'type' => 'institution_to_student', 'rating' => 4, 'review_text' => 'Kerja baik', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $html = $this->get(route('institution.projects.show', $project->id))->getContent();
        $this->assertStringContainsString(route('institution.reviews.show', $reviewId), $html);
        $this->assertStringNotContainsString(route('institution.reviews.create', $project->id), $html);
    }

    public function test_review_create_route_and_store_work_for_a_completed_unreviewed_project(): void
    {
        $institution = $this->makeInstitution();
        $project = $this->project($institution, 'completed');
        $this->actingAs($institution->user);

        // url tebakan audit: 404 sejak phase 1 (bukan QueryException)
        $this->get('/institution/reviews/create')->assertNotFound();

        $html = $this->get(route('institution.reviews.create', $project->id))->assertOk()->getContent();
        $this->assertStringContainsString('action="'.route('institution.reviews.store').'"', $html);
        $this->assertStringContainsString('name="project_id" value="'.$project->id.'"', $html);
        $this->assertSame(5, substr_count($html, 'type="radio" name="rating"'));
        foreach (['name="review"', 'name="strengths"', 'name="improvements"', 'name="would_collaborate_again"'] as $field) {
            $this->assertStringContainsString($field, $html);
        }

        $this->post(route('institution.reviews.store'), ['project_id' => $project->id, 'rating' => 5, 'review' => 'Sangat membantu warga.', 'strengths' => 'Komunikatif'])
            ->assertRedirect(route('institution.projects.show', $project->id))
            ->assertSessionHas('success');
        $review = Review::where('project_id', $project->id)->firstOrFail();
        $this->assertSame('institution_to_student', $review->type);
        $this->assertSame($project->student->user_id, $review->reviewee_id);
        $this->assertSame('Sangat membantu warga.', $review->review_text);
        $this->assertNotNull($project->fresh()->reviewed_at);

        // sudah diulas: create dialihkan, list menampilkannya
        $this->get(route('institution.reviews.create', $project->id))->assertRedirect(route('institution.projects.show', $project->id));
        $list = $this->get(route('institution.reviews.index'))->assertOk()->getContent();
        $this->assertStringContainsString('Sangat membantu warga.', $list);
        $this->assertStringContainsString('5/5', $list);
    }

    public function test_review_create_redirects_for_active_projects_and_validates_rating(): void
    {
        $institution = $this->makeInstitution();
        $active = $this->project($institution);
        $completed = $this->project($institution, 'completed');
        $this->actingAs($institution->user);

        $this->get(route('institution.reviews.create', $active->id))->assertRedirect(route('institution.projects.show', $active->id));
        $this->post(route('institution.reviews.store'), ['project_id' => $completed->id, 'review' => 'Tanpa rating'])->assertSessionHasErrors(['rating']);
        $this->assertSame(0, Review::count());
    }

    public function test_reviews_empty_state_and_dashboard_counts_reviews_from_the_reviews_table(): void
    {
        $institution = $this->makeInstitution();
        $this->actingAs($institution->user);
        $this->assertStringContainsString('Belum ada ulasan', $this->get(route('institution.reviews.index'))->assertOk()->getContent());

        $reviewed = $this->project($institution, 'completed');
        $open = $this->project($institution, 'completed');
        DB::table('reviews')->insert([
            'project_id' => $reviewed->id, 'reviewer_id' => $institution->user_id, 'reviewee_id' => $reviewed->student->user_id,
            'type' => 'institution_to_student', 'rating' => 4, 'review_text' => 'Kerja baik', 'created_at' => now(), 'updated_at' => now(),
        ]);
        // reviewed_at sengaja kosong (seperti data seed)
        $this->assertNull($reviewed->fresh()->reviewed_at);

        $html = $this->get(route('institution.dashboard'))->assertOk()->getContent();
        $this->assertStringContainsString(route('institution.reviews.create', $open->id), $html);
        $this->assertStringNotContainsString(route('institution.reviews.create', $reviewed->id), $html);
    }

    public function test_role_access_is_unchanged(): void
    {
        foreach (['institution.applications.index', 'institution.projects.index', 'institution.reviews.index'] as $route) {
            $this->get(route($route))->assertRedirect(route('login'));
        }
        $this->actingAs($this->makeStudent('Sari', 'Wulandari')->user)->get(route('institution.applications.index'))->assertRedirect();
        $this->actingAs($this->makeCompany()->user)->get(route('institution.reviews.index'))->assertRedirect();
    }
}
