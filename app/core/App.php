<?php
namespace App;

use Illuminate\Database\Capsule\Manager as Capsule;
use Dotenv\Dotenv;
use Illuminate\Support\Str;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * Main Application Engine
 * Handles bootstrapping, environment, and core services
 */
class App {
    private static $instance = null;
    private $logger;

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function boot() {
        // 0. Initialize Logger (Industry Standard)
        $this->logger = new Logger('minilara');
        $this->logger->pushHandler(new StreamHandler(BASE_PATH . '/app/cache/logs/app.log', Logger::DEBUG));

        // 1. Global Error Handling
        $this->registerHandlers();

        // 2. Load Environment
        $dotenv = Dotenv::createImmutable(BASE_PATH);
        $dotenv->load();

        // 3. Initialize Eloquent
        $capsule = new Capsule;
        $capsule->addConnection([
            'driver'    => 'mysql',
            'host'      => $_ENV['DB_SERVER'] ?? 'localhost',
            'database'  => $_ENV['DB_NAME'],
            'username'  => $_ENV['DB_USER'] ?? 'root',
            'password'  => $_ENV['DB_PASS'] ?? '',
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
        ]);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        // LOAD DB SETTINGS INTO $_ENV
        try {
            $dbSettings = Capsule::table('settings')->get();
            foreach ($dbSettings as $s) {
                $_ENV[$s->key] = $s->value1;
            }
        } catch (\Exception $e) {
            // Silently fail if table doesn't exist
        }

        // 4. Start Session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 5. Set Timezone
        date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Asia/Karachi');

        // 6. Dynamic Model Autoloader
        spl_autoload_register(function ($class) {
            if (strpos($class, 'App\\Models\\') === 0) {
                $modelName = str_replace('App\\Models\\', '', $class);
                $tableName = Str::snake(Str::plural($modelName));
                if (!class_exists($class)) {
                    eval("namespace App\Models; class $modelName extends \App\BaseModel { protected \$table = '$tableName'; }");
                }
            }
        });

        // 7. Security & DevTool
        $request = Request::getInstance();
        $mode = $request->input('mode');
        if ($mode && ($_ENV['DEV_DEBUG'] ?? 'false') === 'true') {
            DevTool::run();
        }

        $request = Request::getInstance();
        if (!Auth::checkAccess($request->path())) {
            redirect('/auth/login');
        }

        // 8. Run Router
        $router = new Router();
        $router->defineRoutes();
        $router->run();
    }

    private function registerHandlers() {
        error_reporting(E_ALL);
        set_exception_handler([$this, 'handleException']);
        set_error_handler(function ($level, $message, $file, $line) {
            if (error_reporting() & $level) {
                $this->handleException(new \ErrorException($message, 0, $level, $file, $line));
            }
        });
    }

    public function handleException($e) {
        $this->logger->error($e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        
        if (ob_get_length()) ob_end_clean();
        
        $debug = ($_ENV['DEV_DEBUG'] ?? 'false') === 'true';
        
        if ($debug) {
            echo "<h1>Fatal Error</h1>";
            echo "<p><strong>Message:</strong> " . $e->getMessage() . "</p>";
            echo "<p><strong>File:</strong> " . $e->getFile() . " on line " . $e->getLine() . "</p>";
            echo "<pre>" . $e->getTraceAsString() . "</pre>";
        } else {
            http_response_code(500);
            View::render('errors.500', [
                'exception_type' => get_class($e),
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }
}
