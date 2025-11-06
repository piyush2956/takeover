<?php
require_once 'config.php';
require_once 'contact_mailer.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Get and sanitize form data
        $firstName = filter_input(INPUT_POST, 'firstName', FILTER_SANITIZE_STRING);
        $lastName = filter_input(INPUT_POST, 'lastName', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING);
        $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);

        // Validate required fields
        if (!$firstName || !$lastName || !$email || !$subject || !$message) {
            throw new Exception('All fields are required');
        }

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }

        // Insert into database
        $sql = "INSERT INTO contact_messages (first_name, last_name, email, subject, message) 
                VALUES (:firstName, :lastName, :email, :subject, :message)";
        
        $stmt = $pdo->prepare($sql);
        
        $stmt->execute([
            ':firstName' => $firstName,
            ':lastName' => $lastName,
            ':email' => $email,
            ':subject' => $subject,
            ':message' => $message
        ]);

        // Send email notification
        $mailer = new ContactMailer();
        $mailer->sendContactNotification([
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'subject' => $subject,
            'message' => $message
        ]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Thank you for your message. We will contact you soon!'
        ]);

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed'
    ]);
}
?>
