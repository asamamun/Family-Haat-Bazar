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
<div class="container my-4">
    <div class="row g-3">
        <!-- Card 1 -->
        <div class="col-6 col-md-4 col-lg-2">
            <a href="../brands/aci-limited.php" class="text-decoration-none text-dark">
            <div class="card h-100">
                <img src="" class="card-img-top" alt="">
                <div class="card-body text-center">
                    <h5 class="card-title mb-1">ACI Limited</h5>
                    <p class="small text-muted">Delivery: 1-2 hours</p>
                    <button class="btn btn-primary btn-sm w-100">Find Your Products</button>
                </div>
            </div>
        </div>
        <!-- Card 2 -->
        <div class="col-6 col-md-4 col-lg-2">
        <a href="../brands/mgi-limited.php" class="text-decoration-none text-dark">
            <div class="card h-100">
                <img src="" class="card-img-top" alt="">
                <div class="card-body text-center">
                    <h5 class="card-title mb-1">mgi Limited</h5>
                    <p class="small text-muted">Delivery: 1-2 hours</p>
                    <button class="btn btn-primary btn-sm w-100">Find Your Products</button>
                </div>
            </div>
        </div>
        <!-- Card 3 -->
        <div class="col-6 col-md-4 col-lg-2">
        <a href="../brands/pran-rfl.php" class="text-decoration-none text-dark">
            <div class="card h-100">
                <div class="card-body text-center">
                    <h5 class="card-title mb-1">Pran-RFL</h5>
                    <p class="small text-muted">Delivery: 1-2 hours</p>
                    <button class="btn btn-primary btn-sm w-100">Find Your Products</button>
                </div>
            </div>
        </div>
        <!-- Card 4 -->
        <div class="col-6 col-md-4 col-lg-2">
        <a href="../brands/akij-food.php" class="text-decoration-none text-dark">
            <div class="card h-100">
                <div class="card-body text-center">
                    <h5 class="card-title mb-1">Akij Food</h5>
                    <p class="small text-muted">Delivery: 1-2 hours</p>
                    <button class="btn btn-primary btn-sm w-100">Find Your Products</button>
                </div>
            </div>
        </div>
        <!-- Card 5 -->
        <div class="col-6 col-md-4 col-lg-2">
        <a href="../brands/acmie.php" class="text-decoration-none text-dark">
            <div class="card h-100">
                <div class="card-body text-center">
                    <h5 class="card-title mb-1">Acmie Limited</h5>
                    <p class="small text-muted">Delivery: 1-2 hours</p>
                    <button class="btn btn-primary btn-sm w-100">Find Your Products</button>
                </div>
            </div>
        </div>
        <!-- Card 6 -->
        <div class="col-6 col-md-4 col-lg-2">
        <a href="../brands/well-food.php" class="text-decoration-none text-dark">
            <div class="card h-100">
                <div class="card-body text-center">
                    <h5 class="card-title mb-1">Well-Food</h5>
                    <p class="small text-muted">Delivery: 1-2 hours</p>
                    <button class="btn btn-primary btn-sm w-100">Find Your Products</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- content end -->

<?php require __DIR__ . '/components/footer.php'; 
$db->disconnect();
?>
