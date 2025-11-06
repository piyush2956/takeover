<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

echo "<h2>Database Connection Test</h2>";
echo "<pre>";

// Test database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "Database connection successful!\n\n";
}

// Check total products
$sql = "SELECT COUNT(*) as total FROM products";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
echo "Total products in database: " . $row['total'] . "\n\n";

// Check products by category
$sql = "SELECT category, COUNT(*) as count FROM products GROUP BY category";
$result = $conn->query($sql);
echo "Products by category:\n";
while ($row = $result->fetch_assoc()) {
    echo $row['category'] . ": " . $row['count'] . " products\n";
}

// Check product images
echo "\nProduct images:\n";
$sql = "SELECT p.id, p.name, pi.image_path 
        FROM products p 
        LEFT JOIN product_images pi ON p.id = pi.product_id 
        WHERE pi.is_primary = 1";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    echo "Product ID {$row['id']} ({$row['name']}): " . ($row['image_path'] ?? 'No image') . "\n";
}

// Check image paths and accessibility
echo "\nImage Path Check:\n";
$sql = "SELECT p.id, p.name, pi.image_path 
        FROM products p 
        LEFT JOIN product_images pi ON p.id = pi.product_id 
        WHERE pi.is_primary = 1";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $image_path = $row['image_path'];
    echo "Product {$row['id']} ({$row['name']}):\n";
    echo "- Path: " . ($image_path ?? 'No image') . "\n";
    
    if ($image_path) {
        $full_path = $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($image_path, '/');
        echo "- Full path: {$full_path}\n";
        echo "- Exists: " . (file_exists($full_path) ? "Yes" : "No") . "\n";
        echo "- Readable: " . (is_readable($full_path) ? "Yes" : "No") . "\n";
    }
    echo "\n";
}

$conn->close();
echo "</pre>";
?>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

echo "<h2>Database Check</h2>";

// Check categories
$sql = "SELECT DISTINCT category FROM products";
$result = $conn->query($sql);
echo "<h3>Available Categories:</h3>";
echo "<ul>";
while($row = $result->fetch_assoc()) {
    echo "<li>{$row['category']}</li>";
}
echo "</ul>";

// Check some products
$sql = "SELECT id, name, category FROM products LIMIT 5";
$result = $conn->query($sql);
echo "<h3>Sample Products:</h3>";
echo "<ul>";
while($row = $result->fetch_assoc()) {
    echo "<li>ID: {$row['id']}, Name: {$row['name']}, Category: {$row['category']}</li>";
}
echo "</ul>";

// Check images
$sql = "SELECT p.id, p.name, pi.image_path 
        FROM products p 
        LEFT JOIN product_images pi ON p.id = pi.product_id 
        LIMIT 5";
$result = $conn->query($sql);
echo "<h3>Sample Images:</h3>";
echo "<ul>";
while($row = $result->fetch_assoc()) {
    echo "<li>ID: {$row['id']}, Name: {$row['name']}, Image: {$row['image_path']}</li>";
}
echo "</ul>";

$conn->close();
?>
