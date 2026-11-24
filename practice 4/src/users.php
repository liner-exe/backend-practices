<?php

require_once "users_lib.php";
require_once __DIR__ . '/preferences.php';
require_once __DIR__ . '/users_lib.php';

$prefs = getUserPreferences();
$currentLang = $prefs['lang'];

$t = $prefs['t'];

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

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($currentLang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Пользователи</title>

    <link rel="stylesheet" href="/style.css">
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
<body class="<?= themeClass($prefs['theme']) ?>">
    <div class="container">
        <?= renderHeaderBar() ?>
        <div class="card">
            <h1><?= $t['users_management'] ?></h1>

            <?php if ($code !== null): ?>
                <?php $isError = ($code >= 400); ?>
                <div class="status-banner <?= $isError ? 'error' : 'success' ?>">
                    <span class="status-badge"><?= (int)$code ?> <?= htmlspecialchars(codeToMessage($code)) ?></span>
                    <span><?= htmlspecialchars($message) ?></span>
                </div>
            <?php endif; ?>

            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th><?= $t['users_table_name'] ?></th>
                            <th>Email</th>
                            <th><?= $t['users_table_management'] ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?= htmlspecialchars($u['id']) ?></td>
                                <td><?= htmlspecialchars($u['name']) ?></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td>
                                    <div class="row-actions">
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
        </div>

        <div class="card">
            <h2><?= $edit_user ? $t['users_update'] : $t['users_create'] ?></h2>

            <form class="create-form" method="POST" action="users.php" onsubmit="return handleFormSubmit(event)">
                <div class="fields">
                    <div class="field">
                        <label for="name"><?= $t['users_table_name'] ?></label>
                        <input type="text" name="name" id="name" value="<?= htmlspecialchars($edit_user['name'] ?? '') ?>">
                    </div>
                    <div class="field">
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
                        <?= $edit_user ? $t['users_save_btn'] : $t['users_create_btn'] ?>
                    </button>

                    <?php if ($edit_user): ?>
                        <button type="submit"
                                formmethod="GET"
                                formaction="users.php"
                                class="cancel-button"
                                formnovalidate
                        >
                            <?= $t['users_cancel_btn'] ?>
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
