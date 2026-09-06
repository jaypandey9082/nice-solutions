=== NICE Core ===
Contributors: nicesolutions
Tags: content, portfolio, services, clients, team
Requires at least: 6.6
Requires PHP: 8.2
Stable tag: 1.0.0
License: GPLv2 or later

NICE Solutions content types, taxonomies, metadata, contact settings, and query helpers.

== Description ==

NICE Core keeps reusable business content independent from the NICE block theme.
It adds Services, Case Studies, Clients, and Team Members; the approved Division
and Service Type vocabularies; native metadata and contact settings; query
helpers; and an idempotent WP-CLI content migration.

The plugin does not add frontend styles, scripts, pages, forms, custom tables, or
third-party dependencies. Deactivation and uninstall preserve content and options.

== Installation ==

1. Place `nice-core` in `wp-content/plugins/`.
2. Activate NICE Core in WordPress.
3. Run `wp nice migrate-content` once when the approved starter records should be imported.

The migration is safe to run again and reports records as created or skipped.

== Changelog ==

= 1.0.0 =
* Initial NICE content architecture and migration command.
