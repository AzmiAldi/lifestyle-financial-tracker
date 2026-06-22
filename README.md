# Lifestyle Financial Tracker

Lifestyle Financial Tracker is a modern personal finance web app focused on financial clarity, calm habit building, and lightweight behavioral reflection. It tracks money movements, budgets, savings goals, mood context, subtle progress, and monthly analytics without feeling like heavy accounting software.

## Key Features

- Premium dark landing page for the product introduction.
- Dashboard with balance overview, monthly income, expenses, budgets, savings goals, mood reflection, progress, and recent transactions.
- Transaction tracking with income/expense categories, behavior notes, and consistent Rupiah formatting.
- Global and custom categories, with user-scoped category access.
- Monthly budgets with usage, remaining amount, and safe/warning/exceeded states.
- Savings goals with progress percentage, remaining target, and deadline context.
- Mood tracker for lightweight emotional spending awareness.
- Subtle gamification with XP, levels, streaks, and achievements.
- Analytics page for monthly review, category spending, budget performance, savings progress, mood-spending correlation, and behavior notes.
- User isolation and authorization across finance data.
- Empty states designed to feel intentional instead of unfinished.

## Tech Stack

- Laravel 12
- PHP 8.2+
- Livewire 4
- Volt
- Flux UI
- Tailwind CSS 4
- Pest PHP
- SQLite for local development
- MySQL or PostgreSQL recommended for production

## Local Installation

Install PHP dependencies:

```bash
composer install
```

Install frontend dependencies:

```bash
npm install
```

Create the environment file:

```bash
cp .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

## SQLite Setup

The project is configured for SQLite by default in `.env.example`:

```dotenv
DB_CONNECTION=sqlite
```

Create the SQLite database file if it does not exist:

```bash
type nul > database/database.sqlite
```

On macOS/Linux, use:

```bash
touch database/database.sqlite
```

Run migrations and default seeders:

```bash
php artisan migrate --seed
```

Default seeders create global categories, achievements, and a test user.

Test user:

```text
Email: test@example.com
Password: password
```

## Optional Demo Data

Demo data is intentionally optional and is not included in the default `DatabaseSeeder`. This keeps existing local data safe.

To seed a portfolio/demo account:

```bash
php artisan db:seed --class=DemoSeeder
```

Demo user:

```text
Email: demo@example.com
Password: password
```

The demo seeder creates sample transactions, budgets, savings goals, mood logs, XP, and achievements for the demo account only.

## Running the App

Start the Laravel development server:

```bash
php artisan serve
```

Start Vite for frontend assets:

```bash
npm run dev
```

Or run the combined development command:

```bash
composer run dev
```

Build production assets:

```bash
npm run build
```

## Testing and Formatting

Run the test suite:

```bash
php artisan test --compact
```

Format dirty PHP files with Pint:

```bash
vendor/bin/pint --dirty --format agent
```

Clear compiled views when polishing Blade/Volt UI:

```bash
php artisan view:clear
```

## Database Notes

SQLite is used for local development and automated tests. It is simple, fast, and convenient while the app is being built.

For production or deployment, MySQL or PostgreSQL is recommended for better concurrency, operational tooling, backups, indexing, and long-term reliability.

Example MySQL configuration:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lifestyle_financial_tracker
DB_USERNAME=root
DB_PASSWORD=
```

Example PostgreSQL configuration:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=lifestyle_financial_tracker
DB_USERNAME=postgres
DB_PASSWORD=
```

After changing database drivers, run:

```bash
php artisan migrate --seed
```

## Portfolio QA Checklist

Before presenting the project:

```bash
php artisan migrate:fresh --seed
php artisan db:seed --class=DemoSeeder
php artisan test --compact
npm run build
vendor/bin/pint --dirty --format agent
```

The `DemoSeeder` step is optional, but recommended for screenshots and walkthroughs.

## Roadmap

Completed foundations:

- Phase 1: Transactions, categories, dashboard summary, authorization, tests.
- Phase 2: Budgeting and savings goals.
- Phase 2.5: UI system polish and premium product experience.
- Phase 3: Mood tracker and behavioral reflection layer.
- Phase 4: Subtle gamification with XP, streaks, and achievements.
- Phase 5: Analytics and reports layer.

Possible next phases:

- AI financial coach.
- Notification and reminder system.
- Export reports to PDF/Excel.
- More advanced analytics and trends.
- Mobile API.

## Product Direction

The app is designed to feel calm, premium, modern, and supportive. It should help users understand their money and lifestyle patterns without judgment, pressure, or spreadsheet overload.
