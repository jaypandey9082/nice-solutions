<?php
declare(strict_types=1);
define('NICE_RUNTIME_LIBRARY', true);
require dirname(__DIR__) . '/scripts/runtime.php';

function expect(bool $condition, string $message): void {
    if (!$condition) throw new RuntimeException($message);
}
function captureWp(string $role, string $code): array {
    $process = proc_open([PHP_BINARY, tools()['wp'], '--path=' . RUNTIME . '/' . $role . '/public', '--skip-plugins', '--skip-themes', 'eval', $code], [0 => ['file', '/dev/null', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    $out = stream_get_contents($pipes[1]); fclose($pipes[1]);
    stream_get_contents($pipes[2]); fclose($pipes[2]);
    expect(proc_close($process) === 0, 'WP-CLI read-only probe failed for ' . $role);
    return json_decode($out, true, 512, JSON_THROW_ON_ERROR);
}
function httpStatus(string $url): int {
    $curl = curl_init($url);
    curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15, CURLOPT_FOLLOWLOCATION => true]);
    curl_exec($curl);
    $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    curl_close($curl);
    return $status;
}
try {
    state();
    mysql()->close();
    $data = [];
    foreach (ROLES as $role) {
        $m = manifest($role);
        $data[$role] = captureWp($role, 'echo wp_json_encode(["role"=>NICE_SITE_ROLE,"environment"=>wp_get_environment_type(),"home"=>home_url(),"public"=>get_option("blog_public"),"db"=>DB_NAME,"uploads"=>wp_upload_dir()["basedir"],"multisite"=>is_multisite(),"main"=>NICE_MAIN_URL,"events"=>NICE_EVENTS_URL,"studio"=>NICE_STUDIO_URL,"attachments"=>(int)wp_count_posts("attachment")->inherit]);');
        $d = $data[$role];
        expect($d['role'] === $role && $d['environment'] === 'local', 'Role/environment mismatch.');
        expect($d['home'] === 'http://localhost:' . $m['port'], 'Canonical local URL mismatch.');
        expect($d['public'] === '0' && !$d['multisite'], 'Indexing or installation isolation mismatch.');
        expect($d['uploads'] === RUNTIME . '/' . $role . '/public/wp-content/uploads', 'Uploads are not owned by this site.');
        foreach (ROLES as $target) expect($d[$target] === 'http://localhost:' . manifest($target)['port'] . '/', 'Cross-site URL mismatch.');
        expect(httpStatus($d['home'] . '/') === 200, 'Homepage did not return HTTP 200: ' . $role);
        expect(httpStatus($d['home'] . '/wp-login.php') === 200, 'Admin login did not return HTTP 200: ' . $role);
        if ($role !== 'main') {
            expect($d['attachments'] === 0, 'Division skeleton has media.');
            expect(httpStatus($d['home'] . '/contact/') === 200, 'Minimal contact route failed: ' . $role);
        }
        foreach (['theme' => 'themes/nice-platform', 'plugin' => 'plugins/nice-platform-core'] as $source => $link) {
            expect(readlink(RUNTIME . '/' . $role . '/public/wp-content/' . $link) === PLATFORM . '/' . $source, 'Shared package link mismatch.');
        }
        echo 'PASS ' . $role . ': role, URLs, noindex, separate uploads, homepage/admin' . ($role === 'main' ? '' : ', contact, no media') . PHP_EOL;
    }
    expect(count(array_unique(array_column($data, 'db'))) === 3, 'Database names are not unique.');
    foreach (ROLES as $index => $role) {
        $other = $data[ROLES[($index + 1) % 3]]['db'];
        $code = 'global $wpdb; $wpdb->suppress_errors(true); $value=$wpdb->query(' . var_export('SELECT option_id FROM `' . $other . '`.wp_options LIMIT 1', true) . '); echo wp_json_encode(["denied"=>$value===false]);';
        expect(captureWp($role, $code)['denied'], 'Database user can read a different site.');
    }
    echo "PASS three unique databases; every site user denied cross-database access\n";
    if (in_array('--lifecycle', $argv, true)) {
        foreach (ROLES as $offline) {
            stopSite($offline);
            try {
                expect(httpStatus($data[$offline]['home']) === 0, 'Stopped server still responds.');
                foreach (ROLES as $online) if ($online !== $offline) expect(httpStatus($data[$online]['home']) === 200, 'Site depends on stopped sibling.');
            } finally { startSite($offline); }
        }
        echo "PASS each site stops/restarts without affecting its siblings\n";
    }
} catch (Throwable $error) {
    fwrite(STDERR, 'Runtime check failed: ' . ($error instanceof mysqli_sql_exception ? 'owned database probe failed' : $error->getMessage()) . PHP_EOL);
    exit(1);
}
