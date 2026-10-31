<?php

namespace Tests\Feature\Phase25;

use App\Models\Company;
use App\Services\SupabaseStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * phase 25: profil perusahaan = profil organisasi standar (tanpa foto hero, tanpa angka palsu); edit = form bersection
 * dengan field dan pilihan registrasi. nilai tersimpan selalu terisi (email, ukuran, industri; D-06) dan tidak kosong
 * setelah disimpan. kontrak form dan unggah logo tetap.
 */
class CompanyProfileTest extends TestCase
{
    use BuildsPhase1Data;
    use RefreshDatabase;

    // field yang dikirim form edit (semua sudah divalidasi ProfileController::update)
    private const EDIT_FIELDS = ['_method', '_token', 'address', 'city', 'company_size', 'description', 'email', 'facebook', 'founded_year',
        'industry', 'instagram', 'linkedin', 'location', 'logo', 'name', 'phone', 'postal_code', 'province_id', 'tagline', 'twitter', 'website'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRegion();
    }

    private function seededCompany(): Company
    {
        // bentuk data seeder: email perusahaan kosong, ukuran hanya di employee_count, industri di luar daftar registrasi
        return $this->makeCompany(['industry' => 'Koperasi & Agribisnis', 'employee_count' => '51-100', 'city' => 'Bandung',
            'founded_year' => 1997, 'description' => 'Koperasi petani.', 'verification_status' => 'verified']);
    }

    private function html(Company $company, string $url): string
    {
        return $this->actingAs($company->user)->get($url)->assertOk()->getContent();
    }

    private function optionValues(string $html, string $name): array
    {
        preg_match('/<select name="' . $name . '".*?<\/select>/s', $html, $select);
        preg_match_all('/<option value="([^"]*)"/', $select[0], $values);

        return array_values(array_filter($values[1], fn ($v) => $v !== ''));
    }

    public function test_profile_is_the_standard_organisation_profile(): void
    {
        $company = $this->seededCompany();
        $html = $this->html($company, route('company.profile.index'));

        $this->assertSame(1, substr_count($html, '<h1'));
        $this->assertStringContainsString('class="sg-container sg-org sg-co-profile"', $html);
        $this->assertStringNotContainsString('company-profile.jpg', $html);
        // angka yang tidak pernah dikirim controller dan tombol tanpa aksi sudah dihapus
        foreach (['Profil Dilihat', 'Talenta Tersimpan</span>', 'Ajukan Verifikasi'] as $gone) {
            $this->assertStringNotContainsString($gone, $html);
        }
        // tanpa logo: inisial, bukan gambar
        $this->assertMatchesRegularExpression('/<span class="sg-avatar sg-avatar--xl sg-avatar--square"[^>]*>\s*PT/s', $html);
        // fakta dan kontak (email jatuh balik ke email akun), info akun dan status verifikasi
        foreach (['Koperasi &amp; Agribisnis', '51-100 karyawan', '1997', 'Bandung', $company->user->email, 'Terverifikasi', 'Bergabung sejak',
                  'href="' . route('company.profile.edit') . '"', 'href="' . route('company.public', $company->id) . '"'] as $text) {
            $this->assertStringContainsString($text, $html);
        }
    }

    public function test_profile_shows_logo_only_when_one_is_stored(): void
    {
        $company = $this->makeCompany(['logo' => 'https://cdn.example.test/logo-pt.png']);
        $html = $this->html($company, route('company.profile.index'));

        $this->assertStringContainsString('<img src="https://cdn.example.test/logo-pt.png"', $html);
    }

    public function test_summary_shows_only_real_non_zero_counts(): void
    {
        $company = $this->seededCompany();
        DB::table('job_postings')->insert(['company_id' => $company->id, 'title' => 'Lowongan Uji', 'slug' => 'lowongan-uji',
            'status' => 'posted', 'published_at' => now()->subDay(), 'created_at' => now(), 'updated_at' => now()]);
        $html = $this->html($company, route('company.profile.index'));

        $this->assertMatchesRegularExpression('/<b>1<\/b> lowongan dibuka/', $html);
        $this->assertStringNotContainsString('pelamar</li>', $html);
        $this->assertStringNotContainsString('direkrut</li>', $html);
    }

    public function test_edit_form_contract_and_field_parity(): void
    {
        // halaman registrasi hanya untuk tamu: ambil sebelum masuk
        $register = $this->get(route('register.company'))->assertOk()->getContent();
        $company = $this->seededCompany();
        $html = $this->html($company, route('company.profile.edit'));

        $this->assertStringContainsString('action="' . route('company.profile.update') . '" enctype="multipart/form-data"', $html);
        $this->assertStringContainsString('name="_method" value="PUT"', $html);
        preg_match('/<form method="POST" action="' . preg_quote(route('company.profile.update'), '/') . '".*?<\/form>/s', $html, $form);
        preg_match_all('/\sname="([^"]+)"/', $form[0], $names);
        $this->assertSame(self::EDIT_FIELDS, collect($names[1])->unique()->sort()->values()->all());
        $this->assertStringNotContainsString('company-profile.jpg', $html);
        $this->assertStringNotContainsString('<style', $html);

        // pilihan standar = pilihan registrasi
        $this->assertSame($this->optionValues($register, 'company_size'), array_values(array_diff($this->optionValues($html, 'company_size'), ['51-100'])));
        $this->assertSame($this->optionValues($register, 'industry'), array_values(array_diff($this->optionValues($html, 'industry'), ['Koperasi &amp; Agribisnis'])));
    }

    public function test_every_stored_value_is_prefilled(): void
    {
        $company = $this->seededCompany();
        $company->update(['province_id' => DB::table('provinces')->value('id'), 'phone' => '0221234567', 'tagline' => 'Mitra tani']);
        $html = $this->html($company, route('company.profile.edit'));

        $this->assertStringContainsString('value="' . $company->user->email . '"', $html);
        $this->assertStringContainsString('<option value="51-100" selected>51-100 karyawan</option>', $html);
        $this->assertStringContainsString('<option value="Koperasi &amp; Agribisnis" selected>', $html);
        $this->assertMatchesRegularExpression('/<option value="' . $company->province_id . '"\s+selected/', $html);
        foreach (['value="1997"', 'value="Bandung"', 'value="0221234567"', 'value="Mitra tani"', 'Koperasi petani.</textarea>'] as $value) {
            $this->assertStringContainsString($value, $html);
        }
    }

    public function test_saving_the_prefilled_form_keeps_email_and_size(): void
    {
        $company = $this->seededCompany();

        // nilai yang dikirim form apa adanya (terisi dari data tersimpan)
        $this->actingAs($company->user)->put(route('company.profile.update'), [
            'name' => $company->name, 'industry' => 'Koperasi & Agribisnis', 'company_size' => '51-100',
            'email' => $company->user->email, 'founded_year' => 1997, 'city' => 'Bandung',
        ])->assertRedirect(route('company.profile.index'));

        $company->refresh();
        $this->assertSame($company->user->email, $company->email);
        $this->assertSame('51-100', $company->company_size);
        $this->assertSame('51-100', $company->employee_count);
        $this->assertSame('Koperasi & Agribisnis', $company->industry);

        $html = $this->html($company, route('company.profile.edit'));
        $this->assertStringContainsString('<option value="51-100" selected>', $html);
        $this->assertStringContainsString('value="' . $company->user->email . '"', $html);
    }

    public function test_logo_upload_keeps_field_name_and_storage_path(): void
    {
        $company = $this->seededCompany();
        $storage = $this->mock(SupabaseStorageService::class);
        $storage->shouldReceive('uploadCompanyLogo')->once()->andReturn('companies/logos/company-' . $company->id . '-logo.png');
        $storage->shouldReceive('getPublicUrl')->andReturnUsing(fn ($path) => 'https://storage.test/' . $path);

        $this->actingAs($company->user)->put(route('company.profile.update'), [
            'name' => $company->name, 'logo' => UploadedFile::fake()->image('logo.png', 120, 120),
        ])->assertRedirect(route('company.profile.index'));
        $this->assertSame('companies/logos/company-' . $company->id . '-logo.png', $company->fresh()->logo);

        // jenis berkas tetap divalidasi server (jpeg/png/jpg/webp)
        $this->actingAs($company->user)->from(route('company.profile.edit'))->put(route('company.profile.update'), [
            'name' => $company->name, 'logo' => UploadedFile::fake()->create('logo.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors('logo');

        $html = $this->html($company->fresh(), route('company.profile.edit'));
        $this->assertStringContainsString('accept="image/jpeg,image/jpg,image/png,image/webp"', $html);
        $this->assertStringContainsString('Hapus logo', $html);
    }
}
