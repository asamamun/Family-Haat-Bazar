<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../../vendor/autoload.php';
$db = new MysqliDb();

header('Content-Type: application/json');

try {
    // Get today's date
    $today = date('Y-m-d');
    
    // Get today's completed orders
    $todaysOrders = $db->where('DATE(created_at)', $today)
                       ->where('status', 'completed')
                       ->get('orders');
    
    $totalSales = 0;
    $totalOrders = count($todaysOrders);
    $totalItems = 0;
    
    // Calculate totals
    foreach ($todaysOrders as $order) {
        $totalSales += $order['total_amount'];
        
        // Get items count for this order
        $orderItems = $db->where('order_id', $order['id'])->get('order_items');
        foreach ($orderItems as $item) {
            $totalItems += $item['quantity'];
        }
    }
    
    // Calculate average transaction
    $avgTransaction = $totalOrders > 0 ? $totalSales / $totalOrders : 0;
    
    $response = [
        'total_sales' => $totalSales,
        'total_orders' => $totalOrders,
        'avg_transaction' => $avgTransaction,
        'items_sold' => $totalItems
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    echo json_encode([
        'total_sales' => 0,
        'total_orders' => 0,
        'avg_transaction' => 0,
        'items_sold' => 0,
        'error' => $e->getMessage()
    ]);
}

$db->disconnect();
?>