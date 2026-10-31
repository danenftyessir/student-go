<?php

namespace Tests\Feature\Phase6;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * phase 6: satu template auth untuk masuk, pilih peran, tiga registrasi, lupa/reset password.
 * kontrak form dibandingkan dengan inventaris phase 0 (baseline/FORMS.md): action, method, enctype, name/id field.
 * copy: bahasa indonesia, tanpa nama lama, tanpa statistik karangan (J8, J9), tanpa contoh password (RN-17).
 */
class AuthPagesTest extends TestCase
{
    use BuildsPhase1Data;
    use RefreshDatabase;

    /** [route, judul tab, enctype, [name => id]] — sama dengan baseline phase 0 (+ remember, disetujui user) */
    private function contracts(): array
    {
        return [
            'login' => ['login', 'Masuk — Student Go', null, ['email' => 'email', 'password' => 'password', 'remember' => 'remember']],
            'register.student' => ['register.student.submit', 'Daftar sebagai Mahasiswa — Student Go', 'multipart/form-data', [
                'first_name' => 'first_name', 'last_name' => 'last_name', 'email' => 'email', 'whatsapp_number' => 'whatsapp_number',
                'profile_photo' => 'profile_photo', 'university_id' => 'university_id', 'major' => 'major', 'nim' => 'nim',
                'semester' => 'semester', 'username' => 'username', 'password' => 'password',
                'password_confirmation' => 'password_confirmation', 'terms' => 'terms',
            ]],
            'register.institution' => ['register.institution.submit', 'Daftar sebagai Instansi — Student Go', 'multipart/form-data', [
                'institution_name' => 'institution_name', 'institution_type' => 'institution_type', 'official_email' => 'official_email',
                'address' => 'address', 'province_id' => 'province_id', 'regency_id' => 'regency_id', 'pic_name' => 'pic_name',
                'pic_position' => 'pic_position', 'phone_number' => 'phone_number', 'logo' => 'logo',
                'verification_document' => 'verification_document', 'ktp' => 'ktp', 'npwp' => 'npwp', 'website' => 'website',
                'description' => 'description', 'username' => 'username', 'password' => 'password',
                'password_confirmation' => 'password_confirmation',
            ]],
            'register.company' => ['register.company.submit', 'Daftar sebagai Perusahaan — Student Go', 'multipart/form-data', [
                'company_name' => 'company_name', 'industry' => 'industry', 'company_size' => 'company_size',
                'founded_year' => 'founded_year', 'website' => 'website', 'description' => 'description', 'logo' => 'logo',
                'address' => 'address', 'city' => 'city', 'province_id' => 'province_id', 'email' => 'email', 'phone' => 'phone',
                'pic_name' => 'pic_name', 'pic_position' => 'pic_position', 'username' => 'username', 'password' => 'password',
                'password_confirmation' => 'password_confirmation',
            ]],
            'password.request' => ['password.email', 'Lupa Password — Student Go', null, ['email' => 'email']],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRegion();
    }

    private function text(string $html): string
    {
        $html = preg_replace('#<(script|style)\b[^>]*>.*?</\1>#si', '', $html);

        return trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($html))));
    }

    private function dom(string $html): \DOMXPath
    {
        $doc = new \DOMDocument;
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8"?>'.$html);
        libxml_clear_errors();

        return new \DOMXPath($doc);
    }

    public function test_every_auth_page_uses_the_one_template(): void
    {
        $pages = [route('login'), route('register'), route('register.student'), route('register.institution'), route('register.company'),
            route('password.request'), route('password.reset', ['token' => 'abc', 'email' => 'a@contoh.test'])];

        foreach ($pages as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $x = $this->dom($html);
            $this->assertSame('id', $x->query('//html')->item(0)->getAttribute('lang'), $url);
            $this->assertSame(1, $x->query('//h1')->length, "satu h1: $url");
            $this->assertSame(1, $x->query('//header[contains(@class,"sg-auth-header")]')->length, "template auth: $url");
            $this->assertSame(1, $x->query('//main[@id="main"]')->length, $url);
            $this->assertSame(1, $x->query('//a[@href="#main" and contains(@class,"sg-skip")]')->length, $url);
            $this->assertStringContainsString('assets/brand/student-go-logo.svg', $html, $url);
            // tidak ada <style> sebaris, cdn baru, atau aset lama
            $this->assertSame(0, $x->query('//body//style')->length, "tanpa <style> sebaris: $url");
            $this->assertStringNotContainsString('login-register-view.jpg', $html);
            $this->assertStringNotContainsString('register-background', $html);
            $this->assertSame(1, substr_count($html, 'cdn.jsdelivr.net/npm/alpinejs'), "alpine sekali: $url");
        }
    }

    public function test_form_contracts_match_the_phase_0_inventory(): void
    {
        foreach ($this->contracts() as $page => [$action, $title, $enctype, $fields]) {
            $html = $this->get(route($page))->assertOk()->getContent();
            $x = $this->dom($html);
            $this->assertSame($title, trim($x->query('//title')->item(0)->textContent), $page);

            $forms = $x->query('//form[@action="'.route($action).'"]');
            $this->assertSame(1, $forms->length, "satu form ke $action");
            $form = $forms->item(0);
            $this->assertSame('POST', strtoupper($form->getAttribute('method')), $page);
            $this->assertSame((string) $enctype, $form->getAttribute('enctype'), $page);
            $this->assertSame(1, $x->query('.//input[@name="_token"]', $form)->length, "csrf: $page");

            $found = [];
            foreach ($x->query('.//input|.//select|.//textarea', $form) as $el) {
                $name = $el->getAttribute('name');
                if ($name !== '' && $name !== '_token') {
                    $found[$name] = $el->getAttribute('id');
                }
            }
            ksort($found);
            ksort($fields);
            $this->assertSame($fields, $found, "field form $page");
        }

        $this->assertSame('studentRegisterForm', $this->dom($this->get(route('register.student'))->getContent())->query('//form[@enctype]')->item(0)->getAttribute('id'));
        $this->assertSame('institutionRegisterForm', $this->dom($this->get(route('register.institution'))->getContent())->query('//form[@enctype]')->item(0)->getAttribute('id'));
    }

    public function test_upload_fields_keep_their_accept_lists(): void
    {
        $accept = fn (string $page, string $name) => $this->dom($this->get(route($page))->getContent())
            ->query('//input[@name="'.$name.'"]')->item(0)->getAttribute('accept');

        $this->assertSame('image/*', $accept('register.student', 'profile_photo'));
        $this->assertSame('image/*', $accept('register.institution', 'logo'));
        $this->assertSame('application/pdf', $accept('register.institution', 'verification_document'));
        $this->assertSame('image/jpeg,image/jpg,image/png', $accept('register.institution', 'ktp'));
        $this->assertSame('image/jpeg,image/jpg,image/png', $accept('register.institution', 'npwp'));
        $this->assertSame('image/png,image/jpeg,image/jpg,image/webp', $accept('register.company', 'logo'));
    }

    public function test_copy_is_indonesian_without_invented_numbers_or_old_brand(): void
    {
        foreach (['login', 'register', 'register.student', 'register.institution', 'register.company', 'password.request'] as $page) {
            $html = $this->get(route($page))->getContent();
            $text = $this->text($html);
            $this->assertDoesNotMatchRegularExpression('/karya untuk bangsa/i', $text, $page);
            $this->assertDoesNotMatchRegularExpression('/welcome back|sign in|sign up|back to home|connect\. collaborate|active projects|\bcompany\b|\babout\b|\bcontact\b/i', $text, $page);
            $this->assertDoesNotMatchRegularExpression('/\d[\d.]*\+/', $text, "angka '+' karangan: $page");
        }

        $student = $this->text($this->get(route('register.student'))->getContent());
        $this->assertStringContainsString('Saya menyetujui syarat dan ketentuan serta kebijakan privasi Student Go.', $student);
        $this->assertStringNotContainsString('Contoh password', $student);
        $this->assertStringNotContainsString('Mahasiswa2026!', $student);
        // syarat password dijelaskan lengkap, termasuk angka (sebelumnya tidak disebut)
        foreach (['Minimal 8 karakter', 'Huruf besar dan huruf kecil', 'Setidaknya satu angka', 'Setidaknya satu simbol'] as $rule) {
            $this->assertStringContainsString($rule, $student);
        }
    }

    public function test_required_markers_password_toggle_and_no_error_on_first_render(): void
    {
        $html = $this->get(route('register.institution'))->getContent();
        $x = $this->dom($html);

        // tanda wajib pada label field wajib
        foreach (['institution_name', 'official_email', 'address', 'pic_name', 'phone_number', 'username', 'password'] as $id) {
            $this->assertSame(1, $x->query('//label[@for="'.$id.'" and @data-required]')->length, "penanda wajib $id");
        }
        foreach (['verification_document', 'ktp', 'npwp'] as $id) {
            $this->assertSame(1, $x->query('//span[@id="'.$id.'-label" and @data-required]')->length, "penanda wajib $id");
        }
        // tombol tampil/sembunyi password dengan nama aksesibel
        $toggles = $x->query('//button[contains(@class,"sg-password__toggle")]');
        $this->assertSame(2, $toggles->length);
        $this->assertSame('Tampilkan password', $toggles->item(0)->getAttribute('aria-label'));
        $this->assertSame('password', $toggles->item(0)->getAttribute('aria-controls'));
        // galat tidak tampil saat pertama dibuka
        $this->assertSame(0, $x->query('//*[@aria-invalid="true"]')->length);
        $this->assertSame(0, $x->query('//p[contains(@class,"sg-error") and not(@hidden) and not(@x-show)]')->length);
        // pilihan wilayah: status aria-live + url api yang sama
        $this->assertSame('polite', $x->query('//*[@id="regency_id-status"]')->item(0)->getAttribute('aria-live'));
        $this->assertStringContainsString(url('/api/public/regencies/__ID__'), str_replace('\/', '/', html_entity_decode($html)));
    }

    public function test_role_chooser_links_to_the_same_three_forms(): void
    {
        $x = $this->dom($this->get(route('register'))->getContent());
        $links = [];
        foreach ($x->query('//ul[contains(@class,"sg-role-list")]//a') as $a) {
            $links[] = $a->getAttribute('href');
        }
        $this->assertSame([route('register.student'), route('register.institution'), route('register.company')], $links);
        $this->assertGreaterThanOrEqual(1, $x->query('//a[@href="'.route('login').'"]')->length, 'tautan "Sudah punya akun? Masuk"');
    }

    public function test_flash_messages_render_as_alerts_on_auth_pages(): void
    {
        $html = $this->withSession(['success' => 'password berhasil direset. silakan login dengan password baru', 'error' => 'silakan login terlebih dahulu'])
            ->get(route('login'))->getContent();

        $this->assertMatchesRegularExpression('/data-tone="success"\s+role="status".*password berhasil direset/s', $html);
        $this->assertMatchesRegularExpression('/data-tone="danger"\s+role="alert".*silakan login terlebih dahulu/s', $html);
    }
}
