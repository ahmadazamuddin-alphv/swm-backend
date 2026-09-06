# Siaga Selangor — Laravel Admin (Filament)

Government / operations backend for the **Illegal Dumping** POC (Hackathon: Siaga Selangor).

| | |
|---|---|
| **Stack** | Laravel 12 + Filament 5 |
| **Path** | `C:\laragon\www\swm-backend` |
| **Panel URL** | `/admin` (e.g. `http://swm-backend.test/admin`) |
| **Theme** | Soft Selangor red (`#C45C5C`) + soft gold (`#E8C547`) |
| **Pairs with** | Next.js citizen app at `C:\Users\User\Projects\swm` |

---

## Project overview

| Field | Detail |
|---|---|
| **Project** | Illegal Dumping |
| **This app’s role** | Government dashboard, case management, analytics, CCTV AI POC upload |
| **Development type** | Web platform (admin) + computer vision prototype hooks + analytics |
| **Main focus** | Report intake ops, case management, prevention, contractor/zone performance, disposal route planning |

---

## Scope owned by this backend

1. **Government dashboard** — case queue, notifications, status workflow, assignments  
2. **CCTV AI-powered POC** — video upload + detection results storage/review  
3. **Data analysis** — clearance times, hotspots, contractor/zone/lorry performance  
4. **API / data source** for the Next.js citizen dashboard and reporting app  

Citizen-facing UI lives in the Next.js project.

---

## Default admin login (local)

| Field | Value |
|---|---|
| Email | `admin@selangor.gov.my` |
| Password | `password` |

Change this before any shared or production environment.

---

## Government dashboard requirements

The current hardcoded POC implements all nine items below in `/admin`. See the [government operations demo guide](./docs/government-operations-demo.md) for setup and the walkthrough. Recommendations use deterministic local fixtures. Interactive maps use OpenStreetMap tiles and request driving geometry from the public OSRM demo service; there is no live AI, geocoding, traffic feed or dispatch service.

| # | Feature | Details |
|---|---|---|
| 1 | **New-report notifications** | Notify government users when a citizen submits a new waste report. |
| 2 | **View full report details** | Show GPS coordinates, waste photos, reporter details, submission time, waste type, status, and other investigation fields. |
| 3 | **Review false reports** | Mark reports as false/invalid with a **mandatory reason**. |
| 4 | **Update solved status** | Update report status after the dumping site has been cleared. |
| 5 | **Upload proof of resolution** | Allow the responsible party to upload evidence (e.g. clearance photo). |
| 6 | **Risk-based prioritisation** | Prioritise the queue by **risk score** so high-risk cases are handled first. |
| 7 | **AI manpower & lorry recommendation** | Suggest manpower, number of lorries, and intervention deadline to clear the site. |
| 8 | **Show responsible party** | Display which party owns the area and which party is assigned to act. |
| 9 | **Suggest disposal route** | Plot the selected depot → dump site → nearest suitable disposal centre on an interactive map; use straight-line distance to select the suitable driver and centre, then request a road route with distance and estimated driving time. |

---

## CCTV AI-powered POC requirements

The CCTV POC is implemented in `/admin/cctv-detections`. It stores uploaded video locally, plays the source clip, overlays timestamped deterministic detection annotations, exposes waste/activity/location/confidence findings, and can create a government report while retaining the source clip as incident evidence. It is explicitly a simulated computer-vision workflow and does not claim trained-model inference.

| Feature | Details |
|---|---|
| **Upload video for POC** | Working prototype: upload a video to test computer-vision detection. |
| **Detect illegal dumping from video** | Extract / store: waste type, illegal-dumping activity, location (when available), incident evidence (similar to citizen reporting). |

---

## Data analysis requirements

| Feature | Details |
|---|---|
| **Complaint-to-clearance time** | Measure duration from complaint submitted → resolved. |
| **Repeated complaints & persistent areas** | Flag locations with recurring issues. |
| **Contractor performance** | Score by response time, clearance time, case volume, work quality, repeat complaints. |
| **Zone performance** | Compare zones by report volume, clearance rates, unresolved cases. |
| **Lorry assignment pattern** | See whether specific drivers (*abang lori*) repeatedly handle the same *kawasan* or assignments vary. |
| **Abang lori performance over time** | Track jobs completed, efficiency, and repeat issues per driver. |
| **Postcode / taman grouping** | Group reports by postcode or residential area to find hotspots. |
| **Waste by socioeconomic group** | When data exists, classify patterns by B40 / M40 / T20. |
| **Waste by area type** | Analyse whether area type (e.g. industrial Shah Alam) affects waste volume. |

---

## Suggested domain models (Filament resources)

Implement incrementally as Filament Resources / pages:

| Model | Purpose |
|---|---|
| `Report` | Citizen / CCTV cases; GPS, photos/video, waste type, status, risk score, false-report reason |
| `WasteCategory` | Construction waste, furniture, waste piles, etc. |
| `ResponsibleParty` | Department / contractor / contact person / phone |
| `Zone` | Operational *kawasan* boundaries / postcode / taman mapping |
| `Assignment` | Party/lorry assigned to a case + deadline |
| `ResolutionProof` | Clearance evidence media |
| `DisposalCentre` | Destination sites for route suggestions |
| `CctvDetection` | Uploaded video + AI detection output |
| `Contractor` / `Driver` (*abang lori*) | Performance analytics entities |
| `Notification` | New-report alerts for government users |

### Suggested report statuses

`new` → `under_review` → `assigned` → `in_progress` → `solved` | `false_report`

---

## Theme (soft Selangor)

Configured in `app/Providers/Filament/AdminPanelProvider.php`:

| Token | Hex | Role |
|---|---|---|
| Primary | `#C45C5C` | Soft Selangor red |
| Warning / accent | `#E8C547` | Soft gold / yellow |
| Brand name | Selangor Waste Management | Panel branding |

---

## Local setup (Laragon)

```bash
cd C:\laragon\www\swm-backend
composer install
php artisan migrate
php artisan serve
# Or use Laragon virtual host: http://swm-backend.test/admin
```

Ensure SQLite (`database/database.sqlite`) or configure MySQL in `.env` as preferred.

---

## API note for Next.js

Expose authenticated/public JSON endpoints (or Sanctum) for:

- Creating citizen reports (live camera image + GPS + AI suggestions)  
- Listing map / solved reports / categories / responsible parties  
- Pushing detection results consumed by analytics widgets  

Keep Filament as the ops UI; keep citizen UX in Next.js.

---

## Reference

Related citizen POC repo mentioned in requirements:  
https://github.com/diniizzaty24/siaga-selangor.git
