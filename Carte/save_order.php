<?php
error_reporting(E_ALL); // Enable error reporting temporarily to debug
ini_set('display_errors', 1);
header('Content-Type: application/json');

try {
    require_once 'db.php';
    require_once 'order_mailer.php'; // Add mailer requirement
    
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);
    
    $pdo->beginTransaction();
    
    // Insert customer
    $stmt = $pdo->prepare("INSERT INTO customers (first_name, last_name, phone, email, address, city, state) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $data['firstName'],
        $data['lastName'],
        $data['phone'],
        $data['email'] ?? '',
        $data['address'] ?? '',
        $data['city'] ?? '',
        $data['region'] ?? ''
    ]);
    
    $customerId = $pdo->lastInsertId();
    
    // Insert order
    $stmt = $pdo->prepare("INSERT INTO orders (customer_id, total_amount) VALUES (?, ?)");
    $stmt->execute([
        $customerId,
        $data['totalAmount']
    ]);
    
    $orderId = $pdo->lastInsertId();
    
    // Insert order items
    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, product_size, 
                          product_color, product_image_url, quantity, price, total_price) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($data['items'] as $item) {
        $stmt->execute([
            $orderId,
            $item['id'] ?? 'N/A',
            $item['name'],
            $item['size'] ?? 'N/A',
            $item['color'] ?? 'N/A',
            $item['image'],
            $item['quantity'],
            $item['price'],
            $item['price'] * $item['quantity']
        ]);
    }
    
    $pdo->commit();
    
    // After successful order save, send email
    try {
        $mailer = new OrderMailer();
        $orderInfo = [
            'orderId' => $orderId,
            'totalAmount' => $data['totalAmount'],
            'items' => $data['items']
        ];
        
        $mailer->sendOrderNotification($orderInfo, $data);
        
        echo json_encode([
            'success' => true,
            'message' => 'Order placed successfully and notification sent!',
            'orderId' => $orderId
        ]);
    } catch (Exception $emailError) {
        // Log email error but still return success for order
        error_log("Email sending failed: " . $emailError->getMessage());
        echo json_encode([
            'success' => true,
            'message' => 'Order placed successfully (notification pending)',
            'orderId' => $orderId
        ]);
    }
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("Order error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Order processing failed: ' . $e->getMessage()
    ]);
}
?>