<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/../vendor/autoload.php';

use App\auth\Admin;

if (!Admin::Check()) {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

// Establish PDO connection
try {
    $pdo = new PDO(
        "mysql:host=" . settings()['hostname'] . ";dbname=" . settings()['database'],
        settings()['user'],
        settings()['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$errors = [];
$success = '';
$name = '';
$logo_name = '';
$created_at = date('Y-m-d H:i:s'); // Default to current timestamp

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $created_at = trim($_POST['created_at'] ?? date('Y-m-d H:i:s'));

    // Validation
    if (empty($name)) {
        $errors[] = "Brand name is required.";
    } elseif (strlen($name) < 2) {
        $errors[] = "Brand name must be at least 2 characters long.";
    } elseif (strlen($name) > 100) {
        $errors[] = "Brand name must not exceed 100 characters.";
    } else {
        // Check if brand name already exists
        $check_name = $pdo->prepare("SELECT id FROM brands WHERE name = ?");
        $check_name->execute([$name]);
        if ($check_name->fetch()) {
            $errors[] = "A brand with this name already exists.";
        }
    }

    // Handle image upload
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (!in_array($_FILES['logo']['type'], $allowed_types)) {
            $errors[] = "Only JPEG, PNG, GIF, and WebP images are allowed.";
        } elseif ($_FILES['logo']['size'] > $max_size) {
            $errors[] = "Logo size must not exceed 5MB.";
        } else {
            $upload_dir = settings()['physical_path'] . '/assets/brands/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            if (!is_writable($upload_dir)) {
                $errors[] = "Upload directory is not writable.";
            } else {
                $file_extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
                $logo_name = uniqid() . '_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $logo_name;

                if (!move_uploaded_file($_FILES['logo']['tmp_name'], $upload_path)) {
                    $errors[] = "Failed to upload logo.";
                    $logo_name = '';
                }
            }
        }
    }

    // Insert brand if no errors
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO brands (name, logo, created_at) 
                VALUES (?, ?, ?)
            ");
            if ($stmt->execute([$name, $logo_name, $created_at])) {
                $success = "Brand added successfully!";
                $name = $logo_name = '';
                $created_at = date('Y-m-d H:i:s'); // Reset to current time
            } else {
                $errors[] = "Failed to add brand.";
            }
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>
<?php require __DIR__ . '/components/header.php'; ?>

</head>
<body class="sb-nav-fixed">
    <?php require __DIR__ . '/components/navbar.php'; ?>
    <div id="layoutSidenav">
        <?php require __DIR__ . '/components/sidebar.php'; ?>
        <div id="layoutSidenav_content">
            <main>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <h6>Please fix the following errors:</h6>
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="close" data-dismiss="alert">
                            <span>×</span>
                        </button>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <script>
                        Swal.fire({
                            position: "top-end",
                            icon: "success",
                            title: "<?php echo htmlspecialchars($success); ?>",
                            showConfirmButton: false,
                            timer: 1500
                        });
                    </script>
                <?php endif; ?>
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <div class="d-flex justify-content-between">
                                    <h5 class="card-title mb-0">Brand Details</h5>
                                    <a href="brand-all.php" class="btn btn-primary ml-auto">
                                        <i class="fas fa-arrow-left"></i> Back to Brands
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label for="name">Brand Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name"
                                            value="<?php echo htmlspecialchars($name); ?>"
                                            required maxlength="100">
                                        <small class="form-text text-muted">Enter a unique brand name (2-100 characters)</small>
                                    </div>
                                    <div class="form-group">
                                        <label for="logo">Brand Image</label>
                                        <input type="file" class="form-control-file" id="logo" name="logo"
                                            accept="image/jpeg,image/png,image/gif,image/webp"
                                            onchange="previewImage(event)">
                                        <small class="form-text text-muted">
                                            Optional. Supported formats: JPEG, PNG, GIF, WebP. Max size: 5MB
                                        </small>
                                        <div class="mt-2">
                                            <img id="logo-preview" src="<?php echo !empty($logo_name) ? settings()['adminpage'] . 'assets/brands/' . htmlspecialchars($logo_name) : '#'; ?>"
                                                style="max-width: 150px; max-height: 150px; display: <?php echo !empty($logo_name) ? 'block' : 'none'; ?>;"
                                                alt="Logo Preview">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="created_at">Created At</label>
                                        <input type="text" class="form-control" id="created_at" name="created_at"
                                            value="<?php echo htmlspecialchars($created_at); ?>" readonly>
                                        <small class="form-text text-muted">Automatically set to current timestamp</small>
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Add Brand
                                        </button>
                                        <a href="brand-all.php" class="btn btn-secondary ml-2">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-info-circle"></i> Brand Guidelines
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
                                        Use high-quality logos (square format works best)
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
                                    <i class="fas fa-image"></i> Logo Tips
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
            </main>
            <?php require __DIR__ . '/components/footer.php'; ?>
        </div>
    </div>
    <script src="<?= settings()['adminpage'] ?>assets/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="<?= settings()['adminpage'] ?>assets/js/scripts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
    <script src="<?= settings()['adminpage'] ?>assets/demo/chart-area-demo.js"></script>
    <script src="<?= settings()['adminpage'] ?>assets/demo/chart-bar-demo.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>
    <script src="<?= settings()['adminpage'] ?>assets/js/datatables-simple-demo.js"></script>
    <script>
//         function previewImage(event) {
//             const input = event.target;
//             const preview = document.getElementById('logo-preview');
//             if (input.files && input.files[0]) {
//                 const reader = new FileReader();
//                 reader.onload = function (e) {
//                     preview.src = e.target.result;
//                     preview.style.display = 'block';
//                 };
//                 reader.readAsDataURL(input.files[0]);
//  “

// System: * Today's date and time is 06:53 AM +06 on Tuesday, June 24, 2025.

</body>
</html>