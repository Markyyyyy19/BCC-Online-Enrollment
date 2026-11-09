# BCC Online Enrollment

A Laravel and PostgreSQL enrollment portal for Buenavista Community College. The interface is server-rendered with Blade and plain CSS; Node.js and a frontend build step are not required.

## Requirements

- PHP 8.3 or later with `pdo_pgsql` and `fileinfo`
- PostgreSQL
- Composer

## Run locally

1. Create a PostgreSQL database named `bcc_enrollment` (for example, `psql -U postgres -c "CREATE DATABASE bcc_enrollment;"`) and configure `.env` from `.env.example`.
2. From the project root, install PHP dependencies with `composer install`.
3. Generate the application key with `php artisan key:generate`.
4. Create the application tables and demo records with `php -d extension=pdo_pgsql -d extension=fileinfo artisan migrate --seed`.
5. Start the local server with `php -d extension=pdo_pgsql -d extension=fileinfo -S 127.0.0.1:8000 -t public dev-router.php` (or `composer run dev`).
6. Open `http://127.0.0.1:8000`.

On Windows, if the PHP extensions are already enabled in `php.ini`, the `-d extension=...` flags can be omitted. Set the database host, port, name, username, and password in `backend/.env` before migrating.

## Demo accounts

- Registrar: `registrar@bcc.edu.ph` / `Registrar123!`
- Student: `student@bcc.edu.ph` / `Student123!`

These accounts are for local demonstrations only. Change or remove them before deploying a real enrollment system.

## Verify the system

Run the automated role-access feature tests from the project root:

```sh
php artisan test
```

These tests cover unauthenticated access and student/registrar route boundaries. They do not replace a full manual enrollment and grade-entry demonstration.

## System design

- [Master Project Documentation & Manual](docs/project-documentation.md)
- [System overview and workflow](docs/system-overview.md)
- [Database relationship diagram](docs/database-erd.md)

## Laravel structure

- `app/Http/Controllers/` — authentication, student enrollment, registrar, and dashboard actions
- `app/Http/Middleware/` — active-account and role checks
- `app/Models/` — Eloquent models for users, programs, subjects, and enrollments
- `app/Services/` — curriculum import logic
- `database/migrations/` — PostgreSQL schema migrations
- `database/seeders/` — demo and curriculum seed flow
- `database/sql/` — PostgreSQL schema, seed data, and source curriculum snapshot
- `resources/views/` — Blade layouts and role-specific pages
- `public/` — directly served CSS, fonts, and college logos
- `routes/web.php` — named, session-authenticated web routes

## Included workflows

- Student account registration and sign-in
- Student enrollment applications with major and subject selection, history, and withdrawal before review
- Subject catalog and student grade records
- Registrar application review, student access, program and subject catalogs, and grade encoding

This is a classroom demonstration system, not a production-ready student information system. Before real deployment, add institution-approved identity verification, audit logging, operational monitoring, secure production configuration, backups, and data-protection review.
