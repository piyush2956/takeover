<?php
require_once 'DatabaseConfig.php';

header('Content-Type: application/json');

try {
    $productId = $_GET['id'] ?? null;
    if (!$productId) {
        throw new Exception('Product ID is required');
    }

    $db = DatabaseConfig::getInstance();
    
    // Get product details
    $product = $db->query(
        "SELECT * FROM products WHERE id = ?", 
        [$productId]
    )->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        throw new Exception('Product not found');
    }

    // Get variants with their IDs
    $variants = $db->query(
        "SELECT id, size, color, stock FROM product_variants WHERE product_id = ?",
        [$productId]
    )->fetchAll(PDO::FETCH_ASSOC);

    // Get product images
    $images = $db->query(
        "SELECT image_path FROM product_images WHERE product_id = ?",
        [$productId]
    )->fetchAll(PDO::FETCH_COLUMN);

    $product['images'] = implode(',', $images);

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
