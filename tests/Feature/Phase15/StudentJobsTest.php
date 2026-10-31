<?php

namespace Tests\Feature\Phase15;

use App\Models\Company;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * phase 15: lowongan, notifikasi lowongan dan lowongan tersimpan memakai pola daftar + filter phase 8.
 * tanpa foto header, 17 SDG bernama bahasa indonesia dengan label tertaut, baris Posisi · Perusahaan · Lokasi · SDG · Tenggat,
 * parameter dan endpoint lama tetap. pencarian (ILIKE) dan filter SDG (json contains) diuji di browser, bukan di sqlite.
 */
class StudentJobsTest extends TestCase
{
    use BuildsPhase1Data;
    use RefreshDatabase;

    private Student $student;

    private Company $company;

    private int $seqJob = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRegion();
        $this->student = $this->makeStudent('Farah', 'Wibowo');
        $this->company = $this->makeCompany();
    }

    private function job(string $title, array $attrs = [], ?Company $company = null): int
    {
        $this->seqJob++;

        return DB::table('job_postings')->insertGetId(array_merge([
            'company_id' => ($company ?? $this->company)->id, 'title' => $title, 'slug' => 'uji-'.$this->seqJob,
            'location' => 'Kabupaten Garut', 'status' => 'posted', 'job_type' => 'Internship', 'department' => 'Pertanian',
            'description' => 'Deskripsi', 'sdg_alignment' => json_encode([13, 15]),
            'salary_min' => 1000000, 'salary_max' => 2000000, 'salary_currency' => 'IDR',
            'published_at' => now()->subDays($this->seqJob), 'expires_at' => now()->addDays(20)->startOfDay()->addHours(12),
            'created_at' => now(), 'updated_at' => now(),
        ], $attrs));
    }

    private function category(string $name): int
    {
        return DB::table('job_categories')->insertGetId(['name' => $name, 'slug' => Str::slug($name), 'created_at' => now(), 'updated_at' => now()]);
    }

    private function page(string $url): string
    {
        return $this->actingAs($this->student->user)->get($url)->assertOk()->getContent();
    }

    // isi <main> saja, supaya navbar/footer tidak ikut dinilai
    private function main(string $html): string
    {
        $start = strpos($html, '<main');

        return substr($html, $start, strpos($html, '</main>') - $start);
    }

    private function row(string $html, int $jobId): string
    {
        $start = strpos($html, '<tr data-job-id="'.$jobId.'"');
        $this->assertNotFalse($start, "baris lowongan {$jobId} tidak ada");

        return substr($html, $start, strpos($html, '</tr>', $start) - $start);
    }

    private function assertNoLegacyChrome(string $html): void
    {
        $main = $this->main($html);
        $this->assertStringNotContainsString('<style', $main);
        $this->assertStringNotContainsString('magang-preview', $html);
        $this->assertStringNotContainsString('Space+Grotesk', $html);
        $this->assertDoesNotMatchRegularExpression('/\bfa-[a-z]/', $html);
        $this->assertDoesNotMatchRegularExpression('/font-?awesome/i', $html);
        $this->assertSame(1, substr_count($html, '<h1'));
        $this->assertStringContainsString('class="sg-page-header"', $main);
    }

    public function test_list_uses_page_header_and_phase8_row_pattern_with_job_fields(): void
    {
        $longName = 'Koperasi Serba Usaha Produsen Hasil Hutan Rakyat dan Kerajinan Bambu Cianjur';
        $this->assertSame(76, mb_strlen($longName));
        $company = $this->makeCompany(['name' => $longName]);
        $id = $this->job('Pendamping Reboisasi Desa', ['location' => 'Desa Cijambe, Jawa Barat'], $company);

        $html = $this->page('/student/jobs');
        $this->assertNoLegacyChrome($html);
        $main = $this->main($html);

        $this->assertStringContainsString('Lowongan Kerja &amp; Magang', $main);
        $this->assertStringContainsString('class="sg-browse__layout"', $main);
        $this->assertStringContainsString('id="jobs-filters"', $main);
        $this->assertStringContainsString('<b>1</b> lowongan dibuka', $main);
        foreach (['Posisi', 'Perusahaan', 'Lokasi', 'SDG', 'Tenggat'] as $col) {
            $this->assertStringContainsString('<th scope="col"'.($col === 'SDG' ? ' class="min-w-40"' : '').'>'.$col.'</th>', $main);
        }

        $row = $this->row($html, $id);
        $this->assertStringContainsString('Pendamping Reboisasi Desa', $row);
        $this->assertStringContainsString($longName, $row);
        $this->assertStringContainsString('Desa Cijambe, Jawa Barat', $row);
        $this->assertStringContainsString('Magang · Rp 1–2 juta', $row);
        $this->assertStringContainsString('SDG 13 · Penanganan perubahan iklim', $row);
        $this->assertStringContainsString('data-sdg="15"', $row);
        $this->assertStringContainsString('20 hari lagi', $row);
        $this->assertStringContainsString('/student/jobs/'.$id.'"', $row);
    }

    public function test_sdg_filter_lists_seventeen_indonesian_names_with_linked_visible_labels(): void
    {
        $this->job('Lowongan A');
        $html = $this->main($this->page('/student/jobs?sdg[]=4'));

        preg_match_all('/<label class="sg-check sg-sdg py-1\.5" data-sdg="(\d+)" for="filter-sdg-(\d+)">\s*<input type="checkbox" name="sdg\[\]" id="filter-sdg-(\d+)" value="(\d+)"/', $html, $m);
        $this->assertSame(range(1, 17), array_map('intval', $m[1]));
        $this->assertSame($m[2], $m[3], 'label for harus sama dengan id input');
        $this->assertSame($m[3], $m[4]);

        foreach ([1 => 'Tanpa kemiskinan', 4 => 'Pendidikan berkualitas', 13 => 'Penanganan perubahan iklim', 17 => 'Kemitraan untuk mencapai tujuan'] as $n => $name) {
            $this->assertStringContainsString('<span>'.$n.'. '.$name.'</span>', $html);
        }
        $this->assertDoesNotMatchRegularExpression('/<span[^>]*>\s*SDG \d+\s*<\/span>/', $html, 'tidak boleh hanya nomor SDG');
        $this->assertMatchesRegularExpression('/id="filter-sdg-4" value="4" checked/', $html);
        $this->assertStringContainsString('SDG 4 · Pendidikan berkualitas', $html);   // chip filter aktif
    }

    public function test_category_filter_keeps_old_parameters_and_labels_n_of_m(): void
    {
        $cat = $this->category('Pemberdayaan UMKM');
        $a = $this->job('Pendamping UMKM', ['job_category_id' => $cat, 'location' => 'Kota Bandung']);
        $b = $this->job('Penulis Konten Desa');

        $html = $this->main($this->page('/student/jobs?category='.$cat.'&location=Bandung&job_type[]=Internship&salary_min=500000&sort=oldest'));
        $this->assertStringContainsString('<b>1</b> dari 2 lowongan dibuka sesuai filter', $html);
        $this->row($html, $a);
        $this->assertStringNotContainsString('data-job-id="'.$b.'"', $html);

        // parameter tanpa kontrol tetap ikut terkirim; chip bisa menghapus satu per satu
        $this->assertStringContainsString('<input type="hidden" name="location" value="Bandung">', $html);
        $this->assertStringContainsString('<input type="hidden" name="job_type[]" value="Internship">', $html);
        $this->assertStringContainsString('<input type="hidden" name="salary_min" value="500000">', $html);
        $this->assertStringContainsString('Bidang: Pemberdayaan UMKM', $html);
        $this->assertStringContainsString('Lokasi: Bandung', $html);
        $this->assertMatchesRegularExpression('/<option value="oldest" selected>Terlama<\/option>/', $html);
        foreach (['name="search"', 'name="category"', 'name="sort"'] as $field) {
            $this->assertStringContainsString($field, $html);
        }
    }

    public function test_empty_states_for_no_jobs_no_match_and_out_of_range_page(): void
    {
        $html = $this->main($this->page('/student/jobs'));
        $this->assertStringContainsString('Belum ada lowongan yang dibuka', $html);
        $this->assertStringContainsString('/student/browse-problems', $html);
        $this->assertStringContainsString('/student/jobs/alerts', $html);
        $this->assertStringNotContainsString('<table', $html);

        $this->job('Lowongan A');
        $html = $this->main($this->page('/student/jobs?category=999'));
        $this->assertStringContainsString('Tidak ada lowongan yang cocok', $html);
        $this->assertStringContainsString('Hapus semua filter', $html);

        $html = $this->main($this->page('/student/jobs?page=99'));
        $this->assertStringContainsString('Halaman ini kosong', $html);
        $this->assertStringContainsString('Ke halaman 1', $html);
    }

    public function test_saved_page_uses_same_rows_and_sprite_icons_with_specific_empty_state(): void
    {
        $html = $this->page('/student/jobs/saved');
        $this->assertNoLegacyChrome($html);
        $main = $this->main($html);
        $this->assertStringContainsString('Belum ada lowongan tersimpan', $main);
        $this->assertStringContainsString('no-saved-projects.svg', $main);
        $this->assertMatchesRegularExpression('/href="[^"]*\/student\/jobs"[^>]*>\s*Cari Lowongan/', $main);
        // tombol di dalam .sg-empty tanpa ikon: aturan bersama .sg-empty svg { width: 10rem } akan membesarkannya
        $this->assertDoesNotMatchRegularExpression('/class="sg-empty[^"]*"[\s\S]*sg-btn[^>]*>\s*<svg/', $main);

        $id = $this->job('Fasilitator Posyandu');
        DB::table('saved_jobs')->insert(['user_id' => $this->student->user_id, 'job_posting_id' => $id, 'folder' => 'Prioritas', 'notes' => 'Tanya dosen', 'created_at' => now()]);

        $html = $this->page('/student/jobs/saved');
        $this->assertNoLegacyChrome($html);
        $row = $this->row($html, $id);
        $this->assertStringContainsString('SDG 13 · Penanganan perubahan iklim', $row);
        $this->assertStringContainsString('Prioritas', $row);
        $this->assertStringContainsString('Catatan: Tanya dosen', $row);
        $this->assertStringContainsString('removeSaved('.$id, $row);
        $this->assertStringContainsString('sprite.svg#trash-2', $row);
        $this->assertStringContainsString('<b>1</b> lowongan disimpan', $this->main($html));
    }

    // simpan lalu hapus lewat POST toggle-save yang sama. dulu simpan selalu 500 karena controller mengisi saved_at,
    // kolom yang tidak ada di saved_jobs; waktu simpan kini created_at
    public function test_save_and_remove_use_unchanged_toggle_endpoint(): void
    {
        $id = $this->job('Fasilitator Posyandu');

        $this->actingAs($this->student->user)->postJson('/student/jobs/'.$id.'/toggle-save')->assertOk()->assertJson(['action' => 'saved']);
        $row = DB::table('saved_jobs')->where('user_id', $this->student->user_id)->where('job_posting_id', $id)->first();
        $this->assertNotNull($row);
        $this->assertNotNull($row->created_at);
        $this->assertStringContainsString('<tr data-job-id="'.$id.'"', $this->page('/student/jobs/saved'));

        $this->actingAs($this->student->user)->postJson('/student/jobs/'.$id.'/toggle-save')->assertOk()->assertJson(['action' => 'unsaved']);
        $this->assertDatabaseMissing('saved_jobs', ['user_id' => $this->student->user_id, 'job_posting_id' => $id]);
        $this->assertStringContainsString('Belum ada lowongan tersimpan', $this->main($this->page('/student/jobs/saved')));
    }

    // halaman banding dulu 500: count() dipanggil pada benefits, kolom teks per baris ("- item")
    public function test_compare_page_lists_text_benefits_as_items(): void
    {
        $a = $this->job('Fasilitator Posyandu', ['benefits' => "- Sertifikat KKN digital\n- Pengalaman lapangan nyata"]);
        $b = $this->job('Pendamping UMKM', ['benefits' => null]);

        $html = $this->page('/student/jobs/compare?ids='.$a.','.$b);
        $this->assertMatchesRegularExpression('/<\/svg>\s*Sertifikat KKN digital\s*<\/li>/', $html);
        $this->assertStringContainsString('Pengalaman lapangan nyata', $html);
        $this->assertStringNotContainsString('- Sertifikat', $html);
    }

    public function test_alerts_page_lists_criteria_with_switch_and_specific_empty_state(): void
    {
        $html = $this->page('/student/jobs/alerts');
        $this->assertNoLegacyChrome($html);
        $main = $this->main($html);
        $this->assertStringContainsString('Notifikasi Lowongan', $main);
        $this->assertStringContainsString('Belum ada notifikasi lowongan', $main);
        $this->assertStringContainsString('no-notifications.svg', $main);
        $this->assertStringContainsString('data-sg-open="alert-create"', $main);

        $this->actingAs($this->student->user)->postJson('/student/jobs/alerts/store', [
            'name' => 'Magang web Bandung', 'keywords' => 'laravel', 'job_types' => ['internship'],
            'locations' => ['Bandung', 'Garut'], 'frequency' => 'weekly',
        ])->assertOk()->assertJson(['success' => true]);
        $alertId = (int) DB::table('job_alerts')->value('id');

        $main = $this->main($this->page('/student/jobs/alerts'));
        $this->assertStringContainsString('data-alert-id="'.$alertId.'"', $main);
        $this->assertStringContainsString('Magang web Bandung', $main);
        $this->assertStringContainsString('laravel', $main);
        $this->assertStringContainsString('Bandung, Garut', $main);
        $this->assertStringContainsString('Mingguan', $main);
        $this->assertMatchesRegularExpression('/<input type="checkbox" role="switch" id="alert-active-'.$alertId.'" checked/', $main);
        $this->assertStringContainsString('<label class="sg-check py-1" for="alert-active-'.$alertId.'">', $main);
        $this->assertStringNotContainsString('Ubah', $main, 'tidak ada endpoint ubah notifikasi');
    }

    public function test_alert_toggle_and_delete_endpoints_are_unchanged(): void
    {
        $id = DB::table('job_alerts')->insertGetId([
            'user_id' => $this->student->user_id, 'name' => 'Kontrak Garut', 'keywords' => null, 'job_types' => '[]',
            'locations' => '[]', 'skills' => '[]', 'frequency' => 'daily', 'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->actingAs($this->student->user)->postJson('/student/jobs/alerts/'.$id.'/toggle')->assertOk()->assertJson(['is_active' => false]);
        $this->assertMatchesRegularExpression('/id="alert-active-'.$id.'"\s*>|id="alert-active-'.$id.'"\s+@change/', $this->main($this->page('/student/jobs/alerts')));
        $this->actingAs($this->student->user)->deleteJson('/student/jobs/alerts/'.$id)->assertOk();
        $this->assertDatabaseMissing('job_alerts', ['id' => $id]);
    }
}
