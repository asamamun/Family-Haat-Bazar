<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if(!isset($_GET['id'])){
    header("HTTP/1.0 400 Bad Request");
    exit;
}
require __DIR__ . '/vendor/autoload.php';
$db = new MysqliDb ();
$page = "Product Details";
/* $db->where("id", intval($_GET['id']));
$product = $db->getOne('products'); */
 $db->join("categories c", "p.category_id = c.id", "LEFT");
        $db->join("subcategories sc", "p.subcategory_id = sc.id", "LEFT");
        $db->join("brands b", "p.brand = b.id", "LEFT");
        $db->orderBy("p.id", "DESC");
        $db->where("p.id", intval($_GET['id']));
        $products = $db->get("products p", null, "p.*, c.name as category_name, sc.name as subcategory_name, b.name as brand_name");

// Open Graph data for product page
if (!empty($products)) {
    $product = $products[0];
    $og_title = htmlspecialchars($product['name']) . " - " . settings()['companyname'];
    $og_description = !empty($product['short_description']) ? 
        htmlspecialchars($product['short_description']) : 
        htmlspecialchars($product['description']);
    $og_image = settings()['homepage'] . 'assets/products/' . htmlspecialchars($product['image']);
    $og_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $og_type = "product";
} else {
    // Fallback if product not found
    $og_title = "Product Not Found - " . settings()['companyname'];
    $og_description = "The requested product could not be found.";
    $og_image = settings()['homepage'] . ltrim(settings()['logo'], '/');
    $og_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $og_type = "website";
}
/* var_dump($products);

exit; */
/*
array(26) { ["id"]=> int(2) ["category_id"]=> int(5) ["subcategory_id"]=> int(16) ["name"]=> string(4) "Akij" ["slug"]=> string(2) "ac" ["description"]=> string(7) "dsfgdfg" ["short_description"]=> string(15) " sdfsd fdsaf sd" ["sku"]=> string(9) "idbac2ton" ["barcode"]=> string(16) "43543545fdgdfgfg" ["selling_price"]=> string(8) "45000.00" ["cost_price"]=> string(8) "42000.00" ["markup_percentage"]=> string(4) "0.00" ["pricing_method"]=> string(6) "manual" ["auto_update_price"]=> int(0) ["stock_quantity"]=> int(55) ["min_stock_level"]=> int(5) ["image"]=> string(28) "685b85aaebc84_1750828458.png" ["is_hot_item"]=> int(1) ["is_active"]=> int(1) ["weight"]=> string(5) "55.00" ["dimensions"]=> string(2) "55" ["created_at"]=> string(19) "2025-06-24 05:49:06" ["updated_at"]=> string(19) "2025-06-25 11:14:19" ["brand"]=> int(2) ["sort_order"]=> int(0) ["logo"]=> string(28) "685a2040b8017_1750736960.jpg" }
*/
?>
<?php require __DIR__ . '/components/header.php';?>
<!-- content start -->
<h1 style="text-align:center; color:red;">Product Details</h1>


 <!-- Our Products -->
    <div class="bg-light">
    <div class="container-fluid py-4">     
        <div class="row g-2" id="productContainer">
            <!--  -->
            <!--  -->
            <div class="container mt-5 mb-5">
    <div class="row d-flex justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="row">
                    <div class="col-md-6">
                        <div class="images p-3">
                            <div class="text-center p-4"> <img id="main-image" src="<?= settings()['root'] ?>assets/products/<?= $products[0]['image'] ?>" width="250" onerror="this.onerror=null;this.src='<?= settings()['logo'] ?>';"/> </div>

                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="product p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center"> <i class="fa fa-long-arrow-left"></i> <span class="ml-1">Back</span> </div> <i class="fa fa-shopping-cart text-muted"></i>
                            </div>
                            <div class="mt-4 mb-3"> <span class="text-uppercase text-muted brand"><?= $products[0]['brand_name'] ?></span>
                                <h5 class="text-uppercase"><?= $products[0]['name'] ?></h5>
                                <div class="price d-flex flex-row align-items-center"> <span class="act-price">Taka <?= $products[0]['selling_price'] ?></span>
                                    <!-- <div class="ml-2"> <small class="dis-price">$59</small> <span>40% OFF</span> </div> -->
                                </div>
                            </div>
                            <p class="about"><?= $products[0]['description'] ?></p>
                            <!-- <div class="sizes mt-5">
                                <h6 class="text-uppercase">Size</h6> <label class="radio"> <input type="radio" name="size" value="S" checked> <span>S</span> </label> <label class="radio"> <input type="radio" name="size" value="M"> <span>M</span> </label> <label class="radio"> <input type="radio" name="size" value="L"> <span>L</span> </label> <label class="radio"> <input type="radio" name="size" value="XL"> <span>XL</span> </label> <label class="radio"> <input type="radio" name="size" value="XXL"> <span>XXL</span> </label>
                            </div> -->
                            <div class="cart mt-4 align-items-center"> <button class="btn btn-danger text-uppercase mr-2 px-4 btn-add-cart" data-product-id="<?= $products[0]['id'] ?>" data-product-name="<?= htmlspecialchars($products[0]['name']) ?>" data-product-price="<?= $products[0]['selling_price'] ?>" data-product-image="<?= htmlspecialchars($products[0]['image']) ?>">Add to cart</button> <i class="fa fa-heart text-muted"></i> <i class="fa fa-share-alt text-muted"></i> </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
            <!--  -->
            <!--  -->


        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</div>

<?php require __DIR__ . '/components/footer.php'; ?>
<?php $db->disconnect();?>
</body>
</html>

