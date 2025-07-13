<?php
require __DIR__ . '/../vendor/autoload.php';

$dbConfig = include __DIR__ . '/../config/idb.php';
$db = new MysqliDb(
    $dbConfig['host'],
    $dbConfig['username'],
    $dbConfig['password'],
    $dbConfig['db']
);

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'fetchSales':
        fetchSales($db);
        break;
    case 'fetchProducts':
        fetchProducts($db);
        break;
    case 'fetchUsers':
        fetchUsers($db);
        break;
    case 'fetchProductDaywiseSales':
        fetchProductDaywiseSales($db);
        break;
    case 'fetchUserDaywiseSales':
        fetchUserDaywiseSales($db);
        break;
    case 'fetchBestSellers':
        fetchBestSellers($db);
        break;
    case 'fetchLowInventory':
        fetchLowInventory($db);
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}

function fetchSales($db) {
    $draw = $_POST['draw'];
    $start = $_POST['start'];
    $length = $_POST['length'];
    $searchValue = (string)($_POST['search']['value'] ?? '');
    $orderColumnIndex = $_POST['order'][0]['column'];
    $orderColumnName = $_POST['columns'][$orderColumnIndex]['data'];
    $orderDir = $_POST['order'][0]['dir'];
    $startDate = $_POST['startDate'] ?? null;
    $endDate = $_POST['endDate'] ?? null;

    
    $totalRecords = $db->getValue('orders', 'count(*)');

    if (!empty($searchValue)) {
        $db->where('order_number', '%' . $searchValue . '%', 'LIKE');
        $db->orWhere('status', '%' . $searchValue . '%', 'LIKE');
        $db->orWhere('payment_status', '%' . $searchValue . '%', 'LIKE');
    }

    if ($startDate && $endDate) {
        $db->where('created_at', array($startDate, $endDate), 'BETWEEN');
    }

    $db->orderBy($orderColumnName, $orderDir);
    $db->pageLimit = $length;
    $data = $db->arraybuilder()->paginate('orders', ($start / $length) + 1);

    $totalFilteredRecords = $db->totalCount;

    echo json_encode([
        "draw" => intval($draw),
        "recordsTotal" => intval($totalRecords),
        "recordsFiltered" => intval($totalFilteredRecords),
        "data" => $data
    ]);
}

function fetchProducts($db) {
    $draw = $_POST['draw'];
    $start = $_POST['start'];
    $length = $_POST['length'];
    $searchValue = $_POST['searchValue'] ?? '';
    $orderColumnIndex = $_POST['order'][0]['column'];
    $orderColumnName = $_POST['columns'][$orderColumnIndex]['data'];
    $orderDir = $_POST['order'][0]['dir'];

    
    $totalRecords = $db->getValue('products', 'count(*)');

    if (!empty($searchValue)) {
        $db->where('name', '%' . $searchValue . '%', 'LIKE');
        $db->orWhere('sku', '%' . $searchValue . '%', 'LIKE');
    }

    $db->orderBy($orderColumnName, $orderDir);
    $db->pageLimit = $length;
    $data = $db->arraybuilder()->paginate('products', ($start / $length) + 1);

    $totalFilteredRecords = $db->totalCount;

    echo json_encode([
        "draw" => intval($draw),
        "recordsTotal" => intval($totalRecords),
        "recordsFiltered" => intval($totalFilteredRecords),
        "data" => $data
    ]);
}

function fetchUsers($db) {
    $draw = $_POST['draw'];
    $start = $_POST['start'];
    $length = $_POST['length'];
    $searchValue = $_POST['searchValue'] ?? '';
    $orderColumnIndex = $_POST['order'][0]['column'];
    $orderColumnName = $_POST['columns'][$orderColumnIndex]['data'];
    $orderDir = $_POST['order'][0]['dir'];

    
    $totalRecords = $db->getValue('users', 'count(*)');

    if (!empty($searchValue)) {
        $db->where('first_name', '%' . $searchValue . '%', 'LIKE');
        $db->orWhere('email', '%' . $searchValue . '%', 'LIKE');
    }

    $db->orderBy($orderColumnName, $orderDir);
    $db->pageLimit = $length;
    $data = $db->arraybuilder()->paginate('users', ($start / $length) + 1);

    $totalFilteredRecords = $db->totalCount;

    echo json_encode([
        "draw" => intval($draw),
        "recordsTotal" => intval($totalRecords),
        "recordsFiltered" => intval($totalFilteredRecords),
        "data" => $data
    ]);
}

function fetchProductDaywiseSales($db) {
    $productId = $_POST['productId'] ?? null;

    if (!$productId) {
        echo json_encode(['error' => 'Product ID is required']);
        return;
    }

    $db->join("orders o", "oi.order_id = o.id", "INNER");
    $db->where("oi.product_id", $productId);
    $db->groupBy("DATE(o.created_at)");
    $db->orderBy("DATE(o.created_at)", "ASC");
    $cols = array("DATE(o.created_at) as sale_date", "SUM(oi.quantity) as total_quantity_sold", "SUM(oi.total_price) as total_sales_amount");
    $data = $db->get("order_items oi", null, $cols);

    echo json_encode([
        "data" => $data
    ]);
}

function fetchUserDaywiseSales($db) {
    $userId = $_POST['userId'] ?? null;

    if (!$userId) {
        echo json_encode(['error' => 'User ID is required']);
        return;
    }

    $db->where("user_id", $userId);
    $db->groupBy("DATE(created_at)");
    $db->orderBy("DATE(created_at)", "ASC");
    $cols = array("DATE(created_at) as sale_date", "COUNT(*) as total_orders", "SUM(total_amount) as total_sales_amount");
    $data = $db->get("orders", null, $cols);

    echo json_encode([
        "data" => $data
    ]);
}

function fetchBestSellers($db) {
    $draw = $_POST['draw'];
    $start = $_POST['start'];
    $length = $_POST['length'];
    $searchValue = (string)($_POST['search']['value'] ?? '');
    $orderColumnIndex = $_POST['order'][0]['column'];
    $orderColumnName = $_POST['columns'][$orderColumnIndex]['data'];
    $orderDir = $_POST['order'][0]['dir'];

    $db->join("products p", "oi.product_id = p.id", "INNER");
    $db->groupBy("oi.product_id");
    $db->orderBy($orderColumnName, $orderDir);
    $db->pageLimit = $length;
    $cols = array("oi.product_id", "p.name as product_name", "SUM(oi.quantity) as total_quantity_sold", "SUM(oi.total_price) as total_sales_amount");
    $data = $db->arraybuilder()->paginate("order_items oi", ($start / $length) + 1, $cols);

    $totalRecords = $db->getValue("order_items", "count(DISTINCT product_id)");
    $totalFilteredRecords = $db->totalCount;

    echo json_encode([
        "draw" => intval($draw),
        "recordsTotal" => intval($totalRecords),
        "recordsFiltered" => intval($totalFilteredRecords),
        "data" => $data
    ]);
}

function fetchLowInventory($db) {
    $draw = $_POST['draw'];
    $start = $_POST['start'];
    $length = $_POST['length'];
    $threshold = $_POST['threshold'] ?? 0;
    $searchValue = (string)($_POST['search']['value'] ?? '');
    $orderColumnIndex = $_POST['order'][0]['column'];
    $orderColumnName = $_POST['columns'][$orderColumnIndex]['data'];
    $orderDir = $_POST['order'][0]['dir'];

    $db->where("stock_quantity", $threshold, "<=");
    $db->orderBy($orderColumnName, $orderDir);
    $db->pageLimit = $length;
    $cols = array("id", "name", "sku", "stock_quantity", "min_stock_level");
    $data = $db->arraybuilder()->paginate("products", ($start / $length) + 1, $cols);

    $totalRecords = $db->getValue("products", "count(*)");
    $totalFilteredRecords = $db->totalCount;

    echo json_encode([
        "draw" => intval($draw),
        "recordsTotal" => intval($totalRecords),
        "recordsFiltered" => intval($totalFilteredRecords),
        "data" => $data
    ]);
}

?>