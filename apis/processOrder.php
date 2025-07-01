<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/../vendor/autoload.php';
use App\db;

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    $db = new MysqliDb();

    // Validate required fields
    $required_fields = [
        'billing_first_name', 'billing_last_name', 'billing_address_line_1', 'billing_city',
        'billing_postal_code', 'billing_country', 'billing_phone', 'shipping_first_name',
        'shipping_last_name', 'shipping_address_line_1', 'shipping_city', 'shipping_postal_code',
        'shipping_country', 'shipping_phone', 'payment_method', 'totalPrice', 'items',
        'discount_amount', 'tax_amount', 'grandTotal'
    ];

    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || (is_string($_POST[$field]) && empty($_POST[$field]) && $_POST[$field] !== '0.00')) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Validate items
    $items = json_decode(json_encode($_POST['items']), true);
    if (empty($items)) {
        throw new Exception("Cart is empty");
    }

    $db->startTransaction();

    // Generate unique order number
    $order_number = 'ORD-' . time() . '-' . rand(1000, 9999);

    // Insert into orders table
    $order_data = [
        'order_number' => $order_number,
        'user_id' => isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null,
        'order_type' => 'online',
        'status' => 'pending',
        'payment_status' => $_POST['payment_method'] == 'cash' ? 'pending' : 'pending',
        'payment_method' => $_POST['payment_method'],
        'transaction_id' => isset($_POST['transaction_id']) ? $_POST['transaction_id'] : null,
        'subtotal' => (float)$_POST['totalPrice'],
        'discount_amount' => (float)$_POST['discount_amount'],
        'tax_amount' => (float)$_POST['tax_amount'],
        'shipping_amount' => 0.00,
        'total_amount' => (float)$_POST['grandTotal'],
        'currency' => 'BDT',
        'notes' => isset($_POST['notes']) ? $_POST['notes'] : null,
        'billing_first_name' => $_POST['billing_first_name'],
        'billing_last_name' => $_POST['billing_last_name'],
        'billing_company' => isset($_POST['billing_company']) ? $_POST['billing_company'] : null,
        'billing_address_line_1' => $_POST['billing_address_line_1'],
        'billing_address_line_2' => isset($_POST['billing_address_line_2']) ? $_POST['billing_address_line_2'] : null,
        'billing_city' => $_POST['billing_city'],
        'billing_state' => isset($_POST['billing_state']) ? $_POST['billing_state'] : null,
        'billing_postal_code' => $_POST['billing_postal_code'],
        'billing_country' => $_POST['billing_country'],
        'billing_phone' => $_POST['billing_phone'],
        'shipping_first_name' => $_POST['shipping_first_name'],
        'shipping_last_name' => $_POST['shipping_last_name'],
        'shipping_company' => isset($_POST['shipping_company']) ? $_POST['shipping_company'] : null,
        'shipping_address_line_1' => $_POST['shipping_address_line_1'],
        'shipping_address_line_2' => isset($_POST['shipping_address_line_2']) ? $_POST['shipping_address_line_2'] : null,
        'shipping_city' => $_POST['shipping_city'],
        'shipping_state' => isset($_POST['shipping_state']) ? $_POST['shipping_state'] : null,
        'shipping_postal_code' => $_POST['shipping_postal_code'],
        'shipping_country' => $_POST['shipping_country'],
        'shipping_phone' => $_POST['shipping_phone'],
        'processed_by' => null,
        'processed_at' => null,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];

    $order_id = $db->insert('orders', $order_data);
    if (!$order_id) {
        throw new Exception('Failed to create order');
    }

    // Insert order items
    foreach ($items as $item) {
        if (!isset($item['id']) || !isset($item['name']) || !isset($item['quantity']) || !isset($item['price'])) {
            throw new Exception("Invalid item data for: {$item['name']}");
        }

        $product = $db->where('id', $item['id'])->getOne('products');
        if (!$product) {
            throw new Exception("Product not found: {$item['name']}");
        }

        $item_data = [
            'order_id' => $order_id,
            'product_id' => (int)$item['id'],
            'product_name' => $product['name'],
            'product_sku' => $product['sku'],
            'quantity' => (int)$item['quantity'],
            'unit_price' => (float)$item['price'],
            'total_price' => (float)($item['quantity'] * $item['price']),
            'created_at' => date('Y-m-d H:i:s')
        ];

        if (!$db->insert('order_items', $item_data)) {
            throw new Exception("Failed to add item: {$item['name']}");
        }

        // Update stock using stored procedure
/*         $db->rawQuery('CALL ProcessStockSale(?, ?, ?, ?)', [
            (int)$item['id'],
            (int)$item['quantity'],
            $order_id,
            isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null
        ]); */
    }

    // Insert payment transaction
    $payment_data = [
        'order_id' => $order_id,
        'transaction_id' => isset($_POST['transaction_id']) ? $_POST['transaction_id'] : $order_number,
        'payment_method' => $_POST['payment_method'],
        'amount' => (float)$_POST['grandTotal'],
        'status' => 'pending',
        'gateway_response' => null,
        'processed_at' => null,
        'created_at' => date('Y-m-d H:i:s')
    ];

    if (!$db->insert('payment_transactions', $payment_data)) {
        throw new Exception('Failed to record payment transaction');
    }

    $db->commit();

    $response['success'] = true;
    $response['message'] = 'Order placed successfully';
    $response['order_number'] = $order_number;
} catch (Exception $e) {
    $db->rollback();
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
$db->disconnect();
?>