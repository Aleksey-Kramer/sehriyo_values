<?php

require_once __DIR__ . '/config.php';

// --- CORS для локальной отладки --- //
$allowed_origins = [
    'http://localhost',
    'http://127.0.0.1',
    'http://localhost:5500',
    'http://127.0.0.1:5500',
];

if (!empty($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins, true)) {
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
    header('Vary: Origin');
    // если вдруг будешь слать куки/сессию с фронта:
    // header('Access-Control-Allow-Credentials: true');
}

// если фронт будет дергать OPTIONS (preflight), можно обработать так:
/*
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Methods: GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    exit;
}
*/

try {
    // Считаем, сколько раз каждая ценность встретилась в item_1, item_2, item_3
    $sql = "
        select
            vi.id,
            vi.item,
            coalesce(v.cnt, 0) as votes
        from value_items vi
        left join (
            select item_id, count(*) as cnt
            from (
                select item_1 as item_id from votes
                union all
                select item_2 as item_id from votes
                union all
                select item_3 as item_id from votes
            ) t
            group by item_id
        ) v on v.item_id = vi.id
        order by vi.id
    ";

    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll();

    $labels = [];
    $data   = [];
    $items  = [];

    foreach ($rows as $row) {
        $labels[] = $row['item'];
        $data[]   = (int)$row['votes'];

        $items[] = [
            'id'    => (int)$row['id'],
            'label' => $row['item'],
            'votes' => (int)$row['votes'],
        ];
    }

    json_response([
        'success' => true,
        'labels'  => $labels,
        'data'    => $data,
        'items'   => $items,
    ]);
} catch (PDOException $e) {
    json_error('db_query_failed', 500);
}
