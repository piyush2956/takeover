<?php
require_once 'DatabaseConfig.php';

header('Content-Type: application/json');

try {
    if (!isset($_POST['id'])) {
        throw new Exception('No product ID provided');
    }

    $productId = (int)$_POST['id'];
    $db = DatabaseConfig::getInstance();
    
    // Start transaction
    $db->query("START TRANSACTION");

    // Delete product (cascade will handle related records)
    $db->query("DELETE FROM products WHERE id = ?", [$productId]);

    $db->query("COMMIT");
    
    echo json_encode([
        'success' => true,
        'message' => 'Product deleted successfully'
    ]);

} catch (Exception $e) {
    if (isset($db)) {
        $db->query("ROLLBACK");
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
