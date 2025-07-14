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
                    <?php if ($product['image']): ?>
                        <img src="assets/products/<?php echo htmlspecialchars($product['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <?php endif; ?>
                    <div class="card-body text-center d-flex flex-column">
                        <h5 class="card-title mb-1"><?php echo htmlspecialchars($product['name']); ?></h5>
                        <p class="text-muted small mb-1 flex-grow-1"><?php echo htmlspecialchars($product['short_description']); ?></p>
                        <p class="fw-bold mb-1 text-success">৳<?php echo number_format($product['selling_price'], 2); ?> <span class="text-muted fw-normal small">Per Unit</span></p>
                    </div>
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
<script>
    $(document).ready(function() {
        const cart = new Cart();

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
