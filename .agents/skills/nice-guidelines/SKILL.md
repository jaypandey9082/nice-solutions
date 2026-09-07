---
name: nice-guidelines
description: Core engineering, design, and content guidelines for the NICE Solutions website project. Use whenever developing, modifying, or auditing code, styling, templates, routes, or content across the NICE WordPress theme and NICE Core plugin.
---

# NICE Solutions — Engineering & Design Guidelines

This skill defines the technical and creative standards for the **NICE Solutions** platform (`jaypandey9082/nice-solutions`). All engineering agents operating within this workspace MUST adhere to these guidelines.

---

## 1. Architectural Principles

### Theme vs. Plugin Separation of Concerns
- **`wp-content/themes/nice` (NICE Theme)**:
  - Responsible strictly for presentation, layout, CSS, block patterns, and template rendering.
  - Registers dynamic block render callbacks (`inc/events-pages.php`, `inc/studio-pages.php`).
  - Contains CSS layers in `assets/css/` and lightweight vanilla JavaScript in `assets/js/`.
  - Configures design tokens and block defaults in `theme.json`.
- **`wp-content/plugins/nice-core` (NICE Core)**:
  - Responsible for data modeling, custom post types (`nice_service`, `nice_case_study`, `nice_client`, `nice_team`), custom taxonomies (`nice_division`, `nice_service_type`), post meta registration (`includes/meta.php`), polymorphic division routing (`includes/routes.php`), and query helpers (`includes/queries.php`).
  - No presentation or inline styles belong in NICE Core.

---

## 2. Zero-Library Policy

- **Vanilla Only**:
  - **NO** external animation libraries (no GSAP, Lenis, Framer Motion, anime.js).
  - **NO** front-end utility frameworks or heavy JavaScript runtimes (no jQuery, React, Vue, Tailwind).
  - Use native web APIs: CSS `@layer`, `IntersectionObserver`, `matchMedia('(prefers-reduced-motion: reduce)')`, native `<video>` elements, and vanilla DOM manipulation.
  - All interactive scripts must degrade gracefully when JavaScript is disabled or unsupported.

---

## 3. CSS Architecture & Cascade Layers

All theme styling must conform to CSS Cascade Layers in `assets/css/site.css` and division stylesheets:

```css
@layer tokens, base, components, utilities, motion;
```

1. **`tokens`**: CSS custom properties derived from `theme.json` or root variables (`--nice-*`).
2. **`base`**: Reset, HTML element defaults, typography foundations, and fluid sizing.
3. **`components`**: Reusable cards, navigation shells, rows, and hero layouts (`.nice-card`, `.nice-studio-service-row`).
4. **`utilities`**: Helper classes (`.nice-editorial`, `.nice-editorial-display`, `.nice-section-break`).
5. **`motion`**: Transitions, hover micro-interactions, reveal keyframes, and strict `@media (prefers-reduced-motion: reduce)` overrides.

---

## 4. Brand Guidelines: Events vs. Studio Divisions

NICE Solutions operates two primary creative divisions with distinct visual personalities:

| Dimension | NICE Events | NICE Studio |
| :--- | :--- | :--- |
| **Focus** | Physical experiences, large-scale live productions, exhibitions | Film, digital content, commercial video, high-end motion |
| **Color Scheme** | Light, minimal, structured white with subtle charcoal | Balanced light with **cinematic dark accents** (`#0F0F11`) on featured work & studio story |
| **Typography** | Clean, crisp, precise sans-serif (`var(--wp--preset--font-family--sans)`) | Sans-serif body paired with **NICE Editorial Serif** (`Georgia, "Times New Roman", serif`) for hero displays and quotes |
| **URL Root** | `/events/` (e.g. `/events/services/`, `/events/case-studies/`) | `/studio/` (e.g. `/studio/services/`, `/studio/case-studies/`) |
| **Motion** | Fast, clean, structured fade/slide | Cinematic word-by-word editorial reveal (`[data-nice-editorial-reveal]`) |

---

## 5. Coding Standards

### PHP Standards
- Compatible with PHP 8.2+ and WordPress Coding Standards (WPCS).
- Strict output escaping: always use `esc_html()`, `esc_url()`, `esc_attr()`, or `wp_kses_post()`.
- Use buffered rendering with `ob_start()` / `ob_get_clean()` for all dynamic block render callbacks.
- Never output unescaped variables in templates.
- Guard against null objects and invalid post types.

### JavaScript Standards
- Vanilla ES6+ wrapped in IIFE or module closures (`(() => { 'use strict'; ... })();`).
- Always respect `prefers-reduced-motion`.
- Use accessibility-safe DOM techniques: preserve screen-reader text (`.nice-sr-only`) when splitting elements for animation.

---

## 6. Content Authenticity & Publication-Pending Policy

- **No Placeholder Copy**: Never use `Lorem Ipsum`, "coming soon", or mock placeholder text.
- **Publication-Pending Pattern**:
  - When records (team members, quotes, client metrics) are missing or pending client sign-off, render intentional publication-pending notices:
    - Example: *"Our creative and production rosters are currently being updated for publication."*
  - If optional meta fields (e.g., `_nice_quote_text`, `_nice_hero_video_url`, `_nice_proof_value`) are empty, cleanly omit the respective section without broken wrappers or empty tags.

---

## 7. Form-Free Contact Architecture

- **No Contact Forms**:
  - NICE intentionally avoids web forms, CAPTCHAs, or form backend submissions to prevent friction and spam.
- **Direct Communication Channels Only**:
  - Contact pages render verified direct channels: WhatsApp (`https://wa.me/...`), direct email (`mailto:...`), and telephone (`tel:...`).
  - Contact details are managed centrally via NICE Core contact settings (`nice_get_contact_settings()`).

---

## 8. Strict Route Isolation & 404 Handling

- All unreleased or unapproved URLs must return an HTTP 404 response.
- Prevent WordPress canonical redirects from guessing unreleased route slugs (`nice_filter_unapproved_canonical_guesses()`).
- Polymorphic division routes must strictly validate post types and division taxonomy terms before resolving canonical URLs.
