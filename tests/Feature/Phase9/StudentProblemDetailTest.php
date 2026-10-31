<?php

namespace Tests\Feature\Phase9;

use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * phase 9: detail masalah sebagai brief. rail lamar mengikuti record lamaran asli (bukan "sudah melamar" saja),
 * tautan lamar tetap /student/applications/create/{id}, syarat lamar tetap di ApplicationController@create,
 * foto hanya dari problem_images dan baru tampil setelah termuat, lokasi proyek dan alamat instansi berlabel.
 */
class StudentProblemDetailTest extends TestCase
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

    private function openProblem(array $attrs = []): int
    {
        $id = $this->makeOpenProblems($this->institutionId, 1)[0];
        DB::table('problems')->where('id', $id)->update(array_merge([
            'title' => 'Pemberdayaan Ekonomi Masyarakat Desa',
            'application_deadline' => now()->addDays(20)->toDateString(),
        ], $attrs));

        return $id;
    }

    private function detail(int $problemId, ?Student $student = null): string
    {
        return $this->actingAs(($student ?? $this->student)->user)
            ->get('/student/browse-problems/'.$problemId)
            ->assertOk()
            ->getContent();
    }

    // isi <main> saja, supaya navbar/footer tidak ikut dinilai
    private function main(string $html): string
    {
        $start = strpos($html, '<main');

        return substr($html, $start, strpos($html, '</main>') - $start);
    }

    private function rail(string $html): string
    {
        $start = strpos($html, '<aside class="sg-brief__rail"');
        $this->assertNotFalse($start, 'rail tidak ada');

        return substr($html, $start, strpos($html, '</aside>', $start) - $start);
    }

    private function apply(Student $student, int $problemId, string $status): int
    {
        return DB::table('applications')->insertGetId([
            'student_id' => $student->id,
            'problem_id' => $problemId,
            'motivation' => 'Motivasi',
            'status' => $status,
            'applied_at' => '2026-10-11 09:00:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_brief_layout_has_one_h1_breadcrumb_meta_and_no_inline_style_or_english(): void
    {
        $id = $this->openProblem(['background' => 'Latar belakang masalah.', 'objectives' => 'Tujuan proyek.', 'scope' => 'Ruang lingkup.']);

        $html = $this->detail($id);
        $main = $this->main($html);

        $this->assertSame(1, substr_count($html, '<h1'));
        $this->assertStringContainsString('<h1>Pemberdayaan Ekonomi Masyarakat Desa</h1>', $main);
        $this->assertStringContainsString('href="'.route('student.dashboard').'"', $main);
        $this->assertStringContainsString('href="'.route('student.browse-problems.index').'">Telusuri Masalah</a>', $main);
        $this->assertStringContainsString('<span aria-current="page">Pemberdayaan Ekonomi Masyarakat Desa</span>', $main);
        $this->assertMatchesRegularExpression('/Kuota <b>3<\/b> mahasiswa/', $main);
        foreach (['Deskripsi', 'Latar belakang', 'Tujuan', 'Ruang lingkup', 'Lokasi dan jadwal', 'Tentang instansi'] as $heading) {
            $this->assertMatchesRegularExpression('/<h2 id="brief-[a-z]+">'.$heading.'<\/h2>/', $main);
        }
        $this->assertStringContainsString('class="sg-prose"', $main);
        $this->assertStringNotContainsString('<style', $html);
        $this->assertStringNotContainsString('bg-gradient', $main);
        $this->assertDoesNotMatchRegularExpression('/\b(Requirements|Apply|Expected Outcomes|Views|Browse Problems|Gallery)\b/', strip_tags($main));
        $this->assertStringNotContainsString('Batalkan', $main);
    }

    public function test_without_application_rail_links_to_existing_apply_route(): void
    {
        $id = $this->openProblem();

        $rail = $this->rail($this->detail($id));

        $this->assertStringContainsString('data-state="open"', $rail);
        $this->assertStringContainsString('href="'.route('student.applications.create', $id).'"', $rail);
        $this->assertStringEndsWith('/student/applications/create/'.$id, route('student.applications.create', $id));
        $this->assertStringContainsString('Lamar masalah ini', $rail);
        $this->assertStringNotContainsString('sg-status', $rail);
    }

    public static function applicationStates(): array
    {
        return [
            'pending' => ['pending', 'Menunggu', null],
            'reviewed' => ['reviewed', 'Ditinjau', 'info'],
            'accepted' => ['accepted', 'Diterima', 'success'],
            'rejected' => ['rejected', 'Ditolak', 'danger'],
        ];
    }

    #[DataProvider('applicationStates')]
    public function test_rail_shows_the_real_application_status(string $status, string $label, ?string $tone): void
    {
        $id = $this->openProblem();
        $applicationId = $this->apply($this->student, $id, $status);

        $rail = $this->rail($this->detail($id));

        $this->assertStringContainsString('data-state="applied"', $rail);
        $this->assertStringContainsString('data-application-status="'.$status.'"', $rail);
        $this->assertStringContainsString($label, $rail);
        if ($tone) {
            $this->assertStringContainsString('data-tone="'.$tone.'"', $rail);
        } else {
            $this->assertStringNotContainsString('data-tone=', $rail);
        }
        $this->assertStringContainsString('href="'.route('student.applications.show', $applicationId).'"', $rail);
        // sudah melamar: tidak ada tombol lamar lagi
        $this->assertStringNotContainsString(route('student.applications.create', $id), $rail);
        $this->assertStringNotContainsString('Aplikasi Terkirim', $rail);
    }

    public function test_rejected_application_never_uses_a_success_tone(): void
    {
        $id = $this->openProblem();
        $this->apply($this->student, $id, 'rejected');

        $rail = $this->rail($this->detail($id));

        $this->assertStringNotContainsString('data-tone="success"', $rail);
        $this->assertStringNotContainsString('Diterima', $rail);
        $this->assertStringContainsString('tidak diterima', $rail);
    }

    public function test_another_students_application_does_not_change_the_rail(): void
    {
        $id = $this->openProblem();
        $this->apply($this->makeStudent('Bima', 'Saputra'), $id, 'accepted');

        $rail = $this->rail($this->detail($id));

        $this->assertStringContainsString('data-state="open"', $rail);
        $this->assertStringContainsString(route('student.applications.create', $id), $rail);
    }

    public function test_problem_that_is_not_open_shows_no_apply_link(): void
    {
        $id = $this->openProblem(['status' => 'in_progress']);

        $rail = $this->rail($this->detail($id));

        $this->assertStringContainsString('data-state="closed"', $rail);
        $this->assertStringContainsString('Berjalan', $rail);
        $this->assertStringContainsString('tidak menerima lamaran', $rail);
        $this->assertStringNotContainsString(route('student.applications.create', $id), $rail);
    }

    public function test_passed_deadline_matches_create_rule_and_create_still_redirects(): void
    {
        $id = $this->openProblem(['application_deadline' => now()->subDay()->toDateString()]);

        $rail = $this->rail($this->detail($id));

        $this->assertStringContainsString('data-state="expired"', $rail);
        $this->assertStringContainsString('Pendaftaran ditutup', $rail);
        $this->assertStringNotContainsString(route('student.applications.create', $id), $rail);

        // perilaku lamar tidak berubah: create tetap menolak dan kembali ke detail
        $this->actingAs($this->student->user)->get('/student/applications/create/'.$id)
            ->assertRedirect(route('student.browse-problems.show', $id))
            ->assertSessionHas('error');
    }

    public function test_filled_quota_is_information_only_and_apply_link_stays(): void
    {
        $id = $this->openProblem(['required_students' => 2]);
        $this->apply($this->makeStudent('Bima', 'Saputra'), $id, 'accepted');
        $this->apply($this->makeStudent('Citra', 'Lestari'), $id, 'accepted');

        $rail = $this->rail($this->detail($id));

        $this->assertStringContainsString('Kuota sudah terisi: 2 dari 2 mahasiswa diterima.', $rail);
        // create tidak memeriksa kuota, jadi tautan lamar tidak disembunyikan
        $this->assertStringContainsString(route('student.applications.create', $id), $rail);
        $this->assertMatchesRegularExpression('/sg-brief-num">2<\/span> mahasiswa · <span class="sg-brief-num">2<\/span> diterima/', $rail);
    }

    public function test_gallery_comes_only_from_problem_images_and_waits_for_load(): void
    {
        config(['services.supabase.project_id' => 'proyek-uji']);
        $id = $this->openProblem();
        DB::table('problem_images')->insert([
            ['problem_id' => $id, 'image_path' => 'problems/masalah-desa-1.jpg', 'caption' => 'Aktivitas masyarakat', 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['problem_id' => $id, 'image_path' => 'problems/masalah-desa-2.jpg', 'caption' => null, 'order' => 2, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $main = $this->main($this->detail($id));

        $this->assertStringContainsString('x-data="sgBriefGallery(', $main);
        $this->assertStringContainsString('x-show="visible" x-cloak', $main);
        $this->assertStringContainsString('masalah-desa-1.jpg', $main);
        $this->assertStringContainsString('Aktivitas masyarakat', $main);
        $this->assertStringContainsString('x-on:error="fail(current)"', $main);
        // tidak ada <img src> statis untuk foto masalah (tidak bisa tampil sebagai gambar rusak sebelum dicek)
        $this->assertDoesNotMatchRegularExpression('/<img src="[^"]*masalah-desa/', $main);
        $this->assertDoesNotMatchRegularExpression('/Gallery \d/', $main);
    }

    public function test_problem_without_images_has_no_photo_section(): void
    {
        $id = $this->openProblem();

        $main = $this->main($this->detail($id));

        $this->assertStringNotContainsString('sg-brief-gallery', $main);
        $this->assertStringNotContainsString('id="brief-photos"', $main);
    }

    public function test_project_location_and_institution_address_are_labelled_separately(): void
    {
        DB::table('institutions')->where('id', $this->institutionId)->update(['address' => 'Jl. Merdeka No. 1, Kota Bandung']);
        $id = $this->openProblem(['village' => 'Desa Sukamaju', 'detailed_location' => 'Balai desa, RT 02']);

        $main = $this->main($this->detail($id));

        $this->assertStringContainsString('<dt>Lokasi proyek</dt><dd>Desa Sukamaju, Kota Bandung, Jawa Barat</dd>', $main);
        $this->assertStringContainsString('<dt>Detail lokasi proyek</dt><dd>Balai desa, RT 02</dd>', $main);
        // nama kota yang sudah ada di kolom address tidak diulang
        $this->assertStringContainsString('<dt>Alamat instansi</dt><dd>Jl. Merdeka No. 1, Kota Bandung, Jawa Barat</dd>', $main);
    }

    public function test_long_institution_name_skills_and_similar_problems(): void
    {
        $longName = 'Dinas Pemberdayaan Masyarakat dan Desa Kabupaten Kepulauan Sangihe Talaud Raya';
        DB::table('institutions')->where('id', $this->institutionId)->update(['name' => $longName]);
        $id = $this->openProblem(['required_skills' => json_encode(['Ekonomi', 'Kewirausahaan'])]);
        $others = $this->makeOpenProblems($this->institutionId, 4);

        $main = $this->main($this->detail($id));

        $this->assertStringContainsString($longName, $main);
        $this->assertStringContainsString('<span class="sg-chip">Ekonomi</span>', $main);
        preg_match_all('/<a href="([^"]+)" class="hover:underline">/', substr($main, strpos($main, 'brief-similar')), $m);
        $this->assertCount(3, $m[1]);
        foreach ($m[1] as $href) {
            $this->assertContains((int) basename($href), $others);
            $this->assertStringStartsWith(route('student.browse-problems.index').'/', $href);
        }
    }

    public function test_view_counter_still_increments(): void
    {
        $id = $this->openProblem();
        DB::table('problems')->where('id', $id)->update(['views_count' => 4]);

        $this->detail($id);

        $this->assertSame(5, (int) DB::table('problems')->where('id', $id)->value('views_count'));
    }
}
