<?php

namespace Tests\Feature\Phase29;

use App\Models\Company;
use App\Models\JobPosting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * phase 29 paket E: profil perusahaan publik (/company/{id}) = pola organisasi .sg-org tanpa foto hero, hanya data publik lama;
 * form ubah lowongan (/company/jobs/{id}/edit) = wizard create phase 23 dengan payload PUT yang sama.
 */
class CompanyPackageETest extends TestCase
{
    use BuildsPhase1Data;
    use RefreshDatabase;

    // payload form ubah yang direkam dari halaman lama (reports/phase29-E/before/metrics.json, edit_payload_keys)
    private const EDIT_PAYLOAD_KEYS = ['_method', '_token', 'department', 'description', 'impact_metrics', 'job_category_id', 'job_type', 'location',
        'qualifications', 'responsibilities', 'salary_range', 'sdg_alignment[]', 'skills[]', 'status', 'success_criteria', 'title'];

    private Company $company;
    private int $seqJob = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRegion();
        $this->company = $this->makeCompany([
            'name' => 'Koperasi Tani Makmur Jaya', 'industry' => 'Koperasi & Agribisnis', 'employee_count' => '101-200',
            'city' => 'Bandung', 'phone' => '022-7654321', 'website' => 'https://koperasi.test', 'description' => 'Koperasi petani.',
            'address' => 'Jalan Rahasia 12', 'postal_code' => '40111', 'founded_year' => 1997, 'verification_status' => 'verified',
        ]);
    }

    private function job(string $title, array $attrs = [], ?Company $company = null): int
    {
        $this->seqJob++;

        return DB::table('job_postings')->insertGetId(array_merge([
            'company_id' => ($company ?? $this->company)->id, 'title' => $title, 'slug' => 'uji-e-' . $this->seqJob,
            'location' => 'Kabupaten Garut', 'status' => 'posted', 'job_type' => 'Internship', 'department' => 'Pertanian',
            'description' => 'Deskripsi', 'responsibilities' => '- Tugas', 'qualifications' => '- Syarat',
            'skills' => json_encode(['UMKM', 'Business Development']), 'sdg_alignment' => json_encode([1, 8]),
            'salary_min' => 2000000, 'salary_max' => 3500000, 'salary_currency' => 'IDR', 'salary_period' => 'monthly',
            'published_at' => now()->subDay(), 'created_at' => now()->subMinutes($this->seqJob), 'updated_at' => now(),
        ], $attrs));
    }

    // ---- profil publik ----

    public function test_public_profile_is_the_organisation_pattern_without_hero_photo(): void
    {
        $this->job('Pendamping Koperasi');
        $html = $this->get(route('company.public', $this->company->id))->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, '<h1'));
        $this->assertStringContainsString('class="sg-container sg-org sg-co-public"', $html);
        foreach (['company-profile.jpg', '<style', 'bg-gradient', 'fade-in-up'] as $gone) {
            $this->assertStringNotContainsString($gone, $html, "tidak boleh ada: {$gone}");
        }
        // tanpa logo: inisial
        $this->assertMatchesRegularExpression('/<span class="sg-avatar sg-avatar--xl sg-avatar--square[^"]*"[^>]*>\s*KT/s', $html);
        // ikon telepon dan globe, status terverifikasi dari kosakata
        foreach (['#phone"', '#globe"', 'Terverifikasi', 'koperasi.test', 'href="tel:022-7654321"', '101-200 karyawan', 'Bandung'] as $text) {
            $this->assertStringContainsString($text, $html);
        }
    }

    public function test_public_profile_shows_only_public_data(): void
    {
        // email perusahaan kosong: email akun tidak boleh tampil; alamat, kode pos, tahun berdiri tidak ditampilkan
        $html = $this->get(route('company.public', $this->company->id))->assertOk()->getContent();

        foreach ([$this->company->user->email, 'Jalan Rahasia 12', '40111', '1997', 'mailto:'] as $private) {
            $this->assertStringNotContainsString($private, $html, "data non-publik: {$private}");
        }

        $this->company->update(['email' => 'info@koperasi.test']);
        $this->assertStringContainsString('href="mailto:info@koperasi.test"', $this->get(route('company.public', $this->company->id))->getContent());
    }

    public function test_public_jobs_and_summary_come_from_real_queries(): void
    {
        $open = $this->job('Analis Data Panen', ['job_type' => 'internship']);
        $this->job('Draf Rahasia', ['status' => 'draft', 'published_at' => null]);

        $html = $this->get(route('company.public', $this->company->id))->assertOk()->getContent();
        $this->assertStringContainsString('href="' . route('student.jobs.show', $open) . '"', $html);
        $this->assertStringContainsString('Analis Data Panen', $html);
        $this->assertStringContainsString('<dd>Magang</dd>', $html, 'tipe huruf kecil tetap dilabeli');
        $this->assertStringNotContainsString('Draf Rahasia', $html);
        $this->assertMatchesRegularExpression('/1<\/b>\s*lowongan dibuka/', strip_tags($html, '<b>'));
        $this->assertMatchesRegularExpression('/2<\/b>\s*lowongan/', strip_tags($html, '<b>'));
    }

    public function test_public_profile_empty_state_and_missing_company(): void
    {
        $html = $this->get(route('company.public', $this->company->id))->assertOk()->getContent();
        $this->assertStringContainsString('Belum ada lowongan yang dibuka', $html);
        $this->assertStringNotContainsString('lowongan dibuka</', $html, 'angka nol tidak ditampilkan');

        $this->get('/company/999999')->assertNotFound();
    }

    // ---- form ubah lowongan ----

    private function editHtml(int $id): string
    {
        return $this->actingAs($this->company->user)->get(route('company.jobs.edit', $id))->assertOk()->getContent();
    }

    // argumen pertama jobEditWizard(...) = Js::from($initial) -> array php
    private function initial(string $html): array
    {
        preg_match("/jobEditWizard\(JSON\.parse\('(.*?)'\)/s", $html, $m);

        return json_decode(json_decode('"' . $m[1] . '"'), true);
    }

    public function test_edit_is_the_create_wizard_with_the_same_put_payload(): void
    {
        $id = $this->job('Pendamping Koperasi');
        $html = $this->editHtml($id);

        $this->assertSame(1, substr_count($html, '<h1'));
        $positions = array_map(fn ($name) => strpos($html, '>' . $name . '<'), ['1. Detail Lowongan', '2. Deskripsi', '3. Keahlian', '4. SDG Alignment', '5. Kriteria Dampak']);
        $this->assertNotContains(false, $positions);
        $sorted = $positions;
        sort($sorted);
        $this->assertSame($sorted, $positions);
        $this->assertStringContainsString('Langkah 1 dari 5', $html);
        $this->assertStringContainsString('class="sg-co-preview"', $html);

        preg_match('/const EMPTY = \(\) => \(\{(.*?)\}\);/s', $html, $m);
        preg_match_all('/(\w+):/', $m[1], $keys);
        $payload = array_map(fn ($k) => in_array($k, ['skills', 'sdg_alignment'], true) ? $k . '[]' : $k, $keys[1]);
        array_push($payload, '_token', '_method');
        sort($payload);
        $this->assertSame(self::EDIT_PAYLOAD_KEYS, $payload);
        $this->assertStringContainsString("add('_method', 'PUT')", $html);
        $this->assertStringContainsString((string) \Illuminate\Support\Js::from(route('company.jobs.update', $id)), $html);

        foreach (['company3.jpg', '<style', 'Senior Software Engineer', 'Berlin', 'No Poverty', '>Full-time<', '>Internship<', 'value="published"', 'alert('] as $gone) {
            $this->assertStringNotContainsString($gone, $html, "tidak boleh ada: {$gone}");
        }
        foreach (['value="Internship">Magang', 'value="posted">Dibuka', 'value="draft">Draf', 'value="closed">Ditutup', 'SDG 1 · Tanpa Kemiskinan',
                  'Rp 2.000.000 – Rp 3.500.000 per bulan', 'Simpan perubahan'] as $text) {
            $this->assertStringContainsString($text, $html);
        }
        $this->assertDoesNotMatchRegularExpression('/#[0-9a-fA-F]{3,6}\b(?![^<]*sprite)/', preg_replace('/<script.*?<\/script>|href="[^"]*"/s', '', $html));
    }

    public function test_edit_keeps_stored_values_that_are_outside_the_lists(): void
    {
        $id = $this->job('Analis Data', ['job_type' => 'internship', 'status' => 'archived']);
        $html = $this->editHtml($id);

        // nilai tersimpan diisi ke formData dan tetap menjadi pilihan
        $this->assertStringContainsString('<option value="internship">Magang</option>', $html);
        $this->assertStringContainsString('<option value="archived">Diarsipkan</option>', $html);
        $this->assertStringContainsString('value="Business Development"', $html, 'keahlian di luar daftar tetap tampil');
        $initial = $this->initial($html);
        $this->assertSame(['UMKM', 'Business Development'], $initial['formData']['skills']);
        $this->assertSame(['1', '8'], $initial['formData']['sdg_alignment']);
        $this->assertSame('internship', $initial['formData']['job_type']);
        $this->assertSame('archived', $initial['formData']['status']);
        // gaji untuk pratinjau dalam bahasa indonesia, bukan accessor model
        $this->assertSame('Rp 2.000.000 – Rp 3.500.000 per bulan', $initial['formData']['salary_range']);
        $this->assertStringNotContainsString('Negotiable', $html);

        $noSalary = $this->job('Tanpa Gaji', ['salary_min' => null, 'salary_max' => null, 'salary_period' => null]);
        $html = $this->editHtml($noSalary);
        $this->assertSame('', $this->initial($html)['formData']['salary_range']);
        $this->assertStringNotContainsString('Negotiable', $html);
        $this->assertStringContainsString('Belum diisi', $html);
    }

    public function test_submitting_the_prefilled_form_keeps_the_record(): void
    {
        $id = $this->job('Pendamping Koperasi');
        $before = DB::table('job_postings')->where('id', $id)->first();

        // isian awal persis seperti yang dikirim form (salary_range = nilai accessor lama, diabaikan server)
        $this->actingAs($this->company->user)->put(route('company.jobs.update', $id), [
            'title' => 'Pendamping Koperasi', 'department' => 'Pertanian', 'location' => 'Kabupaten Garut', 'job_type' => 'Internship',
            'status' => 'posted', 'job_category_id' => '', 'salary_range' => JobPosting::find($id)->salary_range,
            'description' => 'Deskripsi', 'responsibilities' => '- Tugas', 'qualifications' => '- Syarat',
            'skills' => ['UMKM', 'Business Development'], 'sdg_alignment' => ['1', '8'], 'impact_metrics' => '', 'success_criteria' => '',
        ])->assertRedirect(route('company.jobs.show', $id));

        $after = DB::table('job_postings')->where('id', $id)->first();
        foreach (['title', 'department', 'location', 'job_type', 'status', 'salary_min', 'salary_max', 'salary_currency', 'salary_period', 'published_at'] as $column) {
            $this->assertEquals($before->$column, $after->$column, $column);
        }
        $this->assertSame(['UMKM', 'Business Development'], json_decode($after->skills, true));
    }

    public function test_server_errors_return_to_the_edit_wizard_with_old_input(): void
    {
        $id = $this->job('Pendamping Koperasi');
        $this->actingAs($this->company->user)->from(route('company.jobs.edit', $id))
            ->put(route('company.jobs.update', $id), ['title' => 'Judul Baru Diketik', 'status' => 'posted'])
            ->assertRedirect(route('company.jobs.edit', $id));

        $initial = $this->initial($this->editHtml($id));
        $this->assertSame('Judul Baru Diketik', $initial['formData']['title']);
        $this->assertSame('', $initial['formData']['department'], 'isian lama, bukan nilai tersimpan');
        $this->assertArrayHasKey('skills', $initial['errors']);
        $this->assertArrayHasKey('department', $initial['errors']);
    }

    public function test_create_and_edit_share_the_step_partial(): void
    {
        $id = $this->job('Pendamping Koperasi', ['skills' => json_encode(['UMKM'])]);
        $edit = $this->editHtml($id);
        $create = $this->actingAs($this->company->user)->get(route('company.jobs.create'))->assertOk()->getContent();

        // langkah 2-4 tidak bergantung pada mode: markup sama persis
        foreach ([1, 2, 3] as $step) {
            $pattern = '/<section class="sg-co-wizard__step" x-show="currentStep === ' . $step . '".*?<\/section>/s';
            preg_match($pattern, $edit, $e);
            preg_match($pattern, $create, $c);
            $this->assertNotEmpty($e, "langkah {$step} ada di edit");
            $this->assertSame($c[0], $e[0], "langkah {$step} sama");
        }
        // langkah 1: create punya isian gaji, edit menampilkan gaji tersimpan dan pilihan status
        $this->assertStringContainsString('id="job-salary"', $create);
        $this->assertStringNotContainsString('id="job-status"', $create);
        $this->assertStringNotContainsString('id="job-salary"', $edit);
        $this->assertStringContainsString('id="job-status"', $edit);
    }

    public function test_edit_keeps_ownership_and_create_keeps_its_preview_note(): void
    {
        $foreign = $this->job('Milik orang lain', [], $this->makeCompany());
        $this->get(route('company.jobs.edit', $foreign))->assertRedirect(route('login'));
        $this->actingAs($this->company->user)->get(route('company.jobs.edit', $foreign))->assertNotFound();

        $create = $this->actingAs($this->company->user)->get(route('company.jobs.create'))->assertOk()->getContent();
        $this->assertStringContainsString('Belum ada yang terbit sampai Anda menekan "Buat lowongan".', $create);
    }
}
