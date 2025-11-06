<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'u330854413_abde');  // Replace with your actual database name
define('DB_USER', 'u330854413_contactus');       // Replace with your database username
define('DB_PASS', 'Tree#45Green');       // Replace with your database password

// Email configuration (for message replies)
define('SMTP_HOST', 'smtp.gmail.com');    // Replace with your SMTP server
define('SMTP_PORT', 587);
define('SMTP_USER', 'abdollahbagueri02@gmail.com');    // Replace with your email
define('SMTP_PASS', 'Sky!23Blue');   // Replace with your email password

// Site configuration
define('SITE_URL', 'https://greenyellow-dotterel-223863.hostingersite.com'); // Replace with your site URL
define('ADMIN_EMAIL', 'abdollahbagueri02@gmail.com');    // Replace with admin email

// Security configuration
define('HASH_SALT', 'your-unique-salt-string');   // Replace with random string
define('SESSION_TIMEOUT', 3600);                  // Session timeout in seconds

// Error reporting (set to 0 in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Time zone setting
date_default_timezone_set('Africa/Casablanca');   // Set to your timezone

// Define allowed file types for uploads
define('ALLOWED_FILE_TYPES', [
    'jpg',
    'jpeg',
    'png',
    'gif',
    'pdf',
    'doc',
    'docx'
]);

// Maximum file upload size (in bytes) - 5MB
define('MAX_FILE_SIZE', 5 * 1024 * 1024);

// Database charset
define('DB_CHARSET', 'utf8mb4');

// API Keys (if needed)
define('API_KEY', 'your-api-key');        // Replace with your API key

// Cache settings
define('CACHE_ENABLED', true);
define('CACHE_DURATION', 3600);           // Cache duration in seconds

// Version control
define('APP_VERSION', '1.0.0');

// Directory paths
define('ROOT_PATH', dirname(__FILE__));
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('LOG_PATH', ROOT_PATH . '/logs');

// Create required directories if they don't exist
if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
}
if (!file_exists(LOG_PATH)) {
    mkdir(LOG_PATH, 0755, true);
}

// Function to log errors
function logError($message) {
    $logFile = LOG_PATH . '/error_log_' . date('Y-m-d') . '.txt';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message\n";
    error_log($logMessage, 3, $logFile);
}

// Function to sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}
?>
