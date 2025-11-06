<?php
header('Content-Type: application/json');
require_once 'db.php';

error_log("Received request for order ID: " . $_GET['order_id']);

if (!isset($_GET['order_id'])) {
    echo json_encode(['success' => false, 'message' => 'Order ID is required']);
    exit;
}

try {
    // Get order details with customer information
    $orderQuery = "
        SELECT 
            o.id,
            o.customer_id,
            o.total_amount,
            o.status,
            o.created_at,
            c.first_name,
            c.last_name,
            c.phone,
            c.email,
            c.address,
            c.city,
            c.state
        FROM orders o
        LEFT JOIN customers c ON o.customer_id = c.id
        WHERE o.id = :order_id
    ";
    
    $stmt = $pdo->prepare($orderQuery);
    error_log("Executing order query for ID: " . $_GET['order_id']);
    $stmt->execute([':order_id' => $_GET['order_id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception('Order not found');
    }

    // Get order items
    $itemsQuery = "
        SELECT 
            id,
            product_id,
            product_name,
            product_size,
            product_color,
            product_image_url,
            quantity,
            price,
            total_price
        FROM order_items 
        WHERE order_id = :order_id
    ";
    
    $stmt = $pdo->prepare($itemsQuery);
    error_log("Executing items query for order ID: " . $_GET['order_id']);
    $stmt->execute([':order_id' => $_GET['order_id']]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $formattedOrder = [
        'order_id' => $order['id'],
        'order_date' => $order['created_at'],
        'status' => ucfirst($order['status']),
        'total_amount' => number_format($order['total_amount'], 2),
        'customer' => [
            'name' => $order['first_name'] . ' ' . $order['last_name'],
            'phone' => $order['phone'],
            'email' => $order['email'],
            'address' => $order['address'],
            'city' => $order['city'],
            'state' => $order['state']
        ]
    ];

    error_log("Successfully formatted order details");
    echo json_encode([
        'success' => true,
        'data' => [
            'order' => $formattedOrder,
            'items' => $items
        ]
    ]);

} catch(Exception $e) {
    error_log("Error in get_order_details.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch order details: ' . $e->getMessage()
    ]);
}
