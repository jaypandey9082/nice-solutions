---
name: code-to-design
description: Use this skill to translate production WordPress HTML/CSS, block markup, and design tokens into Google Stitch (StitchMCP) designs and DESIGN.md specifications. Trigger when synchronizing live code with design prototypes or creating Stitch screens from existing templates.
---

# Google Stitch — Code to Design Skill

This skill guides converting existing production code (WordPress theme blocks, templates, and CSS tokens) into Google Stitch design artifacts, design systems, and screens.

## Bridging NICE Solutions Code to Stitch

The NICE Solutions web platform uses a lightweight, vanilla CSS/HTML architecture anchored by WordPress block themes (`theme.json`) and CSS Cascade Layers (`site.css`).

### Source Locations in Codebase
- **Design Tokens**: `wp-content/themes/nice/theme.json` (`settings.color.palette`, `settings.typography.fontFamilies`, `settings.spacing.spacingSizes`)
- **Layered Stylesheets**:
  - `wp-content/themes/nice/assets/css/site.css` (tokens, base, components, utilities, motion)
  - `wp-content/themes/nice/assets/css/landing.css` (landing specific styles & reveal animations)
  - `wp-content/themes/nice/assets/css/events.css` (Events division layout & components)
  - `wp-content/themes/nice/assets/css/studio.css` (Studio division layout, editorial serif, cinematic cards)
- **Component HTML / Templates**:
  - `wp-content/themes/nice/patterns/` (reusable block patterns)
  - `wp-content/themes/nice/inc/events-pages.php` (Events dynamic blocks)
  - `wp-content/themes/nice/inc/studio-pages.php` (Studio dynamic blocks)

## Workflow

### 1. Extract Production Tokens to `DESIGN.md`
Extract current CSS variables and presets from `theme.json` and `site.css` into a structured markdown document:
```markdown
# NICE Solutions Design System Spec

## Color Tokens
- `--wp--preset--color--primary`: #FFFFFF
- `--wp--preset--color--contrast`: #1A1A1A
- `--wp--preset--color--muted`: #4A4A4A
- `--wp--preset--color--accent`: #E03131
- `--wp--preset--color--studio-bg`: #0F0F11

## Typography Tokens
- `--wp--preset--font-family--sans`: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif
- `--wp--preset--font-family--editorial`: Georgia, "Times New Roman", serif
- Display scale: 3rem / 4.5rem / 6rem
```

### 2. Upload Spec to Stitch Project
Use `upload_design_md` or `create_design_system_from_design_md` via `call_mcp_tool`:
```json
// Tool: call_mcp_tool -> Server: StitchMCP -> Tool: upload_design_md
{
  "projectId": "<project_id>",
  "designMdContent": "<extracted_design_spec>"
}
```

### 3. Generate Stitch Screen from Production DOM/Pattern
When a new screen should match existing production layouts (e.g., Services index or Case Study detail):
1. Read the production template or pattern markup.
2. Formulate a prompt incorporating the exact structure, typographic classes (`nice-editorial`, `nice-studio-service-row`), and content semantics.
3. Call `generate_screen_from_text` with the compiled prompt.
4. Verify the generated screen aligns with the live WordPress rendering at `http://nice-solutions.local/`.
