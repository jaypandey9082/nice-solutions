# NICE Design System

Phase 2 defines the reusable visual and interaction foundation. Phase 3 applies
that system to the landing page without adding division pages, case studies, team
entries, or production contact values.

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

The theme uses normal blocks and patterns rather than custom blocks for this
phase. Registered block styles cover buttons, section groups, and image ratios.
The reusable patterns provide section-heading, project, service, and direct
contact foundations. Production URLs and contact values will be connected only
after the NICE Core plugin and approved content exist.

## Landing Page Application

The front-page template composes registered patterns in this order: global
header, full-bleed source-image hero, Events/Studio pathways, philosophy,
selected work, capabilities, clients, direct-contact band, and global footer.
The visual assets and copy are derived from the supplied NICE company profile.
Contact actions intentionally point to an on-page pending-approval notice until
publication-safe phone, WhatsApp, and email values are confirmed.

## Dependencies

No third-party dependency was added. The Node scripts use built-in modules only
to generate preview variables from `theme.json` and validate required files and
tokens.

## Known Validation Boundary

The theme is active in the running `nice-solutions.local` LocalWP site and its
landing page has been checked in a real browser. The supplied PDF images are
appropriate for this local implementation, but original web-ready masters and
publication rights should still be confirmed before production deployment.
