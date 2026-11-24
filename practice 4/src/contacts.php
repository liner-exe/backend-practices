<?php

require_once __DIR__ . '/preferences.php';

$prefs = getUserPreferences();
$content = $prefs['t']['contacts'];
$contacts = [
    'phone' => '8 (800) 555-35-35',
    'email' => 'support@marketplace.local',
    'hours' => $content['hours_value'],
    'office' => $content['address'],
];
$details = [
    'company' => $content['company_name'],
    'registration' => '1234567890123',
    'tax' => '7701234567 / 770101001',
    'bank' => $content['bank_name'],
    'account' => '40702810900000001234',
    'correspondent' => '30101810400000000225',
    'bank_code' => '044525225',
];
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

        <div class="info-grid">
            <?php foreach ($contacts as $key => $value): ?>
                <div class="info-item">
                    <div class="label"><?= htmlspecialchars($content[$key]) ?></div>
                    <div class="value"><?= htmlspecialchars($value) ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <h2><?= htmlspecialchars($content['details_heading']) ?></h2>
        <table class="requisites">
            <tbody>
                <?php foreach ($details as $key => $value): ?>
                    <tr>
                        <td><?= htmlspecialchars($content[$key]) ?>:</td>
                        <td><?= htmlspecialchars($value) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</div>
</body>
</html>
