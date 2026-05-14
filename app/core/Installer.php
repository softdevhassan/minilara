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
        if ($io->confirm("Configure database?", true)) {
            $dbName = $io->ask("Database Name", $folderName);
            $dbHost = "localhost";
            $dbUser = "root";
            $dbPass = "";

            self::updateEnv('DB_SERVER', $dbHost);
            self::updateEnv('DB_NAME', $dbName);
            self::updateEnv('DB_USER', $dbUser);
            self::updateEnv('DB_PASS', $dbPass);

            try {
                $pdo = new \PDO("mysql:host=$dbHost", $dbUser, $dbPass);
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
                $io->success("Database '$dbName' ready.");
                
                if ($io->confirm("Install default Schema & Seeding?", true)) {
                    $io->note("Applying database structure...");
                    passthru("php ml db:manage --task=reset --no-interaction");
                }
            } catch (\Exception $e) {
                $io->error("DB Error: " . $e->getMessage());
            }
        }

        // 4. Package Installation
        if ($io->confirm("Run 'pnpm install' now?", false)) {
            $io->note("Installing frontend dependencies...");
            passthru("pnpm install");
        }

        // 5. Generate Key
        $key = bin2hex(random_bytes(16));
        self::updateEnv('ML_ENC_KEY', $key);

        $io->section("Installation Complete!");
        $io->writeln("Next steps:");
        $io->writeln("1. Run <comment>cd " . $folderName . "</comment>");
        $io->writeln("2. Run <comment>php ml run</comment> (Starts PHP & Vite)");
        $io->writeln("3. Login at <info>/auth/login</info> (Admin / admin)");
        
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
        
        file_put_contents($envFile, $content);
    }
}
