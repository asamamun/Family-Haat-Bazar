<?php
// Start session
if (session_status() === PHP_SESSION_NONE) session_start();

// Autoload
require __DIR__ . '/../vendor/autoload.php';

// Database Connection
$pdo = new mysqli(
    settings()['hostname'],
    settings()['user'],
    settings()['password'],
    settings()['database']
);

// Globals
$errors = [];
$success = '';

// Helper: Validate Category Name
function validateCategoryName($name, $pdo) {
    global $errors;
    if (empty($name)) {
        $errors[] = "Category name is required.";
    } elseif (strlen($name) < 2) {
        $errors[] = "Category name must be at least 2 characters.";
    } elseif (strlen($name) > 100) {
        $errors[] = "Category name must not exceed 100 characters.";
    } else {
        $check = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
        $check->execute([$name]);
        if ($check->fetch()) {
            $errors[] = "This category name already exists.";
        }
    }
}

// Helper: Upload Image
function uploadCategoryImage($file) {
    global $errors;
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024;

    if (!in_array($file['type'], $allowed)) {
        $errors[] = "Only JPEG, PNG, GIF, and WebP are allowed.";
        return '';
    }

    if ($file['size'] > $maxSize) {
        $errors[] = "Image must not exceed 5MB.";
        return '';
    }

    $uploadDir = 'assets/categories/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = uniqid() . '_' . time() . '.' . $ext;
    $path = $uploadDir . $fileName;

    if (move_uploaded_file($file['tmp_name'], $path)) {
        return $fileName;
    }

    $errors[] = "Image upload failed.";
    return '';
}

// Handle POST Request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $slug = trim($_POST['slug']);
    $description = trim($_POST['description']);
    $status = $_POST['status'] ?? '1';

    validateCategoryName($name, $pdo);

    $imageName = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageName = uploadCategoryImage($_FILES['image']);
    }

    // If no errors, insert into database
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description, image, is_active, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, NOW(), NOW())");

        if ($stmt->execute([$name, $slug, $description, $imageName, $status])) {
            $success = "Category added successfully.";
            $name = $slug = $description = '';
            $status = '1';
        } else {
            $errors[] = "Failed to add category.";
        }
    }
}

// Page Layout
require __DIR__ . '/components/navbar.php';
?>

</head>
<body class="sb-nav-fixed">
<!-- <?php require __DIR__ . '/components/navbar.php'; ?> -->
<div id="layoutSidenav">
    <!-- <?php require __DIR__ . '/components/sidebar.php'; ?> -->
    <div id="layoutSidenav_content">
        <main class="container-fluid px-4 py-4">
            <h2>Add New Category</h2>

            <!-- Messages -->
            <?php if ($errors): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0"><?php foreach ($errors as $e) echo "<li>".htmlspecialchars($e)."</li>"; ?></ul>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <!-- Form -->
            <div class="card">
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group mb-3">
                            <label>Category Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required maxlength="100"
                                   value="<?= htmlspecialchars($name ?? '') ?>">
                        </div>

                        <div class="form-group mb-3">
                            <label>Slug</label>
                            <input type="text" name="slug" class="form-control"
                                   value="<?= htmlspecialchars($slug ?? '') ?>">
                        </div>

                        <div class="form-group mb-3">
                            <label>Description</label>
                            <textarea name="description" rows="3" class="form-control"
                                      maxlength="500"><?= htmlspecialchars($description ?? '') ?></textarea>
                        </div>

                        <div class="form-group mb-3">
                            <label>Category Image</label>
                            <input type="file" name="image" class="form-control"
                                   accept="image/jpeg,image/png,image/gif,image/webp">
                                   <div class="form-group">
    <label for="image">Category Image</label>
    <div id="drop-area" class="border p-3 text-center" style="cursor: pointer;">
        <p>Drag & Drop Image Here or Click to Upload</p>
        <input type="file" id="image" name="image" accept="image/*" hidden>
        <img id="preview" src="#" alt="Image Preview" style="max-width: 100%; display: none; margin-top: 10px;" />
    </div>
    <small class="form-text text-muted">
        Optional. Supported formats: JPEG, PNG, GIF, WebP. Max size: 5MB
    </small>
</div>

                        </div>

                        <div class="form-group mb-3">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="1" <?= ($status ?? '1') === '1' ? 'selected' : '' ?>>Active</option>
                                <option value="0" <?= ($status ?? '1') === '0' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Category
                            </button>
                            <a href="category-all.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
        <?php require __DIR__ . '/components/footer.php'; ?>
    </div>
</div>

<!-- JS -->
<script src="<?= settings()['adminpage'] ?>assets/js/bootstrap.bundle.min.js"></script>
<script src="<?= settings()['adminpage'] ?>assets/js/scripts.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const dropArea = document.getElementById('drop-area');
    const fileInput = document.getElementById('image');
    const previewImg = document.getElementById('preview');

    dropArea.addEventListener('click', () => fileInput.click());

    dropArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropArea.classList.add('bg-light');
    });

    dropArea.addEventListener('dragleave', () => {
        dropArea.classList.remove('bg-light');
    });

    dropArea.addEventListener('drop', (e) => {
        e.preventDefault();
        dropArea.classList.remove('bg-light');
        const file = e.dataTransfer.files[0];
        fileInput.files = e.dataTransfer.files;
        showPreview(file);
    });

    fileInput.addEventListener('change', function () {
        if (this.files && this.files[0]) {
            showPreview(this.files[0]);
        }
    });

    function showPreview(file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            previewImg.src = e.target.result;
            previewImg.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});
</script>

</body>
</html>
