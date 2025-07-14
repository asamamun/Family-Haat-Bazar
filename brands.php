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
        const cart = new Cart();
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
                                        <img src="assets/products/${product.image}" class="card-img-top" alt="${product.name}">
                                        <div class="card-body flex-grow-1">
                                            <h5 class="card-title">${product.name}</h5>
                                            <p class="card-text">Price: ${product.selling_price}</p>
                                        </div>
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

        $(document).on('click', '.btn-add-cart', function() {
            const productId = $(this).data('product-id');
            const productName = $(this).data('product-name');
            const productPrice = $(this).data('product-price');
            const productImage = $(this).data('product-image');
            
            cart.addItem({
                id: productId,
                name: productName,
                price: productPrice,
                quantity: 1,
                image: productImage
            });
            
            updateCartDisplay();

            Swal.fire({
                position: "top-end",
                icon: "success",
                title: "Item added to cart",
                showConfirmButton: false,
                timer: 1500
            });
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
<?php
$db->disconnect();
?>
