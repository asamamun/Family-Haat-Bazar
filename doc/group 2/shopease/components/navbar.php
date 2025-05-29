<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShopEase Navbar</title>
    <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        
        body {
            padding-top: 120px; /* Compensate for fixed navbar height */
        }
        
        a {
            text-decoration: none;
            color: inherit;
        }
        
        /* Navbar styles */
        .navbar {
            width: 100%;
            position: fixed; /* Make navbar fixed */
            top: 0; /* Stick to the top */
            left: 0;
            z-index: 1000; /* Ensure navbar stays above other content */
        }
        
        /* Top Bar - Prussian Blue (#003153) */
        .top-bar {
            background-color: #003153; /* Prussian Blue */
            color: white;
            padding: 10px 5%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
        }
        
        .delivery-location, .app-download, .auth-section {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }
        
        .search-container {
            flex-grow: 1;
            max-width: 500px;
            display: flex;
            margin: 0 20px;
        }
        
        .search-bar {
            padding: 8px 15px;
            border: none;
            border-radius: 4px 0 0 4px;
            width: 100%;
        }
        
        .search-btn {
            padding: 8px 15px;
            border: none;
            background-color: #d4db0ec4;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
        }
        
        .right-section {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .language-selector {
            padding: 5px;
            border-radius: 4px;
            border: none;
            background-color: #f8f8f8;
        }
        
        /* Bottom Navigation - Azure Blue (#007FFF) */
        .bottom-nav {
            background-color: #007FFF; /* Azure Blue */
            color: white;
        }
        
        .nav-menu {
            padding: 10px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .nav-left {
            display: flex;
            align-items: center;
            gap: 30px; /* Space between menu toggle and nav links */
        }
        
        .menu-toggle {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            font-weight: bold;
        }
        
        .nav-links {
            display: flex;
            gap: 20px;
        }
        
        .nav-links a {
            padding: 5px 0;
            position: relative;
        }
        
        .nav-links a:hover {
            text-decoration: underline;
        }
        
        /* Nav Right - with 30px space from nav-left */
        .nav-right {
            display: flex;
            gap: 20px;
            margin-left: 30px; /* Creates the 30px space from nav-left */
        }
        
        .nav-right-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        /* Sample content to demonstrate scrolling */
        .content {
            height: 2000px;
            padding: 20px;
        }
        
        /* Responsive adjustments */
        @media (max-width: 1024px) {
            body {
                padding-top: 180px; /* Increased padding for mobile */
            }
            
            .top-bar {
                flex-direction: column;
                padding: 15px 5%;
            }
            
            .search-container {
                margin: 10px 0;
                max-width: 100%;
            }
            
            .nav-menu {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .nav-left {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
                width: 100%;
            }
            
            .nav-links {
                flex-direction: column;
                gap: 10px;
                width: 100%;
            }
            
            .nav-right {
                margin-left: 0;
                margin-top: 15px;
                width: 100%;
                justify-content: space-between;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <!-- Top Bar - Prussian Blue -->
        <div class="top-bar">
            <div class="logo-section">
                <div class="logo"><a href="index.html">ShopEase</a></div>
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
                <a href="#" class="app-download">
                    <span class="app-icon">📱</span>
                    <span>Download App Now</span>
                </a>
                
                <select class="language-selector">
                    <option>বাংলা</option>
                    <option>English</option>
                </select>
                
                <div class="auth-section">
                    <span class="user-icon">👤</span>
                    <span>Sign In / Sign up</span>
                </div>
            </div>
        </div>
        
        <!-- Bottom Navigation - Azure Blue -->
        <div class="bottom-nav">
            <div class="nav-menu">
                <div class="nav-left">
                    <div class="menu-toggle">
                        <span class="hamburger">☰</span>
                        <span>SHOP BY CATEGORY</span>
                    </div>
                    
                    <div class="nav-links">
                        <a href="index.html">Home</a>
                        <a href="hot-deals.html">Hot Deals</a>
                        <a href="brands.html">Brands</a>
                    </div>
                </div>
                <div class="nav-right">
                    <marquee><span style="color:rgb(252, 250, 250); font-family: Verdana, Geneva, Tahoma, sans-serif">Free Shipping on Orders Over ৳1000!</span></marquee>
                        
                </div>
                <div class="nav-right">
                    <a href="#" class="nav-right-item">
                        <span class="icon">🛒</span>
                        <span>Cart</span>
                    </a>
                    <a href="#" class="nav-right-item">
                        <span class="icon">📦</span>
                        <span>Orders</span>
                    </a>
                
                <div class="nav-right">
                    <a href="#" class="nav-right-item">
                        <span class="icon">🏪</span>
                        <span>Our outlets</span>
                    </a>
                    <a href="#" class="nav-right-item">
                        <span class="icon">❓</span>
                        <span>Help line</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>
</body>

</html>