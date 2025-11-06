<?php
header('Content-Type: application/json; charset=UTF-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log file path
$logFile = __DIR__ . '/debug.log';

function writeLog($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

define('HOSTER_URL', '/hoster/');  // Changed to use relative path

require_once 'config.php';

// Handle single product request
if (isset($_GET['product_id'])) {
    writeLog("Fetching details for product ID: " . $_GET['product_id']); // Debug log

    try {
        // Simplified query to debug the issue
        $stmt = $conn->prepare("
            SELECT p.*, 
                   'Black,White,Red,Blue' as colors,  /* Hardcoded colors for testing */
                   'S,M,L,XL' as sizes              /* Hardcoded sizes for testing */
            FROM products p 
            WHERE p.id = ?
        ");
        
        if (!$stmt) {
            writeLog("Prepare failed: " . $conn->error);
            die(json_encode(['error' => 'Database error']));
        }
        
        $stmt->bind_param("i", $_GET['product_id']);
        
        if (!$stmt->execute()) {
            writeLog("Execute failed: " . $stmt->error);
            die(json_encode(['error' => 'Query failed']));
        }
        
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        
        if (!$product) {
            writeLog("No product found with ID: " . $_GET['product_id']);
            die(json_encode(['error' => 'Product not found']));
        }

        writeLog("Product found: " . json_encode($product)); // Debug log
        
        // Get all product images
        $imagesStmt = $conn->prepare("
            SELECT image_path, is_primary 
            FROM product_images 
            WHERE product_id = ?
            ORDER BY is_primary DESC
        ");
        $imagesStmt->bind_param("i", $_GET['product_id']);
        $imagesStmt->execute();
        $imagesResult = $imagesStmt->get_result();
        
        $images = [];
        while ($img = $imagesResult->fetch_assoc()) {
            $images[] = HOSTER_URL . ltrim($img['image_path'], '/');
        }

        // Clean and format the response
        $response = [
            'id' => (int)$product['id'],
            'name' => strip_tags($product['name']),
            'description' => strip_tags($product['description']),
            'price' => (float)$product['price'],
            'discount' => (int)$product['discount'],
            // Ensure colors and sizes are arrays
            'colors' => explode(',', $product['colors'] ?: 'Black,White,Red,Blue'),
            'sizes' => explode(',', $product['sizes'] ?: 'S,M,L,XL'),
            'images' => array_values(array_filter($images)),
            'mainImage' => $images[0] ?? '../images/placeholder.jpg'
        ];

        writeLog("Response data: " . json_encode($response)); // Debug log
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }
}

$category = isset($_GET['category']) ? $_GET['category'] : '';

// Debug information
echo "<!-- Debug Info: Category = " . htmlspecialchars($category) . " -->\n";

$sql = "SELECT p.*, pi.image_path 
        FROM products p 
        LEFT JOIN product_images pi ON p.id = pi.product_id 
        WHERE 1=1";  // Changed to always true condition

if ($category && $category !== 'all') {
    $sql .= " AND p.category = ?";
}

// Debug SQL query
echo "<!-- Debug Info: SQL Query = " . htmlspecialchars($sql) . " -->\n";

try {
    $stmt = $conn->prepare($sql);
    
    if ($category && $category !== 'all') {
        $stmt->bind_param("s", $category);
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Query execution failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $productCount = $result->num_rows;
    
    // Debug product count
    echo "<!-- Debug Info: Found {$productCount} products -->\n";
    
    if ($productCount === 0) {
        echo '<div class="alert alert-info">No products found in this category.</div>';
    }

    while ($product = $result->fetch_assoc()) {
        writeLog("Rendering product ID: " . $product['id']); // Debug log
        
        $discount_price = $product['price'] * (1 - $product['discount']/100);
        $image_path = !empty($product['image_path']) 
            ? HOSTER_URL . ltrim($product['image_path'], '/') 
            : '../images/placeholder.jpg';

        // Debug image path
        writeLog("Image path for product {$product['id']}: {$image_path}");

        echo '<div class="product-card" data-id="' . $product['id'] . '">';
        echo '    <img src="' . htmlspecialchars($image_path) . '" 
                      alt="' . htmlspecialchars($product['name']) . '"
                      onerror="this.src=\'../images/placeholder.jpg\'"
                      class="product-image">';
                      
        if ($product['discount'] > 0) {
            echo '    <div class="discount-badge">-' . $product['discount'] . '%</div>';
        }
        
        echo '    <div class="product-info">';
        echo '        <h3 class="product-title">' . htmlspecialchars($product['name']) . '</h3>';
        echo '        <p class="description">' . htmlspecialchars($product['description']) . '</p>';
        echo '        <div class="price">';
        echo '            <span>' . number_format($discount_price, 2) . ' MAD</span>';
        if ($product['discount'] > 0) {
            echo '        <span class="original-price">' . number_format($product['price'], 2) . ' MAD</span>';
        }
        echo '        </div>';
        echo '        <button type="button" class="quicklook-btn" data-product-id="' . $product['id'] . '">Quick Look</button>';
        echo '    </div>';
        echo '</div>';
    }

} catch (Exception $e) {
    writeLog("Error: " . $e->getMessage()); // Debug log
    // Log error to file
    error_log("Database Error: " . $e->getMessage(), 0);
    // Display user-friendly error
    echo '<div class="alert alert-danger">
            <h4>Error Loading Products</h4>
            <p>We\'re experiencing technical difficulties. Please try again later.</p>
            <small>Error ID: ' . time() . '</small>
          </div>';
    // Display technical error for admins
    if (isset($_SERVER['SERVER_ADDR']) && $_SERVER['SERVER_ADDR'] === '127.0.0.1') {
        echo '<!-- Admin Debug: ' . htmlspecialchars($e->getMessage()) . ' -->';
    }
}

if (isset($stmt)) {
    $stmt->close();
}
$conn->close();
?>
