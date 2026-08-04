<?php

$name = isset($name) && is_string($name) ? $name : '';
$description = isset($description) && is_string($description) ? $description : '';
$errors = isset($errors) && is_array($errors) ? $errors : [];
?>
<div class="container">
    <div class="studio-grid align-items-start">
        <section class="welcome-panel" aria-labelledby="new-project-title">
            <div class="eyebrow mb-3">Nuevo proyecto</div>
            <h1 id="new-project-title" class="display-title mb-3">Crear proyecto</h1>
            <p class="lead-copy mb-0">
                Dale un nombre al espacio donde vivirá la narración completa. Las decisiones avanzadas aparecerán después,
                cuando el proyecto ya tenga forma.
            </p>
        </section>

        <section class="action-panel" aria-labelledby="new-project-form-title">
            <div class="panel-label mb-2">Información inicial</div>
            <h2 id="new-project-form-title" class="section-title mb-3">Nombre y descripción</h2>

            <?php if ($errors !== []): ?>
                <div class="alert alert-danger" role="alert">
                    <?php foreach ($errors as $error): ?>
                        <p class="mb-0"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="/projects" method="post" novalidate>
                <div class="mb-3">
                    <label class="form-label" for="project-name">Nombre</label>
                    <input
                        class="form-control"
                        id="project-name"
                        name="name"
                        type="text"
                        value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"
                        maxlength="150"
                        required
                    >
                </div>

                <div class="mb-4">
                    <label class="form-label" for="project-description">Descripción</label>
                    <textarea
                        class="form-control"
                        id="project-description"
                        name="description"
                        rows="5"
                        maxlength="1000"
                    ><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <div class="d-flex flex-column flex-sm-row gap-2">
                    <button class="btn btn-primary studio-primary-action" type="submit">
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                        Crear y abrir
                    </button>
                    <a class="btn btn-outline-secondary studio-secondary-action" href="/">Volver</a>
                </div>
            </form>
        </section>
    </div>
</div>
