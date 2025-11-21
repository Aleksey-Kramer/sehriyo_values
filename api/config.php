<?php

// Общая инициализация для всех API-скриптов

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Файл с константами DB_HOST, DB_NAME, DB_USER, DB_PASS
require_once __DIR__ . '/../config/db.php';

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    // Если коннект к БД упал, отдадим JSON-ошибку
    json_response([
        'success' => false,
        'error'   => 'db_connection_failed',
    ], 500);
}

/**
 * Унифицированный JSON-ответ
 */
function json_response(array $data, int $statusCode = 200)
{
    http_response_code($statusCode);

    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }

    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Короткий helper для ошибок
 */
function json_error(string $code, int $statusCode = 200)
{
    json_response([
        'success' => false,
        'error'   => $code,
    ], $statusCode);
}
