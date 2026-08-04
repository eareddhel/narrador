<?php

$heading = isset($pageHeading) && is_string($pageHeading) && $pageHeading !== '' ? $pageHeading : 'Narrador Studio';
$message = isset($emptyStateMessage) && is_string($emptyStateMessage) && $emptyStateMessage !== ''
    ? $emptyStateMessage
    : 'Aún no tienes proyectos.';
$projects = isset($projects) && is_array($projects) ? $projects : [];
$hasProjects = $projects !== [];
?>
<div class="container">
    <div class="studio-grid">
        <section class="welcome-panel" aria-labelledby="dashboard-title">
            <div class="eyebrow mb-3">¡Hola! ¿Qué vamos a crear hoy?</div>
            <h1 id="dashboard-title" class="display-title mb-3">
                <?= htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') ?>
            </h1>
            <p class="lead-copy mb-0">
                Organiza tus guiones, crea narraciones y desarrolla tus proyectos paso a paso.
            </p>
        </section>

        <section class="action-panel" aria-labelledby="primary-action-title">
            <div class="panel-label mb-2">Acción principal</div>
            <h2 id="primary-action-title" class="section-title mb-3">Crear proyecto</h2>
            <p class="section-copy mb-4">
                Empieza con nombre y descripción. Las voces, secciones y exportaciones aparecen cuando el proyecto ya existe.
            </p>
            <a class="btn btn-primary studio-primary-action" href="/projects/new">
                <i class="bi bi-plus-lg" aria-hidden="true"></i>
                Nuevo proyecto
            </a>
        </section>
    </div>

    <?php if ($hasProjects): ?>
        <section class="project-list mt-4 mt-lg-5" aria-labelledby="recent-projects-title">
            <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-4">
                <div>
                    <div class="panel-label mb-2">Proyectos recientes</div>
                    <h2 id="recent-projects-title" class="section-title mb-0">Continúa donde quedó la narración</h2>
                </div>
            </div>

            <div class="row g-3 g-lg-4">
                <?php foreach ($projects as $project): ?>
                    <?php
                    $status = (string) ($project['status'] ?? '');
                    $statusLabel = match ($status) {
                        'draft' => 'Borrador',
                        'active' => 'En producción',
                        'archived' => 'Archivado',
                        default => $status,
                    };
                    ?>
                    <div class="col-md-6 col-xl-4">
                        <article class="card area-card project-card h-100">
                            <div class="card-body p-4 d-flex flex-column">
                                <div class="area-icon mb-4">
                                    <i class="bi bi-folder2-open" aria-hidden="true"></i>
                                    <span class="badge text-bg-light project-status-badge">
                                        <?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </div>
                                <h3 class="card-title h5">
                                    <?= htmlspecialchars((string) $project['name'], ENT_QUOTES, 'UTF-8') ?>
                                </h3>
                                <p class="card-text flex-grow-1">
                                    <?= htmlspecialchars((string) ($project['description'] ?? 'Sin descripción todavía.'), ENT_QUOTES, 'UTF-8') ?>
                                </p>
                                <a class="stretched-link project-open-link" href="/projects/<?= rawurlencode((string) $project['uuid']) ?>">
                                    Abrir proyecto
                                </a>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php else: ?>
        <section class="empty-state mt-4 mt-lg-5" aria-labelledby="empty-projects-title">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <div class="panel-label mb-2">Estado vacío</div>
                    <h2 id="empty-projects-title" class="section-title mb-3">
                        <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
                    </h2>
                    <p class="section-copy mb-0">
                        Crear el primero permitirá organizar los guiones y generar narraciones sin mezclar borradores,
                        audios y exportaciones.
                    </p>
                </div>
                <div class="col-lg-5">
                    <div class="empty-state-card" aria-hidden="true">
                        <span class="empty-state-line"></span>
                        <span class="empty-state-line short"></span>
                        <span class="empty-state-line accent"></span>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <section class="future-areas mt-4 mt-lg-5" aria-labelledby="future-areas-title">
        <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-4">
            <div>
                <div class="panel-label mb-2">Áreas futuras</div>
                <h2 id="future-areas-title" class="section-title mb-0">Espacios preparados para crecer</h2>
            </div>
        </div>

        <div class="row g-3 g-lg-4">
            <div class="col-md-4">
                <article class="card area-card h-100">
                    <div class="card-body p-4">
                        <div class="area-icon mb-4">
                            <i class="bi bi-folder2-open" aria-hidden="true"></i>
                            <span>Proyectos</span>
                        </div>
                        <h3 class="card-title h5">Proyectos</h3>
                        <p class="card-text">Biblioteca para guiones, secciones y estados de producción.</p>
                        <span class="badge text-bg-light">Activo</span>
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
                        <h3 class="card-title h5">Narraciones</h3>
                        <p class="card-text">Generación y revisión de voces para cada sección del proyecto.</p>
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
                        <h3 class="card-title h5">Exportaciones</h3>
                        <p class="card-text">Salida ordenada de audios y materiales listos para publicar.</p>
                        <span class="badge text-bg-light">Próximamente</span>
                    </div>
                </article>
            </div>
        </div>
    </section>
</div>
