# Haq Bahoo — Milk Shop Management

A Laravel 12 application for recording and reporting dairy sales: a product catalogue,
dealers, sale entries with quantity and rate, payment tracking, role-based staff
accounts, and a revenue dashboard.

---

## Contents

- [Requirements](#requirements)
- [Setup](#setup)
- [Seeded logins & roles](#seeded-logins--roles)
- [Features](#features)
- [Architecture](#architecture)
- [Testing](#testing)
- [Deploying](#deploying)
- [Security notes](#security-notes)
- [Troubleshooting](#troubleshooting)

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

## Seeded logins & roles

`php artisan migrate --seed` creates one account per role plus roughly three months of
sales history, so the dashboard has something to show.

| Email                   | Password   | Role          |
| ----------------------- | ---------- | ------------- |
| `owner@haqbahoo.test`   | `password` | Administrator |
| `manager@haqbahoo.test` | `password` | Manager       |
| `cashier@haqbahoo.test` | `password` | Cashier       |

> **Change these passwords before deploying anywhere.** There is no public
> registration route — accounts are created by an administrator under **Staff**.

| Role          | Can do                                                              |
| ------------- | ------------------------------------------------------------------- |
| Administrator | Everything, including staff management                              |
| Manager       | Sales, products and dealers; may delete records                     |
| Cashier       | Records sales; may edit only their own, and only on the day recorded |

Authorization is enforced by policies in `app/Policies/`, not merely by hiding
buttons — a cashier who guesses a URL still receives a 403.

Two safeguards prevent lockout: the last remaining administrator cannot be deleted,
and an administrator cannot change their own role or deactivate themselves.

---

## Features

**Sales** — quantity × rate with derived totals, payment status (paid / partial /
unpaid), sale date, optional customer, dealer and notes. Full-text search across
customer, product, dealer and notes; date-range, status and product filters;
sortable columns; configurable page size; CSV export of the filtered set.

**Dashboard** — reporting-period presets (today, 7 days, this month, last month,
90 days, this year, all time) plus a custom range. Hero revenue figure, collected /
outstanding / average tiles, a revenue trend chart bucketed by day or month, and top
products. Aggregates are cached and invalidated the moment a sale changes.

**Catalogue** — products with a unit (litre, kg, piece, packet, dozen) and a default
rate that pre-fills the sale form; dealers with contact details. Both support
soft deletion; a product with recorded sales is deactivated rather than deleted so
history stays intact.

**Interface** — responsive from 390px up, with card layouts replacing wide tables on
small screens. Light, dark and system themes with no flash on load. Keyboard-
accessible, with focus rings and `aria-*` state throughout.

---

## Architecture

```
app/
├── Enums/                  PaymentStatus, ProductUnit, UserRole — labels + badge styling
├── Http/
│   ├── Controllers/        Dashboard, Sale, Product, Dealer, User, Profile, Auth/
│   ├── Middleware/         EnsureUserIsActive, EnsureUserHasRole, SecurityHeaders
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
│   ├── theme.js            Light / dark / system theme store
│   └── forms.js            Double-submit prevention and busy state
└── views/
    ├── components/         Reusable Blade UI (field, button, card, stat, icon, charts…)
    ├── components/layouts/ Application and guest shells
    ├── errors/             403, 404, 419, 429, 500, 503
    └── dashboard, sales/, products/, dealers/, users/, profile/, auth/
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
nothing actually paid. Overpayment is clamped to the sale total; editing a paid sale
upward correctly returns it to *partial*.

All four tables use soft deletes, so financial history and staff attribution stay
recoverable.

> **Date filtering:** `sale_date` is a `date` cast, which Eloquent stores as
> `Y-m-d 00:00:00`. Filter with the `Sale::between()` scope (`whereDate`), never a
> plain `whereBetween` on `Y-m-d` strings — the latter silently excludes the final
> day of every range.

---

## Testing

```bash
php artisan test
```

101 tests / 318 assertions, running against an in-memory SQLite database.

| Suite                      | Covers                                                     |
| -------------------------- | ---------------------------------------------------------- |
| `AuthenticationTest`       | Sign-in, lockout, deactivation mid-session, no enumeration  |
| `AuthorizationTest`        | Per-role access across all four resources                   |
| `SmokeTest`                | Page rendering, sale CRUD, 404s, validation                 |
| `SaleCalculationTest`      | Total and payment-status derivation, clamping, soft deletes |
| `CatalogueManagementTest`  | Product and dealer CRUD, uniqueness, referential rules      |
| `UserManagementTest`       | Staff CRUD, password handling, lockout safeguards           |
| `ProfileTest`              | Own details, password change, account closure               |
| `ReportingTest`            | Sorting, filters, export, periods, cache invalidation       |
| `SecurityHeadersTest`      | Response hardening                                          |
| `MoneyTest`, `ReportPeriodTest` | Formatting and date-range edge cases                   |

---

## Deploying

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

Set in the production `.env`:

```
APP_ENV=production
APP_DEBUG=false          # leaking stack traces exposes credentials
APP_URL=https://your-domain
```

To roll back the caches (for example while debugging): `php artisan optimize:clear`.

---

## Security notes

- **Rate limiting** — 5 failed logins per email+IP triggers a lockout; a separate
  per-IP throttle blunts spray attacks that rotate the email each attempt.
- **No account enumeration** — password-reset responses are identical for known and
  unknown addresses.
- **Session fixation** — the session id is regenerated on login and invalidated on
  logout.
- **Revocation is immediate** — deactivating a user signs them out on their next
  request, not their next login.
- **Audit trail** — sign-ins, failures, lockouts, password changes and account
  changes are written to `storage/logs/security.log` with 180-day retention.
- **Response headers** — `X-Content-Type-Options`, `X-Frame-Options`,
  `Referrer-Policy`, `Permissions-Policy`, and HSTS over HTTPS.
- **Sort injection** — sort input is matched against a whitelist before reaching
  `orderBy`; anything unrecognised falls back to the default.

---

## Troubleshooting

**"Database file does not exist"** — run `touch database/database.sqlite`. Do not put
an absolute path in `DB_DATABASE`; it will not port between machines.

**Styles or scripts missing** — run `npm run build`. In development, `npm run dev`
must be running for Vite to serve assets.

**Changes to `.env` have no effect** — you have a cached config. Run
`php artisan config:clear`.

**"Attempted to lazy load ..." exception** — intentional. Outside production the app
fails loudly on N+1 queries; add the missing `with()` to the query.

**Blank page after deploying** — check `storage/` and `bootstrap/cache/` are writable
by the web server user.

---

## Commands

```bash
php artisan test                    # run the test suite
./vendor/bin/pint                   # format code
php artisan migrate:fresh --seed    # rebuild the database from scratch
npm run build                       # production asset build
php artisan optimize:clear          # drop all caches
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

| Milestone | Scope                            | Status  |
| --------- | -------------------------------- | ------- |
| 1         | Codebase audit & foundation      | ✅ Done |
| 2         | Security & backend improvements  | ✅ Done |
| 3         | Modern UI/UX & frontend          | ✅ Done |
| 4         | Advanced features & optimization | ✅ Done |
| 5         | Testing & final delivery         | ✅ Done |
