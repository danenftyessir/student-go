<?php

namespace Tests\Feature\Phase14;

use App\Models\Document;
use App\Models\Province;
use App\Models\Student;
use App\Support\RepositoryFormat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * phase 14: repositori sebagai perpustakaan. tabel enam kolom, label manusiawi (PDF, 1,4 MB, nama SDG + swatch),
 * tanpa baris unggulan, header = dokumen yang bisa dicapai, detail dengan definition list dan abstrak,
 * dokumen terkait tanpa duplikat. halaman daftar penuh memakai EXTRACT(YEAR ...) khusus PostgreSQL, jadi view daftar
 * dirender langsung dengan paginator; urutan/pagination sudah diuji di phase 1 dan di browser.
 */
class RepositoryTest extends TestCase
{
    use BuildsPhase1Data;
    use RefreshDatabase;

    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRegion();
        $this->student = $this->makeStudent('Rani', 'Lestari');
    }

    private function makeDocument(array $attrs = []): int
    {
        return DB::table('documents')->insertGetId(array_merge([
            'uploaded_by' => $this->student->user_id,
            'title' => 'Bank Sampah Digital: Inovasi Pengelolaan Sampah Berbasis Aplikasi',
            'description' => 'Laporan akhir program bank sampah di desa binaan.',
            'file_path' => 'documents/a.pdf',
            'file_type' => 'application/pdf',
            'file_size' => 1468006,
            'categories' => json_encode([4]),
            'institution_name' => 'Dinas Lingkungan Hidup Kota Bandung',
            'year' => 2026,
            'province_id' => $this->provinceId,
            'regency_id' => $this->regencyId,
            'view_count' => 1234,
            'download_count' => 56,
            'is_public' => true,
            'is_featured' => true,
            'status' => 'approved',
            'created_at' => '2026-10-01 10:00:00',
            'updated_at' => '2026-10-01 10:00:00',
        ], $attrs));
    }

    private function main(string $html): string
    {
        $start = strpos($html, '<main');

        return substr($html, $start, strpos($html, '</main>') - $start);
    }

    private function renderIndex(array $query = [], ?int $headerTotal = null): string
    {
        $docs = Document::with(['province', 'regency'])->orderBy('id')->get();
        $paginator = new LengthAwarePaginator($docs, $docs->count(), 12, 1, ['path' => '/student/repository']);
        $this->actingAs($this->student->user);
        request()->query->replace($query);

        return view('student.repository.index', [
            'documents' => $paginator,
            'provinces' => Province::orderBy('name')->get(['id', 'name']),
            'featuredDocuments' => $docs,
            'stats' => ['total_documents' => $headerTotal ?? $docs->count(), 'total_downloads' => 0, 'total_provinces' => 1, 'total_views' => 0, 'total_institutions' => 1],
            'years' => collect([2026]),
            'totalDocuments' => $docs->count(),
        ])->render();
    }

    public function test_formatter_gives_human_labels(): void
    {
        $this->assertSame('PDF', RepositoryFormat::fileType('application/pdf'));
        $this->assertSame('PDF', RepositoryFormat::fileType('pdf'));
        $this->assertSame('DOCX', RepositoryFormat::fileType('application/vnd.openxmlformats-officedocument.wordprocessingml.document'));
        $this->assertNull(RepositoryFormat::fileType(null));
        $this->assertSame('1,4 MB', RepositoryFormat::fileSize(1468006));
        $this->assertSame('493 KB', RepositoryFormat::fileSize(504790));
        $this->assertSame('1,0 MB', RepositoryFormat::fileSize(1023999));
        $this->assertSame('PDF · 4,5 MB', RepositoryFormat::fileChip('application/pdf', 4759338));
        $this->assertSame([['id' => 4, 'name' => 'Pendidikan Berkualitas']], RepositoryFormat::sdgs('[4, 4, 99]'));
    }

    public function test_list_is_a_six_column_table_without_featured_row_hero_or_raw_labels(): void
    {
        $this->makeDocument();
        $this->makeDocument(['title' => 'Posyandu Remaja', 'file_type' => 'pdf', 'categories' => json_encode([3, 6])]);
        $html = $this->renderIndex();
        $main = $this->main($html);

        preg_match_all('/<th scope="col"[^>]*>([^<]+)<\/th>/', $main, $m);
        $this->assertSame(['Judul', 'Instansi', 'Wilayah', 'Tahun', 'Dilihat', 'Diunduh'], $m[1]);
        $this->assertStringContainsString('sg-table--stack', $main);
        $this->assertSame(2, substr_count($main, '<tr data-document-id='), 'setiap dokumen tepat satu baris');
        $this->assertStringNotContainsString('Dokumen Unggulan', $main);
        $this->assertStringNotContainsString('repo-student.jpeg', $html);
        $this->assertStringNotContainsString('<style', $main);
        $this->assertStringNotContainsStringIgnoringCase('application/pdf', $main);
        $this->assertStringContainsString('PDF · 1,4 MB', $main);
        $this->assertStringContainsString('SDG 4 · Pendidikan Berkualitas', $main);
        $this->assertStringContainsString('data-sdg="6"', $main);
        $this->assertStringContainsString('1.234', $main);
        // satu <h1>, search menonjol, filter lama
        $this->assertSame(1, substr_count($html, '<h1'));
        $this->assertStringContainsString('name="search"', $main);
        foreach (['category', 'province_id', 'year', 'sort'] as $name) {
            $this->assertStringContainsString('name="'.$name.'"', $main);
        }
        $this->assertStringNotContainsString('most_cited', $main);
        foreach (['Showing', 'Download', 'Detail', 'Total Downloads', 'Knowledge Repository', 'Unknown'] as $english) {
            $this->assertStringNotContainsString($english, $main);
        }
    }

    public function test_header_counts_reachable_documents_and_filtered_total_reads_n_dari_m(): void
    {
        $this->makeDocument();
        $this->assertStringContainsString('<b>1</b> dokumen', $this->main($this->renderIndex()));
        $this->assertStringContainsString('<b>1</b> dari 214 dokumen', $this->main($this->renderIndex(['search' => 'bank'], 214)));
    }

    public function test_empty_repository_states(): void
    {
        $main = $this->main($this->renderIndex());
        $this->assertStringContainsString('Belum ada dokumen', $main);
        $this->assertStringContainsString('no-documents.svg', $main);
        $main = $this->main($this->renderIndex(['search' => 'tidak ada']));
        $this->assertStringContainsString('Tidak ada dokumen yang cocok', $main);
    }

    public function test_detail_page_is_a_catalogue_record_with_dl_abstract_and_existing_download(): void
    {
        $id = $this->makeDocument(['categories' => null]);
        $html = $this->actingAs($this->student->user)->get('/student/repository/'.$id)->assertOk()->getContent();
        $main = $this->main($html);

        $this->assertSame(1, substr_count($html, '<h1'));
        $this->assertStringContainsString('<dl class="sg-dl', $main);
        $this->assertStringContainsString('<dt>Format</dt>', $main);
        $this->assertStringContainsString('<dd>PDF</dd>', $main);
        $this->assertStringContainsString('<dd>1,4 MB</dd>', $main);
        $this->assertStringContainsString('<dt>Kabupaten/Kota</dt>', $main);
        $this->assertStringContainsString('sg-prose', $main);
        $this->assertStringContainsString(route('student.repository.download', $id), $main);
        $this->assertStringNotContainsStringIgnoringCase('application/pdf', $main);
        $this->assertStringNotContainsString('<style', $main);
        $this->assertStringNotContainsString('<iframe', $main);
        $this->assertSame(1235, (int) DB::table('documents')->where('id', $id)->value('view_count'), 'penghitung dilihat tetap naik satu');
    }

    public function test_detail_without_abstract_says_so_without_placeholder_text(): void
    {
        $id = $this->makeDocument(['description' => null, 'categories' => null]);
        $main = $this->main($this->actingAs($this->student->user)->get('/student/repository/'.$id)->assertOk()->getContent());
        $this->assertStringContainsString('Dokumen ini belum memiliki abstrak.', $main);
        $this->assertStringNotContainsString('Lorem', $main);
    }

    public function test_related_documents_have_no_duplicate_titles(): void
    {
        $id = $this->makeDocument(['categories' => null]);
        // judul kembar (seperti data audit) dan judul yang sama dengan dokumen ini
        $this->makeDocument(['title' => 'Posyandu Remaja', 'categories' => null]);
        $this->makeDocument(['title' => 'Posyandu Remaja', 'categories' => null]);
        $this->makeDocument(['title' => 'posyandu  remaja', 'categories' => null]);
        $this->makeDocument(['categories' => null]);
        $this->makeDocument(['title' => 'Sanitasi Sekolah', 'categories' => null]);

        $main = $this->main($this->actingAs($this->student->user)->get('/student/repository/'.$id)->assertOk()->getContent());
        preg_match_all('/<li data-related-id="\d+">\s*<a [^>]+>([^<]+)<\/a>/', $main, $m);
        $titles = array_map(fn ($t) => mb_strtolower(preg_replace('/\s+/', ' ', trim(html_entity_decode($t)))), $m[1]);

        $this->assertCount(2, $titles);
        $this->assertSame($titles, array_values(array_unique($titles)));
        $this->assertNotContains('bank sampah digital: inovasi pengelolaan sampah berbasis aplikasi', $titles);
    }

    public function test_non_numeric_and_unknown_ids_still_return_404(): void
    {
        $this->actingAs($this->student->user)->get('/student/repository/abc')->assertNotFound();
        $this->actingAs($this->student->user)->get('/student/repository/999999')->assertNotFound();
    }
}
