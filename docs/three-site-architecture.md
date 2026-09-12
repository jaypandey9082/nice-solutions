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

Run setup once the constants are in place, then flush permalinks. Either route
does the same work:

- **Tools → NICE Setup** in wp-admin, for a host with no shell. It shows the
  identity it resolved, refuses to run when the identity is missing or
  unrecognised on a production host, reports what it created, what was already
  there, and what belongs to another installation, and leaves every draft as a
  draft.
- `wp nice migrate-content`, where there is shell access.

Setup is scoped to the identity. An Events installation imports the Events
services, Events projects and Events team placeholders and nothing else; Studio
does the same with its own; the gateway imports neither and seeds its curated
previews instead. Clients are shared vocabulary and are created on both division
installations. Reruns create nothing and change nothing.

A division installation also gets a landing page carrying its division slug.
That page is what an administrator selects under Settings → Reading, and the
setup screen offers to select it. Its own section pages sit at the root beside
it.

## Gateway Previews

The gateway publishes no division content, so it cannot show a Case Study: that
record lives in another database, and an attachment ID from one installation
means nothing in another. It publishes **Gateway Projects** instead, a
dashboard-only content type holding its own title, summary, division, image,
display order and destination URL, with its image in its own Media Library.

A destination is accepted only when it lands on the selected division's own
case studies — `https://events.nicesolutions.in/case-studies/...` for an Events
preview. Anything else is discarded rather than stored, at the metadata layer
rather than only in the editing screen, so an import or a REST write faces the
same rule an editor does.

Setup seeds three drafts (Voltas, GCA, Strata). Publishing is an editorial act:
until one is published the front page carries no preview section at all, and the
section lays out correctly with one, two or three published entries.

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

## Packages

`npm run build:release` writes `nice-theme-<version>.zip`, `nice-core-<version>.zip`,
`SHA256SUMS.txt` and a release checklist into `output/releases/`, which is not
committed. The same commit produces byte-identical archives.

Packages deliberately exclude the repository's own machinery, the static design
preview, and the deck photography that has not been cleared for publication
(listed in `scripts/unapproved-media.json`). The theme renders its media
placeholder wherever an image is absent, so a production installation shows an
intentional gap rather than a broken image, and NICE fills it through the Media
Library.

## Verification

`wp eval-file scripts/wp-identity-check.php` asserts the behaviour of all four
identities — combined, Main, Events and Studio — against the running site. It
simulates each one through the `nice_site_division` filter rather than by editing
`wp-config.php`, so it is safe to run on the development installation, and it
covers URL resolution, content ownership, the gateway's refusal to import
division content, destination-URL restriction and source-provenance rules.

`wp eval-file scripts/wp-launch-readiness.php` reports the content side of the
acceptance criteria for whichever installation it is run on: what is configured,
what is approved, and what is still outstanding. It changes nothing.
