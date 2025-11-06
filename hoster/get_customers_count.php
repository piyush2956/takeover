<?php
require_once 'db.php';
header('Content-Type: application/json');

try {
    error_log("Fetching customer count...");
    
    $query = "SELECT COUNT(*) as total FROM customers";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $total = (int)$result['total'];
    error_log("Found {$total} customers");

    echo json_encode([
        'success' => true,
        'total' => $total
    ]);

} catch (Exception $e) {
    error_log("Error in get_customers_count.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch customer count: ' . $e->getMessage()
    ]);
}
?>
