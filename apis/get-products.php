<?php
// Set timezone to Bangladesh Standard Time
date_default_timezone_set('Asia/Dhaka');

require __DIR__ . '/../vendor/autoload.php';

header('Content-Type: application/json');

$db = new MysqliDb();

// Parameters
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;
$subcategory_id = isset($_GET['subcategory']) ? (int)$_GET['subcategory'] : null;
$search_term = isset($_GET['search']) ? $_GET['search'] : null;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12; // Products per page
$offset = ($page - 1) * $limit;

// Base query
$db->where('is_active', 1);

// Category filter
if ($category_id) {
    $db->where('category_id', $category_id);
}

// Subcategory filter
if ($subcategory_id) {
    $db->where('subcategory_id', $subcategory_id);
    $subcategory = $db->where('id', $subcategory_id)->getOne('subcategories');
    $subcategory_name = $subcategory ? $subcategory['name'] : null;
}

// Search filter
if ($search_term) {
    $db->where('name', '%' . $search_term . '%', 'LIKE');
}

// Get total count for pagination
$countDb = $db->copy();
$total_products = $countDb->getValue('products', 'count(*)');
$total_pages = ceil($total_products / $limit);

// Fetch products with pagination
$db->orderBy('name', 'asc');
$products = $db->get('products', [$offset, $limit]);

// Response
echo json_encode([
    'products' => $products,
    'total_pages' => $total_pages,
    'current_page' => $page,
    'subcategory_name' => isset($subcategory_name) ? $subcategory_name : null
]);

$db->disconnect();