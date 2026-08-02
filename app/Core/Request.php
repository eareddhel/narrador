<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Narrador Studio
|--------------------------------------------------------------------------
| Archivo : Request.php
| Autor   : Roberto + ChatGPT
| Versión : 0.1.0
|--------------------------------------------------------------------------
*/

namespace App\Core;

final class Request
{
    private array $get = [];

    private array $post = [];

    private array $files = [];

    private array $cookie = [];

    private array $server = [];

    private array $routeParameters = [];

    private function __construct(
        array $get,
        array $post,
        array $files,
        array $cookie,
        array $server,
        array $routeParameters = []
    ) {
        $this->get = $get;
        $this->post = $post;
        $this->files = $files;
        $this->cookie = $cookie;
        $this->server = $server;
        $this->routeParameters = $routeParameters;
    }

    public static function capture(): self
    {
        return new self(
            $_GET,
            $_POST,
            $_FILES,
            $_COOKIE,
            $_SERVER
        );
    }

    public function method(): string
    {
        return strtoupper((string) ($this->server['REQUEST_METHOD'] ?? 'GET'));
    }

    public function isGet(): bool
    {
        return $this->method() === 'GET';
    }

    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->get[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        if (isset($this->post[$key])) {
            return $this->post[$key];
        }

        return $this->get[$key] ?? $default;
    }

    public function file(string $key): mixed
    {
        return $this->files[$key] ?? null;
    }

    public function header(string $key, mixed $default = null): mixed
    {
        $normalizedKey = 'HTTP_' . strtoupper(str_replace('-', '_', $key));

        return $this->server[$normalizedKey] ?? $default;
    }

    public function cookie(string $key, mixed $default = null): mixed
    {
        return $this->cookie[$key] ?? $default;
    }

    public function server(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }

    public function route(string $key, mixed $default = null): mixed
    {
        return $this->routeParameters[$key] ?? $default;
    }

    public function routeParameters(): array
    {
        return $this->routeParameters;
    }

    public function withRouteParameters(array $parameters): self
    {
        $request = clone $this;
        $request->routeParameters = $parameters;

        return $request;
    }

    public function ip(): string
    {
        return $this->server['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public function userAgent(): string
    {
        return $this->server['HTTP_USER_AGENT'] ?? '';
    }

    public function uri(): string
    {
        return $this->server['REQUEST_URI'] ?? '/';
    }

    public function path(): string
    {
        $uri = $this->uri();

        $position = strpos($uri, '?');

        if ($position !== false) {
            return substr($uri, 0, $position);
        }

        return $uri;
    }

    public function host(): string
    {
        return $this->server['HTTP_HOST'] ?? '';
    }

    public function scheme(): string
    {
        if ($this->isSecure()) {
            return 'https';
        }

        return 'http';
    }

    public function isSecure(): bool
    {
        return ($this->server['HTTPS'] ?? '') !== ''
            && $this->server['HTTPS'] !== 'off';
    }

    public function ajax(): bool
    {
        return $this->header('X-Requested-With') === 'XMLHttpRequest';
    }

    public function all(): array
    {
        return array_merge($this->get, $this->post);
    }

    public function only(array $keys): array
    {
        $all = $this->all();
        $filtered = [];

        foreach ($keys as $key) {
            if (isset($all[$key])) {
                $filtered[$key] = $all[$key];
            }
        }

        return $filtered;
    }

    public function except(array $keys): array
    {
        $all = $this->all();

        foreach ($keys as $key) {
            unset($all[$key]);
        }

        return $all;
    }
}