<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/../vendor/autoload.php';
?>
<?php require __DIR__.'/components/header.php'; ?>

<style>
    /* Optional custom style */
#productGrid {
    max-height: 400px;
    overflow-y: auto;
}
.product-card {
    transition: transform 0.2s ease;
}
.product-card:hover {
    transform: scale(1.03);
}
.modal-body .form-label {
    font-weight: 500;
}
.modal-body input, .modal-body select {
    border-radius: 0.375rem;
}

</style>

    </head>
    <body class="sb-nav-fixed">
    <?php require __DIR__.'/components/navbar.php'; ?>
        <div id="layoutSidenav">
        <?php require __DIR__.'/components/sidebar.php'; ?>
            <div id="layoutSidenav_content">
                <main>
                    <!-- changed content -->
                        <div class="container-fluid px-4">
    <h1 class="mt-4">Point of Sale</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Dashboard / POS</li>
    </ol>

    <div class="row">
        <!-- Product Selection -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-shopping-basket me-1"></i>
                    Products
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Search products..." id="productSearch">
                                <button class="btn btn-outline-secondary" type="button">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <select class="form-select" id="categoryFilter">
                                <option value="">All Categories</option>
                                <option value="1">Electronics</option>
                                <option value="2">Clothing</option>
                                <option value="3">Groceries</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row" id="productGrid">
                        <!-- Product items will be loaded here -->
                        <div class="col-md-3 mb-3">
                            <div class="card product-card">
                                <img src="https://via.placeholder.com/150" class="card-img-top" alt="Product">
                                <div class="card-body">
                                    <h6 class="card-title">Product Name</h6>
                                    <p class="card-text">0</p>
                                    <button class="btn btn-sm btn-primary add-to-cart">Add</button>
                                </div>
                            </div>
                        </div>
                        <!-- More products... -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Cart and Checkout -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-shopping-cart me-1"></i>
                    Current Sale
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="cartTable">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Qty</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Cart items will appear here -->
                                <tr>
                                    <td>Sample Product</td>
                                    <td><input type="number" class="form-control form-control-sm qty-input" value="1" min="1"></td>
                                    <td>0</td>
                                    <td>0</td>
                                    <td><button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <button class="btn btn-danger w-100" id="clearCart">Clear</button>
                        </div>
                        <div class="col-md-6">
                            <button class="btn btn-warning w-100" id="holdCart">Hold</button>
                        </div>
                    </div>

                    <div class="mt-4 p-3 bg-light rounded">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span>0</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax (8%):</span>
                            <span>0</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Discount:</span>
                            <span>0</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold">
                            <span>Total:</span>
                            <span>0</span>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button class="btn btn-success w-100 py-3" id="checkoutBtn">
                            <i class="fas fa-cash-register me-2"></i> Process Payment
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Process Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Total Amount</label>
                        <input type="text" class="form-control" value="$21.59" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <select class="form-select">
                            <option>Cash</option>
                            <option>Credit Card</option>
                            <option>Debit Card</option>
                            <option>Mobile Payment</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount Tendered</label>
                        <input type="number" class="form-control" placeholder="0.00">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Change</label>
                        <input type="text" class="form-control" value="$0.00" readonly>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary">Complete Sale</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// You can add JavaScript functionality here
document.addEventListener('DOMContentLoaded', function() {
    // Initialize POS functionality
    document.getElementById('checkoutBtn').addEventListener('click', function() {
        var paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
        paymentModal.show();
    });
    
    // Add more POS-specific JavaScript here
});
</script>
                    <!-- changed content  ends-->
                </main>
<!-- footer -->
<?php require __DIR__.'/components/footer.php'; ?>
            </div>
        </div>
        <script src="<?= settings()['adminpage'] ?>assets/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="<?= settings()['adminpage'] ?>assets/js/scripts.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="<?= settings()['adminpage'] ?>assets/demo/chart-area-demo.js"></script>
        <script src="<?= settings()['adminpage'] ?>assets/demo/chart-bar-demo.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>
        <script src="<?= settings()['adminpage'] ?>assets/js/datatables-simple-demo.js"></script>
    </body>
</html>
