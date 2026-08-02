<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Narrador Studio
|--------------------------------------------------------------------------
| Archivo : test-database.php
| Autor   : Roberto + ChatGPT
| Versión : 0.1.0
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Database;
use App\Core\Exceptions\DatabaseException;

$failures = [];
$checks = 0;

function assertTrue(bool $condition, string $message): void
{
    global $failures, $checks;

    $checks++;

    if ($condition === false) {
        $failures[] = $message;
    }
}

if (extension_loaded('pdo_sqlite') === false) {
    fwrite(STDERR, 'Missing development dependency: pdo_sqlite extension is not available.' . PHP_EOL);
    exit(1);
}

try {
    assertTrue(class_exists(Database::class), 'Autoloader no resolvió App\Core\Database.');
    assertTrue(
        class_exists(DatabaseException::class),
        'Autoloader no resolvió App\Core\Exceptions\DatabaseException.'
    );

    $pdo = new PDO('sqlite::memory:');
    $database = Database::fromPdo($pdo);

    assertTrue($database instanceof Database, 'Database::fromPdo() no devolvió una instancia de Database.');
    assertTrue($database->inTransaction() === false, 'Database inició con una transacción activa.');

    $database->statement(
        'CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, email TEXT NOT NULL)'
    );

    $firstId = $database->insert(
        'INSERT INTO users (name, email) VALUES (:name, :email)',
        [
            'name' => 'Ada',
            'email' => 'ada@example.test',
        ]
    );

    assertTrue($firstId !== '', 'insert() no devolvió un identificador.');

    $database->insert(
        'INSERT INTO users (name, email) VALUES (:name, :email)',
        [
            ':name' => 'Grace',
            ':email' => 'grace@example.test',
        ]
    );

    $rows = $database->select('SELECT id, name, email FROM users ORDER BY id');

    assertTrue(count($rows) === 2, 'select() no devolvió varias filas.');
    assertTrue(array_key_exists('name', $rows[0]), 'select() no devolvió filas asociativas.');

    $row = $database->selectOne(
        'SELECT id, name, email FROM users WHERE email = :email',
        ['email' => 'ada@example.test']
    );

    assertTrue(is_array($row), 'selectOne() no devolvió una fila existente.');
    assertTrue($row['name'] === 'Ada', 'selectOne() devolvió una fila inesperada.');

    $missingRow = $database->selectOne(
        'SELECT id, name, email FROM users WHERE email = :email',
        ['email' => 'missing@example.test']
    );

    assertTrue($missingRow === null, 'selectOne() no devolvió null sin coincidencias.');

    $statementCount = $database->statement(
        'INSERT INTO users (name, email) VALUES (:name, :email)',
        [
            'name' => 'Linus',
            'email' => 'linus@example.test',
        ]
    );

    assertTrue($statementCount === 1, 'statement() no devolvió filas afectadas.');

    $updatedRows = $database->update(
        'UPDATE users SET name = :name WHERE email = :email',
        [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.test',
        ]
    );

    assertTrue($updatedRows === 1, 'update() no devolvió filas afectadas.');

    $updatedRow = $database->selectOne(
        'SELECT name FROM users WHERE email = :email',
        ['email' => 'ada@example.test']
    );

    assertTrue($updatedRow['name'] === 'Ada Lovelace', 'update() no modificó los datos esperados.');

    $deletedRows = $database->delete(
        'DELETE FROM users WHERE email = :email',
        ['email' => 'linus@example.test']
    );

    assertTrue($deletedRows === 1, 'delete() no devolvió filas afectadas.');

    $remainingRows = $database->select('SELECT id FROM users WHERE email = :email', ['email' => 'linus@example.test']);

    assertTrue($remainingRows === [], 'delete() no eliminó los datos esperados.');

    $transactionResult = $database->transaction(
        function (Database $database): string {
            assertTrue($database->inTransaction(), 'inTransaction() no reflejó una transacción activa.');

            $database->insert(
                'INSERT INTO users (name, email) VALUES (:name, :email)',
                [
                    'name' => 'Commit User',
                    'email' => 'commit@example.test',
                ]
            );

            return 'committed';
        }
    );

    assertTrue($transactionResult === 'committed', 'transaction() no devolvió el valor del callback.');
    assertTrue($database->inTransaction() === false, 'transaction() dejó una transacción abierta tras commit.');

    $committedRow = $database->selectOne(
        'SELECT id FROM users WHERE email = :email',
        ['email' => 'commit@example.test']
    );

    assertTrue($committedRow !== null, 'transaction() no confirmó los cambios.');

    try {
        $database->transaction(
            function (Database $database): void {
                $database->insert(
                    'INSERT INTO users (name, email) VALUES (:name, :email)',
                    [
                        'name' => 'Rollback User',
                        'email' => 'rollback@example.test',
                    ]
                );

                throw new DomainException('Domain rollback');
            }
        );

        assertTrue(false, 'transaction() no relanzó la excepción de dominio.');
    } catch (DomainException $exception) {
        assertTrue($exception->getMessage() === 'Domain rollback', 'transaction() alteró la excepción no PDO.');
    }

    assertTrue($database->inTransaction() === false, 'transaction() dejó una transacción abierta tras rollback.');

    $rolledBackRow = $database->selectOne(
        'SELECT id FROM users WHERE email = :email',
        ['email' => 'rollback@example.test']
    );

    assertTrue($rolledBackRow === null, 'transaction() conservó cambios después de rollback.');

    try {
        $database->statement('INSERT INTO missing_table (name) VALUES (:name)', ['name' => 'Broken']);
        assertTrue(false, 'Un error PDO no fue transformado en DatabaseException.');
    } catch (DatabaseException $exception) {
        assertTrue($exception->getPrevious() instanceof PDOException, 'DatabaseException no conservó PDOException previa.');
    }

    $reflection = new ReflectionClass(Database::class);

    assertTrue($reflection->getProperties(ReflectionProperty::IS_PUBLIC) === [], 'Database expone propiedades públicas.');
    assertTrue($reflection->hasMethod('getPdo') === false, 'Database expone getPdo().');
    assertTrue($reflection->hasMethod('connection') === false, 'Database expone connection().');

    foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        $returnType = $method->getReturnType();

        if ($returnType === null) {
            continue;
        }

        assertTrue(
            in_array($returnType->getName(), [PDO::class, PDOStatement::class], true) === false,
            'Database expone PDO o PDOStatement mediante un método público.'
        );
    }
} catch (Throwable $exception) {
    $failures[] = sprintf(
        'Excepción inesperada: %s: %s',
        $exception::class,
        $exception->getMessage()
    );
}

if ($failures !== []) {
    echo 'Database tests failed.' . PHP_EOL;

    foreach ($failures as $failure) {
        echo '[FAIL] ' . $failure . PHP_EOL;
    }

    exit(1);
}

echo 'Database tests passed.' . PHP_EOL;
echo 'Checks: ' . $checks . PHP_EOL;