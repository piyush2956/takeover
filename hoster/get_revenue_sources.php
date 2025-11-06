<?php
require_once 'db.php';
header('Content-Type: application/json');

try {
    // For demonstration, using simulated data that's more interesting
    // In production, you would get this from your actual orders table
    $revenue_sources = [
        'Online Store' => 35,
        'Mobile App' => 25,
        'Social Media' => 20,
        'Marketplace' => 15,
        'Retail' => 5
    ];

    echo json_encode([
        'success' => true,
        'sources' => array_values($revenue_sources),
        'labels' => array_keys($revenue_sources)
    ]);

} catch (Exception $e) {
    error_log("Error in get_revenue_sources.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch revenue sources data'
    ]);
}
?>
