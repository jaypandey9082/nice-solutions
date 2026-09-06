# NICE Design System

Phase 2 defines the reusable visual and interaction foundation. Phase 3.1 applies
it to the landing page, Phase 4 extends it to the Events home, and Phase 6 carries
the same system through the Events service, case-study, client, team, and contact
pages. Studio remains outside the implemented scope.

## Color Tokens

| Token | Value | Intended use |
| --- | --- | --- |
| Background | `#FFFFFF` | Dominant page background |
| Warm | `#F7F6F2` | Secondary section background |
| Surface | `#FFFFFF` | Navigation and controls |
| Text | `#111111` | Primary text and dark bands |
| Muted | `#5F6268` | Supporting copy and metadata |
| NICE Red | `#EF233C` | Sampled brand accent for decorative emphasis and large text |
| Action Red | `#D7193F` | Buttons, links, and normal-size UI requiring AA contrast with white |
| NICE Blue | `#095584` | Secondary brand cue and focus outline |
| Border | `#D9DADC` | Quiet dividers and control boundaries |
| Media Neutral | `#DCE6EC` | Cool neutral for non-production preview media |

The red and blue starting points were sampled from the supplied company profile.
They remain provisional until original brand artwork or a brand guide is supplied.

## Typography

The initial family is the local system sans stack. It avoids a font download and
keeps the foundation fast while typography licensing is unresolved.

| Role | Mobile | Tablet | Desktop | Weight / line height |
| --- | --- | --- | --- | --- |
| Display | 48px compact / 64px standard | 88px | 112px | 600 / 0.98 |
| H1 | 48px | 64px | 80px | 600 / 1.08 |
| H2 | 36px | 48px | 56px | 600 / 1.08 |
| H3 | 24px | 24px | 24px | 600 / 1.08 |
| Body Large | 19px | 19px | 19px | 400 / 1.55 |
| Body | 16px | 16px | 16px | 400 / 1.625 |
| Small | 14px | 14px | 14px | 400-600 |
| Label | 12px | 12px | 12px | 700 / 1.3, uppercase |

Type sizes change at explicit breakpoints, not continuously with viewport width.
The 48px compact display step applies through 430px so long display words remain
inside a 320px viewport. Letter spacing stays at zero.

## Spacing and Layout

| Token | Value |
| --- | --- |
| XS | 8px |
| SM | 12px |
| MD | 16px |
| LG | 24px |
| XL | 36px |
| 2XL | 56px |
| Section | 80px mobile, 104px tablet, 128px desktop |
| Page gutter | 20px mobile, 32px tablet, 40px desktop |
| Content width | 720px |
| Wide width | 1240px |

`nice-container` is for reading content, `nice-wide` is for editorial layouts,
and `nice-full-bleed` is for intentionally edge-to-edge media or bands.

## Radius and Motion

| Token | Value |
| --- | --- |
| Small | 4px |
| Medium | 8px |
| Large | 16px, reserved for mobile navigation overlay |
| Pill | 999px, reserved for navigation and controls |
| Fast | 140ms |
| Normal | 220ms |
| Slow | 360ms |
| Easing | `cubic-bezier(0.2, 0.8, 0.2, 1)` |

Animations use explicit opacity and transform transitions. The optional
cross-document view transition is progressive enhancement and never blocks
navigation. Reduced-motion mode collapses non-essential animation durations.

## Responsive Strategy

- 320-479px: single column, compact floating header, full mobile menu.
- 480-767px: wider single column and selective utility pairs.
- 768-899px: two-column editorial layouts and tablet spacing.
- 900-1199px: desktop navigation and multi-column components.
- 1200px+: capped wide layout and desktop display scale.

Every grid child uses a zero minimum width so long text cannot force horizontal
overflow. Buttons and menu controls have a minimum 44px touch target.

## Component Classes

- Navigation: `nice-site-header`, `nice-nav-shell`, `nice-mobile-menu`.
- Typography: `nice-display`, `nice-body-large`, `nice-eyebrow`.
- Layout: `nice-container`, `nice-wide`, `nice-grid`, `nice-stack`.
- Buttons: `nice-button--primary`, `nice-button--secondary`,
  `nice-button--text`.
- Links: `nice-link` and `nice-link__arrow`.
- Section headings: `nice-section-heading` and child elements.
- Projects: `nice-project-card` with `--featured`, `--compact`, `--horizontal`,
  or `--full` modifiers.
- Services: `nice-service-list` and `nice-service-card`.
- Media: `nice-media`, `nice-video`, and registered core Image block styles.
- Contact: `nice-contact-band`.
- Footer: `nice-site-footer` and `nice-footer-grid`.

## Navigation and Accessibility

- Sticky navigation remains visible and condenses only after 96px of scroll.
- It restores its full state below 40px, creating hysteresis that prevents jitter.
- The mobile menu uses `aria-expanded`, `aria-controls`, `aria-hidden`, and
  `inert` to keep closed content out of the interaction tree.
- Opening moves focus to Close; Escape closes; Tab is trapped inside; closing
  restores focus to the trigger.
- A skip link, visible focus outline, semantic navigation labels, reduced motion,
  and 44px minimum controls are included.

## WordPress Editing

The theme uses normal blocks and patterns for stable editorial composition.
Registered block styles cover buttons, section groups, and image ratios. The
Events inner templates use server-rendered theme blocks so each request reflects
the current NICE Core records, relations, ordering, and empty states without
copying database content into template markup.

## Landing Page Application

The front-page template composes registered patterns in this order: global
header, full-bleed source-image hero, Events/Studio pathways, philosophy,
selected work, capabilities, clients, direct-contact band, and global footer.
The visual assets and copy are derived from the supplied NICE company profile.
Contact actions intentionally point to an on-page pending-approval notice until
publication-safe phone, WhatsApp, and email values are confirmed.

### Contact Adapter

NICE Core owns the `nice_contact_settings` option and the public contact helper
API. The theme owns only link presentation and its visible empty-value fallback.
The settings remain empty until publication-safe values are approved.

### Temporary Landing Data

The landing adapters read migrated Case Study, Client, and attachment records
from NICE Core. `inc/landing-data.php` retains its complete approved array only
as an atomic fallback when NICE Core is inactive or incompletely migrated.

## Events Home Application

The Events template composes the global header, Events hero, services, selected
work, process, proof, clients, direct-contact band, and global footer. Its
full-bleed single-image hero is intentionally distinct from the landing mosaic,
while typography, color, spacing, controls, navigation, and motion continue to
use the same tokens and shared components.

Events service rows and project previews use unframed editorial layouts instead
of card grids. The process band uses the dark surface, project-level proof uses
plain divided columns, and the client section uses a restrained text list. The
layout changes at the existing 768px and 1200px design-system breakpoints, with
desktop navigation continuing to switch at 900px.

### Events Content Adapter

The three Services, featured Case Studies, Clients, and featured images are read
from NICE Core. `inc/events-data.php` retains approved layout classes, image
descriptions, and fallback records for the Events home only. Project proof is
stored on the source Case Study and is always displayed with that project rather
than as a company-wide metric.

## Events Inner Pages

The Events sub-navigation is shared by every inner page and wraps into two rows
on narrow screens. Service and Case Study indexes use unframed editorial rows;
detail pages pair source-backed narratives with controlled metadata and related
content. The client directory uses text when no approved logo exists. Team and
Contact render explicit publication-pending states while their CMS records and
approved contact channels remain empty.

Hero images are eager and high priority. Content images keep intrinsic dimensions
and load lazily as they approach the viewport. The inner-page stylesheet follows
the established 768px, 900px, and 1200px breakpoints and removes non-essential
transitions when reduced motion is requested.

## Dependencies

No third-party dependency was added. NICE Core loads no frontend CSS or
JavaScript. The Node scripts use built-in modules only to validate the theme,
plugin, required files, and tokens.

## Known Validation Boundary

The theme is active in the running `nice-solutions.local` LocalWP site. The
landing page, Events home, and all Events inner routes have been checked in a
real browser from 320px through 1440px. The supplied PDF images are appropriate
for this local implementation, but original web-ready masters and publication
rights should still be confirmed before production deployment.
