<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/../vendor/autoload.php';
$db = new MysqliDb ();
header('Content-Type: application/json');
// error_log("DATA: " . print_r($_POST, true));
/*
(
    [billing_first_name] => sdf
    [billing_last_name] => asdf
    [billing_company] => sadf
    [billing_address_line_1] => asdf
    [billing_address_line_2] => asdf
    [billing_city] => asdf
    [billing_state] => asdf
    [billing_postal_code] => asdf
    [billing_country] => asdf
    [billing_phone] => asdf
    [shipping_first_name] => sdf
    [shipping_last_name] => asdf
    [shipping_company] => sadf
    [shipping_address_line_1] => asdf
    [shipping_address_line_2] => asdf
    [shipping_city] => asdf
    [shipping_state] => asdf
    [shipping_postal_code] => asdf
    [shipping_country] => asdf
    [shipping_phone] => asdf
    [payment_method] => bkash
    [transaction_id] => asdf
    [notes] => asdf
    [items] => Array
        (
            [0] => Array
                (
                    [id] => 5
                    [name] => Smartphone XYZ
                    [price] => 699.99
                    [quantity] => 2
                    [image] => 
                )

            [1] => Array
                (
                    [id] => 18
                    [name] => Laptop 15
                    [price] => 999.99
                    [quantity] => 1
                    [image] => 
                )

            [2] => Array
                (
                    [id] => 19
                    [name] => Diaper Bag
                    [price] => 39.99
                    [quantity] => 1
                    [image] => 
                )

        )

    [totalItems] => 4
    [totalPrice] => 2439.96
    [discount_amount] => 0.00
    [tax_amount] => 122.00
    [grandTotal] => 2561.96
)
*/
// $orderId = $db->insert('orders', $_POST);
//TODO: insert information in order table, get the insert_id which is the order_id in order to insert in order_items table

echo  json_encode(['success' => true, 'message' => 'Order placed successfully']);