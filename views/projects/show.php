<?php

$project = isset($project) && is_array($project) ? $project : [];
$name = (string) ($project['name'] ?? 'Proyecto');
$description = $project['description'] ?? null;
$status = (string) ($project['status'] ?? '');
$statusLabel = match ($status) {
    'draft' => 'Borrador',
    'active' => 'En producción',
    'archived' => 'Archivado',
    default => $status,
};
?>
<div class="container">
    <section class="welcome-panel project-hero" aria-labelledby="project-title">
        <div class="eyebrow mb-3">Proyecto abierto</div>
        <h1 id="project-title" class="display-title mb-3">
            <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>
        </h1>
        <p class="lead-copy mb-0">
            <?= htmlspecialchars(is_string($description) && $description !== '' ? $description : 'Este proyecto todavía no tiene descripción.', ENT_QUOTES, 'UTF-8') ?>
        </p>
    </section>

    <section class="project-meta mt-4 mt-lg-5" aria-labelledby="project-meta-title">
        <div class="panel-label mb-2">Información general</div>
        <h2 id="project-meta-title" class="section-title mb-4">Estado del proyecto</h2>
        <div class="row g-3 g-lg-4">
            <div class="col-md-4">
                <div class="meta-item h-100">
                    <span>Estado</span>
                    <strong><?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
            </div>
            <div class="col-md-4">
                <div class="meta-item h-100">
                    <span>Creado</span>
                    <strong><?= htmlspecialchars((string) ($project['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
            </div>
            <div class="col-md-4">
                <div class="meta-item h-100">
                    <span>Actualizado</span>
                    <strong><?= htmlspecialchars((string) ($project['updated_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
            </div>
        </div>
    </section>

    <section class="future-areas mt-4 mt-lg-5" aria-labelledby="project-workspaces-title">
        <div class="panel-label mb-2">Próximas áreas</div>
        <h2 id="project-workspaces-title" class="section-title mb-4">El trabajo crecerá dentro de este proyecto</h2>
        <div class="row g-3 g-lg-4">
            <div class="col-md-4">
                <article class="card area-card h-100">
                    <div class="card-body p-4">
                        <div class="area-icon mb-4">
                            <i class="bi bi-card-text" aria-hidden="true"></i>
                            <span>Secciones</span>
                        </div>
                        <p class="card-text">El guion se organizará en bloques manejables.</p>
                        <span class="badge text-bg-light">Próximamente</span>
                    </div>
                </article>
            </div>
            <div class="col-md-4">
                <article class="card area-card h-100">
                    <div class="card-body p-4">
                        <div class="area-icon mb-4">
                            <i class="bi bi-soundwave" aria-hidden="true"></i>
                            <span>Narraciones</span>
                        </div>
                        <p class="card-text">Cada sección podrá convertirse en audio revisable.</p>
                        <span class="badge text-bg-light">Próximamente</span>
                    </div>
                </article>
            </div>
            <div class="col-md-4">
                <article class="card area-card h-100">
                    <div class="card-body p-4">
                        <div class="area-icon mb-4">
                            <i class="bi bi-box-arrow-down" aria-hidden="true"></i>
                            <span>Exportaciones</span>
                        </div>
                        <p class="card-text">Los materiales finales saldrán desde el proyecto.</p>
                        <span class="badge text-bg-light">Próximamente</span>
                    </div>
                </article>
            </div>
        </div>
    </section>
</div>
