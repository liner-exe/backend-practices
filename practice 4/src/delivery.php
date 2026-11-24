<?php

require_once __DIR__ . '/preferences.php';

$prefs = getUserPreferences();
$content = $prefs['t']['delivery'];
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($prefs['lang']) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($content['title']) ?></title>
    <link rel="stylesheet" href="/style.css">
</head>
<body class="info-page <?= themeClass($prefs['theme']) ?>">
<div class="container">
    <?= renderHeaderBar() ?>

    <main class="card">
        <h1><?= htmlspecialchars($content['heading']) ?></h1>
        <p><?= htmlspecialchars($content['intro']) ?></p>

        <h2><?= htmlspecialchars($content['methods_heading']) ?></h2>
        <ul>
            <?php foreach ($content['methods'] as [$method, $description]): ?>
                <li><strong><?= htmlspecialchars($method) ?>:</strong> <?= htmlspecialchars($description) ?></li>
            <?php endforeach; ?>
        </ul>

        <h2><?= htmlspecialchars($content['rates_heading']) ?></h2>
        <ul>
            <?php foreach ($content['rates'] as $rate): ?>
                <li><?= htmlspecialchars($rate) ?></li>
            <?php endforeach; ?>
        </ul>

        <h2><?= htmlspecialchars($content['returns_heading']) ?></h2>
        <p><?= htmlspecialchars($content['returns']) ?></p>
    </main>
</div>
</body>
</html>
