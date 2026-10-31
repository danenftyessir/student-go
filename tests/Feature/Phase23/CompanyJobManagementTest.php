<?php

namespace Tests\Feature\Phase23;

use App\Models\Company;
use App\Models\JobPosting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * phase 23: tabel lowongan (7 kolom, tab status, filter yang benar-benar diterapkan, keadaan kosong di dalam tabel),
 * wizard 5 langkah (nama langkah, urutan dan payload tetap; validasi per langkah; gaji dipetakan ke salary_min/max),
 * halaman rekaman lowongan (tanpa elemen palsu). kriteria L: placeholder lokal; field dan langkah = baseline; 7 kolom.
 */
class CompanyJobManagementTest extends TestCase
{
    use BuildsPhase1Data;
    use RefreshDatabase;

    private Company $company;
    private int $seqJob = 0;

    // payload wizard yang direkam dari halaman lama (reports/phase23/before/metrics_before.json)
    // + job_category_id (bug fix yang disetujui pengguna 2026-10-31: wizard kini punya field kategori)
    private const BASELINE_KEYS = ['_token', 'department', 'description', 'impact_metrics', 'job_category_id', 'job_type', 'location', 'qualifications',
        'responsibilities', 'salary_range', 'sdg_alignment[]', 'skills[]', 'success_criteria', 'title'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRegion();
        $this->company = $this->makeCompany();
    }

    private function category(string $name): int
    {
        return DB::table('job_categories')->insertGetId(['name' => $name, 'slug' => \Illuminate\Support\Str::slug($name), 'created_at' => now(), 'updated_at' => now()]);
    }

    private function job(string $title, string $status = 'posted', array $attrs = [], ?Company $company = null): int
    {
        $this->seqJob++;

        return DB::table('job_postings')->insertGetId(array_merge([
            'company_id' => ($company ?? $this->company)->id, 'title' => $title, 'slug' => 'uji-' . $this->seqJob,
            'location' => 'Kabupaten Garut', 'status' => $status, 'job_type' => 'Internship', 'department' => 'Pertanian',
            'description' => 'Deskripsi', 'views_count' => 7,
            'created_at' => now()->subMinutes($this->seqJob), 'updated_at' => now(),
        ], $attrs));
    }

    private function get200(string $url): string
    {
        return $this->actingAs($this->company->user)->get($url)->assertOk()->getContent();
    }

    public function test_table_keeps_seven_columns_with_real_status_and_counts(): void
    {
        $cat = $this->category('Pemberdayaan UMKM');
        $posted = $this->job('Pendamping Koperasi', 'posted', ['job_category_id' => $cat]);
        $this->job('Penyuluh Gizi', 'draft');
        $this->job('Analis Panen', 'closed');
        $student = $this->makeStudent('Ratna', 'Kusumawati');
        DB::table('job_applications')->insert(['job_posting_id' => $posted, 'user_id' => $student->user_id, 'status' => 'pending', 'created_at' => now(), 'updated_at' => now()]);

        $html = $this->get200(route('company.jobs.index'));

        $this->assertSame(1, substr_count($html, '<h1'));
        preg_match_all('/<th scope="col"[^>]*>(.*?)<\/th>/s', $html, $heads);
        $this->assertSame(['Judul', 'Kategori', 'Status', 'Pelamar', 'Dilihat', 'Tanggal dibuat', 'Aksi'], array_map(fn ($h) => trim(strip_tags($h)), $heads[1]));
        foreach (['Dibuka', 'Draf', 'Ditutup', 'Pemberdayaan UMKM', '3</b> lowongan', '1</b> pelamar'] as $text) {
            $this->assertStringContainsString($text, $html);
        }
        $this->assertStringContainsString('href="' . route('company.applications.index', ['job_posting_id' => $posted]) . '"', $html);
        foreach (['company2.jpg', '<style', 'Total Lowongan', 'Lowongan Ditutup', 'value="published"'] as $gone) {
            $this->assertStringNotContainsString($gone, $html, "tidak boleh ada: {$gone}");
        }
    }

    public function test_search_status_and_category_filters_run_on_the_server(): void
    {
        $cat = $this->category('Kesehatan Masyarakat');
        $a = $this->job('Penyuluh Posyandu', 'posted', ['job_category_id' => $cat]);
        $b = $this->job('Pendamping Koperasi', 'draft');
        $other = $this->job('Lowongan Perusahaan Lain', 'posted', [], $this->makeCompany());

        $ids = function (string $query) {
            preg_match_all('/data-job-id="(\d+)"/', $this->get200(route('company.jobs.index') . '?' . $query), $m);

            return array_map('intval', $m[1]);
        };

        $this->assertEqualsCanonicalizing([$a, $b], $ids(''), 'hanya lowongan perusahaan sendiri');
        $this->assertNotContains($other, $ids(''));
        $this->assertSame([$a], $ids('search=posyandu'));
        $this->assertSame([$a], $ids('search=' . urlencode('kesehatan')), 'cari nama kategori');
        $this->assertSame([$b], $ids('status=draft'));
        $this->assertSame([$a], $ids('status=published'), "nilai lama 'published' = posted");
        $this->assertSame([$a], $ids('category=' . $cat));
        $this->assertSame([], $ids('search=' . urlencode('%')), 'wildcard di-escape');
    }

    public function test_tabs_and_pager_keep_the_query(): void
    {
        for ($i = 1; $i <= 12; $i++) {
            $this->job('Lowongan Draf ' . $i, 'draft');
        }
        $this->job('Satu yang dibuka', 'posted');

        $html = $this->get200(route('company.jobs.index', ['status' => 'draft']));
        $this->assertMatchesRegularExpression('/aria-current="page"[^>]*>\s*Draf/', $html);
        preg_match('/href="([^"]*page=2[^"]*)"/', $html, $m);
        $this->assertNotEmpty($m, 'pager ada');
        $this->assertStringContainsString('status=draft', html_entity_decode($m[1]));
        $this->assertSame(2, substr_count($this->get200(route('company.jobs.index', ['status' => 'draft', 'page' => 2])), 'data-job-id='));
    }

    public function test_empty_states_live_inside_the_table(): void
    {
        $html = $this->get200(route('company.jobs.index'));
        $this->assertMatchesRegularExpression('/<td colspan="7">\s*<div class="sg-empty">.*Belum ada lowongan/s', $html);
        $this->assertStringContainsString('href="' . route('company.jobs.create') . '"', $html);

        $this->job('Pendamping Koperasi');
        $none = $this->get200(route('company.jobs.index', ['search' => 'zzz']));
        $this->assertStringContainsString('Tidak ada lowongan yang cocok', $none);
    }

    public function test_delete_action_keeps_its_endpoint_and_ownership(): void
    {
        $id = $this->job('Pendamping Koperasi');
        $html = $this->get200(route('company.jobs.index'));
        $this->assertStringContainsString('action="' . route('company.jobs.destroy', $id) . '"', $html);
        $this->assertStringContainsString('name="_method" value="DELETE"', $html);

        $this->actingAs($this->company->user)->delete(route('company.jobs.destroy', $id))->assertRedirect(route('company.jobs.index'));
        $this->assertSoftDeleted('job_postings', ['id' => $id]);

        $foreign = $this->job('Milik orang lain', 'posted', [], $this->makeCompany());
        $this->actingAs($this->company->user)->delete(route('company.jobs.destroy', $foreign))->assertRedirect(route('company.jobs.index'));
        $this->assertNotSoftDeleted('job_postings', ['id' => $foreign]);
    }

    public function test_wizard_keeps_five_steps_payload_and_local_placeholders(): void
    {
        $html = $this->get200(route('company.jobs.create'));

        $this->assertSame(1, substr_count($html, '<h1'));
        $positions = array_map(fn ($name) => strpos($html, '>' . $name . '<'), ['1. Detail Lowongan', '2. Deskripsi', '3. Keahlian', '4. SDG Alignment', '5. Kriteria Dampak']);
        $this->assertNotContains(false, $positions, 'lima langkah ada');
        $sorted = $positions;
        sort($sorted);
        $this->assertSame($sorted, $positions, 'urutan langkah tetap');
        $this->assertStringContainsString('Langkah 1 dari 5', $html);

        // payload: kunci formData sama dengan baseline dan dikirim ke company.jobs.store
        preg_match('/const EMPTY = \(\) => \(\{(.*?)\}\);/s', $html, $m);
        preg_match_all('/(\w+):/', $m[1], $keys);
        $payload = array_map(fn ($k) => in_array($k, ['skills', 'sdg_alignment'], true) ? $k . '[]' : $k, $keys[1]);
        $payload[] = '_token';
        sort($payload);
        $this->assertSame(self::BASELINE_KEYS, $payload);
        $this->assertStringContainsString((string) \Illuminate\Support\Js::from(route('company.jobs.store')), $html);

        foreach (['Senior Software Engineer', 'Berlin', 'Germany', 'cth: Engineering', 'No Poverty', 'company3.jpg', '<style', 'deliver minimal 2 fitur'] as $gone) {
            $this->assertStringNotContainsString($gone, $html, "tidak boleh ada: {$gone}");
        }
        foreach (['Koperasi', 'Kabupaten Garut', 'SDG 1 · Tanpa Kemiskinan', 'Pratinjau untuk mahasiswa', 'value="Internship">Magang'] as $text) {
            $this->assertStringContainsString($text, $html);
        }
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'title' => 'Pendamping Digitalisasi Koperasi Uji', 'department' => 'Pemberdayaan Masyarakat',
            'location' => 'Kabupaten Garut, Jawa Barat', 'job_type' => 'Internship', 'salary_range' => 'Rp 3.000.000 - Rp 4.500.000 per bulan',
            'description' => 'Deskripsi uji.', 'responsibilities' => "- Tugas satu\n- Tugas dua", 'qualifications' => '- Syarat satu',
            'skills' => ['UMKM', 'Koperasi'], 'sdg_alignment' => ['8', '9'], 'impact_metrics' => 'Metrik uji.', 'success_criteria' => 'Kriteria uji.',
        ], $override);
    }

    public function test_store_persists_every_value_and_maps_the_salary(): void
    {
        $response = $this->actingAs($this->company->user)->post(route('company.jobs.store'), $this->payload());
        $job = JobPosting::where('company_id', $this->company->id)->firstOrFail();
        $response->assertRedirect(route('company.jobs.show', $job->id));

        $decode = fn ($value) => is_array($value) ? $value : json_decode((string) $value, true);
        foreach (['title', 'department', 'location', 'job_type', 'description', 'qualifications', 'impact_metrics', 'success_criteria'] as $field) {
            $this->assertSame($this->payload()[$field], $job->{$field}, $field);
        }
        $this->assertSame(['UMKM', 'Koperasi'], $decode($job->skills));
        $this->assertSame(['8', '9'], $decode($job->sdg_alignment));
        $this->assertSame('posted', $job->status);
        $this->assertEquals(3000000, (float) $job->salary_min);
        $this->assertEquals(4500000, (float) $job->salary_max);
        $this->assertSame('IDR', $job->salary_currency);
        $this->assertSame('monthly', $job->salary_period);

        // gaji opsional: kosong -> tidak ada angka (tidak dikarang)
        $this->actingAs($this->company->user)->post(route('company.jobs.store'), $this->payload(['title' => 'Tanpa Gaji', 'salary_range' => '']));
        $plain = JobPosting::where('title', 'Tanpa Gaji')->firstOrFail();
        $this->assertNull($plain->salary_min);
        $this->assertNull($plain->salary_max);
    }

    public function test_server_validation_returns_to_the_wizard_with_old_input_and_errors(): void
    {
        $this->actingAs($this->company->user)
            ->from(route('company.jobs.create'))
            ->post(route('company.jobs.store'), $this->payload(['department' => '', 'skills' => []]))
            ->assertRedirect(route('company.jobs.create'));

        $html = $this->actingAs($this->company->user)->get(route('company.jobs.create'))->assertOk()->getContent();
        $this->assertStringContainsString('Pendamping Digitalisasi Koperasi Uji', $html, 'isian lama dikembalikan');
        // galat server ikut ke data awal wizard; Js::from menulis tanda kutip sebagai \u0022
        $quote = '\u0022';
        $errorsAt = strpos($html, 'errors' . $quote . ':{');
        $this->assertNotFalse($errorsAt, 'galat server ada di data awal');
        $this->assertStringContainsString('department' . $quote, substr($html, $errorsAt, 600));
        $this->assertStringContainsString('skills' . $quote, substr($html, $errorsAt, 600));
        $this->assertSame(0, JobPosting::count());
    }

    public function test_detail_is_a_record_page_without_invented_elements(): void
    {
        $cat = $this->category('Pemberdayaan UMKM');
        $id = $this->job('Pendamping Koperasi', 'posted', [
            'job_category_id' => $cat, 'skills' => json_encode(['UMKM']), 'sdg_alignment' => json_encode([8]),
            'impact_metrics' => 'Tiga puluh anggota mencatat penjualan.', 'success_criteria' => 'Laporan bulanan tersusun.',
            'responsibilities' => "- Melatih pengurus\n- Menyusun laporan", 'salary_min' => 3000000, 'salary_max' => 4500000,
            'salary_currency' => 'IDR', 'salary_period' => 'monthly', 'published_at' => now()->subDay(),
        ]);
        $student = $this->makeStudent('Ratna', 'Kusumawati', 'akun lama ratna');
        DB::table('job_applications')->insert(['job_posting_id' => $id, 'user_id' => $student->user_id, 'status' => 'new', 'created_at' => now(), 'updated_at' => now()]);

        $html = $this->get200(route('company.jobs.show', $id));

        $this->assertSame(1, substr_count($html, '<h1'));
        foreach (['Pendamping Koperasi', 'Dibuka', 'Pemberdayaan UMKM', 'Magang', 'Rp 3.000.000 – Rp 4.500.000 per bulan', 'SDG 8 · Pekerjaan Layak',
                     'Kriteria Dampak', 'Tiga puluh anggota mencatat penjualan.', 'Melatih pengurus', 'Ratna Kusumawati', 'Menunggu'] as $text) {
            $this->assertStringContainsString($text, $html, $text);
        }
        $this->assertStringNotContainsString('akun lama ratna', $html);
        $this->assertStringContainsString('href="' . route('company.applications.index', ['job_posting_id' => $id]) . '"', $html);
        $this->assertStringContainsString('action="' . route('company.jobs.update-linkedin', $id) . '"', $html);
        foreach (['name="linkedin_url"', 'name="sdg_goals[]"', 'name="_method" value="PATCH"'] as $field) {
            $this->assertStringContainsString($field, $html);
        }
        foreach (['Invite Candidates', 'Guest View', 'Messaged', 'Not specified', 'Flexible', '1 hire', 'Share on LinkedIn', 'linkedin.com/sharing', '/jobs/pendamping', 'Active', '<style'] as $gone) {
            $this->assertStringNotContainsString($gone, $html, "tidak boleh ada: {$gone}");
        }
    }

    public function test_detail_and_edit_keep_ownership_and_role_access(): void
    {
        $this->get(route('company.jobs.index'))->assertRedirect(route('login'));
        $this->get(route('company.jobs.create'))->assertRedirect(route('login'));

        $foreign = $this->job('Milik orang lain', 'posted', [], $this->makeCompany());
        $this->actingAs($this->company->user)->get(route('company.jobs.show', $foreign))->assertNotFound();

        $own = $this->job('Pendamping Koperasi');
        $this->actingAs($this->company->user)->get(route('company.jobs.edit', $own))->assertOk();

        $student = $this->makeStudent('Ratna', 'Kusumawati');
        $this->actingAs($student->user)->get(route('company.jobs.index'))->assertRedirect();
        $this->actingAs($student->user)->post(route('company.jobs.store'), $this->payload())->assertRedirect();
        $this->assertSame(0, JobPosting::where('title', $this->payload()['title'])->count(), 'mahasiswa tidak dapat membuat lowongan');
    }

    // ---- bug fix 2026-10-31 (atas permintaan pengguna setelah laporan phase 23) ----

    public function test_same_title_gets_a_unique_slug_instead_of_a_500(): void
    {
        $other = $this->makeCompany();
        $this->actingAs($other->user)->post(route('company.jobs.store'), $this->payload())->assertRedirect();
        $this->actingAs($this->company->user)->post(route('company.jobs.store'), $this->payload())->assertRedirect();

        // lowongan terhapus tetap memegang slug-nya; judul yang sama tetap bisa dipasang
        JobPosting::where('company_id', $other->id)->first()->delete();
        $this->actingAs($this->company->user)->post(route('company.jobs.store'), $this->payload())->assertRedirect();

        $slugs = JobPosting::withTrashed()->orderBy('id')->pluck('slug')->all();
        $this->assertSame(['pendamping-digitalisasi-koperasi-uji', 'pendamping-digitalisasi-koperasi-uji-2', 'pendamping-digitalisasi-koperasi-uji-3'], $slugs);

        // mengubah lowongan tanpa mengganti judul mempertahankan slug-nya; judul milik lowongan lain mendapat akhiran
        $mine = JobPosting::where('company_id', $this->company->id)->orderBy('id')->first();
        $this->actingAs($this->company->user)->put(route('company.jobs.update', $mine->id), $this->payload(['status' => 'posted']))->assertRedirect();
        $this->assertSame('pendamping-digitalisasi-koperasi-uji-2', $mine->fresh()->slug);
    }

    public function test_skills_and_sdgs_are_stored_as_plain_json_arrays(): void
    {
        $this->actingAs($this->company->user)->post(route('company.jobs.store'), $this->payload());
        $id = JobPosting::firstOrFail()->id;

        $raw = DB::table('job_postings')->where('id', $id)->first(['skills', 'sdg_alignment']);
        $this->assertSame(['UMKM', 'Koperasi'], json_decode($raw->skills, true), 'satu kali encode, bukan string berisi JSON');
        $this->assertSame(['8', '9'], json_decode($raw->sdg_alignment, true));
        $this->assertSame(['UMKM', 'Koperasi'], JobPosting::find($id)->skills, 'cast model menghasilkan array');

        $this->actingAs($this->company->user)->put(route('company.jobs.update', $id), $this->payload(['skills' => ['Survei'], 'status' => 'posted']));
        $this->assertSame(['Survei'], json_decode(DB::table('job_postings')->where('id', $id)->value('skills'), true));

        $this->actingAs($this->company->user)->patch(route('company.jobs.update-linkedin', $id), ['sdg_goals' => ['3', '11']]);
        $this->assertSame([3, 11], json_decode(DB::table('job_postings')->where('id', $id)->value('required_sdgs'), true));
    }

    public function test_wizard_offers_categories_and_stores_the_choice(): void
    {
        $cat = $this->category('Pemberdayaan UMKM');
        $html = $this->get200(route('company.jobs.create'));
        $this->assertStringContainsString('<option value="' . $cat . '">Pemberdayaan UMKM</option>', $html);
        $this->assertStringContainsString('id="job-category"', $html);

        $this->actingAs($this->company->user)->post(route('company.jobs.store'), $this->payload(['job_category_id' => (string) $cat]));
        $this->assertSame($cat, (int) JobPosting::firstOrFail()->job_category_id);
        $this->assertStringContainsString('Pemberdayaan UMKM', $this->get200(route('company.jobs.index')));

        // tanpa kategori tetap boleh (opsional)
        $this->actingAs($this->company->user)->post(route('company.jobs.store'), $this->payload(['title' => 'Tanpa Kategori', 'job_category_id' => '']));
        $this->assertNull(JobPosting::where('title', 'Tanpa Kategori')->firstOrFail()->job_category_id);
    }

    public function test_company_views_of_its_own_job_are_not_counted(): void
    {
        $id = $this->job('Pendamping Koperasi', 'posted', ['views_count' => 7]);
        $this->get200(route('company.jobs.show', $id));
        $this->get200(route('company.jobs.show', $id));
        $this->assertSame(7, (int) DB::table('job_postings')->where('id', $id)->value('views_count'));
    }

    public function test_editing_without_salary_fields_keeps_the_stored_salary(): void
    {
        $this->actingAs($this->company->user)->post(route('company.jobs.store'), $this->payload());
        $job = JobPosting::firstOrFail();

        // form edit mengirim field lain (termasuk status) tetapi tidak salary_min/max/currency
        $this->actingAs($this->company->user)->put(route('company.jobs.update', $job->id), $this->payload(['description' => 'Deskripsi baru.', 'status' => 'posted']));
        $job->refresh();
        $this->assertSame('Deskripsi baru.', $job->description);
        $this->assertEquals(3000000, (float) $job->salary_min);
        $this->assertEquals(4500000, (float) $job->salary_max);
        $this->assertSame('IDR', $job->salary_currency);
    }
}
