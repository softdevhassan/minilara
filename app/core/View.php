<?php
namespace App;

use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;

class View {
    private static $factory;
    private static $compiler;

    public static function init($viewsPaths, $cachePath) {
        $container = new Container();
        $filesystem = new Filesystem();
        $eventDispatcher = new Dispatcher($container);

        $viewFinder = new FileViewFinder($filesystem, (array)$viewsPaths);
        $resolver = new EngineResolver();

        $compiler = new BladeCompiler($filesystem, $cachePath);
        self::$compiler = $compiler;

        $resolver->register('blade', function () use ($compiler) {
            return new CompilerEngine($compiler);
        });

        self::$factory = new Factory($resolver, $viewFinder, $eventDispatcher);
        self::$factory->setContainer($container);

        self::registerDirectives();
    }

    private static function registerDirectives() {
        self::$compiler->directive('asset', function ($expression) {
            return "<?php echo asset($expression); ?>";
        });
        
        self::$compiler->directive('url', function ($expression) {
            return "<?php echo url($expression); ?>";
        });

        self::$compiler->directive('vite', function ($expression) {
            return "<?php echo vite($expression); ?>";
        });

        self::$compiler->directive('csrf', function () {
            return "<?php echo csrf_field(); ?>";
        });

        self::$compiler->if('auth', function () {
            return \App\Auth::isLoggedIn();
        });

        self::$compiler->if('guest', function () {
            return !\App\Auth::isLoggedIn();
        });
    }

    public static function render($view, $data = []) {
        if (!self::$factory) {
            $pagesPath = BASE_PATH . '/app/src/pages';
            $srcPath = BASE_PATH . '/app/src';
            $cachePath = BASE_PATH . '/app/cache';
            if (!is_dir($cachePath)) mkdir($cachePath, 0777, true);
            self::init([$pagesPath, $srcPath], $cachePath);
        }
        
        echo self::$factory->make($view, $data)->render();
    }

    public static function alert($type, $msg) {
        set_alert($type, $msg);
    }

    public static function renderAlert() {
        $enabled = config('APP_ENABLE_ALERTS', '1');
        if ($enabled === '0') return;

        $alert = get_alert();
        if (!$alert) return;
        
        $color = match($alert['type']) {
            'success' => '#10b981', 'error' => '#ef4444', 'warning' => '#f59e0b', default => '#3b82f6'
        };
        $icon = match($alert['type']) {
            'success' => 'ri-checkbox-circle-fill', 'error' => 'ri-error-warning-fill', 'warning' => 'ri-alert-fill', default => 'ri-information-fill'
        };

        echo "
        <div id='app-alert' class='fixed bottom-6 right-6 z-[9999] animate-bounce-in-right'>
            <div class='luxury-card p-4 flex items-center gap-4 border-l-4' style='border-left-color: $color; min-width: 300px; background: rgba(var(--bs-rgb), 0.8); backdrop-filter: blur(12px);'>
                <div class='w-10 h-10 rounded-full flex items-center justify-center text-white shrink-0' style='background-color: $color;'>
                    <i class='$icon text-xl'></i>
                </div>
                <div class='flex-1'>
                    <p class='text-[10px] font-bold text-ts uppercase mb-0.5'>".ucfirst($alert['type'])."</p>
                    <p class='text-sm font-bold text-tp'>".e($alert['message'])."</p>
                </div>
                <button onclick='this.closest(\"#app-alert\").remove()' class='text-ts hover:text-tp p-1'>
                    <i class='ri-close-line text-lg'></i>
                </button>
            </div>
        </div>
        <script>setTimeout(() => { const a = document.getElementById('app-alert'); if(a){ a.style.opacity='0'; setTimeout(()=>a.remove(),500); } }, 5000);</script>
        ";
    }

    public static function header($title, $tagline = '') {
        echo "<div class='mb-8'>
                <h1 class='text-3xl font-semibold text-tp'>".e($title)."</h1>
                ".($tagline ? "<p class='text-ts text-sm mt-1'>".e($tagline)."</p>" : "")."
              </div>";
    }
}
