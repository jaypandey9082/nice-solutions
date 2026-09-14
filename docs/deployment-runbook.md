# Deploying the three installations

One page per installation, in launch order: **Events, then Studio, then Main**.

Nothing here requires shell access. Every step is a file upload, a wp-config
edit, or a click in wp-admin.

## Before touching any of them

1. **Rotate the three WordPress passwords.** They were shared over WhatsApp, so
   treat them as known. Do this first; every step below assumes the new ones.
2. **Back up files and database for all three**, at host level rather than
   through the expired Backuply licence, and **restore one of them somewhere** to
   prove the backup works. An untested backup is not a rollback plan.
3. **Fix DNS and SSL** so all three hostnames load over HTTPS with no warning.
   `events.nicesolutions.in` currently serves an invalid certificate and
   `studios.nicesolutions.in` is not reliably reachable.
4. **Leave the Main Under Construction page up.** Main is launched last.

Do not change the WordPress Address or Site Address. Events and Studio run
WordPress from a `/wp/` subdirectory — that is why their admin is at
`/wp/wp-admin/` — and the deployment does not need it altered.

## Build the packages

```bash
npm run check && npm run check:phases
npm run build:release
```

Produces in `output/releases/`:

- `nice-theme-0.9.0.zip`
- `nice-core-1.4.0.zip`
- `SHA256SUMS.txt` — verify with `shasum -a 256 -c SHA256SUMS.txt`
- `RELEASE-CHECKLIST.md`

The same commit always builds identical archives, so the checksum is worth
checking after upload.

## 1. Events — events.nicesolutions.in

**wp-config.php**, above the `/* That's all, stop editing! */` line:

```php
define( 'NICE_SITE_DIVISION', 'events' );
define( 'NICE_MAIN_SITE_URL', 'https://nicesolutions.in' );
define( 'NICE_EVENTS_SITE_URL', 'https://events.nicesolutions.in' );
define( 'NICE_STUDIO_SITE_URL', 'https://studios.nicesolutions.in' );
```

Then, in wp-admin:

1. **Settings → Reading**: tick *Discourage search engines*. Leave it ticked
   until the launch review.
2. **Settings → Permalinks**: choose **Post name** and save. Do this *before*
   setup. A fresh WordPress uses Plain permalinks, under which every NICE route
   404s; the setup screen warns if it finds them still set that way.
3. **Plugins → Add New → Upload**: `nice-core-1.4.0.zip`, activate.
4. **Appearance → Themes → Add New → Upload**: `nice-theme-0.9.0.zip`, activate.
5. **Tools → NICE Setup**. It shows the identity it resolved — confirm it says
   *NICE Events* before running. It refuses outright if the constant is missing
   or misspelled. Leave *set the generated home page as the front page* ticked.
6. **Settings → Reading**: confirm the front page is now *Events*.
7. **Settings → NICE Contact**: enter the Events phone, WhatsApp and email, and
   the company social URLs.
8. **Media**: upload the approved project imagery. On each Case Study, set the
   featured image, give the attachment alt text, and tick *Media cleared for
   publication*. Until that box is ticked the page shows a deliberate
   placeholder rather than an unapproved photograph.
9. Disable page caching while doing all of the above; re-enable the host's own
   cache once the site looks right.

Then check: the home page loads over HTTPS, the menu works, Services and Case
Studies list, contact links open the right app, and a Studio record 404s rather
than rendering.

## 2. Studio — studios.nicesolutions.in

Identical, with `define( 'NICE_SITE_DIVISION', 'studio' );` and the Studio
contact details. Setup will confirm *NICE Studio* before it runs.

## 3. Main — nicesolutions.in

Main is the gateway. It owns no Services, Case Studies or Team Members, and
setup will say so.

```php
define( 'NICE_SITE_DIVISION', 'main' );
define( 'NICE_MAIN_SITE_URL', 'https://nicesolutions.in' );
define( 'NICE_EVENTS_SITE_URL', 'https://events.nicesolutions.in' );
define( 'NICE_STUDIO_SITE_URL', 'https://studios.nicesolutions.in' );
```

1. Upload and activate NICE Core, then the NICE theme. Activating the theme
   replaces the Under Construction page, so do this only when Events and Studio
   are both verified.
2. **Tools → NICE Setup**. Confirm it says *Main gateway*.
3. **Settings → Permalinks**: save once.
4. **Settings → NICE Contact**: social URLs, and company contact details if
   there are any distinct from the two divisions.
5. **Gateway Projects**: three drafts are waiting — Voltas, GCA, Strata. Give
   each an approved image, tick *Media cleared for publication*, check the
   destination points at the right division, and publish. Until one is
   published the home page simply carries no preview section.
6. Remove Elementor, Pagelayer and the expired SoftWP components **only** after
   confirming nothing else uses them, and only with the rollback backup in hand.

## Upgrading an installation that already ran an earlier release

The division Team page became the About page in theme 0.9.0 / NICE Core 1.4.0.
Setup carries the existing page over rather than creating a second one: the post
keeps its ID, its revisions and anything an editor wrote, and only its slug,
title and template change. `/events/team/` and `/studio/team/` then `301` to
`/…/about/`.

That rename only runs while there is no About page yet, so a rerun is safe. If
an installation somehow ends up with both, the Team page is the leftover — check
which one holds the real copy before deleting either.

Confirm after Setup:

- [ ] `/…/about/` returns 200 and shows the philosophy, then the team section.
- [ ] `/…/team/` redirects to it rather than 404ing.
- [ ] "About" appears in the navigation bar and the phone menu.

## After each launch

Check over HTTPS: home page, one H1, menus, contact and WhatsApp links,
canonical URLs, sitemap, mobile layout at 320 and 390px, and back/forward
navigation. Then move to the next site — do not launch all three at once.

When all three are verified: untick *Discourage search engines* on each, submit
each hostname separately to Search Console, and tag the verified commit
`nice-launch-v1`.

## If something breaks

Restore that one installation's files and database from its own pre-launch
backup. Each site is independent; a problem on Studio is not a reason to roll
back Events.

## Checking what is still outstanding

With shell access:

```bash
wp eval-file scripts/wp-launch-readiness.php
```

Read-only. It reports identity, sibling URLs, contact and social details,
services, project imagery, hero images, team profiles, source citations and
gateway previews for whichever installation it runs on.
