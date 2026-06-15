# Lifestyle Financial Tracker

A modern personal finance tracker for transactions, budgets, savings goals, mood context, and subtle progress tracking.

## Database Configuration

This project currently uses SQLite for local development. That is fine for day-to-day development and automated tests.

For production or deployment, MySQL or PostgreSQL is recommended so the application can use a more robust database engine for concurrent users, indexing, backups, and operational tooling.

To switch database drivers, update the database variables in `.env`:

```dotenv
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

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

After changing the database connection, run migrations and seeders for the target database:

```bash
php artisan migrate --seed
```
