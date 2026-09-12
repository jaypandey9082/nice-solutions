# NICE Three-Site Architecture

The confirmed production platform consists of three independent WordPress
installations:

| Identity | Host | Content owner |
| --- | --- | --- |
| Main | `nicesolutions.in` | Gateway content and curated division previews |
| Events | `events.nicesolutions.in` | Events services, projects, clients, people, and contact |
| Studio | `studios.nicesolutions.in` | Studio services, projects, clients, people, and contact |

The Studio brand remains **NICE Studio**. The hostname uses **studios**, plural.

## Development Reference

`nice-solutions.local` intentionally keeps the combined paths `/events/` and
`/studio/`. It is the design, CMS, accessibility, and regression reference. Its
path structure must not be treated as the final production canonical structure.

## Configuring an Installation

The codebase supports both shapes. NICE Core reads which division an
installation serves from wp-config.php and derives every path from it:

```php
define( 'NICE_SITE_DIVISION', 'main' );             // on the gateway
define( 'NICE_SITE_DIVISION', 'events' );          // on the Events installation
define( 'NICE_MAIN_SITE_URL', 'https://nicesolutions.in' );
define( 'NICE_EVENTS_SITE_URL', 'https://events.nicesolutions.in' );
define( 'NICE_STUDIO_SITE_URL', 'https://studios.nicesolutions.in' );
```

Every production installation declares itself, including the gateway. Omitting the
constant means the combined development site, which is why `nice-solutions.local`
needs no configuration and stays the reference. Omitting it in production would
publish both divisions from one database.

The gateway owns no division content: it registers no division routes, provisions
no division pages, refuses `wp nice migrate-content`, and resolves Events and
Studio links against `NICE_EVENTS_SITE_URL` and `NICE_STUDIO_SITE_URL`. Its landing
page is static and curated, so it needs no division records at all.

A division installation registers its rewrite rules without the prefix, resolves
its own content against its own host, links the sibling division by absolute
URL, treats every page as belonging to its division, and provisions the section
pages at the root rather than under a landing page. Requests for the sibling's
records return 404.

Run `wp nice migrate-content` and flush permalinks after setting it.

## Ownership Rules

- Each installation owns its database, media, users, settings, sitemap, backup,
  deployment, and rollback.
- No cross-site query may depend on shared post IDs or attachment IDs.
- The Main site stores curated project previews locally and links to full work on
  the owning division site.
- Events and Studio use local production paths such as `/services/` and
  `/case-studies/` after deployment packaging.
- Shared code is released independently. A shared theme does not imply a shared
  deployment schedule.
- Cross-site URLs must be explicit environment configuration, never inferred from
  the development path.

## Release Boundary

This repository does not perform an implicit migration. Before production, each
site receives an inventory, export/import mapping, host-specific URL configuration,
canonical and sitemap checks, a backup, and an independent rollback package.
