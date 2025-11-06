<?php
// Start session with strict settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 in production with HTTPS
session_start();

// Debug session status
error_log("Session ID: " . session_id());
error_log("Admin ID: " . ($_SESSION['admin_id'] ?? 'not set'));

// Set headers for local development
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:8080'); // Adjust port as needed
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Enable detailed error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', 'password_change_error.log');

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

try {
    // Debug authentication status
    if (!isset($_SESSION['admin_id'])) {
        error_log("Authentication failed - admin_id not in session");
        error_log("Session contents: " . print_r($_SESSION, true));
        throw new Exception('Not authenticated');
    }

    // Get and decode request body
    $input = file_get_contents('php://input');
    $postData = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        $postData = $_POST;
    }

    // Validate POST data
    if (empty($postData['currentPassword']) || empty($postData['newPassword'])) {
        throw new Exception('Missing required fields');
    }

    $currentPassword = $postData['currentPassword'];
    $newPassword = $postData['newPassword'];

    // Validate password length
    if (strlen($newPassword) < 8) {
        throw new Exception('New password must be at least 8 characters long');
    }

    // Database connection with error handling
    try {
        $conn = new PDO(
            "mysql:host=localhost;dbname=u330854413_log",
            "u330854413_login",
            "Tree#45Green",
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        throw new Exception('Database connection failed');
    }

    // Get current user's password hash
    $stmt = $conn->prepare("SELECT password FROM admin_users WHERE id = ?");
    if (!$stmt->execute([$_SESSION['admin_id']])) {
        throw new Exception('Failed to verify current password');
    }
    
    $user = $stmt->fetch();

    if (!$user || !password_verify($currentPassword, $user['password'])) {
        throw new Exception('Current password is incorrect');
    }

    // Update password
    $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    $updateStmt = $conn->prepare("UPDATE admin_users SET password = ? WHERE id = ?");
    
    if (!$updateStmt->execute([$newPasswordHash, $_SESSION['admin_id']])) {
        throw new Exception('Failed to update password');
    }

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Password updated successfully'
    ]);

} catch (Exception $e) {
    error_log("Password change error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
