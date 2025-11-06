<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', 'logs/activity_errors.log');

header('Content-Type: application/json');

// Include database configuration
require_once('config/db.php');

if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

try {
    // Use the existing PDO connection from db.php
    $stmt = $pdo->prepare("
        SELECT 
            login_time,
            ip_address,
            status
        FROM login_activity 
        WHERE admin_id = ? 
        ORDER BY login_time DESC 
        LIMIT 10
    ");
    
    $stmt->execute([$_SESSION['admin_id']]);
    $activities = $stmt->fetchAll();
    
    if ($activities) {
        echo json_encode(['success' => true, 'data' => $activities]);
    } else {
        echo json_encode(['success' => true, 'data' => []]);
    }

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Database error',
        'debug' => $e->getMessage()
    ]);
}
