<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/vendor/autoload.php';
use App\User;
use App\model\Category;
// use App\db;
// $conn = db::connect();
$db = new MysqliDb ();
$page = "Home";
?>
<?php require __DIR__ . '/components/header.php';?>
<!-- content start -->
<h1 style="text-align:center; color:red;">Welcome to ShopEase</h1>
<div><span style="font-family:sansherif">Our hot products</span></div>
<div class="owl-carousel owl-theme">
    <div class="item"><h4><img src="assets/images/fruits/green-tea.jpg" alt="green-tea"></h4><span>Green Tea</span></div>
    <div class="item"><h4><img src="assets/images/fruits/halim-mix.jpg" alt="halim-mix"</h4></div>
    <div class="item"><h4><img src="assets/images/fruits/apple.jpg" alt=""</h4></div>
    <div class="item"><h4><img src="assets/images/fruits/Aam.jpg" alt=""</h4></div>
    <div class="item"><h4><img src="assets/images/fruits/Dalim.jpg" alt="green-tea"</h4></div>
    <div class="item"><h4><img src="assets/images/fruits/banana.jpg" alt="green-tea"</h4></div>
    <div class="item"><h4><img src="assets/images/fruits/greenapple.jpg" alt="green-tea"</h4></div>
    <div class="item"><h4><img src="assets/images/fruits/Guava.jpg" alt="green-tea"</h4></div>
    <div class="item"><h4><img src="assets/images/fruits/Malta.jpg" alt="green-tea"</h4></div>
    <div class="item"><h4><img src="assets/images/fruits/Naspati.jpg" alt="green-tea"0</h4></div>
    <div class="item"><h4><img src="assets/images/fruits/pepe.jpg" alt="green-tea"1</h4></div>
    <div class="item"><h4><img src="assets/images/fruits/pineapple.jpg" alt="green-tea"2</h4></div>
</div>
<!-- content end -->
 <!-- Feature Products -->
    <div class="bg-light">
    <div class="container-fluid py-4">
        <div class="text-center mb-4">
            <h1 class="display-4 text-primary">Our Products</h1>
            <!-- <p class="lead text-muted">Discover amazing deals on top-quality items</p> -->
        </div>
        
        <div class="row g-2" id="productContainer">
            <!-- Products will be generated here -->
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sample product data
        const products = [
            { name: "Wireless Headphones", price: 89.99, originalPrice: 129.99, rating: 4.5, image: "https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=300&h=200&fit=crop" },
            { name: "Smart Watch", price: 199.99, originalPrice: 249.99, rating: 4.8, image: "https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=300&h=200&fit=crop" },
            { name: "Bluetooth Speaker", price: 49.99, originalPrice: 79.99, rating: 4.3, image: "https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=300&h=200&fit=crop" },
            { name: "Laptop Stand", price: 34.99, originalPrice: 49.99, rating: 4.6, image: "https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=300&h=200&fit=crop" },
            { name: "USB-C Hub", price: 29.99, originalPrice: 39.99, rating: 4.4, image: "https://images.unsplash.com/photo-1558618047-3c8c76ca7d13?w=300&h=200&fit=crop" },
            { name: "Wireless Mouse", price: 24.99, originalPrice: 34.99, rating: 4.2, image: "https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=300&h=200&fit=crop" },
            { name: "Phone Case", price: 19.99, originalPrice: 29.99, rating: 4.7, image: "https://images.unsplash.com/photo-1556656793-08538906a9f8?w=300&h=200&fit=crop" },
            { name: "Power Bank", price: 39.99, originalPrice: 59.99, rating: 4.5, image: "https://images.unsplash.com/photo-1609081219090-a6d81d3085bf?w=300&h=200&fit=crop" },
            { name: "Tablet", price: 299.99, originalPrice: 399.99, rating: 4.6, image: "https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=300&h=200&fit=crop" },
            { name: "Gaming Keyboard", price: 79.99, originalPrice: 99.99, rating: 4.8, image: "https://images.unsplash.com/photo-1541140532154-b024d705b90a?w=300&h=200&fit=crop" },
            { name: "Webcam", price: 59.99, originalPrice: 79.99, rating: 4.3, image: "https://images.unsplash.com/photo-1587825140708-dfaf72ae4b04?w=300&h=200&fit=crop" },
            { name: "Monitor", price: 249.99, originalPrice: 329.99, rating: 4.7, image: "https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?w=300&h=200&fit=crop" },
            // Duplicate products to show multiple rows
            { name: "Wireless Earbuds", price: 69.99, originalPrice: 99.99, rating: 4.4, image: "https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=300&h=200&fit=crop" },
            { name: "Smart Speaker", price: 79.99, originalPrice: 99.99, rating: 4.6, image: "https://images.unsplash.com/photo-1543512214-318c7553f230?w=300&h=200&fit=crop" },
            { name: "Fitness Tracker", price: 89.99, originalPrice: 119.99, rating: 4.5, image: "https://images.unsplash.com/photo-1575311373937-040b8e1fd5b6?w=300&h=200&fit=crop" },
            { name: "Portable Charger", price: 19.99, originalPrice: 29.99, rating: 4.3, image: "https://images.unsplash.com/photo-1609081219090-a6d81d3085bf?w=300&h=200&fit=crop" },
            { name: "Cable Organizer", price: 14.99, originalPrice: 24.99, rating: 4.2, image: "https://images.unsplash.com/photo-1558618047-3c8c76ca7d13?w=300&h=200&fit=crop" },
            { name: "Phone Holder", price: 12.99, originalPrice: 19.99, rating: 4.4, image: "https://images.unsplash.com/photo-1556656793-08538906a9f8?w=300&h=200&fit=crop" },
            { name: "LED Strip Lights", price: 29.99, originalPrice: 39.99, rating: 4.6, image: "https://images.unsplash.com/photo-1513475382585-d06e58bcb0e0?w=300&h=200&fit=crop" },
            { name: "Desk Lamp", price: 39.99, originalPrice: 59.99, rating: 4.5, image: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300&h=200&fit=crop" },
            { name: "Gaming Mouse", price: 54.99, originalPrice: 74.99, rating: 4.7, image: "https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=300&h=200&fit=crop" },
            { name: "Microphone", price: 99.99, originalPrice: 129.99, rating: 4.8, image: "https://images.unsplash.com/photo-1590602847861-f357a9332bbc?w=300&h=200&fit=crop" },
            { name: "Camera Lens", price: 199.99, originalPrice: 249.99, rating: 4.6, image: "https://images.unsplash.com/photo-1502920917128-1aa500764cbd?w=300&h=200&fit=crop" },
            { name: "Tripod", price: 49.99, originalPrice: 69.99, rating: 4.4, image: "https://images.unsplash.com/photo-1502920917128-1aa500764cbd?w=300&h=200&fit=crop" }
        ];

        function generateStars(rating) {
            const fullStars = Math.floor(rating);
            const hasHalfStar = rating % 1 !== 0;
            let stars = '';
            
            for (let i = 0; i < fullStars; i++) {
                stars += '★';
            }
            if (hasHalfStar) {
                stars += '☆';
            }
            return stars;
        }

        function renderProducts() {
            const container = document.getElementById('productContainer');
            container.innerHTML = '';
            
            products.forEach((product, index) => {
                const productCard = `
                    <div class="col-3 mb-3">
                        <div class="card product-card h-100 shadow-sm">
                            <img src="${product.image}" class="card-img-top product-image" alt="${product.name}">
                            <div class="card-body d-flex flex-column p-2">
                                <h6 class="card-title mb-2" style="font-size: 0.8rem; line-height: 1.2;">${product.name}</h6>
                                <div class="mb-2">
                                    <span class="rating" style="font-size: 0.7rem;">${generateStars(product.rating)}</span>
                                    <small class="text-muted ms-1">(${product.rating})</small>
                                </div>
                                <div class="mb-2">
                                    <span class="price" style="font-size: 0.9rem;">$${product.price}</span>
                                    <small class="original-price ms-1">$${product.originalPrice}</small>
                                </div>
                                <button class="btn btn-primary btn-sm btn-add-cart mt-auto" style="font-size: 0.7rem; padding: 0.25rem 0.5rem;">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                container.innerHTML += productCard;
            });
        }

        // Add to cart functionality
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-add-cart')) {
                const productName = e.target.closest('.card').querySelector('.card-title').textContent;
                
                // Create a temporary success message
                const originalText = e.target.textContent;
                e.target.textContent = 'Added!';
                e.target.classList.remove('btn-primary');
                e.target.classList.add('btn-success');
                
                setTimeout(() => {
                    e.target.textContent = originalText;
                    e.target.classList.remove('btn-success');
                    e.target.classList.add('btn-primary');
                }, 1500);
            }
        });

        // Initialize products
        renderProducts();
    </script>
</div>

<?php require __DIR__ . '/components/footer.php'; ?>
<?php $db->disconnect();?>

