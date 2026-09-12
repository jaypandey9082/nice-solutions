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

== Case Study Source Approval ==

Case Studies include a private Content Source & Approval panel for editors. Add
the HTTPS source URL and a short source label or verification note, then move the
approval state from Draft to Review and finally Approved as evidence and usage
rights are confirmed. Approval records editorial clearance of the wording and its
source; publishing remains a separate WordPress action.

Two rules apply to that panel.

Approval requires a Source URL that identifies the individual post. The seeded
candidates carry the LinkedIn company feed, which records where they came from
but not which post each one came from. A record still carrying the feed URL is
held at Review with an explanatory notice until an editor pastes the real one.

Image rights are a separate control. "Media cleared for publication" is its own
checkbox, because approving wording must never publish the photograph attached to
the record. Migrated records carry deck photography that has not been cleared;
until the box is ticked the project page shows an intentional placeholder.

Running `wp nice migrate-content` also creates five LinkedIn-derived Events
candidate records as WordPress drafts. They contain brief paraphrased text and
the NICE Solutions LinkedIn company-feed URL rather than a per-post citation, and
no images, team members, proof metrics, or publication approval. An existing slug in any status
is skipped completely, so reruns never overwrite editor changes or alter a
published record.

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

== Installation Shapes ==

The same code runs two ways.

The development install is a combined site: Events and Studio sit behind the
/events/ and /studio/ path prefixes. This is the default and needs no
configuration.

Production splits across three installations, where each division owns a
hostname and serves its own content at the root with no prefix. Every production
installation declares itself in wp-config.php, including the gateway:

    define( 'NICE_SITE_DIVISION', 'main' );     // the gateway
    define( 'NICE_SITE_DIVISION', 'events' );   // the Events installation

and tell it where its siblings live, so cross-site links resolve:

    define( 'NICE_MAIN_SITE_URL', 'https://nicesolutions.in' );
    define( 'NICE_EVENTS_SITE_URL', 'https://events.nicesolutions.in' );
    define( 'NICE_STUDIO_SITE_URL', 'https://studios.nicesolutions.in' );

The gateway sets NICE_SITE_DIVISION to 'main'. It owns no division content: it
registers no division routes, provisions no division pages, refuses
wp nice migrate-content, and resolves Events and Studio links against the
configured sibling URLs. Leaving the constant unset there would instead produce
the combined development site and publish both divisions from one database.

An unrecognised value raises a _doing_it_wrong() notice rather than silently
falling back.

What the setting changes:

* Rewrite rules. A division install registers ^services/{slug} rather than
  ^events/services/{slug}, and does not register the sibling's routes at all.
* Generated URLs. Its own content resolves against its own host; the sibling's
  resolves against the configured sibling URL.
* Division context. Every page on a division install belongs to that division,
  so navigation, contact details and styling resolve without a path prefix.
* Page provisioning. `wp nice migrate-content` creates the section pages at the
  root instead of nesting them under an Events or Studio landing page.
* Requests for the sibling division's records return 404 rather than serving a
  page that belongs on another host.

Run `wp nice migrate-content` and flush permalinks after changing the setting.

The prefix and sibling URLs are filterable through `nice_division_prefix` and
`nice_division_site_urls` if a deployment needs something different.

== Team Members ==

Adding a person is a form-filling job. In wp-admin go to Team Members, then
Add New, and fill in:

* Title: the person's full name. A record without a title cannot be published.
* Team Member Details: Role is the job title shown above the name. Division
  decides which page they appear on. Display Order sorts the roster, lowest
  first; use increments of ten so later additions can be slotted between
  existing people without renumbering.
* Featured Image: the portrait. Use a portrait-orientation photograph, roughly
  4:5 for Events and 3:4 for Studio. Set the attachment's alternative text,
  because the roster reads its alt text from the Media Library. A member with
  no portrait renders as role and name, without an empty image box.

Publish when the profile is approved. Draft members are never public.

Two behaviours worth knowing:

* A member saved without a Division appears on neither team page. The division
  select starts empty, so choose one before publishing.
* The Team link is hidden from a division's navigation until that division has
  at least one published member, so a half-filled roster never leaks.

The content migration seeds six placeholder drafts, three per division, so the
structure is visible in wp-admin before real profiles arrive. They carry no
portrait, because a production photograph standing in for a face would
misrepresent the team. Replace or delete them as real people are added; reruns
skip any slug that already exists and never overwrite editor changes.

== Changelog ==

= 1.2.0 =
* Add Studio Services, selected Studio Case Studies, and Studio Home provisioning.

= 1.1.0 =
* Add controlled Events routes, structural Page provisioning, and Phase 6 content enrichment.

= 1.0.0 =
* Initial NICE content architecture and migration command.
