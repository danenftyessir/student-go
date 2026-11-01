<?php

namespace Tests\Feature\Phase29;

use App\Models\Company;
use App\Models\Student;
use App\Support\StatusVocabulary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * phase 29 paket A: pelacak lamaran (student/applications/tracker) di sistem desain. judul bahasa indonesia, status dari
 * StatusVocabulary (kata sama dengan Aplikasi Saya), tanpa grafik 'Aktivitas Minggu Ini', tanpa <style>.
 * formulir lamaran (create) dan cek kepemilikan detail (404) tidak berubah.
 */
class StudentApplicationsPackageATest extends TestCase
{
    use BuildsPhase1Data;
    use RefreshDatabase;

    private int $institutionId;

    private Student $student;

    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRegion();
        $this->institutionId = $this->makeInstitution()->id;
        $this->student = $this->makeStudent('Rina', 'Zahira');
        $this->company = $this->makeCompany();
    }

    private function problem(string $title): int
    {
        $id = $this->makeOpenProblems($this->institutionId, 1)[0];
        DB::table('problems')->where('id', $id)->update(['title' => $title, 'application_deadline' => now()->addDays(20)->toDateString()]);

        return $id;
    }

    private function projectApp(string $title, string $status, ?Student $student = null): int
    {
        return DB::table('applications')->insertGetId([
            'student_id' => ($student ?? $this->student)->id, 'problem_id' => $this->problem($title),
            'motivation' => str_repeat('Motivasi KKN. ', 10), 'status' => $status,
            'applied_at' => now()->subDays(3), 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function jobApp(string $title, string $status): void
    {
        $jobId = DB::table('job_postings')->insertGetId([
            'company_id' => $this->company->id, 'title' => $title, 'slug' => 'uji-'.uniqid(), 'status' => 'posted',
            'job_type' => 'internship', 'description' => 'Deskripsi', 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('job_applications')->insert([
            'job_posting_id' => $jobId, 'user_id' => $this->student->user->id, 'status' => $status,
            'created_at' => now()->subDays(2), 'updated_at' => now()->subDay(),
        ]);
    }

    private function main(string $html): string
    {
        $start = strpos($html, '<main');

        return substr($html, $start, strpos($html, '</main>') - $start);
    }

    private function get200(string $url): string
    {
        return $this->actingAs($this->student->user)->get($url)->assertOk()->getContent();
    }

    public function test_tracker_has_indonesian_header_vocabulary_statuses_and_no_weekly_chart(): void
    {
        $this->projectApp('Revitalisasi Seni', 'pending');
        $this->projectApp('Sekolah Inklusi', 'reviewed');
        $this->projectApp('Kampanye Zero Waste', 'rejected');
        $this->jobApp('Pendamping Reboisasi', 'new');
        $this->jobApp('Analis Data Desa', 'shortlisted');
        $this->jobApp('Fasilitator UMKM', 'rejected');

        $html = $this->get200('/student/applications/tracker');
        $main = $this->main($html);

        $this->assertStringContainsString('<title>Pelacak Lamaran — Student Go</title>', $html);
        $this->assertSame(1, substr_count($html, '<h1'));
        $this->assertStringContainsString('<h1>Pelacak lamaran</h1>', $main);
        $this->assertStringContainsString('class="sg-page-header', $main);

        // ringkasan dari angka controller: 6 lamaran, 4 diproses (pending, reviewed, new, shortlisted), 2 ditolak
        $this->assertMatchesRegularExpression('/<b>6<\/b> lamaran.*<b>4<\/b> sedang diproses.*<b>2<\/b> ditolak/s', $main);
        $this->assertStringNotContainsString('diterima atau direkrut', $main);   // nol tidak ditampilkan

        foreach (['Menunggu', 'Ditinjau', 'Ditolak', 'Terpilih'] as $word) {
            $this->assertStringContainsString($word, $main);
        }
        foreach (['Application Tracker', 'Rejected', 'Pending', 'Shortlisted', 'Applied', '>New<', 'Aktivitas Minggu Ini', 'Aksi Cepat',
                  'Tingkat Keberhasilan', 'Update Profil', 'Company', ' ago', '<style', 'style="'] as $gone) {
            $this->assertStringNotContainsString($gone, $main);
        }
        $this->assertDoesNotMatchRegularExpression('/#[0-9a-fA-F]{6}\b/', preg_replace('/href="[^"]*"/', '', $main));
        $this->assertStringContainsString('hari yang lalu', $main);
    }

    public function test_tracker_status_words_match_the_applications_list(): void
    {
        $this->projectApp('Revitalisasi Seni', 'pending');
        $this->jobApp('Analis Data Desa', 'shortlisted');

        $tracker = $this->main($this->get200('/student/applications/tracker'));
        $projects = $this->main($this->get200('/student/applications?tab=projects'));
        $jobs = $this->main($this->get200('/student/applications?tab=jobs'));

        $pending = StatusVocabulary::label('application', 'pending');
        $shortlisted = StatusVocabulary::label('job_application', 'shortlisted');
        $badge = fn (string $value, string $label) => '/data-value="'.$value.'">\s*<svg[^>]*>.*?<\/svg>\s*'.preg_quote($label, '/').'\s*<\/span>/s';

        $this->assertMatchesRegularExpression($badge('pending', $pending), $tracker);
        $this->assertMatchesRegularExpression($badge('pending', $pending), $projects);
        $this->assertMatchesRegularExpression($badge('shortlisted', $shortlisted), $tracker);
        $this->assertMatchesRegularExpression($badge('shortlisted', $shortlisted), $jobs);
    }

    public function test_tracker_empty_account_shows_empty_state_without_numbers(): void
    {
        $main = $this->main($this->get200('/student/applications/tracker'));

        $this->assertStringContainsString('Belum ada lamaran', $main);
        $this->assertStringContainsString('no-applications.svg', $main);
        $this->assertStringNotContainsString('sg-summary', $main);
        $this->assertStringNotContainsString('<table', $main);
    }

    public function test_create_form_keeps_action_enctype_and_field_names(): void
    {
        $problemId = $this->problem('Kampanye Zero Waste');
        $main = $this->main($this->get200('/student/applications/create/'.$problemId));

        $this->assertStringContainsString('<form action="'.route('student.applications.store').'" method="POST" enctype="multipart/form-data"', $main);
        $form = substr($main, strpos($main, route('student.applications.store')));
        $form = substr($form, 0, strpos($form, '</form>'));
        preg_match_all('/<(?:input|textarea|select)[^>]*name="([^"]+)"/', $form, $m);
        $this->assertSame(['_token', 'problem_id', 'motivation', 'proposal'], $m[1]);
        $this->assertStringContainsString('accept=".pdf,.doc,.docx"', $form);
    }

    public function test_other_students_application_is_still_404(): void
    {
        $other = $this->makeStudent('Bima', 'Saputra');
        $foreign = $this->projectApp('Revitalisasi Seni', 'pending', $other);
        $own = $this->projectApp('Sekolah Inklusi', 'pending');

        $this->actingAs($this->student->user)->get('/student/applications/'.$foreign)->assertNotFound();
        $page = $this->main($this->get200('/student/applications/'.$own));
        $this->assertStringContainsString('<form action="'.route('student.applications.withdraw', $own).'" method="POST"', $page);
        $this->assertStringContainsString('data-sg-confirm-title="Batalkan lamaran?"', $page);
    }
}
