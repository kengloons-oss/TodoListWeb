# TodoListWeb

A personal to-do list application built with PHP 8.5, Laravel 13, Blade, and vanilla JavaScript. SQLite is used for local development; Supabase PostgreSQL is the planned hosting database on Render.

## Local setup

1. Install PHP 8.5+, Composer, Node.js, and npm.
2. Install PHP dependencies with `composer install`.
3. Copy `.env.example` to `.env` if needed, then set `APP_KEY` with `php artisan key:generate`.
4. Create the SQLite file at `database/database.sqlite` if it does not exist.
5. Run database migrations with `php artisan migrate`.
6. Install JavaScript dependencies with `npm install`.
7. Start the app and asset bundler with `composer run dev`.

The local database is stored in `database/database.sqlite`. Do not commit `.env` or production credentials.

## Current implementation

- Local registration, login, and logout use Laravel's session authentication.
- Authenticated users can create, edit, delete, complete, and filter their own tasks.
- Tasks include title, optional description, status, priority, due date, and completion time.
- Tasks can store a reminder time with email and mobile-push preferences. While the task list remains open, due reminders appear as in-app notices and are marked as shown.
- Email delivery and operating-system mobile push require hosting and notification-service configuration. In-app notices do not run while the browser is closed.

## Hosting notes

For hosting, deploy the Laravel application to Render using Docker and configure Supabase PostgreSQL through Laravel's `DB_*` environment variables. Apply migrations during deployment. Actual email delivery requires a mail provider. Mobile web push requires HTTPS, browser subscriptions, and a server-side push sender; both notification channels remain inactive until deployment and provider configuration.
