---
name: generate-design
description: Use this skill to generate UI screens, design layouts, and screen variants using Google Stitch (StitchMCP). Trigger when creating new page concepts, generating component variants, or prototyping screens for NICE Solutions.
---

# Google Stitch — Generate Design Skill

This skill guides the creation and iteration of user interface designs and screens using the Google Stitch MCP integration (`StitchMCP`).

## Available StitchMCP Tools

- `create_project`: Initializes a new Stitch design project with a title and device type (`DESKTOP`, `MOBILE`).
- `list_projects`: Lists existing Stitch projects.
- `get_project`: Retrieves detailed project state, including screens, tokens, and layouts.
- `generate_screen_from_text`: Creates a new UI screen inside a Stitch project based on a detailed text prompt.
- `edit_screens`: Applies targeted modifications to existing screens in a project.
- `generate_variants`: Generates design variations for existing screens to explore layout or styling alternatives.
- `get_screen`: Retrieves detailed screen metadata, HTML/CSS structure, and assets.

## Workflow

### 1. Identify or Create Project
Before generating screens, identify the active Stitch project or create one:
```json
// Tool: call_mcp_tool -> Server: StitchMCP -> Tool: create_project
{
  "title": "NICE Solutions Web Platform",
  "deviceType": "DESKTOP"
}
```

### 2. Generate Screen from Text Prompt
When generating a screen, structure the prompt with high clarity:
- **Role & Audience**: Enterprise/creative corporate clients for NICE Solutions.
- **Layout & Structure**: Clear visual hierarchy (navigation, hero, section grid, footer).
- **Style Directives**: Dominant white/off-white background, deep charcoal text, restrained red accent (`#E03131`), editorial serif headings, generous whitespace.
- **Content Authenticity**: Use real NICE division copy (Events / Studio) rather than generic placeholder text.

```json
// Tool: call_mcp_tool -> Server: StitchMCP -> Tool: generate_screen_from_text
{
  "projectId": "<project_id>",
  "prompt": "Create an editorial case study detail layout for NICE Studio featuring client metadata, widescreen film stills, blockquote client praise, and next/prev project cards.",
  "deviceType": "DESKTOP"
}
```

### 3. Generate Design Variants
To explore alternative layouts or densities:
```json
// Tool: call_mcp_tool -> Server: StitchMCP -> Tool: generate_variants
{
  "projectId": "<project_id>",
  "screenId": "<screen_id>",
  "variantCount": 3,
  "instructions": "Explore asymmetric grid layouts and varied typography scale."
}
```

### 4. Review and Export
Retrieve screen details using `get_screen` to review generated HTML/CSS or inspect assets for translation into WordPress block theme patterns.
