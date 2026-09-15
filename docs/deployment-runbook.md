# Deploying NICE

**The launch is one installation**: `nicesolutions.in`, serving the landing page
at the root, Events under `/events/` and Studio under `/studio/`. That is the
shape `nice-solutions.local` has always run, so production and the development
reference are finally the same thing.

The Events and Studio subdomain installations are **left untouched**. Nothing
here modifies them. Once the combined site is live they can be pointed at
`/events/` and `/studio/` with redirects, and only then do their certificates
matter.

The split shape — three installations, one per hostname — is still supported and
still tested. It is documented at the end for the day it is wanted.

## Before touching anything

1. **Change the three WordPress passwords.** They were shared over WhatsApp, so
   treat them as known.
2. **Take a complete DirectAdmin backup** — files, databases, email and settings
   — and **download it**. The visible backup is from September 2025 and is too
   small to trust. This is the only rollback.
3. **Switch Main to PHP 8.3.** The theme and plugin need 8.2 or newer; the
   account is on 7.4. Do the same for Events and Studio only when you get to
   them; nothing here needs it.
4. **SSL is already fine for this launch.** `nicesolutions.in` and
   `www.nicesolutions.in` are covered by the current Let's Encrypt certificate,
   valid to 20 Nov 2026. The subdomain certificates are a later job.
5. **Leave the Under Construction page up.** It stays up until the very last
   step, which is by design — see the ordering note below.

## Build the packages

```bash
npm run check && npm run check:phases && npm run check:install
npm run build:release
```

Produces in `output/releases/`:

- `nice-theme-0.9.0.zip`
- `nice-core-1.5.0.zip`
- `SHA256SUMS.txt` — verify with `shasum -a 256 -c SHA256SUMS.txt`
- `RELEASE-CHECKLIST.md`

The same commit always builds identical archives, so the checksum is worth
checking after upload.

## Deploy to nicesolutions.in

**The theme is activated last, on purpose.** Everything before it is invisible:
the plugin provisions pages and content while the Under Construction page is
still what the public sees. Activating the NICE theme is the single step that
switches the site over, so the site goes from "under construction" to "finished"
rather than spending an hour half-built in public.

**1. wp-config.php** — File Manager, FTP or SSH; WordPress has no editor for
this file. Above the `/* That's all, stop editing! */` line:

```php
define( 'NICE_SITE_DIVISION', 'combined' );
```

One line. No sibling URLs — there are no siblings, and every link is a path on
this host. **Setup refuses to run until this is set**, so that an installation
nobody has described is never guessed at.

**2. Settings → Reading** → tick *Discourage search engines*. Leave it until the
launch review.

**3. Settings → Permalinks** → **Post name** → Save. **Before setup.** A fresh
WordPress uses Plain permalinks, under which every NICE route 404s. Setup warns
if it finds them still set that way.

**4. Turn off page caching** (SpeedyCache) for the duration.

**5. Plugins → Add New → Upload** → `nice-core-1.5.0.zip` → activate.

**6. Appearance → Themes → Add New → Upload** → `nice-theme-0.9.0.zip` —
**upload only, do not activate yet.**

**7. Tools → NICE Setup.** Confirm it reports:

> Combined site, Events and Studio under /events/ and /studio/

Then run it. One run creates the vocabulary, both division landing pages, all
twelve inner pages, 6 services, 10 case studies, 10 clients, 6 team placeholder
drafts and 3 gateway preview drafts. Drafts stay drafts.

**8. Settings → NICE Contact** — both divisions' phone, WhatsApp and email, plus
the company social URLs.

**9. Media** — upload the approved imagery. On each Case Study: set the featured
image, give the attachment **alt text**, and tick *Media cleared for
publication*. Until that box is ticked the page shows a deliberate placeholder
rather than an unapproved photograph.

**10. Gateway Projects** — three drafts are waiting. Give each an approved image,
tick *Media cleared*, check the destination, publish. Until one is published the
landing page carries no preview section.

**11. Appearance → Themes → activate the NICE theme.** This replaces the Under
Construction page. The site is now live.

**12.** Re-enable the host cache.

## Verify

Every one of these over HTTPS, on desktop and on a phone:

| Route | Expect |
| --- | --- |
| `/` | Landing page, both division doors |
| `/events/` | Events home |
| `/events/services/` + a service | Three services, each opening |
| `/events/case-studies/` + a project | Grouped list, each opening |
| `/events/clients/` | Ten clients |
| `/events/about/` | Philosophy, then the team |
| `/events/contact/` | Contact details, **no form** |
| `/studio/…` | The same six, Studio's own |
| `/events/team/` | **301** to `/events/about/` |
| `/team/`, `/about/` | **404** |

Then: one `<h1>` per page, the menu opens and closes, contact and WhatsApp links
open the right app, and the layout holds at 320 and 390px.

With shell access, the full picture:

```bash
wp eval-file scripts/wp-launch-readiness.php
```

## Go public

Untick *Discourage search engines*, submit `nicesolutions.in` to Search Console,
and tag the verified commit:

```bash
git tag -a nice-launch-v1 -m "Verified in production" && git push origin nice-launch-v1
```

## If something breaks

Restore from the DirectAdmin backup taken above. Failing that, switching the
theme back is enough to restore the Under Construction page — the NICE content
sits in its own post types and pages, and deactivating does not delete it.

## Later: retiring the subdomains

Once the combined site is settled, issue certificates covering
`events.nicesolutions.in` and `studios.nicesolutions.in` and redirect:

```
events.nicesolutions.in  ->  nicesolutions.in/events/
studios.nicesolutions.in ->  nicesolutions.in/studio/
```

Keep the old installations until the redirects have been live long enough for
search engines to follow them.

## The split shape, if it is ever wanted

Three installations, each declaring itself and its siblings:

```php
define( 'NICE_SITE_DIVISION', 'events' );   // or 'studio', or 'main'
define( 'NICE_MAIN_SITE_URL', 'https://nicesolutions.in' );
define( 'NICE_EVENTS_SITE_URL', 'https://events.nicesolutions.in' );
define( 'NICE_STUDIO_SITE_URL', 'https://studios.nicesolutions.in' );
```

Each division's content then sits at its own root with no prefix, the gateway
owns no division content, and cross-site links resolve by hostname. Launch order
is Events, Studio, Main — the gateway last, because activating its theme
replaces the Under Construction page. `npm run check:install` verifies this shape
alongside the combined one.

Events and Studio currently run WordPress from a `/wp/` subdirectory, with a
placeholder `index.html` in the public root. That would need the standard
"WordPress in its own directory" configuration before either could serve at its
root — which is a good reason not to take this path without cause.

## Upgrading an installation that already ran an earlier release

Only relevant to the subdomain installations, and only if they are ever deployed
to. `nicesolutions.in` is a fresh NICE install, so none of this applies there.

The division Team page became the About page in theme 0.9.0. Setup carries the
existing page over rather than creating a second one: the post keeps its ID, its
revisions and anything an editor wrote, and only its slug, title and template
change. `/events/team/` then `301`s to `/events/about/`.

The rename only runs while there is no About page yet, so a rerun is safe. If an
installation somehow ends up with both, the Team page is the leftover — check
which one holds the real copy before deleting either.

## Checking what is still outstanding

With shell access, on any installation:

```bash
wp eval-file scripts/wp-launch-readiness.php
```

Read-only. It reports identity, contact and social details, services, project
imagery, hero images, team profiles, source citations and gateway previews for
whichever shape it runs on.
