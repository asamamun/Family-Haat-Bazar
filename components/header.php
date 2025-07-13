<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= settings()['companyname'] ?> - <?= $page ?></title>
    <link rel="stylesheet" href="<?= settings()['homepage'] ?>assets/css/bootstrap.min.css">
    <script src="<?= settings()['homepage'] ?>assets/js/bootstrap.bundle.min.js"></script>
    <script src="<?= settings()['homepage'] ?>assets/js/cart.js"></script>
    <!-- Add Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<link rel="stylesheet" href="<?= settings()['homepage'] ?>assets/css/styles.css">
<link rel="stylesheet" href="<?= settings()['homepage'] ?>assets/css/footer.css">
<link rel="stylesheet" href="<?= settings()['homepage'] ?>assets/assets/owl.carousel.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>
<body>
    <div class="container">
    <nav class="customnavbar">
        <!-- Top Bar - Prussian Blue -->
        <div class="top-bar">
            <div class="logo-section">
                <div class="logo"><a href="index.php">ShopEase</a></div>
            </div>

            <!-- delivery location -->
            <div class="delivery-location">
                <span class="delivery-icon">🚚</span>
                <span>Select your delivery location</span>
            </div>
            
            <!-- search -->
            <div class="search-container">
                <input type="text" class="search-bar" placeholder="Search your products">
                <button class="search-btn">🔍</button>
            </div>
            
            <div class="right-section">
                <select class="language-selector">
                    <option>বাংলা</option>
                    <option>English</option>
                </select>
                
                <a href="#" class="app-download" title="Download App Now">
                    <span class="app-icon">📱</span>
                    <span >Download App Now</span>                   
                </a>
                
                <!-- Login logout registration -->

                <div class="auth-section">                    
                <?php
                    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == 'true') {
                 if ($_SESSION['role'] == 'admin') {
                    echo '<span><a title="Dashboard" href="admin/index.php">        
                        <span class="dashboard-icon btn btn-light">📊</span>
                        </a></span>';
                     //logout
                    echo '<span><a title="Logout" href="logout.php">      
                        <span class="logout-icon btn btn-light">🚪</span>
                        </a></span>';

                        }
                    else {
                    echo '<span><a class="btn btn-light" href="logout.php">Logout</a></span>';
                        }
                    } else {
                    echo '<span><a class="btn btn-light" href="login.php">Sign In</a>  <a class="btn btn-light" href="registration.php">Sign up</a></span>';
                    }
                ?>
                </div>
            </div>
        </div>
        
        <!-- Bottom Navigation - Azure Blue -->
        <div class="bottom-nav">
            <div class="nav-menu">
                <div class="nav-left">
                    <div class="menu-toggle" id="categoryToggle">
                        <span class="hamburger">☰</span>
                        <span>Select Category</span>
                    </div>
                    
                    <div class="nav-links">
                        <ul>
                            <li class="btn btn-outline-light"><a href="hot-deals.php">Hot Deals</a></li>
                            <li class="btn btn-outline-light"><a href="brands.php">Brands</a></li>
                        </ul>
    
                    </div>
                </div>
                <div class="nav-right">
                    <marquee>Free Shipping on Orders Over ৳1000!</marquee>
                </div>
                
                <!-- Cart Icon -->
                <div class="nav-right">
                    

                    
                    <button class= "icon nav-right-item btn btn-light" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCart" aria-controls="offcanvasCart">🛒</button>
                        <a href="cart.php" class="nav-right-item">(<span id="cartCountButton">0</span>)</a>
                    <!-- <button class="nav-right-item btn btn-light" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCart" aria-controls="offcanvasCart">
                        🛒 (<span id="cartCountButton">0</span>)
                    </button> -->
                </div>
            </div>
        </div>
    </nav>

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
            $conn = new mysqli("", "root", "", "haatbazar");
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
                'panio' => 'fas fa-mug-hot'
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