<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Narrador Studio
|--------------------------------------------------------------------------
| Archivo : ProjectService.php
| Autor   : Roberto + ChatGPT
| Version : 0.1.0
|--------------------------------------------------------------------------
*/

namespace App\Services;

use App\Core\Config;
use App\Models\Project;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

final class ProjectService
{
    private const NAME_MAX_LENGTH = 150;

    private const DESCRIPTION_MAX_LENGTH = 1000;

    public function __construct(
        private readonly Project $projects
    ) {
    }

    public function create(string $name, ?string $description = null): array
    {
        $uuid = $this->generateUuidV4();
        $timestamp = $this->currentTimestamp();

        $project = $this->projects->create([
            'uuid' => $uuid,
            'name' => $this->normalizeName($name),
            'description' => $this->normalizeDescription($description),
            'status' => 'draft',
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
            'archived_at' => null,
        ]);

        if ($project !== []) {
            return $project;
        }

        $createdProject = $this->projects->findByUuid($uuid);

        if ($createdProject === null) {
            throw new RuntimeException('No se pudo recuperar el proyecto creado.');
        }

        return $createdProject;
    }

    public function get(string $uuid): ?array
    {
        return $this->projects->findByUuid($this->normalizeUuid($uuid));
    }

    public function listActive(): array
    {
        return $this->projects->allActive();
    }

    public function update(string $uuid, string $name, ?string $description = null): bool
    {
        $uuid = $this->normalizeUuid($uuid);

        if ($this->projects->existsByUuid($uuid) === false) {
            throw new InvalidArgumentException('El proyecto indicado no existe.');
        }

        return $this->projects->updateByUuid(
            $uuid,
            [
                'name' => $this->normalizeName($name),
                'description' => $this->normalizeDescription($description),
                'updated_at' => $this->currentTimestamp(),
            ]
        );
    }

    public function archive(string $uuid): bool
    {
        $uuid = $this->normalizeUuid($uuid);

        if ($this->projects->existsByUuid($uuid) === false) {
            throw new InvalidArgumentException('El proyecto indicado no existe.');
        }

        return $this->projects->archiveByUuid($uuid, $this->currentTimestamp());
    }

    public function exists(string $uuid): bool
    {
        return $this->projects->existsByUuid($this->normalizeUuid($uuid));
    }

    private function normalizeName(string $name): string
    {
        $name = trim($name);

        if ($name === '') {
            throw new InvalidArgumentException('El nombre del proyecto es obligatorio.');
        }

        if (strlen($name) > self::NAME_MAX_LENGTH) {
            throw new InvalidArgumentException('El nombre del proyecto no puede superar 150 caracteres.');
        }

        return $name;
    }

    private function normalizeDescription(?string $description): ?string
    {
        if ($description === null) {
            return null;
        }

        $description = trim($description);

        if ($description === '') {
            return null;
        }

        if (strlen($description) > self::DESCRIPTION_MAX_LENGTH) {
            throw new InvalidArgumentException('La descripcion del proyecto no puede superar 1000 caracteres.');
        }

        return $description;
    }

    private function normalizeUuid(string $uuid): string
    {
        $uuid = trim($uuid);

        if ($uuid === '') {
            throw new InvalidArgumentException('El UUID del proyecto es obligatorio.');
        }

        return $uuid;
    }

    private function currentTimestamp(): string
    {
        return (new DateTimeImmutable('now', $this->timezone()))->format('Y-m-d H:i:s');
    }

    private function timezone(): DateTimeZone
    {
        $timezone = Config::get('app.timezone', 'UTC');
        $timezone = is_string($timezone) && trim($timezone) !== '' ? $timezone : 'UTC';

        try {
            return new DateTimeZone($timezone);
        } catch (Throwable) {
            return new DateTimeZone('UTC');
        }
    }

    private function generateUuidV4(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }
}
