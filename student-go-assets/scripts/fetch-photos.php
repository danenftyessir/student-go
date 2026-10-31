<?php
/**
 * Student Go — download and optimize the license-checked documentary photographs.
 *
 * Run from the Laravel project root (PHP is already there because Laravel needs it):
 *
 *     php scripts/fetch-photos.php                    # everything in docs/photo-catalog.json
 *     php scripts/fetch-photos.php --only=homepage    # a folder or an id prefix
 *     php scripts/fetch-photos.php --only=library/pesisir
 *     php scripts/fetch-photos.php --dry-run          # list what would be fetched
 *     php scripts/fetch-photos.php --force            # re-download existing files
 *
 * What it does, per photo
 *   1. Tries each URL in the catalog's "downloads" list (primary, then fallback).
 *   2. Refuses anything that is not a real image (a blocked page or an error page is never saved as a photo).
 *   3. Keeps the original in storage/app/asset-originals/ (add that folder to .gitignore).
 *   4. Writes an optimized WebP (longest side 1800 px, quality 80, EXIF stripped, orientation applied)
 *      to public/assets/photography/<folder>/<name>.webp
 *   5. Rewrites public/assets/photography/credits.json for every photo present on disk, so Blade can
 *      print the courtesy credit and link to the source page.
 *
 * WebP encoder, first one found: Imagick extension -> GD (imagewebp) -> `magick` / `convert` / `cwebp` command.
 * Safe to re-run: existing optimized files are skipped. Only URLs listed in the catalog are requested.
 * Every record in the catalog was opened on its own Unsplash/Pexels page and its license read there
 * (see docs/ASSET_SOURCES.md). The script does not search, crawl or follow links.
 */

if (PHP_SAPI !== 'cli') { fwrite(STDERR, "Run this from the command line.\n"); exit(1); }

$root = dirname(__DIR__);
$opt = ['only' => null, 'force' => false, 'dry-run' => false, 'max' => 1800, 'quality' => 80,
        'catalog' => $root . '/docs/photo-catalog.json', 'delay' => 400, 'cacert' => null, 'transport' => 'auto'];
foreach (array_slice($argv, 1) as $a) {
    if (!preg_match('/^--([a-z-]+)(?:=(.*))?$/', $a, $m)) { fwrite(STDERR, "Unknown argument: $a\n"); exit(1); }
    $k = $m[1];
    if (!array_key_exists($k, $opt)) { fwrite(STDERR, "Unknown option: --$k\n"); exit(1); }
    $opt[$k] = is_bool($opt[$k]) ? true : (is_int($opt[$k]) ? (int)($m[2] ?? 0) : ($m[2] ?? null));
}

$cat = json_decode((string)@file_get_contents($opt['catalog']), true);
if (!is_array($cat) || empty($cat['photos'])) { fwrite(STDERR, "Cannot read catalog: {$opt['catalog']}\n"); exit(1); }

$outRoot  = $root . '/public/assets/photography';
$origDir  = $root . '/storage/app/asset-originals';
$UA = 'StudentGo-asset-fetch/1.2 (local project setup; license-checked list)';

function say(string $s): void { fwrite(STDOUT, $s . "\n"); }

/* ---- Network -------------------------------------------------------------------------------------------
 * Two transports. "php" uses PHP's own curl extension, which trusts only the CA bundle named in php.ini
 * (curl.cainfo / openssl.cafile). If that setting is broken (typical on Windows: the path was copied from
 * another computer), every HTTPS request fails before it reaches the server. In "auto" mode the script
 * then switches to the operating system's own curl command, which uses the OS certificate store and needs
 * no php.ini setting (curl.exe ships with Windows 10 1803+ and 11). Certificate checking is never turned off.
 */
const SSL_SETUP_ERRNOS = [35, 51, 58, 60, 77, 83];   // curl errors meaning "HTTPS trust is not set up here"

function is_windows(): bool { return stripos(PHP_OS_FAMILY, 'Windows') === 0; }

function cli_curl(): ?string {
    static $path = false;
    if ($path !== false) return $path;
    $path = null;
    if (function_exists('exec')) {
        @exec(is_windows() ? 'where curl.exe 2>NUL' : 'command -v curl 2>/dev/null', $out, $rc);
        if ($rc === 0 && !empty($out[0])) $path = trim($out[0]);
    }
    return $path;
}

function ini_ca_problems(): array {
    $bad = [];
    foreach (['curl.cainfo', 'openssl.cafile'] as $k) {
        $v = (string)ini_get($k);
        if ($v !== '' && !is_file($v)) $bad[$k] = $v;
    }
    return $bad;
}

function explain_ssl(bool $continuing = false): void {
    say('');
    say($continuing
        ? '  PHP cannot verify HTTPS certificates on this computer. This run continues through the system curl, but fix it for Laravel and Composer too.'
        : '  PHP cannot verify HTTPS certificates on this computer, so no download will work until that is fixed.');
    say('  php.ini in use : ' . (php_ini_loaded_file() ?: '(none loaded)'));
    foreach (['curl.cainfo', 'openssl.cafile'] as $k) {
        $v = (string)ini_get($k);
        say(sprintf('  %-15s: %s', $k, $v === '' ? '(not set)' : $v . (is_file($v) ? '' : '   <-- this file does not exist here')));
    }
    say('  Fix: save https://curl.se/ca/cacert.pem to a folder on THIS computer and point both settings at it,');
    say('  or run once with   php scripts/fetch-photos.php --cacert="C:\\path\\to\\cacert.pem"');
    say('  Step by step: docs/INTEGRATION.md, section 7.');
    say('');
}

/** @return array [bytes|null, httpCode, errorText, curlErrno] */
function http_get_php(string $url, string $ua, ?string $cacert, int $timeout): array {
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        $o = [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_MAXREDIRS => 6,
            CURLOPT_CONNECTTIMEOUT => 20, CURLOPT_TIMEOUT => $timeout, CURLOPT_USERAGENT => $ua,
            CURLOPT_HTTPHEADER => ['Accept: image/jpeg,image/*;q=0.8']];
        if ($cacert) $o[CURLOPT_CAINFO] = $cacert;
        curl_setopt_array($ch, $o);
        $body = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $err = curl_error($ch); $errno = curl_errno($ch);
        curl_close($ch);
        return [$body === false ? null : $body, $code, $err, $errno];
    }
    $ctx = stream_context_create(['http' => ['method' => 'GET', 'timeout' => $timeout, 'follow_location' => 1,
        'max_redirects' => 6, 'ignore_errors' => true, 'header' => "User-Agent: $ua\r\nAccept: image/jpeg,image/*;q=0.8\r\n"],
        'ssl' => $cacert ? ['cafile' => $cacert] : []]);
    $body = @file_get_contents($url, false, $ctx);
    $code = 0;
    foreach (($http_response_header ?? []) as $h) { if (preg_match('#^HTTP/\S+\s+(\d{3})#', $h, $m)) $code = (int)$m[1]; }
    return [$body === false ? null : $body, $code, $body === false ? 'request failed (PHP curl extension not enabled; used PHP streams)' : '', 0];
}

function http_get_cli(string $curl, string $url, string $ua, ?string $cacert, int $timeout): array {
    $tmp = tempnam(sys_get_temp_dir(), 'sgph');
    $cmd = escapeshellarg($curl) . ' -L -f -sS --max-redirs 6 --connect-timeout 20 --max-time ' . $timeout
        . ' -A ' . escapeshellarg($ua) . ' -H ' . escapeshellarg('Accept: image/jpeg,image/*;q=0.8')
        . ($cacert ? ' --cacert ' . escapeshellarg($cacert) : '')
        . ' -o ' . escapeshellarg($tmp) . ' ' . escapeshellarg($url) . ' 2>&1';
    $out = [];
    @exec($cmd, $out, $rc);
    $body = ($rc === 0 && is_file($tmp)) ? file_get_contents($tmp) : null;
    @unlink($tmp);
    $msg = trim(implode(' ', $out));
    return [$body === false ? null : $body, $rc === 0 ? 200 : 0, $rc === 0 ? '' : ($msg ?: "curl exit code $rc"), $rc];
}

/** $net: ['transport' => auto|php|cli, 'cacert' => ?string, 'curl' => ?string, 'told' => bool] (updated in place) */
function http_get(string $url, string $ua, array &$net, int $timeout = 60): array {
    if ($net['transport'] !== 'cli') {
        [$body, $code, $err, $errno] = http_get_php($url, $ua, $net['cacert'], $timeout);
        $sslSetup = $body === null && (in_array($errno, SSL_SETUP_ERRNOS, true) || stripos($err, 'certificate') !== false);
        if (!$sslSetup) return [$body, $code, $err];
        $curl = $net['transport'] === 'auto' ? cli_curl() : null;
        if ($curl === null) {
            if (!$net['told']) { explain_ssl(); $net['told'] = true; }
            return [$body, $code, $err];
        }
        say("       note: PHP's HTTPS certificate setup failed ($err).");
        say("             Using the system curl instead for the rest of this run: $curl");
        $net['transport'] = 'cli'; $net['curl'] = $curl;
        if (!$net['told']) { explain_ssl(true); $net['told'] = true; }
    }
    [$body, $code, $err] = http_get_cli($net['curl'] ?? cli_curl() ?? 'curl', $url, $ua, $net['cacert'], $timeout);
    return [$body, $code, $err];
}

/** null when the bytes are a usable photo, otherwise a short reason. */
function image_problem(string $bytes): ?string {
    $info = @getimagesizefromstring($bytes);
    if (!$info || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) return 'not an image (blocked or error page?)';
    if (max($info[0], $info[1]) < 800) return "image is only {$info[0]}x{$info[1]} px (expected a full-size photo)";
    return null;
}

function to_webp(string $srcFile, string $dst, int $max, int $q): bool {
    @mkdir(dirname($dst), 0775, true);
    if (extension_loaded('imagick')) {
        try {
            $im = new Imagick($srcFile);
            if (method_exists($im, 'autoOrient')) { $im->autoOrient(); }
            $im->stripImage();
            $im->setImageFormat('webp');
            $im->setImageCompressionQuality($q);
            if (max($im->getImageWidth(), $im->getImageHeight()) > $max) { $im->thumbnailImage($max, $max, true); }
            $ok = $im->writeImage($dst);
            $im->clear();
            if ($ok && filesize($dst) > 0) return true;
        } catch (Throwable $e) { /* fall through */ }
    }
    if (function_exists('imagewebp') && function_exists('imagecreatefromstring')) {
        $bytes = file_get_contents($srcFile);
        $im = @imagecreatefromstring($bytes);
        if ($im !== false) {
            if (function_exists('exif_read_data') && ($info = @getimagesizefromstring($bytes)) && $info[2] === IMAGETYPE_JPEG) {
                $exif = @exif_read_data($srcFile);
                $o = $exif['Orientation'] ?? 1;
                $deg = [3 => 180, 6 => -90, 8 => 90][$o] ?? 0;
                if ($deg) { $rot = imagerotate($im, $deg, 0); if ($rot) { imagedestroy($im); $im = $rot; } }
            }
            $w = imagesx($im); $h = imagesy($im);
            if (max($w, $h) > $max) {
                $scale = $max / max($w, $h);
                $res = imagescale($im, (int)round($w * $scale), (int)round($h * $scale), IMG_BICUBIC);
                if ($res) { imagedestroy($im); $im = $res; }
            }
            imagepalettetotruecolor($im);
            $ok = imagewebp($im, $dst, $q);
            imagedestroy($im);
            if ($ok && filesize($dst) > 0) return true;
        }
    }
    // On Windows "convert" is a built-in disk tool, not ImageMagick, so only "magick" is tried there.
    $tools = is_windows() ? [['magick', 'magick']] : [['magick', 'magick'], ['convert', 'convert']];
    foreach ($tools as [$bin, $cmd]) {
        if (!function_exists('exec')) break;
        $which = is_windows() ? 'where' : 'command -v';
        @exec("$which $bin 2>&1", $o, $rc);
        if ($rc === 0) {
            $c = sprintf('%s %s -auto-orient -resize %s -strip -quality %d %s 2>&1', $cmd, escapeshellarg($srcFile),
                escapeshellarg("{$max}x{$max}>"), $q, escapeshellarg($dst));
            @exec($c, $o2, $rc2);
            if ($rc2 === 0 && is_file($dst) && filesize($dst) > 0) return true;
        }
    }
    return false;
}

$rows = $cat['photos'];
if ($opt['only']) {
    $needle = rtrim($opt['only'], '/');
    $rows = array_values(array_filter($rows, fn($p) => $p['folder'] === $needle || str_starts_with($p['folder'], $needle . '/')
        || str_starts_with($p['id'], $needle) || $p['name'] === $needle));
}
if (!$rows) { say('Nothing matches --only=' . $opt['only']); exit(1); }

say(sprintf('%d photo(s) selected from %d in the catalog.', count($rows), count($cat['photos'])));
if ($opt['dry-run']) {
    foreach ($rows as $p) say(sprintf('  %-52s %s', $p['folder'] . '/' . $p['name'] . '.webp', $p['source'] . ' — ' . $p['creator']));
    exit(0);
}
@mkdir($origDir, 0775, true);

// Network preflight: catch a broken CA path in php.ini before it produces dozens of identical errors.
$net = ['transport' => $opt['transport'], 'cacert' => $opt['cacert'], 'curl' => null, 'told' => false];
if (!in_array($net['transport'], ['auto', 'php', 'cli'], true)) { fwrite(STDERR, "--transport must be auto, php or cli\n"); exit(1); }
if ($net['cacert'] !== null && !is_file($net['cacert'])) { fwrite(STDERR, "--cacert file not found: {$net['cacert']}\n"); exit(1); }
if ($net['transport'] === 'cli') {
    $net['curl'] = cli_curl();
    if ($net['curl'] === null) { fwrite(STDERR, "--transport=cli needs the curl command (curl.exe on Windows 10/11), but it was not found.\n"); exit(1); }
}
if ($net['cacert'] === null && $net['transport'] !== 'cli' && ($bad = ini_ca_problems())) {
    say('Notice: php.ini points at a certificate file that does not exist on this computer:');
    foreach ($bad as $k => $v) say("  $k = $v");
    say('  (Usually the path was copied from another computer.)');
    if ($net['transport'] === 'auto' && ($c = cli_curl())) {
        $net['transport'] = 'cli'; $net['curl'] = $c;
        say("  This run will use the system curl instead: $c");
        say('  Please still fix php.ini (docs/INTEGRATION.md section 7); Laravel and Composer need it too.');
    } else {
        explain_ssl(); $net['told'] = true;
    }
    say('');
}

$done = 0; $skipped = 0; $failed = [];
foreach ($rows as $i => $p) {
    $dest = "$outRoot/{$p['folder']}/{$p['name']}.webp";
    $label = "{$p['folder']}/{$p['name']}.webp";
    if (is_file($dest) && filesize($dest) > 0 && !$opt['force']) { say("skip   $label (exists)"); $skipped++; continue; }
    say(sprintf('fetch  %s  [%d/%d]', $label, $i + 1, count($rows)));

    $bytes = null;
    foreach ($p['downloads'] as $url) {
        [$body, $code, $err] = http_get($url, $UA, $net);
        $problem = ($body !== null && $code >= 200 && $code < 300) ? image_problem($body) : 'http';
        if ($body !== null && $code >= 200 && $code < 300 && $problem === null) { $bytes = $body; break; }
        $why = $body === null ? ($err ?: 'no response') : ($code >= 400 || $code < 200 ? "HTTP $code" : $problem);
        say("       ! $why  <- " . preg_replace('#\?.*$#', '', $url));
    }
    if ($bytes === null) { $failed[] = $label . '  (download)'; continue; }

    $ext = (@getimagesizefromstring($bytes)[2] ?? 0) === IMAGETYPE_PNG ? 'png' : 'jpg';
    $orig = "$origDir/{$p['id']}.$ext";
    file_put_contents($orig, $bytes);
    if (!to_webp($orig, $dest, (int)$opt['max'], (int)$opt['quality'])) {
        say('       ! no WebP encoder found (enable the GD webp support or the Imagick extension, or install ImageMagick).');
        say("         The original is kept at storage/app/asset-originals/{$p['id']}.$ext");
        $failed[] = $label . '  (convert)';
        continue;
    }
    file_put_contents("$origDir/SHA256SUMS", hash('sha256', $bytes) . "  {$p['id']}.$ext\n", FILE_APPEND);
    say(sprintf('       ok  %d KB (original %d KB)', (int)round(filesize($dest) / 1024), (int)round(strlen($bytes) / 1024)));
    $done++;
    usleep((int)$opt['delay'] * 1000);
}

// credits.json: one entry per photo that exists on disk. Alt texts you already wrote are preserved.
$prevAlt = [];
foreach ((array)json_decode((string)@file_get_contents("$outRoot/credits.json"), true) as $old) {
    if (!empty($old['path']) && !empty($old['alt'])) $prevAlt[$old['path']] = $old['alt'];
}
$credits = [];
foreach ($cat['photos'] as $p) {
    $f = "$outRoot/{$p['folder']}/{$p['name']}.webp";
    if (!is_file($f)) continue;
    $info = @getimagesize($f);
    $credits[] = ['path' => "/assets/photography/{$p['folder']}/{$p['name']}.webp", 'width' => $info[0] ?? null, 'height' => $info[1] ?? null,
        'title_on_source' => $p['title'], 'location' => $p['location'], 'creator' => $p['creator'], 'creator_url' => $p['creator_url'],
        'source' => $p['source'], 'source_url' => $p['source_url'], 'license' => $p['license'], 'license_url' => $p['license_url'],
        'courtesy_credit' => $p['courtesy_credit'], 'people' => $p['people'], 'alt' => null];
    $credits[count($credits) - 1]['alt'] = $prevAlt["/assets/photography/{$p['folder']}/{$p['name']}.webp"] ?? null;
}
@mkdir($outRoot, 0775, true);
file_put_contents("$outRoot/credits.json", json_encode($credits, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "\n");

say('');
say(sprintf('Downloaded %d, skipped %d, failed %d. credits.json lists %d photo(s).', $done, $skipped, count($failed), count($credits)));
if ($failed) { say('Failed:'); foreach ($failed as $f) say("  - $f"); }
say('Next: open every file in public/assets/photography/ and look at it before using it (composition, crop, whether anyone');
say('is identifiable). Write an Indonesian alt text in credits.json ("alt") for each photo you keep. Delete the ones you do not want.');
exit($failed ? 2 : 0);
