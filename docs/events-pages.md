# Events Pages

Phase 6 implements the Events section without starting Studio or enabling native
CPT archives.

## Public Routes

```text
/events/
/events/services/
/events/services/{service}/
/events/case-studies/
/events/case-studies/{case-study}/
/events/clients/
/events/team/
/events/contact/
```

The three Service and five current Case Study detail URLs are generated from
their published NICE Core slugs. Unknown detail slugs, raw CPT paths, and the
unapproved global `/team/` route return the shared NICE `404` template.

## Template Architecture

Five custom Page templates render the section indexes. Two post-type hierarchy
templates render every Service and Case Study detail page. All use the same
header, footer, Events sub-navigation, `events.css`, and `events-inner.css`.

The templates call eight PHP-rendered theme blocks registered in
`inc/events-pages.php`. These blocks query NICE Core at request time, so content
edits appear without editing a template. They add no frontend JavaScript.

## CMS Ownership

- Services: title, slug, excerpt, editor content, featured image, Service Type,
  and Division.
- Case Studies: title, slug, excerpt, editor narrative, featured image, Client
  relationship or approved fallback credit, location, year, Service Type,
  Division, display order, featured state, and optional project proof.
- Clients: the single shared Client dataset, optional featured image/logo, and
  approved external URL.
- Team: Events-filtered Team Members only.
- Contact: NICE Contact settings only; there is no form.

Events Home also queries the current featured Case Studies and Clients. Existing
approved arrays remain only as an atomic resilience fallback for the landing and
Events Home.

## Routing And Provisioning

NICE Core keeps `has_archive` and `rewrite` disabled on the CPTs. It registers
only the two approved Events detail patterns and filters generated post links to
their canonical URLs. A raw query request redirects to the canonical path; raw
CPT-like paths do not resolve.

Run `wp nice migrate-content` after activation. The command idempotently creates
the five child Pages, assigns their custom templates, imports missing approved
content, and enriches only untouched source-backed fields. Reactivating the
plugin or running the migration refreshes rewrite rules when required.

## Incomplete Content

Case Study groups render only published records, up to three per approved Events
Service Type. Team displays a publication-pending state while it has zero
approved records. Contact displays a publication-pending state while WhatsApp,
email, and phone remain empty. Missing images or optional metadata do not create
empty UI containers.

Original high-resolution, publication-approved media, Client logos, Team
profiles, and public contact details are still required before production.
