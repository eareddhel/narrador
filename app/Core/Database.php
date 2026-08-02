<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Narrador Studio
|--------------------------------------------------------------------------
| Archivo : Database.php
| Autor   : Roberto + ChatGPT
| Versión : 0.1.0
|--------------------------------------------------------------------------
*/

namespace App\Core;

use App\Core\Exceptions\DatabaseException;
use PDO;
use PDOException;
use PDOStatement;
use ReflectionClass;
use Throwable;

final class Database
{
    private PDO $pdo;

    public function __construct(?array $config = null)
    {
        $config = $config ?? $this->resolveConfig();

        $this->validateConfig($config);

        try {
            $this->pdo = new PDO(
                $this->buildDsn($config),
                (string) $config['username'],
                (string) $config['password'],
                $this->defaultOptions()
            );
        } catch (PDOException $exception) {
            throw new DatabaseException('No se pudo establecer la conexión con la base de datos.', 0, $exception);
        }
    }

    public static function fromPdo(PDO $pdo): self
    {
        try {
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            throw new DatabaseException('No se pudo configurar la conexión PDO inyectada.', 0, $exception);
        }

        $reflection = new ReflectionClass(self::class);
        $database = $reflection->newInstanceWithoutConstructor();
        $database->pdo = $pdo;

        return $database;
    }

    public function select(string $sql, array $parameters = []): array
    {
        try {
            $statement = $this->prepareAndExecute($sql, $parameters);

            return $statement->fetchAll();
        } catch (PDOException $exception) {
            throw $this->toDatabaseException('No se pudo ejecutar la consulta de selección.', $exception);
        }
    }

    public function selectOne(string $sql, array $parameters = []): ?array
    {
        try {
            $statement = $this->prepareAndExecute($sql, $parameters);
            $row = $statement->fetch();

            if ($row === false) {
                return null;
            }

            return $row;
        } catch (PDOException $exception) {
            throw $this->toDatabaseException('No se pudo ejecutar la consulta de selección.', $exception);
        }
    }

    public function statement(string $sql, array $parameters = []): int
    {
        try {
            $statement = $this->prepareAndExecute($sql, $parameters);

            return $statement->rowCount();
        } catch (PDOException $exception) {
            throw $this->toDatabaseException('No se pudo ejecutar la sentencia SQL.', $exception);
        }
    }

    public function insert(string $sql, array $parameters = []): string
    {
        try {
            $this->prepareAndExecute($sql, $parameters);

            return $this->pdo->lastInsertId();
        } catch (PDOException $exception) {
            throw $this->toDatabaseException('No se pudo ejecutar la inserción.', $exception);
        }
    }

    public function update(string $sql, array $parameters = []): int
    {
        return $this->statement($sql, $parameters);
    }

    public function delete(string $sql, array $parameters = []): int
    {
        return $this->statement($sql, $parameters);
    }

    public function transaction(callable $callback): mixed
    {
        try {
            $this->pdo->beginTransaction();
            $result = $callback($this);
            $this->pdo->commit();

            return $result;
        } catch (PDOException $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $this->toDatabaseException('No se pudo completar la transacción.', $exception);
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function inTransaction(): bool
    {
        return $this->pdo->inTransaction();
    }

    private function resolveConfig(): array
    {
        $config = Config::get('database');

        if (is_array($config)) {
            return $config;
        }

        return [
            'host' => Config::get('database.host'),
            'port' => Config::get('database.port'),
            'database' => Config::get('database.database'),
            'username' => Config::get('database.username'),
            'password' => Config::get('database.password'),
        ];
    }

    private function validateConfig(array $config): void
    {
        $requiredKeys = ['host', 'port', 'database', 'username', 'password'];

        foreach ($requiredKeys as $key) {
            if (array_key_exists($key, $config) === false) {
                throw new DatabaseException(sprintf('Falta la clave de configuración de base de datos "%s".', $key));
            }
        }

        if (
            trim((string) $config['host']) === ''
            || trim((string) $config['port']) === ''
            || trim((string) $config['database']) === ''
            || trim((string) $config['username']) === ''
        ) {
            throw new DatabaseException('La configuración de base de datos está incompleta.');
        }
    }

    private function buildDsn(array $config): string
    {
        return sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            (string) $config['host'],
            (string) $config['port'],
            (string) $config['database']
        );
    }

    private function defaultOptions(): array
    {
        return [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
    }

    private function prepareAndExecute(string $sql, array $parameters = []): PDOStatement
    {
        $statement = $this->pdo->prepare($sql);

        if ($statement === false) {
            throw new PDOException('No se pudo preparar la consulta SQL.');
        }

        $statement->execute($this->normalizeParameters($parameters));

        return $statement;
    }

    private function normalizeParameters(array $parameters): array
    {
        $normalizedParameters = [];

        foreach ($parameters as $key => $value) {
            if (is_string($key) && str_starts_with($key, ':') === false) {
                $normalizedParameters[':' . $key] = $value;

                continue;
            }

            $normalizedParameters[$key] = $value;
        }

        return $normalizedParameters;
    }

    private function toDatabaseException(string $message, PDOException $exception): DatabaseException
    {
        return new DatabaseException($message, 0, $exception);
    }
}