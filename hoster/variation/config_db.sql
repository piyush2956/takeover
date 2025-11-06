-- Create the database if it doesn't exist
CREATE DATABASE IF NOT EXISTS config_Db;

-- Use the database
USE config_Db;

-- Create shipping_settings table
CREATE TABLE IF NOT EXISTS shipping_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    threshold_amount DECIMAL(10,2) NOT NULL,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default value if table is empty
INSERT INTO shipping_settings (threshold_amount)
SELECT 1000
WHERE NOT EXISTS (SELECT 1 FROM shipping_settings);

-- Create table for maintenance mode settings
CREATE TABLE IF NOT EXISTS maintenance_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    is_enabled BOOLEAN DEFAULT FALSE,
    message TEXT,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create table for promo codes
CREATE TABLE IF NOT EXISTS promo_codes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE NOT NULL,
    discount_percent DECIMAL(5,2) NOT NULL,
    valid_until DATE NOT NULL,
    usage_limit INT NOT NULL,
    times_used INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_used TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create table for login activity
CREATE TABLE IF NOT EXISTS login_activity (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    device_info VARCHAR(255),
    status ENUM('success', 'failed') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add indexes for better performance
CREATE INDEX idx_promo_code ON promo_codes(code);
CREATE INDEX idx_valid_until ON promo_codes(valid_until);
CREATE INDEX idx_login_time ON login_activity(login_time);

-- Grant permissions (adjust username and password as needed)
GRANT ALL PRIVILEGES ON config_Db.* TO 'root'@'localhost';
FLUSH PRIVILEGES;
