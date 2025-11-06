<?php
require_once 'DatabaseConfig.php';

header('Content-Type: application/json');

try {
    $db = DatabaseConfig::getInstance();
    
    $result = $db->query("SELECT COUNT(*) as total FROM products")->fetch();
    
    echo json_encode([
        'success' => true,
        'count' => $result['total']
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
