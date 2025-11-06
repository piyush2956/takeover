<?php
header('Content-Type: application/json');

// Database connection
function connectDB() {
    $host = 'localhost';
    $dbname = 'u330854413_Promocode';
    $username = 'u330854413_promo';
    $password = '|fV6x1a1O8';

    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        return null;
    }
}

// Create shipping_settings table if it doesn't exist
function initializeDatabase($conn) {
    try {
        $sql = "CREATE TABLE IF NOT EXISTS shipping_settings (
            id INT PRIMARY KEY AUTO_INCREMENT,
            threshold_amount DECIMAL(10,2) NOT NULL,
            last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $conn->exec($sql);

        // Insert default value if table is empty
        $sql = "INSERT INTO shipping_settings (threshold_amount) 
                SELECT 1000 
                WHERE NOT EXISTS (SELECT 1 FROM shipping_settings)";
        $conn->exec($sql);
        
        return true;
    } catch(PDOException $e) {
        return false;
    }
}

// Get current shipping threshold
function getShippingThreshold($conn) {
    try {
        $stmt = $conn->query("SELECT threshold_amount FROM shipping_settings ORDER BY id DESC LIMIT 1");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['threshold_amount'] : 1000;
    } catch(PDOException $e) {
        return null;
    }
}

// Update shipping threshold
function updateShippingThreshold($conn, $amount) {
    try {
        $stmt = $conn->prepare("UPDATE shipping_settings SET threshold_amount = ? WHERE id = (SELECT id FROM (SELECT id FROM shipping_settings ORDER BY id DESC LIMIT 1) as temp)");
        return $stmt->execute([$amount]);
    } catch(PDOException $e) {
        return false;
    }
}

// Main logic
$conn = connectDB();
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Initialize database
if (!initializeDatabase($conn)) {
    echo json_encode(['success' => false, 'message' => 'Failed to initialize database']);
    exit;
}

// Handle requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['threshold'])) {
        $threshold = floatval($data['threshold']);
        if ($threshold >= 0) {
            if (updateShippingThreshold($conn, $threshold)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Shipping threshold updated successfully',
                    'threshold' => $threshold
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to update shipping threshold'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid threshold amount'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Threshold value not provided'
        ]);
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $threshold = getShippingThreshold($conn);
    if ($threshold !== null) {
        echo json_encode([
            'success' => true,
            'threshold' => $threshold
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to retrieve shipping threshold'
        ]);
    }
}
