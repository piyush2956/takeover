<?php
require_once 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

try {
    $sql = "UPDATE customers SET 
            first_name = :first_name,
            last_name = :last_name,
            email = :email,
            phone = :phone,
            address = :address,
            city = :city,
            state = :state
            WHERE id = :id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id' => $data['id'],
        ':first_name' => $data['firstName'],
        ':last_name' => $data['lastName'],
        ':email' => $data['email'],
        ':phone' => $data['phone'],
        ':address' => $data['address'],
        ':city' => $data['city'],
        ':state' => $data['state']
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Customer updated successfully'
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error updating customer: ' . $e->getMessage()
    ]);
}
?>