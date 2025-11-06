<?php
header('Content-Type: application/json');

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    // Database connection
    $conn = new PDO(
        "mysql:host=localhost;dbname=u330854413_Promocode",
        "u330854413_promo",
        "|fV6x1a1O8",
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
    );

    // Get request type and action
    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'validate_promo':
            validatePromoCode();
            break;
        case 'get_shipping_threshold':
            getShippingThreshold();
            break;
        default:
            throw new Exception('Invalid action specified');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

function validatePromoCode() {
    global $conn;
    
    // Get the promo code from POST request
    $code = strtoupper($_POST['code'] ?? '');
    
    if (empty($code)) {
        throw new Exception('No promo code provided');
    }

    // Prepare and execute query
    $stmt = $conn->prepare("
        SELECT * FROM promo_codes 
        WHERE code = :code 
        AND valid_until >= CURDATE()
        AND (usage_limit > times_used OR usage_limit = 0)
    ");
    
    $stmt->execute(['code' => $code]);
    $promoCode = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($promoCode) {
        // Update usage count
        $updateStmt = $conn->prepare("
            UPDATE promo_codes 
            SET times_used = times_used + 1,
                last_used = CURRENT_TIMESTAMP
            WHERE id = :id
        ");
        $updateStmt->execute(['id' => $promoCode['id']]);

        echo json_encode([
            'valid' => true,
            'discount' => $promoCode['discount_percent'],
            'message' => "Discount of {$promoCode['discount_percent']}% applied!"
        ]);
    } else {
        echo json_encode([
            'valid' => false,
            'message' => 'Invalid or expired promo code'
        ]);
    }
}

function getShippingThreshold() {
    global $conn;
    
    $stmt = $conn->prepare("SELECT threshold_amount FROM shipping_settings LIMIT 1");
    $stmt->execute();
    $setting = $stmt->fetch();

    echo json_encode([
        'threshold' => floatval($setting['threshold_amount'])
    ]);
}
