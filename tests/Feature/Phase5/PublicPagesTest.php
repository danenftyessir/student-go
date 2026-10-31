<?php

namespace Tests\Feature\Phase5;

use App\Support\TeamMembers;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * phase 5: tentang kami (artikel, tanpa carousel dan angka tanpa sumber), kontak (satu layar, email tim,
 * faq) dan profil publik instansi (nama utuh, ringkasan tanpa nol, keadaan kosong jujur, data privat
 * tidak tampil). rename RN-03 s.d. RN-12 diperiksa pada teks, alt, aria-label, title dan placeholder.
 */
class PublicPagesTest extends TestCase
{
    use BuildsPhase1Data;
    use RefreshDatabase;

    private const LONG_NAME = 'Dinas Sosial Pemberdayaan Perempuan Dan Perlindungan Anak Kabupaten Sidoarjo';

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo('2026-10-31 09:00:00');
        $this->seedRegion();
    }

    private function text(string $html): string
    {
        return preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($html)));
    }

    /**
     * pola probe dari CONTENT_RENAME_INVENTORY.md bagian 7, pada teks dan atribut yang dibacakan
     */
    private function assertNoOldBrand(string $html): void
    {
        $this->assertDoesNotMatchRegularExpression('/karsa|kkn ?go|karya untuk bangsa/i', $this->text($html));
        preg_match_all('/\s(?:alt|aria-label|title|placeholder)="([^"]*)"/i', $html, $m);
        foreach ($m[1] as $value) {
            $this->assertDoesNotMatchRegularExpression('/karsa|kkn ?go|laravel/i', $value, $value);
        }
        preg_match('/<title>(.*?)<\/title>/s', $html, $title);
        $this->assertDoesNotMatchRegularExpression('/karsa|laravel/i', $title[1]);
    }

    public function test_about_is_a_scrolling_article_without_carousel_or_unsourced_figures(): void
    {
        $html = $this->get('/about')->assertOk()->getContent();
        $text = $this->text($html);

        $this->assertStringContainsString('<html lang="id"', $html);
        $this->assertStringContainsString('<title>Tentang Kami — Student Go</title>', $html);
        $this->assertSame(1, substr_count($html, '<h1'));
        $this->assertNoOldBrand($html);

        // carousel 7 slide dan skripnya hilang (J34)
        foreach (['carouselSlides', 'nextSlide(', 'goToSlide(', 'switchTab(', 'scrollTimelineLeft'] as $needle) {
            $this->assertStringNotContainsString($needle, $html, $needle);
        }
        // angka tanpa sumber (J9) dan klaim era karsa (RN-07, RN-08, RN-09) tidak tampil
        $absent = [
            '8,3 juta', '520.000', '520,000', '76%', '1,2 triliun', '75%', '94%', '540 miliar', '4.500 perguruan',
            'Dari Tahun Ke Tahun', 'usia ke-2', 'katalisator', 'mentransformasi ekosistem', 'revolusi mahasiswa',
            'About Us', 'How It Works', 'Real-Time Updates', 'Managed by Experts', 'Wide Reach',
        ];
        foreach ($absent as $needle) {
            $this->assertStringNotContainsStringIgnoringCase($needle, $text, $needle);
        }

        // foto lapangan sebagai figure berbingkai (turunan webp), caption, alt deskriptif; about.jpg tidak dipakai (RN-04)
        $this->assertSame(4, substr_count($html, '<figure class="sg-figure'));
        $files = [
            'kkn-itb-2025-bersama-warga-di-kebun-1600.webp', 'upacara-bendera-bersama-warga-800.webp',
            'kkn-itb-2025-dinding-cap-tangan-1600.webp', 'kkn-itb-2025-rompi-cap-tangan-1600.webp',
        ];
        foreach ($files as $file) {
            $this->assertStringContainsString('assets/photography/field/'.$file, $html);
            $this->assertFileExists(public_path('assets/photography/field/'.$file));
        }
        $this->assertStringContainsString('alt="Rompi hijau keabuan berbordir bunga dan tulisan KKN ITB 2025', $html);
        $this->assertStringNotContainsString('about.jpg', $html);
        // foto di tentang kami berbeda dari beranda (permintaan pemilik, 2026-10-31)
        foreach (['mahasiswa-about.jpeg', 'kkn-itb-2025-mahasiswa-jalan-desa', 'kkn-itb-2025-rombongan-hujan'] as $homepagePhoto) {
            $this->assertStringNotContainsString($homepagePhoto, $html, $homepagePhoto);
        }
        // cara kerja: garis waktu (ol), bukan angka langkah
        $this->assertSame(3, substr_count($html, '<ol class="sg-about__steps">'));

        // tim dari pemilik produk: nama + foto, tanpa peran karangan
        foreach (['M. Abizzar Gamadrian', 'Danendra Shafi Athallah', 'Kenzie Raffa Ardhana'] as $name) {
            $this->assertStringContainsString($name, $text);
            $this->assertStringContainsString('alt="Foto '.$name.'"', $html);
        }
        // foto tim: potongan transparan (latar dihapus), bukan latar hitam
        foreach (TeamMembers::all() as $member) {
            $image = imagecreatefrompng(public_path($member['photo']));
            $corner = imagecolorsforindex($image, imagecolorat($image, 2, 2));
            $this->assertSame(127, $corner['alpha'], $member['photo']);
        }
        $this->assertStringNotContainsString('Full Stack Developer', $text);
        // tidak ada klaim organisasi yang belum disetujui (D-23, D-37)
        $this->assertStringNotContainsString('dibangun oleh', $text);
        $this->assertStringContainsString(route('contact'), $html);
    }

    public function test_contact_lists_the_supplied_email_channels_and_faq_only(): void
    {
        $html = $this->get('/contact')->assertOk()->getContent();
        $text = $this->text($html);

        $this->assertStringContainsString('<title>Kontak — Student Go</title>', $html);
        $this->assertSame(1, substr_count($html, '<h1'));
        $this->assertNoOldBrand($html);

        // RN-11 (sentence case) dan RN-12
        $this->assertStringContainsString('Ingin berkolaborasi dengan Student Go? Hubungi kami di sini.', $text);
        $this->assertStringContainsString('mengenai platform Student Go dan layanan yang tersedia', $text);

        foreach (['13523155@std.stei.itb.ac.id', '13523136@std.stei.itb.ac.id', '18223127@std.stei.itb.ac.id'] as $email) {
            $this->assertStringContainsString('href="mailto:'.$email.'"', $html);
        }
        $this->assertSame(3, substr_count($html, 'href="mailto:'));
        // tanpa whatsapp, tanpa hero foto (RN-10: gambar dihapus), tanpa formulir pesan (D-26)
        $this->assertStringNotContainsString('wa.me', $html);
        $this->assertStringNotContainsStringIgnoringCase('whatsapp', $text);
        $this->assertStringNotContainsString('contact.jpg', $html);
        $this->assertStringNotContainsString('profile_1', $html);
        preg_match('/<main[^>]*>(.*?)<\/main>/s', $html, $main);
        $this->assertStringNotContainsString('<form', $main[1]);
        $this->assertSame(4, substr_count($html, '<details class="sg-faq__item"'));
    }

    public function test_institution_without_problems_shows_full_name_and_honest_empty_state(): void
    {
        $institution = $this->makeInstitution('Dinas Sosial');
        $institution->update(['name' => self::LONG_NAME, 'description' => 'Instansi sosial di Kota Bandung.']);

        $html = $this->get('/institution/'.$institution->id)->assertOk()->getContent();
        $text = $this->text($html);

        $this->assertSame(76, mb_strlen(self::LONG_NAME));
        $this->assertStringContainsString('<title>'.self::LONG_NAME.' — Profil Instansi — Student Go</title>', $html);
        $this->assertMatchesRegularExpression('/<h1 class="sg-org__name">\s*'.preg_quote(self::LONG_NAME, '/').'\s*<\/h1>/', $html);
        $this->assertSame(1, substr_count($html, '<h1'));
        $this->assertStringContainsString('Belum ada masalah yang dipublikasikan', $text);
        $this->assertStringContainsString('Instansi sosial di Kota Bandung.', $text);

        // tanpa kotak statistik nol, tanpa placeholder gambar, tanpa banner gradien
        foreach (['0 masalah', '0 proyek', 'Total Masalah', 'No Image', 'Proyek yang telah selesai'] as $needle) {
            $this->assertStringNotContainsString($needle, $text, $needle);
        }
        $this->assertStringNotContainsString('via.placeholder.com', $html);
        $this->assertStringNotContainsString('ui-avatars.com', $html);
        $this->assertStringNotContainsString('hero-gradient', $html);

        // data privat tetap tidak tampil
        $this->assertStringNotContainsString($institution->email, $html);
        $this->assertStringNotContainsString('0221234567', $html);
        $this->assertStringNotContainsString('Jl. Test', $text);
    }

    public function test_institution_lists_problems_and_completed_work_from_the_database(): void
    {
        $institution = $this->makeInstitution();
        $ids = $this->makeOpenProblems($institution->id, 2);
        DB::table('problems')->where('id', $ids[0])->update([
            'title' => 'Pemetaan Air Bersih Dusun', 'village' => 'Desa Sukamaju',
            'sdg_categories' => json_encode([6]), 'application_deadline' => '2026-11-10',
        ]);
        $sari = $this->makeStudent('Sari', 'Wulandari');
        $budi = $this->makeStudent('Budi', 'Santoso');
        $this->makeProject($sari, $institution->id, $ids[0]);
        $this->makeProject($budi, $institution->id, $ids[0]);
        DB::table('projects')->update(['status' => 'completed', 'title' => 'Pemetaan Air Bersih Dusun']);

        $html = $this->get('/institution/'.$institution->id)->assertOk()->getContent();
        $text = $this->text($html);

        // ringkasan dari query; nilai nol (proyek berjalan) tidak tampil
        $this->assertStringContainsString('2 masalah dibuka', $text);
        $this->assertStringContainsString('2 proyek mahasiswa selesai', $text);
        $this->assertStringNotContainsString('proyek mahasiswa berjalan', $text);

        // baris masalah: tenggat terdekat lebih dulu, tautan detail tetap, fakta pembeda
        $this->assertSame(2, substr_count($html, 'class="sg-org-problem"'));
        $this->assertLessThan(strpos($html, 'Masalah 2'), strpos($html, 'Pemetaan Air Bersih Dusun'));
        $this->assertStringContainsString(route('student.browse-problems.show', $ids[0]), $html);
        $this->assertStringContainsString('Desa Sukamaju, Kota Bandung, Jawa Barat', $text);
        $this->assertStringContainsString('SDG 6 · Air bersih dan sanitasi', $text);
        $this->assertStringContainsString('Dibuka', $text);
        $this->assertStringContainsString('10 Nov 2026', $text);
        $this->assertStringContainsString('Detail masalah dan lamaran tersedia setelah masuk sebagai mahasiswa.', $text);

        // proyek selesai dikelompokkan per masalah, tanpa nama mahasiswa
        $this->assertStringContainsString('Proyek yang telah selesai', $text);
        $this->assertSame(1, substr_count($html, 'class="sg-org__done-title"'));
        $this->assertStringContainsString('2 mahasiswa', $text);
        $this->assertStringNotContainsString('Sari', $text);
        $this->assertStringNotContainsString('Budi', $text);
    }

    public function test_institution_website_is_shown_only_for_http_links(): void
    {
        $institution = $this->makeInstitution();

        $institution->update(['website' => 'javascript:alert(1)']);
        $html = $this->get('/institution/'.$institution->id)->assertOk()->getContent();
        $this->assertStringNotContainsString('javascript:alert', $html);
        $this->assertStringNotContainsString('Situs web', $html);

        $institution->update(['website' => 'https://www.dinkes.example.go.id/']);
        $html = $this->get('/institution/'.$institution->id)->assertOk()->getContent();
        $this->assertStringContainsString('href="https://www.dinkes.example.go.id/"', $html);
        $this->assertStringContainsString('rel="noopener noreferrer"', $html);
        $this->assertStringContainsString('dinkes.example.go.id', $this->text($html));
    }

    public function test_institution_route_contract_is_unchanged(): void
    {
        $this->assertSame(url('/institution/5'), route('institution.public', 5));
        $this->get('/institution/999999')->assertNotFound();
        $this->get('/institution/abc')->assertNotFound();

        // tetap publik untuk peran yang masuk
        $institution = $this->makeInstitution();
        $student = $this->makeStudent('Rina', 'Lestari');
        $this->actingAs($student->user)->get('/institution/'.$institution->id)->assertOk();
    }
}
