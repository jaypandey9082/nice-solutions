<?php
declare(strict_types=1);

// This controller never reads the reference site's wp-config.php or contacts its database.
umask(0077);
define('PLATFORM', dirname(__DIR__));
define('RUNTIME', PLATFORM . '/.runtime');
define('ROLES', ['main', 'events', 'studio']);

function fail(string $message): never { throw new RuntimeException($message); }
function jsonRead(string $path): array { return json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR); }
function privateWrite(string $path, string $content): void {
    if (file_put_contents($path, $content, LOCK_EX) === false) fail('Cannot write runtime state.');
    chmod($path, 0600);
}
function mkdirPrivate(string $path): void { if (!is_dir($path) && !mkdir($path, 0700, true)) fail('Cannot create runtime directory.'); }
function manifest(string $role): array {
    if (!in_array($role, ROLES, true)) fail('Expected role main, events, or studio.');
    return jsonRead(PLATFORM . '/instances/' . $role . '.json');
}
function discover(string $pattern, string $override): string {
    $value = getenv($override);
    if ($value && is_file($value)) return $value;
    $paths = glob($pattern) ?: [];
    natsort($paths);
    if (!$paths) fail('Runtime dependency missing: ' . $override);
    return array_values($paths)[count($paths) - 1];
}
function tools(): array {
    $base = getenv('HOME') . '/Library/Application Support/Local/lightning-services';
    return [
        'php' => PHP_BINARY,
        'mysqld' => discover($base . '/mysql-*/bin/darwin-*/bin/mysqld', 'NICE_MYSQLD_BIN'),
        'wp' => discover('/Applications/Local.app/Contents/Resources/extraResources/bin/wp-cli/wp-cli.phar', 'NICE_WP_CLI'),
        'core' => getenv('NICE_CORE_SOURCE') ?: getenv('HOME') . '/Local Sites/nice-solutions/app/public',
    ];
}
function run(array $args, ?string $log = null): int {
    $descriptors = [0 => ['file', '/dev/null', 'r'], 1 => $log ? ['file', $log, 'a'] : STDOUT, 2 => $log ? ['file', $log, 'a'] : STDERR];
    $process = proc_open($args, $descriptors, $pipes);
    if (!is_resource($process)) fail('Could not start command.');
    return proc_close($process);
}
function checkRun(array $args, ?string $log = null): void {
    if (run($args, $log) !== 0) fail('Command failed. Inspect the private runtime log if present.');
}
function state(): array {
    if (!is_file(RUNTIME . '/owner.json')) fail('Run provision first.');
    $state = jsonRead(RUNTIME . '/owner.json');
    if ($state['platform'] !== PLATFORM) fail('Runtime belongs to another checkout. Do not reuse it.');
    return $state;
}
function socketPath(): string { return RUNTIME . '/mysql.sock'; }
function mysql(): mysqli {
    state();
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $db = new mysqli('localhost', 'root', '', '', 0, socketPath());
    // Refuse even read/write operations if the socket points at a foreign server.
    $dir = $db->query('SELECT @@datadir')->fetch_row()[0];
    if (realpath($dir) !== realpath(RUNTIME . '/mysql')) fail('Refusing foreign MySQL data directory.');
    return $db;
}
function processMatches(int $pid, string $needle): bool {
    if ($pid < 2 || !posix_kill($pid, 0)) return false;
    $command = shell_exec('/bin/ps -p ' . $pid . ' -o command=') ?: '';
    return str_contains($command, $needle);
}
function launch(array $args, string $pidFile, string $log): int {
    // A separate session survives the command runner without retaining its output pipes.
    $child = pcntl_fork();
    if ($child === -1) fail('Could not fork runtime process.');
    if ($child === 0) {
        if (posix_setsid() === -1) exit(1);
        $daemon = pcntl_fork();
        if ($daemon === -1) exit(1);
        if ($daemon > 0) exit(0);
        fclose(STDIN); fclose(STDOUT); fclose(STDERR);
        $input = fopen('/dev/null', 'r');
        $output = fopen($log, 'a');
        $errors = fopen($log, 'a');
        privateWrite($pidFile, (string) getmypid());
        pcntl_exec($args[0], array_slice($args, 1));
        exit(1);
    }
    pcntl_waitpid($child, $status);
    for ($i = 0; $i < 30; $i++) {
        if (is_file($pidFile)) {
            $pid = (int) file_get_contents($pidFile);
            if ($pid > 1 && posix_kill($pid, 0)) return $pid;
        }
        usleep(100000);
    }
    fail('Could not launch runtime process.');
}
function startDatabase(): void {
    state();
    if (file_exists(socketPath())) {
        try { mysql()->close(); return; } catch (mysqli_sql_exception $error) { /* A stale socket is left to MySQL to recover. */ }
    }
    $pidFile = RUNTIME . '/mysql-process.pid';
    if (is_file($pidFile) && processMatches((int) file_get_contents($pidFile), '--datadir=' . RUNTIME . '/mysql')) {
        fail('Owned MySQL is running but not ready. Inspect its private log.');
    }
    launch([tools()['mysqld'], '--no-defaults', '--datadir=' . RUNTIME . '/mysql', '--socket=' . socketPath(), '--skip-networking', '--mysqlx=OFF', '--pid-file=' . RUNTIME . '/mysql-server.pid', '--log-error=' . RUNTIME . '/logs/mysql.log'], $pidFile, RUNTIME . '/logs/mysql-process.log');
    for ($i = 0; $i < 100; $i++) {
        try { mysql()->close(); return; } catch (mysqli_sql_exception $error) { usleep(100000); }
    }
    fail('MySQL did not become ready. Inspect platform/.runtime/logs/mysql.log privately.');
}
function copyTree(string $from, string $to): void {
    if (is_link($from)) fail('Refusing symlink while copying core/package files.');
    if (is_dir($from)) {
        mkdirPrivate($to);
        foreach (new DirectoryIterator($from) as $entry) {
            if ($entry->isDot()) continue;
            copyTree($entry->getPathname(), $to . '/' . $entry->getFilename());
        }
    } elseif (!copy($from, $to)) fail('File copy failed.');
}
function wp(string $role, array $arguments, ?string $log = null): int {
    manifest($role);
    state();
    return run([tools()['php'], tools()['wp'], '--path=' . RUNTIME . '/' . $role . '/public', '--no-color', ...$arguments], $log);
}
function provision(): void {
    $tools = tools();
    if (!is_file($tools['core'] . '/wp-includes/version.php')) fail('Installed WordPress core not found. Set NICE_CORE_SOURCE.');
    mkdirPrivate(RUNTIME);
    mkdirPrivate(RUNTIME . '/logs');
    if (!is_file(RUNTIME . '/owner.json')) {
        if (is_dir(RUNTIME . '/mysql')) fail('Unowned database directory exists; refusing to use it.');
        privateWrite(RUNTIME . '/owner.json', json_encode(['platform' => PLATFORM, 'id' => bin2hex(random_bytes(5))], JSON_PRETTY_PRINT));
    }
    $owner = state();
    if (!is_dir(RUNTIME . '/mysql') || count(scandir(RUNTIME . '/mysql')) === 2) {
        checkRun([$tools['mysqld'], '--no-defaults', '--initialize-insecure', '--datadir=' . RUNTIME . '/mysql'], RUNTIME . '/logs/mysql-init.log');
    }
    startDatabase();
    foreach (ROLES as $role) provisionSite($role, $owner, $tools);
    status();
}
function provisionSite(string $role, array $owner, array $tools): void {
    $m = manifest($role);
    $site = RUNTIME . '/' . $role;
    $root = $site . '/public';
    mkdirPrivate($site);
    $privatePath = $site . '/private.json';
    if (!is_file($privatePath)) {
        if (is_dir($root)) fail('Unowned site directory exists: ' . $role);
        privateWrite($privatePath, json_encode([
            'title' => $m['title'], 'database' => 'nicep_' . $owner['id'] . '_' . $role,
            'database_user' => 'np_' . $owner['id'] . '_' . $role, 'database_password' => bin2hex(random_bytes(24)),
            'admin_user' => 'nice_admin', 'admin_email' => 'admin@' . $role . '.nice.test',
            'admin_password' => bin2hex(random_bytes(24)), 'salts' => array_map(fn() => bin2hex(random_bytes(48)), range(1, 8)),
        ], JSON_PRETTY_PRINT));
    }
    $private = jsonRead($privatePath);
    if (!is_dir($root)) {
        mkdirPrivate($root);
        foreach (['wp-admin', 'wp-includes'] as $dir) copyTree($tools['core'] . '/' . $dir, $root . '/' . $dir);
        $files = ['index.php', 'wp-activate.php', 'wp-blog-header.php', 'wp-comments-post.php', 'wp-cron.php', 'wp-links-opml.php', 'wp-load.php', 'wp-login.php', 'wp-mail.php', 'wp-settings.php', 'wp-signup.php', 'wp-trackback.php', 'xmlrpc.php'];
        foreach ($files as $file) copyTree($tools['core'] . '/' . $file, $root . '/' . $file);
        foreach (['themes', 'plugins', 'uploads'] as $dir) mkdirPrivate($root . '/wp-content/' . $dir);
        // Only the bundled WordPress fallback theme is copied; no NICE theme, plugins or uploads.
        copyTree($tools['core'] . '/wp-content/themes/twentytwentyfive', $root . '/wp-content/themes/twentytwentyfive');
    }
    $db = mysql();
    $name = $private['database'];
    $user = $private['database_user'];
    if (!preg_match('/^nicep_[a-f0-9]{10}_(main|events|studio)$/', $name)) fail('Invalid owned database name.');
    if (!is_file($site . '/database-owned')) {
        $stmt = $db->prepare('SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?');
        $stmt->bind_param('s', $name); $stmt->execute();
        if ($stmt->get_result()->num_rows) fail('Database already exists without ownership marker; refusing to overwrite.');
        $db->query("CREATE DATABASE `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $password = $db->real_escape_string($private['database_password']);
        $db->query("CREATE USER '$user'@'localhost' IDENTIFIED BY '$password'");
        $db->query("GRANT ALL PRIVILEGES ON `$name`.* TO '$user'@'localhost'");
        privateWrite($site . '/database-owned', $name);
    }
    $db->close();
    $config = <<<'CONFIG'
<?php
define('NICE_RUNTIME_PRIVATE', __DIR__ . '/../private.json');
$nicePrivate = json_decode(file_get_contents(NICE_RUNTIME_PRIVATE), true, 512, JSON_THROW_ON_ERROR);
define('DB_NAME', $nicePrivate['database']);
define('DB_USER', $nicePrivate['database_user']);
define('DB_PASSWORD', $nicePrivate['database_password']);
define('DB_HOST', 'localhost:' . dirname(__DIR__, 2) . '/mysql.sock');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');
foreach (['AUTH_KEY', 'SECURE_AUTH_KEY', 'LOGGED_IN_KEY', 'NONCE_KEY', 'AUTH_SALT', 'SECURE_AUTH_SALT', 'LOGGED_IN_SALT', 'NONCE_SALT'] as $index => $key) define($key, $nicePrivate['salts'][$index]);
unset($nicePrivate);
define('WP_ENVIRONMENT_TYPE', 'local');
define('WP_DEBUG', true);
define('WP_DEBUG_DISPLAY', false);
define('WP_DEBUG_LOG', dirname(__DIR__) . '/debug.log');
define('DISALLOW_FILE_EDIT', true);
define('AUTOMATIC_UPDATER_DISABLED', true);
define('WP_AUTO_UPDATE_CORE', false);
define('DISABLE_WP_CRON', true);
define('WP_DEFAULT_THEME', 'twentytwentyfive');
$table_prefix = 'wp_';
CONFIG;
    $config .= "\ndefine('NICE_SITE_ROLE', " . var_export($role, true) . ");\n";
    foreach (ROLES as $target) $config .= "define('NICE_" . strtoupper($target) . "_URL', 'http://localhost:" . manifest($target)['port'] . "/');\n";
    $url = 'http://localhost:' . $m['port'];
    $config .= "define('WP_HOME', '$url');\ndefine('WP_SITEURL', '$url');\n";
    $config .= "if (!defined('ABSPATH')) define('ABSPATH', __DIR__ . '/');\nrequire_once ABSPATH . 'wp-settings.php';\n";
    privateWrite($root . '/wp-config.php', $config);
    if (wp($role, ['--skip-plugins', '--skip-themes', 'eval-file', __DIR__ . '/install.php', '--skip-wordpress'], RUNTIME . '/logs/' . $role . '-install.log') !== 0) fail('WordPress installation failed for ' . $role . '. Inspect private log.');
    linkPackages($role);
    echo $role . ': provisioned independently; no content migrated.' . PHP_EOL;
}
function linkPackages(string $role): void {
    $root = RUNTIME . '/' . $role . '/public/wp-content';
    foreach (['theme' => $root . '/themes/nice-platform', 'plugin' => $root . '/plugins/nice-platform-core'] as $source => $destination) {
        if (is_link($destination)) {
            if (readlink($destination) !== PLATFORM . '/' . $source) fail('Unexpected package symlink.');
        } elseif (file_exists($destination)) fail('Package destination is not a managed link.');
        else symlink(PLATFORM . '/' . $source, $destination);
    }
}
function startSite(string $role): void {
    $m = manifest($role);
    if (!is_file(RUNTIME . '/' . $role . '/public/wp-config.php')) fail('Provision ' . $role . ' first.');
    $pidFile = RUNTIME . '/' . $role . '/server.pid';
    $root = RUNTIME . '/' . $role . '/public';
    if (is_file($pidFile) && processMatches((int) file_get_contents($pidFile), '-t ' . $root)) return;
    foreach (['127.0.0.1', '[::1]'] as $address) {
        $probe = @stream_socket_server('tcp://' . $address . ':' . $m['port'], $errno, $error);
        if (!$probe) fail('Port ' . $m['port'] . ' is occupied; no existing process was stopped.');
        fclose($probe);
    }
    launch([tools()['php'], '-d', 'display_errors=0', '-d', 'upload_max_filesize=64M', '-d', 'post_max_size=64M', '-S', 'localhost:' . $m['port'], '-t', $root, __DIR__ . '/router.php'], $pidFile, RUNTIME . '/logs/' . $role . '-server.log');
    usleep(200000);
    if (!processMatches((int) file_get_contents($pidFile), '-t ' . $root)) fail('Server did not start for ' . $role);
}
function stopSite(string $role): void {
    manifest($role);
    $file = RUNTIME . '/' . $role . '/server.pid';
    if (!is_file($file)) return;
    $pid = (int) file_get_contents($file);
    if (processMatches($pid, '-t ' . RUNTIME . '/' . $role . '/public')) {
        posix_kill($pid, SIGTERM);
        for ($i = 0; $i < 30 && posix_kill($pid, 0); $i++) usleep(100000);
        if (posix_kill($pid, 0)) fail('Owned PHP process has not exited; PID retained.');
    }
    unlink($file);
}
function status(): void {
    echo 'PHP: ' . PHP_BINARY . PHP_EOL;
    foreach (ROLES as $role) {
        $pidFile = RUNTIME . '/' . $role . '/server.pid';
        $running = is_file($pidFile) && processMatches((int) file_get_contents($pidFile), '-t ' . RUNTIME . '/' . $role . '/public');
        echo $role . ': http://localhost:' . manifest($role)['port'] . '/ (' . ($running ? 'running' : 'stopped') . ')' . PHP_EOL;
    }
}
function activate(string $role): void {
    manifest($role);
    if (!is_file(PLATFORM . '/plugin/nice-platform-core.php') || !is_file(PLATFORM . '/theme/style.css')) fail('Shared plugin and theme are not ready.');
    linkPackages($role);
    if (wp($role, ['plugin', 'activate', 'nice-platform-core']) !== 0 || wp($role, ['theme', 'activate', 'nice-platform']) !== 0) fail('Package activation failed.');
}
function package(): void {
    if (!is_file(PLATFORM . '/theme/style.css') || !is_file(PLATFORM . '/plugin/nice-platform-core.php')) fail('Theme/plugin must be ready before packaging.');
    $out = PLATFORM . '/.packages/' . gmdate('Ymd-His') . '-' . bin2hex(random_bytes(3));
    mkdirPrivate($out);
    copyTree(PLATFORM . '/theme', $out . '/nice-platform');
    copyTree(PLATFORM . '/plugin', $out . '/nice-platform-core');
    foreach (['nice-platform', 'nice-platform-core'] as $name) checkRun(['/usr/bin/ditto', '-c', '-k', '--sequesterRsrc', '--keepParent', $out . '/' . $name, $out . '/' . $name . '.zip']);
    foreach (ROLES as $role) {
        $m = manifest($role);
        $config = "<?php\n// Insert before WordPress loads in this instance's privately managed wp-config.php.\n";
        $config .= "define('NICE_SITE_ROLE', '$role');\ndefine('WP_ENVIRONMENT_TYPE', 'production');\n";
        foreach (ROLES as $target) $config .= "define('NICE_" . strtoupper($target) . "_URL', '" . manifest($target)['production_url'] . "');\n";
        privateWrite($out . '/' . $role . '-constants.php', $config);
        privateWrite($out . '/' . $role . '-manifest.json', json_encode($m, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
    echo 'Packages and configuration fragments: ' . $out . PHP_EOL;
}
if (defined('NICE_RUNTIME_LIBRARY')) return;
try {
    $command = $argv[1] ?? 'help';
    $role = $argv[2] ?? 'all';
    switch ($command) {
        case 'provision': provision(); break;
        case 'start': state(); startDatabase(); foreach ($role === 'all' ? ROLES : [$role] as $site) startSite($site); status(); break;
        case 'stop':
            state(); foreach ($role === 'all' ? ROLES : [$role] as $site) stopSite($site);
            if ($role === 'all' && file_exists(socketPath())) { $db = mysql(); $db->query('SHUTDOWN'); $db->close(); }
            status(); break;
        case 'status': status(); break;
        case 'wp': exit(wp($role, array_slice($argv, 3)));
        case 'activate': activate($role); break;
        case 'seed-main':
            if (($argv[2] ?? '') !== '--plugin-ready') fail('Coordinate plugin readiness first; then use seed-main --plugin-ready.');
            if (wp('main', ['cli', 'has-command', 'nice-platform seed-main']) !== 0) fail('The plugin seed command is not available.');
            $result = wp('main', ['nice-platform', 'seed-main']);
            if ($result === 0) privateWrite(RUNTIME . '/main/seed-confirmed', gmdate('c'));
            exit($result);
        case 'import-references':
            if (!is_file(RUNTIME . '/main/seed-confirmed')) fail('Run the coordinated seed-main command successfully first.');
            exit(wp('main', ['eval-file', __DIR__ . '/import-references.php']));
        case 'check': exit(run([PHP_BINARY, PLATFORM . '/tests/runtime-isolation.php', ...array_slice($argv, 2)]));
        case 'package': package(); break;
        default: echo "Commands: provision, start [all|role], stop [all|role], status, wp role <args>, activate role, seed-main --plugin-ready, import-references, check [--lifecycle], package\n";
    }
} catch (Throwable $error) {
    // Never echo exception details: database libraries may include credentials or SQL.
    fwrite(STDERR, $error instanceof mysqli_sql_exception ? "Database operation failed. Existing external databases were not accessed.\n" : $error->getMessage() . "\n");
    exit(1);
}
