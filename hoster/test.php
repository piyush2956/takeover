<?php
require_once 'db.php';
header('Content-Type: application/json');

try {
    // Test database connection
    $pdo->query('SELECT 1');
    
    // Test customers table
    $stmt = $pdo->query('SHOW TABLES LIKE "customers"');
    $tableExists = $stmt->rowCount() > 0;
    
    if (!$tableExists) {
        throw new Exception("Customers table does not exist");
    }
    
    // Test table structure
    $stmt = $pdo->query('DESCRIBE customers');
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo json_encode([
        'success' => true,
        'message' => 'Database connection successful',
        'tables' => ['customers' => $tableExists],
        'columns' => $columns
    ]);
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
