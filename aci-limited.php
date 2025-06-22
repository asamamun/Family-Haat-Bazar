<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/vendor/autoload.php';
use App\User;
use App\model\Category;
// use App\db;
// $conn = db::connect();
$db = new MysqliDb();
$page = "Home";

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Sample products
$products = [
    ['id' => 1, 'name' => 'Soyabean Oil', 'caption' => 'Fresh 5 Liter Soyabean Oil', 'price' => 895, 'image' => 'assets/ACI/ACI-Aerosol-Insect-Spray-250ml_1.jpg', 'weight' => '5L'],
    ['id' => 2, 'name' => 'Shampoo', 'caption' => 'Treseme Shampoo with Keratine', 'price' => 540, 'image' => 'assets/ACI/ACI-Aerosol-Insect-Spray-350ml_1.jpg', 'weight' => '580ml'],
    ['id' => 3, 'name' => 'Aam', 'caption' => 'Aam Premium Quality', 'price' => 362, 'image' => 'assets/ACI/ACI-Aroma-Chinigura-Rice-1kg_1.jpg', 'weight' => '1kg'],
    ['id' => 4, 'name' => 'Apple', 'caption' => 'Apple Fuji Premium', 'price' => 362, 'image' => 'assets/ACI/ACI-Aroma-Mustard-Oil-1L_1.jpg', 'weight' => '1kg'],
    ['id' => 5, 'name' => 'banana', 'caption' => 'Banana', 'price' => 362, 'image' => 'assets/ACI/ACI-Aroma-Mustard-Oil-250ml_1.jpg', 'weight' => '1kg'],
    ['id' => 6, 'name' => 'Dalim', 'caption' => 'Generic Product Caption', 'price' => 362, 'image' => 'assets/ACI/ACI-Aroma-Pure-Basmati-Rice-1kg_1.jpg', 'weight' => '1kg'],
    ['id' => 7, 'name' => 'Product 7', 'caption' => 'Generic Product Caption', 'price' => 362, 'image' => 'assets/ACI/ACI-Brown-Atta-1kg_1.jpg', 'weight' => '1kg'],
    ['id' => 8, 'name' => 'Product 8', 'caption' => 'Generic Product Caption', 'price' => 362, 'image' => 'assets/ACI/ACI-Fun-Chanachur-Hot-Spicy-300gm_1.jpg', 'weight' => '1kg'],
    ['id' => 9, 'name' => 'Product 9', 'caption' => 'Generic Product Caption', 'price' => 362, 'image' => 'assets/ACI/ACI-Fun-Fresh-Fry-Potato-Pallet-18020gm_1.jpg', 'weight' => '1kg'],
    ['id' => 10, 'name' => 'Product 10', 'caption' => 'Generic Product Caption', 'price' => 362, 'image' => 'assets/ACI/ACI-Nutrilife-Black-Rice-1kg_1.jpg', 'weight' => '1kg'],
    ['id' => 11, 'name' => 'Product 11', 'caption' => 'Generic Product Caption', 'price' => 362, 'image' => 'assets/ACI/ACI-Nutrilife-Brown-Rice-1kg_1.jpg.jpg', 'weight' => '1kg'],
    ['id' => 12, 'name' => 'Product 12', 'caption' => 'Generic Product Caption', 'price' => 362, 'image' => 'assets/ACI/aci-nutrilife-low-gi-rice.jpg.jpg', 'weight' => '1kg'],
    ['id' => 13, 'name' => 'Product 13', 'caption' => 'Generic Product Caption', 'price' => 362, 'image' => 'assets/ACI/ACI-Nutrilife-Rice-Bran-Oil-2L_1.jpg', 'weight' => '1kg'],
    ['id' => 14, 'name' => 'Product 14', 'caption' => 'Generic Product Caption', 'price' => 362, 'image' => 'assets/ACI/ACI-Nutrilife-Rice-Bran-Oil-5L-Bottle_1.jpg', 'weight' => '1kg'],
    ['id' => 15, 'name' => 'Product 15', 'caption' => 'Generic Product Caption', 'price' => 362, 'image' => 'assets/ACI/ACI-Salt-100gm-Jar_1.jpg', 'weight' => '1kg'],
    ['id' => 16, 'name' => 'Product 16', 'caption' => 'Generic Product Caption', 'price' => 362, 'image' => 'assets/ACI/ACI-Smart-Detergent-Powder-1kg_1.jpg', 'weight' => '1kg'],
    ['id' => 17, 'name' => 'Product 17', 'caption' => 'Generic Product Caption', 'price' => 362, 'image' => 'assets/ACI/Angelic-Air-Freshener-Citrus-Burst-300ml_1.jpg', 'weight' => '1kg'],
    ['id' => 18, 'name' => 'Product 18', 'caption' => 'Generic Product Caption', 'price' => 362, 'image' => 'assets/ACI/Angelic-Air-Freshener-Green-Valley-300ml_1.jpg', 'weight' => '1kg'],
    ['id' => 19, 'name' => 'Product 19', 'caption' => 'Generic Product Caption', 'price' => 362, 'image' => 'assets/ACI/Angelic-Air-Freshener-Misty-Wood-300ml_1.jpg', 'weight' => '1kg'],
    ['id' => 20, 'name' => 'Product 20', 'caption' => 'Generic Product Caption', 'price' => 362, 'image' => 'assets/ACI/Sunquick-Mango-Drink-250ml-Pet_1.jpg', 'weight' => '1kg'],    
    ['id' => 21, 'name' => 'Product 21', 'caption' => 'Generic Product Caption', 'price' => 362, 'image' => 'assets/ACI/Sunquick-Mixed-Berry-Drink-250ml-Pet_1.jpg', 'weight' => '1kg'],   
    ['id' => 22, 'name' => 'Product 22', 'caption' => 'Generic Product Caption', 'price' => 362, 'image' => 'assets/ACI/Sunquick-Orange-Drink-250ml-Pet_1.jpg', 'weight' => '1kg'],

];



// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'add') {
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity']++;
        } else {
            foreach ($products as $product) {
                if ($product['id'] === $product_id) {
                    $_SESSION['cart'][$product_id] = [
                        'id' => $product['id'],
                        'name' => $product['name'],
                        'price' => $product['price'],
                        'image' => $product['image'],
                        'quantity' => 1
                    ];
                }
            }
        }
    } elseif ($action === 'remove') {
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity']--;
            if ($_SESSION['cart'][$product_id]['quantity'] <= 0) {
                unset($_SESSION['cart'][$product_id]);
            }
        }
    } elseif ($action === 'remove_all') {
        unset($_SESSION['cart'][$product_id]);
    }
}

// Calculate total
function getTotal() {
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return number_format($total, 2);
}

// Get total items
function getTotalItems() {
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['quantity'];
    }
    return $total;
}
?>

<?php require __DIR__ . '/components/header.php'; ?>
<!-- content start -->
<div class="container my-4">
    <!-- Header -->
    <!-- <header class="bg-white shadow-lg sticky-top mb-4">
        <div class="container py-4">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 fw-bold text-dark">Shopping Store</h1>
                <div class="position-relative">
                    <button class="btn btn-primary px-4 py-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#cartSidebar" aria-controls="cartSidebar">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-cart" viewBox="0 0 16 16">
                            <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                        </svg>
                        Cart
                        <?php if (getTotalItems() > 0): ?>
                            <span class="badge bg-danger rounded-pill position-absolute top-0 end-0"><?php echo getTotalItems(); ?></span>
                        <?php endif; ?>
                    </button>
                </div>
            </div>
        </div>
    </header> -->
    <div class="fluid">
  <div class="row">
    <div class="col-12 d-flex justify-content-center">
      <!-- <div class="p-1" style="padding-left: 0 !important;"> -->
        <img src="assets/ACI/aci-banner.jpg">
      </div>
    </div>
  </div>
</div>

    <br>
    <div class="row g-3">
        <?php foreach ($products as $product): ?>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card h-100">
                    <?php if ($product['image']): ?>
                        <img src="<?php echo htmlspecialchars($product['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <?php endif; ?>
                    <div class="card-body text-center">
                        <h5 class="card-title mb-1"><?php echo htmlspecialchars($product['name']); ?></h5>
                        <p class="text-muted small mb-1"><?php echo htmlspecialchars($product['caption']); ?></p>
                        <p class="fw-bold mb-1 text-success">৳<?php echo number_format($product['price'], 2); ?> <span class="text-muted fw-normal small">Per Unit</span></p>
                        <p class="small mb-1"><?php echo htmlspecialchars($product['weight']); ?></p>
                        <p class="small text-muted">Delivery: 1-2 hours</p>
                        <form method="post">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <input type="hidden" name="action" value="add">
                            <button type="submit" class="btn btn-primary btn-sm w-100">Add to Cart</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Cart Sidebar -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="cartSidebar" aria-labelledby="cartSidebarLabel">
        <div class="offcanvas-header">
            <h2 class="offcanvas-title h4 fw-bold" id="cartSidebarLabel">Shopping Cart</h2>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <?php if (empty($_SESSION['cart'])): ?>
                <div class="text-center py-5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-cart text-muted mb-3" viewBox="0 0 16 16">
                        <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                    </svg>
                    <p class="text-muted fs-5">Your cart is empty</p>
                    <p class="text-secondary fs-6">Add some products to get started!</p>
                </div>
            <?php else: ?>
                <div class="mb-4">
                    <?php foreach ($_SESSION['cart'] as $item): ?>
                        <div class="d-flex align-items-center gap-3 p-3 bg-light border rounded mb-2">
                            <?php if ($item['image']): ?>
                                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width: 40px; height: 40px;">
                            <?php endif; ?>
                            <div class="flex-grow-1">
                                <h4 class="fs-6 fw-semibold text-dark"><?php echo htmlspecialchars($item['name']); ?></h4>
                                <p class="text-primary fw-bold">৳<?php echo number_format($item['price'], 2); ?></p>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <form method="post">
                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                    <input type="hidden" name="action" value="remove">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-dash" viewBox="0 0 16 16">
                                            <path d="M4 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 4 8z"/>
                                        </svg>
                                    </button>
                                </form>
                                <span class="fw-bold text-dark px-2"><?php echo $item['quantity']; ?></span>
                                <form method="post">
                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                    <input type="hidden" name="action" value="add">
                                    <button type="submit" class="btn btn-sm btn-outline-success">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16">
                                            <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                                        </svg>
                                    </button>
                                </form>
                                <form method="post">
                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                    <input type="hidden" name="action" value="remove_all">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                            <path d="M5.5 5.5A.5.5 0 0 1 6 5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1-.5-.5zm2.5 3a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5z"/>
                                            <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4.059 3.06A2 2 0 0 1 6 2h4a2 2 0 0 1 1.941 1.5l-.059.94H4.118zM3 5h10v9a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V5z"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="border-top pt-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="fs-5 fw-bold text-dark">Total:</span>
                        <span class="fs-4 fw-bold text-primary">৳<?php echo getTotal(); ?></span>
                    </div>
                    <button class="btn btn-success w-100 py-3 fs-5">
                        Checkout (<?php echo getTotalItems(); ?> items)
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<!-- content end -->
<?php require __DIR__ . '/components/footer.php'; 
$db->disconnect();
?>