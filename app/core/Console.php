<?php
namespace App;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Mini Lara Console Engine - Consolidated Commands
 */

class MigrateCommand extends Command {
    protected function configure() {
        $this->setName('migrate')
             ->setDescription('Run database migrations');
        $this->addOption('fresh', null, InputOption::VALUE_NONE, 'Wipe and re-run all migrations');
    }
    protected function execute(InputInterface $input, OutputInterface $output): int {
        if ($input->getOption('fresh')) { $output->writeln('<comment>Wiping...</comment>'); MigrationManager::wipe(); }
        $count = MigrationManager::migrate();
        $output->writeln($count > 0 ? "<info>Ran $count migrations.</info>" : "<comment>Nothing to migrate.</comment>");
        return Command::SUCCESS;
    }
}

class SeedCommand extends Command {
    protected function configure() {
        $this->setName('db:seed')
             ->setDescription('Seed database');
    }
    protected function execute(InputInterface $input, OutputInterface $output): int {
        $count = SeederManager::seed();
        $output->writeln("<info>Ran $count seeders.</info>");
        return Command::SUCCESS;
    }
}

class MakeMigrationCommand extends Command {
    protected function configure() {
        $this->setName('make:migration')
             ->setDescription('Create a new migration file');
        $this->addArgument('name', InputArgument::REQUIRED, 'Migration name');
    }
    protected function execute(InputInterface $input, OutputInterface $output): int {
        $name = $input->getArgument('name'); $ts = date('Y_m_d_His'); $file = "{$ts}_{$name}.php";
        $path = BASE_PATH . "/app/database/migrations/$file";
        $tpl = "<?php\nuse App\Migration;\nuse Illuminate\Database\Schema\Blueprint;\n\nreturn new class extends Migration {\n    public function up() {\n        \$this->schema->create('table', function (Blueprint \$table) {\n            \$table->id();\n            \$table->timestamps();\n        });\n    }\n    public function down() { \$this->schema->dropIfExists('table'); }\n};";
        file_put_contents($path, $tpl);
        $output->writeln("<info>Created: $file</info>");
        return Command::SUCCESS;
    }
}

class GenerateKeyCommand extends Command {
    protected function configure() {
        $this->setName('gen-keys')
             ->setDescription('Generate encryption keys');
    }
    protected function execute(InputInterface $input, OutputInterface $output): int {
        $path = BASE_PATH . '/.env';
        if (!file_exists($path)) { $output->writeln('<error>.env not found</error>'); return Command::FAILURE; }
        $content = file_get_contents($path);
        $keys = [
            'ENCRYPTION_KEY' => bin2hex(random_bytes(32)),
            'ENCRYPTION_IV' => bin2hex(random_bytes(16)),
            'SESSION_ENCRYPT_KEY' => bin2hex(random_bytes(32))
        ];
        foreach ($keys as $k => $v) {
            $content = preg_replace("/^$k=.*/m", "$k=\"$v\"", $content);
        }
        file_put_contents($path, $content);
        $output->writeln('<info>Keys generated and saved to .env</info>');
        return Command::SUCCESS;
    }
}

class RunCommand extends Command {
    protected function configure() {
        $this->setName('run')
             ->setDescription('Run dev server');
        $this->addOption('port', 'p', InputOption::VALUE_REQUIRED, 'Port', 8000);
    }
    protected function execute(InputInterface $input, OutputInterface $output): int {
        $port = $input->getOption('port');
        $output->writeln("<info>Server started at http://localhost:$port</info>");
        passthru("php -S localhost:$port -t app/public");
        return Command::SUCCESS;
    }
}

class DatabaseManager extends Command {
    protected function configure() {
        $this->setName('db:manage')
             ->setDescription('Manage database lifecycle (migrate, seed, reset)');
        $this->addOption('task', null, InputOption::VALUE_REQUIRED, 'Task to run: status, migrate, seed, reset', 'status');
    }
    protected function execute(InputInterface $input, OutputInterface $output): int {
        $task = $input->getOption('task') ?: 'status';
        switch ($task) {
            case 'migrate': DB::migrate(); break;
            case 'seed': DB::seed(); break;
            case 'reset': DB::migrate(true); DB::seed(); break;
            case 'status': foreach (DB::getStatus() as $t => $c) $output->writeln("<info>$t</info>: $c rows"); break;
        }
        return Command::SUCCESS;
    }
}
