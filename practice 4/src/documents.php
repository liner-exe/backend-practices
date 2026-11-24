<?php

require_once __DIR__ . '/preferences.php';

$prefs        = getUserPreferences();
$currentLogin = $prefs['login'];
$currentTheme = $prefs['theme'];
$currentLang  = $prefs['lang'];
$content = $prefs['t']['documents'];

$pdo = getDB();
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$statusMsg = '';
$isError = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdf_file'])) {
    $file = $_FILES['pdf_file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $statusMsg = 'Ошибка при передаче файла на сервер.';
        $isError = true;
    } else {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);

        if ($mime !== 'application/pdf') {
            $statusMsg = 'Разрешена загрузка только файлов формата PDF!';
            $isError = true;
        } else {
            $originalName = basename($file['name']);
            $storedName = uniqid('doc_', true) . '.pdf';
            $destination = $uploadDir . $storedName;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $author = $currentLogin !== '' ? $currentLogin : 'Гость';
                $stmt = $pdo->prepare(
                    'INSERT INTO pdf_files (original_name, stored_name, uploaded_by) VALUES (:orig, :stored, :author)'
                );
                $stmt->execute([
                    'orig'   => $originalName,
                    'stored' => $storedName,
                    'author' => $author,
                ]);
                $statusMsg = "Файл «{$originalName}» успешно сохранён на сервере.";
            } else {
                $statusMsg = 'Не удалось сохранить файл в директорию uploads.';
                $isError = true;
            }
        }
    }

    header("Location: documents.php");
}

$files = $pdo->query('SELECT * FROM pdf_files ORDER BY id DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($currentLang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($content['title']) ?></title>
    <link rel="stylesheet" href="/style.css">
</head>
<body class="<?= themeClass($prefs['theme']) ?>">
<div class="container">
    <?= renderHeaderBar() ?>

    <div class="card">
        <h1><?= htmlspecialchars($content['title']) ?></h1>

        <?php if ($statusMsg !== ''): ?>
            <div class="alert <?= $isError ? 'alert-err' : 'alert-ok' ?>">
                <?= htmlspecialchars($statusMsg) ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="upload-form">
            <label for="pdf-file"><?= htmlspecialchars($content['upload_label']) ?></label>
            <input id="pdf-file" type="file" name="pdf_file" accept="application/pdf" required>
            <button type="submit"><?= htmlspecialchars($content['upload_button']) ?></button>
        </form>
    </div>

    <div class="card">
        <?php if (empty($files)): ?>
            <p class="empty-state"><?= htmlspecialchars($content['no_files']) ?></p>
        <?php else: ?>
            <div class="table-scroll">
                <table>
                    <thead>
                    <tr>
                        <th><?= htmlspecialchars($content['table_id']) ?></th>
                        <th><?= htmlspecialchars($content['table_name']) ?></th>
                        <th><?= htmlspecialchars($content['table_author']) ?></th>
                        <th><?= htmlspecialchars($content['table_date']) ?></th>
                        <th><?= htmlspecialchars($content['table_action']) ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($files as $f): ?>
                        <tr>
                            <td><?= (int)$f['id'] ?></td>
                            <td><?= htmlspecialchars($f['original_name']) ?></td>
                            <td><?= htmlspecialchars($f['uploaded_by']) ?></td>
                            <td><?= htmlspecialchars($f['uploaded_at']) ?></td>
                            <td>
                                <a href="/download_pdf.php?id=<?= (int)$f['id'] ?>" target="_blank">
                                    <?= htmlspecialchars($content['download_button']) ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>