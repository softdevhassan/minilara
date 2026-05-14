<?php
namespace App;

use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

/**
 * Industry Standard Request Wrapper
 */
class Request {
    private static $instance = null;
    private $request;

    public function __construct() {
        $this->request = SymfonyRequest::createFromGlobals();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function input($key, $default = null) {
        return $this->request->get($key, $default);
    }

    public function all() {
        return array_merge($this->request->query->all(), $this->request->request->all());
    }

    public function method() {
        return $this->request->getMethod();
    }

    public function path() {
        return '/' . trim($this->request->getPathInfo(), '/');
    }

    public function file($key) {
        return $this->request->files->get($key);
    }

    public function header($key, $default = null) {
        return $this->request->headers->get($key, $default);
    }
}
