<?php
require_once 'db.php';

header('Content-Type: application/json');

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid input data'
    ]);
    exit;
}

try {
    if (isset($input['id']) && $input['id']) {
        // Update existing customer
        $stmt = $pdo->prepare('
            UPDATE customers 
            SET first_name = ?, last_name = ?, phone = ?, email = ?, 
                address = ?, city = ?, state = ?
            WHERE id = ?
        ');
        
        $stmt->execute([
            $input['first_name'],
            $input['last_name'],
            $input['phone'],
            $input['email'],
            $input['address'],
            $input['city'],
            $input['state'],
            $input['id']
        ]);
        
        $message = 'Customer updated successfully';
    } else {
        // Insert new customer
        $stmt = $pdo->prepare('
            INSERT INTO customers (first_name, last_name, phone, email, address, city, state)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ');
        
        $stmt->execute([
            $input['first_name'],
            $input['last_name'],
            $input['phone'],
            $input['email'],
            $input['address'],
            $input['city'],
            $input['state']
        ]);
        
        $message = 'Customer added successfully';
    }
    
    echo json_encode([
        'success' => true,
        'message' => $message
    ]);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save customer: ' . $e->getMessage()
    ]);
}
?>
