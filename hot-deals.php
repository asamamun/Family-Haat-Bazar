<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/vendor/autoload.php';
$db = new MysqliDb();
$page = "Hot Deals";

// Pagination
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($currentPage - 1) * $limit;

// Fetch hot items from the database with pagination
$db->where('is_hot_item', 1);
$products = $db->get('products', [$offset, $limit]);

// Get total count for pagination
$db->where('is_hot_item', 1);
$totalProducts = $db->getValue('products', "count(*)");
$totalPages = ceil($totalProducts / $limit);

?>

<?php require __DIR__ . '/components/header.php'; ?>
<style>
    .card .card-img-top {
        height: 150px;
        width: 100%;
        object-fit: contain;
        object-position: center;
        padding: 10px;
        background-color: #f8f9fa;
    }
    
    .card-body {
        min-height: 140px;
        display: flex;
        flex-direction: column;
    }
    
    .card-body .flex-grow-1 {
        flex-grow: 1;
        display: flex;
        align-items: flex-start;
    }
    
    .card-title {
        font-size: 0.9rem;
        line-height: 1.2;
    }
    
    .card-footer {
        padding: 0.5rem;
    }
</style>
<!-- content start -->
<div class="container my-4">
    <div class="fluid">
        <div class="row">
            <div class="col-12 d-flex justify-content-center">
                <img src="assets/images/fruits/hot-deals.png">
            </div>
        </div>
    </div>

    <br>
    <div class="row g-3">
        <?php foreach ($products as $product): ?>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card h-100">
                    <a href="product-details.php?id=<?= $product['id'] ?>" class="text-decoration-none text-dark">
                        <?php if ($product['image']): ?>
                            <img src="assets/products/<?php echo htmlspecialchars($product['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>" onerror="this.onerror=null;this.src='<?= settings()['logo'] ?>';">
                        <?php endif; ?>
                        <div class="card-body text-center d-flex flex-column">
                            <h5 class="card-title mb-1"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <p class="text-muted small mb-1 flex-grow-1"><?php echo htmlspecialchars($product['short_description']); ?></p>
                            <p class="fw-bold mb-1 text-success">৳<?php echo number_format($product['selling_price'], 2); ?> <span class="text-muted fw-normal small">Per Unit</span></p>
                        </div>
                    </a>
                    <div class="card-footer bg-transparent border-0">
                        <button class="btn btn-primary btn-sm w-100 btn-add-cart" data-product-id="<?= $product['id'] ?>" data-product-name="<?= htmlspecialchars($product['name']) ?>" data-product-price="<?= $product['selling_price'] ?>" data-product-image="<?= htmlspecialchars($product['image']) ?>">Add to Cart</button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <nav aria-label="Page navigation" class="mt-4">
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php if ($i === $currentPage) echo 'active'; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>
<!-- content end -->
<?php require __DIR__ . '/components/footer.php'; ?>

<?php
$db->disconnect();
?>
