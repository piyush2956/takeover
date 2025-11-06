<?php
require_once 'config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);
define('HOSTER_URL', '../hoster/');

$category = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : '';
$current_id = isset($_GET['current_id']) ? (int)$_GET['current_id'] : 0;

// Debug logging
error_log("Debug - Category: '$category', Current ID: $current_id");

// First, check if the category exists
$check_sql = "SELECT COUNT(*) as count FROM products WHERE category = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("s", $category);
$check_stmt->execute();
$count_result = $check_stmt->get_result()->fetch_assoc();
error_log("Debug - Found {$count_result['count']} products in category '$category'");

$sql = "SELECT p.id, p.name, p.price, p.discount, p.category,
        (SELECT pi.image_path FROM product_images pi 
         WHERE pi.product_id = p.id LIMIT 1) as image
        FROM products p
        WHERE p.category = ? AND p.id != ?
        ORDER BY RAND()
        LIMIT 4";

$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $category, $current_id);
$stmt->execute();
$result = $stmt->get_result();

$related_products = [];
while($row = $result->fetch_assoc()) {
    error_log("Debug - Processing product: " . json_encode($row));
    if ($row['image']) {
        $row['image'] = HOSTER_URL . trim($row['image']);
        $related_products[] = $row;
    }
}

error_log("Debug - Found " . count($related_products) . " related products");

header('Content-Type: application/json');
echo json_encode($related_products);

$check_stmt->close();
$stmt->close();
$conn->close();
?>
