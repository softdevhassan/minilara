<?php
namespace App;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputOption;

class ServeCommand extends Command {
    protected function configure() {
        $this
            ->setName('serve')
            ->setDescription('Start the Mini Lara development server')
            ->addOption('port', 'p', InputOption::VALUE_REQUIRED, 'The port to serve the application on', 8000);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $io = new SymfonyStyle($input, $output);
        $port = $input->getOption('port');

        $io->title("Mini Lara Unified Dev Stack");
        $io->info("Starting Backend & Frontend in parallel...");

        // Start Vite as a background process and pipe its output to this terminal
        $viteDescriptor = [
            0 => ["pipe", "r"], // stdin
            1 => ["pipe", "w"], // stdout
            2 => ["pipe", "w"]  // stderr
        ];
        
        $viteProcess = proc_open("pnpm dev", $viteDescriptor, $pipes);
        
        if (is_resource($viteProcess)) {
            stream_set_blocking($pipes[1], false);
            stream_set_blocking($pipes[2], false);
            $io->success("Vite Dev Server is running (Background)");
        }

        $io->note("PHP Server starting on http://localhost:$port");
        
        // Start PHP server
        $phpServer = popen("php -S localhost:$port index.php 2>&1", "r");

        while (!feof($phpServer)) {
            // Read and print PHP logs
            echo fgets($phpServer);
            
            // Also read and print Vite logs if any
            if (isset($pipes[1])) {
                while ($line = fgets($pipes[1])) {
                    echo "\033[36m[Vite]\033[0m " . $line;
                }
            }
        }

        pclose($phpServer);
        if (is_resource($viteProcess)) {
            proc_close($viteProcess);
        }

        return Command::SUCCESS;
    }
}
