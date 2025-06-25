<?php
require_once '../vendor/autoload.php';
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

header('Content-Type: application/json');

// Database configuration
$host = settings()['hostname'];
$username = settings()['user'];
$password = settings()['password'];
$database = settings()['database'];

try {
    $db = new MysqliDb($host, $username, $password, $database);
} catch (Exception $e) {
    error_log("Database connection failed: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'fetch':
        fetchProducts($db);
        break;
    case 'create':
        createProducts($db);
        break;
    case 'update':
        updateProducts($db);
        break;
    case 'delete':
        deleteProduct($db);
        break;
    case 'get_single':
        getSingleProduct($db);
        break;
    case 'get_categories':
        getCategories($db);
        break;
    case 'get_subcategories':
        getSubCategories($db);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function fetchProducts($db) {
    try {
        $db->join("categories c", "p.category_id = c.id", "LEFT");
        $db->join("subcategories sc", "p.subcategory_id = sc.id", "LEFT");
        $db->join("brands b", "p.brand = b.id", "LEFT");
        $db->orderBy("p.id", "DESC");
        $products = $db->get("products p", null, "p.*, c.name as category_name, sc.name as subcategory_name, b.name as brand_name");
        // error_log("Products: " . print_r($products, true));
        if ($db->getLastErrno() === 0) {
            echo json_encode(['success' => true, 'data' => $products]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error fetching products: ' . $db->getLastError()]);
        }
    } catch (Exception $e) {
        error_log("Fetch products error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error fetching products: ' . $e->getMessage()]);
    }
}

function createProducts($db) {
    try {
        error_log("POST data: " . print_r($_POST, true));
        
        // Validate required fields
        if (!isset($_POST['category']) || !is_numeric($_POST['category'])) {
            error_log("Validation failed: Category is missing or invalid");
            echo json_encode(['success' => false, 'message' => 'Category is required and must be numeric']);
            return;
        }
        if (empty($_POST['name'])) {
            error_log("Validation failed: Name is missing");
            echo json_encode(['success' => false, 'message' => 'Name is required']);
            return;
        }
        if (empty($_POST['slug'])) {
            error_log("Validation failed: Slug is missing");
            echo json_encode(['success' => false, 'message' => 'Slug is required']);
            return;
        }
        
        // Check if slug already exists
        $db->where("slug", $_POST['slug']);
        $existing = $db->getOne("products", "id");
        if ($existing) {
            echo json_encode(['success' => false, 'message' => 'Slug already exists']);
            return;
        }
        
        // Handle image upload
        $imageName = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imageName = handleImageUpload($_FILES['image']);
            if (!$imageName) {
                echo json_encode(['success' => false, 'message' => 'Image upload failed: Invalid file type or size']);
                return;
            }
        }
        
        // Prepare data for insertion
        $data = [
            'category_id' => (int)$_POST['category'],
            'subcategory_id' => !empty($_POST['subcategory']) ? (int)$_POST['subcategory'] : null,
            'brand' => !empty($_POST['brand']) ? (int)$_POST['brand'] : null,
            'name' => $_POST['name'],
            'slug' => $_POST['slug'],
            'description' => !empty($_POST['description']) ? $_POST['description'] : null,
            'short_description' => !empty($_POST['short_description']) ? $_POST['short_description'] : null,
            'sku' => !empty($_POST['sku']) ? $_POST['sku'] : null,
            'barcode' => !empty($_POST['barcode']) ? $_POST['barcode'] : null,
            'selling_price' => !empty($_POST['selling_price']) ? (float)$_POST['selling_price'] : 0.00,
            'cost_price' => !empty($_POST['cost_price']) ? (float)$_POST['cost_price'] : 0.00,
            'markup_percentage' => !empty($_POST['markup']) ? (float)$_POST['markup'] : 0.00,
            'pricing_method' => !empty($_POST['pricing_method']) ? (int)$_POST['pricing_method'] : 0,
            'auto_update_price' => !empty($_POST['auto_update_price']) ? (int)$_POST['auto_update_price'] : 0,
            'stock_quantity' => !empty($_POST['stock_quantity']) ? (int)$_POST['stock_quantity'] : 0,
            'min_stock_level' => !empty($_POST['min_stock_level']) ? (int)$_POST['min_stock_level'] : 0,
            'weight' => !empty($_POST['weight']) ? (float)$_POST['weight'] : 0,
            'dimensions' => !empty($_POST['dimensions']) ? $_POST['dimensions'] : null,
            'is_active' => isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1,
            'is_hot_item' => isset($_POST['hot_item']) ? (int)$_POST['hot_item'] : 0,
            'image' => $imageName,
            'created_at' => $db->now(),
            'updated_at' => $db->now()
        ];
        
        // Insert product
        $id = $db->insert('products', $data);
        
        if ($id) {
            echo json_encode(['success' => true, 'message' => 'Product created successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error creating product: ' . $db->getLastError()]);
        }
    } catch (Exception $e) {
        error_log("Create product error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error creating product: ' . $e->getMessage()]);
    }
}

function updateProducts($db) {
    try {
        $id = $_POST['product_id'];
        
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'Product ID is required']);
            return;
        }
        
        // Validate required fields
        if (empty($_POST['category']) || !is_numeric($_POST['category'])) {
            echo json_encode(['success' => false, 'message' => 'Category is required and must be numeric']);
            return;
        }
        if (empty($_POST['name'])) {
            echo json_encode(['success' => false, 'message' => 'Name is required']);
            return;
        }
        if (empty($_POST['slug'])) {
            echo json_encode(['success' => false, 'message' => 'Slug is required']);
            return;
        }
        
        // Check if slug already exists (excluding current record)
        $db->where("slug", $_POST['slug']);
        $db->where("id", $id, "!=");
        $existing = $db->getOne("products", "id");
        if ($existing) {
            echo json_encode(['success' => false, 'message' => 'Slug already exists']);
            return;
        }
        
        // Get current image
        $db->where("id", $id);
        $currentProduct = $db->getOne("products", "image");
        $currentImage = $currentProduct['image'] ?? null;
        
        // Handle image upload
        $imageName = $currentImage;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $newImageName = handleImageUpload($_FILES['image']);
            if ($newImageName) {
                // Delete old image if it exists
                $path = rtrim(settings()['physical_path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR . $currentImage;
                if ($currentImage && file_exists($path)) {
                    unlink($path);
                }
                $imageName = $newImageName;
            } else {
                echo json_encode(['success' => false, 'message' => 'Image upload failed: Invalid file type or size']);
                return;
            }
        }
        
        // Prepare data for update
        $data = [
            'category_id' => (int)$_POST['category'],
            'subcategory_id' => !empty($_POST['subcategory']) ? (int)$_POST['subcategory'] : null,
            'brand' => !empty($_POST['brand']) ? (int)$_POST['brand'] : null,
            'name' => $_POST['name'],
            'slug' => $_POST['slug'],
            'description' => !empty($_POST['description']) ? $_POST['description'] : null,
            'short_description' => !empty($_POST['short_description']) ? $_POST['short_description'] : null,
            'sku' => !empty($_POST['sku']) ? $_POST['sku'] : null,
            'barcode' => !empty($_POST['barcode']) ? $_POST['barcode'] : null,
            'selling_price' => !empty($_POST['selling_price']) ? (float)$_POST['selling_price'] : 0.00,
            'cost_price' => !empty($_POST['cost_price']) ? (float)$_POST['cost_price'] : 0.00,
            'markup' => !empty($_POST['markup']) ? (float)$_POST['markup'] : 0.00,
            'pricing_method' => !empty($_POST['pricing_method']) ? (int)$_POST['pricing_method'] : 0,
            'auto_update_price' => !empty($_POST['auto_update_price']) ? (int)$_POST['auto_update_price'] : 0,
            'stock_quantity' => !empty($_POST['stock_quantity']) ? (int)$_POST['stock_quantity'] : 0,
            'min_stock_level' => !empty($_POST['min_stock_level']) ? (int)$_POST['min_stock_level'] : 0,
            'weight' => !empty($_POST['weight']) ? (float)$_POST['weight'] : 0,
            'dimensions' => !empty($_POST['dimensions']) ? $_POST['dimensions'] : null,
            'is_active' => isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1,
            'is_hot_item' => isset($_POST['hot_item']) ? (int)$_POST['hot_item'] : 0,
            'image' => $imageName,
            'updated_at' => $db->now()
        ];
        
        // Update product
        $db->where('id', $id);
        $result = $db->update('products', $data);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating product: ' . $db->getLastError()]);
        }
    } catch (Exception $e) {
        error_log("Update product error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error updating product: ' . $e->getMessage()]);
    }
}

function deleteProduct($db) {
    try {
        $id = $_POST['id'];
        
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'Product ID is required']);
            return;
        }
        
        // Get image name before deletion
        $db->where("id", $id);
        $product = $db->getOne("products", "image");
        
        // Delete product
        $db->where('id', $id);
        $result = $db->delete('products');
        
        if ($result) {
            $path = rtrim(settings()['physical_path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR . $product['image'];
            if ($product && $product['image'] && file_exists($path)) {
                unlink($path);
            }
            echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting product: ' . $db->getLastError()]);
        }
    } catch (Exception $e) {
        error_log("Delete product error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error deleting product: ' . $e->getMessage()]);
    }
}

function getSingleProduct($db) {
    try {
        $id = $_POST['id'];
        
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'Product ID is required']);
            return;
        }
        
        $db->where("id", $id);
        $product = $db->getOne("products");
        
        if ($product) {
            echo json_encode(['success' => true, 'data' => $product]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
        }
    } catch (Exception $e) {
        error_log("Get single product error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error fetching product: ' . $e->getMessage()]);
    }
}

function getCategories($db) {
    try {
        $db->where("is_active", 1);
        $db->orderBy("name", "ASC");
        $categories = $db->get("categories", null, "id, name");
        
        if ($db->getLastErrno() === 0) {
            echo json_encode(['success' => true, 'data' => $categories]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error fetching categories: ' . $db->getLastError()]);
        }
    } catch (Exception $e) {
        error_log("Get categories error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error fetching categories: ' . $e->getMessage()]);
    }
}

function getSubCategories($db) {
    try {
        $catid = $_POST['category_id'];
        $db->where("is_active", 1);
        $db->where("category_id", $catid);
        $db->orderBy("name", "ASC");
        $subcategories = $db->get("subcategories", null, "id, name");
        
        if ($db->getLastErrno() === 0) {
            echo json_encode(['success' => true, 'data' => $subcategories]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error fetching subcategories: ' . $db->getLastError()]);
        }
    } catch (Exception $e) {
        error_log("Get subcategories error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error fetching subcategories: ' . $e->getMessage()]);
    }
}

function handleImageUpload($file) {
    $uploadDir = rtrim(settings()['physical_path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR;
    
    // Create directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, $allowedTypes)) {
        error_log("Image upload failed: Invalid MIME type: $mime");
        return false;
    }
    
    // Validate file size (max 5MB)
    if ($file['size'] > 5242880) {
        error_log("Image upload failed: File size exceeds 5MB");
        return false;
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Resize image        
        $manager = new ImageManager(new Driver());
        $filepath = realpath($filepath);
        $image = $manager->read($filepath);
        $image->scale(width: 400);
        
        // Apply watermark
        $watermarkPath = rtrim(settings()['physical_path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'watermark.png';
        if (file_exists($watermarkPath)) {
            $image->place($watermarkPath, 'center', 0, 0, 30);
        }
        
        // Save the image with compression (quality: 85%)
        $image->save($filepath, 85);
        return $filename;
    }
    
    error_log("Image upload failed: Unable to move uploaded file");
    return false;
}
?>