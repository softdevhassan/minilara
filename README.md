# MiniLara Industrial — Ultra-Lightweight PHP Framework

[![Latest Version](https://img.shields.io/packagist/v/minilara/minilara.svg?style=flat-square)](https://packagist.org/packages/minilara/minilara)
[![PHP Version](https://img.shields.io/badge/PHP-8.3%2B-blue.svg?style=flat-square)](https://php.net)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg?style=flat-square)](LICENSE)
[![Total Downloads](https://img.shields.io/packagist/dt/minilara/minilara.svg?style=flat-square)](https://packagist.org/packages/minilara/minilara)

> **Laravel power. Micro-framework weight.**  
> A 7-file PHP 8.3 boilerplate built for industrial ERP, CRM, and ledger systems — using real Laravel 13 Illuminate components under the hood.

---

## ✨ Why MiniLara?

Most PHP micro-frameworks make you give up Laravel's ecosystem. MiniLara doesn't.  
You get **Eloquent ORM, Blade templating, Laravel Validation, and Symfony HTTP Foundation** — all wired into a blazing-fast 7-file core.

| Feature | MiniLara | Raw Laravel | Other Micro-Frameworks |
|---|---|---|---|
| Laravel Illuminate components | ✅ | ✅ | ❌ |
| 7-file core architecture | ✅ | ❌ | ✅ |
| CLI migration engine | ✅ | ✅ | ❌ |
| PWA support built-in | ✅ | ❌ | ❌ |
| Tailwind CSS 4 + Alpine.js | ✅ | Optional | ❌ |
| Industrial ledger printing | ✅ | ❌ | ❌ |

---

## 🚀 Quick Start

```bash
composer create-project minilara/minilara your-app-name
cd your-app-name
cp .env.example .env
php ml run
```

Open your browser at `http://localhost:8000`  
Login at `/auth/login` → **admin / admin**

---

## 📦 Installation Requirements

- **PHP** 8.3+
- **Composer** 2+
- **Node.js** + pnpm (for frontend assets)
- A supported database (MySQL, SQLite, PostgreSQL)

---

## 🧰 Tech Stack

| Layer | Technology |
|---|---|
| Routing | `nikic/fast-route` |
| HTTP | `symfony/http-foundation` |
| ORM | `illuminate/database` (Eloquent) |
| Templating | `illuminate/view` (Blade) |
| Validation | `illuminate/validation` |
| Logging | `monolog/monolog` |
| Frontend | Tailwind CSS 4 + Alpine.js + jQuery 4 |
| Build Tool | Vite |
| Date Handling | Carbon |
| Env Management | `vlucas/phpdotenv` |

---

## ⚡ Core Features

### 🗄️ CLI Migration Engine
Version-controlled database migrations with full reset support.

```bash
php ml migrate                      # Run pending migrations
php ml migrate --fresh              # Wipe & re-migrate
php ml make:migration create_users  # Create new migration
php ml db:seed                      # Seed the database
php ml gen-keys                     # Generate .env encryption keys
php ml run                          # Start dev server
```

### 🧬 Dynamic Eloquent Models
Zero-configuration models that automatically map to database tables — no boilerplate setup required.

### 📱 PWA Support
Fully installable as a native app on mobile and desktop with offline capabilities and optimized icon assets.

### 🖨️ Industrial Print Infrastructure
High-contrast, ledger-ready A4 document generation for ERP/CRM use cases — with standalone template control.

### 🔐 Strict .env Branding
Developer information and core branding are controlled exclusively via `.env` for maximum security and consistency across environments.

---

## 📁 Project Structure
