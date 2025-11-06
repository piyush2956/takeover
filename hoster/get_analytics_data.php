<?php
require_once 'db_connection.php';

try {
    $response = [
        'success' => true,
        'total_earnings' => 0,
        'total_orders' => 0,
        'total_customers' => 0,
        'total_products' => 0,
        'top_products' => []
    ];

    // Get total earnings and orders count
    $stmt = $pdo->query("
        SELECT 
            COALESCE(SUM(total_amount), 0) as total_earnings,
            COUNT(*) as total_orders 
        FROM orders
    ");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $response['total_earnings'] = floatval($result['total_earnings']);
    $response['total_orders'] = intval($result['total_orders']);

    // Get total customers
    $stmt = $pdo->query("SELECT COUNT(DISTINCT customer_id) as count FROM orders");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $response['total_customers'] = intval($result['count']);

    // Get total products
    $stmt = $pdo->query("SELECT COUNT(DISTINCT product_id) as count FROM order_items");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $response['total_products'] = intval($result['count']);

    // Get top selling products
    $stmt = $pdo->query("
        SELECT 
            oi.product_id,
            oi.product_name,
            COUNT(*) as units_sold,
            SUM(oi.total_price) as revenue
        FROM order_items oi
        GROUP BY oi.product_id, oi.product_name
        ORDER BY units_sold DESC
        LIMIT 3
    ");
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Get trend data for each product (last 6 months)
        $trendStmt = $pdo->prepare("
            SELECT COUNT(*) as sales
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            WHERE oi.product_id = ?
            AND o.created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            GROUP BY MONTH(o.created_at)
            ORDER BY o.created_at DESC
            LIMIT 6
        ");
        $trendStmt->execute([$row['product_id']]);
        $row['trend_data'] = array_column($trendStmt->fetchAll(PDO::FETCH_ASSOC), 'sales');
        
        // If less than 6 months of data, pad with zeros
        while (count($row['trend_data']) < 6) {
            array_unshift($row['trend_data'], 0);
        }
        
        $response['top_products'][] = $row;
    }

    echo json_encode($response);
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
