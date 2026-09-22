<?php

header("Content-Type: application/json; charset=UTF-8");

require_once ("../db.php");
require_once ("../helpers.php");

$pdo = getDB();

$method = $_SERVER['REQUEST_METHOD'];

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

match ($method) {
    'GET' => handleGet($pdo, $id),
    'POST' => handlePost($pdo),
    'PUT' => handlePut($pdo, $id),
    'DELETE' => handleDelete($pdo, $id),
    default => sendResponse(405, "Method not allowed")
};

function handleGet(PDO $db, ?int $id): void
{
    $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;

    if ($id !== null) {
        $query = $db->prepare("
            SELECT r.id, r.user_id, u.name AS author_name, r.rating, r.comment
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            WHERE r.id = ?
        ");
        $query->execute([$id]);
        $review = $query->fetch();

        if ($review) {
            sendResponse(200, "Отзыв найден", $review);
        } else {
            sendResponse(404, "Отзыв не найден");
        }
        return;
    }

    if ($userId !== null) {
        $query = $db->prepare("
            SELECT r.id, r.user_id, u.name AS author_name, r.rating, r.comment
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            WHERE r.user_id = ?
        ");
        $query->execute([$userId]);
        sendResponse(200, "Отзывы пользователя", $query->fetchAll());
    }

    $query = $db->query("
        SELECT r.id, r.user_id, u.name AS author_name, r.rating, r.comment
        FROM reviews r
        JOIN users u ON r.user_id = u.id
        ORDER BY r.id
    ");

    sendResponse(200, "Список отзывов", $query->fetchAll());
}

function handlePost(PDO $db): void
{
    $body = json_decode(file_get_contents("php://input"), true);

    $userId = $body['user_id'] ?? null;
    $rating = $body['rating'] ?? null;
    $comment = trim($body['comment'] ?? '');

    if ($userId === null || $rating === null || $comment === '') {
        sendResponse(400, "Поля user_id, rating, comment обязательны");
        return;
    }

    $rating = (int)$rating;
    if ($rating < 1 || $rating > 5) {
        sendResponse(400, "Рейтинг должен быть целым числом от 1 до 5");
        return;
    }

    try {
        $query = $db->prepare("INSERT INTO reviews (user_id, rating, comment) VALUES (?, ?, ?)");
        $query->execute([$userId, $rating, $comment]);

        sendResponse(201, "Отзыв успешно добавлен", [
            "id" => (int)$db->lastInsertId()
        ]);
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            sendResponse(409, "Пользователь с указанным user_id уже существует");
            return;
        }

        sendResponse(500, "Ошибка при сохранении отзыва");
    }
}

function handlePut(PDO $db, ?int $id): void
{
    if ($id === null) {
        sendResponse(400, "Не указан id отзыва");
        return;
    }

    $body = json_decode(file_get_contents("php://input"), true);

    $fields = [];
    $params = [];

    if (isset($body['rating'])) {
        $rating = (int)$body['rating'];
        if ($rating < 1 || $rating > 5) {
            sendResponse(400, "Рейтинг должен быть от 1 до 5");
            return;
        }
        $fields[] = "rating = ?";
        $params[] = $rating;
    }

    if (isset($body['comment'])) {
        $comment = trim($body['comment']);
        if ($comment === '') {
            sendResponse(400, "Комментарий не может быть пустым");
            return;
        }
        $fields[] = "comment = ?";
        $params[] = $comment;
    }

    if (empty($fields)) {
        sendResponse(400, "Не передано ни одного поля для обновления (rating или comment)");
        return;
    }

    $isExists = $db->prepare("SELECT id FROM reviews WHERE id = ?");
    $isExists->execute([$id]);
    if (!$isExists->fetch()) {
        sendResponse(404, "Отзыв не найден");
        return;
    }

    $params[] = $id;
    $sql = "UPDATE reviews SET " . implode(", ", $fields) . " WHERE id = ?";
    $query = $db->prepare($sql);
    $query->execute($params);

    sendResponse(200, "Отзыв успешно обновлен");
}

function handleDelete(PDO $db, ?int $id): void
{
    if ($id === null) {
        sendResponse(400, "Не указан id отзыва");
        return;
    }

    $query = $db->prepare("DELETE FROM reviews WHERE id = ?");
    $query->execute([$id]);

    if ($query->rowCount() === 0) {
        sendResponse(404, "Отзыв не найден");
    } else {
        sendResponse(200, "Отзыв удален");
    }
}