<?php
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=u330854413_log;charset=utf8mb4",
        "u330854413_login",
        "Tree#45Green",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch(PDOException $e) {
    error_log("Connection failed: " . $e->getMessage());
    die("Connection failed. Please try again later.");
}
?>
