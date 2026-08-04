<?php

$heading = isset($pageHeading) && is_string($pageHeading) && $pageHeading !== '' ? $pageHeading : 'Narrador Studio';
$message = isset($emptyStateMessage) && is_string($emptyStateMessage) && $emptyStateMessage !== ''
    ? $emptyStateMessage
    : 'Aún no tienes proyectos.';
$version = isset($appVersion) && is_string($appVersion) && $appVersion !== '' ? $appVersion : 'v0.1';
?>
<div class="container">
    <div class="studio-grid">
        <section class="welcome-panel" aria-labelledby="dashboard-title">
            <div class="eyebrow mb-3">Dashboard operativo</div>
            <h1 id="dashboard-title" class="display-title mb-3">
                <?= htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') ?>
            </h1>
            <p class="lead-copy mb-0">
                Organiza guiones, prepara narraciones y conserva cada proyecto de audio en un flujo claro,
                tranquilo y listo para crecer.
            </p>
        </section>

        <section class="action-panel" aria-labelledby="primary-action-title">
            <div class="panel-label mb-2">Acción principal</div>
            <h2 id="primary-action-title" class="section-title mb-3">Crear proyecto</h2>
            <p class="section-copy mb-4">
                El primer proyecto reunirá tus guiones y futuras narraciones en un mismo espacio de trabajo.
            </p>
            <button class="btn btn-primary studio-primary-action" type="button" disabled aria-describedby="project-action-note">
                <i class="bi bi-plus-lg" aria-hidden="true"></i>
                Nuevo proyecto
            </button>
            <p id="project-action-note" class="availability-note mb-0 mt-3">Disponible próximamente.</p>
        </section>
    </div>

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

    <section class="future-areas mt-4 mt-lg-5" aria-labelledby="future-areas-title">
        <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-4">
            <div>
                <div class="panel-label mb-2">Áreas futuras</div>
                <h2 id="future-areas-title" class="section-title mb-0">Espacios preparados para crecer</h2>
            </div>
            <span class="version-note align-self-md-end">Versión <?= htmlspecialchars($version, ENT_QUOTES, 'UTF-8') ?></span>
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
