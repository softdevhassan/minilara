<?php
namespace App;

use Illuminate\Validation\Factory;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use Illuminate\Filesystem\Filesystem;

/**
 * Laravel-Standard Validator Wrapper
 */
class Validator {
    private static $factory;

    public static function make($data, $rules, $messages = []) {
        if (!self::$factory) {
            $loader = new FileLoader(new Filesystem(), BASE_PATH . '/app/lang');
            $translator = new Translator($loader, 'en');
            self::$factory = new Factory($translator);
        }

        return self::$factory->make($data, $rules, $messages);
    }
}
