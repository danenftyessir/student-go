<?php

namespace Tests\Feature\Phase29;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * phase 29 paket F: polesan presentasi. C4 footer menempel di bawah (shell kolom flex), C5 galat login,
 * U6 ilustrasi untuk lima keadaan kosong tingkat halaman. keadaan kosong di dalam tab tetap tanpa ilustrasi.
 */
class PackageFPolishTest extends TestCase
{
    use BuildsPhase1Data;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRegion();
    }

    // isi <main> saja
    private function main(string $html): string
    {
        $start = strpos($html, '<main');

        return substr($html, $start, strpos($html, '</main>') - $start);
    }

    // potongan keadaan kosong yang memuat judul tertentu
    private function emptyState(string $html, string $title): string
    {
        $titleAt = strpos($html, $title);
        $this->assertNotFalse($titleAt, "keadaan kosong '{$title}' tidak ada");
        $start = strrpos(substr($html, 0, $titleAt), 'class="sg-empty');
        $this->assertNotFalse($start);

        return substr($html, $start, $titleAt - $start);
    }

    private function job(int $companyId): int
    {
        return DB::table('job_postings')->insertGetId([
            'company_id' => $companyId, 'title' => 'Analis Data Desa', 'slug' => 'analis-data-desa',
            'location' => 'Kabupaten Garut', 'status' => 'posted', 'job_type' => 'Internship', 'department' => 'Pertanian',
            'description' => 'Deskripsi', 'sdg_alignment' => json_encode([13]),
            'published_at' => now()->subDay(), 'expires_at' => now()->addDays(20),
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_c4_shell_is_a_column_with_a_growing_main(): void
    {
        $html = $this->get('/contact')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<div class="sg-shell[^"]*">\s*<header/', $html);
        $this->assertStringContainsString('<main id="main" tabindex="-1" class="sg-main">', $html);

        $student = $this->makeStudent('Rina', 'Zahira');
        $this->assertStringContainsString('class="sg-shell', $this->actingAs($student->user)->get('/student/projects')->getContent());
    }

    // pesan dan kunci session tidak berubah; huruf besar dan jarak diatur css (pilihan pengguna: css saja)
    public function test_c5_failed_login_error_keeps_message_and_gets_auth_error_styles(): void
    {
        $response = $this->from('/login')->post('/login', ['email' => 'tidak-ada@contoh.test', 'password' => 'salah-sandi']);

        $response->assertRedirect('/login')->assertSessionHasErrors('email');
        $this->assertSame('email atau password salah.', session('errors')->first('email'));

        $html = $this->from('/login')->followingRedirects()
            ->post('/login', ['email' => 'tidak-ada@contoh.test', 'password' => 'salah-sandi'])->getContent();
        $this->assertMatchesRegularExpression('/<p class="sg-error" id="email-error">.*email atau password salah\./s', $html);
        $this->assertStringContainsString('class="sg-auth', $html);

        $auth = file_get_contents(resource_path('css/student-go/auth.css'));
        $this->assertStringContainsString('.sg-auth .sg-error::first-letter { text-transform: uppercase; }', $auth);

        // padding halaman galat hanya untuk <section class="sg-error">, tidak untuk galat field
        $errors = file_get_contents(resource_path('css/student-go/errors.css'));
        $this->assertDoesNotMatchRegularExpression('/(^|[\s,}])\.sg-error\s*\{/m', $errors);
        $this->assertStringContainsString('section.sg-error { padding:', $errors);
        $this->assertStringContainsString('<section class="sg-error"', file_get_contents(resource_path('views/errors/partials/content.blade.php')));
    }

    public function test_u6_institution_page_level_empty_states_have_illustrations(): void
    {
        $user = $this->makeInstitution()->user;

        $problems = $this->main($this->actingAs($user)->get('/institution/problems')->assertOk()->getContent());
        $this->assertStringContainsString('empty-states/no-documents.svg', $this->emptyState($problems, 'Belum ada masalah'));

        $projects = $this->main($this->actingAs($user)->get('/institution/projects')->assertOk()->getContent());
        $this->assertStringContainsString('empty-states/no-applications.svg', $this->emptyState($projects, 'Belum ada proyek'));

        $reviews = $this->main($this->actingAs($user)->get('/institution/reviews')->assertOk()->getContent());
        $this->assertStringContainsString('empty-states/no-documents.svg', $this->emptyState($reviews, 'Belum ada ulasan'));
    }

    public function test_u6_student_jobs_page_level_empty_states_have_illustrations(): void
    {
        $student = $this->makeStudent('Farah', 'Wibowo');
        $jobId = $this->job($this->makeCompany()->id);
        DB::table('saved_jobs')->insert(['user_id' => $student->user_id, 'job_posting_id' => $jobId, 'folder' => 'Prioritas', 'created_at' => now()]);

        $saved = $this->main($this->actingAs($student->user)->get('/student/jobs/saved?folder=Lainnya')->assertOk()->getContent());
        $this->assertStringContainsString('empty-states/no-saved-projects.svg', $this->emptyState($saved, 'Tidak ada lowongan di sini'));

        $jobs = $this->main($this->actingAs($student->user)->get('/student/jobs?page=99')->assertOk()->getContent());
        $this->assertStringContainsString('empty-states/no-search-results.svg', $this->emptyState($jobs, 'Halaman ini kosong'));
    }

    public function test_u6_tab_level_empty_states_stay_text_only(): void
    {
        $student = $this->makeStudent('Farah', 'Wibowo');

        $friends = $this->main($this->actingAs($student->user)->get('/student/friends')->assertOk()->getContent());
        foreach (['Belum ada saran', 'Tidak ada permintaan masuk'] as $title) {
            if (str_contains($friends, $title)) {
                $this->assertStringNotContainsString('empty-states/', $this->emptyState($friends, $title));
            }
        }
    }

    public function test_c3_chart_data_table_scrolls_in_its_own_wrapper_and_chart_column_can_shrink(): void
    {
        Carbon::setTestNow('2026-10-15 10:00:00');
        $company = $this->makeCompany();
        $jobId = $this->job($company->id);
        foreach (['2026-05-05', '2026-06-05', '2026-08-05', '2026-09-05', '2026-10-05'] as $i => $date) {
            DB::table('job_applications')->insert([
                'job_posting_id' => $jobId, 'user_id' => $this->makeStudent('Grafik', 'A'.$i)->user_id, 'status' => 'pending',
                'created_at' => $date, 'updated_at' => $date,
            ]);
        }

        $html = $this->actingAs($company->user)->get('/company/dashboard')->assertOk()->getContent();
        Carbon::setTestNow();

        $this->assertStringContainsString('<canvas id="grafik-lamaran"', $html);
        $this->assertMatchesRegularExpression('/<div class="sr-only">\s*(\{\{--.*?--\}\}\s*)?(<!--.*?-->\s*)?<div class="sg-co-dash__table-scroll">\s*<table>/s', $html);

        $css = file_get_contents(resource_path('css/student-go/company.css'));
        $this->assertStringContainsString('.sg-co-dash__col { display: grid; grid-template-columns: minmax(0, 1fr);', $css);
        $this->assertStringContainsString('.sg-co-dash__table-scroll { overflow-x: auto; }', $css);
    }

    public function test_no_placeholder_image_is_referenced(): void
    {
        $student = $this->makeStudent('Farah', 'Wibowo');

        foreach (['/student/friends', '/student/friends/activities', '/student/projects'] as $url) {
            $this->assertStringNotContainsString('placeholder-', $this->actingAs($student->user)->get($url)->getContent(), $url);
        }
    }
}
