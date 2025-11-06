<?php
require_once 'config.php';
header('Content-Type: application/json');

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['id'])) {
        throw new Exception('Message ID is required');
    }

    $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
    $result = $stmt->execute([$data['id']]);
    
    echo json_encode(['success' => $result]);
} catch (Exception $e) {
    error_log("Delete error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
