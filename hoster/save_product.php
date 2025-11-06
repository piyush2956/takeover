<?php
require_once 'DatabaseConfig.php';

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $productData = json_decode($_POST['productData'], true);
    $db = DatabaseConfig::getInstance();
    
    $db->query("START TRANSACTION");

    $isUpdate = isset($productData['id']);
    
    if ($isUpdate) {
        // Get old images before deletion
        $oldImages = $db->query(
            "SELECT image_path FROM product_images WHERE product_id = ?", 
            [$productData['id']]
        )->fetchAll(PDO::FETCH_COLUMN);

        // Delete old data in correct order
        $db->query("DELETE FROM product_variants WHERE product_id = ?", [$productData['id']]);
        $db->query("DELETE FROM product_images WHERE product_id = ?", [$productData['id']]);
        $db->query("DELETE FROM products WHERE id = ?", [$productData['id']]);

        // Clean up old image files that aren't being kept
        if (!empty($oldImages)) {
            $existingImages = isset($productData['existingImages']) ? 
                            explode(',', $productData['existingImages']) : [];
            
            foreach ($oldImages as $oldImage) {
                if (!in_array($oldImage, $existingImages)) {
                    $fullPath = '../hoster/' . $oldImage;
                    if (file_exists($fullPath)) {
                        unlink($fullPath);
                    }
                }
            }
        }
    }

    // Insert basic product info
    $db->query(
        "INSERT INTO products (name, price, discount, description, category, shipping_type, shipping_price) 
         VALUES (:name, :price, :discount, :description, :category, :shipping_type, :shipping_price)",
        [
            'name' => $productData['name'],
            'price' => $productData['price'],
            'discount' => $productData['discount'] ?? 0,
            'description' => $productData['description'] ?? '',
            'category' => $productData['category'],
            'shipping_type' => $productData['shipping']['type'],
            'shipping_price' => $productData['shipping']['price']
        ]
    );
    
    $productId = $db->getConnection()->lastInsertId();

    // Handle existing images for updates
    if ($isUpdate && isset($productData['existingImages'])) {
        foreach (explode(',', $productData['existingImages']) as $imagePath) {
            if (!empty($imagePath)) {
                $db->query(
                    "INSERT INTO product_images (product_id, image_path) VALUES (?, ?)",
                    [$productId, $imagePath]
                );
            }
        }
    }

    // Handle new images
    if (isset($_FILES['images'])) {
        $uploadDir = '../hoster/uploads/products/';
        @mkdir($uploadDir, 0777, true);

        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            if (!empty($tmp_name)) {
                $fileName = time() . '_' . basename($_FILES['images']['name'][$key]);
                $filePath = $uploadDir . $fileName;
                
                if (move_uploaded_file($tmp_name, $filePath)) {
                    $db->query(
                        "INSERT INTO product_images (product_id, image_path) VALUES (?, ?)",
                        [$productId, 'uploads/products/' . $fileName]
                    );
                }
            }
        }
    }

    // Insert variants with stock
    foreach ($productData['variants'] as $variant) {
        if (empty($variant['size']) || empty($variant['color']) || !isset($variant['stock'])) {
            continue; // Skip invalid variants instead of throwing error
        }

        $db->query(
            "INSERT INTO product_variants (product_id, size, color, stock) 
             VALUES (:product_id, :size, :color, :stock)",
            [
                'product_id' => $productId,
                'size' => $variant['size'],
                'color' => $variant['color'],
                'stock' => max(0, intval($variant['stock']))
            ]
        );
    }

    $db->query("COMMIT");
    echo json_encode([
        'success' => true, 
        'message' => $isUpdate ? 'Product updated successfully' : 'Product saved successfully',
        'productId' => $productId
    ]);

} catch (Exception $e) {
    if (isset($db)) {
        $db->query("ROLLBACK");
    }
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?>
