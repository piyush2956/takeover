-- Drop existing tables if they exist
DROP TABLE IF EXISTS product_variants;
DROP TABLE IF EXISTS product_images;
DROP TABLE IF EXISTS products;

-- Create products table
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    discount INT DEFAULT 0,
    description TEXT,
    category VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    CHECK (discount >= 0 AND discount <= 100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create product_images table
CREATE TABLE product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product_images (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create product_variants table
CREATE TABLE product_variants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    size VARCHAR(10) NOT NULL,
    color VARCHAR(50) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_product_variant (product_id, size, color),
    INDEX idx_product_variants (product_id),
    CHECK (stock >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data
INSERT INTO products (name, price, discount, description, category) VALUES
('Summer T-Shirt', 299.99, 10, 'Comfortable cotton summer t-shirt', 'men'),
('Classic Jeans', 599.99, 0, 'Classic blue denim jeans', 'women'),
('Kids Sweater', 249.99, 15, 'Warm winter sweater for kids', 'kids');

-- Insert sample variants
INSERT INTO product_variants (product_id, size, color, stock) VALUES
(1, 'M', 'Blue', 10),
(1, 'L', 'Blue', 15),
(1, 'M', 'Red', 8),
(1, 'L', 'Red', 12),
(2, 'S', 'Blue', 5),
(2, 'M', 'Blue', 8),
(3, 'S', 'Green', 10),
(3, 'M', 'Green', 10);

-- Insert sample images
INSERT INTO product_images (product_id, image_path, is_primary) VALUES
(1, 'uploads/products/tshirt-blue.jpg', TRUE),
(1, 'uploads/products/tshirt-red.jpg', FALSE),
(2, 'uploads/products/jeans-blue.jpg', TRUE),
(3, 'uploads/products/kids-sweater.jpg', TRUE);
