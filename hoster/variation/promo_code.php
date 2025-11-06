<?php
// Database configuration
$db_host = 'localhost';
$db_user = 'u330854413_promo';
$db_password = '|fV6x1a1O8';
$db_name = 'u330854413_Promocode';

// Create database connection
$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// Create promo_codes table if not exists
$sql = "CREATE TABLE IF NOT EXISTS promo_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) UNIQUE NOT NULL,
    discount_percent INT NOT NULL,
    valid_until DATE NOT NULL,
    usage_limit INT NOT NULL,
    times_used INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'expired', 'depleted') DEFAULT 'active'
)";

if (!$conn->query($sql)) {
    die(json_encode(['success' => false, 'message' => 'Error creating table: ' . $conn->error]));
}

// Handle POST request for generating new promo code
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['action']) && $data['action'] === 'generate') {
        $prefix = $data['prefix'] ?? 'PROMO';
        $discount = $data['discount_percent'] ?? 10;
        $valid_until = $data['valid_until'] ?? date('Y-m-d', strtotime('+30 days'));
        $usage_limit = $data['usage_limit'] ?? 100;
        
        // Generate unique code
        do {
            $random = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
            $code = $prefix . '-' . $random;
            $check = $conn->query("SELECT id FROM promo_codes WHERE code = '$code'");
        } while ($check->num_rows > 0);
        
        // Insert new promo code
        $sql = "INSERT INTO promo_codes (code, discount_percent, valid_until, usage_limit) 
                VALUES (?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sisi", $code, $discount, $valid_until, $usage_limit);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Promo code generated successfully',
                'code' => $code,
                'details' => [
                    'discount' => $discount . '%',
                    'valid_until' => $valid_until,
                    'usage_limit' => $usage_limit
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error generating promo code: ' . $stmt->error
            ]);
        }
        
        $stmt->close();
    }
    
    // Handle promo code validation
    elseif (isset($data['action']) && $data['action'] === 'validate') {
        $code = $data['code'];
        
        $sql = "SELECT * FROM promo_codes 
                WHERE code = ? 
                AND valid_until >= CURRENT_DATE 
                AND times_used < usage_limit 
                AND status = 'active'";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $promo = $result->fetch_assoc();
            echo json_encode([
                'success' => true,
                'message' => 'Valid promo code',
                'discount' => $promo['discount_percent']
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid or expired promo code'
            ]);
        }
        
        $stmt->close();
    }
}

// Handle GET request for listing active promo codes
elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT * FROM promo_codes 
            WHERE valid_until >= CURRENT_DATE 
            AND times_used < usage_limit 
            AND status = 'active' 
            ORDER BY created_at DESC";
    
    $result = $conn->query($sql);
    $promos = [];
    
    while ($row = $result->fetch_assoc()) {
        $promos[] = [
            'code' => $row['code'],
            'discount' => $row['discount_percent'],
            'valid_until' => $row['valid_until'],
            'remaining_uses' => $row['usage_limit'] - $row['times_used']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'promo_codes' => $promos
    ]);
}

$conn->close();
?>
