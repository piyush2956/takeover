<?php
$db = new mysqli('localhost', 'u330854413_product', 'Sky!23Blue', 'u330854413_pro');

// Add constant for hoster URL
define('HOSTER_URL', '/hoster/');

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 1;

// Get product details
$stmt = $db->prepare("
    SELECT p.*, 
           p.shipping_type,
           p.shipping_price,
           p.category,
           GROUP_CONCAT(DISTINCT CONCAT('" . HOSTER_URL . "', pi.image_path)) as images,
           GROUP_CONCAT(DISTINCT CONCAT(pv.size, ':', pv.color, ':', pv.stock)) as variants,
           MIN(pv.stock) as min_stock,
           SUM(CASE WHEN pv.stock = 0 THEN 1 ELSE 0 END) as out_of_stock_count
    FROM products p
    LEFT JOIN product_images pi ON p.id = pi.product_id
    LEFT JOIN product_variants pv ON p.id = pv.product_id
    WHERE p.id = ?
    GROUP BY p.id
");

$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

header('Content-Type: application/json');
echo json_encode($result);
?>
