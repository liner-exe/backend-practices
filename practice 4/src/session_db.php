<?php

require_once __DIR__ . '/db.php';

class DBSessionHandler implements SessionHandlerInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function close(): bool
    {
        return true;
    }

    public function destroy(string $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM sessions WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function gc(int $max_lifetime): int|false
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM sessions WHERE last_accessed < DATE_SUB(NOW(), INTERVAL :lifetime SECOND)'
        );
        $stmt->execute(['lifetime' => $max_lifetime]);
        return $stmt->rowCount();
    }

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function read(string $id): string|false
    {
        $stmt = $this->pdo->prepare('SELECT data FROM sessions WHERE id = :id');
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();

        return $row ? (string)$row['data'] : '';
    }

    public function write(string $id, string $data): bool
    {
        $stmt = $this->pdo->prepare(
            'REPLACE INTO sessions (id, data, last_accessed) VALUES (:id, :data, NOW())');

        return $stmt->execute(['id' => $id, 'data' => $data]);
    }
}

$handler = new DBSessionHandler(getDB());
session_set_save_handler($handler, true);

if (session_status() == PHP_SESSION_NONE)
{
    session_start();
}