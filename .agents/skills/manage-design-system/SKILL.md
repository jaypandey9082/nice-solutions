---
name: manage-design-system
description: Use this skill to define, update, and manage design systems, design tokens, and DESIGN.md files in Google Stitch (StitchMCP) for NICE Solutions. Trigger when establishing typography, color palettes, spacing systems, or applying global design tokens to screens.
---

# Google Stitch — Manage Design System Skill

This skill guides creating, updating, and applying design systems in Google Stitch (`StitchMCP`) aligned with the NICE Solutions visual brand and architectural standards.

## Available StitchMCP Tools

- `create_design_system`: Creates a design system inside a project.
- `create_design_system_from_design_md`: Creates or initializes a design system directly from a markdown-formatted `DESIGN.md` specification.
- `update_design_system`: Updates colors, typography, elevation, or component tokens in an existing design system.
- `list_design_systems`: Lists all available design systems in a project.
- `apply_design_system`: Applies a design system's token schema across project screens.
- `upload_design_md`: Uploads a `DESIGN.md` markdown file to configure or update design guidelines.

## NICE Solutions Brand Tokens

When configuring or updating design systems for NICE Solutions, adhere to the core design tokens:

### Palette
- **Primary / Surface**: `#FFFFFF` (pure white surface), `#F8F9FA` (subtle off-white background)
- **Contrast / Text**: `#1A1A1A` (primary deep charcoal/black text), `#4A4A4A` (secondary muted text), `#71717A` (tertiary text)
- **Borders**: `#E5E7EB` (subtle border rule), `#D1D5DB` (card outline)
- **Brand Accent**: `#E03131` / `#C92A2A` (NICE signature red, used sparingly for badges, accents, active states)
- **Studio Dark Theme**: `#0F0F11` (cinematic deep dark background), `#1A1A1E` (elevated card surface), `#FFFFFF` (contrast text)

### Typography
- **Sans Serif (Interface)**: System sans-serif stack (`Inter`, `-apple-system`, `BlinkMacSystemFont`, `Segoe UI`, `Roboto`, sans-serif)
- **Editorial Serif (Headings & Quotes)**: System serif stack (`Georgia`, `"Times New Roman"`, serif)
- **Scale**: Display (`3rem` / `4.5rem` / `6rem`), H1 (`2.5rem` / `3.5rem`), Body (`1rem` / `1.125rem`, line-height `1.6`)

### Layout & Spacing
- **Max Width**: `1200px` standard container, `1400px` wide showcase
- **Spacing Grid**: 8px base unit (`8px`, `16px`, `24px`, `32px`, `48px`, `64px`, `96px`, `128px`)
- **Radii**: Minimalist (`0px` to `4px` clean rectilinear aesthetic)

## Workflow

### 1. Create Design System from DESIGN.md
To define a complete design system in Stitch using markdown documentation:
```json
// Tool: call_mcp_tool -> Server: StitchMCP -> Tool: create_design_system_from_design_md
{
  "projectId": "<project_id>",
  "designMdContent": "# NICE Solutions Design System\n\n## Colors\n- Primary: #FFFFFF\n- Background: #F8F9FA\n- Foreground: #1A1A1A\n- Accent: #E03131\n- Studio Dark: #0F0F11\n\n## Typography\n- Body: System Sans-Serif (400, 500, 600)\n- Editorial: Georgia, serif (400)\n"
}
```

### 2. Apply Design System to Screens
Once configured, apply the design system across screens to enforce consistency:
```json
// Tool: call_mcp_tool -> Server: StitchMCP -> Tool: apply_design_system
{
  "projectId": "<project_id>",
  "designSystemId": "<design_system_id>",
  "screenIds": ["<screen_id_1>", "<screen_id_2>"]
}
```

### 3. Synchronize Tokens with WordPress `theme.json`
Whenever Stitch design systems are updated, mirror changes into:
- `wp-content/themes/nice/theme.json` (presets: colors, typography, spacing)
- `wp-content/themes/nice/assets/css/site.css` (`@layer tokens`)
