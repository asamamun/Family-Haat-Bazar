<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShopEase - Footer</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .footer {
            background-color: #121212;
            color: #ffffff;
            padding: 40px 0 20px;
        }
        .footer a {
            color: #adb5bd;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .footer a:hover {
            color: #ffffff;
            text-decoration: none;
        }
        .footer h5 {
            color: #ffffff;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .footer h6 {
            color: #ffffff;
            margin-bottom: 15px;
            font-weight: 500;
        }
        .footer ul li {
            margin-bottom: 10px;
        }
        .social-links a {
            display: inline-block;
            width: 40px;
            height: 40px;
            background-color: #343a40;
            color: #ffffff !important;
            border-radius: 50%;
            text-align: center;
            line-height: 40px;
            margin-right: 10px;
            transition: all 0.3s ease;
        }
        .social-links a:hover {
            background-color: #007bff;
            transform: translateY(-3px);
        }
        .copyright {
            border-top: 1px solid #343a40;
            padding-top: 20px;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <h5><i class="fas fa-shopping-bag me-2"></i>ShopEase</h5>
                    <p class="text-muted">Your trusted online shopping partner delivering quality products to your doorstep.</p>
                    <div class="mt-3">
                        <img src="https://via.placeholder.com/120x40?text=Payment+Methods" alt="Payment Methods" class="img-fluid">
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <h6>Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="#"><i class="fas fa-chevron-right me-2"></i>About Us</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right me-2"></i>Contact</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right me-2"></i>Privacy Policy</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right me-2"></i>Terms & Conditions</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right me-2"></i>Return Policy</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <h6>Categories</h6>
                    <ul class="list-unstyled">
                        <li><a href="#"><i class="fas fa-chevron-right me-2"></i>Electronics</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right me-2"></i>Fashion</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right me-2"></i>Home & Kitchen</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right me-2"></i>Beauty</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right me-2"></i>Groceries</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <h6>Contact Info</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-map-marker-alt me-2"></i> 123 Street, Dhaka, Bangladesh</li>
                        <li><i class="fas fa-phone me-2"></i> +880 1700-000000</li>
                        <li><i class="fas fa-envelope me-2"></i> info@shopease.com</li>
                    </ul>
                    <div class="mt-3 social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                    <div class="mt-3">
                        <h6>Download Our App</h6>
                        <div class="d-flex gap-2">
                            <a href="#"><img src="https://via.placeholder.com/120x40?text=App+Store" alt="App Store" class="img-fluid"></a>
                            <a href="#"><img src="https://via.placeholder.com/120x40?text=Play+Store" alt="Play Store" class="img-fluid"></a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row copyright">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0">&copy; <script>document.write(new Date().getFullYear())</script> ShopEase. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <p class="mb-0">Designed with <i class="fas fa-heart text-danger"></i> by ShopEase Team</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>