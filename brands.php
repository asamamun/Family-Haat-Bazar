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
$page = "Brands";

$brands = $db->get('brands');
?>
<?php require __DIR__ . '/components/header.php'; ?>
<style>
    .brand-card {
        cursor: pointer;
    }
    
    .brand-card .card-img-top {
        height: 120px;
        width: 100%;
        object-fit: contain;
        object-position: center;
        padding: 10px;
        background-color: #f8f9fa;
    }
    
    .brand-card .card-body {
        padding: 0.75rem;
    }
    
    .brand-card .card-title {
        font-size: 0.9rem;
        line-height: 1.2;
        margin-bottom: 0;
    }
</style>
<!-- content start -->
<div class="container my-4">
    <div class="row g-3">
        <?php foreach ($brands as $brand) : ?>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card h-100 brand-card" data-brandid="<?= $brand['id'] ?>">
                    <img src="assets/brands/<?= $brand['logo'] ?>" class="card-img-top" alt="<?= $brand['name'] ?>">
                    <div class="card-body text-center">
                        <h5 class="card-title mb-1"><?= $brand['name'] ?></h5>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div id="product-container" class="row g-3 mt-4"></div>
    <div id="pagination-container" class="d-flex justify-content-center mt-4"></div>
</div>
<!-- content end -->
<?php require __DIR__ . '/components/footer.php'; ?>
<script>
    $(document).ready(function() {
        let currentPage = 1;
        let currentBrandId = null;

        function loadProducts(brandId, page) {
            currentBrandId = brandId;
            currentPage = page;
            $.ajax({
                url: 'apis/get-brand-products.php',
                type: 'GET',
                dataType: 'json',
                data: {
                    brand_id: brandId,
                    page: page
                },
                success: function(response) {
                    $('#product-container').empty();
                    if (response.products.length > 0) {
                        response.products.forEach(function(product) {
                            var productCard = `
                                <div class="col-6 col-md-4 col-lg-3">
                                    <div class="card h-100 d-flex flex-column">
                                        <a href="product-details.php?id=${product.id}" class="text-decoration-none text-dark">
                                            <img src="assets/products/${product.image}" class="card-img-top" alt="${product.name}" onerror="this.onerror=null;this.src='<?= settings()['logo'] ?>';">
                                            <div class="card-body flex-grow-1">
                                                <h5 class="card-title">${product.name}</h5>
                                                <p class="card-text">Price: ${product.selling_price}</p>
                                            </div>
                                        </a>
                                        <div class="card-footer bg-transparent border-0">
                                            <button class="btn btn-primary btn-add-cart w-100" data-product-id="${product.id}" data-product-name="${product.name}" data-product-price="${product.selling_price}" data-product-image="${product.image}">Add to Cart</button>
                                        </div>
                                    </div>
                                </div>`;
                            $('#product-container').append(productCard);
                        });
                    } else {
                        $('#product-container').html('<p class="text-center">No products found for this brand.</p>');
                    }
                    renderPagination(response.totalPages, response.currentPage);
                },
                error: function(xhr, status, error) {
                    console.error("An error occurred: " + status + " " + error);
                    $('#product-container').html('<p class="text-center">Error loading products.</p>');
                }
            });
        }

        function renderPagination(totalPages, currentPage) {
            $('#pagination-container').empty();
            if (totalPages > 1) {
                let paginationHtml = '<nav><ul class="pagination">';
                for (let i = 1; i <= totalPages; i++) {
                    paginationHtml += `<li class="page-item ${i === currentPage ? 'active' : ''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
                }
                paginationHtml += '</ul></nav>';
                $('#pagination-container').html(paginationHtml);
            }
        }

        $('.brand-card').on('click', function() {
            const brandId = $(this).data('brandid');
            loadProducts(brandId, 1);
        });

        $(document).on('click', '.page-link', function(e) {
            e.preventDefault();
            const page = $(this).data('page');
            if (page !== currentPage) {
                loadProducts(currentBrandId, page);
            }
        });
    });
</script>
<?php
$db->disconnect();
?>
