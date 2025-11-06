<?php
require_once('../config/db.php');

// New password you want to set
$new_password = 'admin123';

// Hash the new password
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

try {
    // Update admin password
    $stmt = $pdo->prepare("UPDATE admin_users SET password = ? WHERE username = 'admin'");
    $result = $stmt->execute([$hashed_password]);
    
    if($result) {
        echo "Password successfully reset to: " . $new_password;
        echo "\nHash: " . $hashed_password;
    } else {
        echo "Failed to reset password";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
