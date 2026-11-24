<?php

require_once "users_lib.php";
require_once __DIR__ . '/preferences.php';
require_once __DIR__ . '/users_lib.php';

$prefs = getUserPreferences();
$currentLang = $prefs['lang'];

$content = $prefs['t']['reviews'];
$dialogs = $prefs['t']['dialogs'];

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

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($currentLang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $prefs['t']['nav']['reviews'] ?></title>

    <link rel="stylesheet" href="/style.css">
    <script src="/confirm.js" defer></script>
</head>
<body class="<?= themeClass($prefs['theme']) ?>">
<div class="container">
    <?= renderHeaderBar() ?>

    <div class="card">
        <h1><?= $content['management'] ?></h1>

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
                    <th><?= $content['table_user_id'] ?></th>
                    <th><?= $content['table_name'] ?></th>
                    <th><?= $content['table_rating'] ?></th>
                    <th><?= $content['table_review'] ?></th>
                    <th><?= $content['table_management'] ?></th>
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
                            <div class="row-actions">
                                <form method="POST" action="reviews.php" data-confirm="<?= htmlspecialchars($dialogs['delete_review']) ?>"
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
    </div>

    <div class="card">
        <h2><?= $edit_review ? $content['update'] : $content['create'] ?></h2>

        <form class="create-form" method="POST" action="reviews.php" data-confirm="<?= htmlspecialchars($dialogs[$edit_review ? 'save_changes' : 'create_review']) ?>">
            <div class="fields review-fields">
                <div class="field">
                    <?php if ($edit_review !== null): ?>
                        <label for="id"><?= $content['form_review_id'] ?></label>
                        <input type="text" name="id" id="id" value="<?= htmlspecialchars($edit_review['id'] ?? '') ?>">
                    <?php else: ?>
                        <label for="user_id"><?= $content['form_user_id'] ?></label>
                        <input type="text" name="user_id" id="user_id" value="<?= htmlspecialchars($edit_review['user_id'] ?? '') ?>">
                    <?php endif; ?>
                </div>

                <div class="field">
                    <span><?= $content['form_rating'] ?></span>
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
                    <label for="comment"><?= $content['form_text'] ?></label>
                    <input type="text" name="comment" id="comment" value="<?= htmlspecialchars($edit_review['comment'] ?? '') ?>">
                </div>
            </div>

            <input type="hidden" name="action" value="<?= $edit_review ? 'update' : 'create' ?>">

            <?php if ($edit_review): ?>
                <input type="hidden" name="id" value="<?= htmlspecialchars((string)$edit_review['id']) ?>">
            <?php endif; ?>

            <div class="create-form-buttons">
                <button type="submit">
                    <?= $edit_review ? $content['save_button'] : $content['create_button'] ?>
                </button>

                <?php if ($edit_review): ?>
                    <button type="submit"
                            formmethod="GET"
                            formaction="reviews.php"
                            class="cancel-button"
                            data-confirm="<?= htmlspecialchars($dialogs['cancel_edit']) ?>"
                            formnovalidate
                    >
                        <?= $content['cancel_button'] ?>
                    </button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>
</body>
</html>
