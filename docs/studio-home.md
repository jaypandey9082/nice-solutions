# Studio Home

Phase 7 implements only the canonical Studio gateway at `/studio/`. The deeper
Studio information architecture remains reserved for Phase 8.

## Page Structure

The `page-studio.html` template renders the server-side `nice/studio-home` block
between the shared global header and footer. The page contains:

1. A Studio-scoped anchor navigation for Home, Services, Selected Work, Clients,
   and Contact.
2. A full-bleed image hero introducing NICE's audiovisual and screen work.
3. Three editorial Service features sourced from NICE Core.
4. A concise capability band for source-supported production formats.
5. Five selected Studio projects sourced from NICE Core.
6. A Story, Production, Screen editorial statement.
7. A curated list of eight records from the shared Client dataset.
8. An optional social section and a direct-contact close driven by approved
   global settings.

## CMS Sources

Studio Home uses NICE Core query and settings helpers. The theme does not carry a
second permanent set of Studio Services or Case Studies.

| Section | Source |
| --- | --- |
| Services | `nice_get_studio_services()` |
| Selected Work | `nice_get_featured_case_studies()` filtered to `studio` |
| Clients | `nice_get_featured_clients()` |
| Contact | `nice_get_contact_settings()` and contact action helpers |
| Social | `nice_get_social_links()` |

## Studio Service Records

The migration creates exactly three Services, each assigned to Division `studio`
and one matching Service Type:

| Service | Service Type |
| --- | --- |
| Corporate Videos | `corporate-videos` |
| Digital Content Creation | `digital-content-creation` |
| Films & Entertainment | `films-entertainment` |

Descriptions, editor content, and featured images are source-supported. The
official names remain aligned with the sitemap.

## Studio Case Studies

The migration creates five conservative, source-supported records:

| Case Study | Classification | Client / credit |
| --- | --- | --- |
| Strata Geosystems Factory Shoot | Corporate Videos | Strata Geosystems India |
| Career Agents Academy | Digital Content Creation | Bajaj |
| Krish-e | Digital Content Creation | Mahindra |
| CRISIL Financial Literacy Content | Digital Content Creation | CRISIL |
| Jayanti | Films & Entertainment | Source-backed project credit only |

Only supported location data is stored, and no unsupported year, result,
audience, award, budget, or production metric is added.

## Fallback Behavior

With NICE Core active and valid records, Studio Home renders CMS output. If NICE
Core is unavailable, the hero can use an approved theme image while data sections
show intentional empty states. If one CMS section is incomplete, the page shows
only available records or that section's empty state. It never merges a partial
CMS list with hardcoded business records.

## Social and Contact

The profile's historical contact and social details are not automatically
published. When NICE Core social values are empty, the social section is omitted.
When contact values are empty, the closing section displays a publication-pending
notice and renders no fake links or form.

## Routes

Phase 7 creates only `/studio/`. These planned Phase 8 routes remain unregistered
and return `404` without redirecting to Events:

- `/studio/services/`
- `/studio/services/{service}/`
- `/studio/case-studies/`
- `/studio/case-studies/{case-study}/`
- `/studio/clients/`
- `/studio/team/`
- `/studio/contact/`

No global `/team/` route is created. Studio Home exposes no live link to a future
route; service-detail destinations are represented as disabled editorial text.

## Media Boundary

The local build uses source-derived WebP images with WordPress attachment IDs,
responsive `srcset`/`sizes`, intrinsic dimensions, and lazy loading below the
hero. Original high-resolution, publication-approved image and video masters,
final captions, and rights confirmation are still required before production.
