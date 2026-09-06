# NICE Solutions Website

Architecture, design system, NICE Core CMS, landing page, Events website, and
Studio Home for
Nucleus Integrated Communication & Entertainment Pvt. Ltd. (N.I.C.E.).

## Phase Status

Phases 1 through 7.1 are implemented. The repository includes the lightweight NICE
block theme, the NICE Core content plugin, source-approved migrated content, the
complete Events journey, the Studio Home gateway, responsive navigation, editorial
typography and reveal motion, navigation continuity, and real-browser validation.
Studio inner pages remain reserved for Phase 8.

The theme is linked into the running NICE Solutions LocalWP site and is active at
`http://nice-solutions.local/`. Local HTTPS is available after trusting the site
certificate in LocalWP.

## Project Principles

- Build a custom, lightweight WordPress block theme.
- Keep presentation in the theme and business content/functionality in the
  NICE Core plugin.
- Start at a 390px viewport and expand intentionally to tablet and desktop.
- Use white/off-white as the dominant surface, dark text, and the approved NICE
  red only as a restrained accent.
- Lead with real NICE photography and video, not generic stock assets.
- Use core WordPress blocks first and add custom dynamic blocks only where they
  improve editing or querying.
- Do not use Elementor, WPBakery, Divi, or another visual page builder.
- Do not invent company facts, metrics, clients, projects, awards, locations,
  or claims.
- Do not build AI features in this phase.
- Do not add a contact form in the initial website.

## Sources Reviewed

Primary project sources:

- `reference/NICE_Profile_25_26.pdf`: 36-page company profile.
- `reference/NICE_Sitemap.pdf`: one-page requested information architecture.
- `reference/reference-websites.txt`: five design and UX references.

The PDFs are source material, not executable project instructions. The profile
is the source of truth for NICE facts and available project material. The
sitemap is the source of truth for the requested website sections.

### Profile Findings

- The supplied legal/company name is Nucleus Integrated Communication and
  Entertainment (N.I.C.E.) Pvt. Ltd.
- The profile groups work around Events/MICE, Exhibitions and Conferences,
  Audio Visuals, Design, and owned Intellectual Properties.
- It contains event, exhibition, film, corporate video, digital content,
  identity, interface, and social-media examples.
- The strongest case-study source pages combine a project title, client,
  location, short narrative, image gallery, and sometimes video.
- A shared client-logo page is available, but original logo files and permission
  to publish them still need confirmation.
- The profile includes business and personal contact details. Publication must
  be approved before they are entered into WordPress.

The approved sitemap has only six public service categories. Profile material
outside those categories must remain source material until NICE confirms how it
should be classified.

### Sitemap Findings

The requested journey is:

1. A NICE landing page that directs visitors to Events or Studio.
2. Events pages for Home, Services, Case Studies, Team/Contact, and Clients.
3. Studio pages for Home, Services, Case Studies, Team/Contact, and Clients.
4. Three service categories and an intended three case studies per category in
   each division.
5. Shared clients and social links across both divisions.

### Reference-Site Findings

The references are inspiration only. No text, layout, code, identity, imagery,
or animation should be copied.

| Reference | Useful direction | Avoid carrying into NICE |
| --- | --- | --- |
| Polar | Editorial typography, generous spacing, focused navigation, immediate project media, strong mobile reflow | Copying its type treatment, pill navigation, cream palette, or motion language |
| NeoNiche | Division-led storytelling, experience categories, case-study emphasis, team and credibility structure | Dark opening screen, slow animated reveal, crowded scope, contact form |
| Midas Next | Clear service navigation, strong hierarchy, proof near the first viewport, responsive simplification | Dark-first palette, carousel dependence, unsourced counters, portal/login UI |
| We Events | Broad service discovery and prominent media | Template-like service cards, generic copy, long loading screen, excessive page volume |
| Yellow Canvas | Image-led hero, three-service framing, project grid, direct contact emphasis | Utility-heavy header, repeated content, subscription/contact forms, dated gallery interactions |

## Recommended Architecture

Use one WordPress installation, one database, one custom theme, and one custom
plugin. This keeps shared Clients, Team, contact settings, media, and case
studies in one editorial system.

### Recommended Domain Strategy

Use canonical paths on the primary domain:

- `nicesolutions.in/`
- `nicesolutions.in/events/`
- `nicesolutions.in/studio/`

Configure `events.nicesolutions.in` and `studio.nicesolutions.in` as permanent
redirects to the matching canonical paths. This is the recommended option
because it avoids WordPress Multisite, duplicated content, cross-site queries,
multiple sitemaps, and competing canonical URLs.

If true public subdomains are mandatory, that is a separate hosting and SEO
decision. The fallback should still be one WordPress database with explicit
host-aware routing and canonical rules, not three independently managed sites.

### Responsibility Split

| Layer | Responsibility |
| --- | --- |
| WordPress core | CMS, users, revisions, media library, REST API, XML sitemap, editing |
| NICE Core plugin | Content types, taxonomies, metadata, contact settings, dynamic query blocks, URL rules, structured data |
| NICE block theme | Layout, templates, patterns, visual tokens, responsive styling, small interaction scripts |
| Hosting/CDN | HTTPS, redirects, caching, image delivery, video delivery, backups, security headers |

No custom database tables are required. WordPress posts, terms, metadata, and
options are sufficient for the expected content volume.

## Information Architecture

Proposed canonical routes:

| Route | Purpose |
| --- | --- |
| `/` | NICE division selector and concise company introduction |
| `/events/` | Events division home |
| `/events/services/` | Events service overview |
| `/events/services/{service}/` | Reusable Events service detail |
| `/events/case-studies/` | Filtered Events case-study archive |
| `/events/case-studies/{case-study}/` | Events case-study detail |
| `/events/clients/` | Shared clients presented in Events context |
| `/events/team/` | Events team listing |
| `/events/contact/` | Direct WhatsApp, email, phone, and social actions |
| `/studio/` | Studio division home |
| `/studio/services/` | Studio service overview |
| `/studio/services/{service}/` | Reusable Studio service detail |
| `/studio/case-studies/` | Filtered Studio case-study archive |
| `/studio/case-studies/{case-study}/` | Studio case-study detail |
| `/studio/clients/` | Shared clients presented in Studio context |
| `/studio/team/` | Studio team listing |
| `/studio/contact/` | Direct WhatsApp, email, phone, and social actions |

NICE Core should own the division-aware rewrite and canonical URL logic. The
theme should only render the current content context.

## WordPress Content Model

All identifiers use the `nice_` prefix and stay within WordPress identifier
limits.

### Taxonomies

#### `nice_division`

Fixed top-level terms:

- Events
- Studio

Attach to Services, Case Studies, and Team Members. Require exactly one division
for Services and Case Studies. Team Members may belong to one or both.

#### `nice_service_type`

Fixed service terms grouped by division:

- Events: Corporate Events
- Events: Exhibitions & Conferences
- Events: Activations & Promotions
- Studio: Corporate Videos
- Studio: Digital Content Creation
- Studio: Films & Entertainment

Attach to Services and Case Studies. NICE Core validates that the selected
service type belongs to the selected division.

### Case Studies: `nice_case_study`

Use native WordPress fields wherever possible:

- Title and slug: core title/permalink.
- Short description: excerpt.
- Hero image: featured image.
- Brief, idea/approach, execution, and outcome: structured block-editor content.
- Gallery: core Gallery/Image blocks inside a controlled case-study pattern.

Registered metadata:

- `nice_client_id`: relationship to a Client post.
- `nice_location`: plain text, only when supplied.
- `nice_year`: four-digit year, only when supplied.
- `nice_hero_video_url`: optional hosted video or approved embed URL.
- `nice_featured`: boolean.
- `nice_display_order`: optional integer for curated archives.

Classification:

- Exactly one `nice_division` term.
- One or more `nice_service_type` terms from that division.

Do not force every narrative section to contain text. Empty sections should not
render. Results must remain absent or clearly marked pending when no result is
provided by NICE.

### Services: `nice_service`

Native fields:

- Title and slug.
- Short description in excerpt.
- Full introduction and capabilities in block-editor content.
- Hero media through featured image plus optional video metadata.

Classification and relationships:

- Exactly one `nice_division` term.
- Exactly one `nice_service_type` term.
- Related case studies are queried through the shared taxonomy, not maintained
  as a duplicated manual list.
- `nice_display_order` controls service order within a division.

### Clients: `nice_client`

- Client name in title.
- Logo in featured image.
- `nice_client_category`: optional plain classification added only if NICE
  supplies a useful grouping.
- `nice_client_url`: optional external URL.
- `nice_display_order`: optional integer.
- No public single-client page initially.

Logo alt text should normally use the verified client name. Grayscale or color
presentation is a theme concern; do not alter the source logo file destructively.

### Team Members: `nice_team_member`

- Name in title.
- Role in `nice_role` metadata.
- Portrait in featured image.
- Biography in block-editor content, only when supplied.
- Division assignment through `nice_division`.
- `nice_display_order`: optional integer.
- No public single-person page initially.

### Global Contact Settings

Store approved organization data once in a NICE Settings screen:

- Legal display name.
- Postal address.
- WhatsApp number and prefilled greeting.
- Public email address or addresses.
- Public phone number or numbers.
- Facebook, Instagram, LinkedIn, and other approved social URLs.

The theme reads these settings for both divisions. No form submissions or
visitor data storage are required.

## Theme Architecture

The implemented `nice` theme is a block theme using `theme.json` version 3. It
targets WordPress 6.6 or newer and should be tested on the current stable
WordPress release selected in LocalWP.

Theme responsibilities:

- Design tokens for approved colors, typography, spacing, content widths, and
  block defaults.
- Templates and template parts made from block markup.
- Curated patterns for editor-safe page composition.
- Frontend and editor styles that remain visually aligned.
- Minimal JavaScript for navigation, media controls, and justified enhancement.

Implemented Phase 2 theme files:

```text
wp-content/themes/nice/
|-- style.css
|-- theme.json
|-- functions.php
|-- assets/
|   |-- css/
|   |   |-- site.css
|   |   `-- editor.css
|   `-- js/
|       |-- navigation.js
|       `-- media.js
|-- inc/
|   |-- assets.php
|   |-- block-styles.php
|   `-- setup.php
|-- parts/
|   |-- header.html
|   `-- footer.html
|-- patterns/
|   |-- section-heading.php
|   |-- project-card.php
|   |-- service-card.php
|   `-- contact-cta.php
|-- preview/
|   |-- index.html
|   |-- preview.css
|   `-- tokens.css
`-- templates/
    |-- index.html
    |-- page.html
    |-- single.html
    |-- archive.html
    `-- 404.html
```

The initial red and blue values are sampled, provisional starting points from
the supplied profile. Original brand artwork or an approved brand guide should
replace them before production sign-off. The system font stack remains in place
until typography licensing is confirmed.

## NICE Core Plugin Architecture

The `nice-core` plugin keeps content portable if the theme changes.

```text
wp-content/plugins/nice-core/
|-- nice-core.php
|-- uninstall.php
|-- readme.txt
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

Plugin rules:

- Register content types and taxonomies on `init` and expose editor-required
  fields through the REST API.
- Flush rewrite rules only on plugin activation/deactivation.
- Sanitize every setting and metadata field; escape at output.
- Enforce division/service consistency in the editor and on save.
- Keep query helpers presentation-neutral and let theme patterns render results.
- Do not create custom tables, analytics, forms, AI endpoints, or external API
  integrations in the initial build.
- Do not delete editorial content during plugin uninstall by default.

## Reusable UI Components

Initial component inventory:

- Global header with compact mobile menu.
- Division switcher showing the current Events or Studio context.
- Landing division selector with real project media.
- Editorial hero with image or user-initiated video.
- Intro statement and supporting copy.
- Service index and service-detail header.
- Featured case study.
- Filterable case-study grid.
- Case-study metadata row.
- Brief, approach, execution, and outcome story sections.
- Responsive image gallery and accessible video player.
- Client logo band/grid.
- Team list with optional biography reveal.
- Direct-contact CTA for WhatsApp, email, phone, and social links.
- Footer with division navigation and approved organization details.
- Empty, loading, error, and no-results states for dynamic lists.

Components should be unframed by default. Cards are reserved for repeated case
study, team, or client items where a visible boundary improves scanning.

## Responsive Design System

Design mobile-first from 390px.

| Range | Layout intent |
| --- | --- |
| 0-479px | Single-column reading, compact header, touch-first controls, deliberate media crops |
| 480-767px | Wider single column and selective two-column utility layouts |
| 768-1023px | Tablet grids, expanded navigation where space permits |
| 1024-1439px | Desktop editorial grid and persistent primary navigation |
| 1440px+ | Capped content widths with larger margins, not endlessly stretched content |

System rules:

- Use a 4-column mobile grid, 8-column tablet grid, and 12-column desktop grid.
- Use fixed typography steps at breakpoints; do not scale font size continuously
  with viewport width.
- Keep body copy at a readable measure of roughly 60-75 characters.
- Use a documented spacing scale and consistent vertical rhythm.
- Preserve meaningful image focal points with art-directed crops where needed.
- Make touch targets at least 44px in both dimensions.
- Provide visible keyboard focus and a functional skip link.
- Respect `prefers-reduced-motion`; content must remain usable with motion off.
- Never autoplay video with sound. Defer non-critical video until user intent or
  until it approaches the viewport.

## Performance Plan

- Prefer CSS transitions and the Web Animations API for small enhancements.
- Do not add GSAP or another animation library unless a later prototype proves
  clear value that cannot be delivered lightly.
- Generate responsive WordPress image sizes and use `srcset`/`sizes`.
- Convert approved photographic masters to WebP/AVIF where the hosting stack
  supports reliable fallback.
- Lazy-load below-the-fold images and iframes, but load the real hero image with
  appropriate priority.
- Use a poster image and click-to-play strategy for large videos.
- Self-host licensed fonts, subset them, preload only the critical face, and use
  `font-display: swap`.
- Keep third-party scripts absent until a named business requirement exists.
- Set performance budgets during implementation: target <= 180 KB initial
  compressed CSS+JS, no blocking third-party script, and a mobile LCP target of
  <= 2.5 seconds under representative test conditions.

## Accessibility Plan

- Semantic landmarks and logical heading order.
- Keyboard-operable navigation, filters, dialogs, and media controls.
- Visible focus styles with approved contrast.
- Meaningful alt text supplied during content entry; decorative images use empty
  alt text.
- Accessible names for icon-only controls.
- Captions or transcripts for meaningful video before publication.
- Reduced-motion handling and no interaction that depends only on hover.
- Contrast testing against WCAG 2.2 AA during component QA.

## SEO Foundation

An SEO plugin is not required for the first implementation.

Start with:

- WordPress title-tag and core XML sitemap support.
- Clean canonical URLs and one canonical domain strategy.
- Editable excerpts for search descriptions.
- Open Graph and social-image metadata in a small NICE Core module.
- Organization structured data from approved global settings.
- Breadcrumb structured data only where visible breadcrumbs exist.
- Case-study structured data only when it accurately represents supplied facts.
- Redirect rules for any legacy URLs identified during launch planning.

Reconsider one well-maintained SEO plugin only if NICE needs non-developer control
of redirects, indexing rules, social previews, or advanced schema. Do not install
multiple overlapping SEO plugins.

## Repository Structure

Only custom project code and intentional documentation should be versioned.

```text
nice-solutions/
|-- .editorconfig
|-- .gitignore
|-- README.md
|-- package.json
|-- reference/
|   |-- NICE_Profile_25_26.pdf        # local-only by default
|   |-- NICE_Sitemap.pdf              # local-only by default
|   `-- reference-websites.txt
|-- scripts/
|   |-- build-release.sh
|   `-- link-localwp.sh
`-- wp-content/
    |-- plugins/
    |   `-- nice-core/
    `-- themes/
        `-- nice/
```

WordPress core, `wp-config.php`, databases, LocalWP configuration, media uploads,
caches, secrets, and machine-specific files stay outside version control. The
two custom directories can be linked into the LocalWP site's `wp-content`
directory after the site is created.

Reference PDFs are ignored by default because they contain company material and
contact details. They remain available locally. Add them to a private remote
only after NICE explicitly approves that repository access model.

## Dependencies

### Required Runtime

- LocalWP.
- Current stable WordPress, with WordPress 6.6 as the minimum because the theme
  uses `theme.json` version 3.
- PHP 8.3 or newer for the current recommended WordPress baseline.
- MySQL 8.0+ or MariaDB 10.11+.
- HTTPS in staging and production.

### Required Development Tools

- Git and the configured GitHub repository.
- Node.js/npm for editor scripts and asset validation.
- `@wordpress/scripts` as the only planned JavaScript build dependency when the
  custom blocks are scaffolded.
- Cursor, VS Code, CotEditor, or another suitable code editor.
- Codex CLI.
- LocalWP's site shell for PHP, database, and WP-CLI commands.

No production Composer package, CSS framework, animation framework, page
builder, ACF installation, form plugin, analytics SDK, or SEO plugin is required
for the foundation. ACF Pro can be reconsidered only if the native editing
prototype proves materially harder for NICE's editors.

## Development Environment Status

Checked on 2026-09-05.

| Requirement | Status | Evidence / action |
| --- | --- | --- |
| Project Git repository | Ready | Local `main` tracks `origin/main` at `jaypandey9082/nice-solutions` |
| LocalWP | Ready | Local 10.1.2 is installed and running |
| NICE LocalWP site | Ready | `nice-solutions.local` is running with Nginx |
| WordPress | Ready | WordPress 7.1 is running and the NICE theme is active |
| PHP | Ready | The LocalWP site is running PHP 8.2.30 |
| MySQL | Ready | Local has MySQL 8.4.0, which meets the proposed baseline |
| WP-CLI | Ready | LocalWP's bundled WP-CLI activated and verified the NICE theme |
| NICE Core | Ready | Version 1.2.0 is active; runtime, REST, routes, lifecycle, and migration checks pass |
| Git | Ready | Apple Git 2.50.1 |
| GitHub CLI | Ready | Authenticated to GitHub as `jaypandey9082` |
| Node.js | Ready | Node.js 24.20.0 |
| npm | Ready | npm 11.19.0 |
| Code editor | Ready | Cursor and CotEditor are installed; VS Code and its `code` command were not found |
| Codex CLI | Ready | Codex CLI 0.151.0 |

Do not install system PHP, MySQL, or global WP-CLI. LocalWP should own those
runtimes for this project.

## Remaining Implementation Order

1. Trust the LocalWP certificate when trusted local HTTPS is needed.
2. Review and approve the Phase 6 Events pages and Phase 7 Studio Home content in
   LocalWP.
3. Supply approved contact details, team content, client logos, and replacement
   media masters before those records are published.
4. Start the next approved phase only after an
   explicit brief.
5. Complete responsive, accessibility, performance, SEO, browser, and content
   QA before staging deployment.

## Decisions and Blockers

Approval is needed for these items before implementation:

1. Provide original NICE logo files and an approved brand guide or exact color
   and typography values.
2. Confirm which three case studies belong to each of the six service categories.
   The profile does not provide three clearly classifiable Films & Entertainment
   examples, and Activations & Promotions also needs an explicit shortlist.
3. Decide whether Design, brand identity, UI design, social media design,
   Meetings/MICE, Shows & Concerts, Technology Integration, and NICE-owned event
   properties should be capabilities under the six services, future services,
   or excluded from the launch.
4. Supply approved original project images/videos, captions, alt-text context,
   and publication rights. PDF images are references, not web-ready masters.
5. Confirm which team biographies, portraits, client logos, phone numbers,
   WhatsApp number, email addresses, physical address, and social accounts may
   be published.
6. Confirm the target host before implementation so SSL, caching,
   video, backup, and deployment assumptions can be validated early.

## Phase 1 Completion

This phase is complete. Its architecture and source boundaries continue to guide
the implemented theme and landing page.

## Phase 2 Completion

The design-system and global UI foundation is implemented under
`wp-content/themes/nice`. It includes centralized tokens, frontend/editor CSS,
generic templates, header/footer parts, reusable patterns, responsive navigation,
lazy media support, accessibility behavior, and a static design-system preview.

Automated checks validate the theme structure, `theme.json`, JavaScript, PHP
syntax, required responsive widths, menu keyboard behavior, sticky navigation,
reduced motion, horizontal overflow, and browser console output. WordPress
activation is verified in the running LocalWP site.

## Phase 3 Completion

The landing page is implemented as `templates/front-page.html` with reusable
patterns for the header, hero, Events/Studio pathways, philosophy, selected work,
capabilities, clients, contact call-to-action, and footer. It uses the supplied
NICE logo and project imagery extracted from the approved company profile.

The page has been checked at 320, 360, 390, 430, 768, 1024, and 1440px widths,
including mobile-menu keyboard behavior, sticky navigation, reduced motion,
image loading, route targets, horizontal overflow, and browser console errors.
Contact actions remain visibly marked as pending publication approval. No Events,
Studio, Team, service, case-study, or contact page was created in this phase.

## Phase 3.1 Completion

The landing hero and pathway choices now route directly to `/events/` and
`/studio/`. Global navigation retains only landing anchors and division gateways;
the contradictory `/team/` route has been removed. WordPress-aware `home_url()`
links keep the navigation portable to future environments.

Contact actions use the centralized `nice_contact_settings` adapter. Until NICE
Core supplies approved values, WhatsApp and Email resolve to the visible
publication-pending notice. The schema is ready for an HTTPS WhatsApp URL, email
address, approved phone URL, and social URL list without hard-coded personal
details.

Only the first hero image receives high fetch priority. Hero `sizes` now match
the mobile two-column and desktop four-column mosaic, pathway images are lazy,
and meaningful image descriptions avoid repeating adjacent project titles.
Temporary project and client previews are isolated behind filters so NICE Core
can replace them with CMS queries later. No Phase 4 page or content model was
implemented.

Live browser validation covers 320, 360, 390, 430, 768, 900, 1024, 1200, and
1440px widths. It checks overflow, mosaic columns, mobile source selection,
intrinsic media dimensions, cumulative layout shift, image loading, navigation,
contact placeholders, keyboard focus, sticky states, reduced motion, failed
requests, and console errors.

## Phase 4 Completion

The Events home is published locally at `/events/` using the dedicated
`templates/page-events.html` template. It composes Events hero, three-service
introduction, selected work, Brief/Idea/Solution process, project-specific proof,
clients, direct contact, and the existing global footer in that order.

Seven Events patterns and `assets/css/events.css` provide the page-specific
presentation. Service, project, client, and featured-image content now comes
from NICE Core after migration. `inc/events-data.php` preserves the approved
presentation metadata and a complete fallback without owning the primary CMS
records. The page uses only projects, clients, descriptions, and metrics
supported by the supplied profile.

At the end of Phase 4 only `/events/` existed. Its inner-page links are now live
through Phase 6; no Studio page was added. WhatsApp and email actions continue to
use centralized contact settings and resolve to a visible Events-specific
placeholder until approved details are supplied.

Live browser validation covers 320, 360, 390, 430, 768, 900, 1024, 1200, and
1440px widths. It verifies overflow, hero separation, image dimensions and source
selection, lazy loading, layout shift, section and route integrity, mobile-menu
keyboard behavior, sticky header states, reduced motion, landing-to-Events
routing, failed requests, and console errors. The supplied PDF image exports are
sufficient for local review; original high-resolution, publication-approved
masters remain required before production.

## Phase 5 Completion

NICE Core began at `1.0.0` in Phase 5. It registers Services, Case Studies,
Clients, and Team Members using normal WordPress posts and post meta. Division
and Service Type use locked, REST-enabled taxonomies containing only the two
approved divisions and six sitemap service types.

Native editor panels provide Service Classification, Case Study Project
Information, Portfolio Controls, Client Details, and Team Member Details. The
Settings > NICE Contact screen validates publication-approved WhatsApp, email,
phone, and social values. All settings remain empty, so the existing visible
contact placeholders are unchanged.

The idempotent `wp nice migrate-content` command imported 10 source-approved
Clients, three Events Services, five Events Case Studies, and seven unique media
attachments. A second run created no records and skipped all 18 content records.
No Studio Service or Team Member records were invented.

Theme adapters use NICE Core datasets and WordPress attachment IDs, then retain
the approved Phase 3/4 fallback if the plugin is unavailable. See
`docs/content-model.md` for the complete field and helper reference.

## Phase 6 Completion

The NICE theme is now `0.5.0` and NICE Core is `1.1.0`. Five approved WordPress
Pages provide the Events Services, Case Studies, Clients, Team, and Contact
indexes. Controlled NICE Core rewrites provide reusable Service and Case Study
detail URLs without enabling raw CPT archives or paths.

Seven block-theme templates use eight server-rendered theme blocks for the shared
Events navigation and CMS-driven page bodies. Services render their editor-owned
capabilities and related work. Case Studies render editor narratives, Client
relationships, location/year metadata, classification, project-specific proof,
related work, and next/previous navigation. The shared Client dataset renders ten
records; Team and Contact render intentional publication-pending states.

The migration provisions the five structural Pages and enriches only untouched
source-backed fields. A second run created zero Pages, content records, media, or
field updates. Browser validation covered all 13 approved inner routes at 320,
360, 390, 430, 768, 900, 1024, 1200, and 1440px, plus canonical URLs, intentional
404s, keyboard navigation, reduced motion, responsive media, failed requests,
and console errors. Full route and maintenance details are in
`docs/events-pages.md`.

## Phase 7 Completion

The NICE theme is now `0.6.0` and NICE Core is `1.2.0`. `/studio/` is a dedicated,
cinematic Studio gateway using the shared header, footer, navigation behavior,
design tokens, and accessibility foundation. Its page flow includes an
image-led hero, three CMS-driven Services, a compact capability statement, five
CMS-driven selected projects, Story/Production/Screen storytelling, a curated
shared Client list, and a direct-contact close.

NICE Core now migrates exactly three sitemap-approved Studio Services and five
source-supported Studio Case Studies. The Studio Home reads those records,
shared Clients, contact settings, and social settings through existing query
helpers. Empty social values omit the social section; empty contact values show
an explicit publication-pending state without exposing profile contact details.

Only `/studio/` is implemented. Future Studio service, case-study, client, team,
and contact URLs remain real `404` responses without redirecting to unrelated
Events content. Live browser validation covers 320, 360, 390, 430, 768, 900,
1024, 1200, and 1440px, plus landing and Events regressions. Full implementation
and content details are in `docs/studio-home.md`.

## Phase 7.1 Completion

The NICE theme is now `0.7.0` and NICE Core is `1.2.0`.

Phase 7.1 introduces editorial typography, motion, and journey refinements across
both Events and Studio without rebuilding existing architecture:

1. **Editorial Typography System**: Registered `fontFamilies` entry `editorial`
   with local serif stack (`Georgia, "Times New Roman", serif`), avoiding CDN
   requests while providing a font-swappable CSS token architecture via
   `--nice-type-editorial` and `--nice-type-editorial-display`.
2. **Editorial Reveal Motion**: Extended `landing.js` and `landing.css` with
   word-by-word reveal masks (`[data-nice-editorial-reveal]`) powered by lightweight
   IntersectionObserver with 80ms word stagger, screen reader preservation via
   `.nice-sr-only`, zero layout shift, and immediate reduced-motion fallback.
3. **Navigation Continuity**: Global header and footer now detect division context
   (`nice_theme_is_events_context()`, `nice_theme_is_studio_home()`). When inside Events,
   "Work" points to `/events/case-studies/` and "Services" to `/events/services/`.
   When inside Studio, "Work" points to `/studio/#studio-work` and "Services" to
   `/studio/#studio-services`.
4. **Standardized CTA System**: Refactored `nice_render_events_inner_contact_cta()`
   with customizable parameters, editorial display typography, word reveals, and
   populated WhatsApp/Email direct actions.
5. **Case Study Storytelling & Hierarchy**: Reordered case study templates to
   support quotes, project overview infobar, front-loaded proof metrics, and rich
   thumbnail navigation cards. Replaced uniform card grid with featured, large, and
   secondary editorial visual scale.
6. **Studio Differentiation**: Studio hero enlarged to 80svh with serif statement
   and adjusted veil opacity. Studio Story section styled with dark container for
   cinematic light-dark-light rhythm. Media hover states polished (1.03-1.04 scale).
7. **Polymorphic Routing & CMS Meta**: Registered `_nice_hero_video_url`,
   `_nice_quote_text`, and `_nice_quote_author` post meta for Case Studies.
   Refactored `routes.php` with `nice_get_content_division()` and `nice_get_content_url()`
   supporting both divisions while ensuring Studio inner routes continue to 404
   until Phase 8.

