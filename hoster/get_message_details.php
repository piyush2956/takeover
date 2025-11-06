<?php
require_once 'config.php';
header('Content-Type: application/json');

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!isset($_GET['id'])) {
        throw new Exception('Message ID is required');
    }

    $stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $message = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($message) {
        echo json_encode(['success' => true, 'data' => $message]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Message not found']);
    }
} catch (Exception $e) {
    error_log("Details error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
