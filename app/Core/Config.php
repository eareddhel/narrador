<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Narrador Studio
|--------------------------------------------------------------------------
| Archivo : Config.php
| Autor   : Roberto + ChatGPT
| Versión : 0.1.0
|--------------------------------------------------------------------------
*/

namespace App\Core;

final class Config
{
    private static array $config = [];

    private static bool $loaded = false;

    public static function load(): void
    {
        if (self::$loaded) {
            return;
        }

        $configDir = ROOT_PATH . '/config';

        if (is_dir($configDir) === false) {
            self::$loaded = true;

            return;
        }

        $files = glob($configDir . '/*.php');

        if ($files === false) {
            self::$loaded = true;

            return;
        }

        foreach ($files as $file) {
            $key = pathinfo($file, PATHINFO_FILENAME);
            $data = require $file;

            if (is_array($data)) {
                self::$config[$key] = $data;
            }
        }

        self::$loaded = true;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $parts = explode('.', $key);

        if (count($parts) < 2) {
            return $default;
        }

        $file = $parts[0];
        $nestedKey = implode('.', array_slice($parts, 1));

        if (array_key_exists($file, self::$config) === false) {
            return $default;
        }

        $value = self::$config[$file];

        $keyParts = explode('.', $nestedKey);

        foreach ($keyParts as $part) {
            if (is_array($value) && array_key_exists($part, $value)) {
                $value = $value[$part];
            } else {
                return $default;
            }
        }

        return $value;
    }

    public static function has(string $key): bool
    {
        return self::get($key) !== null;
    }

    public static function all(): array
    {
        return self::$config;
    }
}
