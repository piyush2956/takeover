<?php
require_once 'DatabaseConfig.php';

try {
    $db = DatabaseConfig::getInstance();
    
    // First get all products with their stock
    $query = "
        SELECT 
            p.*,
            SUM(pv.stock) as total_stock
        FROM products p
        LEFT JOIN product_variants pv ON p.id = pv.product_id
        GROUP BY p.id
        ORDER BY p.created_at DESC
    ";
    
    $products = $db->query($query)->fetchAll();

    // Then get images for each product
    foreach ($products as &$product) {
        $images = $db->query("
            SELECT image_path
            FROM product_images
            WHERE product_id = ?
        ", [$product['id']])->fetchAll(PDO::FETCH_COLUMN);
        
        $product['images'] = implode(',', $images);
    }
    
    echo json_encode([
        'success' => true,
        'products' => $products
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
