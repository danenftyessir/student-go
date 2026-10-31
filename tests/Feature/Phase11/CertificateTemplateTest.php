<?php

namespace Tests\Feature\Phase11;

use App\Services\CertificateService;
use ReflectionMethod;
use Tests\TestCase;

/**
 * template sertifikat student go (2026-11-01): generator memuat template.png dan menulis teks
 * di dalam kanvas 2000x1414 tanpa database atau unggahan.
 */
class CertificateTemplateTest extends TestCase
{
    private function render(array $data): string
    {
        $method = new ReflectionMethod(CertificateService::class, 'createCertificateImage');

        return $method->invoke($this->app->make(CertificateService::class), $data);
    }

    public function test_generator_renders_on_the_new_template(): void
    {
        $this->assertFileExists(public_path('assets/certificate/template.png'));

        $path = $this->render([
            'no_sertifikat' => '007',
            'thn_sertifikat' => 2026,
            'nama_penerima' => 'Muhammad Abizzar Gamadrian Danendra Shafi Athallah Putra',
            'nim_penerima' => '1352204512',
            'thn_laksana' => '2026',
            'tgl_laksana' => '17 September - 30 November 2026',
            'kades' => 'Dr. H. Raden Mas Bambang Wicaksono Hadiningrat, S.Sos., M.Si.',
        ]);

        try {
            $this->assertFileExists($path);
            [$width, $height, $type] = getimagesize($path);
            $this->assertSame([2000, 1414, IMAGETYPE_PNG], [$width, $height, $type]);
            // hasil render berbeda dari template kosong (teks benar-benar ditulis)
            $this->assertNotSame(md5_file(public_path('assets/certificate/template.png')), md5_file($path));
        } finally {
            @unlink($path);
        }
    }
}
