<?php
header('Content-Type: application/json');
require_once 'db.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['order_id']) || !isset($data['status'])) {
        throw new Exception('Order ID and status are required');
    }

    // Validate status
    $validStatuses = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];
    if (!in_array($data['status'], $validStatuses)) {
        throw new Exception('Invalid status value');
    }

    $stmt = $pdo->prepare("UPDATE orders SET status = :status WHERE id = :order_id");
    $result = $stmt->execute([
        ':status' => $data['status'],
        ':order_id' => $data['order_id']
    ]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('Order not found or status not changed');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Order status updated successfully'
    ]);

} catch(Exception $e) {
    error_log("Error updating order status: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update status: ' . $e->getMessage()
    ]);
}
