# NICE Platform Core

Independent WordPress plugin, with no dependency on the legacy NICE Core plugin.
Install this folder as `wp-content/plugins/nice-platform-core/` on each new site.
Activation registers content and flushes rewrite rules only. It does not seed, import,
update contact settings, or read another installation.

## Environment

Define in the installation's `wp-config.php`, before WordPress loads:

```php
define( 'NICE_SITE_ROLE', 'main' ); // main, events or studio; default main.
define( 'NICE_MAIN_URL', 'https://nicesolutions.in/' );
define( 'NICE_EVENTS_URL', 'https://events.nicesolutions.in/' );
define( 'NICE_STUDIO_URL', 'https://studios.nicesolutions.in/' );
```

Override the URL constants with that environment's origins. No `/events/` or
`/studio/` prefix is added. Main registers the non-public, REST-editable
`nice_work_preview` type. Division roles only provide identity, Page hero media,
and contact settings; their content types belong to future stages.

## Theme contracts

- `nice_platform_role()` returns `main`, `events` or `studio`.
- `nice_platform_site_url($role, $path = '')` returns a configured absolute URL;
  invalid roles or unsafe paths return an empty string.
- `nice_platform_home_content($page_id = 0)` returns plain-text keys `hero_title`,
  `hero_description`, `events_description`, `studio_description`, `about_heading`,
  `about_body`, `brief_body`, `idea_body`, `solution_body`. Zero uses `page_on_front`.
- Copy metadata is `_nice_home_{key}`. Missing metadata has the plugin's default;
  an explicitly empty value stays empty. Escape copy at render time.
- `nice_platform_media_slot($slot, $page_id = 0)` returns `id`, `mobile_id`, `x`,
  `y`, `reference`; valid slots are `hero`, `events`, `studio`. Fields are stored
  separately as `_nice_media_{slot}_{field}`. Missing/deleted/non-image attachments
  yield ID 0. Focal points default to 50 and are clamped to 0-100.
- `nice_platform_get_previews()` returns up to three published local `WP_Post`
  objects ordered by `menu_order ASC, ID ASC`; other roles return an empty array.
- `nice_platform_preview_url($post)` validates `_nice_destination_url` against
  `_nice_division` (`events` or `studio`). Exact production HTTPS origins are mapped
  to configured environment URLs. Otherwise an approved configured origin is
  accepted; `_nice_destination_path` is the local path fallback. Unrelated hosts,
  credentials and unsafe paths are rejected. An invalid destination returns `''`.
  The renderer should omit its link rather than use a guessed destination.
- Previews use native Title, Excerpt, Featured Image and Page Attributes Order.
  `_nice_reference` marks reference media; preview images are unassigned at seed.
- `nice_platform_client_names()` returns approved names from the homepage's
  `_nice_client_names` textarea, one name per line. Blank yields an empty array.
- `nice_platform_contact()` returns `email`, `whatsapp`, `phone`, `social`.
  First three are strings; social is an array of URLs. WhatsApp is a `https://wa.me/`
  URL; phone is a validated number (render as `tel:`). No business defaults exist.
  Settings > NICE Platform owns the local `nice_platform_contact` option.

## Explicit provisioning and editing

Run `wp nice-platform seed-main` only on the new Main installation. It reuses a
configured front Page, otherwise an existing Home Page, otherwise creates Home.
It provisions the three approved summaries, blank media slots and approved client
names without reading the reference database. Reruns preserve existing fields,
blank values, images and preview records, including trashed previews. No media
files or external services are fetched.

Import generated images separately with `wp media import <file> --porcelain`.
Use the returned ID with `wp post meta update <home-id> _nice_media_hero_id <id>`.
Set `_nice_media_hero_reference` to `1`. Repeat for `events` and `studio` slots;
all image IDs must belong to this installation. Admin Pages provide Select,
Replace, Remove, desktop/mobile previews, focal points and reference controls.
Attachment alt text belongs to the Media Library. Remove saves ID 0 permanently.
No frontend presentation or remote REST requests are included in this plugin.

## Tests

On a disposable development database with this plugin active:

`wp eval-file platform/tests/cms-contract.php`

`wp eval-file platform/tests/cms-seed.php`

The contract test creates then removes temporary Page, preview and attachment
records. The seed test requires an empty main database and removes its fixtures
afterwards; it refuses to run against existing Page or preview content. Run the
contract test in separate processes with each site role and development URL
configuration. See the test output for checks actually completed. Native editor
and Media Library browser verification remains a separate integration check.
