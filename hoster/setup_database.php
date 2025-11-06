<?php
require_once 'DatabaseConfig.php';

try {
    $db = DatabaseConfig::getInstance();
    
    // Drop existing tables if they exist
    $db->query("DROP TABLE IF EXISTS product_variants");
    $db->query("DROP TABLE IF EXISTS product_images");
    $db->query("DROP TABLE IF EXISTS products");

    // Create products table
    $createProductsTable = "CREATE TABLE products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        discount INT DEFAULT 0,
        description TEXT,
        category VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB";
    
    $db->query($createProductsTable);

    // Create product_images table
    $createImagesTable = "CREATE TABLE product_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT,
        image_path VARCHAR(255),
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB";
    
    $db->query($createImagesTable);

    // Create product_variants table
    $createVariantsTable = "CREATE TABLE product_variants (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT,
        size VARCHAR(10),
        color VARCHAR(50),
        stock INT DEFAULT 0,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB";
    
    $db->query($createVariantsTable);

    echo "Database tables created successfully!";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
