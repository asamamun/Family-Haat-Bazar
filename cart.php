<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/vendor/autoload.php';
use App\User;
use App\model\Category;
$db = new MysqliDb();
$page = "Cart";
?>
<?php require __DIR__ . '/components/header.php';?>
<!-- content start -->
<div class="container my-5">
    <h2 class="text-center mb-4 fw-bold"><i class="fas fa-shopping-cart me-2"></i>Your Shopping Cart</h2>
    
    <div class="card shadow-sm">
        <div class="card-body">
            <div id="cartEmptyMessage" class="alert alert-info text-center d-none">
                <i class="fas fa-info-circle me-2"></i>Your cart is empty. Start shopping now!
            </div>
            <table class="table table-hover table-bordered d-none" id="cartTableMain">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Item</th>
                        <th scope="col">Quantity</th>
                        <th scope="col">Price</th>
                        <th scope="col">Total</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody id="cartTable">
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end fw-bold">Grand Total</td>
                        <td id="grandTotal" class="fw-bold">0.00</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
            <div class="text-center mt-4">
                <a href="place_order.php" class="btn btn-success btn-lg"><i class="fas fa-check-circle me-2"></i>Proceed to Checkout</a>
            </div>
        </div>
    </div>
</div>
<!-- content end -->
<?php require __DIR__ . '/components/footer.php'; 
$db->disconnect();
?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    function populateItems(items, tableId) {
        $(tableId).html("");
        if (items.length === 0) {
            $("#cartEmptyMessage").removeClass("d-none");
            $("#cartTableMain").addClass("d-none");
            return;
        } else {
            $("#cartEmptyMessage").addClass("d-none");
            $("#cartTableMain").removeClass("d-none");
        }
        items.forEach(item => {
            $(tableId).append(`
                <tr>
                    <td class="align-middle">${item.name}</td>
                    <td class="align-middle">
                        <input type="number" class="form-control form-control-sm qty-input" data-id="${item.id}" value="${item.quantity}" min="1" style="width: 80px;">
                    </td>
                    <td class="align-middle">৳${parseFloat(item.price).toFixed(2)}</td>
                    <td class="align-middle">৳${(item.quantity * item.price).toFixed(2)}</td>
                    <td class="align-middle">
                        <button class="btn btn-danger btn-sm remove-item" data-id="${item.id}">
                            <i class="fas fa-trash-alt"></i> Remove
                        </button>
                    </td>
                </tr>
            `);
        });
    }

    let cart = new Cart();
    function updateCartDisplay() {
        let allitems = cart.getSummary();
        $("#cartCount").text(cart.getTotalItems());
        $("#grandTotal").text(parseFloat(cart.getTotalPrice()).toFixed(2));
        populateItems(allitems.items, "#cartTable");
        populateItems(allitems.items, "#cartContent table tbody");
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
<style>
.card {
    border-radius: 10px;
}
.table th, .table td {
    vertical-align: middle;
}
.btn-success {
    padding: 10px 30px;
    font-size: 1.1rem;
}
.alert-info {
    border-radius: 8px;
    padding: 20px;
}
.table-hover tbody tr:hover {
    background-color: #f8f9fa;
}
</style>
</body>
</html>