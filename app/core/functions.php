<?php

use App\Auth;
use App\DB;
use App\Vite;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Mini Lara - Industrial Grade Global Helpers
 * ---------------------------------------------------------------------
 * Laravel-style global functions to minimize overhead and improve DX.
 */

// Manual Load for Consolidated Core (Ultra-Mini)
require_once __DIR__ . '/Framework.php';
require_once __DIR__ . '/DB.php';
require_once __DIR__ . '/Router.php';
require_once __DIR__ . '/Console.php';

if (!function_exists('url')) {
    function url($to = '') {
        return ($_ENV['ROOT_PATH'] ?? '') . $to;
    }
}

if (!function_exists('asset')) {
    function asset($file) {
        return url('/app/public/' . ltrim($file, '/'));
    }
}

if (!function_exists('redirect')) {
    function redirect($to) {
        header('Location: ' . url($to));
        exit;
    }
}

if (!function_exists('vite')) {
    function vite($entries) {
        return \App\Vite::tags($entries);
    }
}

if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars(trim($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('now')) {
    function now($timezone = null) {
        return Carbon::now($timezone ?: ($_ENV['APP_TIMEZONE'] ?? 'Asia/Karachi'));
    }
}

if (!function_exists('auth')) {
    function auth() {
        return Auth::user();
    }
}

if (!function_exists('db')) {
    function db($table = null) {
        return $table ? DB::table($table) : DB::getInstance();
    }
}

if (!function_exists('config')) {
    function config($key, $default = null) {
        return $_ENV[$key] ?? $default;
    }
}

if (!function_exists('get_setting')) {
    function get_setting($key, $default = null) {
        static $settings = null;
        if ($settings === null) {
            try {
                $settings = DB::table('settings')->pluck('value1', 'key')->toArray();
            } catch (\Exception $e) {
                return $default;
            }
        }
        return $settings[$key] ?? $default;
    }
}

if (!function_exists('collect')) {
    function collect($value = null) {
        return new Collection($value);
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token() {
        return $_SESSION['csrf_token'] ?? '';
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field() {
        return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
    }
}

if (!function_exists('validate')) {
    function validate(array $data, array $rules, array $messages = []) {
        return \App\Validator::make($data, $rules, $messages);
    }
}

if (!function_exists('set_alert')) {
    function set_alert($type, $message) {
        $_SESSION['app_alert'] = ['type' => $type, 'message' => $message];
    }
}

if (!function_exists('get_alert')) {
    function get_alert() {
        if (isset($_SESSION['app_alert'])) {
            $alert = $_SESSION['app_alert'];
            unset($_SESSION['app_alert']);
            return $alert;
        }
        return null;
    }
}

if (!function_exists('numberToEnglishWords')) {
    function numberToEnglishWords($number, $withRupees = true) {
        if ($number === null || $number === '' || !is_numeric($number)) return '';
        $number = (float)$number;
        if ($number < 0) return 'Negative ' . numberToEnglishWords(-$number, $withRupees);

        $rupees = (int) floor($number);
        $paisa  = (int) round(($number - $rupees) * 100);

        $ones = [0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen'];
        $tens = [0 => '', 1 => '', 2 => 'Twenty', 3 => 'Thirty', 4 => 'Forty', 5 => 'Fifty', 6 => 'Sixty', 7 => 'Seventy', 8 => 'Eighty', 9 => 'Ninety'];

        $twoDigits = function ($n) use ($ones, $tens) {
            if ($n < 20) return $ones[$n];
            $t = (int)($n / 10); $o = $n % 10;
            return $tens[$t] . ($o ? '-' . $ones[$o] : '');
        };

        $threeDigits = function ($n) use ($ones, $twoDigits) {
            $out = ''; $h = (int)($n / 100); $rest = $n % 100;
            if ($h) $out .= $ones[$h] . ' Hundred';
            if ($rest) $out .= ($out ? ' ' : '') . $twoDigits($rest);
            return $out;
        };

        $words = '';
        $arab  = (int)($rupees / 1000000000); $rupees %= 1000000000;
        $crore = (int)($rupees / 10000000); $rupees %= 10000000;
        $lakh  = (int)($rupees / 100000); $rupees %= 100000;
        $thousand = (int)($rupees / 1000); $hundred  = $rupees % 1000;

        if ($arab)     $words .= $threeDigits($arab) . ' Arab ';
        if ($crore)    $words .= $threeDigits($crore) . ' Crore ';
        if ($lakh)     $words .= $threeDigits($lakh) . ' Lakh ';
        if ($thousand) $words .= $threeDigits($thousand) . ' Thousand ';
        if ($hundred)  $words .= $threeDigits($hundred);

        $words = trim($words);
        if ($words === '') $words = 'Zero';

        if ($withRupees) {
            $out = $words . ' Rupees';
            if ($paisa > 0) $out .= ' and ' . $twoDigits($paisa) . ' Paisa';
            return $out . ' Only';
        }
        return $words . ' Only';
    }
}

/**
 * Encrypt data using the system key.
 */
if (!function_exists('encrypt')) {
    function encrypt($data) {
        $key = $_ENV['ENCRYPTION_KEY'] ?? '';
        $iv = $_ENV['ENCRYPTION_IV'] ?? '';
        if (!$key || !$iv) return $data;
        
        $method = 'aes-256-cbc';
        $key = substr(hash('sha256', $key), 0, 32);
        $iv = substr(hash('sha256', $iv), 0, 16);
        
        $encrypted = openssl_encrypt($data, $method, $key, 0, $iv);
        return base64_encode($encrypted);
    }
}

/**
 * Decrypt data using the system key.
 */
if (!function_exists('decrypt')) {
    function decrypt($data) {
        $key = $_ENV['ENCRYPTION_KEY'] ?? '';
        $iv = $_ENV['ENCRYPTION_IV'] ?? '';
        if (!$key || !$iv) return $data;
        
        $method = 'aes-256-cbc';
        $key = substr(hash('sha256', $key), 0, 32);
        $iv = substr(hash('sha256', $iv), 0, 16);
        
        $decrypted = openssl_decrypt(base64_decode($data), $method, $key, 0, $iv);
        return $decrypted;
    }
}
