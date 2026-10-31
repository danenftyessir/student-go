<?php

namespace Tests\Feature\Phase18;

use App\Models\Institution;
use App\Models\Problem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * phase 18: daftar masalah (tabel tujuh kolom), editor brief enam bagian (37 input create, nama tetap),
 * detail dan edit. kriteria L: kolom + kosakata status, inventaris input, galat dipetakan ke bagian,
 * kabupaten/kota mengikuti provinsi, angka diterima dari lamaran, akses peran tidak berubah.
 */
class InstitutionProblemsTest extends TestCase
{
    use BuildsPhase1Data;
    use RefreshDatabase;

    /** 37 input formulir create (tanpa _token), sama dengan baseline reports/phase18/before/metrics_before.json */
    private const CREATE_INPUTS = [
        'title' => 1, 'description' => 1, 'background' => 1, 'objectives' => 1, 'expected_outcomes' => 1,
        'province_id' => 1, 'regency_id' => 1, 'address' => 1, 'sdg_categories[]' => 17, 'required_students' => 1,
        'required_skills[]' => 1, 'required_majors[]' => 1, 'difficulty_level' => 1, 'deliverables[]' => 1,
        'start_date' => 1, 'end_date' => 1, 'application_deadline' => 1, 'duration_months' => 1,
        'facilities_provided[]' => 1, 'images[]' => 1, 'status' => 1,
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRegion();
    }

    private function problem(Institution $institution, array $attrs = []): int
    {
        $id = $this->makeOpenProblems($institution->id, 1)[0];
        if ($attrs) {
            DB::table('problems')->where('id', $id)->update($attrs);
        }

        return $id;
    }

    private function apply(int $problemId, string $status): void
    {
        $student = $this->makeStudent('Pelamar', 'Uji');
        DB::table('applications')->insert([
            'student_id' => $student->id, 'problem_id' => $problemId, 'motivation' => 'Motivasi',
            'status' => $status, 'applied_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    /** nama kontrol formulir (tanpa _token/_method, tanpa isi <template>) */
    private function formNames(string $html, string $formXpath): array
    {
        $dom = new \DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8"?>'.$html);
        $xpath = new \DOMXPath($dom);
        $names = [];
        foreach ($xpath->query($formXpath.'//*[self::input or self::select or self::textarea][@name][not(ancestor::template)]') as $el) {
            $name = $el->getAttribute('name');
            if (! in_array($name, ['_token', '_method'], true)) {
                $names[$name] = ($names[$name] ?? 0) + 1;
            }
        }

        return $names;
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Pendataan Posyandu Desa Sukamaju',
            'description' => 'Data balita belum terdigitalisasi.',
            'background' => 'Pencatatan masih manual.',
            'objectives' => 'Membangun basis data posyandu.',
            'expected_outcomes' => 'Data balita dapat dicari.',
            'province_id' => $this->provinceId,
            'regency_id' => $this->regencyId,
            'address' => '  Jl. Raya Desa No. 12  ',
            'sdg_categories' => ['3', '4'],
            'required_students' => 4,
            'required_skills' => ['Komunikasi', '', 'Pengolahan data'],
            'required_majors' => ['Kesehatan Masyarakat'],
            'difficulty_level' => 'beginner',
            'deliverables' => ['Laporan akhir', ''],
            'facilities_provided' => ['Akomodasi'],
            'application_deadline' => now()->addDays(10)->toDateString(),
            'start_date' => now()->addDays(20)->toDateString(),
            'end_date' => now()->addDays(80)->toDateString(),
            'duration_months' => 2,
            'status' => 'draft',
        ], $overrides);
    }

    public function test_list_is_a_table_with_seven_columns_and_shared_status_words(): void
    {
        $institution = $this->makeInstitution();
        $open = $this->problem($institution, ['title' => 'Sanitasi Air Bersih']);
        $this->problem($institution, ['title' => 'Literasi Digital Draf', 'status' => 'draft']);
        $this->apply($open, 'accepted');
        $this->apply($open, 'pending');
        DB::table('problems')->where('id', $open)->update(['applications_count' => 2, 'accepted_students' => 0]);

        $html = $this->actingAs($institution->user)->get(route('institution.problems.index'))->assertOk()->getContent();

        preg_match_all('/<th scope="col"[^>]*>([^<]+)<\/th>/', $html, $m);
        $this->assertSame(['Judul', 'Status', 'Pelamar', 'Kuota', 'Tenggat', 'Diperbarui', 'Aksi'], array_map('trim', $m[1]));
        $this->assertStringContainsString('Dibuka', $html);
        $this->assertStringContainsString('Draf', $html);
        $this->assertStringNotContainsString('>open<', $html);
        // diterima dihitung dari lamaran, bukan dari counter accepted_students (0)
        $this->assertStringContainsString('1 diterima', $html);
        $this->assertStringContainsString(route('institution.applications.index', ['problem_id' => $open]), $html);
        $this->assertStringContainsString(route('institution.problems.create'), $html);
    }

    public function test_filters_keep_their_names_and_most_applied_sorts_by_applications(): void
    {
        $institution = $this->makeInstitution();
        $this->problem($institution, ['title' => 'Sedikit Pelamar', 'applications_count' => 1]);
        $this->problem($institution, ['title' => 'Banyak Pelamar', 'applications_count' => 9]);
        $this->problem($institution, ['title' => 'Draf Posyandu', 'status' => 'draft', 'applications_count' => 0]);

        $html = $this->actingAs($institution->user)->get(route('institution.problems.index', ['sort' => 'most_applied']))->assertOk()->getContent();
        $this->assertLessThan(strpos($html, 'Sedikit Pelamar'), strpos($html, 'Banyak Pelamar'));
        $this->assertMatchesRegularExpression('/<option value="most_applied"\s+selected/', $html);
        foreach (['name="search"', 'name="status"', 'name="sort"'] as $field) {
            $this->assertStringContainsString($field, $html);
        }

        $html = $this->get(route('institution.problems.index', ['status' => 'draft']))->getContent();
        $this->assertStringContainsString('Draf Posyandu', $html);
        $this->assertStringNotContainsString('Banyak Pelamar', $html);

        $html = $this->get(route('institution.problems.index', ['search' => 'Sedikit']))->getContent();
        $this->assertStringContainsString('Sedikit Pelamar', $html);
        $this->assertStringNotContainsString('Banyak Pelamar', $html);
    }

    public function test_empty_and_no_match_states(): void
    {
        $empty = $this->makeInstitution();
        $html = $this->actingAs($empty->user)->get(route('institution.problems.index'))->assertOk()->getContent();
        $this->assertStringContainsString('Belum ada masalah', $html);
        $this->assertStringNotContainsString('<table', $html);

        $institution = $this->makeInstitution();
        $this->problem($institution);
        $html = $this->actingAs($institution->user)->get(route('institution.problems.index', ['search' => 'tidak-ada-xyz']))->assertOk()->getContent();
        $this->assertStringContainsString('Tidak ada masalah yang cocok', $html);
        $this->assertStringContainsString('no-search-results', $html);
    }

    public function test_create_form_keeps_all_37_inputs_and_six_sections(): void
    {
        $institution = $this->makeInstitution();
        $html = $this->actingAs($institution->user)->get(route('institution.problems.create'))->assertOk()->getContent();

        $names = $this->formNames($html, '//form[@id="problemForm"]');
        ksort($names);
        $expected = self::CREATE_INPUTS;
        ksort($expected);
        $this->assertSame($expected, $names);
        $this->assertSame(37, array_sum($names));

        $this->assertMatchesRegularExpression('/<form action="'.preg_quote(route('institution.problems.store'), '/').'" method="POST" enctype="multipart\/form-data" id="problemForm">/', $html);
        foreach (['informasi', 'lokasi', 'sasaran', 'kebutuhan', 'media', 'status'] as $key) {
            $this->assertStringContainsString('href="#bagian-'.$key.'"', $html);
            $this->assertStringContainsString('id="bagian-'.$key.'"', $html);
        }
        $this->assertStringNotContainsString('alert(', $html);
        $this->assertStringNotContainsString('via.placeholder.com', $html);
        $this->assertMatchesRegularExpression('/api\\\\?\/regencies\\\\?\/__ID__/', $html);
    }

    public function test_store_saves_every_field_and_address_becomes_detailed_location(): void
    {
        $institution = $this->makeInstitution();

        $response = $this->actingAs($institution->user)->post(route('institution.problems.store'), $this->validPayload());

        $problem = Problem::where('institution_id', $institution->id)->firstOrFail();
        $response->assertRedirect(route('institution.problems.show', $problem->id));
        $this->assertSame('Pendataan Posyandu Desa Sukamaju', $problem->title);
        $this->assertSame('Jl. Raya Desa No. 12', $problem->detailed_location);
        $this->assertSame('draft', $problem->status);
        $this->assertSame([3, 4], array_map('intval', (array) $problem->sdg_categories));
        $this->assertSame(['Komunikasi', 'Pengolahan data'], (array) $problem->required_skills);
        $this->assertSame(['Laporan akhir'], (array) $problem->deliverables);
        $this->assertSame(['Akomodasi'], (array) $problem->facilities_provided);
        $this->assertSame('beginner', $problem->difficulty_level);
        $this->assertSame(4, (int) $problem->required_students);

        $this->post(route('institution.problems.store'), $this->validPayload(['title' => 'Masalah Dibuka', 'status' => 'open', 'address' => '']));
        $open = Problem::where('title', 'Masalah Dibuka')->firstOrFail();
        $this->assertSame('open', $open->status);
        $this->assertNull($open->detailed_location);
    }

    public function test_validation_errors_are_mapped_to_their_sections_and_regencies_survive(): void
    {
        $institution = $this->makeInstitution();
        $this->actingAs($institution->user);

        // keadaan setelah redirect galat: errors + old input di sesi (seperti withErrors()->withInput())
        $payload = $this->validPayload(['title' => '', 'sdg_categories' => []]);
        // session.serialization = json: bag galat disimpan sebagai array dan dibangun ulang saat sesi dimuat
        $errors = ['default' => ['format' => ':message', 'messages' => [
            'title' => ['Judul wajib diisi.'], 'sdg_categories' => ['Pilih minimal satu kategori SDG.'],
        ]]];
        $html = $this->withSession(['errors' => $errors, '_old_input' => $payload])
            ->get(route('institution.problems.create'))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/href="#bagian-informasi"[^>]*data-state="error"/', $html);
        $this->assertMatchesRegularExpression('/href="#bagian-sasaran"[^>]*data-state="error"/', $html);
        $this->assertMatchesRegularExpression('/href="#bagian-lokasi"[^>]*data-state="todo"/', $html);
        $this->assertStringContainsString('Judul wajib diisi.', $html);
        // provinsi lama ikut mengirim daftar kabupaten/kota, isian lama tetap ada
        $this->assertStringContainsString('Kota Bandung', $html);
        $this->assertStringContainsString('Data balita belum terdigitalisasi.', $html);

        // terakhir: aturan server tetap menolak. store() memanggil DB::rollBack() tanpa transaksi saat validasi gagal,
        // yang di test ikut membatalkan transaksi RefreshDatabase, jadi tidak ada request lain sesudahnya.
        $this->from(route('institution.problems.create'))
            ->post(route('institution.problems.store'), $payload)
            ->assertRedirect(route('institution.problems.create'))
            ->assertSessionHasErrors(['title', 'sdg_categories']);
    }

    public function test_detail_shows_real_accepted_count_and_a_confirmed_delete_form(): void
    {
        $institution = $this->makeInstitution();
        $id = $this->problem($institution, ['title' => 'Sanitasi Air Bersih', 'required_students' => 5, 'accepted_students' => 0, 'applications_count' => 3]);
        $this->apply($id, 'accepted');
        $this->apply($id, 'accepted');
        $this->apply($id, 'rejected');

        $html = $this->actingAs($institution->user)->get(route('institution.problems.show', $id))->assertOk()->getContent();
        $this->assertStringContainsString('5 mahasiswa · 2 diterima', $html);
        $this->assertStringContainsString('3 pelamar', $html);
        $this->assertMatchesRegularExpression('/<form action="'.preg_quote(route('institution.problems.destroy', $id), '/').'" method="POST"\s+data-sg-confirm=/', $html);
        $this->assertStringContainsString('name="_method" value="DELETE"', $html);
        $this->assertStringContainsString(route('institution.problems.edit', $id), $html);
    }

    public function test_edit_form_keeps_old_fields_and_loads_regencies_from_the_api(): void
    {
        $institution = $this->makeInstitution();
        $id = $this->problem($institution, [
            'scope' => 'Ruang lingkup', 'village' => 'Sukamaju', 'detailed_location' => 'Balai desa',
            'required_skills' => json_encode(['Komunikasi', 'Survei']), 'deliverables' => json_encode(['Laporan']),
        ]);
        DB::table('problem_images')->insert(['problem_id' => $id, 'image_path' => 'problems/x.jpg', 'order' => 1, 'is_cover' => true, 'created_at' => now(), 'updated_at' => now()]);

        $html = $this->actingAs($institution->user)->get(route('institution.problems.edit', $id))->assertOk()->getContent();
        $names = $this->formNames($html, '//form[@enctype="multipart/form-data"]');

        foreach (['scope', 'village', 'detailed_location', 'delete_images[]', 'images[]', 'status'] as $name) {
            $this->assertArrayHasKey($name, $names);
        }
        $this->assertArrayNotHasKey('address', $names);
        $this->assertSame(2, $names['required_skills[]']);
        $this->assertSame(17, $names['sdg_categories[]']);
        $this->assertStringContainsString('name="_method" value="PUT"', $html);
        $this->assertStringContainsString('id="new-images-input"', $html);
        $this->assertMatchesRegularExpression('/api\\\\?\/regencies\\\\?\/__ID__/', $html);
        $this->assertStringNotContainsString('institution/problems/regencies', $html);
        $this->assertStringContainsString('<option value="closed"', $html);
        // checkbox hapus foto terlihat (bukan hidden) sehingga bisa dijangkau keyboard
        $this->assertDoesNotMatchRegularExpression('/name="delete_images\[\]"[^>]*class="[^"]*hidden/', $html);
    }

    public function test_role_access_and_ownership_are_unchanged(): void
    {
        $owner = $this->makeInstitution();
        $id = $this->problem($owner);
        $other = $this->makeInstitution();

        $this->get(route('institution.problems.index'))->assertRedirect(route('login'));
        $this->actingAs($this->makeStudent('Sari', 'Wulandari')->user)->get(route('institution.problems.index'))->assertRedirect();
        $this->actingAs($this->makeCompany()->user)->get(route('institution.problems.create'))->assertRedirect();

        $this->actingAs($other->user);
        $this->get(route('institution.problems.show', $id))->assertNotFound();
        $this->get(route('institution.problems.edit', $id))->assertNotFound();
        $this->delete(route('institution.problems.destroy', $id))->assertNotFound();
        $this->assertDatabaseHas('problems', ['id' => $id]);
    }
}
