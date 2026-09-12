<?php
// PHP's development server serves existing files and sends permalinks to WordPress.
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/');
$root = realpath($_SERVER['DOCUMENT_ROOT']);
$file = realpath($root . $path);
$allowed = $file && ($file === $root || str_starts_with($file, $root . DIRECTORY_SEPARATOR));
// Development packages are explicit symlinks outside the document root.
foreach (['/wp-content/themes/nice-platform/' => 'theme', '/wp-content/plugins/nice-platform-core/' => 'plugin'] as $prefix => $package) {
    $packageRoot = realpath(dirname(__DIR__) . '/' . $package);
    if ($file && str_starts_with($path, $prefix) && str_starts_with($file, $packageRoot . DIRECTORY_SEPARATOR)) {
        $allowed = true;
    }
}
if ($allowed && (is_file($file) || (is_dir($file) && is_file($file . '/index.php')))) {
    return false;
}
require $root . '/index.php';
