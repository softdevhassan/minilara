<?php
namespace App;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputOption;

class DatabaseManager extends Command {
    protected function configure() {
        $this->setName('db:manage')
             ->setDescription('Manage Mini Lara Database')
             ->addOption('task', 't', InputOption::VALUE_REQUIRED, 'Task: migrate, seed, reset, status');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $io = new SymfonyStyle($input, $output);
        $task = $input->getOption('task') ?: 'status';
        $io->title("Mini Lara Database Manager");

        switch ($task) {
            case 'migrate': DB::migrate($io); break;
            case 'seed':    DB::seed($io); break;
            case 'reset':   
                if ($io->confirm("Reset database?")) {
                    DB::migrate($io, true);
                    DB::seed($io);
                }
                break;
            case 'status':  $this->showStatus($io); break;
            default:        $io->error("Unknown task: $task"); break;
        }
        return Command::SUCCESS;
    }

    private function showStatus($io) {
        $io->section("Database Status");
        foreach (DB::getStatus() as $table => $count) {
            $io->writeln(sprintf("<info>%-30s</info> rows: %s", $table, $count));
        }
    }
}
