<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/../vendor/autoload.php';

$db = new MysqliDb();

if (isset($_POST['action']) && $_POST['action'] == 'fetch') {
    $draw = $_POST['draw'];
    $start = $_POST['start'];
    $length = $_POST['length'];
    $searchValue = $_POST['search']['value'];
    $orderColumnIndex = $_POST['order'][0]['column'];
    $orderColumnName = $_POST['columns'][$orderColumnIndex]['data'];
    $orderDir = $_POST['order'][0]['dir'];

    // Total records without filtering
    $totalRecords = $db->getValue('orders', "count(*)");

    // Filtered records
    if (!empty($searchValue)) {
        $db->where('order_number', '%' . $searchValue . '%', 'LIKE');
        $db->orWhere('status', '%' . $searchValue . '%', 'LIKE');
        $db->orWhere('payment_status', '%' . $searchValue . '%', 'LIKE');
        $db->orWhere('payment_method', '%' . $searchValue . '%', 'LIKE');
        $db->orWhere('transaction_id', '%' . $searchValue . '%', 'LIKE');
        $db->orWhere('notes', '%' . $searchValue . '%', 'LIKE');
    }
    $filteredRecords = $db->getValue('orders', "count(*)");

    // Fetch data for the current page
    if (!empty($searchValue)) {
        $db->where('order_number', '%' . $searchValue . '%', 'LIKE');
        $db->orWhere('status', '%' . $searchValue . '%', 'LIKE');
        $db->orWhere('payment_status', '%' . $searchValue . '%', 'LIKE');
        $db->orWhere('payment_method', '%' . $searchValue . '%', 'LIKE');
        $db->orWhere('transaction_id', '%' . $searchValue . '%', 'LIKE');
        $db->orWhere('notes', '%' . $searchValue . '%', 'LIKE');
    }

    $db->orderBy($orderColumnName, $orderDir);
    $orders = $db->get('orders', array($start, $length));

    $data = array();
    foreach ($orders as $order) {
        $data[] = array(
            "id" => $order['id'],
            "order_number" => $order['order_number'],
            "order_type" => $order['order_type'],
            "status" => $order['status'],
            "payment_status" => $order['payment_status'],
            "payment_method" => $order['payment_method'],
            "transaction_id" => $order['transaction_id'],
            "subtotal" => $order['subtotal'],
            "discount_amount" => $order['discount_amount'],
            "tax_amount" => $order['tax_amount'],
            "total_amount" => $order['total_amount'],
            "currency" => $order['currency'],
            "notes" => $order['notes'],
            "created_at" => $order['created_at'],
            "updated_at" => $order['updated_at']
        );
    }

    $response = array(
        "draw" => intval($draw),
        "recordsTotal" => $totalRecords,
        "recordsFiltered" => $filteredRecords,
        "data" => $data
    );

    echo json_encode($response);
}

$db->disconnect();
?>