<?php
namespace App;


class Auth {
    /**
     * Attempt to log in a user.
     */
    public static function login($username, $password, $remember = false) {
        $user = DB::table('users')->where('username', $username)->first();
        // Convert stdClass to array for backward compatibility
        $user = $user ? (array)$user : null;

        if (!$user) {
            return ['success' => false, 'message' => 'Username not found'];
        }

        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Incorrect password'];
        }

        if (!empty($user['locked'])) {
            return ['success' => false, 'message' => 'Your account is locked'];
        }

        // Standardized Session Storage
        $_SESSION['user'] = $user;
        $_SESSION['logged_in'] = true;

        if ($remember) {
            $token = bin2hex(random_bytes(32));
            setcookie('remember_token', $token, time() + (86400 * 30), '/');
            // In a full implementation, you'd store this token in a 'user_tokens' table
        }

        return ['success' => true, 'user' => $user];
    }

    /**
     * Log the current user out.
     */
    public static function logout() {
        session_destroy();
        setcookie('remember_token', '', time() - 3600, '/');
        redirect('/auth/login');
    }

    /**
     * Check if a user is currently logged in.
     */
    public static function isLoggedIn() {
        return !empty($_SESSION['logged_in']) && !empty($_SESSION['user']);
    }

    /**
     * Get the currently logged-in user.
     */
    public static function user() {
        return $_SESSION['user'] ?? null;
    }

    /**
     * Check access for a specific route.
     */
    public static function checkAccess($route) {
        $route = '/' . trim($route, '/');
        $openRoutes = ['/auth/login', '/auth/signup', '/auth/forgot-password', '/auth/reset-password', '/404'];
        if (!empty($_ENV['OPEN_ROUTES'])) {
            $customOpen = array_map('trim', explode(',', $_ENV['OPEN_ROUTES']));
            $openRoutes = array_unique(array_merge($openRoutes, $customOpen));
        }

        if (in_array($route, $openRoutes) || strpos($route, '/api/') === 0 || strpos($route, '/print/') === 0) return true;
        
        if (!self::isLoggedIn()) return false;
        
        $user = self::user();
        $superUsers = array_map('trim', explode(',', $_ENV['SUPER_USERS'] ?? 'admin'));
        if (in_array($user['username'] ?? '', $superUsers)) return true;
        
        $allowedSlugs = json_decode($user['allowed_routes'] ?? '[]', true);
        if (!is_array($allowedSlugs)) $allowedSlugs = [];

        // Special case for dashboard
        if ($route === '/' || $route === '/home') {
            return in_array('home', $allowedSlugs);
        }

        // Standardized Module Mapping from Config
        $mapping = require dirname(__DIR__) . '/config/permissions.php';

        foreach ($mapping as $slug => $patterns) {
            if (in_array($slug, $allowedSlugs)) {
                foreach ($patterns as $pattern) {
                    if (strpos($route, $pattern) === 0) return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if current user has access to a specific permission slug.
     */
    public static function hasAccess($slug) {
        if (!self::isLoggedIn()) return false;
        $user = self::user();
        $superUsers = array_map('trim', explode(',', $_ENV['SUPER_USERS'] ?? 'admin'));
        if (in_array($user['username'] ?? '', $superUsers)) return true;
        
        $allowed = json_decode($user['allowed_routes'] ?? '[]', true);
        return is_array($allowed) && in_array($slug, $allowed);
    }
}

