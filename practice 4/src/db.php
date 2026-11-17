<?php

function getDB(): PDO
{
    $host = getenv('MYSQL_DB_HOST') ?: 'db';
    $db   = getenv('MYSQL_DATABASE') ?: 'appDB';
    $user = getenv('MYSQL_USER') ?: 'user';
    $pass = getenv('MYSQL_PASSWORD') ?: 'password';

    return new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
}