# Mini Lara Industrial - Ultra-Lightweight PHP Framework (v1.3.0)

Mini Lara is a high-performance, ultra-minimalist PHP boilerplate designed for industrial-grade business systems (ERP, CRM, Ledger). It combines a **7-file core** architecture with the power of **Laravel 13** components, delivering extreme speed without sacrificing professional features.

## What's New in v1.3.0 (The Ultimate Industrial Edition)
- **PWA Support**: Fully installable as a native app on Mobile & Desktop with offline capabilities and premium icon assets.
- **Strict .env Branding**: Developer Information and core branding are now strictly controlled via `.env` for maximum security and integrity.
- **Ultra-Mini Core**: Entire framework logic consolidated into just 7 core files for maximum transparency and speed.
- **Advanced Migration Engine**: Full version-controlled database schema management with robust "Full Reset" capabilities.
- **Industrial Print Infrastructure**: High-contrast, ledger-ready A4 document generation system with standalone template control.
- **Dynamic Models**: Zero-configuration Eloquent models that automatically map to database tables.

## Industry Standard Stack
- **Engine**: PHP ^8.3 (Optimized for 2026 standards)
- **Routing**: `nikic/fast-route` (The king of speed)
- **HTTP Layer**: `symfony/http-foundation` (Professional Request handling)
- **Database**: `illuminate/database` (Eloquent ORM & Query Builder)
- **Validation**: `illuminate/validation` (Laravel's standard validator)
- **Logging**: `monolog/monolog` (Professional logging)
- **Frontend**: Tailwind CSS 4 + Alpine.js + jQuery 4.0

## Core CLI Commands
Mini Lara comes with a powerful CLI tool `ml`:
- `php ml migrate` — Run pending database migrations.
- `php ml migrate --fresh` — Wipe and re-migrate the database.
- `php ml make:migration {name}` — Create a new version-controlled schema file.
- `php ml db:seed` — Populate the database with seeders.
- `php ml run` — Start the built-in PHP development server.
- `php ml gen-keys` — Generate secure encryption keys for `.env`.

## Installation
```bash
composer create-project softdevhassan/minilara your-app-name
```

### Default Credentials
- **URL**: `/auth/login`
- **Username**: `admin`
- **Password**: `admin`

## Authors
- **Hassan Ali** (Lead Developer)
- **Links**: [linktr.ee/softdevhassan](https://linktr.ee/softdevhassan)

## License
MIT - Free to use for personal and commercial projects.
