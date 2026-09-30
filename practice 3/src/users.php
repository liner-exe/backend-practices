<?php

session_start();

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

        $res = @file_get_contents($api_url . '?id=' . $id, false, $context);
        if ($res !== false) {
            $_SESSION['flash'] = json_decode($res, true);
        }

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

        $res = @file_get_contents($api_url, false, $context);
        if ($res !== false) {
            $_SESSION['flash'] = json_decode($res, true);
        }
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

        $res = @file_get_contents($api_url . '?id=' . $id, false, $context);
        if ($res !== false) {
            $_SESSION['flash'] = json_decode($res, true);
        }
    }

    header("Location: users.php");
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


    </style>
    <script>
        function handleFormSubmit(e) {
            const isCancel = e.submitter && e.submitter.classList.contains('cancel-button');

            if (isCancel) {
                return confirm('Отменить редактирование?');
            }

            const isEdit = <?= $edit_user ? 'true' : 'false' ?>;
            return confirm(isEdit ? 'Сохранить изменения?' : 'Создать пользователя?');
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>Управление пользователями</h1>

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
        </div>

        <div class="card" style="margin-top: 12px;">
            <h2><?= $edit_user ? "Обновить пользователя" : "Создать пользователя" ?></h2>

            <form class="create-form" method="POST" action="users.php" onsubmit="return handleFormSubmit(event)">
                <div class="fields">
                    <div>
                        <label for="name">Имя</label>
                        <input type="text" name="name" id="name" value="<?= htmlspecialchars($edit_user['name'] ?? '') ?>">
                    </div>
                    <div>
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" value="<?= htmlspecialchars($edit_user['email'] ?? '') ?>">
                    </div>
                </div>

                <input type="hidden" name="action" value="<?= $edit_user ? 'update' : 'create' ?>">

                <?php if ($edit_user): ?>
                    <input type="hidden" name="id" value="<?= htmlspecialchars((string)$edit_user['id']) ?>">
                <?php endif; ?>

                <div class="create-form-buttons">
                    <button type="submit">
                        <?= $edit_user ? 'Сохранить' : 'Создать' ?>
                    </button>

                    <?php if ($edit_user): ?>
                        <button type="submit"
                                formmethod="GET"
                                formaction="users.php"
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
