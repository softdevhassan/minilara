<?php
/**
 * Mini Lara - Professional Business Management Framework
 * ---------------------------------------------------------------------
 * Standardized Entry Point
 */

// 0. PHP Dev Server Static File Support (Smart Routing)
if (php_sapi_name() === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    // Ultimate Asset Router (Manual Serving for 100% Reliability)
    $uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    $mimes = ['css'=>'text/css', 'js'=>'application/javascript', 'json'=>'application/json', 'png'=>'image/png', 'jpg'=>'image/jpeg', 'svg'=>'image/svg+xml', 'webp'=>'image/webp', 'ico'=>'image/x-icon', 'woff2' => 'font/woff2'];

    // 1. If file exists directly (e.g. /app/public/images/logo.png)
    if ($uri !== '/' && is_file(__DIR__ . $uri)) {
        $file = __DIR__ . $uri;
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        header('Content-Type: ' . ($mimes[$ext] ?? 'application/octet-stream'));
        readfile($file);
        exit;
    }
    
    // 2. If it's a mapped asset (e.g. /images/logo.png -> /app/public/images/logo.png)
    if ($uri !== '/' && is_file(__DIR__ . '/app/public' . $uri)) {
        $file = __DIR__ . '/app/public' . $uri;
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        header('Content-Type: ' . ($mimes[$ext] ?? 'application/octet-stream'));
        readfile($file);
        exit;
    }
}

define('BASE_PATH', __DIR__);

// Check for dependencies
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    header('Content-Type: text/html');
    $offline_html = <<<'HTML'
        <style>
            body { background: #f9fafb; font-family: "Inter", system-ui, -apple-system, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
            .card { background: white; padding: 3rem; border-radius: 1.5rem; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04); text-align: center; max-width: 450px; width: 90%; border: 1px solid #e5e7eb; }
            .icon { width: 64px; height: 64px; background: #fee2e2; color: #ef4444; border-radius: 1rem; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; font-size: 2rem; font-weight: bold; }
            h1 { color: #111827; margin: 0 0 0.75rem; font-size: 1.5rem; font-weight: 700; }
            p { color: #4b5563; margin: 0 0 1.5rem; line-height: 1.6; font-size: 0.95rem; }
            .btn { display: inline-block; background: #2563eb; color: white; padding: 0.75rem 1.5rem; border-radius: 0.75rem; text-decoration: none; font-weight: 600; transition: all 0.2s; font-size: 0.9rem; }
            .btn:hover { background: #1d4ed8; transform: translateY(-1px); }
            .brand { margin-top: 2rem; font-size: 0.75rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; }
            code { background: #f3f4f6; padding: 0.2rem 0.4rem; border-radius: 0.25rem; font-family: monospace; }
        </style>
        <div class="card">
            <div class="icon">!</div>
            <h1>System Offline</h1>
            <p>The application core is ready, but dependencies (<b>vendor</b>) are missing. Please run <code>composer install</code> to continue.</p>
            <a href="?mode=db" class="btn">Launch Mini Lara DevTool</a>
            <div class="brand">Powered by Mini Lara</div>
        </div>
HTML;
    die($offline_html);
}

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/core/functions.php';

// Boot and Run Application
try {
    App\App::getInstance()->boot();
} catch (\Exception $e) {
    // If boot fails, use the framework's internal handler for professional rendering
    App\App::getInstance()->handleException($e);
}
