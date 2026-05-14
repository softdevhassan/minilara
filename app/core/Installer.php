<?php
namespace App;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class Installer {
    public static function install() {
        $io = new SymfonyStyle(new ArgvInput(), new ConsoleOutput());

        $io->title("Mini Lara - Professional Installation");
        $io->note("Welcome to Mini Lara. Let's set up your new application.");

        // 1. Setup .env
        if (!file_exists('.env')) {
            copy('.env.example', '.env');
            $io->success(".env file created from template.");
        }

        // 2. Set App Name from Folder
        $folderName = basename(getcwd());
        $appName = ucwords(str_replace(['-', '_'], ' ', $folderName));
        self::updateEnv('APP_NAME', $appName);
        $io->writeln("Project Name set to: <info>$appName</info>");

        // 3. Database Configuration
        if ($io->confirm("Would you like to configure the database now?", true)) {
            $dbHost = $io->ask("Database Host", "localhost");
            $dbName = $io->ask("Database Name", $folderName);
            $dbUser = $io->ask("Database User", "root");
            $dbPass = $io->ask("Database Password", "");

            self::updateEnv('DB_SERVER', $dbHost);
            self::updateEnv('DB_NAME', $dbName);
            self::updateEnv('DB_USER', $dbUser);
            self::updateEnv('DB_PASS', $dbPass);

            // Test Connection
            try {
                $pdo = new \PDO("mysql:host=$dbHost", $dbUser, $dbPass);
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
                $io->success("Database '$dbName' verified/created.");
                
                // Run Migrations (Silent call to ML tool)
                $io->note("Running initial migrations and seeding...");
                passthru("php ml --task=reset --no-interaction");
                
            } catch (\Exception $e) {
                $io->error("Database connection failed: " . $e->getMessage());
                $io->warning("You will need to configure .env manually and run 'php ml' later.");
            }
        }

        // 4. Generate Key
        $key = bin2hex(random_bytes(16));
        self::updateEnv('ML_ENC_KEY', $key);
        $io->success("Application Encryption Key generated.");

        $io->section("Installation Complete!");
        $io->writeln("Next steps:");
        $io->writeln("1. Run <comment>pnpm install</comment>");
        $io->writeln("2. Run <comment>pnpm dev</comment>");
        $io->writeln("3. Login at /auth/login (Admin / admin)");
        
        return 0;
    }

    private static function updateEnv($key, $value) {
        $envFile = '.env';
        if (!file_exists($envFile)) return;
        
        $content = file_get_contents($envFile);
        $pattern = "/^$key=.*$/m";
        
        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, "$key=\"$value\"", $content);
        } else {
            $content .= "\n$key=\"$value\"";
        }
        
        file_get_contents(file_put_contents($envFile, $content));
    }
}
