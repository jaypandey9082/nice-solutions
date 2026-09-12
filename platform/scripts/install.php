<?php
// Run only through the isolated runtime's WP-CLI wrapper. No legacy configuration is read.
if (!defined('WP_CLI') || !WP_CLI) {
    exit(1);
}
define('WP_INSTALLING', true);
require WP_CLI::get_runner()->config['path'] . '/wp-load.php';
$private = json_decode(file_get_contents(NICE_RUNTIME_PRIVATE), true, 512, JSON_THROW_ON_ERROR);
require_once ABSPATH . 'wp-admin/includes/upgrade.php';
if (!is_blog_installed()) {
    wp_install($private['title'], $private['admin_user'], $private['admin_email'], false, '', $private['admin_password']);
}
update_option('blog_public', '0');
update_option('permalink_structure', '/%postname%/');
update_option('blogdescription', '');
if (in_array(NICE_SITE_ROLE, ['events', 'studio'], true) && !get_page_by_path('contact')) {
    $title = NICE_SITE_ROLE === 'events' ? 'Contact NICE Events' : 'Contact NICE Studio';
    wp_insert_post([
        'post_type' => 'page', 'post_status' => 'publish', 'post_name' => 'contact', 'post_title' => $title,
        'post_content' => '<!-- wp:paragraph --><p><a href="' . esc_url(NICE_MAIN_URL) . '">Back to NICE Solutions</a></p><!-- /wp:paragraph -->',
    ], true);
}
flush_rewrite_rules(false);
WP_CLI::success('Independent local installation ready. Credentials retained privately.');
