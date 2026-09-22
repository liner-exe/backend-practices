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
    if ($id !== null) {
        $query = $db->prepare("SELECT * FROM users WHERE id = ?");
        $query->execute([$id]);
        $user = $query->fetch();

        if ($user) {
            sendResponse(200, "Пользователь найден", $user);
        } else {
            sendResponse(404, "Пользователь не найден");
        }
    } else {
        $query = $db->query("SELECT * FROM users");
        sendResponse(200, "Список пользователей", $query->fetchAll());
    }
}

function handlePost(PDO $db): void
{
    $body = json_decode(file_get_contents("php://input"), true);

    if (empty($body['name']) || empty($body['email'])) {
        sendResponse(400, "Поля name и email обязательны для заполнения");
        return;
    }

    try {
        $query = $db->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
        $query->execute([$body['name'], $body['email']]);

        sendResponse(200, "Пользователь создан", [
            "id" => (int)$db->lastInsertId()
        ]);

    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            sendResponse(409, "Пользователь с таким email уже существует");
            return;
        }

        sendResponse(500, "Ошибка при создании пользователя");
    }
}

function handlePut(PDO $db, ?int $id): void
{
    if ($id === null) {
        sendResponse(400, "id обязателен для указания");
        return;
    }

    $body = json_decode(file_get_contents("php://input"), true);

    if (empty($body['name']) || empty($body['email'])) {
        sendResponse(400, "Поля name и email обязательны для заполнения");
        return;
    }

    $isExist = $db->prepare("SELECT 1 FROM users WHERE id = ?");
    $isExist->execute([$id]);
    if (!$isExist->fetch()) {
        sendResponse(404, "Пользователь с id = {$id} не найден");
        return;
    }

    try {
        $query = $db->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $query->execute([$body['name'], $body['email'], $id]);

        sendResponse(200, "Пользователь обновлен");
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            sendResponse(409, "Email уже занят другим пользователем");
            return;
        }

        sendResponse(500, "Ошибка при редактировании пользователя");
    }
}

function handleDelete(PDO $db, ?int $id): void
{
    if ($id !== null) {
        $query = $db->prepare("DELETE FROM users WHERE id = ?");
        $query->execute([$id]);

        if ($query->rowCount() === 0) {
            sendResponse(404, "Пользователь не найден");
            return;
        }

        sendResponse(204, "Пользователь удален");
    } else {
        sendResponse(400, "id обязателен для указания");
    }
}