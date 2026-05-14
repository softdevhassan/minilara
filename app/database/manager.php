<?php
/**
 * Mini Lara - CLI Database Manager
 */

require __DIR__ . '/../../vendor/autoload.php';

define('BASE_PATH', realpath(__DIR__ . '/../../'));

use Symfony\Component\Console\Application;
use App\DatabaseManager;

// Initialize App (for DB connection)
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->safeLoad();

// Boot Database
$capsule = new Illuminate\Database\Capsule\Manager;
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

$application = new Application("Mini Lara CLI", "1.1.0");
$application->add(new DatabaseManager());
$application->setDefaultCommand('db:manage', true);
$application->run();
