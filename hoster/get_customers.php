<?php
require_once 'db.php';
header('Content-Type: application/json');

try {
    $stmt = $pdo->query('SELECT * FROM customers ORDER BY created_at DESC');
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $customers
    ]);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching customers: ' . $e->getMessage()
    ]);
}
?>
