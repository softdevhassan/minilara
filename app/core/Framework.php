<?php
namespace App;

use Illuminate\Database\Capsule\Manager as Capsule;
use Dotenv\Dotenv;
use Illuminate\Support\Str;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Illuminate\Validation\Factory as ValidationFactory;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Factory as ViewFactory;
use Illuminate\View\FileViewFinder;

/**
 * Mini Lara Framework Core (Ultra-Consolidated)
 */

class App {
    private static $instance = null;
    private $logger;
    public static function getInstance() { return self::$instance ?? (self::$instance = new self()); }

    public function boot() {
        $this->logger = new Logger('minilara');
        $this->logger->pushHandler(new StreamHandler(BASE_PATH . '/app/cache/logs/app.log', Logger::DEBUG));
        $this->registerHandlers();
        $dotenv = Dotenv::createImmutable(BASE_PATH); $dotenv->load();

        $capsule = new Capsule;
        $capsule->addConnection([
            'driver' => 'mysql', 'host' => $_ENV['DB_SERVER'] ?? 'localhost', 'database' => $_ENV['DB_NAME'],
            'username' => $_ENV['DB_USER'] ?? 'root', 'password' => $_ENV['DB_PASS'] ?? '',
            'charset' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci',
        ]);
        $capsule->setAsGlobal(); $capsule->bootEloquent();

        try { $dbS = Capsule::table('settings')->get(); foreach ($dbS as $s) $_ENV[$s->key] = $s->value1; } catch (\Exception $e) {}
        if (session_status() === PHP_SESSION_NONE) session_start();
        date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Asia/Karachi');

        spl_autoload_register(function ($class) {
            if (strpos($class, 'App\\Models\\') === 0) {
                $m = str_replace('App\\Models\\', '', $class); $t = Str::snake(Str::plural($m));
                if (!class_exists($class)) eval("namespace App\Models; class $m extends \App\BaseModel { protected \$table = '$t'; }");
            }
        });

        $req = Request::getInstance();
        if ($req->input('mode') && ($_ENV['DEV_DEBUG'] ?? 'false') === 'true') DevTool::run();
        if (!Auth::checkAccess($req->path())) redirect('/auth/login');

        $router = new Router(); $router->defineRoutes(); $router->run();
    }

    private function registerHandlers() {
        error_reporting(E_ALL); set_exception_handler([$this, 'handleException']);
        set_error_handler(fn($l, $m, $f, $ln) => (error_reporting() & $l) ? $this->handleException(new \ErrorException($m, 0, $l, $f, $ln)) : false);
    }

    public function handleException($e) {
        $this->logger->error($e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        if (ob_get_length()) ob_end_clean();
        if (($_ENV['DEV_DEBUG'] ?? 'false') === 'true') {
            echo "<h1>Fatal Error</h1><p>".$e->getMessage()."</p><pre>".$e->getTraceAsString()."</pre>";
        } else {
            http_response_code(500); View::render('errors.500', ['message' => $e->getMessage()]);
        }
        exit;
    }
}

class Auth {
    public static function login($u, $p) {
        $user = DB::table('users')->where('username', $u)->first(); $user = $user ? (array)$user : null;
        if (!$user || !password_verify($p, $user['password'])) return ['success' => false, 'message' => 'Invalid credentials'];
        $_SESSION['user'] = $user; $_SESSION['logged_in'] = true; return ['success' => true, 'user' => $user];
    }
    public static function logout() { session_destroy(); redirect('/auth/login'); }
    public static function isLoggedIn() { return !empty($_SESSION['logged_in']); }
    public static function user() { return $_SESSION['user'] ?? null; }
    public static function checkAccess($route) {
        $route = '/' . trim($route, '/');
        $open = array_merge(['/auth/login', '/404'], array_map('trim', explode(',', $_ENV['OPEN_ROUTES'] ?? '')));
        if (in_array($route, $open) || strpos($route, '/api/') === 0 || strpos($route, '/print/') === 0) return true;
        if (!self::isLoggedIn()) return false;
        $user = self::user();
        if (in_array($user['username'] ?? '', array_map('trim', explode(',', $_ENV['SUPER_USERS'] ?? 'admin')))) return true;
        $allowed = json_decode($user['allowed_routes'] ?? '[]', true) ?: [];
        if ($route === '/' || $route === '/home') return in_array('home', $allowed);
        $mapping = require BASE_PATH . '/app/config/permissions.php';
        foreach ($mapping as $slug => $patterns) {
            if (in_array($slug, $allowed)) foreach ($patterns as $p) if (strpos($route, $p) === 0) return true;
        }
        return false;
    }
    public static function hasAccess($slug) {
        if (!self::isLoggedIn()) return false;
        $user = self::user();
        if (in_array($user['username'] ?? '', array_map('trim', explode(',', $_ENV['SUPER_USERS'] ?? 'admin')))) return true;
        return in_array($slug, json_decode($user['allowed_routes'] ?? '[]', true) ?: []);
    }
}

class Request {
    private static $instance = null; private $request;
    public function __construct() { $this->request = SymfonyRequest::createFromGlobals(); }
    public static function getInstance() { return self::$instance ?? (self::$instance = new self()); }
    public function input($k, $d = null) { return $this->request->get($k, $d); }
    public function all() { return array_merge($this->request->query->all(), $this->request->request->all()); }
    public function method() { return $this->request->getMethod(); }
    public function path() { return '/' . trim($this->request->getPathInfo(), '/'); }
}

class View {
    private static $factory; private static $compiler;
    public static function init($paths, $cache) {
        $c = new Container(); $f = new Filesystem(); $d = new Dispatcher($c);
        $finder = new FileViewFinder($f, (array)$paths); $res = new EngineResolver();
        $comp = new BladeCompiler($f, $cache); self::$compiler = $comp;
        $res->register('blade', fn() => new CompilerEngine($comp));
        self::$factory = new ViewFactory($res, $finder, $d); self::$factory->setContainer($c);
        self::registerDirectives();
    }
    private static function registerDirectives() {
        self::$compiler->directive('asset', fn($e) => "<?php echo asset($e); ?>");
        self::$compiler->directive('url', fn($e) => "<?php echo url($e); ?>");
        self::$compiler->directive('vite', fn($e) => "<?php echo vite($e); ?>");
    }
    public static function render($view, $data = []) {
        if (!self::$factory) {
            $p = [BASE_PATH.'/app/src/pages', BASE_PATH.'/app/src']; $c = BASE_PATH.'/app/cache';
            if (!is_dir($c)) mkdir($c, 0777, true); self::init($p, $c);
        }
        $out = self::$factory->make($view, $data)->render();
        if (($_ENV['APP_MODE'] ?? 'dev') === 'prod') {
            $out = preg_replace(['/\>[^\S ]+/s', '/[^\S ]+\</s', '/(\s)+/s', '/<!--(.|\s)*?-->/'], ['>', '<', '\\1', ''], $out);
        }
        echo $out . "\n<!-- Built with Mini Lara v1.3.2 - https://github.com/softdevhassan/minilara -->";
    }
    public static function alert($t, $m) { $_SESSION['alert'] = ['type' => $t, 'message' => $m]; }
    public static function renderAlert() {
        if (config('APP_ENABLE_ALERTS', '1') === '0' || !($a = $_SESSION['alert'] ?? null)) return;
        unset($_SESSION['alert']);
        $c = match($a['type']) { 'success' => '#10b981', 'error' => '#ef4444', 'warning' => '#f59e0b', default => '#3b82f6' };
        $i = match($a['type']) { 'success' => 'ri-checkbox-circle-fill', 'error' => 'ri-error-warning-fill', 'warning' => 'ri-alert-fill', default => 'ri-information-fill' };
        echo "<div id='app-alert' class='fixed bottom-6 right-6 z-[9999] animate-bounce-in-right'><div class='luxury-card p-4 flex items-center gap-4 border-l-4' style='border-left-color: $c; min-width: 300px; background: rgba(var(--bs-rgb), 0.8); backdrop-filter: blur(12px);'><div class='w-10 h-10 rounded-full flex items-center justify-center text-white shrink-0' style='background-color: $c;'><i class='$i text-xl'></i></div><div class='flex-1'><p class='text-[10px] font-bold text-ts uppercase mb-0.5'>".ucfirst($a['type'])."</p><p class='text-sm font-bold text-tp'>".e($a['message'])."</p></div><button onclick='this.closest(\"#app-alert\").remove()' class='text-ts hover:text-tp p-1'><i class='ri-close-line text-lg'></i></button></div></div><script>setTimeout(() => { const a = document.getElementById('app-alert'); if(a){ a.style.opacity='0'; setTimeout(()=>a.remove(),500); } }, 5000);</script>";
    }
    public static function header($t, $tg = '') {
        echo "<div class='mb-8'><h1 class='text-3xl font-semibold text-tp'>".e($t)."</h1>".($tg ? "<p class='text-ts text-sm mt-1'>".e($tg)."</p>" : "")."</div>";
    }
}

class Vite {
    public static function tags($entries): string {
        $entries = (array)$entries; $isHot = false;
        if (($_ENV['APP_MODE'] ?? 'dev') === 'dev') {
            $fp = @fsockopen('localhost', 5173, $en, $es, 0.1); if ($fp) { $isHot = true; fclose($fp); }
        }
        if ($isHot) {
            $t = '<script type="module" src="http://localhost:5173/@vite/client"></script>';
            foreach ($entries as $e) $t .= str_ends_with($e, '.css') ? '<link rel="stylesheet" href="http://localhost:5173/'.$e.'">' : '<script type="module" src="http://localhost:5173/'.$e.'"></script>';
            return $t;
        }
        $mP = BASE_PATH . '/app/public/build/.vite/manifest.json'; if (!file_exists($mP)) return '';
        $m = json_decode(file_get_contents($mP), true); $t = '';
        foreach ($entries as $e) {
            if (isset($m[$e])) {
                $f = asset('build/' . $m[$e]['file']);
                $t .= str_ends_with($f, '.css') ? '<link rel="stylesheet" href="'.$f.'">' : '<script type="module" src="'.$f.'"></script>';
            }
        }
        return $t;
    }
}

class Validator {
    private static $factory;
    public static function make($d, $r, $m = []) {
        if (!self::$factory) self::$factory = new ValidationFactory(new Translator(new FileLoader(new Filesystem(), BASE_PATH.'/app/lang'), 'en'));
        return self::$factory->make($d, $r, $m);
    }
}
