# TodoListWeb Setup Guide

This guide sets up TodoListWeb on a new Windows computer from GitHub. The app uses Laravel 13, PHP, Blade, JavaScript/Vite, and the project's Supabase PostgreSQL database.

## 1. Install the required tools

Install and make sure these commands work in a new PowerShell window:

- PHP 8.3 or newer, with `pdo_pgsql` enabled. PHP 8.5 is used on the original development computer.
- Composer 2.
- Node.js LTS, which includes npm.
- Git.

Check the installations:

```powershell
php -v
php -m | Select-String 'pdo_pgsql|openssl|mbstring|fileinfo'
composer --version
node --version
npm --version
git --version
```

The PHP module check must show `pdo_pgsql`. If it is missing, enable the PostgreSQL PDO extension in the `php.ini` used by the `php` command, then open a new PowerShell window and check again. The included startup script can find and enable `pdo_pgsql` for some WinGet PHP installations, but installing/enabling the extension directly is more reliable.

## 2. Download the project

Open PowerShell and run:

```powershell
cd $HOME\Desktop
git clone https://github.com/kengloons-oss/TodoListWeb.git
cd .\TodoListWeb
```

If the repository is private, sign in to GitHub before cloning. The current repository is public, so cloning does not require GitHub authentication.

## 3. Install project dependencies

From the project directory:

```powershell
composer install
npm install
```

If Composer reports that PHP extensions are missing, enable them for the PHP version Composer is using. Check that `php --version` and `composer --version` are using the expected PHP installation.

## 4. Create and configure `.env`

Create your local environment file if Composer has not already created it, then open it:

```powershell
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
notepad .env
```

In `.env`, configure the following values. Get the database connection values from the project owner's Supabase Dashboard under **Connect**. Use the host, port, database name, and username from the chosen connection method; when using a pooler, copy its pooler username exactly.

```dotenv
APP_URL=http://127.0.0.1:8000
APP_TIMEZONE=Asia/Kuala_Lumpur

DB_CONNECTION=pgsql
DB_HOST=<Supabase host>
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=<Supabase username>
DB_PASSWORD="<Supabase database password>"
DB_SSLMODE=require
```

To send real reminder emails through Gmail, configure an App Password for the Gmail account that will send mail. Do not use the normal Google account password.

```dotenv
MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=<sender Gmail address>
MAIL_PASSWORD="<Gmail App Password>"
MAIL_FROM_ADDRESS="${MAIL_USERNAME}"
MAIL_FROM_NAME="${APP_NAME}"
```

Replace each placeholder with the actual value and remove the angle brackets. Keep `.env` private: it contains database and mail credentials and is excluded from Git. Do not paste credentials into `Setup.md`, commit them, or send them in chat. `.env.example` is only a template and does not contain working credentials.

Generate the local Laravel key and clear cached configuration after editing `.env`:

```powershell
php artisan key:generate
php artisan config:clear
```

Keep the generated `APP_KEY` in this computer's `.env`; do not generate a new one every time the app starts.

## 5. Prepare the database

Run the Laravel migrations against the configured Supabase database:

```powershell
php artisan migrate
```

If the connection fails, recheck the Supabase host, port, username, password, SSL mode, and whether the Supabase project is active. Pooler usernames often include the project reference; use the value shown on the **Connect** panel rather than guessing it.

## 6. Start the website and reminder scheduler

Run:

```powershell
.\start-dev.bat
```

Keep this terminal window open while using the app. The script starts Laravel, Vite, the queue listener, and the scheduler. Open [http://127.0.0.1:8000](http://127.0.0.1:8000) in a browser. Press `Ctrl+C` in the terminal to stop the services.

Reminder emails are checked every minute while the scheduler is running. They are sent to the email address of the signed-in user. For a quick check, create a task with an email reminder a few minutes in the future, leave the development services running, and check the inbox and spam folder. The command below manually processes reminders that are already due:

```powershell
php artisan reminders:send-due-emails
```

For troubleshooting, check `storage/logs/laravel.log`. The mobile push option is not configured for local use yet.

## Common problems

### `php`, `composer`, `node`, `npm`, or `npx` is not recognized

Install the missing tool, add it to Windows `PATH`, then close and reopen PowerShell.

### `Could not load PHP's pdo_pgsql driver`

Enable `pdo_pgsql` for the PHP installation being used, or use the startup script's WinGet PHP fallback. Check `where.exe php` and `php --ini` if more than one PHP installation is installed.

### Supabase connection or authentication error

Copy the exact connection parameters from Supabase **Connect**. Confirm the project is active and that `DB_SSLMODE=require` is present. Do not put the full connection URL in a public file because it may contain the database password.

### The app opens, but a reminder email does not arrive

Confirm the reminder time has passed, the task is still pending, the email channel is selected, and the signed-in account has the expected email address. Keep `start-dev.bat` running. Check Gmail's spam folder and `storage/logs/laravel.log`; also confirm the App Password is current and the `MAIL_*` values are correct. To retry due reminders manually, run `php artisan reminders:send-due-emails`.

## Updating the project later

Stop the running development services with `Ctrl+C`, then in the project directory run:

```powershell
git pull
composer install
npm install
php artisan migrate
```

Start the app again with:

```powershell
.\start-dev.bat
```
