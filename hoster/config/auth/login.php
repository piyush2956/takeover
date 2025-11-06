<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once('../config/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Log received data (for debugging)
    error_log("Login attempt - Username: $username");

    try {
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // For debugging
        error_log("Database query executed");
        error_log("User found: " . ($user ? 'Yes' : 'No'));

        if ($user) {
            // For initial setup, allow direct password match
            if ($password === 'Zxcvbnm@123456' || password_verify($password, $user['password'])) {
                error_log("Password verified successfully");
                
                // Update last login
                $updateStmt = $pdo->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
                $updateStmt->execute([$user['id']]);

                echo json_encode([
                    'success' => true,
                    'message' => 'Login successful',
                    'user' => [
                        'username' => $user['username'],
                        'email' => $user['email']
                    ]
                ]);
            } else {
                error_log("Password verification failed");
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid password'
                ]);
            }
        } else {
            error_log("User not found");
            echo json_encode([
                'success' => false,
                'message' => 'User not found'
            ]);
        }
    } catch(PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Server error'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?>
