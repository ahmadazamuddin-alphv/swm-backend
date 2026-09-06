# Government operations POC

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

Government operations officers reviewing illegal-dumping cases and coordinating clearance in Selangor.

## Product Purpose

Demonstrate the nine government requirements in the supplied Illegal Dumping CSV as one case workflow: receive, investigate, prioritise, assign, clear or reject, and retain evidence.

## Operating Context

The existing Laravel 12 / Filament 5 application at `/admin` is the agreed implementation. Its login, Figtree typography, soft Selangor red and gold theme, and standard Filament controls remain the incumbent interface. Officers scan a priority queue on desktop and inspect individual cases on smaller screens.

## Capabilities and Constraints

- A local demonstration with fictional seeded operational records; no production service claims.
- In-app notifications, report evidence and contacts, risk ordering, mandatory false-report reasons, assignments, resolution uploads and history.
- CCTV upload and review runs support local video playback, timestamped simulated detections, incident evidence and report creation from a detected event.
- Deterministic manpower, lorry and deadline suggestions. Location-based disposal and available-driver suggestions use local coordinates and category compatibility.
- Interactive OpenStreetMap views plot reports, driver depots and disposal centres. Case maps request driving geometry, distance and duration from the public OSRM demo service, with a dashed straight-line fallback when routing is unavailable.
- Uploaded evidence stays on local storage. No external AI, email or push delivery.
- The citizen Next.js demo remains a separate browser-local experience; submissions are simulated in the government app for this block.
- Expanded performance analytics are a separate CSV workstream; the CCTV AI-powered POC is implemented as a bounded local demo surface.

## Evidence on Hand

The supplied CSV, existing domain resources and citizen POC photographs under the sibling frontend's `public/mock/reports/`. Operational examples must be labelled as demo data.

## Product Principles

- Keep case evidence and the next valid action together.
- Distinguish the area's responsible party from its assigned clearance team.
- Explain suggestions and preserve officer decisions and closure evidence.
