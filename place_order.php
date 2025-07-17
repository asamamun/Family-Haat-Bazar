<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    $_SESSION['message'] = "You must log in to place an order.";
    header('Location: login.php');
    exit;
}
require __DIR__ . '/vendor/autoload.php';
use App\User;
use App\model\Category;
$db = new MysqliDb();
$page = "Checkout";
?>
<?php require __DIR__ . '/components/header.php';?>
<!-- content start -->
<div class="container my-5">
    <h2 class="text-center mb-4 fw-bold"><i class="fas fa-shopping-cart me-2"></i>Checkout</h2>
    
    <div id="cartEmptyMessage" class="alert alert-info text-center d-none">
        <i class="fas fa-info-circle me-2"></i>Your cart is empty. Start shopping now!
    </div>

    <div class="row">
        <!-- Cart and Address Section -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h3 class="mb-0"><i class="fas fa-cart-arrow-down me-2"></i>Cart Items</h3>
                </div>
                <div class="card-body">
                    <table class="table table-hover table-bordered" id="cartTableMain">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Price</th>
                                <th>Total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="cartTable"></tbody>
                    </table>
                </div>
            </div>

            <!-- Billing Address -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h3 class="mb-0"><i class="fas fa-address-card me-2"></i>Billing Address</h3>
                </div>
                <div class="card-body">
                    <form id="billingForm">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="billing_first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" id="billing_first_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label for="billing_last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" id="billing_last_name" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="billing_company" class="form-label">Company (Optional)</label>
                            <input type="text" id="billing_company" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="billing_address_line_1" class="form-label">Address Line 1 <span class="text-danger">*</span></label>
                            <input type="text" id="billing_address_line_1" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="billing_address_line_2" class="form-label">Address Line 2 (Optional)</label>
                            <input type="text" id="billing_address_line_2" class="form-control">
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="billing_city" class="form-label">City <span class="text-danger">*</span></label>
                                <input type="text" id="billing_city" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label for="billing_state" class="form-label">State <span class="text-danger">*</span></label>
                                <input type="text" id="billing_state" class="form-control" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="billing_postal_code" class="form-label">Postal Code <span class="text-danger">*</span></label>
                                <input type="text" id="billing_postal_code" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label for="billing_country" class="form-label">Country <span class="text-danger">*</span></label>
                                <input type="text" id="billing_country" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="billing_phone" class="form-label">Phone <span class="text-danger">*</span></label>
                            <input type="tel" id="billing_phone" class="form-control" required>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Shipping Address -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h3 class="mb-0"><i class="fas fa-truck me-2"></i>Shipping Address</h3>
                </div>
                <div class="card-body">
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="same_as_shipping">
                        <label class="form-check-label" for="same_as_shipping">Same as Billing Address</label>
                    </div>
                    <form id="shippingForm">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="shipping_first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" id="shipping_first_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label for="shipping_last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" id="shipping_last_name" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="shipping_company" class="form-label">Company (Optional)</label>
                            <input type="text" id="shipping_company" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="shipping_address_line_1" class="form-label">Address Line 1 <span class="text-danger">*</span></label>
                            <input type="text" id="shipping_address_line_1" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="shipping_address_line_2" class="form-label">Address Line 2 (Optional)</label>
                            <input type="text" id="shipping_address_line_2" class="form-control">
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="shipping_city" class="form-label">City <span class="text-danger">*</span></label>
                                <input type="text" id="shipping_city" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label for="shipping_state" class="form-label">State <span class="text-danger">*</span></label>
                                <input type="text" id="shipping_state" class="form-control" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="shipping_postal_code" class="form-label">Postal Code <span class="text-danger">*</span></label>
                                <input type="text" id="shipping_postal_code" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label for="shipping_country" class="form-label">Country <span class="text-danger">*</span></label>
                                <input type="text" id="shipping_country" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="shipping_phone" class="form-label">Phone <span class="text-danger">*</span></label>
                            <input type="tel" id="shipping_phone" class="form-control" required>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Order Summary and Payment -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h3 class="mb-0"><i class="fas fa-receipt me-2"></i>Order Summary</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <td>Net Total</td>
                            <td id="netTotal">৳0.00</td>
                        </tr>
                        <tr>
                            <td>VAT (<?= config('vat.default') ?>)</td>
                            <td id="vatAmount">৳0.00</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Grand Total</td>
                            <td id="grandTotal" class="fw-bold">৳0.00</td>
                        </tr>
                    </table>

                    <!-- Payment Section -->
                    <h3 class="mt-4"><i class="fas fa-credit-card me-2"></i>Payment</h3>
                    <table class="table table-bordered">
                        <tr>
                            <td>Payment Method</td>
                            <td>
                                <select name="payment_method" id="payment_method" class="form-select">
                                    <option value="bkash">bKash</option>
                                    <option value="nagad">Nagad</option>
                                    <option value="cash" selected>Cash on Delivery</option>
                                </select>
                            </td>
                        </tr>
                        <tr id="transaction_id_row" style="display: none;">
                            <td>Transaction ID</td>
                            <td><input type="text" id="transaction_id" class="form-control"></td>
                        </tr>
                    </table>

                    <!-- Notes Section -->
                    <h3 class="mt-4"><i class="fas fa-sticky-note me-2"></i>Order Notes</h3>
                    <textarea name="notes" id="notes" class="form-control" rows="4" placeholder="Add any special instructions here..."></textarea>

                    <div class="text-center mt-4">
                        <button id="placeOrder" class="btn btn-success btn-lg"><i class="fas fa-check-circle me-2"></i>Place Order</button>
                    </div>
                </div>
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
    window.updateCartDisplay = function() {
        let allitems = cart.getSummary();
        $("#cartCountButton").text(cart.getTotalItems());
        populateItems(allitems.items, "#cartContent table tbody");
        populateItems(allitems.items, "#cartTable");
        setTotal(); // Update totals when cart changes
    }

    function setTotal() {
        let netTotal = parseFloat(cart.getTotalPrice());
        let vatRate = <?php 
            $vatConfig = config('vat.rates');
            $defaultVat = config('vat.default');
            foreach ($vatConfig as $vat) {
                if ($vat['name'] === $defaultVat) {
                    echo $vat['value'];
                    break;
                }
            }
        ?>;
        let vatAmount = netTotal * vatRate;
        let grandTotal = netTotal + vatAmount;
        
        $("#netTotal").text(`৳${netTotal.toFixed(2)}`);
        $("#vatAmount").text(`৳${vatAmount.toFixed(2)}`);
        $("#grandTotal").text(`৳${grandTotal.toFixed(2)}`);
    }

    // Initial cart load
    updateCartDisplay();
    setTotal();

    // Same as shipping checkbox
    $("#same_as_shipping").on("change", function() {
        if ($(this).is(":checked")) {
            $("#shippingForm input").each(function() {
                let field = $(this).attr("id").replace("shipping_", "billing_");
                $(this).val($(`#${field}`).val());
            });
        }
    });

    // Payment method change
    $("#payment_method").on("change", function() {
        $("#transaction_id_row").toggle($(this).val() !== "cash");
        if ($(this).val() !== "cash") {
            $("#transaction_id").prop("required", true);
        } else {
            $("#transaction_id").prop("required", false);
        }
    });

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
    $('#offcanvasCart').on('show.bs.offcanvas', updateCartDisplay);

    // Form validation and place order
    $("#placeOrder").on("click", function() {
        let billingValid = $("#billingForm")[0].checkValidity();
        let shippingValid = $("#shippingForm")[0].checkValidity();
        if (!billingValid || !shippingValid) {
            $("#billingForm, #shippingForm").addClass("was-validated");
            Swal.fire({
                icon: 'error',
                title: 'Incomplete Form',
                text: 'Please fill out all required fields.',
            });
            return;
        }

        let data = {
            billing_first_name: $("#billing_first_name").val(),
            billing_last_name: $("#billing_last_name").val(),
            billing_company: $("#billing_company").val(),
            billing_address_line_1: $("#billing_address_line_1").val(),
            billing_address_line_2: $("#billing_address_line_2").val(),
            billing_city: $("#billing_city").val(),
            billing_state: $("#billing_state").val(),
            billing_postal_code: $("#billing_postal_code").val(),
            billing_country: $("#billing_country").val(),
            billing_phone: $("#billing_phone").val(),
            shipping_first_name: $("#shipping_first_name").val(),
            shipping_last_name: $("#shipping_last_name").val(),
            shipping_company: $("#shipping_company").val(),
            shipping_address_line_1: $("#shipping_address_line_1").val(),
            shipping_address_line_2: $("#shipping_address_line_2").val(),
            shipping_city: $("#shipping_city").val(),
            shipping_state: $("#shipping_state").val(),
            shipping_postal_code: $("#shipping_postal_code").val(),
            shipping_country: $("#shipping_country").val(),
            shipping_phone: $("#shipping_phone").val(),
            payment_method: $("#payment_method").val(),
            transaction_id: $("#transaction_id").val(),
            notes: $("#notes").val(),
            items: cart.getSummary().items,
            totalItems: cart.getTotalItems(),
            totalPrice: cart.getTotalPrice(),
            discount_amount: 0,
            tax_amount: $("#vatAmount").text().replace("৳", ""),
            grandTotal: $("#grandTotal").text().replace("৳", ""),
        };

        if (data.payment_method === "cash") {
            delete data.transaction_id;
        }

        $.ajax({
            url: "<?= settings()['root'] ?>apis/processOrder.php",
            method: "POST",
            data: data,
            success: function(response) {
                // response = JSON.parse(response);
                console.log(response);

                if (response.success) {
                    Swal.fire({
                        position: "top-end",
                        icon: "success",
                        title: "Order Placed Successfully",
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        // window.location.href = "order_confirmation.php"; // Redirect to a confirmation page
                        //clear cart and load index
                        cart.clearCart();
                        // updateCartDisplay();
                        window.location.href = "index.php";
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Order Failed",
                        text: response.message || "An error occurred while placing your order."
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "An error occurred while processing your request."
                });
            }
        });
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
.form-control, .form-select {
    border-radius: 6px;
}
.was-validated .form-control:invalid {
    border-color: #dc3545;
}
</style>
</body>
</html>