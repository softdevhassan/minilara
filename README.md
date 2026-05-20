# MiniLara Industrial

[![Latest Version](https://img.shields.io/packagist/v/minilara/minilara.svg?style=flat-square)](https://packagist.org/packages/minilara/minilara)
[![PHP Version](https://img.shields.io/badge/PHP-8.3%2B-blue.svg?style=flat-square)](https://php.net)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg?style=flat-square)](LICENSE)
[![Total Downloads](https://img.shields.io/packagist/dt/minilara/minilara.svg?style=flat-square)](https://packagist.org/packages/minilara/minilara)

**Ultra-lightweight PHP 8.3 framework with a 7-file core, powered by real Laravel 13 Illuminate components.**

Built for industrial-grade business systems — ERP, CRM, ledger, and internal tooling — where you need Eloquent ORM, Blade templating, and Laravel Validation without the full Laravel overhead.

---

## Why MiniLara?

Most PHP micro-frameworks strip out the ecosystem you actually need. MiniLara keeps it. You get Eloquent ORM, Blade templating, Laravel Validation, and Symfony HTTP Foundation — all wired into a transparent 7-file core you can read and understand in under an hour.

| Feature                        | MiniLara | Raw Laravel | Other Micro-Frameworks |
|-------------------------------|----------|-------------|------------------------|
| Laravel Illuminate components | Yes      | Yes         | No                     |
| 7-file core architecture      | Yes      | No          | Yes                    |
| CLI migration engine          | Yes      | Yes         | No                     |
| PWA support built-in          | Yes      | No          | No                     |
| Tailwind CSS 4 + Alpine.js    | Yes      | Optional    | No                     |
| Industrial ledger printing    | Yes      | No          | No                     |

---

## Quick Start

```bash
composer create-project minilara/minilara your-app-name
cd your-app-name
cp .env.example .env
php ml run
```

Open `http://localhost:8000` and log in at `/auth/login` with the default credentials below.

---

## Requirements

- PHP 8.3 or higher
- Composer 2+
- Node.js and pnpm (for frontend asset compilation)
- MySQL, SQLite, or PostgreSQL

---

## Tech Stack

| Layer        | Package                             |
|--------------|-------------------------------------|
| Routing      | nikic/fast-route                    |
| HTTP         | symfony/http-foundation             |
| ORM          | illuminate/database (Eloquent)      |
| Templating   | illuminate/view (Blade)             |
| Validation   | illuminate/validation               |
| Events       | illuminate/events                   |
| Filesystem   | illuminate/filesystem               |
| Translations | illuminate/translation              |
| Logging      | monolog/monolog                     |
| Dates        | nesbot/carbon                       |
| CLI          | symfony/console                     |
| Environment  | vlucas/phpdotenv                    |
| Frontend     | Tailwind CSS 4, Alpine.js, jQuery 4 |
| Build Tool   | Vite                                |

---

## CLI Commands

MiniLara ships with a CLI tool called `ml`. Run all commands from the project root.

```bash
# Database
php ml migrate                        # Run all pending migrations
php ml migrate --fresh                # Wipe the database and re-run all migrations
php ml make:migration create_users    # Create a new version-controlled migration file
php ml db:seed                        # Run all database seeders

# Security
php ml gen-keys                       # Generate secure encryption keys for .env

# Development
php ml run                            # Start the built-in PHP development server
```

---

## Project Structure

```
minilara/
|
|-- app/
|   |
|   |-- api/                              # Lightweight JSON API endpoints
|   |   `-- search.php                    # Global search endpoint
|   |
|   |-- config/                           # Application configuration files
|   |   |-- modules.php                   # Module registration and settings
|   |   `-- permissions.php               # Role and permission definitions
|   |
|   |-- core/                             # Framework kernel (7-file core)
|   |   |-- Console.php                   # CLI command dispatcher
|   |   |-- DB.php                        # Eloquent connection bootstrapper
|   |   |-- DevTool.php                   # Development utilities and debugging
|   |   |-- Framework.php                 # Application container and bootstrapper
|   |   |-- Installer.php                 # First-run installation handler
|   |   |-- Router.php                    # Route dispatcher (wraps nikic/fast-route)
|   |   `-- functions.php                 # Global helper functions
|   |
|   |-- database/
|   |   |-- migrations/                   # Version-controlled schema files
|   |   |   |-- 2023_01_01_000001_create_users_table.php
|   |   |   `-- 2023_01_01_000002_create_settings_table.php
|   |   `-- seeds/                        # Database seeders
|   |       `-- AdminUserSeeder.php       # Creates the default admin user
|   |
|   |-- lang/                             # Translation files
|   |   `-- en/
|   |       `-- validation.php            # English validation messages
|   |
|   |-- public/                           # Publicly served static assets
|   |   |-- images/
|   |   |   |-- default-avatar.webp
|   |   |   |-- favicon.ico
|   |   |   |-- pwa-icon-192.png
|   |   |   `-- pwa-icon.png
|   |   |-- manifest.json                 # PWA web manifest
|   |   `-- sw.js                         # PWA service worker (offline support)
|   |
|   `-- src/                              # Frontend source files (compiled by Vite)
|       |-- assets/
|       |   |-- css/
|       |   |   `-- app.css               # Tailwind CSS 4 entry point
|       |   `-- js/
|       |       `-- app.js                # Alpine.js + jQuery entry point
|       |
|       |-- layout/                       # Shared Blade layout components
|       |   |-- app.blade.php             # Base application layout
|       |   |-- breadcrumbs.blade.php     # Breadcrumb navigation component
|       |   `-- navbar.json               # Navigation menu configuration
|       |
|       `-- pages/                        # Blade page templates
|           |-- auth/
|           |   `-- login.blade.php       # Login page
|           |-- errors/
|           |   |-- 404.blade.php         # 404 Not Found page
|           |   `-- 500.blade.php         # 500 Server Error page
|           |-- main/                     # Core application pages
|           |   |-- home.blade.php        # Dashboard home
|           |   |-- profile.blade.php     # User profile page
|           |   |-- settings.blade.php    # Application settings page
|           |   `-- users.blade.php       # User listing and management
|           |-- manage/
|           |   `-- user.blade.php        # Individual user management
|           `-- prints/                   # Print-ready A4 document templates
|               |-- reports/
|               |   `-- dummy.blade.php   # Report print template
|               `-- single/
|                   `-- dummy.blade.php   # Single-record print template
|
|-- bin/
|   `-- minilara                          # Internal CLI binary
|
|-- .env.example                          # Environment configuration template
|-- .gitignore
|-- .htaccess                             # Apache URL rewrite rules
|-- index.php                             # Front controller (application entry point)
|-- ml                                    # MiniLara CLI tool (run as: php ml <command>)
|-- composer.json                         # PHP dependencies
|-- composer.lock
|-- package.json                          # Frontend dependencies
|-- pnpm-lock.yaml
`-- vite.config.js                        # Vite build configuration
```

---

## Core Features

### The 7-File Core (`app/core/`)

The entire framework kernel lives in seven files. There is no hidden magic, no auto-generated configuration, and no service container you cannot inspect. Each file has a single responsibility:

- `Framework.php` — bootstraps the application, binds services, and handles the request lifecycle
- `Router.php` — dispatches HTTP requests to controllers using nikic/fast-route
- `DB.php` — initializes the Eloquent ORM connection from `.env` configuration
- `Console.php` — registers and dispatches all `php ml` CLI commands
- `Installer.php` — handles first-run setup and environment validation
- `DevTool.php` — provides debugging utilities available in development mode
- `functions.php` — global helpers available throughout the application

### CLI Migration Engine (`app/database/migrations/`)

Migrations are prefixed with a timestamp and tracked in the database. The engine applies only pending migrations in version order, with full reset support via `--fresh`.

### PWA Support (`app/public/`)

The `manifest.json` and `sw.js` service worker are included out of the box. The application installs as a native PWA on desktop and mobile with offline capability, using the icon assets in `app/public/images/`.

### Industrial Print Infrastructure (`app/src/pages/prints/`)

Print templates are standalone Blade files organized into `reports/` for multi-record documents and `single/` for individual record sheets. They do not inherit the main application layout, giving full control over print styling and A4 formatting.

### Configuration System (`app/config/`)

- `modules.php` — registers application modules and controls which features are active
- `permissions.php` — defines roles and permission maps used by the middleware layer

### API Layer (`app/api/`)

Lightweight PHP endpoints for AJAX and JSON responses. `search.php` provides the global search feature used by the navigation bar.

---

## Built For Industrial Use Cases

MiniLara is purpose-built for systems that need speed, database reliability, and full print control — not for public-facing marketing websites.

- **ERP Systems** — Inventory tracking, purchasing workflows, production management
- **CRM Platforms** — Customer records, sales pipelines, contact management
- **Ledger and Accounting** — High-contrast A4 document printing, transaction records
- **Internal Business Tools** — Admin dashboards, reporting systems, operational tools

---

## Default Login Credentials

| Field    | Value       |
|----------|-------------|
| URL      | /auth/login |
| Username | admin       |
| Password | admin       |

Change the default credentials immediately before deploying to any non-local environment.

---

## Contributing

Contributions, bug reports, and pull requests are welcome. Please open an issue first to discuss any significant changes.

---

## Author

**Hassan Ali** — Lead Developer
https://linktr.ee/softdevhassan

---

## License

MIT License — free to use for personal and commercial projects.

---

## Keywords

php micro-framework, laravel boilerplate, php erp framework, eloquent orm lightweight, blade php, php crm, fast-route php, php 8.3 framework, industrial php, composer php boilerplate, tailwind css 4 php, alpine.js php, pwa php framework, php ledger system, php business framework
