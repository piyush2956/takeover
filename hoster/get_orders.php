<?php
header('Content-Type: application/json');
require_once 'db.php';

try {
    $query = "SELECT 
                o.id as order_id,
                o.created_at as order_date,
                o.total_amount,
                o.status,
                CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                COUNT(oi.id) as total_items
              FROM orders o
              LEFT JOIN customers c ON o.customer_id = c.id
              LEFT JOIN order_items oi ON o.id = oi.order_id";

    $whereConditions = [];
    $params = [];

    if (!empty($_GET['status'])) {
        $whereConditions[] = "o.status = :status";
        $params[':status'] = strtolower($_GET['status']);
    }

    if (!empty($_GET['date'])) {
        $whereConditions[] = "DATE(o.created_at) = :date";
        $params[':date'] = $_GET['date'];
    }

    if (!empty($_GET['search'])) {
        $whereConditions[] = "(c.first_name LIKE :search OR c.last_name LIKE :search OR o.id LIKE :search)";
        $params[':search'] = "%" . $_GET['search'] . "%";
    }

    if (!empty($whereConditions)) {
        $query .= " WHERE " . implode(" AND ", $whereConditions);
    }

    $query .= " GROUP BY o.id ORDER BY o.created_at DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format the data for frontend
    $formattedOrders = array_map(function($order) {
        return [
            'order_id' => $order['order_id'],
            'customer_name' => $order['customer_name'],
            'order_date' => $order['order_date'],
            'total_amount' => number_format($order['total_amount'], 2),
            'status' => ucfirst($order['status']),
            'total_items' => $order['total_items']
        ];
    }, $orders);

    echo json_encode([
        'success' => true,
        'data' => $formattedOrders
    ]);

} catch(PDOException $e) {
    error_log("Error fetching orders: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch orders'
    ]);
}
?>
