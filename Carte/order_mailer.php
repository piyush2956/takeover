<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

class OrderMailer {
    private $mailer;
    
    public function __construct() {
        $this->mailer = new PHPMailer(true);
        
        // Debug output
        error_log("Initializing OrderMailer");
        
        $this->mailer->SMTPDebug = 3; // Increase debug level
        $this->mailer->Debugoutput = function($str, $level) {
            error_log("PHPMailer Debug: $str");
        };
        
        // Configure mailer
        $this->mailer->isSMTP();
        $this->mailer->Host = 'smtp.hostinger.com';
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = 'contact@pixelfga.com';
        $this->mailer->Password = 'Sky!23Blue';
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = 587;
        
        // Set default sender
        $this->mailer->setFrom('contact@pixelfga.com', 'PixelFGA Store');
    }
    
    public function sendOrderNotification($orderData, $customerData) {
        try {
            // Send to admin
            $this->sendAdminNotification($orderData, $customerData);
            
            // Send to customer
            $this->sendCustomerNotification($orderData, $customerData);
            
            return true;
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function sendAdminNotification($orderData, $customerData) {
        $this->mailer->clearAddresses();
        $this->mailer->addAddress('abdollahbagueri02@gmail.com', 'Admin');
        $this->mailer->Subject = 'New Order Received - Order #' . $orderData['orderId'];
        $this->mailer->isHTML(true);
        $this->mailer->Body = $this->createAdminEmailBody($orderData, $customerData);
        $this->mailer->send();
    }
    
    private function sendCustomerNotification($orderData, $customerData) {
        $this->mailer->clearAddresses();
        $this->mailer->addAddress($customerData['email'], $customerData['firstName'] . ' ' . $customerData['lastName']);
        $this->mailer->Subject = 'Thank You for Your Order! - Order #' . $orderData['orderId'];
        $this->mailer->isHTML(true);
        $this->mailer->Body = $this->createCustomerEmailBody($orderData, $customerData);
        $this->mailer->send();
    }
    
    private function createCustomerEmailBody($orderData, $customerData) {
        // Check if customer is from a French region
        $isFrench = $this->isFrenchRegion($customerData['region']);
        
        if ($isFrench) {
            return $this->createFrenchCustomerEmail($orderData, $customerData);
        } else {
            return $this->createEnglishCustomerEmail($orderData, $customerData);
        }
    }
    
    private function isFrenchRegion($region) {
        // Add logic to determine if the region/city is French-speaking
        $frenchRegions = [
            'tanger-tetouan-al-hoceima',
            'rabat-sale-kenitra',
            'casablanca-settat',
            'fes-meknes'
            // Add other French-speaking regions as needed
        ];
        return in_array(strtolower($region), $frenchRegions);
    }
    
    private function createFrenchCustomerEmail($orderData, $customerData) {
        $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='text-align: center; margin-bottom: 30px;'>
            </div>
            
            <div style='background: linear-gradient(135deg, #FF6B6B, #4ECDC4); color: white; padding: 20px; border-radius: 10px; text-align: center; margin-bottom: 30px;'>
                <h1 style='margin: 0;'>Merci pour votre commande !</h1>
                <p style='margin: 10px 0 0;'>Commande #" . $orderData['orderId'] . "</p>
            </div>
            
            <div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 30px;'>
                <h2 style='color: #4ECDC4; margin-top: 0;'>Bonjour " . $customerData['firstName'] . ",</h2>
                <p>Nous sommes ravis de vous informer que nous avons bien reçu votre commande et qu'elle est en cours de traitement ! Nous vous contacterons prochainement pour confirmer votre commande et organiser la livraison.</p>
            </div>
            
            <div style='background: #ffffff; border: 1px solid #dee2e6; border-radius: 10px; padding: 20px; margin-bottom: 30px;'>
                <h3 style='color: #FF6B6B; margin-top: 0;'>Récapitulatif de la commande</h3>
                <table style='width: 100%; border-collapse: collapse;'>
                    <tr style='border-bottom: 1px solid #dee2e6;'>
                        <th style='text-align: left; padding: 10px;'>Article</th>
                        <th style='text-align: center; padding: 10px;'>Quantité</th>
                        <th style='text-align: right; padding: 10px;'>Prix</th>
                    </tr>";

        foreach ($orderData['items'] as $item) {
            $body .= "
                    <tr style='border-bottom: 1px solid #dee2e6;'>
                        <td style='padding: 10px;'>" . $item['name'] . "</td>
                        <td style='text-align: center; padding: 10px;'>" . $item['quantity'] . "</td>
                        <td style='text-align: right; padding: 10px;'>" . number_format($item['price'] * $item['quantity'], 2) . " MAD</td>
                    </tr>";
        }

        $body .= "
                    <tr>
                        <td colspan='2' style='text-align: right; padding: 10px;'><strong>Total :</strong></td>
                        <td style='text-align: right; padding: 10px;'><strong>" . number_format($orderData['totalAmount'], 2) . " MAD</strong></td>
                    </tr>
                </table>
            </div>
            
            <div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 30px;'>
                <h3 style='color: #4ECDC4; margin-top: 0;'>Informations de livraison</h3>
                <p style='margin: 0;'>
                    <strong>Adresse :</strong><br>
                    " . $customerData['address'] . "<br>
                    " . $customerData['city'] . ", " . $customerData['region'] . "
                </p>
            </div>
            
            <div style='text-align: center; color: #6c757d; font-size: 0.9em;'>
                <p>Si vous avez des questions, n'hésitez pas à nous contacter :</p>
                <p><a href='tel:+212600400530' style='color: #4ECDC4; text-decoration: none;'>+212 600-400-530</a></p>
                <p>Nous vous remercions de votre confiance !</p>
            </div>
        </div>";
        
        return $body;
    }
    
    private function createEnglishCustomerEmail($orderData, $customerData) {
        $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='text-align: center; margin-bottom: 30px;'>
            </div>
            
            <div style='background: linear-gradient(135deg, #FF6B6B, #4ECDC4); color: white; padding: 20px; border-radius: 10px; text-align: center; margin-bottom: 30px;'>
                <h1 style='margin: 0;'>Thank You for Your Order!</h1>
                <p style='margin: 10px 0 0;'>Order #" . $orderData['orderId'] . "</p>
            </div>
            
            <div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 30px;'>
                <h2 style='color: #4ECDC4; margin-top: 0;'>Hi " . $customerData['firstName'] . ",</h2>
                <p>We're excited to let you know that we've received your order and it's being processed! We'll contact you shortly to confirm your order and arrange delivery.</p>
            </div>
            
            <div style='background: #ffffff; border: 1px solid #dee2e6; border-radius: 10px; padding: 20px; margin-bottom: 30px;'>
                <h3 style='color: #FF6B6B; margin-top: 0;'>Order Summary</h3>
                <table style='width: 100%; border-collapse: collapse;'>
                    <tr style='border-bottom: 1px solid #dee2e6;'>
                        <th style='text-align: left; padding: 10px;'>Item</th>
                        <th style='text-align: center; padding: 10px;'>Quantity</th>
                        <th style='text-align: right; padding: 10px;'>Price</th>
                    </tr>";

        foreach ($orderData['items'] as $item) {
            $body .= "
                    <tr style='border-bottom: 1px solid #dee2e6;'>
                        <td style='padding: 10px;'>" . $item['name'] . "</td>
                        <td style='text-align: center; padding: 10px;'>" . $item['quantity'] . "</td>
                        <td style='text-align: right; padding: 10px;'>" . number_format($item['price'] * $item['quantity'], 2) . " MAD</td>
                    </tr>";
        }

        $body .= "
                    <tr>
                        <td colspan='2' style='text-align: right; padding: 10px;'><strong>Total:</strong></td>
                        <td style='text-align: right; padding: 10px;'><strong>" . number_format($orderData['totalAmount'], 2) . " MAD</strong></td>
                    </tr>
                </table>
            </div>
            
            <div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 30px;'>
                <h3 style='color: #4ECDC4; margin-top: 0;'>Delivery Information</h3>
                <p style='margin: 0;'>
                    <strong>Address:</strong><br>
                    " . $customerData['address'] . "<br>
                    " . $customerData['city'] . ", " . $customerData['region'] . "
                </p>
            </div>
            
            <div style='text-align: center; color: #6c757d; font-size: 0.9em;'>
                <p>If you have any questions, please don't hesitate to contact us:</p>
                <p><a href='tel:+212600400530' style='color: #4ECDC4; text-decoration: none;'>+212 600-400-530</a></p>
                
            </div>
        </div>";
        
        return $body;
    }
    
    private function createAdminEmailBody($orderData, $customerData) {
        $body = "
        <h2>New Order Received</h2>
        <p><strong>Order ID:</strong> #{$orderData['orderId']}</p>
        <p><strong>Total Amount:</strong> " . number_format($orderData['totalAmount'], 2) . " MAD</p>
        
        <h3>Customer Information:</h3>
        <p>
        Name: {$customerData['firstName']} {$customerData['lastName']}<br>
        Email: {$customerData['email']}<br>
        Phone: {$customerData['phone']}<br>
        Address: {$customerData['address']}, {$customerData['city']}, {$customerData['region']}
        </p>
        
        <h3>Order Items:</h3>
        <table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse'>
        <tr>
            <th>Product</th>
            <th>Size</th>
            <th>Color</th>
            <th>Quantity</th>
            <th>Price</th>
            <th>Total</th>
        </tr>";
        
        foreach ($orderData['items'] as $item) {
            $body .= "<tr>
                <td>{$item['name']}</td>
                <td>{$item['size']}</td>
                <td>{$item['color']}</td>
                <td>{$item['quantity']}</td>
                <td>" . number_format($item['price'], 2) . " MAD</td>
                <td>" . number_format($item['price'] * $item['quantity'], 2) . " MAD</td>
            </tr>";
        }
        
        $body .= "</table>";
        return $body;
    }
}
