<?php
if (!defined('WP_CLI') || !WP_CLI || !defined('NICE_SITE_ROLE') || NICE_SITE_ROLE !== 'main') {
    exit(1);
}
if (get_option('nice_runtime_references_imported')) {
    WP_CLI::success('Reference bootstrap already completed; editor selections preserved.');
    return;
}
$page = (int) get_option('page_on_front');
if (!$page || !function_exists('nice_platform_media_slot')) {
    WP_CLI::error('Seed the main homepage and activate NICE Platform Core first.');
}
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
$assets = dirname(__DIR__) . '/reference-assets';
$images = [];
foreach (['event-reference.webp', 'studio-reference.webp'] as $name) {
    $existing = get_posts(['post_type' => 'attachment', 'post_status' => 'inherit', 'meta_key' => '_nice_runtime_reference_source', 'meta_value' => $name, 'fields' => 'ids', 'posts_per_page' => 1]);
    if ($existing) {
        $images[$name] = (int) $existing[0];
        continue;
    }
    if (!is_file($assets . '/' . $name)) WP_CLI::error('Reference asset is not ready: ' . $name);
    $temporary = wp_tempnam($name);
    copy($assets . '/' . $name, $temporary);
    $id = media_handle_sideload(['name' => $name, 'tmp_name' => $temporary], $page, 'Temporary reference imagery');
    if (is_wp_error($id)) {
        if (is_file($temporary)) unlink($temporary);
        WP_CLI::error('Reference image import failed.');
    }
    update_post_meta($id, '_nice_runtime_reference_source', $name);
    update_post_meta($id, '_wp_attachment_image_alt', $name === 'event-reference.webp' ? 'Generic event production reference image' : 'Generic studio production reference image');
    $images[$name] = $id;
}
foreach (['hero' => 'event-reference.webp', 'events' => 'event-reference.webp', 'studio' => 'studio-reference.webp'] as $slot => $name) {
    update_post_meta($page, '_nice_media_' . $slot . '_id', $images[$name]);
    update_post_meta($page, '_nice_media_' . $slot . '_reference', true);
    foreach (['x', 'y'] as $axis) {
        if (!metadata_exists('post', $page, '_nice_media_' . $slot . '_' . $axis)) update_post_meta($page, '_nice_media_' . $slot . '_' . $axis, 50);
    }
}
update_option('nice_runtime_references_imported', true, false);
WP_CLI::success('Two reference attachments imported into Main only; three slots assigned.');
