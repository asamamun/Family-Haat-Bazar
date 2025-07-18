<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Set timezone to Bangladesh Standard Time
date_default_timezone_set('Asia/Dhaka');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=settings()['companyname']?> - <?=$page?></title>

    <!-- Open Graph Protocol Meta Tags -->
    <meta property="og:title" content="<?=isset($og_title) ? $og_title : settings()['companyname'] . ' - ' . $page?>">
    <meta property="og:description" content="<?=isset($og_description) ? $og_description : 'Shop the best products at ' . settings()['companyname'] . '. Quality products, great prices, fast delivery.'?>">
    <meta property="og:image" content="<?=isset($og_image) ? $og_image : 'https://coders64.xyz/projects/haatbazar/' . ltrim(settings()['logo'], '/')?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="<?=isset($og_title) ? $og_title : settings()['companyname']?>">
    <meta property="og:url" content="<?=isset($og_url) ? $og_url : 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']?>">
    <meta property="og:type" content="<?=isset($og_type) ? $og_type : 'website'?>">
    <meta property="og:site_name" content="<?=settings()['companyname']?>">

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?=isset($og_title) ? $og_title : settings()['companyname'] . ' - ' . $page?>">
    <meta name="twitter:description" content="<?=isset($og_description) ? $og_description : 'Shop the best products at ' . settings()['companyname'] . '. Quality products, great prices, fast delivery.'?>">
    <meta name="twitter:image" content="<?=isset($og_image) ? $og_image : 'https://coders64.xyz/projects/haatbazar/' . ltrim(settings()['logo'], '/')?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Add Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<link rel="stylesheet" href="<?=settings()['homepage']?>assets/css/styles.css">
<link rel="stylesheet" href="<?=settings()['homepage']?>assets/css/footer.css">
<link rel="stylesheet" href="<?=settings()['homepage']?>assets/assets/owl.carousel.min.css">
<script src="<?=settings()['homepage']?>assets/js/jquery-3.7.1.min.js"></script>
<script src="<?=settings()['homepage']?>assets/js/cart.js"></script>
<script>console.log("Cart.js loaded. Cart is defined: ", typeof Cart !== 'undefined');</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>
<body>



<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php"><img src="<?=settings()['logo']?>" alt="ShopEase Logo" width="90"></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="javascript:void(0);" id="categoryToggle">
                    <div class="menu-toggle">
                        <span class="hamburger">☰</span>
                        <span>Select Category</span>
                    </div>
                </a></li>
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="hot-deals.php">Hot Deals</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="brands.php">Brands</a>
                </li>
            </ul>
            <form class="d-flex" role="search">
                <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
                <button class="btn btn-outline-success" type="submit">Search</button>
            </form>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <button class="icon nav-right-item btn btn-light" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCart" aria-controls="offcanvasCart">🛒</button>
                </li>
                <li class="nav-item">
                    <a href="cart.php" class="nav-link">(<span id="cartCountButton">0</span>)</a>
                </li>
                <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == 'true'): ?>
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                        <li class="nav-item">
                            <a href="admin/index.php" class="nav-link btn btn-primary me-2">Dashboard</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle btn btn-outline-primary" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle me-1"></i><?=htmlspecialchars($_SESSION['username'] ?? $_SESSION['email'] ?? 'Admin')?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="user-orders.php"><i class="fas fa-shopping-bag me-2"></i>My Orders</a></li>
                                <li><a class="dropdown-item" href="user-settings.php"><i class="fas fa-cog me-2"></i>User Settings</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle btn btn-outline-primary" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle me-1"></i><?=htmlspecialchars($_SESSION['username'] ?? $_SESSION['email'] ?? 'User')?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="user-orders.php"><i class="fas fa-shopping-bag me-2"></i>My Orders</a></li>
                                <li><a class="dropdown-item" href="user-settings.php"><i class="fas fa-cog me-2"></i>User Settings</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </li>
                    <?php endif;?>
                <?php else: ?>
                    <li class="nav-item">
                        <a href="login.php" class="nav-link btn btn-primary me-2">Sign In</a>
                    </li>
                    <li class="nav-item">
                        <a href="registration.php" class="nav-link btn btn-primary">Sign up</a>
                    </li>
                <?php endif;?>
            </ul>
        </div>
    </div>
</nav>


<div class="container">
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Category Sidebar -->
    <!-- Category Sidebar (emon vai) -->

    <div class="category-sidebar" id="categorySidebar">
        <div class="sidebar-header">
            <h5 class="mb-0">Shop by Category</h5>
            <button class="sidebar-close-btn" id="closeSidebar">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <ul class="category-list">
            <?php
// Database connection
$conn = new mysqli(
    settings()['hostname'],
    settings()['user'],
    settings()['password'],
    settings()['database']);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch active categories
$category_query = "SELECT id, name, slug FROM categories WHERE is_active = 1 ORDER BY sort_order, name";
$category_result = $conn->query($category_query);

// Map category names to Font Awesome icons
$category_icons = [
    'Garments' => 'fas fa-tshirt',
    'Automobiles' => 'fas fa-car',
    'Electronics' => 'fas fa-laptop',
    'kids' => 'fas fa-baby',
    'Cattle' => 'fas fa-cow',
    'sdfgdfsgdfg' => 'fas fa-box',
    'panio' => 'fas fa-mug-hot',
];

while ($category = $category_result->fetch_assoc()) {
    $category_id = $category['id'];
    $category_name = htmlspecialchars($category['name']);
    $category_slug = htmlspecialchars($category['slug']);
    $icon_class = isset($category_icons[$category_name]) ? $category_icons[$category_name] : 'fas fa-folder';

    // Fetch subcategories
    $subcategory_query = "SELECT id, name, slug FROM subcategories WHERE category_id = ? AND is_active = 1 ORDER BY sort_order, name";
    $stmt = $conn->prepare($subcategory_query);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $subcategory_result = $stmt->get_result();

    // Output category
    echo '<li class="category-item">';
    echo '<a href="index.php?category=' . $category_id . '" class="category-link category-filter" data-category-id="' . $category_id . '" data-category="' . $category_slug . '">';
    echo '<span><i class="' . $icon_class . ' category-icon"></i>' . $category_name . '</span>';
    echo '<i class="fas fa-chevron-right"></i>';
    echo '</a>';

    // Output subcategories
    if ($subcategory_result->num_rows > 0) {
        echo '<ul class="subcategory-list">';
        while ($subcategory = $subcategory_result->fetch_assoc()) {
            $subcategory_id = $subcategory['id'];
            $subcategory_name = htmlspecialchars($subcategory['name']);
            $subcategory_slug = htmlspecialchars($subcategory['slug']);
            echo '<li><a href="index.php?subcategory=' . $subcategory_id . '" class="subcategory-link subcategory-filter" data-subcategory-id="' . $subcategory_id . '">' . $subcategory_name . '</a></li>';
        }
        echo '</ul>';
    }
    echo '</li>';

    $stmt->close();
}

$conn->close();
?>
        </ul>
    </div>

    <!-- JavaScript for Sidebar Interactivity -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const categoryLinks = document.querySelectorAll('.category-link');
            const closeSidebarBtn = document.getElementById('closeSidebar');
            const sidebar = document.getElementById('categorySidebar');

            // Toggle subcategory visibility
            categoryLinks.forEach(link => {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    const subcategoryList = this.nextElementSibling;
                    if (subcategoryList && subcategoryList.classList.contains('subcategory-list')) {
                        const isActive = subcategoryList.classList.contains('active');
                        document.querySelectorAll('.subcategory-list').forEach(list => list.classList.remove('active'));
                        if (!isActive) {
                            subcategoryList.classList.add('active');
                        }
                    }
                });
            });

            // Close sidebar
            closeSidebarBtn.addEventListener('click', function () {
                sidebar.classList.add('closed');
            });
        });
    </script>
    <!-- Off canves for cart -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasCart" aria-labelledby="offcanvasCartLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="offcanvasCartLabel">Your Cart</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body" id="cartContent">
    <!-- You can load cart content dynamically here using PHP or JS -->
     <table class="table table-hover">
        <thead>
            <tr>
                <th scope="col">Item</th>
                <th scope="col">Quantity</th>
                <th scope="col">Price</th>
                <th scope="col">Total</th>
                <th scope="col">Action</th>
            </tr>
        </thead>
        <tbody>

        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-end fw-bold">Grand Total</td>
                <td id="grandTotalCanvas" class="fw-bold">0.00</td>
                <td></td>
            </tr>
        </tfoot>
     </table>

    <?php include 'cart-preview.php'; // create this file for cart content preview ?>
  </div>
</div>

    <!-- Main Content -->
    <div class="main-content">
        <?php require __DIR__ . '/dismissalert.php';?>
        <?php require __DIR__ . '/sessiondata.php';?>

<script>
function openUserSettings() {
    // For now, show a simple alert. You can later create a user settings page or modal
    Swal.fire({
        title: 'User Settings',
        text: 'User settings functionality will be implemented soon.',
        icon: 'info',
        confirmButtonText: 'OK'
    });
}
</script>