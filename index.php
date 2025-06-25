<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/vendor/autoload.php';
$db = new MysqliDb ();
$page = "Home";
$db->where("is_active",1);
$products = $db->get('products');
// var_dump($products);
// exit;
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
 <!-- Our Products -->
    <div class="bg-light">
    <div class="container-fluid py-4">
        <div class="text-center mb-4">
            <h1 class="display-4 text-primary">Our Products</h1>
            <!-- <p class="lead text-muted">Discover amazing deals on top-quality items</p> -->
        </div>
        
        <div class="row g-2" id="productContainer">
            <!-- Products will be generated here -->
             <?php
                foreach ($products as $product) {
                    echo '<div class="col-3 mb-3">
                        <div class="card product-card h-100 shadow-sm">
                            <img src="'.settings()['root'].'assets/products/'.$product['image'].'" class="card-img-top product-image" alt="'.$product['name'].'">
                            <div class="card-body d-flex flex-column p-2">
                                <h6 class="card-title mb-2" style="font-size: 0.8rem; line-height: 1.2;">'.$product['name'].'</h6>
                                <!--<div class="mb-2">
                                    <span class="rating" style="font-size: 0.7rem;">1</span>
                                    <small class="text-muted ms-1">1</small>
                                </div>-->
                                <div class="mb-2">
                                    <span class="price" style="font-size: 0.9rem;">'.$product['selling_price'].'</span>
                                    <small class="original-price ms-1">'.$product['stock_quantity'].'</small>
                                </div>
                                <a class="btn btn-outline-primary btn-sm" href="product-details.php?id='.$product['id'].'">Details</a>
                                <button data-id="'.$product['id'].'" data-name="'.$product['name'].'" data-price="'.$product['selling_price'].'" data-quantity="1" class="btn btn-primary btn-sm btn-add-cart mt-auto cartBtn" style="font-size: 0.7rem; padding: 0.25rem 0.5rem;"> Add to Cart </button>
                            </div>
                        </div>
                    </div>';
                }
             ?>

        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>


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

    </script>
</div>

<?php require __DIR__ . '/components/footer.php'; ?>
<script>
$(document).ready(function() {
let cart = new Cart();
$("#cartCount").text(cart.getTotalItems());

        $(".cartBtn").click(function() {
           let id = $(this).data('id');
           let name = $(this).data('name');
           let price = $(this).data('price');
           let quantity = $(this).data('quantity');
           //add to cart class           
           let items = cart.addItem({id, name, price, quantity});
           console.log(items); 
           $("#cartCount").text(items.length);
        });
})
</script>
<?php $db->disconnect();?>
</body>
</html>

