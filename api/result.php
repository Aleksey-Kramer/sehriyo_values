<?php

require_once __DIR__ . '/config.php';

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
        'labels'  => $labels,  // Для Chart.js
        'data'    => $data,    // Для Chart.js
        'items'   => $items,   // На всякий случай: детальный список
    ]);
} catch (PDOException $e) {
    json_error('db_query_failed', 500);
}
