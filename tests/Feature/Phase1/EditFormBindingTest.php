<?php

namespace Tests\Feature\Phase1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * J5 / D-06: form edit menampilkan nilai yang tersimpan
 */
class EditFormBindingTest extends TestCase
{
    use RefreshDatabase;
    use BuildsPhase1Data;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRegion();
    }

    public function test_institution_type_free_text_value_is_shown_as_selected(): void
    {
        $institution = $this->makeInstitution('Dinas Kesehatan');

        $html = $this->actingAs($institution->user)->get('/institution/profile/edit')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<option value="Dinas Kesehatan"\s+selected/', $html);
        // opsi standar tetap ada
        $this->assertStringContainsString('value="pemerintah_desa"', $html);
    }

    public function test_institution_type_slug_value_is_selected_without_duplicate_option(): void
    {
        $institution = $this->makeInstitution('puskesmas');

        $html = $this->actingAs($institution->user)->get('/institution/profile/edit')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<option value="puskesmas"\s+selected/', $html);
        $this->assertSame(1, substr_count($html, 'value="puskesmas"'));
    }

    public function test_companies_table_has_the_columns_the_edit_form_writes(): void
    {
        foreach (['email', 'company_size', 'tagline', 'location', 'postal_code', 'linkedin', 'twitter', 'facebook', 'instagram'] as $column) {
            $this->assertTrue(Schema::hasColumn('companies', $column), "kolom companies.{$column} tidak ada");
        }
    }

    public function test_company_size_falls_back_to_stored_employee_count(): void
    {
        $company = $this->makeCompany(['employee_count' => '51-100']);

        $html = $this->actingAs($company->user)->get('/company/profile/edit')->assertOk()->getContent();

        // nilai tersimpan yang tidak ada di daftar opsi tetap tampil terpilih
        $this->assertMatchesRegularExpression('/<option value="51-100"\s+selected/', $html);
    }

    public function test_company_email_and_size_are_bound_and_saved(): void
    {
        $company = $this->makeCompany(['employee_count' => '11-20']);

        $this->actingAs($company->user)->put('/company/profile', [
            'name' => $company->name,
            'company_size' => '11-50',
            'email' => 'kontak@perusahaan.test',
            'tagline' => 'Tagline',
        ])->assertRedirect(route('company.profile.index'));

        $company->refresh();
        $this->assertSame('kontak@perusahaan.test', $company->email);
        $this->assertSame('11-50', $company->company_size);
        // data lama tidak diubah
        $this->assertSame('11-20', $company->employee_count);

        $html = $this->actingAs($company->user)->get('/company/profile/edit')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/<option value="11-50"\s+selected/', $html);
        $this->assertStringContainsString('value="kontak@perusahaan.test"', $html);
    }

    public function test_company_email_falls_back_to_account_email_when_empty(): void
    {
        $company = $this->makeCompany();

        $html = $this->actingAs($company->user)->get('/company/profile/edit')->assertOk()->getContent();

        $this->assertStringContainsString('value="' . $company->user->email . '"', $html);
    }
}
