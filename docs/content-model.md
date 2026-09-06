# NICE Core Content Model

NICE Core `1.2.0` owns reusable NICE business content. The NICE block theme owns
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
    |-- routes.php
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

Native CPT archives and rewrites remain disabled. NICE Core registers only the
controlled Events detail routes and filters Events post permalinks to their
canonical paths. Raw CPT paths return `404`; raw query requests redirect to the
matching canonical Events URL. Phase 7 creates the structural `/studio/` Page
only. Future Studio inner paths remain unregistered and return `404` without
being guessed or redirected to Events content.

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
| `_nice_proof_value` | string | Optional project-specific source-approved metric |
| `_nice_proof_label` | string | Context that keeps the metric attached to its project |

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
nice_get_events_content_url()
nice_is_events_content()
```

Content query helpers return arrays of `WP_Post` objects; singular helpers return
`WP_Post|null`. Convenience query arguments include `division`, `service_type`,
and `featured`. No transient or custom cache layer is added.

## Migration

Run the migration after plugin activation:

```bash
wp nice migrate-content
```

The command creates missing approved terms, Clients, Services, Case Studies,
media attachments, the five approved child Pages below `/events/`, and the
Studio Home Page. It
identifies records by post type and slug and attachments by
`_nice_source_asset`, so repeated runs skip existing data. Phase 6 enriches only
untouched Phase 5 editor content and empty source-backed fields; later editorial
changes are never overwritten.

The migration now contains:

- 10 source-supported Client records used by the landing and Events adapters.
- 3 Events Service records.
- 3 Studio Service records: Corporate Videos, Digital Content Creation, and
  Films & Entertainment.
- 5 Events Case Studies: Voltas Fam-Tastic Fiesta, GCA 2025, Zoetis Employee
  Engagement Day, Vision to Victory, and RunForEquity.
- 5 Studio Case Studies: Strata Geosystems Factory Shoot, Career Agents Academy,
  Krish-e, CRISIL Financial Literacy Content, and Jayanti.
- 12 unique WordPress attachments reused across Service and Case Study featured
  images.
- 5 structural Events Pages with their assigned block-theme templates.
- 1 structural Studio Home Page with its assigned block-theme template.
- 0 Team Member records.

Theme source images remain in place as fallback media. Publication-approved
masters can replace each featured image in WordPress without changing templates
or data relationships.

## Theme Consumption

The landing, Events Home, and Studio Home adapters check for NICE Core helper
functions before switching to CMS output. Events inner pages and Studio Home use
server-rendered theme blocks so current CMS records render on every request.
Services use editor content for capabilities; Case Studies use editor narratives,
relationships, taxonomies, metadata, and featured images. The existing approved
landing and Events fallbacks remain intact. Studio uses its source-backed hero
fallback when NICE Core is unavailable and intentional empty states when a CMS
section is incomplete; it does not keep a duplicate Studio portfolio in PHP.

Project-level proof lives on the related Case Study record and renders only when
both value and context exist. It is never presented as a company-wide statistic.

## Lifecycle and Safety

Activation registers the structures and controlled Events routes, creates only
the approved taxonomy terms, and flushes rewrites once. The explicit migration
also flushes once when it creates structural Pages. Canonical guessing is
disabled below the unimplemented `/studio/*` namespace so future paths cannot
leak into Events. Deactivation flushes
rewrites and preserves content. Uninstall preserves content and options pending
a separate, explicitly approved removal plan.

NICE Core adds no custom tables, frontend assets, custom REST namespace,
third-party dependency, form, or page builder. Admin saves use capability
checks, nonces, sanitization, and registered predictable meta types.
