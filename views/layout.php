<?php

$pageTitle = isset($title) && is_string($title) && $title !== '' ? $title : 'Narrador Studio';
$applicationName = isset($appName) && is_string($appName) && $appName !== '' ? $appName : 'Narrador Studio';
$versionLabel = isset($appVersion) && is_string($appVersion) && $appVersion !== '' ? $appVersion : 'v0.1';
$navigationContext = isset($navigationContext) && is_string($navigationContext) && $navigationContext !== ''
    ? $navigationContext
    : 'Inicio';
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="/assets/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/vendor/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
    <div class="app-shell d-flex flex-column min-vh-100">
        <header class="studio-header border-bottom">
            <div class="container py-3 py-md-4">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <a class="brand-home-link d-flex align-items-center gap-3" href="/" aria-label="Volver al Dashboard de Narrador Studio">
                        <span class="brand-mark" aria-hidden="true"></span>
                        <span>
                            <span class="brand-name mb-0 d-block"><?= htmlspecialchars($applicationName, ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="brand-subtitle mb-0 d-block">Producción organizada de narraciones</span>
                        </span>
                    </a>
                    <div class="d-flex align-items-center gap-2">
                        <span class="version-pill"><?= htmlspecialchars($versionLabel, ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="global-actions-slot d-none d-sm-inline-flex" aria-label="Espacio reservado para futuras acciones globales"></span>
                    </div>
                </div>
                <div class="navigation-context mt-3" aria-label="Ubicación actual">
                    <?= htmlspecialchars($navigationContext, ENT_QUOTES, 'UTF-8') ?>
                </div>
            </div>
        </header>

        <main class="studio-main flex-grow-1 py-4 py-lg-5">
            <?= $content ?>
        </main>

        <footer class="studio-footer border-top mt-auto">
            <div class="container py-3">
                <div class="d-flex flex-column flex-sm-row justify-content-between gap-2 small">
                    <span>&copy; <?= date('Y') ?> Narrador Studio</span>
                    <span><?= htmlspecialchars($versionLabel, ENT_QUOTES, 'UTF-8') ?> · Creado con amor por Regline</span>
                </div>
            </div>
        </footer>
    </div>

    <script src="/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/app.js"></script>
</body>
</html>
