<?php
require __DIR__ . '/../vendor/autoload.php';

use MysqliDb;

// Initialize DB
$db = new MysqliDb();

// Input data (in real usage, sanitize/validate from form input)
$data = [
    'category_id'     => 5,
    'subcategory_id'  => 6,
    'name'            => 'testing db class',
    'description'     => 'testing db class insert',
    'sku'             => 'dbclass01' . mt_rand(1000, 9999),
    'images'          => 'imagenameee.png',
    'price'           => 1000.00,
    'quantity'        => 100,
    'discount'        => 5,
    'hot'             => 1,
];

try {
    // Validate required fields
    $required = ['category_id', 'name', 'price', 'sku'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("Field '{$field}' is required.");
        }
    }

    // Insert into database
    $id = $db->insert('products', $data);

    if ($id) {
        echo "✅ Record added successfully. ID: {$id}<br>";
    } else {
        throw new Exception("❌ Insert failed: " . $db->getLastError());
    }

    // Debug: Show the last query (only in development)
    echo "<hr><pre>" . htmlspecialchars($db->getLastQuery()) . "</pre>";

} catch (Exception $e) {
    echo "<div style='color:red;'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// Close DB connection
$db->disconnect();
