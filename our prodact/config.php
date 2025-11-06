<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'u330854413_product');
define('DB_PASS', 'Sky!23Blue');
define('DB_NAME', 'u330854413_pro');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>