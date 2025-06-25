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
    <!-- Add Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<link rel="stylesheet" href="<?= settings()['homepage'] ?>assets/css/styles.css">
<link rel="stylesheet" href="<?= settings()['homepage'] ?>assets/css/footer.css">
<link rel="stylesheet" href="<?= settings()['homepage'] ?>assets/assets/owl.carousel.min.css">

</head>
<body>
    <div class="container">
    <nav class="customnavbar">
        <!-- Top Bar - Prussian Blue -->
        <div class="top-bar">
            <div class="logo-section">
                <div class="logo"><a href="index.php">ShopEase</a></div>
            </div>
            
            <div class="delivery-location">
                <span class="delivery-icon">🚚</span>
                <span>Select your delivery location</span>
            </div>
            
            <div class="search-container">
                <input type="text" class="search-bar" placeholder="Search your products">
                <button class="search-btn">🔍</button>
            </div>
            
            <div class="right-section">
                <a href="#" class="app-download" title="Download App Now">
                    <span class="app-icon">📱</span>
                    <span>Download App Now</span>                   
                </a>
                
                <select class="language-selector">
                    <option>বাংলা</option>
                    <option>English</option>
                </select>
                
                <div class="auth-section">                    
<?php
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == 'true') {
    if ($_SESSION['role'] == 'admin') {
        echo '<span><a title="Dashboard" href="admin/index.php">        
        <span class="dashboard-icon">📊</span>
        </a></span>';
        //logout
        echo '<span><a title="Logout" href="logout.php">      
        <span class="logout-icon">🚪</span>
        </a></span>';

    }
    else {
        echo '<span><a href="logout.php">Logout</a></span>';
    }
} else {
    echo '<span><a href="login.php">Sign In</a> / <a href="registration.php">Sign up</a></span>';
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
                        <!-- <a href="index.html">Home</a> -->
                        <!-- <a href="hot-deals.html">Hot Deals</a> -->
                        <a href="hot-deals.php">Hot Deals</a>
                        
                        <a href="brands.php">Brands</a>
                    </div>
                </div>
                <div class="nav-right">
                    <marquee>Free Shipping on Orders Over ৳1000!</marquee>
                </div>
                <div class="nav-right">
                    <a href="cart.php" class="nav-right-item">
                        <span class="icon">🛒</span>
                        (<span id="cartCount">0</span>)
                    </a>
                    
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Category Sidebar -->
    <div class="category-sidebar" id="categorySidebar">
        <div class="sidebar-header">
            <h5 class="mb-0">Shop by Category</h5>
            <button class="sidebar-close-btn" id="closeSidebar">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <ul class="category-list">
            <li class="category-item">
                <a href="#" class="category-link" data-category="food">
                    <span><i class="fas fa-apple-alt category-icon"></i>Food & Beverages</span>
                    <i class="fas fa-chevron-right"></i>
                </a>
                <ul class="subcategory-list">
                    <li><a href="fresh-fruits.php" class="subcategory-link">Fresh Fruits</a></li>
                    <li><a href="#" class="subcategory-link">Vegetables</a></li>
                    <li><a href="#" class="subcategory-link">Snacks & Cookies</a></li>
                    <li><a href="#" class="subcategory-link">Beverages</a></li>
                    <li><a href="#" class="subcategory-link">Dairy Products</a></li>
                </ul>
            </li>
            <li class="category-item">
                <a href="#" class="category-link" data-category="baby-care">
                    <span><i class="fas fa-baby category-icon"></i>Baby Care</span>
                    <i class="fas fa-chevron-right"></i>
                </a>
                <ul class="subcategory-list">
                    <li><a href="#" class="subcategory-link">Diapers</a></li>
                    <li><a href="#" class="subcategory-link">Baby Food</a></li>
                    <li><a href="#" class="subcategory-link">Toys</a></li>
                    <li><a href="#" class="subcategory-link">Baby Clothing</a></li>
                </ul>
            </li>
            <li class="category-item">
                <a href="#" class="category-link" data-category="home-kitchen">
                    <span><i class="fas fa-home category-icon"></i>Home & Kitchen</span>
                    <i class="fas fa-chevron-right"></i>
                </a>
                <ul class="subcategory-list">
                    <li><a href="#" class="subcategory-link">Cookware</a></li>
                    <li><a href="#" class="subcategory-link">Kitchen Appliances</a></li>
                    <li><a href="#" class="subcategory-link">Storage Solutions</a></li>
                    <li><a href="#" class="subcategory-link">Cleaning Supplies</a></li>
                </ul>
            </li>
            <li class="category-item">
                <a href="#" class="category-link" data-category="womens-corner">
                    <span><i class="fas fa-female category-icon"></i>Women's Corner</span>
                    <i class="fas fa-chevron-right"></i>
                </a>
                <ul class="subcategory-list">
                    <li><a href="#" class="subcategory-link">Fashion & Clothing</a></li>
                    <li><a href="#" class="subcategory-link">Accessories</a></li>
                    <li><a href="#" class="subcategory-link">Footwear</a></li>
                    <li><a href="#" class="subcategory-link">Handbags</a></li>
                </ul>
            </li>
            <li class="category-item">
                <a href="#" class="category-link" data-category="beauty-health">
                    <span><i class="fas fa-heart category-icon"></i>Beauty & Health</span>
                    <i class="fas fa-chevron-right"></i>
                </a>
                <ul class="subcategory-list">
                    <li><a href="#" class="subcategory-link">Skincare</a></li>
                    <li><a href="#" class="subcategory-link">Makeup</a></li>
                    <li><a href="#" class="subcategory-link">Hair Care</a></li>
                    <li><a href="#" class="subcategory-link">Health Supplements</a></li>
                </ul>
            </li>
            <li class="category-item">
                <a href="#" class="category-link" data-category="electronics">
                    <span><i class="fas fa-laptop category-icon"></i>Electronics</span>
                    <i class="fas fa-chevron-right"></i>
                </a>
                <ul class="subcategory-list">
                    <li><a href="#" class="subcategory-link">Mobile Phones</a></li>
                    <li><a href="#" class="subcategory-link">Computers</a></li>
                    <li><a href="#" class="subcategory-link">Electronics Accessories</a></li>
                </ul>
            </li>
        </ul>
    </div>
    <!-- Main Content -->
    <div class="main-content">
        <?php require __DIR__ . '/dismissalert.php';?>
        <?php require __DIR__ . '/sessiondata.php';?>