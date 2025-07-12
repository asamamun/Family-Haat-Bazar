<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require __DIR__ . '/../vendor/autoload.php';
    $db = new MysqliDb();

    $id = $_POST['id'];
    $status = $_POST['status'];

    if ($db->where('id', $id)->update('orders', ['status' => $status])) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false]);
    }
}
