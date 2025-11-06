<?php
require_once('config/db.php');

try {
    $stmt = $pdo->query("SELECT * FROM admin_users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Database connection successful!\n";
    echo "Number of admin users: " . count($users) . "\n";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
