<?php
namespace App;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * GenerateKeyCommand
 * Generates secure encryption keys and updates the .env file automatically.
 */
class GenerateKeyCommand extends Command {
    protected function configure() {
        $this->setName('gen-keys')
             ->setDescription('Generate secure encryption keys and update .env file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $io = new SymfonyStyle($input, $output);
        $io->title("Mini Lara Security Key Generator");

        $envFile = BASE_PATH . '/.env';

        if (!file_exists($envFile)) {
            $io->error(".env file not found. Please create it first by copying .env.example");
            return Command::FAILURE;
        }

        // Generate cryptographically secure keys
        try {
            $encKey = bin2hex(random_bytes(32));
            $encIv = bin2hex(random_bytes(16));
            $sessKey = bin2hex(random_bytes(32));
        } catch (\Exception $e) {
            $io->error("Failed to generate random bytes: " . $e->getMessage());
            return Command::FAILURE;
        }

        $envContent = file_get_contents($envFile);

        $keys = [
            'ENCRYPTION_KEY' => $encKey,
            'ENCRYPTION_IV' => $encIv,
            'SESSION_ENCRYPT_KEY' => $sessKey
        ];

        $updated = 0;
        foreach ($keys as $key => $value) {
            $pattern = "/^{$key}=.*/m";
            $newLine = "{$key}=\"{$value}\"";
            
            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, $newLine, $envContent);
                $updated++;
            } else {
                // If key doesn't exist, append it at the end
                $envContent .= "\n" . $newLine;
                $updated++;
            }
        }

        if (file_put_contents($envFile, $envContent) !== false) {
            $io->success("Success! $updated security keys have been updated in your .env file.");
            
            $io->table(
                ['Security Key', 'Generated Hex Value'],
                [
                    ['ENCRYPTION_KEY', $encKey],
                    ['ENCRYPTION_IV', $encIv],
                    ['SESSION_ENCRYPT_KEY', $sessKey]
                ]
            );
            
            $io->note("Make sure to keep these keys private and do not share them in public repositories.");
        } else {
            $io->error("Failed to write to .env file. Please check file permissions.");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
