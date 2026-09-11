=== NICE Core ===
Contributors: nicesolutions
Tags: content, portfolio, services, clients, team
Requires at least: 6.6
Requires PHP: 8.2
Stable tag: 1.2.0
License: GPLv2 or later

NICE Solutions content types, taxonomies, metadata, contact settings, and query helpers.

== Description ==

NICE Core keeps reusable business content independent from the NICE block theme.
It adds Services, Case Studies, Clients, and Team Members; the approved Division
and Service Type vocabularies; native metadata and contact settings; query
helpers; deliberate Events permalinks; and an idempotent WP-CLI content migration.

The plugin does not add frontend styles, scripts, forms, custom tables, or
third-party dependencies. The migration provisions the approved Events Pages,
the Studio Home Page, and source-backed starter content. Deactivation and
uninstall preserve content and options.

== Installation ==

1. Place `nice-core` in `wp-content/plugins/`.
2. Activate NICE Core in WordPress.
3. Run `wp nice migrate-content` once when the approved starter records should be imported.

The migration is safe to run again and reports records as created or skipped.

== Events Hero Media ==

Events hero media uses the native Media Library on the top-level Events Page.
The REST fields are _nice_events_hero_image_id, _nice_events_hero_mobile_image_id,
_nice_events_hero_focal_x, _nice_events_hero_focal_y, _nice_events_hero_reference,
and _nice_events_hero_media_initialized. Focal positions default to 50.

To initialize only the optional Events reference hero manually:

    wp eval 'print_r( nice_initialize_events_hero_media() );'

The initializer also runs during activation and the explicit content migration.
It imports assets/images/events-reference-hero.webp from the active theme only
when no Events hero metadata exists. Missing assets remain retryable. Existing
values, including empty selections and false initialized markers, are preserved.
No initialization runs on ordinary frontend or admin requests.

Focused runtime checks (temporarily write metadata and restore it in finally):

    wp eval-file scripts/wp-events-media-check.php

== Changelog ==

= 1.2.0 =
* Add Studio Services, selected Studio Case Studies, and Studio Home provisioning.

= 1.1.0 =
* Add controlled Events routes, structural Page provisioning, and Phase 6 content enrichment.

= 1.0.0 =
* Initial NICE content architecture and migration command.
