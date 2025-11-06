<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once('../config/db.php');
require '../vendor/autoload.php'; // Make sure PHPMailer is installed via composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Function to send verification code
function sendVerificationCode($email, $code) {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.hostinger.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'contact@pixelfga.com';
        $mail->Password = 'Sky!23Blue';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('contact@fashon-haven-store.com', 'Fashion Haven Store');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Password Reset Request - Fashion Haven Store';
        
        // Enhanced HTML email template with fashion theme
        $mail->Body = '
            <div style="background: linear-gradient(135deg, #ff758c, #ff4081); padding: 40px 0;">
                <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                    <div style="text-align: center; margin-bottom: 30px;">
                        <h1 style="color: #4a0f3d; margin: 0; font-size: 28px; font-family: Arial, sans-serif;">Password Reset Verification</h1>
                    </div>
                    <div style="margin-bottom: 30px;">
                        <p style="color: #666; font-size: 16px; line-height: 24px;">We received a request to reset your password. Use this verification code to complete the process:</p>
                        <div style="background: linear-gradient(135deg, #4a0f3d, #7b1649); padding: 20px; border-radius: 10px; text-align: center; margin: 20px 0;">
                            <span style="font-size: 32px; letter-spacing: 8px; color: #ffffff; font-weight: bold; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">'.$code.'</span>
                        </div>
                        <p style="color: #666; font-size: 14px; line-height: 20px;">This code will expire in 15 minutes for security purposes.</p>
                    </div>
                    <div style="text-align: center; padding-top: 20px; border-top: 1px solid #eee;">
                        <p style="color: #999; font-size: 12px;">If you did not request this code, please ignore this email or contact support.</p>
                        <p style="color: #999; font-size: 12px;">© 2024 Fashion Haven Store. All rights reserved.</p>
                    </div>
                </div>
            </div>
        ';

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mail error: " . $mail->ErrorInfo);
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Get admin email from database
    $stmt = $pdo->query("SELECT email FROM admin_users WHERE username = 'admin'");
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    $adminEmail = $admin['email'] ?? null;

    if (!$adminEmail) {
        echo json_encode(['success' => false, 'message' => 'Admin account not found']);
        exit;
    }

    switch ($action) {
        case 'send_code':
            // Generate and store verification code
            $verificationCode = sprintf("%06d", random_int(0, 999999));
            $expiryTime = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            
            error_log("Debug - Admin email: $adminEmail");
            error_log("Debug - Verification code: $verificationCode");
            
            // Store code in database
            $stmt = $pdo->prepare("UPDATE admin_users SET reset_code = ?, reset_code_expiry = ? WHERE username = 'admin'");
            if (!$stmt->execute([$verificationCode, $expiryTime])) {
                error_log("Database error: " . print_r($stmt->errorInfo(), true));
                echo json_encode(['success' => false, 'message' => 'Database error']);
                break;
            }
            
            error_log("Debug - Code stored in database successfully");
            
            // Send code via email with detailed error handling
            $emailResult = sendVerificationCode($adminEmail, $verificationCode);
            error_log("Debug - Email send result: " . ($emailResult ? 'Success' : 'Failure'));
            
            if ($emailResult) {
                echo json_encode(['success' => true, 'message' => 'Verification code sent']);
            } else {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Failed to send verification code. Please check server logs.'
                ]);
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
