<?php

namespace Tests\Feature\Phase24;

use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * phase 24: pipeline pelamar (/company/applications) = tab tahap (7 nilai controller, jumlah dari query) + tabel 6 kolom
 * dengan checkbox aksi massal; halaman rekaman lamaran (/company/applications/{id}). endpoint dan kepemilikan tetap.
 */
class ApplicantPipelineTest extends TestCase
{
    use BuildsPhase1Data;
    use RefreshDatabase;

    private Company $company;
    private int $jobId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRegion();
        $this->company = $this->makeCompany();
        $this->jobId = $this->job($this->company, 'Pendamping Digitalisasi Koperasi');
    }

    private function job(Company $company, string $title): int
    {
        return DB::table('job_postings')->insertGetId([
            'company_id' => $company->id, 'title' => $title, 'slug' => 'uji-' . $company->id . '-' . md5($title),
            'status' => 'posted', 'published_at' => now()->subDay(), 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function apply(int $jobId, string $status, string $first = 'Sari', array $attrs = []): int
    {
        $student = $this->makeStudent($first, 'Rahayu');

        return DB::table('job_applications')->insertGetId(array_merge([
            'job_posting_id' => $jobId, 'user_id' => $student->user_id, 'status' => $status,
            'created_at' => now(), 'updated_at' => now(),
        ], $attrs));
    }

    private function html(string $url, ?Company $as = null): string
    {
        return $this->actingAs(($as ?? $this->company)->user)->get($url)->assertOk()->getContent();
    }

    public function test_page_header_tabs_and_counts_come_from_stored_stages(): void
    {
        foreach (['pending', 'new', 'reviewed', 'interview', 'accepted', 'hired', 'rejected'] as $i => $status) {
            $this->apply($this->jobId, $status, 'Kandidat' . $i);
        }
        $html = $this->html(route('company.applications.index'));

        $this->assertSame(1, substr_count($html, '<h1'));
        $this->assertStringContainsString('class="sg-container sg-co-apps"', $html);
        $this->assertStringNotContainsString('company3.jpg', $html);
        $this->assertStringNotContainsString('max-w-full', $html);

        // 8 tab (semua + 7 tahap controller), label dari StatusVocabulary, jumlah termasuk nilai lama yang setara
        preg_match_all('/id="tahap-[a-z]+-tab" data-tab="([a-z]+)"/', $html, $tabs);
        $this->assertSame(['all', 'new', 'reviewing', 'shortlisted', 'interview', 'offer', 'hired', 'rejected'], $tabs[1]);
        $expected = ['all' => 7, 'new' => 2, 'reviewing' => 1, 'shortlisted' => 0, 'interview' => 1, 'offer' => 0, 'hired' => 2, 'rejected' => 1];
        foreach ($expected as $tab => $count) {
            $this->assertMatchesRegularExpression('/data-tab="' . $tab . '"[^>]*>[^<]*<span class="sg-tabs__count">' . $count . '<\/span>/s', $html, "tab {$tab}");
        }
        foreach (['Menunggu', 'Ditinjau', 'Terpilih', 'Wawancara', 'Penawaran', 'Direkrut', 'Ditolak'] as $label) {
            $this->assertStringContainsString($label, $html);
        }
    }

    public function test_table_keeps_six_columns_checkboxes_and_one_word_per_status(): void
    {
        $accepted = $this->apply($this->jobId, 'accepted', 'Toni');
        $html = $this->html(route('company.applications.index'));

        preg_match('/<thead>(.*?)<\/thead>/s', $html, $head);
        preg_match_all('/<th scope="col"[^>]*>(.*?)<\/th>/s', $head[1], $cols);
        $labels = array_map(fn ($c) => trim(strip_tags($c)), $cols[1]);
        $this->assertSame(['Pilih', 'Kandidat', 'Posisi', 'Rating', 'Tanggal melamar', 'Status', 'Aksi'], $labels);

        // checkbox per baris (nilai = id lamaran) + data-label untuk baris bertumpuk di bawah 640px
        $this->assertStringContainsString('<input type="checkbox" value="' . $accepted . '" x-model="selected"', $html);
        $this->assertStringContainsString('sg-table--stack', $html);
        foreach (['Kandidat', 'Posisi', 'Rating', 'Tanggal melamar', 'Status', 'Aksi'] as $label) {
            $this->assertStringContainsString('data-label="' . $label . '"', $html);
        }
        // nilai lama accepted tampil dengan kata tabnya (Direkrut), bukan "Diterima"
        $this->assertMatchesRegularExpression('/data-application-id="' . $accepted . '".*?data-value="hired"/s', $html);
        $this->assertStringNotContainsString('>Diterima<', $html);
    }

    public function test_bulk_and_row_endpoints_are_unchanged(): void
    {
        $this->assertSame(['POST'], array_values(array_diff(Route::getRoutes()->getByName('company.applications.bulk-update')->methods(), ['HEAD'])));
        $this->assertSame('company/applications/bulk-update', Route::getRoutes()->getByName('company.applications.bulk-update')->uri());

        $this->apply($this->jobId, 'new');
        $html = $this->html(route('company.applications.index'));
        $this->assertStringContainsString("fetch('/company/applications/bulk-update'", str_replace('this.post(', 'fetch(', $html));
        $this->assertStringContainsString('application_ids: this.selected.map(Number)', $html);
        foreach (['/status`', '/rating`', '/notes`'] as $endpoint) {
            $this->assertStringContainsString('/company/applications/${this.current.id}' . $endpoint, $html);
        }
        $this->assertStringContainsString('/company/applications/export?ids=', $html);
    }

    public function test_bulk_update_only_touches_own_applications(): void
    {
        $own = $this->apply($this->jobId, 'new', 'Milik');
        $other = $this->makeCompany();
        $foreign = $this->apply($this->job($other, 'Lowongan Lain'), 'new', 'Asing');

        $this->actingAs($this->company->user)
            ->postJson(route('company.applications.bulk-update'), ['application_ids' => [$own, $foreign], 'status' => 'interview'])
            ->assertOk()->assertJson(['success' => true]);

        $this->assertSame('interview', DB::table('job_applications')->where('id', $own)->value('status'));
        $this->assertSame('new', DB::table('job_applications')->where('id', $foreign)->value('status'));
    }

    public function test_empty_account_shows_message_and_jobs_cta(): void
    {
        $html = $this->html(route('company.applications.index'));

        $this->assertStringContainsString('Belum ada pelamar', $html);
        $this->assertStringContainsString('assets/illustrations/empty-states/no-applications.svg', $html);
        $this->assertStringContainsString('href="' . route('company.jobs.index') . '"', $html);
        $this->assertStringNotContainsString('<table', $html);
    }

    public function test_empty_stage_message_is_rendered_for_the_table(): void
    {
        $this->apply($this->jobId, 'new');
        $html = $this->html(route('company.applications.index'));

        $this->assertStringContainsString('sg-co-apps__empty-row', $html);
        $this->assertStringContainsString('Belum ada pelamar di tahap', $html);
        $this->assertStringContainsString('Tidak ada pelamar yang cocok', $html);
    }

    public function test_job_filter_link_from_job_table_still_works(): void
    {
        $this->apply($this->jobId, 'new', 'Ada');
        $otherJob = $this->job($this->company, 'Analis Data Hasil Panen');
        $this->apply($otherJob, 'new', 'Lain');

        $response = $this->actingAs($this->company->user)->get(route('company.applications.index', ['job_posting_id' => $this->jobId]))->assertOk();
        $this->assertSame(1, $response->viewData('stats')['total']);
        $this->assertStringContainsString('Pelamar untuk lowongan <b>Pendamping Digitalisasi Koperasi</b>', $response->getContent());
    }

    public function test_detail_is_a_record_page_and_ownership_is_kept(): void
    {
        $id = $this->apply($this->jobId, 'reviewed', 'Rina', ['reviewed_at' => now(), 'notes' => 'Catatan tim']);
        $html = $this->html(route('company.applications.show', $id));

        $this->assertSame(1, substr_count($html, '<h1'));
        $this->assertStringNotContainsString('company1.jpg', $html);
        $this->assertStringNotContainsString('<style', $html);
        $this->assertStringContainsString('data-value="reviewing"', $html);
        // @js meng-escape garis miring; bandingkan path endpoint setelah dinormalkan
        $plain = str_replace('\\\\\\/', '/', $html);
        foreach (['update-status', 'shortlist', 'reject', 'hire', 'add-rating', 'add-notes'] as $name) {
            $this->assertStringContainsString(parse_url(route('company.applications.' . $name, $id), PHP_URL_PATH), $plain, $name);
        }
        $this->assertStringContainsString('Catatan tim', $html);

        $other = $this->makeCompany();
        $this->actingAs($other->user)->get(route('company.applications.show', $id))->assertNotFound();
    }

    public function test_views_have_no_inline_style_blocks(): void
    {
        foreach (['index', 'show'] as $view) {
            $source = file_get_contents(resource_path("views/company/applications/{$view}.blade.php"));
            $this->assertStringNotContainsString('<style', $source, $view);
            $this->assertStringNotContainsString('style="', $source, $view);
        }
    }
}
