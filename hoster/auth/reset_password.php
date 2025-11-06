<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
error_log("Reset password process started");

header('Content-Type: application/json');
require_once('../config/db.php');
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendVerificationCode($email, $code) {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->SMTPDebug = 3; // Enable more detailed debug output
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Change to Gmail SMTP
        $mail->SMTPAuth = true;
        $mail->Username = 'abdollahbagueri02@gmail.com'; // Your Gmail address
        $mail->Password = 'Saretoo2005@Bag'; // Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Optional settings for SSL/TLS issues
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // Recipients
        $mail->setFrom('your.email@gmail.com', 'Password Reset');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Password Reset Code';
        $mail->Body = '
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                <h2 style="color: #1a2980;">Your Password Reset Code</h2>
                <p>You requested a password reset. Here is your verification code:</p>
                <div style="background: #f5f5f5; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; letter-spacing: 5px; margin: 20px 0;">
                    '.$code.'
                </div>
                <p>This code will expire in 15 minutes.</p>
                <p style="color: #666; font-size: 12px;">If you did not request this code, please ignore this email.</p>
            </div>';

        error_log("Attempting to send email to: " . $email);
        $result = $mail->send();
        error_log("Mail->send() result: " . ($result ? 'Success' : 'Failed'));
        return $result;
    } catch (Exception $e) {
        error_log("Mail error: " . $mail->ErrorInfo);
        error_log("Detailed exception: " . $e->getMessage());
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'send_code':
            $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
            if (!$email) {
                echo json_encode(['success' => false, 'message' => 'Invalid email format']);
                exit;
            }
            error_log("Processing reset request for email: " . $email);
            
            // Validate email
            $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if (!$user) {
                error_log("Email not found in database");
                echo json_encode(['success' => false, 'message' => 'Email not found']);
                exit;
            }

            // Generate and store code
            $code = sprintf("%06d", random_int(0, 999999));
            $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            error_log("Generated code: " . $code);
            
            try {
                $stmt = $pdo->prepare("UPDATE admin_users SET reset_code = ?, reset_code_expiry = ? WHERE email = ?");
                if ($stmt->execute([$code, $expiry, $email])) {
                    error_log("Code stored in database");
                    if (sendVerificationCode($email, $code)) {
                        echo json_encode(['success' => true, 'message' => 'Reset code sent']);
                    } else {
                        error_log("Failed to send email");
                        echo json_encode(['success' => false, 'message' => 'Failed to send email']);
                    }
                } else {
                    error_log("Database error: " . print_r($stmt->errorInfo(), true));
                    echo json_encode(['success' => false, 'message' => 'Database error']);
                }
            } catch(PDOException $e) {
                error_log("PDO Exception: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Database error']);
            }
            break;

        case 'verify_code':
            $code = $_POST['code'] ?? '';
            
            // Verify code
            $stmt = $pdo->prepare("SELECT reset_code, reset_code_expiry FROM admin_users WHERE username = 'admin'");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result && $result['reset_code'] === $code && strtotime($result['reset_code_expiry']) > time()) {
                echo json_encode(['success' => true, 'message' => 'Code verified']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid or expired code']);
            }
            break;

        case 'update_password':
            $code = $_POST['code'] ?? '';
            $newPassword = $_POST['password'] ?? '';
            
            // Verify code again and update password
            $stmt = $pdo->prepare("SELECT reset_code, reset_code_expiry FROM admin_users WHERE username = 'admin'");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result && $result['reset_code'] === $code && strtotime($result['reset_code_expiry']) > time()) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE admin_users SET password = ?, reset_code = NULL, reset_code_expiry = NULL WHERE username = 'admin'");
                
                if ($stmt->execute([$hashedPassword])) {
                    echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to update password']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid or expired code']);
            }
            break;

                
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
