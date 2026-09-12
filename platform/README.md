# NICE Platform: Stage 1

This directory contains the new three-installation platform. Stage 1 delivers the
Main gateway and shared foundation for review. Events and Studio are development
skeletons; their division designs and full project pages belong to Stages 2 and 3.

This is not WordPress Multisite. Each installation owns its database, database
user, admin accounts, uploads, configuration, content, and settings. Sharing theme
and plugin source does not share content or credentials.

## Sites and boundaries

| Role | Local development | Confirmed production |
| --- | --- | --- |
| `main` | <http://localhost:8180/> | <https://nicesolutions.in/> |
| `events` | <http://localhost:8181/> | <https://events.nicesolutions.in/> |
| `studio` | <http://localhost:8182/> | <https://studios.nicesolutions.in/> |

The brand is NICE Studio; the production hostname is **studios**, plural. Each
division's production paths start at its own host, for example `/contact/` and
eventually `/case-studies/{slug}/`. Do not add `/events/` or `/studio/` prefixes to
these new origins.

The existing `nice-solutions.local` installation and repository-level legacy
`wp-content/` remain the reference implementation. The new runtime does not load
its configuration, connect to its database, or copy its content/uploads. It reuses
installed WordPress core bytes and the bundled WordPress fallback theme only.
No content migration, legacy cleanup, redirects, or production deployment is
included in Stage 1.

Main's visitor journey is: gateway -> project preview or division -> division
site -> relevant work/service -> contact. Work, About, and Contact in the Main
navigation initially address sections on the gateway. Events/Studio links leave
for their configured origins in the same tab.

## Layout and runtime

| Path | Ownership |
| --- | --- |
| `theme/` | Shared NICE Platform theme; installed as `nice-platform` |
| `plugin/` | NICE Platform Core; entry point `nice-platform-core.php` |
| `instances/{main,events,studio}.json` | Role, local port, package names, production URL |
| `reference-assets/` | Temporary generated imagery for Main introductions only |
| `scripts/` | Provisioning, lifecycle, package, WP-CLI, and reference-import commands |
| `tests/` | Runtime and CMS checks; existence is not proof they have passed |
| `.runtime/{role}/public/` | Separate WordPress document root |
| `.runtime/{role}/public/wp-content/uploads/` | That installation's Media Library files |
| `.runtime/{role}/private.json` | Private local admin/database credentials and salts |
| `.runtime/mysql/` and `.runtime/mysql.sock` | Dedicated local MySQL data and socket |
| `.runtime/logs/` | Private installation and server logs |
| `.packages/` | Generated distributable artifacts; ignored by Git |

Development uses one dedicated socket-only MySQL process with three distinct
databases and users, separate from Local's database process. PHP serves each
document root on its own loopback port. Stopping one PHP site should leave its
siblings available; stopping the shared new MySQL process stops database access
for all three. Neither action is intended to affect the reference Local site.

Local prerequisites are PHP 8.2+, Local's MySQL binary, Local's WP-CLI PHAR, and
installed WordPress core. The currently discovered PHP binary is:

```text
/Users/jaypandey/Library/Application Support/Local/lightning-services/php-8.2.30+1/bin/darwin-arm64/bin/php
```

Dependency overrides are `NICE_PHP_BIN`, `NICE_MYSQLD_BIN`, `NICE_WP_CLI`, and
`NICE_CORE_SOURCE`. They select runtime binaries/core files, not another site's
credentials. The default core source is the installed Local site's `app/public`
directory; only explicitly selected core files and its bundled default theme are
copied. Do not point the scripts at a production installation.

Generated local configuration sets `NICE_SITE_ROLE`, all three `NICE_*_URL`
constants, `WP_ENVIRONMENT_TYPE=local`, and local `WP_HOME`/`WP_SITEURL`. Provisioning
sets `blog_public=0`. Development indexing exclusion is not authentication; keep
these servers on loopback.

## Commands

Run commands from the repository root. These are operational instructions, not a
record of completed tests. Provisioning and activation change only the new runtime.

```bash
# Provision independent roots/databases, then start all three servers.
bash platform/scripts/provision.sh
bash platform/scripts/start.sh
bash platform/scripts/runtime.sh status

# Run only after the shared plugin and theme owners confirm readiness.
bash platform/scripts/runtime.sh activate main
bash platform/scripts/runtime.sh activate events
bash platform/scripts/runtime.sh activate studio

# Explicit Main-only content/bootstrap operations, after CMS readiness.
bash platform/scripts/runtime.sh seed-main --plugin-ready
bash platform/scripts/runtime.sh import-references

# Examples of scoped WP-CLI queries.
bash platform/scripts/wp.sh main option get page_on_front
bash platform/scripts/wp.sh events option get blog_public
bash platform/scripts/wp.sh studio option get blog_public

# Stop/restart just one site; no database reset or content removal.
bash platform/scripts/stop.sh events
bash platform/scripts/start.sh events

# Stop all new sites and their dedicated MySQL process.
bash platform/scripts/stop.sh
```

The reference import assigns two generated attachments to three slots on Main:
event imagery in Hero and Events, studio imagery in Studio. It never assigns
reference images to named project previews or the division skeletons. The import
is a one-time bootstrap; subsequent runs preserve editor selections. Use WordPress
to replace or remove images afterwards.

The runtime uses owned process identifiers and refuses occupied ports; it must
not terminate unrelated listeners. If a port is occupied, resolve the collision
before starting. Keep the runtime tied to its original checkout; do not copy its
database directory into another checkout. No reset/drop command is provided.

## Admin and editing workflow

Open `/wp-admin/` on the relevant local origin. Each installation has separate
credentials in `.runtime/{role}/private.json`, outside the served document root.
Access this file locally and privately; never paste its contents into chat,
terminal transcripts, screenshots, commits, or deployment packages. Production
credentials must be provisioned independently.

1. **Homepage copy:** in Main, open Pages and edit the configured static homepage
   (normally Home). Find **NICE Page Content and Media**. Edit the hero, division
   descriptions, company introduction, and Brief/Idea/Solution copy, then Update.
   Approved client names are entered one per line. Empty optional fields remain
   empty; do not invent quotations, results, client relationships, or contacts.
2. **Homepage images:** use Select/Replace/Remove for Hero, Events, and Studio.
   Choose an image from Main's Media Library, optionally choose a separate mobile
   crop, and set horizontal/vertical focal points. Desktop guidance is landscape,
   ideally 16:9 and at least 2000px wide; mobile guidance is portrait 4:5. Set
   attachment alt text in Media Library. Keep **Temporary reference imagery**
   checked for generated references; clear it only when using approved real media.
   Update, reopen, and inspect both screen sizes. Remove is an intentional empty
   selection, not a request for a theme-photo fallback.
3. **Selected work:** use **Work Previews** in Main. Edit Title, Excerpt, Division,
   and **NICE Project Destination**. The first three published previews appear,
   ordered by Page Attributes > Order, with ID breaking ties. Keep both divisions
   represented. Leave Featured Image empty until approved project media exists.
4. **Project destinations:** prefer a division-local Project path such as
   `/case-studies/project-name/`; the configured division origin is applied. An
   optional absolute URL must match that division. Main previews are manually
   curated and never fetch division databases or automatically mirror their edits.
5. **Contact channels:** on each site, an administrator opens Settings > NICE
   Platform. Save only approved email, phone, WhatsApp, and social destinations.
   Empty contact channels stay absent. These settings do not synchronize between
   installations.

Events and Studio currently have only skeleton responsibilities, including a
minimal `/contact/` Page with their site identity and a return to Main. This is
not a completed enquiry journey. Seeded project destinations describe intended
future pages; those pages await Stages 2/3 and may return 404. Do not present these
destinations as published or tested, or manufacture project content to fill them.
Gallery editing on full division case studies belongs to those later stages.

## Verification and review boundary

```bash
# Read-only runtime/database/HTTP probes against running local instances.
bash platform/scripts/check.sh

# Also stop/restart each site in turn; coordinate with ongoing visual review.
bash platform/scripts/check.sh --lifecycle
```

The checks are intended to cover distinct roles/databases/uploads, cross-database
permission denial, local origins, indexing exclusion, homepage/login/contact
responses, and sibling availability during lifecycle checks. Commands alone do
not establish passing results. Record actual output and any failures separately.

Runtime verification and router fixes are owned by the implementation lead.
This documentation update does not certify those checks, admin browser editing,
mobile layouts, animation smoothness, accessibility, or production readiness.
The lead's Stage 1 handoff must identify what passed, what remains unverified,
and provide the Main review URL plus desktop/mobile screenshots. Stage 2/3
redesigns and project destination validation remain outside that handoff.

## Independent deployment

```bash
# Creates local artifacts only; does not publish any site.
bash platform/scripts/package.sh
```

The command prepares theme/plugin archives, three manifests, and per-role PHP
configuration fragments under `.packages/`. It does not package databases,
uploads, generated runtime credentials, WordPress core, or reference assets.
Media and editorial content need a separately scoped transfer if later approved.

Install each package copy into its own WordPress installation. Configure its
database and secrets independently, then apply its role and the exact production
origins from the manifests before WordPress loads. Review rather than overwrite
an existing `wp-config.php`. Shared source can be released to one installation at
a time; local symlinks are a development convenience, not production deployment.

Each site needs its own backup, rollback, domain/TLS setup, canonical URL, sitemap,
and indexing review. The package command does not perform these operations or
copy the Local reference content. No production deployment or migration has been
performed by this documentation task.

## Fonts and attribution

The theme self-hosts Montserrat, Cabin, and Roboto Condensed WOFF2 files under
`theme/assets/fonts/`. No additional Open Sans family is part of this system.

| Family | Upstream attribution | License source |
| --- | --- | --- |
| Montserrat | Copyright 2024 The Montserrat.Git Project Authors | [Montserrat OFL](https://raw.githubusercontent.com/google/fonts/main/ofl/montserrat/OFL.txt) |
| Cabin | Copyright 2018 The Cabin Project Authors | [Cabin OFL](https://raw.githubusercontent.com/google/fonts/main/ofl/cabin/OFL.txt) |
| Roboto Condensed | Copyright 2011 The Roboto Project Authors | [Roboto Condensed OFL](https://raw.githubusercontent.com/google/fonts/main/ofl/robotocondensed/OFL.txt) |

These upstream families use SIL Open Font License 1.1. Preserve the applicable
copyright notices and full license with redistributed font files. The linked
upstream notices are attribution references, not a substitute for packaged license
copies. Before distribution, the theme owner must verify the supplied binaries'
provenance and include their corresponding notices/license files inside the theme
package. That packaging check is not claimed complete here.
