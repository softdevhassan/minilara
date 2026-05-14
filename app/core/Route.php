<?php
namespace App;

/**
 * Route Facade - Provides Laravel-style expressive routing
 */
class Route {
    private static $router;

    public static function setRouter($router) {
        self::$router = $router;
    }

    public static function get($uri, $action) {
        self::$router->get($uri, $action);
    }

    public static function post($uri, $action) {
        self::$router->post($uri, $action);
    }

    public static function all($uri, $action) {
        self::$router->all($uri, $action);
    }
    
    // Group, Middleware, etc can be added here as the framework grows
}
