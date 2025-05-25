<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/../vendor/autoload.php';
$pdo = new mysqli(settings()['hostname'], settings()['user'], settings()['password'], settings()['database']);    
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $slug = trim($_POST['slug']);
    $description = trim($_POST['description']);
    $status = $_POST['status'];
    
    // Validation
    if (empty($name)) {
        $errors[] = "Category name is required.";
    }
    
    if (strlen($name) < 2) {
        $errors[] = "Category name must be at least 2 characters long.";
    }
    
    if (strlen($name) > 100) {
        $errors[] = "Category name must not exceed 100 characters.";
    }
    
    // Check if category name already exists
    if (!empty($name)) {
        $check_name = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
        $check_name->execute([$name]);
        if ($check_name->fetch()) {
            $errors[] = "A category with this name already exists.";
        }
    }
    
    // Handle image upload
    $image_name = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($_FILES['image']['type'], $allowed_types)) {
            $errors[] = "Only JPEG, PNG, GIF, and WebP images are allowed.";
        }
        
        if ($_FILES['image']['size'] > $max_size) {
            $errors[] = "Image size must not exceed 5MB.";
        }
        
        if (empty($errors)) {
            $upload_dir = 'assets/categories/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image_name = uniqid() . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $image_name;
            
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $errors[] = "Failed to upload image.";
                $image_name = '';
            }
        }
    }
    
    // Insert category if no errors
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO categories (name, slug,  description, image, is_active, created_at, updated_at) 
                VALUES (?,?, ?, ?, ?, NOW(), NOW())
            ");
            
            if ($stmt->execute([$name, $slug, $description, $image_name, $status])) {
                $success = "Category added successfully!";
                // Clear form data
                $name = $description = '';
                $status = 'active';
            } else {
                $errors[] = "Failed to add category.";
            }
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>
<?php require __DIR__.'/components/header.php'; ?>

    </head>
    <body class="sb-nav-fixed">
    <?php require __DIR__.'/components/navbar.php'; ?>
        <div id="layoutSidenav">
        <?php require __DIR__.'/components/sidebar.php'; ?>
            <div id="layoutSidenav_content">
                <main>
                    <!-- changed content -->
<?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <h6>Please fix the following errors:</h6>
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $success; ?>
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Category Details</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="name">Category Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" 
                                           required maxlength="100">
                                    <small class="form-text text-muted">Enter a unique category name (2-100 characters)</small>
                                </div>
                                <!-- slug -->
                                <div class="form-group">
                                    <label for="slug">Slug</label>
                                    <input type="text" class="form-control" id="slug" name="slug" 
                                           value="<?php echo isset($slug) ? $slug : ''; ?>">
                                </div>

                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="4" 
                                              maxlength="500"><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea>
                                    <small class="form-text text-muted">Optional description (max 500 characters)</small>
                                </div>

                                <div class="form-group">
                                    <label for="image">Category Image</label>
                                    <input type="file" class="form-control-file" id="image" name="image" 
                                           accept="image/jpeg,image/png,image/gif,image/webp">
                                    <small class="form-text text-muted">
                                        Optional. Supported formats: JPEG, PNG, GIF, WebP. Max size: 5MB
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="1" <?php echo (!isset($status) || $status === '1') ? 'selected' : ''; ?>>
                                            Active
                                        </option>
                                        <option value="0" <?php echo (isset($status) && $status === '0') ? 'selected' : ''; ?>>
                                            Inactive
                                        </option>
                                    </select>
                                    <small class="form-text text-muted">
                                        Inactive categories won't be visible to customers
                                    </small>
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Add Category
                                    </button>
                                    <a href="category-all.php" class="btn btn-secondary ml-2">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-info-circle"></i> Category Guidelines
                            </h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="fas fa-check text-success"></i>
                                    Use clear, descriptive names
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success"></i>
                                    Keep names concise but informative
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success"></i>
                                    Add relevant descriptions for SEO
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success"></i>
                                    Use high-quality images (square format works best)
                                </li>
                                <li class="mb-0">
                                    <i class="fas fa-check text-success"></i>
                                    Set status to active when ready to display
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-image"></i> Image Tips
                            </h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-1">• Recommended size: 300x300px</li>
                                <li class="mb-1">• Format: PNG or JPG</li>
                                <li class="mb-1">• Max file size: 5MB</li>
                                <li class="mb-0">• Use transparent backgrounds for PNG</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
                    <!-- changed content  ends-->
                </main>
<!-- footer -->
<?php require __DIR__.'/components/footer.php'; ?>
            </div>
        </div>
        <script src="<?= settings()['adminpage'] ?>assets/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="<?= settings()['adminpage'] ?>assets/js/scripts.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="<?= settings()['adminpage'] ?>assets/demo/chart-area-demo.js"></script>
        <script src="<?= settings()['adminpage'] ?>assets/demo/chart-bar-demo.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>
        <script src="<?= settings()['adminpage'] ?>assets/js/datatables-simple-demo.js"></script>
    </body>
</html>
