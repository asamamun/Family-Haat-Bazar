<?php
require_once '../vendor/autoload.php';

$db = new MysqliDb();

$brandId = isset($_GET['brand_id']) ? (int)$_GET['brand_id'] : 0;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

if ($brandId > 0) {
    $db->where('brand', $brandId);
}

$products = $db->get('products', [$offset, $limit]);

// Get total count for pagination
if ($brandId > 0) {
    $db->where('brand', $brandId);
}
$totalProducts = $db->getValue('products', "count(*)");
$totalPages = ceil($totalProducts / $limit);

header('Content-Type: application/json');
echo json_encode([
    'products' => $products,
    'totalPages' => $totalPages,
    'currentPage' => $page
]);
?>