<?php

require_once "users_lib.php";

$api_url = 'http://localhost/api/users.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int)($_POST['id'] ?? 0);

    if ($id > 0) {
        $context = stream_context_create([
                'http' => [
                    'method' => 'DELETE',
                    'ignore_errors' => true
                ]
        ]);

        @file_get_contents($api_url . '?id=' . $id, false, $context);

        header("Location: users.php");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($name !== '' && $email !== '') {
        $payload = json_encode([
                'name' => $name,
                'email' => $email
        ]);

        $context = stream_context_create([
                'http' => [
                        'method' => 'POST',
                        'header' => "Content-Type: application/json\r\n",
                        'content' => $payload,
                        'ignore_errors' => true
                ]
        ]);

        @file_get_contents($api_url, false, $context);
    }

    header("Location: users.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($id > 0 && $name !== '' && $email !== '') {
        $payload = json_encode([
            'name' => $name,
            'email' => $email
        ]);

        $context = stream_context_create([
                'http' => [
                        'method' => 'PUT',
                        'header' => "Content-Type: application/json\r\n",
                        'content' => $payload,
                        'ignore_errors' => true
                ]
        ]);

        @file_get_contents($api_url . '?id=' . $id, false, $context);
    }

    header("Location: users.php");
    exit;
}

$response = @file_get_contents($api_url);

$apiData = [];
if ($response !== false) {
    $apiData = json_decode($response, true) ?: [];
}

$code = $apiData['code'] ?? null;
$message = $apiData['message'] ?? '';
$users = $apiData['data'] ?? [];

$edit_id = isset($_GET['edit_id']) ? (int)($_GET['edit_id']) : null;
$edit_user = null;

foreach ($users as $user) {
    if ((int)$user['id'] === $edit_id) {
        $edit_user = $user;
        break;
    }
}

?>

<html lang="ru">
<head>
    <title>Пользователи</title>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Montserrat', sans-serif;
        }

        .status {
            background: aqua;
        }

        .container {
            max-width: 1200px;
            margin: auto;
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

        button.delete {
            width: 32px;
            height: 32px;
            background-color: #fa5a5a;
            color: #fff;
            border: none;
            cursor: pointer;
        }

        form.delete-form {
            display: flex;
            align-items: center;
            margin: 0;
        }

        form.edit-form {
            display: flex;
            align-items: center;
            margin: 0;
        }

        .edit {
            width: 32px;
            height: 32px;
            background-color: #bcbcbc;
            color: #474747;
            border: none;
            cursor: pointer;
            line-height: 2;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Управление пользователями</h1>

        <?php if ($code !== null): ?>
            <div class="status">
                <p>Статус приложения:</p>
                <?= codeToMessage($code) ?>
                <?= $message ?>
            </div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>id</th>
                    <th>имя</th>
                    <th>email</th>
                    <th>Управление</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['id']) ?></td>
                        <td><?= htmlspecialchars($u['name']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td>
                            <div style="display: flex; gap: 8px;">
                                <form method="POST" action="users.php" onsubmit="return confirm('Удалить пользователя?')"
                                    class="delete-form">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars((string)($u['id'] ?? '')) ?>" required>
                                    <button type="submit" class="delete">X</button>
                                </form>
                                <form method="GET" action="users.php" class="edit-form">
                                    <input type="hidden" name="edit_id" value="<?= htmlspecialchars((string)($u['id'] ?? '')) ?>">
                                    <button type="submit" class="edit">✎</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2><?= $edit_user ? "Обновить пользователя" : "Создать пользователя" ?></h2>

        <form method="POST" action="users.php" onsubmit="return confirm('<?= $edit_user ? 'Сохранить изменения?' : 'Создать пользователя?' ?>')">
            <div>
                <label for="name">Имя</label>
                <input type="text" name="name" id="name" value="<?= htmlspecialchars($edit_user['name'] ?? '') ?>">
            </div>
            <div>
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($edit_user['email'] ?? '') ?>">
            </div>

            <input type="hidden" name="action" value="<?= $edit_user ? 'update' : 'create' ?>">

            <?php if ($edit_user): ?>
                <input type="hidden" name="id" value="<?= htmlspecialchars((string)$edit_user['id']) ?>">
            <?php endif; ?>

            <button type="submit">
                <?= $edit_user ? 'Сохранить' : 'Создать' ?>
            </button>

            <?php if ($edit_user): ?>
                <button type="submit" formmethod="GET" formaction="users.php" class="cancel-button">
                    Отмена
                </button>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>
