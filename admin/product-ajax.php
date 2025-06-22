<?php
require_once '../vendor/autoload.php';
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;


header('Content-Type: application/json');

// Include composer autoloader (adjust path as needed)


// Database configuration - Update these with your actual database credentials
$host = settings()['hostname'];
$username = settings()['user'];
$password = settings()['password'];
$database = settings()['database'];

try {
    $db = new MysqliDb($host, $username, $password, $database);
} catch (Exception $e) {
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
        $db->orderBy("p.id", "DESC");
        $products = $db->get("products p", null, "p.*, c.name as category_name, sc.name as subcategory_name");
        
        if ($db->getLastErrno() === 0) {
            echo json_encode(['success' => true, 'data' => $products]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error fetching subcategories: ' . $db->getLastError()]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error fetching subcategories: ' . $e->getMessage()]);
    }
}

function createProducts($db) {
    try {
        // Validate required fields
        if (empty($_POST['category_id']) || empty($_POST['name']) || empty($_POST['slug'])) {
            echo json_encode(['success' => false, 'message' => 'Category, name, and slug are required']);
            return;
        }
        
        // Check if slug already exists
        $db->where("slug", $_POST['slug']);
        $existing = $db->getOne("subcategories", "id");
        if ($existing) {
            echo json_encode(['success' => false, 'message' => 'Slug already exists']);
            return;
        }
        
        // Handle image upload
        $imageName = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imageName = handleImageUpload($_FILES['image']);
            if (!$imageName) {
                echo json_encode(['success' => false, 'message' => 'Error uploading image']);
                return;
            }
        }
        
        // Prepare data for insertion
        //TODO: Add the rest of the fields
        $data = [
            
        ];
        
        // Insert subcategory
        $id = $db->insert('products', $data);
        
        if ($id) {
            echo json_encode(['success' => true, 'message' => 'Subcategory created successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error creating subcategory: ' . $db->getLastError()]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error creating subcategory: ' . $e->getMessage()]);
    }
}

function updateProducts($db) {
    try {
        $id = $_POST['subcategory_id'];
        
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'Subcategory ID is required']);
            return;
        }
        
        // Validate required fields
        if (empty($_POST['category_id']) || empty($_POST['name']) || empty($_POST['slug'])) {
            echo json_encode(['success' => false, 'message' => 'Category, name, and slug are required']);
            return;
        }
        
        // Check if slug already exists (excluding current record)
        $db->where("slug", $_POST['slug']);
        $db->where("id", $id, "!=");
        $existing = $db->getOne("subcategories", "id");
        if ($existing) {
            echo json_encode(['success' => false, 'message' => 'Slug already exists']);
            return;
        }
        
        // Get current image
        $db->where("id", $id);
        $currentSubcategory = $db->getOne("subcategories", "image");
        $currentImage = $currentSubcategory['image'] ?? null;
        
        // Handle image upload
        $imageName = $currentImage;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $newImageName = handleImageUpload($_FILES['image']);
            if ($newImageName) {
                // Delete old image if it exists
                $path = settings()['physical_path'] . "assets/subcategories/$currentImage";
                if ($currentImage && file_exists($path)) {
                    unlink($path);
                }
                $imageName = $newImageName;
            }
        }
        
        // Prepare data for update
        $data = [
            'category_id' => $_POST['category_id'],
            'name' => $_POST['name'],
            'slug' => $_POST['slug'],
            'description' => $_POST['description'] ?? null,
            'image' => $imageName,
            'is_active' => $_POST['is_active'] ?? 1,
            'sort_order' => $_POST['sort_order'] ?? 0,
            'updated_at' => $db->now()
        ];
        
        // Update subcategory
        $db->where('id', $id);
        $result = $db->update('subcategories', $data);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Subcategory updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating subcategory: ' . $db->getLastError()]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error updating subcategory: ' . $e->getMessage()]);
    }
}

function deleteProduct($db) {
    try {
        $id = $_POST['id'];
        
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'Subcategory ID is required']);
            return;
        }
        
        // Get image name before deletion
        $db->where("id", $id);
        $product = $db->getOne("products", "image");
        
        // Delete subcategory
        $db->where('id', $id);
        $result = $db->delete('products');
        
        if ($result) {
            $path = settings()['physical_path'] . "assets/products/{$product['image']}";
            // Delete associated image file
            if ($product && $product['image'] && file_exists($path)) {
                unlink($path);
            }
            echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting product: ' . $db->getLastError()]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error deleting product: ' . $e->getMessage()]);
    }
}

function getSingleProduct($db) {
    try {
        $id = $_POST['id'];
        
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'Subcategory ID is required']);
            return;
        }
        
        $db->where("id", $id);
        $subcategory = $db->getOne("subcategories");
        
        if ($subcategory) {
            echo json_encode(['success' => true, 'data' => $subcategory]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Subcategory not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error fetching subcategory: ' . $e->getMessage()]);
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
        echo json_encode(['success' => false, 'message' => 'Error fetching subcategories: ' . $e->getMessage()]);
    }
}

function handleImageUpload($file) {
    
    $uploadDir = settings()['physical_path'] . '/assets/subcategories/';
    
    // Create directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        return false;
    }
    
    // Validate file size (max 5MB)
    if ($file['size'] > 5242880) {
        return false;
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        //resize image        
        $manager = new ImageManager(new Driver());
        $filepath = realpath($filepath);
        $image = $manager->read($filepath);
        $image->scale(width: 400);


    // Apply watermark
    $watermarkPath = realpath(settings()['physical_path'] . '\admin\assets\watermark.png');
    if (file_exists($watermarkPath)) {
        $image->place($watermarkPath, 'center', 0, 0, 30); // Position: bottom-right with 10px offset
    }

    // Save the image with compression (quality: 85%)
    $image->save($filepath, 85);
        return $filename;
    }
    
    return false;
}
?>