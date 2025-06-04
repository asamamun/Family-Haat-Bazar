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
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="h3 fw-bold text-dark">Cart</h1>
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
<!-- content end -->
<?php require __DIR__ . '/components/footer.php'; 
$db->disconnect();
?>