<?php
require_once 'DatabaseConfig.php';

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        throw new Exception('Invalid data received');
    }

    // Validate required fields
    if (empty($data['variant_id']) || empty($data['quantity']) || empty($data['sale_price'])) {
        throw new Exception('Missing required data');
    }

    $db = DatabaseConfig::getInstance();
    
    // Start transaction
    $db->query("START TRANSACTION");

    // Get current stock and validate variant exists
    $variant = $db->query(
        "SELECT pv.id, pv.stock, pv.product_id, pv.size, pv.color, p.price, p.name 
         FROM product_variants pv 
         JOIN products p ON p.id = pv.product_id 
         WHERE pv.id = ?",
        [$data['variant_id']]
    )->fetch(PDO::FETCH_ASSOC);

    if (!$variant) {
        throw new Exception('Invalid variant ID: ' . $data['variant_id']);
    }

    // Validate stock availability
    if ($variant['stock'] < $data['quantity']) {
        throw new Exception("Insufficient stock. Only {$variant['stock']} units available.");
    }

    // Validate sale price is not too low (optional)
    $minPrice = $variant['price'] * 0.5; // Example: Minimum 50% of original price
    if ($data['sale_price'] < $minPrice) {
        throw new Exception('Sale price is too low');
    }

    // Insert sale record
    $db->query(
        "INSERT INTO sales (product_id, variant_id, quantity, sale_price, sale_date, notes) 
         VALUES (?, ?, ?, ?, ?, ?)",
        [
            $variant['product_id'],
            $data['variant_id'],
            $data['quantity'],
            $data['sale_price'],
            $data['sale_date'],
            $data['notes'] ?? null
        ]
    );

    // Update stock with validation
    $result = $db->query(
        "UPDATE product_variants 
         SET stock = stock - ? 
         WHERE id = ? AND stock >= ?",
        [$data['quantity'], $data['variant_id'], $data['quantity']]
    );

    // Double check if stock was actually updated
    if ($result->rowCount() === 0) {
        throw new Exception('Failed to update stock - possibly insufficient quantity');
    }

    $db->query("COMMIT");

    echo json_encode([
        'success' => true,
        'message' => sprintf(
            'Sale recorded successfully: %s - %s %s (Qty: %d)',
            $variant['name'],
            $variant['size'],
            $variant['color'],
            $data['quantity']
        )
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
