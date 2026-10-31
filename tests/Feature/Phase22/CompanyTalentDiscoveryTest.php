<?php

namespace Tests\Feature\Phase22;

use App\Models\Company;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * phase 22: direktori talenta (filter di server, satu pager, jumlah jujur + aturan pool), talenta tersimpan
 * (folder = category, catatan = notes, tanpa kontrol palsu), bandingkan dan ekspor tetap.
 * kriteria L: satu pager; sdg bahasa indonesia; jumlah + alasan pool; tanpa 'Indonesia' sebagai lokasi;
 * cta kosong tombol bersama; header csv sama. privasi: tanpa email/whatsapp mahasiswa di html (D-04).
 */
class CompanyTalentDiscoveryTest extends TestCase
{
    use BuildsPhase1Data;
    use RefreshDatabase;

    private Company $company;
    private int $institutionId;
    private int $problemId;
    private int $otherUniversityId;
    private int $otherProvinceId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRegion();
        $this->company = $this->makeCompany();
        $this->institutionId = $this->makeInstitution()->id;
        $this->problemId = $this->makeOpenProblems($this->institutionId, 1)[0];
        DB::table('problems')->where('id', $this->problemId)->update(['sdg_categories' => json_encode([1, 4])]);

        $this->otherProvinceId = DB::table('provinces')->insertGetId(['name' => 'Jawa Timur', 'code' => '35']);
        $regency = DB::table('regencies')->insertGetId(['province_id' => $this->otherProvinceId, 'name' => 'Kota Surabaya', 'code' => '3578']);
        $this->otherUniversityId = DB::table('universities')->insertGetId([
            'name' => 'Institut Teknologi Sepuluh Nopember', 'code' => 'ITS',
            'province_id' => $this->otherProvinceId, 'regency_id' => $regency,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    /**
     * mahasiswa dalam pool (punya proyek); $completed = proyek selesai sehingga sdg masalahnya ikut tampil
     */
    private function poolStudent(string $first, string $last, array $studentAttrs = [], bool $completed = true, ?string $userName = null): Student
    {
        $student = $this->makeStudent($first, $last, $userName, $studentAttrs);
        $this->makeProject($student, $this->institutionId, $this->problemId);
        if ($completed) {
            DB::table('projects')->where('student_id', $student->id)->update(['status' => 'completed']);
        }

        return $student;
    }

    private function page(string $query = ''): string
    {
        return $this->actingAs($this->company->user)
            ->get('/company/talents' . ($query !== '' ? '?' . $query : ''))
            ->assertOk()
            ->getContent();
    }

    private function rowCount(string $html): int
    {
        return substr_count($html, 'data-talent-id=');
    }

    public function test_directory_has_one_pager_indonesian_sdg_and_real_campus_location(): void
    {
        for ($i = 1; $i <= 14; $i++) {
            $this->poolStudent('Talenta', 'Ke' . $i);
        }

        $html = $this->page();

        $this->assertSame(1, substr_count($html, '<h1'), 'tepat satu h1');
        $this->assertSame(12, $this->rowCount($html), '12 baris per halaman');
        $this->assertStringContainsString('aria-label="Halaman talenta"', $html, 'satu pager bernomor');
        $this->assertStringContainsString('Menampilkan 1–12 dari 14', $html);
        foreach (['Muat Lebih Banyak', 'loadMore', 'No Poverty', 'Quality Education', 'No Major', 'company1.jpg', '<style', 'impact_score', 'Grid'] as $gone) {
            $this->assertStringNotContainsString($gone, $html, "tidak boleh ada: {$gone}");
        }
        $this->assertStringContainsString('SDG 1 · Tanpa Kemiskinan', $html);
        $this->assertStringContainsString('SDG 4 · Pendidikan Berkualitas', $html);
        $this->assertStringContainsString('Kota Bandung, Jawa Barat', $html);
        $this->assertDoesNotMatchRegularExpression('/>\s*Indonesia\s*</', $html, "lokasi 'Indonesia' sebagai pengganti");

        $page2 = $this->page('page=2');
        $this->assertSame(2, $this->rowCount($page2));
    }

    public function test_count_states_matches_and_the_rule_that_limits_the_pool(): void
    {
        $this->poolStudent('Ratna', 'Kusumawati');
        $this->poolStudent('Budi', 'Santoso');
        $this->makeStudent('Tanpa', 'Proyek'); // bukan bagian pool

        $html = $this->page();
        $this->assertStringContainsString('2 mahasiswa', $html);
        $this->assertStringContainsString('Pool talenta: mahasiswa dengan minimal 1 proyek', $html);

        $filtered = $this->page('search=ratna');
        $this->assertStringContainsString('1 dari 2 mahasiswa cocok', $filtered);
        $this->assertStringContainsString('Pool talenta: mahasiswa dengan minimal 1 proyek', $filtered);
    }

    public function test_search_and_every_filter_run_on_the_server(): void
    {
        $ratna = $this->poolStudent('Ratna', 'Kusumawati', ['major' => 'Kesehatan Masyarakat']);
        $budi = $this->poolStudent('Budi', 'Santoso', ['university_id' => $this->otherUniversityId, 'major' => 'Teknik Sipil', 'skills' => ['Posyandu', 'Survei']]);
        $sinta = $this->poolStudent('Sinta', 'Lestari', [], false); // proyek belum selesai: tanpa sdg

        $ids = fn (string $query) => collect($this->actingAs($this->company->user)
            ->getJson('/company/talents?' . $query, ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->json('talents'))->pluck('id')->sort()->values()->all();

        $this->assertSame([$ratna->user_id], $ids('search=KUSUMA'), 'cari nama, tidak peka huruf besar');
        $this->assertSame([$budi->user_id], $ids('search=sipil'), 'cari jurusan');
        $this->assertSame([$budi->user_id], $ids('search=sepuluh'), 'cari universitas');
        $this->assertSame([$budi->user_id], $ids('university_id=' . $this->otherUniversityId));
        $this->assertSame([$ratna->user_id], $ids('major=' . urlencode('Kesehatan Masyarakat')));
        $this->assertSame([$budi->user_id], $ids('province_id=' . $this->otherProvinceId));
        $this->assertSame([$budi->user_id], $ids('location=surabaya'), 'parameter lama location = kota/provinsi kampus');
        $this->assertSame([$budi->user_id], $ids('skills[]=Posyandu'));
        $this->assertSame([], $ids('skills[]=Posyandu&skills[]=Mengajar'), 'semua keterampilan terpilih harus dimiliki');
        $this->assertSame(collect([$ratna->user_id, $budi->user_id])->sort()->values()->all(), $ids('sdg_alignment[]=4'));
        $this->assertNotContains($sinta->user_id, $ids('sdg_alignment[]=1'));
        $this->assertSame([], $ids('sdg_alignment[]=13'));
        $this->assertSame([], $ids('search=' . urlencode('%')), 'wildcard di-escape');
    }

    public function test_pager_links_keep_query_and_order_is_stable(): void
    {
        for ($i = 1; $i <= 14; $i++) {
            $this->poolStudent('Talenta', 'Ke' . $i);
        }

        $html = $this->page('search=talenta&sdg_alignment%5B%5D=1');
        $this->assertMatchesRegularExpression('/href="[^"]*page=2[^"]*"/', $html);
        preg_match('/href="([^"]*page=2[^"]*)"/', $html, $match);
        $link = html_entity_decode($match[1]);
        $this->assertStringContainsString('search=talenta', $link);
        $this->assertStringContainsString('sdg_alignment', $link);

        // dua halaman = seluruh hasil, tanpa duplikat (created_at sama, id sebagai tie-breaker)
        $seen = [];
        foreach ([1, 2] as $page) {
            preg_match_all('/data-talent-id="(\d+)"/', $this->page('search=talenta&page=' . $page), $rows);
            $seen = array_merge($seen, $rows[1]);
        }
        $this->assertCount(14, $seen);
        $this->assertCount(14, array_unique($seen));
    }

    public function test_save_state_comes_from_the_database(): void
    {
        $saved = $this->poolStudent('Ratna', 'Kusumawati');
        $this->poolStudent('Budi', 'Santoso');
        DB::table('saved_talents')->insert(['company_id' => $this->company->id, 'user_id' => $saved->user_id, 'saved_at' => now(), 'created_at' => now(), 'updated_at' => now()]);

        $html = $this->page();
        $this->assertSame(1, substr_count($html, 'aria-pressed="true"'), 'hanya talenta yang tersimpan');
        $this->assertStringNotContainsString('company_saved_talents', $html, 'status simpan tidak lagi dari localStorage');
    }

    public function test_names_link_to_the_same_record_and_contact_data_stays_private(): void
    {
        // J1: nama akun lama berbeda dari nama mahasiswa
        $student = $this->poolStudent('Ratna', 'Kusumawati', [], true, 'akun lama ratna');

        $html = $this->page();
        $this->assertStringContainsString('Ratna Kusumawati', $html);
        $this->assertStringNotContainsString('akun lama ratna', $html);
        $this->assertStringContainsString('href="' . route('profile.public', $student->user->username) . '"', $html);

        // D-04: perusahaan tidak menerima email atau nomor whatsapp mahasiswa
        $this->assertStringNotContainsString($student->user->email, $html);
        $this->assertStringNotContainsString($student->phone, $html);
    }

    public function test_empty_pool_and_no_match_states(): void
    {
        $html = $this->page();
        $this->assertStringContainsString('Belum ada talenta', $html);
        $this->assertStringContainsString('0 mahasiswa', $html);

        $this->poolStudent('Ratna', 'Kusumawati');
        $none = $this->page('search=zzz');
        $this->assertStringContainsString('Tidak ada mahasiswa yang cocok', $none);
        $this->assertStringContainsString('0 dari 1 mahasiswa cocok', $none);
        $this->assertStringContainsString('Atur ulang filter', $none);
    }

    public function test_saved_page_shows_folders_and_notes_without_fake_controls(): void
    {
        $a = $this->poolStudent('Ratna', 'Kusumawati');
        $b = $this->poolStudent('Budi', 'Santoso');
        $c = $this->makeStudent('Sinta', 'Lestari'); // tersimpan walau di luar pool: tetap tampil
        foreach ([[$a, 'Dihubungi', 'Kuat di penyuluhan.'], [$b, 'Dihubungi', null], [$c, null, 'Cek ulang jadwal.']] as [$s, $category, $notes]) {
            DB::table('saved_talents')->insert(['company_id' => $this->company->id, 'user_id' => $s->user_id, 'category' => $category, 'notes' => $notes, 'saved_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
        }

        $html = $this->actingAs($this->company->user)->get(route('company.talents.saved'))->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, '<h1'));
        $this->assertSame(3, substr_count($html, 'data-talent-id='));
        $this->assertStringContainsString('aria-label="Folder talenta tersimpan"', $html);
        $this->assertStringContainsString('Dihubungi', $html);
        $this->assertStringContainsString('Tanpa folder', $html);
        $this->assertStringContainsString('Kuat di penyuluhan.', $html);
        $this->assertStringContainsString('Cek ulang jadwal.', $html);
        $this->assertStringContainsString('Kota Bandung, Jawa Barat', $html);
        $this->assertStringContainsString('href="' . route('company.talents.export-saved') . '"', $html);
        foreach (['Folder Baru', 'Buat Folder', 'Pengingat', 'Reminder', 'Kirim Pesan', 'setRating', 'Uncategorized', 'No Major', 'company4.jpg', '<style', 'contactMessage', '/contact`'] as $gone) {
            $this->assertStringNotContainsString($gone, $html, "tidak boleh ada: {$gone}");
        }
        $this->assertStringNotContainsString($a->user->email, $html);
    }

    public function test_saved_empty_state_uses_the_shared_primary_button(): void
    {
        $html = $this->actingAs($this->company->user)->get(route('company.talents.saved'))->assertOk()->getContent();

        $this->assertStringContainsString('Belum ada talenta tersimpan', $html);
        $this->assertMatchesRegularExpression(
            '#<a href="' . preg_quote(route('company.talents.index'), '#') . '" class="sg-btn sg-btn--primary">#',
            $html
        );
    }

    public function test_compare_keeps_redirect_and_uses_display_names(): void
    {
        $this->actingAs($this->company->user)->get(route('company.talents.compare'))
            ->assertRedirect(route('company.talents.index'));

        $a = $this->poolStudent('Ratna', 'Kusumawati', [], true, 'akun lama ratna');
        $b = $this->poolStudent('Budi', 'Santoso');
        $html = $this->actingAs($this->company->user)
            ->get(route('company.talents.compare', ['ids' => $a->user_id . ',' . $b->user_id]))
            ->assertOk()->getContent();

        $this->assertStringContainsString('Ratna Kusumawati', $html);
        $this->assertStringNotContainsString('akun lama ratna', $html);
        $this->assertStringContainsString('href="' . route('profile.public', $a->user->username) . '"', $html);
        foreach (['No Major', 'No bio available', 'N/A', 'ui-avatars.com', 'Outfit'] as $gone) {
            $this->assertStringNotContainsString($gone, $html, "tidak boleh ada: {$gone}");
        }
    }

    public function test_csv_headers_are_unchanged(): void
    {
        $this->poolStudent('Ratna', 'Kusumawati');

        $talents = $this->actingAs($this->company->user)->get(route('company.talents.export'));
        $talents->assertOk();
        $this->assertSame('Name,Email,Title,Location,Skills,Verified,"Projects Count"', strtok($talents->streamedContent(), "\n"));

        $saved = $this->actingAs($this->company->user)->get(route('company.talents.export-saved'));
        $saved->assertOk();
        $this->assertSame('Name,Email,Title,Location,Category,Notes,"Saved At"', strtok($saved->streamedContent(), "\n"));
    }

    public function test_role_access_is_unchanged(): void
    {
        $this->get(route('company.talents.index'))->assertRedirect(route('login'));
        $this->get(route('company.talents.saved'))->assertRedirect(route('login'));

        $student = $this->makeStudent('Ratna', 'Kusumawati');
        $this->actingAs($student->user)->get(route('company.talents.index'))->assertRedirect();
        $this->actingAs($student->user)->get(route('company.talents.saved'))->assertRedirect();
    }
}
