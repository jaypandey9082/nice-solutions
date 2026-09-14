# Studio Pages

Phase 8 implements the complete Studio inner-page experience for NICE Solutions
using WordPress block theme templates, NICE Core CMS integration, and editorial
motion styling without introducing external libraries or framework dependencies.

## Public Routes

```text
/studio/
/studio/services/
/studio/services/corporate-videos/
/studio/services/digital-content-creation/
/studio/services/films-entertainment/
/studio/case-studies/
/studio/case-studies/strata-geosystems-factory-shoot/
/studio/case-studies/career-agents-academy/
/studio/case-studies/krish-e/
/studio/case-studies/crisil-financial-literacy-content/
/studio/case-studies/jayanti/
/studio/clients/
/studio/about/
/studio/contact/
```

The three Studio Service and five approved Studio Case Study detail URLs are
generated from their published NICE Core slugs. Unknown detail slugs, cross-division
requests (e.g. requesting an Events case study via a Studio URL or vice-versa),
raw CPT paths (`/nice_service/`, `/nice_case_study/`), and the unapproved global
`/team/` and `/about/` routes strictly return the shared NICE `404` template.
`/studio/team/` is the one exception: About replaced the Team page, so the
retired path `301`s to `/studio/about/`.

## Template Architecture

Five custom Page templates render the Studio section indexes:
- `templates/page-studio-services.html`
- `templates/page-studio-case-studies.html`
- `templates/page-studio-clients.html`
- `templates/page-studio-team.html`
- `templates/page-studio-contact.html`

Two shared CPT single templates (`templates/single-nice_service.html` and
`templates/single-nice_case_study.html`) render detail pages for both Events and
Studio. Each template hosts division-aware server-rendered blocks; blocks detect
the division of the queried post and render only when matching the active division.

The templates invoke eight PHP-rendered theme blocks registered in `inc/studio-pages.php`:
1. `nice/studio-section-navigation`: Context-aware sub-navigation with active state highlighting.
2. `nice/studio-services-index`: Editorial hero, three approved services with capabilities, and contact CTA.
3. `nice/studio-service-detail`: Hero with media, editor narrative, relevant work, related services, and CTA.
4. `nice/studio-case-studies-index`: Grouped by the three approved Studio Service Types with asymmetric card layout.
5. `nice/studio-case-study-detail`: Hero (with video support if populated), client info bar, optional quote block, optional proof metric, narrative story, related projects, previous/next project navigation cards, and closing CTA.
6. `nice/studio-clients-index`: Shared Client CPT dataset presented in a clean 4-column directory.
7. `nice/studio-team-index`: Division-filtered Team directory with an intentional publication-pending empty state.
8. `nice/studio-contact-page`: Form-free, direct WhatsApp, Email, and Phone channels driven by NICE Core settings.

All blocks query NICE Core at request time, ensuring changes in the WordPress CMS
reflect immediately on the frontend without template edits.

## CMS Integration & Taxonomy

- **Division**: All Studio services and case studies are tagged with taxonomy `nice_division` term `studio`.
- **Service Types**: Studio services and case studies use the three approved terms:
  - `corporate-videos` (Corporate Videos)
  - `digital-content-creation` (Digital Content Creation)
  - `films-entertainment` (Films & Entertainment)
- **Metadata**:
  - `_nice_hero_video_url`: Optional direct video URL rendered with `<video muted autoplay playsinline loop>`.
  - `_nice_quote_text` & `_nice_quote_author`: Optional editorial quote rendered in serif display typography.
  - `_nice_proof_value` & `_nice_proof_label`: Optional project-specific proof metric.
  - `_nice_location` & `_nice_year`: Project info bar metadata.
  - `_nice_client_id` / `_nice_client_name`: Client attribution.

## Routing & Provisioning

- Canonical URLs are strictly enforced: `/studio/services/{slug}/` and `/studio/case-studies/{slug}/`.
- Cross-division requests return 404 (Events posts requested via Studio URLs or Studio posts via Events URLs).
- Raw CPT URLs (`/nice_service/`, `/nice_case_study/`) and the global `/team/` and `/about/` routes return 404.
- `/studio/team/` `301`s to `/studio/about/`; the page was renamed rather than replaced, so edited copy follows it.
- `nice_provision_studio_pages()` automatically provisions the 5 child pages under `/studio/` on plugin activation or through `wp nice migrate-content`.

## Visual Language & Editorial Polish

Studio embodies a cinematic, screen-focused visual identity:
- Editorial typography (`.nice-editorial`, serif display heading scale).
- Word-staggered editorial reveals (`[data-nice-editorial-reveal]`) powered by lightweight IntersectionObserver and CSS transitions.
- Asymmetric card hierarchy (featured, large, secondary) on case study index pages.
- Prev/Next project navigation cards with thumbnails and client eyebrows.
- Full responsive support from 320px to 1440px.
- Accessibility standards: single H1 per page, semantic landmarks (`header`, `main`, `nav`, `footer`, `article`), visible focus styles, keyboard navigation with Escape key menu dismissal, and `prefers-reduced-motion` safety overrides.
