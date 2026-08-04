<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Narrador Studio
|--------------------------------------------------------------------------
| Archivo : Project.php
| Autor   : Roberto + ChatGPT
| Version : 0.1.0
|--------------------------------------------------------------------------
*/

namespace App\Models;

use App\Core\Database;

final class Project
{
    private const PUBLIC_COLUMNS = 'uuid, name, description, status, created_at, updated_at, archived_at';

    public function __construct(
        private readonly Database $database
    ) {
    }

    public function create(array $project): array
    {
        $this->database->insert(
            'INSERT INTO projects (uuid, name, description, status, created_at, updated_at, archived_at)
             VALUES (:uuid, :name, :description, :status, :created_at, :updated_at, :archived_at)',
            [
                'uuid' => $project['uuid'],
                'name' => $project['name'],
                'description' => $project['description'],
                'status' => $project['status'],
                'created_at' => $project['created_at'],
                'updated_at' => $project['updated_at'],
                'archived_at' => $project['archived_at'],
            ]
        );

        $createdProject = $this->findByUuid((string) $project['uuid']);

        return $createdProject ?? [];
    }

    public function findByUuid(string $uuid): ?array
    {
        $row = $this->database->selectOne(
            'SELECT ' . self::PUBLIC_COLUMNS . ' FROM projects WHERE uuid = :uuid LIMIT 1',
            ['uuid' => $uuid]
        );

        return $this->mapRow($row);
    }

    public function allActive(): array
    {
        $rows = $this->database->select(
            'SELECT ' . self::PUBLIC_COLUMNS . '
             FROM projects
             WHERE status <> :archived_status
             ORDER BY created_at DESC, id DESC',
            ['archived_status' => 'archived']
        );

        return array_map([$this, 'mapRow'], $rows);
    }

    public function updateByUuid(string $uuid, array $changes): bool
    {
        $allowedFields = ['name', 'description', 'updated_at'];
        $assignments = [];
        $parameters = ['uuid' => $uuid];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $changes) === false) {
                continue;
            }

            $assignments[] = $field . ' = :' . $field;
            $parameters[$field] = $changes[$field];
        }

        if ($assignments === []) {
            return false;
        }

        $affectedRows = $this->database->update(
            'UPDATE projects SET ' . implode(', ', $assignments) . ' WHERE uuid = :uuid',
            $parameters
        );

        return $affectedRows > 0;
    }

    public function archiveByUuid(string $uuid, string $archivedAt): bool
    {
        $affectedRows = $this->database->update(
            'UPDATE projects
             SET status = :status, archived_at = :archived_at, updated_at = :updated_at
             WHERE uuid = :uuid',
            [
                'uuid' => $uuid,
                'status' => 'archived',
                'archived_at' => $archivedAt,
                'updated_at' => $archivedAt,
            ]
        );

        return $affectedRows > 0;
    }

    public function existsByUuid(string $uuid): bool
    {
        $row = $this->database->selectOne(
            'SELECT uuid FROM projects WHERE uuid = :uuid LIMIT 1',
            ['uuid' => $uuid]
        );

        return $row !== null;
    }

    private function mapRow(?array $row): ?array
    {
        if ($row === null) {
            return null;
        }

        return [
            'uuid' => (string) $row['uuid'],
            'name' => (string) $row['name'],
            'description' => $row['description'] === null ? null : (string) $row['description'],
            'status' => (string) $row['status'],
            'created_at' => (string) $row['created_at'],
            'updated_at' => (string) $row['updated_at'],
            'archived_at' => $row['archived_at'] === null ? null : (string) $row['archived_at'],
        ];
    }
}
