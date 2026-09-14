# Parlour POS Billing & Management System

A Core PHP and MySQL web application for parlour billing and management. It now includes secure account setup/sign-in, master data, appointment booking, transactional POS billing, payment capture, receipts, stock movements, expenses, and a dashboard.

## Required software

- PHP 8.1 or newer, with the `pdo_mysql` extension enabled
- MySQL 8.0 or newer
- Composer 2.x (recommended for generated PSR-4 autoloading; the foundation also includes a small fallback autoloader)
- A web server such as Apache, Nginx, or PHP's built-in development server

## Installation

1. Clone or copy the project.
2. Create your local environment file:

   ```powershell
   Copy-Item .env.example .env
   ```

3. Edit `.env` with your local MySQL database name, user, and password. Never commit `.env`.
4. Create the empty database in MySQL:

   ```sql
   CREATE DATABASE parlour_pos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

5. If Composer is installed, generate its autoloader:

   ```powershell
   composer install
   ```

6. Run the application migrations:

   ```powershell
   php bin/migrate.php
   ```

7. Start the local development server:

   ```powershell
   php -S localhost:8000 -t public
   ```

8. Open `http://localhost:8000/setup` once to create the Super Admin account, then sign in at `http://localhost:8000/login`.

## Environment setup

`.env.example` documents all supported Phase 0 configuration. The important database values are `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, and `DB_CHARSET`. `APP_DEBUG=true` displays development error details; set it to `false` in production so internal/database error details are logged but not shown to visitors.

## Database migrations

Migration files belong in `database/migrations/` and use a sortable timestamp prefix, for example `YYYYMMDDHHMMSS_description.php`. Each file returns an array with an `up` callable. `bin/migrate.php` creates and tracks the framework-level `migrations` table, then executes each unapplied migration in order.

Create a future migration such as:

```php
<?php

use PDO;

return [
    'up' => static function (PDO $pdo): void {
        $pdo->exec('CREATE TABLE example (...) ENGINE=InnoDB');
    },
];
```

Run all pending migrations with `php bin/migrate.php`. Do not alter a migration that has already been applied to a shared environment; create a new migration instead.

## Project structure

- `app/` - MVC support classes, controllers, middleware, views, helpers, and services.
- `bootstrap/` - environment loading, configuration, autoloading, error handling, and PDO setup.
- `config/` - application, database, and route configuration.
- `database/` - migrations, seeds, and schema references.
- `public/` - the only web-accessible directory and the front controller.
- `storage/` - private logs, uploads, invoices, and cache files.
- `docs/` - approved project analysis, database design, roadmap, and architecture.

## Main routes

- `/setup` - one-time Super Admin account creation.
- `/login`, `/dashboard`, `/customers`, `/staff`, `/services`, `/products`, `/suppliers`, and `/expenses`.
- `/appointments` - booking with staff schedule-overlap prevention.
- `/pos` - server-side invoice finalization, payment capture, receipt, and stock ledger posting.
- `/health` - PHP runtime and safe database connectivity status.
