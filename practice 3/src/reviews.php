<?php

session_start();

require_once "users_lib.php";

$api_url = 'http://localhost/api/reviews.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int)($_POST['id'] ?? 0);

    if ($id > 0) {
        $context = stream_context_create([
            'http' => [
                'method' => 'DELETE',
                'ignore_errors' => true
            ]
        ]);

        $res = @file_get_contents($api_url . '?id=' . $id, false, $context);
        if ($res !== false) {
            $_SESSION['flash'] = json_decode($res, true);
        }

        header("Location: reviews.php");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $user_id = trim($_POST['user_id'] ?? '');
    $rating = trim($_POST['rating'] ?? '');
    $comment = trim($_POST['comment'] ?? '');

    if ($user_id !== '' && $rating !== '' && $comment !== '') {
        $payload = json_encode([
            'user_id' => $user_id,
            'rating' => $rating,
            'comment' => $comment
        ]);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => $payload,
                'ignore_errors' => true
            ]
        ]);

        $res = @file_get_contents($api_url, false, $context);
        if ($res !== false) {
            $_SESSION['flash'] = json_decode($res, true);
        }
    }

    header("Location: reviews.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
    $id = (int)($_POST['id'] ?? 0);
    $rating = trim($_POST['rating'] ?? '');
    $comment = trim($_POST['comment'] ?? '');

    if ($id > 0 && $rating !== '' && $comment !== '') {
        $payload = json_encode([
            'rating' => $rating,
            'comment' => $comment
        ]);

        $context = stream_context_create([
            'http' => [
                'method' => 'PUT',
                'header' => "Content-Type: application/json\r\n",
                'content' => $payload,
                'ignore_errors' => true
            ]
        ]);

        $res = @file_get_contents($api_url . '?id=' . $id, false, $context);
        if ($res !== false) {
            $_SESSION['flash'] = json_decode($res, true);
        }
    }

    header("Location: reviews.php");
    exit;
}

$response = @file_get_contents($api_url);

$apiData = [];
if ($response !== false) {
    $apiData = json_decode($response, true) ?: [];
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$code = $flash['code'] ?? $apiData['code'] ?? null;
$message = $flash['message'] ?? $apiData['message'] ?? '';
$reviews = $apiData['data'] ?? [];

$edit_id = isset($_GET['edit_id']) ? (int)($_GET['edit_id']) : null;
$edit_review = null;

foreach ($reviews as $review) {
    if ((int)$review['id'] === $edit_id) {
        $edit_review = $review;
        break;
    }
}

?>

<html lang="ru">
<head>
    <title>Отзывы</title>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            background-color: #f8fafc;
            font-family: 'Montserrat', sans-serif;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            padding-top: 30px;
        }

        table {
            width: 100%;
            justify-self: center;
            border-collapse: collapse;
            border-radius: 12px;
        }

        th {
            background: bisque;
        }

        th, td {
            text-align: left;
            padding: 8px 12px;
            border: 1px solid black;
        }

        .card {
            background: #ffffff;
            box-shadow: 0 5px 3px rgba(0, 0, 0, 0.1);
            padding: 20px;
            border-radius: 10px;
        }

        .status-banner {
            background: #e8e8e8;
            margin-bottom: 10px;
            padding: 10px 14px;
            border-radius: 4px;
        }

        .status-banner.success {
            background-color: #92da8b;
        }

        .status-banner.error {
            background-color: #e47d7d;
        }

        .status-badge {
            font-weight: 700;
            background: rgba(0, 0, 0, 0.06);
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 12px;
        }

        button.delete {
            width: 32px;
            height: 32px;
            background-color: #fa5a5a;
            color: #fff;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }

        button.edit {
            width: 32px;
            height: 32px;
            background-color: #e3e3e3;
            color: #474747;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }

        form.delete-form {
            display: flex;
            align-items: center;
            margin: 0;
        }

        form.edit-form {
            display: flex;
            flex-direction: row;
            margin: 0;
        }

        form.create-form {
            display: flex;
            flex-direction: column;
        }

        .fields {
            display: flex;
            gap: 16px;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 4px;
            width: 200px;
        }

        .fields input {
            background-color: #e3e3e3;
            border-radius: 4px;
            border: 1px #aeaeae solid;
            padding: 4px;
        }

        .fields input:focus {
            outline: 1px #92da8b solid;
        }

        .create-form-buttons {
            margin-top: 14px;
            display: flex;
            gap: 8px;
        }

        .create-form-buttons button {
            background-color: #e3e3e3;
            border: none;
            padding: 8px;
            border-radius: 4px;
        }

        input[type="radio"] {
            transform: scale(1.2);
            transition: accent-color 1s ease-in-out;
            color
        }

        input[type="radio"]:checked {
            transform: scale(1.3);
            accent-color: #3e633a;
            background-color: #474747;
        }

        input[type="radio"]:focus {
            outline-color: transparent;
        }
    </style>
    <script>
        function handleFormSubmit(e) {
            const isCancel = e.submitter && e.submitter.classList.contains('cancel-button');

            if (isCancel) {
                return confirm('Отменить редактирование?');
            }

            const isEdit = <?= $edit_review ? 'true' : 'false' ?>
            return confirm(isEdit ? 'Сохранить изменения?' : 'Создать пользователя?');
       }
    </script>
</head>
<body>
<div class="container">
    <div class="card">
        <h1>Управление отзывами</h1>

        <?php if ($code !== null): ?>
            <?php $isError = ($code >= 400); ?>
            <div class="status-banner <?= $isError ? 'error' : 'success' ?>">
                <span class="status-badge"><?= (int)$code ?> <?= htmlspecialchars(codeToMessage($code)) ?></span>
                <span><?= htmlspecialchars($message) ?></span>
            </div>
        <?php endif; ?>

        <table>
            <thead>
            <tr>
                <th>id</th>
                <th>id пользователя</th>
                <th>Имя</th>
                <th>Оценка</th>
                <th>Отзыв</th>
                <th>Управление</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($reviews as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r['id']) ?></td>
                    <td><?= htmlspecialchars($r['user_id']) ?></td>
                    <td><?= htmlspecialchars($r['author_name']) ?></td>
                    <td><?= htmlspecialchars($r['rating']) ?></td>
                    <td><?= htmlspecialchars($r['comment']) ?></td>
                    <td>
                        <div style="display: flex; gap: 8px;">
                            <form method="POST" action="reviews.php" onsubmit="return confirm('Удалить отзыв?')"
                                  class="delete-form">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= htmlspecialchars((string)($r['id'] ?? '')) ?>" required>
                                <button type="submit" class="delete">X</button>
                            </form>
                            <form method="GET" action="reviews.php" class="edit-form">
                                <input type="hidden" name="edit_id" value="<?= htmlspecialchars((string)($r['id'] ?? '')) ?>">
                                <button type="submit" class="edit">✎</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card" style="margin-top: 12px;">
        <h2><?= $edit_review ? "Обновить отзыв" : "Создать отзыв" ?></h2>

        <form class="create-form" method="POST" action="reviews.php" onsubmit="return handleFormSubmit(event)">
            <div class="fields" style="display: flex; flex-direction: column;">
                <div class="field">
                    <?php if ($edit_review !== null): ?>
                        <label for="id">Id отзыва</label>
                        <input type="text" name="id" id="id" value="<?= htmlspecialchars($edit_review['id'] ?? '') ?>">
                    <?php else: ?>
                        <label for="user_id">Id пользователя</label>
                        <input type="text" name="user_id" id="user_id" value="<?= htmlspecialchars($edit_review['user_id'] ?? '') ?>">
                    <?php endif; ?>
                </div>

                <div class="field">
                    <span>Оценка</span>
                    <div>
                        <input class="radio-item" type="radio" name="rating" id="ratingChoice1" value="1" <?= htmlspecialchars($edit_review ? ((int)$edit_review['rating'] === 1 ? 'checked' : '') : '') ?>>
                        <label for="ratingChoice1">1</label>
                        <input class="radio-item" type="radio" name="rating" id="ratingChoice2" value="2" <?= htmlspecialchars($edit_review ? ((int)$edit_review['rating'] === 2 ? 'checked' : '') : '') ?>>
                        <label for="ratingChoice2">2</label>
                        <input class="radio-item" type="radio" name="rating" id="ratingChoice3" value="3" <?= htmlspecialchars($edit_review ? ((int)$edit_review['rating'] === 3 ? 'checked' : '') : '') ?>>
                        <label for="ratingChoice3">3</label>
                        <input class="radio-item" type="radio" name="rating" id="ratingChoice4" value="4" <?= htmlspecialchars($edit_review ? ((int)$edit_review['rating'] === 4 ? 'checked' : '') : '') ?>>
                        <label for="ratingChoice4">4</label>
                        <input class="radio-item" type="radio" name="rating" id="ratingChoice5" value="5" <?= htmlspecialchars($edit_review ? ((int)$edit_review['rating'] === 5 ? 'checked' : '') : '') ?>>
                        <label for="ratingChoice5">5</label>
                    </div>
                </div>

                <div class="field">
                    <label for="comment">Текст отзыва</label>
                    <input type="text" name="comment" id="comment" value="<?= htmlspecialchars($edit_review['comment'] ?? '') ?>">
                </div>
            </div>

            <input type="hidden" name="action" value="<?= $edit_review ? 'update' : 'create' ?>">

            <?php if ($edit_review): ?>
                <input type="hidden" name="id" value="<?= htmlspecialchars((string)$edit_review['id']) ?>">
            <?php endif; ?>

            <div class="create-form-buttons">
                <button type="submit">
                    <?= $edit_review ? 'Сохранить' : 'Создать' ?>
                </button>

                <?php if ($edit_review): ?>
                    <button type="submit"
                            formmethod="GET"
                            formaction="reviews.php"
                            class="cancel-button"
                            formnovalidate
                    >
                        Отмена
                    </button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>
</body>
</html>
