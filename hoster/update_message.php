<?php
require_once 'config.php';
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $input = file_get_contents('php://input');
    if (!$input) {
        throw new Exception('No input data received');
    }

    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data: ' . json_last_error_msg());
    }
    
    // Validate required fields
    if (!isset($data['id']) || !isset($data['first_name']) || !isset($data['email'])) {
        throw new Exception('Required fields are missing');
    }

    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Sanitize inputs
    $id = filter_var($data['id'], FILTER_SANITIZE_NUMBER_INT);
    $first_name = sanitizeInput($data['first_name']);
    $last_name = sanitizeInput($data['last_name']);
    $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
    $subject = sanitizeInput($data['subject']);
    $message = sanitizeInput($data['message']);

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    $stmt = $pdo->prepare("UPDATE contact_messages SET 
        first_name = ?, 
        last_name = ?, 
        email = ?, 
        subject = ?, 
        message = ? 
        WHERE id = ?");

    $success = $stmt->execute([
        $first_name,
        $last_name,
        $email,
        $subject,
        $message,
        $id
    ]);
    
    if (!$success) {
        throw new Exception('Failed to update message');
    }

    // Check if any rows were affected
    if ($stmt->rowCount() === 0) {
        throw new Exception('No message found with the given ID');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Message updated successfully'
    ]);

} catch (Exception $e) {
    error_log("Update error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
