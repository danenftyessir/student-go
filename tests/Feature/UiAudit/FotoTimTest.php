<?php

namespace Tests\Feature\UiAudit;

use App\Support\TeamMembers;
use Tests\TestCase;

/**
 * audit ui/ux tahap 5 (F-15): foto tim dikirim sebagai webp 480x600 transparan, png asli tetap menjadi fallback.
 */
class FotoTimTest extends TestCase
{
    public function test_about_serves_small_transparent_webp_with_png_fallback(): void
    {
        $html = $this->get('/about')->assertOk()->getContent();

        foreach (TeamMembers::all() as $member) {
            $webp = public_path($member['photo_webp']);
            $this->assertFileExists($webp);
            $this->assertLessThan(40_000, filesize($webp), $member['photo_webp']);

            $image = imagecreatefromwebp($webp);
            $this->assertSame([480, 600], [imagesx($image), imagesy($image)]);
            $this->assertSame(127, imagecolorsforindex($image, imagecolorat($image, 2, 2))['alpha'], 'latar transparan');

            $this->assertStringContainsString('<source srcset="'.asset($member['photo_webp']).'" type="image/webp">', $html);
            $this->assertStringContainsString('<img src="'.asset($member['photo']).'" alt="Foto '.$member['name'].'"', $html);
        }
    }
}
