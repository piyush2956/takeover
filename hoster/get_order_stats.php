<?php
require_once 'db.php';
header('Content-Type: application/json');

try {
    $query = "
        SELECT 
            status,
            COUNT(*) as count
        FROM orders 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY status
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $orderCounts = [
        'confirmed' => 0,
        'pending' => 0,
        'shipped' => 0,
        'delivered' => 0,
        'cancelled' => 0
    ];

    foreach ($results as $row) {
        if (isset($orderCounts[$row['status']])) {
            $orderCounts[$row['status']] = (int)$row['count'];
        }
    }

    echo json_encode([
        'success' => true,
        'values' => array_values($orderCounts)
    ]);

} catch (Exception $e) {
    error_log("Error in get_order_stats.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch order statistics'
    ]);
}
?>
