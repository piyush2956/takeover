<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

class ContactMailer {
    private $mailer;
    
    public function __construct() {
        $this->mailer = new PHPMailer(true);
        
        // Debug output
        error_log("Initializing ContactMailer");
        
        $this->mailer->SMTPDebug = 3;
        $this->mailer->Debugoutput = function($str, $level) {
            error_log("PHPMailer Debug: $str");
        };
        
        // Configure mailer
        $this->mailer->isSMTP();
        $this->mailer->Host = 'smtp.hostinger.com';
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = 'contact@fashon-haven-store.com';
        $this->mailer->Password = '3A/5>>TaLKp_U(}eC{GS3f]5E';
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = 465;
        
        // Set default sender
        $this->mailer->setFrom('contact@fashon-haven-store.com', 'Contact Form');
    }
    
    public function sendContactNotification($contactData) {
        try {
            error_log("Attempting to send contact form notification email");
            $this->mailer->addAddress('contact@fashon-haven-store.com', 'Admin');
            $this->mailer->addAddress('abdellahbagueri@gmail.com', 'Dev');
            $this->mailer->Subject = 'New Contact Form Message - ' . $contactData['subject'];
            
            // Create email body
            $body = $this->createEmailBody($contactData);
            $this->mailer->isHTML(true);
            $this->mailer->Body = $body;
            
            $sent = $this->mailer->send();
            error_log("Email sent successfully");
            return true;
        } catch (Exception $e) {
            error_log("Failed to send contact notification email: " . $e->getMessage());
            error_log("Mailer Error: " . $this->mailer->ErrorInfo);
            throw $e;
        }
    }
    
    private function createEmailBody($contactData) {
        $body = "
        <h2>New Contact Form Message</h2>
        
        <h3>Sender Information:</h3>
        <p>
        <strong>Name:</strong> {$contactData['firstName']} {$contactData['lastName']}<br>
        <strong>Email:</strong> {$contactData['email']}<br>
        </p>
        
        <h3>Message Details:</h3>
        <p><strong>Subject:</strong> {$contactData['subject']}</p>
        
        <div style='background-color: #f5f5f5; padding: 15px; border-radius: 5px;'>
            <strong>Message:</strong><br>
            " . nl2br(htmlspecialchars($contactData['message'])) . "
        </div>";
        
        return $body;
    }
}
