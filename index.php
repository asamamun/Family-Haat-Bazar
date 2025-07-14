<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/vendor/autoload.php';
$db = new MysqliDb();
$page = "Home";

// Fetch hot items for the carousel
$db->where('is_hot_item', 1);
$hotItems = $db->get('products');

?>
<?php require __DIR__ . '/components/header.php'; ?>
<br>
<!-- content start -->
<h1 style="text-align:center; font-weight:bold; color:red; ">Welcome to ShopEase</h1>
<div><span style="font-family:sansherif">Our hot products</span></div>
<div class="owl-carousel owl-theme">
    <?php foreach ($hotItems as $item): ?>
        <div class="item">
            <a href="product-details.php?id=<?= $item['id'] ?>" class="text-decoration-none text-dark">
                <h4><img src="assets/products/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" onerror="this.onerror=null;this.src='<?= settings()['logo'] ?>';"></h4>
                <span><?php echo htmlspecialchars($item['name']); ?></span>
            </a>
        </div>
    <?php endforeach; ?>
</div>
<!-- content end -->
<!-- Our Products -->
<div class="bg-light">
    <div class="container-fluid py-4">
        <div class="text-center mb-4">
            <h1 class="display-4 text-primary">Our Products</h1>
        </div>

        <!-- Search and Filter -->
        <div class="row mb-4">
            <div class="col-md-6 offset-md-3">
                <div class="input-group">
                    <input type="text" id="searchInput" class="form-control" placeholder="Search for products...">
                    <button id="searchButton" class="btn btn-primary">Search</button>
                </div>
            </div>
        </div>

        <div id="filter-info" class="text-center mb-3"></div>

        <div class="row g-2" id="productContainer">
            <!-- Products will be loaded here via AJAX -->
        </div>

        <!-- Pagination -->
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center" id="pagination"></ul>
        </nav>
    </div>
</div>

<?php require __DIR__ . '/components/footer.php'; ?>
<script>
    const rootUrl = '<?= settings()['root'] ?>';
    $(document).ready(function() {
        let currentPage = 1;
        let currentCategory = <?= isset($_GET['category']) ? (int)$_GET['category'] : 'null' ?>;
        let currentSubcategory = <?= isset($_GET['subcategory']) ? (int)$_GET['subcategory'] : 'null' ?>;
        let currentSearch = null;

        function loadProducts() {
            $.ajax({
                url: 'apis/get-products.php',
                type: 'GET',
                data: {
                    page: currentPage,
                    category: currentCategory,
                    subcategory: currentSubcategory,
                    search: currentSearch
                },
                dataType: 'json',
                success: function(response) {
                    $('#productContainer').empty();
                    if (response.products.length > 0) {
                        response.products.forEach(function(product) {
                            var productHtml = `
                                <div class="col-3 mb-3">
                                    <div class="card product-card h-100 shadow-sm d-flex flex-column">
                                        <img src="${rootUrl}assets/products/${product.image}" class="card-img-top product-image" alt="${product.name}" onerror="this.onerror=null;this.src='<?= settings()['logo'] ?>';">
                                        <div class="card-body flex-grow-1 p-2">
                                            <h6 class="card-title mb-2" style="font-size: 0.8rem; line-height: 1.2;">${product.name}</h6>
                                            <div class="mb-2">
                                                <span class="price" style="font-size: 0.9rem;">${product.selling_price}</span>
                                                <small class="original-price ms-1">${product.stock_quantity}</small>
                                            </div>
                                            <a class="btn btn-outline-primary btn-sm" href="product-details.php?id=${product.id}">Details</a>
                                        </div>
                                        <div class="card-footer bg-transparent border-0">
                                            <button data-product-id="${product.id}" data-product-name="${product.name}" data-product-price="${product.selling_price}" data-quantity="1" data-product-image="${product.image}" class="btn btn-primary btn-sm btn-add-cart w-100 cartBtn" style="font-size: 0.7rem; padding: 0.25rem 0.5rem;"> Add to Cart </button>
                                        </div>
                                    </div>
                                </div>`;
                            $('#productContainer').append(productHtml);
                        });
                    } else {
                        $('#productContainer').html('<p class="text-center">No products found.</p>');
                    }

                    // Render pagination
                    renderPagination(response.total_pages, response.current_page);

                    // Show filter info
                    if (response.subcategory_name) {
                        $('#filter-info').html(`
                            <span class="me-2">Filtered by: <strong>${response.subcategory_name}</strong></span>
                            <button id="clearFilter" class="btn btn-sm btn-outline-danger">Clear Filter</button>
                        `);
                    } else {
                        $('#filter-info').empty();
                    }
                }
            });
        }

        function renderPagination(totalPages, currentPage) {
            $('#pagination').empty();
            for (let i = 1; i <= totalPages; i++) {
                const liClass = (i === currentPage) ? 'page-item active' : 'page-item';
                const pageLink = `<li class="${liClass}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
                $('#pagination').append(pageLink);
            }
        }

        // Initial load
        loadProducts();

        // Event handlers
        $('#searchButton').on('click', function() {
            currentSearch = $('#searchInput').val();
            currentPage = 1;
            loadProducts();
        });

        $(document).on('click', '.category-filter', function(e) {
            e.preventDefault();
            currentCategory = $(this).data('category-id');
            currentSubcategory = null;
            currentPage = 1;
            loadProducts();
        });

        $(document).on('click', '.subcategory-filter', function(e) {
            e.preventDefault();
            currentSubcategory = $(this).data('subcategory-id');
            currentPage = 1;
            loadProducts();
        });

        $(document).on('click', '#clearFilter', function() {
            currentCategory = null;
            currentSubcategory = null;
            currentPage = 1;
            loadProducts();
        });

        $(document).on('click', '.page-link', function(e) {
            e.preventDefault();
            currentPage = $(this).data('page');
            loadProducts();
        });

        // Initialize Owl Carousel
        $('.owl-carousel').owlCarousel({
            loop:true,
            margin:10,
            nav:true,
            autoplay:true,
            autoplayTimeout:3000,
            autoplayHoverPause:true,
            responsive:{
                0:{
                    items:1
                },
                600:{
                    items:3
                },
                1000:{
                    items:5
                }
            }
        });
    });
</script>
<?php $db->disconnect(); ?>
</body>
</html>