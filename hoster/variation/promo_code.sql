-- Create database if not exists
CREATE DATABASE IF NOT EXISTS config_db;

-- Use the database
USE config_db;

-- Create promo_codes table
CREATE TABLE IF NOT EXISTS promo_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) UNIQUE NOT NULL,
    discount_percent INT NOT NULL,
    valid_until DATE NOT NULL,
    usage_limit INT NOT NULL,
    times_used INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'expired', 'depleted') DEFAULT 'active',
    created_by VARCHAR(50),
    last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_status (status),
    INDEX idx_valid_until (valid_until)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create promo_code_usage table for tracking
CREATE TABLE IF NOT EXISTS promo_code_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    promo_code_id INT NOT NULL,
    user_id INT,
    order_id VARCHAR(50),
    used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    amount_saved DECIMAL(10,2),
    FOREIGN KEY (promo_code_id) REFERENCES promo_codes(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert some sample promo codes
INSERT INTO promo_codes (code, discount_percent, valid_until, usage_limit) VALUES
('WELCOME2023', 10, DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY), 100),
('SUMMER23', 15, DATE_ADD(CURRENT_DATE, INTERVAL 60 DAY), 50),
('SPECIAL50', 50, DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY), 10);

-- Create view for active promo codes
CREATE OR REPLACE VIEW active_promo_codes AS
SELECT 
    code,
    discount_percent,
    valid_until,
    usage_limit,
    times_used,
    (usage_limit - times_used) as remaining_uses,
    created_at
FROM 
    promo_codes
WHERE 
    status = 'active'
    AND valid_until >= CURRENT_DATE
    AND times_used < usage_limit;

-- Create procedure to generate new promo code
DELIMITER //
CREATE PROCEDURE generate_promo_code(
    IN prefix VARCHAR(10),
    IN discount INT,
    IN days_valid INT,
    IN max_uses INT
)
BEGIN
    DECLARE new_code VARCHAR(20);
    DECLARE code_exists INT;
    
    generate_loop: LOOP
        -- Generate random code
        SET new_code = CONCAT(
            prefix, 
            '-',
            UPPER(
                SUBSTRING(MD5(RAND()) FROM 1 FOR 6)
            )
        );
        
        -- Check if code already exists
        SELECT COUNT(*) INTO code_exists 
        FROM promo_codes 
        WHERE code = new_code;
        
        IF code_exists = 0 THEN
            -- Insert new code
            INSERT INTO promo_codes (
                code, 
                discount_percent, 
                valid_until, 
                usage_limit
            ) VALUES (
                new_code,
                discount,
                DATE_ADD(CURRENT_DATE, INTERVAL days_valid DAY),
                max_uses
            );
            LEAVE generate_loop;
        END IF;
    END LOOP generate_loop;
    
    -- Return the generated code
    SELECT new_code AS generated_code;
END //
DELIMITER ;

-- Create trigger to update status automatically
DELIMITER //
CREATE TRIGGER update_promo_status
BEFORE UPDATE ON promo_codes
FOR EACH ROW
BEGIN
    IF NEW.times_used >= NEW.usage_limit THEN
        SET NEW.status = 'depleted';
    ELSEIF NEW.valid_until < CURRENT_DATE THEN
        SET NEW.status = 'expired';
    END IF;
END //
DELIMITER ;

-- Grant permissions (adjust according to your needs)
GRANT SELECT, INSERT, UPDATE ON config_db.promo_codes TO 'your_app_user'@'localhost';
GRANT SELECT, INSERT ON config_db.promo_code_usage TO 'your_app_user'@'localhost';
