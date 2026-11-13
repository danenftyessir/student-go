<?php

namespace Tests\Feature\UiAudit;

use Tests\TestCase;

/**
 * audit ui/ux F-23: label jenis dan gaji lowongan dari satu helper (daftar, detail, bandingkan).
 */
class HelperLowonganTest extends TestCase
{
    public function test_job_type_label_maps_mixed_case_values(): void
    {
        $this->assertSame('Magang', job_type_label('Internship'));
        $this->assertSame('Magang', job_type_label('internship'));
        $this->assertSame('Kontrak', job_type_label('Contract'));
        $this->assertSame('Penuh waktu', job_type_label('full_time'));
        $this->assertSame('Relawan', job_type_label('Relawan'));
        $this->assertNull(job_type_label(null));
    }

    public function test_job_salary_label_formats_rupiah_in_millions(): void
    {
        $job = fn ($min, $max, $cur = 'IDR') => (object) ['salary_min' => $min, 'salary_max' => $max, 'salary_currency' => $cur];

        $this->assertSame('Rp 1–2 juta', job_salary_label($job('1000000.00', '2000000.00')));
        $this->assertSame('Rp 1,5 juta', job_salary_label($job(1500000, null)));
        $this->assertSame('USD 500–1.000', job_salary_label($job(500, 1000, 'USD')));
        $this->assertNull(job_salary_label($job(null, null)));
    }
}
