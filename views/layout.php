<?php

$pageTitle = isset($title) && is_string($title) && $title !== '' ? $title : 'Narrador Studio';
$applicationName = isset($appName) && is_string($appName) && $appName !== '' ? $appName : 'Narrador Studio';
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
</head>
<body>
    <header>
        <p><?= htmlspecialchars($applicationName, ENT_QUOTES, 'UTF-8') ?></p>
    </header>

    <main>
        <?= $content ?>
    </main>

    <footer>
        <p>Narrador Studio</p>
    </footer>
</body>
</html>
