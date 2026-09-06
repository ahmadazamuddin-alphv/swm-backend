# Selangor Waste Management Admin (Laravel + Filament)

Government / operations backend for the **Illegal Dumping** POC (Hackathon: Siaga Selangor).

| | |
|---|---|
| **Stack** | Laravel 12 + Filament 5 + Leaflet + OSRM + PHP 8.2+ |
| **Panel** | `/` redirects to `/admin` |
| **Theme** | Soft Selangor red (`#C45C5C`) + soft gold (`#E8C547`) |
| **Pairs with** | Next.js citizen app |

Full feature scope: [REQUIREMENTS.md](./REQUIREMENTS.md)

Government operations walkthrough: [docs/government-operations-demo.md](./docs/government-operations-demo.md)

---

## Requirements

- PHP 8.2+
- Composer
- Node.js + npm (Vite assets)
- MySQL (Laragon) or SQLite

---

## Quick start (Laragon)

```bash
cd C:\laragon\www\swm-backend

# 1. Dependencies
composer install
npm install

# 2. Environment
copy .env.example .env
php artisan key:generate

# 3. Database (configure DB_* in .env for MySQL)
php artisan migrate --seed
php artisan storage:link

# 4. Run
npm run build
# Prefer Laragon vhost, or:
php artisan serve
```

**Admin panel**

- Laragon: [http://swm-backend.test](http://swm-backend.test) → `/admin`
- Artisan serve: [http://127.0.0.1:8000/admin](http://127.0.0.1:8000/admin)

**Default login:** `admin@selangor.gov.my` / `password` (from seeder — change before any shared/production use)

Optional all-in-one dev processes (server, queue, logs, Vite):

```bash
composer run dev
```

Reset demo data:

```bash
php artisan migrate:fresh --seed
```

---

## Potholes operations (admin)

Nested under **Operations overview** in the sidebar:

| URL | Purpose |
|---|---|
| `/admin/potholes` | Budget meter, Cases \| Forecast tabs, spend priority, impact panel |
| `/admin/pothole-cases` | Full CRUD for pothole cases |

Demo budget: **RM 18,000,000** annual; spent from in-progress/solved `budget_spent_rm` (~RM 2.7M seeded).

---

## Citizen API (Next.js)

Base path: `/api`

| Method | Endpoint | Purpose |
|---|---|---|
| `GET` | `/api/waste-categories` | List waste types |
| `GET` | `/api/zones` | List zones + responsible party |
| `GET` | `/api/responsible-parties` | List parties |
| `GET` | `/api/reports` | Map pins (`?status=` / `?open_only=1`) |
| `GET` | `/api/reports/{id}` | Report detail |
| `POST` | `/api/reports` | Create citizen report (`multipart`: photo/photos, GPS, category, optional `ai_suggestions`) |

No API auth in v1 (POC). Add Sanctum/API keys before production.

---

## Theme

Configured in `app/Providers/Filament/AdminPanelProvider.php`:

| Token | Hex | Role |
|---|---|---|
| Primary | `#C45C5C` | Soft Selangor red |
| Warning / accent | `#E8C547` | Soft gold |
| Brand | Selangor Waste Management | Panel branding |

---

## Project role

This app owns:

1. **Government dashboard** — case queue, status workflow, assignments  
2. **CCTV AI POC** — video upload + detection review  
3. **Analytics** — clearance times, hotspots, contractor/zone/lorry performance  
4. **API / data source** for the Next.js citizen app  

Citizen-facing UI lives in the Next.js project.
