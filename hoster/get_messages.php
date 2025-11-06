<?php
require_once 'config.php';
header('Content-Type: application/json');

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Test if $pdo is available
    if (!isset($pdo)) {
        throw new Exception("Database connection not established");
    }

    // Get new messages count with error checking
    $countStmt = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'new'");
    if (!$countStmt) {
        throw new Exception("Failed to execute count query");
    }
    $newCount = $countStmt->fetchColumn();

    // Get all messages with error checking
    $stmt = $pdo->query("
        SELECT id, first_name, last_name, email, subject, message, created_at, status 
        FROM contact_messages 
        ORDER BY created_at DESC
    ");
    if (!$stmt) {
        throw new Exception("Failed to execute messages query");
    }
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'new_count' => $newCount,
        'messages' => $messages
    ]);
} catch (Exception $e) {
    error_log("Messages error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => __FILE__,
        'line' => __LINE__
    ]);
}
?>
