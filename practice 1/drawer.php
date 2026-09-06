<?php

require_once ("drawer_lib.php");

$error = null;

if (!isset($_GET['num'])) {
    $error = "Ключ num не определен.";
} else {
    $code = (int)$_GET['num'];

    $shape = buildShape((int)$_GET['num']);
}
?>


<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Рисование фигур</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body style="margin: 0; font-family: 'Montserrat', sans-serif">
    <div style="display: flex; justify-content: center; align-items: center; height: 100dvh; background: #2b2b2b">
        <?php if ($error): ?>
            <div style="background: #e04b4b; padding: 16px; border-radius: 8px;">
                <h1 style="color: #fff; margin: 0 0 16px;">Ошибка</h1>
                <p style="color: rgba(255, 255, 255, 0.9); margin: 0;">
                    <?= $error ?>
                </p>
            </div>
        <?php else: ?>
            <?= $shape ?>
        <?php endif; ?>
    </div>
</body>
</html>
