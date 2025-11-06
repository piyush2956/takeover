<?php

require_once 'DatabaseConfig.php';

try {
    $db = DatabaseConfig::getInstance();
    
    // Create products table with all necessary fields
    $createProductsTable = "CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        discount INT DEFAULT 0,
        description TEXT,
        category VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $db->query($createProductsTable);

    // Create product_images table
    $createImagesTable = "CREATE TABLE IF NOT EXISTS product_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT,
        image_path VARCHAR(255),
        FOREIGN KEY (product_id) REFERENCES products(id)
    )";
    $db->query($createImagesTable);

    // Create product_variants table
    $createVariantsTable = "CREATE TABLE IF NOT EXISTS product_variants (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT,
        size VARCHAR(10),
        color VARCHAR(50),
        stock INT DEFAULT 0,
        FOREIGN KEY (product_id) REFERENCES products(id)
    )";
    $db->query($createVariantsTable);

    // Example: Insert a product with variants
    $db->query("START TRANSACTION");

    try {
        // Insert main product
        $insertProduct = "INSERT INTO products (name, price, discount, description, category) 
                         VALUES (:name, :price, :discount, :description, :category)";
        
        $db->query($insertProduct, [
            'name' => 'Summer T-Shirt',
            'price' => 299.99,
            'discount' => 10,
            'description' => 'Comfortable cotton summer t-shirt',
            'category' => 'men'
        ]);
        
        $productId = $db->getConnection()->lastInsertId();

        // Insert product variants
        $insertVariant = "INSERT INTO product_variants (product_id, size, color, stock) 
                         VALUES (:product_id, :size, :color, :stock)";
        
        // Example variants
        $variants = [
            ['size' => 'M', 'color' => 'Blue', 'stock' => 10],
            ['size' => 'L', 'color' => 'Blue', 'stock' => 15],
            ['size' => 'M', 'color' => 'Red', 'stock' => 8],
            ['size' => 'L', 'color' => 'Red', 'stock' => 12]
        ];

        foreach ($variants as $variant) {
            $db->query($insertVariant, [
                'product_id' => $productId,
                'size' => $variant['size'],
                'color' => $variant['color'],
                'stock' => $variant['stock']
            ]);
        }

        // Example: Insert product images
        $insertImage = "INSERT INTO product_images (product_id, image_path) VALUES (:product_id, :image_path)";
        $db->query($insertImage, [
            'product_id' => $productId,
            'image_path' => 'images/products/sample-tshirt.jpg'
        ]);

        $db->query("COMMIT");
        echo "Product added successfully with variants and images!<br>";

        // Fetch and display the added product
        $product = $db->query("
            SELECT p.*, GROUP_CONCAT(DISTINCT pv.size) as sizes, 
                   GROUP_CONCAT(DISTINCT pv.color) as colors,
                   GROUP_CONCAT(DISTINCT pi.image_path) as images
            FROM products p
            LEFT JOIN product_variants pv ON p.id = pv.product_id
            LEFT JOIN product_images pi ON p.id = pi.product_id
            WHERE p.id = ?
            GROUP BY p.id
        ", [$productId])->fetch();

        echo "<pre>";
        print_r($product);
        echo "</pre>";

    } catch (Exception $e) {
        $db->query("ROLLBACK");
        throw $e;
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
