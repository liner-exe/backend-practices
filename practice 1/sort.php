<?php

include_once ('sort_lib.php');

$error = null;

if (!isset($_GET['array']) || trim($_GET['array']) === '') {
    $error = "Укажите массив чисел в параметре array (например, array=4,3,2,1)";
} else {
    $array = $_GET['array'];

    $parsedArray = parseArray($array);

    $sorted = insertionSort($parsedArray);
}

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Сортировка вставками</title>
</head>
<body style="margin: 0;">
    <div style="display: flex; flex-direction: column; justify-content: center; align-items: center; height: 100dvh; background: #2b2b2b">
        <?php if ($error): ?>
            <div style="background: #da4c4c; padding: 10px; color: #fff; border-radius: 8px;">
                <strong>Ошибка:</strong> <?= $error ?>
            </div>
        <?php else: ?>
            <div>
                <h2 style="color: #cd6d6d;">Исходный массив</h2>
                <div style="display: flex; justify-content: center; gap: 4px;">
                    <?php foreach ($parsedArray as $num): ?>
                        <span style="background: aqua; padding: 4px;">
                            <?= $num ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>

            <div>
                <h2 style="color: #cd6d6d;">Отсортированный массив</h2>
                <div style="display: flex; justify-content: center; gap: 4px;">
                    <?php foreach ($sorted as $num): ?>
                        <span style="background: aqua; padding: 4px;">
                            <?= $num ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>

        <?php endif; ?>
    </div>
</body>
</html>