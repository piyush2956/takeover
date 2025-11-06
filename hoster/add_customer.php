<?php
require_once 'db.php';

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log incoming request
error_log("Received add customer request");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    error_log("Received data: " . $input);
    
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid JSON data: ' . json_last_error_msg()
        ]);
        exit;
    }

    // Validate required fields
    if (empty($data['firstName']) || empty($data['lastName']) || empty($data['phone'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'First name, last name and phone are required'
        ]);
        exit;
    }
    
    try {
        $sql = "INSERT INTO customers (first_name, last_name, email, phone, address, city, state) 
                VALUES (:first_name, :last_name, :email, :phone, :address, :city, :state)";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            ':first_name' => $data['firstName'],
            ':last_name' => $data['lastName'],
            ':email' => $data['email'] ?? null,
            ':phone' => $data['phone'],
            ':address' => $data['address'] ?? null,
            ':city' => $data['city'] ?? null,
            ':state' => $data['state'] ?? null
        ]);

        if ($result) {
            $newId = $pdo->lastInsertId();
            error_log("Customer added successfully with ID: " . $newId);
            
            echo json_encode([
                'success' => true,
                'message' => 'Customer added successfully',
                'id' => $newId
            ]);
        } else {
            throw new Exception('Failed to insert record');
        }
    } catch (Exception $e) {
        error_log("Error adding customer: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error adding customer: ' . $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}
?>
