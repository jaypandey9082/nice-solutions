# Events Pages

The Events section uses the shared NICE editorial foundation without enabling
native CPT archives. Its homepage was refined after Phase 7 to match the current
landing and Studio homepages.

## Events Homepage

The Events homepage is rendered by the dynamic `nice/events-home` block in this
order:

```text
Hero
Emagine - Explore - Execute
Services
Selected work
Process
Clients and approved proof
Contact
```

Services and project records continue to come from NICE Core. Four editor-ordered
Events case studies are shown in a two-column desktop grid and a one-column mobile
layout. Their media areas intentionally remain neutral until approved project
photographs are available; the homepage does not load retired deck photography.

The temporary Events hero is editable from **Pages > Events > Events Hero Media**.
Editors can select, replace, or remove the desktop image, optionally add a mobile
alternative, set the focal position, and update attachment alt text in the Media
Library. Mobile falls back to the desktop image. Removing the initialized
reference image is respected by later migration runs.

## Public Routes

```text
/events/
/events/services/
/events/services/{service}/
/events/case-studies/
/events/case-studies/{case-study}/
/events/clients/
/events/about/
/events/contact/
```

The three Service and five current Case Study detail URLs are generated from
their published NICE Core slugs. Unknown detail slugs, raw CPT paths, and the
unapproved global `/team/` and `/about/` routes return the shared NICE `404`
template. `/events/team/` is the one exception: About replaced the Team page, so
the retired path `301`s to `/events/about/`.

## Template Architecture

Five custom Page templates render the section indexes. Two post-type hierarchy
templates render every Service and Case Study detail page. All use the shared
header, footer, and inner-page styles. The Events homepage additionally uses
`events-home.css` and the shared editorial foundation.

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
- About: the shared Emagine/Explore/Execute philosophy, an Events-specific
  introduction, Events-filtered Team Members, and the company social profiles
  and office address.
- Contact: NICE Contact settings only; there is no form.

Events Home also queries the current featured Case Studies and Clients. Existing
approved arrays remain only as an atomic resilience fallback for the landing and
Events Home.

Every public landing, division, service, case-study, client, team, and contact
page contains one shared `Emagine - Explore - Execute` strip immediately after
its main banner or title introduction. Studio Home also presents Services before
Selected Work.

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
