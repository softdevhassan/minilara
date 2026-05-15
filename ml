<?php
/**
 * Mini Lara - CLI Tool
 * ---------------------------------------------------------------------
 * Usage: php ml [command] [options]
 */

require __DIR__ . '/vendor/autoload.php';

define('BASE_PATH', __DIR__);

use Symfony\Component\Console\Application;
use Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager as Capsule;

// 0. Load Core Functions & Consolidated Classes
require_once __DIR__ . '/app/core/functions.php';

// 1. Initialize Environment
$dotenv = Dotenv::createImmutable(BASE_PATH);
$dotenv->safeLoad();

// 2. Boot Database (Safe Boot for Global use)
try {
    $capsule = new Capsule;
    $capsule->addConnection([
        'driver'    => 'mysql',
        'host'      => $_ENV['DB_SERVER'] ?? 'localhost',
        'database'  => $_ENV['DB_NAME'] ?? '',
        'username'  => $_ENV['DB_USER'] ?? 'root',
        'password'  => $_ENV['DB_PASS'] ?? '',
        'charset'   => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ]);
    $capsule->setAsGlobal();
    $capsule->bootEloquent();
} catch (\Exception $e) {
    // Database not ready, but we might still want to run non-DB commands
}

// 3. Run Console Application
$application = new Application("Mini Lara CLI", "1.3.8");

// Add Commands
$application->add(new \App\DatabaseManager());
$application->add(new \App\RunCommand());
$application->add(new \App\GenerateKeyCommand());
$application->add(new \App\MigrateCommand());
$application->add(new \App\SeedCommand());
$application->add(new \App\MakeMigrationCommand());

// Set default if no command is provided
$application->setDefaultCommand('migrate');

$application->run();
