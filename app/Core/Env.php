<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Narrador Studio
|--------------------------------------------------------------------------
| Archivo : Env.php
| Autor   : Roberto + ChatGPT
| Versión : 0.1.0
|--------------------------------------------------------------------------
*/

namespace App\Core;

final class Env
{
    private static array $variables = [];

    private static bool $loaded = false;

    public static function load(): void
    {
        if (self::$loaded) {
            return;
        }

        $filePath = ROOT_PATH . '/.env';

        if (is_file($filePath) === false) {
            self::$loaded = true;

            return;
        }

        $content = file_get_contents($filePath);

        if ($content === false) {
            self::$loaded = true;

            return;
        }

        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || $line[0] === '#') {
                continue;
            }

            $equalPos = strpos($line, '=');

            if ($equalPos === false) {
                continue;
            }

            $key = trim(substr($line, 0, $equalPos));
            $value = trim(substr($line, $equalPos + 1));

            if ($value !== '' && (($value[0] === '"' && substr($value, -1) === '"') || ($value[0] === "'" && substr($value, -1) === "'"))) {
                $value = substr($value, 1, -1);
            }

            self::$variables[$key] = $value;
        }

        self::$loaded = true;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$variables[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return array_key_exists($key, self::$variables);
    }

    public static function all(): array
    {
        return self::$variables;
    }

    public static function getString(string $key, string $default = ''): string
    {
        $value = self::get($key, $default);

        return is_string($value) ? $value : (string) $value;
    }

    public static function getInt(string $key, int $default = 0): int
    {
        $value = self::get($key, $default);

        return is_int($value) ? $value : (int) $value;
    }

    public static function getFloat(string $key, float $default = 0.0): float
    {
        $value = self::get($key, $default);

        return is_float($value) ? $value : (float) $value;
    }

    public static function getBool(string $key, bool $default = false): bool
    {
        $value = self::get($key, null);

        if ($value === null) {
            return $default;
        }

        if (is_bool($value)) {
            return $value;
        }

        $normalized = strtolower(trim((string) $value));

        return in_array($normalized, ['true', 'yes', 'on', '1'], true);
    }
}
