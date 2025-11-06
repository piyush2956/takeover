<?php
header('Content-Type: application/json');
require_once 'db.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['order_id'])) {
        throw new Exception('Order ID is required');
    }

    $orderId = $data['order_id'];
    
    // Log the deletion attempt
    error_log("Attempting to delete order ID: " . $orderId);

    // Start transaction
    $pdo->beginTransaction();

    // First count how many items will be deleted
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE order_id = :order_id");
    $stmt->execute([':order_id' => $orderId]);
    $itemCount = $stmt->fetchColumn();
    error_log("Found {$itemCount} items to delete for order {$orderId}");

    // Delete order items first (due to foreign key constraint)
    $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id = :order_id");
    $stmt->execute([':order_id' => $orderId]);
    $deletedItems = $stmt->rowCount();
    error_log("Deleted {$deletedItems} items from order_items");

    // Then delete the order
    $stmt = $pdo->prepare("DELETE FROM orders WHERE id = :order_id");
    $stmt->execute([':order_id' => $orderId]);
    $deletedOrder = $stmt->rowCount();
    error_log("Deleted order from orders table. Rows affected: {$deletedOrder}");

    // Commit transaction
    $pdo->commit();

    if ($deletedOrder > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Order and its items deleted successfully',
            'details' => [
                'items_deleted' => $deletedItems,
                'order_deleted' => true
            ]
        ]);
    } else {
        throw new Exception('Order not found or already deleted');
    }

} catch(Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log("Error deleting order: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to delete order: ' . $e->getMessage(),
        'error_details' => $e->getMessage()
    ]);
}
