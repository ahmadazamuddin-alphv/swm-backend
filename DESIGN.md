---
name: Selangor Waste Management Operations
description: A calm, evidence-led interface for prioritising and coordinating illegal-dumping cases.
colors:
  selangor-red: "#c45c5c"
  selangor-red-soft: "#f3e0e0"
  selangor-red-deep: "#a84848"
  operations-gold: "#e8c547"
  operations-gold-soft: "#f8efc8"
  surface: "#ffffff"
  ink: "#242529"
  muted: "#606168"
  map-standard: "#71717a"
  map-popup-muted: "#52525b"
  rule: "#e4e4e7"
  wash: "#faf8f8"
typography:
  headline:
    fontFamily: "Figtree, ui-sans-serif, system-ui, sans-serif"
    fontSize: "1.125rem"
    fontWeight: 650
    lineHeight: 1.5
  body:
    fontFamily: "Figtree, ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.875rem"
    fontWeight: 400
    lineHeight: 1.65
  label:
    fontFamily: "Figtree, ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.75rem"
    fontWeight: 500
    lineHeight: 1.6
  control:
    fontFamily: "Figtree, ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.8125rem"
    fontWeight: 600
    lineHeight: 1.4
  emphasis:
    fontFamily: "Figtree, ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.9375rem"
    fontWeight: 600
    lineHeight: 1.5
  recommendation:
    fontFamily: "Figtree, ui-sans-serif, system-ui, sans-serif"
    fontSize: "1.75rem"
    fontWeight: 650
    lineHeight: 1.2
  metric:
    fontFamily: "Figtree, ui-sans-serif, system-ui, sans-serif"
    fontSize: "2rem"
    fontWeight: 650
    lineHeight: 1.2
  attribution:
    fontFamily: "Figtree, ui-sans-serif, system-ui, sans-serif"
    fontSize: "10px"
    fontWeight: 400
    lineHeight: 1.4
  cctv-title:
    fontFamily: "Figtree, ui-sans-serif, system-ui, sans-serif"
    fontSize: "1.5rem"
    fontWeight: 650
    lineHeight: 1.3
  cctv-metric:
    fontFamily: "Figtree, ui-sans-serif, system-ui, sans-serif"
    fontSize: "1.2rem"
    fontWeight: 650
    lineHeight: 1.35
rounded:
  xs: "2px"
  sm: "4px"
  control: "6px"
  media: "8px"
  panel: "14px"
spacing:
  xs: "8px"
  sm: "12px"
  md: "16px"
  lg: "24px"
components:
  operations-panel:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.ink}"
    rounded: "{rounded.panel}"
    padding: "{spacing.lg}"
  text-action:
    textColor: "{colors.selangor-red-deep}"
    typography: "{typography.body}"
  active-navigation:
    backgroundColor: "{colors.selangor-red-deep}"
    textColor: "{colors.surface}"
    rounded: "{rounded.control}"
  evidence-media:
    rounded: "{rounded.media}"
    width: "100%"
---

# Design System: Selangor Waste Management Operations

## Overview

**Creative North Star: "The Selangor Operations Desk"**

This system extends Filament into a calm public-operations workspace. It keeps evidence, risk, ownership and the next valid action close together, with restrained red and gold cues that preserve the established Selangor identity without competing with case data.

The interface favors compact summaries, thin structural rules and readable white or dark panels. Photographic evidence gets full available width; recommendations explain their simulated logic; responsibility and assignment remain visually distinct.

**Key Characteristics:**

- Evidence-led investigation pages with a narrower coordination rail.
- Dense but calm summary strips and priority tables.
- Restrained red for navigation and actions, with gold reserved for warnings.
- Thin borders and tonal surfaces instead of decorative depth.
- Full-width operational mapping with responsive stacking below 1100px and a single-column coordination flow on phones.

## Colors

Muted Selangor red carries identity and interaction, while warm gold identifies operational warnings. Neutral surfaces carry most of the screen so risk, evidence and status remain legible.

### Primary

- **Selangor Red:** Used for the brand mark and primary interaction family.
- **Deep Selangor Red:** Used for active navigation, text actions, error copy and focus outlines.
- **Soft Selangor Red:** Used for hover and selection washes.

### Secondary

- **Operations Gold:** Used for warning and elevated-risk accents.
- **Soft Operations Gold:** Used for warning badge backgrounds.

### Neutral

- **Operations Surface:** Main panel background.
- **Operations Ink:** Primary text on light surfaces.
- **Muted Slate:** Supporting labels, timestamps and explanations.
- **Quiet Rule:** Panel borders and dividers.
- **Case Wash:** Subtle closed-case and hover backgrounds.

### Named Rules

**The Evidence First Rule.** Color supports status and action; it never overpowers report photographs or investigation facts.

**The Two Accent Rule.** Red owns identity and action, while gold is reserved for warnings and elevated operational attention.

## Typography

**Display Font:** Figtree with the system sans-serif fallback.
**Body Font:** Figtree with the system sans-serif fallback.

**Character:** One practical sans-serif family keeps the government panel familiar and scan-friendly. Hierarchy comes from size, weight and spacing instead of mixing display faces.

### Hierarchy

- **Headline** (650, 1.125rem, 1.5): Panel and investigation section titles.
- **Title** (650, 2rem, 1.2): Overview metric values with tabular numerals.
- **Body** (400, 0.875rem, 1.65): Explanations and case narrative, capped at 75 characters where prose runs long.
- **Label** (500, 0.75rem, 1.6): Timestamps, field labels, captions and metadata.
- **Control** (600, 0.8125rem, 1.4): Compact map actions, legends and inbox controls.
- **Emphasis** (600, 0.9375rem, 1.5): Driver, destination and responsibility names.
- **Recommendation** (650, 1.75rem, 1.2): Crew and lorry recommendations.
- **Metric** (650, 2rem, 1.2): Overview totals with tabular numerals.
- **Attribution** (400, 10px, 1.4): Required map attribution only.
- **CCTV title** (650, 1.5rem, 1.3): Analysis run heading above the source clip.
- **CCTV metric** (650, 1.2rem, 1.35): Compact detection summary values.

### Named Rules

**The Operational Measure Rule.** Narrative copy stops at 75ch; facts, tables and route comparisons may use the full panel width.

## Layout

The dashboard uses Filament's responsive grid, with a four-cell summary strip, a priority queue that occupies two of three desktop columns and a full-width operations map. Investigation pages use a 1.7-to-1 main-and-rail grid with 24px gaps. At 1100px the rail becomes a two-column row below the evidence; at 640px it becomes a single vertical stack. Panels use 24px padding on larger screens and 18px on phones. Maps hold a stable 430px overview height and a compact 330px route height, reducing to 340px and 290px on phones.

**The Case Spine Rule.** Evidence, reporter details, proof and history form one continuous main column; accountability and operational recommendations form the adjacent rail.

## Elevation & Depth

Custom operations panels are flat at rest. White or charcoal surface contrast, 1px quiet rules and tonal washes create separation. The active sidebar item alone receives a small shadow because it represents current navigation state.

### Shadow Vocabulary

- **Active navigation** (`0 1px 2px rgb(196 92 92 / 25%)`): A restrained state cue used only on the selected sidebar item.

### Named Rules

**The Flat Operations Rule.** Use borders and surface shifts for structure; do not introduce card stacks or decorative floating shadows.

## Shapes

Large operational panels and outcome banners use gently curved 14px corners. Evidence images and maps use 8px corners, and compact controls use Filament's restrained control radius. Dividers stay straight and thin. Map markers use circles for reports, squares for driver depots and rotated squares for disposal centres; direct polylines distinguish the approach leg from the disposal leg.

**The Controlled Curve Rule.** Reserve the 14px radius for bounded information surfaces; never turn every label or block into a pill.

## Components

### Buttons

- **Primary:** Uses the established Filament red family with compact, direct labels naming the workflow action.
- **Text action:** Deep red, medium weight and underlined on hover.
- **Hover / Focus:** Hover changes the surface or adds an underline; keyboard focus uses a 2px deep-red outline with 4px offset.
- **Disabled:** Reduces opacity to 55% and retains a clear wait cursor during Livewire work.

### Chips

- **Style:** Semantic Filament badges identify risk and status; warning badges use the soft-gold surface.
- **State:** Color communicates workflow meaning while the text label remains sufficient on its own.

### Cards / Containers

- **Corner Style:** Gently curved panel edge (14px).
- **Background:** Operations surface in light mode and charcoal surface in dark mode.
- **Shadow Strategy:** Flat by default.
- **Border:** One-pixel quiet rule.
- **Internal Padding:** 24px desktop and 18px phone.

### Inputs / Fields

- **Style:** Use native Filament fields and validation patterns so assignment, evidence and closure forms remain consistent with the admin platform.
- **Focus:** Preserve Filament's focus treatment; custom interactive content uses the deep-red outline.
- **Error / Disabled:** Errors use deep red; disabled custom actions use reduced opacity.

### Navigation

The active sidebar entry uses a deep-red background with white text and icon. Other entries receive a soft-red hover wash. The top bar uses a two-pixel soft-red lower rule and the brand name uses bold Selangor red.

### Metric Strip

Four equal cells share a single bordered surface. Values use large tabular numerals; labels and explanations stay muted. On phones the strip becomes a two-by-two grid with internal rules preserved.

### Investigation Panel

Evidence media fills its column. Two-column fact lists, accountability blocks, recommendation values, route stops and a chronological history share the same spacing rhythm. Open and closed states change content and actions without changing the page's visual grammar.

### Operations Map

Leaflet maps sit inside the same thin-rule media frame as evidence. Report markers inherit high, elevated and standard risk colours; driver and disposal markers use distinct shapes. Case maps render OSRM road geometry over a quieter dashed direct-route fallback and report road distance and estimated duration in a compact status label. Popups use safe text content and link directly to investigation. The visible legend and OpenStreetMap attribution remain outside decorative hierarchy, and service-error messages explain which fallback remains available.

### CCTV Review

CCTV review treats the source video as primary evidence. The player owns the widest surface, with detection boxes appearing at their stored timestamps and a confidence slider changing visibility without mutating the saved result. A compact metric strip gives officers the incident count, waste category, camera metadata and simulated mode before the timeline and finding copy. Timeline rows are plain text buttons that jump the player to an observation; incident evidence, ordinary observations and the deterministic-demo boundary remain readable without color alone.

- **Player surface:** Bordered 14px panel with a full-width 8px media frame, dark video canvas, timestamp label and a 44px minimum confidence range control.
- **Detection overlays:** Gold 2px boxes mark ordinary observations; deep red 2px boxes mark incident evidence. Labels use compact 4px corners and soft semantic washes.
- **Timeline:** Event rows are full-width text buttons with 14px vertical padding, quiet separators, deep-red timestamps and a wash on hover or the active timestamp.
- **Responsive behavior:** The four-cell metric strip becomes a two-by-two grid at 640px; the timeline and finding panels stack below 1100px; player controls become one column on phones.

## Do's and Don'ts

### Do:

- **Do** put risk, status and submission time at the top of every case.
- **Do** keep area ownership separate from the team assigned to a case.
- **Do** label fictional data, deterministic recommendations, road-route estimates and straight-line selection logic plainly.
- **Do** allow evidence media and operational tables to use the width they need.
- **Do** preserve usable focus states and 44px phone targets for custom actions.
- **Do** keep risk legends, map attribution and route fallback limitations visible beside every operational map.
- **Do** make the video, timestamp events, confidence threshold and simulated-analysis boundary visible in the first review pass.

### Don't:

- **Don't** imply that recommendations or route times use live AI, traffic conditions or dispatch data.
- **Don't** use gradients, ornamental illustration or large shadows in operations surfaces.
- **Don't** use color as the only signal for risk, status or errors.
- **Don't** compress report evidence into small decorative thumbnails.
- **Don't** merge the area owner and assigned clearance team into one ambiguous label.
- **Don't** present the dashed direct-line fallback as road navigation.
- **Don't** label deterministic CCTV annotations as live AI inference or hide the source clip behind summary metrics.

### CCTV review workspace update (2026-09-07)

The CCTV review now uses one compact Filament heading and a camera/status row. The primary desktop surface has a shared viewport height budget: fitted source video and a four-observation timeline on the left, consolidated findings and report navigation on the right. Each fact appears once. Detailed evidence and limitations use a native disclosure; the simulation boundary remains visible. The video shell is fitted in both dimensions using intrinsic aspect ratio so overlays share the rendered image geometry. At narrow content widths the page returns to normal document flow with a two-column observation list. Actual browser fit requires user visual verification.
