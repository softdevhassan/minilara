<?php
/**
 * Mini Lara - CLI Tool
 * ---------------------------------------------------------------------
 * Usage: php ml [command] [options]
 */

require __DIR__ . '/vendor/autoload.php';

define('BASE_PATH', __DIR__);

use Symfony\Component\Console\Application;
use App\DatabaseManager;
use Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager as Capsule;

// 1. Initialize Environment
$dotenv = Dotenv::createImmutable(BASE_PATH);
$dotenv->safeLoad();

// 2. Boot Database (Required for DB commands)
$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => $_ENV['DB_SERVER'] ?? 'localhost',
    'database'  => $_ENV['DB_NAME'],
    'username'  => $_ENV['DB_USER'] ?? 'root',
    'password'  => $_ENV['DB_PASS'] ?? '',
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

// 3. Run Console Application
$application = new Application("Mini Lara CLI", "1.1.0");

// Add Commands
$application->add(new DatabaseManager());
$application->add(new \App\RunCommand());
$application->add(new \App\GenerateKeyCommand());

// Set default if no command is provided
$application->setDefaultCommand('db:manage');

$application->run();
