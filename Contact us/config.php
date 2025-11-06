<?php
$host = 'localhost'; // Your Hostinger MySQL host
$dbname = 'u330854413_abde'; // The database name you created in Hostinger
$username = 'u330854413_contactus'; // Your Hostinger MySQL username
$password = 'Tree#45Green'; // Your Hostinger MySQL password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
