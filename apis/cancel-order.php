<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../vendor/autoload.php';
$db = new MysqliDb();

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    // Check if user is logged in
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        throw new Exception('Please log in to cancel orders.');
    }

    if (!isset($_POST['order_id']) || !is_numeric($_POST['order_id'])) {
        throw new Exception('Invalid order ID.');
    }

    $orderId = (int)$_POST['order_id'];
    $userId = $_SESSION['userid'];

    // Get order details (ensure user owns this order)
    $order = $db->where('id', $orderId)
                ->where('user_id', $userId)
                ->getOne('orders');

    if (!$order) {
        throw new Exception('Order not found or access denied.');
    }

    // Check if order can be cancelled
    if ($order['status'] !== 'pending') {
        throw new Exception('Only pending orders can be cancelled.');
    }

    $db->startTransaction();

    // Update order status to cancelled
    $updateData = [
        'status' => 'cancelled',
        'updated_at' => date('Y-m-d H:i:s')
    ];

    $updated = $db->where('id', $orderId)->update('orders', $updateData);
    
    if (!$updated) {
        throw new Exception('Failed to cancel order.');
    }

    // Restore stock quantities for cancelled items
    $orderItems = $db->where('order_id', $orderId)->get('order_items');
    
    foreach ($orderItems as $item) {
        // Get current product stock
        $product = $db->where('id', $item['product_id'])->getOne('products', 'stock_quantity');
        
        if ($product) {
            $newStock = $product['stock_quantity'] + $item['quantity'];
            $db->where('id', $item['product_id'])->update('products', ['stock_quantity' => $newStock]);
        }
    }

    // Update payment status to cancelled
    $db->where('order_id', $orderId)->update('payment_transactions', [
        'status' => 'cancelled',
        'processed_at' => date('Y-m-d H:i:s')
    ]);

    $db->commit();

    $response['success'] = true;
    $response['message'] = 'Order cancelled successfully.';

} catch (Exception $e) {
    if ($db) {
        $db->rollback();
    }
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
if ($db) {
    $db->disconnect();
}
?>