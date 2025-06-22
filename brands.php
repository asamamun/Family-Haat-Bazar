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
?>
<?php require __DIR__ . '/components/header.php'; ?>
<!-- content start -->
<div class="container my-4">
    <div class="row g-3">
        <!-- Card 1 -->
        <div class="col-6 col-md-4 col-lg-2">
    <div class="card h-100">
        <img src="" class="card-img-top" alt="">
        <div class="card-body text-center">
                <img src="assets/ACI/aci-logo.jpg" class="img-fluid mb-2" alt="ACI Limited Logo">
            <h5 class="card-title mb-1">ACI Limited</h5>
            <p class="small text-muted">Delivery: 1-2 hours</p>
            <a href="aci-limited.php" class="btn btn-primary btn-sm w-100">Find Your Products</a>
        </div>
    </div>
</div>

        <!-- Card 2 -->
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card h-100">
                <img src="assets/MGI/mgi-logo.jpg" class="img-fluid mb-2" alt="">
                <div class="card-body text-center">
                    <h5 class="card-title mb-1">mgi Limited</h5>
                    <p class="small text-muted">Delivery: 1-2 hours</p>
                    <a href="mgi-limited.php" class="btn btn-primary btn-sm w-100">Find Your Products</a>
                </div>
            </div>
        </div>
        <!-- Card 3 -->
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card h-100">
                <img src="assets/PRAN/pran-logo.jpg" class="img-fluid mb-2" alt="">
                <div class="card-body text-center">
                    <h5 class="card-title mb-1">Pran</h5>
                    <p class="small text-muted">Delivery: 1-2 hours</p>
                    <a href="pran.php" class="btn btn-primary btn-sm w-100">Find Your Products</a>
                </div>
            </div>
        </div>
        <!-- Card 4 -->
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card h-100">
                <img src="assets/AKIJ/akij-logo.jpg" class="img-fluid mb-2" alt="">
                <div class="card-body text-center">
                    <h5 class="card-title mb-1">Akij Food</h5>
                    <p class="small text-muted">Delivery: 1-2 hours</p>
                    <a href="akij-food.php" class="btn btn-primary btn-sm w-100">Find Your Products</a>
                </div>
            </div>
        </div>
        <!-- Card 5 -->
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card h-100">
                <div class="card-body text-center">
                    <img src="assets/PUSTI/pusti-logo.jpg" class="img-fluid mb-2" alt="">
                    <h5 class="card-title mb-1">PUSTI</h5>
                    <p class="small text-muted">Delivery: 1-2 hours</p>
                    <a href="pusti.php" class="btn btn-primary btn-sm w-100">Find Your Products</a>
                </div>
            </div>
        </div>
        <!-- Card 6 -->
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card h-100">
                <img src="assets/WF/well-food-logo.jpg" class="img-fluid mb-2" alt="">
                <div class="card-body text-center">
                    <h5 class="card-title mb-1">Well-Food</h5>
                    <p class="small text-muted">Delivery: 1-2 hours</p>
                    <a href="well-food.php" class="btn btn-primary btn-sm w-100">Find Your Products</a>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- content end -->
<?php require __DIR__ . '/components/footer.php'; 
$db->disconnect();
?>