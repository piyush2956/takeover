<?php
require_once 'DatabaseConfig.php';

header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('Product ID is required');
    }

    $db = DatabaseConfig::getInstance();
    $productId = $_GET['id'];

    // Get basic product details
    $product = $db->query("
        SELECT *
        FROM products
        WHERE id = ?
    ", [$productId])->fetch();

    if (!$product) {
        throw new Exception('Product not found');
    }

    // Get images separately
    $images = $db->query("
        SELECT image_path
        FROM product_images
        WHERE product_id = ?
    ", [$productId])->fetchAll(PDO::FETCH_COLUMN);
    
    $product['images'] = implode(',', $images);

    // Get unique size/color combinations
    $variants = $db->query("
        SELECT DISTINCT size, color
        FROM product_variants
        WHERE product_id = ?
        ORDER BY size, color
    ", [$productId])->fetchAll();

    // Get stock for each variant
    foreach ($variants as &$variant) {
        $stock = $db->query("
            SELECT stock
            FROM product_variants
            WHERE product_id = ? AND size = ? AND color = ?
        ", [$productId, $variant['size'], $variant['color']])->fetchColumn();
        
        $variant['stock'] = (int)$stock;
    }

    echo json_encode([
        'success' => true,
        'product' => $product,
        'variants' => $variants
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
