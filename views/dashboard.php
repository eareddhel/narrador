<?php

$heading = isset($pageHeading) && is_string($pageHeading) && $pageHeading !== '' ? $pageHeading : 'Narrador Studio';
$message = isset($emptyStateMessage) && is_string($emptyStateMessage) && $emptyStateMessage !== ''
    ? $emptyStateMessage
    : 'Aún no tienes proyectos.';
$version = isset($appVersion) && is_string($appVersion) && $appVersion !== '' ? $appVersion : null;
?>
<section aria-labelledby="dashboard-title">
    <h1 id="dashboard-title"><?= htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') ?></h1>
    <p>Dashboard operativo</p>
</section>

<section aria-labelledby="empty-projects-title">
    <h2 id="empty-projects-title">Estado de proyectos</h2>
    <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
    <p>Crea tu primer proyecto para organizar guiones y generar narraciones.</p>
</section>

<?php if ($version !== null) : ?>
    <section aria-labelledby="app-version-title">
        <h2 id="app-version-title">Versión</h2>
        <p><?= htmlspecialchars($version, ENT_QUOTES, 'UTF-8') ?></p>
    </section>
<?php endif; ?>
