<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

class EmailNotifier {
    private $mailer;

    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $this->setupMailer();
    }

    private function setupMailer() {
        $this->mailer->isSMTP();
        $this->mailer->Host = 'smtp.hostinger.com';
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = 'contact@pixelfga.com';
        $this->mailer->Password = 'Sky!23Blue';
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = 587;
    }

    public function sendPasswordChangeNotification($userEmail) {
        try {
            $this->mailer->setFrom('contact@pixelfga.com', 'Admin System');
            $this->mailer->addAddress($userEmail);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Password Change Notification';
            
            $body = "
            <h2>Password Change Alert</h2>
            <p>Your password was recently changed in the admin system.</p>
            <p>If you did not make this change, please contact support immediately.</p>
            <p>Time of change: " . date('Y-m-d H:i:s') . "</p>
            <p>IP Address: " . $_SERVER['REMOTE_ADDR'] . "</p>";
            
            $this->mailer->Body = $body;
            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }
}
