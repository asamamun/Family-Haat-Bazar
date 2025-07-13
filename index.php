<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/vendor/autoload.php';
$db = new MysqliDb();
$page = "Home";
?>
<?php require __DIR__ . '/components/header.php'; ?>
<br>
<!-- content start -->
<h1 style="text-align:center; font-weight:bold; color:red; ">Welcome to ShopEase</h1>
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
                                    <div class="card product-card h-100 shadow-sm">
                                        <img src="${rootUrl}assets/products/${product.image}" class="card-img-top product-image" alt="${product.name}">
                                        <div class="card-body d-flex flex-column p-2">
                                            <h6 class="card-title mb-2" style="font-size: 0.8rem; line-height: 1.2;">${product.name}</h6>
                                            <div class="mb-2">
                                                <span class="price" style="font-size: 0.9rem;">${product.selling_price}</span>
                                                <small class="original-price ms-1">${product.stock_quantity}</small>
                                            </div>
                                            <a class="btn btn-outline-primary btn-sm" href="product-details.php?id=${product.id}">Details</a>
                                            <button data-id="${product.id}" data-name="${product.name}" data-price="${product.selling_price}" data-quantity="1" class="btn btn-primary btn-sm btn-add-cart mt-auto cartBtn" style="font-size: 0.7rem; padding: 0.25rem 0.5rem;"> Add to Cart </button>
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

        $(document).on('click', '.cartBtn', function() {
            let id = $(this).data('id');
            let name = $(this).data('name');
            let price = $(this).data('price');
            let quantity = $(this).data('quantity');
            let items = cart.addItem({ id, name, price, quantity });
            $('#cartCountButton').text(items.length);
            showCartItemsOffCanvas(items);
        });

        window.cart = new Cart();
        function updateCartDisplay() {
            let allitems = cart.getSummary();
            $("#cartCountButton").text(cart.getTotalItems());
            $("#grandTotalCanvas").text(parseFloat(cart.getTotalPrice()).toFixed(2));
            populateItems(allitems.items, "#cartContent table tbody");
        }

        function populateItems(items, tableId) {
            $(tableId).html("");
            if (items.length === 0) {
                $(tableId).append(`<tr><td colspan="5" class="text-center">Your cart is empty.</td></tr>`);
                return;
            }
            items.forEach(item => {
                $(tableId).append(`
                    <tr>
                        <td class="align-middle">${item.name}</td>
                        <td class="align-middle">
                            <input type="number" class="form-control form-control-sm qty-input" data-id="${item.id}" value="${item.quantity}" min="1" style="width: 60px;">
                        </td>
                        <td class="align-middle">৳${parseFloat(item.price).toFixed(2)}</td>
                        <td class="align-middle">৳${(item.quantity * item.price).toFixed(2)}</td>
                        <td class="align-middle">
                            <button class="btn btn-danger btn-sm remove-item" data-id="${item.id}">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                `);
            });
        }

        // Initial cart load
        updateCartDisplay();

        // Remove item
        $(document).on("click", ".remove-item", function() {
            let id = $(this).data('id');
            cart.removeItem(id);
            updateCartDisplay();
            Swal.fire({
                icon: 'success',
                title: 'Item Removed',
                text: 'The item has been removed from your cart.',
                timer: 1500,
                showConfirmButton: false
            });
        });

        // Quantity change
        $(document).on("change", ".qty-input", function() {
            let id = $(this).data('id');
            let quantity = parseInt($(this).val());
            if (quantity < 1) {
                $(this).val(1);
                quantity = 1;
            }
            cart.editItem(id, quantity);
            updateCartDisplay();
        });

        // Update offcanvas cart when shown
        $('#offcanvasCart').on('show.bs.offcanvas', function () {
            updateCartDisplay();
        });
    });
</script>
<?php $db->disconnect(); ?>
</body>
</html>