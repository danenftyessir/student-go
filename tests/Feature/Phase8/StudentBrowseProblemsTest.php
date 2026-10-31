<?php

namespace Tests\Feature\Phase8;

use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * phase 8: telusuri masalah sebagai direktori. daftar (tabel) sebagai default, galeri dan peta sebagai alternatif,
 * satu form GET dengan nama parameter lama, chip filter yang bisa dihapus, label "N dari M", status lamaran milik
 * mahasiswa sendiri, state halaman kosong, tanpa hero foto atau <style>.
 * pencarian (ILIKE, khusus PostgreSQL) dan filter SDG (json contains) diuji di browser, bukan di sqlite.
 */
class StudentBrowseProblemsTest extends TestCase
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
        $this->student = $this->makeStudent('Farah', 'Wibowo');
    }

    private function browse(string $query = '', ?Student $student = null): string
    {
        return $this->actingAs(($student ?? $this->student)->user)
            ->get('/student/browse-problems'.$query)
            ->assertOk()
            ->getContent();
    }

    // isi <main> saja, supaya navbar/footer tidak ikut dinilai
    private function main(string $html): string
    {
        $start = strpos($html, '<main');

        return substr($html, $start, strpos($html, '</main>') - $start);
    }

    private function rowIds(string $html): array
    {
        preg_match_all('/<tr data-problem-id="(\d+)"/', $html, $m);

        return array_map('intval', $m[1]);
    }

    private function row(string $html, int $problemId): string
    {
        $start = strpos($html, '<tr data-problem-id="'.$problemId.'"');
        $this->assertNotFalse($start, "baris masalah {$problemId} tidak ada");

        return substr($html, $start, strpos($html, '</tr>', $start) - $start);
    }

    private function apply(Student $student, int $problemId, string $status): void
    {
        DB::table('applications')->insert([
            'student_id' => $student->id,
            'problem_id' => $problemId,
            'motivation' => 'Motivasi',
            'status' => $status,
            'applied_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_list_is_default_view_with_page_header_and_no_hero_or_inline_style(): void
    {
        $this->makeOpenProblems($this->institutionId, 3);

        $html = $this->browse();
        $main = $this->main($html);

        $this->assertSame(1, substr_count($html, '<h1'));
        $this->assertStringContainsString('Telusuri Masalah', $main);
        $this->assertMatchesRegularExpression('/<b>3<\/b>\s*masalah dibuka/', $main);

        // tabel sebagai default; tab daftar terpilih di html awal
        $this->assertStringContainsString('<table class="sg-table sg-table--stack">', $main);
        $this->assertMatchesRegularExpression('/data-view="list"[^>]*aria-selected="true"/s', $main);
        foreach (['Masalah', 'Instansi', 'Lokasi', 'Kuota', 'Kesulitan'] as $column) {
            $this->assertStringContainsString('<th scope="col">'.$column.'</th>', $main);
        }

        // hero lama, css/js 404, google font dan <style> tidak ada lagi
        $this->assertStringNotContainsString('dashboard-student.jpg', $html);
        $this->assertStringNotContainsString('css/browse-problems.css', $html);
        $this->assertStringNotContainsString('pages/browse-problems.js', $html);
        $this->assertStringNotContainsString('Space Grotesk', $html);
        $this->assertStringNotContainsString('<style', $main);
        $this->assertDoesNotMatchRegularExpression('/\b(Beginner|Intermediate|Advanced|Featured|Urgent)\b/', strip_tags($main));
    }

    public function test_filter_form_keeps_every_parameter_name(): void
    {
        $this->makeOpenProblems($this->institutionId, 1);

        $main = $this->main($this->browse());

        $this->assertMatchesRegularExpression('/<form method="GET" action="'.preg_quote(route('student.browse-problems.index'), '/').'" id="browse-filters"/', $main);
        foreach (['province_id', 'regency_id', 'sdg_categories[]', 'difficulty', 'duration', 'is_urgent', 'is_featured', 'view', 'search', 'sort'] as $name) {
            $this->assertStringContainsString('name="'.$name.'"', $main, "parameter {$name} hilang");
        }
        // cari dan urutan berada di luar <form> tetapi milik form filter
        $this->assertMatchesRegularExpression('/name="search"[^>]*form="browse-filters"|form="browse-filters"[^>]*name="search"/s', $main);
        $this->assertMatchesRegularExpression('/name="sort"[^>]*form="browse-filters"|form="browse-filters"[^>]*name="sort"/s', $main);
        // hanya empat nilai sort yang sudah ada
        foreach (['latest', 'deadline', 'popular', 'oldest'] as $sort) {
            $this->assertStringContainsString('value="'.$sort.'"', $main);
        }
        // kabupaten/kota dimuat dari route yang benar-benar ada
        $this->assertStringContainsString(str_replace('/', '\/', route('student.problems.get-regencies')), $main);
        $this->assertStringNotContainsString('browse-problems/get-regencies', $main);
    }

    public function test_every_open_problem_appears_exactly_once_across_pages(): void
    {
        $ids = $this->makeOpenProblems($this->institutionId, 14);

        $first = $this->rowIds($this->browse());
        $second = $this->rowIds($this->browse('?page=2'));

        $this->assertCount(12, $first);
        $this->assertCount(2, $second);
        $all = array_merge($first, $second);
        sort($all);
        $this->assertSame($ids, $all);
    }

    public function test_active_filters_show_removable_chips_and_n_of_m_label(): void
    {
        $ids = $this->makeOpenProblems($this->institutionId, 4);
        DB::table('problems')->whereIn('id', [$ids[0], $ids[1]])->update(['difficulty_level' => 'advanced']);
        DB::table('problems')->whereIn('id', [$ids[2], $ids[3]])->update(['difficulty_level' => 'beginner']);

        $query = '?province_id='.$this->provinceId.'&regency_id='.$this->regencyId.'&difficulty=advanced&sort=deadline';
        $main = $this->main($this->browse($query));

        $this->assertMatchesRegularExpression('/<b>2<\/b>\s*dari 4 masalah dibuka sesuai filter/', $main);
        $this->assertEqualsCanonicalizing([$ids[0], $ids[1]], $this->rowIds($main));

        $this->assertStringContainsString('Provinsi: Jawa Barat', $main);
        $this->assertStringContainsString('Kab/Kota: Kota Bandung', $main);
        $this->assertStringContainsString('Kesulitan: Lanjut', $main);
        $this->assertStringContainsString('Hapus semua filter', $main);

        $index = route('student.browse-problems.index');
        // hapus kesulitan: provinsi, kab/kota dan sort tetap
        $this->assertStringContainsString(e($index.'?'.http_build_query([
            'province_id' => (string) $this->provinceId, 'regency_id' => (string) $this->regencyId, 'sort' => 'deadline',
        ])), $main);
        // hapus provinsi juga menghapus kab/kota
        $this->assertStringContainsString(e($index.'?'.http_build_query(['difficulty' => 'advanced', 'sort' => 'deadline'])), $main);
    }

    public function test_deadline_sort_is_ascending_and_marks_the_header(): void
    {
        $ids = $this->makeOpenProblems($this->institutionId, 3);
        DB::table('problems')->where('id', $ids[0])->update(['application_deadline' => '2026-12-20']);
        DB::table('problems')->where('id', $ids[1])->update(['application_deadline' => '2026-12-05']);
        DB::table('problems')->where('id', $ids[2])->update(['application_deadline' => '2026-12-12']);

        $main = $this->main($this->browse('?sort=deadline'));

        $this->assertSame([$ids[1], $ids[2], $ids[0]], $this->rowIds($main));
        $this->assertStringContainsString('aria-sort="ascending"', $main);
        $this->assertStringNotContainsString('aria-sort', $this->main($this->browse()));
    }

    public function test_application_status_badge_only_for_own_applications(): void
    {
        [$mine, $rejected, $others, $none] = $this->makeOpenProblems($this->institutionId, 4);
        $other = $this->makeStudent('Bima', 'Saputra');
        $this->apply($this->student, $mine, 'pending');
        $this->apply($this->student, $rejected, 'rejected');
        $this->apply($other, $others, 'accepted');

        $main = $this->main($this->browse());

        $this->assertStringContainsString('Menunggu', $this->row($main, $mine));
        $this->assertStringContainsString('Ditolak', $this->row($main, $rejected));
        foreach ([$others, $none] as $id) {
            $row = $this->row($main, $id);
            $this->assertStringNotContainsString('sg-status', $row);
            $this->assertStringNotContainsString('Diterima', $row);
        }
    }

    public function test_row_shows_full_institution_name_location_quota_and_neutral_difficulty(): void
    {
        $longName = 'Dinas Pemberdayaan Masyarakat dan Desa Kabupaten Kepulauan Sangihe Talaud Raya';
        $this->assertSame(78, mb_strlen($longName));
        DB::table('institutions')->where('id', $this->institutionId)->update(['name' => $longName]);
        [$id] = $this->makeOpenProblems($this->institutionId, 1);
        DB::table('problems')->where('id', $id)->update(['difficulty_level' => 'intermediate', 'is_urgent' => true]);

        $row = $this->row($this->main($this->browse()), $id);

        $this->assertStringContainsString($longName, $row);
        $this->assertStringContainsString('Kota Bandung', $row);
        $this->assertStringContainsString('Jawa Barat', $row);
        $this->assertMatchesRegularExpression('/sg-browse-num">3<\/span>.*mahasiswa/s', $row);
        $this->assertStringContainsString('data-level="2"', $row);
        $this->assertStringContainsString('Menengah', $row);
        $this->assertStringContainsString('Mendesak', $row);
        $this->assertStringContainsString('SDG 1 · ', $row);
    }

    public function test_page_beyond_range_offers_first_page_with_filters_kept(): void
    {
        $ids = $this->makeOpenProblems($this->institutionId, 2);
        DB::table('problems')->whereIn('id', $ids)->update(['difficulty_level' => 'beginner']);

        $main = $this->main($this->browse('?difficulty=beginner&page=99'));

        $this->assertStringContainsString('Halaman 99 tidak ada', $main);
        $this->assertStringContainsString('Ke halaman 1', $main);
        $this->assertStringContainsString(e(route('student.browse-problems.index').'?difficulty=beginner&page=1'), $main);
        $this->assertSame([], $this->rowIds($main));
    }

    public function test_no_result_and_no_problem_states(): void
    {
        $ids = $this->makeOpenProblems($this->institutionId, 2);
        DB::table('problems')->whereIn('id', $ids)->update(['difficulty_level' => 'beginner']);

        $filtered = $this->main($this->browse('?difficulty=advanced'));
        $this->assertStringContainsString('Tidak ada masalah yang cocok', $filtered);
        $this->assertStringContainsString('Hapus semua filter', $filtered);
        $this->assertStringNotContainsString('role="tablist"', $filtered);

        DB::table('problems')->update(['status' => 'closed']);
        $empty = $this->main($this->browse());
        $this->assertStringContainsString('Belum ada masalah yang dibuka', $empty);
        $this->assertStringNotContainsString('Hapus semua filter', $empty);
    }

    public function test_map_panel_keeps_leaflet_without_duplicate_filter_and_escapes_popup_text(): void
    {
        $this->makeOpenProblems($this->institutionId, 1);

        $html = $this->browse('?view=map');

        $this->assertStringContainsString('leaflet@1.9.4/dist/leaflet.js', $html);
        $this->assertStringContainsString('leaflet.markercluster@1.5.3', $html);
        $this->assertStringContainsString('id="problems-map"', $html);
        $this->assertStringContainsString('mapViewActivated', $html);
        $this->assertStringNotContainsString('Filter Peta', $html);
        $this->assertStringContainsString('escape(value)', $html);
        $this->assertStringContainsString('e(problem.title)', $html);
        // @js() menulis url sebagai string json (garis miring di-escape)
        $this->assertStringContainsString('assets\/illustrations\/motifs\/map-pin.svg', $html);
    }

    public function test_regency_endpoint_used_by_the_filter_returns_regencies(): void
    {
        $this->actingAs($this->student->user)
            ->getJson(route('student.problems.get-regencies', ['province_id' => $this->provinceId]))
            ->assertOk()
            ->assertExactJson([['id' => $this->regencyId, 'name' => 'Kota Bandung']]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/student/browse-problems')->assertRedirect('/login');
    }
}
