<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/../vendor/autoload.php';

// Database connection using MysqliDb
try {
    $db = new MysqliDb('localhost', 'root', '', 'haatbazar');
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}

// Fetch products from the database
$products = $db->get('products');

// Fetch categories
$categories = $db->get('categories', null, ['id', 'name']);

// Fetch subcategories
$subcategories = $db->get('subcategories', null, ['id', 'name', 'category_id']);
?>

<?php require __DIR__.'/components/header.php'; ?>

<!-- Rest of the HTML, CSS, and JavaScript remains unchanged -->
<style>
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
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <input type="text" class="form-control" placeholder="Search by ID or Name..." id="productSearch">
                                            <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <select class="form-select" id="categoryFilter">
                                            <option value="">All Categories</option>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?= htmlspecialchars($category['id']) ?>">
                                                    <?= htmlspecialchars($category['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <select class="form-select" id="subcategoryFilter">
                                            <option value="">All Subcategories</option>
                                            <?php foreach ($subcategories as $subcategory): ?>
                                                <option value="<?= htmlspecialchars($subcategory['id']) ?>" 
                                                        data-category="<?= htmlspecialchars($subcategory['category_id']) ?>">
                                                    <?= htmlspecialchars($subcategory['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="row" id="productGrid">
                                    <?php foreach ($products as $product): ?>
                                        <div class="col-md-3 mb-3 product-item" 
                                             data-id="<?= htmlspecialchars($product['id']) ?>"
                                             data-name="<?= htmlspecialchars(strtolower($product['name'])) ?>" 
                                             data-category="<?= htmlspecialchars($product['category_id']) ?>"
                                             data-subcategory="<?= htmlspecialchars($product['subcategory_id'] ?? '') ?>">
                                            <div class="card product-card">
                                            <img src="<?= settings()['root'] ?>assets/products/<?= htmlspecialchars($product['image'] ?? '') ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>">
                                                <div class="card-body">
                                                    <h6 class="card-title"><?= htmlspecialchars($product['name']) ?> (ID: <?= $product['id'] ?>)</h6>
                                                    <p class="card-text">$<?= number_format($product['selling_price'], 2) ?></p>
                                                    <button class="btn btn-sm btn-primary add-to-cart" 
                                                            data-id="<?= $product['id'] ?>" 
                                                            data-price="<?= $product['selling_price'] ?>" 
                                                            data-name="<?= htmlspecialchars($product['name']) ?>">
                                                            
                                                        Add
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
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
                                        <tbody id="cartBody">
                                            <!-- Cart items will appear here -->
                                        </tbody>
                                    </table>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <button class="btn btn-danger w-100" id="clearCart">Clear</button>
                                    </div>
                                    <div class

="col-md-6">
                                        <button class="btn btn-warning w-100" id="holdCart">Hold</button>
                                    </div>
                                </div>

                                <div class="mt-4 p-3 bg-light rounded" id="cartSummary">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Subtotal:</span>
                                        <span id="subtotal">$0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Tax (8%):</span>
                                        <span id="tax">$0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Discount:</span>
                                        <span id="discount">$0.00</span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between fw-bold">
                                        <span>Total:</span>
                                        <span id="total">$0.00</span>
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
                                    <input type="text" class="form-control" id="modalTotal" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Payment Method</label>
                                    <select class="form-select" id="paymentMethod">
                                        <option>Cash</option>
                                        <option>Credit Card</option>
                                        <option>Debit Card</option>
                                        <option>Mobile Payment</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Amount Tendered</label>
                                    <input type="number" class="form-control" id="amountTendered" placeholder="0.00" step="0.01">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Change</label>
                                    <input type="text" class="form-control" id="change" readonly>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss=" modal">Cancel</button>
                                <button type="button" class="btn btn-primary" id="completeSale">Complete Sale</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <?php require __DIR__.'/components/footer.php'; ?>
    </div>
</div>

<script src="<?= settings()['adminpage'] ?>assets/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="<?= settings()['adminpage'] ?>assets/js/scripts.js"></script>
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script> -->
<!-- <script src="<?= settings()['adminpage'] ?>assets/demo/chart-area-demo.js"></script>
<script src="<?= settings()['adminpage'] ?>assets/demo/chart-bar-demo.js"></script> -->
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>
<script src="<?= settings()['adminpage'] ?>assets/js/datatables-simple-demo.js"></script>
<script src="<?= settings()['adminpage'] ?>assets/js/jquery-3.7.1.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let cart = [];

    // Search and filter products
    const productSearch = document.getElementById('productSearch');
    const categoryFilter = document.getElementById('categoryFilter');
    const subcategoryFilter = document.getElementById('subcategoryFilter');
    const productItems = document.querySelectorAll('.product-item');

    // Filter subcategories based on selected category
    categoryFilter.addEventListener('change', function() {
        const selectedCategory = this.value;
        const subcategoryOptions = document.querySelectorAll('#subcategoryFilter option');
        subcategoryOptions.forEach(option => {
            if (option.value === '' || !selectedCategory || option.dataset.category === selectedCategory) {
                option.style.display = '';
            } else {
                option.style.display = 'none';
            }
        });
        subcategoryFilter.value = ''; // Reset subcategory
        filterProducts();
    });

    productSearch.addEventListener('input', filterProducts);
    categoryFilter.addEventListener('change', filterProducts);
    subcategoryFilter.addEventListener('change', filterProducts);

    function filterProducts() {
        const searchTerm = productSearch.value.toLowerCase();
        const category = categoryFilter.value;
        const subcategory = subcategoryFilter.value;

        productItems.forEach(item => {
            const id = item.dataset.id;
            const name = item.dataset.name;
            const itemCategory = item.dataset.category;
            const itemSubcategory = item.dataset.subcategory;

            const matchesSearch = searchTerm === '' || 
                                name.includes(searchTerm) || 
                                id === searchTerm;
            const matchesCategory = !category || itemCategory === category;
            const matchesSubcategory = !subcategory || itemSubcategory === subcategory;

            item.style.display = matchesSearch && matchesCategory && matchesSubcategory ? '' : 'none';
        });
    }

    // Add to cart
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const price = parseFloat(this.dataset.price);

            const existingItem = cart.find(item => item.id === id);
            if (existingItem) {
                existingItem.quantity++;
            } else {
                cart.push({ id, name, price, quantity: 1 });
            }
            updateCart();
        });
    });

    // Update cart display
    function updateCart() {
        const cartBody = document.getElementById('cartBody');
        cartBody.innerHTML = '';

        let subtotal = 0;
        cart.forEach(item => {
            const total = item.price * item.quantity;
            subtotal += total;

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${item.name}</td>
                <td><input type="number" class="form-control form-control-sm qty-input" value="${item.quantity}" min="1" data-id="${item.id}"></td>
                <td>$${item.price.toFixed(2)}</td>
                <td>$${total.toFixed(2)}</td>
                <td><button class="btn btn-sm btn-danger remove-item" data-id="${item.id}"><i class="fas fa-trash"></i></button></td>
            `;
            cartBody.appendChild(row);
        });

        const tax = subtotal * 0.08;
        const total = subtotal + tax;

        document.getElementById('subtotal').textContent = `$${subtotal.toFixed(2)}`;
        document.getElementById('tax').textContent = `$${tax.toFixed(2)}`;
        document.getElementById('total').textContent = `$${total.toFixed(2)}`;
        document.getElementById('modalTotal').value = `$${total.toFixed(2)}`;

        // Update quantity
document.querySelectorAll('.qty-input').forEach(input => {

            input.addEventListener('change', function() {
                const id = this.dataset.id;
                const newQty = parseInt(this.value);
                const item = cart.find(item => item.id === id);
                if (item && newQty > 0) {
                    item.quantity = newQty;
                    updateCart();
                }
            });
        });

        // Remove item
        document.querySelectorAll('.remove-item').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                cart = cart.filter(item => item.id !== id);
                updateCart();
            });
        });
    }

    // Clear cart
    document.getElementById('clearCart').addEventListener('click', function() {
        cart = [];
        updateCart();
    });

    // Hold cart (store in sessionStorage)
    document.getElementById('holdCart').addEventListener('click', function() {
        sessionStorage.setItem('heldCart', JSON.stringify(cart));
        alert('Cart held successfully!');
    });

    // Checkout
    document.getElementById('checkoutBtn').addEventListener('click', function() {
        if (cart.length === 0) {
            alert('Cart is empty!');
            return;
        }
        var paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
        paymentModal.show();
    });

    // Amount tendered calculation
    document.getElementById('amountTendered').addEventListener('input', function() {
        const total = parseFloat(document.getElementById('modalTotal').value.replace('$', '')) || 0;
        const tendered = parseFloat(this.value) || 0;
        const change = tendered - total;
        document.getElementById('change').value = `$${change.toFixed(2)}`;
    });

        // Here you would typically send the sale data to the server
// Complete sale
document.getElementById('completeSale').addEventListener('click', function() {
        event.preventDefault();        
    const tendered = parseFloat(document.getElementById('amountTendered').value) || 0;
    const total = parseFloat(document.getElementById('modalTotal').value.replace('$', '')) || 0;
    if (tendered < total) {
        alert('Insufficient amount tendered!');
        return;
    }

    // Prepare data to send to process_order.php
    let data = {
        subtotal: parseFloat(($("#subtotal").text()).replace('$', '')),
        discount_amount: parseFloat(($("#discount").text()).replace('$', '')),
        tax_amount: parseFloat(($("#tax").text()).replace('$', '')),
        shipping_amount: 0.00,
        total_amount: parseFloat(($("#total").text()).replace('$', '')),
        notes: null,
        items: cart // assuming 'cart' is an array of items
    };
    // console.log("data",data);
    // return;

    // Send data via AJAX to process_order.php
    $.ajax({
        url: 'process_order.php',
        method: 'POST',
        data: data,
        success: function(response) {
            console.log(response);
            if(response.success){
                Swal.fire({
                    position: "top-end",
                    icon: "success",
                    title: response.message + ",, Order # " + response.order_number,
                    showConfirmButton: false,
                    timer: 1500
                })
                
            }
            // alert('Sale completed successfully!');
            cart = [];
            updateCart();
            bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
        },
        error: function() {
            alert('An error occurred while processing the order.');
        }
    });
});


});
</script>
</body>
</html>