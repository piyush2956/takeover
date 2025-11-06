<?php
require_once('config/db.php');

$token = $_GET['token'] ?? '';
$message = '';

if (!empty($token)) {
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE verification_token = ? AND status = 'pending'");
        $stmt->execute([$token]);
        
        if ($user = $stmt->fetch()) {
            $updateStmt = $pdo->prepare("UPDATE users SET status = 'active', verification_token = NULL, email_verified_at = NOW() WHERE id = ?");
            $updateStmt->execute([$user['id']]);
            
            $message = '<div class="success">Email verified successfully! You can now login.</div>';
        } else {
            $message = '<div class="error">Invalid or expired verification token.</div>';
        }
    } catch (PDOException $e) {
        $message = '<div class="error">An error occurred. Please try again later.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verification</title>
    <style>
        body { font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .container { text-align: center; padding: 20px; }
        .success { color: green; }
        .error { color: red; }
        .button { display: inline-block; padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <?php echo $message; ?>
        <a href="index.html" class="button">Go to Login</a>
    </div>
</body>
</html>
