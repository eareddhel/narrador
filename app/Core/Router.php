<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Narrador Studio
|--------------------------------------------------------------------------
| Archivo : Router.php
| Autor   : Roberto + ChatGPT
| Versión : 0.1.0
|--------------------------------------------------------------------------
*/

namespace App\Core;

use App\Core\Exceptions\CoreException;
use App\Core\Exceptions\RouteNotFoundException;
use InvalidArgumentException;
use Throwable;
use UnexpectedValueException;

final class Router
{
    /** @var array<string, array<int, array{path: string, handler: callable|string, regex: string, parameters: array<int, string>}>> */
    private array $routes = [
        'GET' => [],
        'POST' => [],
    ];

    private mixed $controllerResolver;

    public function __construct(?callable $controllerResolver = null)
    {
        $this->controllerResolver = $controllerResolver;
    }

    public function get(string $path, callable|string $handler): self
    {
        return $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, callable|string $handler): self
    {
        return $this->addRoute('POST', $path, $handler);
    }

    public function dispatch(Request $request): Response
    {
        try {
            $match = $this->match($request->method(), $request->path());
            $requestWithRoute = $request->withRouteParameters($match['parameters']);
            $handler = $this->resolveHandler($match['handler']);
            $response = $handler($requestWithRoute);

            if ($response instanceof Response === false) {
                throw new UnexpectedValueException('Router handler must return an instance of App\\Core\\Response.');
            }

            return $response;
        } catch (RouteNotFoundException $exception) {
            return $this->errorResponse($exception, 404, 'Route not found.');
        } catch (CoreException $exception) {
            return $this->errorResponse($exception, 500, 'Internal server error.');
        } catch (Throwable $exception) {
            return $this->errorResponse($exception, 500, 'Internal server error.');
        }
    }

    public function run(): void
    {
        $request = Request::capture();
        $response = $this->dispatch($request);

        $response->send();
    }

    private function addRoute(string $method, string $path, callable|string $handler): self
    {
        $normalizedPath = $this->normalizeRoutePath($path);
        $this->routes[$method][] = [
            'path' => $normalizedPath,
            'handler' => $handler,
            'regex' => $this->compileRouteRegex($normalizedPath),
            'parameters' => $this->extractParameterNames($normalizedPath),
        ];

        return $this;
    }

    private function normalizeRoutePath(string $path): string
    {
        $path = trim($path);

        if ($path === '') {
            throw new InvalidArgumentException('Route path cannot be empty.');
        }

        if (str_contains($path, '?')) {
            $path = strtok($path, '?');

            if ($path === false) {
                throw new InvalidArgumentException('Route path cannot be empty.');
            }
        }

        if (str_starts_with($path, '/') === false) {
            $path = '/' . $path;
        }

        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        if ($path === '') {
            return '/';
        }

        return $path;
    }

    private function normalizeRequestPath(string $path): string
    {
        $path = parse_url($path, PHP_URL_PATH) ?? '/';
        $path = is_string($path) ? $path : '/';

        if ($path === '') {
            $path = '/';
        }

        if (str_starts_with($path, '/') === false) {
            $path = '/' . $path;
        }

        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        return $path === '' ? '/' : $path;
    }

    /** @return array{handler: callable|string, parameters: array<string, string>} */
    private function match(string $method, string $path): array
    {
        $method = strtoupper($method);
        $path = $this->normalizeRequestPath($path);

        foreach ($this->routes[$method] ?? [] as $route) {
            if (preg_match($route['regex'], $path, $matches) !== 1) {
                continue;
            }

            $parameters = [];

            foreach ($route['parameters'] as $name) {
                $parameters[$name] = rawurldecode($matches[$name]);
            }

            return [
                'handler' => $route['handler'],
                'parameters' => $parameters,
            ];
        }

        throw new RouteNotFoundException(sprintf('Route not found for %s %s.', $method, $path));
    }

    private function compileRouteRegex(string $path): string
    {
        if ($path === '/') {
            return '#^/$#';
        }

        $segments = explode('/', trim($path, '/'));
        $regexSegments = [];
        $usedParameters = [];

        foreach ($segments as $segment) {
            if ($segment === '') {
                throw new InvalidArgumentException('Route path cannot contain empty segments.');
            }

            if ($this->isPlaceholder($segment)) {
                $name = substr($segment, 1, -1);
                $this->assertValidPlaceholder($name, $usedParameters);
                $usedParameters[] = $name;
                $regexSegments[] = '(?P<' . $name . '>[^/]+)';

                continue;
            }

            if (str_contains($segment, '{') || str_contains($segment, '}')) {
                throw new InvalidArgumentException(sprintf('Invalid route placeholder in segment "%s".', $segment));
            }

            $regexSegments[] = preg_quote($segment, '#');
        }

        return '#^/' . implode('/', $regexSegments) . '$#';
    }

    /** @return array<int, string> */
    private function extractParameterNames(string $path): array
    {
        if ($path === '/') {
            return [];
        }

        $parameters = [];

        foreach (explode('/', trim($path, '/')) as $segment) {
            if ($this->isPlaceholder($segment)) {
                $parameters[] = substr($segment, 1, -1);
            }
        }

        return $parameters;
    }

    /** @param array<int, string> $usedParameters */
    private function assertValidPlaceholder(string $name, array $usedParameters): void
    {
        if ($name === '') {
            throw new InvalidArgumentException('Route placeholder name cannot be empty.');
        }

        if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $name) !== 1) {
            throw new InvalidArgumentException(sprintf('Invalid route placeholder name "%s".', $name));
        }

        if (in_array($name, $usedParameters, true)) {
            throw new InvalidArgumentException(sprintf('Repeated route placeholder "%s" is not allowed.', $name));
        }
    }

    private function isPlaceholder(string $segment): bool
    {
        return str_starts_with($segment, '{') && str_ends_with($segment, '}');
    }

    private function resolveHandler(callable|string $handler): callable
    {
        if (is_string($handler)) {
            if (class_exists($handler)) {
                $controller = $this->resolveController($handler);

                if (is_callable($controller) === false) {
                    throw new InvalidArgumentException(sprintf('Controller "%s" must be invokable.', $handler));
                }

                return $controller;
            }

            if (is_callable($handler)) {
                return $handler;
            }

            throw new InvalidArgumentException(sprintf('Handler class "%s" does not exist.', $handler));
        }

        if (is_callable($handler)) {
            return $handler;
        }

        throw new InvalidArgumentException('Route handler must be callable or an invokable class-string.');
    }

    private function resolveController(string $class): mixed
    {
        if ($this->controllerResolver !== null) {
            return ($this->controllerResolver)($class);
        }

        return new $class();
    }

    private function errorResponse(Throwable $exception, int $status, string $safeMessage): Response
    {
        $body = $safeMessage;

        if ($this->isDebug()) {
            $body .= PHP_EOL
                . 'Exception: ' . $exception::class . PHP_EOL
                . 'Message: ' . $exception->getMessage() . PHP_EOL
                . 'File: ' . $exception->getFile() . ':' . $exception->getLine();
        }

        return (new Response())
            ->status($status)
            ->header('Content-Type', 'text/plain; charset=UTF-8')
            ->body($body);
    }

    private function isDebug(): bool
    {
        return Config::get('app.debug', false) === true;
    }
}