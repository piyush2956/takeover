<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Product ID is required']);
    exit;
}

$product_id = intval($_GET['id']);

try {
    // Get product details
    $stmt = $conn->prepare("
        SELECT p.*,
               GROUP_CONCAT(DISTINCT ps.size) as sizes,
               GROUP_CONCAT(DISTINCT pc.color) as colors,
               GROUP_CONCAT(DISTINCT pi.image_path) as images,
               COALESCE(SUM(inventory.stock), 0) as total_stock
        FROM products p
        LEFT JOIN product_sizes ps ON p.id = ps.product_id
        LEFT JOIN product_colors pc ON p.id = pc.product_id
        LEFT JOIN product_images pi ON p.id = pi.product_id
        LEFT JOIN product_inventory inventory ON p.id = inventory.product_id
        WHERE p.id = ?
        GROUP BY p.id
    ");

    $stmt->bind_param("i", $product_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Query execution failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    if (!$product) {
        http_response_code(404);
        echo json_encode(['error' => 'Product not found']);
        exit;
    }

    // Format the response
    $response = [
        'id' => $product['id'],
        'name' => $product['name'],
        'description' => $product['description'],
        'price' => floatval($product['price']),
        'discount' => intval($product['discount']),
        'stock' => intval($product['total_stock']),
        'sizes' => $product['sizes'] ? explode(',', $product['sizes']) : [],
        'colors' => $product['colors'] ? explode(',', $product['colors']) : [],
        'images' => array_map(function($path) {
            return '../hoster/' . ltrim($path, '/');
        }, explode(',', $product['images'])),
        'category' => $product['category'],
        'specifications' => [
            'material' => $product['material'] ?? null,
            'brand' => $product['brand'] ?? null,
            'model' => $product['model'] ?? null
        ]
    ];

    // Calculate discounted price if applicable
    if ($response['discount'] > 0) {
        $response['discounted_price'] = round($response['price'] * (1 - $response['discount']/100), 2);
    }

    // Get size-specific stock levels
    $stmt = $conn->prepare("
        SELECT size, color, stock
        FROM product_inventory
        WHERE product_id = ?
    ");
    
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $inventory_result = $stmt->get_result();
    
    $response['inventory'] = [];
    while ($row = $inventory_result->fetch_assoc()) {
        $response['inventory'][] = [
            'size' => $row['size'],
            'color' => $row['color'],
            'stock' => intval($row['stock'])
        ];
    }

    echo json_encode($response);

} catch (Exception $e) {
    error_log("Error fetching product details: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal server error',
        'message' => 'An error occurred while fetching product details'
    ]);
}

$stmt->close();
$conn->close();
