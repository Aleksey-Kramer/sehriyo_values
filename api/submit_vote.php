<?php

require_once __DIR__ . '/config.php';

// Для этого эндпоинта мы не отдаём JSON, а просто редиректим на accepted.html
// Переопределим Content-Type из config.php
if (!headers_sent()) {
    header('Content-Type: text/html; charset=utf-8');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.html');
    exit;
}

// Читаем и приводим к int
$item1 = isset($_POST['item_1']) ? (int)$_POST['item_1'] : 0;
$item2 = isset($_POST['item_2']) ? (int)$_POST['item_2'] : 0;
$item3 = isset($_POST['item_3']) ? (int)$_POST['item_3'] : 0;

// Простая валидация: должны быть заданы три разных значения > 0
$items = [$item1, $item2, $item3];
$uniqueItems = array_unique($items);

if ($item1 <= 0 || $item2 <= 0 || $item3 <= 0 || count($uniqueItems) !== 3) {
    // Если что-то пошло не так — возвращаем на главную
    header('Location: ../index.html');
    exit;
}

try {
    $stmt = $pdo->prepare(
        'insert into votes (item_1, item_2, item_3) 
         values (:item1, :item2, :item3)'
    );

    $stmt->execute([
        ':item1' => $item1,
        ':item2' => $item2,
        ':item3' => $item3,
    ]);

    // Успех — идём на страницу "спасибо"
    header('Location: ../accepted.html');
    exit;
} catch (PDOException $e) {
    // На проде можно логировать, а пользователю — просто вернуть на старт
    header('Location: ../index.html');
    exit;
}
