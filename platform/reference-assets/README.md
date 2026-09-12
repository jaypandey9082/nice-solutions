# Temporary reference images

These are generated, generic images for reviewing the Main gateway's composition.
They are not photographs of NICE projects, locations, staff, clients, or production
equipment, and are not extracted from the company deck. They must never be used
as evidence of a named client engagement or project outcome.

| File | Stage 1 use | WordPress ownership |
| --- | --- | --- |
| `event-reference.webp` | Main hero and Main Events introduction | Main Media Library; Hero and Events image slots |
| `studio-reference.webp` | Main Studio introduction | Main Media Library; Studio image slot |

**Never assign either image to a project preview, case study, client proof section,
or testimonial.** Actual project media areas stay blank until approved photographs
are supplied. Do not import these assets into the Events or Studio skeletons in
Stage 1. Future division hero imagery belongs to a separate stage and decision.

## Import and replacement

After the CMS owner confirms readiness, activate the plugin/theme and seed Main
using the commands in `platform/README.md`. From the repository root, run:

```bash
bash platform/scripts/runtime.sh import-references
```

This command requires the successful coordinated Main seed. It imports into
Main's own uploads directory, marks the images as references, and assigns the
three Main slots. The one-time bootstrap preserves later editor choices on rerun.
The command is documented here; this file is not an import or browser-test report.

To replace a reference, open Main's static homepage in WordPress and find **NICE
Page Content and Media**. Select an approved local Media Library image in the
appropriate slot, optionally add a mobile alternative, set focal points and alt
text, and clear **Temporary reference imagery**. Save and inspect desktop/mobile
cropping. To leave the area empty, use Remove and save. Do not edit theme files or
substitute an attachment ID from another WordPress installation.

Reference assets are maintained outside the theme/plugin packages. Production
publication requires an explicit media review; neither keeping this directory nor
running the package command approves these images as NICE work. Original deck
files and the legacy reference installation remain preserved separately.
