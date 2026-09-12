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
