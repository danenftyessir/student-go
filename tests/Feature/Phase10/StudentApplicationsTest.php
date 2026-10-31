<?php

namespace Tests\Feature\Phase10;

use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * phase 10: lamaran mahasiswa. daftar = tabel (tanpa grafik), detail = record + rel fakta, batal = tombol sekunder
 * outline-danger dengan dialog konfirmasi, formulir = satu kolom dengan penghitung netral. rute, metode, nama field,
 * aturan validasi dan cek kepemilikan (404) tidak berubah.
 */
class StudentApplicationsTest extends TestCase
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

    private function problem(string $title = 'Kampanye Zero Waste', array $attrs = []): int
    {
        $id = $this->makeOpenProblems($this->institutionId, 1)[0];
        DB::table('problems')->where('id', $id)->update(array_merge([
            'title' => $title,
            'sdg_categories' => json_encode([11, 12]),
            'application_deadline' => now()->addDays(20)->toDateString(),
        ], $attrs));

        return $id;
    }

    private function apply(int $problemId, string $status, array $attrs = [], ?Student $student = null): int
    {
        return DB::table('applications')->insertGetId(array_merge([
            'student_id' => ($student ?? $this->student)->id,
            'problem_id' => $problemId,
            'motivation' => 'Program KKN ini merupakan kesempatan emas untuk mengimplementasikan teori yang saya pelajari.',
            'status' => $status,
            'applied_at' => '2026-10-16 11:36:00',
            'created_at' => now(),
            'updated_at' => now(),
        ], $attrs));
    }

    private function main(string $html): string
    {
        $start = strpos($html, '<main');

        return substr($html, $start, strpos($html, '</main>') - $start);
    }

    private function get200(string $url): string
    {
        return $this->main($this->actingAs($this->student->user)->get($url)->assertOk()->getContent());
    }

    public function test_list_is_a_table_with_status_badges_summary_and_no_chart(): void
    {
        $this->apply($this->problem('Revitalisasi Seni'), 'pending');
        $this->apply($this->problem('Sekolah Inklusi'), 'reviewed');
        $this->apply($this->problem('Kampanye Zero Waste'), 'rejected', ['feedback' => 'Kami memilih kandidat yang lebih sesuai.', 'rejected_at' => '2026-10-23 11:36:00']);

        $main = $this->get200('/student/applications');

        $this->assertStringContainsString('<table class="sg-table sg-table--stack">', $main);
        foreach (['Proyek', 'Instansi', 'Status', 'Diajukan', 'Tenggat'] as $column) {
            $this->assertStringContainsString('<th scope="col">'.$column.'</th>', $main);
        }
        $this->assertMatchesRegularExpression('/<b>3<\/b> lamaran.*<b>1<\/b> Menunggu.*<b>1<\/b> Ditinjau.*<b>1<\/b> Ditolak/s', $main);
        $this->assertStringNotContainsString('Diterima</li>', $main);   // angka nol tidak ditampilkan
        foreach (['data-value="pending"', 'data-value="reviewed"', 'data-value="rejected"'] as $badge) {
            $this->assertStringContainsString($badge, $main);
        }
        $this->assertStringContainsString('Alasan penolakan:', $main);
        $this->assertStringContainsString('Kami memilih kandidat yang lebih sesuai.', $main);
        foreach (['bar-chart', 'Statistik', 'application-student.jpg', '>Pending<', 'Direview', '<style'] as $gone) {
            $this->assertStringNotContainsString($gone, $main);
        }
        $this->assertSame(1, substr_count($main, '<h1'));
    }

    public function test_list_keeps_query_contract_and_filters(): void
    {
        $this->apply($this->problem('Revitalisasi Seni'), 'pending');
        $this->apply($this->problem('Kampanye Zero Waste'), 'rejected');

        $main = $this->get200('/student/applications?tab=projects&status=rejected&sort=oldest');

        $this->assertStringContainsString('<form method="GET" action="'.route('student.applications.index').'"', $main);
        $this->assertStringContainsString('<input type="hidden" name="tab" value="projects">', $main);
        $this->assertStringContainsString('name="search"', $main);
        $this->assertMatchesRegularExpression('/<select name="status" id="status"[^>]*>.*<option value="rejected" selected>Ditolak<\/option>/s', $main);
        $this->assertMatchesRegularExpression('/<select name="sort" id="sort"[^>]*>.*<option value="oldest" selected>Terlama<\/option>/s', $main);
        $this->assertStringContainsString('Kampanye Zero Waste', $main);
        $this->assertStringNotContainsString('Revitalisasi Seni', $main);

        // tab lowongan tanpa lamaran: keadaan kosong, tanpa bilah filter
        $jobs = $this->get200('/student/applications?tab=jobs');
        $this->assertMatchesRegularExpression('/\?tab=jobs" class="sg-tabs__tab"\s+aria-current="page"/', $jobs);
        $this->assertStringContainsString('Belum ada lamaran lowongan', $jobs);
        $this->assertStringNotContainsString('name="job_status"', $jobs);
    }

    public function test_cancel_in_list_is_outline_danger_with_confirm_and_same_endpoint(): void
    {
        $pending = $this->apply($this->problem('Revitalisasi Seni'), 'pending');
        $this->apply($this->problem('Sekolah Inklusi'), 'reviewed');

        $main = $this->get200('/student/applications');

        $this->assertSame(1, substr_count($main, 'action="'.route('student.applications.withdraw', $pending).'"'));
        $this->assertStringContainsString('name="_method" value="DELETE"', $main);
        $this->assertStringContainsString('data-sg-confirm="Lamaran untuk', $main);
        $this->assertStringContainsString('sg-btn--danger whitespace-nowrap', $main);
        $this->assertStringNotContainsString('sg-btn--danger-solid', $main);
        $this->assertStringNotContainsString('onsubmit=', $main);
    }

    public function test_empty_account_shows_empty_state_without_summary(): void
    {
        $main = $this->get200('/student/applications');

        $this->assertStringContainsString('Belum ada lamaran proyek', $main);
        $this->assertStringContainsString('no-applications.svg', $main);
        $this->assertStringNotContainsString('sg-summary', $main);
        $this->assertStringNotContainsString('<table', $main);
    }

    public function test_pending_detail_is_a_record_with_rail_and_confirmed_secondary_cancel(): void
    {
        $id = $this->apply($this->problem('Revitalisasi Seni'), 'pending', [
            'motivation' => "Saya sangat tertarik dengan proyek ini karena sesuai dengan bidang studi saya.\nBaris kedua.",
        ]);

        $main = $this->get200('/student/applications/'.$id);

        $this->assertSame(1, substr_count($main, '<h1'));
        $this->assertStringContainsString('<h1>Revitalisasi Seni</h1>', $main);
        $this->assertStringContainsString('Menunggu', $main);
        $this->assertStringNotContainsString('Menunggu Review', $main);
        foreach (['ID lamaran', 'Diajukan', 'Durasi proyek', 'Periode', 'Tenggat pendaftaran'] as $fact) {
            $this->assertStringContainsString('<dt>'.$fact.'</dt>', $main);
        }
        $this->assertStringContainsString('#'.str_pad($id, 6, '0', STR_PAD_LEFT), $main);
        $this->assertStringContainsString('16 Okt 2026', $main);
        $this->assertStringContainsString('Menunggu tinjauan instansi', $main);
        $this->assertStringContainsString('Tidak ada proposal yang dilampirkan.', $main);
        $this->assertStringContainsString('studi saya.<br />', $main);
        // pemenggalan normal: tidak ada break-all
        $this->assertStringNotContainsString('break-all', $main);
        $this->assertStringContainsString('wrap-break-word', $main);

        $this->assertStringContainsString('action="'.route('student.applications.withdraw', $id).'"', $main);
        $this->assertStringContainsString('name="_method" value="DELETE"', $main);
        $this->assertStringContainsString('data-sg-confirm-title="Batalkan lamaran?"', $main);
        $this->assertMatchesRegularExpression('/class="sg-btn sg-btn--danger sg-btn--block"[^>]*>\s*<svg[^>]*>.*?<\/svg>\s*Batalkan lamaran/s', $main);
        $this->assertStringNotContainsString('sg-btn--danger-solid', $main);
        $this->assertStringNotContainsString('<style', $main);
    }

    public function test_rejected_detail_shows_feedback_and_similar_projects_link_without_cancel(): void
    {
        $problem = $this->problem('Kampanye Zero Waste');
        $id = $this->apply($problem, 'rejected', [
            'feedback' => 'Terima kasih atas minat Anda.',
            'reviewed_at' => '2026-10-21 11:36:00',
            'rejected_at' => '2026-10-23 11:36:00',
        ]);

        $main = $this->get200('/student/applications/'.$id);

        $this->assertStringContainsString('Lamaran ditolak', $main);
        $this->assertStringContainsString('Terima kasih atas minat Anda.', $main);
        $this->assertStringContainsString('href="'.e(route('student.browse-problems.index', ['sdg_categories' => [11, 12]])).'"', $main);
        $this->assertStringContainsString('Cari proyek serupa', $main);
        $this->assertMatchesRegularExpression('/Diajukan.*Ditinjau.*Ditolak/s', strip_tags($main));
        $this->assertStringNotContainsString('student.applications.withdraw', $main);
        $this->assertStringNotContainsString('Batalkan lamaran', $main);
    }

    public function test_other_students_application_still_returns_404(): void
    {
        $other = $this->makeStudent('Desi', 'Hartono');
        $foreign = $this->apply($this->problem(), 'pending', [], $other);

        $this->actingAs($this->student->user)->get('/student/applications/'.$foreign)->assertNotFound();
        $this->actingAs($this->student->user)->delete('/student/applications/'.$foreign)->assertNotFound();
        $this->assertDatabaseHas('applications', ['id' => $foreign]);
    }

    public function test_create_form_keeps_contract_and_neutral_counter(): void
    {
        $problem = $this->problem('Sistem Transportasi Ramah Lingkungan');

        $html = $this->actingAs($this->student->user)->get('/student/applications/create/'.$problem)->assertOk()->getContent();
        $main = $this->main($html);

        $this->assertMatchesRegularExpression('/<form action="'.preg_quote(route('student.applications.store'), '/').'" method="POST" enctype="multipart\/form-data"/', $main);
        $this->assertStringContainsString('name="_token"', $main);
        $this->assertStringContainsString('<input type="hidden" name="problem_id" value="'.$problem.'">', $main);
        $this->assertMatchesRegularExpression('/<textarea name="motivation" id="motivation"/', $main);
        $this->assertMatchesRegularExpression('/<input type="file"[^>]*name="proposal" id="proposal"[^>]*accept=".pdf,.doc,.docx"/s', $main);
        $this->assertStringContainsString('Minimal 100 karakter.', $main);
        // penghitung netral sebelum interaksi: tidak ada warna galat atau aria-invalid dari server
        $this->assertStringNotContainsString('aria-invalid="true"', $main);
        $this->assertStringNotContainsString('text-red-600', $main);
        $this->assertStringContainsString('applicationForm({ touched: false })', $main);
        $this->assertStringContainsString('Kirim lamaran', $main);
        $this->assertStringContainsString('Mahasiswa dibutuhkan', $main);
        $this->assertSame(1, substr_count($main, '<h1'));
        foreach (['💡', '🎯', '🎨', '📄', '<style'] as $gone) {
            $this->assertStringNotContainsString($gone, $html);
        }
    }

    public function test_short_motivation_is_rejected_by_server_and_repopulated(): void
    {
        $problem = $this->problem();
        $user = $this->student->user;

        $this->actingAs($user)->from('/student/applications/create/'.$problem)
            ->post('/student/applications', ['problem_id' => $problem, 'motivation' => 'Terlalu pendek.'])
            ->assertRedirect('/student/applications/create/'.$problem)
            ->assertSessionHasErrors(['motivation' => 'Motivasi minimal 100 karakter']);

        $main = $this->main($this->actingAs($user)->from('/student/applications/create/'.$problem)->followingRedirects()
            ->post('/student/applications', ['problem_id' => $problem, 'motivation' => 'Terlalu pendek.'])->assertOk()->getContent());
        $this->assertStringContainsString('Terlalu pendek.</textarea>', $main);
        $this->assertStringContainsString('aria-invalid="true"', $main);
        $this->assertStringContainsString('id="motivation-error"', $main);
        $this->assertStringContainsString('applicationForm({ touched: true })', $main);
        $this->assertDatabaseCount('applications', 0);
    }

    public function test_valid_submission_redirects_to_record_with_success_flash_then_cancel_deletes(): void
    {
        $problem = $this->problem();
        $user = $this->student->user;

        $response = $this->actingAs($user)->post('/student/applications', [
            'problem_id' => $problem,
            'motivation' => str_repeat('Saya ingin berkontribusi untuk desa. ', 4),
            'proposal' => UploadedFile::fake()->createWithContent('proposal-kkn.pdf', "%PDF-1.4\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF"),
        ]);

        $id = DB::table('applications')->value('id');
        $response->assertRedirect(route('student.applications.show', $id))
            ->assertSessionHas('success', 'Aplikasi berhasil dikirim! Instansi akan meninjau aplikasi Anda.');

        $main = $this->get200('/student/applications/'.$id);
        $this->assertStringContainsString('proposal-kkn.pdf', $main);
        $this->assertStringContainsString('href="'.route('student.applications.download-proposal', $id).'"', $main);

        $this->actingAs($user)->delete('/student/applications/'.$id)
            ->assertRedirect(route('student.applications.index'))
            ->assertSessionHas('success', 'Aplikasi berhasil dibatalkan.');
        $this->assertDatabaseMissing('applications', ['id' => $id]);
    }
}
