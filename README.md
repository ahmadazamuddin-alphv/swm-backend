# Selangor Waste Management Admin (Laravel + Filament)

Government / operations backend for the **Illegal Dumping** POC (Hackathon: Siaga Selangor).

| | |
|---|---|
| **Stack** | Laravel 12 + Filament 5 + PHP 8.2+ |
| **Panel** | `/admin` |
| **Theme** | Soft Selangor red (`#C45C5C`) + soft gold (`#E8C547`) |
| **Pairs with** | Next.js citizen app |

Full feature scope: [REQUIREMENTS.md](./REQUIREMENTS.md)

---

## Requirements

- PHP 8.2+
- Composer
- Node.js + npm (Vite assets)
- SQLite (default) or MySQL via Laragon

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

# 3. Database (SQLite by default)
# Ensure database/database.sqlite exists, or set DB_* in .env for MySQL
php artisan migrate --seed

# 4. Run
npm run build
# Prefer Laragon vhost, or:
php artisan serve
```

**Admin panel**

- Laragon: [http://swm-backend.test/admin](http://swm-backend.test/admin)
- Artisan serve: [http://127.0.0.1:8000/admin](http://127.0.0.1:8000/admin)

**Default login:** `admin@selangor.gov.my` / `password` (from seeder — change before any shared/production use)

Optional all-in-one dev processes (server, queue, logs, Vite):

```bash
composer run dev
```

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
