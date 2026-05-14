<?php
namespace App;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

/**
 * FastRoute Integration
 */
class Router {
    private $dispatcher;
    private $routes = [];

    public function add($method, $route, $handler) {
        $this->routes[] = [$method, $route, $handler];
    }

    public function defineRoutes() {
        $modulesPath = BASE_PATH . '/app/config/modules.php';
        $modules = file_exists($modulesPath) ? require $modulesPath : [];

        // Dynamic Module Routes
        foreach ($modules as $singular => $plural) {
            $this->add(['GET', 'POST'], "/$plural", ["main.$plural"]);
            $this->add(['GET', 'POST'], "/add/$singular", ["manage.$singular"]);
            $this->add(['GET', 'POST'], "/edit/$singular/{id:\d+}", ["manage.$singular"]);
            $this->add(['GET', 'POST'], "/view/$singular/{id:\d+}", ["views.$singular"]);
            $this->add(['GET', 'POST'], "/print/{module}/{id:[0-9,]+}", ["prints.single.{module}"]);
            $this->add(['GET', 'POST'], "/print/report/{module}", ["prints.reports.{module}"]);
        }

        // Auth
        $this->add(['GET', 'POST'], '/auth/logout', function() {
            Auth::logout();
            redirect('/auth/login');
        });
        $this->add(['GET', 'POST'], '/auth/{slug}', ["auth.{slug}", ['showLayout' => false]]);

        // Profile
        $this->add(['GET', 'POST'], '/profile', ["main.profile"]);

        // General
        $this->add('GET', '/', function() {
            if (!Auth::isLoggedIn()) redirect('/auth/login');
            redirect('/home');
        });

        $this->add(['GET', 'POST'], '/{slug}', function($vars) {
            $slug = $vars['slug'];
            if ($slug === 'home' || $slug === 'settings') {
                View::render("main.$slug");
                return;
            }
            if (file_exists(BASE_PATH . "/app/src/pages/main/$slug.blade.php")) {
                View::render("main.$slug");
            } else {
                View::render('errors.404');
            }
        });
    }

    public function run() {
        $dispatcher = simpleDispatcher(function(RouteCollector $r) {
            foreach ($this->routes as $route) {
                $r->addRoute($route[0], $route[1], $route[2]);
            }
        });

        $request = Request::getInstance();
        $routeInfo = $dispatcher->dispatch($request->method(), $request->path());

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                http_response_code(404);
                View::render('errors.404');
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                http_response_code(405);
                echo "405 Method Not Allowed";
                break;
            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                
                // Set variables into $_GET for backward compatibility with existing pages
                foreach ($vars as $key => $val) {
                    $_GET[$key] = $val;
                }

                if (is_callable($handler)) {
                    $handler($vars);
                } elseif (is_array($handler)) {
                    $viewName = $handler[0];
                    foreach ($vars as $key => $val) {
                        $viewName = str_replace('{' . $key . '}', $val, $viewName);
                    }
                    View::render($viewName, array_merge($vars, $handler[1] ?? []));
                }
                break;
        }
    }
}
