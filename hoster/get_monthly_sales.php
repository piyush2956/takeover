<?php
require_once 'db.php';
header('Content-Type: application/json');

try {
    $query = "
        SELECT 
            DATE_FORMAT(o.created_at, '%Y-%m') as month,
            SUM(oi.total_price) as total_sales
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        WHERE o.created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(o.created_at, '%Y-%m')
        ORDER BY month ASC
        LIMIT 6
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format data for Chart.js
    $monthly_sales = array_map(function($row) {
        return floatval($row['total_sales']);
    }, $results);

    // Pad array to exactly 6 elements if less data available
    while (count($monthly_sales) < 6) {
        array_unshift($monthly_sales, 0);
    }

    echo json_encode([
        'success' => true,
        'monthly_sales' => $monthly_sales
    ]);

} catch (Exception $e) {
    error_log("Error in get_monthly_sales.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch monthly sales data'
    ]);
}
?>
