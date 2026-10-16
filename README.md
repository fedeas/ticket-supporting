# Ticket Supporting

A web application for submitting and managing support / fault tickets, built with Laravel, Livewire and Flux UI. The interface is in Greek.

## Features

- Customer and admin roles
- Customers can create, edit and track their own tickets
- Admin dashboard with status filters (pending, in progress, completed) and search
- Ticket history with notes and status changes
- Email notifications for new tickets, updates and completion
- PDF export of tickets (attached to the admin notification email)
- Authentication with email verification and two-factor authentication (Laravel Fortify)
- Soft deletes for tickets

## Tech stack

- PHP 8.3+, Laravel 13
- Livewire 4, Flux UI
- Tailwind CSS 4, Vite
- DomPDF
- Pest for tests

## Getting started

```bash
git clone <repository-url> ticket-supporting
cd ticket-supporting
composer setup
php artisan db:seed
composer dev
```

`composer setup` installs the dependencies, creates the `.env` file, generates the app key, runs the migrations and builds the frontend assets. By default the app uses SQLite.

The seeder creates an admin account:

| Email | Password |
| --- | --- |
| admin@example.com | password |

Change these credentials before using the app anywhere other than locally.

## Running the tests

```bash
composer test
```

## Docker

A `dockerfile` (Apache + PHP 8.3) and a Laravel Sail `compose.yaml` are included.

```bash
docker build -t ticket-supporting .
docker run -p 8080:80 --env-file .env ticket-supporting
```

## Deployment

`deploy.sh` pulls the latest code and rebuilds dependencies, caches and assets on a server. Set `PROJECT_DIR` to the location of the project before running it.

## License

MIT
