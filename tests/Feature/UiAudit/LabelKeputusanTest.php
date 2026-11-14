<?php

namespace Tests\Feature\UiAudit;

use Tests\TestCase;

/**
 * audit ui/ux F-08: tanggal reviewed_at diberi label "Diputuskan" hanya bila lamaran diterima/ditolak.
 */
class LabelKeputusanTest extends TestCase
{
    public function test_label_follows_application_status(): void
    {
        $source = file_get_contents(resource_path('views/institution/applications/components/detail.blade.php'));

        $this->assertStringContainsString("in_array(\$application->status, ['accepted', 'rejected'], true) ? 'Diputuskan' : 'Ditinjau'", $source);
        $this->assertStringNotContainsString("'Diputuskan' => \$tanggal", $source);
    }
}
