<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db.php';
header('Content-Type: application/json');

try {
    // Verify PDO connection
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception('Database connection not established');
    }

    // Simpler query to debug database issues
    $query = "
        SELECT 
            oi.product_id as id,
            oi.product_name as name,
            COUNT(*) as units_sold,
            SUM(oi.total_price) as revenue
        FROM order_items oi 
        GROUP BY oi.product_id, oi.product_name
        ORDER BY revenue DESC
        LIMIT 5
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    
    // Log query results for debugging
    error_log("Query executed successfully");
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("Found " . count($products) . " products");

    if (empty($products)) {
        // Check if there's any data in the table
        $checkQuery = "SELECT COUNT(*) FROM order_items";
        $count = $pdo->query($checkQuery)->fetchColumn();
        error_log("Total records in order_items: " . $count);
        
        echo json_encode([
            'success' => true,
            'products' => [],
            'debug_info' => 'No products found. Total records: ' . $count
        ]);
        exit;
    }

    // Process results
    foreach ($products as &$product) {
        $product['trend'] = rand(-20, 50);
        $product['trend_data'] = array_map(function() {
            return rand(50, 100);
        }, range(1, 6));
        
        $product['units_sold'] = (int)$product['units_sold'];
        $product['revenue'] = (float)$product['revenue'];
    }

    echo json_encode([
        'success' => true,
        'products' => $products
    ]);

} catch (PDOException $e) {
    error_log("Database error in get_top_products.php: " . $e->getMessage());
    error_log("SQL State: " . $e->errorInfo[0]);
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage(),
        'debug_info' => [
            'sql_state' => $e->errorInfo[0],
            'error_code' => $e->errorInfo[1],
            'message' => $e->errorInfo[2]
        ]
    ]);
} catch (Exception $e) {
    error_log("General error in get_top_products.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
