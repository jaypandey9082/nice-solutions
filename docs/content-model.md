# NICE Core Content Model

NICE Core `1.0.0` owns reusable NICE business content. The NICE block theme owns
templates, patterns, CSS, JavaScript, layout, and presentation. The plugin uses
only WordPress posts, post meta, terms, options, and attachments.

## Plugin Structure

```text
wp-content/plugins/nice-core/
|-- nice-core.php
|-- readme.txt
|-- uninstall.php
`-- includes/
    |-- post-types.php
    |-- taxonomies.php
    |-- meta.php
    |-- settings.php
    |-- queries.php
    |-- helpers.php
    |-- admin.php
    |-- migration.php
    `-- activation.php
```

All public identifiers, option names, meta keys, hooks, and functions use the
`nice_` prefix. The text domain is `nice-core`.

## Post Types

| Admin label | Identifier | Core fields | Public query | REST base |
| --- | --- | --- | --- | --- |
| Services | `nice_service` | title, editor, excerpt, featured image, revisions | Yes | `wp/v2/nice-services` |
| Case Studies | `nice_case_study` | title, editor, excerpt, featured image, revisions | Yes | `wp/v2/nice-case-studies` |
| Clients | `nice_client` | title, excerpt, featured image, revisions | No direct frontend | `wp/v2/nice-clients` |
| Team Members | `nice_team_member` | title, editor, featured image, revisions | No direct frontend | `wp/v2/nice-team-members` |

The post types declare custom-field support because WordPress requires it for
registered REST meta. The raw Custom Fields panel is removed; editors use only
the named NICE panels.

Archives and pretty rewrites are deliberately disabled in Phase 5. Future
division-aware templates will own the approved `/events/...` and `/studio/...`
routes without exposing internal post-type slugs.

## Taxonomies

### Division

Identifier: `nice_division`

- `events`: Events
- `studio`: Studio

Assigned to Services, Case Studies, and Team Members. Clients are shared and do
not receive a Division term.

### Service Type

Identifier: `nice_service_type`

| Term slug | Label | Required Division |
| --- | --- | --- |
| `corporate-events` | Corporate Events | Events |
| `exhibitions-conferences` | Exhibitions & Conferences | Events |
| `activations-promotions` | Activations & Promotions | Events |
| `corporate-videos` | Corporate Videos | Studio |
| `digital-content-creation` | Digital Content Creation | Studio |
| `films-entertainment` | Films & Entertainment | Studio |

Only these sitemap-approved terms can be created. Editors assign them through
the Service Classification or Project Information panels. Assignment validation
keeps each Service/Case Study on one Service Type and its matching Division.

## Registered Meta

### Case Studies

| Meta key | Type | Purpose |
| --- | --- | --- |
| `_nice_client_id` | integer | Relationship to a `nice_client` post |
| `_nice_client_name` | string | Source-approved credit only when no Client relationship applies |
| `_nice_location` | string | Plain-text project location |
| `_nice_year` | integer | Optional four-digit year |
| `_nice_featured` | boolean | Editorial featured control |
| `_nice_display_order` | integer | Stable editorial ordering |
| `_nice_reference_url` | HTTPS string | Optional approved external reference |

### Clients

| Meta key | Type | Purpose |
| --- | --- | --- |
| `_nice_client_url` | HTTPS string | Optional approved website URL |
| `_nice_display_order` | integer | Stable editorial ordering |
| `_nice_featured` | boolean | Featured-client query control |

Client logos use the core featured-image attachment ID.

### Team Members

| Meta key | Type | Purpose |
| --- | --- | --- |
| `_nice_role` | string | Role/title |
| `_nice_display_order` | integer | Stable editorial ordering |

Portraits use the core featured-image attachment ID. No Team Member records are
created until approved names, roles, biographies, and portraits are supplied.

## Contact Settings

Option: `nice_contact_settings`

Stored keys are `whatsapp_url`, `email_address`, `phone`, and `social_urls`.
Public helpers derive `phone_url`. WhatsApp and social values must be HTTPS;
email uses WordPress email validation; phone input preserves readable
international formatting and generates a sanitized `tel:` URL. Invalid URL or
email submissions retain the last approved value.

The UI is under Settings > NICE Contact. Empty fields remain unpublished and the
theme continues to display its publication-pending notice.

## Query API

```text
nice_get_services()
nice_get_events_services()
nice_get_studio_services()
nice_get_service_by_slug()
nice_get_services_by_service_type()

nice_get_case_studies()
nice_get_featured_case_studies()
nice_get_case_studies_by_service()
nice_get_case_study_by_slug()
nice_get_case_study_client_name()

nice_get_clients()
nice_get_featured_clients()
nice_get_client_by_slug()

nice_get_team_members()
nice_get_team_members_by_division()

nice_get_contact_settings()
nice_get_contact_whatsapp_url()
nice_get_contact_email()
nice_get_contact_phone_url()
nice_get_social_links()
```

Content query helpers return arrays of `WP_Post` objects; singular helpers return
`WP_Post|null`. Convenience query arguments include `division`, `service_type`,
and `featured`. No transient or custom cache layer is added.

## Migration

Run the migration after plugin activation:

```bash
wp nice migrate-content
```

The command creates missing approved terms, then Clients, Services, Case
Studies, and media attachments. It identifies records by post type and slug and
attachments by `_nice_source_asset`, so repeated runs skip existing data. It
never runs during a frontend request and never overwrites existing records.

The Phase 5 import contains:

- 10 source-supported Client records used by the landing and Events adapters.
- 3 Events Service records.
- 5 Events Case Studies: Voltas Fam-Tastic Fiesta, GCA 2025, Zoetis Employee
  Engagement Day, Vision to Victory, and RunForEquity.
- 7 unique WordPress attachments reused across Service and Case Study featured
  images.
- 0 Studio Service records and 0 Team Member records.

Theme source images remain in place as fallback media. Publication-approved
masters can replace each featured image in WordPress without changing templates
or data relationships.

## Theme Consumption

The landing and Events adapters check for NICE Core helper functions and require
their complete expected record sets before switching to CMS output. When records
are complete, patterns render WordPress titles, excerpts, client relationships,
and attachment images. If the plugin is inactive or migration is partial, the
entire section uses its existing approved fallback rather than mixing sources.

Project-level proof numbers remain presentation copy because they are explicitly
paired with named projects and are not reusable company-wide statistics.

## Lifecycle and Safety

Activation registers the structures, creates only the approved taxonomy terms,
and flushes rewrites once. Deactivation flushes rewrites and preserves content.
Uninstall also preserves content and options pending a separate, explicitly
approved removal plan.

NICE Core adds no custom tables, frontend assets, custom REST namespace,
third-party dependency, form, page builder, or frontend route. Admin saves use
capability checks, nonces, sanitization, and registered predictable meta types.
