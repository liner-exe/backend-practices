<?php
require_once __DIR__ . '/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    exit('Некорректный ID документа.');
}

$pdo = getDB();
$stmt = $pdo->prepare('SELECT original_name, stored_name FROM pdf_files WHERE id = :id');
$stmt->execute(['id' => $id]);
$file = $stmt->fetch();

if (!$file) {
    http_response_code(404);
    exit('Документ не найден в базе данных.');
}

$filePath = __DIR__ . '/uploads/' . $file['stored_name'];
if (!is_file($filePath)) {
    http_response_code(404);
    exit('Файл отсутствует на диске сервера.');
}

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . rawurlencode($file['original_name']) . '"');
header('Content-Length: ' . filesize($filePath));

readfile($filePath);
exit;