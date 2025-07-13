<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$userid = isset($_SESSION['userid']) ? (int)$_SESSION['userid'] : null;
require __DIR__ . '/../vendor/autoload.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    $db = new MysqliDb();


    // Validate items
    $items = json_decode(json_encode($_POST['items']), true);
    if (empty($items)) {
        throw new Exception("Cart is empty");
    }

    $db->startTransaction();

    // Generate unique order number
    $order_number = 'POS-' . time() . '-' . rand(1000, 9999);

    // Insert into orders table
    /*
{
    "subtotal": 361.97,
    "discount_amount": 0,
    "tax_amount": 28.96,
    "shipping_amount": 0,
    "total_amount": 390.93,
    "notes": null,
    "items": [
        {
            "id": "1",
            "name": "Cotton T-Shirt",
            "price": 15.99,
            "quantity": 1
        },
        {
            "id": "2",
            "name": "Denim Jacket",
            "price": 45.99,
            "quantity": 1
        },
        {
            "id": "3",
            "name": "Electric Scooter",
            "price": 299.99,
            "quantity": 1
        }
    ]
}
    */
    $order_data = [
        'order_number' => $order_number,
        'user_id' => isset($_SESSION['userid']) ? (int)$_SESSION['userid'] : null,
        'order_type' => 'pos',
        'status' => 'delivered',
        'payment_status' => 'paid',
        'payment_method' => 'cash',
        'transaction_id' =>  null,
        'subtotal' => (float)$_POST['subtotal'],
        'discount_amount' => (float)$_POST['discount_amount'],
        'tax_amount' => (float)$_POST['tax_amount'],
        'shipping_amount' => (float)$_POST['shipping_amount'],
        'total_amount' => (float)$_POST['total_amount'],
        'currency' => 'BDT',
        'notes' => isset($_POST['notes']) ? $_POST['notes'] : null,
        'billing_first_name' => '',
        'billing_last_name' => '',
        'billing_company' => null,
        'billing_address_line_1' => '',
        'billing_address_line_2' => null,
        'billing_city' => '',
        'billing_state' => null,
        'billing_postal_code' => '',
        'billing_country' => '',
        'billing_phone' => '',
        'shipping_first_name' => '',
        'shipping_last_name' => '',
        'shipping_company' => null,
        'shipping_address_line_1' => '',
        'shipping_address_line_2' => null,
        'shipping_city' => '',
        'shipping_state' => null,
        'shipping_postal_code' => '',
        'shipping_country' => '',
        'shipping_phone' => '',
        'processed_by' => $userid ,
        'processed_at' => date('Y-m-d H:i:s'),
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => null
    ];
    // error_log(json_encode($order_data));
    /*
    {
    "order_number":"POS-1751268662-8158",
    "user_id":7,
    "order_type":"pos",
    "status":"delivered","payment_status":"paid","payment_method":"cash","transaction_id":null,"subtotal":7503.94,"discount_amount":0,"tax_amount":600.32,"shipping_amount":0,"total_amount":8104.26,"currency":"BDT","notes":"","billing_first_name":"","billing_last_name":"","billing_company":null,"billing_address_line_1":"","billing_address_line_2":null,"billing_city":"","billing_state":null,"billing_postal_code":"","billing_country":"","billing_phone":"","shipping_first_name":"","shipping_last_name":"","shipping_company":null,"shipping_address_line_1":"","shipping_address_line_2":null,"shipping_city":"","shipping_state":null,"shipping_postal_code":"","shipping_country":"","shipping_phone":"","processed_by":7,"processed_at":"2025-06-30 09:31:02","created_at":"2025-06-30 09:31:02","updated_at":null}
    */

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
        error_log("Array:".json_encode([
            (int)$item['id'],
            (int)$item['quantity'],
            $order_id,
            isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null
        ]));

        // Deduct stock quantity
        $current_stock = $product['stock_quantity'];
        $new_stock = $current_stock - (int)$item['quantity'];
        $db->where('id', (int)$item['id'])->update('products', ['stock_quantity' => $new_stock]);
    }

    // Insert payment transaction
    $payment_data = [
        'order_id' => $order_id,
        'transaction_id' => isset($_POST['transaction_id']) ? $_POST['transaction_id'] : $order_number,
        'payment_method' => 'cash',
        'amount' => (float)$_POST['total_amount'],
        'status' => 'success',
        'gateway_response' => null,
        'processed_at' => date('Y-m-d H:i:s'),
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