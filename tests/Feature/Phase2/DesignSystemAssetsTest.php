<?php

namespace Tests\Feature\Phase2;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

/**
 * phase 2: fondasi design system (aset lokal, sprite ikon, komponen <x-icon>,
 * wiring css bertahap D-29) dan aset sertifikat yang dilindungi (D-32)
 */
class DesignSystemAssetsTest extends TestCase
{
    public function test_brand_files_exist(): void
    {
        foreach ([
            'student-go-logo.svg', 'student-go-logo-on-dark.svg', 'student-go-symbol.svg',
            'student-go-symbol-on-dark.svg', 'favicon.svg', 'favicon.ico', 'apple-touch-icon.png',
        ] as $file) {
            $this->assertFileExists(public_path('assets/brand/'.$file));
        }
    }

    public function test_every_font_url_in_fonts_css_exists_as_woff2(): void
    {
        $css = file_get_contents(resource_path('css/student-go/fonts.css'));
        preg_match_all('#url\("(/assets/fonts/[^"]+)"\)#', $css, $m);

        $this->assertCount(3, $m[1]);
        foreach ($m[1] as $url) {
            $path = public_path(ltrim($url, '/'));
            $this->assertFileExists($path);
            $this->assertSame('wOF2', file_get_contents($path, false, null, 0, 4));
        }
    }

    public function test_every_icon_in_icons_json_is_in_the_sprite_at_1_5px(): void
    {
        $names = array_keys(json_decode(file_get_contents(public_path('assets/icons/ui/icons.json')), true));
        $sprite = file_get_contents(public_path('assets/icons/ui/sprite.svg'));

        $this->assertCount(71, $names);
        foreach ($names as $name) {
            $this->assertMatchesRegularExpression('#<symbol id="'.preg_quote($name, '#').'"[^>]*stroke-width="1.5"#', $sprite);
            $this->assertFileExists(public_path("assets/icons/ui/{$name}.svg"));
        }
    }

    public function test_icon_component_renders_decorative_and_labelled_icons(): void
    {
        $decorative = Blade::render('<x-icon name="map-pin" />');
        $this->assertStringContainsString('class="sg-icon"', $decorative);
        $this->assertStringContainsString('aria-hidden="true"', $decorative);
        $this->assertStringContainsString('assets/icons/ui/sprite.svg#map-pin', $decorative);

        $labelled = Blade::render('<x-icon name="bell" size="sm" label="Notifikasi" class="text-gray-500" />');
        $this->assertStringContainsString('class="sg-icon sg-icon--sm text-gray-500"', $labelled);
        $this->assertStringContainsString('role="img" aria-label="Notifikasi"', $labelled);
        $this->assertStringNotContainsString('aria-hidden', $labelled);

        $this->assertStringNotContainsString('sg-icon--xl', Blade::render('<x-icon name="x" size="xl" />'));
    }

    public function test_copied_svgs_are_well_formed_and_carry_no_c2pa_block(): void
    {
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(public_path('assets')));
        $count = 0;
        foreach ($files as $file) {
            if ($file->getExtension() !== 'svg') {
                continue;
            }
            $count++;
            $xml = file_get_contents($file->getPathname());
            $this->assertNotFalse(@simplexml_load_string($xml), $file->getPathname());
            $this->assertStringNotContainsString('c2pa', $xml, $file->getPathname());
        }
        $this->assertSame(96, $count);
    }

    public function test_app_css_imports_the_design_system_with_overrides_active(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertMatchesRegularExpression("#^@import 'tailwindcss';#", $css);
        foreach (['fonts', 'tokens', 'components'] as $file) {
            $this->assertMatchesRegularExpression("#^@import './student-go/{$file}.css';#m", $css);
        }
        // d-29: override global ditahan di phase 2 dan diaktifkan phase 3 bersama shell baru
        $this->assertMatchesRegularExpression("#^@import './student-go/legacy-overrides.css';#m", $css);
    }

    public function test_certificate_assets_are_unchanged(): void
    {
        $expected = [
            'assets/certificate/template.png' => '132d7559f93e4972bcb3ab098a8e008bbd5002e0516825c51850ef4834e4e59f',
            'assets/certificate/fonts/PinyonScript-Regular.ttf' => '0f816f50c0a25ed5e687570f2b43c16c1954969db6121095dcec2ad0745cdb0e',
            'assets/certificate/fonts/Poppins-Regular.ttf' => '707fdc5c8bab57a90061c6a8ed7b70d5ffb82fc810e994e79f90bace890c255a',
        ];
        foreach ($expected as $path => $sha) {
            $this->assertSame($sha, hash_file('sha256', public_path($path)), $path);
        }
    }
}
