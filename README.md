# Haq Bahoo — Milk Shop Management

A Laravel 12 application for recording and reporting dairy sales: products, dealers,
sale entries with quantity and rate, payment tracking, and a revenue dashboard.

---

## Requirements

| Tool     | Version                     |
| -------- | --------------------------- |
| PHP      | 8.2 or newer                |
| Composer | 2.x                         |
| Node.js  | 20 or newer                 |
| Database | SQLite (default) or MySQL 8 |

---

## Setup

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate

# SQLite (default) — create the database file
touch database/database.sqlite

php artisan migrate --seed
npm run build
```

Then serve it:

```bash
php artisan serve          # http://127.0.0.1:8000
```

For local development with hot reloading, run the Vite dev server alongside it:

```bash
npm run dev
```

### Using MySQL instead

Uncomment the MySQL block in `.env`, create the schema, then re-run `php artisan migrate --seed`.
MySQL is recommended for production — SQLite stores `DECIMAL` with NUMERIC affinity,
which is fine for a single shop but less precise for money at scale.

### XAMPP / Apache

Point the virtual host **document root at `public/`**, never at the project root.
Serving the project root exposes `.env`, `vendor/`, and `storage/logs/` over HTTP.

---

## Seeded logins

`php artisan migrate --seed` creates one account per role and roughly three months of
sales history so the dashboard has something to show.

| Email                   | Password   | Role          |
| ----------------------- | ---------- | ------------- |
| `owner@haqbahoo.test`   | `password` | Administrator |
| `manager@haqbahoo.test` | `password` | Manager       |
| `cashier@haqbahoo.test` | `password` | Cashier       |

> **Change these passwords before deploying anywhere.** There is no public
> registration route — accounts are created by an administrator under **Staff**.

### Roles

| Role          | Can do                                                                  |
| ------------- | ----------------------------------------------------------------------- |
| Administrator | Everything, including staff management                                   |
| Manager       | Sales, products and dealers; may delete records                          |
| Cashier       | Records sales; may edit only their own, and only on the day recorded     |

Authorization is enforced by policies in `app/Policies/`, not just by hiding buttons.

---

## Architecture

```
app/
├── Enums/                  PaymentStatus, ProductUnit, UserRole — labels + badge styling
├── Http/
│   ├── Controllers/        Dashboard, Sale, Product, Dealer, User, Profile, Auth/
│   ├── Middleware/         EnsureUserIsActive, EnsureUserHasRole
│   └── Requests/           Form Requests — all validation lives here
├── Models/                 Product, Dealer, Sale, User
├── Policies/               Per-resource authorization
├── Providers/              Blade directives, rate limiters, model strictness
└── Support/
    ├── Money.php           Currency and quantity formatting
    ├── TableSort.php       Whitelisted sorting (raw sort input never hits orderBy)
    └── ReportPeriod.php    Dashboard date-range presets

resources/
├── css/app.css             Tailwind 4 entry, brand tokens, dark-mode variant
├── js/
│   ├── app.js              Alpine bootstrap
│   └── theme.js            Light / dark / system theme store
└── views/
    ├── components/         Reusable Blade UI (field, button, card, stat, icon…)
    ├── components/layouts/ Application shell
    ├── dashboard.blade.php
    └── sales/ products/ dealers/
```

### Data model

```
Product ──< Sale >── Dealer
              │
              └──> User (who recorded it)
```

`sales.total_amount` and `sales.payment_status` are **derived on every save** in
`App\Models\Sale::booted()` and are deliberately not mass assignable. A client cannot
submit a total that disagrees with `quantity × unit_rate`, or mark a sale paid with
nothing actually paid.

All three domain tables use soft deletes, so financial history stays recoverable.

---

## Commands

```bash
php artisan test                    # run the test suite
./vendor/bin/pint                   # format code
php artisan migrate:fresh --seed    # rebuild the database from scratch
npm run build                       # production asset build
```

---

## Configuration

Shop-specific settings live in `config/shop.php`, driven by these `.env` keys:

| Key                    | Default | Purpose                       |
| ---------------------- | ------- | ----------------------------- |
| `SHOP_CURRENCY`        | `PKR`   | Currency code                 |
| `SHOP_CURRENCY_SYMBOL` | `Rs`    | Symbol shown in the interface |

---

## Project status

| Milestone | Scope                            | Status    |
| --------- | -------------------------------- | --------- |
| 1         | Codebase audit & foundation      | ✅ Done    |
| 2         | Security & backend improvements  | ✅ Done    |
| 3         | Modern UI/UX & frontend          | ✅ Done    |
| 4         | Advanced features & optimization | ✅ Done    |
| 5         | Testing & final delivery         | ⏳ Next    |

## Deploying

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Set in the production `.env`:

```
APP_ENV=production
APP_DEBUG=false          # leaking stack traces exposes credentials
APP_URL=https://your-domain
```

Point the web server's document root at **`public/`**, and change the seeded
passwords. Authentication events are written to `storage/logs/security.log`
with 180-day retention.
