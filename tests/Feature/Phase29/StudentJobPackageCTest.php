<?php

namespace Tests\Feature\Phase29;

use App\Models\Company;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * phase 29 paket C: detail lowongan mahasiswa (student/jobs/{id}) di sistem desain. tanpa <style>, label dari copy deck
 * (surat lamaran, keahlian, tujuan SDG, dipublikasikan, dilihat), waktu relatif bahasa indonesia, jenis lowongan lewat
 * pemetaan daftar lowongan. formulir lamar dan batal tetap (action, metode, nama field).
 */
class StudentJobPackageCTest extends TestCase
{
    use BuildsPhase1Data;
    use RefreshDatabase;

    private Student $student;

    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRegion();
        $this->student = $this->makeStudent('Farah', 'Wibowo');
        $this->company = $this->makeCompany();
    }

    private function job(array $attrs = []): int
    {
        return DB::table('job_postings')->insertGetId(array_merge([
            'company_id' => $this->company->id, 'title' => 'Pendamping Reboisasi Desa', 'slug' => 'uji-'.uniqid(),
            'location' => 'Kabupaten Garut', 'status' => 'posted', 'job_type' => 'internship', 'department' => 'Konservasi',
            'description' => 'Mendampingi warga menanam bibit.', 'responsibilities' => 'Mencatat jumlah bibit.',
            'qualifications' => 'Mahasiswa kehutanan.', 'benefits' => 'Uang saku bulanan.',
            'skills' => json_encode(['Pemetaan GIS', 'Penyuluhan']), 'sdg_alignment' => json_encode([13, 15]),
            'views_count' => 1470, 'published_at' => now()->subDay(), 'expires_at' => now()->addDays(20),
            'created_at' => now(), 'updated_at' => now(),
        ], $attrs));
    }

    private function applyAs(int $jobId, string $status): void
    {
        DB::table('job_applications')->insert([
            'job_posting_id' => $jobId, 'user_id' => $this->student->user->id, 'status' => $status,
            'created_at' => now()->subDays(2), 'updated_at' => now()->subDays(2),
        ]);
    }

    private function main(string $html): string
    {
        $start = strpos($html, '<main');

        return substr($html, $start, strpos($html, '</main>') - $start);
    }

    private function page(int $id): string
    {
        return $this->actingAs($this->student->user)->get('/student/jobs/'.$id)->assertOk()->getContent();
    }

    public function test_detail_uses_page_header_and_copy_deck_terms_without_english_labels(): void
    {
        $html = $this->page($this->job());
        $main = $this->main($html);

        $this->assertStringNotContainsString('<style', $main);
        $this->assertSame(1, substr_count($html, '<h1'));
        $this->assertStringContainsString('class="sg-page-header', $main);
        foreach (['Surat lamaran (opsional)', 'Keahlian yang dibutuhkan', 'Tujuan SDG', 'Deskripsi pekerjaan', 'Tanggung jawab', 'Kualifikasi', 'Manfaat',
                  'Magang', 'Dipublikasikan', '1 hari yang lalu', '1.471 kali', 'SDG 13 · ', 'Pemetaan GIS'] as $text) {   // show() menambah 1 tayangan
            $this->assertStringContainsString($text, $main);
        }
        foreach (['Cover Letter', 'Skills Yang Dibutuhkan', 'SDG Alignment', 'Diposting', ' views', 'Internship', 'internship', ' ago', 'Company', 'job-type-badge'] as $english) {
            $this->assertStringNotContainsString($english, $main);
        }
        // tidak ada nilai warna heksadesimal di isi halaman
        $this->assertDoesNotMatchRegularExpression('/#[0-9a-fA-F]{6}\b/', preg_replace('/href="[^"]*"/', '', $main));
    }

    public function test_job_type_uses_the_job_list_mapping(): void
    {
        foreach (['full_time' => 'Penuh waktu', 'part_time' => 'Paruh waktu', 'contract' => 'Kontrak', 'freelance' => 'Lepas', 'Internship' => 'Magang'] as $type => $label) {
            $main = $this->main($this->page($this->job(['job_type' => $type])));
            $this->assertStringContainsString('<span class="sr-only">Jenis: </span>'.$label, $main);
        }
    }

    public function test_apply_form_keeps_action_method_and_field_names(): void
    {
        $id = $this->job();
        $main = $this->main($this->page($id));

        $this->assertMatchesRegularExpression('#<form action="'.preg_quote(route('student.jobs.apply', $id), '#').'" method="POST" class="grid gap-4">#', $main);
        $form = substr($main, strpos($main, route('student.jobs.apply', $id)));
        $form = substr($form, 0, strpos($form, '</form>'));
        $this->assertStringNotContainsString('enctype', $form);
        preg_match_all('/<(?:input|textarea|select)[^>]*name="([^"]+)"/', $form, $m);
        $this->assertSame(['_token', 'cover_letter', 'resume_url', 'portfolio_url'], $m[1]);
        $this->assertStringContainsString('<label class="sg-label"  for="cover_letter"', $form);
        $this->assertStringContainsString('href="#lamar"', $main);

        // kirim lamaran masih bekerja (hanya di sqlite pengujian)
        $this->actingAs($this->student->user)->post(route('student.jobs.apply', $id), ['cover_letter' => 'Saya siap membantu.'])
            ->assertRedirect(route('student.jobs.show', $id));
        $this->assertDatabaseHas('job_applications', ['job_posting_id' => $id, 'user_id' => $this->student->user->id, 'cover_letter' => 'Saya siap membantu.']);
    }

    public function test_applied_state_shows_vocabulary_status_and_confirmed_withdraw(): void
    {
        $id = $this->job();
        $this->applyAs($id, 'new');
        $main = $this->main($this->page($id));

        $this->assertStringContainsString('Anda sudah melamar', $main);
        $this->assertStringContainsString('data-value="new"', $main);
        $this->assertStringContainsString('Menunggu', $main);
        $this->assertStringNotContainsString('name="cover_letter"', $main);
        $this->assertStringContainsString('<form action="'.route('student.jobs.withdraw', $id).'" method="POST"', $main);
        $this->assertStringContainsString('name="_method" value="DELETE"', $main);
        $this->assertStringContainsString('data-sg-confirm-title="Batalkan lamaran?"', $main);
        $this->assertStringNotContainsString('onsubmit=', $main);
    }

    public function test_processed_application_has_no_withdraw_and_uses_vocabulary_word(): void
    {
        $id = $this->job();
        $this->applyAs($id, 'interview');
        $main = $this->main($this->page($id));

        $this->assertStringContainsString('Wawancara', $main);
        $this->assertStringNotContainsString('>Interview<', $main);
        $this->assertStringNotContainsString(route('student.jobs.withdraw', $id), $main);
    }

    public function test_header_wraps_long_title_and_company_name(): void
    {
        $long = 'Koperasi Serba Usaha Produsen Hasil Hutan Rakyat dan Kerajinan Bambu Cianjur';
        $this->company->update(['name' => $long]);
        $main = $this->main($this->page($this->job(['title' => 'Pendamping Lapangan Program Rehabilitasi Hutan dan Lahan Kritis Desa'])));

        $this->assertStringContainsString('[&_h1]:wrap-break-word', $main);
        $this->assertStringContainsString($long, $main);
        $this->assertStringContainsString('flex-wrap', $main);
    }
}
