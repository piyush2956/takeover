<?php
require_once 'db.php';
header('Content-Type: application/json');

try {
    $query = "
        SELECT 
            DATE_FORMAT(o.created_at, '%M') as month,
            SUM(oi.total_price) as total
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        WHERE o.created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
        GROUP BY month
        ORDER BY o.created_at DESC
        LIMIT 6
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $labels = [];
    $values = [];

    foreach ($results as $row) {
        array_unshift($labels, $row['month']);
        array_unshift($values, floatval($row['total']));
    }

    // Pad with zeros if less than 6 months of data
    while (count($labels) < 6) {
        array_unshift($labels, 'Month ' . count($labels));
        array_unshift($values, 0);
    }

    echo json_encode([
        'success' => true,
        'labels' => $labels,
        'values' => $values
    ]);

} catch (Exception $e) {
    error_log('Error in get_sales_data.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Database error'
    ]);
}
?>
