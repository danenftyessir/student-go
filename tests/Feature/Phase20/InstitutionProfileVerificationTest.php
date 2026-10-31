<?php

namespace Tests\Feature\Phase20;

use App\Models\Institution;
use App\Models\VerificationDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * phase 20: profil instansi (I07), edit profil (I08), verifikasi dokumen (I09).
 * kriteria L: teks panjang tidak terpotong, nilai tersimpan terpilih, komponen unggah bersama tanpa input
 * berkas mentah, copy tanpa klaim metode/durasi (D-09), nama field sama dengan baseline (FORMS.md).
 */
class InstitutionProfileVerificationTest extends TestCase
{
    use BuildsPhase1Data;
    use RefreshDatabase;

    // 51 karakter (audit J19) dan 76 karakter (guardrail 10)
    private const LONG_EMAIL = 'dinaskesehatankabupatenbogor85@dinaskesehatan.go.id';
    private const LONG_NAME = 'Dinas Pemberdayaan Masyarakat dan Desa Kabupaten Kepulauan Seribu Utara Raya';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRegion();
    }

    private function longInstitution(string $type = 'Dinas Kesehatan'): Institution
    {
        $institution = $this->makeInstitution($type);
        $institution->user->update(['email' => self::LONG_EMAIL]);
        $institution->update([
            'name' => self::LONG_NAME,
            'website' => 'https://dinaskesehatan.bogorkab.go.id/profil/layanan-kesehatan-masyarakat',
            'description' => 'Melayani kesehatan masyarakat di 40 kecamatan.',
            'pic_name' => 'Ratna Sari', 'pic_position' => 'Kepala Bidang',
        ]);

        return $institution->fresh();
    }

    public function test_long_values_are_lengths_the_audit_names(): void
    {
        $this->assertSame(51, strlen(self::LONG_EMAIL));
        $this->assertSame(76, strlen(self::LONG_NAME));
    }

    public function test_profile_is_an_organisation_page_with_full_long_text_and_both_buttons(): void
    {
        $institution = $this->longInstitution();
        $html = $this->actingAs($institution->user)->get(route('institution.profile.index'))->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, '<h1'));
        $this->assertStringContainsString(self::LONG_NAME, $html);
        $this->assertStringContainsString(self::LONG_EMAIL, $html);
        $this->assertStringContainsString('sg-inst-facts', $html);
        $this->assertStringContainsString('href="' . route('institution.profile.edit') . '"', $html);
        $this->assertStringContainsString('href="' . route('institution.public', $institution->id) . '"', $html);
        $this->assertStringContainsString('Edit Profil', $html);
        $this->assertStringContainsString('Lihat Profil Publik', $html);
        $this->assertStringContainsString('Ratna Sari', $html);
        // tanpa tile statistik serba-nol dan tanpa style inline
        $this->assertStringNotContainsString('Total Masalah', $html);
        $this->assertDoesNotMatchRegularExpression('/<main[\s\S]*<style/', $html);
    }

    public function test_profile_shows_the_real_verification_status(): void
    {
        $institution = $this->longInstitution();
        $institution->update(['verification_status' => 'active']);
        $html = $this->actingAs($institution->user)->get(route('institution.profile.index'))->assertOk()->getContent();
        $this->assertStringContainsString('Terverifikasi', $html);

        $institution->update(['verification_status' => 'pending_verification']);
        $html = $this->actingAs($institution->user->fresh())->get(route('institution.profile.index'))->assertOk()->getContent();
        $this->assertStringContainsString('Menunggu Verifikasi', $html);
        $this->assertStringContainsString(route('institution.verification.upload'), $html);
    }

    public function test_edit_preselects_every_stored_value_and_keeps_field_names(): void
    {
        $institution = $this->longInstitution('Dinas Kesehatan');
        $html = $this->actingAs($institution->user)->get(route('institution.profile.edit'))->assertOk()->getContent();

        // jenis teks bebas terpilih, opsi standar tetap ada (D-06)
        $this->assertMatchesRegularExpression('/<option value="Dinas Kesehatan"\s+selected/', $html);
        $this->assertSame(1, substr_count($html, 'value="Dinas Kesehatan"'));
        $this->assertStringContainsString('value="pemerintah_desa"', $html);
        $this->assertMatchesRegularExpression('/<option value="' . $this->provinceId . '"\s+selected/', $html);
        $this->assertMatchesRegularExpression("/regency: ['\"]" . $this->regencyId . "['\"]/", $html);
        foreach ([self::LONG_NAME, self::LONG_EMAIL, '0221234567', 'Ratna Sari', 'Kepala Bidang', 'Jl. Test', 'Melayani kesehatan masyarakat'] as $value) {
            $this->assertStringContainsString($value, $html);
        }

        // kontrak baseline: action, PUT, multipart, 12 field, id yang dibaca JS
        $this->assertStringContainsString('action="' . route('institution.profile.update') . '"', $html);
        $this->assertStringContainsString('name="_method" value="PUT"', $html);
        $this->assertStringContainsString('enctype="multipart/form-data"', $html);
        foreach (['logo', 'name', 'type', 'email', 'province_id', 'regency_id', 'address', 'phone', 'website', 'pic_name', 'pic_position', 'description'] as $field) {
            $this->assertMatchesRegularExpression('/name="' . $field . '"/', $html, $field);
        }
        foreach (['logo-input', 'province-select', 'regency-select'] as $id) {
            $this->assertStringContainsString('id="' . $id . '"', $html);
        }
        $this->assertStringContainsString('accept="image/jpeg,image/jpg,image/png"', $html);
    }

    public function test_edit_selects_standard_type_without_extra_group(): void
    {
        $institution = $this->makeInstitution('puskesmas');
        $html = $this->actingAs($institution->user)->get(route('institution.profile.edit'))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<option value="puskesmas"\s+selected/', $html);
        $this->assertSame(1, substr_count($html, 'value="puskesmas"'));
        $this->assertStringNotContainsString('Tersimpan saat ini', $html);
    }

    public function test_validation_error_repopulates_old_input(): void
    {
        $institution = $this->longInstitution();
        $this->actingAs($institution->user)
            ->from(route('institution.profile.edit'))
            ->put(route('institution.profile.update'), [
                'name' => 'Nama Baru Instansi', 'type' => 'Dinas Kesehatan', 'email' => 'bukan-email',
                'province_id' => $this->provinceId, 'regency_id' => $this->regencyId,
                'address' => 'Alamat baru', 'phone' => '0811', 'pic_name' => 'PIC Baru', 'pic_position' => 'Staf',
            ])
            ->assertRedirect(route('institution.profile.edit'))
            // perilaku lama: controller menangkap ValidationException sebagai pesan 'error' umum (tidak diubah di phase ini)
            ->assertSessionHas('error');

        $html = $this->actingAs($institution->user)->get(route('institution.profile.edit'))->getContent();
        $this->assertStringContainsString('value="Nama Baru Instansi"', $html);
        $this->assertStringContainsString('value="bukan-email"', $html);
        $this->assertStringContainsString('Terjadi kesalahan saat memperbarui profil', $html);
        $this->assertMatchesRegularExpression('/<option value="Dinas Kesehatan"\s+selected/', $html);
    }

    public function test_update_still_saves_and_redirects_to_profile(): void
    {
        $institution = $this->longInstitution();
        $this->actingAs($institution->user)
            ->put(route('institution.profile.update'), [
                'name' => 'Puskesmas Sukamaju', 'type' => 'puskesmas', 'email' => self::LONG_EMAIL,
                'province_id' => $this->provinceId, 'regency_id' => $this->regencyId,
                'address' => 'Jl. Raya 1', 'phone' => '0811', 'pic_name' => 'PIC', 'pic_position' => 'Kepala',
            ])
            ->assertRedirect(route('institution.profile.index'))
            ->assertSessionHas('success');

        $this->assertSame('puskesmas', $institution->fresh()->type);
    }

    public function test_verification_page_uses_shared_upload_and_neutral_copy(): void
    {
        $institution = $this->longInstitution();
        $institution->update(['verification_status' => 'pending_verification']);
        $html = $this->actingAs($institution->user)->get(route('institution.verification.upload'))->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, '<h1'));
        $this->assertStringContainsString('action="' . route('institution.verification.upload.submit') . '"', $html);
        $this->assertStringContainsString('enctype="multipart/form-data"', $html);
        $accepts = ['official_letter' => 'application/pdf', 'logo' => 'image/jpeg,image/png', 'pic_identity' => 'image/jpeg,image/png', 'npwp' => 'application/pdf,image/jpeg,image/png'];
        foreach ($accepts as $name => $accept) {
            // input berkas asli tersembunyi di dalam dropzone bersama (tidak tampil mentah)
            $this->assertMatchesRegularExpression('/<input type="file" class="sr-only" name="' . $name . '" id="' . $name . '"[^>]*accept="' . preg_quote($accept, '/') . '"/', $html, $name);
        }
        $this->assertSame(4, substr_count($html, 'class="sg-dropzone"'));
        $this->assertSame(4, substr_count($html, 'Belum diunggah'));
        $this->assertStringContainsString('0 dari 4 dokumen diunggah', $html);
        // tanpa klaim AI/otomatis/durasi (D-09); tombol ajukan hanya bila ada dokumen
        $this->assertDoesNotMatchRegularExpression('/\bAI\b|otomatis|hari kerja/i', strip_tags($html));
        $this->assertStringNotContainsString(route('institution.verification.trigger'), $html);
    }

    public function test_checklist_shows_only_stored_per_document_results(): void
    {
        $institution = $this->longInstitution();
        $institution->update(['verification_status' => 'needs_review', 'verification_score' => 72.5]);
        VerificationDocument::create(['institution_id' => $institution->id, 'document_type' => 'official_letter', 'file_url' => 'x',
            'file_name' => 'sk-pengangkatan-kepala-dinas-kesehatan-kabupaten-bogor-2026-final.pdf', 'file_size' => 734003, 'mime_type' => 'application/pdf', 'ai_status' => 'approved']);
        VerificationDocument::create(['institution_id' => $institution->id, 'document_type' => 'pic_identity', 'file_url' => 'x',
            'file_name' => 'ktp.jpg', 'file_size' => 2048, 'mime_type' => 'image/jpeg']);

        $html = $this->actingAs($institution->user)->get(route('institution.verification.upload'))->assertOk()->getContent();

        $this->assertStringContainsString('2 dari 4 dokumen diunggah', $html);
        $this->assertStringContainsString('sk-pengangkatan-kepala-dinas-kesehatan-kabupaten-bogor-2026-final.pdf', $html);
        $this->assertStringContainsString('717 KB', $html);
        $this->assertStringContainsString('data-value="approved"', $html);
        $this->assertStringContainsString('Diunggah, belum diperiksa', $html);
        $this->assertSame(2, substr_count($html, 'Belum diunggah'));
        $this->assertStringContainsString('Perlu Ditinjau', $html);
        $this->assertStringContainsString('72,5/100', $html);
        // url berkas privat tidak ditautkan
        $this->assertStringNotContainsString('href="x"', $html);
        $this->assertStringContainsString('action="' . route('institution.verification.trigger') . '"', $html);
        $this->assertStringContainsString(route('institution.verification.status'), $html);
    }

    public function test_upload_keeps_field_names_types_and_storage_path(): void
    {
        Storage::fake('public');
        $institution = $this->longInstitution();

        $this->actingAs($institution->user)
            ->post(route('institution.verification.upload.submit'), [
                'official_letter' => UploadedFile::fake()->create('sk.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect(route('institution.verification.upload'))
            ->assertSessionHas('success');

        $doc = DB::table('verification_documents')->where('institution_id', $institution->id)->first();
        $this->assertSame('official_letter', $doc->document_type);
        $this->assertSame('sk.pdf', $doc->file_name);
        $this->assertCount(1, Storage::disk('public')->files("verification_documents/{$institution->id}"));

        // jenis berkas tetap ditolak server
        $this->actingAs($institution->user)
            ->post(route('institution.verification.upload.submit'), ['logo' => UploadedFile::fake()->create('logo.pdf', 10, 'application/pdf')])
            ->assertSessionHasErrors('logo');
    }

    public function test_other_roles_cannot_open_the_pages(): void
    {
        $student = $this->makeStudent('Sari', 'Wulandari');
        foreach (['institution.profile.index', 'institution.profile.edit', 'institution.verification.upload'] as $name) {
            $this->actingAs($student->user)->get(route($name))->assertRedirect();
        }
        auth()->logout();
        $this->get(route('institution.profile.index'))->assertRedirect(route('login'));
    }
}
