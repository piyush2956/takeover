<?php
require_once 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id']) || !filter_var($input['id'], FILTER_VALIDATE_INT)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid customer ID'
    ]);
    exit;
}

try {
    // First check if customer has any orders
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE customer_id = ?");
    $stmt->execute([$input['id']]);
    $orderCount = $stmt->fetchColumn();
    
    if ($orderCount > 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Cannot delete customer with existing orders'
        ]);
        exit;
    }
    
    // If no orders, proceed with deletion
    $stmt = $pdo->prepare("DELETE FROM customers WHERE id = ?");
    $stmt->execute([$input['id']]);
    
    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Customer not found'
        ]);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Customer deleted successfully'
    ]);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to delete customer: ' . $e->getMessage()
    ]);
}
?>
