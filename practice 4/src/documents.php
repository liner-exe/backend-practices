<?php

require_once __DIR__ . '/preferences.php';

$prefs        = getUserPreferences();
$currentLogin = $prefs['login'];
$currentTheme = $prefs['theme'];
$currentLang  = $prefs['lang'];
$t            = $prefs['t'];

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
}

$files = $pdo->query('SELECT * FROM pdf_files ORDER BY id DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($currentLang) ?>">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($t['docs_title']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f8fafc; font-family: 'Montserrat', sans-serif; color: #1e293b; margin: 0; padding: 30px 15px; }
        .container { max-width: 960px; margin: auto; }
        .card { background: #ffffff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); padding: 24px; border-radius: 10px; margin-bottom: 20px; }
        h1 { font-size: 22px; margin-top: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 14px; }
        th, td { padding: 10px 12px; border-bottom: 1px solid #e2e8f0; text-align: left; }
        th { background-color: #f1f5f9; font-weight: 600; }
        .alert { padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; font-size: 14px; font-weight: 600; }
        .alert-ok { background: #dcfce7; color: #166534; }
        .alert-err { background: #fee2e2; color: #991b1b; }
        .btn-primary { background: #0284c7; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-family: inherit; font-weight: 600; }
    </style>
    <?= renderThemeStyles() ?>
</head>
<body>
<div class="container">
    <?= renderHeaderBar() ?>

    <div class="card">
        <h1><?= htmlspecialchars($t['docs_title']) ?></h1>

        <?php if ($statusMsg !== ''): ?>
            <div class="alert <?= $isError ? 'alert-err' : 'alert-ok' ?>">
                <?= htmlspecialchars($statusMsg) ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" style="display:flex; flex-wrap:wrap; gap:12px; align-items:center;">
            <label style="font-size:14px; font-weight:600;"><?= htmlspecialchars($t['upload_label']) ?></label>
            <input type="file" name="pdf_file" accept="application/pdf" required>
            <button type="submit" class="btn-primary"><?= htmlspecialchars($t['upload_btn']) ?></button>
        </form>
    </div>

    <div class="card">
        <?php if (empty($files)): ?>
            <p><?= htmlspecialchars($t['no_files']) ?></p>
        <?php else: ?>
            <table>
                <thead>
                <tr>
                    <th><?= htmlspecialchars($t['table_id']) ?></th>
                    <th><?= htmlspecialchars($t['table_name']) ?></th>
                    <th><?= htmlspecialchars($t['table_author']) ?></th>
                    <th><?= htmlspecialchars($t['table_date']) ?></th>
                    <th><?= htmlspecialchars($t['table_action']) ?></th>
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
                                <?= htmlspecialchars($t['download_btn']) ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
</body>
</html>